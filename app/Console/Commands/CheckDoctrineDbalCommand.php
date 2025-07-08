<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CheckDoctrineDbalCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:doctrine-dbal 
                            {--install : Automatically install doctrine/dbal if missing}
                            {--force : Force installation without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if doctrine/dbal is installed and optionally install it';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Checking doctrine/dbal installation...');
        $this->newLine();

        // Check if doctrine/dbal is installed
        $isInstalled = $this->checkDoctrineDbalInstallation();

        if ($isInstalled) {
            $this->info('✅ doctrine/dbal is already installed!');
            $this->showInstalledVersion();
            return 0;
        }

        $this->warn('⚠️  doctrine/dbal is not installed.');
        $this->newLine();

        $this->info('📋 Why you might need doctrine/dbal:');
        $this->line('   - Required for column modifications in migrations');
        $this->line('   - Needed for ->change() methods in Schema builder');
        $this->line('   - Used by Laravel for database schema introspection');
        $this->newLine();

        $this->info('🔧 Alternative solutions:');
        $this->line('   1. Install doctrine/dbal (recommended)');
        $this->line('   2. Use raw SQL in migrations (already implemented)');
        $this->line('   3. Create new migrations instead of modifying columns');
        $this->newLine();

        if ($this->option('install')) {
            return $this->installDoctrineDbal();
        }

        if ($this->confirm('Would you like to install doctrine/dbal now?')) {
            return $this->installDoctrineDbal();
        }

        $this->info('💡 To install manually, run: composer require doctrine/dbal');
        return 0;
    }

    /**
     * Check if doctrine/dbal is installed.
     */
    protected function checkDoctrineDbalInstallation()
    {
        // Method 1: Check if class exists
        if (class_exists('Doctrine\DBAL\Connection')) {
            return true;
        }

        // Method 2: Check composer.json
        $composerPath = base_path('composer.json');
        if (File::exists($composerPath)) {
            $composer = json_decode(File::get($composerPath), true);
            
            if (isset($composer['require']['doctrine/dbal']) || 
                isset($composer['require-dev']['doctrine/dbal'])) {
                return true;
            }
        }

        // Method 3: Check vendor directory
        $vendorPath = base_path('vendor/doctrine/dbal');
        if (File::isDirectory($vendorPath)) {
            return true;
        }

        return false;
    }

    /**
     * Show installed version information.
     */
    protected function showInstalledVersion()
    {
        try {
            $composerPath = base_path('composer.lock');
            if (File::exists($composerPath)) {
                $composerLock = json_decode(File::get($composerPath), true);
                
                foreach ($composerLock['packages'] as $package) {
                    if ($package['name'] === 'doctrine/dbal') {
                        $this->info("📦 Installed version: {$package['version']}");
                        return;
                    }
                }
            }
            
            $this->info('📦 Version information not available');
        } catch (\Exception $e) {
            $this->info('📦 Could not determine version');
        }
    }

    /**
     * Install doctrine/dbal.
     */
    protected function installDoctrineDbal()
    {
        if (!$this->option('force')) {
            if (!$this->confirm('This will run "composer require doctrine/dbal". Continue?')) {
                $this->info('Installation cancelled.');
                return 0;
            }
        }

        $this->info('🔧 Installing doctrine/dbal...');
        $this->newLine();

        // Run composer require
        $command = 'composer require doctrine/dbal';
        $this->line("Running: {$command}");
        $this->newLine();

        $process = proc_open(
            $command,
            [
                0 => ['pipe', 'r'],  // stdin
                1 => ['pipe', 'w'],  // stdout
                2 => ['pipe', 'w'],  // stderr
            ],
            $pipes,
            base_path()
        );

        if (is_resource($process)) {
            // Close stdin
            fclose($pipes[0]);

            // Read stdout
            $output = stream_get_contents($pipes[1]);
            fclose($pipes[1]);

            // Read stderr
            $error = stream_get_contents($pipes[2]);
            fclose($pipes[2]);

            // Get return code
            $returnCode = proc_close($process);

            if ($returnCode === 0) {
                $this->info('✅ doctrine/dbal installed successfully!');
                $this->newLine();
                $this->info('🎉 You can now run migrations that modify columns:');
                $this->line('   php artisan migrate');
                return 0;
            } else {
                $this->error('❌ Failed to install doctrine/dbal');
                $this->newLine();
                
                if ($output) {
                    $this->line('Output:');
                    $this->line($output);
                }
                
                if ($error) {
                    $this->line('Error:');
                    $this->line($error);
                }
                
                $this->newLine();
                $this->info('💡 Try installing manually:');
                $this->line('   composer require doctrine/dbal');
                return 1;
            }
        } else {
            $this->error('❌ Could not start composer process');
            $this->info('💡 Please install manually: composer require doctrine/dbal');
            return 1;
        }
    }

    /**
     * Check for migrations that might need doctrine/dbal.
     */
    protected function checkMigrationsNeedingDoctrine()
    {
        $this->info('🔍 Checking migrations that might need doctrine/dbal...');
        
        $migrationPath = database_path('migrations');
        $files = File::glob($migrationPath . '/*.php');
        
        $needsDoctrine = [];
        
        foreach ($files as $file) {
            $content = File::get($file);
            
            // Look for ->change() method calls
            if (preg_match('/->change\(\)/', $content)) {
                $needsDoctrine[] = basename($file);
            }
        }
        
        if (!empty($needsDoctrine)) {
            $this->warn('⚠️  Found migrations that might need doctrine/dbal:');
            foreach ($needsDoctrine as $file) {
                $this->line("   - {$file}");
            }
        } else {
            $this->info('✅ No migrations found that require doctrine/dbal');
        }
        
        return $needsDoctrine;
    }
}
