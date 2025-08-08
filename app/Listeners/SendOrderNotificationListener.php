<?php

namespace App\Listeners;

use App\Models\Notification;
use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendOrderNotificationListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle($event): void
    {
        try {
            $order = $event->order ?? null;
            
            if (!$order || !($order instanceof Order)) {
                Log::warning('SendOrderNotificationListener: Invalid order data');
                return;
            }

            // Chỉ gửi notification cho đơn hàng mới hoặc status thay đổi thành completed
            if ($this->shouldSendNotification($order, $event)) {
                $this->sendOrderNotification($order);
            }

        } catch (\Exception $e) {
            Log::error('SendOrderNotificationListener error: ' . $e->getMessage(), [
                'order_id' => $order->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Determine if notification should be sent.
     */
    private function shouldSendNotification($order, $event): bool
    {
        // Gửi notification cho đơn hàng mới
        if (isset($event->isNewOrder) && $event->isNewOrder) {
            return true;
        }

        // Gửi notification khi status thay đổi thành completed
        if (isset($event->statusChanged) && $event->statusChanged && $order->status === 'completed') {
            return true;
        }

        // Gửi notification cho đơn hàng có status completed (fallback)
        if ($order->status === 'completed') {
            return true;
        }

        return false;
    }

    /**
     * Send order notification.
     */
    private function sendOrderNotification($order): void
    {
        try {
            // Tạo notification cho admin users
            $adminUsers = \App\Models\User::whereHas('roles', function($query) {
                $query->where('name', 'admin');
            })->get();

            if ($adminUsers->isEmpty()) {
                // Fallback: gửi cho tất cả users nếu không có admin
                $adminUsers = \App\Models\User::limit(10)->get();
            }

            foreach ($adminUsers as $user) {
                $notification = Notification::createWithFCM(
                    $user,
                    'order',
                    $this->getOrderNotificationTitle($order),
                    $this->getOrderNotificationMessage($order),
                    [
                        'order_id' => $order->id,
                        'order_code' => $order->order_code ?? 'ORD-' . $order->id,
                        'customer_name' => $order->customer_name ?? 'Khách lẻ',
                        'total_amount' => $order->total_amount ?? 0,
                        'status' => $order->status,
                        'created_at' => $order->created_at->toISOString(),
                    ],
                    [
                        'priority' => 'high',
                        'action_url' => url('/admin/orders/' . $order->id),
                        'action_text' => 'Xem đơn hàng',
                        'color' => 'primary',
                        'icon' => 'shopping-cart'
                    ]
                );

                Log::info('Order notification sent', [
                    'order_id' => $order->id,
                    'user_id' => $user->id,
                    'notification_id' => $notification->id ?? null
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Failed to send order notification: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get notification title for order.
     */
    private function getOrderNotificationTitle($order): string
    {
        $orderCode = $order->order_code ?? 'ORD-' . $order->id;
        
        if ($order->status === 'completed') {
            return "✅ Đơn hàng hoàn thành - {$orderCode}";
        }
        
        return "🛒 Đơn hàng mới - {$orderCode}";
    }

    /**
     * Get notification message for order.
     */
    private function getOrderNotificationMessage($order): string
    {
        $customerName = $order->customer_name ?? 'Khách lẻ';
        $totalAmount = number_format($order->total_amount ?? 0, 0, ',', '.') . ' VNĐ';
        $orderCode = $order->order_code ?? 'ORD-' . $order->id;
        
        if ($order->status === 'completed') {
            return "Đơn hàng {$orderCode} của khách hàng {$customerName} đã hoàn thành với tổng tiền {$totalAmount}";
        }
        
        return "Có đơn hàng mới {$orderCode} từ khách hàng {$customerName} với tổng tiền {$totalAmount}";
    }
}
