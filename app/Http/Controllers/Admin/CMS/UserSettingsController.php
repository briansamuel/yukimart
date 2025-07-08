<?php

namespace App\Http\Controllers\Admin\CMS;

use App\Http\Controllers\Controller;
use App\Models\UserSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class UserSettingsController extends Controller
{
    /**
     * Display user settings page
     */
    public function index()
    {
        $user = Auth::user();
        $settings = UserSetting::where('user_id', $user->id)->pluck('value', 'key')->toArray();
        
        return view('admin.user-settings.index', compact('user', 'settings'));
    }

    /**
     * Store or update user setting
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'key' => 'required|string|max:255',
            'value' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $userId = Auth::id();
            $key = $request->key;
            $value = $request->value;

            // Validate specific settings
            if (!$this->validateSettingValue($key, $value)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Giá trị cài đặt không hợp lệ'
                ], 422);
            }

            UserSetting::updateOrCreate(
                ['user_id' => $userId, 'key' => $key],
                ['value' => $value]
            );

            // Handle special settings
            $this->handleSpecialSettings($key, $value);

            return response()->json([
                'success' => true,
                'message' => 'Cài đặt đã được lưu thành công'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi lưu cài đặt: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user setting value
     */
    public function get(Request $request, $key)
    {
        try {
            $userId = Auth::id();
            $setting = UserSetting::where('user_id', $userId)
                ->where('key', $key)
                ->first();

            return response()->json([
                'success' => true,
                'data' => [
                    'key' => $key,
                    'value' => $setting ? $setting->value : null
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải cài đặt: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all user settings
     */
    public function getAll()
    {
        try {
            $userId = Auth::id();
            $settings = UserSetting::where('user_id', $userId)
                ->pluck('value', 'key')
                ->toArray();

            return response()->json([
                'success' => true,
                'data' => $settings
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải cài đặt: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete user setting
     */
    public function destroy($key)
    {
        try {
            $userId = Auth::id();
            UserSetting::where('user_id', $userId)
                ->where('key', $key)
                ->delete();

            return response()->json([
                'success' => true,
                'message' => 'Cài đặt đã được xóa thành công'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi xóa cài đặt: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update profile settings
     */
    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . Auth::id(),
            'phone' => 'nullable|string|max:20',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'timezone' => 'nullable|string|max:50',
            'date_format' => 'nullable|string|max:20',
            'time_format' => 'nullable|string|max:20',
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
            $data = $request->only(['name', 'email', 'phone']);

            // Handle avatar upload
            if ($request->hasFile('avatar')) {
                // Delete old avatar
                if ($user->avatar) {
                    \Storage::delete('public/' . $user->avatar);
                }

                $avatar = $request->file('avatar');
                $avatarName = time() . '_' . $user->id . '.' . $avatar->getClientOriginalExtension();
                $avatar->storeAs('public/avatars', $avatarName);
                $data['avatar'] = 'avatars/' . $avatarName;
            }

            $user->update($data);

            // Save additional settings
            $settings = [
                'timezone' => $request->timezone,
                'date_format' => $request->date_format,
                'time_format' => $request->time_format,
            ];

            foreach ($settings as $key => $value) {
                if ($value !== null) {
                    UserSetting::updateOrCreate(
                        ['user_id' => $user->id, 'key' => $key],
                        ['value' => $value]
                    );
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Hồ sơ đã được cập nhật thành công',
                'data' => $user->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi cập nhật hồ sơ: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update notification settings
     */
    public function updateNotificationSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email_notifications' => 'nullable|boolean',
            'sms_notifications' => 'nullable|boolean',
            'push_notifications' => 'nullable|boolean',
            'notification_frequency' => 'nullable|in:immediate,daily,weekly',
            'notification_types' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $userId = Auth::id();
            $settings = [
                'email_notifications' => $request->boolean('email_notifications'),
                'sms_notifications' => $request->boolean('sms_notifications'),
                'push_notifications' => $request->boolean('push_notifications'),
                'notification_frequency' => $request->notification_frequency ?? 'immediate',
                'notification_types' => json_encode($request->notification_types ?? []),
            ];

            foreach ($settings as $key => $value) {
                UserSetting::updateOrCreate(
                    ['user_id' => $userId, 'key' => $key],
                    ['value' => $value]
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Cài đặt thông báo đã được cập nhật thành công'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi cập nhật cài đặt thông báo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reset settings to default
     */
    public function resetToDefault()
    {
        try {
            $userId = Auth::id();
            UserSetting::where('user_id', $userId)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Cài đặt đã được đặt lại về mặc định'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi đặt lại cài đặt: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate setting value
     */
    private function validateSettingValue($key, $value)
    {
        switch ($key) {
            case 'theme':
                return in_array($value, ['light', 'dark', 'system']);
            case 'language':
                return in_array($value, ['en', 'vi']);
            case 'timezone':
                return in_array($value, timezone_identifiers_list());
            case 'date_format':
                return in_array($value, ['d/m/Y', 'm/d/Y', 'Y-m-d', 'd-m-Y']);
            case 'time_format':
                return in_array($value, ['12', '24']);
            case 'items_per_page':
                return is_numeric($value) && $value >= 10 && $value <= 100;
            default:
                return true;
        }
    }

    /**
     * Handle special settings that need immediate action
     */
    private function handleSpecialSettings($key, $value)
    {
        switch ($key) {
            case 'language':
                session(['locale' => $value]);
                app()->setLocale($value);
                break;
            case 'theme':
                // Theme will be handled by frontend
                break;
        }
    }

    /**
     * Export user settings
     */
    public function export()
    {
        try {
            $userId = Auth::id();
            $user = Auth::user();
            $settings = UserSetting::where('user_id', $userId)->get();

            $exportData = [
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'exported_at' => now()->toISOString(),
                ],
                'settings' => $settings->pluck('value', 'key')->toArray()
            ];

            $filename = 'user_settings_' . $user->id . '_' . date('Y-m-d_H-i-s') . '.json';

            return response()->json($exportData)
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Content-Type', 'application/json');

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi xuất cài đặt: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Import user settings
     */
    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'settings_file' => 'required|file|mimes:json|max:1024',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'File không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $file = $request->file('settings_file');
            $content = file_get_contents($file->getPathname());
            $data = json_decode($content, true);

            if (!$data || !isset($data['settings'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'File cài đặt không hợp lệ'
                ], 422);
            }

            $userId = Auth::id();
            $imported = 0;

            foreach ($data['settings'] as $key => $value) {
                if ($this->validateSettingValue($key, $value)) {
                    UserSetting::updateOrCreate(
                        ['user_id' => $userId, 'key' => $key],
                        ['value' => $value]
                    );
                    $imported++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Đã nhập {$imported} cài đặt thành công"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi nhập cài đặt: ' . $e->getMessage()
            ], 500);
        }
    }
}
