<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use App\Http\Traits\ApiOptimizationTrait;
use App\Http\Requests\Api\V1\Notification\NotificationListRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;

class NotificationController extends Controller
{
    use ApiOptimizationTrait;
    /**
     * Get user notifications with pagination and optimization
     */
    public function index(NotificationListRequest $request)
    {
        $startTime = microtime(true);

        try {
            $user = auth()->user();

            // Generate cache key for this user's notifications
            $cacheKey = $this->generateCacheKey($request, "notifications_user_{$user->id}");

            // Try to get cached response (1 minute cache for notifications)
            $cachedResponse = $this->cacheResponse($cacheKey, null, 1);

            if ($cachedResponse) {
                $response = response()->json($cachedResponse);
                return $this->addPerformanceHeaders($response, $startTime);
            }

            // Parse include parameter for dynamic relationship loading
            $includes = $this->parseNotificationIncludes($request->get('include', ''));

            // Base query with optimized eager loading
            $query = Notification::forUser($user->id)->active();

            // Apply dynamic includes
            if (!empty($includes)) {
                $query->with($includes);
            }

            // Apply filters with optimization
            $this->applyNotificationFilters($query, $request);

            // Apply sorting
            $this->applyNotificationSorting($query, $request);

            // Optimized pagination
            $notifications = $this->optimizePagination($query, $request);

            // Parse fields for field selection
            $fields = $this->parseFields($request->get('fields'));

            // Apply field selection if specified
            $notificationData = $notifications->items();
            if (!empty($fields)) {
                $notificationData = $this->applyFieldSelection($notificationData, $fields);
            }

            // Prepare response data
            $responseData = [
                'status' => 'success',
                'message' => 'Notifications retrieved successfully',
                'data' => $notificationData,
                'meta' => [
                    'current_page' => $notifications->currentPage(),
                    'last_page' => $notifications->lastPage(),
                    'per_page' => $notifications->perPage(),
                    'total' => $notifications->total(),
                    'from' => $notifications->firstItem(),
                    'to' => $notifications->lastItem(),
                    'execution_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms'
                ]
            ];

            // Cache the response
            $this->cacheResponse($cacheKey, $responseData, 1); // 1 minute cache

            // Create response with optimization headers
            $response = response()->json($responseData, 200);
            $response = $this->addPerformanceHeaders($response, $startTime);
            $response = $this->addRateLimitHeaders($response, 200, 1); // 200 requests per minute

            return $response;

        } catch (\Exception $e) {
            Log::error('Get notifications failed: ' . $e->getMessage());
            return $this->errorResponse('Failed to retrieve notifications', 500);
        }
    }

