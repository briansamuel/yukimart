<?php

/**
 * Enhanced Postman Collection Generator
 * Creates comprehensive collection with all YukiMart API endpoints
 */

require_once __DIR__ . '/../vendor/autoload.php';

class EnhancedPostmanGenerator
{
    private $baseUrl = 'http://yukimart.local/api/v1';
    private $email = 'yukimart@gmail.com';
    private $password = '123456';
    private $token = null;
    private $responses = [];
    private $postmanCollection = [];

    public function __construct()
    {
        echo "🚀 Enhanced Postman Collection Generator\n";
        echo "========================================\n\n";
        
        $this->initializePostmanCollection();
    }

    public function generateCollection()
    {
        try {
            // 1. Get Authentication Token
            $this->authenticate();
            
            // 2. Test All Endpoints
            $this->testHealthEndpoints();
            $this->testAuthenticationEndpoints();
            $this->testProductEndpoints();
            $this->testOrderEndpoints();
            $this->testCustomerEndpoints();
            $this->testPaymentEndpoints();
            $this->testPlaygroundEndpoints();
            $this->testErrorScenarios();
            
            // 3. Generate Enhanced Collection
            $this->finalizeCollection();
            
            // 4. Save Results
            $this->saveResults();
            
            echo "\n🎉 Enhanced Postman Collection Generated!\n";
            echo "=========================================\n\n";
            $this->printSummary();
            
        } catch (Exception $e) {
            echo "❌ Error: " . $e->getMessage() . "\n";
        }
    }

    private function authenticate()
    {
        echo "🔐 Authenticating...\n";
        
        $response = $this->makeRequest('POST', '/auth/login', [
            'email' => $this->email,
            'password' => $this->password,
            'device_name' => 'Enhanced Postman Collection'
        ]);
        
        if ($response['success'] && isset($response['data']['token'])) {
            $this->token = $response['data']['token'];
            echo "✅ Authentication successful\n\n";
        } else {
            throw new Exception("Authentication failed");
        }
    }

    private function testHealthEndpoints()
    {
        echo "🏥 Testing Health Endpoints...\n";
        
        // Health Check
        $response = $this->makeRequest('GET', '/health');
        $this->addPostmanRequest('🏥 Health Check', 'Health Check', 'GET', '/health', null, $response);
        
        echo "✅ Health endpoints completed\n\n";
    }

    private function testAuthenticationEndpoints()
    {
        echo "🔐 Testing Authentication Endpoints...\n";
        
        // Login
        $loginData = [
            'email' => $this->email,
            'password' => $this->password,
            'device_name' => 'Flutter App'
        ];
        $response = $this->makeRequest('POST', '/auth/login', $loginData);
        $this->addPostmanRequest('🔐 Authentication', 'Login', 'POST', '/auth/login', $loginData, $response);
        
        // Get Profile
        $response = $this->makeAuthenticatedRequest('GET', '/auth/me');
        $this->addPostmanRequest('🔐 Authentication', 'Get Profile', 'GET', '/auth/me', null, $response, true);
        
        // Update Profile
        $updateData = [
            'full_name' => 'YukiMart Admin Updated',
            'phone' => '0987654321',
            'address' => 'YukiMart HQ - New Address'
        ];
        $response = $this->makeAuthenticatedRequest('PUT', '/auth/profile', $updateData);
        $this->addPostmanRequest('🔐 Authentication', 'Update Profile', 'PUT', '/auth/profile', $updateData, $response, true);
        
        // Change Password
        $passwordData = [
            'current_password' => '123456',
            'new_password' => '123456',
            'new_password_confirmation' => '123456'
        ];
        $response = $this->makeAuthenticatedRequest('POST', '/auth/change-password', $passwordData);
        $this->addPostmanRequest('🔐 Authentication', 'Change Password', 'POST', '/auth/change-password', $passwordData, $response, true);
        
        // Logout
        $response = $this->makeAuthenticatedRequest('POST', '/auth/logout');
        $this->addPostmanRequest('🔐 Authentication', 'Logout', 'POST', '/auth/logout', null, $response, true);
        
        // Re-authenticate for other tests
        try {
            $this->authenticate();
        } catch (Exception $e) {
            echo "⚠️ Re-authentication failed, using existing token\n";
        }
        
        echo "✅ Authentication endpoints completed\n\n";
    }

    private function testProductEndpoints()
    {
        echo "📦 Testing Product Endpoints...\n";
        
        // List Products
        $response = $this->makeAuthenticatedRequest('GET', '/products');
        $this->addPostmanRequest('📦 Products', 'List Products', 'GET', '/products', null, $response, true);
        
        // Search Products
        $response = $this->makeAuthenticatedRequest('GET', '/products?search=kem&limit=10');
        $this->addPostmanRequest('📦 Products', 'Search Products', 'GET', '/products?search=kem&limit=10', null, $response, true);
        
        // Products with Pagination
        $response = $this->makeAuthenticatedRequest('GET', '/products?page=1&per_page=15');
        $this->addPostmanRequest('📦 Products', 'Products with Pagination', 'GET', '/products?page=1&per_page=15', null, $response, true);
        
        // Product by Barcode (example)
        $response = $this->makeAuthenticatedRequest('GET', '/products/barcode/1234567890');
        $this->addPostmanRequest('📦 Products', 'Get Product by Barcode', 'GET', '/products/barcode/1234567890', null, $response, true);
        
        echo "✅ Product endpoints completed\n\n";
    }

