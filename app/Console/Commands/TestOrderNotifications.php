<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\OrderService;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Notification;
use App\Models\Inventory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TestOrderNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:order-notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test order creation notifications for dashboard recent activities';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Testing Order Notifications for Dashboard...');
        $this->newLine();

        // Test 1: Check prerequisites
        $this->info('1. Checking Prerequisites:');
        try {
            $customerCount = Customer::count();
            $productCount = Product::count();
            $notificationCount = Notification::count();
            
            $this->line("   ✅ Customers: {$customerCount}");
            $this->line("   ✅ Products: {$productCount}");
            $this->line("   ✅ Notifications: {$notificationCount}");
            
            if ($customerCount === 0 || $productCount === 0) {
                $this->warn('   ⚠️  Creating test data...');
                $this->createTestData();
            }
        } catch (\Exception $e) {
            $this->error("   ❌ Error: " . $e->getMessage());
            return 1;
        }

        // Test 2: Test order creation with notification
        $this->info('2. Testing Order Creation with Notification:');
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
            
            // Get test customer and product
            $customer = Customer::first();
            $product = Product::with('inventory')->first();
            
            if (!$customer || !$product) {
                $this->error('   ❌ No test data available');
                return 1;
            }
            
            $this->line("   👥 Customer: {$customer->name}");
            $this->line("   📦 Product: {$product->product_name}");
            
            // Create order data
            $orderData = [
                'customer_id' => $customer->id,
                'branch_shop_id' => 1,
                'channel' => 'direct',
                'status' => 'processing',
                'delivery_status' => 'pending',
                'discount_amount' => 0,
                'amount_paid' => 0,
                'note' => 'Test order for notification testing',
                'items' => json_encode([
                    [
                        'product_id' => $product->id,
                        'quantity' => 3,
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
                
                // Check if notification was created
                $notification = Notification::where('type', 'order_create')
                    ->where('data->order_id', $order->id)
                    ->first();
                
                if ($notification) {
                    $this->line("   ✅ Notification created successfully");
                    $this->line("   │  - ID: {$notification->id}");
                    $this->line("   │  - Type: {$notification->type}");
                    $this->line("   │  - Title: {$notification->title}");
                    $this->line("   │  - Message: {$notification->message}");
                    $this->line("   │  - Order Code: {$notification->data['order_code']}");
                    $this->line("   │  - Customer: {$notification->data['customer_name']}");
                    $this->line("   │  - Seller: " . ($notification->data['sold_by_name'] ?? 'N/A'));
                    $this->line("   │  - Amount: " . number_format($notification->data['total_amount'], 0, ',', '.') . '₫');
                    $this->line("   │  - Created: {$notification->created_at->format('d/m/Y H:i:s')}");
                } else {
                    $this->error("   ❌ No notification created for order");
                }
                
            } else {
                $this->error("   ❌ Order creation failed: " . $result['message']);
            }
            
        } catch (\Exception $e) {
            $this->error("   ❌ Error: " . $e->getMessage());
        }

        // Test 3: Test dashboard recent activities
        $this->info('3. Testing Dashboard Recent Activities:');
        try {
            $recentActivities = \App\Services\DashboardService::getRecentActivities(10);
            
            $this->line("   📋 Recent activities count: {$recentActivities->count()}");
            
            if ($recentActivities->count() > 0) {
                $this->line("   📝 Recent order activities:");
                
                foreach ($recentActivities->take(5) as $index => $activity) {
                    $this->line("   │");
                    $this->line("   ├─ Activity #" . ($index + 1));
                    $this->line("   ├─ Type: {$activity['type']}");
                    $this->line("   ├─ User: {$activity['user_name']}");
                    $this->line("   ├─ Action: {$activity['action']}");
                    $this->line("   ├─ Description: {$activity['description']}");
                    
                    if (isset($activity['order_code'])) {
                        $this->line("   ├─ Order Code: {$activity['order_code']}");
                    }
                    if (isset($activity['customer_name'])) {
                        $this->line("   ├─ Customer: {$activity['customer_name']}");
                    }
                    if (isset($activity['seller_name'])) {
                        $this->line("   ├─ Seller: {$activity['seller_name']}");
                    }
                    if (isset($activity['formatted_amount'])) {
                        $this->line("   ├─ Amount: {$activity['formatted_amount']}");
                    }
                    
                    $this->line("   └─ Time: {$activity['time_ago']}");
                }
            } else {
                $this->warn("   ⚠️  No recent activities found");
            }
            
        } catch (\Exception $e) {
            $this->error("   ❌ Error: " . $e->getMessage());
        }

        // Test 4: Test notification types
        $this->info('4. Testing Notification Types:');
        try {
            $notificationTypes = Notification::selectRaw('type, count(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray();
            
            $this->line("   📊 Notification types:");
            foreach ($notificationTypes as $type => $count) {
                $this->line("   │  - {$type}: {$count} notifications");
            }
            
            // Check if order_create type exists
            if (array_key_exists('order_create', $notificationTypes)) {
                $orderCreateCount = $notificationTypes['order_create'];
                $this->line("   ✅ 'order_create' notifications: {$orderCreateCount}");
            } else {
                $this->warn("   ⚠️  No 'order_create' notifications found");
            }
            
        } catch (\Exception $e) {
            $this->error("   ❌ Error: " . $e->getMessage());
        }

        // Test 5: Test notification data structure
        $this->info('5. Testing Notification Data Structure:');
        try {
            $orderNotifications = Notification::where('type', 'order_create')
                ->with('creator')
                ->orderBy('created_at', 'desc')
                ->limit(3)
                ->get();
            
            $this->line("   🔍 Recent order notifications:");
            
            foreach ($orderNotifications as $index => $notification) {
                $this->line("   │");
                $this->line("   ├─ Notification #" . ($index + 1));
                $this->line("   ├─ ID: {$notification->id}");
                $this->line("   ├─ Title: {$notification->title}");
                $this->line("   ├─ Creator: " . ($notification->creator ? $notification->creator->name : 'N/A'));
                $this->line("   ├─ Priority: {$notification->priority}");
                $this->line("   ├─ Read: " . ($notification->is_read ? 'Yes' : 'No'));
                $this->line("   ├─ Data keys: " . implode(', ', array_keys($notification->data ?? [])));
                $this->line("   └─ Created: {$notification->created_at->format('d/m/Y H:i:s')}");
            }
            
        } catch (\Exception $e) {
            $this->error("   ❌ Error: " . $e->getMessage());
        }

        // Test 6: Test dashboard integration
        $this->info('6. Testing Dashboard Integration:');
        try {
            $this->line("   🌐 Dashboard URL: /admin/dash-board");
            $this->line("   📱 Recent activities widget should show order notifications");
            $this->line("   🔗 Each notification should have:");
            $this->line("   │  - Order code");
            $this->line("   │  - Customer name");
            $this->line("   │  - Total amount");
            $this->line("   │  - Creator name");
            $this->line("   │  - Time ago");
            $this->line("   │  - Action URL to view order");
            $this->line("   │  - Read/unread status indicator");
            
        } catch (\Exception $e) {
            $this->error("   ❌ Error: " . $e->getMessage());
        }

        $this->newLine();
        $this->info('🎉 Order Notifications Test Completed!');
        $this->line('💡 Visit /admin/dash-board to see the updated recent activities widget');
        
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
                    'name' => 'Test Customer for Notifications',
                    'phone' => '0987654321',
                    'email' => 'test-notifications@example.com',
                    'address' => 'Test Address for Notifications',
                    'customer_type' => 'individual',
                    'status' => 'active'
                ]);
                $this->line("   ✅ Created test customer: {$customer->name}");
            }
            
            // Create test product if needed
            if (Product::count() === 0) {
                $product = Product::create([
                    'product_name' => 'Test Product for Notifications',
                    'sku' => 'TEST-NOTIF-001',
                    'sale_price' => 150000,
                    'cost_price' => 120000,
                    'product_status' => 'publish',
                    'reorder_point' => 5
                ]);
                
                // Create inventory for the product
                Inventory::create([
                    'product_id' => $product->id,
                    'quantity' => 50,
                    'reserved_quantity' => 0
                ]);
                
                $this->line("   ✅ Created test product: {$product->product_name} with stock: 50");
            }
            
            DB::commit();
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("   ❌ Error creating test data: " . $e->getMessage());
        }
    }
}
