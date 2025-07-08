# Hệ Thống Nhập Xuất Tồn Kho - Warehouse Inventory System

## Tổng Quan
Đã tạo hệ thống quản lý nhập xuất tồn kho hoàn chỉnh với hỗ trợ nhiều kho, theo dõi lịch sử giao dịch chi tiết và các chức năng quản lý tồn kho chuyên nghiệp.

## Cấu Trúc Database

### 1. Bảng warehouses
```sql
- id: khóa chính
- name: tên kho
- code: mã kho (unique)
- description: mô tả
- address: địa chỉ
- phone: số điện thoại
- email: email
- manager_name: tên quản lý
- status: trạng thái (active/inactive)
- is_default: kho mặc định
- created_at, updated_at: timestamps
```

### 2. Bảng inventories (đã cập nhật)
```sql
- id: khóa chính
- product_id: sản phẩm
- warehouse_id: kho (foreign key)
- quantity: số lượng tồn
- created_at, updated_at: timestamps
- unique(product_id, warehouse_id): một sản phẩm chỉ có một record per kho
```

### 3. Bảng inventory_transactions (đã cập nhật)
```sql
- id: khóa chính
- product_id: sản phẩm nào
- warehouse_id: kho nào (foreign key)
- transaction_type: loại giao dịch (import/export/transfer/adjustment/initial)
- quantity: số lượng thay đổi (+ hoặc -)
- old_quantity: số tồn trước giao dịch
- new_quantity: số tồn sau giao dịch
- unit_cost: giá nhập
- total_value: tổng giá trị
- notes: ghi chú
- created_by_user: người thực hiện
- created_at, updated_at: timestamps
```

## Models Đã Tạo/Cập Nhật

### 1. Warehouse Model
**File**: `app/Models/Warehouse.php`

#### Relationships:
- `inventories()` - hasMany với Inventory
- `inventoryTransactions()` - hasMany với InventoryTransaction

#### Scopes:
- `active()` - chỉ kho đang hoạt động
- `default()` - kho mặc định

#### Methods:
- `getTotalProductsAttribute()` - tổng số sản phẩm
- `getTotalQuantityAttribute()` - tổng số lượng
- `getTotalValueAttribute()` - tổng giá trị
- `getLowStockProducts()` - sản phẩm sắp hết
- `getOutOfStockProducts()` - sản phẩm hết hàng
- `getProductQuantity($productId)` - số lượng sản phẩm
- `hasStock($productId, $quantity)` - kiểm tra đủ hàng
- `getSummary()` - thống kê tổng quan
- `setAsDefault()` - đặt làm kho mặc định

### 2. Inventory Model (Cập Nhật)
**File**: `app/Models/Inventory.php`

#### Relationships:
- `warehouse()` - belongsTo với Warehouse

#### Static Methods:
- `getProductQuantityInWarehouse($productId, $warehouseId)` - số lượng trong kho cụ thể
- `updateProductQuantity($productId, $quantity, $warehouseId)` - cập nhật số lượng
- `addProductQuantity($productId, $quantity, $warehouseId)` - thêm số lượng
- `removeProductQuantity($productId, $quantity, $warehouseId)` - giảm số lượng

### 3. InventoryTransaction Model (Cập Nhật)
**File**: `app/Models/InventoryTransaction.php`

#### Transaction Types:
- `TYPE_IMPORT` = 'import' - Nhập kho
- `TYPE_EXPORT` = 'export' - Xuất kho
- `TYPE_TRANSFER` = 'transfer' - Chuyển kho
- `TYPE_ADJUSTMENT` = 'adjustment' - Điều chỉnh
- `TYPE_INITIAL` = 'initial' - Tồn đầu kỳ

#### Relationships:
- `warehouse()` - belongsTo với Warehouse

#### Scopes:
- `forWarehouse($warehouseId)` - theo kho

## Services

### WarehouseInventoryService
**File**: `app/Services/WarehouseInventoryService.php`

#### Chức Năng Chính:

**1. Nhập Kho - importInventory()**
```php
$result = $service->importInventory(
    $productId,      // ID sản phẩm
    $warehouseId,    // ID kho
    $quantity,       // Số lượng nhập
    $unitCost,       // Giá nhập (optional)
    $notes          // Ghi chú (optional)
);
```

**2. Xuất Kho - exportInventory()**
```php
$result = $service->exportInventory(
    $productId,      // ID sản phẩm
    $warehouseId,    // ID kho
    $quantity,       // Số lượng xuất
    $notes          // Ghi chú (optional)
);
```

**3. Chuyển Kho - transferInventory()**
```php
$result = $service->transferInventory(
    $productId,         // ID sản phẩm
    $fromWarehouseId,   // Kho nguồn
    $toWarehouseId,     // Kho đích
    $quantity,          // Số lượng chuyển
    $notes             // Ghi chú (optional)
);
```

**4. Điều Chỉnh Tồn Kho - adjustInventory()**
```php
$result = $service->adjustInventory(
    $productId,      // ID sản phẩm
    $warehouseId,    // ID kho
    $newQuantity,    // Số lượng mới
    $reason         // Lý do điều chỉnh (optional)
);
```

**5. Báo Cáo & Thống Kê:**
- `getTransactionHistory()` - Lịch sử giao dịch
- `getInventoryReport()` - Báo cáo tồn kho
- `getLowStockProducts()` - Sản phẩm sắp hết

## Controller

### InventoryController
**File**: `app/Http/Controllers/Admin/InventoryController.php`

