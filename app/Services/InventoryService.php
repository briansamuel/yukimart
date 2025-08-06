<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\Order;
use App\Models\Invoice;
use App\Models\BranchShop;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class InventoryService
{
    /**
     * Process inventory transaction
     */
    public function processTransaction($productId, $type, $quantity, $options = [])
    {
        DB::beginTransaction();
        
        try {
            $product = Product::findOrFail($productId);
            
            if (!$product->track_inventory && !in_array($type, [InventoryTransaction::TYPE_INITIAL, InventoryTransaction::TYPE_ADJUSTMENT])) {
                DB::rollBack();
                return ['success' => false, 'message' => 'Inventory tracking is disabled for this product'];
            }

            $result = match($type) {
                InventoryTransaction::TYPE_IMPORT => $this->processPurchase($product, $quantity, $options),
                InventoryTransaction::TYPE_EXPORT => $this->processSale($product, $quantity, $options),
                InventoryTransaction::TYPE_ADJUSTMENT => $this->processAdjustment($product, $quantity, $options),
                InventoryTransaction::TYPE_TRANSFER => $this->processTransfer($product, $quantity, $options),
                InventoryTransaction::TYPE_INITIAL => $this->processInitialStock($product, $quantity, $options),
                default => ['success' => false, 'message' => 'Invalid transaction type']
            };

            if ($result['success']) {
                DB::commit();
                Log::info("Inventory transaction processed", [
                    'product_id' => $productId,
                    'type' => $type,
                    'quantity' => $quantity,
                    'user_id' => auth()->id()
                ]);
            } else {
                DB::rollBack();
            }

            return $result;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Inventory transaction failed", [
                'product_id' => $productId,
                'type' => $type,
                'quantity' => $quantity,
                'error' => $e->getMessage()
            ]);
            
            return ['success' => false, 'message' => 'Transaction failed: ' . $e->getMessage()];
        }
    }

    /**
     * Process purchase transaction
     */
    protected function processPurchase($product, $quantity, $options)
    {
        $unitCost = $options['unit_cost'] ?? $product->cost_price;
        $reference = $options['reference'] ?? null;
        $notes = $options['notes'] ?? "Purchase of {$quantity} units";

        $product->addStock($quantity, InventoryTransaction::TYPE_IMPORT, $unitCost, $reference, $notes);
        
        return ['success' => true, 'message' => "Added {$quantity} units to inventory"];
    }

    /**
     * Process sale transaction
     */
    protected function processSale($product, $quantity, $options)
    {
        if (!$product->canOrder($quantity)) {
            return ['success' => false, 'message' => 'Insufficient stock for sale'];
        }

        $reference = $options['reference'] ?? null;
        $notes = $options['notes'] ?? "Sale of {$quantity} units";

        $product->removeStock($quantity, InventoryTransaction::TYPE_EXPORT, $reference, $notes);
        
        return ['success' => true, 'message' => "Sold {$quantity} units"];
    }

    /**
     * Process adjustment transaction
     */
    protected function processAdjustment($product, $newQuantity, $options)
    {
        $reason = $options['reason'] ?? 'Manual adjustment';
        $reference = $options['reference'] ?? null;

        $product->adjustStock($newQuantity, $reason, $reference);
        
        return ['success' => true, 'message' => "Stock adjusted to {$newQuantity} units"];
    }

    /**
     * Process return transaction
     */
    protected function processReturn($product, $quantity, $options)
    {
        $reference = $options['reference'] ?? null;
        $notes = $options['notes'] ?? "Return of {$quantity} units";

        $product->addStock($quantity, InventoryTransaction::TYPE_IMPORT, null, $reference, $notes);
        
        return ['success' => true, 'message' => "Returned {$quantity} units to inventory"];
    }

    /**
     * Process damage transaction
     */
    protected function processDamage($product, $quantity, $options)
    {
        $reference = $options['reference'] ?? null;
        $notes = $options['notes'] ?? "Damaged {$quantity} units";

        $product->removeStock($quantity, InventoryTransaction::TYPE_EXPORT, $reference, $notes);
        
        return ['success' => true, 'message' => "Removed {$quantity} damaged units"];
    }

    /**
     * Process transfer transaction
     */
    protected function processTransfer($product, $quantity, $options)
    {
        $locationFrom = $options['location_from'] ?? $product->location;
        $locationTo = $options['location_to'] ?? null;
        $reference = $options['reference'] ?? null;
        $notes = $options['notes'] ?? "Transfer of {$quantity} units from {$locationFrom} to {$locationTo}";

        // For now, just create a transaction record
        // In a full implementation, this might involve multiple locations
        $product->createInventoryTransaction([
            'transaction_type' => InventoryTransaction::TYPE_TRANSFER,
            'quantity' => 0, // No net change in total stock
            'reference' => $reference,
            'notes' => $notes,
            'location_from' => $locationFrom,
            'location_to' => $locationTo
        ]);
        
        return ['success' => true, 'message' => "Transferred {$quantity} units"];
    }

    /**
     * Process initial stock transaction
     */
    protected function processInitialStock($product, $quantity, $options)
    {
        $unitCost = $options['unit_cost'] ?? $product->cost_price;
        $notes = $options['notes'] ?? "Initial stock entry";

        // Update inventory through the new system
        Inventory::updateOrCreate(
            ['product_id' => $product->id, 'warehouse_id' => 1],
            ['quantity' => $quantity]
        );
        
        $product->createInventoryTransaction([
            'transaction_type' => InventoryTransaction::TYPE_INITIAL,
            'quantity' => $quantity,
            'unit_cost' => $unitCost,
            'notes' => $notes
        ]);
        
        return ['success' => true, 'message' => "Set initial stock to {$quantity} units"];
    }

    /**
     * Get low stock products
     */
    public function getLowStockProducts()
    {
        return Product::join('inventories', 'products.id', '=', 'inventories.product_id')
            ->whereRaw('inventories.quantity <= products.reorder_point')
            ->where('products.product_status', 'publish')
            ->select('products.*', 'inventories.quantity as stock_quantity')
            ->get();
    }

    /**
     * Get out of stock products
     */
    public function getOutOfStockProducts()
    {
        return Product::join('inventories', 'products.id', '=', 'inventories.product_id')
            ->where('inventories.quantity', '<=', 0)
            ->where('products.product_status', 'publish')
            ->select('products.*', 'inventories.quantity as stock_quantity')
            ->get();
    }

    /**
     * Generate inventory report
     */
    public function generateInventoryReport($filters = [])
    {
        $query = Product::join('inventories', 'products.id', '=', 'inventories.product_id');

        if (isset($filters['status'])) {
            $query->where('products.product_status', $filters['status']);
        }

        if (isset($filters['low_stock']) && $filters['low_stock']) {
            $query->whereRaw('inventories.quantity <= products.reorder_point');
        }

        if (isset($filters['out_of_stock']) && $filters['out_of_stock']) {
            $query->where('inventories.quantity', '<=', 0);
        }

        $products = $query->select('products.*', 'inventories.quantity as stock_quantity')->get();

        return [
            'total_products' => $products->count(),
            'total_value' => $products->sum(function($product) {
                return ($product->stock_quantity ?? 0) * $product->cost_price;
            }),
            'low_stock_count' => $products->filter(fn($p) => ($p->stock_quantity ?? 0) <= $p->reorder_point)->count(),
            'out_of_stock_count' => $products->filter(fn($p) => ($p->stock_quantity ?? 0) <= 0)->count(),
            'products' => $products
        ];
    }

    /**
     * Check all products for stock alerts
     */
    public function checkAllStockAlerts()
    {
        $products = Product::all();
        $alertsCreated = 0;

        foreach ($products as $product) {
            $product->checkStockAlerts();
            $alertsCreated++;
        }

        return $alertsCreated;
    }



    /**
     * Get inventory statistics
     */
    public function getInventoryStatistics()
    {
        $totalProducts = Product::count();

        $inventoryData = Product::join('inventories', 'products.id', '=', 'inventories.product_id')
            ->select('products.*', 'inventories.quantity as stock_quantity')
            ->get();

        $totalValue = $inventoryData->sum(function($product) {
            return ($product->stock_quantity ?? 0) * $product->cost_price;
        });

        $lowStockCount = $inventoryData->filter(fn($p) => ($p->stock_quantity ?? 0) <= $p->reorder_point)->count();
        $outOfStockCount = $inventoryData->filter(fn($p) => ($p->stock_quantity ?? 0) <= 0)->count();

        return [
            'total_products' => $totalProducts,
            'total_value' => $totalValue,
            'low_stock_count' => $lowStockCount,
            'out_of_stock_count' => $outOfStockCount,
            'stock_health' => $totalProducts > 0 ?
                round((($totalProducts - $lowStockCount - $outOfStockCount) / $totalProducts) * 100, 1) : 100
        ];
    }

    /**
     * Process import transaction
     */
    public function processImport($params)
    {
        DB::beginTransaction();
        try {
            // Validate input parameters
            if (!isset($params['products'])) {
                throw new \Exception('Products data is required');
            }

            $products = json_decode($params['products'], true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON format for products data: ' . json_last_error_msg());
            }

            if (!is_array($products) || empty($products)) {
                throw new \Exception('Products data must be a non-empty array');
            }
            $results = [];

            foreach ($products as $productData) {
                // Validate required fields
                if (!isset($productData['id'])) {
                    throw new \Exception('Product ID is required');
                }
                if (!isset($productData['quantity'])) {
                    throw new \Exception('Quantity is required for product ID: ' . $productData['id']);
                }
                if (!isset($productData['unit_cost'])) {
                    throw new \Exception('Unit cost is required for product ID: ' . $productData['id']);
                }

                $product = Product::findOrFail($productData['id']);
                $quantity = (int) $productData['quantity'];
                $unitCost = (float) $productData['unit_cost'];

                // Validate quantity
                if ($quantity <= 0) {
                    throw new \Exception('Quantity must be greater than 0 for product: ' . $product->product_name);
                }

                // Get current stock for old_quantity and new_quantity
                $currentStock = $this->getCurrentStock($product->id);
                $newStock = $currentStock + $quantity;

                // Create transaction
                $transaction = InventoryTransaction::create([
                    'product_id' => $product->id,
                    'supplier_id' => $params['supplier_id'] ?? null,
                    'warehouse_id' => $params['warehouse_id'] ?? 1,
                    'transaction_type' => 'import',
                    'old_quantity' => $currentStock,
                    'quantity' => $quantity, // This is the change amount
                    'new_quantity' => $newStock,
                    'unit_cost' => $unitCost,
                    'total_value' => $quantity * $unitCost,
                    'reference_type' => 'manual_import',
                    'reference_id' => null,
                    'notes' => $params['notes'] ?? 'Manual import - ' . ($params['reference_number'] ?? 'PN' . date('YmdHis')),
                    'created_by_user' => auth()->id()
                ]);

                // Update inventory
                $this->updateInventory($product->id, $quantity, 'import');

                $results[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->product_name,
                    'quantity' => $quantity,
                    'transaction_id' => $transaction->id
                ];
            }

            DB::commit();
            return [
                'success' => true,
                'message' => 'Import processed successfully',
                'data' => $results
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Import processing failed: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Import processing failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Process export transaction
     */
    public function processExport($params)
    {
        DB::beginTransaction();
        try {
            // Validate input parameters
            if (!isset($params['products'])) {
                throw new \Exception('Products data is required');
            }

            $products = json_decode($params['products'], true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON format for products data: ' . json_last_error_msg());
            }

            if (!is_array($products) || empty($products)) {
                throw new \Exception('Products data must be a non-empty array');
            }
            $results = [];

            foreach ($products as $productData) {
                // Validate required fields
                if (!isset($productData['id'])) {
                    throw new \Exception('Product ID is required');
                }
                if (!isset($productData['quantity'])) {
                    throw new \Exception('Quantity is required for product ID: ' . $productData['id']);
                }
                if (!isset($productData['unit_price'])) {
                    throw new \Exception('Unit price is required for product ID: ' . $productData['id']);
                }

                $product = Product::findOrFail($productData['id']);
                $quantity = (int) $productData['quantity'];
                $unitPrice = (float) $productData['unit_price'];

                // Validate quantity
                if ($quantity <= 0) {
                    throw new \Exception('Quantity must be greater than 0 for product: ' . $product->product_name);
                }

                // Check stock availability
                $currentStock = $this->getCurrentStock($product->id);
                if ($currentStock < $quantity) {
                    throw new \Exception("Insufficient stock for product: {$product->product_name}");
                }

                // Get current stock for old_quantity and new_quantity
                $currentStock = $this->getCurrentStock($product->id);
                $newStock = $currentStock - $quantity;

                // Create transaction
                $transaction = InventoryTransaction::create([
                    'product_id' => $product->id,
                    'warehouse_id' => $params['warehouse_id'] ?? 1,
                    'transaction_type' => 'export',
                    'old_quantity' => $currentStock,
                    'quantity' => -$quantity, // Negative for export
                    'new_quantity' => $newStock,
                    'unit_cost' => $unitPrice,
                    'total_value' => $quantity * $unitPrice,
                    'reference_type' => 'manual_export',
                    'reference_id' => null,
                    'notes' => $params['notes'] ?? 'Manual export - ' . ($params['reference_number'] ?? 'PX' . date('YmdHis')),
                    'created_by_user' => auth()->id()
                ]);

                // Update inventory
                $this->updateInventory($product->id, -$quantity, 'export');

                $results[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->product_name,
                    'quantity' => $quantity,
                    'transaction_id' => $transaction->id
                ];
            }

            DB::commit();
            return [
                'success' => true,
                'message' => 'Export processed successfully',
                'data' => $results
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Export processing failed: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Export processing failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Process bulk adjustment transaction
     */
    public function processBulkAdjustment($params)
    {
        DB::beginTransaction();
        try {
            // Validate input parameters
            if (!isset($params['products'])) {
                throw new \Exception('Products data is required');
            }

            $products = json_decode($params['products'], true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON format for products data: ' . json_last_error_msg());
            }

            if (!is_array($products) || empty($products)) {
                throw new \Exception('Products data must be a non-empty array');
            }
            $results = [];

            foreach ($products as $productData) {
                // Validate required fields
                if (!isset($productData['id'])) {
                    throw new \Exception('Product ID is required');
                }
                if (!isset($productData['current_stock'])) {
                    throw new \Exception('Current stock is required for product ID: ' . $productData['id']);
                }
                if (!isset($productData['actual_stock'])) {
                    throw new \Exception('Actual stock is required for product ID: ' . $productData['id']);
                }

                $product = Product::findOrFail($productData['id']);
                $currentStock = (int) $productData['current_stock'];
                $actualStock = (int) $productData['actual_stock'];
                $difference = $actualStock - $currentStock;
                $reason = $productData['reason'] ?? 'Stock adjustment';

                if ($difference != 0) {
                    // Create transaction
                    $transaction = InventoryTransaction::create([
                        'product_id' => $product->id,
                        'warehouse_id' => $params['warehouse_id'] ?? 1,
                        'transaction_type' => 'adjustment',
                        'old_quantity' => $currentStock,
                        'quantity' => $difference,
                        'new_quantity' => $actualStock,
                        'unit_cost' => null,
                        'total_value' => null,
                        'reference_type' => 'manual_adjustment',
                        'reference_id' => null,
                        'notes' => ($params['reason'] ?? 'Stock adjustment') . ' - ' . $reason,
                        'created_by_user' => auth()->id()
                    ]);

                    // Update inventory
                    $this->updateInventory($product->id, $difference, 'adjustment');

                    $results[] = [
                        'product_id' => $product->id,
                        'product_name' => $product->product_name,
                        'difference' => $difference,
                        'transaction_id' => $transaction->id
                    ];
                }
            }

            DB::commit();
            return [
                'success' => true,
                'message' => 'Adjustment processed successfully',
                'data' => $results
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Adjustment processing failed: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Adjustment processing failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Export transactions to Excel
     */
    public function exportTransactions($filters)
    {
        // This is a placeholder - in real implementation, you would use Laravel Excel
        return response()->json([
            'success' => false,
            'message' => 'Excel export not implemented yet'
        ]);
    }

    /**
     * Update inventory quantity
     */
    private function updateInventory($productId, $quantityChange, $type)
    {
        $inventory = Inventory::firstOrCreate(
            ['product_id' => $productId, 'warehouse_id' => 1],
            ['quantity' => 0, 'reserved_quantity' => 0]
        );

        $inventory->quantity += $quantityChange;
        $inventory->save();

        return $inventory;
    }

    /**
     * Get current stock for a product
     */
    private function getCurrentStock($productId)
    {
        $inventory = Inventory::where('product_id', $productId)
            ->where('warehouse_id', 1)
            ->first();

        return $inventory ? $inventory->quantity : 0;
    }

    /**
     * Update inventory when order status changes.
     */
    public function updateInventoryForOrder(Order $order, $oldStatus = null)
    {
        try {
            DB::beginTransaction();

            // Only update inventory for confirmed, processing, completed orders
            $inventoryUpdateStatuses = ['confirmed', 'processing', 'completed'];
            $shouldUpdateInventory = in_array($order->status, $inventoryUpdateStatuses);

            // If old status was draft/pending and new status requires inventory update
            if ($oldStatus && in_array($oldStatus, ['draft', 'pending']) && $shouldUpdateInventory) {
                // Reduce inventory (first time)
                $this->reduceInventoryForOrder($order);
            }
            // If old status required inventory update and new status doesn't
            elseif ($oldStatus && in_array($oldStatus, $inventoryUpdateStatuses) && !$shouldUpdateInventory) {
                // Restore inventory
                $this->restoreInventoryForOrder($order);
            }

            DB::commit();
            return ['success' => true];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to update inventory for order', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Lỗi cập nhật tồn kho: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update inventory when invoice is created.
     */
    public function updateInventoryForInvoice(Invoice $invoice)
    {
        try {
            DB::beginTransaction();

            // Always reduce inventory for invoices (immediate sale)
            $warnings = $this->reduceInventoryForInvoice($invoice);

            DB::commit();

            $result = ['success' => true];
            if (!empty($warnings)) {
                $result['warnings'] = $warnings;
            }

            return $result;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to update inventory for invoice', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Lỗi cập nhật tồn kho: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Reduce inventory for order items.
     */
    private function reduceInventoryForOrder(Order $order)
    {
        $warehouseId = $this->getWarehouseForBranchShop($order->branchShop);

        foreach ($order->orderItems as $item) {
            $this->reduceProductInventory(
                $item->product_id,
                $item->quantity,
                $warehouseId,
                'order',
                $order->id,
                "Bán hàng - Đơn hàng #{$order->order_code}"
            );
        }
    }

    /**
     * Reduce inventory for invoice items.
     */
    private function reduceInventoryForInvoice(Invoice $invoice)
    {
        $warehouseId = $this->getWarehouseForBranchShop($invoice->branchShop);
        $warnings = [];

        foreach ($invoice->invoiceItems as $item) {
            $warning = $this->reduceProductInventory(
                $item->product_id,
                $item->quantity,
                $warehouseId,
                'invoice',
                $invoice->id,
                "Bán hàng - Hóa đơn #{$invoice->invoice_number}"
            );

            if ($warning) {
                $warnings[] = $warning;
            }
        }

        return $warnings;
    }

    /**
     * Restore inventory for order items.
     */
    private function restoreInventoryForOrder(Order $order)
    {
        $warehouseId = $this->getWarehouseForBranchShop($order->branchShop);

        foreach ($order->orderItems as $item) {
            $this->increaseProductInventory(
                $item->product_id,
                $item->quantity,
                $warehouseId,
                'order_cancel',
                $order->id,
                "Hủy đơn hàng - Đơn hàng #{$order->order_code}"
            );
        }
    }

    /**
     * Reduce product inventory with atomic operation to prevent race conditions.
     */
    private function reduceProductInventory($productId, $quantity, $warehouseId, $type, $referenceId, $notes)
    {
        // Find or create inventory record with lock to prevent race conditions
        $inventory = Inventory::where('product_id', $productId)
                             ->where('warehouse_id', $warehouseId)
                             ->lockForUpdate()
                             ->first();

        if (!$inventory) {
            // Create new inventory record if not exists
            $inventory = Inventory::create([
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'quantity' => 0,
            ]);
        }

        $warning = null;

        // Check if enough stock available - allow negative inventory but return warning
        if ($inventory->quantity < $quantity) {
            // Get product name for user-friendly warning
            $product = \App\Models\Product::find($productId);
            $productName = $product ? $product->product_name : "Sản phẩm ID: {$productId}";

            $warning = [
                'product_id' => $productId,
                'product_name' => $productName,
                'current_quantity' => $inventory->quantity,
                'requested_quantity' => $quantity,
                'shortage' => $quantity - $inventory->quantity,
                'message' => "Sản phẩm '{$productName}' không đủ tồn kho. Hiện có: {$inventory->quantity}, yêu cầu: {$quantity}"
            ];

            Log::warning("Tồn kho âm cho sản phẩm ID: {$productId}", [
                'product_id' => $productId,
                'product_name' => $productName,
                'current_quantity' => $inventory->quantity,
                'requested_quantity' => $quantity,
                'warehouse_id' => $warehouseId,
                'type' => $type,
                'reference_id' => $referenceId
            ]);
        }

        // Update inventory atomically
        $inventory->quantity -= $quantity;
        $inventory->save();

        // Get old quantity for transaction record
        $oldQuantity = $inventory->quantity + $quantity; // Before reduction
        $newQuantity = $inventory->quantity; // After reduction

        // Create transaction record
        InventoryTransaction::create([
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
            'transaction_type' => 'sale', // Use 'sale' instead of 'out'
            'old_quantity' => $oldQuantity,
            'quantity' => $quantity,
            'new_quantity' => $newQuantity,
            'reference_type' => $type,
            'reference_id' => $referenceId,
            'notes' => $notes,
            'created_by_user' => auth()->id(),
        ]);

        return $warning;
    }

    /**
     * Increase product inventory.
     */
    private function increaseProductInventory($productId, $quantity, $warehouseId, $type, $referenceId, $notes)
    {
        // Find inventory record
        $inventory = Inventory::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->first();

        if ($inventory) {
            // Update inventory
            $inventory->quantity += $quantity;
            $inventory->save();

            // Get old quantity for transaction record
            $oldQuantity = $inventory->quantity - $quantity; // Before increase
            $newQuantity = $inventory->quantity; // After increase

            // Create transaction record
            InventoryTransaction::create([
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'transaction_type' => 'adjustment', // Use 'adjustment' for returns/cancellations
                'old_quantity' => $oldQuantity,
                'quantity' => $quantity,
                'new_quantity' => $newQuantity,
                'reference_type' => $type,
                'reference_id' => $referenceId,
                'notes' => $notes,
                'created_by_user' => auth()->id(),
            ]);
        }
    }

    /**
     * Get warehouse ID for branch shop, fallback to default warehouse.
     */
    private function getWarehouseForBranchShop($branchShop)
    {
        // If branch shop has warehouse assigned, use it
        if ($branchShop && $branchShop->warehouse_id) {
            Log::info('Using branch shop warehouse', [
                'branch_shop_id' => $branchShop->id,
                'warehouse_id' => $branchShop->warehouse_id
            ]);
            return $branchShop->warehouse_id;
        }

        // Fallback to default warehouse
        $defaultWarehouse = Warehouse::where('is_default', true)->first();
        if ($defaultWarehouse) {
            Log::info('Using default warehouse', [
                'branch_shop_id' => $branchShop ? $branchShop->id : null,
                'warehouse_id' => $defaultWarehouse->id
            ]);
            return $defaultWarehouse->id;
        }

        // If no default warehouse, use the first available warehouse
        $firstWarehouse = Warehouse::where('status', 'active')->first();
        if ($firstWarehouse) {
            Log::info('Using first available warehouse', [
                'branch_shop_id' => $branchShop ? $branchShop->id : null,
                'warehouse_id' => $firstWarehouse->id
            ]);
            return $firstWarehouse->id;
        }

        // If no warehouse found, throw exception
        Log::error('No warehouse found in system', [
            'branch_shop_id' => $branchShop ? $branchShop->id : null
        ]);
        throw new Exception('Không tìm thấy kho hàng nào trong hệ thống');
    }
}
