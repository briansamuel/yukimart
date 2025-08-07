<?php

/**
 * Add Comprehensive Example Responses to Postman Collection
 * Creates realistic response examples for all endpoints
 */

require_once __DIR__ . '/../vendor/autoload.php';

class ExampleResponseGenerator
{
    private $baseUrl = 'http://yukimart.local/api/v1';
    private $email = 'yukimart@gmail.com';
    private $password = '123456';
    private $token = null;
    private $postmanCollection = [];
    private $mockData = [];

    public function __construct()
    {
        echo "🚀 Adding Comprehensive Example Responses\n";
        echo "=========================================\n\n";
        
        $this->initializeMockData();
    }

    public function generateExamples()
    {
        try {
            // 1. Load existing collection
            $this->loadExistingCollection();
            
            // 2. Get authentication token
            $this->authenticate();
            
            // 3. Add comprehensive examples
            $this->addHealthExamples();
            $this->addAuthenticationExamples();
            $this->addProductExamples();
            $this->addOrderExamples();
            $this->addCustomerExamples();
            $this->addPaymentExamples();
            $this->addPlaygroundExamples();
            $this->addErrorExamples();
            
            // 4. Save enhanced collection
            $this->saveEnhancedCollection();
            
            echo "\n🎉 Example Responses Added Successfully!\n";
            echo "=======================================\n\n";
            $this->printSummary();
            
        } catch (Exception $e) {
            echo "❌ Error: " . $e->getMessage() . "\n";
        }
    }

    private function loadExistingCollection()
    {
        echo "📂 Loading existing collection...\n";
        
        $collectionPath = __DIR__ . '/../storage/testing/postman/yukimart-api-flutter-ready.json';
        if (file_exists($collectionPath)) {
            $this->postmanCollection = json_decode(file_get_contents($collectionPath), true);
            echo "✅ Collection loaded successfully\n\n";
        } else {
            throw new Exception("Collection file not found");
        }
    }

    private function authenticate()
    {
        echo "🔐 Getting authentication token...\n";
        
        $response = $this->makeRequest('POST', '/auth/login', [
            'email' => $this->email,
            'password' => $this->password,
            'device_name' => 'Example Response Generator'
        ]);
        
        if ($response['success'] && isset($response['data']['token'])) {
            $this->token = $response['data']['token'];
            echo "✅ Token obtained successfully\n\n";
        } else {
            throw new Exception("Authentication failed");
        }
    }

    private function addHealthExamples()
    {
        echo "🏥 Adding Health Check examples...\n";
        
        // Get real health response
        $healthResponse = $this->makeRequest('GET', '/health');
        
        $this->addResponseToRequest('🏥 Health Check', 'Health Check', [
            [
                'name' => 'Healthy System',
                'status' => 'OK',
                'code' => 200,
                'body' => json_encode($healthResponse, JSON_PRETTY_PRINT)
            ],
            [
                'name' => 'System Maintenance',
                'status' => 'Service Unavailable',
                'code' => 503,
                'body' => json_encode([
                    'success' => false,
                    'message' => 'System under maintenance',
                    'data' => [
                        'status' => 'maintenance',
                        'estimated_completion' => '2025-08-06T16:00:00Z'
                    ]
                ], JSON_PRETTY_PRINT)
            ]
        ]);
        
        echo "✅ Health examples added\n\n";
    }

    private function addAuthenticationExamples()
    {
        echo "🔐 Adding Authentication examples...\n";
        
        // Login Success
        $loginResponse = $this->makeRequest('POST', '/auth/login', [
            'email' => $this->email,
            'password' => $this->password,
            'device_name' => 'Flutter App'
        ]);
        
        $this->addResponseToRequest('🔐 Authentication', 'Login', [
            [
                'name' => 'Login Success',
                'status' => 'OK',
                'code' => 200,
                'body' => json_encode($loginResponse, JSON_PRETTY_PRINT)
            ],
            [
                'name' => 'Invalid Credentials',
                'status' => 'Unauthorized',
                'code' => 401,
                'body' => json_encode([
                    'success' => false,
                    'message' => 'Invalid credentials',
                    'errors' => [
                        'email' => ['The provided credentials are incorrect.']
                    ]
                ], JSON_PRETTY_PRINT)
            ],
            [
                'name' => 'Validation Error',
                'status' => 'Unprocessable Entity',
                'code' => 422,
                'body' => json_encode([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => [
                        'email' => ['The email field is required.'],
                        'password' => ['The password field is required.']
                    ]
                ], JSON_PRETTY_PRINT)
            ]
        ]);
        
        // Profile Success
        $profileResponse = $this->makeAuthenticatedRequest('GET', '/auth/me');
        
        $this->addResponseToRequest('🔐 Authentication', 'Get Profile', [
            [
                'name' => 'Profile Retrieved',
                'status' => 'OK',
                'code' => 200,
                'body' => json_encode($profileResponse, JSON_PRETTY_PRINT)
            ],
            [
                'name' => 'Unauthorized',
                'status' => 'Unauthorized',
                'code' => 401,
                'body' => json_encode([
                    'success' => false,
                    'message' => 'Unauthenticated',
                    'errors' => ['Token not provided or invalid']
                ], JSON_PRETTY_PRINT)
            ]
        ]);
        
        echo "✅ Authentication examples added\n\n";
    }

