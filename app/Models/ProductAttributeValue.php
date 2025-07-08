<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProductAttributeValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'attribute_id',
        'value',
        'slug',
        'color_code',
        'image',
        'description',
        'sort_order',
        'price_adjustment',
        'status'
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'price_adjustment' => 'decimal:2',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($value) {
            if (empty($value->slug)) {
                $value->slug = Str::slug($value->value);
            }
        });

        static::updating(function ($value) {
            if ($value->isDirty('value') && empty($value->slug)) {
                $value->slug = Str::slug($value->value);
            }
        });
    }

    /**
     * Get the attribute that owns this value
     */
    public function attribute()
    {
        return $this->belongsTo(ProductAttribute::class, 'attribute_id');
    }

    /**
     * Get variant attributes that use this value
     */
    public function variantAttributes()
    {
        return $this->hasMany(ProductVariantAttribute::class, 'attribute_value_id');
    }

    /**
     * Get variants that use this attribute value
     */
    public function variants()
    {
        return $this->belongsToMany(ProductVariant::class, 'product_variant_attributes', 'attribute_value_id', 'variant_id')
                    ->withPivot('attribute_id')
                    ->withTimestamps();
    }

    /**
     * Scope for active values
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for ordered values
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('value');
    }

    /**
     * Get formatted display value
     */
    public function getDisplayValueAttribute()
    {
        $display = $this->value;
        
        if ($this->price_adjustment != 0) {
            $adjustment = $this->price_adjustment > 0 ? '+' : '';
            $display .= ' (' . $adjustment . number_format($this->price_adjustment, 0, ',', '.') . ' VND)';
        }
        
        return $display;
    }

    /**
     * Get color display for color attributes
     */
    public function getColorDisplayAttribute()
    {
        if ($this->attribute && $this->attribute->isColorType() && $this->color_code) {
            return [
                'name' => $this->value,
                'code' => $this->color_code,
                'style' => "background-color: {$this->color_code};"
            ];
        }
        
        return null;
    }

    /**
     * Check if this value has a color code
     */
    public function hasColor()
    {
        return !empty($this->color_code);
    }

    /**
     * Check if this value has an image
     */
    public function hasImage()
    {
        return !empty($this->image);
    }

    /**
     * Get the image URL
     */
    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return asset($this->image);
        }
        
        return null;
    }

    /**
     * Get formatted price adjustment
     */
    public function getFormattedPriceAdjustmentAttribute()
    {
        if ($this->price_adjustment == 0) {
            return null;
        }
        
        $prefix = $this->price_adjustment > 0 ? '+' : '';
        return $prefix . number_format($this->price_adjustment, 0, ',', '.') . ' VND';
    }

    /**
     * Get values by attribute slug
     */
    public static function getByAttributeSlug($attributeSlug)
    {
        return static::whereHas('attribute', function ($query) use ($attributeSlug) {
            $query->where('slug', $attributeSlug)->where('status', 'active');
        })->active()->ordered()->get();
    }
}
