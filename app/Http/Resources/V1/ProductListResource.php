<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductListResource extends JsonResource
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
            // Essential product information
            'id' => $this->id,
            'product_name' => $this->product_name,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'product_status' => $this->product_status,
            
            // Pricing (essential for listing)
            'sale_price' => (float) $this->sale_price,
            'cost_price' => (float) $this->cost_price,
            'min_price' => (float) $this->min_price,
            'max_price' => (float) $this->max_price,
            
            // Stock information (critical for sales)
            'current_stock' => $this->whenLoaded('inventory', function () {
                return $this->inventory ? (int) $this->inventory->quantity : 0;
            }),
            'reorder_point' => (int) $this->reorder_point,
            'stock_status' => $this->when($this->relationLoaded('inventory'), function () {
                $stock = $this->inventory ? $this->inventory->quantity : 0;
                $reorderPoint = $this->reorder_point ?? 0;
                
                if ($stock <= 0) {
                    return 'out_of_stock';
                } elseif ($stock <= $reorderPoint) {
                    return 'low_stock';
                } else {
                    return 'in_stock';
                }
            }),
            
            // Category information (minimal)
            'category_id' => $this->category_id,
            'category_name' => $this->whenLoaded('category', function () {
                return $this->category?->name;
            }),
            
            // Brand information (minimal)
            'brand_id' => $this->brand_id,
            'brand_name' => $this->whenLoaded('brand', function () {
                return $this->brand?->name;
            }),
            
            // Features (essential only)
            'product_feature' => (bool) $this->product_feature,
            'has_variants' => (bool) $this->has_variants,
            'variants_count' => (int) $this->variants_count,
            
            // Image (thumbnail only for listing)
            'product_thumbnail' => $this->product_thumbnail ? 
                asset('storage/' . $this->product_thumbnail) : null,
            
            // Physical properties (basic)
            'weight' => (int) $this->weight,
            'volume' => (float) $this->volume,
            
            // Points and loyalty
            'points' => (int) $this->points,
            
            // Relationships (when loaded)
            'category' => $this->whenLoaded('category', function () {
                return [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                    'slug' => $this->category->slug,
                ];
            }),
            
            'brand' => $this->whenLoaded('brand', function () {
                return [
                    'id' => $this->brand->id,
                    'name' => $this->brand->name,
                ];
            }),
            
            'supplier' => $this->whenLoaded('supplier', function () {
                return [
                    'id' => $this->supplier->id,
                    'name' => $this->supplier->name,
                ];
            }),
            
            // Variants summary (when loaded)
            'variants_summary' => $this->whenLoaded('variants', function () {
                return $this->variants->map(function ($variant) {
                    return [
                        'id' => $variant->id,
                        'sku' => $variant->sku,
                        'sale_price' => (float) $variant->sale_price,
                        'stock' => $variant->inventory ? (int) $variant->inventory->quantity : 0,
                    ];
                });
            }),
            
            // Computed properties (essential only)
            'profit_margin' => $this->sale_price > 0 && $this->cost_price > 0 ?
                round((($this->sale_price - $this->cost_price) / $this->sale_price) * 100, 2) : 0,
            
            'stock_value' => $this->when($this->relationLoaded('inventory'), function () {
                $stock = $this->inventory ? $this->inventory->quantity : 0;
                return (float) ($stock * $this->cost_price);
            }),
            
            // Timestamps
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /**
     * Get additional data for the resource.
     */
    public function with($request)
    {
        return [
            'meta' => [
                'resource_type' => 'product_list',
                'optimized' => true,
            ]
        ];
    }
}
