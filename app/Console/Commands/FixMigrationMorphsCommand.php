<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FixMigrationMorphsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migration:fix-morphs 
                            {--dry-run : Show what would be changed without making changes}
                            {--force : Force fix without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix morphs()->comment() issues in migration files';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Scanning migration files for morphs()->comment() issues...');
        $this->newLine();

        $migrationPath = database_path('migrations');
        $files = File::glob($migrationPath . '/*.php');
        
        $problematicFiles = [];
        
        foreach ($files as $file) {
            $content = File::get($file);
            
            // Check for morphs()->comment() pattern
            if (preg_match('/\$table->morphs\([^)]+\)[^;]*->comment\(/', $content)) {
                $problematicFiles[] = $file;
            }
        }

        if (empty($problematicFiles)) {
            $this->info('✅ No problematic migration files found!');
            return 0;
        }

        $this->warn('⚠️  Found ' . count($problematicFiles) . ' migration files with morphs()->comment() issues:');
        
        foreach ($problematicFiles as $file) {
            $this->line('   - ' . basename($file));
        }

        $this->newLine();

        if ($this->option('dry-run')) {
            $this->info('🔍 DRY RUN MODE - Showing what would be changed:');
            $this->newLine();
            
            foreach ($problematicFiles as $file) {
                $this->showWhatWouldBeFixed($file);
            }
            
            return 0;
        }

        if (!$this->option('force')) {
            if (!$this->confirm('Do you want to fix these files?')) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        $this->info('🔧 Fixing migration files...');
        $this->newLine();

        $fixedCount = 0;
        
        foreach ($problematicFiles as $file) {
            if ($this->fixMigrationFile($file)) {
                $fixedCount++;
                $this->info('✅ Fixed: ' . basename($file));
            } else {
                $this->error('❌ Failed to fix: ' . basename($file));
            }
        }

        $this->newLine();
        $this->info("🎉 Fixed {$fixedCount} out of " . count($problematicFiles) . " files!");

        return 0;
    }

    /**
     * Show what would be fixed in a file.
     */
    protected function showWhatWouldBeFixed($file)
    {
        $content = File::get($file);
        $filename = basename($file);
        
        $this->line("📄 {$filename}:");
        
        $lines = explode("\n", $content);
        
        foreach ($lines as $lineNumber => $line) {
            if (preg_match('/\$table->morphs\([^)]+\)[^;]*->comment\(/', $line)) {
                $this->line("   Line " . ($lineNumber + 1) . ": " . trim($line));
                
                // Show what it would be replaced with
                $fixed = $this->fixMorphsLine($line);
                if ($fixed !== $line) {
                    $this->line("   Would become:");
                    foreach (explode("\n", $fixed) as $fixedLine) {
                        if (trim($fixedLine)) {
                            $this->line("     " . trim($fixedLine));
                        }
                    }
                }
            }
        }
        
        $this->newLine();
    }

    /**
     * Fix a migration file.
     */
    protected function fixMigrationFile($file)
    {
        try {
            $content = File::get($file);
            $originalContent = $content;
            
            // Fix morphs()->comment() patterns
            $content = preg_replace_callback(
                '/(\s*)\$table->morphs\(([^)]+)\)([^;]*)->comment\(([^)]+)\);/',
                function ($matches) {
                    $indent = $matches[1];
                    $morphsParams = $matches[2];
                    $chainedMethods = $matches[3];
                    $comment = $matches[4];
                    
                    // Parse morphs parameters
                    $params = explode(',', $morphsParams);
                    $columnName = trim($params[0], '"\'');
                    $indexName = isset($params[1]) ? trim($params[1], '"\'') : null;
                    
                    // Generate replacement
                    $replacement = $indent . "// Tạo morphs columns riêng biệt để có thể thêm comment\n";
                    $replacement .= $indent . "\$table->string('{$columnName}_type')->comment({$comment});\n";
                    $replacement .= $indent . "\$table->unsignedBigInteger('{$columnName}_id')->comment('ID đối tượng');";
                    
                    return $replacement;
                },
                $content
            );
            
            // Add missing indexes if needed
            $content = $this->addMissingIndexes($content);
            
            if ($content !== $originalContent) {
                File::put($file, $content);
                return true;
            }
            
            return false;
            
        } catch (\Exception $e) {
            $this->error('Error fixing file: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Fix a single morphs line.
     */
    protected function fixMorphsLine($line)
    {
        return preg_replace_callback(
            '/(\s*)\$table->morphs\(([^)]+)\)([^;]*)->comment\(([^)]+)\);/',
            function ($matches) {
                $indent = $matches[1];
                $morphsParams = $matches[2];
                $comment = $matches[4];
                
                // Parse morphs parameters
                $params = explode(',', $morphsParams);
                $columnName = trim($params[0], '"\'');
                
                // Generate replacement
                $replacement = $indent . "// Tạo morphs columns riêng biệt để có thể thêm comment\n";
                $replacement .= $indent . "\$table->string('{$columnName}_type')->comment({$comment});\n";
                $replacement .= $indent . "\$table->unsignedBigInteger('{$columnName}_id')->comment('ID đối tượng');";
                
                return $replacement;
            },
            $line
        );
    }

    /**
     * Add missing indexes for morphs columns.
     */
    protected function addMissingIndexes($content)
    {
        // Look for morphs columns and ensure they have indexes
        $lines = explode("\n", $content);
        $newLines = [];
        $indexesToAdd = [];
        
        foreach ($lines as $line) {
            $newLines[] = $line;
            
            // Check if this line creates a morphs type column
            if (preg_match('/\$table->string\([\'"](\w+)_type[\'"]\)/', $line, $matches)) {
                $columnName = $matches[1];
                
                // Check if index already exists in the content
                if (!preg_match("/index\(\s*\[\s*['\"]?{$columnName}_type['\"]?\s*,\s*['\"]?{$columnName}_id['\"]?\s*\]/", $content)) {
                    $indexesToAdd[] = $columnName;
                }
            }
        }
        
        // Add missing indexes before the closing of Schema::create
        if (!empty($indexesToAdd)) {
            $insertPosition = -1;
            
            // Find the position to insert indexes (before the closing of Schema::create)
            for ($i = count($newLines) - 1; $i >= 0; $i--) {
                if (strpos($newLines[$i], '});') !== false && strpos($newLines[$i-1], 'Schema::create') === false) {
                    $insertPosition = $i;
                    break;
                }
            }
            
            if ($insertPosition > 0) {
                $indexLines = [];
                $indexLines[] = '';
                $indexLines[] = '            // Indexes for morphs columns';
                
                foreach ($indexesToAdd as $columnName) {
                    $indexLines[] = "            \$table->index(['{$columnName}_type', '{$columnName}_id'], '{$columnName}_index');";
                }
                
                array_splice($newLines, $insertPosition, 0, $indexLines);
            }
        }
        
        return implode("\n", $newLines);
    }
}
