# YukiMart FCM API Endpoints

## 📋 Overview

This document describes all FCM (Firebase Cloud Messaging) API endpoints available in YukiMart system. These endpoints are automatically synced to Postman collections using the `php artisan postman:sync` command.

## 🔗 Base URL

```
{{yukimart_base_url}}/api/v1/fcm
```

## 🔐 Authentication

All endpoints require Bearer token authentication:

```
Authorization: Bearer {{yukimart_api_token}}
```

Get the token by calling the login endpoint first.

## 📱 FCM Endpoints

### **1. Register FCM Token**

**Endpoint:** `POST /api/v1/fcm/register-token`

**Description:** Register a new FCM token for the authenticated user

**Headers:**
```
Accept: application/json
Content-Type: application/json
Authorization: Bearer {{yukimart_api_token}}
```

**Request Body:**
```json
{
  "token": "{{test_fcm_token}}",
  "device_type": "android",
  "device_name": "Test Device",
  "app_version": "1.0.0"
}
```

**Response:**
```json
{
  "success": true,
  "message": "FCM token registered successfully",
  "data": {
    "id": 123,
    "token": "c7EZgqA-S76KNE3DSiqp3_...",
    "device_type": "android",
    "device_name": "Test Device",
    "app_version": "1.0.0",
    "created_at": "2025-08-08T15:47:00.000000Z"
  }
}
```

---

### **2. Unregister FCM Token**

**Endpoint:** `POST /api/v1/fcm/unregister-token`

**Description:** Unregister an FCM token for the authenticated user

**Request Body:**
```json
{
  "token": "{{test_fcm_token}}"
}
```

**Response:**
```json
{
  "success": true,
  "message": "FCM token unregistered successfully"
}
```

---

### **3. Get FCM Tokens**

**Endpoint:** `GET /api/v1/fcm/tokens`

**Description:** Get all FCM tokens for the authenticated user

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 123,
      "token": "c7EZgqA-S76KNE3DSiqp3_...",
      "device_type": "android",
      "device_name": "Test Device",
      "app_version": "1.0.0",
      "last_used_at": "2025-08-08T15:47:00.000000Z",
      "created_at": "2025-08-08T15:47:00.000000Z"
    }
  ]
}
```

---

### **4. Send Test Notification**

**Endpoint:** `POST /api/v1/fcm/test-notification`

**Description:** Send a test notification to the authenticated user

**Request Body:**
```json
{
  "title": "🧪 Test Notification",
  "message": "This is a test notification from Postman",
  "type": "test",
  "data": {
    "test_id": "{{$randomUUID}}",
    "timestamp": "{{$timestamp}}"
  }
}
```

**Response:**
```json
{
  "success": true,
  "message": "Test notification sent successfully",
  "data": {
    "notification_id": "uuid-here",
    "tokens_sent": 1,
    "tokens_failed": 0
  }
}
```

---

### **5. Get FCM Statistics**

**Endpoint:** `GET /api/v1/fcm/statistics`

**Description:** Get FCM statistics for the authenticated user

**Response:**
```json
{
  "success": true,
  "data": {
    "total_tokens": 5,
    "active_tokens": 3,
    "notifications_sent": 150,
    "notifications_delivered": 145,
    "delivery_rate": 96.67,
    "last_notification_at": "2025-08-08T15:47:00.000000Z"
  }
}
```

---

### **6. Send Custom Notification**

**Endpoint:** `POST /api/v1/fcm/send-notification`

**Description:** Send a custom notification to specific users or roles

**Request Body:**
```json
{
  "title": "📢 Custom Notification",
  "message": "Custom notification message",
  "type": "custom",
  "target_type": "user",
  "target_ids": [12],
  "data": {
    "action": "open_screen",
    "screen": "dashboard"
  }
}
```

**Target Types:**
- `user` - Send to specific user IDs
- `role` - Send to users with specific roles
- `all` - Send to all users (admin only)

**Response:**
```json
{
  "success": true,
  "message": "Notification sent successfully",
  "data": {
    "notification_id": "uuid-here",
    "target_type": "user",
    "target_count": 1,
    "tokens_sent": 3,
    "tokens_failed": 0
  }
}
```

---

### **7. Test FCM Configuration**

**Endpoint:** `GET /api/v1/fcm/test-config`

**Description:** Test FCM configuration and service account connectivity

**Response:**
```json
{
  "success": true,
  "message": "FCM configuration is working correctly",
  "data": {
    "project_id": "yukimart-pos-system",
    "service_account_email": "firebase-adminsdk-xxx@yukimart-pos-system.iam.gserviceaccount.com",
    "credentials_valid": true,
    "firebase_connectivity": true,
    "last_test_at": "2025-08-08T15:47:00.000000Z"
  }
}
```

## 🧪 Testing with Postman

### **1. Import Collections:**
```
1. Import YukiMart-FCM-Routes.postman_collection.json
2. Import YukiMart-FCM.postman_environment.json
3. Select YukiMart FCM Environment
```

### **2. Authentication Flow:**
```
1. Run: Authentication > Login to YukiMart
2. Copy access_token from response
3. Set yukimart_api_token environment variable
```

### **3. Test FCM Endpoints:**
```
1. Register FCM Token
2. Send Test Notification
3. Check device for notification
4. Get FCM Statistics
5. Test Custom Notification
```

## 🔄 Auto-Sync with Artisan

### **Sync FCM Routes:**
```bash
php artisan postman:sync --type=fcm
```

### **Sync Environment Variables:**
```bash
php artisan postman:sync --type=fcm --sync-env
```

### **Sync All Collections:**
```bash
php artisan postman:sync --type=all --sync-env
```

## 📊 Error Responses

### **Authentication Error (401):**
```json
{
  "success": false,
  "message": "Unauthenticated",
  "error_code": "AUTH_REQUIRED"
}
```

### **Validation Error (422):**
```json
{
  "success": false,
  "message": "The given data was invalid",
  "errors": {
    "token": ["The token field is required."]
  }
}
```

### **FCM Error (500):**
```json
{
  "success": false,
  "message": "Failed to send notification",
  "error_code": "FCM_ERROR",
  "details": "Invalid FCM token"
}
```

## 🎯 Best Practices

### **1. Token Management:**
- Register tokens when app starts
- Unregister tokens when user logs out
- Handle token refresh automatically

### **2. Notification Testing:**
- Use test endpoints for development
- Verify delivery with statistics
- Test different notification types

### **3. Error Handling:**
- Check FCM configuration regularly
- Monitor delivery rates
- Handle invalid tokens gracefully

## 📱 Mobile Integration

### **Android Example:**
```kotlin
// Register FCM token
FirebaseMessaging.getInstance().token.addOnCompleteListener { task ->
    val token = task.result
    // Send token to /api/v1/fcm/register-token
}
```

### **iOS Example:**
```swift
// Register FCM token
Messaging.messaging().token { token, error in
    // Send token to /api/v1/fcm/register-token
}
```

---

**🎉 All FCM endpoints are now available in Postman and ready for testing!**

**Last Updated:** 2025-08-08  
**API Version:** v1  
**Collection:** YukiMart FCM Routes
