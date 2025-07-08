# 🚨 Debug 413 Error - Step by Step Guide

## ✅ PHP Config is OK - Issue is Web Server Level

Since PHP configuration shows all ✅ OK but still getting 413 with 2MB file, the problem is definitely at **web server level**.

## 🔍 Step-by-Step Debug Process

### Step 1: Identify Web Server
**Run:** `http://your-domain/check-server-info.php`

**Look for:**
- Server Software (Apache/Nginx/IIS)
- PHP SAPI (apache2handler/fpm-fcgi/cgi)
- Operating System

### Step 2: Test Simple Upload
**Run:** `http://your-domain/test-upload-simple.html`

**Test with:**
- Small file (< 1MB)
- Medium file (2-5MB) 
- Large file (10MB+)

**Expected results:**
- ✅ Small file works → Server config issue
- ❌ Small file fails → Critical server problem

### Step 3: Check Web Server Config

#### **If Apache:**
**Run:** `http://your-domain/check-apache-modules.php`

**Check:**
- ✅ mod_rewrite loaded
- ✅ .htaccess exists and readable
- ✅ Upload config in .htaccess

**Fix Apache:**
```bash
# Restart Apache
sudo systemctl restart apache2

# Check error log
sudo tail -f /var/log/apache2/error.log

# Test config
sudo apache2ctl configtest
```

#### **If Nginx:**
**Add to nginx.conf:**
```nginx
server {
    client_max_body_size 100M;
    client_body_timeout 300s;
    client_header_timeout 300s;
}
```

**Restart Nginx:**
```bash
sudo systemctl restart nginx
sudo tail -f /var/log/nginx/error.log
```

#### **If XAMPP/WAMP:**
1. **Edit php.ini** from control panel
2. **Restart Apache** from control panel  
3. **Check httpd.conf** for AllowOverride All

### Step 4: Test Laravel Endpoint
**After fixing server config:**

```javascript
// Test in browser console
const formData = new FormData();
const fileInput = document.querySelector('input[type="file"]');
formData.append('import_file', fileInput.files[0]);

fetch('/admin/products/import/test-upload', {
    method: 'POST',
    headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: formData
})
.then(response => {
    console.log('Status:', response.status);
    return response.json();
})
.then(data => console.log('Response:', data));
```

## 🎯 Common Solutions by Server Type

### **Apache Solutions:**

#### **Solution 1: Restart Apache**
```bash
# Ubuntu/Debian
sudo systemctl restart apache2

# CentOS/RHEL
sudo systemctl restart httpd

# XAMPP
# Use XAMPP Control Panel
```

#### **Solution 2: Check .htaccess Override**
```apache
# In httpd.conf or virtual host
<Directory "/path/to/your/site">
    AllowOverride All
</Directory>
```

#### **Solution 3: Add to Virtual Host**
```apache
<VirtualHost *:80>
    # Your existing config
    
    # Add these lines
    LimitRequestBody 104857600
    php_admin_value upload_max_filesize 100M
    php_admin_value post_max_size 100M
</VirtualHost>
```

### **Nginx Solutions:**

#### **Solution 1: Update nginx.conf**
```nginx
http {
    client_max_body_size 100M;
    
    server {
        listen 80;
        
        # Specific for upload endpoints
        location ~ ^/admin/products/import/ {
            client_max_body_size 100M;
            client_body_timeout 300s;
        }
    }
}
```

#### **Solution 2: Check PHP-FPM**
```ini
# /etc/php/8.x/fpm/php.ini
upload_max_filesize = 100M
post_max_size = 100M
```

```bash
sudo systemctl restart php8.3-fpm
```

### **IIS Solutions:**

#### **Solution 1: web.config**
```xml
<configuration>
    <system.webServer>
        <security>
            <requestFiltering>
                <requestLimits maxAllowedContentLength="104857600" />
            </requestFiltering>
        </security>
    </system.webServer>
</configuration>
```

## 🔧 Emergency Fixes

### **Quick Test - Disable .htaccess**
```bash
# Temporarily rename .htaccess
mv public/.htaccess public/.htaccess.backup

# Test upload
# If works → .htaccess issue
# If still fails → Apache/server config issue

# Restore .htaccess
mv public/.htaccess.backup public/.htaccess
```

### **Quick Test - Minimal .htaccess**
```apache
# Create minimal .htaccess for testing
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^ index.php [L]

# Upload config
LimitRequestBody 104857600
```

### **Quick Test - Direct PHP**
Create `test-direct.php`:
```php
<?php
if ($_POST) {
    var_dump($_FILES);
    exit;
}
?>
<form method="post" enctype="multipart/form-data">
    <input type="file" name="test">
    <input type="submit" value="Upload">
</form>
```

## 📊 Expected Results After Fix

### ✅ **Success Indicators:**
- `test-upload-simple.html` works with 2MB+ files
- No 413 errors in browser Network tab
- Laravel test endpoint returns success
- Server logs show no body size errors

### ❌ **Still Need Investigation:**
- 413 with very small files → Critical server issue
- Works in simple test but fails in Laravel → Application issue
- Intermittent failures → Load balancer/proxy issue

## 🚀 Final Verification

1. **Run all test files** in order
2. **Check server logs** during upload
3. **Test with different file sizes**
4. **Verify Laravel endpoint** works
5. **Test production upload** flow

**Once simple upload test works, Laravel should work too!**
