<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceItemResource extends JsonResource
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
            'invoice_id' => $this->invoice_id,
            'product_id' => $this->product_id,
            
            // Product information (stored for historical purposes)
            'product_name' => $this->product_name,
            'product_sku' => $this->product_sku,
            'product_description' => $this->product_description,
            
            // Product relationship (current product data)
            'product' => $this->whenLoaded('product', function () {
                return new ProductResource($this->product);
            }),
            
            // Quantity and pricing
            'quantity' => (int) $this->quantity,
            'unit' => $this->unit,
            'unit_price' => (float) $this->unit_price,
            'discount_rate' => (float) $this->discount_rate,
            'discount_amount' => (float) $this->discount_amount,
            'tax_rate' => (float) $this->tax_rate,
            'tax_amount' => (float) $this->tax_amount,
            'line_total' => (float) $this->line_total,
            
            // Additional information
            'notes' => $this->notes,
            'sort_order' => (int) $this->sort_order,
            
            // Computed properties
            'subtotal' => (float) ($this->quantity * $this->unit_price),
            'total_discount' => (float) $this->discount_amount,
            'total_tax' => (float) $this->tax_amount,
            'net_amount' => (float) ($this->line_total - $this->tax_amount),
            
            // Timestamps
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
