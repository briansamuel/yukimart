<?php

/**
 * Simple Postman Collection Generator
 * Creates comprehensive collection with all YukiMart API endpoints
 */

require_once __DIR__ . '/../vendor/autoload.php';

class SimplePostmanGenerator
{
    private $baseUrl = 'http://yukimart.local/api/v1';
    private $email = 'yukimart@gmail.com';
    private $password = '123456';
    private $token = null;
    private $postmanCollection = [];

    public function __construct()
    {
        echo "🚀 Simple Postman Collection Generator\n";
        echo "======================================\n\n";
        
        $this->initializePostmanCollection();
    }

    public function generateCollection()
    {
        try {
            // 1. Get Authentication Token
            $this->authenticate();
            
            // 2. Test All Endpoints and Build Collection
            $this->buildHealthEndpoints();
            $this->buildAuthenticationEndpoints();
            $this->buildProductEndpoints();
            $this->buildOrderEndpoints();
            $this->buildCustomerEndpoints();
            $this->buildPaymentEndpoints();
            $this->buildPlaygroundEndpoints();
            $this->buildErrorScenarios();
            
            // 3. Finalize Collection
            $this->finalizeCollection();
            
            // 4. Save Results
            $this->saveResults();
            
            echo "\n🎉 Simple Postman Collection Generated!\n";
            echo "=======================================\n\n";
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
            'device_name' => 'Postman Collection Generator'
        ]);
        
