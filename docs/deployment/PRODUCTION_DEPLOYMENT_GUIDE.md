# 🚀 PRODUCTION DEPLOYMENT GUIDE

## **YukiMart Multi-Tenant System Production Deployment**

**Version**: 1.0  
**Date**: 2025-08-11  
**Status**: Ready for Production  

---

## 📋 **PRE-DEPLOYMENT CHECKLIST**

### **✅ System Requirements**
- [x] **PHP 8.3+** with required extensions
- [x] **MySQL 8.0+** or MariaDB 10.6+
- [x] **Nginx** or Apache with subdomain support
- [x] **SSL Certificate** (wildcard recommended)
- [x] **Redis** for caching and sessions
- [x] **Node.js 18+** for asset compilation

### **✅ Application Status**
- [x] **5 Active Tenants** configured and tested
- [x] **250+ Products** seeded across tenants
- [x] **21 Users** with role-based access
- [x] **16 Branch Shops** across all tenants
- [x] **Subdomain Routing** 100% functional
- [x] **Database Structure** optimized and indexed

---

## 🌐 **DNS CONFIGURATION**

### **Required DNS Records**

```dns
# Main domain
yukimart.com.           A       YOUR_SERVER_IP
www.yukimart.com.       CNAME   yukimart.com.

# Wildcard subdomain for tenants
*.yukimart.com.         A       YOUR_SERVER_IP

# Specific tenant subdomains (optional, for better control)
tenant1.yukimart.com.   A       YOUR_SERVER_IP
tenant2.yukimart.com.   A       YOUR_SERVER_IP
tenant3.yukimart.com.   A       YOUR_SERVER_IP
hellomart.yukimart.com. A       YOUR_SERVER_IP
bibomart.yukimart.com.  A       YOUR_SERVER_IP
```

### **SSL Certificate Setup**

```bash
# Option 1: Wildcard SSL Certificate (Recommended)
certbot certonly --dns-cloudflare \
  --dns-cloudflare-credentials ~/.secrets/cloudflare.ini \
  -d yukimart.com \
  -d *.yukimart.com

# Option 2: Individual certificates
certbot certonly --webroot -w /var/www/yukimart/public \
  -d yukimart.com \
  -d www.yukimart.com \
  -d tenant1.yukimart.com \
  -d tenant2.yukimart.com \
  -d tenant3.yukimart.com \
  -d hellomart.yukimart.com \
  -d bibomart.yukimart.com
```

---

## 🔧 **SERVER CONFIGURATION**

### **Nginx Configuration**

```nginx
# /etc/nginx/sites-available/yukimart
server {
    listen 80;
    server_name yukimart.com *.yukimart.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name yukimart.com *.yukimart.com;
    
    root /var/www/yukimart/public;
    index index.php index.html;
    
    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/yukimart.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yukimart.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512;
    ssl_prefer_server_ciphers off;
    
    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;
    
    # PHP Configuration
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }
    
    error_page 404 /index.php;
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        
        # Pass subdomain to PHP
        fastcgi_param HTTP_HOST $host;
        fastcgi_param SERVER_NAME $host;
    }
    
    location ~ /\.(?!well-known).* {
        deny all;
    }
    
    # Asset optimization
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

### **PHP-FPM Configuration**

```ini
# /etc/php/8.3/fpm/pool.d/yukimart.conf
[yukimart]
user = www-data
group = www-data
listen = /var/run/php/php8.3-fpm-yukimart.sock
listen.owner = www-data
listen.group = www-data
listen.mode = 0660

pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 500

# Environment variables
env[APP_ENV] = production
env[APP_DEBUG] = false
env[DB_HOST] = localhost
env[DB_DATABASE] = yukimart_production
env[REDIS_HOST] = localhost
```

---

## 🗄️ **DATABASE SETUP**

### **Production Database Configuration**

```sql
-- Create production database
CREATE DATABASE yukimart_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create dedicated user
CREATE USER 'yukimart_prod'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD_HERE';
GRANT ALL PRIVILEGES ON yukimart_production.* TO 'yukimart_prod'@'localhost';
FLUSH PRIVILEGES;

-- Optimize MySQL for production
SET GLOBAL innodb_buffer_pool_size = 1073741824; -- 1GB
SET GLOBAL max_connections = 200;
SET GLOBAL query_cache_size = 67108864; -- 64MB
```

### **Database Migration & Seeding**

```bash
# Run migrations
php artisan migrate --force

# Seed production data
php artisan db:seed --class=SimpleTenantDataSeeder
php artisan db:seed --class=SimpleProductSeeder

# Create indexes for performance
php artisan db:seed --class=ProductionIndexSeeder
```

---

## 🔐 **SECURITY CONFIGURATION**

### **Environment Variables**

```env
# Production Environment
APP_NAME="YukiMart"
APP_ENV=production
APP_KEY=base64:GENERATE_NEW_KEY_HERE
APP_DEBUG=false
APP_URL=https://yukimart.com

# Database
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=yukimart_production
DB_USERNAME=yukimart_prod
DB_PASSWORD=STRONG_DATABASE_PASSWORD

# Cache & Sessions
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Redis
REDIS_HOST=localhost
REDIS_PASSWORD=REDIS_PASSWORD_HERE
REDIS_PORT=6379

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls

# Security
BCRYPT_ROUNDS=12
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
```

### **File Permissions**

```bash
# Set proper ownership
sudo chown -R www-data:www-data /var/www/yukimart

