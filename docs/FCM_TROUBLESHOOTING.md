# 🔧 FCM Troubleshooting Guide

## 🚨 Common Error: "FCM is not supported in this browser"

### **Root Causes & Solutions**

#### **1. Browser Compatibility Issues**

**Error:** `FCM is not supported in this browser`

**Causes:**
- Using an outdated browser
- Browser doesn't support Service Workers
- Browser doesn't support Push API
- Running in private/incognito mode

**Solutions:**
```javascript
// Check browser support
function checkBrowserSupport() {
    const issues = [];
    
    if (!window.isSecureContext) {
        issues.push('HTTPS required (or localhost)');
    }
    
    if (!('serviceWorker' in navigator)) {
        issues.push('Service Workers not supported');
    }
    
    if (!('Notification' in window)) {
        issues.push('Notifications not supported');
    }
    
    return issues;
}
```

**Supported Browsers:**
- ✅ Chrome 50+
- ✅ Firefox 44+
- ✅ Safari 11.1+
- ✅ Edge 17+
- ❌ Internet Explorer (not supported)

#### **2. HTTPS/Security Context Issues**

**Error:** `FCM requires HTTPS or localhost`

**Causes:**
- Accessing site via HTTP (not HTTPS)
- Mixed content issues
- Invalid SSL certificate

**Solutions:**
- Use HTTPS in production
- Use `localhost` for development
- Fix SSL certificate issues
- Check for mixed content warnings

#### **3. Service Worker Issues**

**Error:** `Service Worker registration failed`

**Causes:**
- Service worker file not found
- CORS issues
- Service worker syntax errors

**Solutions:**
```javascript
// Check service worker registration
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/firebase-messaging-sw.js')
        .then(registration => {
            console.log('SW registered:', registration);
        })
        .catch(error => {
            console.error('SW registration failed:', error);
        });
}
```

#### **4. Firebase SDK Issues**

**Error:** `Firebase SDK not loaded`

**Causes:**
- Network connectivity issues
- CDN blocked by firewall
- Script loading order issues

**Solutions:**
```html
<!-- Load Firebase SDK with error handling -->
<script src="https://www.gstatic.com/firebasejs/9.0.0/firebase-app-compat.js" 
        onerror="console.error('Failed to load Firebase App SDK')"></script>
<script src="https://www.gstatic.com/firebasejs/9.0.0/firebase-messaging-compat.js"
        onerror="console.error('Failed to load Firebase Messaging SDK')"></script>
```

---

## 🔄 **Fallback Solutions**

### **1. Use FCM Fallback Mode**

When FCM is not supported, use the fallback system:

```javascript
// Initialize fallback
const fcmFallback = new FCMFallback();
await fcmFallback.initialize();

// Register for polling-based notifications
await fcmFallback.registerForPolling();

// Send test notification
await fcmFallback.sendTestNotification('Test', 'Hello from fallback!');
```

### **2. Polling-Based Notifications**

For browsers without FCM support:

```javascript
// Check for new notifications every 30 seconds
setInterval(async () => {
    const response = await fetch('/api/v1/notifications?unread=true');
    const notifications = await response.json();
    
    notifications.data.forEach(notification => {
        new Notification(notification.title, {
            body: notification.message,
            icon: '/favicon.ico'
        });
    });
}, 30000);
```

---

## 🛠️ **Debugging Steps**

### **Step 1: Check Browser Console**

Open Developer Tools (F12) and check for errors:

```javascript
// Run in console to check support
console.log('Secure Context:', window.isSecureContext);
console.log('Service Worker:', 'serviceWorker' in navigator);
console.log('Notifications:', 'Notification' in window);
console.log('Firebase:', typeof firebase !== 'undefined');

if (typeof firebase !== 'undefined') {
    console.log('Messaging Support:', firebase.messaging.isSupported());
}
```

### **Step 2: Test Service Worker**

```javascript
// Test service worker registration
navigator.serviceWorker.register('/firebase-messaging-sw.js')
    .then(reg => console.log('SW OK:', reg))
    .catch(err => console.error('SW Error:', err));
```

### **Step 3: Test Notification Permission**

