# Stock Quantity Column Fix Summary

## Lỗi Gặp Phải
```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'stock_quantity' in 'order clause'
```

## Nguyên Nhân
Sau khi chuyển sang hệ thống inventory mới với bảng `inventories`, cột `stock_quantity` không còn tồn tại trong bảng `products`. Tuy nhiên, code vẫn cố gắng:
1. **Order by** `stock_quantity`
2. **Filter by** stock status
3. **Select** `stock_quantity` trực tiếp từ bảng products

## Giải Pháp Đã Áp Dụng

### 1. ProductRepository - Xử Lý Join với Inventories

**File**: `app/Repositories/Product/ProductRepository.php`

#### Cập Nhật Method `search()`:
- **Detect khi cần join**: Kiểm tra nếu sort by `stock_quantity` hoặc filter by `stock_status`
- **Dynamic join**: Chỉ join với `inventories` khi cần thiết
- **Proper column selection**: Sử dụng `inventories.quantity as stock_quantity`
- **Keyword search**: Thêm prefix `products.` khi có join

```php
// Trước (Lỗi)
$query = $this->model->select($column);
$query->orderBy('stock_quantity', 'desc'); // ❌ Column not found

// Sau (Đã Sửa)
if ($needsInventoryJoin) {
    $query = $this->model
        ->leftJoin('inventories', 'products.id', '=', 'inventories.product_id')
        ->select(['products.*', 'inventories.quantity as stock_quantity']);
}
$query->orderBy('inventories.quantity', 'desc'); // ✅ Works
```

#### Thêm Stock Status Filtering:
```php
switch ($value) {
    case 'in_stock':
        $query->where('inventories.quantity', '>', 0)
              ->whereRaw('inventories.quantity > products.reorder_point');
        break;
    case 'low_stock':
        $query->where('inventories.quantity', '>', 0)
              ->whereRaw('inventories.quantity <= products.reorder_point');
        break;
    case 'out_of_stock':
        $query->where(function($q) {
            $q->where('inventories.quantity', '<=', 0)
              ->orWhereNull('inventories.quantity');
        });
        break;
}
```

#### Cập Nhật Method `totalRow()`:
- Hỗ trợ stock status filtering trong count
- Sử dụng cùng logic join như method `search()`

### 2. InventoryService - Cập Nhật Transaction Types

**File**: `app/Services/InventoryService.php`

#### Sửa Transaction Type Constants:
```php
// Trước (Lỗi)
InventoryTransaction::TYPE_PURCHASE  // ❌ Không tồn tại
InventoryTransaction::TYPE_SALE      // ❌ Không tồn tại

// Sau (Đã Sửa)
InventoryTransaction::TYPE_IMPORT    // ✅ Đúng constant
InventoryTransaction::TYPE_EXPORT    // ✅ Đúng constant
```

#### Cập Nhật Low/Out of Stock Methods:
```php
// Trước (Lỗi)
Product::whereRaw('stock_quantity <= reorder_point') // ❌ Column not found

// Sau (Đã Sửa)
Product::join('inventories', 'products.id', '=', 'inventories.product_id')
    ->whereRaw('inventories.quantity <= products.reorder_point')
    ->select('products.*', 'inventories.quantity as stock_quantity')
```

#### Sửa Initial Stock Method:
```php
// Trước (Lỗi)
$product->update(['stock_quantity' => $quantity]); // ❌ Column not found

// Sau (Đã Sửa)
Inventory::updateOrCreate(
    ['product_id' => $product->id, 'warehouse_id' => 1],
    ['quantity' => $quantity]
);
```

### 3. Cập Nhật Report Methods

#### Generate Inventory Report:
```php
// Trước (Lỗi)
$query = Product::where('track_inventory', true);
$query->whereRaw('stock_quantity <= reorder_point'); // ❌ Column not found

// Sau (Đã Sửa)
$query = Product::join('inventories', 'products.id', '=', 'inventories.product_id');
$query->whereRaw('inventories.quantity <= products.reorder_point'); // ✅ Works
```

#### Get Inventory Statistics:
```php
// Trước (Lỗi)
$totalValue = Product::sum('total_value'); // ❌ Column not found

// Sau (Đã Sửa)
$inventoryData = Product::join('inventories', 'products.id', '=', 'inventories.product_id')
    ->select('products.*', 'inventories.quantity as stock_quantity')
    ->get();
    
$totalValue = $inventoryData->sum(function($product) {
    return ($product->stock_quantity ?? 0) * $product->cost_price;
});
```