    private function addProductExamples()
    {
        echo "📦 Adding Product examples...\n";
        
        // Products List (empty)
        $productsResponse = $this->makeAuthenticatedRequest('GET', '/products');
        
        // Create mock products response với data
        $productsWithDataResponse = [
            'success' => true,
            'message' => 'Products retrieved successfully',
            'data' => $this->mockData['products'],
            'pagination' => [
                'current_page' => 1,
                'per_page' => 15,
                'total' => 3,
                'last_page' => 1,
                'from' => 1,
                'to' => 3,
                'has_more' => false
            ]
        ];
        
        $this->addResponseToRequest('📦 Products', 'List Products', [
            [
                'name' => 'Products Found',
                'status' => 'OK',
                'code' => 200,
                'body' => json_encode($productsWithDataResponse, JSON_PRETTY_PRINT)
            ],
            [
                'name' => 'No Products',
                'status' => 'OK',
                'code' => 200,
                'body' => json_encode($productsResponse, JSON_PRETTY_PRINT)
            ]
        ]);
        
        // Product Detail
        $this->addResponseToRequest('📦 Products', 'Get Product by ID', [
            [
                'name' => 'Product Found',
                'status' => 'OK',
                'code' => 200,
                'body' => json_encode([
                    'success' => true,
                    'message' => 'Product retrieved successfully',
                    'data' => $this->mockData['products'][0]
                ], JSON_PRETTY_PRINT)
            ],
            [
                'name' => 'Product Not Found',
                'status' => 'Not Found',
                'code' => 404,
                'body' => json_encode([
                    'success' => false,
                    'message' => 'Product not found',
                    'errors' => ['Product with ID 99999 does not exist']
                ], JSON_PRETTY_PRINT)
            ]
        ]);
        
        echo "✅ Product examples added\n\n";
    }

    private function addOrderExamples()
    {
        echo "📋 Adding Order examples...\n";
        
        // Orders List
        $ordersResponse = $this->makeAuthenticatedRequest('GET', '/orders');
        
        $ordersWithDataResponse = [
            'success' => true,
            'message' => 'Orders retrieved successfully',
            'data' => $this->mockData['orders'],
            'pagination' => [
                'current_page' => 1,
                'per_page' => 15,
                'total' => 2,
                'last_page' => 1
            ]
        ];
        
        $this->addResponseToRequest('📋 Orders', 'List Orders', [
            [
                'name' => 'Orders Found',
                'status' => 'OK',
                'code' => 200,
                'body' => json_encode($ordersWithDataResponse, JSON_PRETTY_PRINT)
            ],
            [
                'name' => 'No Orders',
                'status' => 'OK',
                'code' => 200,
                'body' => json_encode($ordersResponse, JSON_PRETTY_PRINT)
            ]
        ]);
        
        // Create Order
        $this->addResponseToRequest('📋 Orders', 'Create Order', [
            [
                'name' => 'Order Created',
                'status' => 'Created',
                'code' => 201,
                'body' => json_encode([
                    'success' => true,
                    'message' => 'Order created successfully',
                    'data' => $this->mockData['orders'][0]
                ], JSON_PRETTY_PRINT)
            ],
            [
                'name' => 'Validation Error',
                'status' => 'Unprocessable Entity',
                'code' => 422,
                'body' => json_encode([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => [
                        'items' => ['At least one item is required'],
                        'items.0.product_id' => ['The product does not exist']
                    ]
                ], JSON_PRETTY_PRINT)
            ]
        ]);
        
        echo "✅ Order examples added\n\n";
    }

