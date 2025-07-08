# Hướng Dẫn Chạy Inventory Seeders

## Tổng Quan
Hệ thống seeders đã được tạo để sinh dữ liệu mẫu hoàn chỉnh cho hệ thống quản lý tồn kho, bao gồm:
- Products và Inventories cơ bản
- Inventory Transactions chi tiết
- Warehouses (kho) đa dạng
- Advanced transactions (chuyển kho, điều chỉnh, etc.)

## Thứ Tự Chạy Seeders

### 1. Chạy Migrations Trước
```bash
# Chạy tất cả migrations
php artisan migrate

# Hoặc chạy từng migration cụ thể
php artisan migrate --path=database/migrations/2025_06_17_120000_create_warehouses_table.php
php artisan migrate --path=database/migrations/2025_06_17_120001_update_inventory_transactions_table.php
php artisan migrate --path=database/migrations/2025_06_17_120002_update_inventories_table.php
```

### 2. Chạy Tất Cả Seeders
```bash
# Chạy tất cả seeders (bao gồm inventory)
php artisan db:seed

# Hoặc fresh migration + seed
php artisan migrate:fresh --seed
```

### 3. Chạy Từng Seeder Riêng Lẻ
```bash
# 1. Tạo users và pages cơ bản
php artisan db:seed --class=UsersTableSeeder
php artisan db:seed --class=PageSeeder

# 2. Tạo 20 products với inventory cơ bản
php artisan db:seed --class=ProductSeeder

# 3. Tạo inventory transactions chi tiết
php artisan db:seed --class=InventoryTransactionSeeder

# 4. Tạo thêm warehouses
php artisan db:seed --class=WarehouseSeeder

# 5. Tạo advanced transactions (chuyển kho, etc.)
php artisan db:seed --class=AdvancedInventoryTransactionSeeder
```

## Dữ Liệu Được Tạo

### 1. ProductSeeder
- **20 sản phẩm** chia thành 3 categories:
  - 8 Electronics (iPhone, Samsung, MacBook, etc.)
  - 7 Fashion (Nike, Adidas, Uniqlo, etc.)
  - 5 Home & Garden (IKEA, Dyson, Philips, etc.)
- **20 inventory records** với quantity = 0 ban đầu

### 2. InventoryTransactionSeeder
**Tạo transactions cho mỗi sản phẩm:**
- **Initial transactions** - Tồn đầu kỳ (10-200 units tùy sản phẩm)
- **Import transactions** - 2-3 lần nhập kho với số lượng khác nhau
- **Export transactions** - 2-4 lần xuất kho
- **Adjustment transactions** - 30% sản phẩm có điều chỉnh ngẫu nhiên

**Ví dụ cho iPhone 15 Pro Max:**
```
- Initial: 50 units (3 tháng trước)
- Import 1: +30 units (2.5 tháng trước)
- Export 1: -5 units (2 tháng trước)
- Import 2: +20 units (1.5 tháng trước)
- Export 2: -3 units (1 tháng trước)
- Export 3: -2 units (2 tuần trước)
- Adjustment: -2 units (1 tuần trước, lý do: kiểm kê)
- Final stock: 58 units
```

### 3. WarehouseSeeder
**Tạo 4 kho bổ sung:**
- **Kho Chi Nhánh Hà Nội** (HN01)
- **Kho Chi Nhánh TP.HCM** (HCM01)
- **Kho Trung Chuyển Đà Nẵng** (DN01)
- **Kho Hàng Lỗi** (DEFECT)

### 4. AdvancedInventoryTransactionSeeder
**Tạo các giao dịch nâng cao:**
- **Transfer transactions** - Chuyển hàng từ kho chính đến chi nhánh
- **Branch exports** - Xuất hàng từ các chi nhánh
- **Direct imports** - Nhập hàng trực tiếp vào chi nhánh
- **Inter-branch transfers** - Chuyển kho giữa các chi nhánh
- **Historical transactions** - Giao dịch với timestamps khác nhau

## Kết Quả Mong Đợi

### Database Records:
- **Products**: 20 records
- **Warehouses**: 5 records (1 main + 4 branches)
- **Inventories**: 40-60 records (products × warehouses có stock)
- **InventoryTransactions**: 150-200 records

