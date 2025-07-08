<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class Supplier extends Model
{
    use HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['last_ago', 'supplier_edit', 'status_badge', 'full_address'];

    /**
     * Get the supplier edit URL.
     */
    protected function supplierEdit(): Attribute
    {
        return new Attribute(
            get: fn($value, $attributes) => route('supplier.edit', ['supplier_id' => $attributes['id']]),
        );
    }

    /**
     * Get the time ago for created_at.
     */
    protected function lastAgo(): Attribute
    {
        Carbon::setLocale('vi');
        return new Attribute(
            get: fn($value, $attributes) => Carbon::parse($attributes['created_at'])->diffForHumans()
        );
    }

    /**
     * Get the status badge.
     */
    protected function statusBadge(): Attribute
    {
        return new Attribute(
            get: function($value, $attributes) {
                $status = $attributes['status'] ?? 'inactive';
                return match($status) {
                    'active' => '<span class="badge badge-success">Hoạt động</span>',
                    'inactive' => '<span class="badge badge-secondary">Không hoạt động</span>',
                    default => '<span class="badge badge-secondary">Không xác định</span>'
                };
            }
        );
    }

    /**
     * Get the full address.
     */
    protected function fullAddress(): Attribute
    {
        return new Attribute(
            get: function($value, $attributes) {
                $parts = array_filter([
                    $attributes['address'] ?? null,
                    $attributes['ward'] ?? null,
                    $attributes['district'] ?? null,
                    $attributes['province'] ?? null,
                ]);
                return implode(', ', $parts);
            }
        );
    }

    /**
     * Relationship with products (if suppliers are linked to products).
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Relationship with inventory transactions.
     */
    public function inventoryTransactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    /**
     * Scope for active suppliers.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for inactive suppliers.
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    /**
     * Scope for search by name, code, or company.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('code', 'like', "%{$search}%")
              ->orWhere('company', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%");
        });
    }

    /**
     * Generate unique supplier code.
     */
    public static function generateCode($prefix = 'SUP')
    {
        $lastSupplier = static::whereNotNull('code')
            ->where('code', 'like', $prefix . '%')
            ->orderBy('code', 'desc')
            ->first();

        if (!$lastSupplier) {
            return $prefix . '001';
        }

        $lastNumber = (int) substr($lastSupplier->code, strlen($prefix));
        $newNumber = $lastNumber + 1;

        return $prefix . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Get supplier by code.
     */
    public static function findByCode($code)
    {
        return static::where('code', $code)->first();
    }

    /**
     * Check if supplier has any products.
     */
    public function hasProducts()
    {
        return $this->products()->exists();
    }

    /**
     * Get supplier statistics.
     */
    public function getStats()
    {
        $importTransactions = $this->inventoryTransactions()->where('transaction_type', 'import');
        $totalImports = $importTransactions->count();
        $totalImportValue = $importTransactions->sum('total_value') ?? $importTransactions->sum('total_cost') ?? 0;
        $lastImportDate = $importTransactions->latest()->first()?->created_at;

        return [
            'total_products' => $this->products()->count(),
            'active_products' => $this->products()->where('product_status', 'publish')->count(),
            'total_imports' => $totalImports,
            'total_import_value' => $totalImportValue,
            'last_import_date' => $lastImportDate,
            'total_orders' => 0, // Can be implemented when order system is ready
            'last_order_date' => null, // Can be implemented when order system is ready
        ];
    }
}
