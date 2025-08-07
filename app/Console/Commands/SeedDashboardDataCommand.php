<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Supplier;
use App\Models\BranchShop;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;

class SeedDashboardDataCommand extends Command
{
    protected $signature = 'seed:dashboard-data {--days=7 : Number of days to seed data for}';
    protected $description = 'Seed sample data for dashboard testing';

    public function handle()
    {
        $days = $this->option('days');
        
        $this->info('🌱 Seeding dashboard data for ' . $days . ' days...');
        $this->newLine();

        // Create basic data first
        $this->createBasicData();
        
        // Create sample data for the specified days
        $this->createSampleOrders($days);
        
        $this->info('✅ Dashboard data seeded successfully!');
        $this->call('debug:dashboard');
    }

    private function createBasicData()
    {
        $this->info('📦 Creating basic data...');

        // Create branch shop if not exists
        if (BranchShop::count() == 0) {
            try {
                BranchShop::create([
                    'code' => 'CN001',
                    'name' => 'Chi nhánh chính',
                    'address' => '123 Đường ABC, Quận 1, TP.HCM',
                    'phone' => '0123456789',
                    'status' => 'active',
                    'shop_type' => 'flagship'
                ]);
            } catch (\Exception $e) {
                $this->warn('Could not create branch shop: ' . $e->getMessage());
                $this->line('Skipping branch shop creation...');
            }
        }

        // Create categories if not exists
        if (Category::count() == 0) {
            $categories = ['Điện tử', 'Thời trang', 'Gia dụng', 'Sách', 'Thể thao'];
            foreach ($categories as $categoryName) {
                Category::create([
                    'name' => $categoryName,
                    'slug' => Str::slug($categoryName),
                    'status' => 'active'
                ]);
            }
        }

        // Create brands if not exists
        if (Brand::count() == 0) {
            $brands = ['Samsung', 'Apple', 'Nike', 'Adidas', 'Sony'];
            foreach ($brands as $brandName) {
                Brand::create([
                    'name' => $brandName,
                    'slug' => Str::slug($brandName),
                    'status' => 'active'
                ]);
            }
        }

        // Create suppliers if not exists
        if (Supplier::count() == 0) {
            Supplier::create([
                'name' => 'Nhà cung cấp chính',
                'email' => 'supplier@example.com',
                'phone' => '0987654321',
                'address' => '456 Đường XYZ, Quận 2, TP.HCM',
                'status' => 'active'
            ]);
        }

        // Create products if not exists
        if (Product::count() == 0) {
            $category = Category::first();
            $brand = Brand::first();
            $supplier = Supplier::first();

            $products = [
                ['name' => 'iPhone 15 Pro', 'price' => 25000000],
                ['name' => 'Samsung Galaxy S24', 'price' => 20000000],
                ['name' => 'MacBook Air M2', 'price' => 30000000],
                ['name' => 'iPad Pro', 'price' => 15000000],
                ['name' => 'AirPods Pro', 'price' => 5000000],
                ['name' => 'Nike Air Max', 'price' => 3000000],
                ['name' => 'Adidas Ultraboost', 'price' => 3500000],
                ['name' => 'Sony WH-1000XM5', 'price' => 8000000],
                ['name' => 'Dell XPS 13', 'price' => 25000000],
                ['name' => 'Canon EOS R5', 'price' => 45000000],
            ];

            foreach ($products as $index => $productData) {
                Product::create([
                    'product_name' => $productData['name'],
                    'sku' => 'SKU' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                    'barcode' => '123456789' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                    'product_description' => 'Mô tả sản phẩm ' . $productData['name'],
                    'cost_price' => $productData['price'] * 0.7,
                    'sale_price' => $productData['price'],
                    'min_price' => $productData['price'] * 0.8,
                    'max_price' => $productData['price'] * 1.2,
                    'product_status' => 'publish',
                    'category_id' => $category->id,
                    'brand_id' => $brand->id,
                    'supplier_id' => $supplier->id,
                    'reorder_point' => 10,
                    'loyalty_points' => rand(10, 100),
                ]);
            }
        }

        // Create customers if not exists
        if (Customer::count() == 0) {
            $customers = [
                ['name' => 'Nguyễn Văn A', 'phone' => '0901234567'],
                ['name' => 'Trần Thị B', 'phone' => '0901234568'],
                ['name' => 'Lê Văn C', 'phone' => '0901234569'],
                ['name' => 'Phạm Thị D', 'phone' => '0901234570'],
                ['name' => 'Hoàng Văn E', 'phone' => '0901234571'],
            ];

            foreach ($customers as $customerData) {
                Customer::create([
                    'name' => $customerData['name'],
                    'phone' => $customerData['phone'],
                    'email' => Str::slug($customerData['name']) . '@example.com',
                    'address' => 'Địa chỉ của ' . $customerData['name'],
                    'customer_type' => 'individual',
                    'status' => 'active',
                ]);
            }
        }

        $this->line('✅ Basic data created');
    }

