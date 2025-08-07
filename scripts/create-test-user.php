<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    // Check if user exists
    $user = \App\Models\User::where('email', 'yukimart@gmail.com')->first();

    if ($user) {
        // Update existing user
        $user->status = 'active';
        $user->save();
        echo "✅ User activated: {$user->email}\n";
    } else {
        // Create new user
        $user = new \App\Models\User();
        $user->username = 'yukimart';
        $user->email = 'yukimart@gmail.com';
        $user->password = bcrypt('123456');
        $user->full_name = 'YukiMart Admin';
        $user->phone = '0123456789';
        $user->address = 'YukiMart HQ';
        $user->active_code = '';
        $user->description = 'API Test User';
        $user->birth_date = now();

        $user->status = 'active';
        $user->save();
        echo "✅ User created successfully: {$user->email}\n";
    }

    echo "📧 Email: yukimart@gmail.com\n";
    echo "🔑 Password: 123456\n";
    echo "🟢 Status: Active\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
