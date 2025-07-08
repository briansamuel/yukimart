<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use App\Models\BranchShop;
use Illuminate\Support\Facades\DB;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Bắt đầu tạo dữ liệu Orders...');

        // Ensure we have required data
        $this->ensureRequiredData();

        // Get available products
        $products = Product::where('product_status', 'publish')->get();
        if ($products->isEmpty()) {
            $this->command->warn('⚠️  Không có sản phẩm nào. Tạo một số sản phẩm mẫu...');
            $this->createSampleProducts();
            $products = Product::where('product_status', 'publish')->get();
        }

        $this->command->info("📦 Tìm thấy {$products->count()} sản phẩm có sẵn");

        // Create customers first
        $this->command->info('👥 Tạo khách hàng...');
        $customers = Customer::factory(50)->create();
        $this->command->info("✅ Đã tạo {$customers->count()} khách hàng");

        // Create orders with different scenarios
        $this->createCompletedOrders($products, $customers);
        $this->createProcessingOrders($products, $customers);
        $this->createCancelledOrders($products, $customers);
        $this->createRecentOrders($products, $customers);
        $this->createHighValueOrders($products, $customers);

        $totalOrders = Order::count();
        $totalOrderItems = OrderItem::count();
        
        $this->command->info("🎉 Hoàn thành! Đã tạo {$totalOrders} đơn hàng với {$totalOrderItems} sản phẩm");
        
        // Display statistics
        $this->displayStatistics();
    }

    /**
     * Ensure required data exists.
     */
    private function ensureRequiredData(): void
    {
        // Ensure we have at least one user
        if (User::count() === 0) {
            $this->command->warn('⚠️  Không có user nào. Tạo user admin...');
            User::factory()->create([
                'name' => 'Admin',
                'email' => 'admin@yukimart.com',
                'password' => bcrypt('password'),
            ]);
        }

        // Ensure we have at least one branch shop
        if (BranchShop::count() === 0) {
            $this->command->warn('⚠️  Không có chi nhánh nào. Tạo chi nhánh mặc định...');
            BranchShop::create([
                'name' => 'Chi nhánh chính',
                'code' => 'CN001',
                'address' => 'Số 123 Đường ABC',
                'province' => 'TP. Hồ Chí Minh',
                'district' => 'Quận 1',
                'ward' => 'Phường Bến Nghé',
                'phone' => '0123456789',
                'email' => 'chinhanh@yukimart.com',
                'status' => 'active',
                'shop_type' => 'flagship',
                'description' => 'Chi nhánh chính của YukiMart',
            ]);
        }
    }

    /**
     * Create sample products if none exist.
     */
    private function createSampleProducts(): void
    {
        $sampleProducts = [
            ['name' => 'iPhone 15 Pro Max', 'price' => 29990000, 'sku' => 'IP15PM'],
            ['name' => 'Samsung Galaxy S24 Ultra', 'price' => 26990000, 'sku' => 'SGS24U'],
            ['name' => 'MacBook Air M3', 'price' => 34990000, 'sku' => 'MBAM3'],
            ['name' => 'iPad Pro 12.9"', 'price' => 24990000, 'sku' => 'IPP129'],
            ['name' => 'AirPods Pro 2', 'price' => 6490000, 'sku' => 'APP2'],
            ['name' => 'Apple Watch Series 9', 'price' => 9990000, 'sku' => 'AWS9'],
            ['name' => 'Sony WH-1000XM5', 'price' => 8990000, 'sku' => 'SWH1000XM5'],
            ['name' => 'Nintendo Switch OLED', 'price' => 8990000, 'sku' => 'NSOL'],
            ['name' => 'PlayStation 5', 'price' => 13990000, 'sku' => 'PS5'],
            ['name' => 'Xbox Series X', 'price' => 12990000, 'sku' => 'XSX'],
        ];

        foreach ($sampleProducts as $productData) {
            Product::create([
                'product_name' => $productData['name'],
                'sku' => $productData['sku'],
                'product_price' => $productData['price'],
                'product_status' => 'publish',
                'product_description' => 'Sản phẩm chất lượng cao từ ' . $productData['name'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Create completed orders.
     */
    private function createCompletedOrders($products, $customers): void
    {
        $this->command->info('✅ Tạo đơn hàng đã hoàn thành...');
        
        Order::factory(30)
            ->completed()
            ->create()
            ->each(function ($order) use ($products) {
                $this->createOrderItems($order, $products, rand(1, 4));
                $order->calculateTotals();
            });
    }

    /**
     * Create processing orders.
     */
    private function createProcessingOrders($products, $customers): void
    {
        $this->command->info('⏳ Tạo đơn hàng đang xử lý...');
        
        Order::factory(20)
            ->processing()
            ->create()
            ->each(function ($order) use ($products) {
                $this->createOrderItems($order, $products, rand(1, 3));
                $order->calculateTotals();
            });
    }

    /**
     * Create cancelled orders.
     */
    private function createCancelledOrders($products, $customers): void
    {
        $this->command->info('❌ Tạo đơn hàng đã hủy...');
        
        Order::factory(10)
            ->cancelled()
            ->create()
            ->each(function ($order) use ($products) {
                $this->createOrderItems($order, $products, rand(1, 2));
                $order->calculateTotals();
            });
    }

    /**
     * Create recent orders.
     */
    private function createRecentOrders($products, $customers): void
    {
        $this->command->info('🆕 Tạo đơn hàng gần đây...');
        
        Order::factory(15)
            ->recent()
            ->create()
            ->each(function ($order) use ($products) {
                $this->createOrderItems($order, $products, rand(1, 5));
                $order->calculateTotals();
            });
    }

    /**
     * Create high value orders.
     */
    private function createHighValueOrders($products, $customers): void
    {
        $this->command->info('💎 Tạo đơn hàng giá trị cao...');
        
        Order::factory(10)
            ->highValue()
            ->completed()
            ->create()
            ->each(function ($order) use ($products) {
                $this->createOrderItems($order, $products, rand(3, 8), true);
                $order->calculateTotals();
            });
    }

    /**
     * Create order items for an order.
     */
    private function createOrderItems($order, $products, $itemCount, $premium = false): void
    {
        $selectedProducts = $products->random($itemCount);
        
        foreach ($selectedProducts as $product) {
            $factory = OrderItem::factory()->forOrder($order)->forProduct($product);
            
            if ($premium) {
                $factory = $factory->premium();
            }
            
            $factory->create();
        }
    }

    /**
     * Display order statistics.
     */
    private function displayStatistics(): void
    {
        $stats = Order::getStatistics();
        
        $this->command->info('📊 Thống kê đơn hàng:');
        $this->command->info("   • Tổng đơn hàng: {$stats['total_orders']}");
        $this->command->info("   • Đã hoàn thành: {$stats['completed_orders']}");
        $this->command->info("   • Đang xử lý: {$stats['processing_orders']}");
        $this->command->info("   • Đã hủy: {$stats['cancelled_orders']}");
        $this->command->info("   • Tổng doanh thu: " . number_format($stats['total_revenue'], 0, ',', '.') . ' VND');
        $this->command->info("   • Giá trị đơn hàng TB: " . number_format($stats['average_order_value'], 0, ',', '.') . ' VND');
    }
}
