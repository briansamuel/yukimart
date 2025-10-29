<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$user = User::find(46);

if (!$user) {
    echo "User not found!\n";
    exit(1);
}

echo "User ID: " . $user->id . "\n";
echo "Full Name: " . $user->full_name . "\n";
echo "Username: " . $user->username . "\n";
echo "Password hash (first 30 chars): " . substr($user->password, 0, 30) . "...\n";
echo "\n";
echo "Testing passwords:\n";
echo "- Old password (password123): " . (Hash::check('password123', $user->password) ? "✓ VALID" : "✗ INVALID") . "\n";
echo "- New password (newpassword456): " . (Hash::check('newpassword456', $user->password) ? "✓ VALID" : "✗ INVALID") . "\n";

