<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class QuickExampleTestCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'api:quick-example-test';

    /**
     * The console command description.
     */
    protected $description = 'Quick test for API example generation';

    private $baseUrl;
    private $authToken;

    public function __construct()
    {
        parent::__construct();
        $this->baseUrl = config('app.url') . '/api/v1';
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Quick API Example Test');
        $this->info('========================');

        try {
            // 1. Authenticate
            $this->authenticate();
            
            // 2. Test a few key endpoints
            $examples = $this->generateQuickExamples();
            
            // 3. Display results
            $this->displayResults($examples);
            
            // 4. Save to file
            $this->saveExamples($examples);
            
            $this->info('✅ Quick test completed successfully!');
            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Test failed: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Authenticate and get token
     */
    private function authenticate()
    {
        $this->info('🔐 Authenticating...');
        
        $credentials = [
            'email' => 'yukimart@gmail.com',
            'password' => '123456'
        ];

        $response = Http::post($this->baseUrl . '/auth/login', $credentials);
        
        if ($response->successful()) {
            $data = $response->json();
            $this->authToken = $data['data']['access_token'] ?? null;
            
            if (!$this->authToken) {
                throw new \Exception('No access token received');
            }
            
            $this->info('✅ Authentication successful');
        } else {
            throw new \Exception('Authentication failed: ' . $response->body());
        }
    }

    /**
     * Generate quick examples for key endpoints
     */
    private function generateQuickExamples(): array
    {
        $this->info('📊 Generating examples...');
        
        $examples = [];
        
        // Health Check
        $examples['health'] = $this->testEndpoint('GET', '/health');
        
        // Dashboard Stats
        $examples['dashboard_stats'] = $this->testEndpoint('GET', '/dashboard/stats', true);
        
        // Dashboard Recent Orders
        $examples['dashboard_recent_orders'] = $this->testEndpoint('GET', '/dashboard/recent-orders', true);
        
        // Products List
        $examples['products_list'] = $this->testEndpoint('GET', '/products', true);
        
        // Customers List
        $examples['customers_list'] = $this->testEndpoint('GET', '/customers', true);
        
        return $examples;
    }

    /**
     * Test individual endpoint
     */
    private function testEndpoint(string $method, string $endpoint, bool $requireAuth = false): array
    {
        $this->line("  Testing {$method} {$endpoint}");
        
        try {
            $headers = ['Accept' => 'application/json'];
            
            if ($requireAuth) {
                $headers['Authorization'] = 'Bearer ' . $this->authToken;
            }

            $response = Http::withHeaders($headers)->send($method, $this->baseUrl . $endpoint);
            
            $result = [
                'endpoint' => $endpoint,
                'method' => $method,
                'status_code' => $response->status(),
                'success' => $response->successful(),
                'response' => $response->json() ?: $response->body(),
                'headers' => $response->headers(),
                'tested_at' => now()->toISOString()
            ];
            
            if ($response->successful()) {
                $this->line("    ✅ Success ({$response->status()})");
            } else {
                $this->line("    ❌ Failed ({$response->status()})");
            }
            
            return $result;
            
        } catch (\Exception $e) {
            $this->line("    ❌ Error: " . $e->getMessage());
            
            return [
                'endpoint' => $endpoint,
                'method' => $method,
                'status_code' => 500,
                'success' => false,
                'error' => $e->getMessage(),
                'tested_at' => now()->toISOString()
            ];
        }
    }

    /**
     * Display test results
     */
    private function displayResults(array $examples)
    {
        $this->info('');
        $this->info('📋 Test Results:');
        $this->info('================');
        
        $successful = 0;
        $failed = 0;
        
        foreach ($examples as $name => $example) {
            $status = $example['success'] ? '✅' : '❌';
            $code = $example['status_code'];
            $endpoint = $example['endpoint'];
            
            $this->line("{$status} {$name}: {$endpoint} ({$code})");
            
            if ($example['success']) {
                $successful++;
            } else {
                $failed++;
            }
        }
        
        $this->info('');
        $this->info("📊 Summary: {$successful} successful, {$failed} failed");
    }

    /**
     * Save examples to file
     */
    private function saveExamples(array $examples)
    {
        $this->info('💾 Saving examples...');
        
        $data = [
            'generated_at' => now()->toISOString(),
            'base_url' => $this->baseUrl,
            'test_type' => 'quick_test',
            'total_endpoints' => count($examples),
            'examples' => $examples
        ];
        
        $path = 'testing/api-examples/quick-test-examples.json';
        Storage::put($path, json_encode($data, JSON_PRETTY_PRINT));
        
        $fullPath = storage_path("app/{$path}");
        $this->info("✅ Examples saved to: {$fullPath}");
    }
}
