<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderListResource extends JsonResource
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
            // Essential order information
            'id' => $this->id,
            'order_number' => $this->order_number,
            'order_type' => $this->order_type,
            'order_date' => $this->order_date,
            'delivery_date' => $this->delivery_date,
            
            // Status information (critical for listing)
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'delivery_status' => $this->delivery_status,
            'priority' => $this->priority,
            
            // Customer information (essential)
            'customer_id' => $this->customer_id,
            'customer_name' => $this->customer_name,
            'customer_phone' => $this->customer_phone,
            'customer_email' => $this->customer_email,
            
            // Customer details (when loaded)
            'customer' => $this->whenLoaded('customer', function () {
                return [
                    'id' => $this->customer->id,
                    'customer_name' => $this->customer->customer_name,
                    'email' => $this->customer->email,
                    'phone' => $this->customer->phone,
                ];
            }),
            
            // Branch shop information
            'branch_shop_id' => $this->branch_shop_id,
            'branch_shop_name' => $this->whenLoaded('branchShop', function () {
                return $this->branchShop?->name;
            }),
            
            // Financial information (essential for listing)
            'subtotal' => (float) $this->subtotal,
            'discount_amount' => (float) $this->discount_amount,
            'tax_amount' => (float) $this->tax_amount,
            'final_amount' => (float) $this->final_amount,
            
            // Payment information
            'payment_method' => $this->payment_method,
            
            // Order items summary (when loaded)
            'items_count' => $this->whenLoaded('orderItems', function () {
                return $this->orderItems->count();
            }),
            'items_summary' => $this->whenLoaded('orderItems', function () {
                return $this->orderItems->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product_name' => $item->product_name,
                        'product_sku' => $item->product_sku,
                        'quantity' => (int) $item->quantity,
                        'unit_price' => (float) $item->unit_price,
                        'line_total' => (float) $item->line_total,
                    ];
                });
            }),
            
            // User information (minimal)
            'created_by' => $this->whenLoaded('creator', function () {
                return [
                    'id' => $this->creator->id,
                    'name' => $this->creator->full_name ?? $this->creator->username,
                ];
            }),
            'sold_by' => $this->whenLoaded('seller', function () {
                return [
                    'id' => $this->seller->id,
                    'name' => $this->seller->full_name ?? $this->seller->username,
                ];
            }),
            
            // Delivery information (essential)
            'delivery_address' => $this->delivery_address,
            'delivery_notes' => $this->delivery_notes,
            
            // Reference information
            'reference_number' => $this->reference_number,
            'notes' => $this->notes,
            
            // Computed properties (essential only)
            'total_items' => $this->whenLoaded('orderItems', function () {
                return $this->orderItems->sum('quantity');
            }),
            
            'payment_percentage' => $this->final_amount > 0 ?
                round(($this->paid_amount ?? 0) / $this->final_amount * 100, 2) : 0,
            
            'days_since_order' => $this->order_date ? 
                now()->diffInDays($this->order_date) : null,
            
            'is_overdue' => $this->delivery_date && $this->delivery_date < now() && 
                !in_array($this->delivery_status, ['delivered', 'cancelled']),
            
            // Status labels (for UI)
            'status_label' => $this->getStatusLabel(),
            'payment_status_label' => $this->getPaymentStatusLabel(),
            'delivery_status_label' => $this->getDeliveryStatusLabel(),
            'priority_label' => $this->getPriorityLabel(),
            
            // Timestamps
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /**
     * Get status label for display
     */
    private function getStatusLabel()
    {
        $labels = [
            'draft' => 'Nháp',
            'pending' => 'Chờ xử lý',
            'processing' => 'Đang xử lý',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    /**
     * Get payment status label for display
     */
    private function getPaymentStatusLabel()
    {
        $labels = [
            'unpaid' => 'Chưa thanh toán',
            'partial' => 'Thanh toán một phần',
            'paid' => 'Đã thanh toán',
            'refunded' => 'Đã hoàn tiền',
        ];

        return $labels[$this->payment_status] ?? $this->payment_status;
    }

    /**
     * Get delivery status label for display
     */
    private function getDeliveryStatusLabel()
    {
        $labels = [
            'pending' => 'Chờ giao hàng',
            'processing' => 'Đang chuẩn bị',
            'shipped' => 'Đã gửi hàng',
            'delivered' => 'Đã giao hàng',
            'failed' => 'Giao hàng thất bại',
        ];

        return $labels[$this->delivery_status] ?? $this->delivery_status;
    }

    /**
     * Get priority label for display
     */
    private function getPriorityLabel()
    {
        $labels = [
            'low' => 'Thấp',
            'normal' => 'Bình thường',
            'high' => 'Cao',
            'urgent' => 'Khẩn cấp',
        ];

        return $labels[$this->priority] ?? $this->priority;
    }

    /**
     * Get additional data for the resource.
     */
    public function with($request)
    {
        return [
            'meta' => [
                'resource_type' => 'order_list',
                'optimized' => true,
            ]
        ];
    }
}
