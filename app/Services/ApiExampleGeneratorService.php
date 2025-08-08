<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

class ApiExampleGeneratorService
{
    private $baseUrl;
    private $authToken;
    private $testCredentials;

    public function __construct()
    {
        $this->baseUrl = config('app.url') . '/api/v1';
        $this->testCredentials = [
            'email' => config('postman.test_credentials.email', 'yukimart@gmail.com'),
            'password' => config('postman.test_credentials.password', '123456')
        ];
    }

    /**
     * Generate comprehensive examples for all endpoints
     */
    public function generateAllExamples(): array
    {
        $examples = [];
        
        try {
            // Get authentication token first
            $this->authenticate();
            
            // Generate examples for each endpoint group
            $examples['health'] = $this->generateHealthExamples();
            $examples['auth'] = $this->generateAuthExamples();
            $examples['dashboard'] = $this->generateDashboardExamples();
            $examples['invoices'] = $this->generateInvoiceExamples();
            $examples['products'] = $this->generateProductExamples();
            $examples['customers'] = $this->generateCustomerExamples();
            $examples['orders'] = $this->generateOrderExamples();
            $examples['payments'] = $this->generatePaymentExamples();
            
            return $examples;
        } catch (Exception $e) {
            Log::error('Failed to generate API examples: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Authenticate and get token
     */
    private function authenticate(): void
    {
        try {
            $response = Http::post($this->baseUrl . '/auth/login', $this->testCredentials);
            
            if ($response->successful()) {
                $data = $response->json();
                $this->authToken = $data['data']['access_token'] ?? null;
                
                if (!$this->authToken) {
                    throw new Exception('No access token received');
                }
            } else {
                throw new Exception('Authentication failed: ' . $response->body());
            }
        } catch (Exception $e) {
            Log::error('Authentication failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate Health Check examples
     */
    private function generateHealthExamples(): array
    {
        return [
            'health_check' => [
                'name' => 'Health Check',
                'method' => 'GET',
                'url' => '/health',
                'headers' => ['Accept' => 'application/json'],
                'examples' => [
                    'success' => $this->makeRequest('GET', '/health'),
                ]
            ]
        ];
    }

    /**
     * Generate Authentication examples
     */
    private function generateAuthExamples(): array
    {
        return [
            'login' => [
                'name' => 'User Login',
                'method' => 'POST',
                'url' => '/auth/login',
                'headers' => ['Accept' => 'application/json', 'Content-Type' => 'application/json'],
                'body' => $this->testCredentials,
                'examples' => [
                    'success' => $this->makeRequest('POST', '/auth/login', $this->testCredentials),
                    'invalid_credentials' => $this->makeRequest('POST', '/auth/login', [
                        'email' => 'wrong@email.com',
                        'password' => 'wrongpassword'
                    ]),
                    'validation_error' => $this->makeRequest('POST', '/auth/login', [
                        'email' => '',
                        'password' => ''
                    ])
                ]
            ],
            'profile' => [
                'name' => 'Get User Profile',
                'method' => 'GET',
                'url' => '/auth/profile',
                'headers' => $this->getAuthHeaders(),
                'examples' => [
                    'success' => $this->makeAuthenticatedRequest('GET', '/auth/profile'),
                    'unauthorized' => $this->makeRequest('GET', '/auth/profile', null, ['Authorization' => 'Bearer invalid_token'])
                ]
            ],
            'logout' => [
                'name' => 'User Logout',
                'method' => 'POST',
                'url' => '/auth/logout',
                'headers' => $this->getAuthHeaders(),
                'examples' => [
                    'success' => $this->makeAuthenticatedRequest('POST', '/auth/logout')
                ]
            ],
            'refresh' => [
                'name' => 'Refresh Token',
                'method' => 'POST',
                'url' => '/auth/refresh',
                'headers' => $this->getAuthHeaders(),
                'examples' => [
                    'success' => $this->makeAuthenticatedRequest('POST', '/auth/refresh')
                ]
            ]
        ];
    }

    /**
     * Generate Dashboard examples
     */
    private function generateDashboardExamples(): array
    {
        return [
            'index' => [
                'name' => 'Dashboard Overview',
                'method' => 'GET',
                'url' => '/dashboard',
                'headers' => $this->getAuthHeaders(),
                'examples' => [
                    'success' => $this->makeAuthenticatedRequest('GET', '/dashboard')
                ]
            ],
            'stats' => [
                'name' => 'Dashboard Statistics',
                'method' => 'GET',
                'url' => '/dashboard/stats',
                'headers' => $this->getAuthHeaders(),
                'query_params' => ['period' => 'month'],
                'examples' => [
                    'monthly_stats' => $this->makeAuthenticatedRequest('GET', '/dashboard/stats?period=month'),
                    'daily_stats' => $this->makeAuthenticatedRequest('GET', '/dashboard/stats?period=today'),
                    'yearly_stats' => $this->makeAuthenticatedRequest('GET', '/dashboard/stats?period=year')
                ]
            ],
            'recent_orders' => [
                'name' => 'Recent Orders',
                'method' => 'GET',
                'url' => '/dashboard/recent-orders',
                'headers' => $this->getAuthHeaders(),
                'query_params' => ['limit' => 10],
                'examples' => [
                    'success' => $this->makeAuthenticatedRequest('GET', '/dashboard/recent-orders?limit=10')
                ]
            ],
            'top_products' => [
                'name' => 'Top Products',
                'method' => 'GET',
                'url' => '/dashboard/top-products',
                'headers' => $this->getAuthHeaders(),
                'query_params' => ['type' => 'revenue', 'period' => 'month', 'limit' => 10],
                'examples' => [
                    'by_revenue' => $this->makeAuthenticatedRequest('GET', '/dashboard/top-products?type=revenue&period=month&limit=10'),
                    'by_quantity' => $this->makeAuthenticatedRequest('GET', '/dashboard/top-products?type=quantity&period=month&limit=10')
                ]
            ],
            'revenue_data' => [
                'name' => 'Revenue Data',
                'method' => 'GET',
                'url' => '/dashboard/revenue-data',
                'headers' => $this->getAuthHeaders(),
                'query_params' => ['period' => 'month'],
                'examples' => [
                    'monthly_revenue' => $this->makeAuthenticatedRequest('GET', '/dashboard/revenue-data?period=month'),
                    'daily_revenue' => $this->makeAuthenticatedRequest('GET', '/dashboard/revenue-data?period=today')
                ]
            ],
            'low_stock_products' => [
                'name' => 'Low Stock Products',
                'method' => 'GET',
                'url' => '/dashboard/low-stock-products',
                'headers' => $this->getAuthHeaders(),
                'query_params' => ['limit' => 20],
                'examples' => [
                    'success' => $this->makeAuthenticatedRequest('GET', '/dashboard/low-stock-products?limit=20')
                ]
            ]
        ];
    }

    /**
     * Generate Invoice examples
     */
    private function generateInvoiceExamples(): array
    {
        return [
            'list' => [
                'name' => 'List Invoices',
                'method' => 'GET',
                'url' => '/invoices',
                'headers' => $this->getAuthHeaders(),
                'query_params' => ['page' => 1, 'per_page' => 15, 'search' => '', 'status' => ''],
                'examples' => [
                    'all_invoices' => $this->makeAuthenticatedRequest('GET', '/invoices?page=1&per_page=15'),
                    'search_invoices' => $this->makeAuthenticatedRequest('GET', '/invoices?search=INV-2025&page=1'),
                    'filter_by_status' => $this->makeAuthenticatedRequest('GET', '/invoices?status=completed&page=1')
                ]
            ],
            'show' => [
                'name' => 'Get Invoice Details',
                'method' => 'GET',
                'url' => '/invoices/{id}',
                'headers' => $this->getAuthHeaders(),
                'examples' => [
                    'success' => $this->makeAuthenticatedRequest('GET', '/invoices/1'),
                    'not_found' => $this->makeAuthenticatedRequest('GET', '/invoices/99999')
                ]
            ],
            'statistics' => [
                'name' => 'Invoice Statistics',
                'method' => 'GET',
                'url' => '/invoices/statistics',
                'headers' => $this->getAuthHeaders(),
                'query_params' => ['period' => 'month'],
                'examples' => [
                    'monthly_stats' => $this->makeAuthenticatedRequest('GET', '/invoices/statistics?period=month'),
                    'yearly_stats' => $this->makeAuthenticatedRequest('GET', '/invoices/statistics?period=year')
                ]
            ]
        ];
    }

    /**
     * Generate Product examples
     */
    private function generateProductExamples(): array
    {
        return [
            'list' => [
                'name' => 'List Products',
                'method' => 'GET',
                'url' => '/products',
                'headers' => $this->getAuthHeaders(),
                'query_params' => ['page' => 1, 'per_page' => 15, 'search' => '', 'category' => ''],
                'examples' => [
                    'all_products' => $this->makeAuthenticatedRequest('GET', '/products?page=1&per_page=15'),
                    'search_products' => $this->makeAuthenticatedRequest('GET', '/products?search=kem&page=1'),
                    'filter_by_category' => $this->makeAuthenticatedRequest('GET', '/products?category=cosmetics&page=1')
                ]
            ],
            'show' => [
                'name' => 'Get Product Details',
                'method' => 'GET',
                'url' => '/products/{id}',
                'headers' => $this->getAuthHeaders(),
                'examples' => [
                    'success' => $this->makeAuthenticatedRequest('GET', '/products/1'),
                    'not_found' => $this->makeAuthenticatedRequest('GET', '/products/99999')
                ]
            ],
            'search_barcode' => [
                'name' => 'Search by Barcode',
                'method' => 'GET',
                'url' => '/products/search-barcode',
                'headers' => $this->getAuthHeaders(),
                'query_params' => ['barcode' => ''],
                'examples' => [
                    'found' => $this->makeAuthenticatedRequest('GET', '/products/search-barcode?barcode=4987415993461'),
                    'not_found' => $this->makeAuthenticatedRequest('GET', '/products/search-barcode?barcode=0000000000000')
                ]
            ]
        ];
    }

    /**
     * Make authenticated request
     */
    private function makeAuthenticatedRequest(string $method, string $endpoint, array $data = null): array
    {
        return $this->makeRequest($method, $endpoint, $data, $this->getAuthHeaders());
    }

    /**
     * Make HTTP request and return formatted response
     */
    private function makeRequest(string $method, string $endpoint, array $data = null, array $headers = []): array
    {
        try {
            $url = $this->baseUrl . $endpoint;
            $defaultHeaders = ['Accept' => 'application/json'];
            $headers = array_merge($defaultHeaders, $headers);

            $response = Http::withHeaders($headers)->send($method, $url, [
                'json' => $data
            ]);

            return [
                'status_code' => $response->status(),
                'headers' => $response->headers(),
                'body' => $response->json() ?: $response->body(),
                'success' => $response->successful()
            ];
        } catch (Exception $e) {
            return [
                'status_code' => 500,
                'headers' => [],
                'body' => ['error' => $e->getMessage()],
                'success' => false
            ];
        }
    }

    /**
     * Generate Customer examples
     */
    private function generateCustomerExamples(): array
    {
        return [
            'list' => [
                'name' => 'List Customers',
                'method' => 'GET',
                'url' => '/customers',
                'headers' => $this->getAuthHeaders(),
                'query_params' => ['page' => 1, 'per_page' => 15, 'search' => ''],
                'examples' => [
                    'all_customers' => $this->makeAuthenticatedRequest('GET', '/customers?page=1&per_page=15'),
                    'search_customers' => $this->makeAuthenticatedRequest('GET', '/customers?search=Nguyen&page=1')
                ]
            ],
            'show' => [
                'name' => 'Get Customer Details',
                'method' => 'GET',
                'url' => '/customers/{id}',
                'headers' => $this->getAuthHeaders(),
                'examples' => [
                    'success' => $this->makeAuthenticatedRequest('GET', '/customers/1'),
                    'not_found' => $this->makeAuthenticatedRequest('GET', '/customers/99999')
                ]
            ],
            'create' => [
                'name' => 'Create Customer',
                'method' => 'POST',
                'url' => '/customers',
                'headers' => $this->getAuthHeaders(),
                'body' => [
                    'name' => 'Nguyễn Văn A',
                    'phone' => '0987654321',
                    'email' => 'customer@example.com',
                    'address' => 'Hà Nội, Việt Nam'
                ],
                'examples' => [
                    'success' => $this->makeAuthenticatedRequest('POST', '/customers', [
                        'name' => 'Nguyễn Văn A',
                        'phone' => '0987654321',
                        'email' => 'customer@example.com',
                        'address' => 'Hà Nội, Việt Nam'
                    ]),
                    'validation_error' => $this->makeAuthenticatedRequest('POST', '/customers', [
                        'name' => '',
                        'phone' => 'invalid_phone'
                    ])
                ]
            ],
            'statistics' => [
                'name' => 'Customer Statistics',
                'method' => 'GET',
                'url' => '/customers/statistics',
                'headers' => $this->getAuthHeaders(),
                'query_params' => ['period' => 'month'],
                'examples' => [
                    'monthly_stats' => $this->makeAuthenticatedRequest('GET', '/customers/statistics?period=month')
                ]
            ]
        ];
    }

    /**
     * Generate Order examples
     */
    private function generateOrderExamples(): array
    {
        return [
            'list' => [
                'name' => 'List Orders',
                'method' => 'GET',
                'url' => '/orders',
                'headers' => $this->getAuthHeaders(),
                'query_params' => ['page' => 1, 'per_page' => 15, 'status' => ''],
                'examples' => [
                    'all_orders' => $this->makeAuthenticatedRequest('GET', '/orders?page=1&per_page=15'),
                    'filter_by_status' => $this->makeAuthenticatedRequest('GET', '/orders?status=completed&page=1')
                ]
            ],
            'show' => [
                'name' => 'Get Order Details',
                'method' => 'GET',
                'url' => '/orders/{id}',
                'headers' => $this->getAuthHeaders(),
                'examples' => [
                    'success' => $this->makeAuthenticatedRequest('GET', '/orders/1'),
                    'not_found' => $this->makeAuthenticatedRequest('GET', '/orders/99999')
                ]
            ],
            'create' => [
                'name' => 'Create Order',
                'method' => 'POST',
                'url' => '/orders',
                'headers' => $this->getAuthHeaders(),
                'body' => [
                    'customer_id' => 1,
                    'items' => [
                        [
                            'product_id' => 1,
                            'quantity' => 2,
                            'price' => 100000
                        ]
                    ],
                    'notes' => 'Giao hàng nhanh'
                ],
                'examples' => [
                    'success' => $this->makeAuthenticatedRequest('POST', '/orders', [
                        'customer_id' => 1,
                        'items' => [
                            [
                                'product_id' => 1,
                                'quantity' => 2,
                                'price' => 100000
                            ]
                        ],
                        'notes' => 'Giao hàng nhanh'
                    ]),
                    'validation_error' => $this->makeAuthenticatedRequest('POST', '/orders', [
                        'items' => []
                    ])
                ]
            ]
        ];
    }

    /**
     * Generate Payment examples
     */
    private function generatePaymentExamples(): array
    {
        return [
            'list' => [
                'name' => 'List Payments',
                'method' => 'GET',
                'url' => '/payments',
                'headers' => $this->getAuthHeaders(),
                'query_params' => ['page' => 1, 'per_page' => 15, 'type' => ''],
                'examples' => [
                    'all_payments' => $this->makeAuthenticatedRequest('GET', '/payments?page=1&per_page=15'),
                    'filter_by_type' => $this->makeAuthenticatedRequest('GET', '/payments?type=receipt&page=1')
                ]
            ],
            'show' => [
                'name' => 'Get Payment Details',
                'method' => 'GET',
                'url' => '/payments/{id}',
                'headers' => $this->getAuthHeaders(),
                'examples' => [
                    'success' => $this->makeAuthenticatedRequest('GET', '/payments/1'),
                    'not_found' => $this->makeAuthenticatedRequest('GET', '/payments/99999')
                ]
            ],
            'create' => [
                'name' => 'Create Payment',
                'method' => 'POST',
                'url' => '/payments',
                'headers' => $this->getAuthHeaders(),
                'body' => [
                    'reference_type' => 'invoice',
                    'reference_id' => 1,
                    'amount' => 500000,
                    'payment_method' => 'cash',
                    'notes' => 'Thanh toán tiền mặt'
                ],
                'examples' => [
                    'success' => $this->makeAuthenticatedRequest('POST', '/payments', [
                        'reference_type' => 'invoice',
                        'reference_id' => 1,
                        'amount' => 500000,
                        'payment_method' => 'cash',
                        'notes' => 'Thanh toán tiền mặt'
                    ]),
                    'validation_error' => $this->makeAuthenticatedRequest('POST', '/payments', [
                        'amount' => -100
                    ])
                ]
            ],
            'statistics' => [
                'name' => 'Payment Statistics',
                'method' => 'GET',
                'url' => '/payments/statistics',
                'headers' => $this->getAuthHeaders(),
                'query_params' => ['period' => 'month'],
                'examples' => [
                    'monthly_stats' => $this->makeAuthenticatedRequest('GET', '/payments/statistics?period=month')
                ]
            ]
        ];
    }

    /**
     * Get authentication headers
     */
    private function getAuthHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->authToken
        ];
    }
}
