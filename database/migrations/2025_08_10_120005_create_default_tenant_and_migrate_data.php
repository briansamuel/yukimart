<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create default tenant
        $defaultTenantId = DB::table('tenants')->insertGetId([
            'name' => 'Default Company',
            'slug' => 'default',
            'subdomain' => 'app',
            'email' => 'admin@yukimart.local',
            'business_type' => 'retail',
            'status' => 'active',
            'plan_type' => 'enterprise',
            'max_users' => 100,
            'max_branch_shops' => 10,
            'max_products' => 10000,
            'storage_limit' => 10737418240, // 10GB
            'api_rate_limit' => 5000,
            'timezone' => 'Asia/Ho_Chi_Minh',
            'currency' => 'VND',
            'language' => 'vi',
            'settings' => json_encode([
                'allow_negative_inventory' => false,
                'auto_generate_sku' => true,
                'default_tax_rate' => 10,
                'invoice_prefix' => 'HD',
                'order_prefix' => 'DH',
                'return_prefix' => 'TH'
            ]),
            'features' => json_encode([
                'inventory_management' => true,
                'multi_branch' => true,
                'pos_system' => true,
                'online_ordering' => true,
                'reporting' => true,
                'api_access' => true,
                'marketplace_integration' => true,
                'backup_restore' => true
            ]),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        // Update all existing records to use default tenant
        $this->updateExistingData($defaultTenantId);

        // Remove default values from tenant_id columns
        $this->removeDefaultValues();

        // Create tenant admin user relationship
        $this->createTenantUserRelationships($defaultTenantId);

        // Update tenant usage statistics
        $this->updateTenantUsageStats($defaultTenantId);
    }

    /**
     * Update existing data to use default tenant
     */
    private function updateExistingData($tenantId)
    {
        $tables = [
            // Core tables
            'users',
            'roles',
            'permissions',
            'branch_shops',
            'warehouses',
            'settings',
            'audit_logs',
            
            // Business tables
            'customers',
            'suppliers',
            'categories',
            'products',
            'product_attributes',
            'inventories',
            'inventory_transactions',
            
            // Transaction tables
            'orders',
            'invoices',
            'return_orders',
            'payments',
            'bank_accounts',
            'notifications',
            'notification_templates',
            'shopee_tokens',
            'backups',
            'backup_schedules'
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'tenant_id')) {
                DB::table($table)->update(['tenant_id' => $tenantId]);
                
                $count = DB::table($table)->count();
                echo "Updated {$count} records in {$table} table\n";
            }
        }
    }

    /**
     * Remove default values from tenant_id columns
     */
    private function removeDefaultValues()
    {
        $tables = [
            'users', 'roles', 'permissions', 'branch_shops', 'warehouses',
            'customers', 'suppliers', 'categories', 'products', 'inventories',
            'orders', 'invoices', 'payments', 'notifications'
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'tenant_id')) {
                try {
                    DB::statement("ALTER TABLE {$table} ALTER COLUMN tenant_id DROP DEFAULT");
                } catch (Exception $e) {
                    // Some databases might not support this syntax
                    echo "Could not remove default value from {$table}.tenant_id: " . $e->getMessage() . "\n";
                }
            }
        }
    }

    /**
     * Create tenant-user relationships for existing users
     */
    private function createTenantUserRelationships($tenantId)
    {
        // Get all existing users
        $users = DB::table('users')->select('id', 'email')->get();

        foreach ($users as $user) {
            // Determine role based on user data
            $role = 'staff'; // default role
            
            // Check if user is admin (you might have different logic)
            if (str_contains($user->email, 'admin') || $user->id == 1) {
                $role = 'owner';
            }

            // Insert tenant-user relationship
            DB::table('tenant_users')->insert([
                'tenant_id' => $tenantId,
                'user_id' => $user->id,
                'role' => $role,
                'is_active' => true,
                'is_primary' => true,
                'joined_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }

        echo "Created tenant-user relationships for " . $users->count() . " users\n";
    }

    /**
     * Update tenant usage statistics
     */
    private function updateTenantUsageStats($tenantId)
    {
        $stats = [
            'current_users' => DB::table('users')->where('tenant_id', $tenantId)->count(),
            'current_branch_shops' => DB::table('branch_shops')->where('tenant_id', $tenantId)->count(),
            'current_products' => DB::table('products')->where('tenant_id', $tenantId)->count(),
            'current_storage_used' => 0 // Will be calculated later
        ];

        DB::table('tenants')->where('id', $tenantId)->update($stats);

        echo "Updated tenant usage statistics: " . json_encode($stats) . "\n";
    }

    /**
     * Create default tenant settings
     */
    private function createDefaultTenantSettings($tenantId)
    {
        $defaultSettings = [
            ['category' => 'general', 'key' => 'company_name', 'value' => 'Default Company', 'type' => 'string'],
            ['category' => 'general', 'key' => 'company_address', 'value' => '', 'type' => 'string'],
            ['category' => 'general', 'key' => 'company_phone', 'value' => '', 'type' => 'string'],
            ['category' => 'general', 'key' => 'company_email', 'value' => 'admin@yukimart.local', 'type' => 'string'],
            ['category' => 'general', 'key' => 'timezone', 'value' => 'Asia/Ho_Chi_Minh', 'type' => 'string'],
            ['category' => 'general', 'key' => 'currency', 'value' => 'VND', 'type' => 'string'],
            ['category' => 'general', 'key' => 'language', 'value' => 'vi', 'type' => 'string'],
            
            ['category' => 'inventory', 'key' => 'allow_negative_stock', 'value' => 'false', 'type' => 'boolean'],
            ['category' => 'inventory', 'key' => 'auto_generate_sku', 'value' => 'true', 'type' => 'boolean'],
            ['category' => 'inventory', 'key' => 'low_stock_threshold', 'value' => '10', 'type' => 'integer'],
            
            ['category' => 'sales', 'key' => 'default_tax_rate', 'value' => '10', 'type' => 'decimal'],
            ['category' => 'sales', 'key' => 'invoice_prefix', 'value' => 'HD', 'type' => 'string'],
            ['category' => 'sales', 'key' => 'order_prefix', 'value' => 'DH', 'type' => 'string'],
            ['category' => 'sales', 'key' => 'return_prefix', 'value' => 'TH', 'type' => 'string'],
            
            ['category' => 'notifications', 'key' => 'email_notifications', 'value' => 'true', 'type' => 'boolean'],
            ['category' => 'notifications', 'key' => 'sms_notifications', 'value' => 'false', 'type' => 'boolean'],
            ['category' => 'notifications', 'key' => 'push_notifications', 'value' => 'true', 'type' => 'boolean'],
        ];

        foreach ($defaultSettings as $setting) {
            DB::table('tenant_settings')->insert([
                'tenant_id' => $tenantId,
                'category' => $setting['category'],
                'key' => $setting['key'],
                'value' => $setting['value'],
                'type' => $setting['type'],
                'is_public' => false,
                'is_readonly' => false,
                'is_system' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }

        echo "Created " . count($defaultSettings) . " default tenant settings\n";
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Delete tenant-user relationships
        DB::table('tenant_users')->where('tenant_id', 1)->delete();
        
        // Delete tenant settings
        DB::table('tenant_settings')->where('tenant_id', 1)->delete();
        
        // Reset all tenant_id values to 1 (or handle differently)
        $tables = [
            'users', 'roles', 'permissions', 'branch_shops', 'warehouses',
            'customers', 'suppliers', 'categories', 'products', 'inventories',
            'orders', 'invoices', 'payments', 'notifications'
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'tenant_id')) {
                DB::table($table)->update(['tenant_id' => 1]);
            }
        }
        
        // Delete default tenant (this should be the last step)
        DB::table('tenants')->where('id', 1)->delete();
    }
};
