# Order Notifications Dashboard Guide

## 📋 **Overview**

This feature updates the dashboard's recent activities widget to display order creation notifications instead of audit logs. When an order is created, a notification with `type = 'order_create'` is automatically generated and displayed in the dashboard's recent activities section.

## ✅ **Implementation Details**

### **Automatic Notification Creation**
When an order is created successfully, the system automatically:
1. Creates a notification with `type = 'order_create'`
2. Stores order details in the notification data
3. Links the notification to the user who created the order
4. Displays the notification in dashboard recent activities

### **Dashboard Integration Flow**
```
Order Creation → Notification Created → Dashboard getRecentActivities() → Display in Widget
```

## 🔧 **Technical Implementation**

### **1. DashboardService Enhancement**
The `getRecentActivities` method has been updated to fetch order creation notifications:

```php
public static function getRecentActivities($limit = 15) {
    // Get recent order creation notifications
    $orderNotifications = \App\Models\Notification::ofType('order_create')
        ->with(['creator', 'notifiable'])
        ->orderBy('created_at', 'desc')
        ->limit($limit)
        ->get()
        ->map(function($notification) {
            $orderData = $notification->data ?? [];
            $orderCode = $orderData['order_code'] ?? 'N/A';
            $customerName = $orderData['customer_name'] ?? 'Khách hàng';
            $totalAmount = $orderData['total_amount'] ?? 0;
            
            return [
                'id' => $notification->id,
                'user_name' => $notification->creator ? $notification->creator->name : 'Hệ thống',
                'action' => 'Tạo đơn hàng',
                'description' => "Tạo đơn hàng {$orderCode} cho {$customerName}",
                'model_display' => 'Đơn hàng',
                'created_at' => $notification->created_at,
                'time_ago' => $notification->time_ago,
                'icon' => 'ki-basket text-primary',
                'type' => 'order_create',
                'order_code' => $orderCode,
                'customer_name' => $customerName,
                'total_amount' => $totalAmount,
                'formatted_amount' => number_format($totalAmount, 0, ',', '.') . '₫',
                'is_read' => $notification->is_read,
                'priority' => $notification->priority,
                'priority_badge' => $notification->priority_badge,
                'action_url' => $orderData['action_url'] ?? null,
            ];
        });

    return $orderNotifications;
}
```

### **2. OrderService Enhancement**
Added automatic notification creation in the `createOrder` method:

```php
// In OrderService::createOrder()
// Create inventory transactions for sale
$this->createSaleInventoryTransactions($order);

// Create order creation notification
$this->createOrderNotification($order);

DB::commit();
```

### **3. New Method: createOrderNotification**
```php
public function createOrderNotification($order)
{
    try {
        // Create notification for order creation
        \App\Models\Notification::create([
            'notifiable_type' => \App\Models\User::class,
            'notifiable_id' => Auth::id(),
            'type' => 'order_create',
            'title' => 'Đơn hàng mới được tạo',
            'message' => "Đơn hàng {$order->order_code} đã được tạo thành công cho khách hàng {$order->customer->name}",
            'data' => [
                'order_id' => $order->id,
                'order_code' => $order->order_code,
                'customer_name' => $order->customer->name,
                'customer_phone' => $order->customer->phone,
                'total_amount' => $order->final_amount,
                'items_count' => $order->orderItems->count(),
                'action_url' => route('admin.order.show', $order->id),
                'created_by' => Auth::user()->name ?? 'Hệ thống'
            ],
            'priority' => 'normal',
            'channels' => ['web'],
            'created_by' => Auth::id()
        ]);

        return [
            'success' => true,
            'message' => 'Đã tạo thông báo đơn hàng thành công'
        ];

    } catch (\Exception $e) {
        return [
            'success' => false,
            'message' => 'Lỗi khi tạo thông báo đơn hàng: ' . $e->getMessage()
        ];
    }
}
```

## 📊 **Notification Data Structure**

### **Order Creation Notification**
```php
[
    'id' => 'uuid-string',
    'notifiable_type' => 'App\\Models\\User',
    'notifiable_id' => 1,
    'type' => 'order_create',
    'title' => 'Đơn hàng mới được tạo',
    'message' => 'Đơn hàng ORD-001 đã được tạo thành công cho khách hàng John Doe',
    'data' => [
        'order_id' => 123,
        'order_code' => 'ORD-001',
        'customer_name' => 'John Doe',
        'customer_phone' => '0123456789',
        'total_amount' => 500000,
        'items_count' => 3,
        'action_url' => '/admin/order/123',
        'created_by' => 'Admin User'
    ],
    'priority' => 'normal',
    'channels' => ['web'],
    'read_at' => null,
    'created_by' => 1,
    'created_at' => '2024-01-15 10:30:00'
]
```

