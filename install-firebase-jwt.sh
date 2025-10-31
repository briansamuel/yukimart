#!/bin/bash

# Install Firebase JWT library for Service Account authentication
echo "Installing Firebase JWT library..."

# Install via Composer
composer require firebase/php-jwt

echo "Firebase JWT library installed successfully!"
echo ""
echo "Next steps:"
echo "1. Download Service Account JSON from Firebase Console"
echo "2. Place it in storage/app/firebase/service-account.json"
echo "3. Update .env with FCM_SERVICE_ACCOUNT_PATH"
echo "4. Run: php artisan fcm:setup"
