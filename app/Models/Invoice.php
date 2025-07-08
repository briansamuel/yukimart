<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class Invoice extends Model
{
    use HasFactory;

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
        'paid_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'sent_at' => 'datetime',
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
     * Relationship with invoice items.
     */
    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('sort_order');
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
                    'processing' => '<span class="badge badge-warning">Đang xử lý</span>',
                    'completed' => '<span class="badge badge-success">Hoàn thành</span>',
                    'cancelled' => '<span class="badge badge-danger">Đã hủy</span>',
                    'failed' => '<span class="badge badge-dark">Không giao được</span>',
                    // Legacy statuses for backward compatibility
                    'draft' => '<span class="badge badge-secondary">Nháp</span>',
                    'sent' => '<span class="badge badge-info">Đã gửi</span>',
                    'paid' => '<span class="badge badge-success">Đã thanh toán</span>',
                    'overdue' => '<span class="badge badge-danger">Quá hạn</span>',
                    default => '<span class="badge badge-secondary">Không xác định</span>',
                };
            }
        );
    }

    /**
     * Get payment status badge HTML.
     */
    protected function paymentStatusBadge(): Attribute
    {
        return new Attribute(
            get: function($value, $attributes) {
                $status = $attributes['payment_status'] ?? 'unpaid';
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
            get: fn($value, $attributes) => number_format($attributes['remaining_amount'], 0, ',', '.') . ' VND'
        );
    }

    /**
     * Check if invoice is overdue.
     */
    protected function isOverdue(): Attribute
    {
        return new Attribute(
            get: function($value, $attributes) {
                if ($attributes['payment_status'] === 'paid') {
                    return false;
                }
                return Carbon::parse($attributes['due_date'])->isPast();
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
     * Generate unique invoice number.
     */
    public static function generateInvoiceNumber()
    {
        $prefix = 'INV';
        $date = date('Ymd');
        $lastInvoice = self::where('invoice_number', 'like', $prefix . $date . '%')
                          ->orderBy('invoice_number', 'desc')
                          ->first();
        
        if ($lastInvoice) {
            $lastNumber = intval(substr($lastInvoice->invoice_number, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }
        
        return $prefix . $date . $newNumber;
    }

    /**
     * Calculate invoice totals.
     */
    public function calculateTotals()
    {
        $subtotal = $this->invoiceItems->sum('line_total');
        $taxAmount = $subtotal * ($this->tax_rate / 100);
        $totalAmount = $subtotal + $taxAmount - $this->discount_amount;
        $remainingAmount = $totalAmount - $this->paid_amount;

        $this->update([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'remaining_amount' => $remainingAmount,
        ]);

        // Update payment status
        $this->updatePaymentStatus();

        return $this;
    }

    /**
     * Update payment status based on amounts.
     */
    public function updatePaymentStatus()
    {
        if ($this->paid_amount <= 0) {
            $paymentStatus = 'unpaid';
        } elseif ($this->paid_amount >= $this->total_amount) {
            $paymentStatus = $this->paid_amount > $this->total_amount ? 'overpaid' : 'paid';
        } else {
            $paymentStatus = 'partial';
        }

        $this->update(['payment_status' => $paymentStatus]);

        // Update main status if needed
        if ($paymentStatus === 'paid' && $this->status === 'sent') {
            $this->update(['status' => 'paid', 'paid_at' => now()]);
        } elseif ($this->is_overdue && $paymentStatus !== 'paid' && $this->status === 'sent') {
            $this->update(['status' => 'overdue']);
        }

        return $this;
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

        return [
            'total_invoices' => $query->count(),
            'paid_invoices' => (clone $query)->where('payment_status', 'paid')->count(),
            'unpaid_invoices' => (clone $query)->whereIn('payment_status', ['unpaid', 'partial'])->count(),
            'overdue_invoices' => (clone $query)->overdue()->count(),
            'total_amount' => (clone $query)->sum('total_amount'),
            'paid_amount' => (clone $query)->sum('paid_amount'),
            'remaining_amount' => (clone $query)->sum('remaining_amount'),
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
                $invoice->invoice_number = self::generateInvoiceNumber();
            }
            if (empty($invoice->created_by)) {
                $invoice->created_by = auth()->id();
            }
        });

        static::updating(function ($invoice) {
            $invoice->updated_by = auth()->id();
        });
    }
}
