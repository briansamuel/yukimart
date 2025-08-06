<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use App\Services\DatabaseOptimizationService;

class GenerateOptimizationMigrations extends Command
{
    protected $signature = 'make:optimization-migrations {--dry-run : Show what would be generated without creating files}';
    protected $description = 'Generate database migrations for optimization recommendations';

    protected $dbOptimization;

    public function __construct(DatabaseOptimizationService $dbOptimization)
    {
        parent::__construct();
        $this->dbOptimization = $dbOptimization;
    }

    public function handle()
    {
        $this->info('🔍 Analyzing database for optimization opportunities...');
        
        $analysis = $this->dbOptimization->analyzePerformance();
        $migrations = [];

        // Generate index migrations
        if (!empty($analysis['missing_indexes'])) {
            $migrations['indexes'] = $this->generateIndexMigrations($analysis['missing_indexes']);
        }

        // Generate table optimization migrations
        if (!empty($analysis['table_sizes'])) {
            $migrations['optimizations'] = $this->generateOptimizationMigrations($analysis['table_sizes']);
        }

        if (empty($migrations)) {
            $this->info('✅ No optimization migrations needed. Database is already optimized!');
            return 0;
        }

        if ($this->option('dry-run')) {
            $this->displayDryRun($migrations);
        } else {
            $this->createMigrations($migrations);
        }

        return 0;
    }

    /**
     * Generate index migrations
     */
    protected function generateIndexMigrations(array $missingIndexes): array
    {
        $migrations = [];
        $groupedByTable = [];

        // Group indexes by table
        foreach ($missingIndexes as $index) {
            $groupedByTable[$index['table']][] = $index;
        }

        foreach ($groupedByTable as $table => $indexes) {
            $className = 'Add' . Str::studly($table) . 'Indexes';
            $filename = date('Y_m_d_His') . '_add_' . $table . '_indexes.php';
            
            $migrations[] = [
                'type' => 'index',
                'table' => $table,
                'class_name' => $className,
                'filename' => $filename,
                'content' => $this->generateIndexMigrationContent($className, $table, $indexes)
            ];
        }

        return $migrations;
    }

    /**
     * Generate optimization migrations
     */
    protected function generateOptimizationMigrations(array $tableSizes): array
    {
        $migrations = [];
        
        // Find large tables that might need optimization
        $largeTables = array_filter($tableSizes, function($table) {
            return $table['total_size_mb'] > 100; // Tables larger than 100MB
        });

        if (!empty($largeTables)) {
            $className = 'OptimizeLargeTables';
            $filename = date('Y_m_d_His', time() + 1) . '_optimize_large_tables.php';
            
            $migrations[] = [
                'type' => 'optimization',
                'class_name' => $className,
                'filename' => $filename,
                'content' => $this->generateOptimizationMigrationContent($className, $largeTables)
            ];
        }

        return $migrations;
    }

    /**
     * Generate index migration content
     */
    protected function generateIndexMigrationContent(string $className, string $table, array $indexes): string
    {
        $content = "<?php\n\n";
        $content .= "use Illuminate\\Database\\Migrations\\Migration;\n";
        $content .= "use Illuminate\\Database\\Schema\\Blueprint;\n";
        $content .= "use Illuminate\\Support\\Facades\\Schema;\n\n";
        $content .= "return new class extends Migration\n{\n";
        $content .= "    /**\n";
        $content .= "     * Run the migrations.\n";
        $content .= "     */\n";
        $content .= "    public function up(): void\n";
        $content .= "    {\n";
        $content .= "        Schema::table('{$table}', function (Blueprint \$table) {\n";

        foreach ($indexes as $index) {
            $columns = implode("', '", $index['columns']);
            $indexName = $index['index_name'];
            
            $content .= "            // {$index['reason']}\n";
            
            if (count($index['columns']) === 1) {
                $content .= "            \$table->index('{$columns}', '{$indexName}');\n";
            } else {
                $content .= "            \$table->index(['{$columns}'], '{$indexName}');\n";
            }
            $content .= "\n";
        }

        $content .= "        });\n";
        $content .= "    }\n\n";
        $content .= "    /**\n";
        $content .= "     * Reverse the migrations.\n";
        $content .= "     */\n";
        $content .= "    public function down(): void\n";
        $content .= "    {\n";
        $content .= "        Schema::table('{$table}', function (Blueprint \$table) {\n";

        foreach ($indexes as $index) {
            $content .= "            \$table->dropIndex('{$index['index_name']}');\n";
        }

        $content .= "        });\n";
        $content .= "    }\n";
        $content .= "};\n";

        return $content;
    }

