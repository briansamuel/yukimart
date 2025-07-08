<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'permission_id',
        'type',
        'assigned_at',
        'assigned_by',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Type constants
     */
    const TYPE_GRANT = 'grant';
    const TYPE_DENY = 'deny';

    /**
     * Get permission types
     */
    public static function getTypes()
    {
        return [
            self::TYPE_GRANT => 'Grant',
            self::TYPE_DENY => 'Deny',
        ];
    }

    /**
     * Relationship with user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship with permission
     */
    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class);
    }

    /**
     * Relationship with assigner
     */
    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Scope for active permissions
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for granted permissions
     */
    public function scopeGranted($query)
    {
        return $query->where('type', self::TYPE_GRANT);
    }

    /**
     * Scope for denied permissions
     */
    public function scopeDenied($query)
    {
        return $query->where('type', self::TYPE_DENY);
    }

    /**
     * Scope for non-expired permissions
     */
    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Scope for expired permissions
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('expires_at')
                    ->where('expires_at', '<=', now());
    }

    /**
     * Check if permission is expired
     */
    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if permission is valid
     */
    public function isValid()
    {
        return $this->is_active && !$this->isExpired();
    }

    /**
     * Check if permission is granted
     */
    public function isGranted()
    {
        return $this->type === self::TYPE_GRANT;
    }

    /**
     * Check if permission is denied
     */
    public function isDenied()
    {
        return $this->type === self::TYPE_DENY;
    }

    /**
     * Get type display name
     */
    public function getTypeDisplayNameAttribute()
    {
        $types = self::getTypes();
        return $types[$this->type] ?? ucfirst($this->type);
    }

    /**
     * Get type badge class
     */
    public function getTypeBadgeClassAttribute()
    {
        return $this->type === self::TYPE_GRANT 
            ? 'badge-light-success' 
            : 'badge-light-danger';
    }

    /**
     * Activate permission
     */
    public function activate()
    {
        $this->update(['is_active' => true]);
        return $this;
    }

    /**
     * Deactivate permission
     */
    public function deactivate()
    {
        $this->update(['is_active' => false]);
        return $this;
    }

    /**
     * Change to grant type
     */
    public function grant()
    {
        $this->update(['type' => self::TYPE_GRANT]);
        return $this;
    }

    /**
     * Change to deny type
     */
    public function deny()
    {
        $this->update(['type' => self::TYPE_DENY]);
        return $this;
    }

    /**
     * Extend expiration date
     */
    public function extendExpiration($date)
    {
        $this->update(['expires_at' => $date]);
        return $this;
    }

    /**
     * Remove expiration
     */
    public function removeExpiration()
    {
        $this->update(['expires_at' => null]);
        return $this;
    }
}
