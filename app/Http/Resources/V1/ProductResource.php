<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'product_name' => $this->product_name,
            'product_sku' => $this->sku,
            'product_barcode' => $this->barcode,
            'product_description' => $this->product_description,
            'product_status' => $this->product_status,
            
            // Pricing
            'cost_price' => (float) $this->cost_price,
            'sale_price' => (float) $this->sale_price,
            'min_price' => (float) $this->min_price,
            'max_price' => (float) $this->max_price,
            
            // Physical properties
            'weight' => (int) $this->weight,
            'length' => (float) $this->length,
            'width' => (float) $this->width,
            'height' => (float) $this->height,
            'volume' => (float) $this->volume,
            
            // Category and brand
            'category_id' => $this->category_id,
            'brand_id' => $this->brand_id,
            'supplier_id' => $this->supplier_id,
            
            // Inventory
            'reorder_point' => (int) $this->reorder_point,
            'current_stock' => $this->when($this->relationLoaded('inventory'), function () {
                return $this->inventory ? (int) $this->inventory->quantity : 0;
            }),
            
            // Features
            'product_feature' => (bool) $this->product_feature,
            'has_variants' => (bool) $this->has_variants,
            'variants_count' => (int) $this->variants_count,
            'variant_attributes' => $this->variant_attributes,
            
            // Points and loyalty
            'points' => (int) $this->points,
            
            // Images
            'product_image' => $this->product_thumbnail ? $this->product_thumbnail : null,
            'product_gallery' => $this->when($this->product_gallery, function () {
                return collect($this->product_gallery)->map(function ($image) {
                    return asset('storage/' . $image);
                });
            }),
            
            // SEO
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'meta_keywords' => $this->meta_keywords,
            
            // Relationships
            'category' => $this->whenLoaded('category'),
            'brand' => $this->whenLoaded('brand'),
            'supplier' => $this->whenLoaded('supplier'),
            'variants' => $this->whenLoaded('variants', function () {
                return ProductResource::collection($this->variants);
            }),
            
            // Timestamps
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
