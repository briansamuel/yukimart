<?php
// Script to create test products for Quick Order testing

require_once 'vendor/autoload.php';

use App\Models\Product;
use App\Models\Inventory;
use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    DB::beginTransaction();
    
    echo "Creating test products...\n";
    
    // Test Product 1: Laptop
    $laptop = Product::create([
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
    ]);
    
    // Test Product 2: Mouse
    $mouse = Product::create([
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
    ]);
    
    // Test Product 3: Keyboard
    $keyboard = Product::create([
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
    ]);
    
    // Test Product 4: Monitor
    $monitor = Product::create([
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
    ]);
    
    // Test Product 5: Phone
    $phone = Product::create([
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
    ]);
    
    echo "Created products:\n";
    echo "- Laptop: ID {$laptop->id}\n";
    echo "- Mouse: ID {$mouse->id}\n";
    echo "- Keyboard: ID {$keyboard->id}\n";
    echo "- Monitor: ID {$monitor->id}\n";
    echo "- Phone: ID {$phone->id}\n";
    
    // Create inventory records
    echo "\nCreating inventory records...\n";
    
    $inventoryData = [
        ['product_id' => $laptop->id, 'warehouse_id' => 1, 'quantity' => 10],
        ['product_id' => $mouse->id, 'warehouse_id' => 1, 'quantity' => 25],
        ['product_id' => $keyboard->id, 'warehouse_id' => 1, 'quantity' => 15],
        ['product_id' => $monitor->id, 'warehouse_id' => 1, 'quantity' => 8],
        ['product_id' => $phone->id, 'warehouse_id' => 1, 'quantity' => 5],
    ];
    
    foreach ($inventoryData as $data) {
        Inventory::create($data);
        echo "- Created inventory for product ID {$data['product_id']}: {$data['quantity']} units\n";
    }
    
    DB::commit();
    echo "\n✅ Successfully created 5 test products with inventory!\n";
    
} catch (Exception $e) {
    DB::rollback();
    echo "\n❌ Error creating test products: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
