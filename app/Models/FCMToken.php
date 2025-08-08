<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FCMToken extends Model
{
    use HasFactory;

    protected $table = 'fcm_tokens';

    protected $fillable = [
        'user_id',
        'token',
        'device_type',
        'device_id',
        'device_name',
        'app_version',
        'platform_version',
        'is_active',
        'last_used_at',
        'metadata'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the FCM token.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get active tokens only.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get tokens by device type.
     */
    public function scopeByDeviceType($query, $deviceType)
    {
        return $query->where('device_type', $deviceType);
    }

    /**
     * Scope to get tokens for specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Update last used timestamp.
     */
    public function updateLastUsed()
    {
        $this->update(['last_used_at' => now()]);
    }

    /**
     * Deactivate token.
     */
    public function deactivate()
    {
        $this->update(['is_active' => false]);
    }

    /**
     * Register or update FCM token for user.
     */
    public static function registerToken($userId, $token, $deviceData = [])
    {
        $deviceId = $deviceData['device_id'] ?? null;

        // If device_id provided, update existing or create new
        if ($deviceId) {
            $fcmToken = self::updateOrCreate(
                [
                    'user_id' => $userId,
                    'device_id' => $deviceId
                ],
                [
                    'token' => $token,
                    'device_type' => $deviceData['device_type'] ?? 'android',
                    'device_name' => $deviceData['device_name'] ?? null,
                    'app_version' => $deviceData['app_version'] ?? null,
                    'platform_version' => $deviceData['platform_version'] ?? null,
                    'is_active' => true,
                    'last_used_at' => now(),
                    'metadata' => $deviceData['metadata'] ?? null
                ]
            );
        } else {
            // No device_id, check if token already exists
            $existingToken = self::where('token', $token)->first();
            
            if ($existingToken) {
                // Update existing token
                $existingToken->update([
                    'user_id' => $userId,
                    'device_type' => $deviceData['device_type'] ?? $existingToken->device_type,
                    'device_name' => $deviceData['device_name'] ?? $existingToken->device_name,
                    'app_version' => $deviceData['app_version'] ?? $existingToken->app_version,
                    'platform_version' => $deviceData['platform_version'] ?? $existingToken->platform_version,
                    'is_active' => true,
                    'last_used_at' => now(),
                    'metadata' => $deviceData['metadata'] ?? $existingToken->metadata
                ]);
                $fcmToken = $existingToken;
            } else {
                // Create new token
                $fcmToken = self::create([
                    'user_id' => $userId,
                    'token' => $token,
                    'device_type' => $deviceData['device_type'] ?? 'android',
                    'device_name' => $deviceData['device_name'] ?? null,
                    'app_version' => $deviceData['app_version'] ?? null,
                    'platform_version' => $deviceData['platform_version'] ?? null,
                    'is_active' => true,
                    'last_used_at' => now(),
                    'metadata' => $deviceData['metadata'] ?? null
                ]);
            }
        }

        return $fcmToken;
    }

    /**
     * Get active tokens for user.
     */
    public static function getActiveTokensForUser($userId)
    {
        return self::where('user_id', $userId)
            ->where('is_active', true)
            ->pluck('token')
            ->toArray();
    }

    /**
     * Get active tokens for multiple users.
     */
    public static function getActiveTokensForUsers($userIds)
    {
        return self::whereIn('user_id', $userIds)
            ->where('is_active', true)
            ->pluck('token')
            ->toArray();
    }

    /**
     * Get all active tokens.
     */
    public static function getAllActiveTokens()
    {
        return self::where('is_active', true)
            ->pluck('token')
            ->toArray();
    }

    /**
     * Clean up inactive tokens (older than 30 days).
     */
    public static function cleanupInactiveTokens()
    {
        return self::where('is_active', false)
            ->where('updated_at', '<', now()->subDays(30))
            ->delete();
    }

    /**
     * Get statistics.
     */
    public static function getStatistics()
    {
        return [
            'total_tokens' => self::count(),
            'active_tokens' => self::where('is_active', true)->count(),
            'inactive_tokens' => self::where('is_active', false)->count(),
            'by_device_type' => self::where('is_active', true)
                ->groupBy('device_type')
                ->selectRaw('device_type, count(*) as count')
                ->pluck('count', 'device_type')
                ->toArray(),
            'recent_registrations' => self::where('created_at', '>=', now()->subDays(7))->count(),
        ];
    }
}
