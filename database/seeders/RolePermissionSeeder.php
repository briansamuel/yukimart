<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create permissions
        $permissions = [
            // Dashboard
            ['name' => 'dashboard.view', 'display_name' => 'Xem Dashboard', 'module' => 'dashboard', 'action' => 'view'],

            // Products
            ['name' => 'products.view', 'display_name' => 'Xem Sản phẩm', 'module' => 'products', 'action' => 'view'],
            ['name' => 'products.create', 'display_name' => 'Tạo Sản phẩm', 'module' => 'products', 'action' => 'create'],
            ['name' => 'products.edit', 'display_name' => 'Sửa Sản phẩm', 'module' => 'products', 'action' => 'edit'],
            ['name' => 'products.delete', 'display_name' => 'Xóa Sản phẩm', 'module' => 'products', 'action' => 'delete'],

            // Orders
            ['name' => 'orders.view', 'display_name' => 'Xem Đơn hàng', 'module' => 'orders', 'action' => 'view'],
            ['name' => 'orders.create', 'display_name' => 'Tạo Đơn hàng', 'module' => 'orders', 'action' => 'create'],
            ['name' => 'orders.edit', 'display_name' => 'Sửa Đơn hàng', 'module' => 'orders', 'action' => 'edit'],
            ['name' => 'orders.delete', 'display_name' => 'Xóa Đơn hàng', 'module' => 'orders', 'action' => 'delete'],

            // Invoices
            ['name' => 'invoices.view', 'display_name' => 'Xem Hóa đơn', 'module' => 'invoices', 'action' => 'view'],
            ['name' => 'invoices.create', 'display_name' => 'Tạo Hóa đơn', 'module' => 'invoices', 'action' => 'create'],
            ['name' => 'invoices.edit', 'display_name' => 'Sửa Hóa đơn', 'module' => 'invoices', 'action' => 'edit'],
            ['name' => 'invoices.delete', 'display_name' => 'Xóa Hóa đơn', 'module' => 'invoices', 'action' => 'delete'],

            // Customers
            ['name' => 'customers.view', 'display_name' => 'Xem Khách hàng', 'module' => 'customers', 'action' => 'view'],
            ['name' => 'customers.create', 'display_name' => 'Tạo Khách hàng', 'module' => 'customers', 'action' => 'create'],
            ['name' => 'customers.edit', 'display_name' => 'Sửa Khách hàng', 'module' => 'customers', 'action' => 'edit'],
            ['name' => 'customers.delete', 'display_name' => 'Xóa Khách hàng', 'module' => 'customers', 'action' => 'delete'],

            // Users
            ['name' => 'users.view', 'display_name' => 'Xem Người dùng', 'module' => 'users', 'action' => 'view'],
            ['name' => 'users.create', 'display_name' => 'Tạo Người dùng', 'module' => 'users', 'action' => 'create'],
            ['name' => 'users.edit', 'display_name' => 'Sửa Người dùng', 'module' => 'users', 'action' => 'edit'],
            ['name' => 'users.delete', 'display_name' => 'Xóa Người dùng', 'module' => 'users', 'action' => 'delete'],

            // Branch Shops
            ['name' => 'branch_shops.view', 'display_name' => 'Xem Chi nhánh', 'module' => 'branch_shops', 'action' => 'view'],
            ['name' => 'branch_shops.create', 'display_name' => 'Tạo Chi nhánh', 'module' => 'branch_shops', 'action' => 'create'],
            ['name' => 'branch_shops.edit', 'display_name' => 'Sửa Chi nhánh', 'module' => 'branch_shops', 'action' => 'edit'],
            ['name' => 'branch_shops.delete', 'display_name' => 'Xóa Chi nhánh', 'module' => 'branch_shops', 'action' => 'delete'],

            // Reports
            ['name' => 'reports.view', 'display_name' => 'Xem Báo cáo', 'module' => 'reports', 'action' => 'view'],
            ['name' => 'reports.export', 'display_name' => 'Xuất Báo cáo', 'module' => 'reports', 'action' => 'export'],

            // Settings
            ['name' => 'settings.view', 'display_name' => 'Xem Cài đặt', 'module' => 'settings', 'action' => 'view'],
            ['name' => 'settings.edit', 'display_name' => 'Sửa Cài đặt', 'module' => 'settings', 'action' => 'edit'],
        ];

        foreach ($permissions as $permission) {
            \App\Models\Permission::firstOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }

        // Create roles
        $superAdminRole = \App\Models\Role::firstOrCreate(
            ['name' => 'super_admin'],
            [
                'display_name' => 'Super Admin',
                'description' => 'Có tất cả quyền trong hệ thống'
            ]
        );

        $managerRole = \App\Models\Role::firstOrCreate(
            ['name' => 'manager'],
            [
                'display_name' => 'Quản lý',
                'description' => 'Quản lý chi nhánh và nhân viên'
            ]
        );

        $staffRole = \App\Models\Role::firstOrCreate(
            ['name' => 'staff'],
            [
                'display_name' => 'Nhân viên',
                'description' => 'Nhân viên bán hàng'
            ]
        );

        // Assign all permissions to super admin
        $allPermissions = \App\Models\Permission::all();
        $superAdminRole->permissions()->sync($allPermissions->pluck('id'));

        // Assign permissions to manager
        $managerPermissions = \App\Models\Permission::whereIn('name', [
            'dashboard.view',
            'products.view', 'products.create', 'products.edit',
            'orders.view', 'orders.create', 'orders.edit',
            'invoices.view', 'invoices.create', 'invoices.edit',
            'customers.view', 'customers.create', 'customers.edit',
            'users.view',
            'branch_shops.view',
            'reports.view', 'reports.export',
        ])->get();
        $managerRole->permissions()->sync($managerPermissions->pluck('id'));

        // Assign permissions to staff
        $staffPermissions = \App\Models\Permission::whereIn('name', [
            'dashboard.view',
            'products.view',
            'orders.view', 'orders.create', 'orders.edit',
            'invoices.view', 'invoices.create', 'invoices.edit',
            'customers.view', 'customers.create', 'customers.edit',
        ])->get();
        $staffRole->permissions()->sync($staffPermissions->pluck('id'));

        $this->command->info('Roles and permissions created successfully!');
    }
}
