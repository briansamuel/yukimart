# 🔐 FCM Service Account Setup Guide

## 📋 Overview

Firebase Cloud Messaging (FCM) has deprecated the legacy Server Key authentication method. The recommended approach is to use **Service Account JSON** files for authentication with FCM v1 API.

## ✅ Benefits of Service Account

### **🔒 Security**
- More secure than Server Keys
- Granular permissions control
- Automatic token rotation
- OAuth2-based authentication

### **🚀 Features**
- FCM v1 API support
- Better error handling
- Platform-specific configurations
- Advanced notification features

### **📱 Platform Support**
- Android with advanced features
- iOS with APNS configuration
- Web with enhanced options
- Cross-platform consistency

---

## 🛠️ Setup Instructions

### **Step 1: Download Service Account from Firebase Console**

#### **1.1 Access Firebase Console**
```
1. Go to https://console.firebase.google.com/
2. Select your project: saas-techcura
3. Click on Project Settings (gear icon)
```

#### **1.2 Navigate to Service Accounts**
```
1. In Project Settings, click "Service Accounts" tab
2. Select "Firebase Admin SDK" section
3. Choose "Node.js" (JSON format)
```

#### **1.3 Generate Private Key**
```
1. Click "Generate new private key" button
2. Confirm by clicking "Generate key"
3. JSON file will be downloaded automatically
4. Keep this file secure - it contains sensitive credentials
```

### **Step 2: Install Dependencies**

```bash
# Install Firebase JWT library
composer require firebase/php-jwt
```

### **Step 3: Setup Service Account File**

#### **Option A: Automatic Setup (Recommended)**
```bash
# Use the setup command
php artisan fcm:setup-service-account --file=/path/to/downloaded-service-account.json
```

#### **Option B: Manual Setup**
```bash
# 1. Create firebase directory
mkdir -p storage/app/firebase

# 2. Copy service account file
cp /path/to/downloaded-service-account.json storage/app/firebase/service-account.json

# 3. Set secure permissions
chmod 600 storage/app/firebase/service-account.json

# 4. Update .env file
echo "FCM_SERVICE_ACCOUNT_PATH=storage/app/firebase/service-account.json" >> .env
```

### **Step 4: Configure Environment**

Add to your `.env` file:
```bash
# FCM Service Account Configuration
FCM_SERVICE_ACCOUNT_PATH=storage/app/firebase/service-account.json
FCM_PROJECT_ID=saas-techcura
FCM_VAPID_KEY=your_vapid_key_here
```

### **Step 5: Test Configuration**

```bash
# Test FCM setup
php artisan fcm:setup

# Test configuration specifically
php artisan tinker
>>> app(\App\Services\FCMService::class)->testConfiguration()
```

---

## 📁 File Structure

```
storage/
├── app/
│   └── firebase/
│       ├── .gitignore              # Excludes *.json files
│       ├── .gitkeep               # Keeps directory in git
│       ├── service-account.json   # Your actual service account
│       └── service-account.example.json  # Example format
```

---

## 🔧 Service Account JSON Format

Your service account file should look like this:

```json
{
  "type": "service_account",
  "project_id": "saas-techcura",
  "private_key_id": "abc123...",
  "private_key": "-----BEGIN PRIVATE KEY-----\n...\n-----END PRIVATE KEY-----\n",
  "client_email": "firebase-adminsdk-xxxxx@saas-techcura.iam.gserviceaccount.com",
  "client_id": "123456789...",
  "auth_uri": "https://accounts.google.com/o/oauth2/auth",
  "token_uri": "https://oauth2.googleapis.com/token",
  "auth_provider_x509_cert_url": "https://www.googleapis.com/oauth2/v1/certs",
  "client_x509_cert_url": "https://www.googleapis.com/robot/v1/metadata/x509/...",
  "universe_domain": "googleapis.com"
}
```

---

## 🔄 Migration from Server Key

### **If you're currently using Server Key:**

#### **1. Keep Legacy Configuration (Temporary)**
```bash
# Keep existing server key for backward compatibility
FCM_SERVER_KEY=your_existing_server_key

# Add new service account
FCM_SERVICE_ACCOUNT_PATH=storage/app/firebase/service-account.json
```

#### **2. Test Both Configurations**
```bash
# Test new service account
php artisan fcm:setup

# Verify notifications work with both methods
```

#### **3. Remove Server Key (When Ready)**
```bash
# Remove from .env when service account is working
# FCM_SERVER_KEY=your_existing_server_key  # Remove this line
```

---

## 🧪 Testing & Validation

### **Test Commands**

