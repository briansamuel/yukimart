<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\OrderService;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Product;
use App\Models\BranchShop;
use App\Models\Inventory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TestOrderBranchShop extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:order-branch-shop';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test order creation with branch shop integration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Testing Order with Branch Shop Integration...');
        $this->newLine();

        // Test 1: Check prerequisites
        $this->info('1. Checking Prerequisites:');
        try {
            $branchShopCount = BranchShop::count();
            $customerCount = Customer::count();
            $productCount = Product::count();
            
            $this->line("   ✅ Branch Shops: {$branchShopCount}");
            $this->line("   ✅ Customers: {$customerCount}");
            $this->line("   ✅ Products: {$productCount}");
            
            if ($branchShopCount === 0) {
                $this->warn('   ⚠️  Creating branch shops...');
                $this->createBranchShops();
            }
            
            if ($customerCount === 0 || $productCount === 0) {
                $this->warn('   ⚠️  Creating test data...');
                $this->createTestData();
            }
        } catch (\Exception $e) {
            $this->error("   ❌ Error: " . $e->getMessage());
            return 1;
        }

        // Test 2: Test branch shop selection
        $this->info('2. Testing Branch Shop Selection:');
        try {
            $activeBranchShops = BranchShop::active()->orderBy('sort_order')->get();
            
            $this->line("   📋 Active branch shops: {$activeBranchShops->count()}");
            
            foreach ($activeBranchShops->take(5) as $index => $branch) {
                $this->line("   │");
                $this->line("   ├─ Branch #" . ($index + 1));
                $this->line("   ├─ Name: {$branch->name}");
                $this->line("   ├─ Code: {$branch->code}");
                $this->line("   ├─ Type: {$branch->shop_type_label}");
                $this->line("   ├─ Address: {$branch->full_address}");
                $this->line("   ├─ Delivery: " . ($branch->has_delivery ? 'Yes' : 'No'));
                if ($branch->has_delivery) {
                    $this->line("   ├─ Delivery Fee: " . number_format($branch->delivery_fee, 0, ',', '.') . '₫');
                    $this->line("   ├─ Delivery Radius: {$branch->delivery_radius}km");
                }
                $this->line("   └─ Status: {$branch->status}");
            }
            
        } catch (\Exception $e) {
            $this->error("   ❌ Error: " . $e->getMessage());
        }

        // Test 3: Test order creation with branch shop
        $this->info('3. Testing Order Creation with Branch Shop:');
        try {
            // Login as first user for testing
            $user = \App\Models\User::first();
            if (!$user) {
                $this->error('   ❌ No users found. Please create a user first.');
                return 1;
            }
            
            Auth::login($user);
            $this->line("   👤 Logged in as: {$user->name}");
            
            $orderService = app(OrderService::class);
            
            // Get test data
            $customer = Customer::first();
            $product = Product::with('inventory')->first();
            $branchShop = BranchShop::active()->first();
            
            if (!$customer || !$product || !$branchShop) {
                $this->error('   ❌ Missing test data');
                return 1;
            }
            
            $this->line("   👥 Customer: {$customer->name}");
            $this->line("   📦 Product: {$product->product_name}");
            $this->line("   🏪 Branch Shop: {$branchShop->name}");
            
            // Create order data with branch shop
            $orderData = [
                'customer_id' => $customer->id,
                'branch_shop_id' => $branchShop->id,
                'channel' => 'direct',
                'status' => 'processing',
                'delivery_status' => 'pending',
                'discount_amount' => 0,
                'shipping_fee' => $branchShop->has_delivery ? $branchShop->delivery_fee : 0,
                'tax_amount' => 0,
                'amount_paid' => 0,
                'notes' => 'Test order with branch shop integration',
                'items' => json_encode([
                    [
                        'product_id' => $product->id,
                        'quantity' => 2,
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
                
                // Load order with branch shop relationship
                $orderWithBranch = Order::with('branchShop')->find($order->id);
                
                if ($orderWithBranch->branchShop) {
                    $this->line("   ✅ Branch shop relationship loaded");
                    $this->line("   │  - Branch Shop: {$orderWithBranch->branchShop->name}");
                    $this->line("   │  - Shop Type: {$orderWithBranch->branchShop->shop_type_label}");
                    $this->line("   │  - Address: {$orderWithBranch->branchShop->full_address}");
                    $this->line("   │  - Delivery Available: " . ($orderWithBranch->branchShop->has_delivery ? 'Yes' : 'No'));
                    $this->line("   │  - Shipping Fee: " . number_format($orderWithBranch->shipping_fee, 0, ',', '.') . '₫');
                } else {
                    $this->error("   ❌ Branch shop relationship not loaded");
                }
                
                // Check notification data
                $notification = \App\Models\Notification::where('type', 'order_create')
                    ->where('data->order_id', $order->id)
                    ->first();
                
                if ($notification && isset($notification->data['branch_shop_name'])) {
                    $this->line("   ✅ Notification includes branch shop info");
                    $this->line("   │  - Branch Shop Name: {$notification->data['branch_shop_name']}");
                    $this->line("   │  - Channel: {$notification->data['channel']}");
                } else {
                    $this->warn("   ⚠️  Notification missing branch shop info");
                }
                
            } else {
                $this->error("   ❌ Order creation failed: " . $result['message']);
            }
            
        } catch (\Exception $e) {
            $this->error("   ❌ Error: " . $e->getMessage());
        }

        // Test 4: Test order statistics by branch shop
        $this->info('4. Testing Order Statistics by Branch Shop:');
        try {
            $branchShops = BranchShop::withCount('orders')->get();
            
            $this->line("   📊 Order statistics by branch shop:");
            
            foreach ($branchShops as $branch) {
                $totalRevenue = $branch->orders()->sum('final_amount');
                $this->line("   │");
                $this->line("   ├─ {$branch->name}");
                $this->line("   ├─ Orders: {$branch->orders_count}");
                $this->line("   └─ Revenue: " . number_format($totalRevenue, 0, ',', '.') . '₫');
            }
            
        } catch (\Exception $e) {
            $this->error("   ❌ Error: " . $e->getMessage());
        }

        // Test 5: Test branch shop filtering
        $this->info('5. Testing Branch Shop Filtering:');
        try {
            $branchShops = BranchShop::active()->get();
            
            foreach ($branchShops->take(3) as $branch) {
                $ordersCount = Order::byBranchShop($branch->id)->count();
                $this->line("   🏪 {$branch->name}: {$ordersCount} orders");
            }
            
            // Test delivery availability
            $deliveryBranches = BranchShop::withDelivery()->count();
            $this->line("   🚚 Branches with delivery: {$deliveryBranches}");
            
            // Test by shop type
            $flagshipCount = BranchShop::ofType('flagship')->count();
            $standardCount = BranchShop::ofType('standard')->count();
            $miniCount = BranchShop::ofType('mini')->count();
            $kioskCount = BranchShop::ofType('kiosk')->count();
            
            $this->line("   📈 Shop types:");
            $this->line("   │  - Flagship: {$flagshipCount}");
            $this->line("   │  - Standard: {$standardCount}");
            $this->line("   │  - Mini: {$miniCount}");
            $this->line("   └─ Kiosk: {$kioskCount}");
            
        } catch (\Exception $e) {
            $this->error("   ❌ Error: " . $e->getMessage());
        }

        $this->newLine();
        $this->info('🎉 Order Branch Shop Integration Test Completed!');
        $this->line('💡 Visit /admin/orders/add to see the branch shop selection in order form');
        
        return 0;
    }

    /**
     * Create branch shops if they don't exist
     */
    private function createBranchShops()
    {
        try {
            DB::beginTransaction();
            
            $branchShops = [
                [
                    'name' => 'YukiMart Quận 1',
                    'code' => 'YM-Q1-001',
                    'shop_type' => 'flagship',
                    'address' => '123 Nguyễn Huệ',
                    'ward' => 'Phường Bến Nghé',
                    'district' => 'Quận 1',
                    'province' => 'TP. Hồ Chí Minh',
                    'phone' => '028-3822-1234',
                    'email' => 'quan1@yukimart.vn',
                    'status' => 'active',
                    'area' => 250.50,
                    'staff_count' => 15,
                    'opening_time' => '07:00',
                    'closing_time' => '22:00',
                    'working_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
                    'has_delivery' => true,
                    'delivery_radius' => 5.0,
                    'delivery_fee' => 25000,
                    'sort_order' => 1,
                ],
                [
                    'name' => 'YukiMart Quận 3',
                    'code' => 'YM-Q3-001',
                    'shop_type' => 'standard',
                    'address' => '456 Võ Văn Tần',
                    'ward' => 'Phường 5',
                    'district' => 'Quận 3',
                    'province' => 'TP. Hồ Chí Minh',
                    'phone' => '028-3930-5678',
                    'email' => 'quan3@yukimart.vn',
                    'status' => 'active',
                    'area' => 180.25,
                    'staff_count' => 10,
                    'opening_time' => '08:00',
                    'closing_time' => '21:00',
                    'working_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
                    'has_delivery' => true,
                    'delivery_radius' => 3.0,
                    'delivery_fee' => 20000,
                    'sort_order' => 2,
                ],
                [
                    'name' => 'YukiMart Mini Store',
                    'code' => 'YM-MINI-001',
                    'shop_type' => 'mini',
                    'address' => '789 Lê Lợi',
                    'ward' => 'Phường Bến Thành',
                    'district' => 'Quận 1',
                    'province' => 'TP. Hồ Chí Minh',
                    'phone' => '028-3824-9999',
                    'email' => 'mini@yukimart.vn',
                    'status' => 'active',
                    'area' => 80.00,
                    'staff_count' => 5,
                    'opening_time' => '09:00',
                    'closing_time' => '20:00',
                    'working_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'],
                    'has_delivery' => false,
                    'delivery_radius' => 0,
                    'delivery_fee' => 0,
                    'sort_order' => 3,
                ],
            ];
            
            foreach ($branchShops as $branchShop) {
                BranchShop::create($branchShop);
            }
            
            DB::commit();
            $this->line("   ✅ Created " . count($branchShops) . " branch shops");
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("   ❌ Error creating branch shops: " . $e->getMessage());
        }
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
                    'name' => 'Test Customer for Branch Shop',
                    'phone' => '0987654321',
                    'email' => 'test-branch@example.com',
                    'address' => 'Test Address for Branch Shop',
                    'customer_type' => 'individual',
                    'status' => 'active'
                ]);
                $this->line("   ✅ Created test customer: {$customer->name}");
            }
            
            // Create test product if needed
            if (Product::count() === 0) {
                $product = Product::create([
                    'product_name' => 'Test Product for Branch Shop',
                    'sku' => 'TEST-BRANCH-001',
                    'sale_price' => 200000,
                    'cost_price' => 160000,
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
