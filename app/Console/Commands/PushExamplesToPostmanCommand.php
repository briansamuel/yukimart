<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class PushExamplesToPostmanCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'postman:push-examples 
                            {--generate : Generate fresh examples before pushing}
                            {--test : Test endpoints to capture real responses}';

    /**
     * The console command description.
     */
    protected $description = 'Push real API examples to existing Postman collection';

    private $baseUrl;
    private $authToken;
    private $postmanApiKey;
    private $collectionId;

    public function __construct()
    {
        parent::__construct();
        $this->baseUrl = config('app.url') . '/api/v1';
        $this->postmanApiKey = config('postman.api_key');
        $this->collectionId = config('postman.collection_id');
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('📮 Pushing Examples to Postman Collection');
        $this->info('==========================================');

        if (!$this->postmanApiKey || !$this->collectionId) {
            $this->error('❌ Postman API key or Collection ID not configured');
            return 1;
        }

        try {
            // 1. Get current collection
            $collection = $this->getCurrentCollection();
            $this->info("📦 Retrieved collection: " . $collection['info']['name']);

            // 2. Generate/test examples if requested
            $examples = [];
            if ($this->option('generate') || $this->option('test')) {
                $examples = $this->generateExamples();
            } else {
                $examples = $this->loadExistingExamples();
            }

            if (empty($examples)) {
                $this->error('❌ No examples found to push');
                return 1;
            }

            // 3. Set collection authentication
            $this->setCollectionAuth($collection);

            // 4. Add examples to collection
            $enhancedCollection = $this->addExamplesToCollection($collection, $examples);

            // 5. Update collection in Postman
            $this->updatePostmanCollection($enhancedCollection);

            $this->info('✅ Examples pushed to Postman successfully!');
            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Failed to push examples: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Get current Postman collection
     */
    private function getCurrentCollection(): array
    {
        $this->info('📥 Retrieving current Postman collection...');

        $response = Http::withHeaders([
            'X-API-Key' => $this->postmanApiKey
        ])->get("https://api.getpostman.com/collections/{$this->collectionId}");

        if (!$response->successful()) {
            throw new \Exception('Failed to retrieve collection: ' . $response->body());
        }

        return $response->json()['collection'];
    }

    /**
     * Generate fresh examples
     */
    private function generateExamples(): array
    {
        $this->info('🔍 Generating fresh examples...');

        $examples = [];

        // First, test login to get token
        $loginExample = $this->testLogin();
        if ($loginExample) {
            $examples['Login Success'] = $loginExample;
        }

        // Test health check first
        $healthExample = $this->testEndpoint('GET', '/health', false, 'Health Check');
        if ($healthExample) {
            $examples['Health Check'] = $healthExample;
        }

        // Now test authenticated endpoints if we have token
        if ($this->authToken) {
            $authenticatedEndpoints = [
                ['GET', '/auth/profile', 'Get Profile'],
                ['GET', '/dashboard/stats', 'Dashboard Stats'],
                ['GET', '/dashboard/stats?period=today', 'Dashboard Stats Today'],
                ['GET', '/dashboard/stats?period=month', 'Dashboard Stats Month'],
                ['GET', '/dashboard/recent-orders', 'Recent Orders'],
                ['GET', '/dashboard/top-products?type=revenue&period=month&limit=10', 'Top Products Revenue'],
                ['GET', '/products?page=1&per_page=5', 'List Products'],
                ['GET', '/products/search-barcode?barcode=4987415993461', 'Search Barcode'],
                ['GET', '/customers?page=1&per_page=5', 'List Customers'],
                ['GET', '/invoices?page=1&per_page=5', 'List Invoices'],
                ['GET', '/orders?page=1&per_page=5', 'List Orders'],
                ['GET', '/payments?page=1&per_page=5', 'List Payments'],
                ['GET', '/notifications?page=1&per_page=5', 'List Notifications'],
                ['GET', '/notifications/statistics', 'Notification Statistics'],
            ];

            foreach ($authenticatedEndpoints as $endpoint) {
                [$method, $url, $name] = $endpoint;
                $example = $this->testEndpoint($method, $url, true, $name);
                if ($example) {
                    $examples[$name] = $example;
                }
            }
        }

        $this->info("📊 Generated " . count($examples) . " examples");

        // Save examples
        $this->saveExamples($examples);

        return $examples;
    }

    /**
     * Test login endpoint and get token
     */
    private function testLogin(): ?array
    {
        $this->line("  Testing: Login");

        $credentials = [
            'email' => 'yukimart@gmail.com',
            'password' => '123456'
        ];

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ])->post($this->baseUrl . '/auth/login', $credentials);

            if ($response->successful()) {
                $data = $response->json();
                $this->authToken = $data['data']['access_token'] ?? null;

                $this->line("    ✅ Success - Token obtained");

                return [
                    'name' => 'Login Success',
                    'method' => 'POST',
                    'url' => '/auth/login',
                    'status_code' => $response->status(),
                    'response_body' => $data,
                    'request_body' => $credentials
                ];
            } else {
                $this->line("    ❌ Failed ({$response->status()})");
                return null;
            }
        } catch (\Exception $e) {
            $this->line("    ❌ Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Test individual endpoint
     */
    private function testEndpoint(string $method, string $url, bool $requireAuth, string $name): ?array
    {
        $this->line("  Testing: {$name}");

        try {
            $headers = ['Accept' => 'application/json'];

            if ($requireAuth && $this->authToken) {
                $headers['Authorization'] = 'Bearer ' . $this->authToken;
            }

            $response = Http::withHeaders($headers)->send($method, $this->baseUrl . $url);

            if ($response->successful()) {
                $this->line("    ✅ Success");

                return [
                    'name' => $name,
                    'method' => $method,
                    'url' => $url,
                    'status_code' => $response->status(),
                    'response_body' => $response->json(),
                    'request_body' => null
                ];
            } else {
                $this->line("    ❌ Failed ({$response->status()})");
                return null;
            }
        } catch (\Exception $e) {
            $this->line("    ❌ Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Load existing examples
     */
    private function loadExistingExamples(): array
    {
        $this->info('📂 Loading existing examples...');

        $path = 'testing/api-examples/quick-test-examples.json';
        
        if (!Storage::exists($path)) {
            $this->warn('⚠️ No existing examples found. Run with --generate to create new ones.');
            return [];
        }

        $data = json_decode(Storage::get($path), true);
        return $data['examples'] ?? [];
    }

    /**
     * Set auth configuration for collection
     */
    private function setCollectionAuth(array &$collection): void
    {
        $this->info('🔐 Setting collection authentication...');

        // Set collection-level auth
        $collection['auth'] = [
            'type' => 'bearer',
            'bearer' => [
                [
                    'key' => 'token',
                    'value' => '{{access_token}}',
                    'type' => 'string'
                ]
            ]
        ];

        // Set variables
        if (!isset($collection['variable'])) {
            $collection['variable'] = [];
        }

        // Add/update variables
        $variables = [
            [
                'key' => 'base_url',
                'value' => config('app.url') . '/api/v1',
                'type' => 'string'
            ],
            [
                'key' => 'access_token',
                'value' => $this->authToken ?? 'your_access_token_here',
                'type' => 'string'
            ]
        ];

        foreach ($variables as $newVar) {
            $found = false;
            foreach ($collection['variable'] as &$existingVar) {
                if ($existingVar['key'] === $newVar['key']) {
                    $existingVar['value'] = $newVar['value'];
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $collection['variable'][] = $newVar;
            }
        }

        // Set auth for protected endpoints
        foreach ($collection['item'] as &$folder) {
            foreach ($folder['item'] as &$request) {
                $url = $request['request']['url']['raw'] ?? '';

                // Skip auth for login and health endpoints
                if (str_contains($url, '/auth/login') || str_contains($url, '/health')) {
                    $request['request']['auth'] = [
                        'type' => 'noauth'
                    ];
                } else {
                    // Use collection auth (Bearer token)
                    $request['request']['auth'] = [
                        'type' => 'bearer',
                        'bearer' => [
                            [
                                'key' => 'token',
                                'value' => '{{access_token}}',
                                'type' => 'string'
                            ]
                        ]
                    ];
                }
            }
        }
    }

    /**
     * Add examples to collection
     */
    private function addExamplesToCollection(array $collection, array $examples): array
    {
        $this->info('🔧 Adding examples to collection...');

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
                        $this->line("  ✅ Added example to: {$requestName}");
                        break;
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
        $cleanRequestUrl = str_replace(['{{base_url}}/', '{{base_url}}'], '', $requestUrl);
        $cleanExampleUrl = ltrim($exampleUrl, '/');
        
        return $requestMethod === $exampleMethod && 
               (str_contains($cleanRequestUrl, $cleanExampleUrl) || 
                str_contains($cleanExampleUrl, str_replace('api/v1/', '', $cleanRequestUrl)));
    }

    /**
     * Create Postman response from example
     */
    private function createPostmanResponse(array $example): array
    {
        // Determine if this endpoint requires auth
        $requiresAuth = !in_array($example['url'], ['/health', '/auth/login']);

        $headers = [
            [
                'key' => 'Accept',
                'value' => 'application/json'
            ]
        ];

        // Add auth header for protected endpoints
        if ($requiresAuth) {
            $headers[] = [
                'key' => 'Authorization',
                'value' => 'Bearer {{access_token}}'
            ];
        }

        // Add request body for POST requests
        $requestBody = null;
        if ($example['method'] === 'POST' && isset($example['request_body'])) {
            $requestBody = [
                'mode' => 'raw',
                'raw' => json_encode($example['request_body'], JSON_PRETTY_PRINT),
                'options' => [
                    'raw' => [
                        'language' => 'json'
                    ]
                ]
            ];
        }

        $originalRequest = [
            'method' => $example['method'],
            'header' => $headers,
            'url' => [
                'raw' => '{{base_url}}' . $example['url'],
                'host' => ['{{base_url}}'],
                'path' => explode('/', trim($example['url'], '/'))
            ]
        ];

        if ($requestBody) {
            $originalRequest['body'] = $requestBody;
        }

        return [
            'name' => $example['name'],
            'originalRequest' => $originalRequest,
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
            'body' => json_encode($example['response_body'], JSON_PRETTY_PRINT)
        ];
    }

    /**
     * Update Postman collection
     */
    private function updatePostmanCollection(array $collection)
    {
        $this->info('📤 Updating Postman collection...');

        $response = Http::withHeaders([
            'X-API-Key' => $this->postmanApiKey,
            'Content-Type' => 'application/json'
        ])->put("https://api.getpostman.com/collections/{$this->collectionId}", [
            'collection' => $collection
        ]);

        if (!$response->successful()) {
            throw new \Exception('Failed to update collection: ' . $response->body());
        }

        $this->info('✅ Collection updated successfully!');
    }



    /**
     * Save examples to file
     */
    private function saveExamples(array $examples)
    {
        $data = [
            'generated_at' => now()->toISOString(),
            'total_examples' => count($examples),
            'examples' => $examples
        ];
        
        Storage::put('testing/api-examples/postman-push-examples.json', json_encode($data, JSON_PRETTY_PRINT));
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
