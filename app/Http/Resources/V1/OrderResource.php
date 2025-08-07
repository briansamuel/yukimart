<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'order_number' => $this->order_number,
            'order_type' => $this->order_type,
            'status' => $this->status,
            'order_date' => $this->order_date,
            'delivery_date' => $this->delivery_date,
            'priority' => $this->priority,
            
            // Customer information
            'customer_id' => $this->customer_id,
            'customer_name' => $this->customer_name,
            'customer_phone' => $this->customer_phone,
            'customer_email' => $this->customer_email,
            'customer' => $this->whenLoaded('customer', function () {
                return new CustomerResource($this->customer);
            }),
            
            // Branch shop information
            'branch_shop_id' => $this->branch_shop_id,
            'branch_shop' => $this->whenLoaded('branchShop', function () {
                return new BranchShopResource($this->branchShop);
            }),
            
            // Financial information
            'subtotal' => (float) $this->subtotal,
            'tax_rate' => (float) $this->tax_rate,
            'tax_amount' => (float) $this->tax_amount,
            'discount_rate' => (float) $this->discount_rate,
            'discount_amount' => (float) $this->discount_amount,
            'shipping_fee' => (float) $this->shipping_fee,
            'other_amount' => (float) $this->other_amount,
            'final_amount' => (float) $this->final_amount,
            
            // Payment information
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'paid_amount' => (float) $this->paid_amount,
            'remaining_amount' => (float) $this->remaining_amount,
            'paid_at' => $this->paid_at,
            
            // Delivery information
            'delivery_address' => $this->delivery_address,
            'delivery_notes' => $this->delivery_notes,
            'delivery_fee' => (float) $this->delivery_fee,
            'delivery_status' => $this->delivery_status,
            'delivered_at' => $this->delivered_at,
            
            // Order items
            'items' => $this->whenLoaded('orderItems', function () {
                return OrderItemResource::collection($this->orderItems);
            }),
            'items_count' => $this->when($this->relationLoaded('orderItems'), function () {
                return $this->orderItems->count();
            }),
            
            // Additional information
            'notes' => $this->notes,
            'internal_notes' => $this->internal_notes,
            'source' => $this->source,
            'reference_number' => $this->reference_number,
            
            // Loyalty points
            'points_earned' => (int) $this->points_earned,
            'points_used' => (int) $this->points_used,
            
            // User tracking
            'created_by' => $this->whenLoaded('creator', function () {
                return new UserResource($this->creator);
            }),
            'updated_by' => $this->whenLoaded('updater', function () {
                return new UserResource($this->updater);
            }),
            'sold_by' => $this->whenLoaded('seller', function () {
                return new UserResource($this->seller);
            }),
            
            // Timestamps
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
