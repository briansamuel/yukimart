<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Exception;

class TestTenantMigrations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:test-migrations 
                            {--rollback : Test rollback functionality}
                            {--verbose : Show detailed output}
                            {--dry-run : Show what would be done without executing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test tenant migration files for correctness and functionality';

    /**
     * Migration files in execution order
     */
    private $migrationFiles = [
        '2025_08_10_120000_create_tenants_table.php',
        '2025_08_10_120001_create_tenant_relationship_tables.php',
        '2025_08_10_120002_add_tenant_id_to_core_tables.php',
        '2025_08_10_120003_add_tenant_id_to_business_tables.php',
        '2025_08_10_120004_add_tenant_id_to_transaction_tables.php',
        '2025_08_10_120005_create_default_tenant_and_migrate_data.php'
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 TENANT MIGRATION TESTING STARTED');
        $this->newLine();

        if ($this->option('dry-run')) {
            $this->showDryRun();
            return 0;
        }

        try {
            // Create backup
            $this->createBackup();

            if ($this->option('rollback')) {
                $this->testRollback();
            } else {
                $this->testMigrations();
            }

            $this->info('✅ ALL TESTS PASSED SUCCESSFULLY!');
            return 0;

        } catch (Exception $e) {
            $this->error('❌ TEST FAILED: ' . $e->getMessage());
            $this->restoreBackup();
            return 1;
        }
    }

    /**
     * Show what would be done without executing
     */
    private function showDryRun()
    {
        $this->info('🔍 DRY RUN - SHOWING PLANNED ACTIONS');
        $this->newLine();

        $this->info('📋 Migration files to be tested:');
        foreach ($this->migrationFiles as $index => $file) {
            $this->line("  " . ($index + 1) . ". {$file}");
        }

        $this->newLine();
        $this->info('🔍 Tests that would be performed:');
        $this->line('  ✓ Table creation validation');
        $this->line('  ✓ Column existence checks');
        $this->line('  ✓ Foreign key constraint validation');
        $this->line('  ✓ Index creation verification');
        $this->line('  ✓ Data migration testing');
        $this->line('  ✓ Rollback functionality testing');

        $this->newLine();
        $this->info('💡 To execute actual tests, run without --dry-run flag');
    }

    /**
     * Test migration execution
     */
    private function testMigrations()
    {
        $this->info('🚀 Testing Migration Execution...');
        $this->newLine();

        foreach ($this->migrationFiles as $index => $file) {
            $step = $index + 1;
            $this->info("📝 Step {$step}: Testing {$file}");

            try {
                // Execute migration
                Artisan::call('migrate', [
                    '--path' => "database/migrations/{$file}",
                    '--force' => true
                ]);

                // Validate migration
                $this->validateMigrationStep($step, $file);
                
                $this->info("✅ Step {$step} completed successfully");
                $this->newLine();

            } catch (Exception $e) {
                throw new Exception("Migration step {$step} failed: " . $e->getMessage());
            }
        }

        // Final validation
        $this->performFinalValidation();
    }

    /**
     * Test rollback functionality
     */
    private function testRollback()
    {
        $this->info('🔄 Testing Rollback Functionality...');
        $this->newLine();

        // First run all migrations
        $this->info('📝 Running all migrations first...');
        foreach ($this->migrationFiles as $file) {
            Artisan::call('migrate', [
                '--path' => "database/migrations/{$file}",
                '--force' => true
            ]);
        }

        // Then rollback in reverse order
        $this->info('📝 Rolling back migrations...');
        $reversedFiles = array_reverse($this->migrationFiles);

        foreach ($reversedFiles as $index => $file) {
            $step = $index + 1;
            $this->info("🔄 Rollback Step {$step}: {$file}");

            try {
                Artisan::call('migrate:rollback', [
                    '--path' => "database/migrations/{$file}",
                    '--force' => true
                ]);

                $this->info("✅ Rollback Step {$step} completed");

            } catch (Exception $e) {
                throw new Exception("Rollback step {$step} failed: " . $e->getMessage());
            }
        }

        $this->info('✅ All rollbacks completed successfully');
    }

    /**
     * Validate specific migration step
     */
    private function validateMigrationStep($step, $file)
    {
        switch ($step) {
            case 1:
                $this->validateTenantsTable();
                break;
            case 2:
                $this->validateRelationshipTables();
                break;
            case 3:
                $this->validateCoreTables();
                break;
            case 4:
                $this->validateBusinessTables();
                break;
            case 5:
                $this->validateTransactionTables();
                break;
            case 6:
                $this->validateDataMigration();
                break;
        }
    }

    /**
     * Validate tenants table creation
     */
    private function validateTenantsTable()
    {
        if (!Schema::hasTable('tenants')) {
            throw new Exception('Tenants table was not created');
        }

        $requiredColumns = ['id', 'name', 'slug', 'subdomain', 'status', 'plan_type'];
        foreach ($requiredColumns as $column) {
            if (!Schema::hasColumn('tenants', $column)) {
                throw new Exception("Required column '{$column}' missing in tenants table");
            }
        }

        if ($this->option('verbose')) {
            $this->line('  ✓ Tenants table created with all required columns');
        }
    }

    /**
     * Validate relationship tables creation
     */
    private function validateRelationshipTables()
    {
        $tables = ['tenant_users', 'tenant_settings', 'tenant_invitations', 'tenant_activity_logs'];
        
        foreach ($tables as $table) {
            if (!Schema::hasTable($table)) {
                throw new Exception("Table '{$table}' was not created");
            }
        }

        if ($this->option('verbose')) {
            $this->line('  ✓ All relationship tables created successfully');
        }
    }

    /**
     * Validate core tables tenant_id addition
     */
    private function validateCoreTables()
    {
        $tables = ['users', 'roles', 'permissions', 'branch_shops'];
        
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                if (!Schema::hasColumn($table, 'tenant_id')) {
                    throw new Exception("tenant_id column missing in {$table} table");
                }
            }
        }

        if ($this->option('verbose')) {
            $this->line('  ✓ tenant_id added to all core tables');
        }
    }

    /**
     * Validate business tables tenant_id addition
     */
    private function validateBusinessTables()
    {
        $tables = ['customers', 'suppliers', 'categories', 'products', 'inventories'];
        
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                if (!Schema::hasColumn($table, 'tenant_id')) {
                    throw new Exception("tenant_id column missing in {$table} table");
                }
            }
        }

        if ($this->option('verbose')) {
            $this->line('  ✓ tenant_id added to all business tables');
        }
    }

    /**
     * Validate transaction tables tenant_id addition
     */
    private function validateTransactionTables()
    {
        $tables = ['orders', 'invoices', 'payments'];
        
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                if (!Schema::hasColumn($table, 'tenant_id')) {
                    throw new Exception("tenant_id column missing in {$table} table");
                }
            }
        }

        if ($this->option('verbose')) {
            $this->line('  ✓ tenant_id added to all transaction tables');
        }
    }

    /**
     * Validate data migration
     */
    private function validateDataMigration()
    {
        // Check if default tenant was created
        $tenantCount = DB::table('tenants')->count();
        if ($tenantCount === 0) {
            throw new Exception('Default tenant was not created');
        }

        // Check if data was migrated
        $tables = ['users', 'products', 'orders'];
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                $nullTenantCount = DB::table($table)->whereNull('tenant_id')->count();
                if ($nullTenantCount > 0) {
                    throw new Exception("Some records in {$table} table do not have tenant_id assigned");
                }
            }
        }

        if ($this->option('verbose')) {
            $this->line('  ✓ Default tenant created and data migrated successfully');
        }
    }

    /**
     * Perform final validation
     */
    private function performFinalValidation()
    {
        $this->info('🔍 Performing Final Validation...');

        // Check foreign key constraints
        $this->validateForeignKeyConstraints();

        // Check indexes
        $this->validateIndexes();

        // Check data integrity
        $this->validateDataIntegrity();

        $this->info('✅ Final validation completed successfully');
    }

    /**
     * Validate foreign key constraints
     */
    private function validateForeignKeyConstraints()
    {
        try {
            // Try to insert invalid tenant_id
            DB::table('users')->insert([
                'tenant_id' => 999999,
                'username' => 'test_invalid',
                'email' => 'test_invalid@example.com',
                'password' => 'test',
                'full_name' => 'Test Invalid',
                'address' => 'Test',
                'phone' => '123456789',
                'birth_date' => now(),
                'active_code' => 'test',
                'group_id' => '1'
            ]);

            throw new Exception('Foreign key constraint not working - invalid tenant_id was accepted');

        } catch (Exception $e) {
            if (str_contains($e->getMessage(), 'foreign key constraint')) {
                if ($this->option('verbose')) {
                    $this->line('  ✓ Foreign key constraints working correctly');
                }
            } else {
                throw $e;
            }
        }
    }

    /**
     * Validate indexes
     */
    private function validateIndexes()
    {
        $tables = ['users', 'products', 'orders'];
        
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                $indexes = DB::select("SHOW INDEX FROM {$table}");
                $hastenantIndex = collect($indexes)->contains(function ($index) {
                    return str_contains($index->Key_name, 'tenant');
                });

                if (!$hastenantIndex) {
                    throw new Exception("No tenant-related index found in {$table} table");
                }
            }
        }

        if ($this->option('verbose')) {
            $this->line('  ✓ All required indexes created');
        }
    }

    /**
     * Validate data integrity
     */
    private function validateDataIntegrity()
    {
        // Check tenant-user relationships
        if (Schema::hasTable('tenant_users')) {
            $userCount = DB::table('users')->count();
            $tenantUserCount = DB::table('tenant_users')->count();

            if ($userCount > 0 && $tenantUserCount === 0) {
                throw new Exception('Tenant-user relationships not created');
            }
        }

        if ($this->option('verbose')) {
            $this->line('  ✓ Data integrity validated');
        }
    }

    /**
     * Create database backup
     */
    private function createBackup()
    {
        $this->info('💾 Creating database backup...');
        
        // This is a placeholder - implement actual backup logic
        if ($this->option('verbose')) {
            $this->line('  ✓ Backup created (placeholder)');
        }
    }

    /**
     * Restore database backup
     */
    private function restoreBackup()
    {
        $this->warn('🔄 Restoring database backup...');
        
        // This is a placeholder - implement actual restore logic
        if ($this->option('verbose')) {
            $this->line('  ✓ Backup restored (placeholder)');
        }
    }
}
