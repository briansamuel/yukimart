<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

$tenantId = 3;

echo "=== Cleaning up old permissions and roles for Tenant {$tenantId} ===\n\n";

// Set permission team ID
setPermissionsTeamId($tenantId);

// Step 1: Clear pivot tables first
echo "Step 1: Clearing role_has_permissions...\n";
DB::table('role_has_permissions')->truncate();
echo "Cleared role_has_permissions\n\n";

echo "Step 2: Clearing model_has_roles...\n";
DB::table('model_has_roles')->truncate();
echo "Cleared model_has_roles\n\n";

echo "Step 3: Clearing model_has_permissions...\n";
DB::table('model_has_permissions')->truncate();
echo "Cleared model_has_permissions\n\n";

// Step 4: Delete all roles for this tenant (all guards)
echo "Step 4: Deleting all roles for tenant {$tenantId}...\n";
$deletedRoles = DB::table('roles')
    ->where('tenant_id', $tenantId)
    ->delete();
echo "Deleted {$deletedRoles} roles\n\n";

// Step 5: Delete all permissions for this tenant (all guards)
echo "Step 5: Deleting all permissions for tenant {$tenantId}...\n";
$deletedPermissions = DB::table('permissions')
    ->where('tenant_id', $tenantId)
    ->delete();
echo "Deleted {$deletedPermissions} permissions\n\n";

// Step 6: Delete all global permissions with guard 'web' or 'admin'
echo "Step 6: Deleting global permissions with guard 'web' or 'admin'...\n";
$deletedGlobalPerms = DB::table('permissions')
    ->whereNull('tenant_id')
    ->whereIn('guard_name', ['web', 'admin'])
    ->delete();
echo "Deleted {$deletedGlobalPerms} global permissions\n\n";

echo "✅ Cleanup complete!\n\n";
echo "Now run the seeder to create roles and permissions with guard 'tenant':\n";
echo "php artisan db:seed --class=AssignOwnerPermissionsSeeder\n";

