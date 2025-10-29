# 🔥 FCM (Firebase Cloud Messaging) Integration Guide

## 📋 Tổng quan

YukiMart đã tích hợp hoàn chỉnh Firebase Cloud Messaging (FCM) để gửi push notifications đến mobile và web applications. Hệ thống FCM được đồng bộ hoàn toàn với notification system hiện tại.

## ✅ Tính năng đã implement

### 🔧 **Backend Features**
- ✅ **FCM Token Management** - Đăng ký/hủy đăng ký device tokens
- ✅ **Push Notifications** - Gửi notifications đến specific users hoặc all users
- ✅ **Multi-Platform Support** - Android, iOS, và Web
- ✅ **Queue Integration** - Async notification sending với retry logic
- ✅ **Notification Sync** - Tích hợp với notification system hiện tại
- ✅ **Statistics & Monitoring** - Track token usage và notification delivery
- ✅ **Complete REST API** - 7 endpoints cho FCM management

### 📱 **Mobile App Support**
- ✅ **Token Registration** - Apps có thể đăng ký FCM tokens
- ✅ **Device Management** - Track multiple devices per user
- ✅ **Automatic Cleanup** - Invalid tokens được tự động deactivate
- ✅ **Test Notifications** - Send test notifications to verify setup

## 🚀 Quick Setup

### 1. **Run Setup Command**
```bash
php artisan fcm:setup
```

### 2. **Setup Service Account (Recommended)**
```bash
# Download Service Account JSON from Firebase Console
# Place it in storage/app/firebase/service-account.json
php artisan fcm:setup-service-account --file=/path/to/service-account.json

# Or add to .env manually
FCM_SERVICE_ACCOUNT_PATH=storage/app/firebase/service-account.json
FCM_PROJECT_ID=saas-techcura
FCM_VAPID_KEY=your_vapid_key_here
```

### 3. **Legacy Configuration (Deprecated)**
```bash
# Old method using Server Key (deprecated)
FCM_SERVER_KEY=your_server_key_here
FCM_SENDER_ID=185186239234
FCM_API_KEY=AIzaSyAIt7ztwOlPP39U0WIZsTXiFU5hkDLNdNc
FCM_AUTH_DOMAIN=saas-techcura.firebaseapp.com
FCM_STORAGE_BUCKET=saas-techcura.firebasestorage.app
FCM_APP_ID=1:185186239234:web:9717b33e89ce7c71fd381b
FCM_MEASUREMENT_ID=G-PWKCFCL5ZQ
```

### 4. **Install Dependencies**
```bash
# Install Firebase JWT library
composer require firebase/php-jwt
```

### 5. **Run Migration**
```bash
php artisan migrate
```

### 6. **Start Queue Worker**
```bash
php artisan queue:work --queue=notifications
```

## 📱 API Endpoints

### **Authentication Required (Bearer Token)**

#### 1. **Register FCM Token**
```http
POST /api/v1/fcm/register-token
Content-Type: application/json
Authorization: Bearer YOUR_TOKEN

{
  "token": "fcm_device_token_here",
  "device_type": "android", // android, ios, web
  "device_id": "unique_device_id", // optional
  "device_name": "Samsung Galaxy S21", // optional
  "app_version": "1.0.0", // optional
  "platform_version": "Android 12", // optional
  "metadata": {} // optional additional data
}
```

#### 2. **Unregister FCM Token**
```http
POST /api/v1/fcm/unregister-token
Content-Type: application/json
Authorization: Bearer YOUR_TOKEN

{
  "token": "fcm_device_token_here"
}
```

#### 3. **Get User's Tokens**
```http
GET /api/v1/fcm/tokens
Authorization: Bearer YOUR_TOKEN
```

#### 4. **Send Test Notification**
```http
POST /api/v1/fcm/test-notification
Content-Type: application/json
Authorization: Bearer YOUR_TOKEN

{
  "title": "Test Notification", // optional
  "message": "Hello from YukiMart!" // optional
}
```

### **Admin Only Endpoints**

#### 5. **Get FCM Statistics**
```http
GET /api/v1/fcm/statistics
Authorization: Bearer ADMIN_TOKEN
```

#### 6. **Send Notification to Users**
```http
POST /api/v1/fcm/send-notification
Content-Type: application/json
Authorization: Bearer ADMIN_TOKEN

{
  "title": "New Order Alert",
  "message": "You have a new order #12345",
  "user_ids": [1, 2, 3], // optional, if not provided sends to all
  "type": "order", // optional: order, invoice, inventory, system, user
  "priority": "high", // optional: low, normal, high, urgent
  "action_url": "https://app.com/orders/12345", // optional
  "action_text": "View Order", // optional
  "data": {} // optional additional data
}
```

#### 7. **Test FCM Configuration**
```http
GET /api/v1/fcm/test-config
Authorization: Bearer ADMIN_TOKEN
```

## 🔄 Integration với Notification System

### **Automatic FCM Sending**

Khi tạo notification với FCM channel, system sẽ tự động gửi push notification:

```php
// Tạo notification với FCM support
$notification = Notification::createWithFCM(
    $user,
    'order',
    'New Order',
    'You have received a new order #12345',
    ['order_id' => 12345],
    [
        'priority' => 'high',
        'action_url' => '/orders/12345',
        'action_text' => 'View Order'
    ]
);

// Hoặc tạo cho tất cả users
$notifications = Notification::createForAllWithFCM(
    'system',
    'System Maintenance',
    'System will be down for maintenance at 2 AM',
    [],
    ['priority' => 'normal']
);
```

### **Manual FCM Sending**

