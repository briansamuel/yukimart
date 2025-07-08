<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketplaceProductLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'platform',
        'marketplace_item_id',
        'sku',
        'name',
        'image_url',
        'shop_name',
        'shop_id',
        'price',
        'stock_quantity',
        'status',
        'platform_data',
        'last_synced_at'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'platform_data' => 'array',
        'last_synced_at' => 'datetime'
    ];

    // Platform constants
    const PLATFORM_SHOPEE = 'shopee';
    const PLATFORM_TIKI = 'tiki';
    const PLATFORM_LAZADA = 'lazada';
    const PLATFORM_SENDO = 'sendo';
    const PLATFORM_TIKTOK = 'tiktok';

    // Status constants
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_DELETED = 'deleted';

    /**
     * Get the product that owns this link
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get platform display name
     */
    public function getPlatformNameAttribute(): string
    {
        return match($this->platform) {
            self::PLATFORM_SHOPEE => 'Shopee',
            self::PLATFORM_TIKI => 'Tiki',
            self::PLATFORM_LAZADA => 'Lazada',
            self::PLATFORM_SENDO => 'Sendo',
            self::PLATFORM_TIKTOK => 'TikTok Shop',
            default => ucfirst($this->platform)
        };
    }

    /**
     * Get platform color for UI
     */
    public function getPlatformColorAttribute(): string
    {
        return match($this->platform) {
            self::PLATFORM_SHOPEE => '#ee4d2d',
            self::PLATFORM_TIKI => '#0073e6',
            self::PLATFORM_LAZADA => '#ff6600',
            self::PLATFORM_SENDO => '#ed3757',
            self::PLATFORM_TIKTOK => '#000000',
            default => '#6c757d'
        };
    }

    /**
     * Get marketplace product URL
     */
    public function getMarketplaceUrlAttribute(): ?string
    {
        return match($this->platform) {
            self::PLATFORM_SHOPEE => "https://shopee.vn/product/{$this->shop_id}/{$this->marketplace_item_id}",
            self::PLATFORM_TIKI => "https://tiki.vn/product-p{$this->marketplace_item_id}.html",
            self::PLATFORM_LAZADA => "https://www.lazada.vn/products/i{$this->marketplace_item_id}.html",
            self::PLATFORM_SENDO => "https://www.sendo.vn/san-pham/{$this->marketplace_item_id}",
            default => null
        };
    }

    /**
     * Check if link needs sync (older than 1 hour)
     */
    public function needsSync(): bool
    {
        return !$this->last_synced_at || $this->last_synced_at->diffInHours(now()) >= 1;
    }

    /**
     * Mark as synced
     */
    public function markAsSynced(): void
    {
        $this->update(['last_synced_at' => now()]);
    }

    /**
     * Scope for specific platform
     */
    public function scopePlatform($query, string $platform)
    {
        return $query->where('platform', $platform);
    }

    /**
     * Scope for active links
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope for Shopee links
     */
    public function scopeShopee($query)
    {
        return $query->platform(self::PLATFORM_SHOPEE);
    }

    /**
     * Scope for links that need sync
     */
    public function scopeNeedsSync($query)
    {
        return $query->where(function($q) {
            $q->whereNull('last_synced_at')
              ->orWhere('last_synced_at', '<', now()->subHour());
        });
    }
}
