<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'notification_type',
        'channels',
        'is_enabled',
        'quiet_hours_start',
        'quiet_hours_end',
        'quiet_days',
    ];

    protected $casts = [
        'channels' => 'array',
        'quiet_days' => 'array',
        'is_enabled' => 'boolean',
        'quiet_hours_start' => 'datetime:H:i',
        'quiet_hours_end' => 'datetime:H:i',
    ];

    /**
     * The attributes that should be filled with default values.
     */
    protected $attributes = [
        'is_enabled' => true,
    ];

    /**
     * Boot method to set default values.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($setting) {
            if (empty($setting->channels)) {
                $setting->channels = ['web'];
            }
        });
    }

    /**
     * Get the user that owns the notification setting.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for enabled settings.
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    /**
     * Scope for disabled settings.
     */
    public function scopeDisabled($query)
    {
        return $query->where('is_enabled', false);
    }

    /**
     * Scope for specific notification type.
     */
    public function scopeForType($query, $type)
    {
        return $query->where('notification_type', $type);
    }

    /**
     * Scope for specific channel.
     */
    public function scopeForChannel($query, $channel)
    {
        return $query->whereJsonContains('channels', $channel);
    }

    /**
     * Check if user should receive notification at current time.
     */
    public function shouldReceiveNotification()
    {
        if (!$this->is_enabled) {
            return false;
        }

        // Check quiet hours
        if ($this->quiet_hours_start && $this->quiet_hours_end) {
            $now = now()->format('H:i');
            $start = $this->quiet_hours_start;
            $end = $this->quiet_hours_end;

            if ($start <= $end) {
                // Same day range
                if ($now >= $start && $now <= $end) {
                    return false;
                }
            } else {
                // Overnight range
                if ($now >= $start || $now <= $end) {
                    return false;
                }
            }
        }

        // Check quiet days
        if ($this->quiet_days && in_array(now()->dayOfWeek, $this->quiet_days)) {
            return false;
        }

        return true;
    }

    /**
     * Get default settings for a user.
     */
    public static function getDefaultSettings($userId)
    {
        return [
            [
                'user_id' => $userId,
                'notification_type' => 'order_created',
                'channels' => ['web', 'email'],
                'is_enabled' => true,
            ],
            [
                'user_id' => $userId,
                'notification_type' => 'order_completed',
                'channels' => ['web'],
                'is_enabled' => true,
            ],
            [
                'user_id' => $userId,
                'notification_type' => 'invoice_overdue',
                'channels' => ['web', 'email'],
                'is_enabled' => true,
            ],
            [
                'user_id' => $userId,
                'notification_type' => 'inventory_low',
                'channels' => ['web'],
                'is_enabled' => true,
            ],
            [
                'user_id' => $userId,
                'notification_type' => 'system_maintenance',
                'channels' => ['web', 'email'],
                'is_enabled' => true,
            ],
        ];
    }

    /**
     * Create default settings for a user.
     */
    public static function createDefaultForUser($userId)
    {
        $defaultSettings = self::getDefaultSettings($userId);
        
        foreach ($defaultSettings as $setting) {
            self::firstOrCreate(
                [
                    'user_id' => $setting['user_id'],
                    'notification_type' => $setting['notification_type'],
                ],
                $setting
            );
        }
    }
}
