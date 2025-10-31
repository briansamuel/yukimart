<?php

namespace App\Models\Analytics;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerRetentionStats extends Model
{
    protected $table = 'customer_retention_stats';

    protected $fillable = [
        'tenant_id',
        'branch_shop_id',
        'cohort_month',
        'cohort_size',
        'month_0_retained',
        'month_1_retained',
        'month_2_retained',
        'month_3_retained',
        'month_6_retained',
        'month_12_retained',
        'month_0_retention_rate',
        'month_1_retention_rate',
        'month_2_retention_rate',
        'month_3_retention_rate',
        'month_6_retention_rate',
        'month_12_retention_rate',
        'cohort_revenue',
        'avg_revenue_per_customer',
    ];

    protected $casts = [
        'month_0_retention_rate' => 'decimal:4',
        'month_1_retention_rate' => 'decimal:4',
        'month_2_retention_rate' => 'decimal:4',
        'month_3_retention_rate' => 'decimal:4',
        'month_6_retention_rate' => 'decimal:4',
        'month_12_retention_rate' => 'decimal:4',
        'cohort_revenue' => 'decimal:2',
        'avg_revenue_per_customer' => 'decimal:2',
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

    public function scopeByCohortMonth($query, $month)
    {
        return $query->where('cohort_month', $month);
    }

    public function scopeRecentCohorts($query, $months = 12)
    {
        $startMonth = now()->subMonths($months)->format('Y-m');
        return $query->where('cohort_month', '>=', $startMonth);
    }
}

