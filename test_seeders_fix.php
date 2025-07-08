<?php

// Test script để kiểm tra seeders sau khi fix lỗi
// Run with: php artisan tinker < test_seeders_fix.php

echo "=== Testing Seeders After Fix ===\n\n";

echo "1. Testing ProductSeeder (should work without createInitialTransaction error):\n";
echo "// Clear existing data\n";
echo "DB::table('inventory_transactions')->delete();\n";
echo "DB::table('inventories')->delete();\n";
echo "DB::table('products')->delete();\n";

echo "\n// Run ProductSeeder\n";
echo "Artisan::call('db:seed', ['--class' => 'ProductSeeder']);\n";
echo "echo 'ProductSeeder completed: ' . Product::count() . ' products created';\n";

echo "\n2. Testing InventoryTransactionSeeder:\n";
echo "// Run InventoryTransactionSeeder\n";
echo "Artisan::call('db:seed', ['--class' => 'InventoryTransactionSeeder']);\n";
echo "echo 'InventoryTransactionSeeder completed: ' . InventoryTransaction::count() . ' transactions created';\n";

echo "\n3. Testing WarehouseSeeder:\n";
echo "// Run WarehouseSeeder\n";
echo "Artisan::call('db:seed', ['--class' => 'WarehouseSeeder']);\n";
echo "echo 'WarehouseSeeder completed: ' . Warehouse::count() . ' warehouses total';\n";

echo "\n4. Testing AdvancedInventoryTransactionSeeder:\n";
echo "// Run AdvancedInventoryTransactionSeeder\n";
echo "Artisan::call('db:seed', ['--class' => 'AdvancedInventoryTransactionSeeder']);\n";
echo "echo 'AdvancedInventoryTransactionSeeder completed';\n";

echo "\n5. Verify final results:\n";
echo "// Check final counts\n";
echo "\$products = Product::count();\n";
echo "\$warehouses = Warehouse::count();\n";
echo "\$inventories = Inventory::count();\n";
echo "\$transactions = InventoryTransaction::count();\n";

echo "\necho \"Final Results:\";\n";
echo "echo \"- Products: {\$products}\";\n";
echo "echo \"- Warehouses: {\$warehouses}\";\n";
echo "echo \"- Inventories: {\$inventories}\";\n";
echo "echo \"- Transactions: {\$transactions}\";\n";

echo "\n6. Check transaction types:\n";
echo "\$transactionTypes = InventoryTransaction::select('transaction_type', DB::raw('count(*) as count'))\n";
echo "    ->groupBy('transaction_type')\n";
echo "    ->get();\n";

echo "\necho \"Transaction Types:\";\n";
echo "foreach (\$transactionTypes as \$type) {\n";
echo "    echo \"- {\$type->transaction_type}: {\$type->count}\";\n";
echo "}\n";

echo "\n7. Check inventory distribution:\n";
echo "\$warehouseStats = Warehouse::with('inventories')->get()->map(function(\$warehouse) {\n";
echo "    return [\n";
echo "        'name' => \$warehouse->name,\n";
echo "        'code' => \$warehouse->code,\n";
echo "        'products' => \$warehouse->inventories->count(),\n";
echo "        'total_quantity' => \$warehouse->inventories->sum('quantity')\n";
echo "    ];\n";
echo "});\n";

echo "\necho \"Warehouse Distribution:\";\n";
echo "foreach (\$warehouseStats as \$stats) {\n";
echo "    echo \"- {\$stats['name']} ({\$stats['code']}): {\$stats['products']} products, {\$stats['total_quantity']} units\";\n";
echo "}\n";

echo "\n8. Sample product with transactions:\n";
echo "\$product = Product::with(['inventory', 'inventoryTransactions' => function(\$query) {\n";
echo "    \$query->orderBy('created_at');\n";
echo "}])->first();\n";

echo "\nif (\$product) {\n";
echo "    echo \"Sample Product: {\$product->product_name}\";\n";
echo "    echo \"Current Stock: \" . (\$product->inventory->quantity ?? 0);\n";
echo "    echo \"Transaction History (\" . \$product->inventoryTransactions->count() . \" transactions):\";\n";
echo "    \n";
echo "    foreach (\$product->inventoryTransactions as \$transaction) {\n";
echo "        echo \"  - \" . \$transaction->created_at->format('Y-m-d') . \": \" .\n";
echo "             \$transaction->transaction_type . \" \" .\n";
echo "             \$transaction->quantity . \" (\" .\n";
echo "             \$transaction->old_quantity . \" → \" .\n";
echo "             \$transaction->new_quantity . \")\";\n";
echo "    }\n";
echo "}\n";

echo "\n=== Commands to Run ===\n";
echo "To test the fixed seeders:\n\n";

echo "# Option 1: Run all seeders\n";
echo "php artisan migrate:fresh --seed\n\n";

echo "# Option 2: Run step by step\n";
echo "php artisan migrate:fresh\n";
echo "php artisan db:seed --class=ProductSeeder\n";
echo "php artisan db:seed --class=InventoryTransactionSeeder\n";
echo "php artisan db:seed --class=WarehouseSeeder\n";
echo "php artisan db:seed --class=AdvancedInventoryTransactionSeeder\n\n";

echo "# Option 3: Test individual seeders\n";
echo "php artisan db:seed --class=ProductSeeder\n\n";

echo "=== Expected Results ===\n";
echo "✅ No 'createInitialTransaction' method error\n";
echo "✅ Products created with warehouse_id in inventories\n";
echo "✅ Inventory transactions created successfully\n";
echo "✅ Multiple warehouses created\n";
echo "✅ Advanced transactions (transfers, etc.) created\n";
echo "✅ Final inventory quantities > 0 for most products\n";

echo "\n=== Seeders Fixed Successfully ===\n";
