<?php

/**
 * Demo script to test Product Variants System
 * Run this after setting up the variants system
 */

require_once 'vendor/autoload.php';

use App\Models\Product;
use App\Models\ProductAttribute;
use App\Services\ProductVariantService;

echo "🧪 Testing Product Variants System\n";
echo "==================================\n\n";

// Test 1: Check if attributes are seeded
echo "1. Checking available attributes...\n";
$attributes = ProductAttribute::getVariationOptions();
echo "   Found " . $attributes->count() . " attributes:\n";
foreach ($attributes as $attr) {
    echo "   - {$attr->name} ({$attr->values->count()} values)\n";
}
echo "\n";

// Test 2: Create a sample product
echo "2. Creating sample product...\n";
$product = Product::create([
    'product_name' => 'Trà sữa cao cấp',
    'product_slug' => 'tra-sua-cao-cap',
    'sku' => 'TSU-001',
    'product_description' => 'Trà sữa thơm ngon với nhiều hương vị',
    'product_content' => 'Trà sữa được làm từ nguyên liệu tự nhiên',
    'cost_price' => 25000,
    'sale_price' => 45000,
    'product_type' => 'simple',
    'product_status' => 'publish',
    'language' => 'vi'
]);
echo "   Created product: {$product->product_name} (ID: {$product->id})\n\n";

// Test 3: Create variants
echo "3. Creating variants...\n";
$variantService = new ProductVariantService();

// Get attribute IDs
$flavorAttr = ProductAttribute::where('slug', 'huong-vi')->first();
$sizeAttr = ProductAttribute::where('slug', 'kich-thuoc')->first();

if ($flavorAttr && $sizeAttr) {
    $attributeData = [
        $flavorAttr->id => [1, 2, 3], // First 3 flavor values
        $sizeAttr->id => [4, 5]       // First 2 size values
    ];
    
    try {
        $variants = $variantService->createVariants($product, $attributeData);
        echo "   Created " . count($variants) . " variants:\n";
        
        foreach ($variants as $variant) {
            echo "   - {$variant->variant_name} (SKU: {$variant->sku}, Price: " . number_format($variant->sale_price) . " VND)\n";
        }
    } catch (Exception $e) {
        echo "   Error creating variants: " . $e->getMessage() . "\n";
    }
} else {
    echo "   Error: Required attributes not found\n";
}
echo "\n";

// Test 4: Check product after variant creation
echo "4. Checking product after variant creation...\n";
$product->refresh();
echo "   Product type: {$product->product_type}\n";
echo "   Has variants: " . ($product->has_variants ? 'Yes' : 'No') . "\n";
echo "   Variants count: {$product->variants_count}\n";
echo "   Price range: " . number_format($product->min_price) . " - " . number_format($product->max_price) . " VND\n";
echo "\n";

// Test 5: Test variant combinations
echo "5. Testing variant combinations...\n";
$combinations = $variantService->getVariantCombinations($product);
echo "   Found " . count($combinations) . " variant combinations:\n";
foreach ($combinations as $combo) {
    $attributes = collect($combo['attributes'])->pluck('value_name')->implode(', ');
    echo "   - {$combo['name']}: {$attributes} - {$combo['formatted_price']}\n";
}
echo "\n";

// Test 6: Test inventory integration
echo "6. Testing inventory integration...\n";
$variants = $product->variants;
foreach ($variants->take(2) as $variant) {
    try {
        \App\Models\Inventory::addVariantQuantity($variant->id, 100);
        $quantity = \App\Models\Inventory::getVariantQuantity($variant->id);
        echo "   Added 100 units to {$variant->variant_name}, current stock: {$quantity}\n";
    } catch (Exception $e) {
        echo "   Error with inventory for {$variant->variant_name}: " . $e->getMessage() . "\n";
    }
}
echo "\n";

echo "✅ Product Variants System test completed!\n";
echo "\n";
echo "📊 Summary:\n";
echo "- Attributes: " . $attributes->count() . " available\n";
echo "- Product created: {$product->product_name}\n";
echo "- Variants created: " . $product->variants_count . "\n";
echo "- Price range: " . number_format($product->min_price) . " - " . number_format($product->max_price) . " VND\n";
echo "\n";
echo "🎯 You can now:\n";
echo "1. Visit /admin/products to see the product\n";
echo "2. Edit the product to manage variants\n";
echo "3. Create new variable products with different attributes\n";
echo "4. Test the variant management interface\n";