    private function addCustomerExamples()
    {
        echo "👥 Adding Customer examples...\n";
        
        $customersResponse = $this->makeAuthenticatedRequest('GET', '/customers');
        
        $customersWithDataResponse = [
            'success' => true,
            'message' => 'Customers retrieved successfully',
            'data' => $this->mockData['customers'],
            'pagination' => [
                'current_page' => 1,
                'per_page' => 15,
                'total' => 2
            ]
        ];
        
        $this->addResponseToRequest('👥 Customers', 'List Customers', [
            [
                'name' => 'Customers Found',
                'status' => 'OK',
                'code' => 200,
                'body' => json_encode($customersWithDataResponse, JSON_PRETTY_PRINT)
            ],
            [
                'name' => 'No Customers',
                'status' => 'OK',
                'code' => 200,
                'body' => json_encode($customersResponse, JSON_PRETTY_PRINT)
            ]
        ]);
        
        echo "✅ Customer examples added\n\n";
    }

    private function addPaymentExamples()
    {
        echo "💰 Adding Payment examples...\n";
        
        $paymentsResponse = $this->makeAuthenticatedRequest('GET', '/payments');
        
        $paymentsWithDataResponse = [
            'success' => true,
            'message' => 'Payments retrieved successfully',
            'data' => $this->mockData['payments'],
            'pagination' => [
                'current_page' => 1,
                'per_page' => 15,
                'total' => 3
            ]
        ];
        
        $this->addResponseToRequest('💰 Payments', 'List Payments', [
            [
                'name' => 'Payments Found',
                'status' => 'OK',
                'code' => 200,
                'body' => json_encode($paymentsWithDataResponse, JSON_PRETTY_PRINT)
            ],
            [
                'name' => 'No Payments',
                'status' => 'OK',
                'code' => 200,
                'body' => json_encode($paymentsResponse, JSON_PRETTY_PRINT)
            ]
        ]);
        
        echo "✅ Payment examples added\n\n";
    }

    private function addPlaygroundExamples()
    {
        echo "🧪 Adding Playground examples...\n";
        
        $statsResponse = $this->makeAuthenticatedRequest('GET', '/playground/stats');
        
        $this->addResponseToRequest('🧪 Playground', 'Get Statistics', [
            [
                'name' => 'Statistics Retrieved',
                'status' => 'OK',
                'code' => 200,
                'body' => json_encode($statsResponse, JSON_PRETTY_PRINT)
            ]
        ]);
        
        echo "✅ Playground examples added\n\n";
    }

    private function addErrorExamples()
    {
        echo "⚠️ Adding Error examples...\n";
        
        // Unauthorized access
        $unauthorizedResponse = $this->makeRequest('GET', '/auth/me');
        
        $this->addResponseToRequest('⚠️ Error Scenarios', 'Unauthorized Access', [
            [
                'name' => 'Unauthorized',
                'status' => 'Unauthorized',
                'code' => 401,
                'body' => json_encode($unauthorizedResponse, JSON_PRETTY_PRINT)
            ]
        ]);
        
        echo "✅ Error examples added\n\n";
    }

