<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'bank_name',
        'bank_code',
        'account_number',
        'account_holder',
        'branch_name',
        'qr_code',
        'is_active',
        'is_default',
        'sort_order',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    /**
     * Relationship with payments.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get active bank accounts
     */
    public static function getActive()
    {
        return self::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('bank_name')
            ->get();
    }

    /**
     * Get default bank account
     */
    public static function getDefault()
    {
        return self::where('is_active', true)
            ->where('is_default', true)
            ->first();
    }

    /**
     * Get formatted account info
     */
    public function getFormattedAccountAttribute()
    {
        return $this->bank_name . ' - ' . $this->account_number . ' - ' . $this->account_holder;
    }
}
