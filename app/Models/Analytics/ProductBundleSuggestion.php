<?php

namespace App\Models\Analytics;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductBundleSuggestion extends Model
{
    protected $table = 'product_bundle_suggestion';

    protected $fillable = [
        'tenant_id',
        'product_id_1',
        'product_id_2',
        'product_name_1',
        'product_sku_1',
        'product_name_2',
        'product_sku_2',
        'co_purchase_count',
        'co_purchase_frequency',
        'average_bundle_value',
        'bundle_orders',
        'bundle_revenue',
        'bundle_profit',
        'recommendation_score',
        'analysis_start_date',
        'analysis_end_date',
    ];

    protected $casts = [
        'co_purchase_frequency' => 'decimal:4',
        'average_bundle_value' => 'decimal:2',
        'bundle_revenue' => 'decimal:2',
        'bundle_profit' => 'decimal:2',
        'analysis_start_date' => 'date',
        'analysis_end_date' => 'date',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    public function product1(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Product::class, 'product_id_1');
    }

    public function product2(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Product::class, 'product_id_2');
    }

    public function scopeByTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeTopRecommendations($query, $limit = 10)
    {
        return $query->orderByDesc('recommendation_score')->limit($limit);
    }

    public function scopeHighFrequency($query, $minFrequency = 0.5)
    {
        return $query->where('co_purchase_frequency', '>=', $minFrequency);
    }
}

