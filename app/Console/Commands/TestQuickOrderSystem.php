<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\Customer;
use App\Models\BranchShop;
use App\Models\User;
use App\Http\Controllers\Api\ProductBarcodeController;
use Illuminate\Http\Request;

class TestQuickOrderSystem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:quick-order {--setup : Setup test data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the Quick Order system functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Testing Quick Order System...');
        $this->newLine();

        if ($this->option('setup')) {
            $this->setupTestData();
            return;
        }

        // Test 1: Database Models
        $this->testDatabaseModels();

        // Test 2: API Controllers
        $this->testApiControllers();

        // Test 3: Routes
        $this->testRoutes();

        // Test 4: Sample Data
        $this->testSampleData();

        $this->newLine();
        $this->info('✅ Quick Order System test completed!');
    }

    protected function setupTestData()
    {
        $this->info('📦 Setting up test data...');

        try {
            // Create test products with barcodes
            $this->info('Creating test products...');
            
            $products = [
                [
                    'product_name' => 'Test Product 1',
                    'sku' => 'TEST-001',
                    'barcode' => '1234567890123',
                    'sale_price' => 100000,
                    'cost_price' => 80000,
                    'product_status' => 'publish',
                ],
                [
                    'product_name' => 'Test Product 2',
                    'sku' => 'TEST-002',
                    'barcode' => '2345678901234',
                    'sale_price' => 150000,
                    'cost_price' => 120000,
                    'product_status' => 'publish',
                ],
                [
                    'product_name' => 'Test Product 3',
                    'sku' => 'TEST-003',
                    'barcode' => '3456789012345',
                    'sale_price' => 200000,
                    'cost_price' => 160000,
                    'product_status' => 'publish',
                ],
            ];

            foreach ($products as $productData) {
                $existing = Product::where('barcode', $productData['barcode'])->first();
                if (!$existing) {
                    Product::create($productData);
                    $this->line("✓ Created product: {$productData['product_name']} (Barcode: {$productData['barcode']})");
                } else {
                    $this->line("- Product already exists: {$productData['product_name']}");
                }
            }

            // Create test customer
            $customer = Customer::firstOrCreate(
                ['phone' => '0123456789'],
                [
                    'name' => 'Test Customer',
                    'email' => 'test@example.com',
                    'status' => 'active',
                ]
            );
            $this->line("✓ Test customer: {$customer->name}");

            // Create test branch shop
            $branchShop = BranchShop::firstOrCreate(
                ['code' => 'TEST-SHOP'],
                [
                    'name' => 'Test Shop',
                    'address' => 'Test Address',
                    'phone' => '0987654321',
                    'status' => 'active',
                ]
            );
            $this->line("✓ Test branch shop: {$branchShop->name}");

            $this->info('✅ Test data setup completed!');

        } catch (\Exception $e) {
            $this->error('❌ Error setting up test data: ' . $e->getMessage());
        }
    }

    protected function testDatabaseModels()
    {
        $this->info('🔍 Testing Database Models...');

        try {
            // Test Product model
            $productCount = Product::count();
            $this->line("✓ Products table: {$productCount} records");

            $productsWithBarcode = Product::whereNotNull('barcode')->count();
            $this->line("✓ Products with barcode: {$productsWithBarcode} records");

            // Test Customer model
            $customerCount = Customer::count();
            $this->line("✓ Customers table: {$customerCount} records");

            $activeCustomers = Customer::active()->count();
            $this->line("✓ Active customers: {$activeCustomers} records");

            // Test BranchShop model
            $branchShopCount = BranchShop::count();
            $this->line("✓ Branch shops table: {$branchShopCount} records");

            $activeBranchShops = BranchShop::active()->count();
            $this->line("✓ Active branch shops: {$activeBranchShops} records");

        } catch (\Exception $e) {
            $this->error("❌ Database model test failed: " . $e->getMessage());
        }
    }

    protected function testApiControllers()
    {
        $this->info('🔍 Testing API Controllers...');

        try {
            $controller = new ProductBarcodeController();

            // Test barcode search
            $testBarcode = Product::whereNotNull('barcode')->first()?->barcode;
            if ($testBarcode) {
                $response = $controller->findByBarcode($testBarcode);
                $data = json_decode($response->getContent(), true);
                
                if ($data['success']) {
                    $this->line("✓ Barcode search API: Found product for barcode {$testBarcode}");
                } else {
                    $this->line("⚠ Barcode search API: {$data['message']}");
                }
            } else {
                $this->line("⚠ No products with barcodes found for testing");
            }

            // Test product search
            $request = new Request(['q' => 'test', 'limit' => 5]);
            $response = $controller->search($request);
            $data = json_decode($response->getContent(), true);
            
            if ($data['success']) {
                $this->line("✓ Product search API: Found {$data['data']->count()} products");
            } else {
                $this->line("⚠ Product search API: {$data['message']}");
            }

        } catch (\Exception $e) {
            $this->error("❌ API controller test failed: " . $e->getMessage());
        }
    }

    protected function testRoutes()
    {
        $this->info('🔍 Testing Routes...');

        try {
            // Check if routes are registered
            $router = app('router');
            $routes = $router->getRoutes();

            $quickOrderRoutes = [];
            $apiRoutes = [];

            foreach ($routes as $route) {
                $uri = $route->uri();
                if (str_contains($uri, 'quick-order')) {
                    $quickOrderRoutes[] = $uri;
                }
                if (str_contains($uri, 'api/products')) {
                    $apiRoutes[] = $uri;
                }
            }

            $this->line("✓ Quick Order routes found: " . count($quickOrderRoutes));
            foreach ($quickOrderRoutes as $route) {
                $this->line("  - {$route}");
            }

            $this->line("✓ API routes found: " . count($apiRoutes));
            foreach ($apiRoutes as $route) {
                $this->line("  - {$route}");
            }

        } catch (\Exception $e) {
            $this->error("❌ Route test failed: " . $e->getMessage());
        }
    }

    protected function testSampleData()
    {
        $this->info('🔍 Testing Sample Data...');

        try {
            // Show sample products with barcodes
            $sampleProducts = Product::whereNotNull('barcode')
                ->where('product_status', 'publish')
                ->limit(5)
                ->get();

            if ($sampleProducts->count() > 0) {
                $this->line("✓ Sample products for testing:");
                foreach ($sampleProducts as $product) {
                    $this->line("  - {$product->product_name} (SKU: {$product->sku}, Barcode: {$product->barcode})");
                }
            } else {
                $this->warn("⚠ No published products with barcodes found. Run with --setup to create test data.");
            }

            // Show sample customers
            $sampleCustomers = Customer::active()->limit(3)->get();
            if ($sampleCustomers->count() > 0) {
                $this->line("✓ Sample customers:");
                foreach ($sampleCustomers as $customer) {
                    $this->line("  - {$customer->name} ({$customer->phone})");
                }
            }

            // Show sample branch shops
            $sampleBranchShops = BranchShop::active()->limit(3)->get();
            if ($sampleBranchShops->count() > 0) {
                $this->line("✓ Sample branch shops:");
                foreach ($sampleBranchShops as $shop) {
                    $this->line("  - {$shop->name}");
                }
            }

        } catch (\Exception $e) {
            $this->error("❌ Sample data test failed: " . $e->getMessage());
        }
    }
}
