<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class UserSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'key',
        'value',
        'type',
        'description',
        'is_public',
        'is_cacheable',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'is_cacheable' => 'boolean',
    ];

    /**
     * Boot method to handle caching.
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($setting) {
            if ($setting->is_cacheable) {
                $setting->clearCache();
            }
        });

        static::deleted(function ($setting) {
            if ($setting->is_cacheable) {
                $setting->clearCache();
            }
        });
    }

    /**
     * Get the user that owns the setting.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the typed value of the setting.
     */
    public function getTypedValueAttribute()
    {
        return $this->castValue($this->value, $this->type);
    }

    /**
     * Set the value with automatic type detection.
     */
    public function setTypedValue($value)
    {
        $this->type = $this->detectType($value);
        $this->value = $this->serializeValue($value);
        return $this;
    }

    /**
     * Cast value to appropriate type.
     */
    protected function castValue($value, $type)
    {
        if ($value === null) {
            return null;
        }

        return match($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            'float' => (float) $value,
            'json' => json_decode($value, true),
            default => $value,
        };
    }

    /**
     * Detect type from value.
     */
    protected function detectType($value)
    {
        if (is_bool($value)) {
            return 'boolean';
        }
        
        if (is_int($value)) {
            return 'integer';
        }
        
        if (is_float($value)) {
            return 'float';
        }
        
        if (is_array($value) || is_object($value)) {
            return 'json';
        }
        
        return 'string';
    }

    /**
     * Serialize value for storage.
     */
    protected function serializeValue($value)
    {
        if (is_array($value) || is_object($value)) {
            return json_encode($value);
        }
        
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }
        
        return (string) $value;
    }

    /**
     * Get cache key for this setting.
     */
    public function getCacheKey()
    {
        return "user_setting_{$this->user_id}_{$this->key}";
    }

    /**
     * Clear cache for this setting.
     */
    public function clearCache()
    {
        Cache::forget($this->getCacheKey());
        Cache::forget("user_settings_{$this->user_id}");
    }

    /**
     * Scope for public settings.
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope for private settings.
     */
    public function scopePrivate($query)
    {
        return $query->where('is_public', false);
    }

    /**
     * Scope for cacheable settings.
     */
    public function scopeCacheable($query)
    {
        return $query->where('is_cacheable', true);
    }

    /**
     * Scope for specific key.
     */
    public function scopeForKey($query, $key)
    {
        return $query->where('key', $key);
    }

    /**
     * Get default settings for a user.
     */
    public static function getDefaultSettings()
    {
        return [
            'theme' => [
                'value' => 'light',
                'type' => 'string',
                'description' => 'User interface theme',
                'is_public' => false,
                'is_cacheable' => true,
            ],
            'language' => [
                'value' => 'vi',
                'type' => 'string',
                'description' => 'User interface language',
                'is_public' => false,
                'is_cacheable' => true,
            ],
            'timezone' => [
                'value' => 'Asia/Ho_Chi_Minh',
                'type' => 'string',
                'description' => 'User timezone',
                'is_public' => false,
                'is_cacheable' => true,
            ],
            'date_format' => [
                'value' => 'd/m/Y',
                'type' => 'string',
                'description' => 'Preferred date format',
                'is_public' => false,
                'is_cacheable' => true,
            ],
            'time_format' => [
                'value' => 'H:i',
                'type' => 'string',
                'description' => 'Preferred time format',
                'is_public' => false,
                'is_cacheable' => true,
            ],
            'items_per_page' => [
                'value' => 25,
                'type' => 'integer',
                'description' => 'Number of items per page in lists',
                'is_public' => false,
                'is_cacheable' => true,
            ],
            'notifications_enabled' => [
                'value' => true,
                'type' => 'boolean',
                'description' => 'Enable notifications',
                'is_public' => false,
                'is_cacheable' => true,
            ],
            'email_notifications' => [
                'value' => true,
                'type' => 'boolean',
                'description' => 'Enable email notifications',
                'is_public' => false,
                'is_cacheable' => true,
            ],
            'dashboard_widgets' => [
                'value' => ['orders', 'revenue', 'inventory', 'customers'],
                'type' => 'json',
                'description' => 'Enabled dashboard widgets',
                'is_public' => false,
                'is_cacheable' => true,
            ],
            'sidebar_collapsed' => [
                'value' => false,
                'type' => 'boolean',
                'description' => 'Sidebar collapsed state',
                'is_public' => false,
                'is_cacheable' => true,
            ],
        ];
    }

    /**
     * Create default settings for a user.
     */
    public static function createDefaultForUser($userId)
    {
        $defaultSettings = self::getDefaultSettings();
        
        foreach ($defaultSettings as $key => $config) {
            self::firstOrCreate(
                [
                    'user_id' => $userId,
                    'key' => $key,
                ],
                array_merge($config, [
                    'user_id' => $userId,
                    'key' => $key,
                ])
            );
        }
    }
}
