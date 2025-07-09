#!/bin/bash

# YukiMart Production Deployment Script
# Usage: ./deploy.sh

echo "🚀 Starting YukiMart Production Deployment..."

# 1. Backup current state
echo "📦 Creating backup..."
php artisan backup:run --only-db
tar -czf "yukimart-backup-$(date +%Y%m%d-%H%M%S).tar.gz" --exclude='node_modules' --exclude='.git' .

# 2. Pull latest code
echo "📥 Pulling latest code..."
git pull origin main

# 3. Install dependencies
echo "📚 Installing dependencies..."
composer install --no-dev --optimize-autoloader

# 4. Run migrations
echo "🗄️  Running migrations..."
php artisan migrate --force

# 5. Clear and optimize
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

echo "⚡ Optimizing..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# 6. Set permissions
echo "🔒 Setting permissions..."
chmod -R 755 storage
chmod -R 755 bootstrap/cache

# 7. Restart services (uncomment if needed)
# sudo systemctl restart nginx
# sudo systemctl restart php8.3-fpm

echo "✅ Deployment completed successfully!"
echo "🔍 Don't forget to:"
echo "   - Update .env with production settings"
echo "   - Test the application"
echo "   - Monitor logs for any issues"
