<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\UserSettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserSettingController extends Controller
{
    protected $userSettingService;

    public function __construct(UserSettingService $userSettingService)
    {
        $this->userSettingService = $userSettingService;
    }

    /**
     * Display user settings page.
     */
    public function index()
    {
        $settings = $this->userSettingService->getAll();
        $uiPreferences = $this->userSettingService->getUIPreferences();
        $notificationPreferences = $this->userSettingService->getNotificationPreferences();
        
        return view('admin.settings.index', compact(
            'settings', 
            'uiPreferences', 
            'notificationPreferences'
        ));
    }

    /**
     * Update user settings.
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'theme' => 'nullable|in:light,dark',
            'language' => 'nullable|in:vi,en',
            'timezone' => 'nullable|string|max:50',
            'date_format' => 'nullable|string|max:20',
            'time_format' => 'nullable|string|max:20',
            'items_per_page' => 'nullable|integer|min:10|max:100',
            'notifications_enabled' => 'nullable|boolean',
            'email_notifications' => 'nullable|boolean',
            'sidebar_collapsed' => 'nullable|boolean',
            'dashboard_widgets' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $settings = $request->only([
            'theme', 'language', 'timezone', 'date_format', 'time_format',
            'items_per_page', 'notifications_enabled', 'email_notifications',
            'sidebar_collapsed', 'dashboard_widgets'
        ]);

        // Remove null values
        $settings = array_filter($settings, function($value) {
            return $value !== null;
        });

        $success = $this->userSettingService->setMultiple($settings);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => __('Settings updated successfully'),
                'settings' => $this->userSettingService->getAll()
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('Failed to update settings')
        ], 500);
    }

    /**
     * Get specific setting.
     */
    public function getSetting($key)
    {
        $value = $this->userSettingService->get($key);
        
        return response()->json([
            'success' => true,
            'key' => $key,
            'value' => $value
        ]);
    }

    /**
     * Set specific setting.
     */
    public function setSetting(Request $request, $key)
    {
        $validator = Validator::make($request->all(), [
            'value' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $success = $this->userSettingService->set($key, $request->value);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => __('Setting updated successfully'),
                'key' => $key,
                'value' => $this->userSettingService->get($key)
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('Failed to update setting')
        ], 500);
    }

    /**
     * Reset settings to default.
     */
    public function resetToDefault()
    {
        $success = $this->userSettingService->resetToDefault();

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => __('Settings reset to default successfully'),
                'settings' => $this->userSettingService->getAll()
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('Failed to reset settings')
        ], 500);
    }

    /**
     * Export user settings.
     */
    public function export()
    {
        $settings = $this->userSettingService->export();
        
        $filename = 'user_settings_' . Auth::id() . '_' . date('Y-m-d_H-i-s') . '.json';
        
        return response()->json($settings)
                        ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Import user settings.
     */
    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $success = $this->userSettingService->import($request->settings);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => __('Settings imported successfully'),
                'settings' => $this->userSettingService->getAll()
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('Failed to import settings')
        ], 500);
    }

    /**
     * Update theme setting.
     */
    public function updateTheme(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'theme' => 'required|in:light,dark',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid theme',
                'errors' => $validator->errors()
            ], 422);
        }

        $success = $this->userSettingService->setTheme($request->theme);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => __('Theme updated successfully'),
                'theme' => $request->theme
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('Failed to update theme')
        ], 500);
    }

    /**
     * Update language setting.
     */
    public function updateLanguage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language' => 'required|in:vi,en',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid language',
                'errors' => $validator->errors()
            ], 422);
        }

        $success = $this->userSettingService->setLanguage($request->language);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => __('Language updated successfully'),
                'language' => $request->language
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('Failed to update language')
        ], 500);
    }

    /**
     * Clear user cache.
     */
    public function clearCache()
    {
        $success = $this->userSettingService->clearUserCache();

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => __('Cache cleared successfully')
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('Failed to clear cache')
        ], 500);
    }
}