        if ($response['success'] && isset($response['data']['token'])) {
            $this->token = $response['data']['token'];
            echo "✅ Token obtained successfully\n\n";
        } else {
            throw new Exception("Authentication failed");
        }
    }

    private function buildHealthEndpoints()
    {
        echo "🏥 Building Health endpoints...\n";
        
        $response = $this->makeRequest('GET', '/health');
        $this->addPostmanRequest('🏥 Health Check', 'Health Check', 'GET', '/health', null, $response);
        
        echo "✅ Health endpoints added\n\n";
    }

    private function buildAuthenticationEndpoints()
    {
        echo "🔐 Building Authentication endpoints...\n";
        
        // Login
        $loginData = [
            'email' => '{{user_email}}',
            'password' => '{{user_password}}',
            'device_name' => 'Flutter App'
        ];
        $loginResponse = $this->makeRequest('POST', '/auth/login', [
            'email' => $this->email,
            'password' => $this->password,
            'device_name' => 'Flutter App'
        ]);
        $this->addPostmanRequest('🔐 Authentication', 'Login', 'POST', '/auth/login', $loginData, $loginResponse);
        
        // Get Profile
        $profileResponse = $this->makeAuthenticatedRequest('GET', '/auth/me');
        $this->addPostmanRequest('🔐 Authentication', 'Get Profile', 'GET', '/auth/me', null, $profileResponse, true);
        
        // Update Profile
        $updateData = [
            'full_name' => 'Updated Name',
            'phone' => '0987654321',
            'address' => 'Updated Address'
        ];
        $updateResponse = $this->makeAuthenticatedRequest('PUT', '/auth/profile', $updateData);
        $this->addPostmanRequest('🔐 Authentication', 'Update Profile', 'PUT', '/auth/profile', $updateData, $updateResponse, true);
        
        // Change Password
        $passwordData = [
            'current_password' => '{{user_password}}',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123'
        ];
        $this->addPostmanRequest('🔐 Authentication', 'Change Password', 'POST', '/auth/change-password', $passwordData, null, true);
        
        // Logout
        $logoutResponse = $this->makeAuthenticatedRequest('POST', '/auth/logout');
        $this->addPostmanRequest('🔐 Authentication', 'Logout', 'POST', '/auth/logout', null, $logoutResponse, true);
        
        echo "✅ Authentication endpoints added\n\n";
    }

    private function buildProductEndpoints()
    {
        echo "📦 Building Product endpoints...\n";
        
        // List Products
        $response = $this->makeAuthenticatedRequest('GET', '/products');
        $this->addPostmanRequest('📦 Products', 'List Products', 'GET', '/products', null, $response, true);
        
        // Search Products
        $response = $this->makeAuthenticatedRequest('GET', '/products?search=kem&limit=10');
        $this->addPostmanRequest('📦 Products', 'Search Products', 'GET', '/products?search=kem&limit=10', null, $response, true);
        
        // Products with Pagination
        $response = $this->makeAuthenticatedRequest('GET', '/products?page=1&per_page=15');
        $this->addPostmanRequest('📦 Products', 'Products with Pagination', 'GET', '/products?page=1&per_page=15', null, $response, true);
        
        // Product by Barcode
        $this->addPostmanRequest('📦 Products', 'Get Product by Barcode', 'GET', '/products/barcode/{{barcode}}', null, null, true);
        
        // Get Product by ID
        $this->addPostmanRequest('📦 Products', 'Get Product by ID', 'GET', '/products/{{product_id}}', null, null, true);
        
        // Create Product
        $createData = [
            'name' => 'New Product',
            'sku' => 'SKU001',
            'price' => 100000,
            'category_id' => 1,
            'description' => 'Product description'
        ];
        $this->addPostmanRequest('📦 Products', 'Create Product', 'POST', '/products', $createData, null, true);
        
        echo "✅ Product endpoints added\n\n";
    }

    private function buildOrderEndpoints()
    {
        echo "📋 Building Order endpoints...\n";
        
        // List Orders
        $response = $this->makeAuthenticatedRequest('GET', '/orders');
        $this->addPostmanRequest('📋 Orders', 'List Orders', 'GET', '/orders', null, $response, true);
        
        // Orders with Filters
        $this->addPostmanRequest('📋 Orders', 'Orders with Filters', 'GET', '/orders?status=processing&page=1', null, null, true);
        
        // Get Order by ID
        $this->addPostmanRequest('📋 Orders', 'Get Order by ID', 'GET', '/orders/{{order_id}}', null, null, true);
        
        // Create Order
        $orderData = [
            'customer_id' => 0,
            'items' => [
                [
                    'product_id' => 1,
                    'quantity' => 2,
                    'price' => 50000
                ]
            ],
            'payment_method' => 'cash',
            'note' => 'Test order'
        ];
        $this->addPostmanRequest('📋 Orders', 'Create Order', 'POST', '/orders', $orderData, null, true);
        
        // Update Order Status
        $statusData = [
            'status' => 'processing'
        ];
        $this->addPostmanRequest('📋 Orders', 'Update Order Status', 'PUT', '/orders/{{order_id}}/status', $statusData, null, true);
        
        echo "✅ Order endpoints added\n\n";
    }

    private function buildCustomerEndpoints()
    {
        echo "👥 Building Customer endpoints...\n";
        
        // List Customers
        $response = $this->makeAuthenticatedRequest('GET', '/customers');
        $this->addPostmanRequest('👥 Customers', 'List Customers', 'GET', '/customers', null, $response, true);
        
        // Search Customers
        $this->addPostmanRequest('👥 Customers', 'Search Customers', 'GET', '/customers?search={{search_term}}&page=1', null, null, true);
        
        // Get Customer by ID
        $this->addPostmanRequest('👥 Customers', 'Get Customer by ID', 'GET', '/customers/{{customer_id}}', null, null, true);
        
        // Create Customer
        $customerData = [
            'name' => 'New Customer',
            'email' => 'customer@example.com',
            'phone' => '0123456789',
            'address' => 'Customer Address'
        ];
        $this->addPostmanRequest('👥 Customers', 'Create Customer', 'POST', '/customers', $customerData, null, true);
        
        // Update Customer
        $updateData = [
            'name' => 'Updated Customer Name',
            'phone' => '0987654321'
        ];
        $this->addPostmanRequest('👥 Customers', 'Update Customer', 'PUT', '/customers/{{customer_id}}', $updateData, null, true);
        
        echo "✅ Customer endpoints added\n\n";
    }

    private function buildPaymentEndpoints()
    {
        echo "💰 Building Payment endpoints...\n";
        
        // List Payments
        $response = $this->makeAuthenticatedRequest('GET', '/payments');
        $this->addPostmanRequest('💰 Payments', 'List Payments', 'GET', '/payments', null, $response, true);
        
        // Payments with Filters
        $this->addPostmanRequest('💰 Payments', 'Payments with Filters', 'GET', '/payments?type=income&page=1', null, null, true);
        
        // Payment Summary
        $this->addPostmanRequest('💰 Payments', 'Payment Summary', 'GET', '/payments/summary', null, null, true);
        
        // Create Payment
        $paymentData = [
            'amount' => 100000,
            'type' => 'income',
            'description' => 'Payment description',
            'reference_type' => 'order',
            'reference_id' => 1
        ];
        $this->addPostmanRequest('💰 Payments', 'Create Payment', 'POST', '/payments', $paymentData, null, true);
        
        echo "✅ Payment endpoints added\n\n";
    }

    private function buildPlaygroundEndpoints()
    {
        echo "🧪 Building Playground endpoints...\n";
        
        // Playground Stats
        $response = $this->makeAuthenticatedRequest('GET', '/playground/stats');
        $this->addPostmanRequest('🧪 Playground', 'Get Statistics', 'GET', '/playground/stats', null, $response, true);
        
        // Generate Code
        $codeData = [
            'endpoint' => '/auth/login',
            'method' => 'POST',
            'language' => 'dart'
        ];
        $this->addPostmanRequest('🧪 Playground', 'Generate Dart Code', 'POST', '/playground/generate-code', $codeData, null, true);
        
        // Validate Endpoint
        $validateData = [
            'endpoint' => '/products',
            'method' => 'GET'
        ];
        $this->addPostmanRequest('🧪 Playground', 'Validate Endpoint', 'POST', '/playground/validate', $validateData, null, true);
        
        echo "✅ Playground endpoints added\n\n";
    }

    private function buildErrorScenarios()
    {
        echo "⚠️ Building Error scenarios...\n";
        
        // Unauthorized Access
        $this->addPostmanRequest('⚠️ Error Scenarios', 'Unauthorized Access', 'GET', '/auth/me', null, null);
        
        // Invalid Login
        $invalidData = [
            'email' => 'wrong@email.com',
            'password' => 'wrongpassword',
            'device_name' => 'Test'
        ];
        $this->addPostmanRequest('⚠️ Error Scenarios', 'Invalid Login', 'POST', '/auth/login', $invalidData, null);
        
        // Resource Not Found
        $this->addPostmanRequest('⚠️ Error Scenarios', 'Product Not Found', 'GET', '/products/99999', null, null, true);
        
        // Invalid Data
        $invalidOrderData = [
            'customer_id' => 'invalid',
            'items' => []
        ];
        $this->addPostmanRequest('⚠️ Error Scenarios', 'Invalid Order Data', 'POST', '/orders', $invalidOrderData, null, true);
        
        echo "✅ Error scenarios added\n\n";
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

    private function initializePostmanCollection()
    {
        $this->postmanCollection = [
            'info' => [
                'name' => 'YukiMart API v1 - Flutter Ready Collection',
                'description' => 'Complete API collection optimized for Flutter development with real examples and comprehensive documentation.',
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
                    'value' => '{{token_from_login}}',
                    'description' => 'Authentication token obtained from login'
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
                ],
                [
                    'key' => 'product_id',
                    'value' => '1',
                    'description' => 'Sample product ID for testing'
                ],
                [
                    'key' => 'order_id',
                    'value' => '1',
                    'description' => 'Sample order ID for testing'
                ],
                [
                    'key' => 'customer_id',
                    'value' => '1',
                    'description' => 'Sample customer ID for testing'
                ],
                [
                    'key' => 'barcode',
                    'value' => '1234567890',
                    'description' => 'Sample barcode for testing'
                ],
                [
                    'key' => 'search_term',
                    'value' => 'kem',
                    'description' => 'Sample search term'
                ]
            ],
            'item' => []
        ];
    }

    private function addPostmanRequest($folder, $name, $method, $endpoint, $body = null, $response = null, $requiresAuth = false)
    {
        // Find or create folder
        $folderIndex = null;
        foreach ($this->postmanCollection['item'] as $index => $item) {
            if ($item['name'] === $folder) {
                $folderIndex = $index;
                break;
            }
        }
        
        if ($folderIndex === null) {
            $this->postmanCollection['item'][] = [
                'name' => $folder,
                'description' => $this->getFolderDescription($folder),
                'item' => []
            ];
            $folderIndex = count($this->postmanCollection['item']) - 1;
        }
        
        // Create request
        $request = [
            'name' => $name,
            'request' => [
                'method' => $method,
                'header' => [
                    [
                        'key' => 'Accept',
                        'value' => 'application/json',
                        'description' => 'Accept JSON responses'
                    ]
                ],
                'url' => [
                    'raw' => '{{base_url}}' . $endpoint,
                    'host' => ['{{base_url}}'],
                    'path' => array_filter(explode('/', $endpoint))
                ],
                'description' => $this->getRequestDescription($name, $method, $endpoint)
            ]
        ];
        
        if ($requiresAuth) {
            $request['request']['header'][] = [
                'key' => 'Authorization',
                'value' => 'Bearer {{api_token}}',
                'description' => 'Authentication token'
            ];
        }
        
        if ($body) {
            $request['request']['header'][] = [
                'key' => 'Content-Type',
                'value' => 'application/json',
                'description' => 'JSON content type'
            ];
            $request['request']['body'] = [
                'mode' => 'raw',
                'raw' => json_encode($body, JSON_PRETTY_PRINT),
                'options' => [
                    'raw' => [
                        'language' => 'json'
                    ]
                ]
            ];
        }
        
        if ($response) {
            $request['response'] = [[
                'name' => 'Example Response',
                'status' => $this->getStatusText($response['http_code'] ?? 200),
                'code' => $response['http_code'] ?? 200,
                'body' => json_encode($response, JSON_PRETTY_PRINT),
                'header' => [
                    [
                        'key' => 'Content-Type',
                        'value' => 'application/json'
                    ]
                ]
            ]];
        }
        
        $this->postmanCollection['item'][$folderIndex]['item'][] = $request;
    }

    private function getFolderDescription($folder)
    {
        $descriptions = [
            '🏥 Health Check' => 'System health and status monitoring endpoints',
            '🔐 Authentication' => 'User authentication, profile management, and security',
            '📦 Products' => 'Product management, search, and inventory operations',
            '📋 Orders' => 'Order creation, management, and status tracking',
            '👥 Customers' => 'Customer management, search, and profile operations',
            '💰 Payments' => 'Payment processing, financial operations, and reporting',
            '🧪 Playground' => 'Interactive API testing, code generation, and validation',
            '⚠️ Error Scenarios' => 'Error handling examples and validation patterns'
        ];
        
        return $descriptions[$folder] ?? 'API endpoints';
    }

    private function getRequestDescription($name, $method, $endpoint)
    {
        return "**{$method}** `{$endpoint}`\n\n{$name} endpoint for YukiMart API. Includes real response examples where available.";
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
            500 => 'Internal Server Error'
        ];
        
        return $statusTexts[$code] ?? 'Unknown';
    }

    private function finalizeCollection()
    {
        echo "📮 Finalizing collection...\n";
        
        // Update token in collection
        if ($this->token) {
            foreach ($this->postmanCollection['variable'] as &$variable) {
                if ($variable['key'] === 'api_token') {
                    $variable['value'] = $this->token;
                    break;
                }
            }
        }
        
        // Add comprehensive documentation
        $this->postmanCollection['info']['description'] .= "\n\n## 🚀 Quick Start Guide\n\n### 1. Import Collection\n- Import this JSON file into Postman\n- Collection includes 30+ endpoints with real examples\n\n### 2. Set Environment Variables\n```\nbase_url: http://yukimart.local/api/v1\napi_token: (get from Login request)\nuser_email: yukimart@gmail.com\nuser_password: 123456\n```\n\n### 3. Authentication Flow\n1. Run **Login** request to get authentication token\n2. Token will be automatically used in other requests\n3. Test protected endpoints with real data\n\n## 📱 Flutter Development Ready\n\n### Response Format\nAll API responses follow this standard format:\n```json\n{\n  \"success\": true,\n  \"message\": \"Operation successful\",\n  \"data\": {...},\n  \"meta\": {\n    \"timestamp\": \"2025-08-06T14:12:07Z\",\n    \"version\": \"v1\",\n    \"request_id\": \"unique-id\"\n  }\n}\n```\n\n### Error Handling\nError responses include detailed information:\n```json\n{\n  \"success\": false,\n  \"message\": \"Error description\",\n  \"errors\": {...},\n  \"meta\": {...}\n}\n```\n\n### Pagination\nList endpoints support pagination:\n```\nGET /products?page=1&per_page=15\nGET /orders?page=2&limit=20\n```\n\n### Authentication\nUse Bearer token authentication:\n```\nAuthorization: Bearer your_token_here\n```\n\n## 🔧 Available Endpoints\n\n- **Health Check**: System status monitoring\n- **Authentication**: Login, profile, password management\n- **Products**: CRUD operations, search, barcode lookup\n- **Orders**: Creation, management, status updates\n- **Customers**: Management, search, profiles\n- **Payments**: Processing, reporting, summaries\n- **Playground**: Interactive testing, code generation\n- **Error Scenarios**: Comprehensive error examples\n\n## 📊 Features\n\n✅ **Real API responses** included\n✅ **Complete authentication flow**\n✅ **Error handling examples**\n✅ **Pagination support**\n✅ **Search and filtering**\n✅ **CRUD operations**\n✅ **Flutter-optimized examples**\n\n## 🎯 Perfect for:\n\n- Flutter mobile app development\n- API integration testing\n- Frontend development\n- QA testing\n- Documentation reference\n\nStart with the Login request and explore all endpoints with real data!";
        
        echo "✅ Collection finalized\n\n";
    }

    private function saveResults()
    {
        // Ensure directories exist
        $testingDir = __DIR__ . '/../storage/testing';
        $postmanDir = $testingDir . '/postman';
        
        if (!is_dir($testingDir)) {
            mkdir($testingDir, 0755, true);
        }
        if (!is_dir($postmanDir)) {
            mkdir($postmanDir, 0755, true);
        }
        
        // Save Postman collection
        file_put_contents(
            $postmanDir . '/yukimart-api-flutter-ready.json',
            json_encode($this->postmanCollection, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
        
        echo "💾 Collection saved:\n";
        echo "   - Postman Collection: storage/testing/postman/yukimart-api-flutter-ready.json\n\n";
    }

    private function printSummary()
    {
        $totalRequests = 0;
        foreach ($this->postmanCollection['item'] as $folder) {
            $totalRequests += count($folder['item']);
        }
        
        echo "📊 Collection Summary:\n";
        echo "=====================\n";
        echo "- Total Folders: " . count($this->postmanCollection['item']) . "\n";
        echo "- Total Requests: {$totalRequests}\n";
        echo "- Authentication Token: " . ($this->token ? 'Included' : 'Failed') . "\n";
        echo "- Real Response Examples: ✅ Included\n";
        echo "- Environment Variables: ✅ Configured\n";
        echo "- Documentation: ✅ Comprehensive\n\n";
        
        echo "🔗 Usage Instructions:\n";
        echo "1. Import: storage/testing/postman/yukimart-api-flutter-ready.json\n";
        echo "2. Set environment variables in Postman\n";
        echo "3. Run Login request to get authentication token\n";
        echo "4. Test all endpoints with real examples\n";
        echo "5. Use for Flutter development integration\n\n";
        
        echo "📱 Flutter Development Benefits:\n";
        echo "- Real API response examples\n";
        echo "- Complete authentication flow\n";
        echo "- Error handling patterns\n";
        echo "- Pagination and filtering examples\n";
        echo "- CRUD operation templates\n";
        echo "- Production-ready request formats\n\n";
        
        echo "🎉 Ready for Flutter team usage!\n";
    }
}

// Generate collection
$generator = new SimplePostmanGenerator();
$generator->generateCollection();
