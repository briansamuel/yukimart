<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Services\ErrorMonitoringService;

class SystemHealthCheck extends Command
{
    protected $signature = 'system:health-check {--fix : Automatically fix issues where possible}';
    protected $description = 'Check system health and optionally fix common issues';

    protected $errorMonitor;

    public function __construct(ErrorMonitoringService $errorMonitor)
    {
        parent::__construct();
        $this->errorMonitor = $errorMonitor;
    }

    public function handle()
    {
        $this->info('🏥 Starting System Health Check...');
        $this->newLine();

        $issues = [];
        $fixes = [];

        // Check database connection
        $issues = array_merge($issues, $this->checkDatabase());

        // Check cache system
        $issues = array_merge($issues, $this->checkCache());

        // Check storage permissions
        $issues = array_merge($issues, $this->checkStorage());

        // Check log files
        $issues = array_merge($issues, $this->checkLogs());

        // Check error patterns
        $issues = array_merge($issues, $this->checkErrorPatterns());

        // Check JavaScript/CSS files
        $issues = array_merge($issues, $this->checkAssets());

        // Check configuration
        $issues = array_merge($issues, $this->checkConfiguration());

        // Apply fixes if requested
        if ($this->option('fix')) {
            $fixes = $this->applyFixes($issues);
        }

        // Display summary
        $this->displaySummary($issues, $fixes);

        return empty($issues) ? 0 : 1;
    }

    protected function checkDatabase(): array
    {
        $this->info('🗄️  Checking Database...');
        $issues = [];

        try {
            DB::connection()->getPdo();
            $this->line('   ✅ Database connection: OK');

            // Check for long-running queries
            $longQueries = DB::select("SHOW PROCESSLIST");
            $slowQueries = array_filter($longQueries, function($query) {
                return isset($query->Time) && $query->Time > 30;
            });

            if (!empty($slowQueries)) {
                $issues[] = [
                    'type' => 'performance',
                    'severity' => 'warning',
                    'message' => 'Long-running queries detected',
                    'details' => count($slowQueries) . ' queries running > 30 seconds',
                    'fixable' => false
                ];
                $this->warn('   ⚠️  Long-running queries detected: ' . count($slowQueries));
            }

        } catch (\Exception $e) {
            $issues[] = [
                'type' => 'database',
                'severity' => 'critical',
                'message' => 'Database connection failed',
                'details' => $e->getMessage(),
                'fixable' => false
            ];
            $this->error('   ❌ Database connection: FAILED');
        }

        return $issues;
    }

    protected function checkCache(): array
    {
        $this->info('💾 Checking Cache System...');
        $issues = [];

        try {
            Cache::put('health_check_test', 'test_value', 60);
            $value = Cache::get('health_check_test');
            Cache::forget('health_check_test');

            if ($value === 'test_value') {
                $this->line('   ✅ Cache system: OK');
            } else {
                $issues[] = [
                    'type' => 'cache',
                    'severity' => 'warning',
                    'message' => 'Cache read/write test failed',
                    'details' => 'Cache value mismatch',
                    'fixable' => true
                ];
                $this->warn('   ⚠️  Cache system: READ/WRITE FAILED');
            }
        } catch (\Exception $e) {
            $issues[] = [
                'type' => 'cache',
                'severity' => 'critical',
                'message' => 'Cache system error',
                'details' => $e->getMessage(),
                'fixable' => true
            ];
            $this->error('   ❌ Cache system: ERROR');
        }

        return $issues;
    }

    protected function checkStorage(): array
    {
        $this->info('📁 Checking Storage Permissions...');
        $issues = [];

        $directories = [
            'storage/app',
            'storage/framework/cache',
            'storage/framework/sessions',
            'storage/framework/views',
            'storage/logs',
            'bootstrap/cache',
            'public/storage'
        ];

        foreach ($directories as $dir) {
            $path = base_path($dir);
            
            if (!is_dir($path)) {
                $issues[] = [
                    'type' => 'storage',
                    'severity' => 'warning',
                    'message' => "Directory missing: {$dir}",
                    'details' => "Path: {$path}",
                    'fixable' => true
                ];
                $this->warn("   ⚠️  Directory missing: {$dir}");
                continue;
            }

            if (!is_writable($path)) {
                $issues[] = [
                    'type' => 'storage',
                    'severity' => 'critical',
                    'message' => "Directory not writable: {$dir}",
                    'details' => "Path: {$path}",
                    'fixable' => true
                ];
                $this->error("   ❌ Not writable: {$dir}");
            } else {
                $this->line("   ✅ Writable: {$dir}");
            }
        }

        return $issues;
    }

