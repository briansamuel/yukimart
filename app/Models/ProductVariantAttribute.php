<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariantAttribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'variant_id',
        'attribute_id',
        'attribute_value_id'
    ];

    /**
     * Get the variant that owns this attribute
     */
    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    /**
     * Get the attribute
     */
    public function attribute()
    {
        return $this->belongsTo(ProductAttribute::class, 'attribute_id');
    }

    /**
     * Get the attribute value
     */
    public function attributeValue()
    {
        return $this->belongsTo(ProductAttributeValue::class, 'attribute_value_id');
    }

    /**
     * Get variant attributes with their details
     */
    public static function getVariantAttributesWithDetails($variantId)
    {
        return static::where('variant_id', $variantId)
                    ->with(['attribute', 'attributeValue'])
                    ->get()
                    ->map(function ($item) {
                        return [
                            'attribute_name' => $item->attribute->name,
                            'attribute_slug' => $item->attribute->slug,
                            'value_name' => $item->attributeValue->value,
                            'value_slug' => $item->attributeValue->slug,
                            'color_code' => $item->attributeValue->color_code,
                            'price_adjustment' => $item->attributeValue->price_adjustment,
                        ];
                    });
    }

    /**
     * Create variant attributes from array
     */
    public static function createVariantAttributes($variantId, $attributes)
    {
        foreach ($attributes as $attributeId => $valueId) {
            static::create([
                'variant_id' => $variantId,
                'attribute_id' => $attributeId,
                'attribute_value_id' => $valueId
            ]);
        }
    }

    /**
     * Update variant attributes
     */
    public static function updateVariantAttributes($variantId, $attributes)
    {
        // Delete existing attributes
        static::where('variant_id', $variantId)->delete();
        
        // Create new attributes
        static::createVariantAttributes($variantId, $attributes);
    }
}
