<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class BankAccountResource extends JsonResource
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
            'account_name' => $this->account_name,
            'account_number' => $this->account_number,
            'bank_name' => $this->bank_name,
            'bank_branch' => $this->bank_branch,
            'account_type' => $this->account_type,
            'currency' => $this->currency,
            'is_active' => (bool) $this->is_active,
            'is_default' => (bool) $this->is_default,
            'description' => $this->description,
            
            // Balance information (if available)
            'current_balance' => $this->when(isset($this->current_balance), function () {
                return (float) $this->current_balance;
            }),
            
            // Timestamps
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