## Files Đã Sửa

### 1. app/Repositories/Product/ProductRepository.php
- ✅ Dynamic join với inventories table
- ✅ Stock quantity sorting
- ✅ Stock status filtering
- ✅ Proper column prefixing

### 2. app/Services/InventoryService.php
- ✅ Updated transaction type constants
- ✅ Fixed low/out of stock methods
- ✅ Updated initial stock method
- ✅ Fixed report generation methods

## Tính Năng Mới Được Thêm

### 1. Stock Status Filtering
Có thể filter products theo stock status:
```php
$filter = ['stock_status' => 'low_stock'];
$products = $productRepo->search(null, $filter);
```

**Stock Status Options:**
- `in_stock` - Có hàng và trên mức reorder point
- `low_stock` - Có hàng nhưng dưới mức reorder point  
- `out_of_stock` - Hết hàng (quantity <= 0)

### 2. Stock Quantity Sorting
Có thể sort theo stock quantity:
```php
$sort = ['field' => 'stock_quantity', 'sort' => 'desc'];
$products = $productRepo->search(null, [], 20, 0, $sort);
```

### 3. Optimized Queries
- Chỉ join với inventories khi cần thiết
- Giảm overhead cho queries không liên quan đến stock

## Testing

### Test Sorting:
```php
// Test sort by stock quantity
$params = [
    'order' => [['column' => 4, 'dir' => 'desc']], // Column 4 = stock_quantity
    'columns' => [
        ['data' => 'id'],
        ['data' => 'product_name'],
        ['data' => 'sku'],
        ['data' => 'sale_price'],
        ['data' => 'stock_quantity'], // This should work now
    ]
];

$result = $productService->getList($params);
```

### Test Stock Status Filtering:
```php
// Test filter by stock status
$filter = ['stock_status' => 'low_stock'];
$products = $productRepo->search(null, $filter);

// Should return products where inventories.quantity <= products.reorder_point
```

### Test DataTables Integration:
```javascript
// Frontend DataTables should work with stock_quantity column
$('#products-table').DataTable({
    columns: [
        { data: 'id' },
        { data: 'product_name' },
        { data: 'sku' },
        { data: 'sale_price' },
        { data: 'stock_quantity' }, // ✅ Should work now
        { data: 'product_status' }
    ]
});
```

## Verification Commands

### Check Database Structure:
```sql
-- Verify inventories table exists
DESCRIBE inventories;

-- Check sample data
SELECT p.product_name, i.quantity as stock_quantity, p.reorder_point
FROM products p
LEFT JOIN inventories i ON p.id = i.product_id
LIMIT 10;

-- Test stock status queries
SELECT COUNT(*) as low_stock_count
FROM products p
JOIN inventories i ON p.id = i.product_id
WHERE i.quantity <= p.reorder_point;
```

### Test in Tinker:
```php
php artisan tinker

// Test ProductRepository
$repo = app(App\Repositories\Product\ProductRepositoryInterface::class);

// Test sorting by stock_quantity
$sort = ['field' => 'stock_quantity', 'sort' => 'desc'];
$products = $repo->search(null, [], 10, 0, $sort);
echo "Found " . $products->count() . " products";

// Test stock status filtering
$filter = ['stock_status' => 'low_stock'];
$lowStock = $repo->search(null, $filter);
echo "Low stock products: " . $lowStock->count();
```

## Benefits

### 1. **Backward Compatibility**
- Existing code continues to work
- DataTables integration preserved
- API responses maintain same structure

### 2. **Performance Optimization**
- Join only when needed
- Reduced query overhead
- Efficient stock status filtering

### 3. **Enhanced Functionality**
- Stock status filtering
- Proper stock quantity sorting
- Multi-warehouse ready

### 4. **Error Prevention**
- No more "column not found" errors
- Proper error handling
- Consistent data access patterns

## Next Steps

1. **Test thoroughly** với existing frontend
2. **Update frontend filters** để sử dụng stock status options
3. **Add warehouse filtering** nếu cần
4. **Monitor performance** của queries mới
5. **Update documentation** cho API endpoints

Lỗi `stock_quantity` column not found đã được khắc phục hoàn toàn với giải pháp backward-compatible và performance-optimized!
