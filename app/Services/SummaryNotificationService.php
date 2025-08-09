<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class SummaryNotificationService
{
    /**
     * Types that support summary notifications.
     */
    protected static $summaryTypes = [
        'order_created',
        'invoice_created',
        'product_created',
        'inventory_import',
        'inventory_export',
        'inventory_low_stock',
        'inventory_out_of_stock',
        'user_login'
    ];

    /**
     * Create or update summary notification.
     */
    public static function createOrUpdate($user, $type, $title, $message, $data = [], $options = [])
    {
        // Check if user should receive this notification
        if (!\App\Models\NotificationSetting::shouldUserReceiveNotification($user->id, $type, 'web')) {
            return null; // User has disabled this notification type
        }

        // Check if this type supports summary
        if (!in_array($type, self::$summaryTypes)) {
            return self::createNormalNotification($user, $type, $title, $message, $data, $options);
        }

        // Find the latest unread notification of the same type for the same user
        $existingNotification = Notification::where('notifiable_type', User::class)
            ->where('notifiable_id', $user->id)
            ->where('type', $type)
            ->whereNull('read_at')
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$existingNotification) {
            // No existing notification, create new one normally
            return self::createNormalNotification($user, $type, $title, $message, $data, $options);
        }

        // Update existing notification with summary data
        $existingData = $existingNotification->data ?? [];
        $newSummaryData = self::mergeSummaryData($existingData, $data, $type);
        $summaryMessage = self::generateSummaryMessage($newSummaryData, $type);

        $existingNotification->update([
            'message' => $summaryMessage,
            'data' => $newSummaryData,
            'updated_at' => now(),
        ]);

        // Check if user should receive FCM notifications
        if (\App\Models\NotificationSetting::shouldUserReceiveNotification($user->id, $type, 'fcm')) {
            \App\Jobs\SendFCMNotificationJob::dispatch($existingNotification->id)->delay(now()->addSeconds(2));
        }

        return $existingNotification;
    }

    /**
     * Create normal notification without summary.
     */
    protected static function createNormalNotification($user, $type, $title, $message, $data = [], $options = [])
    {
        // Get user's enabled channels for this notification type
        $userChannels = self::getUserEnabledChannels($user->id, $type);
        if (empty($userChannels)) {
            return null; // User has disabled all channels for this notification
        }

        $options['channels'] = $userChannels;

        $notification = Notification::createForUser($user, $type, $title, $message, $data, $options);

        // Dispatch FCM job only if user has enabled FCM for this type
        if (in_array('fcm', $userChannels)) {
            \App\Jobs\SendFCMNotificationJob::dispatch($notification->id)->delay(now()->addSeconds(2));
        }

        return $notification;
    }

    /**
     * Get user's enabled channels for a notification type.
     */
    protected static function getUserEnabledChannels($userId, $type)
    {
        $setting = \App\Models\NotificationSetting::where('user_id', $userId)
            ->where('notification_type', $type)
            ->first();

        if (!$setting) {
            // If no setting exists, use default config
            $availableTypes = \App\Models\NotificationSetting::getAvailableTypes();
            if (isset($availableTypes[$type])) {
                $config = $availableTypes[$type];
                return $config['default_enabled'] ? $config['default_channels'] : [];
            }
            return [];
        }

        return $setting->is_enabled ? $setting->channels : [];
    }

    /**
     * Merge summary data from existing and new notifications.
     */
    protected static function mergeSummaryData($existingData, $newData, $type)
    {
        // Initialize summary structure if not exists
        if (!isset($existingData['summary'])) {
            $existingData['summary'] = [
                'count' => 1,
                'items' => [$existingData],
                'type' => $type,
            ];

            // Initialize type-specific fields
            self::initializeSummaryFields($existingData, $type);
        }

        // Add new item to summary
        $existingData['summary']['count']++;
        $existingData['summary']['items'][] = $newData;

        // Update type-specific fields
        self::updateSummaryFields($existingData, $newData, $type);

        return $existingData;
    }

    /**
     * Initialize summary fields based on notification type.
     */
    protected static function initializeSummaryFields(&$existingData, $type)
    {
        switch ($type) {
            case 'order_created':
            case 'invoice_created':
                $existingData['summary']['total_amount'] = $existingData['total_amount'] ?? 0;
                $existingData['summary']['sellers'] = [];
                $existingData['summary']['branch_shops'] = [];

                if (isset($existingData['seller_name'])) {
                    $existingData['summary']['sellers'][] = $existingData['seller_name'];
                }
                if (isset($existingData['branch_shop_name'])) {
                    $existingData['summary']['branch_shops'][] = $existingData['branch_shop_name'];
                }
                break;

            case 'product_created':
                $existingData['summary']['categories'] = [];
                $existingData['summary']['creators'] = [];

                if (isset($existingData['category_name'])) {
                    $existingData['summary']['categories'][] = $existingData['category_name'];
                }
                if (isset($existingData['created_by_name'])) {
                    $existingData['summary']['creators'][] = $existingData['created_by_name'];
                }
                break;

            case 'inventory_import':
            case 'inventory_export':
                $existingData['summary']['total_quantity'] = $existingData['quantity'] ?? 0;
                $existingData['summary']['products'] = [];
                $existingData['summary']['warehouses'] = [];

                if (isset($existingData['product_name'])) {
                    $existingData['summary']['products'][] = $existingData['product_name'];
                }
                if (isset($existingData['warehouse_name'])) {
                    $existingData['summary']['warehouses'][] = $existingData['warehouse_name'];
                }
                break;

            case 'inventory_low_stock':
            case 'inventory_out_of_stock':
                $existingData['summary']['products'] = [];
                $existingData['summary']['warehouses'] = [];

                if (isset($existingData['product_name'])) {
                    $existingData['summary']['products'][] = $existingData['product_name'];
                }
                if (isset($existingData['warehouse_name'])) {
                    $existingData['summary']['warehouses'][] = $existingData['warehouse_name'];
                }
                break;

            case 'user_login':
                $existingData['summary']['users'] = [];
                $existingData['summary']['locations'] = [];

                if (isset($existingData['user_name'])) {
                    $existingData['summary']['users'][] = $existingData['user_name'];
                }
                if (isset($existingData['login_location'])) {
                    $existingData['summary']['locations'][] = $existingData['login_location'];
                }
                break;
        }
    }

    /**
     * Update summary fields with new data based on notification type.
     */
    protected static function updateSummaryFields(&$existingData, $newData, $type)
    {
        switch ($type) {
            case 'order_created':
            case 'invoice_created':
                $existingData['summary']['total_amount'] += $newData['total_amount'] ?? 0;

                if (isset($newData['seller_name']) && !in_array($newData['seller_name'], $existingData['summary']['sellers'])) {
                    $existingData['summary']['sellers'][] = $newData['seller_name'];
                }
                if (isset($newData['branch_shop_name']) && !in_array($newData['branch_shop_name'], $existingData['summary']['branch_shops'])) {
                    $existingData['summary']['branch_shops'][] = $newData['branch_shop_name'];
                }
                break;

            case 'product_created':
                if (isset($newData['category_name']) && !in_array($newData['category_name'], $existingData['summary']['categories'])) {
                    $existingData['summary']['categories'][] = $newData['category_name'];
                }
                if (isset($newData['created_by_name']) && !in_array($newData['created_by_name'], $existingData['summary']['creators'])) {
                    $existingData['summary']['creators'][] = $newData['created_by_name'];
                }
                break;

            case 'inventory_import':
            case 'inventory_export':
                $existingData['summary']['total_quantity'] += $newData['quantity'] ?? 0;

                if (isset($newData['product_name']) && !in_array($newData['product_name'], $existingData['summary']['products'])) {
                    $existingData['summary']['products'][] = $newData['product_name'];
                }
                if (isset($newData['warehouse_name']) && !in_array($newData['warehouse_name'], $existingData['summary']['warehouses'])) {
                    $existingData['summary']['warehouses'][] = $newData['warehouse_name'];
                }
                break;

            case 'inventory_low_stock':
            case 'inventory_out_of_stock':
                if (isset($newData['product_name']) && !in_array($newData['product_name'], $existingData['summary']['products'])) {
                    $existingData['summary']['products'][] = $newData['product_name'];
                }
                if (isset($newData['warehouse_name']) && !in_array($newData['warehouse_name'], $existingData['summary']['warehouses'])) {
                    $existingData['summary']['warehouses'][] = $newData['warehouse_name'];
                }
                break;

            case 'user_login':
                if (isset($newData['user_name']) && !in_array($newData['user_name'], $existingData['summary']['users'])) {
                    $existingData['summary']['users'][] = $newData['user_name'];
                }
                if (isset($newData['login_location']) && !in_array($newData['login_location'], $existingData['summary']['locations'])) {
                    $existingData['summary']['locations'][] = $newData['login_location'];
                }
                break;
        }
    }

    /**
     * Generate summary message based on data.
     */
    protected static function generateSummaryMessage($data, $type)
    {
        $summary = $data['summary'];
        $count = $summary['count'];

        switch ($type) {
            case 'order_created':
            case 'invoice_created':
                return self::generateOrderInvoiceMessage($summary, $type, $count);

            case 'product_created':
                return self::generateProductMessage($summary, $count);

            case 'inventory_import':
            case 'inventory_export':
                return self::generateInventoryMessage($summary, $type, $count);

            case 'inventory_low_stock':
            case 'inventory_out_of_stock':
                return self::generateStockMessage($summary, $type, $count);

            case 'user_login':
                return self::generateLoginMessage($summary, $count);

            default:
                return "Có {$count} thông báo mới";
        }
    }

    /**
     * Generate message for order/invoice notifications.
     */
    protected static function generateOrderInvoiceMessage($summary, $type, $count)
    {
        $totalAmount = number_format($summary['total_amount'], 0, ',', '.') . ' ₫';
        $sellers = $summary['sellers'] ?? [];
        $branchShops = $summary['branch_shops'] ?? [];

        // Determine entity type
        $entityType = $type === 'order_created' ? 'đơn hàng' : 'hóa đơn';

        // Generate seller text
        $sellerText = '';
        if (count($sellers) === 1) {
            $sellerText = $sellers[0];
        } elseif (count($sellers) > 1) {
            $sellerText = $sellers[0] . ' và ' . (count($sellers) - 1) . ' người khác';
        }

        // Generate branch shop text
        $branchShopText = '';
        if (count($branchShops) === 1) {
            $branchShopText = $branchShops[0];
        } elseif (count($branchShops) > 1) {
            $branchShopText = $branchShops[0] . ' và ' . (count($branchShops) - 1) . ' chi nhánh khác';
        }

        // Generate message
        if ($sellerText && $branchShopText) {
            return "{$sellerText} đã bán {$count} {$entityType} với tổng trị giá {$totalAmount} tại {$branchShopText}";
        } elseif ($sellerText) {
            return "{$sellerText} đã bán {$count} {$entityType} với tổng trị giá {$totalAmount}";
        } else {
            return "Có {$count} {$entityType} mới với tổng trị giá {$totalAmount}";
        }
    }

    /**
     * Generate message for product notifications.
     */
    protected static function generateProductMessage($summary, $count)
    {
        $categories = $summary['categories'] ?? [];
        $creators = $summary['creators'] ?? [];

        $categoryText = '';
        if (count($categories) === 1) {
            $categoryText = " thuộc danh mục {$categories[0]}";
        } elseif (count($categories) > 1) {
            $categoryText = " thuộc {$categories[0]} và " . (count($categories) - 1) . " danh mục khác";
        }

        $creatorText = '';
        if (count($creators) === 1) {
            $creatorText = "{$creators[0]} đã tạo ";
        } elseif (count($creators) > 1) {
            $creatorText = "{$creators[0]} và " . (count($creators) - 1) . " người khác đã tạo ";
        }

        if ($creatorText) {
            return "{$creatorText}{$count} sản phẩm mới{$categoryText}";
        } else {
            return "Có {$count} sản phẩm mới{$categoryText}";
        }
    }

    /**
     * Generate message for inventory notifications.
     */
    protected static function generateInventoryMessage($summary, $type, $count)
    {
        $totalQuantity = $summary['total_quantity'] ?? 0;
        $products = $summary['products'] ?? [];
        $warehouses = $summary['warehouses'] ?? [];

        $actionText = $type === 'inventory_import' ? 'nhập' : 'xuất';

        $productText = '';
        if (count($products) === 1) {
            $productText = $products[0];
        } elseif (count($products) > 1) {
            $productText = $products[0] . ' và ' . (count($products) - 1) . ' sản phẩm khác';
        }

        $warehouseText = '';
        if (count($warehouses) === 1) {
            $warehouseText = " tại {$warehouses[0]}";
        } elseif (count($warehouses) > 1) {
            $warehouseText = " tại {$warehouses[0]} và " . (count($warehouses) - 1) . " kho khác";
        }

        if ($productText) {
            return "Đã {$actionText} {$count} lần với tổng {$totalQuantity} sản phẩm ({$productText}){$warehouseText}";
        } else {
            return "Có {$count} giao dịch {$actionText} kho với tổng {$totalQuantity} sản phẩm{$warehouseText}";
        }
    }

    /**
     * Generate message for stock notifications.
     */
    protected static function generateStockMessage($summary, $type, $count)
    {
        $products = $summary['products'] ?? [];
        $warehouses = $summary['warehouses'] ?? [];

        $statusText = $type === 'inventory_low_stock' ? 'sắp hết hàng' : 'hết hàng';

        $productText = '';
        if (count($products) === 1) {
            $productText = $products[0];
        } elseif (count($products) > 1) {
            $productText = $products[0] . ' và ' . (count($products) - 1) . ' sản phẩm khác';
        }

        $warehouseText = '';
        if (count($warehouses) === 1) {
            $warehouseText = " tại {$warehouses[0]}";
        } elseif (count($warehouses) > 1) {
            $warehouseText = " tại {$warehouses[0]} và " . (count($warehouses) - 1) . " kho khác";
        }

        if ($productText) {
            return "Có {$count} sản phẩm {$statusText}: {$productText}{$warehouseText}";
        } else {
            return "Có {$count} sản phẩm {$statusText}{$warehouseText}";
        }
    }

    /**
     * Generate message for login notifications.
     */
    protected static function generateLoginMessage($summary, $count)
    {
        $users = $summary['users'] ?? [];
        $locations = $summary['locations'] ?? [];

        $userText = '';
        if (count($users) === 1) {
            $userText = $users[0];
        } elseif (count($users) > 1) {
            $userText = $users[0] . ' và ' . (count($users) - 1) . ' người khác';
        }

        $locationText = '';
        if (count($locations) === 1) {
            $locationText = " từ {$locations[0]}";
        } elseif (count($locations) > 1) {
            $locationText = " từ {$locations[0]} và " . (count($locations) - 1) . " vị trí khác";
        }

        if ($userText) {
            return "{$userText} đã đăng nhập {$count} lần{$locationText}";
        } else {
            return "Có {$count} lần đăng nhập{$locationText}";
        }
    }
}
