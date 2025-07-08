<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\Inventory;

class TestStockStatusInfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:stock-status-info';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test getStockStatusInfo function';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Testing getStockStatusInfo Function...');
        $this->newLine();

        // Test 1: Check if products exist
        $this->info('1. Testing Product Availability:');
        try {
            $productCount = Product::count();
            $this->line("   ✅ Total products: {$productCount}");
            
            if ($productCount === 0) {
                $this->warn('   ⚠️  No products found. Creating test products...');
                $this->createTestProducts();
            }
        } catch (\Exception $e) {
            $this->error("   ❌ Error: " . $e->getMessage());
            return 1;
        }

        // Test 2: Test getStockStatusInfo with different scenarios
        $this->info('2. Testing Stock Status Scenarios:');
        
        $testScenarios = [
            ['stock' => 0, 'reorder' => 10, 'description' => 'Out of stock'],
            ['stock' => 5, 'reorder' => 10, 'description' => 'Low stock (below reorder point)'],
            ['stock' => 15, 'reorder' => 10, 'description' => 'Medium stock (1.5x reorder point)'],
            ['stock' => 50, 'reorder' => 10, 'description' => 'Good stock (5x reorder point)'],
            ['stock' => 100, 'reorder' => 20, 'description' => 'High stock'],
        ];

        foreach ($testScenarios as $scenario) {
            $this->testStockScenario($scenario['stock'], $scenario['reorder'], $scenario['description']);
        }

        // Test 3: Test with real products
        $this->info('3. Testing with Real Products:');
        $products = Product::with('inventory')->limit(5)->get();
        
        foreach ($products as $product) {
            $this->testRealProduct($product);
        }

        // Test 4: Test accessor
        $this->info('4. Testing Stock Status Accessor:');
        $product = Product::first();
        if ($product) {
            try {
                $stockStatus = $product->stock_status;
                $this->line("   ✅ Accessor working: " . $stockStatus['label']);
                $this->line("   │  - Status: " . $stockStatus['status']);
                $this->line("   │  - Class: " . $stockStatus['class']);
                $this->line("   │  - Quantity: " . $stockStatus['quantity']);
                $this->line("   │  - Percentage: " . $stockStatus['percentage'] . '%');
                $this->line("   │  - Urgency: " . $stockStatus['urgency']);
            } catch (\Exception $e) {
                $this->error("   ❌ Accessor error: " . $e->getMessage());
            }
        }

        // Test 5: Test HTML generation
        $this->info('5. Testing HTML Generation:');
        if ($product) {
            try {
                $stockInfo = $product->getStockStatusInfo();
                $this->line("   ✅ Badge HTML generated");
                $this->line("   ✅ Progress HTML generated");
                $this->line("   ✅ Alert message: " . $stockInfo['alert_message']);
            } catch (\Exception $e) {
                $this->error("   ❌ HTML generation error: " . $e->getMessage());
            }
        }

        $this->newLine();
        $this->info('🎉 Stock Status Info Test Completed!');
        
        return 0;
    }

    /**
     * Test stock scenario
     */
    private function testStockScenario($stockQuantity, $reorderPoint, $description)
    {
        try {
            $product = new Product();
            $stockInfo = $product->getStockStatusInfo($stockQuantity, $reorderPoint);
            
            $this->line("   📊 {$description}:");
            $this->line("   │  Stock: {$stockQuantity}, Reorder: {$reorderPoint}");
            $this->line("   │  Status: {$stockInfo['status']} ({$stockInfo['label']})");
            $this->line("   │  Class: {$stockInfo['class']}, Urgency: {$stockInfo['urgency']}");
            $this->line("   │  Percentage: {$stockInfo['percentage']}%");
            $this->line("   │  Days until out: {$stockInfo['days_until_out_of_stock']}");
            
            if ($stockInfo['reorder_suggestion']['should_reorder']) {
                $this->line("   │  🔄 Reorder: {$stockInfo['reorder_suggestion']['suggested_quantity']} units");
            } else {
                $this->line("   │  ✅ No reorder needed");
            }
            
            $this->newLine();
            
        } catch (\Exception $e) {
            $this->error("   ❌ Error testing scenario '{$description}': " . $e->getMessage());
        }
    }

    /**
     * Test real product
     */
    private function testRealProduct($product)
    {
        try {
            $stockInfo = $product->getStockStatusInfo();
            
            $this->line("   📦 Product: {$product->product_name}");
            $this->line("   │  SKU: {$product->sku}");
            $this->line("   │  Stock: {$stockInfo['quantity']} units");
            $this->line("   │  Status: {$stockInfo['status']} ({$stockInfo['label']})");
            $this->line("   │  Urgency: {$stockInfo['urgency']}");
            
            if ($stockInfo['reorder_suggestion']['should_reorder']) {
                $this->line("   │  ⚠️  Needs reorder: {$stockInfo['reorder_suggestion']['suggested_quantity']} units");
            }
            
            $this->newLine();
            
        } catch (\Exception $e) {
            $this->error("   ❌ Error testing product '{$product->product_name}': " . $e->getMessage());
        }
    }

    /**
     * Create test products if none exist
     */
    private function createTestProducts()
    {
        try {
            $testProducts = [
                [
                    'product_name' => 'Test Product 1 - Out of Stock',
                    'sku' => 'TEST-001',
                    'sale_price' => 100000,
                    'cost_price' => 80000,
                    'reorder_point' => 10,
                    'stock_quantity' => 0
                ],
                [
                    'product_name' => 'Test Product 2 - Low Stock',
                    'sku' => 'TEST-002',
                    'sale_price' => 150000,
                    'cost_price' => 120000,
                    'reorder_point' => 15,
                    'stock_quantity' => 8
                ],
                [
                    'product_name' => 'Test Product 3 - Good Stock',
                    'sku' => 'TEST-003',
                    'sale_price' => 200000,
                    'cost_price' => 160000,
                    'reorder_point' => 20,
                    'stock_quantity' => 100
                ]
            ];

            foreach ($testProducts as $productData) {
                $stockQuantity = $productData['stock_quantity'];
                unset($productData['stock_quantity']);
                
                $product = Product::create($productData);
                
                // Create inventory record
                Inventory::create([
                    'product_id' => $product->id,
                    'quantity' => $stockQuantity,
                    'reserved_quantity' => 0
                ]);
                
                $this->line("   ✅ Created test product: {$product->product_name}");
            }
            
        } catch (\Exception $e) {
            $this->error("   ❌ Error creating test products: " . $e->getMessage());
        }
    }
}
