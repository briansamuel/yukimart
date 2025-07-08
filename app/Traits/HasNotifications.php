<?php

namespace App\Traits;

use App\Models\Notification;
use App\Models\User;

trait HasNotifications
{
    /**
     * Boot the trait
     */
    protected static function bootHasNotifications()
    {
        // Listen for model events and create notifications
        static::created(function ($model) {
            $model->createNotificationForEvent('created');
        });

        static::updated(function ($model) {
            $model->createNotificationForEvent('updated');
        });

        static::deleted(function ($model) {
            $model->createNotificationForEvent('deleted');
        });
    }

    /**
     * Get notifications for this model
     */
    public function notifications()
    {
        return $this->morphMany(Notification::class, 'notifiable');
    }

    /**
     * Create notification for model event
     */
    public function createNotificationForEvent($event)
    {
        // Check if notifications are enabled for this model instance
        if (!$this->notificationsEnabled()) {
            return;
        }

        $config = $this->getNotificationConfig($event);

        if (!$config || !$config['enabled']) {
            return;
        }

        $this->createNotification(
            $config['type'],
            $config['title'],
            $config['message'],
            $config['data'] ?? [],
            $config['options'] ?? []
        );
    }

    /**
     * Get notification configuration for event
     */
    protected function getNotificationConfig($event)
    {
        $modelName = strtolower(class_basename($this));
        $eventType = $modelName . '_' . $event;

        $configs = [
            // Product notifications
            'product_created' => [
                'enabled' => true,
                'type' => 'product_created',
                'title' => 'Sản phẩm mới được tạo',
                'message' => "Sản phẩm '{$this->product_name}' đã được thêm vào hệ thống",
                'data' => [
                    'product_id' => $this->id ?? null,
                    'product_name' => $this->product_name ?? null,
                    'sku' => $this->sku ?? null,
                ],
                'options' => [
                    'priority' => 'normal',
                    'channels' => ['web'],
                    'action_url' => route('admin.products.show', $this->id ?? 0),
                    'action_text' => 'Xem chi tiết',
                ]
            ],
            'product_updated' => [
                'enabled' => true,
                'type' => 'product_updated',
                'title' => 'Sản phẩm được cập nhật',
                'message' => "Sản phẩm '{$this->product_name}' đã được cập nhật",
                'data' => [
                    'product_id' => $this->id ?? null,
                    'product_name' => $this->product_name ?? null,
                    'changes' => $this->getDirty(),
                ],
                'options' => [
                    'priority' => 'low',
                    'channels' => ['web'],
                ]
            ],
            'product_deleted' => [
                'enabled' => true,
                'type' => 'product_deleted',
                'title' => 'Sản phẩm đã bị xóa',
                'message' => "Sản phẩm '{$this->product_name}' đã bị xóa khỏi hệ thống",
                'data' => [
                    'product_id' => $this->id ?? null,
                    'product_name' => $this->product_name ?? null,
                ],
                'options' => [
                    'priority' => 'high',
                    'channels' => ['web'],
                ]
            ],

            // Order notifications
            'order_created' => [
                'enabled' => true,
                'type' => 'order_created',
                'title' => 'Đơn hàng mới',
                'message' => "Đơn hàng #{$this->order_code} đã được tạo với tổng tiền " . number_format($this->final_amount ?? 0) . " VNĐ",
                'data' => [
                    'order_id' => $this->id ?? null,
                    'order_code' => $this->order_code ?? null,
                    'customer_name' => $this->customer ? $this->customer->name : null,
                    'customer_phone' => $this->customer ? $this->customer->phone : null,
                    'final_amount' => $this->final_amount ?? 0,
                    'items_count' => $this->orderItems ? $this->orderItems->count() : 0,
                    'sold_by_name' => $this->seller ? $this->seller->name : null,
                    'branch_shop_name' => $this->branchShop ? $this->branchShop->name : null,
                ],
                'options' => [
                    'priority' => 'high',
                    'channels' => ['web'],
                    'action_url' => '/admin/orders?Code=' . ($this->order_code ?? $this->id),
                    'action_text' => 'Xem đơn hàng',
                ]
            ],
            'order_updated' => [
                'enabled' => true,
                'type' => 'order_updated',
                'title' => 'Đơn hàng được cập nhật',
                'message' => "Đơn hàng #{$this->order_code} đã được cập nhật",
                'data' => [
                    'order_id' => $this->id ?? null,
                    'order_code' => $this->order_code ?? null,
                    'changes' => $this->getDirty(),
                ],
                'options' => [
                    'priority' => 'normal',
                    'channels' => ['web'],
                ]
            ],

            // Inventory notifications
            'inventorytransaction_created' => [
                'enabled' => $this->shouldSendInventoryNotification(),
                'type' => $this->type === 'import' ? 'inventory_import' :
                         ($this->type === 'export' ? 'inventory_export' : 'inventory_adjustment'),
                'title' => $this->type === 'import' ? 'Nhập kho' :
                          ($this->type === 'export' ? 'Xuất kho' : 'Điều chỉnh kho'),
                'message' => $this->getInventoryMessage(),
                'data' => [
                    'transaction_id' => $this->id ?? null,
                    'product_id' => $this->product_id ?? null,
                    'quantity' => $this->quantity ?? 0,
                    'type' => $this->type ?? null,
                ],
                'options' => [
                    'priority' => $this->type === 'export' ? 'high' : 'normal',
                    'channels' => ['web'],
                ]
            ],
        ];

        return $configs[$eventType] ?? null;
    }

