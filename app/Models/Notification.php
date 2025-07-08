<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Notification extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'type',
        'notifiable_type',
        'notifiable_id',
        'title',
        'message',
        'data',
        'priority',
        'channels',
        'read_at',
        'expires_at',
        'action_url',
        'action_text',
        'icon',
        'color',
        'is_dismissible',
        'created_by'
    ];

    protected $casts = [
        'id' => 'string',
        'data' => 'array',
        'channels' => 'array',
        'read_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_dismissible' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The attributes that should be filled with default values.
     */
    protected $attributes = [
        'priority' => 'normal',
    ];

    /**
     * Boot method to generate UUID.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($notification) {
            if (empty($notification->id)) {
                $notification->id = (string) Str::uuid();
            }
            if (empty($notification->created_by)) {
                $notification->created_by = auth()->id();
            }
            if (empty($notification->channels)) {
                $notification->channels = ['web'];
            }
        });
    }

    /**
     * Get the notifiable entity that the notification belongs to.
     */
    public function notifiable()
    {
        return $this->morphTo();
    }

    /**
     * Get the user who created the notification.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if notification is read.
     */
    protected function isRead(): Attribute
    {
        return new Attribute(
            get: fn($value, $attributes) => !is_null($attributes['read_at'])
        );
    }

    /**
     * Check if notification is expired.
     */
    protected function isExpired(): Attribute
    {
        return new Attribute(
            get: function($value, $attributes) {
                if (is_null($attributes['expires_at'])) {
                    return false;
                }
                return Carbon::parse($attributes['expires_at'])->isPast();
            }
        );
    }

    /**
     * Get formatted time ago.
     */
    protected function timeAgo(): Attribute
    {
        return new Attribute(
            get: fn($value, $attributes) => Carbon::parse($attributes['created_at'])->diffForHumans()
        );
    }

    /**
     * Get priority badge HTML.
     */
    protected function priorityBadge(): Attribute
    {
        return new Attribute(
            get: function($value, $attributes) {
                $priority = $attributes['priority'] ?? 'normal';
                return match($priority) {
                    'low' => '<span class="badge badge-light-secondary">Thấp</span>',
                    'normal' => '<span class="badge badge-light-primary">Bình thường</span>',
                    'high' => '<span class="badge badge-light-warning">Cao</span>',
                    'urgent' => '<span class="badge badge-light-danger">Khẩn cấp</span>',
                    default => '<span class="badge badge-light-secondary">Không xác định</span>',
                };
            }
        );
    }

    /**
     * Get type display name.
     */
    protected function typeDisplay(): Attribute
    {
        return new Attribute(
            get: function($value, $attributes) {
                $type = $attributes['type'] ?? '';
                return match($type) {
                    'order' => 'Đơn hàng',
                    'invoice' => 'Hóa đơn',
                    'inventory' => 'Tồn kho',
                    'system' => 'Hệ thống',
                    'user' => 'Người dùng',
                    default => ucfirst($type),
                };
            }
        );
    }

    /**
     * Get type icon.
     */
    protected function typeIcon(): Attribute
    {
        return new Attribute(
            get: function($value, $attributes) {
                $type = $attributes['type'] ?? '';
                return match($type) {
                    'order' => 'fas fa-shopping-cart',
                    'invoice' => 'fas fa-file-invoice',
                    'inventory' => 'fas fa-boxes',
                    'system' => 'fas fa-cog',
                    'user' => 'fas fa-user',
                    default => 'fas fa-bell',
                };
            }
        );
    }

    /**
     * Get type color.
     */
    protected function typeColor(): Attribute
    {
        return new Attribute(
            get: function($value, $attributes) {
                $type = $attributes['type'] ?? '';
                return match($type) {
                    'order' => 'primary',
                    'invoice' => 'success',
                    'inventory' => 'warning',
                    'system' => 'info',
                    'user' => 'secondary',
                    default => 'light',
                };
            }
        );
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead()
    {
        if (is_null($this->read_at)) {
            $this->update(['read_at' => now()]);
        }
        return $this;
    }

    /**
     * Mark notification as unread.
     */
    public function markAsUnread()
    {
        $this->update(['read_at' => null]);
        return $this;
    }

    /**
     * Scope for unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope for read notifications.
     */
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Scope for active (not expired) notifications.
     */
    public function scopeActive($query)
    {
        return $query->where(function($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Scope for expired notifications.
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('expires_at')
                    ->where('expires_at', '<=', now());
    }

    /**
     * Scope for specific type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for specific priority.
     */
    public function scopeWithPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope for notifications sent via specific channel.
     */
    public function scopeViaChannel($query, $channel)
    {
        return $query->whereJsonContains('channels', $channel);
    }

    /**
     * Scope for user notifications.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('notifiable_type', User::class)
                    ->where('notifiable_id', $userId);
    }

    /**
     * Scope for recent notifications.
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Get notification statistics.
     */
    public static function getStatistics($userId = null, $filters = [])
    {
        $query = self::query();

        if ($userId) {
            $query->forUser($userId);
        }

        // Apply filters
        if (isset($filters['type'])) {
            $query->ofType($filters['type']);
        }
        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return [
            'total' => $query->count(),
            'unread' => (clone $query)->unread()->count(),
            'read' => (clone $query)->read()->count(),
            'expired' => (clone $query)->expired()->count(),
            'by_type' => (clone $query)->selectRaw('type, count(*) as count')
                                     ->groupBy('type')
                                     ->pluck('count', 'type')
                                     ->toArray(),
            'by_priority' => (clone $query)->selectRaw('priority, count(*) as count')
                                           ->groupBy('priority')
                                           ->pluck('count', 'priority')
                                           ->toArray(),
        ];
    }

    /**
     * Clean up expired notifications.
     */
    public static function cleanupExpired()
    {
        return self::expired()->delete();
    }

    /**
     * Clean up old notifications.
     */
    public static function cleanupOld($days = 30)
    {
        return self::where('created_at', '<', now()->subDays($days))->delete();
    }

    /**
     * Create notification for user.
     */
    public static function createForUser($user, $type, $title, $message, $data = [], $options = [])
    {
        return self::create([
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'priority' => $options['priority'] ?? 'normal',
            'channels' => $options['channels'] ?? ['web'],
            'expires_at' => $options['expires_at'] ?? null,
        ]);
    }
}
