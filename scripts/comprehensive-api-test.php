<?php

/**
 * YukiMart API Comprehensive Test Script
 * Tests all endpoints with yukimart@gmail.com account and generates Postman collection
 */

require_once __DIR__ . '/../vendor/autoload.php';

class ComprehensiveApiTester
{
    private $baseUrl = 'http://yukimart.local/api/v1';
    private $email = 'yukimart@gmail.com';
    private $password = '123456';
    private $token = null;
    private $responses = [];
    private $postmanCollection = [];

    public function __construct()
    {
        echo "🚀 YukiMart API Comprehensive Testing\n";
        echo "=====================================\n\n";
        
        $this->initializePostmanCollection();
    }

    public function runAllTests()
    {
        try {
            // 1. Authentication Tests
            $this->testAuthentication();
            
            // 2. Products Tests
            $this->testProducts();
            
            // 3. Orders Tests
            $this->testOrders();
            
            // 4. Customers Tests
            $this->testCustomers();
            
            // 5. Payments Tests
            $this->testPayments();
            
            // 6. Error Scenarios
            $this->testErrorScenarios();
            
            // 7. Generate Postman Collection
            $this->generatePostmanCollection();
            
            // 8. Save Results
            $this->saveResults();
            
            echo "\n🎉 Comprehensive API Testing Completed!\n";
            echo "======================================\n\n";
            $this->printSummary();
            
        } catch (Exception $e) {
            echo "❌ Error: " . $e->getMessage() . "\n";
        }
    }

    private function testAuthentication()
    {
        echo "🔐 Testing Authentication...\n";
        
        // Login
        $loginResponse = $this->makeRequest('POST', '/auth/login', [
            'email' => $this->email,
            'password' => $this->password,
            'device_name' => 'Postman Collection Test'
        ]);
        
        $this->addResponse('auth_login_success', $loginResponse);
        $this->addPostmanRequest('Authentication', 'Login', 'POST', '/auth/login', [
            'email' => $this->email,
            'password' => $this->password,
            'device_name' => 'Postman Collection Test'
        ], $loginResponse);
        
        if ($loginResponse['success'] && isset($loginResponse['data']['token'])) {
            $this->token = $loginResponse['data']['token'];
            echo "✅ Login successful, token obtained\n";
        } else {
            throw new Exception("Login failed");
        }
        
        // Get Profile
        $profileResponse = $this->makeAuthenticatedRequest('GET', '/auth/me');
        $this->addResponse('auth_profile_success', $profileResponse);
        $this->addPostmanRequest('Authentication', 'Get Profile', 'GET', '/auth/me', null, $profileResponse, true);
        
        // Update Profile
        $updateData = [
            'full_name' => 'YukiMart Admin Updated',
            'phone' => '0123456789',
            'address' => 'YukiMart HQ - Updated'
        ];
        $updateResponse = $this->makeAuthenticatedRequest('PUT', '/auth/profile', $updateData);
        $this->addResponse('auth_update_profile', $updateResponse);
        $this->addPostmanRequest('Authentication', 'Update Profile', 'PUT', '/auth/profile', $updateData, $updateResponse, true);
        
        // Test Invalid Login
        $invalidResponse = $this->makeRequest('POST', '/auth/login', [
            'email' => $this->email,
            'password' => 'wrongpassword',
            'device_name' => 'Test'
        ]);
        $this->addResponse('auth_login_invalid', $invalidResponse);
        $this->addPostmanRequest('Authentication', 'Login Invalid', 'POST', '/auth/login', [
            'email' => $this->email,
            'password' => 'wrongpassword',
            'device_name' => 'Test'
        ], $invalidResponse);
        
        echo "✅ Authentication tests completed\n\n";
    }

    private function testProducts()
    {
        echo "📦 Testing Products...\n";
        
        // List Products
        $productsResponse = $this->makeAuthenticatedRequest('GET', '/products');
        $this->addResponse('products_list', $productsResponse);
        $this->addPostmanRequest('Products', 'List Products', 'GET', '/products', null, $productsResponse, true);
        
        // Search Products
        $searchResponse = $this->makeAuthenticatedRequest('GET', '/products?search=kem');
        $this->addResponse('products_search', $searchResponse);
        $this->addPostmanRequest('Products', 'Search Products', 'GET', '/products?search=kem', null, $searchResponse, true);
        
        // Get Product by ID (if products exist)
        if (!empty($productsResponse['data']) && count($productsResponse['data']) > 0) {
            $productId = $productsResponse['data'][0]['id'];
            $productResponse = $this->makeAuthenticatedRequest('GET', "/products/{$productId}");
            $this->addResponse('product_detail', $productResponse);
            $this->addPostmanRequest('Products', 'Get Product Detail', 'GET', "/products/{$productId}", null, $productResponse, true);
        }
        
        echo "✅ Products tests completed\n\n";
    }

    private function testOrders()
    {
        echo "📋 Testing Orders...\n";
        
        // List Orders
        $ordersResponse = $this->makeAuthenticatedRequest('GET', '/orders');
        $this->addResponse('orders_list', $ordersResponse);
        $this->addPostmanRequest('Orders', 'List Orders', 'GET', '/orders', null, $ordersResponse, true);
        
        // Get Order by ID (if orders exist)
        if (!empty($ordersResponse['data']) && count($ordersResponse['data']) > 0) {
            $orderId = $ordersResponse['data'][0]['id'];
            $orderResponse = $this->makeAuthenticatedRequest('GET', "/orders/{$orderId}");
            $this->addResponse('order_detail', $orderResponse);
            $this->addPostmanRequest('Orders', 'Get Order Detail', 'GET', "/orders/{$orderId}", null, $orderResponse, true);
        }
        
        echo "✅ Orders tests completed\n\n";
    }

