<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * Lightweight version for invoice list API - optimized for performance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            // Core information
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'invoice_type' => $this->invoice_type,
            'sales_channel' => $this->sales_channel,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'invoice_date' => $this->invoice_date?->format('Y-m-d'),
            'due_date' => $this->due_date?->format('Y-m-d'),
            
            // Customer information (minimal)
            'customer_id' => $this->customer_id,
            'customer_name' => $this->customer_name,
            
            // Branch shop information (minimal)
            'branch_shop_id' => $this->branch_shop_id,
            'branch_shop_name' => $this->whenLoaded('branchShop', function () {
                return $this->branchShop?->name;
            }),
            
            // Financial information (essential only)
            'subtotal' => (float) $this->subtotal,
            'total_amount' => (float) $this->total_amount,
            'amount_paid' => (float) $this->amount_paid,
            'amount_due' => (float) ($this->total_amount - $this->amount_paid),
            
            // Items with detailed information
            'items' => $this->whenLoaded('invoiceItems', function () {
                return $this->invoiceItems->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product_name' => $item->product_name,
                        'product_sku' => $item->product_sku,
                        'product_image' => $item->product && $item->product->product_thumbnail
                            ? $item->product->product_thumbnail
                            : null,
                        'quantity' => (int) $item->quantity,
                        'unit_price' => (float) $item->unit_price,
                    ];
                });
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
            
            // Computed properties (essential only)
            'payment_percentage' => $this->total_amount > 0 ?
                round(($this->amount_paid / $this->total_amount) * 100, 2) : 0,
            'is_overdue' => $this->when($this->due_date, function () {
                return $this->due_date->isPast() && $this->payment_status !== 'paid';
            }),
            
            // Timestamps
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /**
     * Get payment method label
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

        return $labels[$method] ?? $method;
    }
}
