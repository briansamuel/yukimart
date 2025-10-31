# YukiMart Multi-Tenant System - Production Deployment Guide

## 🚀 **PRODUCTION DEPLOYMENT CHECKLIST**

### **Prerequisites**
- [ ] PHP 8.1+ with required extensions
- [ ] MySQL 8.0+ or PostgreSQL 13+
- [ ] Redis for caching and sessions
- [ ] Nginx or Apache web server
- [ ] SSL certificate for HTTPS
- [ ] Domain with subdomain support

### **Step 1: Server Setup**

#### **1.1 Install Dependencies**
```bash
# Ubuntu/Debian
sudo apt update
sudo apt install php8.1-fpm php8.1-mysql php8.1-redis php8.1-gd php8.1-curl php8.1-zip php8.1-xml php8.1-mbstring
sudo apt install nginx mysql-server redis-server
sudo apt install certbot python3-certbot-nginx

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

#### **1.2 Configure Nginx**
```nginx
# /etc/nginx/sites-available/yukimart
server {
    listen 80;
    server_name yukimart.local *.yukimart.local;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name yukimart.local *.yukimart.local;
    root /var/www/yukimart/public;
    index index.php;

    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/yukimart.local/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yukimart.local/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;

    # Laravel Configuration
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # File Upload Limits
    client_max_body_size 100M;
    
    # Gzip Compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/javascript application/xml+rss application/json;
}
```

### **Step 2: Application Deployment**

#### **2.1 Clone and Setup**
```bash
# Clone repository
cd /var/www
git clone https://github.com/your-repo/yukimart.git
cd yukimart

# Install dependencies
composer install --optimize-autoloader --no-dev
npm install && npm run production

# Set permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

#### **2.2 Environment Configuration**
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure .env file
nano .env
```

#### **2.3 Production .env Configuration**
```env
APP_NAME="YukiMart Multi-Tenant"
APP_ENV=production
APP_KEY=base64:YOUR_GENERATED_KEY
APP_DEBUG=false
APP_URL=https://yukimart.local

LOG_CHANNEL=daily
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=yukimart_production
DB_USERNAME=yukimart_user
DB_PASSWORD=STRONG_PASSWORD

BROADCAST_DRIVER=redis
CACHE_DRIVER=redis
FILESYSTEM_DISK=local
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yukimart.local
MAIL_FROM_NAME="${APP_NAME}"

# Security
SANCTUM_STATEFUL_DOMAINS=yukimart.local,*.yukimart.local
SESSION_DOMAIN=.yukimart.local
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=strict

# File Storage
FILESYSTEM_DISK=public
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false
```

### **Step 3: Database Setup**

#### **3.1 Create Database and User**
```sql
-- MySQL
CREATE DATABASE yukimart_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'yukimart_user'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD';
GRANT ALL PRIVILEGES ON yukimart_production.* TO 'yukimart_user'@'localhost';
FLUSH PRIVILEGES;
```

#### **3.2 Run Migrations and Seeders**
```bash
# Run migrations
php artisan migrate --force

# Seed initial data
php artisan db:seed --class=PlatformSeeder
php artisan db:seed --class=TenantTestDataSeeder

# Create storage link
php artisan storage:link

# Clear and cache config
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### **Step 4: SSL Certificate Setup**

#### **4.1 Obtain SSL Certificate**
```bash
# Using Let's Encrypt
sudo certbot --nginx -d yukimart.local -d *.yukimart.local

# Or upload your own certificate to:
# /etc/ssl/certs/yukimart.local.crt
# /etc/ssl/private/yukimart.local.key
```

### **Step 5: Process Management**

#### **5.1 Setup Queue Workers**
```bash
# Create supervisor configuration
sudo nano /etc/supervisor/conf.d/yukimart-worker.conf
```

```ini
[program:yukimart-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/yukimart/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/var/www/yukimart/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
# Start supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start yukimart-worker:*
```

#### **5.2 Setup Cron Jobs**
```bash
# Add to crontab
sudo crontab -e

# Add this line:
* * * * * cd /var/www/yukimart && php artisan schedule:run >> /dev/null 2>&1
```

