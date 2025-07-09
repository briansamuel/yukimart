<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Log;

class Customer extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship with orders.
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get customer's full contact info.
     */
    protected function fullContact(): Attribute
    {
        return new Attribute(
            get: function($value, $attributes) {
                $contact = [];
                if (!empty($attributes['phone'])) {
                    $contact[] = $attributes['phone'];
                }
                if (!empty($attributes['email'])) {
                    $contact[] = $attributes['email'];
                }
                return implode(' | ', $contact);
            }
        );
    }

    /**
     * Get customer status badge HTML.
     */
    protected function statusBadge(): Attribute
    {
        return new Attribute(
            get: function($value, $attributes) {
                $status = $attributes['status'] ?? 'inactive';
                $badgeClass = $status === 'active' ? 'badge-light-success' : 'badge-light-danger';
                $statusText = $status === 'active' ? 'Hoạt động' : 'Không hoạt động';
                return "<span class=\"badge {$badgeClass}\">{$statusText}</span>";
            }
        );
    }

    /**
     * Get customer type display text.
     */
    protected function customerTypeDisplay(): Attribute
    {
        return new Attribute(
            get: function($value, $attributes) {
                $type = $attributes['customer_type'] ?? 'individual';
                return $type === 'business' ? 'Doanh nghiệp' : 'Cá nhân';
            }
        );
    }

    /**
     * Scope for search by name, phone, or email.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        });
    }

    /**
     * Scope for active customers.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Get customer statistics.
     */
    public function getStats()
    {
        try {
            // Kiểm tra xem bảng orders có tồn tại không
            if (!$this->relationLoaded('orders')) {
                // Load orders relationship nếu chưa có
                $this->load('orders');
            }

            // Tính tổng đơn hàng đã hoàn thành (chỉ tính completed orders)
            $completedOrders = $this->orders()->where('status', 'completed');
            $totalSales = $completedOrders->sum('final_amount') ?? 0;

            // Tính tổng trả hàng (orders với status = 'returned')
            $returnedOrders = $this->orders()->where('status', 'returned');
            $totalReturns = $returnedOrders->sum('final_amount') ?? 0;

            // Tính nợ hiện tại - chỉ tính các đơn chưa thanh toán hoặc thanh toán một phần
            $unpaidOrders = $this->orders()
                ->whereIn('payment_status', ['unpaid', 'partial'])
                ->whereNotIn('status', ['cancelled', 'returned']) // Không tính đơn đã hủy hoặc trả hàng
                ->get();

            $totalDebt = 0;
            foreach ($unpaidOrders as $order) {
                $remaining = $order->final_amount - ($order->amount_paid ?? 0);
                $totalDebt += max(0, $remaining); // Chỉ tính nợ dương
            }

            // Tính điểm tích lũy
            $totalPoints = $this->points ?? 0;

            // Số lần mua = số đơn hàng hoàn thành (không tính trả hàng)
            // Chỉ tính đơn có status = 'completed' và không phải là đơn trả hàng
            $purchaseCount = $this->orders()
                ->where('status', 'completed')
                ->count() ?? 0;

            // Tổng bán trừ trả hàng = Tổng đơn hoàn thành - Tổng đơn trả hàng
            $netSales = $totalSales - $totalReturns;

            return [
                'total_orders' => $this->orders()->count() ?? 0,
                'completed_orders' => $completedOrders->count() ?? 0,
                'total_spent' => $totalSales,
                'total_returns' => $totalReturns,
                'net_sales' => $netSales,
                'total_debt' => max(0, $totalDebt), // Không cho phép nợ âm
                'total_points' => $totalPoints,
                'purchase_count' => $purchaseCount,
                'average_order_value' => $completedOrders->avg('final_amount') ?? 0,
                'last_order_date' => $this->orders()->latest()->first()?->created_at,
            ];
        } catch (\Exception $e) {
            Log::error('Customer::getStats - Error calculating stats', [
                'customer_id' => $this->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Return default values on error
            return [
                'total_orders' => 0,
                'completed_orders' => 0,
                'total_spent' => 0,
                'total_returns' => 0,
                'net_sales' => 0,
                'total_debt' => 0,
                'total_points' => $this->points ?? 0,
                'purchase_count' => 0,
                'average_order_value' => 0,
                'last_order_date' => null,
            ];
        }
    }

    /**
     * Get customer order history with details.
     */
    public function getOrderHistory()
    {
        try {
            return $this->orders()
                ->with(['seller', 'creator']) // Load seller và creator relationships
                ->orderBy('created_at', 'desc')
                ->limit(20) // Giới hạn số lượng để tránh quá tải
                ->get()
                ->map(function($order) {
                    // Ưu tiên seller, nếu không có thì dùng creator
                    $sellerName = 'N/A';
                    if ($order->seller) {
                        $sellerName = $order->seller->full_name;
                    } elseif ($order->creator) {
                        $sellerName = $order->creator->full_name;
                    }

                    return [
                        'order_code' => $order->order_code ?? 'N/A',
                        'date' => $order->created_at ? $order->created_at->format('d/m/Y H:i') : 'N/A',
                        'seller' => $sellerName,
                        'total' => $order->final_amount ?? 0,
                        'status' => $order->status ?? 'unknown',
                        'payment_status' => $order->payment_status ?? 'unknown',
                    ];
                })->toArray();
        } catch (\Exception $e) {
            Log::error('Customer::getOrderHistory - Error getting order history', [
                'customer_id' => $this->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return []; // Return empty array on error
        }
    }
}