### **Dashboard Activity Data**
```php
[
    'id' => 'notification-uuid',
    'user_name' => 'Admin User',
    'action' => 'Tạo đơn hàng',
    'description' => 'Tạo đơn hàng ORD-001 cho John Doe',
    'model_display' => 'Đơn hàng',
    'created_at' => Carbon::instance,
    'time_ago' => '5 phút trước',
    'icon' => 'ki-basket text-primary',
    'type' => 'order_create',
    'order_code' => 'ORD-001',
    'customer_name' => 'John Doe',
    'total_amount' => 500000,
    'formatted_amount' => '500.000₫',
    'is_read' => false,
    'priority' => 'normal',
    'priority_badge' => '<span class="badge badge-light-primary">Bình thường</span>',
    'action_url' => '/admin/order/123'
]
```

## 🎨 **Dashboard Widget Updates**

### **Enhanced Recent Activities Widget**
The dashboard widget now displays:

1. **Order Information:**
   - Order code as the main title
   - Customer name with user icon
   - Total amount prominently displayed

2. **User Context:**
   - Creator name with profile icon
   - Time ago with relative formatting

3. **Interactive Elements:**
   - "View" button linking to order details
   - Unread indicator (blue dot)
   - Priority badge if applicable

4. **Visual Design:**
   - Shopping basket icon for order notifications
   - Primary color scheme for order-related items
   - Scrollable container for multiple notifications

### **Widget HTML Structure**
```html
<!--begin::Item-->
<div class="d-flex align-items-center border-bottom border-gray-300 pb-6 mb-6">
    <!--begin::Symbol-->
    <div class="symbol symbol-45px me-5">
        <div class="symbol-label bg-light-primary text-primary">
            <i class="ki-duotone ki-basket fs-1">
                <span class="path1"></span>
                <span class="path2"></span>
            </i>
        </div>
    </div>
    <!--end::Symbol-->
    
    <!--begin::Content-->
    <div class="d-flex flex-column flex-grow-1">
        <!--begin::Title-->
        <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="fw-bold text-gray-800 fs-6">ORD-001</span>
            <span class="badge badge-light-primary">Bình thường</span>
        </div>
        <!--end::Title-->
        
        <!--begin::Description-->
        <div class="text-gray-600 fs-7 mb-2">
            <i class="ki-duotone ki-user fs-7 me-1"></i>
            John Doe
        </div>
        <!--end::Description-->
        
        <!--begin::Info-->
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <i class="ki-duotone ki-profile-user fs-7 text-gray-400 me-1"></i>
                <span class="text-gray-400 fs-7">Admin User</span>
            </div>
            <span class="fw-bold text-primary fs-7">500.000₫</span>
        </div>
        <!--end::Info-->
        
        <!--begin::Time-->
        <div class="d-flex align-items-center justify-content-between mt-2">
            <span class="text-gray-400 fs-8">5 phút trước</span>
            <a href="/admin/order/123" class="btn btn-sm btn-light-primary">
                <i class="ki-duotone ki-eye fs-7"></i>
                Xem
            </a>
        </div>
        <!--end::Time-->
    </div>
    <!--end::Content-->
    
    <!--begin::Status-->
    <div class="w-8px h-8px rounded-circle bg-primary ms-3"></div>
    <!--end::Status-->
</div>
<!--end::Item-->
```

## 🔄 **Process Flow**

### **1. Order Creation Process**
```
1. User creates order through admin interface
2. OrderService::createOrder() is called
3. Order is validated and created
4. Order items are added
5. Inventory transactions are created
6. → Order notification is created automatically ←
7. Order creation completes successfully
8. User sees success message
```

### **2. Dashboard Display Process**
```
1. User visits dashboard (/admin/dash-board)
2. DashboardController calls DashboardService::getRecentActivities()
3. Service fetches order_create notifications from database
4. Notifications are transformed into activity format
5. Activities are passed to dashboard view
6. Widget displays formatted order activities
7. User can click "View" to see order details
```

### **3. Notification Lifecycle**
```
1. Notification created with order data
2. Stored in notifications table
3. Retrieved by dashboard service
4. Displayed in recent activities widget
5. User can mark as read (optional)
6. Notification remains for audit trail
```

## 🧪 **Testing**

### **Test Command**
```bash
php artisan test:order-notifications
```

### **Test Scenarios**
The test command covers:

1. **Prerequisites Check:** Ensures customers and products exist
2. **Order Creation:** Tests automatic notification creation
3. **Dashboard Integration:** Verifies getRecentActivities() returns order notifications
4. **Notification Types:** Confirms 'order_create' type is working
5. **Data Structure:** Validates notification data completeness
6. **Dashboard Integration:** Confirms widget displays correctly

