<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Users in database:\n";
echo "==================\n";

$users = App\Models\User::all(['id', 'email', 'username', 'status']);

if ($users->count() > 0) {
    foreach ($users as $user) {
        echo "ID: {$user->id} | Email: {$user->email} | Username: {$user->username} | Status: {$user->status}\n";
    }
} else {
    echo "No users found in database.\n";
}

echo "\nTotal users: " . $users->count() . "\n";
