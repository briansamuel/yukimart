<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$tenantId = 3;

echo "=== Users for Tenant {$tenantId} ===\n\n";

$users = DB::table('users')
    ->where('tenant_id', $tenantId)
    ->select('id', 'email', 'full_name', 'username')
    ->get();

foreach ($users as $user) {
    echo "ID: {$user->id}\n";
    echo "Email: {$user->email}\n";
    echo "Name: {$user->full_name}\n";
    echo "Username: {$user->username}\n";
    echo "---\n";
}

