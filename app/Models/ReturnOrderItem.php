<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class ReturnOrderItem extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'line_total' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship with return order.
     */
    public function returnOrder()
    {
        return $this->belongsTo(ReturnOrder::class);
    }

    /**
     * Relationship with invoice item.
     */
    public function invoiceItem()
    {
        return $this->belongsTo(InvoiceItem::class);
    }

    /**
     * Relationship with product.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get condition display name.
     */
    protected function conditionDisplay(): Attribute
    {
        return new Attribute(
            get: function($value, $attributes) {
                $condition = $attributes['condition'] ?? 'new';
                return match($condition) {
                    'new' => 'Mới',
                    'used' => 'Đã sử dụng',
                    'damaged' => 'Hỏng',
                    'expired' => 'Hết hạn',
                    default => 'Không xác định',
                };
            }
        );
    }

    /**
     * Get formatted unit price.
     */
    protected function formattedUnitPrice(): Attribute
    {
        return new Attribute(
            get: fn($value, $attributes) => number_format($attributes['unit_price'], 0, ',', '.') . ' VND'
        );
    }

    /**
     * Get formatted line total.
     */
    protected function formattedLineTotal(): Attribute
    {
        return new Attribute(
            get: fn($value, $attributes) => number_format($attributes['line_total'], 0, ',', '.') . ' VND'
        );
    }

    /**
     * Boot method to handle model events.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            // Calculate line total
            $item->line_total = $item->quantity_returned * $item->unit_price;
        });

        static::updating(function ($item) {
            // Recalculate line total if quantity or price changed
            if ($item->isDirty(['quantity_returned', 'unit_price'])) {
                $item->line_total = $item->quantity_returned * $item->unit_price;
            }
        });

        static::saved(function ($item) {
            // Recalculate return order totals
            $item->returnOrder->calculateTotals();
        });

        static::deleted(function ($item) {
            // Recalculate return order totals
            $item->returnOrder->calculateTotals();
        });
    }
}
