<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Define settings permissions
        $settingsPermissions = [
            // Products Settings
            [
                'module' => 'settings',
                'sub_module' => 'products',
                'action' => 'read',
                'display_name' => 'Xem cài đặt hàng hóa',
                'description' => 'Cho phép xem và truy cập các cài đặt liên quan đến hàng hóa',
            ],
            [
                'module' => 'settings',
                'sub_module' => 'products',
                'action' => 'update',
                'display_name' => 'Cập nhật cài đặt hàng hóa',
                'description' => 'Cho phép cập nhật các cài đặt liên quan đến hàng hóa',
            ],
            
            // Orders Settings
            [
                'module' => 'settings',
                'sub_module' => 'orders',
                'action' => 'read',
                'display_name' => 'Xem cài đặt đơn hàng',
                'description' => 'Cho phép xem và truy cập các cài đặt liên quan đến đơn hàng',
            ],
            [
                'module' => 'settings',
                'sub_module' => 'orders',
                'action' => 'update',
                'display_name' => 'Cập nhật cài đặt đơn hàng',
                'description' => 'Cho phép cập nhật các cài đặt liên quan đến đơn hàng',
            ],
            
            // Customers Settings
            [
                'module' => 'settings',
                'sub_module' => 'customers',
                'action' => 'read',
                'display_name' => 'Xem cài đặt khách hàng',
                'description' => 'Cho phép xem và truy cập các cài đặt liên quan đến khách hàng',
            ],
            [
                'module' => 'settings',
                'sub_module' => 'customers',
                'action' => 'update',
                'display_name' => 'Cập nhật cài đặt khách hàng',
                'description' => 'Cho phép cập nhật các cài đặt liên quan đến khách hàng',
            ],
            
            // Cashbook Settings
            [
                'module' => 'settings',
                'sub_module' => 'cashbook',
                'action' => 'read',
                'display_name' => 'Xem cài đặt sổ quỹ',
                'description' => 'Cho phép xem và truy cập các cài đặt liên quan đến sổ quỹ',
            ],
            [
                'module' => 'settings',
                'sub_module' => 'cashbook',
                'action' => 'update',
                'display_name' => 'Cập nhật cài đặt sổ quỹ',
                'description' => 'Cho phép cập nhật các cài đặt liên quan đến sổ quỹ',
            ],
            
            // Store Settings
            [
                'module' => 'settings',
                'sub_module' => 'store',
                'action' => 'read',
                'display_name' => 'Xem cài đặt cửa hàng',
                'description' => 'Cho phép xem và truy cập các cài đặt liên quan đến cửa hàng',
            ],
            [
                'module' => 'settings',
                'sub_module' => 'store',
                'action' => 'update',
                'display_name' => 'Cập nhật cài đặt cửa hàng',
                'description' => 'Cho phép cập nhật các cài đặt liên quan đến cửa hàng',
            ],
            
            // Users Settings
            [
                'module' => 'settings',
                'sub_module' => 'users',
                'action' => 'read',
                'display_name' => 'Xem cài đặt người dùng',
                'description' => 'Cho phép xem và truy cập các cài đặt liên quan đến người dùng',
            ],
            [
                'module' => 'settings',
                'sub_module' => 'users',
                'action' => 'update',
                'display_name' => 'Cập nhật cài đặt người dùng',
                'description' => 'Cho phép cập nhật các cài đặt liên quan đến người dùng',
            ],
            
            // Branches Settings
            [
                'module' => 'settings',
                'sub_module' => 'branches',
                'action' => 'read',
                'display_name' => 'Xem cài đặt chi nhánh',
                'description' => 'Cho phép xem và truy cập các cài đặt liên quan đến chi nhánh',
            ],
            [
                'module' => 'settings',
                'sub_module' => 'branches',
                'action' => 'update',
                'display_name' => 'Cập nhật cài đặt chi nhánh',
                'description' => 'Cho phép cập nhật các cài đặt liên quan đến chi nhánh',
            ],
            
            // API Settings
            [
                'module' => 'settings',
                'sub_module' => 'api',
                'action' => 'read',
                'display_name' => 'Xem cài đặt API',
                'description' => 'Cho phép xem và truy cập các cài đặt liên quan đến API',
            ],
            [
                'module' => 'settings',
                'sub_module' => 'api',
                'action' => 'update',
                'display_name' => 'Cập nhật cài đặt API',
                'description' => 'Cho phép cập nhật các cài đặt liên quan đến API',
            ],
            
            // Tax Settings
            [
                'module' => 'settings',
                'sub_module' => 'tax',
                'action' => 'read',
                'display_name' => 'Xem cài đặt thuế',
                'description' => 'Cho phép xem và truy cập các cài đặt liên quan đến thuế',
            ],
            [
                'module' => 'settings',
                'sub_module' => 'tax',
                'action' => 'update',
                'display_name' => 'Cập nhật cài đặt thuế',
                'description' => 'Cho phép cập nhật các cài đặt liên quan đến thuế',
            ],
            
            // General Settings
            [
                'module' => 'settings',
                'sub_module' => 'general',
                'action' => 'read',
                'display_name' => 'Xem cài đặt chung',
                'description' => 'Cho phép xem và truy cập các cài đặt chung của hệ thống',
            ],
            [
                'module' => 'settings',
                'sub_module' => 'general',
                'action' => 'update',
                'display_name' => 'Cập nhật cài đặt chung',
                'description' => 'Cho phép cập nhật các cài đặt chung của hệ thống',
            ],
            
            // Notifications Settings
            [
                'module' => 'settings',
                'sub_module' => 'notifications',
                'action' => 'read',
                'display_name' => 'Xem cài đặt thông báo',
                'description' => 'Cho phép xem và truy cập các cài đặt liên quan đến thông báo',
            ],
            [
                'module' => 'settings',
                'sub_module' => 'notifications',
                'action' => 'update',
                'display_name' => 'Cập nhật cài đặt thông báo',
                'description' => 'Cho phép cập nhật các cài đặt liên quan đến thông báo',
            ],
            
            // Backup Settings
            [
                'module' => 'settings',
                'sub_module' => 'backup',
                'action' => 'read',
                'display_name' => 'Xem cài đặt sao lưu',
                'description' => 'Cho phép xem và truy cập các cài đặt liên quan đến sao lưu',
            ],
            [
                'module' => 'settings',
                'sub_module' => 'backup',
                'action' => 'update',
                'display_name' => 'Cập nhật cài đặt sao lưu',
                'description' => 'Cho phép cập nhật các cài đặt liên quan đến sao lưu',
            ],
        ];

        DB::beginTransaction();

        try {
            $createdCount = 0;
            $existingCount = 0;

            // Create permissions for both web and tenant guards
            $guards = ['web', 'tenant'];

            foreach ($guards as $guard) {
                foreach ($settingsPermissions as $permData) {
                    $name = Permission::generateName($permData['module'], $permData['action'], $permData['sub_module']);

                    // Check if permission already exists for this guard
                    $existing = Permission::where('name', $name)
                        ->where('guard_name', $guard)
                        ->first();

                    if ($existing) {
                        $existingCount++;
                        $this->command->info("Permission already exists: {$name} ({$guard})");
                        continue;
                    }

                    // Create permission
                    Permission::create([
                        'name' => $name,
                        'guard_name' => $guard,
                        'display_name' => $permData['display_name'],
                        'module' => $permData['module'],
                        'sub_module' => $permData['sub_module'],
                        'action' => $permData['action'],
                        'description' => $permData['description'],
                        'is_active' => true,
                        'sort_order' => 0,
                    ]);

                    $createdCount++;
                    $this->command->info("Created permission: {$name} ({$guard})");
                }
            }

            DB::commit();

            $this->command->info("\n✅ Settings Permissions Seeder completed!");
            $this->command->info("Created: {$createdCount} permissions");
            $this->command->info("Existing: {$existingCount} permissions");
            
            // Assign all settings permissions to admin role
            $this->assignPermissionsToAdminRole();
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("Error creating permissions: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Assign all settings permissions to admin role
     */
    protected function assignPermissionsToAdminRole(): void
    {
        $this->command->info("\n🔐 Assigning permissions to admin roles...");

        $guards = ['web', 'tenant'];

        foreach ($guards as $guard) {
            // Find admin role for this guard
            $adminRole = Role::where('name', 'admin')
                ->where('guard_name', $guard)
                ->first();

            if (!$adminRole) {
                $this->command->warn("Admin role ({$guard} guard) not found. Skipping.");
                continue;
            }

            // Get all settings permissions for this guard
            $settingsPermissions = Permission::where('module', 'settings')
                ->where('guard_name', $guard)
                ->get();

            if ($settingsPermissions->isEmpty()) {
                $this->command->warn("No settings permissions found for {$guard} guard.");
                continue;
            }

            // Sync permissions to admin role
            $adminRole->syncPermissions($settingsPermissions);

            $this->command->info("✅ Assigned {$settingsPermissions->count()} settings permissions to admin role ({$guard} guard)");
        }
    }
}

