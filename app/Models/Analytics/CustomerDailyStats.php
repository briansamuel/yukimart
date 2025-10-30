<?php

namespace App\Models\Analytics;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerDailyStats extends Model
{
    protected $table = 'customer_daily_stats';

    protected $fillable = [
        'tenant_id',
        'branch_shop_id',
        'stats_date',
        'total_customers',
        'new_customers',
        'returning_customers',
        'walkin_customers',
        'vip_customers',
        'new_customer_revenue',
        'returning_customer_revenue',
        'walkin_revenue',
        'vip_revenue',
        'avg_order_value',
        'avg_customer_lifetime_value',
    ];

    protected $casts = [
        'stats_date' => 'date',
        'new_customer_revenue' => 'decimal:2',
        'returning_customer_revenue' => 'decimal:2',
        'walkin_revenue' => 'decimal:2',
        'vip_revenue' => 'decimal:2',
        'avg_order_value' => 'decimal:2',
        'avg_customer_lifetime_value' => 'decimal:2',
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

