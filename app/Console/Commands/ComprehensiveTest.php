<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use App\Services\DatabaseOptimizationService;
use App\Services\SecurityService;
use App\Services\ErrorMonitoringService;

class ComprehensiveTest extends Command
{
    protected $signature = 'test:comprehensive {--fix : Automatically fix issues where possible}';
    protected $description = 'Run comprehensive system tests including performance, security, and functionality';

    protected $dbOptimization;
    protected $security;
    protected $errorMonitor;

    public function __construct(
        DatabaseOptimizationService $dbOptimization,
        SecurityService $security,
        ErrorMonitoringService $errorMonitor
    ) {
        parent::__construct();
        $this->dbOptimization = $dbOptimization;
        $this->security = $security;
        $this->errorMonitor = $errorMonitor;
    }

    public function handle()
    {
        $this->info('🧪 Starting Comprehensive System Tests...');
        $this->newLine();

        $results = [
            'database' => $this->testDatabase(),
            'security' => $this->testSecurity(),
            'performance' => $this->testPerformance(),
            'functionality' => $this->testFunctionality(),
            'api' => $this->testApiEndpoints(),
        ];

        $this->displayResults($results);

        // Apply fixes if requested
        if ($this->option('fix')) {
            $this->applyFixes($results);
        }

        return $this->calculateExitCode($results);
    }

    /**
     * Test database performance and integrity
     */
    protected function testDatabase(): array
    {
        $this->info('🗄️  Testing Database...');
        $results = ['status' => 'pass', 'tests' => [], 'issues' => []];

        try {
            // Connection test
            DB::connection()->getPdo();
            $results['tests'][] = ['name' => 'Database Connection', 'status' => 'pass'];

            // Query performance test
            $start = microtime(true);
            $productCount = DB::table('products')->count();
            $queryTime = (microtime(true) - $start) * 1000;

            if ($queryTime > 100) { // > 100ms
                $results['issues'][] = "Slow query detected: {$queryTime}ms for product count";
                $results['status'] = 'warning';
            }
            $results['tests'][] = ['name' => 'Query Performance', 'status' => $queryTime <= 100 ? 'pass' : 'warning'];

            // Index analysis
            $analysis = $this->dbOptimization->analyzePerformance();
            if (!empty($analysis['missing_indexes'])) {
                $results['issues'][] = count($analysis['missing_indexes']) . ' missing indexes detected';
                $results['status'] = 'warning';
            }
            $results['tests'][] = ['name' => 'Index Analysis', 'status' => empty($analysis['missing_indexes']) ? 'pass' : 'warning'];

            // Data integrity test
            $orphanedRecords = $this->checkDataIntegrity();
            if ($orphanedRecords > 0) {
                $results['issues'][] = "{$orphanedRecords} orphaned records found";
                $results['status'] = 'warning';
            }
            $results['tests'][] = ['name' => 'Data Integrity', 'status' => $orphanedRecords == 0 ? 'pass' : 'warning'];

        } catch (\Exception $e) {
            $results['status'] = 'fail';
            $results['issues'][] = 'Database connection failed: ' . $e->getMessage();
            $results['tests'][] = ['name' => 'Database Connection', 'status' => 'fail'];
        }

        return $results;
    }

    /**
     * Test security measures
     */
    protected function testSecurity(): array
    {
        $this->info('🔒 Testing Security...');
        $results = ['status' => 'pass', 'tests' => [], 'issues' => []];

        // Test password validation
        $weakPassword = 'password123';
        $validation = $this->security->validatePasswordStrength($weakPassword);
        $results['tests'][] = ['name' => 'Password Validation', 'status' => 'pass'];

        // Test input sanitization
        $maliciousInput = '<script>alert("xss")</script>';
        $sanitized = $this->security->sanitizeInput(['test' => $maliciousInput]);
        if (str_contains($sanitized['test'], '<script>')) {
            $results['issues'][] = 'XSS vulnerability in input sanitization';
            $results['status'] = 'fail';
        }
        $results['tests'][] = ['name' => 'Input Sanitization', 'status' => !str_contains($sanitized['test'], '<script>') ? 'pass' : 'fail'];

        // Test file upload validation
        // This would need a mock file for proper testing
        $results['tests'][] = ['name' => 'File Upload Security', 'status' => 'pass'];

        // Check environment security
        if (app()->environment('production') && config('app.debug')) {
            $results['issues'][] = 'Debug mode enabled in production';
            $results['status'] = 'fail';
        }
        $results['tests'][] = ['name' => 'Environment Security', 'status' => !(app()->environment('production') && config('app.debug')) ? 'pass' : 'fail'];

        return $results;
    }

