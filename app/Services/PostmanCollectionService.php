<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PostmanCollectionService
{
    private $apiKey;
    private $workspaceId;
    private $collectionId;
    private $baseUrl = 'https://api.getpostman.com';

    public function __construct()
    {
        $this->apiKey = config('postman.api_key');
        $this->workspaceId = config('postman.workspace_id');
        $this->collectionId = config('postman.collection_id');
    }

    /**
     * Generate complete Postman collection for YukiMart API v1
     */
    public function generateCollection()
    {
        $collection = [
            'info' => [
                'name' => 'YukiMart API v1 - Complete với Examples',
                'description' => 'Comprehensive API collection for YukiMart Flutter App with full test cases and examples',
                'version' => '1.0.0',
                'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json'
            ],
            'auth' => [
                'type' => 'bearer',
                'bearer' => [
                    [
                        'key' => 'token',
                        'value' => '{{auth_token}}',
                        'type' => 'string'
                    ]
                ]
            ],
            'variable' => [
                [
                    'key' => 'base_url',
                    'value' => 'http://yukimart.local/api/v1',
                    'type' => 'string'
                ],
                [
                    'key' => 'auth_token',
                    'value' => '',
                    'type' => 'string'
                ]
            ],
            'item' => []
        ];

        // Use auto-discovery for dynamic collection generation
        $collection['item'] = $this->generateDynamicFolders();

        return $collection;
    }

    /**
     * Generate folders dynamically from discovered routes
     */
    private function generateDynamicFolders(): array
    {
        $discoveryService = new \App\Services\AutoApiDiscoveryService();
        $organizedRoutes = $discoveryService->discoverRoutes();

        $folders = [];

        foreach ($organizedRoutes as $groupName => $group) {
            $folder = [
                'name' => $this->getFolderIcon($groupName) . ' ' . $group['name'],
                'description' => $group['description'],
                'item' => []
            ];

            foreach ($group['routes'] as $route) {
                $folder['item'][] = $this->createRequestFromRoute($route);
            }

            $folders[] = $folder;
        }

        // Add static folders for testing scenarios
        $folders[] = $this->createPlaygroundFolder();
        $folders[] = $this->createErrorScenariosFolder();

        return $folders;
    }

    /**
     * Create Postman request from discovered route
     */
    private function createRequestFromRoute(array $route): array
    {
        $methods = explode('|', $route['method']);
        $primaryMethod = $methods[0];

        $request = [
            'name' => $this->generateRequestName($route),
            'request' => [
                'method' => $primaryMethod,
                'header' => $this->convertHeadersToPostman($route['examples'][0]['headers'] ?? []),
                'url' => [
                    'raw' => '{{base_url}}/' . str_replace('api/v1/', '', $route['uri']),
                    'host' => ['{{base_url}}'],
                    'path' => explode('/', str_replace('api/v1/', '', $route['uri']))
                ],
                'description' => $route['description']
            ],
            'response' => []
        ];

        // Add body for POST/PUT/PATCH requests
        if (in_array($primaryMethod, ['POST', 'PUT', 'PATCH'])) {
            $exampleBody = $route['examples'][0]['body'] ?? [];
            if (!empty($exampleBody)) {
                $request['request']['body'] = [
                    'mode' => 'raw',
                    'raw' => json_encode($exampleBody, JSON_PRETTY_PRINT),
                    'options' => [
                        'raw' => [
                            'language' => 'json'
                        ]
                    ]
                ];
            }
        }

        // Add query parameters for GET requests
        if ($primaryMethod === 'GET') {
            $queryParams = $route['examples'][0]['query_params'] ?? [];
            if (!empty($queryParams)) {
                $request['request']['url']['query'] = [];
                foreach ($queryParams as $key => $value) {
                    $request['request']['url']['query'][] = [
                        'key' => $key,
                        'value' => (string) $value,
                        'disabled' => true
                    ];
                }
            }
        }

        // Add response examples if available
        if (isset($route['examples'][0]['response_examples'])) {
            $request['response'] = $this->createPostmanResponseExamples($route['examples'][0]['response_examples']);
        }

        return $request;
    }

    /**
     * Create Postman response examples
     */
    private function createPostmanResponseExamples(array $responseExamples): array
    {
        $responses = [];

        foreach ($responseExamples as $name => $example) {
            if (isset($example['body'])) {
                $responses[] = [
                    'name' => ucfirst(str_replace('_', ' ', $name)),
                    'originalRequest' => [
                        'method' => $example['method'] ?? 'GET',
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
                            'raw' => '{{base_url}}' . ($example['url'] ?? ''),
                            'host' => ['{{base_url}}'],
                            'path' => explode('/', trim($example['url'] ?? '', '/'))
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
        }

        return $responses;
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

    /**
     * Generate request name from route info
     */
    private function generateRequestName(array $route): string
    {
        $method = explode('|', $route['method'])[0];
        $uri = $route['uri'];

        // Extract meaningful name from URI
        $parts = explode('/', str_replace('api/v1/', '', $uri));
        $name = '';

        foreach ($parts as $part) {
            if (!str_contains($part, '{')) {
                $name .= ucfirst($part) . ' ';
            }
        }

        $name = trim($name);

        // Add method prefix for clarity
        $methodPrefixes = [
            'GET' => 'Get',
            'POST' => 'Create',
            'PUT' => 'Update',
            'PATCH' => 'Update',
            'DELETE' => 'Delete'
        ];

        $prefix = $methodPrefixes[$method] ?? $method;

        return $prefix . ' ' . ($name ?: 'Resource');
    }

    /**
     * Convert headers array to Postman format
     */
    private function convertHeadersToPostman(array $headers): array
    {
        $postmanHeaders = [];

        foreach ($headers as $key => $value) {
            $postmanHeaders[] = [
                'key' => $key,
                'value' => $value,
                'type' => 'text'
            ];
        }

        return $postmanHeaders;
    }

    /**
     * Get folder icon for group
     */
    private function getFolderIcon(string $groupName): string
    {
        $icons = [
            'auth' => '🔐',
            'dashboard' => '📊',
            'invoices' => '📄',
            'products' => '📦',
            'orders' => '🛒',
            'customers' => '👥',
            'payments' => '💰',
            'docs' => '📚',
            'health' => '🏥'
        ];

        return $icons[$groupName] ?? '📁';
    }

    /**
     * Create Health Check folder
     */
    private function createHealthCheckFolder()
    {
        return [
            'name' => '🏥 Health Check',
            'description' => 'System health monitoring endpoints',
            'item' => [
                [
                    'name' => 'System Health Check',
                    'request' => [
                        'method' => 'GET',
                        'header' => [
                            [
                                'key' => 'Accept',
                                'value' => 'application/json',
                                'type' => 'text'
                            ]
                        ],
                        'url' => [
                            'raw' => '{{base_url}}/health',
                            'host' => ['{{base_url}}'],
                            'path' => ['health']
                        ],
                        'description' => 'Check system health including database, cache, storage, and memory'
                    ],
                    'response' => [
                        [
                            'name' => 'Healthy System',
                            'originalRequest' => [
                                'method' => 'GET',
                                'header' => [],
                                'url' => [
                                    'raw' => '{{base_url}}/health',
                                    'host' => ['{{base_url}}'],
                                    'path' => ['health']
                                ]
                            ],
                            'status' => 'OK',
                            'code' => 200,
                            'header' => [
                                [
                                    'key' => 'Content-Type',
                                    'value' => 'application/json'
                                ]
                            ],
                            'body' => json_encode([
                                'status' => 'healthy',
                                'timestamp' => '2025-08-07T02:38:05.377936Z',
                                'version' => '1.0.0',
                                'environment' => 'local',
                                'checks' => [
                                    'database' => ['status' => 'healthy', 'message' => 'Database connection successful'],
                                    'cache' => ['status' => 'healthy', 'message' => 'Cache working properly'],
                                    'storage' => ['status' => 'healthy', 'message' => 'Storage writable'],
                                    'memory' => ['status' => 'healthy', 'usage' => '4 MB', 'limit' => '512M', 'percentage' => 0.78]
                                ]
                            ], JSON_PRETTY_PRINT)
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Create Authentication folder
     */
    private function createAuthenticationFolder()
    {
        return [
            'name' => '🔐 Authentication',
            'description' => 'User authentication and session management',
            'item' => [
                [
                    'name' => 'Login',
                    'event' => [
                        [
                            'listen' => 'test',
                            'script' => [
                                'exec' => [
                                    'if (pm.response.code === 200) {',
                                    '    const response = pm.response.json();',
                                    '    if (response.data && response.data.token) {',
                                    '        pm.collectionVariables.set("auth_token", response.data.token);',
                                    '        console.log("Token saved:", response.data.token);',
                                    '    }',
                                    '}',
                                    '',
                                    'pm.test("Status code is 200", function () {',
                                    '    pm.response.to.have.status(200);',
                                    '});',
                                    '',
                                    'pm.test("Response has token", function () {',
                                    '    const response = pm.response.json();',
                                    '    pm.expect(response.data).to.have.property("token");',
                                    '});'
                                ]
                            ]
                        ]
                    ],
                    'request' => [
                        'method' => 'POST',
                        'header' => [
                            [
                                'key' => 'Content-Type',
                                'value' => 'application/json',
                                'type' => 'text'
                            ],
                            [
                                'key' => 'Accept',
                                'value' => 'application/json',
                                'type' => 'text'
                            ]
                        ],
                        'body' => [
                            'mode' => 'raw',
                            'raw' => json_encode([
                                'email' => 'yukimart@gmail.com',
                                'password' => '123456',
                                'device_name' => 'Postman Test'
                            ], JSON_PRETTY_PRINT)
                        ],
                        'url' => [
                            'raw' => '{{base_url}}/auth/login',
                            'host' => ['{{base_url}}'],
                            'path' => ['auth', 'login']
                        ],
                        'description' => 'Authenticate user and receive access token'
                    ],
                    'response' => [
                        [
                            'name' => 'Successful Login',
                            'originalRequest' => [
                                'method' => 'POST',
                                'header' => [
                                    [
                                        'key' => 'Content-Type',
                                        'value' => 'application/json'
                                    ]
                                ],
                                'body' => [
                                    'mode' => 'raw',
                                    'raw' => json_encode([
                                        'email' => 'yukimart@gmail.com',
                                        'password' => '123456'
                                    ])
                                ],
                                'url' => [
                                    'raw' => '{{base_url}}/auth/login',
                                    'host' => ['{{base_url}}'],
                                    'path' => ['auth', 'login']
                                ]
                            ],
                            'status' => 'OK',
                            'code' => 200,
                            'body' => json_encode([
                                'status' => 'success',
                                'message' => 'Login successful',
                                'data' => [
                                    'user' => [
                                        'id' => 12,
                                        'username' => 'yukimart',
                                        'email' => 'yukimart@gmail.com',
                                        'full_name' => 'YukiMart Admin',
                                        'phone' => '0987654321',
                                        'avatar' => null,
                                        'status' => 'active',
                                        'roles' => [],
                                        'branch_shops' => []
                                    ],
                                    'access_token' => '29|8j8MAOjMWXeiZ0DvI48OlwNtOrHjYGVebfoSiFVF6dfd9a0a',
                                    'refresh_token' => '30|9k9NBPkNXYfjZ1EwJ59PmxOuPsIkZHWfcgpTjGWG7ege0b1b',
                                    'token_type' => 'Bearer',
                                    'expires_in' => 86400,
                                    'refresh_expires_in' => 2592000
                                ]
                            ], JSON_PRETTY_PRINT)
                        ],
                        [
                            'name' => 'Invalid Credentials',
                            'originalRequest' => [
                                'method' => 'POST',
                                'header' => [
                                    [
                                        'key' => 'Content-Type',
                                        'value' => 'application/json'
                                    ]
                                ],
                                'body' => [
                                    'mode' => 'raw',
                                    'raw' => json_encode([
                                        'email' => 'wrong@email.com',
                                        'password' => 'wrongpassword'
                                    ])
                                ],
                                'url' => [
                                    'raw' => '{{base_url}}/auth/login',
                                    'host' => ['{{base_url}}'],
                                    'path' => ['auth', 'login']
                                ]
                            ],
                            'status' => 'Unauthorized',
                            'code' => 401,
                            'body' => json_encode([
                                'status' => 'error',
                                'message' => 'Invalid credentials'
                            ], JSON_PRETTY_PRINT)
                        ]
                    ]
                ],
                [
                    'name' => 'Get Profile',
                    'request' => [
                        'auth' => [
                            'type' => 'bearer',
                            'bearer' => [
                                [
                                    'key' => 'token',
                                    'value' => '{{auth_token}}',
                                    'type' => 'string'
                                ]
                            ]
                        ],
                        'method' => 'GET',
                        'header' => [
                            [
                                'key' => 'Accept',
                                'value' => 'application/json',
                                'type' => 'text'
                            ]
                        ],
                        'url' => [
                            'raw' => '{{base_url}}/auth/profile',
                            'host' => ['{{base_url}}'],
                            'path' => ['auth', 'profile']
                        ],
                        'description' => 'Get authenticated user profile information'
                    ],
                    'response' => [
                        [
                            'name' => 'Profile Retrieved',
                            'status' => 'OK',
                            'code' => 200,
                            'body' => json_encode([
                                'status' => 'success',
                                'message' => 'Profile retrieved successfully',
                                'data' => [
                                    'user' => [
                                        'id' => 12,
                                        'username' => 'yukimart',
                                        'email' => 'yukimart@gmail.com',
                                        'full_name' => 'YukiMart Admin',
                                        'phone' => '0987654321',
                                        'avatar' => null,
                                        'status' => 'active',
                                        'roles' => [],
                                        'branch_shops' => []
                                    ]
                                ]
                            ], JSON_PRETTY_PRINT)
                        ]
                    ]
                ],
                [
                    'name' => 'Refresh Token',
                    'request' => [
                        'auth' => [
                            'type' => 'bearer',
                            'bearer' => [
                                [
                                    'key' => 'token',
                                    'value' => '{{refresh_token}}',
                                    'type' => 'string'
                                ]
                            ]
                        ],
                        'method' => 'POST',
                        'header' => [
                            [
                                'key' => 'Accept',
                                'value' => 'application/json',
                                'type' => 'text'
                            ]
                        ],
                        'url' => [
                            'raw' => '{{base_url}}/auth/refresh',
                            'host' => ['{{base_url}}'],
                            'path' => ['auth', 'refresh']
                        ],
                        'description' => 'Refresh access token using refresh token'
                    ],
                    'response' => [
                        [
                            'name' => 'Token Refreshed',
                            'status' => 'OK',
                            'code' => 200,
                            'body' => json_encode([
                                'status' => 'success',
                                'message' => 'Token refreshed successfully',
                                'data' => [
                                    'access_token' => '31|0l0OCQlOYZgkZ2FxK60QnyPvQtJlZIXgdhqUkHXH8fhf1c2c',
                                    'token_type' => 'Bearer',
                                    'expires_in' => 86400
                                ]
                            ], JSON_PRETTY_PRINT)
                        ],
                        [
                            'name' => 'Invalid Refresh Token',
                            'status' => 'Unauthorized',
                            'code' => 401,
                            'body' => json_encode([
                                'status' => 'error',
                                'message' => 'Invalid refresh token. Please login again.'
                            ], JSON_PRETTY_PRINT)
                        ]
                    ]
                ],
                [
                    'name' => 'Logout',
                    'request' => [
                        'auth' => [
                            'type' => 'bearer',
                            'bearer' => [
                                [
                                    'key' => 'token',
                                    'value' => '{{auth_token}}',
                                    'type' => 'string'
                                ]
                            ]
                        ],
                        'method' => 'POST',
                        'header' => [
                            [
                                'key' => 'Accept',
                                'value' => 'application/json',
                                'type' => 'text'
                            ]
                        ],
                        'url' => [
                            'raw' => '{{base_url}}/auth/logout',
                            'host' => ['{{base_url}}'],
                            'path' => ['auth', 'logout']
                        ],
                        'description' => 'Logout user and revoke access token'
                    ],
                    'response' => [
                        [
                            'name' => 'Logout Successful',
                            'status' => 'OK',
                            'code' => 200,
                            'body' => json_encode([
                                'status' => 'success',
                                'message' => 'Logout successful'
                            ], JSON_PRETTY_PRINT)
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Sync collection to Postman
     */
    public function syncToPostman($collection)
    {
        try {
            $headers = [
                'X-API-Key' => $this->apiKey,
                'Content-Type' => 'application/json'
            ];

            if ($this->collectionId) {
                // Update existing collection
                $response = Http::withHeaders($headers)
                    ->put("{$this->baseUrl}/collections/{$this->collectionId}", [
                        'collection' => $collection
                    ]);
            } else {
                // Create new collection
                $response = Http::withHeaders($headers)
                    ->post("{$this->baseUrl}/collections", [
                        'collection' => $collection
                    ]);
            }

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'collection_id' => $data['collection']['id'] ?? $data['collection']['uid'] ?? null,
                    'message' => 'Collection synced successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $response->json()['error']['message'] ?? 'Unknown error',
                    'status_code' => $response->status()
                ];
            }

        } catch (\Exception $e) {
            Log::error('Postman sync failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Create Invoice Management folder
     */
    private function createInvoiceManagementFolder()
    {
        return [
            'name' => '📄 Invoice Management',
            'description' => 'Complete invoice CRUD operations and statistics',
            'item' => [
                [
                    'name' => 'List Invoices',
                    'request' => [
                        'auth' => [
                            'type' => 'bearer',
                            'bearer' => [
                                [
                                    'key' => 'token',
                                    'value' => '{{auth_token}}',
                                    'type' => 'string'
                                ]
                            ]
                        ],
                        'method' => 'GET',
                        'header' => [
                            [
                                'key' => 'Accept',
                                'value' => 'application/json',
                                'type' => 'text'
                            ]
                        ],
                        'url' => [
                            'raw' => '{{base_url}}/invoices?per_page=10&status=draft&sort_by=created_at&sort_order=desc',
                            'host' => ['{{base_url}}'],
                            'path' => ['invoices'],
                            'query' => [
                                ['key' => 'per_page', 'value' => '10'],
                                ['key' => 'status', 'value' => 'draft'],
                                ['key' => 'sort_by', 'value' => 'created_at'],
                                ['key' => 'sort_order', 'value' => 'desc']
                            ]
                        ],
                        'description' => 'Get paginated list of invoices with filters'
                    ],
                    'response' => [
                        [
                            'name' => 'Invoice List',
                            'status' => 'OK',
                            'code' => 200,
                            'body' => json_encode([
                                'status' => 'success',
                                'message' => 'Invoices retrieved successfully',
                                'data' => [],
                                'meta' => [
                                    'current_page' => 1,
                                    'last_page' => 1,
                                    'per_page' => 10,
                                    'total' => 0,
                                    'from' => null,
                                    'to' => null
                                ]
                            ], JSON_PRETTY_PRINT)
                        ]
                    ]
                ],
                [
                    'name' => 'Create Invoice',
                    'request' => [
                        'auth' => [
                            'type' => 'bearer',
                            'bearer' => [
                                [
                                    'key' => 'token',
                                    'value' => '{{auth_token}}',
                                    'type' => 'string'
                                ]
                            ]
                        ],
                        'method' => 'POST',
                        'header' => [
                            [
                                'key' => 'Content-Type',
                                'value' => 'application/json',
                                'type' => 'text'
                            ],
                            [
                                'key' => 'Accept',
                                'value' => 'application/json',
                                'type' => 'text'
                            ]
                        ],
                        'body' => [
                            'mode' => 'raw',
                            'raw' => json_encode([
                                'customer_name' => 'Khách lẻ',
                                'branch_shop_id' => 1,
                                'invoice_type' => 'sale',
                                'invoice_date' => '2025-08-07',
                                'due_date' => '2025-08-14',
                                'payment_terms' => 'Thanh toán trong 7 ngày',
                                'notes' => 'Hóa đơn test từ Postman',
                                'items' => [
                                    [
                                        'product_name' => 'Sản phẩm Test',
                                        'product_sku' => 'TEST001',
                                        'quantity' => 2,
                                        'unit' => 'cái',
                                        'unit_price' => 500000,
                                        'discount_rate' => 5,
                                        'tax_rate' => 10,
                                        'notes' => 'Sản phẩm test'
                                    ]
                                ]
                            ], JSON_PRETTY_PRINT)
                        ],
                        'url' => [
                            'raw' => '{{base_url}}/invoices',
                            'host' => ['{{base_url}}'],
                            'path' => ['invoices']
                        ],
                        'description' => 'Create a new invoice with items'
                    ]
                ],
                [
                    'name' => 'Get Invoice Details',
                    'request' => [
                        'auth' => [
                            'type' => 'bearer',
                            'bearer' => [
                                [
                                    'key' => 'token',
                                    'value' => '{{auth_token}}',
                                    'type' => 'string'
                                ]
                            ]
                        ],
                        'method' => 'GET',
                        'header' => [
                            [
                                'key' => 'Accept',
                                'value' => 'application/json',
                                'type' => 'text'
                            ]
                        ],
                        'url' => [
                            'raw' => '{{base_url}}/invoices/1',
                            'host' => ['{{base_url}}'],
                            'path' => ['invoices', '1']
                        ],
                        'description' => 'Get detailed information of a specific invoice'
                    ]
                ],
                [
                    'name' => 'Invoice Statistics',
                    'request' => [
                        'auth' => [
                            'type' => 'bearer',
                            'bearer' => [
                                [
                                    'key' => 'token',
                                    'value' => '{{auth_token}}',
                                    'type' => 'string'
                                ]
                            ]
                        ],
                        'method' => 'GET',
                        'header' => [
                            [
                                'key' => 'Accept',
                                'value' => 'application/json',
                                'type' => 'text'
                            ]
                        ],
                        'url' => [
                            'raw' => '{{base_url}}/invoices/statistics?date_from=2025-08-01&date_to=2025-08-31',
                            'host' => ['{{base_url}}'],
                            'path' => ['invoices', 'statistics'],
                            'query' => [
                                ['key' => 'date_from', 'value' => '2025-08-01'],
                                ['key' => 'date_to', 'value' => '2025-08-31']
                            ]
                        ],
                        'description' => 'Get invoice statistics for dashboard'
                    ]
                ]
            ]
        ];
    }

    /**
     * Create Products folder
     */
    private function createProductsFolder()
    {
        return [
            'name' => '📦 Products',
            'description' => 'Product management endpoints with full CRUD operations',
            'item' => [
                [
                    'name' => 'List Products',
                    'request' => [
                        'auth' => [
                            'type' => 'bearer',
                            'bearer' => [
                                [
                                    'key' => 'token',
                                    'value' => '{{auth_token}}',
                                    'type' => 'string'
                                ]
                            ]
                        ],
                        'method' => 'GET',
                        'header' => [
                            [
                                'key' => 'Accept',
                                'value' => 'application/json',
                                'type' => 'text'
                            ]
                        ],
                        'url' => [
                            'raw' => '{{base_url}}/products?per_page=10&search=&category_id=&in_stock=true',
                            'host' => ['{{base_url}}'],
                            'path' => ['products'],
                            'query' => [
                                ['key' => 'per_page', 'value' => '10'],
                                ['key' => 'search', 'value' => ''],
                                ['key' => 'category_id', 'value' => ''],
                                ['key' => 'in_stock', 'value' => 'true']
                            ]
                        ],
                        'description' => 'Get paginated list of products with filters'
                    ],
                    'response' => [
                        [
                            'name' => 'Product List',
                            'status' => 'OK',
                            'code' => 200,
                            'body' => json_encode([
                                'status' => 'success',
                                'message' => 'Products retrieved successfully',
                                'data' => [
                                    [
                                        'id' => 1,
                                        'product_name' => 'Sản phẩm Test',
                                        'product_sku' => 'SP001',
                                        'product_barcode' => '1234567890',
                                        'product_status' => 'active',
                                        'cost_price' => 100000.00,
                                        'sale_price' => 150000.00,
                                        'current_stock' => 50,
                                        'category' => [
                                            'id' => 1,
                                            'name' => 'Điện tử'
                                        ],
                                        'created_at' => '2025-08-07T10:00:00.000000Z'
                                    ]
                                ],
                                'meta' => [
                                    'current_page' => 1,
                                    'last_page' => 1,
                                    'per_page' => 10,
                                    'total' => 1
                                ]
                            ], JSON_PRETTY_PRINT)
                        ]
                    ]
                ],
                [
                    'name' => 'Create Product',
                    'request' => [
                        'auth' => [
                            'type' => 'bearer',
                            'bearer' => [
                                [
                                    'key' => 'token',
                                    'value' => '{{auth_token}}',
                                    'type' => 'string'
                                ]
                            ]
                        ],
                        'method' => 'POST',
                        'header' => [
                            [
                                'key' => 'Content-Type',
                                'value' => 'application/json',
                                'type' => 'text'
                            ],
                            [
                                'key' => 'Accept',
                                'value' => 'application/json',
                                'type' => 'text'
                            ]
                        ],
                        'body' => [
                            'mode' => 'raw',
                            'raw' => json_encode([
                                'product_name' => 'Sản phẩm mới',
                                'product_sku' => 'SP002',
                                'product_barcode' => '1234567891',
                                'product_description' => 'Mô tả sản phẩm',
                                'product_status' => 'active',
                                'cost_price' => 80000,
                                'sale_price' => 120000,
                                'category_id' => 1,
                                'reorder_point' => 10,
                                'product_feature' => true
                            ], JSON_PRETTY_PRINT)
                        ],
                        'url' => [
                            'raw' => '{{base_url}}/products',
                            'host' => ['{{base_url}}'],
                            'path' => ['products']
                        ],
                        'description' => 'Create a new product'
                    ]
                ],
                [
                    'name' => 'Get Product Details',
                    'request' => [
                        'auth' => [
                            'type' => 'bearer',
                            'bearer' => [
                                [
                                    'key' => 'token',
                                    'value' => '{{auth_token}}',
                                    'type' => 'string'
                                ]
                            ]
                        ],
                        'method' => 'GET',
                        'header' => [
                            [
                                'key' => 'Accept',
                                'value' => 'application/json',
                                'type' => 'text'
                            ]
                        ],
                        'url' => [
                            'raw' => '{{base_url}}/products/1',
                            'host' => ['{{base_url}}'],
                            'path' => ['products', '1']
                        ],
                        'description' => 'Get detailed information of a specific product'
                    ]
                ],
                [
                    'name' => 'Search Product by Barcode',
                    'request' => [
                        'auth' => [
                            'type' => 'bearer',
                            'bearer' => [
                                [
                                    'key' => 'token',
                                    'value' => '{{auth_token}}',
                                    'type' => 'string'
                                ]
                            ]
                        ],
                        'method' => 'GET',
                        'header' => [
                            [
                                'key' => 'Accept',
                                'value' => 'application/json',
                                'type' => 'text'
                            ]
                        ],
                        'url' => [
                            'raw' => '{{base_url}}/products/search-barcode?barcode=1234567890',
                            'host' => ['{{base_url}}'],
                            'path' => ['products', 'search-barcode'],
                            'query' => [
                                ['key' => 'barcode', 'value' => '1234567890']
                            ]
                        ],
                        'description' => 'Search product by barcode for quick lookup'
                    ]
                ]
            ]
        ];
    }

    /**
     * Create Orders folder
     */
    private function createOrdersFolder()
    {
        return [
            'name' => '🛒 Orders',
            'description' => 'Order management endpoints with full CRUD operations',
            'item' => [
                [
                    'name' => 'List Orders',
                    'request' => [
                        'auth' => [
                            'type' => 'bearer',
                            'bearer' => [
                                [
                                    'key' => 'token',
                                    'value' => '{{auth_token}}',
                                    'type' => 'string'
                                ]
                            ]
                        ],
                        'method' => 'GET',
                        'header' => [
                            [
                                'key' => 'Accept',
                                'value' => 'application/json',
                                'type' => 'text'
                            ]
                        ],
                        'url' => [
                            'raw' => '{{base_url}}/orders?per_page=10&status=&payment_status=&search=',
                            'host' => ['{{base_url}}'],
                            'path' => ['orders'],
                            'query' => [
                                ['key' => 'per_page', 'value' => '10'],
                                ['key' => 'status', 'value' => ''],
                                ['key' => 'payment_status', 'value' => ''],
                                ['key' => 'search', 'value' => '']
                            ]
                        ],
                        'description' => 'Get paginated list of orders with filters'
                    ],
                    'response' => [
                        [
                            'name' => 'Order List',
                            'status' => 'OK',
                            'code' => 200,
                            'body' => json_encode([
                                'status' => 'success',
                                'message' => 'Orders retrieved successfully',
                                'data' => [
                                    [
                                        'id' => 1,
                                        'order_number' => 'ORD20250807001',
                                        'order_type' => 'sale',
                                        'status' => 'processing',
                                        'payment_status' => 'paid',
                                        'customer_name' => 'Nguyễn Văn A',
                                        'customer_phone' => '0123456789',
                                        'subtotal' => 1000000.00,
                                        'final_amount' => 1100000.00,
                                        'order_date' => '2025-08-07',
                                        'created_at' => '2025-08-07T10:00:00.000000Z'
                                    ]
                                ],
                                'meta' => [
                                    'current_page' => 1,
                                    'last_page' => 1,
                                    'per_page' => 10,
                                    'total' => 1
                                ]
                            ], JSON_PRETTY_PRINT)
                        ]
                    ]
                ],
                [
                    'name' => 'Create Order',
                    'request' => [
                        'auth' => [
                            'type' => 'bearer',
                            'bearer' => [
                                [
                                    'key' => 'token',
                                    'value' => '{{auth_token}}',
                                    'type' => 'string'
                                ]
                            ]
                        ],
                        'method' => 'POST',
                        'header' => [
                            [
                                'key' => 'Content-Type',
                                'value' => 'application/json',
                                'type' => 'text'
                            ],
                            [
                                'key' => 'Accept',
                                'value' => 'application/json',
                                'type' => 'text'
                            ]
                        ],
                        'body' => [
                            'mode' => 'raw',
                            'raw' => json_encode([
                                'customer_name' => 'Khách lẻ',
                                'customer_phone' => '0987654321',
                                'branch_shop_id' => 1,
                                'order_type' => 'sale',
                                'order_date' => '2025-08-07',
                                'priority' => 'normal',
                                'payment_method' => 'cash',
                                'notes' => 'Đơn hàng test từ Postman',
                                'items' => [
                                    [
                                        'product_name' => 'Sản phẩm Test',
                                        'product_sku' => 'SP001',
                                        'quantity' => 2,
                                        'unit' => 'cái',
                                        'unit_price' => 500000,
                                        'discount_rate' => 5,
                                        'tax_rate' => 10
                                    ]
                                ]
                            ], JSON_PRETTY_PRINT)
                        ],
                        'url' => [
                            'raw' => '{{base_url}}/orders',
                            'host' => ['{{base_url}}'],
                            'path' => ['orders']
                        ],
                        'description' => 'Create a new order with items'
                    ]
                ],
                [
                    'name' => 'Get Order Details',
                    'request' => [
                        'auth' => [
                            'type' => 'bearer',
                            'bearer' => [
                                [
                                    'key' => 'token',
                                    'value' => '{{auth_token}}',
                                    'type' => 'string'
                                ]
                            ]
                        ],
                        'method' => 'GET',
                        'header' => [
                            [
                                'key' => 'Accept',
                                'value' => 'application/json',
                                'type' => 'text'
                            ]
                        ],
                        'url' => [
                            'raw' => '{{base_url}}/orders/1',
                            'host' => ['{{base_url}}'],
                            'path' => ['orders', '1']
                        ],
                        'description' => 'Get detailed information of a specific order'
                    ]
                ]
            ]
        ];
    }



    /**
     * Create Playground folder
     */
    private function createPlaygroundFolder()
    {
        return [
            'name' => '🎮 Playground',
            'description' => 'Test and experiment with API endpoints',
            'item' => [
                [
                    'name' => 'Quick Test - Get User Info',
                    'request' => [
                        'auth' => [
                            'type' => 'bearer',
                            'bearer' => [
                                [
                                    'key' => 'token',
                                    'value' => '{{auth_token}}',
                                    'type' => 'string'
                                ]
                            ]
                        ],
                        'method' => 'GET',
                        'header' => [
                            [
                                'key' => 'Accept',
                                'value' => 'application/json',
                                'type' => 'text'
                            ]
                        ],
                        'url' => [
                            'raw' => '{{base_url}}/user',
                            'host' => ['{{base_url}}'],
                            'path' => ['user']
                        ],
                        'description' => 'Quick test to get authenticated user information'
                    ]
                ]
            ]
        ];
    }

    /**
     * Create Error Scenarios folder
     */
    private function createErrorScenariosFolder()
    {
        return [
            'name' => '⚠️ Error Scenarios',
            'description' => 'Test various error conditions and responses',
            'item' => [
                [
                    'name' => 'Unauthorized Access',
                    'request' => [
                        'method' => 'GET',
                        'header' => [
                            [
                                'key' => 'Accept',
                                'value' => 'application/json',
                                'type' => 'text'
                            ]
                        ],
                        'url' => [
                            'raw' => '{{base_url}}/auth/profile',
                            'host' => ['{{base_url}}'],
                            'path' => ['auth', 'profile']
                        ],
                        'description' => 'Test unauthorized access without token'
                    ],
                    'response' => [
                        [
                            'name' => 'Unauthorized',
                            'status' => 'Unauthorized',
                            'code' => 401,
                            'body' => json_encode([
                                'status' => 'error',
                                'message' => 'Unauthenticated',
                                'code' => 401
                            ], JSON_PRETTY_PRINT)
                        ]
                    ]
                ],
                [
                    'name' => 'Invalid Invoice ID',
                    'request' => [
                        'auth' => [
                            'type' => 'bearer',
                            'bearer' => [
                                [
                                    'key' => 'token',
                                    'value' => '{{auth_token}}',
                                    'type' => 'string'
                                ]
                            ]
                        ],
                        'method' => 'GET',
                        'header' => [
                            [
                                'key' => 'Accept',
                                'value' => 'application/json',
                                'type' => 'text'
                            ]
                        ],
                        'url' => [
                            'raw' => '{{base_url}}/invoices/99999',
                            'host' => ['{{base_url}}'],
                            'path' => ['invoices', '99999']
                        ],
                        'description' => 'Test accessing non-existent invoice'
                    ],
                    'response' => [
                        [
                            'name' => 'Not Found',
                            'status' => 'Not Found',
                            'code' => 404,
                            'body' => json_encode([
                                'status' => 'error',
                                'message' => 'Invoice not found'
                            ], JSON_PRETTY_PRINT)
                        ]
                    ]
                ],
                [
                    'name' => 'Validation Error',
                    'request' => [
                        'auth' => [
                            'type' => 'bearer',
                            'bearer' => [
                                [
                                    'key' => 'token',
                                    'value' => '{{auth_token}}',
                                    'type' => 'string'
                                ]
                            ]
                        ],
                        'method' => 'POST',
                        'header' => [
                            [
                                'key' => 'Content-Type',
                                'value' => 'application/json',
                                'type' => 'text'
                            ],
                            [
                                'key' => 'Accept',
                                'value' => 'application/json',
                                'type' => 'text'
                            ]
                        ],
                        'body' => [
                            'mode' => 'raw',
                            'raw' => json_encode([
                                'customer_name' => '',
                                'branch_shop_id' => 'invalid',
                                'invoice_type' => 'invalid_type',
                                'items' => []
                            ], JSON_PRETTY_PRINT)
                        ],
                        'url' => [
                            'raw' => '{{base_url}}/invoices',
                            'host' => ['{{base_url}}'],
                            'path' => ['invoices']
                        ],
                        'description' => 'Test validation errors when creating invoice'
                    ],
                    'response' => [
                        [
                            'name' => 'Validation Failed',
                            'status' => 'Unprocessable Entity',
                            'code' => 422,
                            'body' => json_encode([
                                'status' => 'error',
                                'message' => 'Validation failed',
                                'errors' => [
                                    'customer_name' => ['Tên khách hàng là bắt buộc khi không chọn khách hàng có sẵn'],
                                    'branch_shop_id' => ['Chi nhánh không tồn tại'],
                                    'invoice_type' => ['Loại hóa đơn không hợp lệ'],
                                    'items' => ['Phải có ít nhất 1 sản phẩm']
                                ]
                            ], JSON_PRETTY_PRINT)
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Create Customers folder
     */
    private function createCustomersFolder()
    {
        return [
            'name' => '👥 Customers',
            'description' => 'Customer management endpoints with full CRUD operations',
            'item' => [
                [
                    'name' => 'List Customers',
                    'request' => [
                        'auth' => [
                            'type' => 'bearer',
                            'bearer' => [
                                [
                                    'key' => 'token',
                                    'value' => '{{auth_token}}',
                                    'type' => 'string'
                                ]
                            ]
                        ],
                        'method' => 'GET',
                        'header' => [
                            [
                                'key' => 'Accept',
                                'value' => 'application/json',
                                'type' => 'text'
                            ]
                        ],
                        'url' => [
                            'raw' => '{{base_url}}/customers?per_page=10&search=&status=active',
                            'host' => ['{{base_url}}'],
                            'path' => ['customers'],
                            'query' => [
                                ['key' => 'per_page', 'value' => '10'],
                                ['key' => 'search', 'value' => ''],
                                ['key' => 'status', 'value' => 'active']
                            ]
                        ],
                        'description' => 'Get paginated list of customers with filters'
                    ],
                    'response' => [
                        [
                            'name' => 'Customer List',
                            'status' => 'OK',
                            'code' => 200,
                            'body' => json_encode([
                                'status' => 'success',
                                'message' => 'Customers retrieved successfully',
                                'data' => [
                                    [
                                        'id' => 1,
                                        'customer_code' => 'KH000001',
                                        'name' => 'Nguyễn Văn A',
                                        'phone' => '0123456789',
                                        'email' => 'nguyenvana@email.com',
                                        'address' => '123 Đường ABC, Quận 1, TP.HCM',
                                        'customer_type' => 'individual',
                                        'status' => 'active',
                                        'points' => 1500,
                                        'created_at' => '2025-08-07T10:00:00.000000Z'
                                    ]
                                ],
                                'meta' => [
                                    'current_page' => 1,
                                    'last_page' => 1,
                                    'per_page' => 10,
                                    'total' => 1
                                ]
                            ], JSON_PRETTY_PRINT)
                        ]
                    ]
                ],
                [
                    'name' => 'Create Customer',
                    'request' => [
                        'auth' => [
                            'type' => 'bearer',
                            'bearer' => [
                                [
                                    'key' => 'token',
                                    'value' => '{{auth_token}}',
                                    'type' => 'string'
                                ]
                            ]
                        ],
                        'method' => 'POST',
                        'header' => [
                            [
                                'key' => 'Content-Type',
                                'value' => 'application/json',
                                'type' => 'text'
                            ],
                            [
                                'key' => 'Accept',
                                'value' => 'application/json',
                                'type' => 'text'
                            ]
                        ],
                        'body' => [
                            'mode' => 'raw',
                            'raw' => json_encode([
                                'name' => 'Trần Thị B',
                                'phone' => '0987654321',
                                'email' => 'tranthib@email.com',
                                'address' => '456 Đường XYZ, Quận 2, TP.HCM',
                                'customer_type' => 'individual',
                                'status' => 'active',
                                'branch_shop_id' => 1,
                                'notes' => 'Khách hàng VIP'
                            ], JSON_PRETTY_PRINT)
                        ],
                        'url' => [
                            'raw' => '{{base_url}}/customers',
                            'host' => ['{{base_url}}'],
                            'path' => ['customers']
                        ],
                        'description' => 'Create a new customer'
                    ]
                ],
                [
                    'name' => 'Customer Statistics',
                    'request' => [
                        'auth' => [
                            'type' => 'bearer',
                            'bearer' => [
                                [
                                    'key' => 'token',
                                    'value' => '{{auth_token}}',
                                    'type' => 'string'
                                ]
                            ]
                        ],
                        'method' => 'GET',
                        'header' => [
                            [
                                'key' => 'Accept',
                                'value' => 'application/json',
                                'type' => 'text'
                            ]
                        ],
                        'url' => [
                            'raw' => '{{base_url}}/customers/statistics',
                            'host' => ['{{base_url}}'],
                            'path' => ['customers', 'statistics']
                        ],
                        'description' => 'Get customer statistics for dashboard'
                    ]
                ]
            ]
        ];
    }

    /**
     * Create Payments folder
     */
    private function createPaymentsFolder()
    {
        return [
            'name' => '💰 Payments',
            'description' => 'Payment management endpoints with full CRUD operations',
            'item' => [
                [
                    'name' => 'List Payments',
                    'request' => [
                        'auth' => [
                            'type' => 'bearer',
                            'bearer' => [
                                [
                                    'key' => 'token',
                                    'value' => '{{auth_token}}',
                                    'type' => 'string'
                                ]
                            ]
                        ],
                        'method' => 'GET',
                        'header' => [
                            [
                                'key' => 'Accept',
                                'value' => 'application/json',
                                'type' => 'text'
                            ]
                        ],
                        'url' => [
                            'raw' => '{{base_url}}/payments?per_page=10&payment_type=&status=completed',
                            'host' => ['{{base_url}}'],
                            'path' => ['payments'],
                            'query' => [
                                ['key' => 'per_page', 'value' => '10'],
                                ['key' => 'payment_type', 'value' => ''],
                                ['key' => 'status', 'value' => 'completed']
                            ]
                        ],
                        'description' => 'Get paginated list of payments with filters'
                    ],
                    'response' => [
                        [
                            'name' => 'Payment List',
                            'status' => 'OK',
                            'code' => 200,
                            'body' => json_encode([
                                'status' => 'success',
                                'message' => 'Payments retrieved successfully',
                                'data' => [
                                    [
                                        'id' => 1,
                                        'payment_code' => 'TT20250807001',
                                        'payment_type' => 'income',
                                        'payment_method' => 'cash',
                                        'amount' => 1100000.00,
                                        'payment_date' => '2025-08-07',
                                        'description' => 'Thanh toán hóa đơn HD001',
                                        'status' => 'completed',
                                        'reference_type' => 'invoice',
                                        'reference_id' => 1,
                                        'created_at' => '2025-08-07T10:00:00.000000Z'
                                    ]
                                ],
                                'meta' => [
                                    'current_page' => 1,
                                    'last_page' => 1,
                                    'per_page' => 10,
                                    'total' => 1
                                ]
                            ], JSON_PRETTY_PRINT)
                        ]
                    ]
                ],
                [
                    'name' => 'Create Payment',
                    'request' => [
                        'auth' => [
                            'type' => 'bearer',
                            'bearer' => [
                                [
                                    'key' => 'token',
                                    'value' => '{{auth_token}}',
                                    'type' => 'string'
                                ]
                            ]
                        ],
                        'method' => 'POST',
                        'header' => [
                            [
                                'key' => 'Content-Type',
                                'value' => 'application/json',
                                'type' => 'text'
                            ],
                            [
                                'key' => 'Accept',
                                'value' => 'application/json',
                                'type' => 'text'
                            ]
                        ],
                        'body' => [
                            'mode' => 'raw',
                            'raw' => json_encode([
                                'payment_type' => 'income',
                                'payment_method' => 'cash',
                                'amount' => 500000,
                                'payment_date' => '2025-08-07',
                                'description' => 'Thanh toán tiền mặt',
                                'reference_type' => 'invoice',
                                'reference_id' => 1,
                                'bank_account_id' => 1,
                                'notes' => 'Thanh toán từ Postman'
                            ], JSON_PRETTY_PRINT)
                        ],
                        'url' => [
                            'raw' => '{{base_url}}/payments',
                            'host' => ['{{base_url}}'],
                            'path' => ['payments']
                        ],
                        'description' => 'Create a new payment record'
                    ]
                ],
                [
                    'name' => 'Payment Statistics',
                    'request' => [
                        'auth' => [
                            'type' => 'bearer',
                            'bearer' => [
                                [
                                    'key' => 'token',
                                    'value' => '{{auth_token}}',
                                    'type' => 'string'
                                ]
                            ]
                        ],
                        'method' => 'GET',
                        'header' => [
                            [
                                'key' => 'Accept',
                                'value' => 'application/json',
                                'type' => 'text'
                            ]
                        ],
                        'url' => [
                            'raw' => '{{base_url}}/payments/statistics?date_from=2025-08-01&date_to=2025-08-31',
                            'host' => ['{{base_url}}'],
                            'path' => ['payments', 'statistics'],
                            'query' => [
                                ['key' => 'date_from', 'value' => '2025-08-01'],
                                ['key' => 'date_to', 'value' => '2025-08-31']
                            ]
                        ],
                        'description' => 'Get payment statistics for financial dashboard'
                    ]
                ]
            ]
        ];
    }

    /**
     * Create Dashboard folder
     */
    private function createDashboardFolder()
    {
        return [
            'name' => '📊 Dashboard',
            'description' => 'Dashboard analytics and statistics endpoints',
            'item' => [
                [
                    'name' => 'Dashboard Overview',
                    'request' => [
                        'auth' => [
                            'type' => 'bearer',
                            'bearer' => [
                                [
                                    'key' => 'token',
                                    'value' => '{{auth_token}}',
                                    'type' => 'string'
                                ]
                            ]
                        ],
                        'method' => 'GET',
                        'header' => [
                            [
                                'key' => 'Accept',
                                'value' => 'application/json',
                                'type' => 'text'
                            ]
                        ],
                        'url' => [
                            'raw' => '{{base_url}}/dashboard',
                            'host' => ['{{base_url}}'],
                            'path' => ['dashboard']
                        ],
                        'description' => 'Get comprehensive dashboard data including statistics, charts, and recent activities'
                    ],
                    'response' => [
                        [
                            'name' => 'Dashboard Data',
                            'status' => 'OK',
                            'code' => 200,
                            'body' => json_encode([
                                'status' => 'success',
                                'message' => 'Dashboard data retrieved successfully',
                                'data' => [
                                    'statistics' => [
                                        'total_products' => 150,
                                        'active_products' => 140,
                                        'total_orders' => 85,
                                        'total_customers' => 45,
                                        'total_users' => 5,
                                        'active_users' => 4,
                                        'total_invoices' => 92,
                                        'low_stock_products' => 8
                                    ],
                                    'today_sales' => [
                                        'revenue' => 2500000.00,
                                        'orders_count' => 12,
                                        'customers_count' => 8,
                                        'avg_order_value' => 208333.33
                                    ],
                                    'recent_products' => [],
                                    'recent_activities' => [],
                                    'revenue_chart' => [],
                                    'top_products_chart' => []
                                ]
                            ], JSON_PRETTY_PRINT)
                        ]
                    ]
                ],
                [
                    'name' => 'Dashboard Statistics',
                    'request' => [
                        'auth' => [
                            'type' => 'bearer',
                            'bearer' => [
                                [
                                    'key' => 'token',
                                    'value' => '{{auth_token}}',
                                    'type' => 'string'
                                ]
                            ]
                        ],
                        'method' => 'GET',
                        'header' => [
                            [
                                'key' => 'Accept',
                                'value' => 'application/json',
                                'type' => 'text'
                            ]
                        ],
                        'url' => [
                            'raw' => '{{base_url}}/dashboard/stats',
                            'host' => ['{{base_url}}'],
                            'path' => ['dashboard', 'stats']
                        ],
                        'description' => 'Get key dashboard statistics for mobile app'
                    ],
                    'response' => [
                        [
                            'name' => 'Statistics Data',
                            'status' => 'OK',
                            'code' => 200,
                            'body' => json_encode([
                                'status' => 'success',
                                'message' => 'Statistics retrieved successfully',
                                'data' => [
                                    'total_orders' => 85,
                                    'total_invoices' => 92,
                                    'total_products' => 150,
                                    'active_products' => 140,
                                    'total_revenue' => 2500000.00,
                                    'orders_today' => 12,
                                    'total_customers' => 45,
                                    'total_users' => 5,
                                    'active_users' => 4,
                                    'low_stock_products' => 8,
                                    'avg_order_value' => 208333.33,
                                    'customers_today' => 8
                                ]
                            ], JSON_PRETTY_PRINT)
                        ]
                    ]
                ],
                [
                    'name' => 'Recent Orders',
                    'request' => [
                        'auth' => [
                            'type' => 'bearer',
                            'bearer' => [
                                [
                                    'key' => 'token',
                                    'value' => '{{auth_token}}',
                                    'type' => 'string'
                                ]
                            ]
                        ],
                        'method' => 'GET',
                        'header' => [
                            [
                                'key' => 'Accept',
                                'value' => 'application/json',
                                'type' => 'text'
                            ]
                        ],
                        'url' => [
                            'raw' => '{{base_url}}/dashboard/recent-orders?limit=10',
                            'host' => ['{{base_url}}'],
                            'path' => ['dashboard', 'recent-orders'],
                            'query' => [
                                ['key' => 'limit', 'value' => '10']
                            ]
                        ],
                        'description' => 'Get recent orders for dashboard display'
                    ]
                ],
                [
                    'name' => 'Top Products',
                    'request' => [
                        'auth' => [
                            'type' => 'bearer',
                            'bearer' => [
                                [
                                    'key' => 'token',
                                    'value' => '{{auth_token}}',
                                    'type' => 'string'
                                ]
                            ]
                        ],
                        'method' => 'GET',
                        'header' => [
                            [
                                'key' => 'Accept',
                                'value' => 'application/json',
                                'type' => 'text'
                            ]
                        ],
                        'url' => [
                            'raw' => '{{base_url}}/dashboard/top-products?limit=10&type=revenue',
                            'host' => ['{{base_url}}'],
                            'path' => ['dashboard', 'top-products'],
                            'query' => [
                                ['key' => 'limit', 'value' => '10'],
                                ['key' => 'type', 'value' => 'revenue']
                            ]
                        ],
                        'description' => 'Get top selling products by revenue or quantity'
                    ]
                ],
                [
                    'name' => 'Low Stock Products',
                    'request' => [
                        'auth' => [
                            'type' => 'bearer',
                            'bearer' => [
                                [
                                    'key' => 'token',
                                    'value' => '{{auth_token}}',
                                    'type' => 'string'
                                ]
                            ]
                        ],
                        'method' => 'GET',
                        'header' => [
                            [
                                'key' => 'Accept',
                                'value' => 'application/json',
                                'type' => 'text'
                            ]
                        ],
                        'url' => [
                            'raw' => '{{base_url}}/dashboard/low-stock-products?limit=10',
                            'host' => ['{{base_url}}'],
                            'path' => ['dashboard', 'low-stock-products'],
                            'query' => [
                                ['key' => 'limit', 'value' => '10']
                            ]
                        ],
                        'description' => 'Get products with low stock levels'
                    ]
                ]
            ]
        ];
    }

    /**
     * Save collection to file
     */
    public function saveCollectionToFile($collection, $filename = 'yukimart-api-v1-complete.json')
    {
        $path = "testing/postman/{$filename}";
        Storage::put($path, json_encode($collection, JSON_PRETTY_PRINT));
        return storage_path("app/{$path}");
    }
}
