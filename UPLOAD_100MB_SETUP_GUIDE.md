# 📤 Cấu hình Upload 100MB - Hướng dẫn hoàn chỉnh

## 🚨 Lỗi 413 Request Entity Too Large - ĐÃ SỬA

### ✅ **Đã cập nhật trong code:**
- Laravel validation: `max:102400` (100MB)
- JavaScript validation: Check file size trước upload
- UI text: "Kích thước tệp tối đa: 100MB"
- Translation keys: Thêm `max_file_size_100mb`

## 🔧 **Cấu hình Server cần thiết**

### 1. **PHP Configuration (php.ini)**
```ini
; File upload settings
upload_max_filesize = 100M
post_max_size = 100M
max_file_uploads = 20

; Execution settings  
max_execution_time = 300
max_input_time = 300
memory_limit = 512M
max_input_vars = 3000

; Session settings
session.gc_maxlifetime = 1800
```

**Vị trí file php.ini:**
- **XAMPP**: `C:\xampp\php\php.ini`
- **WAMP**: `C:\wamp64\bin\php\php8.x\php.ini`
- **Linux**: `/etc/php/8.x/apache2/php.ini` hoặc `/etc/php/8.x/fpm/php.ini`
- **Docker**: Mount custom php.ini

### 2. **Apache Configuration**

**Thêm vào .htaccess trong thư mục public:**
```apache
# PHP Upload Settings
php_value upload_max_filesize 100M
php_value post_max_size 100M
php_value max_execution_time 300
php_value memory_limit 512M
php_value max_input_time 300

# Apache Request Body Limit
LimitRequestBody 104857600

# Timeout Settings
TimeOut 300
```

**Hoặc trong httpd.conf/apache2.conf:**
```apache
<Directory "/path/to/your/laravel/public">
    php_admin_value upload_max_filesize 100M
    php_admin_value post_max_size 100M
    php_admin_value max_execution_time 300
    php_admin_value memory_limit 512M
    LimitRequestBody 104857600
</Directory>
```

### 3. **Nginx Configuration**

**Thêm vào nginx.conf hoặc site config:**
```nginx
server {
    # Global settings
    client_max_body_size 100M;
    client_body_timeout 300s;
    client_header_timeout 300s;
    
    # Specific for upload endpoint
    location /admin/products/import/upload {
        client_max_body_size 100M;
        client_body_timeout 300s;
        
        fastcgi_read_timeout 300s;
        fastcgi_send_timeout 300s;
        fastcgi_connect_timeout 300s;
    }
}
```

### 4. **Docker Configuration**

**docker-compose.yml:**
```yaml
services:
  php:
    build: .
    environment:
      - PHP_UPLOAD_MAX_FILESIZE=100M
      - PHP_POST_MAX_SIZE=100M
      - PHP_MAX_EXECUTION_TIME=300
      - PHP_MEMORY_LIMIT=512M
    volumes:
      - ./php-config/uploads.ini:/usr/local/etc/php/conf.d/uploads.ini

  nginx:
    image: nginx:alpine
    volumes:
      - ./nginx-config/default.conf:/etc/nginx/conf.d/default.conf
```

**php-config/uploads.ini:**
```ini
upload_max_filesize = 100M
post_max_size = 100M
max_execution_time = 300
memory_limit = 512M
max_input_time = 300
```

## 🔍 **Kiểm tra cấu hình**

### 1. **Tạo file test-upload.php:**
```php
<?php
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "post_max_size: " . ini_get('post_max_size') . "\n";
echo "max_execution_time: " . ini_get('max_execution_time') . "\n";
echo "memory_limit: " . ini_get('memory_limit') . "\n";
echo "max_file_uploads: " . ini_get('max_file_uploads') . "\n";
?>
```

### 2. **Laravel Artisan Commands:**
```bash
# Check PHP configuration
php -i | grep upload_max_filesize
php -i | grep post_max_size
php -i | grep max_execution_time

# Clear Laravel caches
php artisan config:clear
php artisan route:clear
php artisan cache:clear
```

### 3. **Browser Developer Tools:**
- Network tab → Check request headers
- Console → Check for JavaScript errors
- Response → Check for 413 errors

## 🚀 **Test Upload**

### 1. **Tạo file test lớn:**
```bash
# Tạo file 50MB để test
dd if=/dev/zero of=test-50mb.csv bs=1M count=50

# Hoặc tạo Excel file lớn với nhiều dòng dữ liệu
```

### 2. **Test qua curl:**
```bash
curl -X POST \
  -F "import_file=@test-50mb.csv" \
  -H "X-CSRF-TOKEN: your-token" \
  http://your-domain/admin/products/import/upload
```

### 3. **Monitor logs:**
```bash
# PHP error log
tail -f /var/log/php_errors.log

# Nginx error log  
tail -f /var/log/nginx/error.log

# Apache error log
tail -f /var/log/apache2/error.log

# Laravel log
tail -f storage/logs/laravel.log
```

## ⚡ **Performance Optimization**

### 1. **Chunked Upload (Advanced):**
```javascript
// Implement chunked upload for very large files
const chunkSize = 1024 * 1024; // 1MB chunks
const totalChunks = Math.ceil(file.size / chunkSize);

for (let i = 0; i < totalChunks; i++) {
    const start = i * chunkSize;
    const end = Math.min(start + chunkSize, file.size);
    const chunk = file.slice(start, end);
    
    // Upload each chunk
    await uploadChunk(chunk, i, totalChunks);
}
```

### 2. **Progress Indicator:**
```javascript
const xhr = new XMLHttpRequest();
xhr.upload.addEventListener('progress', (e) => {
    if (e.lengthComputable) {
        const percentComplete = (e.loaded / e.total) * 100;
        updateProgressBar(percentComplete);
    }
});
```

## 🎯 **Troubleshooting**

### **Vẫn gặp lỗi 413:**
1. Restart web server sau khi thay đổi config
2. Check multiple config files (php.ini, .htaccess, nginx.conf)
3. Verify file permissions
4. Check disk space

### **Upload chậm:**
1. Increase timeout values
2. Optimize server resources
3. Use SSD storage
4. Consider CDN for static files

### **Memory errors:**
1. Increase PHP memory_limit
2. Process files in chunks
3. Use streaming for large files
4. Monitor server resources

## ✅ **Kết quả**

Sau khi cấu hình:
- ✅ Upload file lên đến 100MB
- ✅ Timeout 5 phút cho xử lý
- ✅ Memory 512MB cho PHP
- ✅ Progress indicator cho user
- ✅ Error handling tốt hơn

**🎊 Hệ thống đã sẵn sàng xử lý file Excel/CSV lớn!**
