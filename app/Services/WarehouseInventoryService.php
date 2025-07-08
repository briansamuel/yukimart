<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Inventory;
use App\Models\InventoryTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class WarehouseInventoryService
{
    /**
     * Nhập kho - Import inventory
     */
    public function importInventory($productId, $warehouseId, $quantity, $unitCost = null, $notes = null)
    {
        return DB::transaction(function () use ($productId, $warehouseId, $quantity, $unitCost, $notes) {
            // Lấy thông tin sản phẩm và kho
            $product = Product::findOrFail($productId);
            $warehouse = Warehouse::findOrFail($warehouseId);
            
            // Lấy số lượng hiện tại
            $currentQuantity = Inventory::getProductQuantityInWarehouse($productId, $warehouseId);
            $newQuantity = $currentQuantity + $quantity;
            
            // Cập nhật inventory
            Inventory::updateProductQuantity($productId, $newQuantity, $warehouseId);
            
            // Tạo transaction record
            $transaction = InventoryTransaction::create([
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'transaction_type' => InventoryTransaction::TYPE_IMPORT,
                'quantity' => $quantity,
                'old_quantity' => $currentQuantity,
                'new_quantity' => $newQuantity,
                'unit_cost' => $unitCost ?? $product->cost_price,
                'total_value' => $quantity * ($unitCost ?? $product->cost_price),
                'notes' => $notes ?? "Nhập kho {$quantity} sản phẩm {$product->product_name}",
                'created_by_user' => Auth::id(),
            ]);
            
            return [
                'success' => true,
                'transaction' => $transaction,
                'old_quantity' => $currentQuantity,
                'new_quantity' => $newQuantity,
                'message' => "Đã nhập {$quantity} sản phẩm vào kho {$warehouse->name}"
            ];
        });
    }

    /**
     * Xuất kho - Export inventory
     */
    public function exportInventory($productId, $warehouseId, $quantity, $notes = null)
    {
        return DB::transaction(function () use ($productId, $warehouseId, $quantity, $notes) {
            // Lấy thông tin sản phẩm và kho
            $product = Product::findOrFail($productId);
            $warehouse = Warehouse::findOrFail($warehouseId);
            
            // Lấy số lượng hiện tại
            $currentQuantity = Inventory::getProductQuantityInWarehouse($productId, $warehouseId);
            
            // Kiểm tra đủ hàng để xuất
            if ($currentQuantity < $quantity) {
                return [
                    'success' => false,
                    'message' => "Không đủ hàng để xuất. Tồn kho hiện tại: {$currentQuantity}, yêu cầu xuất: {$quantity}"
                ];
            }
            
            $newQuantity = $currentQuantity - $quantity;
            
            // Cập nhật inventory
            Inventory::updateProductQuantity($productId, $newQuantity, $warehouseId);
            
            // Tạo transaction record
            $transaction = InventoryTransaction::create([
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'transaction_type' => InventoryTransaction::TYPE_EXPORT,
                'quantity' => -$quantity, // Số âm cho xuất kho
                'old_quantity' => $currentQuantity,
                'new_quantity' => $newQuantity,
                'unit_cost' => $product->cost_price,
                'total_value' => $quantity * $product->cost_price,
                'notes' => $notes ?? "Xuất kho {$quantity} sản phẩm {$product->product_name}",
                'created_by_user' => Auth::id(),
            ]);
            
            return [
                'success' => true,
                'transaction' => $transaction,
                'old_quantity' => $currentQuantity,
                'new_quantity' => $newQuantity,
                'message' => "Đã xuất {$quantity} sản phẩm từ kho {$warehouse->name}"
            ];
        });
    }

    /**
     * Chuyển kho - Transfer between warehouses
     */
    public function transferInventory($productId, $fromWarehouseId, $toWarehouseId, $quantity, $notes = null)
    {
        return DB::transaction(function () use ($productId, $fromWarehouseId, $toWarehouseId, $quantity, $notes) {
            // Lấy thông tin
            $product = Product::findOrFail($productId);
            $fromWarehouse = Warehouse::findOrFail($fromWarehouseId);
            $toWarehouse = Warehouse::findOrFail($toWarehouseId);
            
            // Kiểm tra kho nguồn có đủ hàng
            $fromQuantity = Inventory::getProductQuantityInWarehouse($productId, $fromWarehouseId);
            if ($fromQuantity < $quantity) {
                return [
                    'success' => false,
                    'message' => "Kho {$fromWarehouse->name} không đủ hàng để chuyển. Tồn kho: {$fromQuantity}, yêu cầu: {$quantity}"
                ];
            }
            
            // Lấy số lượng kho đích
            $toQuantity = Inventory::getProductQuantityInWarehouse($productId, $toWarehouseId);
            
            // Cập nhật kho nguồn (giảm)
            $newFromQuantity = $fromQuantity - $quantity;
            Inventory::updateProductQuantity($productId, $newFromQuantity, $fromWarehouseId);
            
            // Cập nhật kho đích (tăng)
            $newToQuantity = $toQuantity + $quantity;
            Inventory::updateProductQuantity($productId, $newToQuantity, $toWarehouseId);
            
            // Tạo transaction cho kho nguồn (xuất)
            $fromTransaction = InventoryTransaction::create([
                'product_id' => $productId,
                'warehouse_id' => $fromWarehouseId,
                'transaction_type' => InventoryTransaction::TYPE_TRANSFER,
                'quantity' => -$quantity,
                'old_quantity' => $fromQuantity,
                'new_quantity' => $newFromQuantity,
                'unit_cost' => $product->cost_price,
                'total_value' => $quantity * $product->cost_price,
                'notes' => $notes ?? "Chuyển {$quantity} sản phẩm {$product->product_name} đến kho {$toWarehouse->name}",
                'created_by_user' => Auth::id(),
            ]);
            
            // Tạo transaction cho kho đích (nhập)
            $toTransaction = InventoryTransaction::create([
                'product_id' => $productId,
                'warehouse_id' => $toWarehouseId,
                'transaction_type' => InventoryTransaction::TYPE_TRANSFER,
                'quantity' => $quantity,
                'old_quantity' => $toQuantity,
                'new_quantity' => $newToQuantity,
                'unit_cost' => $product->cost_price,
                'total_value' => $quantity * $product->cost_price,
                'notes' => $notes ?? "Nhận {$quantity} sản phẩm {$product->product_name} từ kho {$fromWarehouse->name}",
                'created_by_user' => Auth::id(),
            ]);
            
            return [
                'success' => true,
                'from_transaction' => $fromTransaction,
                'to_transaction' => $toTransaction,
                'message' => "Đã chuyển {$quantity} sản phẩm từ kho {$fromWarehouse->name} đến kho {$toWarehouse->name}"
            ];
        });
    }

    /**
     * Điều chỉnh tồn kho - Adjust inventory
     */
    public function adjustInventory($productId, $warehouseId, $newQuantity, $reason = null)
    {
        return DB::transaction(function () use ($productId, $warehouseId, $newQuantity, $reason) {
            // Lấy thông tin
            $product = Product::findOrFail($productId);
            $warehouse = Warehouse::findOrFail($warehouseId);
            
            // Lấy số lượng hiện tại
            $currentQuantity = Inventory::getProductQuantityInWarehouse($productId, $warehouseId);
            $quantityChange = $newQuantity - $currentQuantity;
            
            if ($quantityChange == 0) {
                return [
                    'success' => false,
                    'message' => 'Số lượng mới giống số lượng hiện tại, không cần điều chỉnh'
                ];
            }
            
            // Cập nhật inventory
            Inventory::updateProductQuantity($productId, $newQuantity, $warehouseId);
            
            // Tạo transaction record
            $transaction = InventoryTransaction::create([
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'transaction_type' => InventoryTransaction::TYPE_ADJUSTMENT,
                'quantity' => $quantityChange,
                'old_quantity' => $currentQuantity,
                'new_quantity' => $newQuantity,
                'unit_cost' => $product->cost_price,
                'total_value' => abs($quantityChange) * $product->cost_price,
                'notes' => $reason ?? "Điều chỉnh tồn kho từ {$currentQuantity} thành {$newQuantity}",
                'created_by_user' => Auth::id(),
            ]);
            
            $action = $quantityChange > 0 ? 'tăng' : 'giảm';
            
            return [
                'success' => true,
                'transaction' => $transaction,
                'old_quantity' => $currentQuantity,
                'new_quantity' => $newQuantity,
                'quantity' => $quantityChange,
                'message' => "Đã điều chỉnh tồn kho {$action} " . abs($quantityChange) . " sản phẩm trong kho {$warehouse->name}"
            ];
        });
    }

    /**
     * Lấy lịch sử giao dịch
     */
    public function getTransactionHistory($productId = null, $warehouseId = null, $limit = 50)
    {
        $query = InventoryTransaction::with(['product', 'warehouse', 'creator'])
            ->orderBy('created_at', 'desc');
            
        if ($productId) {
            $query->forProduct($productId);
        }
        
        if ($warehouseId) {
            $query->forWarehouse($warehouseId);
        }
        
        return $query->limit($limit)->get();
    }

    /**
     * Lấy báo cáo tồn kho theo kho
     */
    public function getInventoryReport($warehouseId = null)
    {
        $query = Inventory::with(['product', 'warehouse']);
        
        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }
        
        return $query->orderBy('quantity', 'desc')->get();
    }

    /**
     * Lấy sản phẩm sắp hết hàng
     */
    public function getLowStockProducts($warehouseId = null)
    {
        $query = Inventory::join('products', 'inventories.product_id', '=', 'products.id')
            ->whereRaw('inventories.quantity <= products.reorder_point')
            ->where('products.product_status', 'publish');
            
        if ($warehouseId) {
            $query->where('inventories.warehouse_id', $warehouseId);
        }
        
        return $query->select('products.*', 'inventories.quantity as stock_quantity', 'inventories.warehouse_id')
            ->with('warehouse')
            ->get();
    }
}
