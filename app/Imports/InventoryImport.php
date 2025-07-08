<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Inventory;
use App\Models\InventoryTransaction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class InventoryImport implements ToCollection, WithHeadingRow, WithBatchInserts, WithChunkReading
{
    protected $options;
    protected $results;
    protected $errors;

    public function __construct($options = [])
    {
        $this->options = array_merge([
            'allow_negative' => false,
            'create_missing_products' => false,
            'create_missing_warehouses' => false,
            'update_product_cost' => false,
        ], $options);

        $this->results = [
            'total_rows' => 0,
            'processed' => 0,
            'skipped' => 0,
            'errors' => 0,
            'error_details' => [],
            'transactions' => []
        ];

        $this->errors = [];
    }

    /**
     * Process the collection
     */
    public function collection(Collection $rows)
    {
        $this->results['total_rows'] = $rows->count();

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // Account for header row
            
            try {
                $this->processRow($row->toArray(), $rowNumber);
            } catch (\Exception $e) {
                $this->results['errors']++;
                $this->results['error_details'][] = [
                    'row' => $rowNumber,
                    'error' => $e->getMessage(),
                    'data' => $row->toArray()
                ];
            }
        }
    }

    /**
     * Process a single row
     */
    protected function processRow($row, $rowNumber)
    {
        // Normalize column names (handle different possible header formats)
        $normalizedRow = $this->normalizeRowData($row);

        // Validate row data
        $validation = $this->validateRow($normalizedRow, $rowNumber);
        if (!$validation['valid']) {
            $this->results['skipped']++;
            $this->results['error_details'][] = [
                'row' => $rowNumber,
                'error' => implode(', ', $validation['errors']),
                'data' => $normalizedRow
            ];
            return;
        }

        // Get validated data
        $validatedData = $validation['data'];

        // Process the inventory transaction
        $this->processInventoryTransaction($validatedData, $rowNumber);
    }

    /**
     * Normalize row data to standard column names
     */
    protected function normalizeRowData($row)
    {
        $columnMapping = [
            // Product fields
            'product_sku' => ['product_sku', 'sku', 'ma_sku', 'ma_san_pham'],
            'product_name' => ['product_name', 'ten_san_pham', 'name'],
            
            // Warehouse fields
            'warehouse_code' => ['warehouse_code', 'ma_kho', 'kho'],
            'warehouse_name' => ['warehouse_name', 'ten_kho'],
            
            // Quantity fields
            'current_quantity' => ['current_quantity', 'ton_kho_hien_tai', 'so_luong_hien_tai'],
            'adjustment_quantity' => ['adjustment_quantity', 'so_luong_dieu_chinh', 'quantity'],
            'adjustment_type' => ['adjustment_type', 'loai_dieu_chinh', 'type'],
            
            // Cost fields
            'unit_cost' => ['unit_cost', 'don_gia', 'gia_von'],
            'total_cost' => ['total_cost', 'tong_tien', 'thanh_tien'],
            
            // Additional fields
            'reason' => ['reason', 'ly_do', 'ghi_chu_ly_do'],
            'reference_number' => ['reference_number', 'so_tham_chieu', 'ma_tham_chieu'],
            'notes' => ['notes', 'ghi_chu', 'mo_ta']
        ];

        $normalized = [];
        
        foreach ($columnMapping as $standardKey => $possibleKeys) {
            foreach ($possibleKeys as $key) {
                if (isset($row[$key]) && !empty($row[$key])) {
                    $normalized[$standardKey] = $row[$key];
                    break;
                }
            }
        }

        return $normalized;
    }

    /**
     * Validate a single row
     */
    protected function validateRow($row, $rowNumber)
    {
        $errors = [];

        // Required fields validation
        if (empty($row['product_sku'])) {
            $errors[] = 'Mã SKU sản phẩm là bắt buộc';
        }

        if (empty($row['warehouse_code'])) {
            $errors[] = 'Mã kho là bắt buộc';
        }

        if (!isset($row['adjustment_quantity']) || !is_numeric($row['adjustment_quantity'])) {
            $errors[] = 'Số lượng điều chỉnh phải là số';
        }

        if (empty($row['adjustment_type']) || !in_array(strtolower($row['adjustment_type']), ['import', 'export', 'adjustment', 'nhap', 'xuat', 'dieu_chinh'])) {
            $errors[] = 'Loại điều chỉnh phải là: import/nhap, export/xuat, hoặc adjustment/dieu_chinh';
        }

        // Normalize adjustment type
        $typeMapping = [
            'nhap' => 'import',
            'xuat' => 'export',
            'dieu_chinh' => 'adjustment'
        ];
        
        if (isset($row['adjustment_type'])) {
            $row['adjustment_type'] = $typeMapping[strtolower($row['adjustment_type'])] ?? strtolower($row['adjustment_type']);
        }

        // Validate product exists
        if (!empty($row['product_sku'])) {
            $product = Product::where('sku', $row['product_sku'])->first();
            if (!$product) {
                if ($this->options['create_missing_products']) {
                    // Create product if option is enabled
                    $product = $this->createMissingProduct($row);
                } else {
                    $errors[] = "Không tìm thấy sản phẩm với SKU: " . $row['product_sku'];
                }
            }
            
            if ($product) {
                $row['product_id'] = $product->id;
                $row['product'] = $product;
            }
        }

        // Validate warehouse exists
        if (!empty($row['warehouse_code'])) {
            $warehouse = Warehouse::where('code', $row['warehouse_code'])->first();
            if (!$warehouse) {
                if ($this->options['create_missing_warehouses']) {
                    // Create warehouse if option is enabled
                    $warehouse = $this->createMissingWarehouse($row);
                } else {
                    $errors[] = "Không tìm thấy kho với mã: " . $row['warehouse_code'];
                }
            }
            
            if ($warehouse) {
                $row['warehouse_id'] = $warehouse->id;
                $row['warehouse'] = $warehouse;
            }
        }

        // Validate numeric fields
        if (isset($row['unit_cost']) && !empty($row['unit_cost']) && !is_numeric($row['unit_cost'])) {
            $errors[] = 'Đơn giá phải là số';
        }

        if (isset($row['total_cost']) && !empty($row['total_cost']) && !is_numeric($row['total_cost'])) {
            $errors[] = 'Tổng tiền phải là số';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'data' => $row
        ];
    }

    /**
     * Process inventory transaction
     */
    protected function processInventoryTransaction($data, $rowNumber)
    {
        DB::beginTransaction();
        
        try {
            // Get or create inventory record
            $inventory = Inventory::firstOrCreate([
                'product_id' => $data['product_id'],
                'warehouse_id' => $data['warehouse_id']
            ], [
                'quantity' => 0
            ]);

            // Calculate new quantity based on adjustment type
            $oldQuantity = $inventory->quantity;
            $adjustmentQuantity = (float) $data['adjustment_quantity'];
            
            switch ($data['adjustment_type']) {
                case 'import':
                    $newQuantity = $oldQuantity + abs($adjustmentQuantity);
                    $finalAdjustment = abs($adjustmentQuantity);
                    break;
                case 'export':
                    $newQuantity = $oldQuantity - abs($adjustmentQuantity);
                    $finalAdjustment = -abs($adjustmentQuantity);
                    break;
                case 'adjustment':
                    $newQuantity = abs($adjustmentQuantity); // Set absolute value
                    $finalAdjustment = $newQuantity - $oldQuantity;
                    break;
                default:
                    throw new \Exception('Loại điều chỉnh không hợp lệ');
            }

            // Prevent negative inventory if not allowed
            if ($newQuantity < 0 && !$this->options['allow_negative']) {
                $this->results['skipped']++;
                $this->results['error_details'][] = [
                    'row' => $rowNumber,
                    'error' => 'Không cho phép tồn kho âm. Tồn kho hiện tại: ' . $oldQuantity . ', điều chỉnh: ' . $finalAdjustment,
                    'data' => $data
                ];
                DB::rollBack();
                return;
            }

            // Update inventory
            $inventory->update(['quantity' => $newQuantity]);

            // Update product cost if option is enabled
            if ($this->options['update_product_cost'] && !empty($data['unit_cost'])) {
                $data['product']->update(['cost_price' => $data['unit_cost']]);
            }

            // Create transaction record
            $transaction = InventoryTransaction::create([
                'product_id' => $data['product_id'],
                'warehouse_id' => $data['warehouse_id'],
                'type' => $data['adjustment_type'],
                'quantity' => $finalAdjustment,
                'unit_cost' => $data['unit_cost'] ?? 0,
                'total_cost' => $data['total_cost'] ?? ($finalAdjustment * ($data['unit_cost'] ?? 0)),
                'reason' => $data['reason'] ?? 'Import từ Excel',
                'reference_number' => $data['reference_number'] ?? '',
                'notes' => $data['notes'] ?? '',
                'old_quantity' => $oldQuantity,
                'new_quantity' => $newQuantity,
                'created_by' => auth()->id(),
                'transaction_date' => now()
            ]);

            $this->results['transactions'][] = $transaction;
            $this->results['processed']++;

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->results['errors']++;
            $this->results['error_details'][] = [
                'row' => $rowNumber,
                'error' => $e->getMessage(),
                'data' => $data
            ];
        }
    }

    /**
     * Create missing product
     */
    protected function createMissingProduct($row)
    {
        return Product::create([
            'sku' => $row['product_sku'],
            'product_name' => $row['product_name'] ?? $row['product_sku'],
            'product_slug' => \Str::slug($row['product_name'] ?? $row['product_sku']),
            'product_description' => 'Tạo tự động từ import',
            'product_content' => '',
            'cost_price' => $row['unit_cost'] ?? 0,
            'sale_price' => ($row['unit_cost'] ?? 0) * 1.2, // 20% markup
            'product_status' => 'draft',
            'created_by_user' => auth()->id(),
            'updated_by_user' => auth()->id(),
        ]);
    }

    /**
     * Create missing warehouse
     */
    protected function createMissingWarehouse($row)
    {
        return Warehouse::create([
            'code' => $row['warehouse_code'],
            'name' => $row['warehouse_name'] ?? $row['warehouse_code'],
            'description' => 'Tạo tự động từ import',
            'status' => 'active',
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);
    }

    /**
     * Get import results
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * Batch size for processing
     */
    public function batchSize(): int
    {
        return 100;
    }

    /**
     * Chunk size for reading
     */
    public function chunkSize(): int
    {
        return 500;
    }
}
