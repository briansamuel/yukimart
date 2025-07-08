<?php

namespace App\Services;

use App\Models\UserSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

class UserSettingService
{
    /**
     * Get a setting value for a user.
     */
    public function get($key, $userId = null, $default = null)
    {
        $userId = $userId ?? Auth::id();
        
        if (!$userId) {
            return $default;
        }

        $cacheKey = "user_setting_{$userId}_{$key}";
        
        return Cache::remember($cacheKey, 3600, function () use ($userId, $key, $default) {
            $setting = UserSetting::where('user_id', $userId)
                                 ->where('key', $key)
                                 ->first();
            
            return $setting ? $setting->typed_value : $default;
        });
    }

    /**
     * Set a setting value for a user.
     */
    public function set($key, $value, $userId = null, $options = [])
    {
        $userId = $userId ?? Auth::id();
        
        if (!$userId) {
            return false;
        }

        $setting = UserSetting::firstOrNew([
            'user_id' => $userId,
            'key' => $key,
        ]);

        $setting->setTypedValue($value);
        
        if (isset($options['description'])) {
            $setting->description = $options['description'];
        }
        
        if (isset($options['is_public'])) {
            $setting->is_public = $options['is_public'];
        }
        
        if (isset($options['is_cacheable'])) {
            $setting->is_cacheable = $options['is_cacheable'];
        }

        return $setting->save();
    }

    /**
     * Get multiple settings for a user.
     */
    public function getMultiple($keys, $userId = null)
    {
        $userId = $userId ?? Auth::id();
        
        if (!$userId) {
            return [];
        }

        $cacheKey = "user_settings_{$userId}_" . md5(implode(',', $keys));
        
        return Cache::remember($cacheKey, 3600, function () use ($userId, $keys) {
            $settings = UserSetting::where('user_id', $userId)
                                  ->whereIn('key', $keys)
                                  ->get()
                                  ->keyBy('key');
            
            $result = [];
            foreach ($keys as $key) {
                $result[$key] = isset($settings[$key]) ? $settings[$key]->typed_value : null;
            }
            
            return $result;
        });
    }

    /**
     * Get all settings for a user.
     */
    public function getAll($userId = null)
    {
        $userId = $userId ?? Auth::id();
        
        if (!$userId) {
            return [];
        }

        $cacheKey = "user_settings_{$userId}";
        
        return Cache::remember($cacheKey, 3600, function () use ($userId) {
            return UserSetting::where('user_id', $userId)
                             ->get()
                             ->mapWithKeys(function ($setting) {
                                 return [$setting->key => $setting->typed_value];
                             })
                             ->toArray();
        });
    }

    /**
     * Set multiple settings for a user.
     */
    public function setMultiple($settings, $userId = null)
    {
        $userId = $userId ?? Auth::id();
        
        if (!$userId) {
            return false;
        }

        $success = true;
        
        foreach ($settings as $key => $value) {
            if (!$this->set($key, $value, $userId)) {
                $success = false;
            }
        }
        
        return $success;
    }

    /**
     * Delete a setting for a user.
     */
    public function delete($key, $userId = null)
    {
        $userId = $userId ?? Auth::id();
        
        if (!$userId) {
            return false;
        }

        $setting = UserSetting::where('user_id', $userId)
                             ->where('key', $key)
                             ->first();
        
        if ($setting) {
            $setting->clearCache();
            return $setting->delete();
        }
        
        return true;
    }

    /**
     * Reset settings to default for a user.
     */
    public function resetToDefault($userId = null)
    {
        $userId = $userId ?? Auth::id();
        
        if (!$userId) {
            return false;
        }

        // Delete all existing settings
        UserSetting::where('user_id', $userId)->delete();
        
        // Clear cache
        Cache::forget("user_settings_{$userId}");
        
        // Create default settings
        UserSetting::createDefaultForUser($userId);
        
        return true;
    }

    /**
     * Get theme setting.
     */
    public function getTheme($userId = null)
    {
        return $this->get('theme', $userId, 'light');
    }

    /**
     * Set theme setting.
     */
    public function setTheme($theme, $userId = null)
    {
        return $this->set('theme', $theme, $userId);
    }

    /**
     * Get language setting.
     */
    public function getLanguage($userId = null)
    {
        return $this->get('language', $userId, 'vi');
    }

    /**
     * Set language setting.
     */
    public function setLanguage($language, $userId = null)
    {
        return $this->set('language', $language, $userId);
    }

    /**
     * Get timezone setting.
     */
    public function getTimezone($userId = null)
    {
        return $this->get('timezone', $userId, 'Asia/Ho_Chi_Minh');
    }

    /**
     * Set timezone setting.
     */
    public function setTimezone($timezone, $userId = null)
    {
        return $this->set('timezone', $timezone, $userId);
    }

    /**
     * Get items per page setting.
     */
    public function getItemsPerPage($userId = null)
    {
        return $this->get('items_per_page', $userId, 25);
    }

    /**
     * Set items per page setting.
     */
    public function setItemsPerPage($itemsPerPage, $userId = null)
    {
        return $this->set('items_per_page', $itemsPerPage, $userId);
    }

    /**
     * Check if notifications are enabled.
     */
    public function isNotificationsEnabled($userId = null)
    {
        return $this->get('notifications_enabled', $userId, true);
    }

    /**
     * Enable/disable notifications.
     */
    public function setNotificationsEnabled($enabled, $userId = null)
    {
        return $this->set('notifications_enabled', $enabled, $userId);
    }

    /**
     * Get dashboard widgets setting.
     */
    public function getDashboardWidgets($userId = null)
    {
        return $this->get('dashboard_widgets', $userId, ['orders', 'revenue', 'inventory', 'customers']);
    }

    /**
     * Set dashboard widgets setting.
     */
    public function setDashboardWidgets($widgets, $userId = null)
    {
        return $this->set('dashboard_widgets', $widgets, $userId);
    }

    /**
     * Get user preferences for UI.
     */
    public function getUIPreferences($userId = null)
    {
        $keys = ['theme', 'language', 'sidebar_collapsed', 'items_per_page'];
        return $this->getMultiple($keys, $userId);
    }

    /**
     * Get user preferences for notifications.
     */
    public function getNotificationPreferences($userId = null)
    {
        $keys = ['notifications_enabled', 'email_notifications'];
        return $this->getMultiple($keys, $userId);
    }

    /**
     * Export user settings.
     */
    public function export($userId = null)
    {
        $userId = $userId ?? Auth::id();
        
        if (!$userId) {
            return [];
        }

        return UserSetting::where('user_id', $userId)
                         ->get()
                         ->map(function ($setting) {
                             return [
                                 'key' => $setting->key,
                                 'value' => $setting->typed_value,
                                 'type' => $setting->type,
                                 'description' => $setting->description,
                             ];
                         })
                         ->toArray();
    }

    /**
     * Import user settings.
     */
    public function import($settings, $userId = null)
    {
        $userId = $userId ?? Auth::id();

        if (!$userId) {
            return false;
        }

        $success = true;

        foreach ($settings as $setting) {
            if (!$this->set(
                $setting['key'],
                $setting['value'],
                $userId,
                ['description' => $setting['description'] ?? null]
            )) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Clear all cache for a user.
     */
    public function clearUserCache($userId = null)
    {
        $userId = $userId ?? Auth::id();

        if (!$userId) {
            return false;
        }

        // Clear all user setting caches
        $settings = UserSetting::where('user_id', $userId)->get();

        foreach ($settings as $setting) {
            $setting->clearCache();
        }

        return true;
    }
}
