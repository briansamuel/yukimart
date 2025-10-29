# 🔥 FCM HTTP v1 API Setup Guide

## 📋 Overview

YukiMart now supports Firebase Cloud Messaging HTTP v1 API, which is the modern and recommended way to send push notifications. This replaces the legacy server key method with OAuth 2.0 authentication using service account files.

## 🆚 HTTP v1 vs Legacy API

### ✅ **HTTP v1 API Advantages:**
- **🔒 More Secure**: Uses OAuth 2.0 instead of static server keys
- **🚀 Better Performance**: Optimized message delivery
- **📱 Platform Specific**: Better support for Android, iOS, and Web
- **🔄 Future Proof**: Legacy API will be deprecated
- **📊 Better Analytics**: Enhanced delivery reporting

### ⚠️ **Legacy API Limitations:**
- **🔑 Static Keys**: Less secure server key authentication
- **📅 Deprecated**: Will be removed in future
- **🚫 Limited Features**: Missing modern FCM features

## 🔧 Setup Instructions

### **1. 📱 Firebase Console Setup**

1. **Go to Firebase Console:**
   ```
   https://console.firebase.google.com/project/saas-techcura
   ```

2. **Navigate to Project Settings:**
   - Click the gear icon ⚙️
   - Select "Project settings"

3. **Go to Service Accounts:**
   - Click "Service accounts" tab
   - Select "Firebase Admin SDK"

4. **Generate Service Account Key:**
   - Click "Generate new private key"
   - Confirm by clicking "Generate key"
   - Download the JSON file

### **2. 📁 File Setup**

1. **Save Service Account File:**
   ```bash
   # Save the downloaded JSON file as:
   storage/firebase/service-account.json
   ```

2. **Set Proper Permissions:**
   ```bash
   chmod 600 storage/firebase/service-account.json
   ```

3. **Verify File Structure:**
   ```json
   {
     "type": "service_account",
     "project_id": "saas-techcura",
     "private_key_id": "...",
     "private_key": "-----BEGIN PRIVATE KEY-----\n...\n-----END PRIVATE KEY-----\n",
     "client_email": "firebase-adminsdk-xxxxx@saas-techcura.iam.gserviceaccount.com",
     "client_id": "...",
     "auth_uri": "https://accounts.google.com/o/oauth2/auth",
     "token_uri": "https://oauth2.googleapis.com/token",
     "auth_provider_x509_cert_url": "https://www.googleapis.com/oauth2/v1/certs",
     "client_x509_cert_url": "...",
     "universe_domain": "googleapis.com"
   }
   ```

### **3. ⚙️ Environment Configuration**

Your `.env` file should have:
```env
# FCM HTTP v1 Configuration
FCM_API_VERSION=v1
FCM_PROJECT_ID=saas-techcura
FCM_SERVICE_ACCOUNT_FILE=storage/firebase/service-account.json

# Firebase Web Configuration
FCM_API_KEY=AIzaSyAIt7ztwOlPP39U0WIZsTXiFU5hkDLNdNc
FCM_AUTH_DOMAIN=saas-techcura.firebaseapp.com
FCM_STORAGE_BUCKET=saas-techcura.firebasestorage.app
FCM_MESSAGING_SENDER_ID=185186239234
FCM_APP_ID=1:185186239234:web:9717b33e89ce7c71fd381b
FCM_MEASUREMENT_ID=G-PWKCFCL5ZQ

# Optional: Logging
FCM_LOGGING_ENABLED=true
FCM_LOGGING_LEVEL=info
```

## 🧪 Testing

### **Automated Setup:**
```bash
# Run setup command
php artisan fcm:setup-v1

# Test HTTP v1 functionality
php artisan test:fcm-v1

# Test complete FCM system
php artisan test:fcm
```

### **Manual Testing:**
```bash
# Test configuration
php artisan tinker
>>> app(App\Services\FcmService::class)->testConfiguration()

# Send test notification
>>> App\Models\Notification::createAndSendFcm(
...     App\Models\User::first(),
...     'system',
...     'HTTP v1 Test',
...     'Testing FCM HTTP v1 API'
... )
```

## 📊 API Differences

### **HTTP v1 Message Format:**
```json
{
  "message": {
    "token": "fcm_token_here",
    "notification": {
      "title": "Notification Title",
      "body": "Notification Body"
    },
    "data": {
      "key1": "value1",
      "key2": "value2"
    },
    "android": {
      "priority": "high",
      "notification": {
        "sound": "default",
        "channel_id": "yukimart_notifications"
      }
    },
    "apns": {
      "headers": {
        "apns-priority": "10"
      },
      "payload": {
        "aps": {
          "sound": "default",
          "badge": 1
        }
      }
    }
  }
}
```

### **Legacy API Format:**
```json
{
  "registration_ids": ["token1", "token2"],
  "notification": {
    "title": "Notification Title",
    "body": "Notification Body"
  },
  "data": {
    "key1": "value1"
  }
}
```

## 🔄 Migration from Legacy

### **Automatic Migration:**
The system supports both APIs simultaneously:

```php
// Set API version in .env
FCM_API_VERSION=v1  // Use HTTP v1
FCM_API_VERSION=legacy  // Use legacy API
```

### **Code Compatibility:**
All existing code continues to work:

```php
// This works with both APIs
$fcmService = app(FcmService::class);
$result = $fcmService->sendToUser($userId, $title, $message);

// Notification integration unchanged
Notification::createAndSendFcm($user, $type, $title, $message);
```

## 🚨 Troubleshooting

### **Common Issues:**

1. **"Service account file not found"**
   ```bash
   # Check file exists
   ls -la storage/firebase/service-account.json
   
   # Check permissions
   chmod 600 storage/firebase/service-account.json
   ```

2. **"Failed to get access token"**
   - Verify service account file is valid JSON
   - Check private key format (should include BEGIN/END markers)
   - Ensure project_id matches your Firebase project

3. **"Invalid private key"**
   - Re-download service account file from Firebase Console
   - Ensure no extra characters or line breaks in private key

4. **"Permission denied"**
   - Check service account has FCM permissions
   - Verify Firebase Admin SDK is enabled

### **Debug Commands:**
```bash
# Check configuration
php artisan fcm:setup-v1

# Test HTTP v1 specifically
php artisan test:fcm-v1

# View logs
tail -f storage/logs/laravel.log | grep FCM
```

## 📈 Performance Benefits

### **HTTP v1 Improvements:**
- ✅ **Individual Token Handling**: Better error handling per token
- ✅ **OAuth 2.0 Security**: Automatic token refresh
- ✅ **Platform Optimization**: Specific configurations for Android/iOS/Web
- ✅ **Better Error Reporting**: Detailed error codes and messages
- ✅ **Future Features**: Access to latest FCM capabilities

### **Monitoring:**
```bash
# Check FCM statistics
curl -H "Authorization: Bearer {token}" \
  http://yukimart.local/api/v1/fcm/statistics

# View notification logs
grep "FCM HTTP v1" storage/logs/laravel.log
```

## 🎯 Next Steps

1. **✅ Setup Complete**: Service account file configured
2. **🧪 Test Functionality**: Run test commands
3. **📱 Mobile Integration**: Update mobile apps to handle v1 format
4. **📊 Monitor Performance**: Check delivery rates and errors
5. **🔄 Migrate Fully**: Remove legacy server key when ready

---

**🚀 Your FCM HTTP v1 integration is now ready for production!**

For support, check the logs or run diagnostic commands above.
