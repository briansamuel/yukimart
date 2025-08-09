<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

class SyncPostmanCollection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'postman:sync
                            {--type=all : Collection type (fcm, api, all)}
                            {--api-key= : Postman API key}
                            {--collection-id= : Postman collection ID}
                            {--capture-examples : Capture live API examples}
                            {--update-collection : Update existing collection}
                            {--upload : Upload collection to Postman}
                            {--export-only : Only export to local files}
                            {--sync-env : Sync environment variables from .env}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync APIs to Postman collection with real examples and FCM endpoints';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🚀 Starting Postman Collection Sync...');

        // Check options
        $type = $this->option('type');
        $captureExamples = $this->option('capture-examples');
        $updateCollection = $this->option('update-collection');
        $exportOnly = $this->option('export-only');
        $syncEnv = $this->option('sync-env');
        $upload = $this->option('upload');

        $this->info("📋 Collection type: {$type}");

        // Sync environment variables from .env
        if ($syncEnv) {
            $this->syncEnvironmentVariables();
        }

        // Handle different collection types
        switch ($type) {
            case 'fcm':
                $this->syncFCMCollection();
                break;
            case 'api':
                $this->syncDashboardAPIs();
                break;
            case 'all':
                $this->syncFCMCollection();
                $this->syncDashboardAPIs();
                break;
            default:
                $this->error('Invalid collection type. Use: fcm, api, or all');
                return 1;
        }

        if ($captureExamples) {
            $this->captureApiExamples();
        }

        if ($exportOnly) {
            $this->info('📤 Export-only mode completed');
            // $this->exportCollectionFiles(); // TODO: Implement if needed
            return Command::SUCCESS;
        }

        if ($updateCollection) {
            $this->updatePostmanCollection();
        } else {
            $this->info('📤 Collection files updated locally');
        }

        // Upload to Postman if requested
        if ($upload) {
            $this->uploadToPostman($type);
        }

        $this->info('✅ Postman sync completed successfully!');
        return Command::SUCCESS;
    }

    /**
     * Capture live API examples
     */
    protected function captureApiExamples()
    {
        $this->info('📊 Capturing live API examples...');

        // Get authentication token
        $token = $this->getAuthToken();
        if (!$token) {
            $this->error('❌ Failed to get authentication token');
            return;
        }

        $this->info("🔑 Got authentication token: " . substr($token, 0, 20) . "...");

        // Define endpoints to capture
        $endpoints = $this->getDashboardEndpoints();

        $examples = [];
        $captured = 0;

        foreach ($endpoints as $endpoint) {
            $this->line("Capturing: {$endpoint['name']}");

            try {
                $response = Http::timeout(30)
                    ->withHeaders([
                        'Accept' => 'application/json',
                        'Authorization' => "Bearer {$token}"
                    ])
                    ->get($endpoint['url']);

                if ($response->successful()) {
                    $examples[] = [
                        'name' => $endpoint['name'],
                        'url' => $endpoint['url'],
                        'method' => $endpoint['method'],
                        'description' => $endpoint['description'],
                        'response' => $response->json(),
                        'status_code' => $response->status()
                    ];

                    $this->info("  ✅ Captured successfully");
                    $captured++;
                } else {
                    $this->warn("  ⚠️  Failed with status: {$response->status()}");
                }

            } catch (\Exception $e) {
                $this->warn("  ❌ Error: " . $e->getMessage());
            }
        }

        // Save examples
        $examplesPath = storage_path('testing/dashboard-api-examples.json');
        File::ensureDirectoryExists(dirname($examplesPath));
        File::put($examplesPath, json_encode($examples, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->info("📁 Saved {$captured} examples to: {$examplesPath}");
    }

    /**
     * Get authentication token
     */
    protected function getAuthToken()
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json'
                ])
                ->post(config('app.url') . '/api/v1/auth/login', [
                    'email' => 'yukimart@gmail.com',
                    'password' => '123456',
                    'device_name' => 'Artisan Postman Sync'
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['data']['access_token'] ?? null;
            }

        } catch (\Exception $e) {
            $this->error("Login error: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Get dashboard endpoints to capture
     */
    protected function getDashboardEndpoints()
    {
        $baseUrl = config('app.url');

        return [
            [
                'name' => 'Dashboard Stats - Today',
                'url' => "{$baseUrl}/admin/dashboard/stats?period=today",
                'method' => 'GET',
                'description' => 'Get today\'s dashboard statistics'
            ],
            [
                'name' => 'Dashboard Stats - Month',
                'url' => "{$baseUrl}/admin/dashboard/stats?period=month",
                'method' => 'GET',
                'description' => 'Get current month dashboard statistics'
            ],
            [
                'name' => 'Dashboard Stats - Year',
                'url' => "{$baseUrl}/admin/dashboard/stats?period=year",
                'method' => 'GET',
                'description' => 'Get current year dashboard statistics'
            ],
            [
                'name' => 'Revenue Chart - Month',
                'url' => "{$baseUrl}/admin/dashboard/revenue-chart?period=month",
                'method' => 'GET',
                'description' => 'Get monthly revenue chart data'
            ],
            [
                'name' => 'Revenue Chart - Today',
                'url' => "{$baseUrl}/admin/dashboard/revenue-chart?period=today",
                'method' => 'GET',
                'description' => 'Get today\'s revenue chart data'
            ],
            [
                'name' => 'Top Products - Revenue',
                'url' => "{$baseUrl}/admin/dashboard/top-products?type=revenue&period=month&limit=10",
                'method' => 'GET',
                'description' => 'Get top products by revenue'
            ],
            [
                'name' => 'Top Products - Quantity',
                'url' => "{$baseUrl}/admin/dashboard/top-products?type=quantity&period=month&limit=10",
                'method' => 'GET',
                'description' => 'Get top products by quantity sold'
            ]
        ];
    }

    /**
     * Sync FCM collection with current environment and routes
     */
    protected function syncFCMCollection()
    {
        $this->info('🔥 Syncing FCM collection...');

        // Get current environment variables
        $envVars = $this->getEnvironmentVariables();

        // Get FCM routes from Laravel
        $fcmRoutes = $this->getFCMRoutes();

        // Load existing FCM collection
        $collectionPath = base_path('postman/YukiMart-FCM-API.postman_collection.json');

        if (!File::exists($collectionPath)) {
            $this->error('FCM collection not found at: ' . $collectionPath);
            return;
        }

        $collection = json_decode(File::get($collectionPath), true);

        // Update collection with current environment
        $collection = $this->updateFCMCollectionEnvironment($collection, $envVars);

        // Add FCM routes to collection
        $collection = $this->addFCMRoutesToCollection($collection, $fcmRoutes);

        // Save updated collection
        File::put($collectionPath, json_encode($collection, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $this->info('✅ FCM collection synced successfully!');
        $this->info("📁 Collection saved to: {$collectionPath}");
        $this->info("🔗 Added " . count($fcmRoutes) . " FCM routes to collection");
    }

    /**
     * Sync all API routes to main collection
     */
    protected function syncDashboardAPIs()
    {
        $this->info('📡 Syncing All API routes...');

        // Get current environment variables
        $envVars = $this->getEnvironmentVariables();

        // Get all API routes from Laravel
        $apiRoutes = $this->getAllAPIRoutes();

        // Load existing API collection
        $collectionPath = base_path('postman/YukiMart-API.postman_collection.json');

        if (!File::exists($collectionPath)) {
            $this->error('API collection not found at: ' . $collectionPath);
            return;
        }

        $collection = json_decode(File::get($collectionPath), true);

        // Update collection with current environment
        $collection = $this->updateAPICollectionEnvironment($collection, $envVars);

        // Add API routes to collection
        $collection = $this->addAPIRoutesToCollection($collection, $apiRoutes);

        // Save updated collection
        File::put($collectionPath, json_encode($collection, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $this->info('✅ API collection synced successfully!');
        $this->info("📁 Collection saved to: {$collectionPath}");
        $this->info("🔗 Added " . count($apiRoutes) . " API routes to collection");
    }

    /**
     * Get environment variables from .env and config
     */
    protected function getEnvironmentVariables()
    {
        return [
            'yukimart_base_url' => config('app.url'),
            'yukimart_email' => 'yukimart@gmail.com', // Default admin email
            'yukimart_password' => '123456', // Default admin password
            'firebase_project_id' => config('services.fcm.project_id', 'yukimart-pos-system'),
            'firebase_service_account_email' => config('services.fcm.service_account_email', ''),
            'app_name' => config('app.name'),
            'app_env' => config('app.env'),
            'database_name' => config('database.connections.mysql.database'),
            'test_fcm_token' => 'c7EZgqA-S76KNE3DSiqp3_:APA91bF1N6GHrxKxuvmZbPgmLRfD_T-KoEKnd5zM9hPM0V7_s7c5gT6pizPuQBg6SzmMKo1rV5uyBDdxLpFdrftCgWqOxGQEuo-EuqiQvKEhAgCAJN3R82g',
        ];
    }

    /**
     * Update FCM collection with current environment variables
     */
    protected function updateFCMCollectionEnvironment($collection, $envVars)
    {
        // Update collection info
        $collection['info']['_updated_at'] = now()->toISOString();
        $collection['info']['_synced_from_env'] = true;

        // Update variable values in collection
        if (isset($collection['variable'])) {
            foreach ($collection['variable'] as &$variable) {
                $key = $variable['key'];
                if (isset($envVars[$key])) {
                    $variable['value'] = $envVars[$key];
                }
            }
        }

        // Update request URLs with current base URL
        $baseUrl = $envVars['yukimart_base_url'];
        $this->updateCollectionUrls($collection['item'], $baseUrl);

        return $collection;
    }

    /**
     * Recursively update URLs in collection items
     */
    protected function updateCollectionUrls(&$items, $baseUrl)
    {
        foreach ($items as &$item) {
            if (isset($item['item'])) {
                // This is a folder, recurse into it
                $this->updateCollectionUrls($item['item'], $baseUrl);
            } elseif (isset($item['request']['url'])) {
                // This is a request, update its URL
                if (is_string($item['request']['url'])) {
                    $item['request']['url'] = str_replace('{{yukimart_base_url}}', $baseUrl, $item['request']['url']);
                } elseif (is_array($item['request']['url']) && isset($item['request']['url']['raw'])) {
                    $item['request']['url']['raw'] = str_replace('{{yukimart_base_url}}', $baseUrl, $item['request']['url']['raw']);
                }
            }
        }
    }

    /**
     * Sync environment variables from .env to Postman environment file
     */
    protected function syncEnvironmentVariables()
    {
        $this->info('🔄 Syncing environment variables...');

        $envVars = $this->getEnvironmentVariables();
        $envPath = base_path('postman/YukiMart-FCM.postman_environment.json');

        if (!File::exists($envPath)) {
            $this->warn('Environment file not found, creating new one...');
            $this->createEnvironmentFile($envPath, $envVars);
            return;
        }

        $environment = json_decode(File::get($envPath), true);

        // Update environment variables
        foreach ($envVars as $key => $value) {
            $found = false;
            foreach ($environment['values'] as &$envVar) {
                if ($envVar['key'] === $key) {
                    $envVar['value'] = $value;
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $environment['values'][] = [
                    'key' => $key,
                    'value' => $value,
                    'enabled' => true,
                    'type' => 'default',
                    'description' => "Auto-synced from .env"
                ];
            }
        }

        // Update metadata
        $environment['_postman_exported_at'] = now()->toISOString();
        $environment['_synced_from_env'] = true;

        File::put($envPath, json_encode($environment, JSON_PRETTY_PRINT));
        $this->info('✅ Environment variables synced successfully!');
        $this->info("📁 Environment saved to: {$envPath}");
    }

    /**
     * Create new environment file
     */
    protected function createEnvironmentFile($path, $envVars)
    {
        $environment = [
            'id' => 'yukimart-fcm-env-' . now()->format('Y-m-d'),
            'name' => 'YukiMart FCM Environment (Auto-synced)',
            'values' => [],
            '_postman_variable_scope' => 'environment',
            '_postman_exported_at' => now()->toISOString(),
            '_synced_from_env' => true
        ];

        foreach ($envVars as $key => $value) {
            $environment['values'][] = [
                'key' => $key,
                'value' => $value,
                'enabled' => true,
                'type' => 'default',
                'description' => "Auto-synced from .env"
            ];
        }

        File::put($path, json_encode($environment, JSON_PRETTY_PRINT));
        $this->info("✅ New environment file created: {$path}");
    }

    /**
     * Get FCM routes from Laravel application
     */
    protected function getFCMRoutes()
    {
        $routes = collect(Route::getRoutes())->filter(function ($route) {
            $uri = $route->uri();
            $name = $route->getName();

            // Filter FCM related routes
            return str_contains($uri, 'api/v1/fcm') ||
                   str_contains($name, 'fcm') ||
                   str_contains($uri, 'fcm');
        });

        return $routes->map(function ($route) {
            $methods = array_filter($route->methods(), fn($method) => $method !== 'HEAD');
            $uri = $route->uri();
            $name = $route->getName() ?: $this->generateRouteName($uri);

            return [
                'name' => $this->formatRouteName($name),
                'methods' => $methods,
                'uri' => $uri,
                'action' => $route->getActionName(),
                'middleware' => $route->middleware(),
                'parameters' => $this->extractRouteParameters($uri),
            ];
        })->values()->toArray();
    }

    /**
     * Add FCM routes to Postman collection
     */
    protected function addFCMRoutesToCollection($collection, $fcmRoutes)
    {
        if (empty($fcmRoutes)) {
            $this->info('No FCM routes found to add');
            return $collection;
        }

        // Find YukiMart FCM API folder
        $fcmFolderIndex = null;
        foreach ($collection['item'] as $index => $item) {
            if ($item['name'] === 'YukiMart FCM API') {
                $fcmFolderIndex = $index;
                break;
            }
        }

        if ($fcmFolderIndex === null) {
            $this->error('YukiMart FCM API folder not found in collection');
            return $collection;
        }

        $this->info("📁 Found YukiMart FCM API folder at index {$fcmFolderIndex}");

        // Get existing items (preserve manual ones, remove auto-generated)
        $existingItems = $collection['item'][$fcmFolderIndex]['item'] ?? [];
        $manualItems = array_filter($existingItems, fn($item) => !isset($item['_auto_generated']));

        $this->info("🔄 Preserving " . count($manualItems) . " manual items");
        $this->info("🗑️  Removing old auto-generated items");

        // Start with manual items
        $newItems = array_values($manualItems);

        // Add auto-generated routes
        foreach ($fcmRoutes as $route) {
            foreach ($route['methods'] as $method) {
                $newItems[] = $this->createFCMPostmanRequest($route, $method);
            }
        }

        // Update the folder
        $collection['item'][$fcmFolderIndex]['item'] = $newItems;
        $collection['item'][$fcmFolderIndex]['description'] = 'YukiMart internal FCM API endpoints (auto-updated from Laravel routes)';
        $collection['item'][$fcmFolderIndex]['_last_sync'] = now()->toISOString();

        $this->info("✅ Added " . count($fcmRoutes) . " FCM routes to existing folder");

        return $collection;
    }

    /**
     * Create Postman request from route
     */
    protected function createPostmanRequest($route, $method)
    {
        $url = '{{yukimart_base_url}}/' . ltrim($route['uri'], '/');
        $requestName = $route['name'] . ' (' . strtoupper($method) . ')';

        $request = [
            'name' => $requestName,
            'request' => [
                'method' => strtoupper($method),
                'header' => $this->getRequestHeaders($route, $method),
                'url' => [
                    'raw' => $url,
                    'host' => ['{{yukimart_base_url}}'],
                    'path' => array_filter(explode('/', $route['uri']))
                ],
                'description' => "Auto-generated from route: {$route['action']}"
            ],
            '_auto_generated' => true,
            '_generated_at' => now()->toISOString()
        ];

        // Add request body for POST/PUT/PATCH methods
        if (in_array(strtoupper($method), ['POST', 'PUT', 'PATCH'])) {
            $request['request']['body'] = $this->getRequestBody($route, $method);
        }

        return $request;
    }

    /**
     * Create Postman request specifically for FCM routes
     */
    protected function createFCMPostmanRequest($route, $method)
    {
        $url = '{{yukimart_base_url}}/' . ltrim($route['uri'], '/');
        $requestName = $this->formatFCMRequestName($route, $method);

        $request = [
            'name' => $requestName,
            'request' => [
                'method' => strtoupper($method),
                'header' => $this->getFCMRequestHeaders($route),
                'url' => [
                    'raw' => $url,
                    'host' => ['{{yukimart_base_url}}'],
                    'path' => array_filter(explode('/', $route['uri']))
                ],
                'description' => $this->getFCMRequestDescription($route)
            ],
            '_auto_generated' => true,
            '_generated_at' => now()->toISOString(),
            '_route_name' => $route['name'] ?? 'unknown'
        ];

        // Add request body for POST/PUT/PATCH methods
        if (in_array(strtoupper($method), ['POST', 'PUT', 'PATCH'])) {
            $request['request']['body'] = $this->getFCMRequestBody($route);
        }

        return $request;
    }

    /**
     * Format FCM request name
     */
    protected function formatFCMRequestName($route, $method)
    {
        $uri = $route['uri'];

        // Extract endpoint name from URI
        if (str_contains($uri, 'register-token')) {
            return 'Register FCM Token';
        } elseif (str_contains($uri, 'unregister-token')) {
            return 'Unregister FCM Token';
        } elseif (str_contains($uri, 'test-notification')) {
            return 'Send Test Notification';
        } elseif (str_contains($uri, 'send-notification')) {
            return 'Send Custom Notification';
        } elseif (str_contains($uri, 'test-config')) {
            return 'Test FCM Configuration';
        } elseif (str_contains($uri, 'statistics')) {
            return 'Get FCM Statistics';
        } elseif (str_contains($uri, 'tokens')) {
            return 'Get FCM Tokens';
        }

        // Fallback
        return ucwords(str_replace(['-', '_'], ' ', basename($uri)));
    }

    /**
     * Get FCM request headers
     */
    protected function getFCMRequestHeaders($route)
    {
        $headers = [
            [
                'key' => 'Accept',
                'value' => 'application/json',
                'type' => 'text'
            ],
            [
                'key' => 'Content-Type',
                'value' => 'application/json',
                'type' => 'text'
            ],
            [
                'key' => 'Authorization',
                'value' => 'Bearer {{yukimart_api_token}}',
                'type' => 'text'
            ]
        ];

        return $headers;
    }

    /**
     * Get FCM request body
     */
    protected function getFCMRequestBody($route)
    {
        $uri = $route['uri'];
        $body = ['mode' => 'raw', 'raw' => ''];

        if (str_contains($uri, 'register-token')) {
            $body['raw'] = json_encode([
                'token' => '{{test_fcm_token}}',
                'device_type' => 'android',
                'device_name' => 'Test Device',
                'app_version' => '1.0.0'
            ], JSON_PRETTY_PRINT);
        } elseif (str_contains($uri, 'unregister-token')) {
            $body['raw'] = json_encode([
                'token' => '{{test_fcm_token}}'
            ], JSON_PRETTY_PRINT);
        } elseif (str_contains($uri, 'test-notification')) {
            $body['raw'] = json_encode([
                'title' => '🧪 Test Notification',
                'message' => 'This is a test notification from Postman',
                'type' => 'test',
                'data' => [
                    'test_id' => '{{$randomUUID}}',
                    'timestamp' => '{{$timestamp}}'
                ]
            ], JSON_PRETTY_PRINT);
        } elseif (str_contains($uri, 'send-notification')) {
            $body['raw'] = json_encode([
                'title' => '📢 Custom Notification',
                'message' => 'Custom notification message',
                'type' => 'custom',
                'target_type' => 'user',
                'target_ids' => [12],
                'data' => [
                    'action' => 'open_screen',
                    'screen' => 'dashboard'
                ]
            ], JSON_PRETTY_PRINT);
        }

        return $body;
    }

    /**
     * Get FCM request description
     */
    protected function getFCMRequestDescription($route)
    {
        $uri = $route['uri'];

        if (str_contains($uri, 'register-token')) {
            return 'Register a new FCM token for the authenticated user';
        } elseif (str_contains($uri, 'unregister-token')) {
            return 'Unregister an FCM token for the authenticated user';
        } elseif (str_contains($uri, 'test-notification')) {
            return 'Send a test notification to the authenticated user';
        } elseif (str_contains($uri, 'send-notification')) {
            return 'Send a custom notification to specific users or roles';
        } elseif (str_contains($uri, 'test-config')) {
            return 'Test FCM configuration and service account connectivity';
        } elseif (str_contains($uri, 'statistics')) {
            return 'Get FCM statistics for the authenticated user';
        } elseif (str_contains($uri, 'tokens')) {
            return 'Get all FCM tokens for the authenticated user';
        }

        return "Auto-generated from route: {$route['action']}";
    }

    /**
     * Get request headers based on route
     */
    protected function getRequestHeaders($route, $method)
    {
        $headers = [
            [
                'key' => 'Accept',
                'value' => 'application/json',
                'type' => 'text'
            ],
            [
                'key' => 'Content-Type',
                'value' => 'application/json',
                'type' => 'text'
            ]
        ];

        // Add auth header if route has auth middleware
        if (in_array('auth:sanctum', $route['middleware']) || in_array('auth', $route['middleware'])) {
            $headers[] = [
                'key' => 'Authorization',
                'value' => 'Bearer {{yukimart_api_token}}',
                'type' => 'text'
            ];
        }

        return $headers;
    }

    /**
     * Get request body based on route and method
     */
    protected function getRequestBody($route, $method)
    {
        $routeName = $route['name'];
        $uri = $route['uri'];
        $body = ['mode' => 'raw', 'raw' => ''];



        // Generate sample request bodies based on route URI and name
        if (str_contains($uri, 'register-token')) {
            $body['raw'] = json_encode([
                'token' => '{{test_fcm_token}}',
                'device_type' => 'android',
                'device_name' => 'Test Device',
                'app_version' => '1.0.0'
            ], JSON_PRETTY_PRINT);
        } elseif (str_contains($uri, 'unregister-token')) {
            $body['raw'] = json_encode([
                'token' => '{{test_fcm_token}}'
            ], JSON_PRETTY_PRINT);
        } elseif (str_contains($uri, 'test-notification')) {
            $body['raw'] = json_encode([
                'title' => '🧪 Test Notification',
                'message' => 'This is a test notification from Postman',
                'type' => 'test',
                'data' => [
                    'test_id' => '{{$randomUUID}}',
                    'timestamp' => '{{$timestamp}}'
                ]
            ], JSON_PRETTY_PRINT);
        } elseif (str_contains($uri, 'send-notification')) {
            $body['raw'] = json_encode([
                'title' => '📢 Custom Notification',
                'message' => 'Custom notification message',
                'type' => 'custom',
                'target_type' => 'user', // user, role, all
                'target_ids' => [12], // user IDs or role IDs
                'data' => [
                    'action' => 'open_screen',
                    'screen' => 'dashboard'
                ]
            ], JSON_PRETTY_PRINT);
        }

        return $body;
    }

    /**
     * Format route name for display
     */
    protected function formatRouteName($name)
    {
        // Remove api.v1.fcm prefix and format
        $name = str_replace('api.v1.fcm.', '', $name);
        $name = str_replace(['-', '_'], ' ', $name);
        return ucwords($name);
    }

    /**
     * Generate route name from URI
     */
    protected function generateRouteName($uri)
    {
        $segments = explode('/', $uri);
        return end($segments);
    }

    /**
     * Extract route parameters
     */
    protected function extractRouteParameters($uri)
    {
        preg_match_all('/\{([^}]+)\}/', $uri, $matches);
        return $matches[1] ?? [];
    }

    /**
     * Get all API routes from Laravel application
     */
    protected function getAllAPIRoutes()
    {
        $routes = collect(Route::getRoutes())->filter(function ($route) {
            $uri = $route->uri();

            // Include all API routes
            return str_starts_with($uri, 'api/');
        });

        return $routes->map(function ($route) {
            $methods = array_filter($route->methods(), fn($method) => $method !== 'HEAD');
            $uri = $route->uri();
            $name = $route->getName() ?: $this->generateRouteName($uri);

            return [
                'name' => $this->formatRouteName($name),
                'methods' => $methods,
                'uri' => $uri,
                'action' => $route->getActionName(),
                'middleware' => $route->middleware(),
                'parameters' => $this->extractRouteParameters($uri),
            ];
        })->values()->toArray();
    }

    /**
     * Update API collection with current environment
     */
    protected function updateAPICollectionEnvironment($collection, $envVars)
    {
        // Update collection info
        $collection['info']['_updated_at'] = now()->toISOString();
        $collection['info']['_synced_from_env'] = true;

        // Update variable values in collection
        if (isset($collection['variable'])) {
            foreach ($collection['variable'] as &$variable) {
                $key = $variable['key'];
                if ($key === 'base_url' && isset($envVars['yukimart_base_url'])) {
                    $variable['value'] = $envVars['yukimart_base_url'];
                }
            }
        }

        return $collection;
    }

    /**
     * Add API routes to collection
     */
    protected function addAPIRoutesToCollection($collection, $apiRoutes)
    {
        if (empty($apiRoutes)) {
            $this->info('No API routes found to add');
            return $collection;
        }

        // Group routes by category
        $groupedRoutes = $this->groupAPIRoutes($apiRoutes);

        // Remove existing auto-generated folders
        $collection['item'] = array_filter(
            $collection['item'] ?? [],
            fn($item) => !isset($item['_auto_generated'])
        );

        // Add new route groups
        foreach ($groupedRoutes as $groupName => $routes) {
            if (!empty($routes)) {
                $collection['item'][] = $this->createAPIRouteGroup($groupName, $routes);
            }
        }

        return $collection;
    }

    /**
     * Group API routes by category
     */
    protected function groupAPIRoutes($routes)
    {
        $groups = [];

        foreach ($routes as $route) {
            $groupName = $this->getAPIRouteGroup($route);
            $groups[$groupName][] = $route;
        }

        return $groups;
    }

    /**
     * Get API route group name
     */
    protected function getAPIRouteGroup($route)
    {
        $uri = $route['uri'];

        // FCM routes
        if (str_contains($uri, 'fcm')) {
            return 'FCM API';
        }

        // Auth routes
        if (str_contains($uri, 'auth')) {
            return 'Authentication';
        }

        // Other API routes by first segment
        $segments = explode('/', $uri);
        if (count($segments) >= 3 && $segments[0] === 'api' && $segments[1] === 'v1') {
            return ucfirst($segments[2]) . ' API';
        } elseif (count($segments) >= 2 && $segments[0] === 'api') {
            return ucfirst($segments[1]) . ' API';
        }

        return 'General API';
    }

    /**
     * Create API route group for Postman
     */
    protected function createAPIRouteGroup($groupName, $routes)
    {
        $items = [];

        foreach ($routes as $route) {
            foreach ($route['methods'] as $method) {
                $items[] = $this->createAPIPostmanRequest($route, $method);
            }
        }

        return [
            'name' => $groupName,
            'item' => $items,
            'description' => "Auto-generated {$groupName} endpoints from Laravel routes",
            '_auto_generated' => true,
            '_generated_at' => now()->toISOString()
        ];
    }

    /**
     * Create API Postman request
     */
    protected function createAPIPostmanRequest($route, $method)
    {
        $url = '{{base_url}}/' . ltrim($route['uri'], '/');
        $requestName = $this->formatAPIRequestName($route, $method);

        $request = [
            'name' => $requestName,
            'request' => [
                'method' => strtoupper($method),
                'header' => $this->getAPIRequestHeaders($route),
                'url' => [
                    'raw' => $url,
                    'host' => ['{{base_url}}'],
                    'path' => array_filter(explode('/', $route['uri']))
                ],
                'description' => $this->getAPIRequestDescription($route)
            ],
            '_auto_generated' => true,
            '_generated_at' => now()->toISOString(),
            '_route_name' => $route['name'] ?? 'unknown'
        ];

        // Add request body for POST/PUT/PATCH methods
        if (in_array(strtoupper($method), ['POST', 'PUT', 'PATCH'])) {
            $request['request']['body'] = $this->getAPIRequestBody($route);
        }

        return $request;
    }

    /**
     * Format API request name
     */
    protected function formatAPIRequestName($route, $method)
    {
        $uri = $route['uri'];
        $name = $route['name'] ?? '';

        // Use route name if available
        if ($name) {
            $name = str_replace(['api.v1.', 'api.'], '', $name);
            $name = str_replace(['.', '-', '_'], ' ', $name);
            return ucwords($name);
        }

        // Generate from URI
        $segments = array_filter(explode('/', $uri));
        $lastSegment = end($segments);
        return ucwords(str_replace(['-', '_'], ' ', $lastSegment));
    }

    /**
     * Get API request headers
     */
    protected function getAPIRequestHeaders($route)
    {
        $headers = [
            [
                'key' => 'Accept',
                'value' => 'application/json',
                'type' => 'text'
            ]
        ];

        // Add Content-Type for POST/PUT/PATCH
        $methods = $route['methods'];
        if (array_intersect(['POST', 'PUT', 'PATCH'], $methods)) {
            $headers[] = [
                'key' => 'Content-Type',
                'value' => 'application/json',
                'type' => 'text'
            ];
        }

        // Add auth header if route has auth middleware
        $middleware = $route['middleware'];

        if (in_array('api.token', $middleware) ||
            in_array('auth:sanctum', $middleware) ||
            in_array('auth', $middleware)) {
            $headers[] = [
                'key' => 'Authorization',
                'value' => 'Bearer {{api_token}}',
                'type' => 'text'
            ];
        }

        return $headers;
    }

    /**
     * Get API request body
     */
    protected function getAPIRequestBody($route)
    {
        $uri = $route['uri'];
        $body = ['mode' => 'raw', 'raw' => ''];

        // Generate sample bodies based on route
        if (str_contains($uri, 'auth/login')) {
            $body['raw'] = json_encode([
                'email' => '{{yukimart_email}}',
                'password' => '{{yukimart_password}}'
            ], JSON_PRETTY_PRINT);
        } elseif (str_contains($uri, 'fcm')) {
            // Use FCM body generation
            return $this->getFCMRequestBody($route);
        } else {
            // Generic body
            $body['raw'] = json_encode([
                'example' => 'data'
            ], JSON_PRETTY_PRINT);
        }

        return $body;
    }

    /**
     * Get API request description
     */
    protected function getAPIRequestDescription($route)
    {
        $uri = $route['uri'];

        if (str_contains($uri, 'auth/login')) {
            return 'Login to YukiMart and get API access token';
        } elseif (str_contains($uri, 'auth/logout')) {
            return 'Logout and invalidate the current access token';
        } elseif (str_contains($uri, 'auth/user')) {
            return 'Get authenticated user profile information';
        } elseif (str_contains($uri, 'fcm')) {
            return $this->getFCMRequestDescription($route);
        }

        return "Auto-generated from route: {$route['action']}";
    }

    /**
     * Upload collection to Postman
     */
    protected function uploadToPostman($type)
    {
        $this->info('🚀 Uploading to Postman...');

        // Get API credentials
        $apiKey = $this->option('api-key') ?: env('POSTMAN_API_KEY');
        $collectionId = $this->option('collection-id') ?: env('POSTMAN_COLLECTION_ID');

        if (!$apiKey || !$collectionId) {
            $this->error('❌ Missing Postman API credentials');
            $this->info('Set POSTMAN_API_KEY and POSTMAN_COLLECTION_ID in .env or use --api-key and --collection-id options');
            return;
        }

        // Determine which collection to upload
        $collectionPath = $this->getCollectionPath($type);

        if (!File::exists($collectionPath)) {
            $this->error("❌ Collection file not found: {$collectionPath}");
            return;
        }

        $collection = json_decode(File::get($collectionPath), true);

        // Update collection metadata for upload
        $collection = $this->prepareCollectionForUpload($collection);

        // Upload to Postman
        $this->uploadCollectionToPostman($collection, $apiKey, $collectionId);
    }

    /**
     * Get collection file path based on type
     */
    protected function getCollectionPath($type)
    {
        switch ($type) {
            case 'fcm':
                return base_path('postman/YukiMart-FCM-API.postman_collection.json');
            case 'api':
                return base_path('postman/YukiMart-API.postman_collection.json');
            case 'all':
                // For 'all', upload the main API collection
                return base_path('postman/YukiMart-API.postman_collection.json');
            default:
                return base_path('postman/YukiMart-FCM-API.postman_collection.json');
        }
    }

    /**
     * Prepare collection for upload
     */
    protected function prepareCollectionForUpload($collection)
    {
        // Update collection info
        $collection['info']['name'] = 'YukiMart API v1 - Complete với Examples';
        $collection['info']['description'] = 'Complete YukiMart API collection with FCM endpoints and examples. Auto-synced from Laravel application.';
        $collection['info']['_uploaded_at'] = now()->toISOString();
        $collection['info']['_uploaded_from'] = 'Laravel Artisan Command';
        $collection['info']['version'] = '1.0.0';

        // Add collection-level variables
        if (!isset($collection['variable'])) {
            $collection['variable'] = [];
        }

        // Ensure base URL variable exists
        $hasBaseUrl = false;
        foreach ($collection['variable'] as $var) {
            if ($var['key'] === 'base_url') {
                $hasBaseUrl = true;
                break;
            }
        }

        if (!$hasBaseUrl) {
            $collection['variable'][] = [
                'key' => 'base_url',
                'value' => '{{yukimart_base_url}}',
                'type' => 'string',
                'description' => 'YukiMart base URL'
            ];
        }

        return $collection;
    }

    /**
     * Upload collection to Postman via API
     */
    protected function uploadCollectionToPostman($collection, $apiKey, $collectionId)
    {
        $this->info("📡 Uploading to collection ID: {$collectionId}");

        try {
            $response = Http::withHeaders([
                'X-API-Key' => $apiKey,
                'Content-Type' => 'application/json'
            ])->put("https://api.getpostman.com/collections/{$collectionId}", [
                'collection' => $collection
            ]);

            if ($response->successful()) {
                $this->info('✅ Collection uploaded successfully to Postman!');
                $this->info("🔗 Collection URL: https://www.postman.com/collection/{$collectionId}");

                // Show upload stats
                $stats = $this->getCollectionStats($collection);
                $this->info("📊 Upload stats: {$stats['folders']} folders, {$stats['requests']} requests");
            } else {
                $this->error('❌ Failed to upload collection to Postman');
                $this->error("Status: {$response->status()}");
                $this->error("Response: {$response->body()}");
            }
        } catch (\Exception $e) {
            $this->error('❌ Error uploading to Postman: ' . $e->getMessage());
        }
    }

    /**
     * Get collection statistics
     */
    protected function getCollectionStats($collection)
    {
        $folders = 0;
        $requests = 0;

        if (isset($collection['item'])) {
            foreach ($collection['item'] as $item) {
                if (isset($item['item'])) {
                    // This is a folder
                    $folders++;
                    $requests += count($item['item']);
                } else {
                    // This is a request
                    $requests++;
                }
            }
        }

        return [
            'folders' => $folders,
            'requests' => $requests
        ];
    }
}
