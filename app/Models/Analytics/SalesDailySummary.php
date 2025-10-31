<?php

namespace App\Models\Analytics;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesDailySummary extends Model
{
    protected $table = 'sales_daily_summary';

    protected $fillable = [
        'tenant_id',
        'branch_shop_id',
        'summary_date',
        'total_orders',
        'total_return_orders',
        'total_revenue',
        'total_return_amount',
        'net_revenue',
        'total_cogs',
        'total_profit',
        'unique_customers',
        'new_customers',
        'returning_customers',
        'walkin_customers',
        'offline_revenue',
        'online_revenue',
        'marketplace_revenue',
    ];

    protected $casts = [
        'summary_date' => 'date',
        'total_revenue' => 'decimal:2',
        'total_return_amount' => 'decimal:2',
        'net_revenue' => 'decimal:2',
        'total_cogs' => 'decimal:2',
        'total_profit' => 'decimal:2',
        'offline_revenue' => 'decimal:2',
        'online_revenue' => 'decimal:2',
        'marketplace_revenue' => 'decimal:2',
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

    public function scopeByBranch($query, $branchId)
    {
        return $query->where('branch_shop_id', $branchId);
    }

    public function scopeByDateRange($query, $fromDate, $toDate)
    {
        return $query->whereBetween('summary_date', [$fromDate, $toDate]);
    }
}

