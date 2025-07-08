<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\UserTimeStamp;
use App\Traits\HasNotifications;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Product extends Model
{
    use HasFactory, SoftDeletes, UserTimeStamp, HasNotifications;

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
        'cost_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'weight' => 'integer',
        'points' => 'integer',
        'reorder_point' => 'integer',
        'product_feature' => 'boolean',
        'supplier_cost' => 'decimal:2',
        'lead_time_days' => 'integer',
        'length' => 'decimal:2',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
        'volume' => 'decimal:4',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'has_variants' => 'boolean',
        'variants_count' => 'integer',
        'variant_attributes' => 'array',
        'min_price' => 'decimal:2',
        'max_price' => 'decimal:2',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['badge_status', 'formatted_price', 'stock_status', 'product_edit_url'];

    /**
     * Get the badge status for the product
     */
    protected function badgeStatus(): Attribute
    {
        return new Attribute(
            get: fn($value, $attributes) => $this->getStatusBadge($attributes['product_status'] ?? 'draft')
        );
    }

    private function getStatusBadge($status)
    {
        $badges = [
            'publish' => '<span class="badge badge-light-success">Published</span>',
            'pending' => '<span class="badge badge-light-warning">Pending</span>',
            'draft' => '<span class="badge badge-light-info">Draft</span>',
            'trash' => '<span class="badge badge-light-danger">Trash</span>',
        ];
        return $badges[$status] ?? '<span class="badge badge-light-secondary">' . ucfirst($status) . '</span>';
    }

    /**
     * Get formatted price information
     */
    protected function formattedPrice(): Attribute
    {
        return new Attribute(
            get: fn($value, $attributes) => [
                'sale' => number_format($attributes['sale_price'] ?? 0, 0, ',', '.') . ' VND',
                'cost' => number_format($attributes['cost_price'] ?? 0, 0, ',', '.') . ' VND',
                'profit' => number_format(($attributes['sale_price'] ?? 0) - ($attributes['cost_price'] ?? 0), 0, ',', '.') . ' VND',
                'margin' => ($attributes['sale_price'] ?? 0) > 0 ?
                    round(((($attributes['sale_price'] ?? 0) - ($attributes['cost_price'] ?? 0)) / ($attributes['sale_price'] ?? 0)) * 100, 2) . '%' : '0%'
            ]
        );
    }

    /**
     * Get stock status
     */
    protected function stockStatus(): Attribute
    {
        return new Attribute(
            get: fn($value, $attributes) => $this->getStockStatusInfo($this->stock_quantity ?? 0, $attributes['reorder_point'] ?? 0)
        );
    }

    /**
     * Get product edit URL
     */
    protected function productEditUrl(): Attribute
    {
        return new Attribute(
            get: fn($value, $attributes) => route('admin.products.edit', ['id' => $attributes['id']]),
        );
    }

    /**
     * Get the user who created this product
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user');
    }

    /**
     * Get the category for this product.
     */
    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    /**
     * Get the user who last updated this product
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by_user');
    }

    /**
     * Get all inventory transactions for this product
     */
    public function inventoryTransactions()
    {
        return $this->hasMany(InventoryTransaction::class)->orderBy('created_at', 'desc');
    }



    /**
     * Get the inventory record for this product
     */
    public function inventory()
    {
        return $this->hasOne(Inventory::class);
    }

    /**
     * Get all marketplace links for this product
     */
    public function marketplaceLinks()
    {
        return $this->hasMany(MarketplaceProductLink::class);
    }

    /**
     * Get all variants for this product
     */
    public function variants()
    {
        return $this->hasMany(ProductVariant::class, 'parent_product_id')->ordered();
    }

    /**
     * Get active variants for this product
     */
    public function activeVariants()
    {
        return $this->hasMany(ProductVariant::class, 'parent_product_id')->active()->ordered();
    }

    /**
     * Get the default variant for this product
     */
    public function defaultVariant()
    {
        return $this->hasOne(ProductVariant::class, 'parent_product_id')->where('is_default', true);
    }

    /**
     * Get Shopee links for this product
     */
    public function shopeeLinks()
    {
        return $this->marketplaceLinks()->where('platform', MarketplaceProductLink::PLATFORM_SHOPEE);
    }

    /**
     * Get active marketplace links
     */
    public function activeMarketplaceLinks()
    {
        return $this->marketplaceLinks()->where('status', MarketplaceProductLink::STATUS_ACTIVE);
    }

    /**
     * Check if product is linked to a specific platform
     */
    public function isLinkedToPlatform(string $platform): bool
    {
        return $this->marketplaceLinks()
            ->where('platform', $platform)
            ->where('status', MarketplaceProductLink::STATUS_ACTIVE)
            ->exists();
    }

    /**
     * Get marketplace link for specific platform
     */
    public function getMarketplaceLink(string $platform): ?MarketplaceProductLink
    {
        return $this->marketplaceLinks()
            ->where('platform', $platform)
            ->where('status', MarketplaceProductLink::STATUS_ACTIVE)
            ->first();
    }

    /**
     * Scope a query to only include published products
     */
    public function scopePublished($query)
    {
        return $query->where('product_status', 'publish');
    }

    /**
     * Scope a query to only include featured products
     */
    public function scopeFeatured($query)
    {
        return $query->where('product_feature', true);
    }

    /**
     * Scope a query to filter by product type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('product_type', $type);
    }

    /**
     * Scope a query to filter by brand
     */
    public function scopeOfBrand($query, $brand)
    {
        return $query->where('brand', $brand);
    }

    /**
     * Scope for products with variants
     */
    public function scopeWithVariants($query)
    {
        return $query->where('has_variants', true);
    }

    /**
     * Scope for simple products (no variants)
     */
    public function scopeSimple($query)
    {
        return $query->where('has_variants', false);
    }

    /**
     * Check if product is low on stock
     */
    public function isLowStock()
    {
        return ($this->stock_quantity ?? 0) <= $this->reorder_point;
    }

    /**
     * Check if product is out of stock
     */
    public function isOutOfStock()
    {
        return ($this->stock_quantity ?? 0) <= 0;
    }

    /**
     * Get profit margin percentage
     */
    public function getProfitMargin()
    {
        if ($this->sale_price > 0) {
            return round((($this->sale_price - $this->cost_price) / $this->sale_price) * 100, 2);
        }
        return 0;
    }

    /**
     * Get stock quantity from inventory relationship
     */
    public function getStockQuantityAttribute()
    {
        // First try to get from inventory relationship
        if ($this->relationLoaded('inventory') && $this->inventory) {
            return $this->inventory->quantity;
        }

        // If relationship not loaded, query it
        $inventory = $this->inventory()->first();
        return $inventory ? $inventory->quantity : 0;
    }

    /**
     * Get available quantity (stock - reserved)
     */
    public function getAvailableQuantity()
    {
        $stockQuantity = $this->stock_quantity ?? 0;
        $reservedQuantity = $this->getAttribute('reserved_quantity') ?? 0;

        return max(0, $stockQuantity - $reservedQuantity);
    }

    /**
     * Check if product can be ordered with given quantity
     */
    public function canOrder($quantity = 1)
    {
        // Always track inventory now - removed track_inventory column
        return $this->getAvailableQuantity() >= $quantity;
    }

    /**
     * Reserve stock for an order
     */
    public function reserveStock($quantity, $reference = null)
    {
        // Always track inventory now - removed track_inventory column
        if ($this->getAvailableQuantity() < $quantity) {
            return false;
        }

        $this->increment('reserved_quantity', $quantity);

        // Create inventory transaction
        $this->createInventoryTransaction([
            'transaction_type' => InventoryTransaction::TYPE_RESERVATION,
            'quantity' => -$quantity,
            'reference' => $reference,
            'notes' => "Reserved {$quantity} units"
        ]);

        return true;
    }

    /**
     * Release reserved stock
     */
    public function releaseStock($quantity, $reference = null)
    {
        // Always track inventory now - removed track_inventory column
        $releaseQuantity = min($quantity, $this->reserved_quantity ?? 0);
        $this->decrement('reserved_quantity', $releaseQuantity);

        // Create inventory transaction
        $this->createInventoryTransaction([
            'transaction_type' => InventoryTransaction::TYPE_RELEASE,
            'quantity' => $releaseQuantity,
            'reference' => $reference,
            'notes' => "Released {$releaseQuantity} units from reservation"
        ]);

        return true;
    }

    /**
     * Adjust stock quantity
     */
    public function adjustStock($newQuantity, $reason = 'Manual adjustment', $reference = null)
    {
        $oldQuantity = $this->stock_quantity ?? 0;
        $change = $newQuantity - $oldQuantity;

        // Update inventory table
        Inventory::updateProductQuantity($this->id, $newQuantity);

        // Update last stock update timestamp
        $this->update(['last_stock_update' => now()]);

        // Create inventory transaction
        $this->createInventoryTransaction([
            'transaction_type' => InventoryTransaction::TYPE_ADJUSTMENT,
            'quantity' => $change,
            'reference' => $reference,
            'notes' => $reason
        ]);

        // Check for alerts
        $this->checkStockAlerts();

        return true;
    }

    /**
     * Add stock (purchase, return, etc.)
     */
    public function addStock($quantity, $type = InventoryTransaction::TYPE_PURCHASE, $unitCost = null, $reference = null, $notes = null)
    {
        // Add to inventory table
        Inventory::addProductQuantity($this->id, $quantity);

        // Update last stock update timestamp
        $this->update(['last_stock_update' => now()]);

        // Create inventory transaction
        $this->createInventoryTransaction([
            'transaction_type' => $type,
            'quantity' => $quantity,
            'unit_cost' => $unitCost ?? $this->cost_price,
            'reference' => $reference,
            'notes' => $notes ?? "Added {$quantity} units via {$type}"
        ]);

        // Check for alerts
        $this->checkStockAlerts();

        return true;
    }

    /**
     * Remove stock (sale, damage, etc.)
     */
    public function removeStock($quantity, $type = InventoryTransaction::TYPE_SALE, $reference = null, $notes = null)
    {
        // Always track inventory now - removed track_inventory column

        // Remove from inventory table
        Inventory::removeProductQuantity($this->id, $quantity);

        // Create inventory transaction
        $this->createInventoryTransaction([
            'transaction_type' => $type,
            'quantity' => -$quantity,
            'reference' => $reference,
            'notes' => $notes ?? "Removed {$quantity} units via {$type}"
        ]);

        // Check for alerts
        $this->checkStockAlerts();

        return true;
    }

    /**
     * Create an inventory transaction
     */
    protected function createInventoryTransaction($data)
    {
        $quantityBefore = $this->getOriginal('stock_quantity') ?? 0;
        $quantityAfter = $this->stock_quantity ?? 0;

        return InventoryTransaction::create([
            'product_id' => $this->id,
            'transaction_type' => $data['transaction_type'],
            'old_quantity' => $quantityBefore,
            'quantity' => $data['quantity'],
            'new_quantity' => $quantityAfter,
            'unit_cost' => $data['unit_cost'] ?? $this->cost_price,
            'total_value' => ($data['unit_cost'] ?? $this->cost_price) * abs($data['quantity']),
            'reference_type' => $data['reference'] ? get_class($data['reference']) : null,
            'reference_id' => $data['reference'] ? $data['reference']->id : null,
            'notes' => $data['notes'] ?? '',
            'created_by_user' => auth()->id() ?? 1,
        ]);
    }



    /**
     * Get inventory turnover rate (sales per period)
     */
    public function getInventoryTurnover($days = 30)
    {
        $salesTransactions = $this->inventoryTransactions()
            ->where('transaction_type', InventoryTransaction::TYPE_SALE)
            ->where('created_at', '>=', now()->subDays($days))
            ->sum('quantity');

        $averageInventory = $this->stock_quantity ?? 0;

        if ($averageInventory > 0) {
            return abs($salesTransactions) / $averageInventory;
        }

        return 0;
    }

    /**
     * Get days of inventory remaining
     */
    public function getDaysOfInventory($days = 30)
    {
        $turnover = $this->getInventoryTurnover($days);

        if ($turnover > 0) {
            return round($days / $turnover, 1);
        }

        return 999; // Effectively infinite
    }

    /**
     * Get inventory value
     */
    public function getInventoryValue()
    {
        return ($this->stock_quantity ?? 0) * $this->cost_price;
    }

    /**
     * Check if product needs reordering
     */
    public function needsReordering()
    {
        // Always track inventory now - removed track_inventory column
        return ($this->stock_quantity ?? 0) <= $this->reorder_point;
    }

    /**
     * Get suggested reorder quantity
     */
    public function getSuggestedReorderQuantity($days = 30)
    {
        $turnover = $this->getInventoryTurnover($days);
        $leadTimeDays = $this->lead_time_days ?? 7;

        // Calculate quantity needed for lead time + safety stock
        $leadTimeQuantity = $turnover * $leadTimeDays;
        $safetyStock = $this->reorder_point ?? 0;

        return max(1, ceil($leadTimeQuantity + $safetyStock));
    }

    /**
     * Get physical dimensions as array
     */
    public function getDimensions()
    {
        return [
            'length' => $this->length ?? 0,
            'width' => $this->width ?? 0,
            'height' => $this->height ?? 0,
            'weight' => $this->weight ?? 0,
            'volume' => $this->volume ?? 0,
        ];
    }

    /**
     * Check if product has valid dimensions
     */
    public function hasValidDimensions()
    {
        return ($this->length ?? 0) > 0 &&
               ($this->width ?? 0) > 0 &&
               ($this->height ?? 0) > 0;
    }

    /**
     * Check if product is variable (has variants)
     */
    public function isVariable()
    {
        return $this->product_type === 'variable' && $this->has_variants;
    }

    /**
     * Check if product is simple (no variants)
     */
    public function isSimple()
    {
        return $this->product_type === 'simple' || !$this->has_variants;
    }

    /**
     * Get price range for variable products
     */
    public function getPriceRange()
    {
        if (!$this->isVariable()) {
            return [
                'min' => $this->sale_price,
                'max' => $this->sale_price,
                'formatted' => number_format($this->sale_price, 0, ',', '.') . ' VND'
            ];
        }

        $minPrice = $this->min_price ?? $this->sale_price;
        $maxPrice = $this->max_price ?? $this->sale_price;

        if ($minPrice == $maxPrice) {
            return [
                'min' => $minPrice,
                'max' => $maxPrice,
                'formatted' => number_format($minPrice, 0, ',', '.') . ' VND'
            ];
        }

        return [
            'min' => $minPrice,
            'max' => $maxPrice,
            'formatted' => number_format($minPrice, 0, ',', '.') . ' - ' . number_format($maxPrice, 0, ',', '.') . ' VND'
        ];
    }

    /**
     * Get variant attributes used by this product
     */
    public function getVariantAttributes()
    {
        if (!$this->isVariable()) {
            return collect();
        }

        return ProductAttribute::whereIn('id', $this->variant_attributes ?? [])
                              ->with('values')
                              ->ordered()
                              ->get();
    }

    /**
     * Update variant statistics
     */
    public function updateVariantStats()
    {
        if (!$this->isVariable()) {
            return;
        }

        $variants = $this->activeVariants;

        $this->update([
            'variants_count' => $variants->count(),
            'min_price' => $variants->min('sale_price'),
            'max_price' => $variants->max('sale_price')
        ]);
    }

    /**
     * Get detailed stock status information
     */
    public function getStockStatusInfo($stockQuantity = null, $reorderPoint = null)
    {
        $stockQuantity = $stockQuantity ?? $this->stock_quantity ?? 0;
        $reorderPoint = $reorderPoint ?? $this->reorder_point ?? 0;

        // Determine stock status
        if ($stockQuantity <= 0) {
            $status = 'out_of_stock';
            $label = __('product.out_of_stock');
            $class = 'danger';
            $icon = 'cross-circle';
            $color = '#F1416C';
        } elseif ($stockQuantity <= $reorderPoint) {
            $status = 'low_stock';
            $label = __('product.low_stock');
            $class = 'warning';
            $icon = 'warning-2';
            $color = '#FFC700';
        } elseif ($stockQuantity <= ($reorderPoint * 2)) {
            $status = 'medium_stock';
            $label = __('product.medium_stock');
            $class = 'info';
            $icon = 'information-5';
            $color = '#7239EA';
        } else {
            $status = 'in_stock';
            $label = __('product.in_stock');
            $class = 'success';
            $icon = 'check-circle';
            $color = '#50CD89';
        }

        // Calculate stock level percentage
        $maxStock = max($reorderPoint * 3, 100); // Assume max stock is 3x reorder point or 100
        $percentage = $maxStock > 0 ? min(100, ($stockQuantity / $maxStock) * 100) : 0;

        // Determine urgency level
        $urgency = 'normal';
        if ($stockQuantity <= 0) {
            $urgency = 'critical';
        } elseif ($stockQuantity <= $reorderPoint) {
            $urgency = 'high';
        } elseif ($stockQuantity <= ($reorderPoint * 1.5)) {
            $urgency = 'medium';
        }

        // Calculate days until out of stock (based on average daily sales)
        $daysUntilOutOfStock = $this->calculateDaysUntilOutOfStock($stockQuantity);

        // Get reorder suggestion
        $reorderSuggestion = $this->getReorderSuggestion($stockQuantity, $reorderPoint);

        return [
            'status' => $status,
            'label' => $label,
            'class' => $class,
            'icon' => $icon,
            'color' => $color,
            'quantity' => $stockQuantity,
            'reorder_point' => $reorderPoint,
            'percentage' => round($percentage, 1),
            'urgency' => $urgency,
            'days_until_out_of_stock' => $daysUntilOutOfStock,
            'reorder_suggestion' => $reorderSuggestion,
            'badge_html' => $this->getStockStatusBadge($status, $label, $class, $stockQuantity),
            'progress_html' => $this->getStockProgressBar($percentage, $class, $color),
            'alert_message' => $this->getStockAlertMessage($status, $stockQuantity, $daysUntilOutOfStock)
        ];
    }

    /**
     * Calculate days until out of stock based on sales velocity
     */
    private function calculateDaysUntilOutOfStock($stockQuantity)
    {
        if ($stockQuantity <= 0) {
            return 0;
        }

        // Get average daily sales over last 30 days
        $salesLast30Days = $this->inventoryTransactions()
            ->where('transaction_type', 'sale')
            ->where('created_at', '>=', now()->subDays(30))
            ->sum('quantity');

        $averageDailySales = abs($salesLast30Days) / 30;

        if ($averageDailySales > 0) {
            return round($stockQuantity / $averageDailySales, 1);
        }

        return 999; // No sales data, assume long time
    }

    /**
     * Get reorder suggestion
     */
    private function getReorderSuggestion($stockQuantity, $reorderPoint)
    {
        if ($stockQuantity <= $reorderPoint) {
            $suggestedQuantity = $this->getSuggestedReorderQuantity();
            return [
                'should_reorder' => true,
                'suggested_quantity' => $suggestedQuantity,
                'message' => __('product.reorder_suggestion', [
                    'quantity' => $suggestedQuantity,
                    'current' => $stockQuantity,
                    'reorder_point' => $reorderPoint
                ])
            ];
        }

        return [
            'should_reorder' => false,
            'suggested_quantity' => 0,
            'message' => __('product.stock_sufficient')
        ];
    }

    /**
     * Get stock status badge HTML
     */
    private function getStockStatusBadge($status, $label, $class, $quantity)
    {
        $iconMap = [
            'out_of_stock' => 'cross-circle',
            'low_stock' => 'warning-2',
            'medium_stock' => 'information-5',
            'in_stock' => 'check-circle'
        ];

        $icon = $iconMap[$status] ?? 'information';

        return sprintf(
            '<span class="badge badge-light-%s d-inline-flex align-items-center">
                <i class="ki-duotone ki-%s fs-7 me-1">
                    <span class="path1"></span>
                    <span class="path2"></span>
                </i>
                %s (%d)
            </span>',
            $class,
            $icon,
            $label,
            $quantity
        );
    }

    /**
     * Get stock progress bar HTML
     */
    private function getStockProgressBar($percentage, $class, $color)
    {
        return sprintf(
            '<div class="progress h-6px w-100">
                <div class="progress-bar bg-%s" role="progressbar" style="width: %s%%; background-color: %s !important;"
                     aria-valuenow="%s" aria-valuemin="0" aria-valuemax="100"></div>
            </div>',
            $class,
            $percentage,
            $color,
            $percentage
        );
    }

    /**
     * Get stock alert message
     */
    private function getStockAlertMessage($status, $quantity, $daysUntilOutOfStock)
    {
        switch ($status) {
            case 'out_of_stock':
                return __('product.alert_out_of_stock');

            case 'low_stock':
                if ($daysUntilOutOfStock <= 7) {
                    return __('product.alert_critical_low_stock', ['days' => $daysUntilOutOfStock]);
                }
                return __('product.alert_low_stock', ['quantity' => $quantity]);

            case 'medium_stock':
                return __('product.alert_medium_stock', ['days' => $daysUntilOutOfStock]);

            default:
                return __('product.alert_stock_good');
        }
    }

    /**
     * Check stock alerts and create notifications if needed
     */
    private function checkStockAlerts()
    {
        $stockInfo = $this->getStockStatusInfo();

        // Create notification for critical stock levels
        if (in_array($stockInfo['urgency'], ['critical', 'high'])) {
            $this->createNotification(
                'stock_alert',
                __('product.stock_alert_title'),
                $stockInfo['alert_message'],
                [
                    'product_id' => $this->id,
                    'product_name' => $this->product_name,
                    'stock_quantity' => $stockInfo['quantity'],
                    'reorder_point' => $stockInfo['reorder_point'],
                    'urgency' => $stockInfo['urgency']
                ],
                [
                    'action_url' => route('admin.products.edit', $this->id)
                ]
            );
        }
    }

}
