# 📤 Cấu hình Upload 100MB cho Product Import

## 🚨 Lỗi 413 Request Entity Too Large

### Nguyên nhân:
- Server giới hạn kích thước request
- PHP giới hạn upload file
- Nginx/Apache giới hạn body size

## 🔧 Giải pháp

### 1. **PHP Configuration (php.ini)**
```ini
; Maximum allowed size for uploaded files
upload_max_filesize = 100M

; Maximum size of POST data
post_max_size = 100M

; Maximum execution time (5 minutes)
max_execution_time = 300

; Maximum memory limit
memory_limit = 512M

; Maximum input time
max_input_time = 300
```

### 2. **Nginx Configuration**
```nginx
server {
    # Maximum client body size
    client_max_body_size 100M;
    
    # Timeout settings
    client_body_timeout 300s;
    client_header_timeout 300s;
}
```

### 3. **Apache Configuration (.htaccess)**
```apache
# Maximum file upload size
php_value upload_max_filesize 100M
php_value post_max_size 100M
php_value max_execution_time 300
php_value memory_limit 512M
php_value max_input_time 300

# Apache specific
LimitRequestBody 104857600
```

### 4. **Laravel Configuration**
```php
// config/app.php or .env
MAX_FILE_SIZE=104857600  // 100MB in bytes
```

## 🐳 Docker Configuration

### docker-compose.yml:
```yaml
services:
  php:
    environment:
      - PHP_UPLOAD_MAX_FILESIZE=100M
      - PHP_POST_MAX_SIZE=100M
      - PHP_MAX_EXECUTION_TIME=300
      - PHP_MEMORY_LIMIT=512M
```

### Dockerfile:
```dockerfile
RUN echo "upload_max_filesize = 100M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size = 100M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "max_execution_time = 300" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "memory_limit = 512M" >> /usr/local/etc/php/conf.d/uploads.ini
```

## 📋 Kiểm tra cấu hình

### 1. Tạo file info.php:
```php
<?php phpinfo(); ?>
```

### 2. Kiểm tra các giá trị:
- upload_max_filesize
- post_max_size  
- max_execution_time
- memory_limit

### 3. Laravel command:
```bash
php -i | grep upload_max_filesize
php -i | grep post_max_size
```