    private function testOrderEndpoints()
    {
        echo "📋 Testing Order Endpoints...\n";
        
        // List Orders
        $response = $this->makeAuthenticatedRequest('GET', '/orders');
        $this->addPostmanRequest('📋 Orders', 'List Orders', 'GET', '/orders', null, $response, true);
        
        // Orders with Filters
        $response = $this->makeAuthenticatedRequest('GET', '/orders?status=processing&page=1');
        $this->addPostmanRequest('📋 Orders', 'Orders with Filters', 'GET', '/orders?status=processing&page=1', null, $response, true);
        
        // Create Order (example)
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
            'note' => 'Test order from Postman'
        ];
        $response = $this->makeAuthenticatedRequest('POST', '/orders', $orderData);
        $this->addPostmanRequest('📋 Orders', 'Create Order', 'POST', '/orders', $orderData, $response, true);
        
        echo "✅ Order endpoints completed\n\n";
    }

    private function testCustomerEndpoints()
    {
        echo "👥 Testing Customer Endpoints...\n";
        
        // List Customers
        $response = $this->makeAuthenticatedRequest('GET', '/customers');
        $this->addPostmanRequest('👥 Customers', 'List Customers', 'GET', '/customers', null, $response, true);
        
        // Search Customers
        $response = $this->makeAuthenticatedRequest('GET', '/customers?search=test&page=1');
        $this->addPostmanRequest('👥 Customers', 'Search Customers', 'GET', '/customers?search=test&page=1', null, $response, true);
        
        // Create Customer (example)
        $customerData = [
            'name' => 'Test Customer',
            'email' => 'customer@test.com',
            'phone' => '0123456789',
            'address' => 'Test Address'
        ];
        $response = $this->makeAuthenticatedRequest('POST', '/customers', $customerData);
        $this->addPostmanRequest('👥 Customers', 'Create Customer', 'POST', '/customers', $customerData, $response, true);
        
        echo "✅ Customer endpoints completed\n\n";
    }

    private function testPaymentEndpoints()
    {
        echo "💰 Testing Payment Endpoints...\n";
        
        // List Payments
        $response = $this->makeAuthenticatedRequest('GET', '/payments');
        $this->addPostmanRequest('💰 Payments', 'List Payments', 'GET', '/payments', null, $response, true);
        
        // Payments with Filters
        $response = $this->makeAuthenticatedRequest('GET', '/payments?type=income&page=1');
        $this->addPostmanRequest('💰 Payments', 'Payments with Filters', 'GET', '/payments?type=income&page=1', null, $response, true);
        
        // Payment Summary
        $response = $this->makeAuthenticatedRequest('GET', '/payments/summary');
        $this->addPostmanRequest('💰 Payments', 'Payment Summary', 'GET', '/payments/summary', null, $response, true);
        
        echo "✅ Payment endpoints completed\n\n";
    }

    private function testPlaygroundEndpoints()
    {
        echo "🧪 Testing Playground Endpoints...\n";
        
        // Playground Stats
        $response = $this->makeAuthenticatedRequest('GET', '/playground/stats');
        $this->addPostmanRequest('🧪 Playground', 'Get Statistics', 'GET', '/playground/stats', null, $response, true);
        
        // Generate Code
        $codeData = [
            'endpoint' => '/auth/login',
            'method' => 'POST',
            'language' => 'dart'
        ];
        $response = $this->makeAuthenticatedRequest('POST', '/playground/generate-code', $codeData);
        $this->addPostmanRequest('🧪 Playground', 'Generate Code', 'POST', '/playground/generate-code', $codeData, $response, true);
        
        echo "✅ Playground endpoints completed\n\n";
    }

    private function testErrorScenarios()
    {
        echo "⚠️ Testing Error Scenarios...\n";
        
        // Unauthorized Access
        $response = $this->makeRequest('GET', '/auth/me');
        $this->addPostmanRequest('⚠️ Error Scenarios', 'Unauthorized Access', 'GET', '/auth/me', null, $response);
        
        // Invalid Login
        $invalidData = [
            'email' => 'wrong@email.com',
            'password' => 'wrongpassword',
            'device_name' => 'Test'
        ];
        $response = $this->makeRequest('POST', '/auth/login', $invalidData);
        $this->addPostmanRequest('⚠️ Error Scenarios', 'Invalid Login', 'POST', '/auth/login', $invalidData, $response);
        
        // Resource Not Found
        $response = $this->makeAuthenticatedRequest('GET', '/products/99999');
        $this->addPostmanRequest('⚠️ Error Scenarios', 'Product Not Found', 'GET', '/products/99999', null, $response, true);
        
        // Invalid Data
        $invalidOrderData = [
            'customer_id' => 'invalid',
            'items' => []
        ];
        $response = $this->makeAuthenticatedRequest('POST', '/orders', $invalidOrderData);
        $this->addPostmanRequest('⚠️ Error Scenarios', 'Invalid Order Data', 'POST', '/orders', $invalidOrderData, $response, true);
        
        echo "✅ Error scenarios completed\n\n";
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
                'name' => 'YukiMart API v1 - Complete Collection',
                'description' => 'Comprehensive API collection with real examples for Flutter development. Includes authentication, CRUD operations, error handling, and playground features.',
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
            '🏥 Health Check' => 'System health and status endpoints',
            '🔐 Authentication' => 'User authentication and profile management',
            '📦 Products' => 'Product management and search functionality',
            '📋 Orders' => 'Order creation and management',
            '👥 Customers' => 'Customer management and search',
            '💰 Payments' => 'Payment processing and financial operations',
            '🧪 Playground' => 'Interactive API testing and code generation',
            '⚠️ Error Scenarios' => 'Error handling and validation examples'
        ];
        
        return $descriptions[$folder] ?? 'API endpoints';
    }

    private function getRequestDescription($name, $method, $endpoint)
    {
        return "**{$method}** `{$endpoint}`\n\n{$name} - Real example with actual API response data.";
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
            501 => 'Not Implemented'
        ];
        
        return $statusTexts[$code] ?? 'Unknown';
    }

    private function finalizeCollection()
    {
        echo "📮 Finalizing Postman Collection...\n";
        
        // Update token in collection if we have one
        if ($this->token) {
            foreach ($this->postmanCollection['variable'] as &$variable) {
                if ($variable['key'] === 'api_token') {
                    $variable['value'] = $this->token;
                    break;
                }
            }
        }
        
        // Add collection-level documentation
        $this->postmanCollection['info']['description'] .= "\n\n## 🚀 Quick Start\n\n1. **Import this collection** into Postman\n2. **Set environment variables**:\n   - `base_url`: http://yukimart.local/api/v1\n   - `api_token`: Get from Login request\n3. **Run Login request** to get authentication token\n4. **Test other endpoints** with real examples\n\n## 📱 For Flutter Development\n\nThis collection provides real API response examples that you can use directly in your Flutter app development. Each request includes:\n\n- ✅ **Real request/response data**\n- ✅ **Proper authentication headers**\n- ✅ **Error handling examples**\n- ✅ **Pagination and filtering**\n\n## 🔧 Authentication\n\nMost endpoints require Bearer token authentication. Get your token from the Login endpoint and it will be automatically used in other requests.\n\n## 📊 Response Format\n\nAll API responses follow this standard format:\n\n```json\n{\n  \"success\": true,\n  \"message\": \"Operation successful\",\n  \"data\": {...},\n  \"meta\": {\n    \"timestamp\": \"2025-08-06T14:12:07Z\",\n    \"version\": \"v1\",\n    \"request_id\": \"unique-id\"\n  }\n}\n```";
        
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
        
        // Save enhanced Postman collection
        file_put_contents(
            $postmanDir . '/yukimart-api-enhanced.json',
            json_encode($this->postmanCollection, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
        
        echo "💾 Enhanced collection saved:\n";
        echo "   - Postman Collection: storage/testing/postman/yukimart-api-enhanced.json\n\n";
    }

    private function printSummary()
    {
        $totalRequests = 0;
        foreach ($this->postmanCollection['item'] as $folder) {
            $totalRequests += count($folder['item']);
        }
        
        echo "📊 Enhanced Collection Summary:\n";
        echo "==============================\n";
        echo "- Total Folders: " . count($this->postmanCollection['item']) . "\n";
        echo "- Total Requests: {$totalRequests}\n";
        echo "- Authentication Token: " . ($this->token ? 'Included' : 'Failed') . "\n";
        echo "- Real Response Examples: ✅ Included\n";
        echo "- Error Scenarios: ✅ Included\n";
        echo "- Documentation: ✅ Comprehensive\n\n";
        
        echo "🔗 Usage Instructions:\n";
        echo "1. Import: storage/testing/postman/yukimart-api-enhanced.json\n";
        echo "2. Set environment: base_url = http://yukimart.local/api/v1\n";
        echo "3. Run Login request to get authentication token\n";
        echo "4. Test all endpoints with real examples\n";
        echo "5. Use for Flutter development integration\n\n";
        
        echo "📱 Perfect for Flutter Development!\n";
        echo "- Real API response examples\n";
        echo "- Complete authentication flow\n";
        echo "- Error handling patterns\n";
        echo "- Pagination and filtering examples\n\n";
    }
}

// Generate enhanced collection
$generator = new EnhancedPostmanGenerator();
$generator->generateCollection();
