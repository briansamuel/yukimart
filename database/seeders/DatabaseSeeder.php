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
        $this->call([
            RolesAndPermissionsSeeder::class,
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
        ]);
    }
}
