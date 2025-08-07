<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'parent_product_id' => $this->parent_product_id,
            'variant_name' => $this->variant_name,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            
            // Pricing
            'cost_price' => $this->cost_price,
            'sale_price' => $this->sale_price,
            'regular_price' => $this->regular_price,
            'formatted_cost_price' => number_format($this->cost_price, 0, ',', '.') . ' VND',
            'formatted_sale_price' => number_format($this->sale_price, 0, ',', '.') . ' VND',
            'formatted_regular_price' => $this->regular_price ? 
                number_format($this->regular_price, 0, ',', '.') . ' VND' : null,
            
            // Physical attributes
            'weight' => $this->weight,
            'dimensions' => $this->dimensions,
            
            // Images
            'image' => $this->image,
            'images' => $this->images,
            
            // Business attributes
            'points' => $this->points,
            'reorder_point' => $this->reorder_point,
            
            // Status
            'is_default' => $this->is_default,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
            
            // Meta data
            'meta_data' => $this->meta_data,
            
            // Inventory information
            'stock_quantity' => $this->whenLoaded('inventory', function () {
                return $this->inventory ? $this->inventory->quantity : 0;
            }),
            
            'available_quantity' => $this->whenLoaded('inventory', function () {
                $stockQuantity = $this->inventory ? $this->inventory->quantity : 0;
                $reservedQuantity = $this->getAttribute('reserved_quantity') ?? 0;
                return max(0, $stockQuantity - $reservedQuantity);
            }),
            
            'inventory_value' => $this->whenLoaded('inventory', function () {
                $quantity = $this->inventory ? $this->inventory->quantity : 0;
                return $quantity * $this->cost_price;
            }),
            
            'formatted_inventory_value' => $this->whenLoaded('inventory', function () {
                $quantity = $this->inventory ? $this->inventory->quantity : 0;
                $value = $quantity * $this->cost_price;
                return number_format($value, 0, ',', '.') . ' VND';
            }),
            
            // Timestamps
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            
            // Computed attributes
            'has_stock' => $this->whenLoaded('inventory', function () {
                return $this->inventory && $this->inventory->quantity > 0;
            }),
            
            'needs_reordering' => $this->whenLoaded('inventory', function () {
                $quantity = $this->inventory ? $this->inventory->quantity : 0;
                return $quantity <= $this->reorder_point;
            }),
            
            'profit_margin' => $this->cost_price > 0 ? 
                round((($this->sale_price - $this->cost_price) / $this->cost_price) * 100, 2) : 0,
            
            'profit_per_unit' => $this->sale_price - $this->cost_price,
            'formatted_profit_per_unit' => number_format($this->sale_price - $this->cost_price, 0, ',', '.') . ' VND',
            
            // Parent product information
            'parent_product' => $this->whenLoaded('parentProduct', function () {
                return [
                    'id' => $this->parentProduct->id,
                    'product_name' => $this->parentProduct->product_name,
                    'sku' => $this->parentProduct->sku,
                    'product_thumbnail' => $this->parentProduct->product_thumbnail,
                ];
            }),
            
            // Inventory details
            'inventory' => $this->whenLoaded('inventory', function () {
                return [
                    'id' => $this->inventory->id,
                    'warehouse_id' => $this->inventory->warehouse_id,
                    'quantity' => $this->inventory->quantity,
                    'updated_at' => $this->inventory->updated_at?->toISOString(),
                ];
            }),
        ];
    }
}
