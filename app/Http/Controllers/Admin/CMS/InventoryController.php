<?php

namespace App\Http\Controllers\Admin\CMS;

use App\Http\Controllers\Controller;
use App\Services\InventoryService;
use App\Services\ValidationService;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InventoryController extends Controller
{
    protected $request;
    protected $validator;
    protected $inventoryService;

    public function __construct(Request $request, ValidationService $validator, InventoryService $inventoryService)
    {
        $this->request = $request;
        $this->validator = $validator;
        $this->inventoryService = $inventoryService;
    }

    /**
     * Display inventory dashboard
     */
    public function index()
    {
        $statistics = $this->inventoryService->getInventoryStatistics();
        $lowStockProducts = $this->inventoryService->getLowStockProducts()->take(10);
        $outOfStockProducts = $this->inventoryService->getOutOfStockProducts()->take(10);
        $recentTransactions = InventoryTransaction::with('product', 'creator')
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();


        return view('admin.inventory.index', compact(
            'statistics',
            'lowStockProducts',
            'outOfStockProducts',
            'recentTransactions'
        ));
    }

    /**
     * Display inventory transactions
     */
    public function transactions()
    {
        $warehouses = $this->getWarehouses();
        return view('admin.inventory.transactions', compact('warehouses'));
    }

    /**
     * Get inventory transactions via AJAX (DataTables format)
     */
    public function getTransactionsAjax()
    {
        try {
            $params = $this->request->all();

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
                    'reference_number' => ($transaction->reference_type ?? 'REF') . ($transaction->reference_id ? '-' . $transaction->reference_id : ''),
                    'product_name' => $transaction->product->product_name ?? 'N/A',
                    'product_sku' => $transaction->product->sku ?? 'N/A',
                    'transaction_type' => $transaction->transaction_type,
                    'warehouse_name' => $transaction->warehouse->name ?? 'Kho Chính',
                    'supplier_name' => $transaction->supplier->name ?? null,
                    'quantity' => $transaction->quantity ?? 0,
                    'total_value' => $transaction->total_value ?? 0,
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
            Log::error('Statistics error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading statistics: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Process inventory transaction
     */
    public function processTransaction()
    {
        try {
            $params = $this->request->all();
            
            // Validate required fields
            $validator = $this->validator->make($params, 'inventory_transaction_fields');
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()->all()
                ], 422);
            }

            $result = $this->inventoryService->processTransaction(
                $params['product_id'],
                $params['transaction_type'],
                $params['quantity'],
                [
                    'unit_cost' => $params['unit_cost'] ?? null,
                    'notes' => $params['notes'] ?? null,
                    'reason' => $params['reason'] ?? null,
                ]
            );

            return response()->json($result);
            
        } catch (\Exception $e) {
            Log::error('Inventory transaction failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Transaction failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show import form
     */
    public function import()
    {
        $warehouses = $this->getWarehouses();
        return view('admin.inventory.import', compact('warehouses'));
    }

    /**
     * Process import
     */
    public function processImport()
    {
        try {
            $params = $this->request->all();
            $result = $this->inventoryService->processImport($params);

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process import: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show export form
     */
    public function export()
    {
        $warehouses = $this->getWarehouses();
        return view('admin.inventory.export', compact('warehouses'));
    }

    /**
     * Process export
     */
    public function processExport()
    {
        try {
            $params = $this->request->all();
            $result = $this->inventoryService->processExport($params);

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process export: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show adjustment form
     */
    public function adjustment()
    {
        $warehouses = $this->getWarehouses();
        return view('admin.inventory.adjustment', compact('warehouses'));
    }

    /**
     * Process adjustment
     */
    public function processAdjustment()
    {
        try {
            $params = $this->request->all();
            $result = $this->inventoryService->processBulkAdjustment($params);

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process adjustment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get transaction detail
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
     * Export transactions to Excel
     */
    public function exportTransactions()
    {
        try {
            $filters = $this->request->all();
            $result = $this->inventoryService->exportTransactions($filters);

            return $result;

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export transactions: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show stock check page
     */
    public function stockCheck()
    {
        $warehouses = $this->getWarehouses();
        return view('admin.inventory.stock-check', compact('warehouses'));
    }

    /**
     * Get inventory report
     */
    public function report()
    {
        $filters = $this->request->all();
        $report = $this->inventoryService->generateInventoryReport($filters);

        if ($this->request->ajax()) {
            return response()->json($report);
        }

        return view('admin.inventory.report', compact('report', 'filters'));
    }

    /**
     * Get warehouses list
     */
    private function getWarehouses()
    {
        // Try to get actual warehouses from database, fallback to default if none exist
        $warehouses = Warehouse::active()->get();

        if ($warehouses->isEmpty()) {
            // Return default warehouses if none exist in database
            return collect([
                (object) ['id' => 1, 'name' => 'Kho Chính', 'code' => 'KC001'],
                (object) ['id' => 2, 'name' => 'Kho Phụ', 'code' => 'KP001'],
            ]);
        }

        return $warehouses;
    }
}