    /**
     * Get unread notifications count with aggressive caching
     */
    public function getUnreadCount()
    {
        $startTime = microtime(true);

        try {
            $user = auth()->user();

            // Generate cache key for unread count
            $cacheKey = "notifications_unread_count_user_{$user->id}";

            // Try to get cached count (30 seconds cache for very dynamic data)
            $unreadCount = Cache::remember($cacheKey, 30, function() use ($user) {
                return Notification::forUser($user->id)
                    ->unread()
                    ->active()
                    ->count();
            });

            // Prepare response data
            $responseData = [
                'status' => 'success',
                'message' => 'Unread count retrieved successfully',
                'data' => [
                    'unread_count' => $unreadCount
                ],
                'meta' => [
                    'cached' => Cache::has($cacheKey),
                    'execution_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms'
                ]
            ];

            // Create response with optimization headers
            $response = response()->json($responseData, 200);
            $response = $this->addPerformanceHeaders($response, $startTime);

            return $response;

        } catch (\Exception $e) {
            Log::error('Get unread count failed: ' . $e->getMessage());
            return $this->errorResponse('Failed to retrieve unread count', 500);
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
     * Mark all notifications as read with cache invalidation
     */
    public function markAllAsRead()
    {
        $startTime = microtime(true);

        try {
            $user = auth()->user();

            // Perform bulk update
            $count = Notification::forUser($user->id)
                ->unread()
                ->update(['read_at' => now()]);

            // Invalidate related caches
            $this->invalidateNotificationCaches($user->id);

            // Prepare response data
            $responseData = [
                'status' => 'success',
                'message' => "Marked {$count} notifications as read",
                'data' => [
                    'count' => $count
                ],
                'meta' => [
                    'execution_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms'
                ]
            ];

            // Create response with optimization headers
            $response = response()->json($responseData, 200);
            $response = $this->addPerformanceHeaders($response, $startTime);

            return $response;

        } catch (\Exception $e) {
            Log::error('Mark all notifications as read failed: ' . $e->getMessage());
            return $this->errorResponse('Failed to mark all notifications as read', 500);
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
            return $this->errorResponse('Failed to create notification', 500);
        }
    }

    /**
     * Parse notification includes for dynamic relationship loading
     */
    private function parseNotificationIncludes($includeString)
    {
        if (empty($includeString)) {
            return [];
        }

        $availableIncludes = [
            'creator' => 'creator:id,full_name,username',
            'notifiable' => 'notifiable', // Polymorphic relationship
        ];

        $requestedIncludes = array_map('trim', explode(',', $includeString));
        $validIncludes = [];

        foreach ($requestedIncludes as $include) {
            if (isset($availableIncludes[$include])) {
                $validIncludes[] = $availableIncludes[$include];
            }
        }

        return $validIncludes;
    }

    /**
     * Apply filters to notification query with optimization
     */
    private function applyNotificationFilters($query, $request)
    {
        // Type filter
        if ($request->filled('type')) {
            if (is_array($request->type)) {
                $query->whereIn('type', $request->type);
            } else {
                $query->ofType($request->type);
            }
        }

        // Priority filter
        if ($request->filled('priority')) {
            if (is_array($request->priority)) {
                $query->whereIn('priority', $request->priority);
            } else {
                $query->withPriority($request->priority);
            }
        }

        // Status filter
        if ($request->filled('status')) {
            $status = $request->status;
            if ($status === 'read') {
                $query->read();
            } elseif ($status === 'unread') {
                $query->unread();
            }
            // 'all' doesn't need additional filtering
        }

        // Dismissible filter
        if ($request->filled('is_dismissible')) {
            $query->where('is_dismissible', $request->is_dismissible);
        }

        // Date range filters
        if ($request->filled('created_from')) {
            $query->whereDate('created_at', '>=', $request->created_from);
        }
        if ($request->filled('created_to')) {
            $query->whereDate('created_at', '<=', $request->created_to);
        }

        // Expires date range filters
        if ($request->filled('expires_from')) {
            $query->whereDate('expires_at', '>=', $request->expires_from);
        }
        if ($request->filled('expires_to')) {
            $query->whereDate('expires_at', '<=', $request->expires_to);
        }

        // Read date range filters
        if ($request->filled('read_from')) {
            $query->whereDate('read_at', '>=', $request->read_from);
        }
        if ($request->filled('read_to')) {
            $query->whereDate('read_at', '<=', $request->read_to);
        }

        // Enhanced search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $searchFields = $request->get('search_fields', ['title', 'message']);

            $query->where(function($q) use ($search, $searchFields) {
                foreach ($searchFields as $field) {
                    if (in_array($field, ['title', 'message', 'type'])) {
                        $q->orWhere($field, 'like', "%{$search}%");
                    }
                }
            });
        }
    }

    /**
     * Apply sorting to notification query
     */
    private function applyNotificationSorting($query, $request)
    {
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        // Validate sort field
        $validSortFields = ['id', 'type', 'title', 'priority', 'created_at', 'read_at', 'expires_at'];

        if (!in_array($sortBy, $validSortFields)) {
            $sortBy = 'created_at';
        }

        // Validate sort order
        if (!in_array(strtolower($sortOrder), ['asc', 'desc'])) {
            $sortOrder = 'desc';
        }

        $query->orderBy($sortBy, $sortOrder);
    }

    /**
     * Invalidate notification-related caches for a user
     */
    private function invalidateNotificationCaches($userId)
    {
        // Clear unread count cache
        Cache::forget("notifications_unread_count_user_{$userId}");

        // Clear notification list caches (pattern-based clearing would be ideal)
        $cacheKeys = [
            "notifications_user_{$userId}",
            "notifications_statistics_user_{$userId}"
        ];

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }
    }

    /**
     * Get notification statistics with caching
     */
    public function statistics()
    {
        $startTime = microtime(true);

        try {
            $user = auth()->user();

            // Generate cache key for statistics
            $cacheKey = "notifications_statistics_user_{$user->id}";

            // Try to get cached statistics (5 minutes cache)
            $stats = Cache::remember($cacheKey, 300, function() use ($user) {
                return $this->calculateNotificationStatistics($user->id);
            });

            // Prepare response data
            $responseData = [
                'status' => 'success',
                'message' => 'Notification statistics retrieved successfully',
                'data' => $stats,
                'meta' => [
                    'cached' => Cache::has($cacheKey),
                    'execution_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms'
                ]
            ];

            // Create response with optimization headers
            $response = response()->json($responseData, 200);
            $response = $this->addPerformanceHeaders($response, $startTime);

            return $response;

        } catch (\Exception $e) {
            Log::error('Get notification statistics failed: ' . $e->getMessage());
            return $this->errorResponse('Failed to retrieve notification statistics', 500);
        }
    }

    /**
     * Calculate notification statistics for a user
     */
    private function calculateNotificationStatistics($userId)
    {
        $baseQuery = Notification::forUser($userId);

        // Get basic counts
        $totalNotifications = $baseQuery->count();
        $unreadNotifications = $baseQuery->unread()->count();
        $readNotifications = $baseQuery->read()->count();
        $activeNotifications = $baseQuery->active()->count();
        $expiredNotifications = $baseQuery->expired()->count();

        // Get statistics by type
        $byType = $baseQuery->groupBy('type')
            ->selectRaw('type, count(*) as count')
            ->get()
            ->keyBy('type')
            ->map(function($item) {
                return [
                    'type' => $item->type,
                    'count' => (int) $item->count
                ];
            });

        // Get statistics by priority
        $byPriority = $baseQuery->groupBy('priority')
            ->selectRaw('priority, count(*) as count')
            ->get()
            ->keyBy('priority')
            ->map(function($item) {
                return [
                    'priority' => $item->priority,
                    'count' => (int) $item->count
                ];
            });

        return [
            'total_notifications' => $totalNotifications,
            'unread_notifications' => $unreadNotifications,
            'read_notifications' => $readNotifications,
            'active_notifications' => $activeNotifications,
            'expired_notifications' => $expiredNotifications,
            'by_type' => $byType,
            'by_priority' => $byPriority,
            'read_percentage' => $totalNotifications > 0 ? round(($readNotifications / $totalNotifications) * 100, 2) : 0
        ];
    }
}