#### Routes & Actions:
- `GET /admin/inventory` - Danh sách tồn kho
- `GET /admin/inventory/import` - Form nhập kho
- `POST /admin/inventory/import` - Xử lý nhập kho
- `GET /admin/inventory/export` - Form xuất kho
- `POST /admin/inventory/export` - Xử lý xuất kho
- `GET /admin/inventory/transfer` - Form chuyển kho
- `POST /admin/inventory/transfer` - Xử lý chuyển kho
- `GET /admin/inventory/adjust` - Form điều chỉnh
- `POST /admin/inventory/adjust` - Xử lý điều chỉnh
- `GET /admin/inventory/stock` - API lấy số lượng tồn
- `GET /admin/inventory/transactions` - Lịch sử giao dịch
- `GET /admin/inventory/report` - Báo cáo tồn kho

## Migrations

### 1. Create Warehouses Table
**File**: `database/migrations/2025_06_17_120000_create_warehouses_table.php`
- Tạo bảng warehouses
- Tự động tạo kho mặc định "Kho Chính"

### 2. Update Inventory Transactions Table
**File**: `database/migrations/2025_06_17_120001_update_inventory_transactions_table.php`
- Thêm warehouse_id
- Thêm old_quantity, new_quantity
- Cập nhật transaction_type enum

### 3. Update Inventories Table
**File**: `database/migrations/2025_06_17_120002_update_inventories_table.php`
- Thêm warehouse_id
- Thêm unique constraint (product_id, warehouse_id)
- Di chuyển dữ liệu hiện tại sang kho mặc định

## Workflow Sử Dụng

### 1. Nhập Kho
```php
// Nhập 100 sản phẩm vào kho chính
$result = $inventoryService->importInventory(
    productId: 1,
    warehouseId: 1,
    quantity: 100,
    unitCost: 50000,
    notes: 'Nhập hàng từ nhà cung cấp ABC'
);

// Kết quả:
// - Tăng inventory.quantity từ 0 lên 100
// - Tạo transaction record với type='import'
// - old_quantity=0, new_quantity=100, quantity=+100
```

### 2. Xuất Kho
```php
// Xuất 20 sản phẩm từ kho
$result = $inventoryService->exportInventory(
    productId: 1,
    warehouseId: 1,
    quantity: 20,
    notes: 'Xuất hàng cho đơn hàng #12345'
);

// Kết quả:
// - Giảm inventory.quantity từ 100 xuống 80
// - Tạo transaction record với type='export'
// - old_quantity=100, new_quantity=80, quantity=-20
```

### 3. Chuyển Kho
```php
// Chuyển 30 sản phẩm từ kho A sang kho B
$result = $inventoryService->transferInventory(
    productId: 1,
    fromWarehouseId: 1,
    toWarehouseId: 2,
    quantity: 30,
    notes: 'Chuyển hàng sang chi nhánh'
);

// Kết quả:
// - Kho A: giảm từ 80 xuống 50
// - Kho B: tăng từ 0 lên 30
// - Tạo 2 transaction records (xuất từ A, nhập vào B)
```

### 4. Điều Chỉnh Tồn Kho
```php
// Điều chỉnh tồn kho sau kiểm kê
$result = $inventoryService->adjustInventory(
    productId: 1,
    warehouseId: 1,
    newQuantity: 45,
    reason: 'Kiểm kê định kỳ - phát hiện thiếu 5 sản phẩm'
);

// Kết quả:
// - Điều chỉnh inventory.quantity từ 50 xuống 45
// - Tạo transaction record với type='adjustment'
// - old_quantity=50, new_quantity=45, quantity=-5
```

## Tính Năng Nổi Bật

### 1. Multi-Warehouse Support
- Hỗ trợ nhiều kho
- Theo dõi tồn kho riêng biệt cho từng kho
- Chuyển hàng giữa các kho

### 2. Complete Audit Trail
- Lưu trữ đầy đủ lịch sử giao dịch
- Theo dõi số lượng trước/sau mỗi giao dịch
- Ghi nhận người thực hiện và thời gian

### 3. Business Logic
- Kiểm tra đủ hàng trước khi xuất
- Tự động tính toán số lượng mới
- Validation đầy đủ

### 4. Reporting & Analytics
- Báo cáo tồn kho theo kho
- Danh sách sản phẩm sắp hết/hết hàng
- Thống kê tổng quan

### 5. API Support
- RESTful API cho tất cả operations
- JSON response với error handling
- AJAX-ready cho frontend

## Chạy Migrations

```bash
# Chạy tất cả migrations
php artisan migrate

# Hoặc chạy từng migration
php artisan migrate --path=database/migrations/2025_06_17_120000_create_warehouses_table.php
php artisan migrate --path=database/migrations/2025_06_17_120001_update_inventory_transactions_table.php
php artisan migrate --path=database/migrations/2025_06_17_120002_update_inventories_table.php
```

## Testing

```php
// Test nhập kho
$service = app(WarehouseInventoryService::class);
$result = $service->importInventory(1, 1, 100, 50000, 'Test import');

// Test xuất kho
$result = $service->exportInventory(1, 1, 20, 'Test export');

// Test chuyển kho
$result = $service->transferInventory(1, 1, 2, 30, 'Test transfer');

// Test điều chỉnh
$result = $service->adjustInventory(1, 1, 45, 'Test adjustment');
```

Hệ thống nhập xuất tồn kho đã hoàn thiện với đầy đủ chức năng quản lý kho chuyên nghiệp, hỗ trợ nhiều kho và theo dõi lịch sử chi tiết.
