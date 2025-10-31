<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TenantScoped;
use App\Traits\UserTimeStamp;

class ProductUnit extends Model
{
    use HasFactory, TenantScoped, UserTimeStamp;

    protected $fillable = [
        'product_id',
        'tenant_id',
        'unit_name',
        'sale_price',
        'is_direct_sale',
        'conversion_rate',
        'is_base_unit',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sale_price' => 'decimal:2',
        'conversion_rate' => 'decimal:4',
        'is_direct_sale' => 'boolean',
        'is_base_unit' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the product that owns the unit.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the tenant that owns the unit.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Scope to get only active units.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get only direct sale units.
     */
    public function scopeDirectSale($query)
    {
        return $query->where('is_direct_sale', true);
    }

    /**
     * Scope to get base unit.
     */
    public function scopeBaseUnit($query)
    {
        return $query->where('is_base_unit', true);
    }

    /**
     * Scope to order by sort order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Calculate price based on conversion rate.
     * 
     * @param float $basePrice Base unit price
     * @return float Calculated price for this unit
     */
    public function calculatePrice($basePrice)
    {
        return $basePrice * $this->conversion_rate;
    }

    /**
     * Convert quantity from this unit to base unit.
     * 
     * @param float $quantity Quantity in this unit
     * @return float Quantity in base unit
     */
    public function toBaseUnit($quantity)
    {
        return $quantity * $this->conversion_rate;
    }

    /**
     * Convert quantity from base unit to this unit.
     * 
     * @param float $baseQuantity Quantity in base unit
     * @return float Quantity in this unit
     */
    public function fromBaseUnit($baseQuantity)
    {
        return $this->conversion_rate > 0 ? $baseQuantity / $this->conversion_rate : 0;
    }
}