### Transaction Types:
- **initial**: 20 transactions (tồn đầu kỳ)
- **import**: 60-80 transactions
- **export**: 40-60 transactions
- **transfer**: 20-30 transactions
- **adjustment**: 5-10 transactions

### Stock Distribution:
- **Kho Chính (MAIN)**: Phần lớn tồn kho
- **Chi nhánh HN01**: 10-20% tổng tồn kho
- **Chi nhánh HCM01**: 10-20% tổng tồn kho
- **Đà Nẵng (DN01)**: 5-10% tổng tồn kho
- **Kho lỗi (DEFECT)**: Ít hoặc không có

## Kiểm Tra Kết Quả

### 1. Kiểm Tra Database
```sql
-- Tổng số records
SELECT 'Products' as table_name, COUNT(*) as count FROM products
UNION ALL
SELECT 'Warehouses', COUNT(*) FROM warehouses
UNION ALL
SELECT 'Inventories', COUNT(*) FROM inventories
UNION ALL
SELECT 'Transactions', COUNT(*) FROM inventory_transactions;

-- Transactions theo loại
SELECT transaction_type, COUNT(*) as count 
FROM inventory_transactions 
GROUP BY transaction_type;

-- Tồn kho theo kho
SELECT w.name, w.code, COUNT(i.id) as products, SUM(i.quantity) as total_quantity
FROM warehouses w
LEFT JOIN inventories i ON w.id = i.warehouse_id
GROUP BY w.id, w.name, w.code;
```

### 2. Test API Endpoints
```bash
# Lấy thông tin tồn kho
curl "http://localhost/admin/inventory/stock?product_id=1&warehouse_id=1"

# Lấy lịch sử giao dịch
curl "http://localhost/admin/inventory/transactions?product_id=1"
```

### 3. Test Services
```php
// Test trong tinker
php artisan tinker

// Kiểm tra service
$service = app(App\Services\WarehouseInventoryService::class);
$history = $service->getTransactionHistory(1, 1, 10);
echo "Found " . $history->count() . " transactions";

// Kiểm tra inventory
$quantity = App\Models\Inventory::getProductQuantityInWarehouse(1, 1);
echo "Product 1 in Warehouse 1: {$quantity} units";
```

## Troubleshooting

### Lỗi Thường Gặp:

1. **"Default warehouse not found"**
   ```bash
   # Chạy warehouse migration trước
   php artisan migrate --path=database/migrations/2025_06_17_120000_create_warehouses_table.php
   ```

2. **"No products found"**
   ```bash
   # Chạy ProductSeeder trước
   php artisan db:seed --class=ProductSeeder
   ```

3. **"Foreign key constraint fails"**
   ```bash
   # Chạy migrations theo đúng thứ tự
   php artisan migrate:fresh
   php artisan db:seed
   ```

4. **"Class not found"**
   ```bash
   # Clear cache và autoload
   php artisan cache:clear
   php artisan config:clear
   composer dump-autoload
   ```

## Customization

### Thay Đổi Số Lượng Sản Phẩm:
Sửa file `InventoryTransactionSeeder.php`, mảng `$stockData`:
```php
// Tăng stock cho iPhone
['initial' => 100, 'imports' => [50, 30], 'exports' => [10, 8, 5]],
```

### Thêm Warehouses:
Sửa file `WarehouseSeeder.php`, thêm vào mảng `$warehouses`:
```php
[
    'name' => 'Kho Mới',
    'code' => 'NEW01',
    'description' => 'Mô tả kho mới',
    // ...
]
```

### Thay Đổi Transaction Logic:
Sửa file `AdvancedInventoryTransactionSeeder.php` để thêm logic nghiệp vụ riêng.

## Kết Luận

Sau khi chạy xong tất cả seeders, bạn sẽ có:
- Hệ thống tồn kho hoàn chỉnh với 5 kho
- Lịch sử giao dịch chi tiết cho 3 tháng
- Dữ liệu realistic để test và demo
- Foundation để phát triển thêm tính năng

Chạy `php artisan migrate:fresh --seed` để có trải nghiệm hoàn chỉnh!
