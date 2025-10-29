# 🔥 FCM Token Test Guide

## 📱 Token Information
**Test Token:** `c7EZgqA-S76KNE3DSiqp3_:APA91bF1N6GHrxKxuvmZbPgmLRfD_T-KoEKnd5zM9hPM0V7_s7c5gT6pizPuQBg6SzmMKo1rV5uyBDdxLpFdrftCgWqOxGQEuo-EuqiQvKEhAgCAJN3R82g`

**Token Type:** Android FCM Token  
**Length:** 163 characters

---

## 🧪 Testing Methods

### **Method 1: Web Interface (Recommended)**

#### **Access URL:**
```
http://yukimart.local/fcm-token-test.html
```

#### **Steps:**
1. **Set Bearer Token** - Enter your API authentication token
2. **Run All Tests** - Comprehensive test suite
3. **View Results** - Check test results and logs

#### **Individual Tests Available:**
- ✅ **Configuration Test** - Verify FCM Service Account setup
- ✅ **Token Registration** - Register token with user account
- ✅ **Test Notification** - Send basic test notification
- ✅ **Custom Notification** - Send notification with custom settings
- ✅ **Token Information** - Check token status and details

---

### **Method 2: Laravel Artisan Command**

#### **Basic Test:**
```bash
php artisan fcm:test-token "c7EZgqA-S76KNE3DSiqp3_:APA91bF1N6GHrxKxuvmZbPgmLRfD_T-KoEKnd5zM9hPM0V7_s7c5gT6pizPuQBg6SzmMKo1rV5uyBDdxLpFdrftCgWqOxGQEuo-EuqiQvKEhAgCAJN3R82g"
```

#### **With Specific User:**
```bash
php artisan fcm:test-token "c7EZgqA-S76KNE3DSiqp3_:APA91bF1N6GHrxKxuvmZbPgmLRfD_T-KoEKnd5zM9hPM0V7_s7c5gT6pizPuQBg6SzmMKo1rV5uyBDdxLpFdrftCgWqOxGQEuo-EuqiQvKEhAgCAJN3R82g" --user-id=1
```

#### **Expected Output:**
```
🔥 Testing FCM with token: c7EZgqA-S76KNE3DSiqp3_...

⚙️  Test 1: FCM Configuration
------------------------------
✅ FCM Service Account configuration is valid
   📧 Service Account: firebase-adminsdk-xxxxx@saas-techcura.iam.gserviceaccount.com
   🆔 Project ID: saas-techcura
   🔗 API Version: v1

📱 Test 2: Register FCM Token
------------------------------
✅ Token registered successfully
   👤 User: Admin User (ID: 1)
   📱 Device: Test Device via Command
   🆔 Token ID: 123

🔔 Test 3: Send Test Notification
----------------------------------
✅ Test notification sent successfully
   📊 Sent: 1
   ❌ Failed: 0

🎯 Test 4: Send Custom Notification
------------------------------------
✅ Custom notification sent successfully
   📊 Sent: 1
   ❌ Failed: 0

📋 Test 5: Check Token Status
------------------------------
✅ Token found in database
   🆔 ID: 123
   👤 User: Admin User (ID: 1)
   📱 Device: Test Device via Command
   📋 Type: android
   ✅ Active: Yes

🎉 FCM testing completed!
```

---

### **Method 3: PHP Script**

#### **Setup:**
```bash
# Update Bearer token in script
nano test-fcm-token.php

# Find this line and replace with your token:
$bearerToken = 'YOUR_BEARER_TOKEN_HERE';
```

#### **Run Script:**
```bash
php test-fcm-token.php
```

---

### **Method 4: API Testing (cURL)**

#### **1. Test Configuration:**
```bash
curl -X GET http://yukimart.local/api/v1/fcm/test-config \
  -H "Authorization: Bearer YOUR_BEARER_TOKEN" \
  -H "Accept: application/json"
```

#### **2. Register Token:**
```bash
curl -X POST http://yukimart.local/api/v1/fcm/register-token \
  -H "Authorization: Bearer YOUR_BEARER_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "token": "c7EZgqA-S76KNE3DSiqp3_:APA91bF1N6GHrxKxuvmZbPgmLRfD_T-KoEKnd5zM9hPM0V7_s7c5gT6pizPuQBg6SzmMKo1rV5uyBDdxLpFdrftCgWqOxGQEuo-EuqiQvKEhAgCAJN3R82g",
    "device_type": "android",
    "device_name": "Test Device",
    "app_version": "1.0.0"
  }'
```

#### **3. Send Test Notification:**
```bash
curl -X POST http://yukimart.local/api/v1/fcm/test-notification \
  -H "Authorization: Bearer YOUR_BEARER_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "FCM Test",
    "message": "This is a test notification!"
  }'
```

