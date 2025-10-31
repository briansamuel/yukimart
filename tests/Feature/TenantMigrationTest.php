<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class TenantMigrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test tenants table creation
     */
    public function test_tenants_table_creation()
    {
        // Run the tenants table migration
        Artisan::call('migrate', [
            '--path' => 'database/migrations/2025_08_10_120000_create_tenants_table.php'
        ]);

        // Check if tenants table exists
        $this->assertTrue(Schema::hasTable('tenants'));

        // Check required columns
        $requiredColumns = [
            'id', 'name', 'slug', 'subdomain', 'domain', 'email', 'phone',
            'business_type', 'status', 'plan_type', 'max_users', 'max_branch_shops',
            'max_products', 'storage_limit', 'timezone', 'currency', 'language',
            'created_at', 'updated_at', 'deleted_at'
        ];

        foreach ($requiredColumns as $column) {
            $this->assertTrue(
                Schema::hasColumn('tenants', $column),
                "Column '{$column}' does not exist in tenants table"
            );
        }

        // Check indexes
        $indexes = DB::select("SHOW INDEX FROM tenants");
        $indexNames = collect($indexes)->pluck('Key_name')->unique()->toArray();
        
        $this->assertContains('idx_tenant_status_plan', $indexNames);
        $this->assertContains('idx_tenant_created', $indexNames);
    }

    /**
     * Test tenant relationship tables creation
     */
    public function test_tenant_relationship_tables_creation()
    {
        // Run tenants table first
        Artisan::call('migrate', [
            '--path' => 'database/migrations/2025_08_10_120000_create_tenants_table.php'
        ]);

        // Run relationship tables migration
        Artisan::call('migrate', [
            '--path' => 'database/migrations/2025_08_10_120001_create_tenant_relationship_tables.php'
        ]);

        // Check if all relationship tables exist
        $tables = ['tenant_users', 'tenant_settings', 'tenant_invitations', 'tenant_activity_logs'];
        
        foreach ($tables as $table) {
            $this->assertTrue(
                Schema::hasTable($table),
                "Table '{$table}' does not exist"
            );
        }

        // Check tenant_users table structure
        $tenantUsersColumns = ['id', 'tenant_id', 'user_id', 'role', 'is_active', 'is_primary'];
        foreach ($tenantUsersColumns as $column) {
            $this->assertTrue(
                Schema::hasColumn('tenant_users', $column),
                "Column '{$column}' does not exist in tenant_users table"
            );
        }

        // Check tenant_settings table structure
        $tenantSettingsColumns = ['id', 'tenant_id', 'category', 'key', 'value', 'type'];
        foreach ($tenantSettingsColumns as $column) {
            $this->assertTrue(
                Schema::hasColumn('tenant_settings', $column),
                "Column '{$column}' does not exist in tenant_settings table"
            );
        }
    }

    /**
     * Test core tables tenant_id addition
     */
    public function test_core_tables_tenant_id_addition()
    {
        // Run prerequisite migrations
        $this->runPrerequisiteMigrations();

        // Run core tables migration
        Artisan::call('migrate', [
            '--path' => 'database/migrations/2025_08_10_120002_add_tenant_id_to_core_tables.php'
        ]);

        // Check if tenant_id column exists in core tables
        $coreTables = ['users', 'roles', 'permissions', 'branch_shops'];
        
        foreach ($coreTables as $table) {
            if (Schema::hasTable($table)) {
                $this->assertTrue(
                    Schema::hasColumn($table, 'tenant_id'),
                    "Column 'tenant_id' does not exist in {$table} table"
                );

                // Check foreign key constraint exists
                $foreignKeys = $this->getForeignKeys($table);
                $hasTenantForeignKey = collect($foreignKeys)->contains(function ($fk) {
                    return $fk->COLUMN_NAME === 'tenant_id' && $fk->REFERENCED_TABLE_NAME === 'tenants';
                });
                
                $this->assertTrue(
                    $hasTenantForeignKey,
                    "Foreign key constraint for tenant_id does not exist in {$table} table"
                );
            }
        }
    }

    /**
     * Test business tables tenant_id addition
     */
    public function test_business_tables_tenant_id_addition()
    {
        // Run prerequisite migrations
        $this->runPrerequisiteMigrations();

        // Run business tables migration
        Artisan::call('migrate', [
            '--path' => 'database/migrations/2025_08_10_120003_add_tenant_id_to_business_tables.php'
        ]);

        // Check if tenant_id column exists in business tables
        $businessTables = ['customers', 'suppliers', 'categories', 'products', 'inventories'];
        
        foreach ($businessTables as $table) {
            if (Schema::hasTable($table)) {
                $this->assertTrue(
                    Schema::hasColumn($table, 'tenant_id'),
                    "Column 'tenant_id' does not exist in {$table} table"
                );

                // Check indexes
                $indexes = $this->getTableIndexes($table);
                $hasTenantIndex = collect($indexes)->contains(function ($index) {
                    return str_contains($index, 'tenant');
                });
                
                $this->assertTrue(
                    $hasTenantIndex,
                    "Tenant-related index does not exist in {$table} table"
                );
            }
        }
    }

    /**
     * Test transaction tables tenant_id addition
     */
    public function test_transaction_tables_tenant_id_addition()
    {
        // Run prerequisite migrations
        $this->runPrerequisiteMigrations();

        // Run transaction tables migration
        Artisan::call('migrate', [
            '--path' => 'database/migrations/2025_08_10_120004_add_tenant_id_to_transaction_tables.php'
        ]);

        // Check if tenant_id column exists in transaction tables
        $transactionTables = ['orders', 'invoices', 'payments'];
        
        foreach ($transactionTables as $table) {
            if (Schema::hasTable($table)) {
                $this->assertTrue(
                    Schema::hasColumn($table, 'tenant_id'),
                    "Column 'tenant_id' does not exist in {$table} table"
                );
            }
        }
    }

    /**
     * Test default tenant creation and data migration
     */
    public function test_default_tenant_creation_and_data_migration()
    {
        // Run all prerequisite migrations
        $this->runAllPrerequisiteMigrations();

        // Create some test data first
        $this->createTestData();

        // Run data migration
        Artisan::call('migrate', [
            '--path' => 'database/migrations/2025_08_10_120005_create_default_tenant_and_migrate_data.php'
        ]);

        // Check if default tenant was created
        $defaultTenant = DB::table('tenants')->first();
        $this->assertNotNull($defaultTenant, 'Default tenant was not created');
        $this->assertEquals('Default Company', $defaultTenant->name);
        $this->assertEquals('default', $defaultTenant->slug);

        // Check if existing data was migrated
        if (Schema::hasTable('users')) {
            $usersWithoutTenant = DB::table('users')->whereNull('tenant_id')->count();
            $this->assertEquals(0, $usersWithoutTenant, 'Some users do not have tenant_id assigned');
        }

        // Check if tenant-user relationships were created
        if (Schema::hasTable('tenant_users')) {
            $tenantUserCount = DB::table('tenant_users')->count();
            $userCount = DB::table('users')->count();
            $this->assertEquals($userCount, $tenantUserCount, 'Tenant-user relationships not created for all users');
        }
    }

    /**
     * Test migration rollback
     */
    public function test_migration_rollback()
    {
        // Run all migrations
        $this->runAllMigrations();

        // Verify tables exist
        $this->assertTrue(Schema::hasTable('tenants'));
        $this->assertTrue(Schema::hasTable('tenant_users'));

        // Rollback migrations in reverse order
        Artisan::call('migrate:rollback', [
            '--path' => 'database/migrations/2025_08_10_120005_create_default_tenant_and_migrate_data.php'
        ]);

        Artisan::call('migrate:rollback', [
            '--path' => 'database/migrations/2025_08_10_120004_add_tenant_id_to_transaction_tables.php'
        ]);

        Artisan::call('migrate:rollback', [
            '--path' => 'database/migrations/2025_08_10_120003_add_tenant_id_to_business_tables.php'
        ]);

        Artisan::call('migrate:rollback', [
            '--path' => 'database/migrations/2025_08_10_120002_add_tenant_id_to_core_tables.php'
        ]);

        Artisan::call('migrate:rollback', [
            '--path' => 'database/migrations/2025_08_10_120001_create_tenant_relationship_tables.php'
        ]);

        Artisan::call('migrate:rollback', [
            '--path' => 'database/migrations/2025_08_10_120000_create_tenants_table.php'
        ]);

        // Verify tables are removed
        $this->assertFalse(Schema::hasTable('tenants'));
        $this->assertFalse(Schema::hasTable('tenant_users'));
    }

    /**
     * Helper method to run prerequisite migrations
     */
    private function runPrerequisiteMigrations()
    {
        Artisan::call('migrate', [
            '--path' => 'database/migrations/2025_08_10_120000_create_tenants_table.php'
        ]);

        Artisan::call('migrate', [
            '--path' => 'database/migrations/2025_08_10_120001_create_tenant_relationship_tables.php'
        ]);
    }

    /**
     * Helper method to run all prerequisite migrations
     */
    private function runAllPrerequisiteMigrations()
    {
        // Run existing migrations first
        Artisan::call('migrate');

        // Run tenant migrations
        $this->runPrerequisiteMigrations();

        Artisan::call('migrate', [
            '--path' => 'database/migrations/2025_08_10_120002_add_tenant_id_to_core_tables.php'
        ]);

        Artisan::call('migrate', [
            '--path' => 'database/migrations/2025_08_10_120003_add_tenant_id_to_business_tables.php'
        ]);

        Artisan::call('migrate', [
            '--path' => 'database/migrations/2025_08_10_120004_add_tenant_id_to_transaction_tables.php'
        ]);
    }

    /**
     * Helper method to run all tenant migrations
     */
    private function runAllMigrations()
    {
        $migrations = [
            '2025_08_10_120000_create_tenants_table.php',
            '2025_08_10_120001_create_tenant_relationship_tables.php',
            '2025_08_10_120002_add_tenant_id_to_core_tables.php',
            '2025_08_10_120003_add_tenant_id_to_business_tables.php',
            '2025_08_10_120004_add_tenant_id_to_transaction_tables.php',
            '2025_08_10_120005_create_default_tenant_and_migrate_data.php'
        ];

        foreach ($migrations as $migration) {
            Artisan::call('migrate', ['--path' => "database/migrations/{$migration}"]);
        }
    }

    /**
     * Helper method to create test data
     */
    private function createTestData()
    {
        // Create test users if users table exists
        if (Schema::hasTable('users')) {
            DB::table('users')->insert([
                'username' => 'testuser',
                'email' => 'test@example.com',
                'password' => bcrypt('password'),
                'full_name' => 'Test User',
                'address' => 'Test Address',
                'phone' => '1234567890',
                'birth_date' => now(),
                'active_code' => 'test123',
                'group_id' => '1',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    /**
     * Helper method to get foreign keys for a table
     */
    private function getForeignKeys($table)
    {
        return DB::select("
            SELECT 
                COLUMN_NAME, 
                REFERENCED_TABLE_NAME, 
                REFERENCED_COLUMN_NAME 
            FROM 
                INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
            WHERE 
                TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = ? 
                AND REFERENCED_TABLE_NAME IS NOT NULL
        ", [$table]);
    }

    /**
     * Helper method to get indexes for a table
     */
    private function getTableIndexes($table)
    {
        $indexes = DB::select("SHOW INDEX FROM {$table}");
        return collect($indexes)->pluck('Key_name')->unique()->toArray();
    }
}
