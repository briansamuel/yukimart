<?php
// Script to create test products using raw SQL to avoid notification issues

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    DB::beginTransaction();
    
    echo "Creating test products using raw SQL...\n";
    
    // Insert products using raw SQL
    $productsData = [
        [
            'product_name' => 'Laptop Dell Inspiron 15',
            'product_slug' => 'laptop-dell-inspiron-15',
            'product_description' => 'Laptop Dell Inspiron 15 inch với hiệu năng cao',
            'product_content' => 'Laptop Dell Inspiron 15 inch với hiệu năng cao, phù hợp cho công việc và giải trí',
            'sku' => 'LAPTOP001',
            'barcode' => '1234567890123',
            'cost_price' => 15000000.00,
            'sale_price' => 18000000.00,
            'product_status' => 'publish',
            'product_type' => 'simple',
            'brand' => 'Dell',
            'weight' => 2500,
            'points' => 180,
            'reorder_point' => 5,
            'product_feature' => 1,
            'created_by_user' => 1,
            'updated_by_user' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'product_name' => 'Chuột Logitech MX Master 3',
            'product_slug' => 'chuot-logitech-mx-master-3',
            'product_description' => 'Chuột không dây Logitech MX Master 3 cao cấp',
            'product_content' => 'Chuột không dây Logitech MX Master 3 với thiết kế ergonomic và pin lâu',
            'sku' => 'MOUSE001',
            'barcode' => '2345678901234',
            'cost_price' => 1500000.00,
            'sale_price' => 2200000.00,
            'product_status' => 'publish',
            'product_type' => 'simple',
            'brand' => 'Logitech',
            'weight' => 150,
            'points' => 22,
            'reorder_point' => 10,
            'product_feature' => 0,
            'created_by_user' => 1,
            'updated_by_user' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'product_name' => 'Bàn phím cơ Keychron K2',
            'product_slug' => 'ban-phim-co-keychron-k2',
            'product_description' => 'Bàn phím cơ Keychron K2 wireless với switch Blue',
            'product_content' => 'Bàn phím cơ Keychron K2 wireless với switch Blue, kết nối Bluetooth và USB-C',
            'sku' => 'KEYBOARD001',
            'barcode' => '3456789012345',
            'cost_price' => 2000000.00,
            'sale_price' => 2800000.00,
            'product_status' => 'publish',
            'product_type' => 'simple',
            'brand' => 'Keychron',
            'weight' => 800,
            'points' => 28,
            'reorder_point' => 8,
            'product_feature' => 1,
            'created_by_user' => 1,
            'updated_by_user' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'product_name' => 'Màn hình Samsung 24 inch',
            'product_slug' => 'man-hinh-samsung-24-inch',
            'product_description' => 'Màn hình Samsung 24 inch Full HD IPS',
            'product_content' => 'Màn hình Samsung 24 inch Full HD IPS với độ phân giải 1920x1080',
            'sku' => 'MONITOR001',
            'barcode' => '4567890123456',
            'cost_price' => 3500000.00,
            'sale_price' => 4200000.00,
            'product_status' => 'publish',
            'product_type' => 'simple',
            'brand' => 'Samsung',
            'weight' => 4000,
            'points' => 42,
            'reorder_point' => 3,
            'product_feature' => 0,
            'created_by_user' => 1,
            'updated_by_user' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'product_name' => 'iPhone 15 Pro Max',
            'product_slug' => 'iphone-15-pro-max',
            'product_description' => 'iPhone 15 Pro Max 256GB Titanium Natural',
            'product_content' => 'iPhone 15 Pro Max 256GB Titanium Natural với chip A17 Pro và camera 48MP',
            'sku' => 'PHONE001',
            'barcode' => '5678901234567',
            'cost_price' => 28000000.00,
            'sale_price' => 32000000.00,
            'product_status' => 'publish',
            'product_type' => 'simple',
            'brand' => 'Apple',
            'weight' => 221,
            'points' => 320,
            'reorder_point' => 2,
            'product_feature' => 1,
            'created_by_user' => 1,
            'updated_by_user' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ];
    
    // Insert products
    foreach ($productsData as $productData) {
        DB::table('products')->insert($productData);
        echo "Created product: {$productData['product_name']}\n";
    }
    
    // Get product IDs
    $productIds = DB::table('products')
        ->whereIn('sku', ['LAPTOP001', 'MOUSE001', 'KEYBOARD001', 'MONITOR001', 'PHONE001'])
        ->pluck('id', 'sku');
    
    echo "\nCreated products with IDs:\n";
    foreach ($productIds as $sku => $id) {
        echo "- {$sku}: ID {$id}\n";
    }
    
    // Create inventory records
    echo "\nCreating inventory records...\n";
    
    $inventoryData = [
        ['product_id' => $productIds['LAPTOP001'], 'warehouse_id' => 1, 'quantity' => 10, 'created_at' => now(), 'updated_at' => now()],
        ['product_id' => $productIds['MOUSE001'], 'warehouse_id' => 1, 'quantity' => 25, 'created_at' => now(), 'updated_at' => now()],
        ['product_id' => $productIds['KEYBOARD001'], 'warehouse_id' => 1, 'quantity' => 15, 'created_at' => now(), 'updated_at' => now()],
        ['product_id' => $productIds['MONITOR001'], 'warehouse_id' => 1, 'quantity' => 8, 'created_at' => now(), 'updated_at' => now()],
        ['product_id' => $productIds['PHONE001'], 'warehouse_id' => 1, 'quantity' => 5, 'created_at' => now(), 'updated_at' => now()],
    ];
    
    foreach ($inventoryData as $data) {
        DB::table('inventories')->insert($data);
        echo "- Created inventory for product ID {$data['product_id']}: {$data['quantity']} units\n";
    }
    
    DB::commit();
    echo "\n✅ Successfully created 5 test products with inventory using raw SQL!\n";
    
    // Display summary
    $totalProducts = DB::table('products')->count();
    echo "\nSummary:\n";
    echo "- Total products in database: {$totalProducts}\n";
    echo "- Test products created: 5\n";
    echo "- Inventory records created: 5\n";
    
} catch (Exception $e) {
    DB::rollback();
    echo "\n❌ Error creating test products: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
