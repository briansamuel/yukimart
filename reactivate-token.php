<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Reactivate token
$token = 'c7EZgqA-S76KNE3DSiqp3_:APA91bF1N6GHrxKxuvmZbPgmLRfD_T-KoEKnd5zM9hPM0V7_s7c5gT6pizPuQBg6SzmMKo1rV5uyBDdxLpFdrftCgWqOxGQEuo-EuqiQvKEhAgCAJN3R82g';

$updated = \App\Models\FCMToken::where('token', $token)->update(['is_active' => true]);

echo "Reactivated $updated token(s)\n";

// Check status
$fcmToken = \App\Models\FCMToken::where('token', $token)->first();
if ($fcmToken) {
    echo "Token status: " . ($fcmToken->is_active ? 'ACTIVE' : 'INACTIVE') . "\n";
    echo "User ID: " . $fcmToken->user_id . "\n";
    echo "Device: " . $fcmToken->device_name . "\n";
} else {
    echo "Token not found\n";
}