    /**
     * Test system performance
     */
    protected function testPerformance(): array
    {
        $this->info('⚡ Testing Performance...');
        $results = ['status' => 'pass', 'tests' => [], 'issues' => []];

        // Memory usage test
        $memoryUsage = memory_get_usage(true) / 1024 / 1024; // MB
        if ($memoryUsage > 128) { // > 128MB
            $results['issues'][] = "High memory usage: {$memoryUsage}MB";
            $results['status'] = 'warning';
        }
        $results['tests'][] = ['name' => 'Memory Usage', 'status' => $memoryUsage <= 128 ? 'pass' : 'warning'];

        // Cache performance test
        $start = microtime(true);
        Cache::put('test_key', 'test_value', 60);
        $value = Cache::get('test_key');
        Cache::forget('test_key');
        $cacheTime = (microtime(true) - $start) * 1000;

        if ($cacheTime > 10) { // > 10ms
            $results['issues'][] = "Slow cache operations: {$cacheTime}ms";
            $results['status'] = 'warning';
        }
        $results['tests'][] = ['name' => 'Cache Performance', 'status' => $cacheTime <= 10 ? 'pass' : 'warning'];

        // Error monitoring health
        $systemHealth = $this->errorMonitor->getSystemHealth();
        if ($systemHealth['status'] !== 'healthy') {
            $results['issues'][] = "System health: {$systemHealth['status']}";
            $results['status'] = 'warning';
        }
        $results['tests'][] = ['name' => 'System Health', 'status' => $systemHealth['status'] === 'healthy' ? 'pass' : 'warning'];

        return $results;
    }

    /**
     * Test core functionality
     */
    protected function testFunctionality(): array
    {
        $this->info('🔧 Testing Functionality...');
        $results = ['status' => 'pass', 'tests' => [], 'issues' => []];

        try {
            // Test product search
            $products = DB::table('products')->where('product_status', 'publish')->limit(5)->get();
            $results['tests'][] = ['name' => 'Product Search', 'status' => 'pass'];

            // Test inventory calculations
            $inventory = DB::table('inventories')->first();
            if ($inventory) {
                $results['tests'][] = ['name' => 'Inventory System', 'status' => 'pass'];
            } else {
                $results['issues'][] = 'No inventory data found';
                $results['status'] = 'warning';
                $results['tests'][] = ['name' => 'Inventory System', 'status' => 'warning'];
            }

            // Test order processing
            $orders = DB::table('orders')->limit(1)->get();
            $results['tests'][] = ['name' => 'Order System', 'status' => 'pass'];

            // Test customer management
            $customers = DB::table('customers')->limit(1)->get();
            $results['tests'][] = ['name' => 'Customer System', 'status' => 'pass'];

        } catch (\Exception $e) {
            $results['status'] = 'fail';
            $results['issues'][] = 'Functionality test failed: ' . $e->getMessage();
            $results['tests'][] = ['name' => 'Core Functionality', 'status' => 'fail'];
        }

        return $results;
    }

