<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
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
        'product_id' => 'integer',
        'variant_id' => 'integer',
        'warehouse_id' => 'integer',
        'quantity' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the product that owns this inventory record
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the warehouse that owns this inventory record
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the variant that owns this inventory record
     */
    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    /**
     * Scope a query to only include inventory for a specific product
     */
    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope a query to only include inventory for a specific variant
     */
    public function scopeForVariant($query, $variantId)
    {
        return $query->where('variant_id', $variantId);
    }

    /**
     * Scope a query to only include inventory for simple products (no variants)
     */
    public function scopeForSimpleProducts($query)
    {
        return $query->whereNull('variant_id');
    }

    /**
     * Scope a query to only include inventory with positive quantities
     */
    public function scopeInStock($query)
    {
        return $query->where('quantity', '>', 0);
    }

    /**
     * Scope a query to only include inventory with zero or negative quantities
     */
    public function scopeOutOfStock($query)
    {
        return $query->where('quantity', '<=', 0);
    }

    /**
     * Get the total quantity for all products
     */
    public static function getTotalQuantity()
    {
        return static::sum('quantity');
    }

    /**
     * Get the total quantity for a specific product across all warehouses
     */
    public static function getProductQuantity($productId)
    {
        return static::where('product_id', $productId)->sum('quantity');
    }

    /**
     * Get quantity for a specific product in a specific warehouse
     */
    public static function getProductQuantityInWarehouse($productId, $warehouseId)
    {
        return static::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->whereNull('variant_id') // Only simple products
            ->value('quantity') ?? 0;
    }

    /**
     * Get quantity for a specific variant in a specific warehouse
     */
    public static function getVariantQuantityInWarehouse($variantId, $warehouseId)
    {
        return static::where('variant_id', $variantId)
            ->where('warehouse_id', $warehouseId)
            ->value('quantity') ?? 0;
    }

    /**
     * Get total quantity for a variant across all warehouses
     */
    public static function getVariantQuantity($variantId)
    {
        return static::where('variant_id', $variantId)->sum('quantity');
    }

    /**
     * Update or create inventory record for a product in a warehouse
     */
    public static function updateProductQuantity($productId, $quantity, $warehouseId = null)
    {
        if (!$warehouseId) {
            $warehouseId = Warehouse::getDefault()->id;
        }

        return static::updateOrCreate(
            ['product_id' => $productId, 'warehouse_id' => $warehouseId, 'variant_id' => null],
            ['quantity' => $quantity]
        );
    }

    /**
     * Update or create inventory record for a variant in a warehouse
     */
    public static function updateVariantQuantity($variantId, $quantity, $warehouseId = null)
    {
        if (!$warehouseId) {
            $warehouseId = Warehouse::getDefault()->id;
        }

        $variant = ProductVariant::find($variantId);
        if (!$variant) {
            throw new \Exception("Variant not found: {$variantId}");
        }

        return static::updateOrCreate(
            ['product_id' => $variant->parent_product_id, 'variant_id' => $variantId, 'warehouse_id' => $warehouseId],
            ['quantity' => $quantity]
        );
    }

    /**
     * Add quantity to a product's inventory in a warehouse
     */
    public static function addProductQuantity($productId, $quantity, $warehouseId = null)
    {
        if (!$warehouseId) {
            $warehouseId = Warehouse::getDefault()->id;
        }

        $inventory = static::firstOrCreate(
            ['product_id' => $productId, 'warehouse_id' => $warehouseId, 'variant_id' => null],
            ['quantity' => 0]
        );
        $inventory->increment('quantity', $quantity);
        return $inventory;
    }

    /**
     * Add quantity to a variant's inventory in a warehouse
     */
    public static function addVariantQuantity($variantId, $quantity, $warehouseId = null)
    {
        if (!$warehouseId) {
            $warehouseId = Warehouse::getDefault()->id;
        }

        $variant = ProductVariant::find($variantId);
        if (!$variant) {
            throw new \Exception("Variant not found: {$variantId}");
        }

        $inventory = static::firstOrCreate(
            ['product_id' => $variant->parent_product_id, 'variant_id' => $variantId, 'warehouse_id' => $warehouseId],
            ['quantity' => 0]
        );
        $inventory->increment('quantity', $quantity);
        return $inventory;
    }

    /**
     * Remove quantity from a product's inventory in a warehouse
     */
    public static function removeProductQuantity($productId, $quantity, $warehouseId = null)
    {
        if (!$warehouseId) {
            $warehouseId = Warehouse::getDefault()->id;
        }

        $inventory = static::firstOrCreate(
            ['product_id' => $productId, 'warehouse_id' => $warehouseId, 'variant_id' => null],
            ['quantity' => 0]
        );
        $newQuantity = max(0, $inventory->quantity - $quantity);
        $inventory->update(['quantity' => $newQuantity]);
        return $inventory;
    }

    /**
     * Remove quantity from a variant's inventory in a warehouse
     */
    public static function removeVariantQuantity($variantId, $quantity, $warehouseId = null)
    {
        if (!$warehouseId) {
            $warehouseId = Warehouse::getDefault()->id;
        }

        $variant = ProductVariant::find($variantId);
        if (!$variant) {
            throw new \Exception("Variant not found: {$variantId}");
        }

        $inventory = static::firstOrCreate(
            ['product_id' => $variant->parent_product_id, 'variant_id' => $variantId, 'warehouse_id' => $warehouseId],
            ['quantity' => 0]
        );
        $newQuantity = max(0, $inventory->quantity - $quantity);
        $inventory->update(['quantity' => $newQuantity]);
        return $inventory;
    }

    /**
     * Check if product has sufficient stock
     */
    public static function hasStock($productId, $requiredQuantity = 1)
    {
        $currentQuantity = static::getProductQuantity($productId);
        return $currentQuantity >= $requiredQuantity;
    }

    /**
     * Get products with low stock based on reorder point
     */
    public static function getLowStockProducts()
    {
        return static::select('inventories.*', 'products.product_name', 'products.reorder_point')
            ->join('products', 'inventories.product_id', '=', 'products.id')
            ->whereRaw('inventories.quantity <= products.reorder_point')
            ->where('products.product_status', 'publish')
            ->get();
    }

    /**
     * Get products that are out of stock
     */
    public static function getOutOfStockProducts()
    {
        return static::select('inventories.*', 'products.product_name')
            ->join('products', 'inventories.product_id', '=', 'products.id')
            ->where('inventories.quantity', '<=', 0)
            ->where('products.product_status', 'publish')
            ->get();
    }
}
