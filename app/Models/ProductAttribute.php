<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProductAttribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'description',
        'is_required',
        'is_variation',
        'is_visible',
        'sort_order',
        'options',
        'status'
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_variation' => 'boolean',
        'is_visible' => 'boolean',
        'sort_order' => 'integer',
        'options' => 'array',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($attribute) {
            if (empty($attribute->slug)) {
                $attribute->slug = Str::slug($attribute->name);
            }
        });

        static::updating(function ($attribute) {
            if ($attribute->isDirty('name') && empty($attribute->slug)) {
                $attribute->slug = Str::slug($attribute->name);
            }
        });
    }

    /**
     * Get the attribute values for this attribute
     */
    public function values()
    {
        return $this->hasMany(ProductAttributeValue::class, 'attribute_id')
                    ->where('status', 'active')
                    ->orderBy('sort_order');
    }

    /**
     * Get all attribute values (including inactive)
     */
    public function allValues()
    {
        return $this->hasMany(ProductAttributeValue::class, 'attribute_id')
                    ->orderBy('sort_order');
    }

    /**
     * Get variant attributes that use this attribute
     */
    public function variantAttributes()
    {
        return $this->hasMany(ProductVariantAttribute::class, 'attribute_id');
    }

    /**
     * Scope for active attributes
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for variation attributes
     */
    public function scopeForVariation($query)
    {
        return $query->where('is_variation', true);
    }

    /**
     * Scope for visible attributes
     */
    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    /**
     * Get attributes ordered by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Get active variation attributes for dropdown
     */
    public static function getVariationOptions()
    {
        return static::active()
                     ->forVariation()
                     ->ordered()
                     ->with('values')
                     ->get();
    }

    /**
     * Check if attribute is color type
     */
    public function isColorType()
    {
        return $this->type === 'color';
    }

    /**
     * Check if attribute is select type
     */
    public function isSelectType()
    {
        return $this->type === 'select';
    }

    /**
     * Get formatted name for display
     */
    public function getDisplayNameAttribute()
    {
        return $this->name . ($this->is_required ? ' *' : '');
    }

    /**
     * Get the total number of values
     */
    public function getValuesCountAttribute()
    {
        return $this->values()->count();
    }
}