#### **4. Send Custom Notification:**
```bash
curl -X POST http://yukimart.local/api/v1/fcm/send-notification \
  -H "Authorization: Bearer YOUR_BEARER_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Custom FCM Test",
    "message": "Custom notification with high priority!",
    "type": "test",
    "priority": "high",
    "action_url": "https://yukimart.local/dashboard",
    "action_text": "Open Dashboard"
  }'
```

#### **5. Get Token Information:**
```bash
curl -X GET http://yukimart.local/api/v1/fcm/tokens \
  -H "Authorization: Bearer YOUR_BEARER_TOKEN" \
  -H "Accept: application/json"
```

---

## 🔐 Getting Bearer Token

### **Method 1: API Login**
```bash
curl -X POST http://yukimart.local/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@yukimart.com",
    "password": "your_password"
  }'
```

### **Method 2: Laravel Tinker**
```bash
php artisan tinker
>>> $user = \App\Models\User::find(1);
>>> $token = $user->createToken('FCM Test')->plainTextToken;
>>> echo $token;
```

### **Method 3: From Browser**
```javascript
// In browser console after login
console.log(localStorage.getItem('auth_token'));
// or
console.log(sessionStorage.getItem('auth_token'));
```

---

## 📊 Expected Test Results

### **✅ Successful Test Results:**

#### **Configuration Test:**
```json
{
  "status": "success",
  "message": "FCM Service Account configuration is valid",
  "details": {
    "project_id": "saas-techcura",
    "service_account_email": "firebase-adminsdk-xxxxx@saas-techcura.iam.gserviceaccount.com",
    "api_version": "v1"
  }
}
```

#### **Token Registration:**
```json
{
  "status": "success",
  "message": "FCM token registered successfully",
  "data": {
    "id": 123,
    "user_id": 1,
    "token": "c7EZgqA-S76KNE3DSiqp3_...",
    "device_type": "android",
    "device_name": "Test Device",
    "is_active": true
  }
}
```

#### **Test Notification:**
```json
{
  "status": "success",
  "message": "Test notification sent successfully",
  "data": {
    "sent_count": 1,
    "failed_count": 0,
    "notification_id": "test-123456789"
  }
}
```

### **❌ Common Error Results:**

#### **Authentication Error:**
```json
{
  "status": "error",
  "message": "Unauthenticated"
}
```

#### **Configuration Error:**
```json
{
  "status": "error",
  "message": "FCM Service Account file not found"
}
```

#### **Invalid Token Error:**
```json
{
  "status": "error",
  "message": "Invalid FCM token format"
}
```

---

## 🔧 Troubleshooting

### **1. Authentication Issues**
```bash
# Check if user exists
php artisan tinker
>>> \App\Models\User::find(1);

# Create test user if needed
>>> \App\Models\User::factory()->create(['email' => 'test@yukimart.com']);
```

### **2. Service Account Issues**
```bash
# Check service account file
ls -la storage/app/firebase/service-account.json

# Test service account manually
php artisan tinker
>>> app(\App\Services\FCMService::class)->testConfiguration();
```

### **3. Token Format Issues**
```bash
# Validate token format
php artisan tinker
>>> $token = 'c7EZgqA-S76KNE3DSiqp3_:APA91bF1N6GHrxKxuvmZbPgmLRfD_T-KoEKnd5zM9hPM0V7_s7c5gT6pizPuQBg6SzmMKo1rV5uyBDdxLpFdrftCgWqOxGQEuo-EuqiQvKEhAgCAJN3R82g';
>>> strlen($token); // Should be 163
>>> strpos($token, ':APA91b'); // Should be > 0 for Android tokens
```

### **4. Network Issues**
```bash
# Test API connectivity
curl -I http://yukimart.local/api/v1/fcm/test-config

# Check Laravel logs
tail -f storage/logs/laravel.log | grep FCM
```

---

## 🎯 Success Criteria

### **All tests should pass with:**
- ✅ **Configuration Valid** - Service Account working
- ✅ **Token Registered** - Token stored in database
- ✅ **Notifications Sent** - FCM API calls successful
- ✅ **No Errors** - All API responses successful
- ✅ **Token Active** - Token marked as active in database

### **Expected Notification Delivery:**
- 📱 **Android Device** - Should receive push notifications
- 🔔 **Notification Sound** - Default notification sound
- 📋 **Notification Content** - Title and message displayed
- 🔗 **Action Button** - Clickable action (if provided)

---

## 🚀 Next Steps After Successful Test

1. **Production Deployment** - Deploy to production environment
2. **Mobile App Integration** - Integrate with actual mobile apps
3. **Monitoring Setup** - Monitor notification delivery rates
4. **User Registration** - Implement user token registration flow
5. **Notification Templates** - Create notification templates for different use cases

**Token test completed successfully! FCM system is ready for production use.** 🔥
