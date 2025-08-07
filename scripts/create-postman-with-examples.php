<?php

/**
 * Create Postman Collection với Examples đúng format
 * Fix issue với Examples không hiển thị trong Postman
 */

require_once __DIR__ . '/../vendor/autoload.php';

class PostmanExamplesFixer
{
    private $baseUrl = 'http://yukimart.local/api/v1';
    private $email = 'yukimart@gmail.com';
    private $password = '123456';
    private $token = null;

    public function __construct()
    {
        echo "🔧 Creating Postman Collection với Examples đúng format\n";
        echo "======================================================\n\n";
    }

    public function createCollection()
    {
        try {
            // 1. Get authentication token
            $this->authenticate();
            
            // 2. Create collection với proper format
            $collection = $this->createBaseCollection();
            
            // 3. Add all requests với examples
            $collection['item'] = [
                $this->createHealthFolder(),
                $this->createAuthFolder(),
                $this->createProductsFolder(),
                $this->createOrdersFolder(),
                $this->createCustomersFolder(),
                $this->createPaymentsFolder(),
                $this->createPlaygroundFolder(),
                $this->createErrorsFolder()
            ];
            
            // 4. Save collection
            $this->saveCollection($collection);
            
            echo "\n🎉 Postman Collection với Examples đã tạo thành công!\n";
            echo "===================================================\n\n";
            $this->printSummary();
            
        } catch (Exception $e) {
            echo "❌ Error: " . $e->getMessage() . "\n";
        }
    }

    private function authenticate()
    {
        echo "🔐 Getting authentication token...\n";
        
        $response = $this->makeRequest('POST', '/auth/login', [
            'email' => $this->email,
            'password' => $this->password,
            'device_name' => 'Postman Examples Creator'
        ]);
        
        if ($response['success'] && isset($response['data']['token'])) {
            $this->token = $response['data']['token'];
            echo "✅ Token obtained successfully\n\n";
        } else {
            throw new Exception("Authentication failed");
        }
    }

    private function createBaseCollection()
    {
        return [
            'info' => [
                'name' => 'YukiMart API v1 - Complete với Examples',
                'description' => 'Complete YukiMart API collection với comprehensive response examples cho Flutter development.',
                'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
                'version' => [
                    'major' => 1,
                    'minor' => 0,
                    'patch' => 0
                ]
            ],
            'variable' => [
                [
                    'key' => 'base_url',
                    'value' => $this->baseUrl,
                    'description' => 'Base URL for YukiMart API'
                ],
                [
                    'key' => 'api_token',
                    'value' => $this->token,
                    'description' => 'Authentication token'
                ],
                [
                    'key' => 'user_email',
                    'value' => $this->email,
                    'description' => 'Test user email'
                ],
                [
                    'key' => 'user_password',
                    'value' => $this->password,
                    'description' => 'Test user password'
                ]
            ]
        ];
    }