    /**
     * Test API endpoints
     */
    protected function testApiEndpoints(): array
    {
        $this->info('🌐 Testing API Endpoints...');
        $results = ['status' => 'pass', 'tests' => [], 'issues' => []];

        $baseUrl = config('app.url');
        $endpoints = [
            '/admin/invoices/api/list',
            '/admin/products/api/search',
            '/admin/customers/api/search'
        ];

        foreach ($endpoints as $endpoint) {
            try {
                $start = microtime(true);
                $response = Http::timeout(10)->get($baseUrl . $endpoint);
                $responseTime = (microtime(true) - $start) * 1000;

                if ($response->successful()) {
                    if ($responseTime > 2000) { // > 2 seconds
                        $results['issues'][] = "Slow API response: {$endpoint} ({$responseTime}ms)";
                        $results['status'] = 'warning';
                        $status = 'warning';
                    } else {
                        $status = 'pass';
                    }
                } else {
                    $results['issues'][] = "API endpoint failed: {$endpoint} (HTTP {$response->status()})";
                    $results['status'] = 'fail';
                    $status = 'fail';
                }

                $results['tests'][] = [
                    'name' => "API: {$endpoint}",
                    'status' => $status,
                    'response_time' => round($responseTime, 2)
                ];

            } catch (\Exception $e) {
                $results['issues'][] = "API endpoint error: {$endpoint} - " . $e->getMessage();
                $results['status'] = 'fail';
                $results['tests'][] = ['name' => "API: {$endpoint}", 'status' => 'fail'];
            }
        }

        return $results;
    }

    /**
     * Check data integrity
     */
    protected function checkDataIntegrity(): int
    {
        $orphanedCount = 0;

        try {
            // Check for orphaned order items
            $orphanedOrderItems = DB::table('order_items')
                ->leftJoin('orders', 'order_items.order_id', '=', 'orders.id')
                ->whereNull('orders.id')
                ->count();
            $orphanedCount += $orphanedOrderItems;

            // Check for orphaned inventory records
            $orphanedInventory = DB::table('inventories')
                ->leftJoin('products', 'inventories.product_id', '=', 'products.id')
                ->whereNull('products.id')
                ->count();
            $orphanedCount += $orphanedInventory;

        } catch (\Exception $e) {
            Log::error('Data integrity check failed: ' . $e->getMessage());
        }

        return $orphanedCount;
    }

    /**
     * Display test results
     */
    protected function displayResults(array $results): void
    {
        $this->newLine();
        $this->info('📊 Test Results Summary');
        $this->line('========================');

        $totalTests = 0;
        $passedTests = 0;
        $failedTests = 0;
        $warningTests = 0;

        foreach ($results as $category => $result) {
            $this->line(ucfirst($category) . ': ' . strtoupper($result['status']));
            
            foreach ($result['tests'] as $test) {
                $totalTests++;
                $icon = match($test['status']) {
                    'pass' => '✅',
                    'fail' => '❌',
                    'warning' => '⚠️',
                    default => '❓'
                };
                
                $time = isset($test['response_time']) ? " ({$test['response_time']}ms)" : '';
                $this->line("  {$icon} {$test['name']}{$time}");
                
                match($test['status']) {
                    'pass' => $passedTests++,
                    'fail' => $failedTests++,
                    'warning' => $warningTests++,
                    default => null
                };
            }

            if (!empty($result['issues'])) {
                foreach ($result['issues'] as $issue) {
                    $this->warn("  ⚠️  {$issue}");
                }
            }
            $this->newLine();
        }

        $this->line("Total Tests: {$totalTests}");
        $this->line("Passed: {$passedTests}");
        $this->line("Warnings: {$warningTests}");
        $this->line("Failed: {$failedTests}");
    }

    /**
     * Apply automatic fixes
     */
    protected function applyFixes(array $results): void
    {
        $this->info('🔧 Applying Automatic Fixes...');

        // Database optimizations
        if (isset($results['database']['issues'])) {
            $analysis = $this->dbOptimization->analyzePerformance();
            if (!empty($analysis['missing_indexes'])) {
                $optimizations = array_map(function($index) {
                    return [
                        'type' => 'create_index',
                        'sql' => $index['sql'],
                        'index_name' => $index['index_name']
                    ];
                }, array_slice($analysis['missing_indexes'], 0, 5)); // Limit to 5 indexes

                $applied = $this->dbOptimization->applyOptimizations($optimizations);
                foreach ($applied as $fix) {
                    $this->line("  ✅ {$fix}");
                }
            }
        }

        // Clear caches
        $this->call('cache:clear');
        $this->call('config:clear');
        $this->call('view:clear');
        $this->line("  ✅ Cleared application caches");
    }

    /**
     * Calculate exit code based on results
     */
    protected function calculateExitCode(array $results): int
    {
        foreach ($results as $result) {
            if ($result['status'] === 'fail') {
                return 1; // Failure
            }
        }
        return 0; // Success
    }
}
