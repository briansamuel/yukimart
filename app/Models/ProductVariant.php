<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_product_id',
        'variant_name',
        'sku',
        'barcode',
        'cost_price',
        'sale_price',
        'regular_price',
        'image',
        'images',
        'weight',
        'dimensions',
        'points',
        'reorder_point',
        'is_default',
        'is_active',
        'sort_order',
        'meta_data'
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'regular_price' => 'decimal:2',
        'images' => 'array',
        'weight' => 'integer',
        'points' => 'integer',
        'reorder_point' => 'integer',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'meta_data' => 'array',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($variant) {
            if (empty($variant->sku)) {
                $variant->sku = static::generateUniqueSku($variant->parent_product_id);
            }
        });

        static::created(function ($variant) {
            // Update parent product variants count
            $variant->updateParentVariantsCount();
            $variant->updateParentPriceRange();
        });

        static::updated(function ($variant) {
            $variant->updateParentPriceRange();
        });

        static::deleted(function ($variant) {
            $variant->updateParentVariantsCount();
            $variant->updateParentPriceRange();
        });
    }

    /**
     * Get the parent product
     */
    public function parentProduct()
    {
        return $this->belongsTo(Product::class, 'parent_product_id');
    }

    /**
     * Get the variant attributes
     */
    public function variantAttributes()
    {
        return $this->hasMany(ProductVariantAttribute::class, 'variant_id');
    }

    /**
     * Get the attributes with their values
     */
    public function attributes()
    {
        return $this->belongsToMany(ProductAttribute::class, 'product_variant_attributes', 'variant_id', 'attribute_id')
                    ->withPivot('attribute_value_id')
                    ->withTimestamps();
    }

    /**
     * Get the attribute values
     */
    public function attributeValues()
    {
        return $this->belongsToMany(ProductAttributeValue::class, 'product_variant_attributes', 'variant_id', 'attribute_value_id')
                    ->withPivot('attribute_id')
                    ->withTimestamps();
    }

    /**
     * Get inventory for this variant
     */
    public function inventories()
    {
        return $this->hasMany(Inventory::class, 'variant_id');
    }

    /**
     * Get inventory transactions for this variant
     */
    public function inventoryTransactions()
    {
        return $this->hasMany(InventoryTransaction::class, 'variant_id');
    }

    /**
     * Scope for active variants
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for default variant
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope for ordered variants
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('variant_name');
    }

    /**
     * Generate unique SKU for variant
     */
    public static function generateUniqueSku($parentProductId)
    {
        $parentProduct = Product::find($parentProductId);
        $baseSku = $parentProduct ? $parentProduct->sku : 'VAR';

        $counter = 1;
        do {
            $sku = $baseSku . '-V' . str_pad($counter, 3, '0', STR_PAD_LEFT);
            $counter++;
        } while (static::where('sku', $sku)->exists());

        return $sku;
    }

    /**
     * Update parent product variants count
     */
    public function updateParentVariantsCount()
    {
        if ($this->parentProduct) {
            $count = static::where('parent_product_id', $this->parent_product_id)->count();
            $this->parentProduct->update([
                'variants_count' => $count,
                'has_variants' => $count > 0
            ]);
        }
    }

    /**
     * Update parent product price range
     */
    public function updateParentPriceRange()
    {
        if ($this->parentProduct) {
            $variants = static::where('parent_product_id', $this->parent_product_id)
                             ->where('is_active', true)
                             ->get();

            if ($variants->count() > 0) {
                $minPrice = $variants->min('sale_price');
                $maxPrice = $variants->max('sale_price');

                $this->parentProduct->update([
                    'min_price' => $minPrice,
                    'max_price' => $maxPrice
                ]);
            }
        }
    }

    /**
     * Get formatted variant name with attributes
     */
    public function getFormattedNameAttribute()
    {
        $attributeValues = $this->attributeValues()->with('attribute')->get();

        if ($attributeValues->count() > 0) {
            $parts = [];
            foreach ($attributeValues as $value) {
                $parts[] = $value->value;
            }
            return $this->parentProduct->product_name . ' - ' . implode(' - ', $parts);
        }

        return $this->variant_name;
    }

    /**
     * Get variant display price
     */
    public function getDisplayPriceAttribute()
    {
        if ($this->regular_price && $this->regular_price > $this->sale_price) {
            return [
                'sale_price' => number_format($this->sale_price, 0, ',', '.'),
                'regular_price' => number_format($this->regular_price, 0, ',', '.'),
                'discount_percent' => round((($this->regular_price - $this->sale_price) / $this->regular_price) * 100)
            ];
        }

        return [
            'sale_price' => number_format($this->sale_price, 0, ',', '.'),
            'regular_price' => null,
            'discount_percent' => 0
        ];
    }

    /**
     * Get variant image URL
     */
    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return asset($this->image);
        }

        // Fallback to parent product image
        if ($this->parentProduct && $this->parentProduct->product_thumbnail) {
            return asset($this->parentProduct->product_thumbnail);
        }

        return asset('admin-assets/assets/images/upload-thumbnail.png');
    }

    /**
     * Get all variant images
     */
    public function getAllImagesAttribute()
    {
        $images = [];

        // Add variant specific image
        if ($this->image) {
            $images[] = asset($this->image);
        }

        // Add variant gallery images
        if ($this->images && is_array($this->images)) {
            foreach ($this->images as $image) {
                $images[] = asset($image);
            }
        }

        // Fallback to parent product image if no variant images
        if (empty($images) && $this->parentProduct && $this->parentProduct->product_thumbnail) {
            $images[] = asset($this->parentProduct->product_thumbnail);
        }

        return $images;
    }
}
