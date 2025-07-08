<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\ProductCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\HeadingRowImport;


class ProductImportService
{
    /**
     * Parse file and get preview data
     *
     * @param string $filePath
     * @param string $fileExtension
     * @return array
     */
    public function parseFilePreview(string $filePath, string $fileExtension): array
    {
        $fullPath = storage_path('app/' . $filePath);

        // Get headers directly from first row
        $headers = [];
        if ($fileExtension === 'csv') {
            $headers = $this->getCsvHeaders($fullPath);
        } else {
            $headers = $this->getExcelHeaders($fullPath);
        }

        // Get first 10 rows for preview
        $data = [];
        if ($fileExtension === 'csv') {
            $data = $this->parseCsvPreview($fullPath);
        } else {
            $data = $this->parseExcelPreview($fullPath);
        }

        return [
            'headers' => $headers,
            'data' => array_slice($data, 0, 10), // First 10 rows
            'total_rows' => count($data),
            'file_type' => $fileExtension,
        ];
    }

    /**
     * Get CSV headers from first row
     *
     * @param string $filePath
     * @return array
     */
    protected function getCsvHeaders(string $filePath): array
    {
        $headers = [];
        if (($handle = fopen($filePath, 'r')) !== false) {
            $headers = fgetcsv($handle);
            fclose($handle);
        }

        // Clean and ensure headers are strings
        return array_map(function($header) {
            return trim((string) $header);
        }, $headers ?: []);
    }

    /**
     * Get Excel headers from first row
     *
     * @param string $filePath
     * @return array
     */
    protected function getExcelHeaders(string $filePath): array
    {
        $data = Excel::toArray([], $filePath)[0] ?? [];
        $headers = $data[0] ?? [];

        // Clean and ensure headers are strings
        return array_map(function($header) {
            return trim((string) $header);
        }, $headers);
    }

    /**
     * Parse CSV file preview
     *
     * @param string $filePath
     * @return array
     */
    protected function parseCsvPreview(string $filePath): array
    {
        $data = [];
        if (($handle = fopen($filePath, 'r')) !== false) {
            fgetcsv($handle); // Skip header row
            $rowIndex = 0;
            while (($row = fgetcsv($handle)) !== false && $rowIndex < 100) { // Limit to 100 rows for preview
                $data[] = $row;
                $rowIndex++;
            }
            fclose($handle);
        }
        return $data;
    }

    /**
     * Parse Excel file preview
     *
     * @param string $filePath
     * @return array
     */
    protected function parseExcelPreview(string $filePath): array
    {
        $data = Excel::toArray([], $filePath)[0] ?? [];
        // Remove header row
        array_shift($data);
        return $data;
    }

