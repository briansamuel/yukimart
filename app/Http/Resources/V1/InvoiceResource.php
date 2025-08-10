<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
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
            'invoice_number' => $this->invoice_number,
            'invoice_type' => $this->invoice_type,
            'sales_channel' => $this->sales_channel,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'invoice_date' => $this->invoice_date?->format('Y-m-d'),
            'due_date' => $this->due_date?->format('Y-m-d'),
            
            // Customer information
            'customer' => $this->whenLoaded('customer', function () {
                return new CustomerResource($this->customer);
            }),
            'customer_id' => $this->customer_id,
            'customer_name' => $this->customer_name,
            
            // Branch shop information
            'branch_shop' => $this->whenLoaded('branchShop', function () {
                return new BranchShopResource($this->branchShop);
            }),
            'branch_shop_id' => $this->branch_shop_id,

            // User information
            'sold_by' => $this->whenLoaded('seller', function () {
                return [
                    'id' => $this->seller->id,
                    'name' => $this->seller->full_name ?? $this->seller->username,
                    'username' => $this->seller->username,
                    'email' => $this->seller->email,
                ];
            }),
            'created_by' => $this->whenLoaded('creator', function () {
                return [
                    'id' => $this->creator->id,
                    'name' => $this->creator->full_name ?? $this->creator->username,
                    'username' => $this->creator->username,
                    'email' => $this->creator->email,
                ];
            }),

            // Financial information
            'subtotal' => (float) $this->subtotal,
            'tax_rate' => (float) $this->tax_rate,
            'tax_amount' => (float) $this->tax_amount,
            'discount_rate' => (float) $this->discount_rate,
            'discount_amount' => (float) $this->discount_amount,
            'total_amount' => (float) $this->total_amount,
            'amount_paid' => (float) $this->amount_paid,
            'amount_due' => (float) ($this->total_amount - $this->amount_paid),
            
            // Additional information
            'notes' => $this->notes,
            'terms_conditions' => $this->terms_conditions,
            'payment_terms' => $this->payment_terms,
            'reference_number' => $this->reference_number,
            
            // Invoice items
            'items' => $this->whenLoaded('invoiceItems', function () {
                return InvoiceItemResource::collection($this->invoiceItems);
            }),
            'items_count' => $this->when($this->relationLoaded('invoiceItems'), function () {
                return $this->invoiceItems->count();
            }),

            // Products summary (name and quantity only)
            'products_summary' => $this->whenLoaded('invoiceItems', function () {
                return $this->invoiceItems->map(function ($item) {
                    return [
                        'name' => $item->product_name,
                        'quantity' => (int) $item->quantity,
                    ];
                });
            }),
            
            // Payments
            'payments' => $this->whenLoaded('payments', function () {
                return PaymentResource::collection($this->payments);
            }),

            // Payment methods summary
            'payment_methods' => $this->whenLoaded('payments', function () {
                $completedPayments = $this->payments->where('status', 'completed');

                if ($completedPayments->isEmpty()) {
                    return [];
                }

                // Group payments by method and sum amounts
                $methodSummary = $completedPayments->groupBy('payment_method')->map(function ($payments, $method) {
                    return [
                        'method' => $method,
                        'method_label' => $this->getPaymentMethodLabel($method),
                        'total_amount' => (float) $payments->sum('actual_amount'),
                        'payment_count' => $payments->count(),
                    ];
                })->values();

                return $methodSummary;
            }),
            
            // Payment information
            'payments' => $this->whenLoaded('payments', function () {
                return $this->payments->map(function ($payment) {
                    return [
                        'id' => $payment->id,
                        'payment_number' => $payment->payment_number,
                        'payment_type' => $payment->payment_type,
                        'payment_method' => $payment->payment_method,
                        'payment_method_label' => $payment->payment_method_display,
                        'amount' => (float) $payment->amount,
                        'actual_amount' => (float) $payment->actual_amount,
                        'status' => $payment->status,
                        'payment_date' => $payment->payment_date,
                        'description' => $payment->description,
                        'notes' => $payment->notes,
                        'created_by' => $payment->relationLoaded('creator') && $payment->creator ? [
                            'id' => $payment->creator->id,
                            'name' => $payment->creator->full_name ?? $payment->creator->username,
                            'username' => $payment->creator->username,
                            'email' => $payment->creator->email,
                        ] : null,
                        'created_at' => $payment->created_at,
                        'updated_at' => $payment->updated_at,
                    ];
                });
            }),

            // User information (full user data)
            'created_by' => $this->whenLoaded('creator', function () {
                return new UserResource($this->creator);
            }),
            'seller' => $this->whenLoaded('seller', function () {
                return new UserResource($this->seller);
            }),

            // Timestamps
            'sent_at' => $this->sent_at,
            'cancelled_at' => $this->cancelled_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Computed properties
            'is_overdue' => $this->when($this->due_date, function () {
                return $this->due_date->isPast() && $this->payment_status !== 'paid';
            }),
            'days_overdue' => $this->when($this->due_date && $this->due_date->isPast(), function () {
                return $this->due_date->diffInDays(now());
            }),
            'payment_percentage' => $this->total_amount > 0 ?
                round(($this->amount_paid / $this->total_amount) * 100, 2) : 0,
        ];
    }

    /**
     * Get payment method label in Vietnamese
     */
    private function getPaymentMethodLabel($method)
    {
        $labels = [
            'cash' => 'Tiền mặt',
            'card' => 'Thẻ',
            'transfer' => 'Chuyển khoản',
            'check' => 'Séc',
            'points' => 'Điểm thưởng',
            'other' => 'Khác',
        ];

        return $labels[$method] ?? ucfirst($method);
    }
}
