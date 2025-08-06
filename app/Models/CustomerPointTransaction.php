<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerPointTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'reference_type',
        'reference_id',
        'transaction_date',
        'type',
        'amount',
        'points',
        'balance_after',
        'notes'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'transaction_date' => 'datetime',
        'points' => 'integer',
        'amount' => 'decimal:2',
        'balance_after' => 'integer',
    ];

    /**
     * Relationship with customer.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Relationship with branch shop.
     */
    public function branchShop()
    {
        return $this->belongsTo(BranchShop::class);
    }

    /**
     * Relationship with user who created the transaction.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the reference model (polymorphic).
     */
    public function reference()
    {
        return $this->morphTo();
    }
}
