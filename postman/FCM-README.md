# YukiMart FCM API Postman Collection

## 📋 Overview

This Postman collection provides comprehensive testing capabilities for YukiMart's Firebase Cloud Messaging (FCM) system, including both direct Firebase v1 API calls and YukiMart internal FCM endpoints.

## 📁 Files

- **`YukiMart-FCM-API.postman_collection.json`** - Main FCM Postman collection
- **`YukiMart-FCM.postman_environment.json`** - FCM Environment variables
- **`FCM-README.md`** - This documentation

## 🚀 Quick Setup

### 1. Import to Postman

1. Open Postman
2. Click **Import** button
3. Select FCM JSON files:
   - `YukiMart-FCM-API.postman_collection.json`
   - `YukiMart-FCM.postman_environment.json`
4. Select **YukiMart FCM Environment** as active environment

### 2. Configure Environment Variables

Update the following variables in your environment:

#### **YukiMart Settings:**
- `yukimart_base_url`: `http://yukimart.local`
- `yukimart_email`: `yukimart@gmail.com`
- `yukimart_password`: `123456`

#### **Firebase Settings:**
- `firebase_project_id`: `yukimart-pos-system`
- `test_fcm_token`: Your Android device FCM token
- `firebase_service_account_email`: Service account email
- `firebase_private_key`: Private key from service-account.json

### 3. Authentication Setup

#### **YukiMart API Authentication:**
1. Run **Authentication > Login to YukiMart**
2. Copy the `access_token` from response
3. Set `yukimart_api_token` environment variable

#### **Firebase Authentication:**
For Firebase v1 API, you need to generate access token using service account.

## 📱 Collection Structure

### **1. Firebase FCM v1 API**
Direct Firebase API endpoints for testing FCM functionality:

- **Send Notification to Token** - Send notification to specific device
- **Send Notification to Topic** - Broadcast to topic subscribers

### **2. YukiMart FCM API**
Internal YukiMart endpoints for FCM management:

- **Register FCM Token** - Register device token
- **Send Test Notification** - Send test notification
- **Get User Notifications** - Retrieve user notifications
- **Get FCM Tokens** - List user's FCM tokens
- **Send Order Notification** - Test order notifications
- **Send Invoice Notification** - Test invoice notifications

### **3. Authentication**
Authentication endpoints:

- **Login to YukiMart** - Get API access token

## 🧪 Testing Scenarios

### **Scenario 1: Basic FCM Test**
1. Login to YukiMart API
2. Register FCM token
3. Send test notification
4. Verify notification received on device

### **Scenario 2: Order Notification Test**
1. Login to YukiMart API
2. Send order notification with real order data
3. Check notification content and delivery

### **Scenario 3: Invoice Notification Test**
1. Login to YukiMart API
2. Send invoice notification with real invoice data
3. Verify notification format and data

### **Scenario 4: Direct Firebase API Test**
1. Generate Firebase access token
2. Send notification via Firebase v1 API
3. Test different notification types

## 📊 Example Requests

### **Order Notification Request:**
```json
{
  "order_id": 25,
  "order_code": "DH202508080003",
  "customer_name": "Khách lẻ",
  "total_amount": 50000,
  "status": "processing"
}
```

### **Invoice Notification Request:**
```json
{
  "invoice_id": 1859,
  "invoice_number": "HD-1859",
  "customer_name": "Khách lẻ",
  "total_amount": 50000,
  "status": "paid"
}
```

### **Firebase Direct Notification:**
```json
{
  "message": {
    "token": "{{test_fcm_token}}",
    "notification": {
      "title": "🛒 Test Notification",
      "body": "This is a test notification from Postman"
    },
    "data": {
      "type": "test",
      "source": "postman",
      "timestamp": "{{$timestamp}}"
    },
    "android": {
      "priority": "high",
      "notification": {
        "color": "#009ef7",
        "icon": "ic_notification"
      }
    }
  }
}
```

## 🔧 Current FCM Token

**Test Device Token:**
```
c7EZgqA-S76KNE3DSiqp3_:APA91bF1N6GHrxKxuvmZbPgmLRfD_T-KoEKnd5zM9hPM0V7_s7c5gT6pizPuQBg6SzmMKo1rV5uyBDdxLpFdrftCgWqOxGQEuo-EuqiQvKEhAgCAJN3R82g
```

**Firebase Project:** `yukimart-pos-system`

## 🔧 Troubleshooting

### **Common Issues:**

#### **1. Authentication Failed**
- Check `yukimart_email` and `yukimart_password`
- Ensure YukiMart server is running at `http://yukimart.local`
- Verify API token is set correctly

#### **2. Firebase Access Token Invalid**
- Generate new access token using service account
- Check service account permissions
- Verify project ID is correct

#### **3. FCM Token Invalid**
- Check FCM token format (should be 142+ characters)
- Ensure token is active and registered
- Test with different device token

#### **4. Notification Not Received**
- Check device is online
- Verify app is installed and has notification permissions
- Check Firebase console for delivery status

## 📝 Environment Variables Reference

| Variable | Description | Example |
|----------|-------------|---------|
| `yukimart_base_url` | YukiMart base URL | `http://yukimart.local` |
| `yukimart_email` | Admin email | `yukimart@gmail.com` |
| `yukimart_password` | Admin password | `123456` |
| `yukimart_api_token` | API access token | Auto-filled after login |
| `firebase_project_id` | Firebase project ID | `yukimart-pos-system` |
| `firebase_access_token` | Firebase access token | Generated from service account |
| `test_fcm_token` | Test device FCM token | 142+ character string |

## 🎯 Success Criteria

### **Successful Test Results:**
- ✅ Authentication returns valid token
- ✅ FCM token registration successful
- ✅ Notifications delivered to device
- ✅ Correct notification content and format
- ✅ No duplicate notifications
- ✅ Proper error handling

## 📞 Support

For issues with the FCM Postman collection:

1. **Check environment variables** are set correctly
2. **Verify authentication** tokens are valid
3. **Test with different FCM tokens** if delivery fails
4. **Check YukiMart logs** for server-side errors

---

**Last Updated:** 2025-08-08  
**Collection Version:** 1.0.0  
**Compatible with:** Postman 10.0+
