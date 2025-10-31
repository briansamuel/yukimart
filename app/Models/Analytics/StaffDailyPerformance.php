<?php

namespace App\Models\Analytics;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffDailyPerformance extends Model
{
    protected $table = 'staff_daily_performance';

    protected $fillable = [
        'tenant_id',
        'branch_shop_id',
        'staff_id',
        'performance_date',
        'total_orders',
        'total_revenue',
        'total_profit',
        'unique_customers',
        'new_customers',
        'avg_order_value',
        'avg_profit_per_order',
        'profit_margin',
        'customer_satisfaction_score',
    ];

    protected $casts = [
        'performance_date' => 'date',
        'total_revenue' => 'decimal:2',
        'total_profit' => 'decimal:2',
        'avg_order_value' => 'decimal:2',
        'avg_profit_per_order' => 'decimal:2',
        'profit_margin' => 'decimal:4',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(\App\Models\BranchShop::class, 'branch_shop_id');
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'staff_id');
    }

    public function scopeByTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeByStaff($query, $staffId)
    {
        return $query->where('staff_id', $staffId);
    }

    public function scopeByDateRange($query, $fromDate, $toDate)
    {
        return $query->whereBetween('performance_date', [$fromDate, $toDate]);
    }
}

