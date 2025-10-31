<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TenantScoped;

class VoucherUsage extends Model
{
    use HasFactory, TenantScoped;

    protected $table = 'voucher_usage';

    protected $fillable = [
        'tenant_id',
        'voucher_id',
        'customer_id',
        'order_id',
        'invoice_id',
        'discount_amount',
        'used_at',
    ];

    protected $casts = [
        'discount_amount' => 'decimal:2',
        'used_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship with voucher.
     */
    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }

    /**
     * Relationship with customer.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Relationship with order.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Relationship with invoice.
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}