### **Manual Testing**
```php
// Create test order
$orderService = app(OrderService::class);
$result = $orderService->createOrder([
    'customer_id' => 1,
    'items' => json_encode([
        ['product_id' => 1, 'quantity' => 2, 'unit_price' => 100000]
    ])
]);

// Check if notification was created
$notification = Notification::where('type', 'order_create')
    ->where('data->order_id', $result['data']->id)
    ->first();

echo "Notification created: " . ($notification ? 'Yes' : 'No');

// Check dashboard activities
$activities = DashboardService::getRecentActivities(10);
echo "Recent activities count: " . $activities->count();
```

## 📈 **Benefits**

### **1. Real-time Order Tracking**
- Immediate visibility of new orders
- No need to navigate to orders page
- Quick access to order details

### **2. Enhanced User Experience**
- Rich order information at a glance
- Visual indicators for unread notifications
- Direct action buttons for quick access

### **3. Better Dashboard Relevance**
- Order-focused recent activities
- Business-relevant information display
- Contextual data for decision making

### **4. Improved Workflow**
- Faster order management
- Reduced clicks to access orders
- Better awareness of business activity

## 🔍 **Monitoring and Analytics**

### **1. Notification Queries**
```php
// Get all order creation notifications
$orderNotifications = Notification::where('type', 'order_create')
    ->with(['creator'])
    ->orderBy('created_at', 'desc')
    ->get();

// Get unread order notifications
$unreadOrders = Notification::where('type', 'order_create')
    ->whereNull('read_at')
    ->count();

// Get notifications by user
$userNotifications = Notification::where('type', 'order_create')
    ->where('created_by', $userId)
    ->get();
```

### **2. Dashboard Analytics**
```php
// Get notification statistics
$stats = Notification::selectRaw('
    type,
    count(*) as total,
    sum(case when read_at is null then 1 else 0 end) as unread
')
->groupBy('type')
->get();

// Get recent activity engagement
$recentActivities = DashboardService::getRecentActivities(20);
$orderActivities = $recentActivities->where('type', 'order_create');
$engagementRate = $orderActivities->where('is_read', true)->count() / $orderActivities->count();
```

### **3. Performance Monitoring**
```php
// Monitor notification creation performance
$startTime = microtime(true);
$orderService->createOrder($orderData);
$endTime = microtime(true);
$executionTime = $endTime - $startTime;

// Monitor dashboard load performance
$startTime = microtime(true);
$activities = DashboardService::getRecentActivities(15);
$endTime = microtime(true);
$dashboardLoadTime = $endTime - $startTime;
```

## ⚠️ **Important Considerations**

### **1. Performance**
- Notifications table can grow large over time
- Consider implementing cleanup for old notifications
- Index on type and created_at for faster queries

### **2. Data Consistency**
- Notification creation is part of order transaction
- If notification fails, order creation still succeeds
- Consider implementing retry mechanism for failed notifications

### **3. User Experience**
- Limit number of activities displayed (default: 15)
- Implement pagination for large datasets
- Consider real-time updates with WebSockets

### **4. Customization**
- Allow users to filter activity types
- Implement notification preferences
- Add mark as read/unread functionality

## 🚀 **Future Enhancements**

### **Potential Improvements**
1. **Real-time Updates:** WebSocket integration for live notifications
2. **Notification Preferences:** User-configurable notification types
3. **Advanced Filtering:** Filter by date, user, priority, etc.
4. **Notification Center:** Dedicated page for all notifications
5. **Email Notifications:** Send email for important order events
6. **Mobile Push:** Push notifications for mobile apps
7. **Notification Templates:** Customizable notification formats
8. **Bulk Actions:** Mark multiple notifications as read
9. **Notification Scheduling:** Delayed or scheduled notifications
10. **Integration APIs:** Webhook support for external systems

### **Configuration Options**
```php
// Future configuration possibilities
'notifications' => [
    'order_create' => [
        'enabled' => true,
        'priority' => 'normal',
        'channels' => ['web', 'email'],
        'template' => 'order-created',
        'auto_read_after' => '7 days'
    ],
    'dashboard' => [
        'recent_activities_limit' => 15,
        'auto_refresh_interval' => 30, // seconds
        'show_read_notifications' => true,
        'group_by_type' => false
    ]
]
```

## 📞 **Support**

### **Troubleshooting**
- **No notifications appearing:** Check if orders are being created successfully
- **Dashboard not updating:** Verify getRecentActivities() is returning data
- **Performance issues:** Check database indexes on notifications table
- **Missing order data:** Ensure notification data structure is complete

### **Debugging**
```php
// Enable notification logging
Log::info('Order notification created', [
    'order_id' => $order->id,
    'notification_id' => $notification->id,
    'user_id' => Auth::id()
]);

// Debug dashboard activities
$activities = DashboardService::getRecentActivities(5);
Log::debug('Dashboard activities', $activities->toArray());
```

---

**The order notifications dashboard integration provides real-time visibility into order creation activities and enhances the overall user experience! 🚀**
