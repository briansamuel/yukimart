<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\FCMToken;
use App\Models\Notification;
use App\Services\FCMService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class FCMController extends Controller
{
    protected $fcmService;

    public function __construct(FCMService $fcmService)
    {
        $this->fcmService = $fcmService;
    }

    /**
     * Register FCM token for the authenticated user.
     */
    public function registerToken(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'token' => 'required|string|max:500',
                'device_type' => 'required|in:android,ios,web',
                'device_id' => 'nullable|string|max:255',
                'device_name' => 'nullable|string|max:255',
                'app_version' => 'nullable|string|max:50',
                'platform_version' => 'nullable|string|max:50',
                'metadata' => 'nullable|array'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            $data = $validator->validated();

            $fcmToken = FCMToken::registerToken($user->id, $data['token'], [
                'device_type' => $data['device_type'],
                'device_id' => $data['device_id'] ?? null,
                'device_name' => $data['device_name'] ?? null,
                'app_version' => $data['app_version'] ?? null,
                'platform_version' => $data['platform_version'] ?? null,
                'metadata' => $data['metadata'] ?? null
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'FCM token registered successfully',
                'data' => [
                    'id' => $fcmToken->id,
                    'device_type' => $fcmToken->device_type,
                    'device_name' => $fcmToken->device_name,
                    'registered_at' => $fcmToken->created_at,
                    'last_used_at' => $fcmToken->last_used_at
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to register FCM token: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Unregister FCM token.
     */
    public function unregisterToken(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'token' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            $token = $request->input('token');

            $fcmToken = FCMToken::where('user_id', $user->id)
                ->where('token', $token)
                ->first();

            if (!$fcmToken) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'FCM token not found'
                ], 404);
            }

            $fcmToken->deactivate();

            return response()->json([
                'status' => 'success',
                'message' => 'FCM token unregistered successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to unregister FCM token: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's registered FCM tokens.
     */
    public function getTokens(Request $request)
    {
        try {
            $user = Auth::user();
            
            $tokens = FCMToken::where('user_id', $user->id)
                ->where('is_active', true)
                ->orderBy('last_used_at', 'desc')
                ->get(['id', 'device_type', 'device_name', 'app_version', 'last_used_at', 'created_at']);

            return response()->json([
                'status' => 'success',
                'message' => 'FCM tokens retrieved successfully',
                'data' => $tokens
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve FCM tokens: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send test notification to user's devices.
     */
    public function sendTestNotification(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'nullable|string|max:255',
                'message' => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            
            $notification = [
                'title' => $request->input('title', 'Test Notification'),
                'message' => $request->input('message', 'This is a test notification from YukiMart'),
                'type' => 'test',
                'priority' => 'normal',
                'icon' => 'test',
                'action_url' => null,
                'action_text' => null
            ];

            $result = $this->fcmService->sendToUser($user->id, $notification, [
                'test' => true,
                'timestamp' => now()->toISOString()
            ]);

            if ($result['success']) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Test notification sent successfully',
                    'data' => [
                        'sent_count' => $result['sent_count'] ?? 0,
                        'failed_count' => $result['failed_count'] ?? 0
                    ]
                ], 200);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => $result['message'] ?? 'Failed to send test notification'
                ], 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send test notification: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get FCM statistics (Admin only).
     */
    public function getStatistics(Request $request)
    {
        try {
            // Check if user has admin permissions (you can customize this)
            $user = Auth::user();
            if (!$user || $user->group_id != 1) { // Assuming group_id 1 is admin
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized. Admin access required.'
                ], 403);
            }

            $statistics = FCMToken::getStatistics();

            return response()->json([
                'status' => 'success',
                'message' => 'FCM statistics retrieved successfully',
                'data' => $statistics
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve FCM statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send notification to specific users (Admin only).
     */
    public function sendNotification(Request $request)
    {
        try {
            // Check if user has admin permissions
            $user = Auth::user();
            if (!$user || $user->group_id != 1) { // Assuming group_id 1 is admin
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized. Admin access required.'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'message' => 'required|string|max:1000',
                'user_ids' => 'nullable|array',
                'user_ids.*' => 'exists:users,id',
                'type' => 'nullable|string|in:order,invoice,inventory,system,user',
                'priority' => 'nullable|string|in:low,normal,high,urgent',
                'action_url' => 'nullable|url',
                'action_text' => 'nullable|string|max:100',
                'data' => 'nullable|array'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();
            
            $notification = [
                'title' => $data['title'],
                'message' => $data['message'],
                'type' => $data['type'] ?? 'system',
                'priority' => $data['priority'] ?? 'normal',
                'action_url' => $data['action_url'] ?? null,
                'action_text' => $data['action_text'] ?? null,
            ];

            $additionalData = $data['data'] ?? [];

            // Send to specific users or all users
            if (!empty($data['user_ids'])) {
                $result = $this->fcmService->sendToUsers($data['user_ids'], $notification, $additionalData);
            } else {
                $result = $this->fcmService->sendToAll($notification, $additionalData);
            }

            if ($result['success']) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Notification sent successfully',
                    'data' => [
                        'sent_count' => $result['sent_count'] ?? 0,
                        'failed_count' => $result['failed_count'] ?? 0
                    ]
                ], 200);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => $result['message'] ?? 'Failed to send notification'
                ], 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send notification: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test FCM configuration (Admin only).
     */
    public function testConfiguration(Request $request)
    {
        try {
            // Check if user has admin permissions
            $user = Auth::user();
            if (!$user || $user->group_id != 1) { // Assuming group_id 1 is admin
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized. Admin access required.'
                ], 403);
            }

            $result = $this->fcmService->testConfiguration();

            return response()->json([
                'status' => $result['success'] ? 'success' : 'error',
                'message' => $result['message'],
                'data' => [
                    'fcm_configured' => $result['success'],
                    'server_key_present' => !empty(config('services.fcm.server_key'))
                ]
            ], $result['success'] ? 200 : 400);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to test FCM configuration: ' . $e->getMessage()
            ], 500);
        }
    }
}
