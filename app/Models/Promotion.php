<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\TenantScoped;
use App\Traits\UserTimeStamp;

class Promotion extends Model
{
    use HasFactory, SoftDeletes, TenantScoped, UserTimeStamp;

    protected $fillable = [
        'tenant_id',
        'promotion_code',
        'name',
        'description',
        'type',
        'discount_value',
        'max_discount_amount',
        'min_order_value',
        'min_quantity',
        'buy_quantity',
        'get_quantity',
        'applicable_product_ids',
        'applicable_category_ids',
        'start_date',
        'end_date',
        'usage_limit',
        'usage_count',
        'usage_limit_per_customer',
        'apply_to',
        'priority',
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
        'applicable_product_ids' => 'array',
        'applicable_category_ids' => 'array',
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
     * Check if promotion is active.
     */
    public function isActive()
    {
        return $this->status === 'active' 
            && $this->start_date <= now() 
            && $this->end_date >= now();
    }

    /**
     * Check if promotion has reached usage limit.
     */
    public function hasReachedLimit()
    {
        if ($this->usage_limit === null) {
            return false;
        }
        return $this->usage_count >= $this->usage_limit;
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
            'buy_x_get_y' => 'Mua X tặng Y',
            default => 'Không xác định',
        };
    }

    /**
     * Boot method to handle model events.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($promotion) {
            if (empty($promotion->promotion_code)) {
                $promotion->promotion_code = self::generatePromotionCode();
            }
        });
    }

    /**
     * Generate unique promotion code.
     */
    public static function generatePromotionCode()
    {
        $prefix = 'KM';
        $date = now()->format('Ymd');
        
        $lastPromotion = self::whereDate('created_at', now()->toDateString())
            ->orderBy('id', 'desc')
            ->first();
        
        $sequence = $lastPromotion ? (intval(substr($lastPromotion->promotion_code, -3)) + 1) : 1;
        
        return $prefix . $date . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }
}

