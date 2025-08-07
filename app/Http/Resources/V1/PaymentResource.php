<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
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
            'payment_code' => $this->payment_code,
            'payment_type' => $this->payment_type,
            'payment_method' => $this->payment_method,
            'amount' => (float) $this->amount,
            'payment_date' => $this->payment_date,
            'description' => $this->description,
            'notes' => $this->notes,
            'status' => $this->status,
            
            // Reference information
            'reference_type' => $this->reference_type,
            'reference_id' => $this->reference_id,
            'reference_number' => $this->reference_number,
            
            // Bank account information
            'bank_account' => $this->whenLoaded('bankAccount', function () {
                return new BankAccountResource($this->bankAccount);
            }),
            
            // User information
            'created_by' => $this->whenLoaded('creator', function () {
                return new UserResource($this->creator);
            }),
            
            // Timestamps
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
