<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class BranchShopResource extends JsonResource
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
            'name' => $this->name,
            'code' => $this->code,
            'address' => $this->address,
            'phone' => $this->phone,
            'email' => $this->email,
            'manager_name' => $this->manager_name,
            'status' => $this->status,
            'description' => $this->description,
            'opening_hours' => $this->opening_hours,
            'coordinates' => $this->coordinates,
            
            // Pivot data when loaded through user relationship
            'role_in_shop' => $this->when($this->pivot, function () {
                return $this->pivot->role_in_shop;
            }),
            'is_primary' => $this->when($this->pivot, function () {
                return (bool) $this->pivot->is_primary;
            }),
            'is_active' => $this->when($this->pivot, function () {
                return (bool) $this->pivot->is_active;
            }),
            'start_date' => $this->when($this->pivot, function () {
                return $this->pivot->start_date;
            }),
            'end_date' => $this->when($this->pivot, function () {
                return $this->pivot->end_date;
            }),
            
            // Timestamps
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
