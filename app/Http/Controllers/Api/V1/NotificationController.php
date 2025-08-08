<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    /**
     * Get user notifications with pagination
     */
    public function index(Request $request)
    {
        try {
            $user = auth()->user();
            $perPage = $request->get('per_page', 15);
            $type = $request->get('type');
            $priority = $request->get('priority');
            $status = $request->get('status'); // read, unread, all

            $query = Notification::forUser($user->id)
                ->active()
                ->orderBy('created_at', 'desc');

            // Apply filters
            if ($type) {
                $query->ofType($type);
            }

            if ($priority) {
                $query->withPriority($priority);
            }

            if ($status === 'read') {
                $query->read();
            } elseif ($status === 'unread') {
                $query->unread();
            }

            $notifications = $query->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'message' => 'Notifications retrieved successfully',
                'data' => $notifications->items(),
                'pagination' => [
                    'current_page' => $notifications->currentPage(),
                    'last_page' => $notifications->lastPage(),
                    'per_page' => $notifications->perPage(),
                    'total' => $notifications->total(),
                    'from' => $notifications->firstItem(),
                    'to' => $notifications->lastItem(),
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Get notifications failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve notifications',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get notification details
     */
    public function show($id)
    {
        try {
            $user = auth()->user();

            $notification = Notification::forUser($user->id)
                ->where('id', $id)
                ->first();

            if (!$notification) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Notification not found'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Notification retrieved successfully',
                'data' => $notification
            ], 200);

        } catch (\Exception $e) {
            Log::error('Get notification failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        try {
            $user = auth()->user();

            $notification = Notification::forUser($user->id)
                ->where('id', $id)
                ->first();

            if (!$notification) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Notification not found'
                ], 404);
            }

            $notification->markAsRead();

            return response()->json([
                'status' => 'success',
                'message' => 'Notification marked as read',
                'data' => $notification->fresh()
            ], 200);

        } catch (\Exception $e) {
            Log::error('Mark notification as read failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to mark notification as read',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark notification as unread
     */
    public function markAsUnread($id)
    {
        try {
            $user = auth()->user();

            $notification = Notification::forUser($user->id)
                ->where('id', $id)
                ->first();

            if (!$notification) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Notification not found'
                ], 404);
            }

            $notification->markAsUnread();

            return response()->json([
                'status' => 'success',
                'message' => 'Notification marked as unread',
                'data' => $notification->fresh()
            ], 200);

        } catch (\Exception $e) {
            Log::error('Mark notification as unread failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to mark notification as unread',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        try {
            $user = auth()->user();

            $count = Notification::forUser($user->id)
                ->unread()
                ->update(['read_at' => now()]);

            return response()->json([
                'status' => 'success',
                'message' => "Marked {$count} notifications as read",
                'data' => ['count' => $count]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Mark all notifications as read failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to mark all notifications as read',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete notification
     */
    public function destroy($id)
    {
        try {
            $user = auth()->user();

            $notification = Notification::forUser($user->id)
                ->where('id', $id)
                ->first();

            if (!$notification) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Notification not found'
                ], 404);
            }

            $notification->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Notification deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Delete notification failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get notification statistics
     */
    public function statistics()
    {
        try {
            $user = auth()->user();

            $stats = Notification::getStatistics($user->id);

            return response()->json([
                'status' => 'success',
                'message' => 'Notification statistics retrieved successfully',
                'data' => $stats
            ], 200);

        } catch (\Exception $e) {
            Log::error('Get notification statistics failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve notification statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create notification (Admin only)
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'type' => 'required|string|in:order,invoice,inventory,system,user',
                'title' => 'required|string|max:255',
                'message' => 'required|string',
                'user_id' => 'nullable|exists:users,id',
                'priority' => 'nullable|string|in:low,normal,high,urgent',
                'expires_at' => 'nullable|date|after:now',
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

            // If user_id is provided, create for specific user
            if (isset($data['user_id'])) {
                $user = User::find($data['user_id']);
                $notification = Notification::createForUser(
                    $user,
                    $data['type'],
                    $data['title'],
                    $data['message'],
                    $data['data'] ?? [],
                    [
                        'priority' => $data['priority'] ?? 'normal',
                        'expires_at' => $data['expires_at'] ?? null,
                    ]
                );
            } else {
                // Create for all users
                $users = User::all();
                $notifications = [];

                foreach ($users as $user) {
                    $notifications[] = Notification::createForUser(
                        $user,
                        $data['type'],
                        $data['title'],
                        $data['message'],
                        $data['data'] ?? [],
                        [
                            'priority' => $data['priority'] ?? 'normal',
                            'expires_at' => $data['expires_at'] ?? null,
                        ]
                    );
                }

                return response()->json([
                    'status' => 'success',
                    'message' => 'Notifications created for all users',
                    'data' => ['count' => count($notifications)]
                ], 201);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Notification created successfully',
                'data' => $notification
            ], 201);

        } catch (\Exception $e) {
            Log::error('Create notification failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
