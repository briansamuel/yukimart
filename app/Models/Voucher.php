<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\TenantScoped;
use App\Traits\UserTimeStamp;

class Voucher extends Model
{
    use HasFactory, SoftDeletes, TenantScoped, UserTimeStamp;

    protected $fillable = [
        'tenant_id',
        'voucher_code',
        'name',
        'description',
        'type',
        'discount_value',
        'max_discount_amount',
        'min_order_value',
        'start_date',
        'end_date',
        'total_quantity',
        'used_quantity',
        'usage_limit_per_customer',
        'apply_to',
        'applicable_customer_ids',
        'applicable_customer_groups',
        'is_public',
        'status',
        'branch_shop_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'min_order_value' => 'decimal:2',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'applicable_customer_ids' => 'array',
        'applicable_customer_groups' => 'array',
        'is_public' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship with branch shop.
     */
    public function branchShop()
    {
        return $this->belongsTo(BranchShop::class, 'branch_shop_id');
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
     * Relationship with voucher usage.
     */
    public function usages()
    {
        return $this->hasMany(VoucherUsage::class);
    }

    /**
     * Check if voucher is active.
     */
    public function isActive()
    {
        return $this->status === 'active' 
            && $this->start_date <= now() 
            && $this->end_date >= now()
            && !$this->isUsedUp();
    }

    /**
     * Check if voucher is used up.
     */
    public function isUsedUp()
    {
        if ($this->total_quantity === null) {
            return false;
        }
        return $this->used_quantity >= $this->total_quantity;
    }

    /**
     * Get remaining quantity.
     */
    public function getRemainingQuantity()
    {
        if ($this->total_quantity === null) {
            return null; // Unlimited
        }
        return max(0, $this->total_quantity - $this->used_quantity);
    }

    /**
     * Get status badge class.
     */
    public function getStatusBadgeClass()
    {
        return match($this->status) {
            'active' => 'badge-success',
            'inactive' => 'badge-secondary',
            'expired' => 'badge-danger',
            'used_up' => 'badge-warning',
            default => 'badge-secondary',
        };
    }

    /**
     * Get status label.
     */
    public function getStatusLabel()
    {
        return match($this->status) {
            'active' => 'Đang hoạt động',
            'inactive' => 'Không hoạt động',
            'expired' => 'Hết hạn',
            'used_up' => 'Đã hết',
            default => 'Không xác định',
        };
    }

    /**
     * Get type label.
     */
    public function getTypeLabel()
    {
        return match($this->type) {
            'percentage' => 'Giảm giá %',
            'fixed_amount' => 'Giảm giá cố định',
            'freeship' => 'Miễn phí vận chuyển',
            default => 'Không xác định',
        };
    }

    /**
     * Boot method to handle model events.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($voucher) {
            if (empty($voucher->voucher_code)) {
                $voucher->voucher_code = self::generateVoucherCode();
            }
        });
    }

    /**
     * Generate unique voucher code.
     */
    public static function generateVoucherCode()
    {
        $prefix = 'VC';
        $date = now()->format('Ymd');
        
        $lastVoucher = self::whereDate('created_at', now()->toDateString())
            ->orderBy('id', 'desc')
            ->first();
        
        $sequence = $lastVoucher ? (intval(substr($lastVoucher->voucher_code, -3)) + 1) : 1;
        
        return $prefix . $date . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }
}

