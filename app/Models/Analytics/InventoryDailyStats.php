<?php

namespace App\Models\Analytics;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryDailyStats extends Model
{
    protected $table = 'inventory_daily_stats';

    protected $fillable = [
        'tenant_id',
        'branch_shop_id',
        'stats_date',
        'total_skus',
        'low_stock_items',
        'overstock_items',
        'out_of_stock_items',
        'total_inventory_value',
        'low_stock_value',
        'overstock_value',
        'items_sold',
        'items_received',
        'inventory_turnover_rate',
        'items_not_sold_30_days',
        'items_not_sold_60_days',
        'items_not_sold_90_days',
    ];

    protected $casts = [
        'stats_date' => 'date',
        'total_inventory_value' => 'decimal:2',
        'low_stock_value' => 'decimal:2',
        'overstock_value' => 'decimal:2',
        'inventory_turnover_rate' => 'decimal:4',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(\App\Models\BranchShop::class, 'branch_shop_id');
    }

    public function scopeByTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeByDateRange($query, $fromDate, $toDate)
    {
        return $query->whereBetween('stats_date', [$fromDate, $toDate]);
    }
}