    private function createSampleOrders($days)
    {
        $this->info('📋 Creating sample orders for ' . $days . ' days...');

        $branchShop = BranchShop::first();
        $user = User::first();
        $customers = Customer::all();
        $products = Product::all();

        if (!$user) {
            $this->error('No users found. Please create a user first.');
            return;
        }

        for ($i = 0; $i < $days; $i++) {
            $date = Carbon::today()->subDays($i);
            
            // Create 2-8 orders per day
            $ordersPerDay = rand(2, 8);
            
            for ($j = 0; $j < $ordersPerDay; $j++) {
                $customer = $customers->random();
                $orderTime = $date->copy()->addHours(rand(8, 20))->addMinutes(rand(0, 59));
                
                // Create order
                $order = Order::create([
                    'order_number' => 'ORD' . $orderTime->format('Ymd') . str_pad(($j + 1), 3, '0', STR_PAD_LEFT),
                    'customer_id' => $customer->id,
                    'customer_name' => $customer->name,
                    'customer_phone' => $customer->phone,
                    'customer_email' => $customer->email,
                    'customer_address' => $customer->address,
                    'order_type' => 'sale',
                    'status' => collect(['draft', 'processing', 'completed'])->random(),
                    'payment_status' => collect(['pending', 'paid', 'partial'])->random(),
                    'delivery_status' => collect(['pending', 'processing', 'delivered'])->random(),
                    'subtotal_amount' => 0, // Will calculate below
                    'tax_amount' => 0,
                    'discount_amount' => rand(0, 500000),
                    'shipping_amount' => rand(0, 100000),
                    'final_amount' => 0, // Will calculate below
                    'notes' => 'Đơn hàng mẫu cho ngày ' . $orderTime->format('d/m/Y'),
                    'branch_shop_id' => $branchShop->id,
                    'created_by' => $user->id,
                    'created_at' => $orderTime,
                    'updated_at' => $orderTime,
                ]);

                // Add random products to order
                $numProducts = rand(1, 4);
                $subtotal = 0;

                for ($k = 0; $k < $numProducts; $k++) {
                    $product = $products->random();
                    $quantity = rand(1, 3);
                    $unitPrice = $product->sale_price;
                    $totalPrice = $quantity * $unitPrice;
                    $subtotal += $totalPrice;

                    // Create order item (if table exists)
                    try {
                        \DB::table('order_items')->insert([
                            'order_id' => $order->id,
                            'product_id' => $product->id,
                            'product_name' => $product->product_name,
                            'product_sku' => $product->sku,
                            'quantity' => $quantity,
                            'unit_price' => $unitPrice,
                            'total_price' => $totalPrice,
                            'created_at' => $orderTime,
                            'updated_at' => $orderTime,
                        ]);
                    } catch (\Exception $e) {
                        // Order items table might not exist, skip
                    }
                }

                // Update order totals
                $finalAmount = $subtotal - $order->discount_amount + $order->shipping_amount;
                $order->update([
                    'subtotal_amount' => $subtotal,
                    'final_amount' => $finalAmount,
                ]);
            }
            
            $this->line('📅 ' . $date->format('d/m/Y') . ': ' . $ordersPerDay . ' orders created');
        }

        $this->line('✅ Sample orders created');
    }
}
