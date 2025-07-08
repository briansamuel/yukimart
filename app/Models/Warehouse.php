<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
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
        'is_default' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get all inventories in this warehouse
     */
    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    /**
     * Get all inventory transactions for this warehouse
     */
    public function inventoryTransactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    /**
     * Get all branch shops using this warehouse
     */
    public function branchShops()
    {
        return $this->hasMany(BranchShop::class);
    }

    /**
     * Scope to get only active warehouses
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get default warehouse
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Get total products in this warehouse
     */
    public function getTotalProductsAttribute()
    {
        return $this->inventories()->count();
    }

    /**
     * Get total quantity in this warehouse
     */
    public function getTotalQuantityAttribute()
    {
        return $this->inventories()->sum('quantity');
    }

    /**
     * Get total value in this warehouse
     */
    public function getTotalValueAttribute()
    {
        return $this->inventories()
            ->join('products', 'inventories.product_id', '=', 'products.id')
            ->selectRaw('SUM(inventories.quantity * products.cost_price) as total_value')
            ->value('total_value') ?? 0;
    }

    /**
     * Get products with low stock in this warehouse
     */
    public function getLowStockProducts()
    {
        return $this->inventories()
            ->join('products', 'inventories.product_id', '=', 'products.id')
            ->whereRaw('inventories.quantity <= products.reorder_point')
            ->where('products.product_status', 'publish')
            ->select('products.*', 'inventories.quantity as stock_quantity')
            ->get();
    }

    /**
     * Get products out of stock in this warehouse
     */
    public function getOutOfStockProducts()
    {
        return $this->inventories()
            ->join('products', 'inventories.product_id', '=', 'products.id')
            ->where('inventories.quantity', '<=', 0)
            ->where('products.product_status', 'publish')
            ->select('products.*', 'inventories.quantity as stock_quantity')
            ->get();
    }

    /**
     * Get inventory for a specific product
     */
    public function getProductInventory($productId)
    {
        return $this->inventories()->where('product_id', $productId)->first();
    }

    /**
     * Get product quantity in this warehouse
     */
    public function getProductQuantity($productId)
    {
        $inventory = $this->getProductInventory($productId);
        return $inventory ? $inventory->quantity : 0;
    }

    /**
     * Check if warehouse has enough stock for a product
     */
    public function hasStock($productId, $requiredQuantity = 1)
    {
        return $this->getProductQuantity($productId) >= $requiredQuantity;
    }

    /**
     * Get warehouse summary statistics
     */
    public function getSummary()
    {
        $totalProducts = $this->total_products;
        $totalQuantity = $this->total_quantity;
        $totalValue = $this->total_value;
        $lowStockCount = $this->getLowStockProducts()->count();
        $outOfStockCount = $this->getOutOfStockProducts()->count();

        return [
            'total_products' => $totalProducts,
            'total_quantity' => $totalQuantity,
            'total_value' => $totalValue,
            'low_stock_count' => $lowStockCount,
            'out_of_stock_count' => $outOfStockCount,
            'in_stock_count' => $totalProducts - $outOfStockCount,
            'stock_health_percentage' => $totalProducts > 0 ? 
                round((($totalProducts - $lowStockCount - $outOfStockCount) / $totalProducts) * 100, 1) : 100
        ];
    }

    /**
     * Get default warehouse
     */
    public static function getDefault()
    {
        return static::default()->first();
    }

    /**
     * Set as default warehouse
     */
    public function setAsDefault()
    {
        // Remove default from other warehouses
        static::where('is_default', true)->update(['is_default' => false]);
        
        // Set this warehouse as default
        $this->update(['is_default' => true]);
        
        return $this;
    }
}
