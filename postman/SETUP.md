# YukiMart FCM Postman Setup Guide

## 🚀 Quick Setup (5 minutes)

### Step 1: Import Postman Collection

1. **Open Postman**
2. **Click Import** button (top left)
3. **Drag & drop** these files:
   - `YukiMart-FCM-API.postman_collection.json`
   - `YukiMart-FCM.postman_environment.json`
4. **Select environment**: "YukiMart FCM Environment"

### Step 2: Configure Environment

Click the **Environment** tab and update these values:

#### ✅ **Required Variables:**
```
yukimart_base_url = http://yukimart.local
yukimart_email = yukimart@gmail.com
yukimart_password = 123456
firebase_project_id = yukimart-pos-system
test_fcm_token = c7EZgqA-S76KNE3DSiqp3_:APA91bF1N6GHrxKxuvmZbPgmLRfD_T-KoEKnd5zM9hPM0V7_s7c5gT6pizPuQBg6SzmMKo1rV5uyBDdxLpFdrftCgWqOxGQEuo-EuqiQvKEhAgCAJN3R82g
```

#### 🔐 **Auto-filled Variables:**
```
yukimart_api_token = (auto-filled after login)
firebase_access_token = (generate using script)
timestamp = (auto-generated)
```

### Step 3: Test Authentication

1. **Run**: `Authentication > Login to YukiMart`
2. **Check response**: Should return `access_token`
3. **Verify**: `yukimart_api_token` is auto-filled

### Step 4: Test FCM Notification

1. **Run**: `YukiMart FCM API > Send Test Notification`
2. **Check device**: Should receive notification
3. **Verify logs**: Check YukiMart logs for success

## 🔥 Advanced Setup (Firebase Direct API)

### Generate Firebase Access Token

#### Option A: Using Node.js Script (Recommended)

```bash
# Navigate to postman directory
cd postman

# Install dependencies
npm install

# Generate access token
npm run generate-token
```

#### Option B: Manual Generation

1. **Install Google Auth Library**:
   ```bash
   npm install google-auth-library
   ```

2. **Create token script**:
   ```javascript
   const {GoogleAuth} = require('google-auth-library');
   
   async function getToken() {
     const auth = new GoogleAuth({
       keyFile: '../storage/app/firebase/service-account.json',
       scopes: ['https://www.googleapis.com/auth/firebase.messaging']
     });
     
     const client = await auth.getClient();
     const token = await client.getAccessToken();
     console.log('Token:', token.token);
   }
   
   getToken();
   ```

3. **Run script** and copy token to `firebase_access_token`

## 📱 Test Scenarios

### 🧪 **Scenario 1: Basic Test**
```
1. Authentication > Login to YukiMart ✅
2. YukiMart FCM API > Register FCM Token ✅
3. YukiMart FCM API > Send Test Notification ✅
4. Check device for notification ✅
```

### 🛒 **Scenario 2: Order Notification**
```
1. Authentication > Login to YukiMart ✅
2. YukiMart FCM API > Send Order Notification ✅
3. Verify notification content ✅
```

### 💰 **Scenario 3: Invoice Notification**
```
1. Authentication > Login to YukiMart ✅
2. YukiMart FCM API > Send Invoice Notification ✅
3. Verify notification content ✅
```

### 🔥 **Scenario 4: Firebase Direct API**
```
1. Generate Firebase access token ✅
2. Firebase FCM v1 API > Send Notification to Token ✅
3. Check device for notification ✅
```

## 🔧 Troubleshooting

### ❌ **Login Failed**
```
Problem: Authentication returns 401
Solution: 
- Check yukimart_base_url is correct
- Verify email/password
- Ensure YukiMart server is running
```

### ❌ **No Notification Received**
```
Problem: FCM notification not delivered
Solution:
- Check test_fcm_token is correct
- Verify device is online
- Check app has notification permissions
- Test with different FCM token
```

### ❌ **Firebase API Failed**
```
Problem: Firebase v1 API returns 401
Solution:
- Generate new access token
- Check service account permissions
- Verify project ID is correct
```

### ❌ **Token Generation Failed**
```
Problem: Cannot generate Firebase access token
Solution:
- Check service-account.json exists
- Verify service account permissions
- Install google-auth-library: npm install google-auth-library
```

## 📊 Expected Results

### ✅ **Successful Login Response:**
```json
{
  "success": true,
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...",
  "token_type": "bearer",
  "expires_in": 3600
}
```

### ✅ **Successful FCM Response:**
```json
{
  "success": true,
  "message": "Notification sent successfully",
  "notification_id": "uuid-here",
  "tokens_sent": 1
}
```

### ✅ **Firebase Direct API Response:**
```json
{
  "name": "projects/yukimart-pos-system/messages/message-id"
}
```

## 🎯 Success Checklist

- [ ] Postman collection imported
- [ ] Environment variables configured
- [ ] YukiMart authentication working
- [ ] Test notification received on device
- [ ] Order notification working
- [ ] Invoice notification working
- [ ] Firebase direct API working (optional)

## 📞 Need Help?

### **Common Issues:**
1. **Environment not selected** → Select "YukiMart FCM Environment"
2. **Variables not set** → Check all required variables are filled
3. **Server not running** → Start YukiMart server at http://yukimart.local
4. **Wrong FCM token** → Use the provided test token or get new one

### **Check Logs:**
```bash
# YukiMart logs
docker exec -it php83 tail -f /var/www/html/yukimart/storage/logs/laravel.log

# Filter FCM logs
docker exec -it php83 grep FCM /var/www/html/yukimart/storage/logs/laravel.log
```

---

**🎉 You're ready to test FCM notifications with Postman!**

**Last Updated:** 2025-08-08  
**Setup Time:** ~5 minutes  
**Difficulty:** Easy ⭐⭐☆☆☆
