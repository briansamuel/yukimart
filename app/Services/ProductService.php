<?php
namespace App\Services;

use App\Repositories\Product\ProductRepositoryInterface;
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ProductService
{
    protected $productRepo;

    public function __construct(ProductRepositoryInterface $productRepo)
    {
        $this->productRepo = $productRepo;
    }

    public function totalRows($params) {
        $result = $this->productRepo->count($params);
        return $result;
    }

    public function getMany($limit, $offset, $filter)
    {
        $result = $this->productRepo->getMany($limit, $offset, $filter);
        return $result ? $result : [];
    }

    public function searchProducts($keyword)
    {
        $filter = array('product_status' => 'publish');
        $result = $this->productRepo->search($keyword, $filter);
        return $result ? $result : [];
    }

    public function findByKey($key, $value)
    {
        $result = $this->productRepo->findByKey($key, $value);
        return $result ? $result : [];
    }

    /**
     * Get detailed product information for product detail page
     */
    public function getProductDetail($id)
    {
        $product = $this->productRepo->findByKey('id', $id);

        if (!$product) {
            return null;
        }

        // Load relationships
        $product->load([
            'inventory',
            'inventoryTransactions' => function($query) {
                $query->latest()->limit(10);
            },
            'category',
            'createdByUser:id,name',
            'updatedByUser:id,name'
        ]);

        // Calculate stock information
        $stockQuantity = $product->inventory ? $product->inventory->quantity : 0;
        $reservedQuantity = $product->inventory ? $product->inventory->reserved_quantity : 0;
        $availableQuantity = $stockQuantity - $reservedQuantity;

        // Determine stock status
        $stockStatus = 'out_of_stock';
        if ($stockQuantity > $product->reorder_point) {
            $stockStatus = 'in_stock';
        } elseif ($stockQuantity > 0) {
            $stockStatus = 'low_stock';
        }

        // Calculate profit margin
        $profitMargin = 0;
        if ($product->cost_price > 0) {
            $profitMargin = (($product->sale_price - $product->cost_price) / $product->cost_price) * 100;
        }

        // Add computed fields
        $product->stock_quantity = $stockQuantity;
        $product->reserved_quantity = $reservedQuantity;
        $product->available_quantity = $availableQuantity;
        $product->stock_status = $stockStatus;
        $product->profit_margin = round($profitMargin, 2);
        $product->stock_value = $stockQuantity * $product->cost_price;
        $product->retail_value = $stockQuantity * $product->sale_price;

        return $product;
    }

    public function findBySku($sku)
    {
        $condition['sku'] = $sku;
        $condition['product_status'] = 'publish';
        $result = $this->productRepo->findByCondition($condition);
        return $result ? $result : [];
    }

    public function insert($params)
    {
        $insert['product_name'] = $params['product_name'];

        // Generate slug with proper Unicode support
        $slugSource = isset($params['product_slug']) ? $params['product_slug'] : $params['product_name'];
        $insert['product_slug'] = $this->generateUniqueSlug($slugSource);

        $insert['product_description'] = $params['product_description'];
        $insert['product_content'] = $params['product_content'];
        $insert['sku'] = $params['sku'];
        $insert['barcode'] = isset($params['barcode']) ? $params['barcode'] : '';
        $insert['product_type'] = isset($params['product_type']) ? $params['product_type'] : 'simple';
        $insert['brand'] = isset($params['brand']) ? $params['brand'] : '';
        $insert['cost_price'] = $params['cost_price'];
        $insert['sale_price'] = $params['sale_price'];
        $insert['reorder_point'] = isset($params['reorder_point']) ? $params['reorder_point'] : 0;
        $insert['weight'] = isset($params['weight']) ? $params['weight'] : null;
        $insert['points'] = isset($params['points']) ? $params['points'] : 0;
        $insert['location'] = isset($params['location']) ? $params['location'] : '';

        // Handle thumbnail URL parsing
        if (isset($params['product_thumbnail'])) {
            $url_p = parse_url($params['product_thumbnail']);
            $insert['product_thumbnail'] = isset($url_p['path']) ? $url_p['path'] : $params['product_thumbnail'];
        }

        $insert['product_status'] = $params['product_status'];
        $insert['product_feature'] = isset($params['product_feature']) ? $params['product_feature'] : 0;
        $insert['language'] = isset($params['language']) ? $params['language'] : 'vi';
        $insert['created_by_user'] = isset($params['created_by_user']) ? $params['created_by_user'] : 0;
        $insert['updated_by_user'] = isset($params['updated_by_user']) ? $params['updated_by_user'] : 0;
        $insert['created_at'] = isset($params['created_at']) ? $params['created_at'] : date("Y-m-d H:i:s");
        $insert['updated_at'] = date("Y-m-d H:i:s");

        return $this->productRepo->create($insert);
    }

    public function update($id, $params)
    {
        $update['product_name'] = $params['product_name'];

        // Generate slug with proper Unicode support
        $slugSource = isset($params['product_slug']) ? $params['product_slug'] : $params['product_name'];
        $update['product_slug'] = $this->generateUniqueSlug($slugSource, $id);

        $update['product_description'] = $params['product_description'];
        $update['product_content'] = $params['product_content'];
        $update['sku'] = $params['sku'];
        $update['barcode'] = isset($params['barcode']) ? $params['barcode'] : '';
        $update['product_type'] = isset($params['product_type']) ? $params['product_type'] : 'simple';
        $update['brand'] = isset($params['brand']) ? $params['brand'] : '';
        $update['cost_price'] = $params['cost_price'];
        $update['sale_price'] = $params['sale_price'];
        $update['reorder_point'] = isset($params['reorder_point']) ? $params['reorder_point'] : 0;
        $update['weight'] = isset($params['weight']) ? $params['weight'] : null;
        $update['points'] = isset($params['points']) ? $params['points'] : 0;
        $update['location'] = isset($params['location']) ? $params['location'] : '';
        $update['product_thumbnail'] = isset($params['product_thumbnail']) ? $params['product_thumbnail'] : '';
        $update['product_status'] = $params['product_status'];
        $update['product_feature'] = isset($params['product_feature']) ? $params['product_feature'] : 0;
        $update['language'] = isset($params['language']) ? $params['language'] : 'vi';
        $update['updated_by_user'] = isset($params['updated_by_user']) ? $params['updated_by_user'] : 0;
        $update['updated_at'] = date("Y-m-d H:i:s");

        return $this->productRepo->update($id, $update);
    }

    public function updateMany($ids, $data)
    {
        return $this->productRepo->update($ids, $data);
    }

    /**
     * Update product with partial data (only provided fields)
     */
    public function updatePartial($id, $params)
    {
        $update = [];

        // Only update fields that are provided
        if (isset($params['product_name'])) {
            $update['product_name'] = $params['product_name'];
            $slugSource = isset($params['product_slug']) ? $params['product_slug'] : $params['product_name'];
            $update['product_slug'] = $this->generateUniqueSlug($slugSource, $id);
        }

        if (isset($params['product_description'])) {
            $update['product_description'] = $params['product_description'];
        }

        if (isset($params['product_content'])) {
            $update['product_content'] = $params['product_content'];
        }

        if (isset($params['sku'])) {
            $update['sku'] = $params['sku'];
        }

        if (isset($params['barcode'])) {
            $update['barcode'] = $params['barcode'];
        }

        if (isset($params['product_type'])) {
            $update['product_type'] = $params['product_type'];
        }

        if (isset($params['brand'])) {
            $update['brand'] = $params['brand'];
        }

        if (isset($params['cost_price'])) {
            $update['cost_price'] = $params['cost_price'];
        }

        if (isset($params['sale_price'])) {
            $update['sale_price'] = $params['sale_price'];
        }

        if (isset($params['regular_price'])) {
            $update['regular_price'] = $params['regular_price'];
        }

        if (isset($params['reorder_point'])) {
            $update['reorder_point'] = $params['reorder_point'];
        }

        if (isset($params['weight'])) {
            $update['weight'] = $params['weight'];
        }

        if (isset($params['points'])) {
            $update['points'] = $params['points'];
        }

        if (isset($params['location'])) {
            $update['location'] = $params['location'];
        }

        if (isset($params['product_thumbnail'])) {
            $update['product_thumbnail'] = $params['product_thumbnail'];
        }

        if (isset($params['product_status'])) {
            $update['product_status'] = $params['product_status'];
        }

        if (isset($params['product_feature'])) {
            $update['product_feature'] = $params['product_feature'];
        }

        if (isset($params['language'])) {
            $update['language'] = $params['language'];
        }

        // Always update these fields
        if (isset($params['updated_by_user'])) {
            $update['updated_by_user'] = $params['updated_by_user'];
        }

        $update['updated_at'] = date("Y-m-d H:i:s");

        return $this->productRepo->update($id, $update);
    }

    public function deleteMany($ids)
    {
        return $this->productRepo->deleteMany($ids);
    }

    public function delete($id)
    {
        return $this->productRepo->delete($id);
    }

    public function getList($params = [], $column = ['*'])
    {
        $search = $params['search'] ?? ['value' => ''];
        $keyword = $search['value'] ?? '';

        $limit = isset($params['length']) ? $params['length'] : 20;
        $offset = isset($params['start']) ? $params['start'] : 0;
        $sort = [];
        $filter = [];

        // Handle column-specific filters
        if (isset($params['columns']) && is_array($params['columns'])) {
            foreach ($params['columns'] as $column_data) {
                if (isset($column_data['search']['value']) && !empty($column_data['search']['value'])) {
                    $columnName = $column_data['data'] ?? '';
                    $searchValue = $column_data['search']['value'];

                    switch ($columnName) {
                        case 'product_status':
                            $filter['product_status'] = $searchValue;
                            break;
                        case 'product_type':
                            $filter['product_type'] = $searchValue;
                            break;
                        case 'brand':
                            $filter['brand'] = $searchValue;
                            break;
                        case 'created_at':
                            $filter['created_at'] = $searchValue;
                            break;
                    }
                }
            }
        }

        // Stock status filter (from custom parameter)
        if (isset($params['stock_status']) && !empty($params['stock_status'])) {
            $filter['stock_status'] = $params['stock_status'];
        }

        // Handle sorting
        if (isset($params['order'][0]['column']) && isset($params['columns'])) {
            $column_index = intval($params['order'][0]['column']);
            if (isset($params['columns'][$column_index]['data'])) {
                $sort['field'] = $params['columns'][$column_index]['data'];
                $sort['sort'] = $params['order'][0]['dir'] ?? 'desc';
            }
        }

        // Default sorting
        if (empty($sort)) {
            $sort['field'] = 'id';
            $sort['sort'] = 'desc';
        }

        // Use basic product columns since stock_quantity comes from inventory relationship
        if ($column === ['*']) {
            $column = [
                'id', 'product_name', 'product_slug', 'sku', 'product_status',
                'product_type', 'product_thumbnail' ,'brand', 'cost_price', 'sale_price',
                'reorder_point', 'product_feature', 'created_at', 'updated_at'
            ];
        }

        // Get products with inventory relationship
        $result = $this->productRepo->search($keyword, $filter, $limit, $offset, $sort, $column);

        // Load inventory relationship for all products
        $result->load('inventory');

        $total = $this->productRepo->totalRow($keyword, $filter);

        // Transform data for DataTables with inventory information
        $transformedData = $result->map(function($product) {
            // Get stock quantity from inventory relationship
            $stockQuantity = $product->stock_quantity ?? 0;
            $reorderPoint = $product->reorder_point ?? 0;

            // Determine stock status
            $stockStatus = $this->getStockStatus($stockQuantity, $reorderPoint);

            // Determine product status badge
            $statusBadge = $this->getStatusBadge($product->product_status);

            return [
                'id' => $product->id,
                'product_name' => $product->product_name,
                'product_slug' => $product->product_slug ?? '',
                'sku' => $product->sku,
                'sale_price' => $product->sale_price,
                'stock_quantity' => $stockQuantity,
                'stock_status' => $stockStatus,
                'product_status' => $product->product_status,
                'badge_status' => $statusBadge,
                'product_type' => $product->product_type ?? 'simple',
                'product_thumbnail' => $product->product_thumbnail,
                'product_edit_url' => route('admin.products.edit', $product->id),
                'created_at' => $product->created_at,
                'updated_at' => $product->updated_at,
                // Additional data for expansion
                'cost_price' => $product->cost_price ?? 0,
                'regular_price' => $product->regular_price ?? 0,
                'reorder_point' => $reorderPoint,
                'weight' => $product->weight,
                'dimensions' => $product->dimensions,
                'barcode' => $product->barcode,
                'product_description' => $product->product_description,
            ];
        });

        $data['data'] = $transformedData;
        $data['recordsTotal'] = $total;
        $data['recordsFiltered'] = $total;

        return $data;
    }

    public function getListIDs($data) {
        $ids = array();
        foreach($data as $row) {
            array_push($ids, $row->id);
        }
        return $ids;
    }

    public function takeNew($quantity, $filter)
    {
        return $this->productRepo->takeNew($quantity, $filter);
    }

    public function takeFeatured($quantity, $filter)
    {
        $filter['product_feature'] = 1;
        return $this->productRepo->takeNew($quantity, $filter);
    }

    public function checkSkuExists($sku, $excludeId = null)
    {
        $query = Product::where('sku', $sku);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        return $query->exists();
    }

    public function updateStock($id, $quantity, $operation = 'add')
    {
        $product = $this->findByKey('id', $id);
        if ($product) {
            $currentStock = $product->stock_quantity ?? 0;
            $newStock = $operation === 'add' ? $currentStock + $quantity : $currentStock - $quantity;
            $newStock = max(0, $newStock); // Ensure stock doesn't go negative

            $result = $this->productRepo->update($id, [
                'stock_quantity' => $newStock,
                'last_stock_update' => now()
            ]);

            if ($result) {
                // Check for stock alerts after update
                $product->refresh();
                $product->checkStockAlerts();
            }

            return $result;
        }
        return false;
    }

    /**
     * Get products with low stock
     */
    public function getLowStockProducts($limit = null)
    {
        $query = Product::join('inventories', 'products.id', '=', 'inventories.product_id')
            ->whereRaw('inventories.quantity <= products.reorder_point')
            ->where('products.product_status', 'publish')
            ->select('products.*', 'inventories.quantity as stock_quantity');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * Get products that are out of stock
     */
    public function getOutOfStockProducts($limit = null)
    {
        $query = Product::join('inventories', 'products.id', '=', 'inventories.product_id')
            ->where('inventories.quantity', '<=', 0)
            ->where('products.product_status', 'publish')
            ->select('products.*', 'inventories.quantity as stock_quantity');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * Get inventory summary
     */
    public function getInventorySummary()
    {
        $totalProducts = Product::count();
        $lowStockCount = $this->getLowStockProducts()->count();
        $outOfStockCount = $this->getOutOfStockProducts()->count();
        $totalValue = Product::join('inventories', 'products.id', '=', 'inventories.product_id')
            ->selectRaw('SUM(inventories.quantity * products.cost_price) as total_value')
            ->value('total_value') ?? 0;

        return [
            'total_products' => $totalProducts,
            'low_stock_count' => $lowStockCount,
            'out_of_stock_count' => $outOfStockCount,
            'in_stock_count' => $totalProducts - $lowStockCount - $outOfStockCount,
            'total_value' => $totalValue,
            'stock_health_percentage' => $totalProducts > 0 ?
                round((($totalProducts - $lowStockCount - $outOfStockCount) / $totalProducts) * 100, 1) : 100
        ];
    }

    /**
     * Reserve stock for a product
     */
    public function reserveStock($id, $quantity, $reference = null)
    {
        $product = $this->findByKey('id', $id);
        if ($product) {
            return $product->reserveStock($quantity, $reference);
        }
        return false;
    }

    /**
     * Release reserved stock for a product
     */
    public function releaseStock($id, $quantity, $reference = null)
    {
        $product = $this->findByKey('id', $id);
        if ($product) {
            return $product->releaseStock($quantity, $reference);
        }
        return false;
    }

    /**
     * Check if product can fulfill order quantity
     */
    public function canFulfillOrder($id, $quantity)
    {
        $product = $this->findByKey('id', $id);
        if ($product) {
            return $product->canOrder($quantity);
        }
        return false;
    }

    /**
     * Get products that need reordering
     */
    public function getProductsNeedingReorder()
    {
        return Product::join('inventories', 'products.id', '=', 'inventories.product_id')
            ->whereRaw('inventories.quantity <= products.reorder_point')
            ->where('products.product_status', 'publish')
            ->select('products.*', 'inventories.quantity as stock_quantity')
            ->with(['inventoryTransactions' => function($query) {
                $query->latest()->limit(5);
            }])
            ->get();
    }

    /**
     * Get stock status information
     */
    protected function getStockStatus($stockQuantity, $reorderPoint)
    {
        if ($stockQuantity <= 0) {
            return [
                'status' => 'out_of_stock',
                'label' => 'Out of Stock',
                'class' => 'danger'
            ];
        } elseif ($stockQuantity <= $reorderPoint) {
            return [
                'status' => 'low_stock',
                'label' => 'Low Stock',
                'class' => 'warning'
            ];
        } else {
            return [
                'status' => 'in_stock',
                'label' => 'In Stock',
                'class' => 'success'
            ];
        }
    }

    /**
     * Get product status badge HTML
     */
    protected function getStatusBadge($status)
    {
        $badges = [
            'publish' => '<span class="badge badge-light-success">Published</span>',
            'pending' => '<span class="badge badge-light-warning">Pending</span>',
            'draft' => '<span class="badge badge-light-info">Draft</span>',
            'trash' => '<span class="badge badge-light-danger">Trash</span>',
        ];

        return $badges[$status] ?? '<span class="badge badge-light-secondary">' . ucfirst($status) . '</span>';
    }

    /**
     * Duplicate a product
     */
    public function duplicate($id)
    {
        $originalProduct = $this->findByKey('id', $id);
        if (!$originalProduct) {
            return false;
        }

        // Prepare data for duplication
        $duplicateData = [
            'product_name' => $originalProduct->product_name . ' (Copy)',
            'product_slug' => Str::slug($originalProduct->product_name . ' copy ' . time()),
            'product_description' => $originalProduct->product_description,
            'product_content' => $originalProduct->product_content,
            'sku' => $this->generateUniqueSku($originalProduct->sku),
            'barcode' => '', // Clear barcode for duplicate
            'product_type' => $originalProduct->product_type,
            'brand' => $originalProduct->brand,
            'cost_price' => $originalProduct->cost_price,
            'sale_price' => $originalProduct->sale_price,
            'regular_price' => $originalProduct->regular_price ?? $originalProduct->sale_price,
            'reorder_point' => $originalProduct->reorder_point,
            'weight' => $originalProduct->weight,
            'dimensions' => $originalProduct->dimensions,
            'points' => $originalProduct->points,
            'location' => $originalProduct->location,
            'product_thumbnail' => $originalProduct->product_thumbnail,
            'product_status' => 'draft', // Set as draft by default
            'product_feature' => 0, // Not featured by default
            'language' => $originalProduct->language,
            'created_by_user' => auth()->id(),
            'updated_by_user' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now()
        ];

        // Create the duplicate product
        $duplicatedProduct = $this->productRepo->create($duplicateData);

        if ($duplicatedProduct) {
            // Create inventory record with 0 quantity
            $duplicatedProduct->inventory()->create([
                'quantity' => 0,
                'reserved_quantity' => 0,
                'warehouse_id' => 1, // Default warehouse
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        return $duplicatedProduct;
    }

    /**
     * Generate unique SKU for duplicate product
     */
    protected function generateUniqueSku($originalSku)
    {
        $baseSku = $originalSku;
        $counter = 1;
        $newSku = $baseSku . '-COPY';

        // Keep trying until we find a unique SKU
        while ($this->checkSkuExists($newSku)) {
            $counter++;
            $newSku = $baseSku . '-COPY-' . $counter;
        }

        return $newSku;
    }

    /**
     * Adjust product stock
     */
    public function adjustStock($id, $params)
    {
        try {
            $product = $this->findByKey('id', $id);
            if (!$product) {
                return [
                    'success' => false,
                    'message' => 'Product not found',
                    'errors' => ['Product does not exist']
                ];
            }

            $adjustmentType = $params['adjustment_type'];
            $quantity = (int) $params['quantity'];
            $reference = $params['reference'] ?? null;
            $notes = $params['notes'] ?? null;

            if ($quantity <= 0) {
                return [
                    'success' => false,
                    'message' => 'Quantity must be greater than 0',
                    'errors' => ['Invalid quantity provided']
                ];
            }

            // Get current inventory
            $inventory = $product->inventory;
            if (!$inventory) {
                // Create inventory record if it doesn't exist
                $inventory = $product->inventory()->create([
                    'quantity' => 0,
                    'reserved_quantity' => 0,
                    'warehouse_id' => 1, // Default warehouse
                ]);
            }

            $currentStock = $inventory->quantity;
            $newStock = $currentStock;

            // Calculate new stock based on adjustment type
            switch ($adjustmentType) {
                case 'increase':
                    $newStock = $currentStock + $quantity;
                    $transactionType = 'in';
                    break;
                case 'decrease':
                    $newStock = $currentStock - $quantity;
                    $transactionType = 'out';
                    break;
                case 'set':
                    $newStock = $quantity;
                    $transactionType = $quantity > $currentStock ? 'in' : 'out';
                    $quantity = abs($quantity - $currentStock);
                    break;
                default:
                    return [
                        'success' => false,
                        'message' => 'Invalid adjustment type',
                        'errors' => ['Adjustment type must be increase, decrease, or set']
                    ];
            }

            // Ensure stock doesn't go negative
            if ($newStock < 0) {
                return [
                    'success' => false,
                    'message' => 'Insufficient stock for this adjustment',
                    'errors' => ['Cannot reduce stock below 0']
                ];
            }

            // Update inventory
            $inventory->update(['quantity' => $newStock]);

            // Create inventory transaction record
            $product->inventoryTransactions()->create([
                'type' => $transactionType,
                'quantity' => $quantity,
                'reference' => $reference,
                'notes' => $notes,
                'user_id' => auth()->id(),
                'warehouse_id' => $inventory->warehouse_id,
                'created_at' => now()
            ]);

            return [
                'success' => true,
                'message' => 'Stock adjusted successfully',
                'data' => [
                    'product_id' => $id,
                    'old_stock' => $currentStock,
                    'new_stock' => $newStock,
                    'adjustment_type' => $adjustmentType,
                    'quantity' => $quantity
                ]
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to adjust stock: ' . $e->getMessage(),
                'errors' => ['System error occurred']
            ];
        }
    }

    /**
     * Generate unique slug with Unicode support
     */
    protected function generateUniqueSlug($title, $excludeId = null)
    {
        // Convert Vietnamese characters to ASCII equivalents
        $slug = $this->convertVietnameseToAscii($title);

        // Generate base slug
        $baseSlug = Str::slug($slug, '-', 'en');

        // If slug is empty (all special characters), use fallback
        if (empty($baseSlug)) {
            $baseSlug = 'product-' . time();
        }

        $finalSlug = $baseSlug;
        $counter = 1;

        // Check if slug exists and make it unique
        while ($this->checkSlugExists($finalSlug, $excludeId)) {
            $finalSlug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $finalSlug;
    }

    /**
     * Convert Vietnamese characters to ASCII
     */
    protected function convertVietnameseToAscii($str)
    {
        $vietnamese = [
            'à', 'á', 'ạ', 'ả', 'ã', 'â', 'ầ', 'ấ', 'ậ', 'ẩ', 'ẫ', 'ă', 'ằ', 'ắ', 'ặ', 'ẳ', 'ẵ',
            'è', 'é', 'ẹ', 'ẻ', 'ẽ', 'ê', 'ề', 'ế', 'ệ', 'ể', 'ễ',
            'ì', 'í', 'ị', 'ỉ', 'ĩ',
            'ò', 'ó', 'ọ', 'ỏ', 'õ', 'ô', 'ồ', 'ố', 'ộ', 'ổ', 'ỗ', 'ơ', 'ờ', 'ớ', 'ợ', 'ở', 'ỡ',
            'ù', 'ú', 'ụ', 'ủ', 'ũ', 'ư', 'ừ', 'ứ', 'ự', 'ử', 'ữ',
            'ỳ', 'ý', 'ỵ', 'ỷ', 'ỹ',
            'đ',
            'À', 'Á', 'Ạ', 'Ả', 'Ã', 'Â', 'Ầ', 'Ấ', 'Ậ', 'Ẩ', 'Ẫ', 'Ă', 'Ằ', 'Ắ', 'Ặ', 'Ẳ', 'Ẵ',
            'È', 'É', 'Ẹ', 'Ẻ', 'Ẽ', 'Ê', 'Ề', 'Ế', 'Ệ', 'Ể', 'Ễ',
            'Ì', 'Í', 'Ị', 'Ỉ', 'Ĩ',
            'Ò', 'Ó', 'Ọ', 'Ỏ', 'Õ', 'Ô', 'Ồ', 'Ố', 'Ộ', 'Ổ', 'Ỗ', 'Ơ', 'Ờ', 'Ớ', 'Ợ', 'Ở', 'Ỡ',
            'Ù', 'Ú', 'Ụ', 'Ủ', 'Ũ', 'Ư', 'Ừ', 'Ứ', 'Ự', 'Ử', 'Ữ',
            'Ỳ', 'Ý', 'Ỵ', 'Ỷ', 'Ỹ',
            'Đ'
        ];

        $ascii = [
            'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a',
            'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e',
            'i', 'i', 'i', 'i', 'i',
            'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o',
            'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u',
            'y', 'y', 'y', 'y', 'y',
            'd',
            'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A',
            'E', 'E', 'E', 'E', 'E', 'E', 'E', 'E', 'E', 'E', 'E',
            'I', 'I', 'I', 'I', 'I',
            'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O',
            'U', 'U', 'U', 'U', 'U', 'U', 'U', 'U', 'U', 'U', 'U',
            'Y', 'Y', 'Y', 'Y', 'Y',
            'D'
        ];

        return str_replace($vietnamese, $ascii, $str);
    }

    /**
     * Check if slug exists
     */
    protected function checkSlugExists($slug, $excludeId = null)
    {
        $query = Product::where('product_slug', $slug);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        return $query->exists();
    }

    /**
     * Get product history/activity log
     */
    public function getHistory($id)
    {
        // Get product change history from logs_user table
        $history = DB::table('logs_user')
            ->where('action', 'like', '%Product%')
            ->where('action', 'like', '%' . $id . '%')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->map(function($log) {
                $content = json_decode($log->content, true);

                // Determine action type
                $actionType = 'updated';
                if (strpos($log->action, 'add') !== false || strpos($log->action, 'create') !== false) {
                    $actionType = 'created';
                } elseif (strpos($log->action, 'delete') !== false) {
                    $actionType = 'deleted';
                } elseif (strpos($log->action, 'duplicate') !== false) {
                    $actionType = 'duplicated';
                } elseif (strpos($log->action, 'status') !== false) {
                    $actionType = 'status_change';
                }

                return [
                    'id' => $log->id,
                    'type' => $actionType,
                    'title' => $this->formatHistoryTitle($log->action),
                    'description' => $this->formatHistoryDescription($log->action, $content),
                    'user_id' => $log->user_id,
                    'ip_address' => $log->ip,
                    'created_at' => $log->created_at,
                    'content' => $content
                ];
            });

        // Add inventory transaction history
        $inventoryHistory = DB::table('inventory_transactions')
            ->join('products', 'inventory_transactions.product_id', '=', 'products.id')
            ->where('inventory_transactions.product_id', $id)
            ->select([
                'inventory_transactions.*',
                'products.product_name'
            ])
            ->orderBy('inventory_transactions.created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function($transaction) {
                return [
                    'id' => 'inv_' . $transaction->id,
                    'type' => 'stock_change',
                    'title' => 'Stock ' . ucfirst($transaction->type),
                    'description' => $this->formatStockChangeDescription($transaction),
                    'user_id' => $transaction->user_id ?? null,
                    'ip_address' => null,
                    'created_at' => $transaction->created_at,
                    'content' => [
                        'transaction_type' => $transaction->type,
                        'quantity' => $transaction->quantity,
                        'reference' => $transaction->reference,
                        'notes' => $transaction->notes
                    ]
                ];
            });

        // Merge and sort all history
        $allHistory = $history->concat($inventoryHistory)
            ->sortByDesc('created_at')
            ->values()
            ->take(30);

        return $allHistory;
    }

    /**
     * Format history title
     */
    protected function formatHistoryTitle($action)
    {
        if (strpos($action, 'add') !== false || strpos($action, 'create') !== false) {
            return 'Product Created';
        } elseif (strpos($action, 'update') !== false) {
            return 'Product Updated';
        } elseif (strpos($action, 'delete') !== false) {
            return 'Product Deleted';
        } elseif (strpos($action, 'duplicate') !== false) {
            return 'Product Duplicated';
        } elseif (strpos($action, 'status') !== false) {
            return 'Status Changed';
        }

        return 'Product Modified';
    }

    /**
     * Format history description
     */
    protected function formatHistoryDescription($action, $content)
    {
        if (strpos($action, 'status') !== false && isset($content['old_status'], $content['new_status'])) {
            return "Status changed from '{$content['old_status']}' to '{$content['new_status']}'";
        } elseif (strpos($action, 'duplicate') !== false && isset($content['new_id'])) {
            return "Product duplicated to new product ID {$content['new_id']}";
        } elseif (strpos($action, 'update') !== false && isset($content['updated_fields'])) {
            $fields = is_array($content['updated_fields']) ? implode(', ', $content['updated_fields']) : 'multiple fields';
            return "Updated: {$fields}";
        }

        return $action;
    }

    /**
     * Format stock change description
     */
    protected function formatStockChangeDescription($transaction)
    {
        $type = ucfirst($transaction->type);
        $quantity = $transaction->quantity;
        $reference = $transaction->reference ? " (Ref: {$transaction->reference})" : '';

        return "{$type} {$quantity} units{$reference}";
    }
}
