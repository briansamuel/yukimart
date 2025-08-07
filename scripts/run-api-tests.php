<?php

/**
 * YukiMart API Test Runner
 * 
 * This script runs comprehensive API tests and captures real response data
 * for updating Postman collection with actual examples.
 */

require_once __DIR__ . '/../vendor/autoload.php';

class ApiTestRunner
{
    protected string $baseDir;
    protected array $testResults = [];
    protected array $apiResponses = [];

    public function __construct()
    {
        $this->baseDir = dirname(__DIR__);
    }

    /**
     * Run all API tests and capture responses
     */
    public function runTests(): void
    {
        $this->printHeader();
        
        // Prepare test environment
        $this->prepareTestEnvironment();
        
        // Run test suites
        $this->runTestSuite('Authentication', 'tests/Feature/Api/V1/AuthControllerTest.php');
        $this->runTestSuite('Products', 'tests/Feature/Api/V1/ProductControllerTest.php');
        $this->runTestSuite('Orders', 'tests/Feature/Api/V1/OrderControllerTest.php');
        $this->runTestSuite('Customers', 'tests/Feature/Api/V1/CustomerControllerTest.php');
        $this->runTestSuite('Payments', 'tests/Feature/Api/V1/PaymentControllerTest.php');
        $this->runTestSuite('Invoices', 'tests/Feature/Api/V1/InvoiceControllerTest.php');
        $this->runTestSuite('Playground', 'tests/Feature/Api/V1/PlaygroundControllerTest.php');
        
        // Generate reports
        $this->generateTestReport();
        $this->updatePostmanCollection();
        $this->generateDocumentation();
        
        $this->printSummary();
    }

    /**
     * Prepare test environment
     */
    protected function prepareTestEnvironment(): void
    {
        echo "🔧 Preparing test environment...\n";
        
        // Clear previous test data
        $this->runCommand('php artisan config:clear');
        $this->runCommand('php artisan cache:clear');
        
        // Migrate test database
        $this->runCommand('php artisan migrate:fresh --seed --env=testing');
        
        // Create storage directories
        $this->ensureDirectory('storage/testing');
        $this->ensureDirectory('storage/testing/reports');
        $this->ensureDirectory('storage/testing/postman');
        
        echo "✅ Test environment prepared\n\n";
    }

    /**
     * Run specific test suite
     */
    protected function runTestSuite(string $suiteName, string $testFile): void
    {
        echo "🧪 Running {$suiteName} Tests...\n";
        
        $startTime = microtime(true);
        
        // Run PHPUnit tests
        $command = "php artisan test {$testFile} --testdox";
        $output = $this->runCommand($command);
        
        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);
        
        // Parse test results
        $this->parseTestResults($suiteName, $output, $duration);
        
