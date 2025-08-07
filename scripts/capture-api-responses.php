<?php

/**
 * YukiMart API Response Capture Script
 * 
 * This script makes real API calls and captures responses for Postman collection
 */

require_once __DIR__ . '/../vendor/autoload.php';

class ApiResponseCapture
{
    protected string $baseUrl;
    protected array $responses = [];
    protected ?string $authToken = null;

    public function __construct()
    {
        $this->baseUrl = 'http://yukimart.local/api/v1';
    }

    /**
     * Capture all API responses
     */
    public function captureResponses(): void
    {
        $this->printHeader();
        
        // Test basic endpoints
        $this->testHealthCheck();
        $this->testAuthentication();
        
        if ($this->authToken) {
            $this->testProducts();
            $this->testCustomers();
            $this->testOrders();
            $this->testPayments();
            $this->testPlayground();
        }
        
        $this->saveResponses();
        $this->generatePostmanCollection();
        $this->printSummary();
    }

    /**
     * Test health check endpoint
     */
    protected function testHealthCheck(): void
    {
        echo "🔍 Testing Health Check...\n";
        
        $response = $this->makeRequest('GET', '/health');
        $this->storeResponse('health_check_success', $response);
        
        echo "✅ Health check completed\n\n";
    }

    /**
     * Test authentication endpoints
     */
    protected function testAuthentication(): void
    {
        echo "🔐 Testing Authentication...\n";
        
        // Test login
        $loginData = [
            'email' => 'yukimart@gmail.com',
            'password' => '123456',
            'device_name' => 'API Test Device',
        ];
        
        $response = $this->makeRequest('POST', '/auth/login', $loginData);
        $this->storeResponse('auth_login_success', $response);
        
        if (isset($response['data']['token'])) {
            $this->authToken = $response['data']['token'];
            echo "✅ Login successful, token obtained\n";
            
            // Test profile
            $profileResponse = $this->makeRequest('GET', '/auth/me');
            $this->storeResponse('auth_me_success', $profileResponse);
            
        } else {
            echo "❌ Login failed\n";
        }
        
        // Test invalid login
        $invalidLogin = [
            'email' => 'invalid@example.com',
            'password' => 'wrongpassword',
            'device_name' => 'Test Device',
        ];
        
        $invalidResponse = $this->makeRequest('POST', '/auth/login', $invalidLogin);
        $this->storeResponse('auth_login_failure', $invalidResponse);
        
        echo "✅ Authentication tests completed\n\n";
    }

    /**
     * Test products endpoints
     */
    protected function testProducts(): void
    {
        echo "📦 Testing Products...\n";
        
        // Get products list
        $response = $this->makeRequest('GET', '/products');
        $this->storeResponse('products_list_success', $response);
        
        // Search products
        $searchResponse = $this->makeRequest('GET', '/products/search?q=test');
        $this->storeResponse('products_search_success', $searchResponse);
        
        // Test barcode lookup
        $barcodeResponse = $this->makeRequest('GET', '/products/barcode/1234567890');
        $this->storeResponse('products_barcode_not_found', $barcodeResponse);
        
        echo "✅ Products tests completed\n\n";
    }

    /**
     * Test customers endpoints
     */
    protected function testCustomers(): void
    {
        echo "👥 Testing Customers...\n";
        
        // Get customers list
        $response = $this->makeRequest('GET', '/customers');
        $this->storeResponse('customers_list_success', $response);
        
        // Search customers
        $searchResponse = $this->makeRequest('GET', '/customers/search?q=test');
        $this->storeResponse('customers_search_success', $searchResponse);
        
        echo "✅ Customers tests completed\n\n";
    }

    /**
     * Test orders endpoints
     */
    protected function testOrders(): void
    {
        echo "📋 Testing Orders...\n";
        
        // Get orders list
        $response = $this->makeRequest('GET', '/orders');
        $this->storeResponse('orders_list_success', $response);
        
        echo "✅ Orders tests completed\n\n";
    }

    /**
     * Test payments endpoints
     */
    protected function testPayments(): void
    {
        echo "💰 Testing Payments...\n";
        
        // Get payments list
        $response = $this->makeRequest('GET', '/payments');
        $this->storeResponse('payments_list_success', $response);
        
        // Get payment summary
        $summaryResponse = $this->makeRequest('GET', '/payments/summary');
        $this->storeResponse('payments_summary_success', $summaryResponse);
        
        echo "✅ Payments tests completed\n\n";
    }

    /**
     * Test playground endpoints
     */
    protected function testPlayground(): void
    {
        echo "🧪 Testing Playground...\n";
        
        // Test playground stats
        $response = $this->makeRequest('GET', '/playground/stats');
        $this->storeResponse('playground_stats_success', $response);
        
        // Test code generation
        $codeGenData = [
            'method' => 'GET',
            'endpoint' => '/health',
            'language' => 'dart',
        ];
        
        $codeResponse = $this->makeRequest('POST', '/playground/generate-code', $codeGenData);
        $this->storeResponse('playground_generate_code_success', $codeResponse);
        
        echo "✅ Playground tests completed\n\n";
    }

