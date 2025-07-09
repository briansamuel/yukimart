<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Log;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_code',
        'name',
        'phone',
        'email',
        'facebook',
        'address',
        'area',
        'customer_type',
        'customer_group',
        'tax_code',
        'status',
        'notes',
        'birthday',
        'points',
        'created_by',
        'updated_by'
    ];

    protected $hidden = [
        'deleted_by'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'birthday' => 'date',
        'points' => 'integer',
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
     * Scope for business customers.
     */
    public function scopeBusiness($query)
    {
        return $query->where('customer_type', 'business');
    }

    /**
     * Scope for individual customers.
     */
    public function scopeIndividual($query)
    {
        return $query->where('customer_type', 'individual');
    }

    /**
     * Scope for customers with orders.
     */
    public function scopeWithOrders($query)
    {
        return $query->has('orders');
    }

    /**
     * Generate unique customer code.
     */
    public static function generateCustomerCode()
    {
        $prefix = 'KH';

        // Use a more reliable method to get the next number
        $maxNumber = self::where('customer_code', 'like', $prefix . '%')
                        ->selectRaw('MAX(CAST(SUBSTRING(customer_code, 3) AS UNSIGNED)) as max_number')
                        ->value('max_number');

        $newNumber = ($maxNumber ?? 0) + 1;

        // Ensure uniqueness by checking if code already exists
        do {
            $code = $prefix . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
            $exists = self::where('customer_code', $code)->exists();
            if ($exists) {
                $newNumber++;
            }
        } while ($exists);

        return $code;
    }

    /**
     * Get customer statistics with optimized queries.
     */
    public function getStats()
    {
        try {
            // Use optimized queries instead of loading all orders
            $orderStats = $this->orders()
                ->selectRaw('
                    COUNT(*) as total_orders,
                    COUNT(CASE WHEN status = "completed" THEN 1 END) as completed_orders,
                    COALESCE(SUM(CASE WHEN status = "completed" THEN final_amount END), 0) as total_sales,
                    COALESCE(SUM(CASE WHEN status = "returned" THEN final_amount END), 0) as total_returns,
                    COALESCE(SUM(CASE WHEN payment_status IN ("unpaid", "partial") AND status NOT IN ("cancelled", "returned") THEN (final_amount - COALESCE(amount_paid, 0)) END), 0) as total_debt,
                    COALESCE(AVG(CASE WHEN status = "completed" THEN final_amount END), 0) as avg_order_value
                ')
                ->first();

            // Get last order date efficiently
            $lastOrderDate = $this->orders()
                ->latest()
                ->value('created_at');

            // Calculate derived values from optimized query
            $totalSales = $orderStats->total_sales ?? 0;
            $totalReturns = $orderStats->total_returns ?? 0;
            $totalDebt = $orderStats->total_debt ?? 0;
            $netSales = $totalSales - $totalReturns;
            $totalPoints = $this->points ?? 0;

            return [
                'total_orders' => $orderStats->total_orders ?? 0,
                'completed_orders' => $orderStats->completed_orders ?? 0,
                'total_spent' => $totalSales,
                'total_returns' => $totalReturns,
                'net_sales' => $netSales,
                'total_debt' => max(0, $totalDebt),
                'total_points' => $totalPoints,
                'purchase_count' => $orderStats->completed_orders ?? 0,
                'average_order_value' => $orderStats->avg_order_value ?? 0,
                'last_order_date' => $lastOrderDate,
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