    /**
     * Get available product fields for mapping
     *
     * @return array
     */
    public function getAvailableFields(): array
    {
        return [
            'product_name' => [
                'label' => __('product.product_name'),
                'required' => true,
                'type' => 'string',
                'description' => __('product.product_name_description'),
            ],
            'product_description' => [
                'label' => __('product.product_description'),
                'required' => false,
                'type' => 'text',
                'description' => __('product.product_description_description'),
            ],
            'sku' => [
                'label' => __('product.sku'),
                'required' => true,
                'type' => 'string',
                'description' => __('product.sku_description'),
            ],
            'barcode' => [
                'label' => __('product.barcode'),
                'required' => false,
                'type' => 'string',
                'description' => __('product.barcode_description'),
            ],
            'sale_price' => [
                'label' => 'Giá bán',
                'required' => true,
                'type' => 'number',
                'description' => 'Giá bán sản phẩm cho khách hàng',
            ],
            'compare_price' => [
                'label' => 'Giá so sánh',
                'required' => false,
                'type' => 'number',
                'description' => 'Giá gốc để so sánh (giá gạch ngang)',
            ],
            'cost_price' => [
                'label' => __('product.cost_price'),
                'required' => false,
                'type' => 'number',
                'description' => __('product.cost_price_description'),
            ],
            'category_name' => [
                'label' => __('product.category'),
                'required' => false,
                'type' => 'string',
                'description' => __('product.category_description'),
            ],
            'brand' => [
                'label' => 'Thương hiệu',
                'required' => false,
                'type' => 'string',
                'description' => 'Tên thương hiệu/nhãn hiệu của sản phẩm',
            ],
            'stock_quantity' => [
                'label' => __('product.stock_quantity'),
                'required' => false,
                'type' => 'number',
                'description' => __('product.stock_quantity_description'),
            ],
            'weight' => [
                'label' => __('product.weight'),
                'required' => false,
                'type' => 'number',
                'description' => __('product.weight_description'),
            ],
            'length' => [
                'label' => __('product.length'),
                'required' => false,
                'type' => 'number',
                'description' => __('product.length_description'),
            ],
            'width' => [
                'label' => __('product.width'),
                'required' => false,
                'type' => 'number',
                'description' => __('product.width_description'),
            ],
            'height' => [
                'label' => __('product.height'),
                'required' => false,
                'type' => 'number',
                'description' => __('product.height_description'),
            ],
            'product_status' => [
                'label' => __('product.status'),
                'required' => false,
                'type' => 'select',
                'options' => [
                    'publish' => __('product.status_publish'),
                    'draft' => __('product.status_draft'),
                ],
                'description' => __('product.status_description'),
            ],
            'product_thumbnail' => [
                'label' => __('product.product_thumbnail'),
                'required' => false,
                'type' => 'string',
                'description' => __('product.product_thumbnail_description'),
            ],
            'reorder_point' => [
                'label' => __('product.reorder_point'),
                'required' => false,
                'type' => 'number',
                'description' => __('product.reorder_point_description'),
            ],
            'points' => [
                'label' => __('product.points'),
                'required' => false,
                'type' => 'number',
                'description' => __('product.points_description'),
            ],
        ];
    }

