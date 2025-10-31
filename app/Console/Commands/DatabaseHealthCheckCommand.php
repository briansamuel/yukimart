<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseHealthCheckCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'yukimart:db-health 
                            {--tables : Show table information}
                            {--counts : Show record counts}
                            {--indexes : Show index information}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check YukiMart database health and structure';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🏥 YukiMart Database Health Check');
        $this->info('='.str_repeat('=', 50));

        // Basic connection test
        $this->testDatabaseConnection();

        // Check required tables
        $this->checkRequiredTables();

        // Show table information if requested
        if ($this->option('tables')) {
            $this->showTableInformation();
        }

        // Show record counts if requested
        if ($this->option('counts')) {
            $this->showRecordCounts();
        }

        // Show index information if requested
        if ($this->option('indexes')) {
            $this->showIndexInformation();
        }

        $this->newLine();
        $this->info('✅ Database health check completed!');

        return 0;
    }

    /**
     * Test database connection
     */
    private function testDatabaseConnection()
    {
        $this->newLine();
        $this->info('🔌 Database Connection Test');
        $this->info('-'.str_repeat('-', 30));

        try {
            DB::connection()->getPdo();
            $this->info('✅ Database connection: OK');
            
            $dbName = DB::connection()->getDatabaseName();
            $this->info("📊 Database name: {$dbName}");
            
            $driver = DB::connection()->getDriverName();
            $this->info("🔧 Database driver: {$driver}");
            
        } catch (\Exception $e) {
            $this->error('❌ Database connection failed: ' . $e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * Check required tables
     */
    private function checkRequiredTables()
    {
        $this->newLine();
        $this->info('📋 Required Tables Check');
        $this->info('-'.str_repeat('-', 30));

        $requiredTables = [
            'users' => 'User accounts',
            'roles' => 'User roles',
            'permissions' => 'System permissions',
            'role_user' => 'User role assignments',
            'tenants' => 'Tenant information',
            'tenant_users' => 'Tenant user relationships',
            'products' => 'Product catalog',
            'orders' => 'Order management',
            'invoices' => 'Invoice records',
            'customers' => 'Customer database',
            'categories' => 'Product categories',
            'brands' => 'Product brands',
            'branch_shops' => 'Branch/shop locations',
            'payments' => 'Payment records',
            'return_orders' => 'Return order records'
        ];

        $missingTables = [];
        $existingTables = [];

        foreach ($requiredTables as $table => $description) {
            if (Schema::hasTable($table)) {
                $existingTables[] = $table;
                $this->info("✅ {$table} - {$description}");
            } else {
                $missingTables[] = $table;
                $this->error("❌ {$table} - {$description} (MISSING)");
            }
        }

        $this->newLine();
        $this->info("📊 Summary: " . count($existingTables) . " tables found, " . count($missingTables) . " missing");

        if (!empty($missingTables)) {
            $this->warn('⚠️ Missing tables detected. Run migrations:');
            $this->line('   php artisan migrate');
        }
    }

    /**
     * Show table information
     */
    private function showTableInformation()
    {
        $this->newLine();
        $this->info('📊 Table Information');
        $this->info('-'.str_repeat('-', 30));

        $tables = DB::select('SHOW TABLES');
        $tableData = [];

        foreach ($tables as $table) {
            $tableName = array_values((array) $table)[0];
            
            try {
                $columns = Schema::getColumnListing($tableName);
                $columnCount = count($columns);
                
                // Get table size (MySQL specific)
                $sizeQuery = "SELECT 
                    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'size_mb'
                    FROM information_schema.TABLES 
                    WHERE table_schema = DATABASE() 
                    AND table_name = ?";
                
                $sizeResult = DB::select($sizeQuery, [$tableName]);
                $sizeMB = $sizeResult[0]->size_mb ?? 0;

                $tableData[] = [
                    'table' => $tableName,
                    'columns' => $columnCount,
                    'size_mb' => $sizeMB
                ];
            } catch (\Exception $e) {
                $tableData[] = [
                    'table' => $tableName,
                    'columns' => 'Error',
                    'size_mb' => 'Error'
                ];
            }
        }

        $this->table(
            ['Table Name', 'Columns', 'Size (MB)'],
            $tableData
        );
    }

    /**
     * Show record counts
     */
    private function showRecordCounts()
    {
        $this->newLine();
        $this->info('📈 Record Counts');
        $this->info('-'.str_repeat('-', 30));

        $tables = [
            'users' => 'Users',
            'tenants' => 'Tenants',
            'tenant_users' => 'Tenant Users',
            'products' => 'Products',
            'orders' => 'Orders',
            'invoices' => 'Invoices',
            'customers' => 'Customers',
            'categories' => 'Categories',
            'brands' => 'Brands',
            'payments' => 'Payments'
        ];

        $countData = [];

        foreach ($tables as $table => $description) {
            if (Schema::hasTable($table)) {
                try {
                    $count = DB::table($table)->count();
                    $countData[] = [
                        'table' => $description,
                        'count' => number_format($count),
                        'status' => $count > 0 ? '✅' : '⚠️'
                    ];
                } catch (\Exception $e) {
                    $countData[] = [
                        'table' => $description,
                        'count' => 'Error',
                        'status' => '❌'
                    ];
                }
            } else {
                $countData[] = [
                    'table' => $description,
                    'count' => 'N/A',
                    'status' => '❌'
                ];
            }
        }

        $this->table(
            ['Table', 'Records', 'Status'],
            $countData
        );
    }

    /**
     * Show index information
     */
    private function showIndexInformation()
    {
        $this->newLine();
        $this->info('🔍 Index Information');
        $this->info('-'.str_repeat('-', 30));

        $importantTables = ['users', 'tenants', 'products', 'orders', 'invoices'];
        
        foreach ($importantTables as $table) {
            if (Schema::hasTable($table)) {
                try {
                    $indexes = DB::select("SHOW INDEX FROM {$table}");
                    $this->line("📋 {$table}:");
                    
                    foreach ($indexes as $index) {
                        $unique = $index->Non_unique == 0 ? ' (UNIQUE)' : '';
                        $this->line("   - {$index->Key_name} on {$index->Column_name}{$unique}");
                    }
                    $this->newLine();
                } catch (\Exception $e) {
                    $this->error("❌ Error getting indexes for {$table}: " . $e->getMessage());
                }
            }
        }
    }
}
