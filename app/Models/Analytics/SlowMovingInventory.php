<?php

namespace App\Models\Analytics;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SlowMovingInventory extends Model
{
    protected $table = 'slow_moving_inventory';

    protected $fillable = [
        'tenant_id',
        'branch_shop_id',
        'product_id',
        'product_name',
        'product_sku',
        'category_id',
        'current_stock',
        'stock_value',
        'days_without_sale',
        'last_sale_days_ago',
        'last_sale_date',
        'sales_last_30_days',
        'sales_last_60_days',
        'sales_last_90_days',
        'aging_category',
        'recommendation',
    ];

    protected $casts = [
        'stock_value' => 'decimal:2',
        'last_sale_date' => 'date',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(\App\Models\BranchShop::class, 'branch_shop_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Product::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Category::class);
    }

    public function scopeByTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeByAgingCategory($query, $category)
    {
        return $query->where('aging_category', $category);
    }

    public function scopeDeadStock($query)
    {
        return $query->where('aging_category', 'dead_stock');
    }

    public function scopeSlowMoving($query)
    {
        return $query->where('aging_category', 'slow_moving');
    }
}

