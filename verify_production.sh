#!/bin/bash

# YukiMart Production Verification Script
# Usage: ./verify_production.sh

echo "🔍 Verifying YukiMart Production Deployment..."

# Check if site is accessible
echo "🌐 Checking website accessibility..."
if curl -s -o /dev/null -w "%{http_code}" http://localhost | grep -q "200"; then
    echo "✅ Website is accessible"
else
    echo "❌ Website is not accessible"
fi

# Check database connection
echo "🗄️  Checking database connection..."
php artisan tinker --execute="try { DB::connection()->getPdo(); echo 'Database: OK'; } catch(Exception \$e) { echo 'Database: ERROR - ' . \$e->getMessage(); }"

# Check critical routes
echo "🛣️  Checking critical routes..."
php artisan route:list | grep -E "(admin.order|admin.product|admin.customer)" | wc -l | xargs echo "Admin routes found:"

# Check logs for errors
echo "📋 Checking for recent errors..."
if [ -f "storage/logs/laravel.log" ]; then
    ERROR_COUNT=$(tail -100 storage/logs/laravel.log | grep -i error | wc -l)
    echo "Recent errors in logs: $ERROR_COUNT"
else
    echo "No log file found"
fi

# Check file permissions
echo "🔒 Checking file permissions..."
if [ -w "storage/logs" ]; then
    echo "✅ Storage directory is writable"
else
    echo "❌ Storage directory is not writable"
fi

echo "🎉 Verification completed!"