```php
use App\Services\FCMService;

$fcmService = app(FCMService::class);

// Send to specific user
$result = $fcmService->sendToUser($userId, [
    'title' => 'Hello',
    'message' => 'This is a test notification',
    'type' => 'test',
    'priority' => 'normal'
]);

// Send to multiple users
$result = $fcmService->sendToUsers([1, 2, 3], $notification);

// Send to all users
$result = $fcmService->sendToAll($notification);
```

## 📱 Mobile App Integration

### **Android (Flutter)**

```dart
// 1. Add FCM dependency
dependencies:
  firebase_messaging: ^14.6.5

// 2. Initialize FCM
class FCMService {
  static Future<void> initialize() async {
    FirebaseMessaging messaging = FirebaseMessaging.instance;
    
    // Request permission
    await messaging.requestPermission();
    
    // Get token
    String? token = await messaging.getToken();
    if (token != null) {
      await registerToken(token);
    }
    
    // Listen for token refresh
    messaging.onTokenRefresh.listen(registerToken);
  }
  
  static Future<void> registerToken(String token) async {
    final response = await http.post(
      Uri.parse('${API_BASE}/fcm/register-token'),
      headers: {
        'Authorization': 'Bearer $authToken',
        'Content-Type': 'application/json',
      },
      body: json.encode({
        'token': token,
        'device_type': 'android',
        'device_id': await getDeviceId(),
        'app_version': await getAppVersion(),
      }),
    );
  }
}
```

### **iOS (Flutter)**

```dart
// Similar to Android but with iOS-specific setup
// Add to ios/Runner/Info.plist for background notifications
```

### **Web (JavaScript)**

```javascript
// 1. Initialize FCM
import { initializeApp } from 'firebase/app';
import { getMessaging, getToken, onMessage } from 'firebase/messaging';

const firebaseConfig = {
  // Your Firebase config
};

const app = initializeApp(firebaseConfig);
const messaging = getMessaging(app);

// 2. Register service worker
navigator.serviceWorker.register('/firebase-messaging-sw.js');

// 3. Get token and register
async function registerFCMToken() {
  try {
    const token = await getToken(messaging, {
      vapidKey: 'YOUR_VAPID_KEY'
    });
    
    if (token) {
      await fetch('/api/v1/fcm/register-token', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${authToken}`,
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          token: token,
          device_type: 'web',
          device_name: navigator.userAgent,
        }),
      });
    }
  } catch (error) {
    console.error('FCM registration failed:', error);
  }
}

// 4. Listen for foreground messages
onMessage(messaging, (payload) => {
  console.log('Message received:', payload);
  // Show notification to user
});
```

## 🔧 Advanced Configuration

### **Queue Configuration**

```php
// config/queue.php - Add notifications queue
'connections' => [
    'database' => [
        'driver' => 'database',
        'table' => 'jobs',
        'queue' => 'default',
        'retry_after' => 90,
    ],
],

// Supervisor configuration for production
[program:yukimart-fcm-worker]
command=php /path/to/yukimart/artisan queue:work --queue=notifications
directory=/path/to/yukimart
autostart=true
autorestart=true
user=www-data
```

### **Custom Notification Channels**

```php
// Add FCM to notification channels
$notification = Notification::create([
    'channels' => ['web', 'fcm', 'email'], // Multiple channels
    // ... other fields
]);
```

## 📊 Monitoring & Statistics

### **FCM Statistics API Response**
```json
{
  "status": "success",
  "data": {
    "total_tokens": 150,
    "active_tokens": 142,
    "inactive_tokens": 8,
    "by_device_type": {
      "android": 85,
      "ios": 45,
      "web": 12
    },
    "recent_registrations": 23
  }
}
```

### **Notification Delivery Status**

```php
$notification = Notification::find($id);
$fcmStatus = $notification->getFCMStatus();

// Returns:
[
    'sent' => true,
    'sent_at' => '2025-08-08T10:30:00Z',
    'sent_count' => 5,
    'failed_count' => 1,
    'failed' => false,
    'error' => null
]
```

## 🎯 Best Practices

### **1. Token Management**
- Always provide `device_id` for better token management
- Handle token refresh in mobile apps
- Unregister tokens when user logs out

### **2. Notification Design**
- Keep titles under 50 characters
- Keep messages under 240 characters
- Use appropriate priority levels
- Include action URLs for better UX

### **3. Error Handling**
- Monitor FCM statistics regularly
- Handle invalid tokens gracefully
- Implement retry logic for failed notifications

### **4. Performance**
- Use queue workers for background processing
- Batch notifications for multiple users
- Clean up inactive tokens regularly

## 🚨 Troubleshooting

### **Common Issues**

1. **"FCM server key not configured"**
   - Add `FCM_SERVER_KEY` to .env file
   - Get key from Firebase Console > Cloud Messaging

2. **"No active tokens for user"**
   - User hasn't registered any FCM tokens
   - Check if mobile app is calling register-token API

3. **"Invalid token" errors**
   - Tokens expire or become invalid
   - System automatically deactivates invalid tokens

4. **Notifications not received**
   - Check FCM statistics for delivery status
   - Verify VAPID key for web notifications
   - Check device notification permissions

### **Debug Commands**

```bash
# Test FCM configuration
php artisan fcm:setup

# Check queue status
php artisan queue:work --queue=notifications --verbose

# Monitor logs
tail -f storage/logs/laravel.log | grep FCM
```

## 🎉 Conclusion

FCM integration is now complete and ready for production use! The system provides:

- ✅ **Complete API** for mobile/web apps
- ✅ **Automatic sync** with notification system  
- ✅ **Background processing** with queues
- ✅ **Multi-platform support** (Android, iOS, Web)
- ✅ **Admin tools** for management and monitoring
- ✅ **Production-ready** with error handling and retry logic

Mobile developers can now integrate push notifications using the provided API endpoints and examples.
