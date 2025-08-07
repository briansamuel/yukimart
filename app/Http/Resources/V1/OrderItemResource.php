<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
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
            'order_id' => $this->order_id,
            
            // Product information
            'product_id' => $this->product_id,
            'product_name' => $this->product_name,
            'product_sku' => $this->product_sku,
            'product_barcode' => $this->product_barcode,
            'product_description' => $this->product_description,
            'product' => $this->whenLoaded('product', function () {
                return new ProductResource($this->product);
            }),
            
            // Quantity and pricing
            'quantity' => (int) $this->quantity,
            'unit' => $this->unit,
            'unit_price' => (float) $this->unit_price,
            'cost_price' => (float) $this->cost_price,
            
            // Discounts and taxes
            'discount_rate' => (float) $this->discount_rate,
            'discount_amount' => (float) $this->discount_amount,
            'tax_rate' => (float) $this->tax_rate,
            'tax_amount' => (float) $this->tax_amount,
            'line_total' => (float) $this->line_total,
            
            // Additional information
            'notes' => $this->notes,
            'sort_order' => (int) $this->sort_order,
            
            // Marketplace information
            'marketplace_item_id' => $this->marketplace_item_id,
            'marketplace_sku' => $this->marketplace_sku,
            
            // Timestamps
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
