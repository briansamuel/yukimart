<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'discount_rate' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'line_total' => 'decimal:2',
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
     * Relationship with product.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
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
     * Get formatted discount amount.
     */
    protected function formattedDiscountAmount(): Attribute
    {
        return new Attribute(
            get: fn($value, $attributes) => number_format($attributes['discount_amount'], 0, ',', '.') . ' VND'
        );
    }

    /**
     * Get formatted tax amount.
     */
    protected function formattedTaxAmount(): Attribute
    {
        return new Attribute(
            get: fn($value, $attributes) => number_format($attributes['tax_amount'], 0, ',', '.') . ' VND'
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
     * Get subtotal before discount and tax.
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
     * Calculate line total based on quantity, unit price, discount, and tax.
     */
    public function calculateLineTotal()
    {
        $subtotal = $this->quantity * $this->unit_price;
        $afterDiscount = $subtotal - $this->discount_amount;
        $this->line_total = $afterDiscount + $this->tax_amount;
        return $this;
    }

    /**
     * Calculate discount amount based on discount rate.
     */
    public function calculateDiscountAmount()
    {
        if ($this->discount_rate > 0) {
            $subtotal = $this->quantity * $this->unit_price;
            $this->discount_amount = $subtotal * ($this->discount_rate / 100);
        }
        return $this;
    }

    /**
     * Calculate tax amount based on tax rate.
     */
    public function calculateTaxAmount()
    {
        if ($this->tax_rate > 0) {
            $subtotal = $this->quantity * $this->unit_price;
            $afterDiscount = $subtotal - $this->discount_amount;
            $this->tax_amount = $afterDiscount * ($this->tax_rate / 100);
        }
        return $this;
    }

    /**
     * Boot method to automatically calculate amounts.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($invoiceItem) {
            $invoiceItem->calculateDiscountAmount();
            $invoiceItem->calculateTaxAmount();
            $invoiceItem->calculateLineTotal();
        });

        static::saved(function ($invoiceItem) {
            // Recalculate invoice totals when invoice item is saved
            if ($invoiceItem->invoice) {
                $invoiceItem->invoice->calculateTotals();
            }
        });

        static::deleted(function ($invoiceItem) {
            // Recalculate invoice totals when invoice item is deleted
            if ($invoiceItem->invoice) {
                $invoiceItem->invoice->calculateTotals();
            }
        });
    }

    /**
     * Create invoice item from product.
     */
    public static function createFromProduct(Product $product, $quantity = 1, $customPrice = null)
    {
        return new self([
            'product_id' => $product->id,
            'product_name' => $product->product_name,
            'product_sku' => $product->sku,
            'product_description' => $product->product_description,
            'quantity' => $quantity,
            'unit_price' => $customPrice ?? $product->product_price,
            'unit' => 'cái', // Default unit
        ]);
    }

    /**
     * Scope for items with discount.
     */
    public function scopeWithDiscount($query)
    {
        return $query->where('discount_amount', '>', 0);
    }

    /**
     * Scope for items with tax.
     */
    public function scopeWithTax($query)
    {
        return $query->where('tax_amount', '>', 0);
    }

    /**
     * Scope for items by product.
     */
    public function scopeByProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }
}
