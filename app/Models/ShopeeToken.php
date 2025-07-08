<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class ShopeeToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'access_token',
        'refresh_token',
        'shop_id',
        'partner_id',
        'expired_at',
        'user_id',
        'shop_info',
        'is_active',
        'last_used_at'
    ];

    protected $casts = [
        'expired_at' => 'datetime',
        'last_used_at' => 'datetime',
        'shop_info' => 'array',
        'is_active' => 'boolean'
    ];

    protected $hidden = [
        'access_token',
        'refresh_token'
    ];

    /**
     * Get the user that owns the token
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if token is expired
     */
    public function isExpired(): bool
    {
        return $this->expired_at->isPast();
    }

    /**
     * Check if token will expire soon (within 24 hours)
     */
    public function isExpiringSoon(): bool
    {
        return $this->expired_at->diffInHours(now()) <= 24;
    }

    /**
     * Check if token is valid and active
     */
    public function isValid(): bool
    {
        return $this->is_active && !$this->isExpired();
    }

    /**
     * Update last used timestamp
     */
    public function markAsUsed(): void
    {
        $this->update(['last_used_at' => now()]);
    }

    /**
     * Get shop name from shop_info
     */
    public function getShopNameAttribute(): ?string
    {
        return $this->shop_info['shop_name'] ?? null;
    }

    /**
     * Get shop region from shop_info
     */
    public function getShopRegionAttribute(): ?string
    {
        return $this->shop_info['region'] ?? null;
    }

    /**
     * Scope for active tokens
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for valid tokens (active and not expired)
     */
    public function scopeValid($query)
    {
        return $query->active()->where('expired_at', '>', now());
    }

    /**
     * Scope for expiring soon tokens
     */
    public function scopeExpiringSoon($query)
    {
        return $query->active()->whereBetween('expired_at', [now(), now()->addHours(24)]);
    }
}
