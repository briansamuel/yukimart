<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$tenantId = 3;

echo "=== Checking existing roles for Tenant {$tenantId} ===\n\n";

$roles = DB::table('roles')
    ->where('tenant_id', $tenantId)
    ->get();

echo "Found {$roles->count()} roles:\n";
foreach ($roles as $role) {
    echo "- ID: {$role->id}, Name: {$role->name}, Guard: {$role->guard_name}\n";
}

echo "\n=== Checking all roles (any tenant) ===\n\n";
$allRoles = DB::table('roles')->get();
echo "Total roles in database: {$allRoles->count()}\n";
foreach ($allRoles as $role) {
    echo "- ID: {$role->id}, Name: {$role->name}, Guard: {$role->guard_name}, Tenant: " . ($role->tenant_id ?? 'NULL') . "\n";
}

