<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    $user = \App\Models\User::where('email', 'yukimart@gmail.com')->first();
    
    if ($user) {
        echo "✅ User found:\n";
        echo "- ID: {$user->id}\n";
        echo "- Email: {$user->email}\n";
        echo "- Username: {$user->username}\n";
        echo "- Status: {$user->status}\n";
        echo "- Full Name: {$user->full_name}\n";
        echo "- Created: {$user->created_at}\n";
        echo "- Updated: {$user->updated_at}\n";
    } else {
        echo "❌ User not found\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