    private function testCustomers()
    {
        echo "👥 Testing Customers...\n";
        
        // List Customers
        $customersResponse = $this->makeAuthenticatedRequest('GET', '/customers');
        $this->addResponse('customers_list', $customersResponse);
        $this->addPostmanRequest('Customers', 'List Customers', 'GET', '/customers', null, $customersResponse, true);
        
        // Search Customers
        $searchResponse = $this->makeAuthenticatedRequest('GET', '/customers?search=test');
        $this->addResponse('customers_search', $searchResponse);
        $this->addPostmanRequest('Customers', 'Search Customers', 'GET', '/customers?search=test', null, $searchResponse, true);
        
        echo "✅ Customers tests completed\n\n";
    }

    private function testPayments()
    {
        echo "💰 Testing Payments...\n";
        
        // List Payments
        $paymentsResponse = $this->makeAuthenticatedRequest('GET', '/payments');
        $this->addResponse('payments_list', $paymentsResponse);
        $this->addPostmanRequest('Payments', 'List Payments', 'GET', '/payments', null, $paymentsResponse, true);
        
        echo "✅ Payments tests completed\n\n";
    }

    private function testErrorScenarios()
    {
        echo "⚠️ Testing Error Scenarios...\n";
        
        // Unauthorized Access
        $unauthorizedResponse = $this->makeRequest('GET', '/auth/me');
        $this->addResponse('unauthorized_access', $unauthorizedResponse);
        $this->addPostmanRequest('Error Scenarios', 'Unauthorized Access', 'GET', '/auth/me', null, $unauthorizedResponse);
        
        // Not Found
        $notFoundResponse = $this->makeAuthenticatedRequest('GET', '/products/99999');
        $this->addResponse('not_found', $notFoundResponse);
        $this->addPostmanRequest('Error Scenarios', 'Product Not Found', 'GET', '/products/99999', null, $notFoundResponse, true);
        
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

    private function addResponse($key, $response)
    {
        $this->responses[$key] = [
            'response' => $response,
            'timestamp' => date('Y-m-d H:i:s'),
            'http_code' => $response['http_code'] ?? 0
        ];
    }

    private function initializePostmanCollection()
    {
        $this->postmanCollection = [
            'info' => [
                'name' => 'YukiMart API v1 - Comprehensive Collection',
                'description' => 'Complete API collection with real response examples for Flutter development',
                'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json'
            ],
            'variable' => [
                [
                    'key' => 'base_url',
                    'value' => $this->baseUrl
                ],
                [
                    'key' => 'api_token',
                    'value' => '{{token_from_login}}'
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
                        'value' => 'application/json'
                    ]
                ],
                'url' => [
                    'raw' => '{{base_url}}' . $endpoint,
                    'host' => ['{{base_url}}'],
                    'path' => array_filter(explode('/', $endpoint))
                ]
            ]
        ];
        
        if ($requiresAuth) {
            $request['request']['header'][] = [
                'key' => 'Authorization',
                'value' => 'Bearer {{api_token}}'
            ];
        }
        
        if ($body) {
            $request['request']['header'][] = [
                'key' => 'Content-Type',
                'value' => 'application/json'
            ];
            $request['request']['body'] = [
                'mode' => 'raw',
                'raw' => json_encode($body, JSON_PRETTY_PRINT)
            ];
        }
        
        if ($response) {
            $request['response'] = [[
                'name' => 'Example Response',
                'status' => $this->getStatusText($response['http_code'] ?? 200),
                'code' => $response['http_code'] ?? 200,
                'body' => json_encode($response, JSON_PRETTY_PRINT)
            ]];
        }
        
        $this->postmanCollection['item'][$folderIndex]['item'][] = $request;
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
            500 => 'Internal Server Error'
        ];
        
        return $statusTexts[$code] ?? 'Unknown';
    }

    private function generatePostmanCollection()
    {
        echo "📮 Generating Postman Collection...\n";
        
        // Update token in collection if we have one
        if ($this->token) {
            foreach ($this->postmanCollection['variable'] as &$variable) {
                if ($variable['key'] === 'api_token') {
                    $variable['value'] = $this->token;
                    break;
                }
            }
        }
        
        echo "✅ Postman collection generated\n\n";
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
        
        // Save responses
        file_put_contents(
            $testingDir . '/comprehensive_api_responses.json',
            json_encode($this->responses, JSON_PRETTY_PRINT)
        );
        
        // Save Postman collection
        file_put_contents(
            $postmanDir . '/yukimart-api-comprehensive.json',
            json_encode($this->postmanCollection, JSON_PRETTY_PRINT)
        );
        
        echo "💾 Results saved:\n";
        echo "   - API Responses: storage/testing/comprehensive_api_responses.json\n";
        echo "   - Postman Collection: storage/testing/postman/yukimart-api-comprehensive.json\n\n";
    }

    private function printSummary()
    {
        echo "📊 Test Summary:\n";
        echo "================\n";
        echo "- Total Responses Captured: " . count($this->responses) . "\n";
        echo "- Authentication Token: " . ($this->token ? 'Obtained' : 'Failed') . "\n";
        echo "- Postman Collection: Generated\n\n";
        
        echo "🔗 Next Steps:\n";
        echo "1. Import Postman collection: storage/testing/postman/yukimart-api-comprehensive.json\n";
        echo "2. Set environment variables (base_url, api_token)\n";
        echo "3. Test all endpoints with real examples\n";
        echo "4. Use for Flutter development integration\n\n";
    }
}

// Run the comprehensive test
$tester = new ComprehensiveApiTester();
$tester->runAllTests();
