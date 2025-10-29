# 🔥 Firebase Cloud Messaging (FCM) Setup Guide

## 📋 Overview

YukiMart now supports Firebase Cloud Messaging (FCM) for sending push notifications to mobile and web applications. This guide will help you set up FCM integration.

## 🚀 Features

- ✅ **FCM Token Management** - Register/unregister device tokens
- ✅ **Push Notifications** - Send notifications to specific users or all users
- ✅ **Multi-Platform Support** - Android, iOS, and Web
- ✅ **Queue Integration** - Async notification sending with retry logic
- ✅ **Notification Sync** - Integrated with existing notification system
- ✅ **Statistics & Monitoring** - Track token usage and notification delivery
- ✅ **API Endpoints** - Complete REST API for FCM management

## 🔧 Setup Instructions

### 1. Firebase Project Setup

**✅ Project Already Configured:**
- **Project ID:** `saas-techcura`
- **Project URL:** https://console.firebase.google.com/project/saas-techcura

**Get Server Key:**
1. Go to [Firebase Console](https://console.firebase.google.com/project/saas-techcura)
2. Navigate to **Project Settings** > **Cloud Messaging**
3. Copy the **Server Key** (Legacy)
4. Copy the **VAPID Key** (for Web Push)

### 2. Environment Configuration

**✅ Auto-Setup Available:**
```bash
# Run auto-setup command
php artisan fcm:setup

# Or with server key
php artisan fcm:setup --server-key=your_server_key_here
```

**Manual Configuration (.env):**
```env
# FCM Configuration (Auto-added by setup command)
FCM_PROJECT_ID=saas-techcura
FCM_API_KEY=AIzaSyAIt7ztwOlPP39U0WIZsTXiFU5hkDLNdNc
FCM_AUTH_DOMAIN=saas-techcura.firebaseapp.com
FCM_STORAGE_BUCKET=saas-techcura.firebasestorage.app
FCM_MESSAGING_SENDER_ID=185186239234
FCM_APP_ID=1:185186239234:web:9717b33e89ce7c71fd381b
FCM_MEASUREMENT_ID=G-PWKCFCL5ZQ

# Required: Add your server key manually
FCM_SERVER_KEY=your_firebase_server_key_here

# Optional: Logging configuration
FCM_LOGGING_ENABLED=true
FCM_LOGGING_LEVEL=info
FCM_LOGGING_CHANNEL=single
```

### 3. Queue Configuration

FCM notifications are sent via queues for better performance. Make sure your queue is configured:

```env
QUEUE_CONNECTION=database
```

Run queue worker:
```bash
php artisan queue:work --queue=notifications
```

## 📱 API Endpoints

### Authentication
All FCM endpoints require authentication via Bearer token.

### FCM Token Management

#### Register FCM Token
```http
POST /api/v1/fcm/register-token
Authorization: Bearer {access_token}
Content-Type: application/json

{
  "token": "fcm_device_token_here",
  "device_type": "android", // android, ios, web
  "device_id": "unique_device_id",
  "app_version": "1.0.0"
}
```

#### Get User's FCM Tokens
```http
GET /api/v1/fcm/tokens
Authorization: Bearer {access_token}
```

#### Unregister FCM Token
```http
POST /api/v1/fcm/unregister-token
Authorization: Bearer {access_token}
Content-Type: application/json

{
  "token": "fcm_device_token_to_remove"
}
```

### Notifications

#### Send Test Notification
```http
POST /api/v1/fcm/test-notification
Authorization: Bearer {access_token}
Content-Type: application/json

{
  "title": "Test Notification",
  "message": "This is a test message",
  "data": {
    "custom_key": "custom_value"
  }
}
```

### Statistics & Monitoring

#### Get FCM Statistics
```http
GET /api/v1/fcm/statistics
Authorization: Bearer {access_token}
```

#### Test FCM Configuration
```http
GET /api/v1/fcm/test-config
Authorization: Bearer {access_token}
```

## 💻 Code Examples

### Creating Notification with FCM

```php
use App\Models\Notification;
use App\Models\User;

$user = User::find(1);

// Create notification and send FCM automatically
$notification = Notification::createAndSendFcm(
    $user,
    'order',
    'New Order Received',
    'You have a new order #ORD-001',
    [
        'order_id' => 1,
        'amount' => 500000
    ],
    [
        'priority' => 'high',
        'channels' => ['web', 'fcm']
    ]
);
```

### Manual FCM Sending

```php
use App\Services\FcmService;

$fcmService = app(FcmService::class);

// Send to specific user
$result = $fcmService->sendToUser(
    $userId,
    'Notification Title',
    'Notification Message',
    ['custom_data' => 'value'],
    ['priority' => 'high']
);

// Send to multiple users
$result = $fcmService->sendToUsers(
    [1, 2, 3],
    'Bulk Notification',
    'Message for multiple users'
);

// Send to all users
$result = $fcmService->sendToAllUsers(
    'System Announcement',
    'Important system update'
);
```

### FCM Token Management

```php
use App\Models\FcmToken;

// Register token
$fcmToken = FcmToken::registerToken(
    $userId,
    $deviceToken,
    'android',
    'device_123',
    '1.0.0'
);

// Get active tokens for user
$tokens = FcmToken::getActiveTokensForUser($userId);

// Remove token
FcmToken::removeToken($userId, $deviceToken);
```

## 🔧 Configuration Options

### Notification Types

Configure different notification types in `config/fcm.php`:

```php
'notification_types' => [
    'order' => [
        'icon' => '🛒',
        'color' => '#007bff',
        'sound' => 'order_sound.mp3',
        'priority' => 'high',
    ],
    'invoice' => [
        'icon' => '📄',
        'color' => '#28a745',
        'sound' => 'invoice_sound.mp3',
        'priority' => 'normal',
    ],
    // ... more types
],
```

### Platform-Specific Options

```php
// Android specific
'android' => [
    'priority' => 'high',
    'notification' => [
        'sound' => 'default',
        'channel_id' => 'yukimart_notifications',
    ]
],

// iOS specific
'apns' => [
    'headers' => [
        'apns-priority' => '10',
    ],
    'payload' => [
        'aps' => [
            'sound' => 'default',
            'badge' => 1,
        ]
    ]
],
```

## 🧪 Testing

### Test FCM Functionality
```bash
# Complete FCM test
php artisan test:fcm

# Setup FCM configuration
php artisan fcm:setup
```

### Web Integration Test
```bash
# Open in browser
http://yukimart.local/test-fcm-web.html
```

**Web Test Features:**
- ✅ **Authentication** - Test login and token management
- ✅ **FCM Initialization** - Initialize Firebase messaging
- ✅ **Permission Request** - Request notification permissions
- ✅ **Token Registration** - Get and register FCM tokens
- ✅ **Send Notifications** - Send test notifications
- ✅ **Statistics** - View FCM statistics
- ✅ **Real-time Logging** - Monitor all FCM activities

### Test Specific Components
```bash
# Test FCM service
php artisan tinker
>>> app(App\Services\FcmService::class)->testConfiguration()

# Test notification creation
>>> App\Models\Notification::createAndSendFcm(
...     App\Models\User::first(),
...     'system',
...     'Test Title',
...     'Test Message'
... )
```

## 📊 Monitoring & Logs

FCM activities are logged for monitoring:

- **Token Registration/Unregistration**
- **Notification Sending Success/Failure**
- **Invalid Token Cleanup**
- **Job Processing Status**

Check logs in `storage/logs/laravel.log` for FCM-related activities.

## 🔒 Security Considerations

1. **Server Key Protection** - Keep FCM server key secure
2. **Token Validation** - Invalid tokens are automatically cleaned up
3. **Rate Limiting** - FCM jobs are rate limited to prevent spam
4. **User Authorization** - Only authenticated users can manage their tokens

## 🚨 Troubleshooting

### Common Issues

1. **"FCM server key not configured"**
   - Add `FCM_SERVER_KEY` to `.env` file
   - Restart application after adding

2. **Notifications not received**
   - Check if FCM tokens are registered
   - Verify queue worker is running
   - Check Firebase project configuration

3. **Invalid token errors**
   - Tokens are automatically deactivated
   - App should re-register tokens when needed

### Debug Commands

```bash
# Check FCM configuration
php artisan test:fcm

# Check queue jobs
php artisan queue:failed

# Check notification statistics
curl -H "Authorization: Bearer {token}" http://yukimart.local/api/v1/fcm/statistics
```

## 📈 Performance Tips

1. **Use Queues** - Always send FCM notifications via queues
2. **Batch Sending** - FCM supports up to 1000 tokens per request
3. **Token Cleanup** - Invalid tokens are automatically removed
4. **Rate Limiting** - Respect FCM rate limits (600,000 messages/minute)

## 🔄 Integration with Mobile Apps

### Android (Flutter)
```dart
// Register FCM token
FirebaseMessaging.instance.getToken().then((token) {
  // Send token to API
  registerFcmToken(token, 'android');
});

// Handle notifications
FirebaseMessaging.onMessage.listen((RemoteMessage message) {
  // Handle foreground notification
});
```

### iOS (Flutter)
```dart
// Request permission
await FirebaseMessaging.instance.requestPermission();

// Get token
String? token = await FirebaseMessaging.instance.getToken();
registerFcmToken(token, 'ios');
```

---

**🎯 Your FCM integration is now complete and ready for production use!**
