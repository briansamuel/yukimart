<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotificationSettingController extends Controller
{
    /**
     * Display notification settings page with performance optimizations.
     */
    public function index()
    {
        $userId = Auth::id();

        // Cache key for user settings
        $cacheKey = "notification_settings_user_{$userId}";

        // Try to get from cache first (5 minutes cache)
        $settingsByCategory = Cache::remember($cacheKey, 300, function () use ($userId) {
            // Ensure user has default settings
            NotificationSetting::createDefaultForUser($userId);

            // Get user settings grouped by category with optimized query
            return NotificationSetting::getUserSettingsByCategory($userId);
        });

        // Cache available channels (longer cache since they rarely change)
        $availableChannels = Cache::remember('notification_available_channels', 3600, function () {
            return NotificationSetting::getAvailableChannels();
        });

        return view('admin.notification-settings.index', compact(
            'settingsByCategory',
            'availableChannels'
        ));
    }

    /**
     * Update notification settings with performance optimizations.
     */
    public function update(Request $request)
    {
        // Optimized validation with cached rules
        $validationRules = Cache::remember('notification_validation_rules', 3600, function () {
            return [
                'settings' => 'required|array',
                'settings.*.is_enabled' => 'boolean',
                'settings.*.channels' => 'array',
                'settings.*.channels.*' => 'in:web,fcm,email,sms,phone',
                'settings.*.quiet_hours_start' => 'nullable|date_format:H:i',
                'settings.*.quiet_hours_end' => 'nullable|date_format:H:i',
                'settings.*.quiet_days' => 'nullable|array',
                'settings.*.quiet_days.*' => 'integer|between:0,6',
            ];
        });

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $userId = Auth::id();
            $settings = $request->input('settings', []);

            // Use database transaction for data integrity
            DB::transaction(function () use ($userId, $settings) {
                NotificationSetting::updateUserSettings($userId, $settings);
            });

            // Clear user settings cache after update
            Cache::forget("notification_settings_user_{$userId}");

            return response()->json([
                'success' => true,
                'message' => 'Cài đặt thông báo đã được cập nhật thành công'
            ]);

        } catch (\Exception $e) {
            Log::error('Notification settings update error', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật cài đặt'
            ], 500);
        }
    }

    /**
     * Reset notification settings to default with performance optimizations.
     */
    public function reset()
    {
        try {
            $userId = Auth::id();

            // Use database transaction for data integrity
            DB::transaction(function () use ($userId) {
                // Delete existing settings efficiently
                NotificationSetting::where('user_id', $userId)->delete();

                // Create default settings
                NotificationSetting::createDefaultForUser($userId);
            });

            // Clear user settings cache after reset
            Cache::forget("notification_settings_user_{$userId}");

            return response()->json([
                'success' => true,
                'message' => 'Cài đặt thông báo đã được khôi phục về mặc định'
            ]);

        } catch (\Exception $e) {
            Log::error('Notification settings reset error', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi khôi phục cài đặt'
            ], 500);
        }
    }

    /**
     * Get notification settings for API.
     */
    public function getSettings()
    {
        $userId = Auth::id();
        
        // Ensure user has default settings
        NotificationSetting::createDefaultForUser($userId);
        
        $settingsByCategory = NotificationSetting::getUserSettingsByCategory($userId);

        return response()->json([
            'success' => true,
            'data' => $settingsByCategory
        ]);
    }

    /**
     * Test notification.
     */
    public function test(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|string',
            'channel' => 'required|in:web,fcm,email,sms'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();
            $type = $request->input('type');
            $channel = $request->input('channel');

            // Check if user should receive this notification
            if (!NotificationSetting::shouldUserReceiveNotification($user->id, $type, $channel)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn đã tắt loại thông báo này hoặc kênh này'
                ]);
            }

            // Create test notification
            $testData = [
                'test' => true,
                'message' => 'Đây là thông báo thử nghiệm'
            ];

            \App\Models\Notification::createWithFCM(
                $user,
                $type,
                'Thông báo thử nghiệm',
                'Đây là thông báo thử nghiệm để kiểm tra cài đặt của bạn',
                $testData,
                ['channels' => [$channel]]
            );

            return response()->json([
                'success' => true,
                'message' => 'Thông báo thử nghiệm đã được gửi'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi gửi thông báo thử nghiệm: ' . $e->getMessage()
            ], 500);
        }
    }
}