    /**
     * Process import with column mapping
     *
     * @param string $filePath
     * @param string $fileExtension
     * @param array $columnMapping
     * @param array $options
     * @return array
     */
    public function processImport(string $filePath, string $fileExtension, array $columnMapping, array $options = []): array
    {
        $fullPath = storage_path('app/' . $filePath);
        
        // Parse all data
        $data = [];
        if ($fileExtension === 'csv') {
            $data = $this->parseCsvData($fullPath);
        } else {
            $data = $this->parseExcelData($fullPath);
        }

        $results = [
            'total_rows' => count($data),
            'processed' => 0,
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        DB::beginTransaction();
        try {
            foreach ($data as $index => $row) {
                $rowNumber = $index + 2; // +2 because index starts at 0 and we skip header
                
                try {
                    $productData = $this->mapRowData($row, $columnMapping);
                    
                    if (empty($productData['product_name']) || empty($productData['sku'])) {
                        $results['skipped']++;
                        $results['errors'][] = __('product.row_missing_required_fields', ['row' => $rowNumber]);
                        continue;
                    }

                    // Check if product exists
                    $existingProduct = Product::where('sku', $productData['sku'])->first();
                    
                    if ($existingProduct && !($options['update_existing'] ?? false)) {
                        $results['skipped']++;
                        $results['errors'][] = __('product.product_already_exists', ['sku' => $productData['sku'], 'row' => $rowNumber]);
                        continue;
                    }

                    if ($existingProduct) {
                        // Update existing product
                        $this->updateProduct($existingProduct, $productData);
                        $results['updated']++;
                    } else {
                        // Create new product
                        $this->createProduct($productData);
                        $results['created']++;
                    }

                    $results['processed']++;

                } catch (\Exception $e) {
                    $results['errors'][] = __('product.row_processing_error', [
                        'row' => $rowNumber,
                        'error' => $e->getMessage()
                    ]);
                    Log::error('Product import row error', [
                        'row' => $rowNumber,
                        'data' => $row,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }

        // Log import result
        Log::info('Product import completed', [
            'user_id' => Auth::id(),
            'results' => $results,
        ]);

        return $results;
    }

    /**
     * Parse CSV data
     *
     * @param string $filePath
     * @return array
     */
    protected function parseCsvData(string $filePath): array
    {
        $data = [];
        if (($handle = fopen($filePath, 'r')) !== false) {
            fgetcsv($handle); // Skip header row
            while (($row = fgetcsv($handle)) !== false) {
                $data[] = $row;
            }
            fclose($handle);
        }
        return $data;
    }

    /**
     * Parse Excel data
     *
     * @param string $filePath
     * @return array
     */
    protected function parseExcelData(string $filePath): array
    {
        $data = Excel::toArray([], $filePath)[0] ?? [];
        // Remove header row
        array_shift($data);
        return $data;
    }

    /**
     * Map row data to product fields
     *
     * @param array $row
     * @param array $columnMapping
     * @return array
     */
    protected function mapRowData(array $row, array $columnMapping): array
    {
        $productData = [];
      
        
        foreach ($columnMapping as $columnIndex => $fieldName) {
            if ($fieldName && isset($row[$columnIndex])) {
                $value = trim($row[$columnIndex]);
                
                // Handle special fields
                switch ($fieldName) {
                    case 'category_name':
                        if ($value) {
                            $categoryId = $this->processCategoryHierarchy($value);
                            if ($categoryId) {
                                $productData['category_id'] = $categoryId;
                            }
                        }
                        break;

                    case 'brand':
                        $productData[$fieldName] = $value;
                        break;
                    case 'product_status':
                        $productData[$fieldName] = in_array($value, ['publish', 'draft']) ? $value : 'draft';
                        break;
                    
                    case 'sale_price':
                        $productData[$fieldName] = (float) str_replace(',', '', $value);
                        break;
                    case 'compare_price':
                    case 'cost_price':
                        $productData[$fieldName] = (float) str_replace(',', '', $value);
                        break;
                    case 'product_thumbnail':
                        $images = explode(',', $value);
                        $productData[$fieldName] = $images[0] ?? '';
                        break;
                    case 'weight':
                    case 'length':
                    case 'width':
                    case 'height':
                    case 'stock_quantity':
                    case 'reorder_point':
                    case 'points':
                        $productData[$fieldName] = is_numeric($value) ? (float) $value : 0;
                        break;
                    
                    default:
                        $productData[$fieldName] = $value;
                        break;
                }
            }
        }
     
        // Set default values
        $productData['created_by_user'] = Auth::id();
        $productData['updated_by_user'] = Auth::id();
        $productData['product_status'] = $productData['product_status'] ?? 'draft';
        $productData['product_type'] =  $productData['product_type'] ?? 'simple';
        $productData['product_slug'] = Str::slug($productData['product_name']);
        $productData['product_content'] = $productData['product_description'] ?? '';
        return $productData;
    }

    /**
     * Process category hierarchy from string like "Mỹ phẩm & Làm đẹp>>Chăm Sóc Da Mặt>>Kem Chống nắng"
     *
     * @param string $categoryString
     * @return int|null
     */
    protected function processCategoryHierarchy(string $categoryString): ?int
    {
        // Split by >> to get category levels
        $categoryLevels = array_map('trim', explode('>>', $categoryString));

        if (empty($categoryLevels)) {
            return null;
        }

        $parentId = null;
        $categoryId = null;

        foreach ($categoryLevels as $categoryName) {
            if (empty($categoryName)) {
                continue;
            }

            // Find existing category with this name and parent
            $category = ProductCategory::where('name', $categoryName)
                ->where('parent_id', $parentId)
                ->first();

            if (!$category) {
              
                // Create if not exists
                $category = ProductCategory::firstOrCreate([
                    'name' => $categoryName,
                    'parent_id' => $parentId,
                    'slug' => Str::slug($categoryName),
                    'is_active' => 1,
                    'created_by' => Auth::id(),
                ]);
            }

            $categoryId = $category->id;
            $parentId = $categoryId; // This category becomes parent for next level
        }

        return $categoryId;
    }

  
    /**
     * Create new product
     *
     * @param array $productData
     * @return Product
     */
    protected function createProduct(array $productData): Product
    {
        // Extract stock quantity for inventory
        $stockQuantity = $productData['stock_quantity'] ?? 0;
        unset($productData['stock_quantity']);
        
        // Create product
        $product = Product::create($productData);

        // Create inventory record and transaction if stock quantity is provided
        if ($stockQuantity > 0) {
            $this->createInventoryTransaction($product, $stockQuantity);
        }

        return $product;
    }

    /**
     * Create inventory transaction for imported product
     *
     * @param Product $product
     * @param int $stockQuantity
     * @return void
     */
    protected function createInventoryTransaction(Product $product, int $stockQuantity): void
    {
        // Get default warehouse
        $warehouse = Warehouse::getDefault();
        if (!$warehouse) {
            Log::warning('No default warehouse found for inventory transaction', [
                'product_id' => $product->id,
                'stock_quantity' => $stockQuantity
            ]);
            return;
        }

        // Create or update inventory record
        Inventory::updateOrCreate(
            [
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id
            ],
            [
                'quantity' => $stockQuantity
            ]
        );

        // Create inventory transaction
        InventoryTransaction::create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'transaction_type' => InventoryTransaction::TYPE_IMPORT,
            'old_quantity' => 0,
            'quantity' => $stockQuantity,
            'new_quantity' => $stockQuantity,
            'unit_cost' => $product->cost_price ?? 0,
            'total_value' => $stockQuantity * ($product->cost_price ?? 0),
            'reference_type' => 'ProductImport',
            'reference_id' => null,
            'notes' => 'Nhập hàng từ import sản phẩm - SKU: ' . $product->sku,
            'location_to' => $warehouse->name,
            'created_by_user' => Auth::id(),
        ]);

        Log::info('Inventory transaction created for imported product', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'stock_quantity' => $stockQuantity,
            'sku' => $product->sku,
        ]);
    }

    /**
     * Update existing product
     *
     * @param Product $product
     * @param array $productData
     * @return Product
     */
    protected function updateProduct(Product $product, array $productData): Product
    {
        // Extract stock quantity for inventory
        $stockQuantity = $productData['stock_quantity'] ?? null;
        unset($productData['stock_quantity']);

        // Update product
        $product->update($productData);

        // Update inventory if stock quantity is provided
        if ($stockQuantity !== null && $stockQuantity > 0) {
            $this->updateInventoryTransaction($product, $stockQuantity);
        }

        return $product;
    }

    /**
     * Update inventory transaction for existing product
     *
     * @param Product $product
     * @param int $newStockQuantity
     * @return void
     */
    protected function updateInventoryTransaction(Product $product, int $newStockQuantity): void
    {
        // Get default warehouse
        $warehouse = Warehouse::getDefault();
        if (!$warehouse) {
            Log::warning('No default warehouse found for inventory update', [
                'product_id' => $product->id,
                'new_stock_quantity' => $newStockQuantity
            ]);
            return;
        }

        // Get current inventory
        $inventory = Inventory::where('product_id', $product->id)
            ->where('warehouse_id', $warehouse->id)
            ->first();

        $oldQuantity = $inventory ? $inventory->quantity : 0;
        $quantityChange = $newStockQuantity - $oldQuantity;

        // Update inventory record
        Inventory::updateOrCreate(
            [
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id
            ],
            [
                'quantity' => $newStockQuantity
            ]
        );

        // Create inventory transaction only if there's a change
        if ($quantityChange != 0) {
            InventoryTransaction::create([
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'transaction_type' => $quantityChange > 0 ? InventoryTransaction::TYPE_IMPORT : InventoryTransaction::TYPE_ADJUSTMENT,
                'old_quantity' => $oldQuantity,
                'quantity' => $quantityChange,
                'new_quantity' => $newStockQuantity,
                'unit_cost' => $product->cost_price ?? 0,
                'total_value' => abs($quantityChange) * ($product->cost_price ?? 0),
                'reference_type' => 'ProductImport',
                'reference_id' => null,
                'notes' => 'Cập nhật tồn kho từ import sản phẩm - SKU: ' . $product->sku,
                'location_to' => $quantityChange > 0 ? $warehouse->name : null,
                'location_from' => $quantityChange < 0 ? $warehouse->name : null,
                'created_by_user' => Auth::id(),
            ]);

            Log::info('Inventory updated for existing product', [
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'old_quantity' => $oldQuantity,
                'new_quantity' => $newStockQuantity,
                'change' => $quantityChange,
                'sku' => $product->sku,
            ]);
        }
    }

    /**
     * Validate import data
     *
     * @param string $filePath
     * @param string $fileExtension
     * @param array $columnMapping
     * @return array
     */
    public function validateImportData(string $filePath, string $fileExtension, array $columnMapping): array
    {
        Log::info('validateImportData called', [
            'filePath' => $filePath,
            'fileExtension' => $fileExtension,
            'columnMapping' => $columnMapping,
            'columnMappingCount' => count($columnMapping)
        ]);

        if (empty($columnMapping)) {
            Log::warning('Column mapping is empty');
            return [
                'total_rows' => 0,
                'valid_rows' => 0,
                'invalid_rows' => 0,
                'errors' => ['Không có cột nào được map. Vui lòng quay lại bước 2 để map các cột.'],
                'warnings' => [],
            ];
        }

        $fullPath = storage_path('app/' . $filePath);

        // Parse data
        $data = [];
        if ($fileExtension === 'csv') {
            $data = $this->parseCsvData($fullPath);
        } else {
            $data = $this->parseExcelData($fullPath);
        }

        $validation = [
            'total_rows' => count($data),
            'valid_rows' => 0,
            'invalid_rows' => 0,
            'errors' => [],
            'warnings' => [],
        ];

        foreach ($data as $index => $row) {
            $rowNumber = $index + 2;
          
            $productData = $this->mapRowData($row, $columnMapping);
         
            $rowErrors = [];
            
            // Validate required fields
            if (empty($productData['product_name'])) {
                $rowErrors[] = __('product.product_name_required');
            }
            
            if (empty($productData['sku'])) {
                $rowErrors[] = __('product.sku_required');
            } else {
                // Check for duplicate SKU in database
                if (Product::where('sku', $productData['sku'])->exists()) {
                    $validation['warnings'][] = __('product.sku_already_exists', ['sku' => $productData['sku'], 'row' => $rowNumber]);
                }
            }
            
            if (empty($productData['sale_price']) || $productData['sale_price'] <= 0) {
              
                $rowErrors[] = __('product.sale_price_required');
            }

            if (!empty($rowErrors)) {
                $validation['invalid_rows']++;
                $validation['errors'][] = __('product.row_validation_errors', [
                    'row' => $rowNumber,
                    'errors' => implode(', ', $rowErrors)
                ]);
            } else {
                $validation['valid_rows']++;
            }
        }

        return $validation;
    }

    /**
     * Generate import template
     *
     * @return string
     */
    public function generateTemplate(): string
    {
        $headers = [
            __('product.product_name'),
            __('product.product_description'),
            __('product.sku'),
            __('product.barcode'),
            __('product.sale_price'),
            __('product.compare_price'),
            __('product.cost_price'),
            __('product.category'),
            __('product.stock_quantity'),
            __('product.weight'),
            __('product.length'),
            __('product.width'),
            __('product.height'),
            __('product.status'),
        ];

        $sampleData = [
            [
                'Sample Product 1',
                'This is a sample product description',
                'SKU001',
                '1234567890123',
                '100000',
                '120000',
                '80000',
                'Electronics',
                '50',
                '1.5',
                '10',
                '5',
                '3',
                'publish',
            ],
            [
                'Sample Product 2',
                'Another sample product',
                'SKU002',
                '2345678901234',
                '200000',
                '250000',
                '150000',
                'Clothing',
                '25',
                '0.5',
                '20',
                '15',
                '2',
                'draft',
            ],
        ];

        $data = array_merge([$headers], $sampleData);
        
        $tempPath = storage_path('app/temp/product_import_template_' . time() . '.xlsx');

        // Create directory if not exists
        $tempDir = dirname($tempPath);
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        // Create a simple Excel export class
        $export = new class($data) implements \Maatwebsite\Excel\Concerns\FromArray {
            private $data;

            public function __construct($data) {
                $this->data = $data;
            }

            public function array(): array {
                return $this->data;
            }
        };

        Excel::store($export, basename($tempPath), 'local', \Maatwebsite\Excel\Excel::XLSX);

        return $tempPath;
    }

    /**
     * Get detailed preview of file with pagination
     *
     * @param string $filePath
     * @param string $fileExtension
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function getDetailedPreview(string $filePath, string $fileExtension, int $page = 1, int $limit = 50): array
    {
        $fullPath = storage_path('app/' . $filePath);

        // Get headers
        $headers = Excel::toArray(new HeadingRowImport, $fullPath)[0] ?? [];

        // Get all data
        $allData = [];
        if ($fileExtension === 'csv') {
            $allData = $this->parseCsvData($fullPath);
        } else {
            $allData = $this->parseExcelData($fullPath);
        }

        $totalRows = count($allData);
        $offset = ($page - 1) * $limit;
        $data = array_slice($allData, $offset, $limit);

        // Add row numbers
        $dataWithRowNumbers = [];
        foreach ($data as $index => $row) {
            $dataWithRowNumbers[] = [
                'row_number' => $offset + $index + 2, // +2 because index starts at 0 and we skip header
                'data' => $row
            ];
        }

        return [
            'headers' => $headers,
            'data' => $dataWithRowNumbers,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total' => $totalRows,
                'last_page' => ceil($totalRows / $limit),
                'from' => $offset + 1,
                'to' => min($offset + $limit, $totalRows)
            ],
            'file_type' => $fileExtension,
        ];
    }

    /**
     * Get file statistics
     *
     * @param string $filePath
     * @param string $fileExtension
     * @param string $fileName
     * @return array
     */
    public function getFileStatistics(string $filePath, string $fileExtension, string $fileName): array
    {
        $fullPath = storage_path('app/' . $filePath);
        $fileSize = filesize($fullPath);

        // Get headers
        $headers = Excel::toArray(new HeadingRowImport, $fullPath)[0] ?? [];

        // Get all data for statistics
        $allData = [];
        if ($fileExtension === 'csv') {
            $allData = $this->parseCsvData($fullPath);
        } else {
            $allData = $this->parseExcelData($fullPath);
        }

        $totalRows = count($allData);
        $totalColumns = count($headers);

        // Analyze data quality
        $emptyRows = 0;
        $duplicateRows = [];
        $columnStats = [];

        // Initialize column statistics
        foreach ($headers as $index => $header) {
            $columnStats[$index] = [
                'name' => $header,
                'filled_count' => 0,
                'empty_count' => 0,
                'unique_values' => [],
                'data_types' => []
            ];
        }

        // Analyze each row
        $seenRows = [];
        foreach ($allData as $rowIndex => $row) {
            $isEmpty = true;
            $rowKey = implode('|', $row);

            // Check for duplicates
            if (isset($seenRows[$rowKey])) {
                $duplicateRows[] = $rowIndex + 2; // +2 for header and 1-based indexing
            } else {
                $seenRows[$rowKey] = true;
            }

            // Analyze each column
            foreach ($row as $colIndex => $value) {
                if (isset($columnStats[$colIndex])) {
                    $value = trim($value);

                    if (!empty($value)) {
                        $isEmpty = false;
                        $columnStats[$colIndex]['filled_count']++;

                        // Track unique values (limit to 100 for performance)
                        if (count($columnStats[$colIndex]['unique_values']) < 100) {
                            $columnStats[$colIndex]['unique_values'][$value] = true;
                        }

                        // Detect data type
                        if (is_numeric($value)) {
                            $columnStats[$colIndex]['data_types']['numeric'] =
                                ($columnStats[$colIndex]['data_types']['numeric'] ?? 0) + 1;
                        } elseif (filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $columnStats[$colIndex]['data_types']['email'] =
                                ($columnStats[$colIndex]['data_types']['email'] ?? 0) + 1;
                        } elseif (preg_match('/^\d{4}-\d{2}-\d{2}/', $value)) {
                            $columnStats[$colIndex]['data_types']['date'] =
                                ($columnStats[$colIndex]['data_types']['date'] ?? 0) + 1;
                        } else {
                            $columnStats[$colIndex]['data_types']['text'] =
                                ($columnStats[$colIndex]['data_types']['text'] ?? 0) + 1;
                        }
                    } else {
                        $columnStats[$colIndex]['empty_count']++;
                    }
                }
            }

            if ($isEmpty) {
                $emptyRows++;
            }
        }

        // Calculate percentages and finalize column stats
        foreach ($columnStats as $index => &$stats) {
            $stats['unique_count'] = count($stats['unique_values']);
            $stats['fill_percentage'] = $totalRows > 0 ? round(($stats['filled_count'] / $totalRows) * 100, 2) : 0;

            // Determine primary data type
            if (!empty($stats['data_types'])) {
                $stats['primary_type'] = array_keys($stats['data_types'], max($stats['data_types']))[0];
            } else {
                $stats['primary_type'] = 'empty';
            }

            // Remove unique_values array to reduce response size
            unset($stats['unique_values']);
        }

        return [
            'file_info' => [
                'name' => $fileName,
                'size' => $fileSize,
                'size_formatted' => $this->formatFileSize($fileSize),
                'type' => $fileExtension,
                'uploaded_at' => now()->toDateTimeString()
            ],
            'data_summary' => [
                'total_rows' => $totalRows,
                'total_columns' => $totalColumns,
                'empty_rows' => $emptyRows,
                'data_rows' => $totalRows - $emptyRows,
                'duplicate_rows' => count($duplicateRows),
                'data_quality_score' => $this->calculateDataQualityScore($totalRows, $emptyRows, count($duplicateRows), $columnStats)
            ],
            'column_analysis' => $columnStats,
            'issues' => [
                'empty_rows' => $emptyRows,
                'duplicate_rows' => array_slice($duplicateRows, 0, 10), // Show first 10 duplicates
                'total_duplicates' => count($duplicateRows)
            ]
        ];
    }

    /**
     * Calculate data quality score
     *
     * @param int $totalRows
     * @param int $emptyRows
     * @param int $duplicateRows
     * @param array $columnStats
     * @return int
     */
    protected function calculateDataQualityScore(int $totalRows, int $emptyRows, int $duplicateRows, array $columnStats): int
    {
        if ($totalRows === 0) return 0;

        $score = 100;

        // Deduct for empty rows
        $emptyRowPenalty = ($emptyRows / $totalRows) * 30;
        $score -= $emptyRowPenalty;

        // Deduct for duplicate rows
        $duplicatePenalty = ($duplicateRows / $totalRows) * 20;
        $score -= $duplicatePenalty;

        // Deduct for columns with low fill rates
        $totalColumns = count($columnStats);
        if ($totalColumns > 0) {
            $avgFillRate = array_sum(array_column($columnStats, 'fill_percentage')) / $totalColumns;
            $fillRatePenalty = (100 - $avgFillRate) * 0.5;
            $score -= $fillRatePenalty;
        }

        return max(0, min(100, round($score)));
    }

    /**
     * Format file size
     *
     * @param int $bytes
     * @return string
     */
    protected function formatFileSize(int $bytes): string
    {
        if ($bytes === 0) return '0 Bytes';

        $k = 1024;
        $sizes = ['Bytes', 'KB', 'MB', 'GB'];
        $i = floor(log($bytes) / log($k));

        return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
    }

    /**
     * Get import history (placeholder - implement based on your logging needs)
     *
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function getImportHistory(int $page = 1, int $limit = 10): array
    {
        // This is a placeholder implementation
        // You might want to create an import_logs table to track imports
        return [
            'data' => [],
            'total' => 0,
            'page' => $page,
            'limit' => $limit,
        ];
    }
}