### **Step 6: Monitoring and Logging**

#### **6.1 Setup Log Rotation**
```bash
# Create logrotate configuration
sudo nano /etc/logrotate.d/yukimart
```

```
/var/www/yukimart/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    notifempty
    create 0644 www-data www-data
    postrotate
        /usr/bin/supervisorctl restart yukimart-worker:*
    endscript
}
```

#### **6.2 Setup Health Checks**
```bash
# Create health check script
nano /var/www/yukimart/health-check.sh
```

```bash
#!/bin/bash
# Health check script
curl -f https://yukimart.local/api/health || exit 1
```

### **Step 7: Security Hardening**

#### **7.1 Firewall Configuration**
```bash
# UFW Firewall
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

#### **7.2 Fail2Ban Setup**
```bash
# Install and configure fail2ban
sudo apt install fail2ban

# Create custom jail
sudo nano /etc/fail2ban/jail.local
```

```ini
[DEFAULT]
bantime = 3600
findtime = 600
maxretry = 5

[nginx-http-auth]
enabled = true

[nginx-limit-req]
enabled = true
```

### **Step 8: Backup Strategy**

#### **8.1 Database Backup Script**
```bash
# Create backup script
nano /var/www/yukimart/backup.sh
```

```bash
#!/bin/bash
BACKUP_DIR="/var/backups/yukimart"
DATE=$(date +%Y%m%d_%H%M%S)

# Create backup directory
mkdir -p $BACKUP_DIR

# Database backup
mysqldump -u yukimart_user -p yukimart_production > $BACKUP_DIR/database_$DATE.sql

# Files backup
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /var/www/yukimart/storage/app/public

# Keep only last 7 days
find $BACKUP_DIR -name "*.sql" -mtime +7 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +7 -delete
```

#### **8.2 Automated Backups**
```bash
# Add to crontab
0 2 * * * /var/www/yukimart/backup.sh
```

### **Step 9: Performance Optimization**

#### **9.1 PHP-FPM Optimization**
```ini
# /etc/php/8.1/fpm/pool.d/www.conf
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 500
```

#### **9.2 MySQL Optimization**
```ini
# /etc/mysql/mysql.conf.d/mysqld.cnf
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
query_cache_size = 64M
max_connections = 200
```

### **Step 10: Final Verification**

#### **10.1 Test Checklist**
- [ ] Main domain loads correctly (https://yukimart.local)
- [ ] Subdomain routing works (https://tenant1.yukimart.local)
- [ ] Admin login functions
- [ ] Tenant switching works
- [ ] API endpoints respond correctly
- [ ] File uploads work
- [ ] Email sending works
- [ ] Queue processing works
- [ ] Scheduled tasks run
- [ ] SSL certificate is valid
- [ ] All security headers present

#### **10.2 Performance Tests**
```bash
# Load testing with Apache Bench
ab -n 1000 -c 10 https://yukimart.local/

# Check response times
curl -w "@curl-format.txt" -o /dev/null -s https://yukimart.local/
```

### **Step 11: Go Live**

#### **11.1 DNS Configuration**
```
# DNS Records
A     yukimart.local          YOUR_SERVER_IP
A     *.yukimart.local        YOUR_SERVER_IP
CNAME www.yukimart.local      yukimart.local
```

#### **11.2 Final Steps**
```bash
# Clear all caches
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart services
sudo systemctl restart nginx
sudo systemctl restart php8.1-fpm
sudo systemctl restart mysql
sudo systemctl restart redis
```

## 🎉 **DEPLOYMENT COMPLETE!**

Your YukiMart Multi-Tenant System is now live and ready for production use!

### **Next Steps:**
1. Monitor logs for any issues
2. Set up regular backups
3. Configure monitoring alerts
4. Train users on the system
5. Plan for scaling as needed

### **Support:**
- Check logs: `/var/www/yukimart/storage/logs/`
- Monitor queues: `php artisan queue:monitor`
- Health check: `https://yukimart.local/api/health`
