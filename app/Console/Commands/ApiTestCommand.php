<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Exception;

class ApiTestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:test
                            {--endpoint= : Specific endpoint to test}
                            {--detailed : Show detailed responses}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test YukiMart API v1 endpoints';

    private $baseUrl;
    private $testCredentials;
    private $authToken;

    public function __construct()
    {
        parent::__construct();
        $this->baseUrl = config('postman.base_url', 'http://yukimart.local/api/v1');
        $this->testCredentials = config('postman.test_credentials');
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 YukiMart API v1 - Endpoint Testing');
        $this->info('====================================');
        $this->newLine();

        $endpoint = $this->option('endpoint');
        
        if ($endpoint) {
            $this->testSpecificEndpoint($endpoint);
        } else {
            $this->runFullTestSuite();
        }

        return 0;
    }

    /**
     * Run full test suite
     */
    private function runFullTestSuite()
    {
        $this->info('🚀 Running Full API Test Suite...');
        $this->newLine();

        $tests = [
            'Health Check' => [$this, 'testHealthCheck'],
            'Authentication - Login' => [$this, 'testLogin'],
            'Authentication - Profile' => [$this, 'testProfile'],
            'Authentication - Logout' => [$this, 'testLogout'],
            'Invoice - List' => [$this, 'testInvoiceList'],
            'Invoice - Statistics' => [$this, 'testInvoiceStatistics'],
            'Customer - List' => [$this, 'testCustomerList'],
            'Customer - Statistics' => [$this, 'testCustomerStatistics'],
            'Product - List' => [$this, 'testProductList'],
            'Product - Search Barcode' => [$this, 'testProductSearchBarcode'],
            'Order - List' => [$this, 'testOrderList'],
            'Payment - List' => [$this, 'testPaymentList'],
            'Payment - Statistics' => [$this, 'testPaymentStatistics'],
            'Dashboard - Index' => [$this, 'testDashboardIndex'],
            'Dashboard - Stats' => [$this, 'testDashboardStats'],
            'Dashboard - Recent Orders' => [$this, 'testDashboardRecentOrders'],
            'Error - Unauthorized' => [$this, 'testUnauthorized'],
            'Error - Not Found' => [$this, 'testNotFound'],
        ];

        $passed = 0;
        $total = count($tests);

        foreach ($tests as $testName => $testMethod) {
            $this->line("Testing: {$testName}");
            
            try {
                $result = call_user_func($testMethod);
                if ($result['success']) {
                    $this->line("   ✅ PASS - {$result['message']}");
                    $passed++;
                } else {
                    $this->line("   ❌ FAIL - {$result['message']}");
                }
                
                if ($this->option('detailed') && isset($result['response'])) {
                    $this->line("   📄 Response: " . json_encode($result['response'], JSON_PRETTY_PRINT));
                }
                
            } catch (Exception $e) {
                $this->line("   ❌ ERROR - {$e->getMessage()}");
            }
            
            $this->newLine();
        }

        // Summary
        $this->info('📊 Test Results Summary:');
        $this->info('========================');
        $this->line("   - Total Tests: {$total}");
        $this->line("   - Passed: {$passed}");
        $this->line("   - Failed: " . ($total - $passed));
        $this->line("   - Success Rate: " . round(($passed / $total) * 100, 2) . "%");
        
        if ($passed === $total) {
            $this->info('🎉 All tests passed! API is working perfectly.');
        } else {
            $this->warn('⚠️  Some tests failed. Please check the API configuration.');
        }
    }

    /**
     * Test specific endpoint
     */
    private function testSpecificEndpoint($endpoint)
    {
        $this->info("🎯 Testing specific endpoint: {$endpoint}");
        $this->newLine();

        $testMethods = [
            'health' => 'testHealthCheck',
            'login' => 'testLogin',
            'profile' => 'testProfile',
            'logout' => 'testLogout',
            'invoices' => 'testInvoiceList',
            'invoice-statistics' => 'testInvoiceStatistics',
            'customers' => 'testCustomerList',
            'customer-statistics' => 'testCustomerStatistics',
            'products' => 'testProductList',
            'product-barcode' => 'testProductSearchBarcode',
            'orders' => 'testOrderList',
            'payments' => 'testPaymentList',
            'payment-statistics' => 'testPaymentStatistics',
            'dashboard' => 'testDashboardIndex',
            'dashboard-stats' => 'testDashboardStats',
            'dashboard-recent-orders' => 'testDashboardRecentOrders',
        ];

        if (isset($testMethods[$endpoint])) {
            $method = $testMethods[$endpoint];
            $result = $this->$method();
            
            if ($result['success']) {
                $this->info("✅ SUCCESS - {$result['message']}");
            } else {
                $this->error("❌ FAILED - {$result['message']}");
            }
            
            if ($this->option('detailed') && isset($result['response'])) {
                $this->line("📄 Response:");
                $this->line(json_encode($result['response'], JSON_PRETTY_PRINT));
            }
        } else {
            $this->error("Unknown endpoint: {$endpoint}");
            $this->line("Available endpoints: " . implode(', ', array_keys($testMethods)));
        }
    }

    /**
     * Test health check endpoint
     */
    private function testHealthCheck()
    {
        try {
            $response = Http::timeout(10)->get("{$this->baseUrl}/health");
            
            if ($response->successful()) {
                $data = $response->json();
                if ($data['status'] === 'healthy') {
                    return [
                        'success' => true,
                        'message' => 'Health check passed',
                        'response' => $data
                    ];
                }
            }
            
            return [
                'success' => false,
                'message' => 'Health check failed - Invalid response',
                'response' => $response->json()
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Health check failed - ' . $e->getMessage()
            ];
        }
    }

    /**
     * Test login endpoint
     */
    private function testLogin()
    {
        try {
            $response = Http::timeout(10)->post("{$this->baseUrl}/auth/login", [
                'email' => $this->testCredentials['email'],
                'password' => $this->testCredentials['password'],
                'device_name' => 'API Test'
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                if ($data['status'] === 'success' && isset($data['data']['token'])) {
                    $this->authToken = $data['data']['token'];
                    return [
                        'success' => true,
                        'message' => 'Login successful, token received',
                        'response' => $data
                    ];
                }
            }
            
            return [
                'success' => false,
                'message' => 'Login failed - Invalid response',
                'response' => $response->json()
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Login failed - ' . $e->getMessage()
            ];
        }
    }

    /**
     * Test profile endpoint
     */
    private function testProfile()
    {
        if (!$this->authToken) {
            $loginResult = $this->testLogin();
            if (!$loginResult['success']) {
                return [
                    'success' => false,
                    'message' => 'Cannot test profile - Login failed'
                ];
            }
        }

        try {
            $response = Http::timeout(10)
                ->withToken($this->authToken)
                ->get("{$this->baseUrl}/auth/profile");
            
            if ($response->successful()) {
                $data = $response->json();
                if ($data['status'] === 'success' && isset($data['data']['user'])) {
                    return [
                        'success' => true,
                        'message' => 'Profile retrieved successfully',
                        'response' => $data
                    ];
                }
            }
            
            return [
                'success' => false,
                'message' => 'Profile failed - Invalid response',
                'response' => $response->json()
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Profile failed - ' . $e->getMessage()
            ];
        }
    }

    /**
     * Test logout endpoint
     */
    private function testLogout()
    {
        if (!$this->authToken) {
            $loginResult = $this->testLogin();
            if (!$loginResult['success']) {
                return [
                    'success' => false,
                    'message' => 'Cannot test logout - Login failed'
                ];
            }
        }

        try {
            $response = Http::timeout(10)
                ->withToken($this->authToken)
                ->post("{$this->baseUrl}/auth/logout");
            
            if ($response->successful()) {
                $data = $response->json();
                if ($data['status'] === 'success') {
                    $this->authToken = null; // Clear token
                    return [
                        'success' => true,
                        'message' => 'Logout successful',
                        'response' => $data
                    ];
                }
            }
            
            return [
                'success' => false,
                'message' => 'Logout failed - Invalid response',
                'response' => $response->json()
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Logout failed - ' . $e->getMessage()
            ];
        }
    }

    /**
     * Test invoice list endpoint
     */
    private function testInvoiceList()
    {
        if (!$this->authToken) {
            $loginResult = $this->testLogin();
            if (!$loginResult['success']) {
                return [
                    'success' => false,
                    'message' => 'Cannot test invoices - Login failed'
                ];
            }
        }

        try {
            $response = Http::timeout(10)
                ->withToken($this->authToken)
                ->get("{$this->baseUrl}/invoices");
            
            if ($response->successful()) {
                $data = $response->json();
                if ($data['status'] === 'success' && isset($data['meta'])) {
                    return [
                        'success' => true,
                        'message' => 'Invoice list retrieved successfully',
                        'response' => $data
                    ];
                }
            }
            
            return [
                'success' => false,
                'message' => 'Invoice list failed - Invalid response',
                'response' => $response->json()
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Invoice list failed - ' . $e->getMessage()
            ];
        }
    }

    /**
     * Test invoice statistics endpoint
     */
    private function testInvoiceStatistics()
    {
        if (!$this->authToken) {
            $loginResult = $this->testLogin();
            if (!$loginResult['success']) {
                return [
                    'success' => false,
                    'message' => 'Cannot test statistics - Login failed'
                ];
            }
        }

        try {
            $response = Http::timeout(10)
                ->withToken($this->authToken)
                ->get("{$this->baseUrl}/invoices/statistics");
            
            if ($response->successful()) {
                $data = $response->json();
                if ($data['status'] === 'success' && isset($data['data'])) {
                    return [
                        'success' => true,
                        'message' => 'Invoice statistics retrieved successfully',
                        'response' => $data
                    ];
                }
            }
            
            return [
                'success' => false,
                'message' => 'Invoice statistics failed - Invalid response',
                'response' => $response->json()
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Invoice statistics failed - ' . $e->getMessage()
            ];
        }
    }

    /**
     * Test unauthorized access
     */
    private function testUnauthorized()
    {
        try {
            $response = Http::timeout(10)->get("{$this->baseUrl}/auth/profile");
            
            if ($response->status() === 401) {
                return [
                    'success' => true,
                    'message' => 'Unauthorized access properly blocked',
                    'response' => $response->json()
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Unauthorized access not properly blocked',
                'response' => $response->json()
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Unauthorized test failed - ' . $e->getMessage()
            ];
        }
    }

    /**
     * Test not found error
     */
    private function testNotFound()
    {
        if (!$this->authToken) {
            $loginResult = $this->testLogin();
            if (!$loginResult['success']) {
                return [
                    'success' => false,
                    'message' => 'Cannot test not found - Login failed'
                ];
            }
        }

        try {
            $response = Http::timeout(10)
                ->withToken($this->authToken)
                ->get("{$this->baseUrl}/invoices/99999");
            
            if ($response->status() === 404) {
                return [
                    'success' => true,
                    'message' => 'Not found error properly returned',
                    'response' => $response->json()
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Not found error not properly returned',
                'response' => $response->json()
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Not found test failed - ' . $e->getMessage()
            ];
        }
    }

    /**
     * Test customer list endpoint
     */
    private function testCustomerList()
    {
        if (!$this->authToken) {
            $loginResult = $this->testLogin();
            if (!$loginResult['success']) {
                return [
                    'success' => false,
                    'message' => 'Cannot test customers - Login failed'
                ];
            }
        }

        try {
            $response = Http::timeout(10)
                ->withToken($this->authToken)
                ->get("{$this->baseUrl}/customers");

            if ($response->successful()) {
                $data = $response->json();
                if ($data['status'] === 'success' && isset($data['meta'])) {
                    return [
                        'success' => true,
                        'message' => 'Customer list retrieved successfully',
                        'response' => $data
                    ];
                }
            }

            return [
                'success' => false,
                'message' => 'Customer list failed - Invalid response',
                'response' => $response->json()
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Customer list failed - ' . $e->getMessage()
            ];
        }
    }

    /**
     * Test customer statistics endpoint
     */
    private function testCustomerStatistics()
    {
        if (!$this->authToken) {
            $loginResult = $this->testLogin();
            if (!$loginResult['success']) {
                return [
                    'success' => false,
                    'message' => 'Cannot test customer statistics - Login failed'
                ];
            }
        }

        try {
            $response = Http::timeout(10)
                ->withToken($this->authToken)
                ->get("{$this->baseUrl}/customers/statistics");

            if ($response->successful()) {
                $data = $response->json();
                if ($data['status'] === 'success' && isset($data['data'])) {
                    return [
                        'success' => true,
                        'message' => 'Customer statistics retrieved successfully',
                        'response' => $data
                    ];
                }
            }

            return [
                'success' => false,
                'message' => 'Customer statistics failed - Invalid response',
                'response' => $response->json()
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Customer statistics failed - ' . $e->getMessage()
            ];
        }
    }

    /**
     * Test product list endpoint
     */
    private function testProductList()
    {
        if (!$this->authToken) {
            $loginResult = $this->testLogin();
            if (!$loginResult['success']) {
                return [
                    'success' => false,
                    'message' => 'Cannot test products - Login failed'
                ];
            }
        }

        try {
            $response = Http::timeout(10)
                ->withToken($this->authToken)
                ->get("{$this->baseUrl}/products");

            if ($response->successful()) {
                $data = $response->json();
                if ($data['status'] === 'success' && isset($data['meta'])) {
                    return [
                        'success' => true,
                        'message' => 'Product list retrieved successfully',
                        'response' => $data
                    ];
                }
            }

            return [
                'success' => false,
                'message' => 'Product list failed - Invalid response',
                'response' => $response->json()
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Product list failed - ' . $e->getMessage()
            ];
        }
    }

    /**
     * Test product search by barcode endpoint
     */
    private function testProductSearchBarcode()
    {
        if (!$this->authToken) {
            $loginResult = $this->testLogin();
            if (!$loginResult['success']) {
                return [
                    'success' => false,
                    'message' => 'Cannot test product barcode search - Login failed'
                ];
            }
        }

        try {
            $response = Http::timeout(10)
                ->withToken($this->authToken)
                ->get("{$this->baseUrl}/products/search-barcode?barcode=1234567890");

            // This endpoint might return 404 if no product found, which is valid
            if ($response->successful() || $response->status() === 404) {
                $data = $response->json();
                if (($data['status'] === 'success' && isset($data['data'])) ||
                    ($data['status'] === 'error' && $data['message'] === 'Product not found')) {
                    return [
                        'success' => true,
                        'message' => 'Product barcode search working correctly',
                        'response' => $data
                    ];
                }
            }

            return [
                'success' => false,
                'message' => 'Product barcode search failed - Invalid response',
                'response' => $response->json()
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Product barcode search failed - ' . $e->getMessage()
            ];
        }
    }

    /**
     * Test order list endpoint
     */
    private function testOrderList()
    {
        if (!$this->authToken) {
            $loginResult = $this->testLogin();
            if (!$loginResult['success']) {
                return [
                    'success' => false,
                    'message' => 'Cannot test orders - Login failed'
                ];
            }
        }

        try {
            $response = Http::timeout(10)
                ->withToken($this->authToken)
                ->get("{$this->baseUrl}/orders");

            if ($response->successful()) {
                $data = $response->json();
                if ($data['status'] === 'success' && isset($data['meta'])) {
                    return [
                        'success' => true,
                        'message' => 'Order list retrieved successfully',
                        'response' => $data
                    ];
                }
            }

            return [
                'success' => false,
                'message' => 'Order list failed - Invalid response',
                'response' => $response->json()
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Order list failed - ' . $e->getMessage()
            ];
        }
    }

    /**
     * Test payment list endpoint
     */
    private function testPaymentList()
    {
        if (!$this->authToken) {
            $loginResult = $this->testLogin();
            if (!$loginResult['success']) {
                return [
                    'success' => false,
                    'message' => 'Cannot test payments - Login failed'
                ];
            }
        }

        try {
            $response = Http::timeout(10)
                ->withToken($this->authToken)
                ->get("{$this->baseUrl}/payments");

            if ($response->successful()) {
                $data = $response->json();
                if ($data['status'] === 'success' && isset($data['meta'])) {
                    return [
                        'success' => true,
                        'message' => 'Payment list retrieved successfully',
                        'response' => $data
                    ];
                }
            }

            return [
                'success' => false,
                'message' => 'Payment list failed - Invalid response',
                'response' => $response->json()
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Payment list failed - ' . $e->getMessage()
            ];
        }
    }

    /**
     * Test payment statistics endpoint
     */
    private function testPaymentStatistics()
    {
        if (!$this->authToken) {
            $loginResult = $this->testLogin();
            if (!$loginResult['success']) {
                return [
                    'success' => false,
                    'message' => 'Cannot test payment statistics - Login failed'
                ];
            }
        }

        try {
            $response = Http::timeout(10)
                ->withToken($this->authToken)
                ->get("{$this->baseUrl}/payments/statistics");

            if ($response->successful()) {
                $data = $response->json();
                if ($data['status'] === 'success' && isset($data['data'])) {
                    return [
                        'success' => true,
                        'message' => 'Payment statistics retrieved successfully',
                        'response' => $data
                    ];
                }
            }

            return [
                'success' => false,
                'message' => 'Payment statistics failed - Invalid response',
                'response' => $response->json()
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Payment statistics failed - ' . $e->getMessage()
            ];
        }
    }

    /**
     * Test dashboard index endpoint
     */
    private function testDashboardIndex()
    {
        if (!$this->authToken) {
            $loginResult = $this->testLogin();
            if (!$loginResult['success']) {
                return [
                    'success' => false,
                    'message' => 'Cannot test dashboard - Login failed'
                ];
            }
        }

        try {
            $response = Http::timeout(10)
                ->withToken($this->authToken)
                ->get("{$this->baseUrl}/dashboard");

            if ($response->successful()) {
                $data = $response->json();
                if ($data['status'] === 'success' && isset($data['data']['statistics'])) {
                    return [
                        'success' => true,
                        'message' => 'Dashboard index retrieved successfully',
                        'response' => $data
                    ];
                }
            }

            return [
                'success' => false,
                'message' => 'Dashboard index failed - Invalid response',
                'response' => $response->json()
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Dashboard index failed - ' . $e->getMessage()
            ];
        }
    }

    /**
     * Test dashboard stats endpoint
     */
    private function testDashboardStats()
    {
        if (!$this->authToken) {
            $loginResult = $this->testLogin();
            if (!$loginResult['success']) {
                return [
                    'success' => false,
                    'message' => 'Cannot test dashboard stats - Login failed'
                ];
            }
        }

        try {
            $response = Http::timeout(10)
                ->withToken($this->authToken)
                ->get("{$this->baseUrl}/dashboard/stats");

            if ($response->successful()) {
                $data = $response->json();
                if ($data['status'] === 'success' && isset($data['data'])) {
                    return [
                        'success' => true,
                        'message' => 'Dashboard stats retrieved successfully',
                        'response' => $data
                    ];
                }
            }

            return [
                'success' => false,
                'message' => 'Dashboard stats failed - Invalid response',
                'response' => $response->json()
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Dashboard stats failed - ' . $e->getMessage()
            ];
        }
    }

    /**
     * Test dashboard recent orders endpoint
     */
    private function testDashboardRecentOrders()
    {
        if (!$this->authToken) {
            $loginResult = $this->testLogin();
            if (!$loginResult['success']) {
                return [
                    'success' => false,
                    'message' => 'Cannot test dashboard recent orders - Login failed'
                ];
            }
        }

        try {
            $response = Http::timeout(10)
                ->withToken($this->authToken)
                ->get("{$this->baseUrl}/dashboard/recent-orders?limit=5");

            if ($response->successful()) {
                $data = $response->json();
                if ($data['status'] === 'success' && isset($data['data'])) {
                    return [
                        'success' => true,
                        'message' => 'Dashboard recent orders retrieved successfully',
                        'response' => $data
                    ];
                }
            }

            return [
                'success' => false,
                'message' => 'Dashboard recent orders failed - Invalid response',
                'response' => $response->json()
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Dashboard recent orders failed - ' . $e->getMessage()
            ];
        }
    }
}
