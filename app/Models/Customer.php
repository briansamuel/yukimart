<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

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
        'branch_shop_id',
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
     * Relationship with invoices.
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Relationship with return orders.
     */
    public function returnOrders()
    {
        return $this->hasMany(ReturnOrder::class);
    }

    /**
     * Relationship with point transactions.
     */
    public function pointTransactions()
    {
        return $this->hasMany(CustomerPointTransaction::class);
    }

    /**
     * Relationship with branch shop (where customer was created)
     */
    public function branchShop()
    {
        return $this->belongsTo(BranchShop::class);
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
            // Get all invoices for this customer (excluding cancelled and draft)
            $invoices = $this->invoices()
                ->whereNotIn('status', ['cancelled', 'draft'])
                ->get();

            // Calculate statistics from the collection
            $totalInvoices = $invoices->count();
            $completedInvoices = $invoices->whereIn('status', ['completed', 'paid'])->count();
            $totalSales = $invoices->whereIn('status', ['completed', 'paid'])->sum('total_amount');

            // Calculate debt: sum of unpaid amounts (total_amount - paid_amount for unpaid/partial invoices)
            $totalDebt = $invoices->filter(function($invoice) {
                $paymentStatus = $invoice->payment_status; // Uses accessor
                return in_array($paymentStatus, ['unpaid', 'partial']);
            })->sum(function($invoice) {
                return $invoice->total_amount - $invoice->paid_amount; // Uses accessor
            });

            // Get return order statistics (if table exists)
            $totalReturns = 0;
            if (Schema::hasTable('return_orders')) {
                $totalReturns = $this->returnOrders()
                    ->where('status', 'completed')
                    ->sum('total_amount') ?? 0;
            }

            // Get point statistics from point_transactions
            $pointStats = $this->pointTransactions()
                ->selectRaw('
                    COALESCE(SUM(points), 0) as current_points_balance,
                    COALESCE(SUM(CASE WHEN points > 0 THEN points END), 0) as total_points_earned
                ')
                ->first();

            // Get last invoice date efficiently
            $lastInvoiceDate = $this->invoices()
                ->latest()
                ->value('created_at');

            // Calculate derived values
            $netSales = $totalSales - $totalReturns; // Tổng bán trừ trả hàng
            $currentPoints = $pointStats->current_points_balance ?? 0; // Điểm hiện tại (số dư)
            $totalPointsEarned = $pointStats->total_points_earned ?? 0; // Tổng điểm tích lũy (chỉ điểm cộng)
            $avgInvoiceValue = $completedInvoices > 0 ? $totalSales / $completedInvoices : 0;

            return [
                'total_invoices' => $totalInvoices,
                'completed_invoices' => $completedInvoices, // Số lần mua (hóa đơn thành công)
                'total_spent' => $totalSales,
                'total_returns' => $totalReturns,
                'net_sales' => $netSales, // Tổng bán trừ trả hàng
                'total_debt' => max(0, $totalDebt), // Nợ: Số tiền hóa đơn chưa thanh toán
                'current_points' => $currentPoints, // Điểm: Số điểm tích lũy hiện tại (số dư)
                'total_points_earned' => $totalPointsEarned, // Tổng điểm: Tổng số điểm tích lũy (không bao gồm điểm bị trừ)
                'purchase_count' => $completedInvoices, // Số lần mua
                'average_invoice_value' => $avgInvoiceValue,
                'last_invoice_date' => $lastInvoiceDate,
            ];
        } catch (\Exception $e) {
            Log::error('Customer::getStats - Error calculating stats', [
                'customer_id' => $this->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Return default values on error
            return [
                'total_invoices' => 0,
                'completed_invoices' => 0,
                'total_spent' => 0,
                'total_returns' => 0,
                'net_sales' => 0,
                'total_debt' => 0,
                'total_points' => $this->points ?? 0,
                'purchase_count' => 0,
                'average_invoice_value' => 0,
                'last_invoice_date' => null,
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