# Set directory permissions
sudo find /var/www/yukimart -type d -exec chmod 755 {} \;

# Set file permissions
sudo find /var/www/yukimart -type f -exec chmod 644 {} \;

# Storage and cache directories
sudo chmod -R 775 /var/www/yukimart/storage
sudo chmod -R 775 /var/www/yukimart/bootstrap/cache
```

---

## 🚀 **DEPLOYMENT PROCESS**

### **Step 1: Server Preparation**

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install required packages
sudo apt install -y nginx mysql-server redis-server php8.3-fpm \
  php8.3-mysql php8.3-redis php8.3-xml php8.3-curl php8.3-zip \
  php8.3-mbstring php8.3-gd php8.3-intl php8.3-bcmath

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs
```

### **Step 2: Application Deployment**

```bash
# Clone repository
cd /var/www
sudo git clone https://github.com/your-repo/yukimart.git
cd yukimart

# Install dependencies
composer install --no-dev --optimize-autoloader
npm install && npm run production

# Set up environment
sudo cp .env.production .env
php artisan key:generate
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations and seeders
php artisan migrate --force
php artisan db:seed --class=SimpleTenantDataSeeder
php artisan db:seed --class=SimpleProductSeeder
```

### **Step 3: Service Configuration**

```bash
# Enable and start services
sudo systemctl enable nginx mysql redis-server php8.3-fpm
sudo systemctl start nginx mysql redis-server php8.3-fpm

# Configure Nginx
sudo ln -s /etc/nginx/sites-available/yukimart /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx

# Set up SSL
sudo certbot --nginx -d yukimart.com -d *.yukimart.com
```

---

## 📊 **MONITORING & MAINTENANCE**

### **Health Check Endpoints**

```bash
# System health
curl https://yukimart.com/health

# Tenant health
curl https://tenant1.yukimart.com/api/tenant/info
curl https://hellomart.yukimart.com/api/tenant/info
```

### **Log Monitoring**

```bash
# Application logs
tail -f /var/www/yukimart/storage/logs/laravel.log

# Nginx logs
tail -f /var/log/nginx/access.log
tail -f /var/log/nginx/error.log

# PHP-FPM logs
tail -f /var/log/php8.3-fpm.log
```

### **Performance Optimization**

```bash
# Enable OPcache
echo "opcache.enable=1" >> /etc/php/8.3/fpm/php.ini
echo "opcache.memory_consumption=256" >> /etc/php/8.3/fpm/php.ini

# Configure Redis
echo "maxmemory 512mb" >> /etc/redis/redis.conf
echo "maxmemory-policy allkeys-lru" >> /etc/redis/redis.conf

# Restart services
sudo systemctl restart php8.3-fpm redis-server
```

---

## 🎯 **POST-DEPLOYMENT VERIFICATION**

### **Functional Tests**

```bash
# Test all tenant subdomains
curl -I https://tenant1.yukimart.com
curl -I https://tenant2.yukimart.com
curl -I https://tenant3.yukimart.com
curl -I https://hellomart.yukimart.com
curl -I https://bibomart.yukimart.com

# Test admin login pages
curl -s https://tenant1.yukimart.com/admin/login | grep "Admin Login"
curl -s https://hellomart.yukimart.com/admin/login | grep "Admin Login"

# Test API endpoints
curl https://tenant1.yukimart.com/api/tenant/info
curl https://bibomart.yukimart.com/api/tenant/info
```

### **Performance Tests**

```bash
# Load testing with Apache Bench
ab -n 1000 -c 10 https://yukimart.com/
ab -n 500 -c 5 https://tenant1.yukimart.com/admin/login

# Database performance
mysql -u yukimart_prod -p -e "SHOW PROCESSLIST;"
mysql -u yukimart_prod -p -e "SHOW ENGINE INNODB STATUS\G"
```

---

## 🔄 **BACKUP & RECOVERY**

### **Automated Backup Script**

```bash
#!/bin/bash
# /usr/local/bin/yukimart-backup.sh

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/yukimart"
DB_NAME="yukimart_production"

# Create backup directory
mkdir -p $BACKUP_DIR

# Database backup
mysqldump -u yukimart_prod -p$DB_PASSWORD $DB_NAME > $BACKUP_DIR/db_$DATE.sql

# Application backup
tar -czf $BACKUP_DIR/app_$DATE.tar.gz /var/www/yukimart \
  --exclude=/var/www/yukimart/storage/logs \
  --exclude=/var/www/yukimart/node_modules

# Cleanup old backups (keep 7 days)
find $BACKUP_DIR -name "*.sql" -mtime +7 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +7 -delete

echo "Backup completed: $DATE"
```

### **Cron Job Setup**

```bash
# Add to crontab
0 2 * * * /usr/local/bin/yukimart-backup.sh >> /var/log/yukimart-backup.log 2>&1
```

---

## 📞 **SUPPORT & TROUBLESHOOTING**

### **Common Issues**

1. **Subdomain not resolving**: Check DNS configuration and Nginx server_name
2. **SSL certificate errors**: Verify certificate paths and renewal
3. **Database connection issues**: Check credentials and MySQL service status
4. **Permission errors**: Verify file ownership and permissions

### **Emergency Contacts**

- **System Administrator**: admin@yukimart.com
- **Database Administrator**: dba@yukimart.com
- **Security Team**: security@yukimart.com

---

**🎉 Production deployment guide completed!**  
**System is ready for live deployment with 5 tenants and 250+ products.**
