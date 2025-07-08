# 🚨 Debug 413 Request Entity Too Large

## 📋 Checklist để sửa lỗi 413

### 1. **Kiểm tra PHP Configuration**
Truy cập: `http://your-domain/check-php-config.php`

**Cần kiểm tra:**
- ✅ `upload_max_filesize` >= 100M
- ✅ `post_max_size` >= 100M  
- ✅ `max_execution_time` >= 300
- ✅ `memory_limit` >= 512M

### 2. **Test Upload đơn giản**
```bash
# Test với curl
curl -X POST \
  -F "import_file=@your-file.xlsx" \
  -H "X-CSRF-TOKEN: your-token" \
  http://your-domain/admin/products/import/test-upload
```

### 3. **Kiểm tra Web Server**

#### **Apache:**
- File `.htaccess` đã được cập nhật
- Restart Apache: `sudo systemctl restart apache2`
- Check error log: `tail -f /var/log/apache2/error.log`

#### **Nginx:**
```nginx
# Thêm vào nginx.conf
server {
    client_max_body_size 100M;
    client_body_timeout 300s;
}
```
- Restart Nginx: `sudo systemctl restart nginx`
- Check error log: `tail -f /var/log/nginx/error.log`

### 4. **Kiểm tra PHP-FPM (nếu dùng)**
```ini
# /etc/php/8.x/fpm/php.ini
upload_max_filesize = 100M
post_max_size = 100M
max_execution_time = 300
memory_limit = 512M
```
- Restart PHP-FPM: `sudo systemctl restart php8.3-fpm`

### 5. **XAMPP/WAMP Configuration**

#### **XAMPP:**
- File: `C:\xampp\php\php.ini`
- Restart Apache từ XAMPP Control Panel

#### **WAMP:**
- File: `C:\wamp64\bin\php\php8.x\php.ini`
- Restart All Services từ WAMP

### 6. **Docker Configuration**
```yaml
# docker-compose.yml
services:
  php:
    environment:
      - PHP_UPLOAD_MAX_FILESIZE=100M
      - PHP_POST_MAX_SIZE=100M
    volumes:
      - ./php.ini:/usr/local/etc/php/php.ini
```

## 🔍 **Debug Steps**

### Step 1: Kiểm tra PHP Config
```bash
php -i | grep upload_max_filesize
php -i | grep post_max_size
```

### Step 2: Test với file nhỏ
- Tạo file CSV 1KB để test
- Nếu vẫn lỗi 413 → Vấn đề ở web server

### Step 3: Check Browser Network Tab
- F12 → Network → Upload file
- Xem request headers và response

### Step 4: Check Server Logs
```bash
# Apache
tail -f /var/log/apache2/error.log

# Nginx  
tail -f /var/log/nginx/error.log

# PHP
tail -f /var/log/php_errors.log

# Laravel
tail -f storage/logs/laravel.log
```

## 🛠️ **Common Solutions**

### **Solution 1: Restart Web Server**
```bash
# Apache
sudo systemctl restart apache2

# Nginx
sudo systemctl restart nginx

# PHP-FPM
sudo systemctl restart php8.3-fpm
```

### **Solution 2: Check File Permissions**
```bash
chmod 755 public/
chmod 644 public/.htaccess
chmod 644 public/.user.ini
```

### **Solution 3: Clear All Caches**
```bash
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan view:clear
```

### **Solution 4: Temporary Disable Security**
```apache
# Temporary add to .htaccess for testing
SecRuleEngine Off
```

## 🎯 **Quick Test Commands**

### Test 1: PHP Info
```php
<?php
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "post_max_size: " . ini_get('post_max_size') . "\n";
?>
```

### Test 2: Simple Upload
```html
<form method="post" enctype="multipart/form-data">
    <input type="file" name="test_file">
    <input type="submit" value="Upload">
</form>
```

### Test 3: AJAX Upload
```javascript
const formData = new FormData();
formData.append('test_file', file);

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
.then(data => console.log('Response:', data))
.catch(error => console.error('Error:', error));
```

## 📊 **Expected Results**

### ✅ **Working Configuration:**
- PHP config shows 100M limits
- Test upload returns success
- No 413 errors in logs
- File uploads successfully

### ❌ **Problem Indicators:**
- 413 error with small files
- PHP config shows small limits
- Web server logs show body size errors
- Upload fails immediately

## 🚀 **Final Verification**

1. **Access**: `http://your-domain/check-php-config.php`
2. **Upload**: 2MB Excel file via form
3. **Check**: Browser Network tab for 413 errors
4. **Verify**: Laravel logs for successful upload
5. **Test**: AJAX upload via import page

**If still getting 413 with 2MB file, the issue is definitely at web server level, not Laravel!**
