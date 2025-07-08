<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\Warehouse;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure we have at least one user for created_by and updated_by
        $user = User::first();
        if (!$user) {
            $user = User::factory()->create([
                'name' => 'Admin User',
                'email' => 'admin@yukimart.com',
                'password' => bcrypt('password'),
            ]);
        }

        $this->command->info('Creating 20 products with inventory...');

        // Create products in batches with different categories and stock levels
        DB::transaction(function () use ($user) {
            
            // Electronics Products (8 products)
            $this->command->info('Creating electronics products...');
            $electronicsProducts = [
                [
                    'name' => 'iPhone 15 Pro Max',
                    'brand' => 'Apple',
                    'cost' => 25000000,
                    'sale' => 30000000,
                    'stock' => 50,
                    'reorder' => 10
                ],
                [
                    'name' => 'Samsung Galaxy S24 Ultra',
                    'brand' => 'Samsung',
                    'cost' => 22000000,
                    'sale' => 27000000,
                    'stock' => 35,
                    'reorder' => 8
                ],
                [
                    'name' => 'MacBook Pro M3',
                    'brand' => 'Apple',
                    'cost' => 45000000,
                    'sale' => 55000000,
                    'stock' => 15,
                    'reorder' => 5
                ],
                [
                    'name' => 'Sony WH-1000XM5 Headphones',
                    'brand' => 'Sony',
                    'cost' => 7000000,
                    'sale' => 9000000,
                    'stock' => 80,
                    'reorder' => 20
                ],
                [
                    'name' => 'iPad Air M2',
                    'brand' => 'Apple',
                    'cost' => 15000000,
                    'sale' => 18000000,
                    'stock' => 25,
                    'reorder' => 8
                ],
                [
                    'name' => 'LG OLED 55" Smart TV',
                    'brand' => 'LG',
                    'cost' => 20000000,
                    'sale' => 25000000,
                    'stock' => 12,
                    'reorder' => 3
                ],
                [
                    'name' => 'Canon EOS R6 Mark II',
                    'brand' => 'Canon',
                    'cost' => 35000000,
                    'sale' => 42000000,
                    'stock' => 8,
                    'reorder' => 2
                ],
                [
                    'name' => 'Xiaomi Mi Band 8',
                    'brand' => 'Xiaomi',
                    'cost' => 800000,
                    'sale' => 1200000,
                    'stock' => 200,
                    'reorder' => 50
                ]
            ];

            foreach ($electronicsProducts as $productData) {
                $product = Product::create([
                    'product_name' => $productData['name'],
                    'product_slug' => Str::slug($productData['name']),
                    'product_description' => "High-quality {$productData['name']} with premium features and excellent performance.",
                    'product_content' => "Experience the latest technology with {$productData['name']}. This product offers exceptional quality, innovative features, and reliable performance that meets all your needs. Perfect for both personal and professional use.",
                    'sku' => 'SKU-' . strtoupper(Str::random(6)),
                    'cost_price' => $productData['cost'],
                    'sale_price' => $productData['sale'],
                    'product_status' => 'publish',
                    'product_type' => 'simple',
                    'brand' => $productData['brand'],
                    'weight' => rand(100, 2000),
                    'points' => rand(10, 100),
                    'reorder_point' => $productData['reorder'],
                    'product_feature' => rand(0, 1),
                    'created_by_user' => $user->id,
                    'updated_by_user' => $user->id,
                ]);

                // Create inventory record (quantity will be set by InventoryTransactionSeeder)
                Inventory::create([
                    'product_id' => $product->id,
                    'warehouse_id' => 1, // Kho mặc định
                    'quantity' => 0, // Start with 0, will be updated by transactions
                ]);
            }

            // Fashion Products (7 products)
            $this->command->info('Creating fashion products...');
            $fashionProducts = [
                [
                    'name' => 'Nike Air Max 270',
                    'brand' => 'Nike',
                    'cost' => 2000000,
                    'sale' => 2800000,
                    'stock' => 0,
                    'reorder' => 15
                ],
                [
                    'name' => 'Adidas Ultraboost 22',
                    'brand' => 'Adidas',
                    'cost' => 2200000,
                    'sale' => 3000000,
                    'stock' => 0,
                    'reorder' => 12
                ],
                [
                    'name' => 'Uniqlo Heattech T-Shirt',
                    'brand' => 'Uniqlo',
                    'cost' => 200000,
                    'sale' => 350000,
                    'stock' => 0,
                    'reorder' => 30
                ],
                [
                    'name' => 'Zara Slim Fit Jeans',
                    'brand' => 'Zara',
                    'cost' => 800000,
                    'sale' => 1200000,
                    'stock' => 0,
                    'reorder' => 20
                ],
                [
                    'name' => 'H&M Cotton Hoodie',
                    'brand' => 'H&M',
                    'cost' => 400000,
                    'sale' => 650000,
                    'stock' => 0,
                    'reorder' => 25
                ],
                [
                    'name' => 'Levi\'s 501 Original Jeans',
                    'brand' => 'Levi\'s',
                    'cost' => 1200000,
                    'sale' => 1800000,
                    'stock' => 0,
                    'reorder' => 15
                ],
                [
                    'name' => 'Champion Reverse Weave Sweatshirt',
                    'brand' => 'Champion',
                    'cost' => 800000,
                    'sale' => 1300000,
                    'stock' => 0,
                    'reorder' => 10
                ]
            ];

            foreach ($fashionProducts as $productData) {
                $product = Product::create([
                    'product_name' => $productData['name'],
                    'product_slug' => Str::slug($productData['name']),
                    'product_description' => "Stylish and comfortable {$productData['name']} perfect for everyday wear.",
                    'product_content' => "Discover the perfect blend of style and comfort with {$productData['name']}. Made from high-quality materials with attention to detail, this product offers exceptional durability and timeless design.",
                    'sku' => 'SKU-' . strtoupper(Str::random(6)),
                    'cost_price' => $productData['cost'],
                    'sale_price' => $productData['sale'],
                    'product_status' => 'publish',
                    'product_type' => 'variable',
                    'brand' => $productData['brand'],
                    'weight' => rand(200, 800),
                    'points' => rand(5, 50),
                    'reorder_point' => $productData['reorder'],
                    'product_feature' => rand(0, 1),
                    'created_by_user' => $user->id,
                    'updated_by_user' => $user->id,
                ]);

                // Create inventory record (quantity will be set by InventoryTransactionSeeder)
                Inventory::create([
                    'product_id' => $product->id,
                    'warehouse_id' => 1, // Kho mặc định
                    'quantity' => 0, // Start with 0, will be updated by transactions
                ]);
            }

            // Home & Garden Products (5 products)
            $this->command->info('Creating home & garden products...');
            $homeProducts = [
                [
                    'name' => 'IKEA MALM Bed Frame',
                    'brand' => 'IKEA',
                    'cost' => 3000000,
                    'sale' => 4200000,
                    'stock' => 0,
                    'reorder' => 5
                ],
                [
                    'name' => 'Dyson V15 Detect Vacuum',
                    'brand' => 'Dyson',
                    'cost' => 12000000,
                    'sale' => 15000000,
                    'stock' => 0,
                    'reorder' => 3
                ],
                [
                    'name' => 'Philips Air Fryer XXL',
                    'brand' => 'Philips',
                    'cost' => 4000000,
                    'sale' => 5500000,
                    'stock' => 0,
                    'reorder' => 8
                ],
                [
                    'name' => 'Muji Aroma Diffuser',
                    'brand' => 'Muji',
                    'cost' => 800000,
                    'sale' => 1200000,
                    'stock' => 0,
                    'reorder' => 15
                ],
                [
                    'name' => 'Xiaomi Robot Vacuum S10',
                    'brand' => 'Xiaomi',
                    'cost' => 6000000,
                    'sale' => 8000000,
                    'stock' => 0,
                    'reorder' => 4
                ]
            ];

            foreach ($homeProducts as $productData) {
                $product = Product::create([
                    'product_name' => $productData['name'],
                    'product_slug' => Str::slug($productData['name']),
                    'product_description' => "Premium {$productData['name']} designed to enhance your home living experience.",
                    'product_content' => "Transform your home with {$productData['name']}. This carefully designed product combines functionality with aesthetic appeal, making it a perfect addition to any modern home.",
                    'sku' => 'SKU-' . strtoupper(Str::random(6)),
                    'cost_price' => $productData['cost'],
                    'sale_price' => $productData['sale'],
                    'product_status' => 'publish',
                    'product_type' => 'simple',
                    'brand' => $productData['brand'],
                    'weight' => rand(1000, 10000),
                    'points' => rand(20, 150),
                    'reorder_point' => $productData['reorder'],
                    'product_feature' => rand(0, 1),
                    'created_by_user' => $user->id,
                    'updated_by_user' => $user->id,
                ]);

                // Create inventory record (quantity will be set by InventoryTransactionSeeder)
                Inventory::create([
                    'product_id' => $product->id,
                    'warehouse_id' => 1, // Kho mặc định
                    'quantity' => 0, // Start with 0, will be updated by transactions
                ]);
            }
        });

        $this->command->info('Successfully created 20 products with inventory records!');
        
        // Display summary
        $totalProducts = Product::count();
        $totalInventory = Inventory::sum('quantity');
        $lowStockCount = Product::join('inventories', 'products.id', '=', 'inventories.product_id')
            ->whereRaw('inventories.quantity <= products.reorder_point')
            ->count();
        
        $this->command->info("Summary:");
        $this->command->info("- Total Products: {$totalProducts}");
        $this->command->info("- Total Inventory: {$totalInventory} units");
        $this->command->info("- Low Stock Products: {$lowStockCount}");
    }
}
