<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Traits\UserTimeStamp;
use App\Traits\HasNotifications;
use Carbon\Carbon;

class Order extends Model
{
    use HasFactory, UserTimeStamp, HasNotifications;

    protected $guarded = [];

    /**
     * Control notifications for this model instance
     */
    protected $notificationsDisabled = false;

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'payment_date' => 'datetime',
        'due_date' => 'date',
        'total_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'other_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'marketplace_data' => 'array',
        'marketplace_created_at' => 'datetime',
        'marketplace_shipping_fee' => 'decimal:2',
        'is_marketplace_order' => 'boolean',
    ];

    /**
     * The attributes that should be filled with default values.
     */
    protected $attributes = [
        'amount_paid' => 0,
        'discount_amount' => 0,
        'other_amount' => 0,
        'total_quantity' => 0,
    ];

    /**
     * Relationship with customer.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get customer name with walk-in customer support
     */
    public function getCustomerNameAttribute()
    {
        if ($this->customer_id == 0 || $this->customer_id === null) {
            return 'Khách lẻ';
        }

        return $this->customer ? $this->customer->name : 'Khách hàng không xác định';
    }

    /**
     * Get customer display name for UI
     */
    public function getCustomerDisplayAttribute()
    {
        if ($this->customer_id == 0 || $this->customer_id === null) {
            return 'Khách lẻ';
        }

        if ($this->customer) {
            return $this->customer->name . ($this->customer->phone ? ' - ' . $this->customer->phone : '');
        }

        return 'Khách hàng không xác định';
    }

    /**
     * Relationship with branch shop.
     */
    public function branchShop()
    {
        return $this->belongsTo(BranchShop::class, 'branch_shop_id');
    }

    /**
     * Relationship with creator (user who created the order).
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relationship with seller (user who sold the order).
     */
    public function seller()
    {
        return $this->belongsTo(User::class, 'sold_by');
    }

    /**
     * Relationship with order items.
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Relationship with invoices.
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get order edit URL.
     */
    protected function orderEdit(): Attribute
    {
        return new Attribute(
            get: fn($value, $attributes) => route('admin.order.edit', ['order_id' => $attributes['id']]),
        );
    }

    /**
     * Get formatted created date.
     */
    protected function createdAtFormatted(): Attribute
    {
        Carbon::setLocale('vi');
        return new Attribute(
            get: fn($value, $attributes) => Carbon::parse($attributes['created_at'])->diffForHumans()
        );
    }

    /**
     * Get status badge HTML.
     */
    protected function statusBadge(): Attribute
    {
        return new Attribute(
            get: function($value, $attributes) {
                $status = $attributes['status'] ?? 'processing';
                return match($status) {
                    'processing' => '<span class="badge badge-light-warning">
                                        <i class="ki-duotone ki-time fs-7 me-1">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        Đang xử lý
                                    </span>',
                    'completed' => '<span class="badge badge-light-success">
                                        <i class="ki-duotone ki-check-circle fs-7 me-1">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        Hoàn thành
                                    </span>',
                    'cancelled' => '<span class="badge badge-light-danger">
                                        <i class="ki-duotone ki-cross-circle fs-7 me-1">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        Đã hủy
                                    </span>',
                    'failed' => '<span class="badge badge-light-dark">
                                        <i class="ki-duotone ki-cross fs-7 me-1">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        Thất bại
                                    </span>',
                    default => '<span class="badge badge-light-secondary">
                                        <i class="ki-duotone ki-question fs-7 me-1">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        Không xác định
                                    </span>',
                };
            }
        );
    }

    /**
     * Get delivery status badge HTML.
     */
    protected function deliveryStatusBadge(): Attribute
    {
        return new Attribute(
            get: function($value, $attributes) {
                $status = $attributes['delivery_status'] ?? 'pending';
                return match($status) {
                    'pending' => '<span class="badge badge-light-secondary">
                                    <i class="ki-duotone ki-time fs-7 me-1">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                    Chờ xử lý
                                </span>',
                    'picking' => '<span class="badge badge-light-info">
                                    <i class="ki-duotone ki-package fs-7 me-1">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                    </i>
                                    Đang chuẩn bị
                                </span>',
                    'delivering' => '<span class="badge badge-light-primary">
                                        <i class="ki-duotone ki-delivery fs-7 me-1">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                            <span class="path3"></span>
                                            <span class="path4"></span>
                                            <span class="path5"></span>
                                        </i>
                                        Đang giao
                                    </span>',
                    'delivered' => '<span class="badge badge-light-success">
                                        <i class="ki-duotone ki-check-circle fs-7 me-1">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        Đã giao
                                    </span>',
                    'returning' => '<span class="badge badge-light-warning">
                                        <i class="ki-duotone ki-undo fs-7 me-1">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        Đang trả
                                    </span>',
                    'returned' => '<span class="badge badge-light-danger">
                                        <i class="ki-duotone ki-cross-circle fs-7 me-1">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        Đã trả
                                    </span>',
                    default => '<span class="badge badge-light-secondary">
                                        <i class="ki-duotone ki-question fs-7 me-1">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        Không xác định
                                    </span>',
                };
            }
        );
    }

    /**
     * Get channel display name.
     */
    protected function channelDisplay(): Attribute
    {
        return new Attribute(
            get: function($value, $attributes) {
                $channel = $attributes['channel'] ?? 'direct';
                return match($channel) {
                    'direct' => 'Trực tiếp',
                    'online' => 'Trực tuyến',
                    'pos' => 'POS',
                    'other' => 'Khác',
                    default => 'Không xác định',
                };
            }
        );
    }

    /**
     * Get payment method display name.
     */
    protected function paymentMethodDisplay(): Attribute
    {
        return new Attribute(
            get: function($value, $attributes) {
                $method = $attributes['payment_method'] ?? 'cash';
                return match($method) {
                    'cash' => 'Tiền mặt',
                    'card' => 'Thẻ tín dụng/ghi nợ',
                    'transfer' => 'Chuyển khoản',
                    'cod' => 'Thanh toán khi nhận hàng',
                    'e_wallet' => 'Ví điện tử',
                    'installment' => 'Trả góp',
                    'credit' => 'Công nợ',
                    'voucher' => 'Phiếu quà tặng',
                    'points' => 'Điểm tích lũy',
                    'mixed' => 'Thanh toán hỗn hợp',
                    default => 'Không xác định',
                };
            }
        );
    }

    /**
     * Get payment status badge HTML.
     */
    protected function paymentStatusBadge(): Attribute
    {
        return new Attribute(
            get: function($value, $attributes) {
                $status = $attributes['payment_status'] ?? 'unpaid';
                return match($status) {
                    'unpaid' => '<span class="badge badge-light-danger">
                                    <i class="ki-duotone ki-cross-circle fs-7 me-1">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                    Chưa thanh toán
                                </span>',
                    'partial' => '<span class="badge badge-light-warning">
                                    <i class="ki-duotone ki-time fs-7 me-1">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                    Thanh toán một phần
                                </span>',
                    'paid' => '<span class="badge badge-light-success">
                                    <i class="ki-duotone ki-check-circle fs-7 me-1">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                    Đã thanh toán
                                </span>',
                    'overpaid' => '<span class="badge badge-light-info">
                                    <i class="ki-duotone ki-arrow-up-circle fs-7 me-1">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                    Thanh toán thừa
                                </span>',
                    'refunded' => '<span class="badge badge-light-dark">
                                    <i class="ki-duotone ki-undo fs-7 me-1">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                    Đã hoàn tiền
                                </span>',
                    default => '<span class="badge badge-light-secondary">
                                    <i class="ki-duotone ki-question fs-7 me-1">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                    Không xác định
                                </span>',
                };
            }
        );
    }

    /**
     * Get payment status display name.
     */
    protected function paymentStatusDisplay(): Attribute
    {
        return new Attribute(
            get: function($value, $attributes) {
                $status = $attributes['payment_status'] ?? 'unpaid';
                return match($status) {
                    'unpaid' => 'Chưa thanh toán',
                    'partial' => 'Thanh toán một phần',
                    'paid' => 'Đã thanh toán',
                    'overpaid' => 'Thanh toán thừa',
                    'refunded' => 'Đã hoàn tiền',
                    default => 'Không xác định',
                };
            }
        );
    }

    /**
     * Get formatted total amount.
     */
    protected function formattedTotalAmount(): Attribute
    {
        return new Attribute(
            get: fn($value, $attributes) => number_format($attributes['total_amount'], 0, ',', '.') . ' VND'
        );
    }

    /**
     * Get formatted final amount.
     */
    protected function formattedFinalAmount(): Attribute
    {
        return new Attribute(
            get: fn($value, $attributes) => number_format($attributes['final_amount'], 0, ',', '.') . ' VND'
        );
    }

    /**
     * Get remaining amount to pay.
     */
    protected function remainingAmount(): Attribute
    {
        return new Attribute(
            get: fn($value, $attributes) => $attributes['final_amount'] - $attributes['amount_paid']
        );
    }

    /**
     * Get formatted remaining amount.
     */
    protected function formattedRemainingAmount(): Attribute
    {
        return new Attribute(
            get: fn($value, $attributes) => number_format($this->remaining_amount, 0, ',', '.') . ' VND'
        );
    }

    /**
     * Check if order is fully paid.
     */
    protected function isFullyPaid(): Attribute
    {
        return new Attribute(
            get: fn($value, $attributes) => $attributes['amount_paid'] >= $attributes['final_amount']
        );
    }

    /**
     * Scope for active orders.
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['processing', 'completed']);
    }

    /**
     * Scope for completed orders.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for cancelled orders.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Scope for orders by channel.
     */
    public function scopeByChannel($query, $channel)
    {
        return $query->where('channel', $channel);
    }

    /**
     * Scope for orders by branch shop.
     */
    public function scopeByBranchShop($query, $branchShopId)
    {
        return $query->where('branch_shop_id', $branchShopId);
    }

    /**
     * Scope for orders by date range.
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope for orders by payment method.
     */
    public function scopeByPaymentMethod($query, $paymentMethod)
    {
        return $query->where('payment_method', $paymentMethod);
    }

    /**
     * Scope for orders by payment status.
     */
    public function scopeByPaymentStatus($query, $paymentStatus)
    {
        return $query->where('payment_status', $paymentStatus);
    }

    /**
     * Scope for paid orders.
     */
    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    /**
     * Scope for unpaid orders.
     */
    public function scopeUnpaid($query)
    {
        return $query->where('payment_status', 'unpaid');
    }

    /**
     * Scope for partially paid orders.
     */
    public function scopePartiallyPaid($query)
    {
        return $query->where('payment_status', 'partial');
    }

    /**
     * Scope for overdue orders.
     */
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
                    ->whereIn('payment_status', ['unpaid', 'partial']);
    }

    /**
     * Scope for search by order code or customer name.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('order_code', 'like', "%{$search}%")
              ->orWhereHas('customer', function($customerQuery) use ($search) {
                  $customerQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('phone', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
              });
        });
    }

    /**
     * Scope for marketplace orders
     */
    public function scopeMarketplace($query)
    {
        return $query->where('is_marketplace_order', true);
    }

    /**
     * Scope for specific marketplace platform
     */
    public function scopeFromPlatform($query, string $platform)
    {
        return $query->where('marketplace_platform', $platform);
    }

    /**
     * Scope for Shopee orders
     */
    public function scopeShopee($query)
    {
        return $query->fromPlatform('shopee');
    }

    /**
     * Check if this is a marketplace order
     */
    public function isMarketplaceOrder(): bool
    {
        return $this->is_marketplace_order;
    }

    /**
     * Get marketplace platform display name
     */
    public function getMarketplacePlatformNameAttribute(): ?string
    {
        if (!$this->marketplace_platform) {
            return null;
        }

        return match($this->marketplace_platform) {
            'shopee' => 'Shopee',
            'tiki' => 'Tiki',
            'lazada' => 'Lazada',
            'sendo' => 'Sendo',
            'tiktok' => 'TikTok Shop',
            default => ucfirst($this->marketplace_platform)
        };
    }

    /**
     * Get marketplace order URL
     */
    public function getMarketplaceOrderUrlAttribute(): ?string
    {
        if (!$this->marketplace_platform || !$this->marketplace_order_id) {
            return null;
        }

        return match($this->marketplace_platform) {
            'shopee' => "https://seller.shopee.vn/portal/sale/order/{$this->marketplace_order_id}",
            'tiki' => "https://seller.tiki.vn/order/{$this->marketplace_order_id}",
            'lazada' => "https://sellercenter.lazada.vn/order/detail/{$this->marketplace_order_id}",
            default => null
        };
    }

    /**
     * Generate unique order code.
     */
    public static function generateOrderCode()
    {
        return \App\Services\PrefixGeneratorService::generateOrderCode();
    }

    /**
     * Calculate order totals.
     */
    public function calculateTotals()
    {

        $totalQuantity = $this->orderItems->sum('quantity');
        $totalAmount = $this->orderItems->sum('total_price');
        $finalAmount = $totalAmount - $this->discount_amount + $this->other_amount;

        // Store current notification state
        $notificationsWereDisabled = !$this->notificationsEnabled();

        // Disable notifications for this update to avoid triggering updated event
        if (!$notificationsWereDisabled) {
            $this->disableNotifications();
        }

        $this->update([
            'total_quantity' => $totalQuantity,
            'total_amount' => $totalAmount,
            'final_amount' => $finalAmount,
        ]);

        // Update payment status based on amount paid
        $this->updatePaymentStatus();

        // Restore notification state
        if (!$notificationsWereDisabled) {
            $this->enableNotifications();
        }

        return $this;
    }

    /**
     * Update payment status based on amount paid.
     */
    public function updatePaymentStatus()
    {
        $amountPaid = $this->amount_paid;
        $finalAmount = $this->final_amount;

        if ($amountPaid <= 0) {
            $status = 'unpaid';
        } elseif ($amountPaid < $finalAmount) {
            $status = 'partial';
        } elseif ($amountPaid == $finalAmount) {
            $status = 'paid';
        } else {
            $status = 'overpaid';
        }

        // Store current notification state
        $notificationsWereDisabled = !$this->notificationsEnabled();

        // Disable notifications for this update to avoid triggering updated event
        if (!$notificationsWereDisabled) {
            $this->disableNotifications();
        }

        $this->update(['payment_status' => $status]);

        // Restore notification state
        if (!$notificationsWereDisabled) {
            $this->enableNotifications();
        }

        return $this;
    }

    /**
     * Record a payment for this order.
     */
    public function recordPayment($amount, $method = null, $reference = null, $notes = null)
    {
        $this->amount_paid += $amount;

        if ($method) {
            $this->payment_method = $method;
        }

        if ($reference) {
            $this->payment_reference = $reference;
        }

        if ($notes) {
            $this->payment_notes = $notes;
        }

        $this->payment_date = now();

        $this->save();
        $this->updatePaymentStatus();

        return $this;
    }

    /**
     * Check if order is overdue.
     */
    public function isOverdue()
    {
        return $this->due_date &&
               $this->due_date < now()->toDateString() &&
               in_array($this->payment_status, ['unpaid', 'partial']);
    }

    /**
     * Get days until due date.
     */
    public function getDaysUntilDue()
    {
        if (!$this->due_date) {
            return null;
        }

        return now()->diffInDays($this->due_date, false);
    }

    /**
     * Mark order as paid.
     */
    public function markAsPaid($method = null, $reference = null, $notes = null)
    {
        $this->amount_paid = $this->final_amount;
        $this->payment_status = 'paid';
        $this->payment_date = now();

        if ($method) {
            $this->payment_method = $method;
        }

        if ($reference) {
            $this->payment_reference = $reference;
        }

        if ($notes) {
            $this->payment_notes = $notes;
        }

        $this->save();

        return $this;
    }

    /**
     * Get order statistics.
     */
    public static function getStatistics($filters = [])
    {
        $query = self::query();

        // Apply filters
        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }
        if (isset($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }

        return [
            'total_orders' => $query->count(),
            'completed_orders' => (clone $query)->where('status', 'completed')->count(),
            'cancelled_orders' => (clone $query)->where('status', 'cancelled')->count(),
            'processing_orders' => (clone $query)->where('status', 'processing')->count(),
            'total_revenue' => (clone $query)->where('status', 'completed')->sum('final_amount'),
            'average_order_value' => (clone $query)->where('status', 'completed')->avg('final_amount'),

            // Payment statistics
            'paid_orders' => (clone $query)->where('payment_status', 'paid')->count(),
            'unpaid_orders' => (clone $query)->where('payment_status', 'unpaid')->count(),
            'partial_paid_orders' => (clone $query)->where('payment_status', 'partial')->count(),
            'overdue_orders' => (clone $query)->overdue()->count(),
            'total_paid_amount' => (clone $query)->sum('amount_paid'),
            'total_outstanding_amount' => (clone $query)->whereIn('payment_status', ['unpaid', 'partial'])
                                                        ->selectRaw('SUM(final_amount - amount_paid) as outstanding')
                                                        ->value('outstanding') ?? 0,

            // Payment method breakdown
            'payment_methods' => (clone $query)->selectRaw('payment_method, COUNT(*) as count, SUM(final_amount) as total_amount')
                                               ->groupBy('payment_method')
                                               ->get()
                                               ->keyBy('payment_method')
                                               ->toArray(),
        ];
    }

    /**
     * Check if should send inventory notification for this order.
     */
    public function shouldSendInventoryNotification()
    {
        // Only send inventory notifications for confirmed/processing/completed orders
        return in_array($this->status, ['confirmed', 'processing', 'completed']);
    }
}
