<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Tenant;

// Find tenant 3
$tenant = Tenant::find(3);
echo "Tenant: {$tenant->name} (ID: {$tenant->id})\n";
echo "Subdomain: {$tenant->subdomain}\n\n";

// Find owner
$owner = $tenant->owners()->first();
if (!$owner) {
    $owner = $tenant->users()->wherePivot('is_primary', true)->first();
}

echo "Owner: {$owner->full_name} ({$owner->email})\n\n";

// Set permission team ID
setPermissionsTeamId($tenant->id);

// Check roles
echo "=== ROLES ===\n";
$roles = $owner->roles;
foreach ($roles as $role) {
    echo "- {$role->name} (ID: {$role->id}, tenant_id: {$role->tenant_id})\n";
}
echo "\n";

// Check permissions through roles
echo "=== PERMISSIONS (through roles) ===\n";
$permissions = $owner->getAllPermissions();
echo "Total permissions: " . $permissions->count() . "\n";

// Check specific permission
try {
    $hasProductRead = $owner->hasPermissionTo('catalog.products.read', 'tenant');
    echo "\nHas 'catalog.products.read' (guard: tenant): " . ($hasProductRead ? 'YES' : 'NO') . "\n";
} catch (\Exception $e) {
    echo "\nError checking permission: " . $e->getMessage() . "\n";
}

// Check if permission exists
$permission = DB::table('permissions')
    ->where('name', 'catalog.products.read')
    ->first();

if ($permission) {
    echo "\nPermission 'catalog.products.read' exists:\n";
    echo "- ID: {$permission->id}\n";
    echo "- tenant_id: " . ($permission->tenant_id ?? 'NULL') . "\n";
    echo "- module: {$permission->module}\n";
    echo "- action: {$permission->action}\n";
} else {
    echo "\nPermission 'catalog.products.read' NOT FOUND!\n";
}

// List all permissions with 'catalog.products' prefix
echo "\n=== ALL CATALOG.PRODUCTS PERMISSIONS ===\n";
$catalogPermissions = DB::table('permissions')
    ->where('name', 'like', 'catalog.products.%')
    ->get();

foreach ($catalogPermissions as $perm) {
    echo "- {$perm->name} (tenant_id: " . ($perm->tenant_id ?? 'NULL') . ")\n";
}

