<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
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
            'customer_code' => $this->customer_code,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'facebook' => $this->facebook,
            'address' => $this->address,
            'area' => $this->area,
            'customer_type' => $this->customer_type,
            'customer_group' => $this->customer_group,
            'tax_code' => $this->tax_code,
            'status' => $this->status,
            'notes' => $this->notes,
            'birthday' => $this->birthday?->format('Y-m-d'),
            'points' => (int) $this->points,
            'avatar' => $this->avatar ? asset('storage/' . $this->avatar) : null,
            
            // Branch shop information
            'branch_shop_id' => $this->branch_shop_id,
            'branch_shop' => $this->whenLoaded('branchShop', function () {
                return new BranchShopResource($this->branchShop);
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
            'avg_order_value' => $this->when($this->relationLoaded('orders'), function () {
                return (float) $this->orders->avg('final_amount');
            }),
            'last_order_date' => $this->when($this->relationLoaded('orders'), function () {
                return $this->orders->max('created_at');
            }),
            
            // User tracking
            'created_by' => $this->whenLoaded('creator', function () {
                return new UserResource($this->creator);
            }),
            'updated_by' => $this->whenLoaded('updater', function () {
                return new UserResource($this->updater);
            }),
            
            // Timestamps
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
