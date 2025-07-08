<?php

// Test script to demonstrate inventory factories usage
// Run with: php artisan tinker < test_inventory_factories.php

echo "=== Inventory Factories Test ===\n\n";

// Test InventoryFactory with default quantity = 0
echo "1. Testing InventoryFactory (default quantity = 0):\n";
echo "Inventory::factory()->make()->toArray();\n";

// Test InventoryFactory with different states
echo "\n2. Testing InventoryFactory states:\n";
echo "Inventory::factory()->inStock()->make()->quantity; // 50-500\n";
echo "Inventory::factory()->lowStock()->make()->quantity; // 1-10\n";
echo "Inventory::factory()->outOfStock()->make()->quantity; // 0\n";
echo "Inventory::factory()->quantity(100)->make()->quantity; // 100\n";

// Test InventoryTransactionFactory basic usage
echo "\n3. Testing InventoryTransactionFactory:\n";
echo "InventoryTransaction::factory()->make()->toArray();\n";

// Test transaction type states
echo "\n4. Testing transaction type states:\n";
echo "InventoryTransaction::factory()->purchase()->make()->transaction_type; // 'purchase'\n";
echo "InventoryTransaction::factory()->sale()->make()->transaction_type; // 'sale'\n";
echo "InventoryTransaction::factory()->adjustment()->make()->transaction_type; // 'adjustment'\n";
echo "InventoryTransaction::factory()->initial()->make()->transaction_type; // 'initial'\n";

// Test customization methods
echo "\n5. Testing customization methods:\n";
echo "InventoryTransaction::factory()->quantityChange(50)->make()->quantity_change; // 50\n";
echo "InventoryTransaction::factory()->unitCost(100000)->make()->unit_cost; // 100000\n";
echo "InventoryTransaction::factory()->withNotes('Test note')->make()->notes; // 'Test note'\n";

// Test realistic scenarios
echo "\n6. Realistic usage scenarios:\n";
echo "\n// Create product with zero inventory:\n";
echo "\$product = Product::factory()->create();\n";
echo "\$inventory = Inventory::factory()->forProduct(\$product)->create();\n";
echo "echo \$inventory->quantity; // 0\n";

echo "\n// Add initial stock:\n";
echo "\$initial = InventoryTransaction::factory()\n";
echo "    ->initial()\n";
echo "    ->forProduct(\$product)\n";
echo "    ->quantityChange(100)\n";
echo "    ->create();\n";

echo "\n// Create sale transaction:\n";
echo "\$sale = InventoryTransaction::factory()\n";
echo "    ->sale()\n";
echo "    ->forProduct(\$product)\n";
echo "    ->quantityChange(-5)\n";
echo "    ->withNotes('Customer order #12345')\n";
echo "    ->create();\n";

echo "\n// Create return transaction:\n";
echo "\$return = InventoryTransaction::factory()\n";
echo "    ->return()\n";
echo "    ->forProduct(\$product)\n";
echo "    ->quantityChange(2)\n";
echo "    ->withNotes('Customer return - wrong size')\n";
echo "    ->create();\n";

echo "\n=== Factory Tests Complete ===\n";
echo "\nTo run these tests interactively:\n";
echo "php artisan tinker\n";
echo "Then copy and paste the commands above.\n";
