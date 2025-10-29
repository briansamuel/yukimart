<?php

/**
 * Tenant Migration Validation Script
 * 
 * This script validates tenant migration files without executing them
 * Run: php scripts/validate_tenant_migrations.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

class TenantMigrationValidator
{
    private $migrationPath;
    private $errors = [];
    private $warnings = [];

    public function __construct()
    {
        $this->migrationPath = __DIR__ . '/../database/migrations/';
    }

    public function validate()
    {
        echo "🔍 TENANT MIGRATION VALIDATION\n";
        echo "==============================\n\n";

        $this->validateMigrationFiles();
        $this->validateMigrationContent();
        $this->validateMigrationOrder();
        $this->showResults();

        return empty($this->errors);
    }

    private function validateMigrationFiles()
    {
        echo "📁 Checking migration files...\n";

        $expectedFiles = [
            '2025_08_10_120000_create_tenants_table.php',
            '2025_08_10_120001_create_tenant_relationship_tables.php',
            '2025_08_10_120002_add_tenant_id_to_core_tables.php',
            '2025_08_10_120003_add_tenant_id_to_business_tables.php',
            '2025_08_10_120004_add_tenant_id_to_transaction_tables.php',
            '2025_08_10_120005_create_default_tenant_and_migrate_data.php'
        ];

        foreach ($expectedFiles as $file) {
            $filePath = $this->migrationPath . $file;
            if (file_exists($filePath)) {
                echo "  ✅ {$file}\n";
            } else {
                $this->errors[] = "Missing migration file: {$file}";
                echo "  ❌ {$file} - NOT FOUND\n";
            }
        }

        echo "\n";
    }

    private function validateMigrationContent()
    {
        echo "📝 Validating migration content...\n";

        $this->validateTenantsTableMigration();
        $this->validateRelationshipTablesMigration();
        $this->validateTenantIdMigrations();
        $this->validateDataMigration();

        echo "\n";
    }

    private function validateTenantsTableMigration()
    {
        $file = '2025_08_10_120000_create_tenants_table.php';
        $content = $this->getMigrationContent($file);

        if (!$content) {
            $this->errors[] = "Cannot read {$file}";
            return;
        }

        // Check for required table creation
        if (!str_contains($content, "Schema::create('tenants'")) {
            $this->errors[] = "Tenants table creation not found in {$file}";
        } else {
            echo "  ✅ Tenants table creation found\n";
        }

        // Check for required columns
        $requiredColumns = ['name', 'slug', 'subdomain', 'status', 'plan_type'];
        foreach ($requiredColumns as $column) {
            if (!str_contains($content, "'{$column}'")) {
                $this->warnings[] = "Column '{$column}' might be missing in tenants table";
            }
        }

        // Check for indexes
        if (!str_contains($content, 'index(')) {
            $this->warnings[] = "No indexes found in tenants table migration";
        } else {
            echo "  ✅ Indexes found in tenants table\n";
        }
    }

    private function validateRelationshipTablesMigration()
    {
        $file = '2025_08_10_120001_create_tenant_relationship_tables.php';
        $content = $this->getMigrationContent($file);

        if (!$content) {
            $this->errors[] = "Cannot read {$file}";
            return;
        }

        $expectedTables = ['tenant_users', 'tenant_settings', 'tenant_invitations', 'tenant_activity_logs'];
        
        foreach ($expectedTables as $table) {
            if (!str_contains($content, "Schema::create('{$table}'")) {
                $this->errors[] = "Table '{$table}' creation not found in {$file}";
            } else {
                echo "  ✅ {$table} table creation found\n";
            }
        }

        // Check for foreign key constraints
        if (!str_contains($content, 'constrained(')) {
            $this->warnings[] = "No foreign key constraints found in relationship tables";
        }
    }

    private function validateTenantIdMigrations()
    {
        $files = [
            '2025_08_10_120002_add_tenant_id_to_core_tables.php',
            '2025_08_10_120003_add_tenant_id_to_business_tables.php',
            '2025_08_10_120004_add_tenant_id_to_transaction_tables.php'
        ];

        foreach ($files as $file) {
            $content = $this->getMigrationContent($file);
            
            if (!$content) {
                $this->errors[] = "Cannot read {$file}";
                continue;
            }

            // Check for tenant_id column addition
            if (!str_contains($content, 'tenant_id')) {
                $this->errors[] = "tenant_id column addition not found in {$file}";
            } else {
                echo "  ✅ tenant_id additions found in {$file}\n";
            }

            // Check for foreign key constraints
            if (!str_contains($content, 'constrained(\'tenants\')')) {
                $this->warnings[] = "Foreign key to tenants table might be missing in {$file}";
            }

            // Check for indexes
            if (!str_contains($content, 'index(')) {
                $this->warnings[] = "No indexes found in {$file}";
            }

            // Check for rollback functionality
            if (!str_contains($content, 'dropForeign') || !str_contains($content, 'dropColumn')) {
                $this->warnings[] = "Rollback functionality might be incomplete in {$file}";
            }
        }
    }

    private function validateDataMigration()
    {
        $file = '2025_08_10_120005_create_default_tenant_and_migrate_data.php';
        $content = $this->getMigrationContent($file);

        if (!$content) {
            $this->errors[] = "Cannot read {$file}";
            return;
        }

        // Check for default tenant creation
        if (!str_contains($content, 'insertGetId') && !str_contains($content, 'insert')) {
            $this->errors[] = "Default tenant creation not found in {$file}";
        } else {
            echo "  ✅ Default tenant creation found\n";
        }

        // Check for data migration logic
        if (!str_contains($content, 'update')) {
            $this->warnings[] = "Data migration logic might be missing in {$file}";
        } else {
            echo "  ✅ Data migration logic found\n";
        }

        // Check for tenant-user relationship creation
        if (!str_contains($content, 'tenant_users')) {
            $this->warnings[] = "Tenant-user relationship creation might be missing";
        }
    }

    private function validateMigrationOrder()
    {
        echo "🔢 Validating migration order...\n";

        $files = glob($this->migrationPath . '2025_08_10_12000*_*.php');
        sort($files);

        $expectedOrder = [
            'create_tenants_table',
            'create_tenant_relationship_tables',
            'add_tenant_id_to_core_tables',
            'add_tenant_id_to_business_tables',
            'add_tenant_id_to_transaction_tables',
            'create_default_tenant_and_migrate_data'
        ];

        foreach ($files as $index => $file) {
            $filename = basename($file);
            $expectedPattern = $expectedOrder[$index] ?? null;

            if ($expectedPattern && str_contains($filename, $expectedPattern)) {
                echo "  ✅ {$filename} - Correct order\n";
            } else {
                $this->warnings[] = "Migration order might be incorrect for {$filename}";
            }
        }

        echo "\n";
    }

    private function getMigrationContent($filename)
    {
        $filePath = $this->migrationPath . $filename;
        return file_exists($filePath) ? file_get_contents($filePath) : false;
    }

    private function showResults()
    {
        echo "📊 VALIDATION RESULTS\n";
        echo "====================\n\n";

        if (empty($this->errors) && empty($this->warnings)) {
            echo "🎉 ALL VALIDATIONS PASSED!\n";
            echo "✅ Migration files are ready for execution\n\n";
        } else {
            if (!empty($this->errors)) {
                echo "❌ ERRORS FOUND:\n";
                foreach ($this->errors as $error) {
                    echo "  • {$error}\n";
                }
                echo "\n";
            }

            if (!empty($this->warnings)) {
                echo "⚠️  WARNINGS:\n";
                foreach ($this->warnings as $warning) {
                    echo "  • {$warning}\n";
                }
                echo "\n";
            }
        }

        echo "📈 SUMMARY:\n";
        echo "  Errors: " . count($this->errors) . "\n";
        echo "  Warnings: " . count($this->warnings) . "\n";
        echo "  Status: " . (empty($this->errors) ? "READY" : "NEEDS FIXES") . "\n\n";

        if (!empty($this->errors)) {
            echo "🔧 Please fix the errors before proceeding with migration execution.\n";
        } else {
            echo "🚀 Ready to execute migrations! Use the following commands:\n";
            echo "  php artisan tenant:test-migrations --dry-run\n";
            echo "  php artisan tenant:test-migrations\n";
            echo "  php artisan test --filter TenantMigrationTest\n";
        }
    }
}

// Run validation
$validator = new TenantMigrationValidator();
$success = $validator->validate();

exit($success ? 0 : 1);