    /**
     * Get inventory transaction message
     */
    protected function getInventoryMessage()
    {
        $productName = $this->product->product_name ?? 'Sản phẩm';
        $quantity = $this->quantity ?? 0;
        
        switch ($this->type) {
            case 'import':
                return "Đã nhập {$quantity} sản phẩm {$productName} vào kho";
            case 'export':
                return "Đã xuất {$quantity} sản phẩm {$productName} ra khỏi kho";
            case 'adjustment':
                return "Đã điều chỉnh tồn kho sản phẩm {$productName}: {$quantity}";
            default:
                return "Giao dịch kho cho sản phẩm {$productName}";
        }
    }

    /**
     * Create notification
     */
    public function createNotification($type, $title, $message, $data = [], $options = [])
    {
        // Determine recipients
        $recipients = $this->getNotificationRecipients($type);
        
        foreach ($recipients as $recipient) {
            Notification::create([
                
                'notifiable_type' => get_class($recipient),
                'notifiable_id' => $recipient->id,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'data' => array_merge($data, [
                    'model_type' => get_class($this),
                    'model_id' => $this->id,
                ]),
                'priority' => $options['priority'] ?? 'normal',
                'channels' => $options['channels'] ?? ['web'],
                'expires_at' => $options['expires_at'] ?? null,
                'action_url' => $options['action_url'] ?? null,
                'action_text' => $options['action_text'] ?? null,
                'created_by' => auth()->id(),
            ]);
        }
    }

    /**
     * Get notification recipients based on type
     */
    protected function getNotificationRecipients($type)
    {
        // Default: notify all admin users
        $recipients = User::where('is_root', 1)->get();

        // Specific rules for different notification types
        // switch ($type) {
        //     case 'inventory_low_stock':
        //     case 'inventory_out_of_stock':
        //         // Notify inventory managers
        //         $recipients = User::whereIn('role', ['admin', 'inventory_manager'])->get();
        //         break;
                
        //     case 'order_created':
        //     case 'order_updated':
        //         // Notify sales team
        //         $recipients = User::whereIn('role', ['admin', 'sales_manager'])->get();
        //         break;
                
        //     case 'product_created':
        //     case 'product_updated':
        //         // Notify product managers
        //         $recipients = User::whereIn('role', ['admin', 'product_manager'])->get();
        //         break;
        // }

        return $recipients;
    }

    /**
     * Create low stock notification
     */
    public function createLowStockNotification()
    {
        if (!isset($this->stock_quantity) || !isset($this->reorder_point)) {
            return;
        }

        if ($this->stock_quantity <= $this->reorder_point && $this->stock_quantity > 0) {
            $this->createNotification(
                'inventory_low_stock',
                'Sắp hết hàng',
                "Sản phẩm '{$this->product_name}' sắp hết hàng (còn {$this->stock_quantity} sản phẩm)",
                [
                    'product_id' => $this->id,
                    'product_name' => $this->product_name,
                    'current_stock' => $this->stock_quantity,
                    'reorder_point' => $this->reorder_point,
                ],
                [
                    'priority' => 'high',
                    'action_url' => route('admin.products.show', $this->id),
                    'action_text' => 'Xem sản phẩm',
                ]
            );
        }
    }

    /**
     * Create out of stock notification
     */
    public function createOutOfStockNotification()
    {
        if (!isset($this->stock_quantity)) {
            return;
        }

        if ($this->stock_quantity <= 0) {
            $this->createNotification(
                'inventory_out_of_stock',
                'Hết hàng',
                "Sản phẩm '{$this->product_name}' đã hết hàng",
                [
                    'product_id' => $this->id,
                    'product_name' => $this->product_name,
                    'current_stock' => $this->stock_quantity,
                ],
                [
                    'priority' => 'urgent',
                    'action_url' => route('admin.products.show', $this->id),
                    'action_text' => 'Nhập hàng ngay',
                ]
            );
        }
    }

    /**
     * Create custom notification
     */
    public function notify($type, $title, $message, $data = [], $options = [])
    {
        return $this->createNotification($type, $title, $message, $data, $options);
    }

    /**
     * Get recent notifications for this model
     */
    public function getRecentNotifications($limit = 5)
    {
        return $this->notifications()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Check if notifications are enabled for this model
     */
    protected function notificationsEnabled()
    {
        // Check if the model has a property to control notifications
        if (property_exists($this, 'notificationsDisabled')) {
            return !$this->notificationsDisabled;
        }

        // Default to enabled
        return true;
    }

    /**
     * Disable notifications for this model instance
     */
    public function disableNotifications()
    {
        $this->notificationsDisabled = true;
        return $this;
    }

    /**
     * Enable notifications for this model instance
     */
    public function enableNotifications()
    {
        $this->notificationsDisabled = false;
        return $this;
    }
}
