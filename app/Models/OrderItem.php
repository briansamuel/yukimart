<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class OrderItem extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'discount' => 'decimal:2',
        'total_price' => 'decimal:2',
        'marketplace_item_data' => 'array',
        'marketplace_price' => 'decimal:2',
        'marketplace_discount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship with order.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Relationship with product.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get marketplace product link if exists
     */
    public function marketplaceLink()
    {
        if (!$this->marketplace_item_id || !$this->order->marketplace_platform) {
            return null;
        }

        return $this->product?->marketplaceLinks()
            ->where('platform', $this->order->marketplace_platform)
            ->where('marketplace_item_id', $this->marketplace_item_id)
            ->first();
    }

    /**
     * Check if this item is from marketplace
     */
    public function isMarketplaceItem(): bool
    {
        return !empty($this->marketplace_item_id);
    }

    /**
     * Get marketplace item URL
     */
    public function getMarketplaceItemUrlAttribute(): ?string
    {
        $link = $this->marketplaceLink();
        return $link?->marketplace_url;
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
     * Get formatted discount.
     */
    protected function formattedDiscount(): Attribute
    {
        return new Attribute(
            get: fn($value, $attributes) => number_format($attributes['discount'], 0, ',', '.') . ' VND'
        );
    }

    /**
     * Get formatted total price.
     */
    protected function formattedTotalPrice(): Attribute
    {
        return new Attribute(
            get: fn($value, $attributes) => number_format($attributes['total_price'], 0, ',', '.') . ' VND'
        );
    }

    /**
     * Get subtotal before discount.
     */
    protected function subtotal(): Attribute
    {
        return new Attribute(
            get: fn($value, $attributes) => $attributes['quantity'] * $attributes['unit_price']
        );
    }

    /**
     * Get formatted subtotal.
     */
    protected function formattedSubtotal(): Attribute
    {
        return new Attribute(
            get: fn($value, $attributes) => number_format($this->subtotal, 0, ',', '.') . ' VND'
        );
    }

    /**
     * Calculate total price based on quantity, unit price, and discount.
     */
    public function calculateTotalPrice()
    {
        $subtotal = $this->quantity * $this->unit_price;
        $this->total_price = $subtotal - $this->discount;
        return $this;
    }

    /**
     * Boot method to automatically calculate total price.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($orderItem) {
            $orderItem->calculateTotalPrice();
        });

        static::saved(function ($orderItem) {
            // Recalculate order totals when order item is saved
            if ($orderItem->order) {
                $orderItem->order->calculateTotals();
            }
        });

        static::deleted(function ($orderItem) {
            // Recalculate order totals when order item is deleted
            if ($orderItem->order) {
                $orderItem->order->calculateTotals();
            }
        });
    }
}
