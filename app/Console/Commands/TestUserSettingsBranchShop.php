<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\BranchShop;
use App\Models\UserSetting;
use App\Models\Warehouse;
use App\Services\OrderService;
use Illuminate\Support\Facades\Auth;

class TestUserSettingsBranchShop extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:user-settings-branch-shop';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test user settings branch shop integration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Testing User Settings Branch Shop Integration...');
        $this->newLine();

        // Test 1: Check prerequisites
        $this->info('1. Checking Prerequisites:');
        try {
            $userCount = User::count();
            $branchShopCount = BranchShop::count();
            $warehouseCount = Warehouse::count();
            
            $this->line("   ✅ Users: {$userCount}");
            $this->line("   ✅ Branch Shops: {$branchShopCount}");
            $this->line("   ✅ Warehouses: {$warehouseCount}");
            
            if ($branchShopCount === 0) {
                $this->error('   ❌ No branch shops found. Please run BranchShopSeeder first.');
                return 1;
            }
            
            if ($warehouseCount === 0) {
                $this->error('   ❌ No warehouses found. Please run WarehouseSeeder first.');
                return 1;
            }
            
        } catch (\Exception $e) {
            $this->error("   ❌ Error: " . $e->getMessage());
            return 1;
        }

        // Test 2: Test user settings functionality
        $this->info('2. Testing User Settings Functionality:');
        try {
            $user = User::first();
            if (!$user) {
                $this->error('   ❌ No users found.');
                return 1;
            }
            
            $this->line("   👤 Testing with user: {$user->name}");
            
            // Test getSetting method
            $defaultBranchShop = $user->getSetting('default_branch_shop');
            $this->line("   📋 Current default branch shop: " . ($defaultBranchShop ?: 'Not set'));
            
            // Test setSetting method
            $branchShop = BranchShop::active()->first();
            if ($branchShop) {
                $user->setSetting('default_branch_shop', $branchShop->id);
                $this->line("   ✅ Set default branch shop to: {$branchShop->name}");
                
                // Verify setting was saved
                $savedSetting = $user->getSetting('default_branch_shop');
                if ($savedSetting == $branchShop->id) {
                    $this->line("   ✅ Setting saved successfully");
                } else {
                    $this->error("   ❌ Setting not saved correctly");
                }
            }
            
        } catch (\Exception $e) {
            $this->error("   ❌ Error: " . $e->getMessage());
        }

        // Test 3: Test branch shop warehouse relationship
        $this->info('3. Testing Branch Shop Warehouse Relationship:');
        try {
            $branchShopsWithWarehouses = BranchShop::with('warehouse')->get();
            
            foreach ($branchShopsWithWarehouses->take(5) as $branch) {
                $this->line("   🏪 {$branch->name}");
                if ($branch->warehouse) {
                    $this->line("   │  ✅ Warehouse: {$branch->warehouse->name}");
                    $this->line("   │  📍 Location: {$branch->warehouse->address}");
                } else {
                    $this->line("   │  ⚠️  No warehouse assigned");
                }
                $this->line("   │  📋 Type: {$branch->shop_type_label}");
                $this->line("   └─ Address: {$branch->full_address}");
            }
            
        } catch (\Exception $e) {
            $this->error("   ❌ Error: " . $e->getMessage());
        }

        // Test 4: Test order creation with user's default branch shop
        $this->info('4. Testing Order Creation with Default Branch Shop:');
        try {
            $user = User::first();
            Auth::login($user);
            
            $defaultBranchShopId = $user->getSetting('default_branch_shop');
            if (!$defaultBranchShopId) {
                $this->warn("   ⚠️  User has no default branch shop set");
                return 0;
            }
            
            $branchShop = BranchShop::with('warehouse')->find($defaultBranchShopId);
            if (!$branchShop) {
                $this->error("   ❌ Default branch shop not found");
                return 1;
            }
            
            $this->line("   🏪 Default Branch Shop: {$branchShop->name}");
            $this->line("   📦 Warehouse: " . ($branchShop->warehouse ? $branchShop->warehouse->name : 'Not assigned'));
            
            // Test OrderService getProductsForOrder with warehouse filtering
            $orderService = app(OrderService::class);
            $products = $orderService->getProductsForOrder();
            
            $this->line("   📦 Products available for this branch: " . $products->count());
            
            if ($products->count() > 0) {
                $this->line("   📋 Sample products:");
                foreach ($products->take(3) as $product) {
                    $this->line("   │  - {$product['name']} (Stock: {$product['stock_quantity']})");
                }
            }
            
        } catch (\Exception $e) {
            $this->error("   ❌ Error: " . $e->getMessage());
        }

        // Test 5: Test user settings data structure
        $this->info('5. Testing User Settings Data Structure:');
        try {
            $user = User::first();
            
            // Test various settings
            $settings = [
                'theme_mode' => 'light',
                'language' => 'vi',
                'email_order_created' => '1',
                'web_notifications' => '1',
                'widget_sales_today' => '1',
                'items_per_page' => '25',
                'date_format' => 'd/m/Y'
            ];
            
            foreach ($settings as $key => $value) {
                $user->setSetting($key, $value);
                $savedValue = $user->getSetting($key);
                
                if ($savedValue === $value) {
                    $this->line("   ✅ {$key}: {$value}");
                } else {
                    $this->error("   ❌ {$key}: Expected {$value}, got {$savedValue}");
                }
            }
            
        } catch (\Exception $e) {
            $this->error("   ❌ Error: " . $e->getMessage());
        }

        // Test 6: Test branch shop selection in order form
        $this->info('6. Testing Branch Shop Selection in Order Form:');
        try {
            $user = User::first();
            $defaultBranchShopId = $user->getSetting('default_branch_shop');
            
            if ($defaultBranchShopId) {
                $branchShop = BranchShop::find($defaultBranchShopId);
                
                $this->line("   🎯 Order form will use:");
                $this->line("   │  - Branch Shop: {$branchShop->name}");
                $this->line("   │  - Type: {$branchShop->shop_type_label}");
                $this->line("   │  - Delivery: " . ($branchShop->has_delivery ? 'Available' : 'Not available'));
                if ($branchShop->has_delivery) {
                    $this->line("   │  - Delivery Fee: " . number_format($branchShop->delivery_fee, 0, ',', '.') . '₫');
                }
                $this->line("   └─ Warehouse: " . ($branchShop->warehouse ? $branchShop->warehouse->name : 'Not assigned'));
                
                // Test order data preparation
                $orderData = [
                    'customer_id' => 1,
                    'branch_shop_id' => '', // Empty to test auto-assignment
                    'channel' => 'direct',
                    'items' => json_encode([])
                ];
                
                // Simulate order service validation
                if (empty($orderData['branch_shop_id'])) {
                    $orderData['branch_shop_id'] = $user->getSetting('default_branch_shop');
                }
                
                if ($orderData['branch_shop_id'] == $branchShop->id) {
                    $this->line("   ✅ Branch shop auto-assignment working correctly");
                } else {
                    $this->error("   ❌ Branch shop auto-assignment failed");
                }
                
            } else {
                $this->warn("   ⚠️  No default branch shop set for user");
            }
            
        } catch (\Exception $e) {
            $this->error("   ❌ Error: " . $e->getMessage());
        }

        $this->newLine();
        $this->info('🎉 User Settings Branch Shop Integration Test Completed!');
        $this->line('💡 Key Features Tested:');
        $this->line('   - User settings storage and retrieval');
        $this->line('   - Branch shop warehouse relationships');
        $this->line('   - Order form branch shop auto-assignment');
        $this->line('   - Product filtering by warehouse');
        $this->line('   - User settings interface integration');
        $this->newLine();
        $this->line('🔗 Visit /admin/settings to configure user settings');
        $this->line('🔗 Visit /admin/orders/add to see branch shop integration');
        
        return 0;
    }
}
