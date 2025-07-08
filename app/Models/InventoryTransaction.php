<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Traits\HasNotifications;

class InventoryTransaction extends Model
{
    use HasFactory, HasNotifications;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Control notifications for this model instance
     */
    protected $notificationsDisabled = false;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'product_id' => 'integer',
        'warehouse_id' => 'integer',
        'supplier_id' => 'integer',
        'quantity' => 'integer',
        'old_quantity' => 'integer',
        'new_quantity' => 'integer',
        'unit_cost' => 'decimal:2',
        'total_value' => 'decimal:2',
        'reference_id' => 'integer',
        'created_by_user' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Transaction type constants
     */
    const TYPE_IMPORT = 'import';      // Nhập kho
    const TYPE_EXPORT = 'export';      // Xuất kho
    const TYPE_SALE = 'sale';          // Bán hàng
    const TYPE_TRANSFER = 'transfer';  // Chuyển kho
    const TYPE_ADJUSTMENT = 'adjustment'; // Điều chỉnh
    const TYPE_INITIAL = 'initial';    // Tồn đầu kỳ

    /**
     * Get all transaction types
     */
    public static function getTransactionTypes()
    {
        return [
            self::TYPE_IMPORT => 'Nhập kho',
            self::TYPE_EXPORT => 'Xuất kho',
            self::TYPE_SALE => 'Bán hàng',
            self::TYPE_TRANSFER => 'Chuyển kho',
            self::TYPE_ADJUSTMENT => 'Điều chỉnh',
            self::TYPE_INITIAL => 'Tồn đầu kỳ',
        ];
    }

    /**
     * Get the product that owns the transaction
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the warehouse that owns the transaction
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the supplier that owns the transaction
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the user who created this transaction
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user');
    }

    /**
     * Get the related model (polymorphic relationship)
     */
    public function reference()
    {
        return $this->morphTo('reference', 'reference_type', 'reference_id');
    }

    /**
     * Get formatted transaction type
     */
    protected function formattedType(): Attribute
    {
        return new Attribute(
            get: function ($value, $attributes) {
                $types = self::getTransactionTypes();
                return $types[$attributes['transaction_type']] ?? $attributes['transaction_type'];
            }
        );
    }

    /**
     * Get formatted quantity change with sign
     */
    protected function formattedQuantityChange(): Attribute
    {
        return new Attribute(
            get: function ($value, $attributes) {
                $change = $attributes['quantity'];
                return $change > 0 ? '+' . $change : (string) $change;
            }
        );
    }

    /**
     * Get transaction impact (positive/negative)
     */
    protected function impact(): Attribute
    {
        return new Attribute(
            get: function ($value, $attributes) {
                return $attributes['quantity'] > 0 ? 'positive' : 'negative';
            }
        );
    }

    /**
     * Scope a query to only include transactions for a specific product
     */
    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope a query to only include transactions for a specific warehouse
     */
    public function scopeForWarehouse($query, $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    /**
     * Scope a query to only include transactions for a specific supplier
     */
    public function scopeForSupplier($query, $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    /**
     * Scope a query to only include transactions of a specific type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('transaction_type', $type);
    }

    /**
     * Scope a query to only include positive transactions (stock increases)
     */
    public function scopePositive($query)
    {
        return $query->where('quantity', '>', 0);
    }

    /**
     * Scope a query to only include negative transactions (stock decreases)
     */
    public function scopeNegative($query)
    {
        return $query->where('quantity', '<', 0);
    }

    /**
     * Scope a query to filter by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope a query to filter by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('created_by_user', $userId);
    }

    /**
     * Check if transaction increases stock
     */
    public function isStockIncrease()
    {
        return $this->quantity > 0;
    }

    /**
     * Check if transaction decreases stock
     */
    public function isStockDecrease()
    {
        return $this->quantity < 0;
    }

    /**
     * Get absolute quantity change
     */
    public function getAbsoluteQuantityChange()
    {
        return abs($this->quantity);
    }

    /**
     * Check if should send inventory notification
     */
    public function shouldSendInventoryNotification()
    {
        // Don't send notification for invoice-related transactions
        if ($this->reference_type === 'invoice') {
            return false;
        }

        // Don't send notification for sale transactions (from orders/invoices)
        if ($this->transaction_type === 'sale') {
            return false;
        }

        // Send notification for other types (import, export, adjustment, etc.)
        return true;
    }
}
