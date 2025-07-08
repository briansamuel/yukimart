<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating permissions...');
        $this->createPermissions();
        
        $this->command->info('Creating roles...');
        $this->createRoles();
        
        $this->command->info('Assigning permissions to roles...');
        $this->assignPermissionsToRoles();
        
        $this->command->info('Roles and permissions seeded successfully!');
    }

    /**
     * Create permissions for all modules
     */
    private function createPermissions()
    {
        $modules = [
            'pages' => ['view', 'create', 'edit', 'delete'],
            'products' => ['view', 'create', 'edit', 'delete', 'export', 'import'],
            'categories' => ['view', 'create', 'edit', 'delete'],
            'orders' => ['view', 'create', 'edit', 'delete', 'export'],
            'customers' => ['view', 'create', 'edit', 'delete', 'export', 'import'],
            'inventory' => ['view', 'create', 'edit', 'delete', 'export', 'import'],
            'transactions' => ['view', 'create', 'edit', 'delete', 'export'],
            'suppliers' => ['view', 'create', 'edit', 'delete', 'export', 'import'],
            'branches' => ['view', 'create', 'edit', 'delete'],
            'branch_shops' => ['view', 'create', 'edit', 'delete'],
            'users' => ['view', 'create', 'edit', 'delete'],
            'roles' => ['view', 'create', 'edit', 'delete'],
            'settings' => ['view', 'edit'],
            'reports' => ['view', 'export'],
            'notifications' => ['view', 'create', 'edit', 'delete'],
        ];

        $sortOrder = 0;
        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                Permission::create([
                    'name' => "{$module}.{$action}",
                    'display_name' => ucfirst($action) . ' ' . ucfirst(str_replace('_', ' ', $module)),
                    'module' => $module,
                    'action' => $action,
                    'description' => "Allow user to {$action} " . str_replace('_', ' ', $module),
                    'is_active' => true,
                    'sort_order' => $sortOrder++,
                ]);
            }
        }
    }

    /**
     * Create default roles
     */
    private function createRoles()
    {
        $roles = [
            [
                'name' => 'admin',
                'display_name' => 'Administrator',
                'description' => 'Full system access with all permissions',
                'is_active' => true,
                'sort_order' => 1,
                'settings' => [
                    'can_assign_roles' => true,
                    'can_manage_system' => true,
                    'dashboard_access' => 'full',
                ],
            ],
            [
                'name' => 'shop_manager',
                'display_name' => 'Shop Manager',
                'description' => 'Manage shop operations, orders, inventory, and staff',
                'is_active' => true,
                'sort_order' => 2,
                'settings' => [
                    'can_assign_roles' => false,
                    'can_manage_system' => false,
                    'dashboard_access' => 'manager',
                    'branch_access' => 'assigned',
                ],
            ],
            [
                'name' => 'staff',
                'display_name' => 'Staff',
                'description' => 'Handle daily operations, orders, and customer service',
                'is_active' => true,
                'sort_order' => 3,
                'settings' => [
                    'can_assign_roles' => false,
                    'can_manage_system' => false,
                    'dashboard_access' => 'limited',
                    'branch_access' => 'assigned',
                ],
            ],
            [
                'name' => 'partime',
                'display_name' => 'Part-time',
                'description' => 'Limited access for part-time workers',
                'is_active' => true,
                'sort_order' => 4,
                'settings' => [
                    'can_assign_roles' => false,
                    'can_manage_system' => false,
                    'dashboard_access' => 'basic',
                    'branch_access' => 'assigned',
                ],
            ],
        ];

        foreach ($roles as $roleData) {
            Role::create($roleData);
        }
    }

    /**
     * Assign permissions to roles
     */
    private function assignPermissionsToRoles()
    {
        // Admin - All permissions
        $adminRole = Role::where('name', 'admin')->first();
        $allPermissions = Permission::all();
        $adminRole->permissions()->sync($allPermissions->pluck('id'));

        // Shop Manager - Most permissions except user/role management
        $shopManagerRole = Role::where('name', 'shop_manager')->first();
        $shopManagerPermissions = Permission::whereNotIn('module', ['users', 'roles', 'settings'])
            ->orWhere(function($query) {
                $query->where('module', 'settings')
                      ->where('action', 'view');
            })
            ->get();
        $shopManagerRole->permissions()->sync($shopManagerPermissions->pluck('id'));

        // Staff - Basic operations
        $staffRole = Role::where('name', 'staff')->first();
        $staffPermissions = Permission::whereIn('module', [
                'products', 'orders', 'customers', 'inventory', 'transactions'
            ])
            ->whereIn('action', ['view', 'create', 'edit'])
            ->orWhere(function($query) {
                $query->whereIn('module', ['reports', 'notifications'])
                      ->where('action', 'view');
            })
            ->get();
        $staffRole->permissions()->sync($staffPermissions->pluck('id'));

        // Part-time - Very limited access
        $partimeRole = Role::where('name', 'partime')->first();
        $partimePermissions = Permission::whereIn('module', [
                'products', 'orders', 'customers'
            ])
            ->whereIn('action', ['view', 'create'])
            ->get();
        $partimeRole->permissions()->sync($partimePermissions->pluck('id'));
    }
}
