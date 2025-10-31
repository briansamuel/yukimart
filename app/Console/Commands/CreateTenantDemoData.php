<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Database\Seeders\TenantDemoDataSeeder;
use Database\Seeders\SimpleTenantDemoSeeder;

class CreateTenantDemoData extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'tenant:demo-data {--fresh : Drop all data and create fresh demo data}';

    /**
     * The console command description.
     */
    protected $description = 'Create comprehensive demo data for tenant system showcase';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🎭 Creating Tenant Demo Data...');
        $this->newLine();

        if ($this->option('fresh')) {
            $this->warn('⚠️  Fresh option selected - This will clear existing data!');
            if (!$this->confirm('Are you sure you want to proceed?')) {
                $this->info('Operation cancelled.');
                return 0;
            }
            
            $this->info('🗑️  Clearing existing data...');
            $this->clearExistingData();
        }

        try {
            // Run the simple demo data seeder
            $this->info('📊 Running SimpleTenantDemoSeeder...');
            $seeder = new SimpleTenantDemoSeeder();
            $seeder->setCommand($this);
            $seeder->run();

            $this->newLine();
            $this->info('✅ Demo data created successfully!');
            $this->newLine();

            // Display summary
            $this->displaySummary();

            // Display login credentials
            $this->displayLoginCredentials();

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Failed to create demo data:');
            $this->error("   {$e->getMessage()}");
            $this->error("   File: {$e->getFile()}:{$e->getLine()}");
            return 1;
        }
    }

    /**
     * Clear existing data (for fresh option)
     */
    private function clearExistingData(): void
    {
        // Clear in reverse order to respect foreign key constraints
        \DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        $tables = [
            'invoice_items',
            'invoices',
            'order_items',
            'orders',
            'inventories',
            'products',
            'categories',
            'customers',
            'warehouses',
            'branch_shops',
            'tenant_users',
            'tenant_settings',
            'tenant_activity_logs',
        ];

        foreach ($tables as $table) {
            if (\Schema::hasTable($table)) {
                \DB::table($table)->truncate();
                $this->line("   Cleared: {$table}");
            }
        }

        // Don't clear tenants and users tables completely, just reset counts
        \DB::table('tenants')->update([
            'current_users' => 0,
            'current_products' => 0,
            'current_customers' => 0,
            'current_orders' => 0,
            'current_storage_used' => 0,
        ]);

        \DB::statement('SET FOREIGN_KEY_CHECKS=1');
        $this->info('   ✅ Existing data cleared');
    }

    /**
     * Display summary of created data
     */
    private function displaySummary(): void
    {
        $this->info('📈 Demo Data Summary:');
        $this->newLine();

        $tenants = \App\Models\Tenant::all();
        
        foreach ($tenants as $tenant) {
            $this->line("🏢 <fg=cyan>{$tenant->name}</> ({$tenant->slug})");
            $this->line("   Status: <fg=green>{$tenant->status}</>");
            $this->line("   Plan: <fg=yellow>{$tenant->plan_type}</>");
            
            // Get statistics
            $userCount = \App\Models\TenantUser::where('tenant_id', $tenant->id)->count();
            $productCount = \App\Models\Product::where('tenant_id', $tenant->id)->count();
            $customerCount = \App\Models\Customer::where('tenant_id', $tenant->id)->count();
            $orderCount = \App\Models\Order::where('tenant_id', $tenant->id)->count();
            $invoiceCount = \App\Models\Invoice::where('tenant_id', $tenant->id)->count();
            $categoryCount = \App\Models\Category::where('tenant_id', $tenant->id)->count();
            $warehouseCount = \App\Models\Warehouse::where('tenant_id', $tenant->id)->count();
            
            $this->line("   Users: <fg=green>{$userCount}</> / {$tenant->max_users}");
            $this->line("   Products: <fg=green>{$productCount}</> / {$tenant->max_products}");
            $this->line("   Customers: <fg=green>{$customerCount}</>");
            $this->line("   Orders: <fg=green>{$orderCount}</>");
            $this->line("   Invoices: <fg=green>{$invoiceCount}</>");
            $this->line("   Categories: <fg=green>{$categoryCount}</>");
            $this->line("   Warehouses: <fg=green>{$warehouseCount}</>");
            $this->newLine();
        }

        // Overall statistics
        $totalTenants = $tenants->count();
        $totalUsers = \App\Models\TenantUser::count();
        $totalProducts = \App\Models\Product::count();
        $totalCustomers = \App\Models\Customer::count();
        $totalOrders = \App\Models\Order::count();
        $totalInvoices = \App\Models\Invoice::count();

        $this->info('🌍 Overall Statistics:');
        $this->line("   Total Tenants: <fg=cyan>{$totalTenants}</>");
        $this->line("   Total Users: <fg=green>{$totalUsers}</>");
        $this->line("   Total Products: <fg=green>{$totalProducts}</>");
        $this->line("   Total Customers: <fg=green>{$totalCustomers}</>");
        $this->line("   Total Orders: <fg=green>{$totalOrders}</>");
        $this->line("   Total Invoices: <fg=green>{$totalInvoices}</>");
        $this->newLine();
    }

    /**
     * Display login credentials for demo
     */
    private function displayLoginCredentials(): void
    {
        $this->info('🔑 Demo Login Credentials:');
        $this->newLine();

        $tenants = \App\Models\Tenant::all();
        
        foreach ($tenants as $tenant) {
            $this->line("🏢 <fg=cyan>{$tenant->name}</>");
            $this->line("   Admin: admin@{$tenant->slug}.local / 123456");
            $this->line("   Manager: manager@{$tenant->slug}.local / 123456");
            $this->newLine();
        }

        $this->info('🌐 Access URLs:');
        $this->line("   Admin Panel: <fg=blue>http://yukimart.local/admin/login</>");
        $this->line("   Tenant Test: <fg=blue>http://yukimart.local/tenant/test</>");
        $this->line("   Dashboard: <fg=blue>http://yukimart.local/admin/dashboard</>");
        $this->newLine();

        $this->info('💡 Demo Features:');
        $this->line("   ✅ Multi-tenant data isolation");
        $this->line("   ✅ Tenant switching functionality");
        $this->line("   ✅ Role-based access control");
        $this->line("   ✅ Comprehensive statistics");
        $this->line("   ✅ Product, customer, order management");
        $this->line("   ✅ Invoice and payment tracking");
        $this->line("   ✅ Inventory management");
        $this->line("   ✅ Warehouse management");
        $this->newLine();

        $this->info('🎯 Next Steps:');
        $this->line("   1. Login with any of the credentials above");
        $this->line("   2. Test tenant switching in the admin header");
        $this->line("   3. Explore different tenant data");
        $this->line("   4. Check statistics and reports");
        $this->line("   5. Test API endpoints at /tenant/test");
        $this->newLine();
    }
}