```javascript
// Check notification permission
console.log('Permission:', Notification.permission);

// Request permission
Notification.requestPermission()
    .then(permission => console.log('New permission:', permission));
```

### **Step 4: Test Firebase Configuration**

```javascript
// Test Firebase config
const config = {
    apiKey: "AIzaSyAIt7ztwOlPP39U0WIZsTXiFU5hkDLNdNc",
    projectId: "saas-techcura",
    messagingSenderId: "185186239234",
    appId: "1:185186239234:web:9717b33e89ce7c71fd381b"
};

try {
    firebase.initializeApp(config);
    console.log('Firebase initialized successfully');
} catch (error) {
    console.error('Firebase init error:', error);
}
```

---

## 🌐 **Environment-Specific Issues**

### **Development Environment**

**Issue:** FCM not working on localhost

**Solutions:**
- Use `http://localhost` (not `http://127.0.0.1`)
- Ensure port is consistent
- Clear browser cache and service workers
- Check firewall settings

### **Production Environment**

**Issue:** FCM not working on production

**Solutions:**
- Verify HTTPS certificate is valid
- Check CORS settings
- Verify Firebase project configuration
- Test with different browsers

### **Corporate Networks**

**Issue:** FCM blocked by firewall

**Solutions:**
- Whitelist Firebase domains:
  - `*.googleapis.com`
  - `*.firebase.com`
  - `*.firebaseapp.com`
- Use fallback mode for restricted environments
- Contact IT department for firewall rules

---

## 📱 **Mobile Browser Issues**

### **iOS Safari**

**Known Issues:**
- Service Workers limited in private browsing
- Push notifications require user interaction
- Background sync limitations

**Solutions:**
- Prompt user to add to home screen
- Use Web App Manifest
- Implement fallback for private browsing

### **Android Chrome**

**Known Issues:**
- Battery optimization may affect notifications
- Data saver mode may block requests

**Solutions:**
- Educate users about battery optimization
- Implement retry logic for failed requests

---

## 🔧 **Quick Fixes**

### **Clear Browser Data**
```javascript
// Clear service workers
navigator.serviceWorker.getRegistrations()
    .then(registrations => {
        registrations.forEach(reg => reg.unregister());
    });

// Clear localStorage
localStorage.clear();
sessionStorage.clear();
```

### **Reset Firebase**
```javascript
// Delete Firebase app and reinitialize
firebase.apps.forEach(app => app.delete());
firebase.initializeApp(firebaseConfig);
```

### **Force Refresh Token**
```javascript
// Force token refresh
if (messaging) {
    messaging.deleteToken()
        .then(() => messaging.getToken())
        .then(token => console.log('New token:', token));
}
```

---

## 📞 **Getting Help**

### **Test Interface**
Use the built-in test interface: `http://yukimart.local/fcm-test.html`

### **API Endpoints for Testing**
```bash
# Test FCM configuration
curl -X GET http://yukimart.local/api/v1/fcm/test-config \
  -H "Authorization: Bearer YOUR_TOKEN"

# Get FCM statistics
curl -X GET http://yukimart.local/api/v1/fcm/statistics \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### **Log Analysis**
Check Laravel logs for FCM-related errors:
```bash
tail -f storage/logs/laravel.log | grep FCM
```

### **Browser Developer Tools**
- **Console:** Check for JavaScript errors
- **Network:** Verify API requests
- **Application:** Check Service Workers and Storage
- **Security:** Verify HTTPS and certificates

---

## ✅ **Success Checklist**

Before reporting issues, verify:

- [ ] Browser supports FCM (Chrome 50+, Firefox 44+, Safari 11.1+)
- [ ] Site is accessed via HTTPS or localhost
- [ ] Service Workers are enabled
- [ ] Notifications permission granted
- [ ] Firebase SDK loaded successfully
- [ ] Firebase configuration is correct
- [ ] FCM server key is set in .env
- [ ] VAPID key is configured
- [ ] Service worker file is accessible
- [ ] No console errors
- [ ] Network requests are successful

If all checks pass and FCM still doesn't work, use the fallback mode for basic notification functionality.
