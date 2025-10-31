<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('🚀 Starting YukiMart Database Seeding...');

        $this->call([
            // Core system seeders
            RolesAndPermissionsSeeder::class,

            // Platform users (superadmin, admin, dev, manager, support)
            PlatformUsersSeeder::class,

            // Original seeders
            UsersTableSeeder::class,
            PageSeeder::class,
            BranchShopSeeder::class,
            SupplierSeeder::class,
            CustomerSeeder::class,
            ProductSeeder::class,
            ProductAttributeSeeder::class,
            InventoryTransactionSeeder::class,
            WarehouseSeeder::class,
            AdvancedInventoryTransactionSeeder::class,
            OrderSeeder::class,
            InvoiceSeeder::class,
            BankAccountSeeder::class,
            PaymentSeeder::class,
            TestDataSeeder::class,

            // Tenant test data (includes tenant users)
            TenantTestDataSeeder::class,

            // Update all user passwords to 123456
            UpdateTenantUsersPasswordSeeder::class,
        ]);

        $this->command->info('🎉 YukiMart Database Seeding Completed!');
        $this->command->info('');
        $this->command->info('🔐 Default Login Credentials:');
        $this->command->info('   Platform Users: superadmin@yukimart.local / 123456');
        $this->command->info('   Tenant Users: All passwords set to 123456');
        $this->command->info('');
        $this->command->info('🌐 Access URLs:');
        $this->command->info('   Platform: http://yukimart.local/admin/login');
        $this->command->info('   TechMart: http://tenant1.yukimart.local/admin/login');
        $this->command->info('   Fashion:  http://tenant2.yukimart.local/admin/login');
    }
}
