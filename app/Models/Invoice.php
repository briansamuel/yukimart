<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Events\InvoiceCreated;
use App\Events\InvoiceStatusChanged;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_rate' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'sent_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'deleted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship with customer.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get customer name or "Khách lẻ" for walk-in customers.
     */
    protected function customerName(): Attribute
    {
        return new Attribute(
            get: fn($value, $attributes) => $this->customer ? $this->customer->name : 'Khách lẻ'
        );
    }

    /**
     * Get customer display with phone.
     */
    protected function customerDisplay(): Attribute
    {
        return new Attribute(
            get: function($value, $attributes) {
                if (!$this->customer) {
                    return 'Khách lẻ';
                }
                $phone = $this->customer->phone ? ' - ' . $this->customer->phone : '';
                return $this->customer->name . $phone;
            }
        );
    }

    /**
     * Relationship with order.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Relationship with branch shop.
     */
    public function branchShop()
    {
        return $this->belongsTo(BranchShop::class, 'branch_shop_id');
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
     * Relationship with seller.
     */
    public function seller()
    {
        return $this->belongsTo(User::class, 'sold_by');
    }

    /**
     * Relationship with canceller.
     */
    public function canceller()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    /**
     * Relationship with invoice items.
     */
    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('sort_order');
    }

    /**
     * Relationship with return orders.
     */
    public function returnOrders()
    {
        return $this->hasMany(ReturnOrder::class);
    }

    /**
     * Relationship with payments.
     */
    public function payments()
    {
        return $this->morphMany(Payment::class, 'reference', 'reference_type', 'reference_id')
                    ->where('reference_type', 'invoice');
    }

    /**
     * Get status badge HTML.
     */
    protected function statusBadge(): Attribute
    {
        return new Attribute(
            get: function($value, $attributes) {
                $status = $attributes['status'] ?? 'processing';
                return match($status) {
                    'draft' => '<span class="badge badge-secondary">Nháp</span>',
                    'pending' => '<span class="badge badge-info">Chờ xử lý</span>',
                    'confirmed' => '<span class="badge badge-primary">Đã xác nhận</span>',
                    'processing' => '<span class="badge badge-warning">Đang xử lý</span>',
                    'completed' => '<span class="badge badge-success">Hoàn thành</span>',
                    'cancelled' => '<span class="badge badge-danger">Đã hủy</span>',
                    'returned_partial' => '<span class="badge badge-light">Trả một phần</span>',
                    'returned_full' => '<span class="badge badge-dark">Trả toàn bộ</span>',
                    // Legacy statuses for backward compatibility
                    'failed' => '<span class="badge badge-dark">Không giao được</span>',
                    'sent' => '<span class="badge badge-info">Đã gửi</span>',
                    'paid' => '<span class="badge badge-success">Đã thanh toán</span>',
                    'overdue' => '<span class="badge badge-danger">Quá hạn</span>',
                    default => '<span class="badge badge-secondary">Không xác định</span>',
                };
            }
        );
    }

    /**
     * Get cancellation info.
     */
    protected function cancellationInfo(): Attribute
    {
        return new Attribute(
            get: function($value, $attributes) {
                if ($attributes['status'] !== 'cancelled' || !$attributes['cancelled_at']) {
                    return null;
                }

                $cancelledAt = Carbon::parse($attributes['cancelled_at']);
                $info = [
                    'cancelled_at' => $cancelledAt->format('d/m/Y H:i'),
                    'cancelled_at_human' => $cancelledAt->diffForHumans(),
                    'cancelled_by_id' => $attributes['cancelled_by'] ?? null,
                    'cancelled_by_name' => null
                ];

                // Load canceller name if available
                if ($this->canceller) {
                    $info['cancelled_by_name'] = $this->canceller->full_name ?? $this->canceller->name;
                }

                return $info;
            }
        );
    }

    /**
     * Get payment status badge HTML.
     */
    protected function paymentStatusBadge(): Attribute
    {
        return new Attribute(
            get: function() {
                $status = $this->payment_status;
                return match($status) {
                    'unpaid' => '<span class="badge badge-light">Chưa thanh toán</span>',
                    'partial' => '<span class="badge badge-warning">Thanh toán một phần</span>',
                    'paid' => '<span class="badge badge-success">Đã thanh toán</span>',
                    'overpaid' => '<span class="badge badge-info">Thanh toán thừa</span>',
                    default => '<span class="badge badge-secondary">Không xác định</span>',
                };
            }
        );
    }

    /**
     * Get payment status computed from payments.
     */
    protected function paymentStatus(): Attribute
    {
        return new Attribute(
            get: function() {
                $paidAmount = $this->paid_amount;
                $totalAmount = $this->total_amount;

                if ($paidAmount <= 0) {
                    return 'unpaid';
                } elseif ($paidAmount >= $totalAmount) {
                    return $paidAmount > $totalAmount ? 'overpaid' : 'paid';
                } else {
                    return 'partial';
                }
            }
        );
    }

    /**
     * Get total paid amount from payments.
     */
    protected function paidAmount(): Attribute
    {
        return new Attribute(
            get: function() {
                return $this->payments()
                    ->where('payment_type', 'receipt')
                    ->where('status', 'completed')
                    ->sum('actual_amount') ?? 0;
            }
        );
    }

    /**
     * Get invoice type display name.
     */
    protected function invoiceTypeDisplay(): Attribute
    {
        return new Attribute(
            get: function($value, $attributes) {
                $type = $attributes['invoice_type'] ?? 'sale';
                return match($type) {
                    'sale' => 'Bán hàng',
                    'return' => 'Trả hàng',
                    'adjustment' => 'Điều chỉnh',
                    'other' => 'Khác',
                    default => 'Không xác định',
                };
            }
        );
    }

    /**
     * Get formatted total amount.
     */
    protected function formattedTotalAmount(): Attribute
    {
        return new Attribute(
            get: fn($value, $attributes) => number_format($attributes['total_amount'], 0, ',', '.') . ' VND'
        );
    }

    /**
     * Get formatted remaining amount.
     */
    protected function formattedRemainingAmount(): Attribute
    {
        return new Attribute(
            get: fn() => number_format($this->remaining_amount, 0, ',', '.') . ' VND'
        );
    }

    /**
     * Get remaining amount computed from payments.
     */
    protected function remainingAmount(): Attribute
    {
        return new Attribute(
            get: function() {
                return max(0, $this->total_amount - $this->paid_amount);
            }
        );
    }

    /**
     * Check if invoice is overdue.
     */
    protected function isOverdue(): Attribute
    {
        return new Attribute(
            get: function() {
                if ($this->payment_status === 'paid') {
                    return false;
                }
                return Carbon::parse($this->due_date)->isPast();
            }
        );
    }

    /**
     * Get days until due or overdue.
     */
    protected function daysUntilDue(): Attribute
    {
        return new Attribute(
            get: fn($value, $attributes) => Carbon::parse($attributes['due_date'])->diffInDays(now(), false)
        );
    }

    /**
     * Update invoice status atomically to prevent race conditions.
     */
    public function updateStatusAtomic($newStatus, $additionalData = [])
    {
        return DB::transaction(function () use ($newStatus, $additionalData) {
            // Lock the record for update
            $invoice = self::lockForUpdate()->find($this->id);

            if (!$invoice) {
                throw new \Exception('Invoice not found');
            }

            // Validate status transition
            $allowedTransitions = [
                'processing' => ['completed', 'cancelled', 'undeliverable'],
                'completed' => ['cancelled'], // Allow cancellation of completed invoices
                'cancelled' => [], // Cannot change from cancelled
                'undeliverable' => ['processing', 'cancelled'], // Can retry or cancel
            ];

            $currentStatus = $invoice->status;
            if (!in_array($newStatus, $allowedTransitions[$currentStatus] ?? [])) {
                throw new \Exception("Cannot change status from {$currentStatus} to {$newStatus}");
            }

            // Update status and additional data
            $updateData = array_merge($additionalData, [
                'status' => $newStatus,
                'updated_by' => auth()->id(),
                'updated_at' => now(),
            ]);

            // Add status-specific fields
            if ($newStatus === 'completed') {
                $updateData['completed_at'] = now();
            } elseif ($newStatus === 'cancelled') {
                $updateData['cancelled_at'] = now();
            }

            $invoice->update($updateData);

            return $invoice->fresh();
        });
    }

    /**
     * Generate unique invoice number with atomic operation to prevent race conditions.
     */
    public static function generateInvoiceNumber($customPrefix = null)
    {
        return \App\Services\PrefixGeneratorService::generateInvoiceNumber($customPrefix);
    }

    /**
     * Generate unique exchange invoice number for return orders.
     */
    public static function generateExchangeInvoiceNumber()
    {
        return self::generateInvoiceNumber('HDD_TH');
    }

    /**
     * Calculate invoice totals.
     */
    public function calculateTotals()
    {
        $subtotal = $this->invoiceItems->sum('line_total');
        $taxAmount = $subtotal * ($this->tax_rate / 100);
        $totalAmount = $subtotal + $taxAmount - $this->discount_amount;

        $this->update([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
        ]);

        // Update payment status
        $this->updatePaymentStatus();

        return $this;
    }

    /**
     * Update payment status based on amounts atomically.
     * Note: Payment status is now computed from payments table, not stored in invoice.
     */
    public function updatePaymentStatus()
    {
        return DB::transaction(function () {
            // Lock the record for update to prevent race conditions
            $invoice = self::lockForUpdate()->find($this->id);

            if (!$invoice) {
                throw new \Exception('Invoice not found');
            }

            // Calculate payment status from payments table
            $paidAmount = $invoice->paid_amount; // This uses the accessor
            $totalAmount = $invoice->total_amount;

            $paymentStatus = 'unpaid';
            if ($paidAmount > 0) {
                if ($paidAmount >= $totalAmount) {
                    $paymentStatus = $paidAmount > $totalAmount ? 'overpaid' : 'paid';
                } else {
                    $paymentStatus = 'partial';
                }
            }

            $updateData = [
                'updated_by' => auth()->id(),
                'updated_at' => now(),
            ];

            // Update main status if needed
            if ($paymentStatus === 'paid' && in_array($invoice->status, ['processing', 'sent'])) {
                $updateData['status'] = 'completed';
            } elseif ($invoice->is_overdue && $paymentStatus !== 'paid' && $invoice->status === 'sent') {
                $updateData['status'] = 'overdue';
            }

            $invoice->update($updateData);

            return $invoice->fresh();
        });
    }

    /**
     * Get customer display name
     */
    public function getCustomerDisplayAttribute()
    {
        if ($this->customer) {
            return $this->customer->name ?? 'Khách hàng';
        }
        return 'Khách lẻ';
    }

    /**
     * Scope for active invoices.
     */
    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['cancelled']);
    }

    /**
     * Scope for overdue invoices.
     */
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
                    ->whereNotIn('payment_status', ['paid'])
                    ->whereNotIn('status', ['cancelled']);
    }

    /**
     * Scope for paid invoices.
     */
    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    /**
     * Scope for unpaid invoices.
     */
    public function scopeUnpaid($query)
    {
        return $query->whereIn('payment_status', ['unpaid', 'partial']);
    }

    /**
     * Scope for search by invoice number or customer.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('invoice_number', 'like', "%{$search}%")
              ->orWhereHas('customer', function($customerQuery) use ($search) {
                  $customerQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('phone', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
              });
        });
    }

    /**
     * Get invoice statistics.
     */
    public static function getStatistics($filters = [])
    {
        $query = self::query();

        // Apply filters
        if (isset($filters['date_from'])) {
            $query->whereDate('invoice_date', '>=', $filters['date_from']);
        }
        if (isset($filters['date_to'])) {
            $query->whereDate('invoice_date', '<=', $filters['date_to']);
        }
        if (isset($filters['branch_shop_id'])) {
            $query->where('branch_shop_id', $filters['branch_shop_id']);
        }

        // Calculate paid and remaining amounts from payments table
        $invoices = $query->with('payments')->get();
        $totalAmount = $invoices->sum('total_amount');
        $paidAmount = $invoices->sum('paid_amount'); // Uses accessor
        $remainingAmount = $totalAmount - $paidAmount;

        // Count invoices by payment status (computed)
        $paidCount = $invoices->filter(fn($invoice) => $invoice->payment_status === 'paid')->count();
        $unpaidCount = $invoices->filter(fn($invoice) => in_array($invoice->payment_status, ['unpaid', 'partial']))->count();
        $overdueCount = $invoices->filter(fn($invoice) => $invoice->is_overdue)->count();

        return [
            'total_invoices' => $invoices->count(),
            'paid_invoices' => $paidCount,
            'unpaid_invoices' => $unpaidCount,
            'overdue_invoices' => $overdueCount,
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'remaining_amount' => $remainingAmount,
        ];
    }

    /**
     * Boot method to handle model events.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            if (empty($invoice->invoice_number)) {
                // Generate invoice number within transaction to ensure atomicity
                DB::transaction(function () use ($invoice) {
                    $invoice->invoice_number = self::generateInvoiceNumber();
                });
            }
            if (empty($invoice->created_by)) {
                $invoice->created_by = auth()->id();
            }
            if (empty($invoice->sold_by)) {
                $invoice->sold_by = auth()->id();
            }
        });

        static::updating(function ($invoice) {
            $invoice->updated_by = auth()->id();

            // Track status changes for notifications
            if ($invoice->isDirty('status')) {
                $oldStatus = $invoice->getOriginal('status');
                $newStatus = $invoice->status;

                // Store old status for the updated event
                $invoice->_oldStatus = $oldStatus;
            }
        });

        // Dispatch event when invoice is created
        static::created(function ($invoice) {
            InvoiceCreated::dispatch($invoice, true, false);
        });

        // Dispatch event when invoice status is updated
        static::updated(function ($invoice) {
            if (isset($invoice->_oldStatus)) {
                InvoiceStatusChanged::dispatch($invoice, $invoice->_oldStatus, $invoice->status);
            }
        });
    }
}
