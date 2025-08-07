<?php

/**
 * Sync Real API Responses to Postman Collection
 * Updates collection with all real response examples
 */

require_once __DIR__ . '/../vendor/autoload.php';

class ResponseSyncService
{
    private $baseUrl = 'http://yukimart.local/api/v1';
    private $email = 'yukimart@gmail.com';
    private $password = '123456';
    private $token = null;
    private $postmanCollection = [];
    private $realResponses = [];

    public function __construct()
    {
        echo "🔄 Syncing Real Responses to Postman Collection\n";
        echo "===============================================\n\n";
    }

    public function syncResponses()
    {
        try {
            // 1. Load existing collection
            $this->loadCollection();
            
            // 2. Load real responses
            $this->loadRealResponses();
            
            // 3. Get fresh authentication
            $this->authenticate();
            
            // 4. Capture fresh responses
            $this->captureFreshResponses();
            
            // 5. Sync all responses to collection
            $this->syncAllResponsesToCollection();
            
            // 6. Save updated collection
            $this->saveUpdatedCollection();
            
            echo "\n🎉 Response Sync Completed Successfully!\n";
            echo "=======================================\n\n";
            $this->printSummary();
            
        } catch (Exception $e) {
            echo "❌ Error: " . $e->getMessage() . "\n";
        }
    }

    private function loadCollection()
    {
        echo "📂 Loading Postman collection...\n";
        
        $collectionPath = __DIR__ . '/../storage/testing/postman/yukimart-api-flutter-ready.json';
        if (file_exists($collectionPath)) {
            $this->postmanCollection = json_decode(file_get_contents($collectionPath), true);
            echo "✅ Collection loaded successfully\n\n";
        } else {
            throw new Exception("Collection file not found");
        }
    }

    private function loadRealResponses()
    {
        echo "📊 Loading real responses...\n";
        
        $responsesPath = __DIR__ . '/../storage/testing/comprehensive_api_responses.json';
        if (file_exists($responsesPath)) {
            $this->realResponses = json_decode(file_get_contents($responsesPath), true);
            echo "✅ Real responses loaded: " . count($this->realResponses) . " responses\n\n";
        } else {
            echo "⚠️ No existing responses found, will capture fresh ones\n\n";
            $this->realResponses = [];
        }
    }

    private function authenticate()
    {
        echo "🔐 Getting authentication token...\n";
        
        $response = $this->makeRequest('POST', '/auth/login', [
            'email' => $this->email,
            'password' => $this->password,
            'device_name' => 'Response Sync Service'
        ]);
        
        if ($response['success'] && isset($response['data']['token'])) {
            $this->token = $response['data']['token'];
            echo "✅ Token obtained successfully\n\n";
        } else {
            throw new Exception("Authentication failed");
        }
    }

    private function captureFreshResponses()
    {
        echo "📡 Capturing fresh API responses...\n";
        
        // Health Check
        $this->captureResponse('health_check', 'GET', '/health');
        
        // Authentication
        $this->captureResponse('auth_login', 'POST', '/auth/login', [
            'email' => $this->email,
            'password' => $this->password,
            'device_name' => 'Flutter App'
        ]);
        
        $this->captureResponse('auth_profile', 'GET', '/auth/me', null, true);
        $this->captureResponse('auth_update_profile', 'PUT', '/auth/profile', [
            'full_name' => 'YukiMart Admin Updated',
            'phone' => '0987654321'
        ], true);
        
        // Products
        $this->captureResponse('products_list', 'GET', '/products', null, true);
        $this->captureResponse('products_search', 'GET', '/products?search=kem', null, true);
        $this->captureResponse('products_pagination', 'GET', '/products?page=1&per_page=15', null, true);
        
        // Orders
        $this->captureResponse('orders_list', 'GET', '/orders', null, true);
        
        // Customers
        $this->captureResponse('customers_list', 'GET', '/customers', null, true);
        
        // Payments
        $this->captureResponse('payments_list', 'GET', '/payments', null, true);
        
        // Playground
        $this->captureResponse('playground_stats', 'GET', '/playground/stats', null, true);
        
        // Error scenarios
        $this->captureResponse('unauthorized_access', 'GET', '/auth/me');
        
        echo "✅ Fresh responses captured\n\n";
    }

