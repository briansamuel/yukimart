#!/bin/bash

# YukiMart Multi-Tenant Production Deployment Script
# Version: 1.0
# Date: 2025-08-11

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
PROJECT_NAME="YukiMart Multi-Tenant System"
PROJECT_DIR="/var/www/yukimart"
BACKUP_DIR="/var/backups/yukimart"
LOG_FILE="/var/log/yukimart-deployment.log"
NGINX_SITE="yukimart"
PHP_VERSION="8.3"

# Functions
log() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')] $1${NC}" | tee -a "$LOG_FILE"
}

error() {
    echo -e "${RED}[$(date +'%Y-%m-%d %H:%M:%S')] ERROR: $1${NC}" | tee -a "$LOG_FILE"
    exit 1
}

warning() {
    echo -e "${YELLOW}[$(date +'%Y-%m-%d %H:%M:%S')] WARNING: $1${NC}" | tee -a "$LOG_FILE"
}

info() {
    echo -e "${BLUE}[$(date +'%Y-%m-%d %H:%M:%S')] INFO: $1${NC}" | tee -a "$LOG_FILE"
}

check_requirements() {
    log "Checking system requirements..."
    
    # Check if running as root or with sudo
    if [[ $EUID -ne 0 ]]; then
        error "This script must be run as root or with sudo"
    fi
    
    # Check PHP version
    if ! command -v php &> /dev/null; then
        error "PHP is not installed"
    fi
    
    local php_version=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")
    if [[ "$php_version" < "8.3" ]]; then
        error "PHP 8.3 or higher is required. Current version: $php_version"
    fi
    
    # Check required services
    local services=("nginx" "mysql" "redis-server" "php${PHP_VERSION}-fpm")
    for service in "${services[@]}"; do
        if ! systemctl is-active --quiet "$service"; then
            warning "Service $service is not running. Attempting to start..."
            systemctl start "$service" || error "Failed to start $service"
        fi
    done
    
    # Check required PHP extensions
    local extensions=("mysql" "redis" "xml" "curl" "zip" "mbstring" "gd" "intl" "bcmath")
    for ext in "${extensions[@]}"; do
        if ! php -m | grep -q "$ext"; then
            error "PHP extension $ext is not installed"
        fi
    done
    
    log "✅ All requirements met"
}

create_backup() {
    log "Creating backup before deployment..."
    
    local timestamp=$(date +%Y%m%d_%H%M%S)
    local backup_path="$BACKUP_DIR/pre_deployment_$timestamp"
    
    mkdir -p "$backup_path"
    
    # Backup database
    if [[ -f "$PROJECT_DIR/.env" ]]; then
        local db_name=$(grep "^DB_DATABASE=" "$PROJECT_DIR/.env" | cut -d'=' -f2)
        local db_user=$(grep "^DB_USERNAME=" "$PROJECT_DIR/.env" | cut -d'=' -f2)
        local db_pass=$(grep "^DB_PASSWORD=" "$PROJECT_DIR/.env" | cut -d'=' -f2)
        
        if [[ -n "$db_name" && -n "$db_user" ]]; then
            log "Backing up database: $db_name"
            mysqldump -u"$db_user" -p"$db_pass" "$db_name" > "$backup_path/database.sql" || error "Database backup failed"
        fi
    fi
    
    # Backup application files
    if [[ -d "$PROJECT_DIR" ]]; then
        log "Backing up application files..."
        tar -czf "$backup_path/application.tar.gz" -C "$PROJECT_DIR" . \
            --exclude=node_modules \
            --exclude=storage/logs \
            --exclude=.git || error "Application backup failed"
    fi
    
    log "✅ Backup created at $backup_path"
}

install_dependencies() {
    log "Installing system dependencies..."
    
    # Update package list
    apt update
    
    # Install required packages
    local packages=(
        "nginx"
        "mysql-server"
        "redis-server"
        "php${PHP_VERSION}-fpm"
        "php${PHP_VERSION}-mysql"
        "php${PHP_VERSION}-redis"
        "php${PHP_VERSION}-xml"
        "php${PHP_VERSION}-curl"
        "php${PHP_VERSION}-zip"
        "php${PHP_VERSION}-mbstring"
        "php${PHP_VERSION}-gd"
        "php${PHP_VERSION}-intl"
        "php${PHP_VERSION}-bcmath"
        "certbot"
        "python3-certbot-nginx"
        "git"
        "curl"
        "unzip"
    )
    
    for package in "${packages[@]}"; do
        if ! dpkg -l | grep -q "^ii  $package "; then
            log "Installing $package..."
            apt install -y "$package" || error "Failed to install $package"
        fi
    done
    
    # Install Composer if not exists
    if ! command -v composer &> /dev/null; then
        log "Installing Composer..."
        curl -sS https://getcomposer.org/installer | php
        mv composer.phar /usr/local/bin/composer
        chmod +x /usr/local/bin/composer
    fi
    
    # Install Node.js if not exists
    if ! command -v node &> /dev/null; then
        log "Installing Node.js..."
        curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
        apt-get install -y nodejs
    fi
    
    log "✅ Dependencies installed"
}

