<?php

/**
 * Test script to verify inventory_alerts removal
 * Run this script to check if all inventory_alerts references have been properly removed
 */

echo "=== Inventory Alerts Removal Verification ===\n\n";

// Check if InventoryAlert model file exists
echo "1. Checking InventoryAlert Model File:\n";
$modelPath = 'app/Models/InventoryAlert.php';
if (file_exists($modelPath)) {
    echo "   ❌ ERROR: InventoryAlert.php still exists at {$modelPath}\n";
} else {
    echo "   ✅ SUCCESS: InventoryAlert.php has been removed\n";
}

// Check Product model for alert references
echo "\n2. Checking Product Model for Alert References:\n";
$productModelPath = 'app/Models/Product.php';
if (file_exists($productModelPath)) {
    $productContent = file_get_contents($productModelPath);
    
    $alertReferences = [
        'inventoryAlerts' => 'inventoryAlerts() method',
        'unresolvedAlerts' => 'unresolvedAlerts() method',
        'checkStockAlerts' => 'checkStockAlerts() method',
        'createAlert' => 'createAlert() method',
        'InventoryAlert::' => 'InventoryAlert class references'
    ];
    
    $foundReferences = [];
    foreach ($alertReferences as $reference => $description) {
        if (strpos($productContent, $reference) !== false) {
            $foundReferences[] = $description;
        }
    }
    
    if (empty($foundReferences)) {
        echo "   ✅ SUCCESS: No alert references found in Product model\n";
    } else {
        echo "   ❌ ERROR: Found alert references in Product model:\n";
        foreach ($foundReferences as $ref) {
            echo "      - {$ref}\n";
        }
    }
} else {
    echo "   ❌ ERROR: Product model not found\n";
}

// Check InventoryService for alert references
echo "\n3. Checking InventoryService for Alert References:\n";
$servicePath = 'app/Services/InventoryService.php';
if (file_exists($servicePath)) {
    $serviceContent = file_get_contents($servicePath);
    
    $alertReferences = [
        'resolveAlert' => 'resolveAlert() method',
        'InventoryAlert' => 'InventoryAlert class references',
        'unresolved_alerts' => 'unresolved_alerts field'
    ];
    
    $foundReferences = [];
    foreach ($alertReferences as $reference => $description) {
        if (strpos($serviceContent, $reference) !== false) {
            $foundReferences[] = $description;
        }
    }
    
    if (empty($foundReferences)) {
        echo "   ✅ SUCCESS: No alert references found in InventoryService\n";
    } else {
        echo "   ❌ ERROR: Found alert references in InventoryService:\n";
        foreach ($foundReferences as $ref) {
            echo "      - {$ref}\n";
        }
    }
} else {
    echo "   ❌ ERROR: InventoryService not found\n";
}

// Check InventoryController for alert references
echo "\n4. Checking InventoryController for Alert References:\n";
$controllerPath = 'app/Http/Controllers/Admin/CMS/InventoryController.php';
if (file_exists($controllerPath)) {
    $controllerContent = file_get_contents($controllerPath);
    
    $alertReferences = [
        'alerts()' => 'alerts() method',
        'ajaxGetAlerts' => 'ajaxGetAlerts() method',
        'resolveAlert' => 'resolveAlert() method',
        'getAlertActions' => 'getAlertActions() method',
        'InventoryAlert' => 'InventoryAlert class references',
        'unresolvedAlerts' => 'unresolvedAlerts variable'
    ];
    
    $foundReferences = [];
    foreach ($alertReferences as $reference => $description) {
        if (strpos($controllerContent, $reference) !== false) {
            $foundReferences[] = $description;
        }
    }
    
    if (empty($foundReferences)) {
        echo "   ✅ SUCCESS: No alert references found in InventoryController\n";
    } else {
        echo "   ❌ ERROR: Found alert references in InventoryController:\n";
        foreach ($foundReferences as $ref) {
            echo "      - {$ref}\n";
        }
    }
} else {
    echo "   ❌ ERROR: InventoryController not found\n";
}

// Check routes for alert references
echo "\n5. Checking Routes for Alert References:\n";
$routesPath = 'routes/admin.php';
if (file_exists($routesPath)) {
    $routesContent = file_get_contents($routesPath);
    
    $alertRoutes = [
        'inventory.alerts' => 'inventory alerts route',
        'alerts/ajax' => 'alerts ajax route',
        'resolveAlert' => 'resolve alert route'
    ];
    
    $foundRoutes = [];
    foreach ($alertRoutes as $route => $description) {
        if (strpos($routesContent, $route) !== false) {
            $foundRoutes[] = $description;
        }
    }
    
    if (empty($foundRoutes)) {
        echo "   ✅ SUCCESS: No alert routes found in admin routes\n";
    } else {
        echo "   ❌ ERROR: Found alert routes in admin routes:\n";
        foreach ($foundRoutes as $route) {
            echo "      - {$route}\n";
        }
    }
} else {
    echo "   ❌ ERROR: Admin routes file not found\n";
}

// Check for migration file
echo "\n6. Checking Drop Migration File:\n";
$migrationPath = 'database/migrations/2025_06_17_130000_drop_inventory_alerts_table.php';
if (file_exists($migrationPath)) {
    echo "   ✅ SUCCESS: Drop migration file exists\n";
} else {
    echo "   ❌ ERROR: Drop migration file not found\n";
}

// Check for any remaining alert references in codebase
echo "\n7. Scanning for Remaining Alert References:\n";
$directories = ['app/', 'resources/views/', 'public/'];
$alertPatterns = ['InventoryAlert', 'inventory_alerts', 'inventoryAlerts'];

$foundFiles = [];
foreach ($directories as $dir) {
    if (is_dir($dir)) {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
        foreach ($iterator as $file) {
            if ($file->isFile() && in_array($file->getExtension(), ['php', 'blade.php', 'js'])) {
                $content = file_get_contents($file->getPathname());
                foreach ($alertPatterns as $pattern) {
                    if (strpos($content, $pattern) !== false) {
                        $foundFiles[] = $file->getPathname() . " (contains: {$pattern})";
                        break;
                    }
                }
            }
        }
    }
}

if (empty($foundFiles)) {
    echo "   ✅ SUCCESS: No remaining alert references found in codebase\n";
} else {
    echo "   ⚠️  WARNING: Found potential alert references:\n";
    foreach (array_unique($foundFiles) as $file) {
        echo "      - {$file}\n";
    }
    echo "   Note: These may be in documentation or comments\n";
}

// Summary
echo "\n=== Summary ===\n";
echo "Inventory alerts removal verification complete.\n";
echo "Please review any errors or warnings above.\n\n";

echo "Next steps:\n";
echo "1. Run the drop migration: php artisan migrate\n";
echo "2. Test inventory dashboard: Visit /admin/inventory\n";
echo "3. Test product operations: Create/edit products\n";
echo "4. Test stock operations: Process inventory transactions\n";
echo "5. Check application logs for any errors\n\n";

echo "If all tests pass, inventory alerts have been successfully removed!\n";