    private function captureResponse($key, $method, $endpoint, $data = null, $authenticated = false)
    {
        if ($authenticated) {
            $response = $this->makeAuthenticatedRequest($method, $endpoint, $data);
        } else {
            $response = $this->makeRequest($method, $endpoint, $data);
        }
        
        $this->realResponses[$key] = [
            'response' => $response,
            'timestamp' => date('Y-m-d H:i:s'),
            'http_code' => $response['http_code'] ?? 200
        ];
    }

    private function syncAllResponsesToCollection()
    {
        echo "🔄 Syncing responses to collection...\n";
        
        // Health Check
        $this->addResponseToRequest('🏥 Health Check', 'Health Check', [
            $this->createResponseExample('Healthy System', 200, $this->realResponses['health_check']['response']),
            $this->createResponseExample('System Maintenance', 503, [
                'success' => false,
                'message' => 'System under maintenance',
                'data' => [
                    'status' => 'maintenance',
                    'estimated_completion' => '2025-08-06T16:00:00Z'
                ]
            ])
        ]);
        
        // Authentication - Login
        $this->addResponseToRequest('🔐 Authentication', 'Login', [
            $this->createResponseExample('Login Success', 200, $this->realResponses['auth_login']['response']),
            $this->createResponseExample('Invalid Credentials', 401, [
                'success' => false,
                'message' => 'Invalid credentials',
                'errors' => ['email' => ['The provided credentials are incorrect.']]
            ]),
            $this->createResponseExample('Validation Error', 422, [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => [
                    'email' => ['The email field is required.'],
                    'password' => ['The password field is required.']
                ]
            ])
        ]);
        
        // Authentication - Profile
        $this->addResponseToRequest('🔐 Authentication', 'Get Profile', [
            $this->createResponseExample('Profile Retrieved', 200, $this->realResponses['auth_profile']['response']),
            $this->createResponseExample('Unauthorized', 401, [
                'success' => false,
                'message' => 'Unauthenticated',
                'errors' => ['Token not provided or invalid']
            ])
        ]);
        
        // Authentication - Update Profile
        $this->addResponseToRequest('🔐 Authentication', 'Update Profile', [
            $this->createResponseExample('Profile Updated', 200, $this->realResponses['auth_update_profile']['response']),
            $this->createResponseExample('Validation Error', 422, [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => [
                    'phone' => ['The phone format is invalid.']
                ]
            ])
        ]);
        
        // Authentication - Logout
        $this->addResponseToRequest('🔐 Authentication', 'Logout', [
            $this->createResponseExample('Logout Success', 200, [
                'success' => true,
                'message' => 'Logout successful',
                'data' => null
            ])
        ]);
        
        // Products - List
        $this->addResponseToRequest('📦 Products', 'List Products', [
            $this->createResponseExample('Products Found', 200, $this->createProductsWithDataResponse()),
            $this->createResponseExample('No Products', 200, $this->realResponses['products_list']['response'])
        ]);
        
        // Products - Search
        $this->addResponseToRequest('📦 Products', 'Search Products', [
            $this->createResponseExample('Search Results', 200, $this->realResponses['products_search']['response']),
            $this->createResponseExample('No Results', 200, [
                'success' => true,
                'message' => 'Products retrieved successfully',
                'data' => [],
                'pagination' => [
                    'current_page' => 1,
                    'per_page' => 15,
                    'total' => 0
                ]
            ])
        ]);
        
        // Products - Pagination
        $this->addResponseToRequest('📦 Products', 'Products with Pagination', [
            $this->createResponseExample('Paginated Results', 200, $this->realResponses['products_pagination']['response'])
        ]);
        
        // Products - By Barcode
        $this->addResponseToRequest('📦 Products', 'Get Product by Barcode', [
            $this->createResponseExample('Product Found', 200, [
                'success' => true,
                'message' => 'Product retrieved successfully',
                'data' => $this->getMockProduct()
            ]),
            $this->createResponseExample('Product Not Found', 404, [
                'success' => false,
                'message' => 'Product not found',
                'errors' => ['Product with barcode not found']
            ])
        ]);
        
        // Products - By ID
        $this->addResponseToRequest('📦 Products', 'Get Product by ID', [
            $this->createResponseExample('Product Found', 200, [
                'success' => true,
                'message' => 'Product retrieved successfully',
                'data' => $this->getMockProduct()
            ]),
            $this->createResponseExample('Product Not Found', 404, [
                'success' => false,
                'message' => 'Product not found',
                'errors' => ['Product with ID 99999 does not exist']
            ])
        ]);
        
        // Products - Create
        $this->addResponseToRequest('📦 Products', 'Create Product', [
            $this->createResponseExample('Product Created', 201, [
                'success' => true,
                'message' => 'Product created successfully',
                'data' => $this->getMockProduct()
            ]),
            $this->createResponseExample('Validation Error', 422, [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => [
                    'name' => ['The name field is required.'],
                    'price' => ['The price must be a number.']
                ]
            ])
        ]);
        
        // Orders - List
        $this->addResponseToRequest('📋 Orders', 'List Orders', [
            $this->createResponseExample('Orders Found', 200, $this->createOrdersWithDataResponse()),
            $this->createResponseExample('No Orders', 200, $this->realResponses['orders_list']['response'])
        ]);
        
        // Orders - Filters
        $this->addResponseToRequest('📋 Orders', 'Orders with Filters', [
            $this->createResponseExample('Filtered Results', 200, $this->createOrdersWithDataResponse())
        ]);
        
        // Orders - By ID
        $this->addResponseToRequest('📋 Orders', 'Get Order by ID', [
            $this->createResponseExample('Order Found', 200, [
                'success' => true,
                'message' => 'Order retrieved successfully',
                'data' => $this->getMockOrder()
            ]),
            $this->createResponseExample('Order Not Found', 404, [
                'success' => false,
                'message' => 'Order not found',
                'errors' => ['Order with ID not found']
            ])
        ]);
        
        // Orders - Create
        $this->addResponseToRequest('📋 Orders', 'Create Order', [
            $this->createResponseExample('Order Created', 201, [
                'success' => true,
                'message' => 'Order created successfully',
                'data' => $this->getMockOrder()
            ]),
            $this->createResponseExample('Validation Error', 422, [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => [
                    'items' => ['At least one item is required'],
                    'items.0.product_id' => ['The product does not exist']
                ]
            ])
        ]);
        
        // Orders - Update Status
        $this->addResponseToRequest('📋 Orders', 'Update Order Status', [
            $this->createResponseExample('Status Updated', 200, [
                'success' => true,
                'message' => 'Order status updated successfully',
                'data' => $this->getMockOrder()
            ])
        ]);
        
        // Customers - List
        $this->addResponseToRequest('👥 Customers', 'List Customers', [
            $this->createResponseExample('Customers Found', 200, $this->createCustomersWithDataResponse()),
            $this->createResponseExample('No Customers', 200, $this->realResponses['customers_list']['response'])
        ]);
        
        // Customers - Search
        $this->addResponseToRequest('👥 Customers', 'Search Customers', [
            $this->createResponseExample('Search Results', 200, $this->createCustomersWithDataResponse())
        ]);
        
        // Customers - By ID
        $this->addResponseToRequest('👥 Customers', 'Get Customer by ID', [
            $this->createResponseExample('Customer Found', 200, [
                'success' => true,
                'message' => 'Customer retrieved successfully',
                'data' => $this->getMockCustomer()
            ]),
            $this->createResponseExample('Customer Not Found', 404, [
                'success' => false,
                'message' => 'Customer not found'
            ])
        ]);
        
        // Customers - Create
        $this->addResponseToRequest('👥 Customers', 'Create Customer', [
            $this->createResponseExample('Customer Created', 201, [
                'success' => true,
                'message' => 'Customer created successfully',
                'data' => $this->getMockCustomer()
            ])
        ]);
        
        // Customers - Update
        $this->addResponseToRequest('👥 Customers', 'Update Customer', [
            $this->createResponseExample('Customer Updated', 200, [
                'success' => true,
                'message' => 'Customer updated successfully',
                'data' => $this->getMockCustomer()
            ])
        ]);
        
        // Payments - List
        $this->addResponseToRequest('💰 Payments', 'List Payments', [
            $this->createResponseExample('Payments Found', 200, $this->createPaymentsWithDataResponse()),
            $this->createResponseExample('No Payments', 200, $this->realResponses['payments_list']['response'])
        ]);
        
        // Payments - Filters
        $this->addResponseToRequest('💰 Payments', 'Payments with Filters', [
            $this->createResponseExample('Filtered Results', 200, $this->createPaymentsWithDataResponse())
        ]);
        
        // Payments - Summary
        $this->addResponseToRequest('💰 Payments', 'Payment Summary', [
            $this->createResponseExample('Summary Retrieved', 200, [
                'success' => true,
                'message' => 'Payment summary retrieved successfully',
                'data' => [
                    'total_income' => 1500000,
                    'total_expense' => 500000,
                    'net_income' => 1000000,
                    'transaction_count' => 15
                ]
            ])
        ]);
        
        // Payments - Create
        $this->addResponseToRequest('💰 Payments', 'Create Payment', [
            $this->createResponseExample('Payment Created', 201, [
                'success' => true,
                'message' => 'Payment created successfully',
                'data' => $this->getMockPayment()
            ])
        ]);
        
        // Playground - Stats
        $this->addResponseToRequest('🧪 Playground', 'Get Statistics', [
            $this->createResponseExample('Statistics Retrieved', 200, $this->realResponses['playground_stats']['response'])
        ]);
        
        // Playground - Generate Code
        $this->addResponseToRequest('🧪 Playground', 'Generate Dart Code', [
            $this->createResponseExample('Code Generated', 200, [
                'success' => true,
                'message' => 'Code generated successfully',
                'data' => [
                    'language' => 'dart',
                    'code' => 'import \'package:http/http.dart\' as http;\n\nFuture<void> login() async {\n  final response = await http.post(\n    Uri.parse(\'http://yukimart.local/api/v1/auth/login\'),\n    headers: {\'Content-Type\': \'application/json\'},\n    body: json.encode({\'email\': \'user@example.com\', \'password\': \'password\'}),\n  );\n}'
                ]
            ])
        ]);
        
        // Playground - Validate
        $this->addResponseToRequest('🧪 Playground', 'Validate Endpoint', [
            $this->createResponseExample('Endpoint Valid', 200, [
                'success' => true,
                'message' => 'Endpoint validation successful',
                'data' => [
                    'endpoint' => '/products',
                    'method' => 'GET',
                    'valid' => true,
                    'response_time' => '120ms'
                ]
            ])
        ]);
        
        // Error Scenarios
        $this->addResponseToRequest('⚠️ Error Scenarios', 'Unauthorized Access', [
            $this->createResponseExample('Unauthorized', 401, $this->realResponses['unauthorized_access']['response'])
        ]);
        
        $this->addResponseToRequest('⚠️ Error Scenarios', 'Invalid Login', [
            $this->createResponseExample('Invalid Credentials', 401, [
                'success' => false,
                'message' => 'Invalid credentials',
                'errors' => ['email' => ['The provided credentials are incorrect.']]
            ])
        ]);
        
        $this->addResponseToRequest('⚠️ Error Scenarios', 'Product Not Found', [
            $this->createResponseExample('Not Found', 404, [
                'success' => false,
                'message' => 'Product not found',
                'errors' => ['Product with ID 99999 does not exist']
            ])
        ]);
        
        $this->addResponseToRequest('⚠️ Error Scenarios', 'Invalid Order Data', [
            $this->createResponseExample('Validation Error', 422, [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => [
                    'customer_id' => ['The customer id must be an integer.'],
                    'items' => ['The items field is required.']
                ]
            ])
        ]);
        
        echo "✅ All responses synced to collection\n\n";
    }

