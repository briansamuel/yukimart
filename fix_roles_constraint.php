<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Fixing roles table constraints ===\n\n";

// Drop the incorrect unique constraints
echo "Step 1: Dropping incorrect unique constraints...\n";
try {
    DB::statement('ALTER TABLE roles DROP INDEX unique_tenant_role_name');
    echo "Dropped unique_tenant_role_name\n";
} catch (\Exception $e) {
    echo "Could not drop unique_tenant_role_name: " . $e->getMessage() . "\n";
}

try {
    DB::statement('ALTER TABLE roles DROP INDEX roles_tenant_name_guard_unique');
    echo "Dropped roles_tenant_name_guard_unique\n";
} catch (\Exception $e) {
    echo "Could not drop roles_tenant_name_guard_unique: " . $e->getMessage() . "\n";
}

// Add correct unique constraint (name + tenant_id + guard_name)
echo "\nStep 2: Adding correct unique constraint...\n";
try {
    DB::statement('ALTER TABLE roles ADD UNIQUE KEY unique_tenant_role (tenant_id, name, guard_name)');
    echo "Added unique_tenant_role constraint\n";
} catch (\Exception $e) {
    echo "Could not add unique_tenant_role: " . $e->getMessage() . "\n";
}

echo "\n✅ Done!\n";

