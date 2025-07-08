<?php
/**
 * Simple test script for Quick Order system
 * Run this file directly in browser: http://your-domain/test-quick-order-simple.php
 */

// Basic configuration
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🧾 Quick Order System Test</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
    .success { color: green; background: #d4edda; padding: 10px; margin: 5px 0; border-radius: 5px; }
    .error { color: red; background: #f8d7da; padding: 10px; margin: 5px 0; border-radius: 5px; }
    .info { color: blue; background: #d1ecf1; padding: 10px; margin: 5px 0; border-radius: 5px; }
    .test-section { border: 1px solid #ddd; padding: 15px; margin: 15px 0; border-radius: 5px; }
    pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
</style>";

// Test 1: Check if files exist
echo "<div class='test-section'>";
echo "<h2>📁 File Structure Test</h2>";

$files_to_check = [
    'app/Http/Controllers/Admin/QuickOrderController.php',
    'app/Http/Controllers/Api/ProductBarcodeController.php',
    'app/Services/QuickOrderService.php',
    'resources/views/admin/quick-order/index.blade.php',
    'public/admin/js/quick-order.js',
    'database/migrations/2024_01_20_000001_add_barcode_to_products_table.php',
    'database/seeders/AddBarcodeToProductsSeeder.php',
    'app/Console/Commands/TestQuickOrderSystem.php',
];

foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        echo "<div class='success'>✓ {$file}</div>";
    } else {
        echo "<div class='error'>✗ {$file} - NOT FOUND</div>";
    }
}
echo "</div>";

// Test 2: Check routes file
echo "<div class='test-section'>";
echo "<h2>🛣️ Routes Test</h2>";

if (file_exists('routes/admin.php')) {
    $admin_routes = file_get_contents('routes/admin.php');
    if (strpos($admin_routes, 'quick-order') !== false) {
        echo "<div class='success'>✓ Quick Order routes found in admin.php</div>";
    } else {
        echo "<div class='error'>✗ Quick Order routes NOT found in admin.php</div>";
    }
} else {
    echo "<div class='error'>✗ routes/admin.php NOT found</div>";
}

if (file_exists('routes/api.php')) {
    $api_routes = file_get_contents('routes/api.php');
    if (strpos($api_routes, 'ProductBarcodeController') !== false) {
        echo "<div class='success'>✓ Product Barcode API routes found in api.php</div>";
    } else {
        echo "<div class='error'>✗ Product Barcode API routes NOT found in api.php</div>";
    }
} else {
    echo "<div class='error'>✗ routes/api.php NOT found</div>";
}
echo "</div>";

// Test 3: Check models
echo "<div class='test-section'>";
echo "<h2>📊 Models Test</h2>";

$models_to_check = [
    'app/Models/Product.php',
    'app/Models/Customer.php',
    'app/Models/BranchShop.php',
];

foreach ($models_to_check as $model) {
    if (file_exists($model)) {
        $content = file_get_contents($model);
        echo "<div class='success'>✓ {$model} exists</div>";
        
        // Check for specific methods/properties
        if ($model === 'app/Models/Product.php' && strpos($content, 'barcode') !== false) {
            echo "<div class='info'>  → Barcode field support detected</div>";
        }
        if ($model === 'app/Models/Customer.php' && strpos($content, 'scopeActive') !== false) {
            echo "<div class='info'>  → Active scope detected</div>";
        }
        if ($model === 'app/Models/BranchShop.php' && strpos($content, 'scopeActive') !== false) {
            echo "<div class='info'>  → Active scope detected</div>";
        }
    } else {
        echo "<div class='error'>✗ {$model} - NOT FOUND</div>";
    }
}
echo "</div>";

// Test 4: Check JavaScript
echo "<div class='test-section'>";
echo "<h2>📜 JavaScript Test</h2>";

if (file_exists('public/admin/js/quick-order.js')) {
    $js_content = file_get_contents('public/admin/js/quick-order.js');
    $js_size = strlen($js_content);
    echo "<div class='success'>✓ quick-order.js exists ({$js_size} bytes)</div>";
    
    // Check for key functions
    $functions_to_check = [
        'searchBarcode',
        'addProductToOrder',
        'createOrder',
        'handleBarcodeInput',
        'updateOrderDisplay'
    ];
    
    foreach ($functions_to_check as $func) {
        if (strpos($js_content, $func) !== false) {
            echo "<div class='info'>  → Function {$func}() found</div>";
        } else {
            echo "<div class='error'>  → Function {$func}() NOT found</div>";
        }
    }
} else {
    echo "<div class='error'>✗ public/admin/js/quick-order.js - NOT FOUND</div>";
}
echo "</div>";

// Test 5: Check language files
echo "<div class='test-section'>";
echo "<h2>🌐 Language Files Test</h2>";

$lang_files = [
    'resources/lang/vi/order.php',
    'resources/lang/en/order.php',
];

foreach ($lang_files as $lang_file) {
    if (file_exists($lang_file)) {
        $content = file_get_contents($lang_file);
        echo "<div class='success'>✓ {$lang_file} exists</div>";
        
        if (strpos($content, 'quick_order') !== false) {
            echo "<div class='info'>  → Quick Order translations found</div>";
        } else {
            echo "<div class='error'>  → Quick Order translations NOT found</div>";
        }
    } else {
        echo "<div class='error'>✗ {$lang_file} - NOT FOUND</div>";
    }
}
echo "</div>";

// Test 6: Environment check
echo "<div class='test-section'>";
echo "<h2>🔧 Environment Test</h2>";

if (file_exists('.env')) {
    echo "<div class='success'>✓ .env file exists</div>";
} else {
    echo "<div class='error'>✗ .env file NOT found</div>";
}

if (file_exists('composer.json')) {
    echo "<div class='success'>✓ composer.json exists</div>";
} else {
    echo "<div class='error'>✗ composer.json NOT found</div>";
}

if (file_exists('vendor/autoload.php')) {
    echo "<div class='success'>✓ Composer dependencies installed</div>";
} else {
    echo "<div class='error'>✗ Composer dependencies NOT installed - Run: composer install</div>";
}
echo "</div>";

// Test 7: Quick links
echo "<div class='test-section'>";
echo "<h2>🔗 Quick Links</h2>";

$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
$current_dir = dirname($_SERVER['REQUEST_URI']);

echo "<div class='info'>";
echo "<strong>Quick Order URLs to test:</strong><br>";
echo "• <a href='{$base_url}/admin/quick-order' target='_blank'>Quick Order Page</a><br>";
echo "• <a href='{$base_url}/api/products/search?q=test&limit=5' target='_blank'>Product Search API</a><br>";
echo "• <a href='{$base_url}/api/products/barcode/1234567890123' target='_blank'>Barcode Search API</a><br>";
echo "• <a href='{$current_dir}/test-quick-order.html' target='_blank'>Interactive Test Page</a><br>";
echo "</div>";
echo "</div>";

// Summary
echo "<div class='test-section'>";
echo "<h2>📋 Summary</h2>";
echo "<div class='info'>";
echo "<strong>Next Steps:</strong><br>";
echo "1. Make sure your Laravel server is running<br>";
echo "2. Run migrations: <code>php artisan migrate</code><br>";
echo "3. Seed barcode data: <code>php artisan db:seed --class=AddBarcodeToProductsSeeder</code><br>";
echo "4. Test the Quick Order page: <a href='{$base_url}/admin/quick-order' target='_blank'>/admin/quick-order</a><br>";
echo "5. Use the interactive test page for API testing<br>";
echo "</div>";
echo "</div>";

echo "<p><em>Test completed at: " . date('Y-m-d H:i:s') . "</em></p>";
?>
