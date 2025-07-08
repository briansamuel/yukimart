<?php

namespace App\Http\Controllers\Admin\CMS;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Display a listing of notifications
     */
    public function index()
    {
        return view('admin.notifications.index');
    }

    /**
     * Get notifications data for DataTables
     */
    public function getData(Request $request)
    {
        try {
            $userId = Auth::id();
            $options = [
                'per_page' => $request->get('length', 20),
                'type' => $request->get('type'),
                'unread_only' => $request->get('unread_only', false),
            ];

            $notifications = $this->notificationService->getUserNotifications($userId, $options);

            $data = $notifications->map(function($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'type_display' => $notification->type_display,
                    'type_icon' => $notification->type_icon,
                    'type_color' => $notification->type_color,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'priority' => $notification->priority,
                    'priority_badge' => $notification->priority_badge,
                    'is_read' => $notification->is_read,
                    'time_ago' => $notification->time_ago,
                    'created_at' => $notification->created_at,
                    'data' => $notification->data,
                ];
            });

            return response()->json([
                'data' => $data,
                'recordsTotal' => $notifications->total(),
                'recordsFiltered' => $notifications->total(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải dữ liệu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get recent notifications for header dropdown
     */
    public function getRecent(Request $request)
    {
        try {
            $userId = Auth::id();
            $limit = $request->get('limit', 10);
            $unread = $request->get('unread');
            $type = $request->get('type');

            $query = Notification::forUser($userId)->active();

            // Filter by unread status
            if ($unread) {
                $query->whereNull('read_at');
            }

            // Filter by type - only show invoice and order notifications
            if ($type) {
                if ($type === 'order') {
                    $query->whereIn('type', ['order_created', 'order_updated', 'order_completed', 'order_cancelled']);
                } elseif ($type === 'invoice') {
                    $query->whereIn('type', ['invoice_sale', 'invoice_created', 'invoice_updated']);
                } elseif ($type === 'system') {
                    // System notifications exclude inventory adjustments
                    $query->whereNotIn('type', [
                        'order_created', 'order_updated', 'order_completed', 'order_cancelled',
                        'invoice_sale', 'invoice_created', 'invoice_updated',
                        'inventory_adjustment', 'inventory_import', 'inventory_export'
                    ]);
                } else {
                    $query->where('type', $type);
                }
            } else {
                // Default: only show invoice and order notifications, exclude inventory
                $query->whereIn('type', [
                    'order_created', 'order_updated', 'order_completed', 'order_cancelled',
                    'invoice_sale', 'invoice_created', 'invoice_updated'
                ]);
            }

            $notifications = $query->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            $unreadCount = $this->notificationService->getUnreadCount($userId);

            return response()->json([
                'success' => true,
                'data' => $notifications,
                'unread_count' => $unreadCount,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải thông báo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get notification counts (total and unread)
     */
    public function getCount()
    {
        try {
            $userId = Auth::id();
            $unreadCount = $this->notificationService->getUnreadCount($userId);
            $totalCount = Notification::forUser($userId)->active()->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'total' => $totalCount,
                    'unread' => $unreadCount
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải số lượng thông báo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get unread count
     */
    public function getUnreadCount()
    {
        try {
            $userId = Auth::id();
            $count = $this->notificationService->getUnreadCount($userId);

            return response()->json([
                'success' => true,
                'count' => $count,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải số lượng thông báo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        try {
            $userId = Auth::id();
            $result = $this->notificationService->markAsRead($id, $userId);

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi đánh dấu đã đọc: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        try {
            $userId = Auth::id();
            $result = $this->notificationService->markAllAsRead($userId);

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi đánh dấu tất cả đã đọc: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete notification
     */
    public function destroy($id)
    {
        try {
            $userId = Auth::id();
            $result = $this->notificationService->deleteNotification($id, $userId);

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi xóa thông báo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create new notification (admin only)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|string|max:50',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'priority' => 'required|in:low,normal,high,urgent',
            'recipients' => 'required|array',
            'recipients.*' => 'exists:users,id',
            'expires_at' => 'nullable|date|after:now',
        ], [
            'type.required' => 'Loại thông báo là bắt buộc',
            'title.required' => 'Tiêu đề là bắt buộc',
            'message.required' => 'Nội dung là bắt buộc',
            'priority.required' => 'Mức độ ưu tiên là bắt buộc',
            'recipients.required' => 'Người nhận là bắt buộc',
            'expires_at.after' => 'Thời gian hết hạn phải sau thời điểm hiện tại',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $users = \App\Models\User::whereIn('id', $request->recipients)->get();
            
            $results = $this->notificationService->sendToUsers(
                $users,
                $request->type,
                $request->title,
                $request->message,
                $request->data ?? [],
                [
                    'priority' => $request->priority,
                    'expires_at' => $request->expires_at,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Thông báo đã được gửi thành công',
                'data' => $results
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tạo thông báo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get notification statistics
     */
    public function getStatistics(Request $request)
    {
        try {
            $userId = Auth::id();
            $filters = [
                'type' => $request->get('type'),
                'date_from' => $request->get('date_from'),
                'date_to' => $request->get('date_to'),
            ];

            $statistics = Notification::getStatistics($userId, $filters);

            return response()->json([
                'success' => true,
                'data' => $statistics
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải thống kê: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clean up expired notifications (admin only)
     */
    public function cleanupExpired()
    {
        try {
            $result = $this->notificationService->cleanupExpired();
            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi dọn dẹp thông báo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clean up old notifications (admin only)
     */
    public function cleanupOld(Request $request)
    {
        try {
            $days = $request->get('days', 30);
            $result = $this->notificationService->cleanupOld($days);
            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi dọn dẹp thông báo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get notification types
     */
    public function getTypes()
    {
        $types = [
            'product_created' => 'Sản phẩm mới',
            'product_updated' => 'Cập nhật sản phẩm',
            'product_deleted' => 'Xóa sản phẩm',
            'inventory_import' => 'Nhập kho',
            'inventory_export' => 'Xuất kho',
            'inventory_adjustment' => 'Điều chỉnh kho',
            'inventory_low_stock' => 'Sắp hết hàng',
            'inventory_out_of_stock' => 'Hết hàng',
            'order_created' => 'Đơn hàng mới',
            'order_updated' => 'Cập nhật đơn hàng',
            'order_cancelled' => 'Hủy đơn hàng',
            'order_completed' => 'Hoàn thành đơn hàng',
            'invoice_created' => 'Hóa đơn mới',
            'invoice_paid' => 'Thanh toán hóa đơn',
            'user_login' => 'Đăng nhập',
            'system_update' => 'Cập nhật hệ thống',
            'system_maintenance' => 'Bảo trì hệ thống',
            'general' => 'Thông báo chung',
        ];

        return response()->json([
            'success' => true,
            'data' => $types
        ]);
    }
}
