<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Tenant;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

class AssignOwnerPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenantId = 3;

        // Find tenant
        $tenant = Tenant::find($tenantId);
        if (!$tenant) {
            $this->command->error("Tenant with ID {$tenantId} not found!");
            return;
        }

        $this->command->info("Processing tenant: {$tenant->name} (ID: {$tenantId})");

        // Find owner of this tenant
        $owner = $tenant->owners()->first();
        
        if (!$owner) {
            $this->command->warn("No owner found for tenant {$tenantId}. Looking for primary user...");
            
            // Try to find primary user
            $owner = $tenant->users()->wherePivot('is_primary', true)->first();
            
            if (!$owner) {
                $this->command->error("No primary user found for tenant {$tenantId}!");
                return;
            }
        }

        $this->command->info("Found owner: {$owner->full_name} ({$owner->email})");

        // Set permission team ID for multi-tenant
        setPermissionsTeamId($tenantId);

        // Create roles for this tenant with guard 'tenant'
        $ownerRole = Role::firstOrCreate(
            [
                'name' => 'Owner',
                'guard_name' => 'tenant',
                'tenant_id' => $tenantId,
            ],
            [
                'display_name' => 'Chủ cửa hàng',
                'description' => 'Full access to all features',
            ]
        );

        $adminRole = Role::firstOrCreate(
            [
                'name' => 'Admin',
                'guard_name' => 'tenant',
                'tenant_id' => $tenantId,
            ],
            [
                'display_name' => 'Quản trị viên',
                'description' => 'Administrator with full access',
            ]
        );

        $managerRole = Role::firstOrCreate(
            [
                'name' => 'Manager',
                'guard_name' => 'tenant',
                'tenant_id' => $tenantId,
            ],
            [
                'display_name' => 'Quản lý',
                'description' => 'Store manager',
            ]
        );

        $cashierRole = Role::firstOrCreate(
            [
                'name' => 'Cashier',
                'guard_name' => 'tenant',
                'tenant_id' => $tenantId,
            ],
            [
                'display_name' => 'Thu ngân',
                'description' => 'Cashier role',
            ]
        );

        $warehouseRole = Role::firstOrCreate(
            [
                'name' => 'Warehouse',
                'guard_name' => 'tenant',
                'tenant_id' => $tenantId,
            ],
            [
                'display_name' => 'Kiểm kho',
                'description' => 'Warehouse keeper',
            ]
        );

        $this->command->info("Roles created successfully");

        // Get all permissions (both global and tenant-specific)
        $allPermissions = Permission::where(function($query) use ($tenantId) {
            $query->where('tenant_id', $tenantId)
                  ->orWhereNull('tenant_id');
        })->get();

        if ($allPermissions->isEmpty()) {
            $this->command->warn("No permissions found. Creating default permissions for tenant {$tenantId}...");
            $this->createDefaultPermissions($tenantId);
            $allPermissions = Permission::where(function($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId)
                      ->orWhereNull('tenant_id');
            })->get();
        }

        $this->command->info("Found {$allPermissions->count()} permissions");

        // Assign all permissions to Owner role
        $ownerRole->syncPermissions($allPermissions);
        $this->command->info("Assigned all permissions to Owner role");

        // Assign Owner role to owner user
        $owner->syncRoles([$ownerRole]);
        $this->command->info("Assigned Owner role to owner: {$owner->full_name}");

        // Update tenant_users pivot to set role as 'owner'
        DB::table('tenant_users')
            ->where('tenant_id', $tenantId)
            ->where('user_id', $owner->id)
            ->update([
                'role' => 'owner',
                'is_active' => true,
                'is_primary' => true,
            ]);

        $this->command->info("Updated tenant_users pivot table");

        $this->command->info("✅ Successfully assigned full permissions to owner!");
    }

    /**
     * Create default permissions for tenant
     */
    private function createDefaultPermissions(int $tenantId): void
    {
        $modules = [
            'settings' => [
                'roles' => ['read', 'create', 'update', 'delete'],
                'users' => ['read', 'create', 'update', 'delete'],
                'branches' => ['read', 'create', 'update', 'delete'],
                'retailer' => ['read', 'update'],
            ],
            'catalog' => [
                'products' => ['read', 'create', 'update', 'delete', 'import', 'export'],
                'categories' => ['read', 'create', 'update', 'delete'],
            ],
            'sales' => [
                'orders' => ['read', 'create', 'update', 'delete', 'export'],
                'invoices' => ['read', 'create', 'update', 'delete', 'export'],
                'returns' => ['read', 'create', 'update', 'delete'],
                'quick-orders' => ['read', 'create'],
            ],
            'inventory' => [
                'inventory' => ['read', 'update', 'adjust'],
                'warehouses' => ['read', 'create', 'update', 'delete'],
            ],
            'customers' => [
                'customers' => ['read', 'create', 'update', 'delete', 'import', 'export'],
            ],
            'payments' => [
                'payments' => ['read', 'create', 'update', 'delete'],
            ],
            'reports' => [
                'sales' => ['read', 'export'],
                'inventory' => ['read', 'export'],
                'customers' => ['read', 'export'],
            ],
        ];

        setPermissionsTeamId($tenantId);

        foreach ($modules as $module => $subModules) {
            foreach ($subModules as $subModule => $actions) {
                foreach ($actions as $action) {
                    $permissionName = "{$module}.{$subModule}.{$action}";

                    Permission::firstOrCreate(
                        [
                            'name' => $permissionName,
                            'guard_name' => 'tenant',
                            'tenant_id' => $tenantId,
                        ],
                        [
                            'display_name' => ucfirst($action) . ' ' . ucfirst($subModule),
                            'description' => ucfirst($action) . ' permission for ' . $subModule,
                            'module' => $module,
                            'sub_module' => $subModule,
                            'action' => $action,
                            'is_active' => true,
                            'sort_order' => 0,
                        ]
                    );
                }
            }
        }

        $this->command->info("Created default permissions for tenant {$tenantId}");
    }
}

