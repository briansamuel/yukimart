<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Services\PostmanCollectionService;

class AddExamplesToPostmanCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'api:add-examples-to-postman 
                            {--test-endpoints : Test endpoints and capture real responses}
                            {--sync : Sync to Postman after adding examples}
                            {--save-collection : Save enhanced collection to file}';

    /**
     * The console command description.
     */
    protected $description = 'Add real API response examples to Postman collection';

    private $baseUrl;
    private $authToken;
    private $postmanService;

    public function __construct(PostmanCollectionService $postmanService)
    {
        parent::__construct();
        $this->baseUrl = config('app.url') . '/api/v1';
        $this->postmanService = $postmanService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('📮 Adding Real Examples to Postman Collection');
        $this->info('==============================================');

        try {
            // 1. Authenticate
            $this->authenticate();
            
            // 2. Generate base collection
            $collection = $this->postmanService->generateCollection();
            $this->info("📦 Base collection generated with " . count($collection['item']) . " folders");
            
            // 3. Test endpoints and capture responses
            if ($this->option('test-endpoints')) {
                $examples = $this->captureRealResponses();
                $collection = $this->enhanceCollectionWithExamples($collection, $examples);
            }
            
            // 4. Save enhanced collection
            if ($this->option('save-collection')) {
                $this->saveEnhancedCollection($collection);
            }
            
            // 5. Sync to Postman
            if ($this->option('sync')) {
                $this->syncToPostman($collection);
            }
            
            $this->info('✅ Examples added to Postman collection successfully!');
            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Failed to add examples: ' . $e->getMessage());
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
            'email' => config('postman.test_credentials.email', 'yukimart@gmail.com'),
            'password' => config('postman.test_credentials.password', '123456')
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
     * Capture real responses from API endpoints
     */
    private function captureRealResponses(): array
    {
        $this->info('🔍 Capturing real API responses...');
        
        $examples = [];
        
        // Define key endpoints to test
        $endpoints = [
            // Health
            ['GET', '/health', false, 'Health Check'],
            
            // Auth
            ['POST', '/auth/login', false, 'Login Success', ['email' => 'yukimart@gmail.com', 'password' => '123456']],
            ['GET', '/auth/profile', true, 'Get Profile'],
            ['POST', '/auth/logout', true, 'Logout'],
            
            // Dashboard
            ['GET', '/dashboard', true, 'Dashboard Overview'],
            ['GET', '/dashboard/stats', true, 'Dashboard Stats'],
            ['GET', '/dashboard/stats?period=today', true, 'Dashboard Stats Today'],
            ['GET', '/dashboard/stats?period=month', true, 'Dashboard Stats Month'],
            ['GET', '/dashboard/recent-orders', true, 'Recent Orders'],
            ['GET', '/dashboard/recent-orders?limit=5', true, 'Recent Orders Limited'],
            ['GET', '/dashboard/top-products?type=revenue&period=month&limit=10', true, 'Top Products by Revenue'],
            ['GET', '/dashboard/top-products?type=quantity&period=month&limit=10', true, 'Top Products by Quantity'],
            
            // Invoices
            ['GET', '/invoices', true, 'List Invoices'],
            ['GET', '/invoices?page=1&per_page=10', true, 'List Invoices Paginated'],
            ['GET', '/invoices/statistics', true, 'Invoice Statistics'],
            
            // Products
            ['GET', '/products?page=1&per_page=5', true, 'List Products'],
            ['GET', '/products/search-barcode?barcode=4987415993461', true, 'Search by Barcode'],
            
            // Customers
            ['GET', '/customers', true, 'List Customers'],
            ['GET', '/customers?page=1&per_page=5', true, 'List Customers Paginated'],
            ['GET', '/customers/statistics', true, 'Customer Statistics'],
            
            // Orders
            ['GET', '/orders', true, 'List Orders'],
            ['GET', '/orders?page=1&per_page=5', true, 'List Orders Paginated'],
            
            // Payments
            ['GET', '/payments', true, 'List Payments'],
            ['GET', '/payments?page=1&per_page=5', true, 'List Payments Paginated'],
            ['GET', '/payments/statistics', true, 'Payment Statistics'],
        ];
        
        $successful = 0;
        $failed = 0;
        
        foreach ($endpoints as $endpoint) {
            [$method, $url, $requireAuth, $name, $body] = array_pad($endpoint, 5, null);
            
            $this->line("  Testing: {$name}");
            
            try {
                $headers = ['Accept' => 'application/json'];
                
                if ($requireAuth) {
                    $headers['Authorization'] = 'Bearer ' . $this->authToken;
                }
                
                if ($body) {
                    $headers['Content-Type'] = 'application/json';
                }

                $response = Http::withHeaders($headers)->send($method, $this->baseUrl . $url, [
                    'json' => $body
                ]);
                
                $examples[$name] = [
                    'name' => $name,
                    'method' => $method,
                    'url' => $url,
                    'status_code' => $response->status(),
                    'success' => $response->successful(),
                    'headers' => $response->headers(),
                    'body' => $response->json() ?: $response->body(),
                    'request_body' => $body,
                    'tested_at' => now()->toISOString()
                ];
                
                if ($response->successful()) {
                    $this->line("    ✅ Success ({$response->status()})");
                    $successful++;
                } else {
                    $this->line("    ❌ Failed ({$response->status()})");
                    $failed++;
                }
                
            } catch (\Exception $e) {
                $this->line("    ❌ Error: " . $e->getMessage());
                $examples[$name] = [
                    'name' => $name,
                    'method' => $method,
                    'url' => $url,
                    'error' => $e->getMessage(),
                    'success' => false,
                    'tested_at' => now()->toISOString()
                ];
                $failed++;
            }
        }
        
        $this->info("📊 Captured {$successful} successful, {$failed} failed responses");
        return $examples;
    }

    /**
     * Enhance collection with real examples
     */
    private function enhanceCollectionWithExamples(array $collection, array $examples): array
    {
        $this->info('🔧 Enhancing collection with real examples...');
        
        foreach ($collection['item'] as &$folder) {
            foreach ($folder['item'] as &$request) {
                $requestName = $request['name'];
                $requestMethod = $request['request']['method'] ?? '';
                $requestUrl = $request['request']['url']['raw'] ?? '';
                
                // Find matching examples
                foreach ($examples as $example) {
                    if ($this->matchesRequest($requestMethod, $requestUrl, $example)) {
                        if (!isset($request['response'])) {
                            $request['response'] = [];
                        }
                        
                        $request['response'][] = $this->createPostmanResponse($example);
                    }
                }
            }
        }
        
        return $collection;
    }

    /**
     * Check if example matches request
     */
    private function matchesRequest(string $requestMethod, string $requestUrl, array $example): bool
    {
        $exampleMethod = $example['method'] ?? '';
        $exampleUrl = $example['url'] ?? '';
        
        // Clean URLs for comparison
        $cleanRequestUrl = str_replace('{{base_url}}/', '', $requestUrl);
        $cleanExampleUrl = ltrim($exampleUrl, '/');
        
        return $requestMethod === $exampleMethod && 
               (str_contains($cleanRequestUrl, $cleanExampleUrl) || str_contains($cleanExampleUrl, $cleanRequestUrl));
    }

    /**
     * Create Postman response from example
     */
    private function createPostmanResponse(array $example): array
    {
        return [
            'name' => $example['name'],
            'originalRequest' => [
                'method' => $example['method'],
                'header' => [
                    [
                        'key' => 'Accept',
                        'value' => 'application/json'
                    ],
                    [
                        'key' => 'Authorization',
                        'value' => 'Bearer {{auth_token}}'
                    ]
                ],
                'url' => [
                    'raw' => '{{base_url}}' . $example['url'],
                    'host' => ['{{base_url}}'],
                    'path' => explode('/', trim($example['url'], '/'))
                ]
            ],
            'status' => $this->getStatusText($example['status_code'] ?? 200),
            'code' => $example['status_code'] ?? 200,
            '_postman_previewlanguage' => 'json',
            'header' => [
                [
                    'key' => 'Content-Type',
                    'value' => 'application/json'
                ]
            ],
            'cookie' => [],
            'body' => json_encode($example['body'], JSON_PRETTY_PRINT)
        ];
    }

    /**
     * Save enhanced collection to file
     */
    private function saveEnhancedCollection(array $collection)
    {
        $this->info('💾 Saving enhanced collection...');
        
        $path = 'testing/postman/yukimart-api-with-examples.json';
        Storage::put($path, json_encode($collection, JSON_PRETTY_PRINT));
        
        $fullPath = storage_path("app/{$path}");
        $this->info("✅ Enhanced collection saved to: {$fullPath}");
    }

    /**
     * Sync to Postman
     */
    private function syncToPostman(array $collection)
    {
        $this->info('📮 Syncing enhanced collection to Postman...');
        
        try {
            $result = $this->postmanService->syncToPostman($collection);
            $this->info('✅ Collection synced to Postman successfully!');
        } catch (\Exception $e) {
            $this->error('❌ Postman sync failed: ' . $e->getMessage());
        }
    }

    /**
     * Get status text from code
     */
    private function getStatusText(int $code): string
    {
        $statusTexts = [
            200 => 'OK',
            201 => 'Created',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            404 => 'Not Found',
            422 => 'Unprocessable Entity',
            500 => 'Internal Server Error'
        ];

        return $statusTexts[$code] ?? 'Unknown';
    }
}
