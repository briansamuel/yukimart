<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class Payment extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'payment_date' => 'datetime',
        'amount' => 'decimal:2',
        'actual_amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Polymorphic relationship with reference (invoice, return_order, order).
     */
    public function reference()
    {
        return $this->morphTo('reference', 'reference_type', 'reference_id');
    }

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
        return $this->belongsTo(BranchShop::class, 'branch_shop_id');
    }

    /**
     * Relationship with bank account.
     */
    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id');
    }

    /**
     * Relationship with creator.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relationship with updater.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Relationship with approver.
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Relationship with collector.
     */
    public function collector()
    {
        return $this->belongsTo(User::class, 'collector_id');
    }

    /**
     * Get payment type display name.
     */
    protected function paymentTypeDisplay(): Attribute
    {
        return new Attribute(
            get: function($value, $attributes) {
                $type = $attributes['payment_type'] ?? 'receipt';
                return match($type) {
                    'receipt' => 'Phiếu thu',
                    'payment' => 'Phiếu chi',
                    default => 'Không xác định',
                };
            }
        );
    }

    /**
     * Get payment method display name.
     */
    protected function paymentMethodDisplay(): Attribute
    {
        return new Attribute(
            get: function($value, $attributes) {
                $method = $attributes['payment_method'] ?? 'cash';
                return match($method) {
                    'cash' => 'Tiền mặt',
                    'card' => 'Thẻ',
                    'transfer' => 'Chuyển khoản',
                    'check' => 'Séc',
                    'points' => 'Điểm thưởng',
                    'other' => 'Khác',
                    default => 'Không xác định',
                };
            }
        );
    }

    /**
     * Get status badge HTML.
     */
    protected function statusBadge(): Attribute
    {
        return new Attribute(
            get: function($value, $attributes) {
                $status = $attributes['status'] ?? 'pending';
                return match($status) {
                    'pending' => '<span class="badge badge-warning">Chờ xử lý</span>',
                    'completed' => '<span class="badge badge-success">Đã hoàn thành</span>',
                    'cancelled' => '<span class="badge badge-danger">Đã hủy</span>',
                    default => '<span class="badge badge-secondary">Không xác định</span>',
                };
            }
        );
    }

    /**
     * Get formatted amount.
     */
    protected function formattedAmount(): Attribute
    {
        return new Attribute(
            get: fn($value, $attributes) => number_format($attributes['amount'], 0, ',', '.') . ' VND'
        );
    }

    /**
     * Get formatted actual amount.
     */
    protected function formattedActualAmount(): Attribute
    {
        return new Attribute(
            get: fn($value, $attributes) => number_format($attributes['actual_amount'] ?? 0, 0, ',', '.') . ' VND'
        );
    }

    /**
     * Generate unique payment number.
     */
    public static function generatePaymentNumber($type = 'receipt', $referenceNumber = null, $referenceType = null)
    {
        // Special case for invoice payments: TTHD{invoice_id}
        if ($referenceType === 'invoice' && $referenceNumber) {
            // Extract invoice ID from invoice number (HD20250709001 -> extract ID from database)
            $invoice = Invoice::where('invoice_number', $referenceNumber)->first();
            if ($invoice) {
                return \App\Services\PrefixGeneratorService::generatePaymentCode('invoice', $invoice->id);
            }
        }

        $prefix = $type === 'receipt' ? 'TTH' : 'TTC'; // Thu/Chi
        $date = date('Ymd');

        if ($referenceNumber && $referenceType !== 'invoice') {
            // Base on reference number (e.g., TH20250709001 -> TTH20250709001)
            $baseNumber = str_replace(['HD', 'TH', 'DH'], $prefix, $referenceNumber);
        } else {
            // Generate new number
            $baseNumber = $prefix . $date;
        }

        // Check if payment number already exists
        $count = self::where('payment_number', 'like', $baseNumber . '%')
                    ->count();

        if ($count > 0) {
            $baseNumber .= '-' . ($count + 1);
        } else if (!$referenceNumber || $referenceType === 'manual') {
            // Add sequence for manual payments
            $lastPayment = self::where('payment_number', 'like', $prefix . $date . '%')
                             ->orderBy('payment_number', 'desc')
                             ->first();

            if ($lastPayment) {
                $lastNumber = intval(substr($lastPayment->payment_number, -4));
                $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
            } else {
                $newNumber = '0001';
            }

            $baseNumber .= $newNumber;
        }

        return $baseNumber;
    }

    /**
     * Boot method to handle model events.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            if (empty($payment->payment_number)) {
                $payment->payment_number = self::generatePaymentNumber($payment->payment_type);
            }
            if (empty($payment->created_by)) {
                $payment->created_by = auth()->id();
            }
            if (empty($payment->actual_amount)) {
                $payment->actual_amount = $payment->amount;
            }
        });

        static::updating(function ($payment) {
            $payment->updated_by = auth()->id();
        });
    }
}
