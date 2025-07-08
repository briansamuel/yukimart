<?php

namespace App\Http\Controllers\Admin\CMS;

use App\Http\Controllers\Controller;
use App\Services\InventoryImportExportService;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InventoryImportExportController extends Controller
{
    protected $inventoryService;

    public function __construct(InventoryImportExportService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Display import/export page
     */
    public function index()
    {
        $warehouses = Warehouse::active()->orderBy('name')->get();
        return view('admin.inventory.import-export.index', compact('warehouses'));
    }

    /**
     * Export inventory data
     */
    public function export(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'format' => 'required|in:xlsx,csv',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'product_category' => 'nullable|string',
            'low_stock_only' => 'nullable|boolean',
            'out_of_stock_only' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $filters = [
                'warehouse_id' => $request->warehouse_id,
                'product_category' => $request->product_category,
                'low_stock_only' => $request->boolean('low_stock_only'),
                'out_of_stock_only' => $request->boolean('out_of_stock_only'),
                'search' => $request->search,
            ];

            return $this->inventoryService->exportInventory($filters);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi xuất dữ liệu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Import inventory data
     */
    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
            'allow_negative' => 'nullable|boolean',
            'create_missing_products' => 'nullable|boolean',
            'create_missing_warehouses' => 'nullable|boolean',
            'update_product_cost' => 'nullable|boolean',
        ], [
            'file.required' => 'File import là bắt buộc',
            'file.mimes' => 'File phải có định dạng: xlsx, xls, csv',
            'file.max' => 'File không được vượt quá 10MB',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $options = [
                'allow_negative' => $request->boolean('allow_negative'),
                'create_missing_products' => $request->boolean('create_missing_products'),
                'create_missing_warehouses' => $request->boolean('create_missing_warehouses'),
                'update_product_cost' => $request->boolean('update_product_cost'),
            ];

            $result = $this->inventoryService->importInventory($request->file('file'), $options);

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi import dữ liệu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download import template
     */
    public function downloadTemplate()
    {
        try {
            return $this->inventoryService->generateImportTemplate();

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tạo template: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get import/export history
     */
    public function getHistory(Request $request)
    {
        try {
            $filters = [
                'date_from' => $request->date_from,
                'date_to' => $request->date_to,
                'type' => $request->type,
                'warehouse_id' => $request->warehouse_id,
                'product_id' => $request->product_id,
                'per_page' => $request->get('per_page', 20),
            ];

            $history = $this->inventoryService->getImportExportHistory($filters);

            return response()->json([
                'success' => true,
                'data' => $history
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải lịch sử: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get inventory summary
     */
    public function getSummary(Request $request)
    {
        try {
            $filters = [
                'warehouse_id' => $request->warehouse_id,
                'product_category' => $request->product_category,
                'low_stock_only' => $request->boolean('low_stock_only'),
                'out_of_stock_only' => $request->boolean('out_of_stock_only'),
            ];

            $summary = $this->inventoryService->getInventorySummary($filters);

            $statistics = [
                'total_products' => $summary->count(),
                'total_value' => $summary->sum(function($item) {
                    return $item->quantity * $item->cost_price;
                }),
                'low_stock_count' => $summary->filter(function($item) {
                    return $item->quantity <= $item->reorder_point && $item->quantity > 0;
                })->count(),
                'out_of_stock_count' => $summary->filter(function($item) {
                    return $item->quantity <= 0;
                })->count(),
                'in_stock_count' => $summary->filter(function($item) {
                    return $item->quantity > $item->reorder_point;
                })->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $statistics
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải thống kê: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate import file before processing
     */
    public function validateImportFile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'File không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // This would involve reading the file and validating its structure
            // For now, we'll return a simple validation
            $file = $request->file('file');
            
            return response()->json([
                'success' => true,
                'message' => 'File hợp lệ',
                'data' => [
                    'filename' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'extension' => $file->getClientOriginalExtension(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi kiểm tra file: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available warehouses for dropdown
     */
    public function getWarehouses()
    {
        try {
            $warehouses = Warehouse::active()
                ->orderBy('name')
                ->select('id', 'code', 'name')
                ->get()
                ->map(function($warehouse) {
                    return [
                        'id' => $warehouse->id,
                        'text' => $warehouse->code . ' - ' . $warehouse->name
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $warehouses
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải danh sách kho: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get product categories for dropdown
     */
    public function getProductCategories()
    {
        try {
            $categories = \App\Models\Product::select('product_category')
                ->whereNotNull('product_category')
                ->where('product_category', '!=', '')
                ->distinct()
                ->orderBy('product_category')
                ->pluck('product_category')
                ->map(function($category) {
                    return [
                        'id' => $category,
                        'text' => $category
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $categories
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải danh mục sản phẩm: ' . $e->getMessage()
            ], 500);
        }
    }
}
