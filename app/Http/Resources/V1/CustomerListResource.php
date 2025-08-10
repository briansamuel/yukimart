<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomerListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * Lightweight version for customer list API - optimized for performance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            // Core information
            'id' => $this->id,
            'customer_code' => $this->customer_code,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'area' => $this->area,
            'customer_type' => $this->customer_type,
            'customer_group' => $this->customer_group,
            'status' => $this->status,
            'points' => (int) $this->points,
            'birthday' => $this->birthday?->format('Y-m-d'),
            
            // Branch shop information (minimal)
            'branch_shop_id' => $this->branch_shop_id,
            'branch_shop_name' => $this->whenLoaded('branchShop', function () {
                return $this->branchShop?->name;
            }),
            
            // Statistics (when loaded)
            'orders_count' => $this->when($this->relationLoaded('orders'), function () {
                return $this->orders->count();
            }),
            'invoices_count' => $this->when($this->relationLoaded('invoices'), function () {
                return $this->invoices->count();
            }),
            'total_spent' => $this->when($this->relationLoaded('orders'), function () {
                return (float) $this->orders->sum('final_amount');
            }),
            
            // Timestamps
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
