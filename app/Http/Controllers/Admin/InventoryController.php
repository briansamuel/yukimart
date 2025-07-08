<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Services\WarehouseInventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InventoryController extends Controller
{
    protected $inventoryService;

    public function __construct(WarehouseInventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Hiển thị danh sách tồn kho
     */
    public function index(Request $request)
    {
        $warehouseId = $request->get('warehouse_id');
        $warehouses = Warehouse::active()->get();
        
        $inventories = Inventory::with(['product', 'warehouse'])
            ->when($warehouseId, function($query) use ($warehouseId) {
                return $query->where('warehouse_id', $warehouseId);
            })
            ->orderBy('quantity', 'desc')
            ->paginate(20);

        return view('admin.inventory.index', compact('inventories', 'warehouses', 'warehouseId'));
    }

    /**
     * Hiển thị form nhập kho
     */
    public function importForm()
    {
        $products = Product::where('product_status', 'publish')->get();
        $warehouses = Warehouse::active()->get();
        
        return view('admin.inventory.import', compact('products', 'warehouses'));
    }

    /**
     * Xử lý nhập kho
     */
    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|integer|min:1',
            'unit_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->inventoryService->importInventory(
            $request->product_id,
            $request->warehouse_id,
            $request->quantity,
            $request->unit_cost,
            $request->notes
        );

        return response()->json($result);
    }

    /**
     * Hiển thị form xuất kho
     */
    public function exportForm()
    {
        $products = Product::where('product_status', 'publish')->get();
        $warehouses = Warehouse::active()->get();
        
        return view('admin.inventory.export', compact('products', 'warehouses'));
    }

    /**
     * Xử lý xuất kho
     */
    public function export(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->inventoryService->exportInventory(
            $request->product_id,
            $request->warehouse_id,
            $request->quantity,
            $request->notes
        );

        return response()->json($result);
    }

    /**
     * Hiển thị form chuyển kho
     */
    public function transferForm()
    {
        $products = Product::where('product_status', 'publish')->get();
        $warehouses = Warehouse::active()->get();
        
        return view('admin.inventory.transfer', compact('products', 'warehouses'));
    }

    /**
     * Xử lý chuyển kho
     */
    public function transfer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'to_warehouse_id' => 'required|exists:warehouses,id|different:from_warehouse_id',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->inventoryService->transferInventory(
            $request->product_id,
            $request->from_warehouse_id,
            $request->to_warehouse_id,
            $request->quantity,
            $request->notes
        );

        return response()->json($result);
    }

    /**
     * Hiển thị form điều chỉnh tồn kho
     */
    public function adjustForm()
    {
        $products = Product::where('product_status', 'publish')->get();
        $warehouses = Warehouse::active()->get();
        
        return view('admin.inventory.adjust', compact('products', 'warehouses'));
    }

    /**
     * Xử lý điều chỉnh tồn kho
     */
    public function adjust(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'new_quantity' => 'required|integer|min:0',
            'reason' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->inventoryService->adjustInventory(
            $request->product_id,
            $request->warehouse_id,
            $request->new_quantity,
            $request->reason
        );

        return response()->json($result);
    }

    /**
     * Lấy số lượng tồn kho của sản phẩm trong kho
     */
    public function getProductStock(Request $request)
    {
        $productId = $request->get('product_id');
        $warehouseId = $request->get('warehouse_id');

        if (!$productId || !$warehouseId) {
            return response()->json([
                'success' => false,
                'message' => 'Thiếu thông tin sản phẩm hoặc kho'
            ], 400);
        }

        $quantity = Inventory::getProductQuantityInWarehouse($productId, $warehouseId);
        $product = Product::find($productId);

        return response()->json([
            'success' => true,
            'quantity' => $quantity,
            'product_name' => $product ? $product->product_name : '',
            'cost_price' => $product ? $product->cost_price : 0
        ]);
    }

    /**
     * Hiển thị lịch sử giao dịch
     */
    public function transactions(Request $request)
    {
        $warehouses = Warehouse::active()->get();
        return view('admin.inventory.transactions', compact('warehouses'));
    }

    /**
     * Get transactions data for DataTables (AJAX)
     */
    public function getTransactionsAjax(Request $request)
    {
        try {
            $params = $request->all();

            // DataTables parameters
            $draw = $params['draw'] ?? 1;
            $start = $params['start'] ?? 0;
            $length = $params['length'] ?? 10;
            $searchValue = $params['search']['value'] ?? '';

            // Build query
            $query = InventoryTransaction::with(['product', 'warehouse', 'supplier', 'creator']);

            // Apply search
            if (!empty($searchValue)) {
                $query->where(function($q) use ($searchValue) {
                    $q->whereHas('product', function($productQuery) use ($searchValue) {
                        $productQuery->where('product_name', 'like', "%{$searchValue}%")
                                   ->orWhere('sku', 'like', "%{$searchValue}%");
                    })
                    ->orWhere('notes', 'like', "%{$searchValue}%")
                    ->orWhere('reference_type', 'like', "%{$searchValue}%");
                });
            }

            // Apply filters
            if (!empty($params['transaction_type'])) {
                $query->where('transaction_type', $params['transaction_type']);
            }

            if (!empty($params['warehouse_id'])) {
                $query->where('warehouse_id', $params['warehouse_id']);
            }

            if (!empty($params['supplier_id'])) {
                $query->where('supplier_id', $params['supplier_id']);
            }

            if (!empty($params['date_from'])) {
                $query->whereDate('created_at', '>=', $params['date_from']);
            }

            if (!empty($params['date_to'])) {
                $query->whereDate('created_at', '<=', $params['date_to']);
            }

            // Get total count before pagination
            $totalRecords = $query->count();

            // Apply pagination and ordering
            $transactions = $query->skip($start)
                                 ->take($length)
                                 ->orderBy('created_at', 'desc')
                                 ->get();

            // Format data for DataTables
            $data = $transactions->map(function($transaction) {
                return [
                    'id' => $transaction->id,
                    'created_at' => $transaction->created_at->toISOString(),
                    'reference_number' => $transaction->reference_type . ($transaction->reference_id ? '-' . $transaction->reference_id : ''),
                    'product_name' => $transaction->product->product_name ?? 'N/A',
                    'product_sku' => $transaction->product->sku ?? 'N/A',
                    'transaction_type' => $transaction->transaction_type,
                    'warehouse_name' => $transaction->warehouse->name ?? 'N/A',
                    'supplier_name' => $transaction->supplier->name ?? null,
                    'quantity' => $transaction->quantity,
                    'total_value' => $transaction->total_value,
                    'created_by' => $transaction->creator->full_name ?? 'Hệ thống',
                    'can_edit' => $transaction->created_at->diffInHours(now()) < 24, // Can edit within 24 hours
                    'actions' => '' // Will be rendered by JavaScript
                ];
            });

            return response()->json([
                'draw' => intval($draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalRecords,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'draw' => $params['draw'] ?? 1,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Error loading transactions: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get transaction statistics (AJAX)
     */
    public function getTransactionStatistics()
    {
        try {
            $today = now()->startOfDay();

            $todayImports = InventoryTransaction::where('transaction_type', 'import')
                                               ->whereDate('created_at', $today)
                                               ->count();

            $todayExports = InventoryTransaction::where('transaction_type', 'export')
                                              ->whereDate('created_at', $today)
                                              ->count();

            $todayAdjustments = InventoryTransaction::where('transaction_type', 'adjustment')
                                                  ->whereDate('created_at', $today)
                                                  ->count();

            $totalTransactions = InventoryTransaction::count();

            return response()->json([
                'success' => true,
                'data' => [
                    'today_imports' => $todayImports,
                    'today_exports' => $todayExports,
                    'today_adjustments' => $todayAdjustments,
                    'total_transactions' => $totalTransactions
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading statistics: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get transaction detail (AJAX)
     */
    public function getTransactionDetail($id)
    {
        try {
            $transaction = InventoryTransaction::with(['product', 'warehouse', 'supplier', 'creator'])
                                             ->findOrFail($id);

            $html = view('admin.inventory.partials.transaction-detail', compact('transaction'))->render();

            return response($html);

        } catch (\Exception $e) {
            return response('<div class="alert alert-danger">Không thể tải chi tiết giao dịch: ' . $e->getMessage() . '</div>');
        }
    }

    /**
     * Báo cáo tồn kho
     */
    public function report(Request $request)
    {
        $warehouseId = $request->get('warehouse_id');
        $warehouses = Warehouse::active()->get();
        
        $inventories = $this->inventoryService->getInventoryReport($warehouseId);
        $lowStockProducts = $this->inventoryService->getLowStockProducts($warehouseId);

        // Tính toán thống kê
        $totalProducts = $inventories->count();
        $totalQuantity = $inventories->sum('quantity');
        $totalValue = $inventories->sum(function($inventory) {
            return $inventory->quantity * $inventory->product->cost_price;
        });
        $lowStockCount = $lowStockProducts->count();
        $outOfStockCount = $inventories->where('quantity', '<=', 0)->count();

        $statistics = [
            'total_products' => $totalProducts,
            'total_quantity' => $totalQuantity,
            'total_value' => $totalValue,
            'low_stock_count' => $lowStockCount,
            'out_of_stock_count' => $outOfStockCount,
            'in_stock_count' => $totalProducts - $outOfStockCount,
            'stock_health_percentage' => $totalProducts > 0 ? 
                round((($totalProducts - $lowStockCount - $outOfStockCount) / $totalProducts) * 100, 1) : 100
        ];

        return view('admin.inventory.report', compact('inventories', 'lowStockProducts', 'warehouses', 'warehouseId', 'statistics'));
    }
}