    /**
     * Generate optimization migration content
     */
    protected function generateOptimizationMigrationContent(string $className, array $largeTables): string
    {
        $content = "<?php\n\n";
        $content .= "use Illuminate\\Database\\Migrations\\Migration;\n";
        $content .= "use Illuminate\\Support\\Facades\\DB;\n";
        $content .= "use Illuminate\\Support\\Facades\\Log;\n\n";
        $content .= "return new class extends Migration\n{\n";
        $content .= "    /**\n";
        $content .= "     * Run the migrations.\n";
        $content .= "     */\n";
        $content .= "    public function up(): void\n";
        $content .= "    {\n";
        $content .= "        // Optimize large tables for better performance\n";
        $content .= "        \$tables = [\n";

        foreach ($largeTables as $table) {
            $content .= "            '{$table['table']}', // Size: {$table['total_size_mb']}MB, Rows: {$table['rows']}\n";
        }

        $content .= "        ];\n\n";
        $content .= "        foreach (\$tables as \$table) {\n";
        $content .= "            try {\n";
        $content .= "                Log::info(\"Optimizing table: {\$table}\");\n";
        $content .= "                \n";
        $content .= "                // Analyze table\n";
        $content .= "                DB::statement(\"ANALYZE TABLE {\$table}\");\n";
        $content .= "                \n";
        $content .= "                // Optimize table\n";
        $content .= "                DB::statement(\"OPTIMIZE TABLE {\$table}\");\n";
        $content .= "                \n";
        $content .= "                Log::info(\"Successfully optimized table: {\$table}\");\n";
        $content .= "            } catch (\\Exception \$e) {\n";
        $content .= "                Log::error(\"Failed to optimize table {\$table}: \" . \$e->getMessage());\n";
        $content .= "            }\n";
        $content .= "        }\n";
        $content .= "    }\n\n";
        $content .= "    /**\n";
        $content .= "     * Reverse the migrations.\n";
        $content .= "     */\n";
        $content .= "    public function down(): void\n";
        $content .= "    {\n";
        $content .= "        // Table optimization cannot be reversed\n";
        $content .= "        // This is a one-way optimization\n";
        $content .= "    }\n";
        $content .= "};\n";

        return $content;
    }

    /**
     * Display dry run results
     */
    protected function displayDryRun(array $migrations): void
    {
        $this->info('🔍 Dry Run - Migrations that would be generated:');
        $this->newLine();

        foreach ($migrations as $type => $migrationList) {
            $this->line("📁 {$type} migrations:");
            
            foreach ($migrationList as $migration) {
                $this->line("  📄 {$migration['filename']}");
                $this->line("     Class: {$migration['class_name']}");
                
                if (isset($migration['table'])) {
                    $this->line("     Table: {$migration['table']}");
                }
                
                $this->newLine();
            }
        }

        $totalMigrations = array_sum(array_map('count', $migrations));
        $this->info("Total migrations to be created: {$totalMigrations}");
        $this->newLine();
        $this->comment('Run without --dry-run to create the migration files.');
    }

    /**
     * Create migration files
     */
    protected function createMigrations(array $migrations): void
    {
        $created = 0;
        $migrationPath = database_path('migrations');

        foreach ($migrations as $type => $migrationList) {
            $this->info("Creating {$type} migrations...");
            
            foreach ($migrationList as $migration) {
                $filePath = $migrationPath . '/' . $migration['filename'];
                
                if (File::exists($filePath)) {
                    $this->warn("  ⚠️  Migration already exists: {$migration['filename']}");
                    continue;
                }

                File::put($filePath, $migration['content']);
                $this->line("  ✅ Created: {$migration['filename']}");
                $created++;
                
                // Add small delay to ensure unique timestamps
                sleep(1);
            }
        }

        $this->newLine();
        $this->info("✅ Successfully created {$created} migration files!");
        $this->newLine();
        $this->comment('Next steps:');
        $this->comment('1. Review the generated migrations');
        $this->comment('2. Run: php artisan migrate');
        $this->comment('3. Monitor database performance after applying');
    }

    /**
     * Generate migration class name
     */
    protected function generateMigrationClassName(string $table, string $type): string
    {
        $studlyTable = Str::studly($table);
        
        switch ($type) {
            case 'index':
                return "Add{$studlyTable}Indexes";
            case 'optimization':
                return "Optimize{$studlyTable}Table";
            default:
                return "Update{$studlyTable}Table";
        }
    }

    /**
     * Generate migration filename
     */
    protected function generateMigrationFilename(string $table, string $type): string
    {
        $timestamp = date('Y_m_d_His');
        $snakeTable = Str::snake($table);
        
        switch ($type) {
            case 'index':
                return "{$timestamp}_add_{$snakeTable}_indexes.php";
            case 'optimization':
                return "{$timestamp}_optimize_{$snakeTable}_table.php";
            default:
                return "{$timestamp}_update_{$snakeTable}_table.php";
        }
    }
}
