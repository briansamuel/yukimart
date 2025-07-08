<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Models\ProductVariantAttribute;
use App\Models\Inventory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ProductVariantService
{
    /**
     * Create variants for a product based on selected attributes
     */
    public function createVariants(Product $product, array $attributeData)
    {
        DB::beginTransaction();
        
        try {
            // Update product to be variable
            $product->update([
                'product_type' => 'variable',
                'has_variants' => true,
                'variant_attributes' => array_keys($attributeData)
            ]);

            // Generate all possible combinations
            $combinations = $this->generateAttributeCombinations($attributeData);
            
            $variants = [];
            foreach ($combinations as $index => $combination) {
                $variant = $this->createSingleVariant($product, $combination, $index === 0);
                $variants[] = $variant;
            }

            // Update product price range
            $product->updateVariantStats();

            DB::commit();
            return $variants;
            
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Create a single variant
     */
    public function createSingleVariant(Product $product, array $attributeCombination, bool $isDefault = false)
    {
        // Generate variant name
        $variantName = $this->generateVariantName($product, $attributeCombination);
        
        // Calculate variant price (base price + adjustments)
        $variantPrice = $this->calculateVariantPrice($product, $attributeCombination);
        
        // Create the variant
        $variant = ProductVariant::create([
            'parent_product_id' => $product->id,
            'variant_name' => $variantName,
            'sku' => ProductVariant::generateUniqueSku($product->id),
            'cost_price' => $product->cost_price,
            'sale_price' => $variantPrice,
            'regular_price' => $variantPrice,
            'weight' => $product->weight,
            'points' => $product->points,
            'reorder_point' => $product->reorder_point,
            'is_default' => $isDefault,
            'is_active' => true,
            'sort_order' => 0
        ]);

        // Create variant attributes
        foreach ($attributeCombination as $attributeId => $valueId) {
            ProductVariantAttribute::create([
                'variant_id' => $variant->id,
                'attribute_id' => $attributeId,
                'attribute_value_id' => $valueId
            ]);
        }

        // Create inventory record for variant
        $this->createVariantInventory($variant);

        return $variant;
    }

    /**
     * Update variant
     */
    public function updateVariant(ProductVariant $variant, array $data)
    {
        DB::beginTransaction();
        
        try {
            // Update variant data
            $variant->update($data);

            // Update variant attributes if provided
            if (isset($data['attributes'])) {
                ProductVariantAttribute::updateVariantAttributes($variant->id, $data['attributes']);
                
                // Regenerate variant name
                $attributeCombination = [];
                foreach ($data['attributes'] as $attrId => $valueId) {
                    $attributeCombination[$attrId] = $valueId;
                }
                
                $newName = $this->generateVariantName($variant->parentProduct, $attributeCombination);
                $variant->update(['variant_name' => $newName]);
            }

            // Update parent product stats
            $variant->parentProduct->updateVariantStats();

            DB::commit();
            return $variant;
            
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Delete variant
     */
    public function deleteVariant(ProductVariant $variant)
    {
        DB::beginTransaction();
        
        try {
            $parentProduct = $variant->parentProduct;
            
            // Delete variant attributes
            ProductVariantAttribute::where('variant_id', $variant->id)->delete();
            
            // Delete inventory records
            Inventory::where('variant_id', $variant->id)->delete();
            
            // Delete the variant
            $variant->delete();

            // Update parent product stats
            $parentProduct->updateVariantStats();
            
            // If no variants left, convert back to simple product
            if ($parentProduct->variants()->count() === 0) {
                $parentProduct->update([
                    'product_type' => 'simple',
                    'has_variants' => false,
                    'variants_count' => 0,
                    'variant_attributes' => null,
                    'min_price' => null,
                    'max_price' => null
                ]);
            }

            DB::commit();
            return true;
            
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Generate all possible attribute combinations
     */
    private function generateAttributeCombinations(array $attributeData)
    {
        $combinations = [[]];
        
        foreach ($attributeData as $attributeId => $valueIds) {
            $newCombinations = [];
            
            foreach ($combinations as $combination) {
                foreach ($valueIds as $valueId) {
                    $newCombination = $combination;
                    $newCombination[$attributeId] = $valueId;
                    $newCombinations[] = $newCombination;
                }
            }
            
            $combinations = $newCombinations;
        }
        
        return $combinations;
    }

    /**
     * Generate variant name from attribute combination
     */
    private function generateVariantName(Product $product, array $attributeCombination)
    {
        $parts = [$product->product_name];
        
        foreach ($attributeCombination as $attributeId => $valueId) {
            $value = ProductAttributeValue::find($valueId);
            if ($value) {
                $parts[] = $value->value;
            }
        }
        
        return implode(' - ', $parts);
    }

    /**
     * Calculate variant price with adjustments
     */
    private function calculateVariantPrice(Product $product, array $attributeCombination)
    {
        $basePrice = $product->sale_price;
        $adjustment = 0;
        
        foreach ($attributeCombination as $attributeId => $valueId) {
            $value = ProductAttributeValue::find($valueId);
            if ($value && $value->price_adjustment) {
                $adjustment += $value->price_adjustment;
            }
        }
        
        return $basePrice + $adjustment;
    }

    /**
     * Create inventory record for variant
     */
    private function createVariantInventory(ProductVariant $variant)
    {
        // Get default warehouse
        $defaultWarehouse = \App\Models\Warehouse::where('is_default', true)->first();
        
        if ($defaultWarehouse) {
            Inventory::create([
                'product_id' => $variant->parent_product_id,
                'variant_id' => $variant->id,
                'warehouse_id' => $defaultWarehouse->id,
                'quantity' => 0
            ]);
        }
    }

    /**
     * Get available attributes for creating variants
     */
    public function getAvailableAttributes()
    {
        return ProductAttribute::active()
                              ->forVariation()
                              ->with(['values' => function($query) {
                                  $query->where('status', 'active')
                                        ->orderBy('sort_order');
                              }])
                              ->ordered()
                              ->get();
    }

    /**
     * Get variant combinations for display
     */
    public function getVariantCombinations(Product $product)
    {
        if (!$product->isVariable()) {
            return [];
        }

        return $product->activeVariants()
                      ->with(['attributeValues.attribute'])
                      ->get()
                      ->map(function ($variant) {
                          return [
                              'id' => $variant->id,
                              'name' => $variant->variant_name,
                              'sku' => $variant->sku,
                              'price' => $variant->sale_price,
                              'formatted_price' => number_format($variant->sale_price, 0, ',', '.') . ' VND',
                              'attributes' => $variant->attributeValues->map(function ($value) {
                                  return [
                                      'attribute_name' => $value->attribute->name,
                                      'value_name' => $value->value,
                                      'color_code' => $value->color_code
                                  ];
                              })
                          ];
                      });
    }

    /**
     * Bulk update variant prices
     */
    public function bulkUpdatePrices(Product $product, array $priceData)
    {
        DB::beginTransaction();
        
        try {
            foreach ($priceData as $variantId => $prices) {
                $variant = ProductVariant::find($variantId);
                if ($variant && $variant->parent_product_id == $product->id) {
                    $variant->update([
                        'cost_price' => $prices['cost_price'] ?? $variant->cost_price,
                        'sale_price' => $prices['sale_price'] ?? $variant->sale_price,
                        'regular_price' => $prices['regular_price'] ?? $variant->regular_price,
                    ]);
                }
            }

            // Update parent product price range
            $product->updateVariantStats();

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Create variants from form data (new UI format)
     */
    public function createVariantsFromFormData(Product $product, array $variantData)
    {
        DB::beginTransaction();

        try {
            // Update product to be variable
            $product->update([
                'product_type' => 'variable',
                'has_variants' => true
            ]);

            $variants = [];

            foreach ($variantData as $variantInfo) {
                $variant = $this->createVariantFromFormData($product, $variantInfo);
                if ($variant) {
                    $variants[] = $variant;
                }
            }

            // Update product statistics
            $product->updateVariantStats();

            DB::commit();
            return $variants;

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Create a single variant from form data
     */
    private function createVariantFromFormData(Product $product, array $variantInfo)
    {
        // Generate variant name from attributes
        $variantName = $product->product_name;
        if (isset($variantInfo['attributes']) && is_array($variantInfo['attributes'])) {
            $attributeNames = array_column($variantInfo['attributes'], 'value');
            $variantName .= ' - ' . implode(' ', $attributeNames);
        }

        // Generate SKU
        $sku = $variantInfo['sku'] ?? ProductVariant::generateUniqueSku($product->id);

        // Create the variant
        $variant = ProductVariant::create([
            'parent_product_id' => $product->id,
            'variant_name' => $variantName,
            'sku' => $sku,
            'barcode' => $variantInfo['barcode'] ?? null,
            'cost_price' => $variantInfo['cost_price'] ?? $product->cost_price ?? 0,
            'sale_price' => $variantInfo['sale_price'] ?? $product->sale_price ?? 0,
            'regular_price' => $variantInfo['regular_price'] ?? $product->sale_price ?? 0,
            'weight' => $variantInfo['weight'] ?? $product->weight ?? 0,
            'points' => $variantInfo['points'] ?? $product->points ?? 0,
            'reorder_point' => $variantInfo['reorder_point'] ?? $product->reorder_point ?? 0,
            'is_active' => true,
            'sort_order' => ProductVariant::where('parent_product_id', $product->id)->count() + 1
        ]);

        // Create variant attribute relationships
        if (isset($variantInfo['attributes']) && is_array($variantInfo['attributes'])) {
            foreach ($variantInfo['attributes'] as $attributeData) {
                // Find or create attribute value
                $attributeValue = $this->findOrCreateAttributeValue(
                    $attributeData['attribute_id'],
                    $attributeData['value']
                );

                if ($attributeValue) {
                    ProductVariantAttribute::create([
                        'variant_id' => $variant->id,
                        'attribute_id' => $attributeData['attribute_id'],
                        'attribute_value_id' => $attributeValue->id
                    ]);
                }
            }
        }

        // Create inventory record
        if (isset($variantInfo['stock_quantity']) && $variantInfo['stock_quantity'] > 0) {
            $this->createVariantInventoryWithQuantity($variant, $variantInfo['stock_quantity']);
        } else {
            $this->createVariantInventory($variant);
        }

        // Handle variant images
        if (isset($variantInfo['images']) && is_array($variantInfo['images'])) {
            $this->handleVariantImages($variant, $variantInfo['images']);
        }

        return $variant;
    }

    /**
     * Find or create attribute value
     */
    private function findOrCreateAttributeValue($attributeId, $value)
    {
        $attributeValue = ProductAttributeValue::where('attribute_id', $attributeId)
            ->where('value', $value)
            ->first();

        if (!$attributeValue) {
            $attributeValue = ProductAttributeValue::create([
                'attribute_id' => $attributeId,
                'value' => $value,
                'slug' => \Str::slug($value),
                'price_adjustment' => 0,
                'sort_order' => ProductAttributeValue::where('attribute_id', $attributeId)->count() + 1,
                'status' => 'active'
            ]);
        }

        return $attributeValue;
    }

    /**
     * Create inventory record for variant with initial quantity
     */
    private function createVariantInventoryWithQuantity(ProductVariant $variant, $quantity)
    {
        // Get default warehouse
        $defaultWarehouse = \App\Models\Warehouse::where('is_default', true)->first();

        if ($defaultWarehouse) {
            Inventory::create([
                'product_id' => $variant->parent_product_id,
                'variant_id' => $variant->id,
                'warehouse_id' => $defaultWarehouse->id,
                'quantity' => $quantity
            ]);

            // Create inventory transaction
            \App\Models\InventoryTransaction::create([
                'product_id' => $variant->parent_product_id,
                'variant_id' => $variant->id,
                'warehouse_id' => $defaultWarehouse->id,
                'transaction_type' => 'adjustment',
                'quantity' => $quantity,
                'reference_type' => 'variant_creation',
                'reference_id' => $variant->id,
                'notes' => 'Initial stock for variant: ' . $variant->variant_name,
                'created_by' => auth()->id()
            ]);
        }
    }

    /**
     * Handle variant images upload
     */
    private function handleVariantImages($variant, $images)
    {
        // This will be implemented when we add variant images support
        // For now, just store the first image as variant thumbnail
        if (!empty($images) && isset($images[0])) {
            $variant->update(['variant_thumbnail' => $images[0]]);
        }
    }
}