deploy_application() {
    log "Deploying application..."
    
    # Create project directory if it doesn't exist
    mkdir -p "$PROJECT_DIR"
    cd "$PROJECT_DIR"
    
    # Set proper ownership
    chown -R www-data:www-data "$PROJECT_DIR"
    
    # Install PHP dependencies
    log "Installing PHP dependencies..."
    sudo -u www-data composer install --no-dev --optimize-autoloader --no-interaction
    
    # Install Node.js dependencies and build assets
    log "Building frontend assets..."
    sudo -u www-data npm install
    sudo -u www-data npm run production
    
    # Set up environment file
    if [[ ! -f "$PROJECT_DIR/.env" ]]; then
        log "Setting up environment file..."
        cp "$PROJECT_DIR/.env.production" "$PROJECT_DIR/.env"
        
        # Generate application key
        sudo -u www-data php artisan key:generate --force
    fi
    
    # Set proper permissions
    find "$PROJECT_DIR" -type f -exec chmod 644 {} \;
    find "$PROJECT_DIR" -type d -exec chmod 755 {} \;
    chmod -R 775 "$PROJECT_DIR/storage"
    chmod -R 775 "$PROJECT_DIR/bootstrap/cache"
    
    log "✅ Application deployed"
}

setup_database() {
    log "Setting up database..."
    
    cd "$PROJECT_DIR"
    
    # Run migrations
    log "Running database migrations..."
    sudo -u www-data php artisan migrate --force
    
    # Run production seeder
    log "Seeding production data..."
    sudo -u www-data php artisan db:seed --class=ProductionSeeder --force
    
    log "✅ Database setup completed"
}

configure_nginx() {
    log "Configuring Nginx..."
    
    # Copy Nginx configuration
    cp "$PROJECT_DIR/docs/deployment/nginx.conf" "/etc/nginx/sites-available/$NGINX_SITE"
    
    # Enable site
    ln -sf "/etc/nginx/sites-available/$NGINX_SITE" "/etc/nginx/sites-enabled/$NGINX_SITE"
    
    # Remove default site
    rm -f /etc/nginx/sites-enabled/default
    
    # Test Nginx configuration
    nginx -t || error "Nginx configuration test failed"
    
    # Reload Nginx
    systemctl reload nginx
    
    log "✅ Nginx configured"
}

setup_ssl() {
    log "Setting up SSL certificates..."
    
    # Check if certificates already exist
    if [[ -f "/etc/letsencrypt/live/yukimart.com/fullchain.pem" ]]; then
        log "SSL certificates already exist"
        return
    fi
    
    # Get SSL certificates
    log "Obtaining SSL certificates..."
    certbot --nginx -d yukimart.com -d "*.yukimart.com" --non-interactive --agree-tos --email admin@yukimart.com
    
    # Set up auto-renewal
    echo "0 12 * * * /usr/bin/certbot renew --quiet" | crontab -
    
    log "✅ SSL certificates configured"
}

optimize_system() {
    log "Optimizing system for production..."
    
    cd "$PROJECT_DIR"
    
    # Clear and cache Laravel configurations
    sudo -u www-data php artisan config:clear
    sudo -u www-data php artisan route:clear
    sudo -u www-data php artisan view:clear
    sudo -u www-data php artisan cache:clear
    
    sudo -u www-data php artisan config:cache
    sudo -u www-data php artisan route:cache
    sudo -u www-data php artisan view:cache
    
    # Create storage link
    sudo -u www-data php artisan storage:link
    
    # Optimize PHP-FPM
    local php_ini="/etc/php/${PHP_VERSION}/fpm/php.ini"
    sed -i 's/;opcache.enable=1/opcache.enable=1/' "$php_ini"
    sed -i 's/;opcache.memory_consumption=128/opcache.memory_consumption=256/' "$php_ini"
    sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 100M/' "$php_ini"
    sed -i 's/post_max_size = 8M/post_max_size = 100M/' "$php_ini"
    
    # Restart PHP-FPM
    systemctl restart "php${PHP_VERSION}-fpm"
    
    log "✅ System optimized"
}

setup_monitoring() {
    log "Setting up monitoring..."
    
    # Create log rotation for application logs
    cat > /etc/logrotate.d/yukimart << EOF
$PROJECT_DIR/storage/logs/*.log {
    daily
    missingok
    rotate 52
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
}
EOF
    
    # Set up cron jobs
    (crontab -l 2>/dev/null; echo "0 2 * * * $PROJECT_DIR/scripts/backup.sh") | crontab -
    (crontab -l 2>/dev/null; echo "*/5 * * * * curl -f http://localhost/health > /dev/null 2>&1") | crontab -
    
    log "✅ Monitoring configured"
}

verify_deployment() {
    log "Verifying deployment..."
    
    # Check if services are running
    local services=("nginx" "mysql" "redis-server" "php${PHP_VERSION}-fpm")
    for service in "${services[@]}"; do
        if ! systemctl is-active --quiet "$service"; then
            error "Service $service is not running"
        fi
    done
    
    # Check if application is accessible
    local test_urls=(
        "http://localhost"
        "http://localhost/health"
    )
    
    for url in "${test_urls[@]}"; do
        if ! curl -f "$url" > /dev/null 2>&1; then
            warning "URL $url is not accessible"
        fi
    done
    
    log "✅ Deployment verified"
}

main() {
    log "🚀 Starting $PROJECT_NAME deployment..."
    log "Deployment started at $(date)"
    
    check_requirements
    create_backup
    install_dependencies
    deploy_application
    setup_database
    configure_nginx
    setup_ssl
    optimize_system
    setup_monitoring
    verify_deployment
    
    log "🎉 Deployment completed successfully!"
    log "Deployment finished at $(date)"
    
    info "Next steps:"
    info "1. Configure DNS records for your domain"
    info "2. Test all tenant subdomains"
    info "3. Set up monitoring and alerting"
    info "4. Configure backup systems"
    info "5. Run security audit"
    
    info "Tenant URLs:"
    info "• TechMart: https://tenant1.yukimart.com"
    info "• Fashion: https://tenant2.yukimart.com"
    info "• Food & Beverage: https://tenant3.yukimart.com"
    info "• HelloMart: https://hellomart.yukimart.com"
    info "• BiboMart: https://bibomart.yukimart.com"
}

# Run main function
main "$@"