    /**
     * Make HTTP request
     */
    protected function makeRequest(string $method, string $endpoint, array $data = []): array
    {
        $url = $this->baseUrl . $endpoint;
        
        $ch = curl_init();
        
        $headers = [
            'Accept: application/json',
            'Content-Type: application/json',
        ];
        
        if ($this->authToken) {
            $headers[] = 'Authorization: Bearer ' . $this->authToken;
        }
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
        ]);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if (!empty($data)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        } elseif ($method === 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            if (!empty($data)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        } elseif ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return [
                'success' => false,
                'message' => 'cURL Error: ' . $error,
                'http_code' => 0,
            ];
        }
        
        $decodedResponse = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'success' => false,
                'message' => 'Invalid JSON response',
                'http_code' => $httpCode,
                'raw_response' => $response,
            ];
        }
        
        $decodedResponse['http_code'] = $httpCode;
        
        return $decodedResponse;
    }

    /**
     * Store API response
     */
    protected function storeResponse(string $key, array $response): void
    {
        $this->responses[$key] = [
            'response' => $response,
            'timestamp' => date('Y-m-d H:i:s'),
            'http_code' => $response['http_code'] ?? 200,
        ];
    }

    /**
     * Save responses to file
     */
    protected function saveResponses(): void
    {
        $responsesFile = 'storage/testing/api_responses.json';
        
        if (!file_exists(dirname($responsesFile))) {
            mkdir(dirname($responsesFile), 0755, true);
        }
        
        file_put_contents($responsesFile, json_encode($this->responses, JSON_PRETTY_PRINT));
        
        echo "💾 Responses saved to: {$responsesFile}\n";
    }

    /**
     * Generate Postman collection
     */
    protected function generatePostmanCollection(): void
    {
        $collection = [
            'info' => [
                'name' => 'YukiMart API v1 - Real Responses',
                'description' => 'Auto-generated collection with real API response examples',
                'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
            ],
            'variable' => [
                [
                    'key' => 'base_url',
                    'value' => $this->baseUrl,
                ],
                [
                    'key' => 'api_token',
                    'value' => $this->authToken ?? '',
                ],
            ],
            'item' => [],
        ];

        // Group endpoints
        $groups = [
            'Health Check' => ['health_check_'],
            'Authentication' => ['auth_'],
            'Products' => ['products_'],
            'Customers' => ['customers_'],
            'Orders' => ['orders_'],
            'Payments' => ['payments_'],
            'Playground' => ['playground_'],
        ];

        foreach ($groups as $groupName => $prefixes) {
            $groupItems = [];
            
            foreach ($this->responses as $key => $responseData) {
                foreach ($prefixes as $prefix) {
                    if (str_starts_with($key, $prefix)) {
                        $groupItems[] = $this->createPostmanItem($key, $responseData);
                        break;
                    }
                }
            }
            
            if (!empty($groupItems)) {
                $collection['item'][] = [
                    'name' => $groupName,
                    'item' => $groupItems,
                ];
            }
        }

        $collectionFile = 'storage/testing/postman/yukimart-api-real-responses.json';
        
        if (!file_exists(dirname($collectionFile))) {
            mkdir(dirname($collectionFile), 0755, true);
        }
        
        file_put_contents($collectionFile, json_encode($collection, JSON_PRETTY_PRINT));
        
        echo "📮 Postman collection generated: {$collectionFile}\n";
    }

    /**
     * Create Postman item
     */
    protected function createPostmanItem(string $key, array $responseData): array
    {
        // Map keys to endpoints
        $endpointMap = [
            'health_check_success' => ['GET', '/health'],
            'auth_login_success' => ['POST', '/auth/login'],
            'auth_login_failure' => ['POST', '/auth/login'],
            'auth_me_success' => ['GET', '/auth/me'],
            'products_list_success' => ['GET', '/products'],
            'products_search_success' => ['GET', '/products/search'],
            'customers_list_success' => ['GET', '/customers'],
            'orders_list_success' => ['GET', '/orders'],
            'payments_list_success' => ['GET', '/payments'],
            'playground_stats_success' => ['GET', '/playground/stats'],
        ];
        
        [$method, $endpoint] = $endpointMap[$key] ?? ['GET', '/unknown'];
        
        return [
            'name' => ucwords(str_replace('_', ' ', $key)),
            'request' => [
                'method' => $method,
                'header' => [
                    [
                        'key' => 'Accept',
                        'value' => 'application/json',
                    ],
                    [
                        'key' => 'Authorization',
                        'value' => 'Bearer {{api_token}}',
                    ],
                ],
                'url' => [
                    'raw' => '{{base_url}}' . $endpoint,
                    'host' => ['{{base_url}}'],
                    'path' => explode('/', trim($endpoint, '/')),
                ],
            ],
            'response' => [
                [
                    'name' => 'Example Response',
                    'status' => $responseData['http_code'] === 200 ? 'OK' : 'Error',
                    'code' => $responseData['http_code'],
                    'body' => json_encode($responseData['response'], JSON_PRETTY_PRINT),
                ],
            ],
        ];
    }

    /**
     * Print header
     */
    protected function printHeader(): void
    {
        echo "\n";
        echo "🚀 YukiMart API Response Capture\n";
        echo "================================\n\n";
    }

    /**
     * Print summary
     */
    protected function printSummary(): void
    {
        echo "\n";
        echo "🎉 API Response Capture Completed!\n";
        echo "==================================\n\n";
        
        echo "📊 Summary:\n";
        echo "- Total Responses Captured: " . count($this->responses) . "\n";
        echo "- Authentication Token: " . ($this->authToken ? 'Obtained' : 'Failed') . "\n\n";
        
        echo "📁 Generated Files:\n";
        echo "- API Responses: storage/testing/api_responses.json\n";
        echo "- Postman Collection: storage/testing/postman/yukimart-api-real-responses.json\n\n";
        
        echo "🔗 Next Steps:\n";
        echo "1. Import Postman collection into Postman\n";
        echo "2. Set environment variables (base_url, api_token)\n";
        echo "3. Test all endpoints with real examples\n\n";
    }
}

// Run the capture script
if (php_sapi_name() === 'cli') {
    $capture = new ApiResponseCapture();
    $capture->captureResponses();
}
