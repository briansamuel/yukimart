<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class RemoveMigrationCommentsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migration:remove-comments 
                            {--dry-run : Show what would be changed without making changes}
                            {--force : Force remove without confirmation}
                            {--file= : Process specific file only}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove ->comment() from migration files and replace with // comments';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Scanning migration files for ->comment() patterns...');
        $this->newLine();

        $migrationPath = database_path('migrations');
        
        if ($this->option('file')) {
            $files = [database_path('migrations/' . $this->option('file'))];
            if (!file_exists($files[0])) {
                $this->error('File not found: ' . $this->option('file'));
                return 1;
            }
        } else {
            $files = File::glob($migrationPath . '/*.php');
        }
        
        $filesWithComments = [];
        
        foreach ($files as $file) {
            $content = File::get($file);
            
            // Check for ->comment() pattern
            if (preg_match('/->comment\(/', $content)) {
                $filesWithComments[] = $file;
            }
        }

        if (empty($filesWithComments)) {
            $this->info('✅ No migration files with ->comment() found!');
            return 0;
        }

        $this->warn('⚠️  Found ' . count($filesWithComments) . ' migration files with ->comment():');
        
        foreach ($filesWithComments as $file) {
            $this->line('   - ' . basename($file));
        }

        $this->newLine();

        if ($this->option('dry-run')) {
            $this->info('🔍 DRY RUN MODE - Showing what would be changed:');
            $this->newLine();
            
            foreach ($filesWithComments as $file) {
                $this->showWhatWouldBeChanged($file);
            }
            
            return 0;
        }

        if (!$this->option('force')) {
            if (!$this->confirm('Do you want to remove comments from these files?')) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        $this->info('🔧 Removing comments from migration files...');
        $this->newLine();

        $processedCount = 0;
        
        foreach ($filesWithComments as $file) {
            if ($this->processMigrationFile($file)) {
                $processedCount++;
                $this->info('✅ Processed: ' . basename($file));
            } else {
                $this->error('❌ Failed to process: ' . basename($file));
            }
        }

        $this->newLine();
        $this->info("🎉 Processed {$processedCount} out of " . count($filesWithComments) . " files!");

        return 0;
    }

    /**
     * Show what would be changed in a file.
     */
    protected function showWhatWouldBeChanged($file)
    {
        $content = File::get($file);
        $filename = basename($file);
        
        $this->line("📄 {$filename}:");
        
        $lines = explode("\n", $content);
        
        foreach ($lines as $lineNumber => $line) {
            if (preg_match('/->comment\(/', $line)) {
                $this->line("   Line " . ($lineNumber + 1) . ": " . trim($line));
                
                // Show what it would be replaced with
                $fixed = $this->processLine($line);
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
     * Process a migration file.
     */
    protected function processMigrationFile($file)
    {
        try {
            $content = File::get($file);
            $originalContent = $content;
            
            $lines = explode("\n", $content);
            $newLines = [];
            
            foreach ($lines as $line) {
                $processedLine = $this->processLine($line);
                
                // If line was processed (comment removed), add comment line before it
                if ($processedLine !== $line && preg_match('/\$table->/', $line)) {
                    $comment = $this->extractComment($line);
                    if ($comment) {
                        $indent = $this->getIndentation($line);
                        $newLines[] = $indent . '// ' . $comment;
                    }
                }
                
                $newLines[] = $processedLine;
            }
            
            $newContent = implode("\n", $newLines);
            
            if ($newContent !== $originalContent) {
                File::put($file, $newContent);
                return true;
            }
            
            return false;
            
        } catch (\Exception $e) {
            $this->error('Error processing file: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Process a single line.
     */
    protected function processLine($line)
    {
        // Remove ->comment('...') or ->comment("...")
        return preg_replace('/->comment\([\'"][^\'"]*[\'"]\)/', '', $line);
    }

    /**
     * Extract comment text from line.
     */
    protected function extractComment($line)
    {
        if (preg_match('/->comment\([\'"]([^\'"]*)[\'"]/', $line, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Get indentation of a line.
     */
    protected function getIndentation($line)
    {
        preg_match('/^(\s*)/', $line, $matches);
        return $matches[1] ?? '';
    }

    /**
     * Clean up extra semicolons and formatting.
     */
    protected function cleanupLine($line)
    {
        // Remove double semicolons
        $line = preg_replace('/;;+/', ';', $line);
        
        // Clean up extra spaces
        $line = preg_replace('/\s+;/', ';', $line);
        
        return $line;
    }
}
