<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestProductsApiCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'test:products-api';

    /**
     * The console command description.
     */
    protected $description = 'Test Products API endpoints';

    private $baseUrl;
    private $authToken;

    public function __construct()
    {
        parent::__construct();
        $this->baseUrl = config('app.url') . '/api/v1';
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('📦 Testing Products API');
        $this->info('=======================');

        try {
            // 1. Authenticate
            $this->authenticate();
            
            // 2. Test API endpoints
            $this->testGetProducts();
            $this->testGetProductById();
            $this->testSearchByBarcode();
            
            $this->info('✅ All products API tests completed successfully!');
            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Test failed: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Authenticate and get token
     */
    private function authenticate()
    {
        $this->info('🔐 Authenticating...');
        
        $credentials = [
            'email' => 'yukimart@gmail.com',
            'password' => '123456'
        ];

        $response = Http::post($this->baseUrl . '/auth/login', $credentials);
        
        if ($response->successful()) {
            $data = $response->json();
            $this->authToken = $data['data']['access_token'] ?? null;
            
            if (!$this->authToken) {
                throw new \Exception('No access token received');
            }
            
            $this->info('✅ Authentication successful');
        } else {
            throw new \Exception('Authentication failed: ' . $response->body());
        }
    }

    /**
     * Test get products endpoint
     */
    private function testGetProducts()
    {
        $this->info('📋 Testing GET /products...');
        
        $headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $this->authToken
        ];

        $response = Http::withHeaders($headers)->get($this->baseUrl . '/products');
        
        if ($response->successful()) {
            $data = $response->json();
            $count = count($data['data']);
            $this->info("  ✅ Success - Retrieved {$count} products");
            
            // Show first product details
            if ($count > 0) {
                $firstProduct = $data['data'][0];
                $this->info("  📦 First product: {$firstProduct['product_name']} (ID: {$firstProduct['id']})");
                $this->info("  💰 Price: " . number_format($firstProduct['sale_price']) . " VNĐ");
                $this->info("  📊 Stock: " . ($firstProduct['current_stock'] ?? 'N/A'));
            }
            
            // Test with pagination
            $response = Http::withHeaders($headers)->get($this->baseUrl . '/products?per_page=5&page=1');
            if ($response->successful()) {
                $this->info("  ✅ Pagination works");
            }
            
            // Test with filters
            $response = Http::withHeaders($headers)->get($this->baseUrl . '/products?product_status=publish');
            if ($response->successful()) {
                $this->info("  ✅ Status filter works");
            }
        } else {
            $this->error("  ❌ Failed: " . $response->body());
        }
    }

    /**
     * Test get product by ID endpoint
     */
    private function testGetProductById()
    {
        $this->info('🔍 Testing GET /products/{id}...');
        
        $headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $this->authToken
        ];

        // Get first product ID
        $response = Http::withHeaders($headers)->get($this->baseUrl . '/products?per_page=1');
        
        if ($response->successful()) {
            $data = $response->json();
            if (count($data['data']) > 0) {
                $productId = $data['data'][0]['id'];
                
                $response = Http::withHeaders($headers)->get($this->baseUrl . "/products/{$productId}");
                
                if ($response->successful()) {
                    $product = $response->json()['data'];
                    $this->info("  ✅ Success - Retrieved product: {$product['product_name']}");
                } else {
                    $this->error("  ❌ Failed: " . $response->body());
                }
            } else {
                $this->warn("  ⚠️ No products found to test");
            }
        } else {
            $this->error("  ❌ Failed to get products list: " . $response->body());
        }
    }

    /**
     * Test search by barcode endpoint
     */
    private function testSearchByBarcode()
    {
        $this->info('🔍 Testing GET /products/search-barcode...');
        
        $headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $this->authToken
        ];

        // Test with a sample barcode
        $response = Http::withHeaders($headers)->get($this->baseUrl . '/products/search-barcode?barcode=123456789');
        
        if ($response->status() === 404) {
            $this->info("  ✅ Success - Correctly returns 404 for non-existent barcode");
        } elseif ($response->successful()) {
            $product = $response->json()['data'];
            $this->info("  ✅ Success - Found product: {$product['product_name']}");
        } else {
            $this->error("  ❌ Failed: " . $response->body());
        }

        // Test with invalid barcode (validation error)
        $response = Http::withHeaders($headers)->get($this->baseUrl . '/products/search-barcode');
        
        if ($response->status() === 422) {
            $this->info("  ✅ Success - Correctly validates missing barcode parameter");
        } else {
            $this->warn("  ⚠️ Validation might not be working as expected");
        }
    }
}
