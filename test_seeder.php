<?php

// Simple test script to verify seeder syntax
require_once 'vendor/autoload.php';

echo "Testing ProductSeeder syntax...\n";

try {
    // Test if classes can be loaded
    $productSeederClass = new ReflectionClass('Database\Seeders\ProductSeeder');
    echo "✓ ProductSeeder class loads successfully\n";
    
    $productFactoryClass = new ReflectionClass('Database\Factories\ProductFactory');
    echo "✓ ProductFactory class loads successfully\n";
    
    $inventoryFactoryClass = new ReflectionClass('Database\Factories\InventoryFactory');
    echo "✓ InventoryFactory class loads successfully\n";
    
    echo "\nAll seeder files are syntactically correct!\n";
    echo "\nTo run the seeder:\n";
    echo "php artisan db:seed --class=ProductSeeder\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