```bash
# Complete FCM setup test
php artisan fcm:setup

# Service account specific test
php artisan fcm:setup-service-account --file=/path/to/service-account.json

# API test
curl -X GET http://yukimart.local/api/v1/fcm/test-config \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### **Expected Test Results**

#### **✅ Successful Configuration:**
```
✅ Service Account File: storage/app/firebase/service-account.json
✅ Service Account Valid: firebase-adminsdk-xxxxx@saas-techcura.iam.gserviceaccount.com
✅ FCM Project ID: saas-techcura
✅ FCM Service Account configuration is valid
```

#### **❌ Common Issues:**
```
❌ Service Account file not found
❌ Service Account file is invalid
❌ Failed to generate access token
❌ FCM API test failed
```

---

## 🔒 Security Best Practices

### **File Permissions**
```bash
# Set restrictive permissions
chmod 600 storage/app/firebase/service-account.json

# Verify permissions
ls -la storage/app/firebase/service-account.json
# Should show: -rw------- (600)
```

### **Git Security**
```bash
# Ensure .gitignore excludes service account files
echo "*.json" >> storage/app/firebase/.gitignore
echo "!.gitkeep" >> storage/app/firebase/.gitignore
```

### **Environment Variables**
```bash
# Never commit service account content to .env
# Only store the file path
FCM_SERVICE_ACCOUNT_PATH=storage/app/firebase/service-account.json
```

### **Production Deployment**
```bash
# Use secure file transfer for production
scp service-account.json user@server:/path/to/app/storage/app/firebase/

# Set correct ownership and permissions
chown www-data:www-data storage/app/firebase/service-account.json
chmod 600 storage/app/firebase/service-account.json
```

---

## 🚀 Advanced Configuration

### **Multiple Environments**

#### **Development**
```bash
FCM_SERVICE_ACCOUNT_PATH=storage/app/firebase/service-account-dev.json
FCM_PROJECT_ID=saas-techcura-dev
```

#### **Production**
```bash
FCM_SERVICE_ACCOUNT_PATH=storage/app/firebase/service-account-prod.json
FCM_PROJECT_ID=saas-techcura
```

### **Docker Configuration**

```dockerfile
# Dockerfile
COPY service-account.json /app/storage/app/firebase/service-account.json
RUN chmod 600 /app/storage/app/firebase/service-account.json
```

```yaml
# docker-compose.yml
volumes:
  - ./service-account.json:/app/storage/app/firebase/service-account.json:ro
```

---

## 🎯 API Changes

### **FCM v1 API Features**

#### **Enhanced Notification Structure**
```php
// New FCM v1 format
$payload = [
    'message' => [
        'token' => $fcmToken,
        'notification' => [
            'title' => 'Title',
            'body' => 'Message',
            'image' => 'https://example.com/image.png'
        ],
        'android' => [
            'priority' => 'high',
            'notification' => [
                'icon' => 'custom_icon',
                'sound' => 'custom_sound'
            ]
        ],
        'apns' => [
            'headers' => [
                'apns-priority' => '10'
            ],
            'payload' => [
                'aps' => [
                    'alert' => [...],
                    'sound' => 'default'
                ]
            ]
        ],
        'webpush' => [
            'notification' => [
                'icon' => '/icon.png',
                'badge' => '/badge.png'
            ]
        ]
    ]
];
```

#### **Platform-Specific Options**
- **Android:** Custom icons, sounds, priority
- **iOS:** APNS headers, custom payload
- **Web:** Enhanced web push features

---

## ✅ Verification Checklist

Before going to production, verify:

- [ ] Service Account JSON downloaded from Firebase Console
- [ ] File placed in `storage/app/firebase/service-account.json`
- [ ] File permissions set to 600
- [ ] `.env` configured with `FCM_SERVICE_ACCOUNT_PATH`
- [ ] Firebase JWT library installed (`firebase/php-jwt`)
- [ ] Configuration test passes (`php artisan fcm:setup`)
- [ ] API test successful (`/api/v1/fcm/test-config`)
- [ ] Test notification sent successfully
- [ ] Service account file excluded from git
- [ ] Production deployment plan ready

---

## 🆘 Troubleshooting

### **Common Errors & Solutions**

#### **"Service Account file not found"**
```bash
# Check file path
ls -la storage/app/firebase/service-account.json

# Verify .env configuration
grep FCM_SERVICE_ACCOUNT_PATH .env
```

#### **"Failed to generate access token"**
```bash
# Check JSON format
cat storage/app/firebase/service-account.json | jq .

# Verify required fields
php artisan tinker
>>> $sa = json_decode(file_get_contents('storage/app/firebase/service-account.json'), true);
>>> isset($sa['private_key'], $sa['client_email'])
```

#### **"FCM API test failed"**
```bash
# Check project ID
grep FCM_PROJECT_ID .env

# Verify service account permissions in Firebase Console
```

---

## 🎉 Success!

Once setup is complete, you'll have:

- ✅ **Secure authentication** with Service Account
- ✅ **FCM v1 API** with advanced features
- ✅ **Platform-specific** notification options
- ✅ **Future-proof** configuration
- ✅ **Production-ready** setup

Your FCM system is now using the modern, secure Service Account authentication method! 🔥
