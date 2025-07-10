<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class ReturnOrder extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'return_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship with invoice.
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Relationship with customer.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Relationship with branch shop.
     */
    public function branchShop()
    {
        return $this->belongsTo(BranchShop::class, 'branch_shop_id');
    }

    /**
     * Relationship with approver.
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Relationship with creator.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relationship with updater.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Relationship with receiver.
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /**
     * Relationship with return order items.
     */
    public function returnOrderItems()
    {
        return $this->hasMany(ReturnOrderItem::class)->orderBy('sort_order');
    }

    /**
     * Relationship with payments.
     */
    public function payments()
    {
        return $this->morphMany(Payment::class, 'reference');
    }

    /**
     * Get status badge HTML.
     */
    protected function statusBadge(): Attribute
    {
        return new Attribute(
            get: function($value, $attributes) {
                $status = $attributes['status'] ?? 'pending';
                return match($status) {
                    'pending' => '<span class="badge badge-warning">Chờ duyệt</span>',
                    'approved' => '<span class="badge badge-info">Đã duyệt</span>',
                    'rejected' => '<span class="badge badge-danger">Từ chối</span>',
                    'completed' => '<span class="badge badge-success">Hoàn thành</span>',
                    default => '<span class="badge badge-secondary">Không xác định</span>',
                };
            }
        );
    }

    /**
     * Get reason display name.
     */
    protected function reasonDisplay(): Attribute
    {
        return new Attribute(
            get: function($value, $attributes) {
                $reason = $attributes['reason'] ?? 'customer_request';
                return match($reason) {
                    'defective' => 'Hàng lỗi',
                    'wrong_item' => 'Giao sai hàng',
                    'customer_request' => 'Khách hàng yêu cầu',
                    'damaged' => 'Hàng bị hỏng',
                    'expired' => 'Hết hạn',
                    'other' => 'Khác',
                    default => 'Không xác định',
                };
            }
        );
    }

    /**
     * Get refund method display name.
     */
    protected function refundMethodDisplay(): Attribute
    {
        return new Attribute(
            get: function($value, $attributes) {
                $method = $attributes['refund_method'] ?? null;
                return match($method) {
                    'cash' => 'Tiền mặt',
                    'card' => 'Thẻ',
                    'transfer' => 'Chuyển khoản',
                    'store_credit' => 'Tín dụng cửa hàng',
                    'exchange' => 'Đổi hàng',
                    'points' => 'Điểm thưởng',
                    'other' => 'Khác',
                    default => 'Chưa xác định',
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
     * Generate unique return number.
     */
    public static function generateReturnNumber()
    {
        $prefix = 'TH';
        $date = date('Ymd');
        $lastReturn = self::where('return_number', 'like', $prefix . $date . '%')
                         ->orderBy('return_number', 'desc')
                         ->first();

        if ($lastReturn) {
            $lastNumber = intval(substr($lastReturn->return_number, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix . $date . $newNumber;
    }

    /**
     * Calculate return totals.
     */
    public function calculateTotals()
    {
        $subtotal = $this->returnOrderItems->sum('line_total');
        $taxAmount = $subtotal * ($this->tax_rate / 100);
        $totalAmount = $subtotal + $taxAmount;

        $this->update([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
        ]);

        return $this;
    }

    /**
     * Boot method to handle model events.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($returnOrder) {
            if (empty($returnOrder->return_number)) {
                $returnOrder->return_number = self::generateReturnNumber();
            }
            if (empty($returnOrder->created_by)) {
                $returnOrder->created_by = auth()->id();
            }
        });

        static::updating(function ($returnOrder) {
            $returnOrder->updated_by = auth()->id();
        });
    }
}
