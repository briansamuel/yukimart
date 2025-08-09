<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\NotificationSetting;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class NotificationSettingController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Get user notification settings.
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Ensure user has default settings
            NotificationSetting::createDefaultForUser($user->id);
            
            // Get user settings grouped by category
            $settingsByCategory = NotificationSetting::getUserSettingsByCategory($user->id);
            
            // Get available channels
            $availableChannels = NotificationSetting::getAvailableChannels();
            
            // Get categories
            $categories = NotificationSetting::getCategories();

            return response()->json([
                'success' => true,
                'data' => [
                    'settings_by_category' => $settingsByCategory,
                    'available_channels' => $availableChannels,
                    'categories' => $categories,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy cài đặt thông báo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user notification settings.
     */
    public function update(Request $request)
    {
        try {
            $user = Auth::user();
            $settings = $request->input('settings', []);

            // Validate settings structure
            $validator = Validator::make($request->all(), [
                'settings' => 'required|array',
                'settings.*.is_enabled' => 'boolean',
                'settings.*.channels' => 'array',
                'settings.*.channels.*' => 'string|in:' . implode(',', array_keys(NotificationSetting::getAvailableChannels())),
                'settings.*.quiet_hours_start' => 'nullable|date_format:H:i',
                'settings.*.quiet_hours_end' => 'nullable|date_format:H:i',
                'settings.*.quiet_days' => 'nullable|array',
                'settings.*.quiet_days.*' => 'integer|between:0,6',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu không hợp lệ',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Update settings
            NotificationSetting::updateUserSettings($user->id, $settings);

            return response()->json([
                'success' => true,
                'message' => 'Cài đặt thông báo đã được cập nhật thành công'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật cài đặt',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reset user notification settings to default.
     */
    public function reset(Request $request)
    {
        try {
            $user = Auth::user();

            // Delete existing settings
            NotificationSetting::where('user_id', $user->id)->delete();

            // Create default settings
            NotificationSetting::createDefaultForUser($user->id);

            return response()->json([
                'success' => true,
                'message' => 'Cài đặt thông báo đã được khôi phục về mặc định'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi khôi phục cài đặt',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test notification for specific type and channel.
     */
    public function test(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'type' => 'required|string',
                'channel' => 'required|string|in:' . implode(',', array_keys(NotificationSetting::getAvailableChannels())),
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu không hợp lệ',
                    'errors' => $validator->errors()
                ], 422);
            }

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

            // Get notification type config
            $availableTypes = NotificationSetting::getAvailableTypes();
            if (!isset($availableTypes[$type])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Loại thông báo không hợp lệ'
                ], 422);
            }

            $typeConfig = $availableTypes[$type];

            // Send test notification
            $result = $this->notificationService->sendToUser(
                $user,
                $type,
                'Thông báo thử nghiệm - ' . $typeConfig['name'],
                'Đây là thông báo thử nghiệm để kiểm tra cài đặt của bạn cho loại: ' . $typeConfig['name'],
                ['test' => true],
                ['channels' => [$channel]]
            );

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Thông báo thử nghiệm đã được gửi thành công'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Không thể gửi thông báo thử nghiệm'
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi gửi thông báo thử nghiệm',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get notification statistics for user.
     */
    public function statistics(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Get total notification types
            $totalTypes = count(NotificationSetting::getAvailableTypes());
            
            // Get enabled types count
            $enabledTypes = NotificationSetting::where('user_id', $user->id)
                ->where('is_enabled', true)
                ->count();
            
            // Get settings by category
            $settingsByCategory = NotificationSetting::getUserSettingsByCategory($user->id);
            
            $categoryStats = [];
            foreach ($settingsByCategory as $categoryKey => $category) {
                $totalInCategory = count($category['types']);
                $enabledInCategory = 0;
                
                foreach ($category['types'] as $typeData) {
                    if ($typeData['setting']['is_enabled'] ?? false) {
                        $enabledInCategory++;
                    }
                }
                
                $categoryStats[$categoryKey] = [
                    'name' => $category['name'],
                    'total' => $totalInCategory,
                    'enabled' => $enabledInCategory,
                    'percentage' => $totalInCategory > 0 ? round(($enabledInCategory / $totalInCategory) * 100) : 0
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'total_types' => $totalTypes,
                    'enabled_types' => $enabledTypes,
                    'enabled_percentage' => $totalTypes > 0 ? round(($enabledTypes / $totalTypes) * 100) : 0,
                    'category_stats' => $categoryStats
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy thống kê',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
