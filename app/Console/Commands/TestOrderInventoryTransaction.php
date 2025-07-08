<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\OrderService;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Product;
use App\Models\InventoryTransaction;
use App\Models\Inventory;
use Illuminate\Support\Facades\DB;

class TestOrderInventoryTransaction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:order-inventory-transaction';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test automatic inventory transaction creation when order is created';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Testing Order Inventory Transaction Creation...');
        $this->newLine();

        // Test 1: Check prerequisites
        $this->info('1. Checking Prerequisites:');
        try {
            $customerCount = Customer::count();
            $productCount = Product::count();
            
            $this->line("   ✅ Customers: {$customerCount}");
            $this->line("   ✅ Products: {$productCount}");
            
            if ($customerCount === 0 || $productCount === 0) {
                $this->warn('   ⚠️  Creating test data...');
                $this->createTestData();
            }
        } catch (\Exception $e) {
            $this->error("   ❌ Error: " . $e->getMessage());
            return 1;
        }

        // Test 2: Test order creation with inventory transaction
        $this->info('2. Testing Order Creation with Inventory Transaction:');
        try {
            $orderService = app(OrderService::class);
            
            // Get test customer and product
            $customer = Customer::first();
            $product = Product::with('inventory')->first();
            
            if (!$customer || !$product) {
                $this->error('   ❌ No test data available');
                return 1;
            }
            
            // Get initial stock
            $initialStock = $product->inventory ? $product->inventory->quantity : 0;
            $this->line("   📦 Product: {$product->product_name}");
            $this->line("   📊 Initial stock: {$initialStock}");
            
            // Create order data
            $orderData = [
                'customer_id' => $customer->id,
                'branch_id' => 1,
                'channel' => 'direct',
                'status' => 'processing',
                'delivery_status' => 'pending',
                'discount_amount' => 0,
                'amount_paid' => 0,
                'note' => 'Test order for inventory transaction',
                'items' => json_encode([
                    [
                        'product_id' => $product->id,
                        'quantity' => 5,
                        'unit_price' => $product->sale_price,
                        'discount' => 0
                    ]
                ])
            ];
            
            // Create order
            $result = $orderService->createOrder($orderData);
            
            if ($result['success']) {
                $order = $result['data'];
                $this->line("   ✅ Order created: {$order->order_code}");
                
                // Check if inventory transaction was created
                $transactions = InventoryTransaction::where('reference_type', 'App\\Models\\Order')
                    ->where('reference_id', $order->id)
                    ->where('transaction_type', 'sale')
                    ->get();
                
                if ($transactions->count() > 0) {
                    $this->line("   ✅ Inventory transactions created: {$transactions->count()}");
                    
                    foreach ($transactions as $transaction) {
                        $this->line("   │  - Product ID: {$transaction->product_id}");
                        $this->line("   │  - Type: {$transaction->transaction_type}");
                        $this->line("   │  - Quantity: {$transaction->quantity}");
                        $this->line("   │  - Old stock: {$transaction->old_quantity}");
                        $this->line("   │  - New stock: {$transaction->new_quantity}");
                        $this->line("   │  - Notes: {$transaction->notes}");
                    }
                    
                    // Check if inventory was updated
                    $product->refresh();
                    $newStock = $product->inventory ? $product->inventory->quantity : 0;
                    $this->line("   📊 New stock: {$newStock}");
                    
                    if ($newStock === ($initialStock - 5)) {
                        $this->line("   ✅ Stock correctly decreased by 5 units");
                    } else {
                        $this->warn("   ⚠️  Stock change unexpected. Expected: " . ($initialStock - 5) . ", Got: {$newStock}");
                    }
                } else {
                    $this->error("   ❌ No inventory transactions created");
                }
                
            } else {
                $this->error("   ❌ Order creation failed: " . $result['message']);
            }
            
        } catch (\Exception $e) {
            $this->error("   ❌ Error: " . $e->getMessage());
        }

        // Test 3: Test transaction details
        $this->info('3. Testing Transaction Details:');
        try {
            $recentTransactions = InventoryTransaction::where('transaction_type', 'sale')
                ->with(['product', 'creator'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
            
            $this->line("   📋 Recent sale transactions: {$recentTransactions->count()}");
            
            foreach ($recentTransactions as $transaction) {
                $this->line("   │");
                $this->line("   ├─ ID: {$transaction->id}");
                $this->line("   ├─ Product: {$transaction->product->product_name}");
                $this->line("   ├─ Type: {$transaction->transaction_type}");
                $this->line("   ├─ Quantity: {$transaction->quantity}");
                $this->line("   ├─ Value: " . number_format($transaction->total_value, 0, ',', '.') . '₫');
                $this->line("   ├─ Date: {$transaction->created_at->format('d/m/Y H:i')}");
                $this->line("   └─ Notes: {$transaction->notes}");
            }
            
        } catch (\Exception $e) {
            $this->error("   ❌ Error: " . $e->getMessage());
        }

        // Test 4: Test transaction types
        $this->info('4. Testing Transaction Types:');
        try {
            $transactionTypes = InventoryTransaction::getTransactionTypes();
            
            $this->line("   📝 Available transaction types:");
            foreach ($transactionTypes as $type => $label) {
                $count = InventoryTransaction::where('transaction_type', $type)->count();
                $this->line("   │  - {$type}: {$label} ({$count} records)");
            }
            
            // Check if 'sale' type exists
            if (array_key_exists('sale', $transactionTypes)) {
                $this->line("   ✅ 'sale' transaction type is available");
            } else {
                $this->error("   ❌ 'sale' transaction type is missing");
            }
            
        } catch (\Exception $e) {
            $this->error("   ❌ Error: " . $e->getMessage());
        }

        // Test 5: Test inventory consistency
        $this->info('5. Testing Inventory Consistency:');
        try {
            $products = Product::with('inventory')->limit(5)->get();
            
            foreach ($products as $product) {
                $currentStock = $product->inventory ? $product->inventory->quantity : 0;
                
                // Calculate stock from transactions
                $transactions = InventoryTransaction::where('product_id', $product->id)->get();
                $calculatedStock = 0;
                
                foreach ($transactions as $transaction) {
                    if ($transaction->transaction_type === 'initial') {
                        $calculatedStock = $transaction->new_quantity;
                    } else {
                        $calculatedStock += $transaction->quantity;
                    }
                }
                
                $this->line("   📦 {$product->product_name}:");
                $this->line("   │  - Current stock: {$currentStock}");
                $this->line("   │  - Calculated from transactions: {$calculatedStock}");
                
                if ($currentStock === $calculatedStock) {
                    $this->line("   │  ✅ Stock is consistent");
                } else {
                    $this->line("   │  ⚠️  Stock inconsistency detected");
                }
            }
            
        } catch (\Exception $e) {
            $this->error("   ❌ Error: " . $e->getMessage());
        }

        $this->newLine();
        $this->info('🎉 Order Inventory Transaction Test Completed!');
        
        return 0;
    }

    /**
     * Create test data if needed
     */
    private function createTestData()
    {
        try {
            DB::beginTransaction();
            
            // Create test customer if needed
            if (Customer::count() === 0) {
                $customer = Customer::create([
                    'name' => 'Test Customer',
                    'phone' => '0123456789',
                    'email' => 'test@example.com',
                    'address' => 'Test Address',
                    'customer_type' => 'individual',
                    'status' => 'active'
                ]);
                $this->line("   ✅ Created test customer: {$customer->name}");
            }
            
            // Create test product if needed
            if (Product::count() === 0) {
                $product = Product::create([
                    'product_name' => 'Test Product',
                    'sku' => 'TEST-001',
                    'sale_price' => 100000,
                    'cost_price' => 80000,
                    'product_status' => 'publish',
                    'reorder_point' => 10
                ]);
                
                // Create inventory for the product
                Inventory::create([
                    'product_id' => $product->id,
                    'quantity' => 100,
                    'reserved_quantity' => 0
                ]);
                
                $this->line("   ✅ Created test product: {$product->product_name} with stock: 100");
            }
            
            DB::commit();
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("   ❌ Error creating test data: " . $e->getMessage());
        }
    }
}