        echo "✅ {$suiteName} tests completed in {$duration}s\n\n";
    }

    /**
     * Parse test results from PHPUnit output
     */
    protected function parseTestResults(string $suiteName, string $output, float $duration): void
    {
        $lines = explode("\n", $output);
        $tests = [];
        $passed = 0;
        $failed = 0;
        
        foreach ($lines as $line) {
            if (preg_match('/✓\s+(.+)/', $line, $matches)) {
                $tests[] = ['name' => trim($matches[1]), 'status' => 'passed'];
                $passed++;
            } elseif (preg_match('/✗\s+(.+)/', $line, $matches)) {
                $tests[] = ['name' => trim($matches[1]), 'status' => 'failed'];
                $failed++;
            }
        }
        
        $this->testResults[$suiteName] = [
            'tests' => $tests,
            'passed' => $passed,
            'failed' => $failed,
            'duration' => $duration,
            'success_rate' => $passed > 0 ? round(($passed / ($passed + $failed)) * 100, 2) : 0,
        ];
    }

    /**
     * Generate comprehensive test report
     */
    protected function generateTestReport(): void
    {
        echo "📊 Generating test report...\n";
        
        $totalTests = 0;
        $totalPassed = 0;
        $totalFailed = 0;
        $totalDuration = 0;
        
        $report = "# YukiMart API Test Report\n\n";
        $report .= "**Generated:** " . date('Y-m-d H:i:s') . "\n\n";
        
        $report .= "## 📊 Test Summary\n\n";
        
        foreach ($this->testResults as $suiteName => $results) {
            $totalTests += count($results['tests']);
            $totalPassed += $results['passed'];
            $totalFailed += $results['failed'];
            $totalDuration += $results['duration'];
            
            $report .= "### {$suiteName}\n";
            $report .= "- **Tests**: " . count($results['tests']) . "\n";
            $report .= "- **Passed**: {$results['passed']} ✅\n";
            $report .= "- **Failed**: {$results['failed']} ❌\n";
            $report .= "- **Success Rate**: {$results['success_rate']}%\n";
            $report .= "- **Duration**: {$results['duration']}s\n\n";
        }
        
        $overallSuccessRate = $totalTests > 0 ? round(($totalPassed / $totalTests) * 100, 2) : 0;
        
        $report .= "## 🏆 Overall Results\n\n";
        $report .= "- **Total Tests**: {$totalTests}\n";
        $report .= "- **Total Passed**: {$totalPassed} ✅\n";
        $report .= "- **Total Failed**: {$totalFailed} ❌\n";
        $report .= "- **Overall Success Rate**: {$overallSuccessRate}%\n";
        $report .= "- **Total Duration**: " . round($totalDuration, 2) . "s\n\n";
        
        // Add detailed test results
        $report .= "## 📋 Detailed Results\n\n";
        
        foreach ($this->testResults as $suiteName => $results) {
            $report .= "### {$suiteName} Tests\n\n";
            
            foreach ($results['tests'] as $test) {
                $icon = $test['status'] === 'passed' ? '✅' : '❌';
                $report .= "- {$icon} {$test['name']}\n";
            }
            
            $report .= "\n";
        }
        
        // Save report
        file_put_contents('storage/testing/reports/test-report.md', $report);
        
        echo "✅ Test report generated: storage/testing/reports/test-report.md\n";
    }

    /**
     * Update Postman collection with real response data
     */
    protected function updatePostmanCollection(): void
    {
        echo "📮 Updating Postman collection...\n";
        
        // Load captured API responses
        $responsesFile = 'storage/testing/api_responses.json';
        if (!file_exists($responsesFile)) {
            echo "⚠️ No API responses found to update Postman collection\n";
            return;
        }
        
        $responses = json_decode(file_get_contents($responsesFile), true);
        
        // Generate enhanced Postman collection
        $collection = $this->generateEnhancedPostmanCollection($responses);
        
        // Save collection
        $collectionFile = 'storage/testing/postman/yukimart-api-collection.json';
        file_put_contents($collectionFile, json_encode($collection, JSON_PRETTY_PRINT));
        
        // Sync to Postman if configured
        if (config('api.documentation.postman_sync')) {
            $this->runCommand('php artisan api:sync-postman');
        }
        
        echo "✅ Postman collection updated: {$collectionFile}\n";
    }

    /**
     * Generate enhanced Postman collection with real response examples
     */
    protected function generateEnhancedPostmanCollection(array $responses): array
    {
        $collection = [
            'info' => [
                'name' => 'YukiMart API v1 - Tested Collection',
                'description' => 'Auto-generated collection with real response examples from test suite',
                'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
                'version' => [
                    'major' => 1,
                    'minor' => 0,
                    'patch' => 0,
                ],
            ],
            'variable' => [
                [
                    'key' => 'base_url',
                    'value' => config('app.url') . '/api/v1',
                    'type' => 'string',
                ],
                [
                    'key' => 'api_token',
                    'value' => '',
                    'type' => 'string',
                ],
            ],
            'item' => [],
        ];

        // Group responses by module
        $modules = [
            'Authentication' => ['auth_'],
            'Products' => ['products_'],
            'Orders' => ['orders_'],
            'Customers' => ['customers_'],
            'Payments' => ['payments_'],
            'Invoices' => ['invoices_'],
            'Playground' => ['playground_'],
        ];

        foreach ($modules as $moduleName => $prefixes) {
            $moduleItems = [];
            
            foreach ($responses as $key => $responseData) {
                foreach ($prefixes as $prefix) {
                    if (str_starts_with($key, $prefix)) {
                        $moduleItems[] = $this->createPostmanRequest($key, $responseData);
                        break;
                    }
                }
            }
            
            if (!empty($moduleItems)) {
                $collection['item'][] = [
                    'name' => $moduleName,
                    'item' => $moduleItems,
                ];
            }
        }

        return $collection;
    }

    /**
     * Create Postman request from test response
     */
    protected function createPostmanRequest(string $key, array $responseData): array
    {
        // Extract endpoint info from key
        $parts = explode('_', $key);
        $action = end($parts);
        
        $method = 'GET';
        $endpoint = '';
        
        // Map test keys to actual endpoints
        $endpointMap = [
            'auth_login_success' => ['POST', '/auth/login'],
            'auth_register_success' => ['POST', '/auth/register'],
            'auth_me_success' => ['GET', '/auth/me'],
            'products_list_success' => ['GET', '/products'],
            'products_create_success' => ['POST', '/products'],
            'orders_list_success' => ['GET', '/orders'],
            'orders_create_success' => ['POST', '/orders'],
            // Add more mappings as needed
        ];
        
        if (isset($endpointMap[$key])) {
            [$method, $endpoint] = $endpointMap[$key];
        }
        
        return [
            'name' => ucwords(str_replace('_', ' ', $key)),
            'request' => [
                'method' => $method,
                'header' => [
                    [
                        'key' => 'Accept',
                        'value' => 'application/json',
                    ],
                    [
                        'key' => 'Content-Type',
                        'value' => 'application/json',
                    ],
                ],
                'url' => [
                    'raw' => '{{base_url}}' . $endpoint,
                    'host' => ['{{base_url}}'],
                    'path' => explode('/', trim($endpoint, '/')),
                ],
            ],
            'response' => [
                [
                    'name' => 'Success Response',
                    'originalRequest' => [
                        'method' => $method,
                        'url' => '{{base_url}}' . $endpoint,
                    ],
                    'status' => 'OK',
                    'code' => 200,
                    'header' => [],
                    'body' => json_encode($responseData['response'], JSON_PRETTY_PRINT),
                ],
            ],
        ];
    }

    /**
     * Generate API documentation from test results
     */
    protected function generateDocumentation(): void
    {
        echo "📖 Generating API documentation...\n";
        
        $doc = "# YukiMart API v1 - Test Documentation\n\n";
        $doc .= "This documentation is auto-generated from comprehensive API tests.\n\n";
        
        $doc .= "## 🎯 Test Coverage\n\n";
        
        foreach ($this->testResults as $suiteName => $results) {
            $doc .= "### {$suiteName}\n";
            $doc .= "- **Coverage**: {$results['success_rate']}%\n";
            $doc .= "- **Tests**: " . count($results['tests']) . "\n";
            $doc .= "- **Status**: " . ($results['failed'] === 0 ? '✅ All Passing' : "❌ {$results['failed']} Failed") . "\n\n";
        }
        
        file_put_contents('storage/testing/reports/api-documentation.md', $doc);
        
        echo "✅ API documentation generated\n";
    }

    /**
     * Run shell command and return output
     */
    protected function runCommand(string $command): string
    {
        $output = shell_exec("cd {$this->baseDir} && {$command} 2>&1");
        return $output ?? '';
    }

    /**
     * Ensure directory exists
     */
    protected function ensureDirectory(string $path): void
    {
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }

    /**
     * Print header
     */
    protected function printHeader(): void
    {
        echo "\n";
        echo "🚀 YukiMart API Test Runner\n";
        echo "===========================\n\n";
    }

    /**
     * Print summary
     */
    protected function printSummary(): void
    {
        echo "\n";
        echo "🎉 Test Suite Completed!\n";
        echo "========================\n\n";
        
        $totalTests = array_sum(array_column($this->testResults, 'passed')) + 
                     array_sum(array_column($this->testResults, 'failed'));
        $totalPassed = array_sum(array_column($this->testResults, 'passed'));
        $successRate = $totalTests > 0 ? round(($totalPassed / $totalTests) * 100, 2) : 0;
        
        echo "📊 Results:\n";
        echo "- Total Tests: {$totalTests}\n";
        echo "- Passed: {$totalPassed}\n";
        echo "- Success Rate: {$successRate}%\n\n";
        
        echo "📁 Generated Files:\n";
        echo "- Test Report: storage/testing/reports/test-report.md\n";
        echo "- API Documentation: storage/testing/reports/api-documentation.md\n";
        echo "- Postman Collection: storage/testing/postman/yukimart-api-collection.json\n";
        echo "- API Responses: storage/testing/api_responses.json\n\n";
        
        if ($successRate >= 95) {
            echo "🏆 Excellent! API is production ready!\n";
        } elseif ($successRate >= 80) {
            echo "✅ Good! Minor issues to address.\n";
        } else {
            echo "⚠️ Needs attention before production.\n";
        }
        
        echo "\n";
    }
}

// Run the test suite
if (php_sapi_name() === 'cli') {
    $runner = new ApiTestRunner();
    $runner->runTests();
}