    private function createResponseExample($name, $code, $responseData)
    {
        return [
            'name' => $name,
            'status' => $this->getStatusText($code),
            'code' => $code,
            'body' => json_encode($responseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            'header' => [
                [
                    'key' => 'Content-Type',
                    'value' => 'application/json'
                ]
            ]
        ];
    }

    private function addResponseToRequest($folderName, $requestName, $responses)
    {
        foreach ($this->postmanCollection['item'] as &$folder) {
            if ($folder['name'] === $folderName) {
                foreach ($folder['item'] as &$request) {
                    if ($request['name'] === $requestName) {
                        $request['response'] = $responses;
                        break;
                    }
                }
                break;
            }
        }
    }

    private function createProductsWithDataResponse()
    {
        return [
            'success' => true,
            'message' => 'Products retrieved successfully',
            'data' => [
                $this->getMockProduct(1, 'Kem Dưỡng Da Nivea', 'NIVEA001', 89000),
                $this->getMockProduct(2, 'Sữa Rửa Mặt Cetaphil', 'CETAPHIL001', 125000),
                $this->getMockProduct(3, 'Nước Hoa Chanel No.5', 'CHANEL001', 2500000)
            ],
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
    }

    private function createOrdersWithDataResponse()
    {
        return [
            'success' => true,
            'message' => 'Orders retrieved successfully',
            'data' => [
                $this->getMockOrder(1, 'ORD-20250806-001', 214000),
                $this->getMockOrder(2, 'ORD-20250806-002', 125000)
            ],
            'pagination' => [
                'current_page' => 1,
                'per_page' => 15,
                'total' => 2,
                'last_page' => 1
            ]
        ];
    }

    private function createCustomersWithDataResponse()
    {
        return [
            'success' => true,
            'message' => 'Customers retrieved successfully',
            'data' => [
                $this->getMockCustomer(1, 'Nguyễn Văn A', 'nguyenvana@email.com'),
                $this->getMockCustomer(2, 'Trần Thị B', 'tranthib@email.com')
            ],
            'pagination' => [
                'current_page' => 1,
                'per_page' => 15,
                'total' => 2
            ]
        ];
    }

    private function createPaymentsWithDataResponse()
    {
        return [
            'success' => true,
            'message' => 'Payments retrieved successfully',
            'data' => [
                $this->getMockPayment(1, 214000, 'income', 'cash'),
                $this->getMockPayment(2, 125000, 'income', 'card'),
                $this->getMockPayment(3, 500000, 'expense', 'transfer')
            ],
            'pagination' => [
                'current_page' => 1,
                'per_page' => 15,
                'total' => 3
            ]
        ];
    }

    private function getMockProduct($id = 1, $name = 'Kem Dưỡng Da Nivea', $sku = 'NIVEA001', $price = 89000)
    {
        return [
            'id' => $id,
            'name' => $name,
            'sku' => $sku,
            'barcode' => '123456789012' . $id,
            'price' => $price,
            'cost_price' => intval($price * 0.7),
            'stock_quantity' => 50,
            'category_id' => 1,
            'category_name' => 'Mỹ Phẩm',
            'description' => 'Sản phẩm chất lượng cao',
            'image_url' => 'https://example.com/product' . $id . '.jpg',
            'status' => 'active',
            'created_at' => '2025-08-06T10:00:00Z',
            'updated_at' => '2025-08-06T10:00:00Z'
        ];
    }

    private function getMockOrder($id = 1, $orderNumber = 'ORD-20250806-001', $totalAmount = 214000)
    {
        return [
            'id' => $id,
            'order_number' => $orderNumber,
            'customer_id' => 1,
            'customer_name' => 'Nguyễn Văn A',
            'customer_phone' => '0123456789',
            'total_amount' => $totalAmount,
            'discount_amount' => 0,
            'final_amount' => $totalAmount,
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
        ];
    }

    private function getMockCustomer($id = 1, $name = 'Nguyễn Văn A', $email = 'nguyenvana@email.com')
    {
        return [
            'id' => $id,
            'name' => $name,
            'email' => $email,
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
        ];
    }

    private function getMockPayment($id = 1, $amount = 214000, $type = 'income', $method = 'cash')
    {
        return [
            'id' => $id,
            'reference_id' => 'TT' . $id,
            'reference_type' => 'invoice',
            'amount' => $amount,
            'type' => $type,
            'method' => $method,
            'description' => 'Thanh toán đơn hàng',
            'status' => 'completed',
            'bank_account_id' => $method === 'cash' ? null : 1,
            'created_by' => 12,
            'created_at' => '2025-08-06T09:45:00Z',
            'updated_at' => '2025-08-06T09:45:00Z'
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

    private function getStatusText($code)
    {
        $statusTexts = [
            200 => 'OK',
            201 => 'Created',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            422 => 'Unprocessable Entity',
            500 => 'Internal Server Error',
            503 => 'Service Unavailable'
        ];
        
        return $statusTexts[$code] ?? 'Unknown';
    }

    private function saveUpdatedCollection()
    {
        echo "💾 Saving updated collection...\n";
        
        // Update token in collection
        if ($this->token) {
            foreach ($this->postmanCollection['variable'] as &$variable) {
                if ($variable['key'] === 'api_token') {
                    $variable['value'] = $this->token;
                    break;
                }
            }
        }
        
        // Save updated collection
        $postmanDir = __DIR__ . '/../storage/testing/postman';
        file_put_contents(
            $postmanDir . '/yukimart-api-complete-examples.json',
            json_encode($this->postmanCollection, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        );
        
        echo "✅ Updated collection saved\n\n";
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
        
        echo "📊 Sync Summary:\n";
        echo "================\n";
        echo "- Total Folders: " . count($this->postmanCollection['item']) . "\n";
        echo "- Total Requests: {$totalRequests}\n";
        echo "- Total Response Examples: {$totalResponses}\n";
        echo "- Authentication Token: " . ($this->token ? 'Updated' : 'Failed') . "\n";
        echo "- Real Responses: ✅ Synced\n";
        echo "- Mock Data: ✅ Realistic Vietnamese data\n";
        echo "- Error Scenarios: ✅ Comprehensive coverage\n\n";
        
        echo "🔗 Usage Instructions:\n";
        echo "1. Import: storage/testing/postman/yukimart-api-complete-examples.json\n";
        echo "2. All requests now have comprehensive response examples\n";
        echo "3. Use examples for Flutter development reference\n";
        echo "4. Test both success and error scenarios\n\n";
        
        echo "📱 Perfect for Flutter Development!\n";
        echo "- Real API response examples\n";
        echo "- Realistic Vietnamese mock data\n";
        echo "- Complete error handling patterns\n";
        echo "- Multiple response scenarios\n";
        echo "- Production-ready examples\n\n";
    }
}

// Run the sync
$syncService = new ResponseSyncService();
$syncService->syncResponses();