    protected function checkLogs(): array
    {
        $this->info('📋 Checking Log Files...');
        $issues = [];

        $logPath = storage_path('logs/laravel.log');
        
        if (!file_exists($logPath)) {
            $this->line('   ✅ No log file (clean system)');
            return $issues;
        }

        $logSize = filesize($logPath);
        $maxSize = 50 * 1024 * 1024; // 50MB

        if ($logSize > $maxSize) {
            $issues[] = [
                'type' => 'logs',
                'severity' => 'warning',
                'message' => 'Log file too large',
                'details' => 'Size: ' . round($logSize / 1024 / 1024, 2) . 'MB',
                'fixable' => true
            ];
            $this->warn('   ⚠️  Log file size: ' . round($logSize / 1024 / 1024, 2) . 'MB (>50MB)');
        } else {
            $this->line('   ✅ Log file size: ' . round($logSize / 1024 / 1024, 2) . 'MB');
        }

        // Check for recent errors
        $recentErrors = shell_exec("tail -100 {$logPath} | grep -i error | wc -l");
        if ($recentErrors > 10) {
            $issues[] = [
                'type' => 'logs',
                'severity' => 'warning',
                'message' => 'High error rate in logs',
                'details' => "Recent errors: {$recentErrors}",
                'fixable' => false
            ];
            $this->warn("   ⚠️  Recent errors in logs: {$recentErrors}");
        }

        return $issues;
    }

    protected function checkErrorPatterns(): array
    {
        $this->info('🔍 Checking Error Patterns...');
        $issues = [];

        $health = $this->errorMonitor->getSystemHealth();
        
        if ($health['status'] !== 'healthy') {
            $issues[] = [
                'type' => 'errors',
                'severity' => $health['status'] === 'unhealthy' ? 'critical' : 'warning',
                'message' => 'System health degraded',
                'details' => implode(', ', $health['issues']),
                'fixable' => false
            ];
            $this->warn("   ⚠️  System health: {$health['status']} (Score: {$health['score']})");
        } else {
            $this->line('   ✅ System health: healthy');
        }

        return $issues;
    }

    protected function checkAssets(): array
    {
        $this->info('🎨 Checking Assets...');
        $issues = [];

        $criticalAssets = [
            'public/admin-assets/js/invoice-selection.js',
            'public/admin-assets/css/optimized-components.css',
            'public/admin/css/quick-order-tabs.css'
        ];

        foreach ($criticalAssets as $asset) {
            if (!file_exists(base_path($asset))) {
                $issues[] = [
                    'type' => 'assets',
                    'severity' => 'warning',
                    'message' => "Missing asset file: {$asset}",
                    'details' => 'File not found',
                    'fixable' => false
                ];
                $this->warn("   ⚠️  Missing: {$asset}");
            } else {
                $this->line("   ✅ Found: " . basename($asset));
            }
        }

        return $issues;
    }

    protected function checkConfiguration(): array
    {
        $this->info('⚙️  Checking Configuration...');
        $issues = [];

        // Check environment
        if (app()->environment('production') && config('app.debug')) {
            $issues[] = [
                'type' => 'config',
                'severity' => 'critical',
                'message' => 'Debug mode enabled in production',
                'details' => 'APP_DEBUG=true in production environment',
                'fixable' => false
            ];
            $this->error('   ❌ Debug mode enabled in production');
        }

        // Check key
        if (empty(config('app.key'))) {
            $issues[] = [
                'type' => 'config',
                'severity' => 'critical',
                'message' => 'Application key not set',
                'details' => 'Run php artisan key:generate',
                'fixable' => true
            ];
            $this->error('   ❌ Application key not set');
        } else {
            $this->line('   ✅ Application key: set');
        }

        return $issues;
    }

    protected function applyFixes(array $issues): array
    {
        $this->info('🔧 Applying Fixes...');
        $fixes = [];

        foreach ($issues as $issue) {
            if (!$issue['fixable']) {
                continue;
            }

            switch ($issue['type']) {
                case 'cache':
                    $this->call('cache:clear');
                    $fixes[] = 'Cleared cache';
                    break;

                case 'storage':
                    if (str_contains($issue['message'], 'Directory missing')) {
                        $dir = str_replace('Directory missing: ', '', $issue['message']);
                        $path = base_path($dir);
                        if (!is_dir($path)) {
                            mkdir($path, 0755, true);
                            $fixes[] = "Created directory: {$dir}";
                        }
                    }
                    break;

                case 'logs':
                    if (str_contains($issue['message'], 'Log file too large')) {
                        $logPath = storage_path('logs/laravel.log');
                        $backupPath = storage_path('logs/laravel-' . date('Y-m-d-H-i-s') . '.log');
                        rename($logPath, $backupPath);
                        touch($logPath);
                        chmod($logPath, 0644);
                        $fixes[] = 'Rotated large log file';
                    }
                    break;

                case 'config':
                    if (str_contains($issue['message'], 'Application key not set')) {
                        $this->call('key:generate');
                        $fixes[] = 'Generated application key';
                    }
                    break;
            }
        }

        return $fixes;
    }

    protected function displaySummary(array $issues, array $fixes): void
    {
        $this->newLine();
        $this->info('📊 Health Check Summary');
        $this->line('========================');

        $critical = array_filter($issues, fn($i) => $i['severity'] === 'critical');
        $warnings = array_filter($issues, fn($i) => $i['severity'] === 'warning');

        $this->line("Critical Issues: " . count($critical));
        $this->line("Warnings: " . count($warnings));
        $this->line("Total Issues: " . count($issues));

        if (!empty($fixes)) {
            $this->newLine();
            $this->info('🔧 Applied Fixes:');
            foreach ($fixes as $fix) {
                $this->line("   ✅ {$fix}");
            }
        }

        if (empty($issues)) {
            $this->newLine();
            $this->info('🎉 System is healthy!');
        } else {
            $this->newLine();
            $this->warn('⚠️  Issues found. Review the details above.');
        }
    }
}
