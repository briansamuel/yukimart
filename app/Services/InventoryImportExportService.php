<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\InventoryExport;
use App\Imports\InventoryImport;

class InventoryImportExportService
{
    /**
     * Export inventory data to Excel
     */
    public function exportInventory($filters = [])
    {
        try {
            $filename = 'inventory_export_' . date('Y-m-d_H-i-s') . '.xlsx';
            
            return Excel::download(new InventoryExport($filters), $filename);

        } catch (\Exception $e) {
            throw new \Exception('Lỗi khi xuất dữ liệu tồn kho: ' . $e->getMessage());
        }
    }

    /**
     * Import inventory data from Excel
     */
    public function importInventory($file, $options = [])
    {
        try {
            DB::beginTransaction();

            $validator = Validator::make(['file' => $file], [
                'file' => 'required|mimes:xlsx,xls,csv|max:10240'
            ]);

            if ($validator->fails()) {
                throw new \Exception('File không hợp lệ. Chỉ chấp nhận file Excel (.xlsx, .xls) hoặc CSV.');
            }

            // Store the uploaded file temporarily
            $path = $file->store('temp');
            
            // Import data
            $import = new InventoryImport($options);
            Excel::import($import, $path);

            // Clean up temporary file
            Storage::delete($path);

            $results = $import->getResults();

            DB::commit();

            return [
                'success' => true,
                'message' => 'Import thành công',
                'data' => $results
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Clean up temporary file if exists
            if (isset($path)) {
                Storage::delete($path);
            }

            throw new \Exception('Lỗi khi import dữ liệu: ' . $e->getMessage());
        }
    }

    /**
     * Generate inventory template for import
     */
    public function generateImportTemplate()
    {
        try {
            $filename = 'inventory_import_template.xlsx';
            
            // Get sample data
            $sampleData = [
                [
                    'product_sku' => 'SAMPLE001',
                    'product_name' => 'Sản phẩm mẫu 1',
                    'warehouse_code' => 'WH001',
                    'warehouse_name' => 'Kho chính',
                    'current_quantity' => 100,
                    'adjustment_quantity' => 50,
                    'adjustment_type' => 'import', // import, export, adjustment
                    'unit_cost' => 25000,
                    'total_cost' => 1250000,
                    'reason' => 'Nhập hàng từ nhà cung cấp',
                    'reference_number' => 'PO-2024-001',
                    'notes' => 'Ghi chú bổ sung'
                ],
                [
                    'product_sku' => 'SAMPLE002',
                    'product_name' => 'Sản phẩm mẫu 2',
                    'warehouse_code' => 'WH001',
                    'warehouse_name' => 'Kho chính',
                    'current_quantity' => 75,
                    'adjustment_quantity' => -25,
                    'adjustment_type' => 'export',
                    'unit_cost' => 30000,
                    'total_cost' => -750000,
                    'reason' => 'Xuất hàng bán lẻ',
                    'reference_number' => 'SO-2024-001',
                    'notes' => 'Xuất cho đơn hàng #12345'
                ]
            ];

            return Excel::download(new \App\Exports\InventoryTemplateExport($sampleData), $filename);

        } catch (\Exception $e) {
            throw new \Exception('Lỗi khi tạo template: ' . $e->getMessage());
        }
    }

    /**
     * Validate import data
     */
    public function validateImportData($data)
    {
        $errors = [];
        $validatedData = [];

        foreach ($data as $index => $row) {
            $rowNumber = $index + 2; // Account for header row
            $rowErrors = [];

            // Validate required fields
            if (empty($row['product_sku'])) {
                $rowErrors[] = "Mã SKU sản phẩm là bắt buộc";
            }

            if (empty($row['warehouse_code'])) {
                $rowErrors[] = "Mã kho là bắt buộc";
            }

            if (!isset($row['adjustment_quantity']) || !is_numeric($row['adjustment_quantity'])) {
                $rowErrors[] = "Số lượng điều chỉnh phải là số";
            }

            if (empty($row['adjustment_type']) || !in_array($row['adjustment_type'], ['import', 'export', 'adjustment'])) {
                $rowErrors[] = "Loại điều chỉnh phải là: import, export, hoặc adjustment";
            }

            // Validate product exists
            if (!empty($row['product_sku'])) {
                $product = Product::where('sku', $row['product_sku'])->first();
                if (!$product) {
                    $rowErrors[] = "Không tìm thấy sản phẩm với SKU: " . $row['product_sku'];
                } else {
                    $row['product_id'] = $product->id;
                    $row['product'] = $product;
                }
            }

            // Validate warehouse exists
            if (!empty($row['warehouse_code'])) {
                $warehouse = Warehouse::where('code', $row['warehouse_code'])->first();
                if (!$warehouse) {
                    $rowErrors[] = "Không tìm thấy kho với mã: " . $row['warehouse_code'];
                } else {
                    $row['warehouse_id'] = $warehouse->id;
                    $row['warehouse'] = $warehouse;
                }
            }

            // Validate numeric fields
            if (isset($row['unit_cost']) && !is_numeric($row['unit_cost'])) {
                $rowErrors[] = "Đơn giá phải là số";
            }

            if (isset($row['total_cost']) && !is_numeric($row['total_cost'])) {
                $rowErrors[] = "Tổng tiền phải là số";
            }

            if (!empty($rowErrors)) {
                $errors["Dòng {$rowNumber}"] = $rowErrors;
            } else {
                $validatedData[] = $row;
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'data' => $validatedData
        ];
    }

    /**
     * Process validated import data
     */
    public function processImportData($validatedData, $options = [])
    {
        $results = [
            'processed' => 0,
            'skipped' => 0,
            'errors' => 0,
            'transactions' => []
        ];

        foreach ($validatedData as $row) {
            try {
                // Get or create inventory record
                $inventory = Inventory::firstOrCreate([
                    'product_id' => $row['product_id'],
                    'warehouse_id' => $row['warehouse_id']
                ], [
                    'quantity' => 0
                ]);

                // Calculate new quantity based on adjustment type
                $oldQuantity = $inventory->quantity;
                $adjustmentQuantity = (float) $row['adjustment_quantity'];
                
                switch ($row['adjustment_type']) {
                    case 'import':
                        $newQuantity = $oldQuantity + $adjustmentQuantity;
                        break;
                    case 'export':
                        $newQuantity = $oldQuantity - abs($adjustmentQuantity);
                        break;
                    case 'adjustment':
                        $newQuantity = $adjustmentQuantity; // Set absolute value
                        $adjustmentQuantity = $newQuantity - $oldQuantity;
                        break;
                    default:
                        throw new \Exception('Loại điều chỉnh không hợp lệ');
                }

                // Prevent negative inventory if configured
                if ($newQuantity < 0 && !($options['allow_negative'] ?? false)) {
                    $results['skipped']++;
                    continue;
                }

                // Update inventory
                $inventory->update(['quantity' => $newQuantity]);

                // Create transaction record
                $transaction = InventoryTransaction::create([
                    'product_id' => $row['product_id'],
                    'warehouse_id' => $row['warehouse_id'],
                    'type' => $row['adjustment_type'],
                    'quantity' => $adjustmentQuantity,
                    'unit_cost' => $row['unit_cost'] ?? 0,
                    'total_cost' => $row['total_cost'] ?? 0,
                    'reason' => $row['reason'] ?? '',
                    'reference_number' => $row['reference_number'] ?? '',
                    'notes' => $row['notes'] ?? '',
                    'old_quantity' => $oldQuantity,
                    'new_quantity' => $newQuantity,
                    'created_by' => auth()->id(),
                    'transaction_date' => now()
                ]);

                $results['transactions'][] = $transaction;
                $results['processed']++;

            } catch (\Exception $e) {
                $results['errors']++;
                \Log::error('Import inventory error: ' . $e->getMessage(), [
                    'row' => $row,
                    'user_id' => auth()->id()
                ]);
            }
        }

        return $results;
    }

    /**
     * Get import/export history
     */
    public function getImportExportHistory($filters = [])
    {
        $query = InventoryTransaction::with(['product', 'warehouse', 'creator'])
            ->whereIn('type', ['import', 'export', 'adjustment']);

        // Apply filters
        if (!empty($filters['date_from'])) {
            $query->whereDate('transaction_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('transaction_date', '<=', $filters['date_to']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['warehouse_id'])) {
            $query->where('warehouse_id', $filters['warehouse_id']);
        }

        if (!empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        return $query->orderBy('transaction_date', 'desc')
                    ->paginate($filters['per_page'] ?? 20);
    }

    /**
     * Get inventory summary for export
     */
    public function getInventorySummary($filters = [])
    {
        $query = Inventory::with(['product', 'warehouse'])
            ->join('products', 'inventories.product_id', '=', 'products.id')
            ->join('warehouses', 'inventories.warehouse_id', '=', 'warehouses.id')
            ->select([
                'inventories.*',
                'products.product_name',
                'products.sku',
                'products.cost_price',
                'products.sale_price',
                'warehouses.name as warehouse_name',
                'warehouses.code as warehouse_code'
            ]);

        // Apply filters
        if (!empty($filters['warehouse_id'])) {
            $query->where('inventories.warehouse_id', $filters['warehouse_id']);
        }

        if (!empty($filters['product_category'])) {
            $query->where('products.product_category', $filters['product_category']);
        }

        if (!empty($filters['low_stock_only'])) {
            $query->whereRaw('inventories.quantity <= products.reorder_point');
        }

        if (!empty($filters['out_of_stock_only'])) {
            $query->where('inventories.quantity', '<=', 0);
        }

        return $query->orderBy('products.product_name')
                    ->get();
    }
}