    private function initializeMockData()
    {
        $this->mockData = [
            'products' => [
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
                ],
                [
                    'id' => 3,
                    'name' => 'Nước Hoa Chanel No.5',
                    'sku' => 'CHANEL001',
                    'barcode' => '1234567890125',
                    'price' => 2500000,
                    'cost_price' => 1800000,
                    'stock_quantity' => 5,
                    'category_id' => 2,
                    'category_name' => 'Nước Hoa',
                    'description' => 'Nước hoa cao cấp từ Chanel',
                    'image_url' => 'https://example.com/chanel.jpg',
                    'status' => 'active',
                    'created_at' => '2025-08-06T10:00:00Z',
                    'updated_at' => '2025-08-06T10:00:00Z'
                ]
            ],
            'orders' => [
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
                        ],
                        [
                            'product_id' => 3,
                            'product_name' => 'Nước Hoa Chanel No.5',
                            'quantity' => 1,
                            'price' => 36000,
                            'total' => 36000
                        ]
                    ],
                    'created_at' => '2025-08-06T09:30:00Z',
                    'updated_at' => '2025-08-06T09:45:00Z'
                ],
                [
                    'id' => 2,
                    'order_number' => 'ORD-20250806-002',
                    'customer_id' => 0,
                    'customer_name' => 'Khách lẻ',
                    'customer_phone' => null,
                    'total_amount' => 125000,
                    'discount_amount' => 5000,
                    'final_amount' => 120000,
                    'payment_method' => 'card',
                    'payment_status' => 'paid',
                    'status' => 'processing',
                    'note' => null,
                    'items' => [
                        [
                            'product_id' => 2,
                            'product_name' => 'Sữa Rửa Mặt Cetaphil',
                            'quantity' => 1,
                            'price' => 125000,
                            'total' => 125000
                        ]
                    ],
                    'created_at' => '2025-08-06T11:15:00Z',
                    'updated_at' => '2025-08-06T11:15:00Z'
                ]
            ],
            'customers' => [
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
                ],
                [
                    'id' => 2,
                    'name' => 'Trần Thị B',
                    'email' => 'tranthib@email.com',
                    'phone' => '0987654321',
                    'address' => '456 Đường XYZ, Quận 3, TP.HCM',
                    'birth_date' => '1985-12-20',
                    'gender' => 'female',
                    'total_orders' => 3,
                    'total_spent' => 890000,
                    'loyalty_points' => 89,
                    'status' => 'active',
                    'created_at' => '2025-07-15T14:30:00Z',
                    'updated_at' => '2025-08-05T16:20:00Z'
                ]
            ],
            'payments' => [
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
                ],
                [
                    'id' => 2,
                    'reference_id' => 'TT2',
                    'reference_type' => 'invoice',
                    'amount' => 120000,
                    'type' => 'income',
                    'method' => 'card',
                    'description' => 'Thanh toán đơn hàng ORD-20250806-002',
                    'status' => 'completed',
                    'bank_account_id' => 1,
                    'created_by' => 12,
                    'created_at' => '2025-08-06T11:15:00Z',
                    'updated_at' => '2025-08-06T11:15:00Z'
                ],
                [
                    'id' => 3,
                    'reference_id' => 'EXP001',
                    'reference_type' => 'expense',
                    'amount' => 500000,
                    'type' => 'expense',
                    'method' => 'transfer',
                    'description' => 'Mua hàng từ nhà cung cấp',
                    'status' => 'completed',
                    'bank_account_id' => 1,
                    'created_by' => 12,
                    'created_at' => '2025-08-06T08:00:00Z',
                    'updated_at' => '2025-08-06T08:00:00Z'
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

    private function addResponseToRequest($folderName, $requestName, $responses)
    {
        foreach ($this->postmanCollection['item'] as &$folder) {
            if ($folder['name'] === $folderName) {
                foreach ($folder['item'] as &$request) {
                    if ($request['name'] === $requestName) {
                        $request['response'] = [];
                        foreach ($responses as $response) {
                            $request['response'][] = [
                                'name' => $response['name'],
                                'status' => $response['status'],
                                'code' => $response['code'],
                                'body' => $response['body'],
                                'header' => [
                                    [
                                        'key' => 'Content-Type',
                                        'value' => 'application/json'
                                    ]
                                ]
                            ];
                        }
                        break;
                    }
                }
                break;
            }
        }
    }

    private function saveEnhancedCollection()
    {
        echo "💾 Saving enhanced collection...\n";
        
        // Update token in collection
        if ($this->token) {
            foreach ($this->postmanCollection['variable'] as &$variable) {
                if ($variable['key'] === 'api_token') {
                    $variable['value'] = $this->token;
                    break;
                }
            }
        }
        
        // Save enhanced collection
        $postmanDir = __DIR__ . '/../storage/testing/postman';
        file_put_contents(
            $postmanDir . '/yukimart-api-with-examples.json',
            json_encode($this->postmanCollection, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
        
        echo "✅ Enhanced collection saved\n\n";
    }

    private function printSummary()
    {
        $totalRequests = 0;
        $totalResponses = 0;
        
        foreach ($this->postmanCollection['item'] as $folder) {
            $totalRequests += count($folder['item']);
            foreach ($folder['item'] as $request) {
                if (isset($request['response'])) {
                    $totalResponses += count($request['response']);
                }
            }
        }
        
        echo "📊 Enhanced Collection Summary:\n";
        echo "==============================\n";
        echo "- Total Folders: " . count($this->postmanCollection['item']) . "\n";
        echo "- Total Requests: {$totalRequests}\n";
        echo "- Total Response Examples: {$totalResponses}\n";
        echo "- Authentication Token: " . ($this->token ? 'Updated' : 'Failed') . "\n";
        echo "- Mock Data: ✅ Realistic examples included\n";
        echo "- Error Scenarios: ✅ Comprehensive coverage\n\n";
        
        echo "🔗 Usage Instructions:\n";
        echo "1. Import: storage/testing/postman/yukimart-api-with-examples.json\n";
        echo "2. Review response examples for each endpoint\n";
        echo "3. Use examples for Flutter development reference\n";
        echo "4. Test both success and error scenarios\n\n";
        
        echo "📱 Perfect for Flutter Development!\n";
        echo "- Real API response examples\n";
        echo "- Realistic mock data included\n";
        echo "- Error handling patterns\n";
        echo "- Multiple response scenarios\n\n";
    }
}

// Generate enhanced examples
$generator = new ExampleResponseGenerator();
$generator->generateExamples();
