<?php

namespace App\Models\Analytics;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountsReceivable extends Model
{
    protected $table = 'accounts_receivable';

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'invoice_id',
        'invoice_amount',
        'paid_amount',
        'outstanding_amount',
        'invoice_date',
        'due_date',
        'paid_date',
        'status',
        'days_overdue',
        'notes',
    ];

    protected $casts = [
        'invoice_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'outstanding_amount' => 'decimal:2',
        'invoice_date' => 'date',
        'due_date' => 'date',
        'paid_date' => 'date',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Customer::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Invoice::class);
    }

    public function scopeByTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeOutstanding($query)
    {
        return $query->where('outstanding_amount', '>', 0);
    }

    public function scopeOverdue($query)
    {
        return $query->where('days_overdue', '>', 0);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}