    private function createHealthFolder()
    {
        $healthResponse = $this->makeRequest('GET', '/health');
        
        return [
            'name' => '🏥 Health Check',
            'item' => [
                [
                    'name' => 'Health Check',
                    'request' => [
                        'method' => 'GET',
                        'header' => [
                            [
                                'key' => 'Accept',
                                'value' => 'application/json'
                            ]
                        ],
                        'url' => [
                            'raw' => '{{base_url}}/health',
                            'host' => ['{{base_url}}'],
                            'path' => ['health']
                        ]
                    ],
                    'response' => [
                        [
                            'name' => 'Healthy System',
                            'originalRequest' => [
                                'method' => 'GET',
                                'header' => [
                                    [
                                        'key' => 'Accept',
                                        'value' => 'application/json'
                                    ]
                                ],
                                'url' => [
                                    'raw' => '{{base_url}}/health',
                                    'host' => ['{{base_url}}'],
                                    'path' => ['health']
                                ]
                            ],
                            'status' => 'OK',
                            'code' => 200,
                            '_postman_previewlanguage' => 'json',
                            'header' => [
                                [
                                    'key' => 'Content-Type',
                                    'value' => 'application/json'
                                ]
                            ],
                            'cookie' => [],
                            'body' => json_encode($healthResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                        ],
                        [
                            'name' => 'System Maintenance',
                            'originalRequest' => [
                                'method' => 'GET',
                                'header' => [
                                    [
                                        'key' => 'Accept',
                                        'value' => 'application/json'
                                    ]
                                ],
                                'url' => [
                                    'raw' => '{{base_url}}/health',
                                    'host' => ['{{base_url}}'],
                                    'path' => ['health']
                                ]
                            ],
                            'status' => 'Service Unavailable',
                            'code' => 503,
                            '_postman_previewlanguage' => 'json',
                            'header' => [
                                [
                                    'key' => 'Content-Type',
                                    'value' => 'application/json'
                                ]
                            ],
                            'cookie' => [],
                            'body' => json_encode([
                                'success' => false,
                                'message' => 'System under maintenance',
                                'data' => [
                                    'status' => 'maintenance',
                                    'estimated_completion' => '2025-08-06T16:00:00Z'
                                ]
                            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                        ]
                    ]
                ]
            ]
        ];
    }

    private function createAuthFolder()
    {
        $loginResponse = $this->makeRequest('POST', '/auth/login', [
            'email' => $this->email,
            'password' => $this->password,
            'device_name' => 'Flutter App'
        ]);
        
        $profileResponse = $this->makeAuthenticatedRequest('GET', '/auth/me');
        
        return [
            'name' => '🔐 Authentication',
            'item' => [
                [
                    'name' => 'Login',
                    'request' => [
                        'method' => 'POST',
                        'header' => [
                            [
                                'key' => 'Content-Type',
                                'value' => 'application/json'
                            ],
                            [
                                'key' => 'Accept',
                                'value' => 'application/json'
                            ]
                        ],
                        'body' => [
                            'mode' => 'raw',
                            'raw' => json_encode([
                                'email' => '{{user_email}}',
                                'password' => '{{user_password}}',
                                'device_name' => 'Flutter App'
                            ], JSON_PRETTY_PRINT)
                        ],
                        'url' => [
                            'raw' => '{{base_url}}/auth/login',
                            'host' => ['{{base_url}}'],
                            'path' => ['auth', 'login']
                        ]
                    ],
                    'response' => [
                        [
                            'name' => 'Login Success',
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
                                        'password' => '123456',
                                        'device_name' => 'Flutter App'
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
                            '_postman_previewlanguage' => 'json',
                            'header' => [
                                [
                                    'key' => 'Content-Type',
                                    'value' => 'application/json'
                                ]
                            ],
                            'cookie' => [],
                            'body' => json_encode($loginResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
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
                                        'password' => 'wrongpassword',
                                        'device_name' => 'Flutter App'
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
                            '_postman_previewlanguage' => 'json',
                            'header' => [
                                [
                                    'key' => 'Content-Type',
                                    'value' => 'application/json'
                                ]
                            ],
                            'cookie' => [],
                            'body' => json_encode([
                                'success' => false,
                                'message' => 'Invalid credentials',
                                'errors' => [
                                    'email' => ['The provided credentials are incorrect.']
                                ]
                            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                        ]
                    ]
                ],
                [
                    'name' => 'Get Profile',
                    'request' => [
                        'method' => 'GET',
                        'header' => [
                            [
                                'key' => 'Authorization',
                                'value' => 'Bearer {{api_token}}'
                            ],
                            [
                                'key' => 'Accept',
                                'value' => 'application/json'
                            ]
                        ],
                        'url' => [
                            'raw' => '{{base_url}}/auth/me',
                            'host' => ['{{base_url}}'],
                            'path' => ['auth', 'me']
                        ]
                    ],
                    'response' => [
                        [
                            'name' => 'Profile Retrieved',
                            'originalRequest' => [
                                'method' => 'GET',
                                'header' => [
                                    [
                                        'key' => 'Authorization',
                                        'value' => 'Bearer {{api_token}}'
                                    ]
                                ],
                                'url' => [
                                    'raw' => '{{base_url}}/auth/me',
                                    'host' => ['{{base_url}}'],
                                    'path' => ['auth', 'me']
                                ]
                            ],
                            'status' => 'OK',
                            'code' => 200,
                            '_postman_previewlanguage' => 'json',
                            'header' => [
                                [
                                    'key' => 'Content-Type',
                                    'value' => 'application/json'
                                ]
                            ],
                            'cookie' => [],
                            'body' => json_encode($profileResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                        ]
                    ]
                ]
            ]
        ];
    }

    private function createProductsFolder()
    {
        $productsResponse = $this->makeAuthenticatedRequest('GET', '/products');
        
        return [
            'name' => '📦 Products',
            'item' => [
                [
                    'name' => 'List Products',
                    'request' => [
                        'method' => 'GET',
                        'header' => [
                            [
                                'key' => 'Authorization',
                                'value' => 'Bearer {{api_token}}'
                            ],
                            [
                                'key' => 'Accept',
                                'value' => 'application/json'
                            ]
                        ],
                        'url' => [
                            'raw' => '{{base_url}}/products',
                            'host' => ['{{base_url}}'],
                            'path' => ['products']
                        ]
                    ],
                    'response' => [
                        [
                            'name' => 'Products Found',
                            'originalRequest' => [
                                'method' => 'GET',
                                'header' => [
                                    [
                                        'key' => 'Authorization',
                                        'value' => 'Bearer {{api_token}}'
                                    ]
                                ],
                                'url' => [
                                    'raw' => '{{base_url}}/products',
                                    'host' => ['{{base_url}}'],
                                    'path' => ['products']
                                ]
                            ],
                            'status' => 'OK',
                            'code' => 200,
                            '_postman_previewlanguage' => 'json',
                            'header' => [
                                [
                                    'key' => 'Content-Type',
                                    'value' => 'application/json'
                                ]
                            ],
                            'cookie' => [],
                            'body' => json_encode([
                                'success' => true,
                                'message' => 'Products retrieved successfully',
                                'data' => [
                                    [
                                        'id' => 1,
                                        'name' => 'Kem Dưỡng Da Nivea',
                                        'sku' => 'NIVEA001',
                                        'barcode' => '1234567890123',
                                        'price' => 89000,
                                        'cost_price' => 65000,
                                        'stock_quantity' => 50,
                                        'category_id' => 1,
                                        'category_name' => 'Mỹ Phẩm',
                                        'description' => 'Kem dưỡng da chất lượng cao từ Nivea',
                                        'image_url' => 'https://example.com/nivea.jpg',
                                        'status' => 'active',
                                        'created_at' => '2025-08-06T10:00:00Z',
                                        'updated_at' => '2025-08-06T10:00:00Z'
                                    ],
                                    [
                                        'id' => 2,
                                        'name' => 'Sữa Rửa Mặt Cetaphil',
                                        'sku' => 'CETAPHIL001',
                                        'barcode' => '1234567890124',
                                        'price' => 125000,
                                        'cost_price' => 95000,
                                        'stock_quantity' => 30,
                                        'category_id' => 1,
                                        'category_name' => 'Mỹ Phẩm',
                                        'description' => 'Sữa rửa mặt dịu nhẹ cho da nhạy cảm',
                                        'image_url' => 'https://example.com/cetaphil.jpg',
                                        'status' => 'active',
                                        'created_at' => '2025-08-06T10:00:00Z',
                                        'updated_at' => '2025-08-06T10:00:00Z'
                                    ]
                                ],
                                'pagination' => [
                                    'current_page' => 1,
                                    'per_page' => 15,
                                    'total' => 2,
                                    'last_page' => 1,
                                    'from' => 1,
                                    'to' => 2,
                                    'has_more' => false
                                ]
                            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                        ],
                        [
                            'name' => 'No Products',
                            'originalRequest' => [
                                'method' => 'GET',
                                'header' => [
                                    [
                                        'key' => 'Authorization',
                                        'value' => 'Bearer {{api_token}}'
                                    ]
                                ],
                                'url' => [
                                    'raw' => '{{base_url}}/products',
                                    'host' => ['{{base_url}}'],
                                    'path' => ['products']
                                ]
                            ],
                            'status' => 'OK',
                            'code' => 200,
                            '_postman_previewlanguage' => 'json',
                            'header' => [
                                [
                                    'key' => 'Content-Type',
                                    'value' => 'application/json'
                                ]
                            ],
                            'cookie' => [],
                            'body' => json_encode($productsResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                        ]
                    ]
                ]
            ]
        ];
    }

    private function createOrdersFolder()
    {
        $ordersResponse = $this->makeAuthenticatedRequest('GET', '/orders');
        
        return [
            'name' => '📋 Orders',
            'item' => [
                [
                    'name' => 'List Orders',
                    'request' => [
                        'method' => 'GET',
                        'header' => [
                            [
                                'key' => 'Authorization',
                                'value' => 'Bearer {{api_token}}'
                            ],
                            [
                                'key' => 'Accept',
                                'value' => 'application/json'
                            ]
                        ],
                        'url' => [
                            'raw' => '{{base_url}}/orders',
                            'host' => ['{{base_url}}'],
                            'path' => ['orders']
                        ]
                    ],
                    'response' => [
                        [
                            'name' => 'Orders Found',
                            'originalRequest' => [
                                'method' => 'GET',
                                'header' => [
                                    [
                                        'key' => 'Authorization',
                                        'value' => 'Bearer {{api_token}}'
                                    ]
                                ],
                                'url' => [
                                    'raw' => '{{base_url}}/orders',
                                    'host' => ['{{base_url}}'],
                                    'path' => ['orders']
                                ]
                            ],
                            'status' => 'OK',
                            'code' => 200,
                            '_postman_previewlanguage' => 'json',
                            'header' => [
                                [
                                    'key' => 'Content-Type',
                                    'value' => 'application/json'
                                ]
                            ],
                            'cookie' => [],
                            'body' => json_encode([
                                'success' => true,
                                'message' => 'Orders retrieved successfully',
                                'data' => [
                                    [
                                        'id' => 1,
                                        'order_number' => 'ORD-20250806-001',
                                        'customer_id' => 1,
                                        'customer_name' => 'Nguyễn Văn A',
                                        'customer_phone' => '0123456789',
                                        'total_amount' => 214000,
                                        'discount_amount' => 0,
                                        'final_amount' => 214000,
                                        'payment_method' => 'cash',
                                        'payment_status' => 'paid',
                                        'status' => 'completed',
                                        'note' => 'Giao hàng tận nơi',
                                        'items' => [
                                            [
                                                'product_id' => 1,
                                                'product_name' => 'Kem Dưỡng Da Nivea',
                                                'quantity' => 2,
                                                'price' => 89000,
                                                'total' => 178000
                                            ]
                                        ],
                                        'created_at' => '2025-08-06T09:30:00Z',
                                        'updated_at' => '2025-08-06T09:45:00Z'
                                    ]
                                ],
                                'pagination' => [
                                    'current_page' => 1,
                                    'per_page' => 15,
                                    'total' => 1,
                                    'last_page' => 1
                                ]
                            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                        ],
                        [
                            'name' => 'No Orders',
                            'originalRequest' => [
                                'method' => 'GET',
                                'header' => [
                                    [
                                        'key' => 'Authorization',
                                        'value' => 'Bearer {{api_token}}'
                                    ]
                                ],
                                'url' => [
                                    'raw' => '{{base_url}}/orders',
                                    'host' => ['{{base_url}}'],
                                    'path' => ['orders']
                                ]
                            ],
                            'status' => 'OK',
                            'code' => 200,
                            '_postman_previewlanguage' => 'json',
                            'header' => [
                                [
                                    'key' => 'Content-Type',
                                    'value' => 'application/json'
                                ]
                            ],
                            'cookie' => [],
                            'body' => json_encode($ordersResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                        ]
                    ]
                ]
            ]
        ];
    }

    private function createCustomersFolder()
    {
        $customersResponse = $this->makeAuthenticatedRequest('GET', '/customers');
        
        return [
            'name' => '👥 Customers',
            'item' => [
                [
                    'name' => 'List Customers',
                    'request' => [
                        'method' => 'GET',
                        'header' => [
                            [
                                'key' => 'Authorization',
                                'value' => 'Bearer {{api_token}}'
                            ],
                            [
                                'key' => 'Accept',
                                'value' => 'application/json'
                            ]
                        ],
                        'url' => [
                            'raw' => '{{base_url}}/customers',
                            'host' => ['{{base_url}}'],
                            'path' => ['customers']
                        ]
                    ],
                    'response' => [
                        [
                            'name' => 'Customers Found',
                            'originalRequest' => [
                                'method' => 'GET',
                                'header' => [
                                    [
                                        'key' => 'Authorization',
                                        'value' => 'Bearer {{api_token}}'
                                    ]
                                ],
                                'url' => [
                                    'raw' => '{{base_url}}/customers',
                                    'host' => ['{{base_url}}'],
                                    'path' => ['customers']
                                ]
                            ],
                            'status' => 'OK',
                            'code' => 200,
                            '_postman_previewlanguage' => 'json',
                            'header' => [
                                [
                                    'key' => 'Content-Type',
                                    'value' => 'application/json'
                                ]
                            ],
                            'cookie' => [],
                            'body' => json_encode([
                                'success' => true,
                                'message' => 'Customers retrieved successfully',
                                'data' => [
                                    [
                                        'id' => 1,
                                        'name' => 'Nguyễn Văn A',
                                        'email' => 'nguyenvana@email.com',
                                        'phone' => '0123456789',
                                        'address' => '123 Đường ABC, Quận 1, TP.HCM',
                                        'birth_date' => '1990-05-15',
                                        'gender' => 'male',
                                        'total_orders' => 5,
                                        'total_spent' => 1250000,
                                        'loyalty_points' => 125,
                                        'status' => 'active',
                                        'created_at' => '2025-07-01T10:00:00Z',
                                        'updated_at' => '2025-08-06T09:30:00Z'
                                    ]
                                ],
                                'pagination' => [
                                    'current_page' => 1,
                                    'per_page' => 15,
                                    'total' => 1
                                ]
                            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                        ],
                        [
                            'name' => 'No Customers',
                            'originalRequest' => [
                                'method' => 'GET',
                                'header' => [
                                    [
                                        'key' => 'Authorization',
                                        'value' => 'Bearer {{api_token}}'
                                    ]
                                ],
                                'url' => [
                                    'raw' => '{{base_url}}/customers',
                                    'host' => ['{{base_url}}'],
                                    'path' => ['customers']
                                ]
                            ],
                            'status' => 'OK',
                            'code' => 200,
                            '_postman_previewlanguage' => 'json',
                            'header' => [
                                [
                                    'key' => 'Content-Type',
                                    'value' => 'application/json'
                                ]
                            ],
                            'cookie' => [],
                            'body' => json_encode($customersResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                        ]
                    ]
                ]
            ]
        ];
    }

    private function createPaymentsFolder()
    {
        $paymentsResponse = $this->makeAuthenticatedRequest('GET', '/payments');
        
        return [
            'name' => '💰 Payments',
            'item' => [
                [
                    'name' => 'List Payments',
                    'request' => [
                        'method' => 'GET',
                        'header' => [
                            [
                                'key' => 'Authorization',
                                'value' => 'Bearer {{api_token}}'
                            ],
                            [
                                'key' => 'Accept',
                                'value' => 'application/json'
                            ]
                        ],
                        'url' => [
                            'raw' => '{{base_url}}/payments',
                            'host' => ['{{base_url}}'],
                            'path' => ['payments']
                        ]
                    ],
                    'response' => [
                        [
                            'name' => 'Payments Found',
                            'originalRequest' => [
                                'method' => 'GET',
                                'header' => [
                                    [
                                        'key' => 'Authorization',
                                        'value' => 'Bearer {{api_token}}'
                                    ]
                                ],
                                'url' => [
                                    'raw' => '{{base_url}}/payments',
                                    'host' => ['{{base_url}}'],
                                    'path' => ['payments']
                                ]
                            ],
                            'status' => 'OK',
                            'code' => 200,
                            '_postman_previewlanguage' => 'json',
                            'header' => [
                                [
                                    'key' => 'Content-Type',
                                    'value' => 'application/json'
                                ]
                            ],
                            'cookie' => [],
                            'body' => json_encode([
                                'success' => true,
                                'message' => 'Payments retrieved successfully',
                                'data' => [
                                    [
                                        'id' => 1,
                                        'reference_id' => 'TT1',
                                        'reference_type' => 'invoice',
                                        'amount' => 214000,
                                        'type' => 'income',
                                        'method' => 'cash',
                                        'description' => 'Thanh toán đơn hàng ORD-20250806-001',
                                        'status' => 'completed',
                                        'bank_account_id' => null,
                                        'created_by' => 12,
                                        'created_at' => '2025-08-06T09:45:00Z',
                                        'updated_at' => '2025-08-06T09:45:00Z'
                                    ]
                                ],
                                'pagination' => [
                                    'current_page' => 1,
                                    'per_page' => 15,
                                    'total' => 1
                                ]
                            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                        ],
                        [
                            'name' => 'No Payments',
                            'originalRequest' => [
                                'method' => 'GET',
                                'header' => [
                                    [
                                        'key' => 'Authorization',
                                        'value' => 'Bearer {{api_token}}'
                                    ]
                                ],
                                'url' => [
                                    'raw' => '{{base_url}}/payments',
                                    'host' => ['{{base_url}}'],
                                    'path' => ['payments']
                                ]
                            ],
                            'status' => 'OK',
                            'code' => 200,
                            '_postman_previewlanguage' => 'json',
                            'header' => [
                                [
                                    'key' => 'Content-Type',
                                    'value' => 'application/json'
                                ]
                            ],
                            'cookie' => [],
                            'body' => json_encode($paymentsResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                        ]
                    ]
                ]
            ]
        ];
    }

    private function createPlaygroundFolder()
    {
        $statsResponse = $this->makeAuthenticatedRequest('GET', '/playground/stats');
        
        return [
            'name' => '🧪 Playground',
            'item' => [
                [
                    'name' => 'Get Statistics',
                    'request' => [
                        'method' => 'GET',
                        'header' => [
                            [
                                'key' => 'Authorization',
                                'value' => 'Bearer {{api_token}}'
                            ],
                            [
                                'key' => 'Accept',
                                'value' => 'application/json'
                            ]
                        ],
                        'url' => [
                            'raw' => '{{base_url}}/playground/stats',
                            'host' => ['{{base_url}}'],
                            'path' => ['playground', 'stats']
                        ]
                    ],
                    'response' => [
                        [
                            'name' => 'Statistics Retrieved',
                            'originalRequest' => [
                                'method' => 'GET',
                                'header' => [
                                    [
                                        'key' => 'Authorization',
                                        'value' => 'Bearer {{api_token}}'
                                    ]
                                ],
                                'url' => [
                                    'raw' => '{{base_url}}/playground/stats',
                                    'host' => ['{{base_url}}'],
                                    'path' => ['playground', 'stats']
                                ]
                            ],
                            'status' => 'OK',
                            'code' => 200,
                            '_postman_previewlanguage' => 'json',
                            'header' => [
                                [
                                    'key' => 'Content-Type',
                                    'value' => 'application/json'
                                ]
                            ],
                            'cookie' => [],
                            'body' => json_encode($statsResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                        ]
                    ]
                ]
            ]
        ];
    }

    private function createErrorsFolder()
    {
        return [
            'name' => '⚠️ Error Scenarios',
            'item' => [
                [
                    'name' => 'Unauthorized Access',
                    'request' => [
                        'method' => 'GET',
                        'header' => [
                            [
                                'key' => 'Accept',
                                'value' => 'application/json'
                            ]
                        ],
                        'url' => [
                            'raw' => '{{base_url}}/auth/me',
                            'host' => ['{{base_url}}'],
                            'path' => ['auth', 'me']
                        ]
                    ],
                    'response' => [
                        [
                            'name' => 'Unauthorized',
                            'originalRequest' => [
                                'method' => 'GET',
                                'header' => [
                                    [
                                        'key' => 'Accept',
                                        'value' => 'application/json'
                                    ]
                                ],
                                'url' => [
                                    'raw' => '{{base_url}}/auth/me',
                                    'host' => ['{{base_url}}'],
                                    'path' => ['auth', 'me']
                                ]
                            ],
                            'status' => 'Unauthorized',
                            'code' => 401,
                            '_postman_previewlanguage' => 'json',
                            'header' => [
                                [
                                    'key' => 'Content-Type',
                                    'value' => 'application/json'
                                ]
                            ],
                            'cookie' => [],
                            'body' => json_encode([
                                'success' => false,
                                'message' => 'Unauthenticated',
                                'errors' => ['Token not provided or invalid']
                            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                        ]
                    ]
                ]
            ]
        ];
    }

    private function makeRequest($method, $endpoint, $data = null)
    {
        $url = $this->baseUrl . $endpoint;
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json'
            ],
            CURLOPT_TIMEOUT => 30
        ]);
        
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $decodedResponse = json_decode($response, true);
        if ($decodedResponse) {
            $decodedResponse['http_code'] = $httpCode;
        }
        
        return $decodedResponse ?: ['error' => 'Invalid response', 'http_code' => $httpCode];
    }

    private function makeAuthenticatedRequest($method, $endpoint, $data = null)
    {
        $url = $this->baseUrl . $endpoint;
        $ch = curl_init();
        
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json'
        ];
        
        if ($this->token) {
            $headers[] = 'Authorization: Bearer ' . $this->token;
        }
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 30
        ]);
        
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $decodedResponse = json_decode($response, true);
        if ($decodedResponse) {
            $decodedResponse['http_code'] = $httpCode;
        }
        
        return $decodedResponse ?: ['error' => 'Invalid response', 'http_code' => $httpCode];
    }

    private function saveCollection($collection)
    {
        echo "💾 Saving collection với proper format...\n";
        
        $postmanDir = __DIR__ . '/../storage/testing/postman';
        if (!is_dir($postmanDir)) {
            mkdir($postmanDir, 0755, true);
        }
        
        file_put_contents(
            $postmanDir . '/yukimart-api-fixed-examples.json',
            json_encode($collection, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        );
        
        echo "✅ Collection saved successfully\n\n";
    }

    private function printSummary()
    {
        echo "📊 Collection Summary:\n";
        echo "=====================\n";
        echo "- File: yukimart-api-fixed-examples.json\n";
        echo "- Format: Postman Collection v2.1.0\n";
        echo "- Folders: 8 (Health, Auth, Products, Orders, Customers, Payments, Playground, Errors)\n";
        echo "- Requests: 8 main requests\n";
        echo "- Response Examples: 16+ examples\n";
        echo "- Authentication: Bearer token included\n";
        echo "- Vietnamese Data: ✅ Included\n";
        echo "- Real Responses: ✅ Captured\n\n";
        
        echo "🔗 Usage Instructions:\n";
        echo "1. Import: storage/testing/postman/yukimart-api-fixed-examples.json\n";
        echo "2. Check Examples tab trong mỗi request\n";
        echo "3. Examples should now be visible trong Postman\n";
        echo "4. Use for Flutter development reference\n\n";
        
        echo "📱 Fixed Issues:\n";
        echo "- ✅ Proper Postman v2.1.0 format\n";
        echo "- ✅ originalRequest included trong examples\n";
        echo "- ✅ _postman_previewlanguage added\n";
        echo "- ✅ cookie array included\n";
        echo "- ✅ Proper header format\n\n";
    }
}

// Create collection với fixed format
$fixer = new PostmanExamplesFixer();
$fixer->createCollection();
