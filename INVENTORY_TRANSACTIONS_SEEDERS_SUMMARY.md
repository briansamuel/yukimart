# Inventory Transactions Seeders - Tổng Hợp

## Tổng Quan
Đã tạo thành công hệ thống seeders hoàn chỉnh để sinh dữ liệu mẫu cho hệ thống quản lý tồn kho, bao gồm products, warehouses, inventories và inventory transactions với lịch sử chi tiết.

## Seeders Đã Tạo

### 1. InventoryTransactionSeeder
**File**: `database/seeders/InventoryTransactionSeeder.php`

#### Chức Năng:
- Tạo inventory transactions cho tất cả 20 products từ ProductSeeder
- Mô phỏng hoạt động tồn kho trong 3 tháng qua
- Tạo lịch sử giao dịch realistic với timestamps khác nhau

#### Dữ Liệu Tạo:
**Cho mỗi sản phẩm:**
- **1 Initial Transaction** - Tồn đầu kỳ (10-200 units)
- **2-3 Import Transactions** - Nhập kho với số lượng khác nhau
- **2-4 Export Transactions** - Xuất kho theo thời gian
- **0-1 Adjustment Transaction** - 30% chance có điều chỉnh

#### Stock Data Realistic:
```php
// Electronics - High value, moderate stock
['initial' => 50, 'imports' => [30, 20], 'exports' => [5, 3, 2]], // iPhone
['initial' => 35, 'imports' => [25, 15], 'exports' => [4, 2, 1]], // Samsung

// Fashion - Medium value, high turnover
['initial' => 150, 'imports' => [100, 50], 'exports' => [25, 20, 15]], // Uniqlo
['initial' => 60, 'imports' => [40, 25], 'exports' => [10, 8, 5]], // Zara

// Home & Garden - Low turnover, stable stock
['initial' => 20, 'imports' => [15, 10], 'exports' => [3, 2, 1]], // IKEA
['initial' => 8, 'imports' => [5, 3], 'exports' => [1]], // Dyson
```

### 2. WarehouseSeeder
**File**: `database/seeders/WarehouseSeeder.php`

#### Warehouses Tạo:
1. **Kho Chi Nhánh Hà Nội** (HN01)
   - Phục vụ miền Bắc
   - Manager: Nguyễn Văn A
   - Address: Cầu Giấy, Hà Nội

2. **Kho Chi Nhánh TP.HCM** (HCM01)
   - Phục vụ miền Nam
   - Manager: Trần Thị B
   - Address: Quận 1, TP.HCM

3. **Kho Trung Chuyển Đà Nẵng** (DN01)
   - Phục vụ miền Trung
   - Manager: Lê Văn C
   - Address: Hải Châu, Đà Nẵng

4. **Kho Hàng Lỗi** (DEFECT)
   - Chứa hàng lỗi, trả về
   - Manager: Phòng QC

### 3. AdvancedInventoryTransactionSeeder
**File**: `database/seeders/AdvancedInventoryTransactionSeeder.php`

#### Chức Năng Nâng Cao:
- **Transfer Transactions** - Chuyển hàng giữa các kho
- **Branch Operations** - Hoạt động tại chi nhánh
- **Direct Imports** - Nhập hàng trực tiếp vào chi nhánh
- **Inter-branch Transfers** - Chuyển kho giữa chi nhánh
- **Historical Transactions** - Giao dịch với timestamps đa dạng

#### Workflow Mô Phỏng:
```php
// 1. Phân phối từ kho chính
Main Warehouse → Hanoi Branch (5 products, 5 units each)
Main Warehouse → HCM Branch (5 products, 5 units each)

// 2. Bán hàng tại chi nhánh
Hanoi Branch: Export 2 units × 3 products
HCM Branch: Export 2 units × 3 products

// 3. Nhập hàng trực tiếp
Hanoi Branch: Import 10-30 units × 3 products

// 4. Điều chỉnh tồn kho
Main Warehouse: Adjust ±3 units × 4 products

// 5. Chuyển kho giữa chi nhánh
Hanoi → Danang: Transfer 2 units × 2 products
```

## Cấu Trúc Transaction Types

### Transaction Types Được Sử Dụng:
1. **initial** - Tồn đầu kỳ
2. **import** - Nhập kho
3. **export** - Xuất kho
4. **transfer** - Chuyển kho
5. **adjustment** - Điều chỉnh

### Transaction Structure:
```php
[
    'product_id' => 1,
    'warehouse_id' => 1,
    'transaction_type' => 'import',
    'quantity' => 30,           // + cho nhập, - cho xuất
    'old_quantity' => 50,       // Số tồn trước giao dịch
    'new_quantity' => 80,       // Số tồn sau giao dịch
    'unit_cost' => 25000000,    // Giá nhập
    'total_value' => 750000000, // Tổng giá trị
    'notes' => 'Nhập kho lần 1 - iPhone 15 Pro Max',
    'created_by_user' => 1,
    'created_at' => '2024-03-15 10:30:00'
]
```

## Kết Quả Mong Đợi

### Database Records:
- **Products**: 20 records
- **Warehouses**: 5 records (1 main + 4 additional)
- **Inventories**: 40-60 records
- **InventoryTransactions**: 150-200 records

### Transaction Distribution:
```
initial: 20 transactions (1 per product)
import: 60-80 transactions (2-3 per product + direct imports)
export: 40-60 transactions (2-4 per product + branch sales)
transfer: 20-30 transactions (inter-warehouse movements)
adjustment: 5-10 transactions (random adjustments)
```

### Stock Distribution Example:
```
iPhone 15 Pro Max:
- Main Warehouse: 58 units
- Hanoi Branch: 13 units
- HCM Branch: 5 units
- Total: 76 units

Samsung Galaxy S24:
- Main Warehouse: 42 units
- Hanoi Branch: 8 units
- HCM Branch: 3 units
- Total: 53 units
```

## Timeline Mô Phỏng

### 3 Tháng Trước (Initial):
- Tất cả products có tồn đầu kỳ
- Chỉ có kho chính hoạt động

### 2.5 - 2 Tháng Trước:
- Import transactions đầu tiên
- Export transactions bắt đầu

### 1.5 - 1 Tháng Trước:
- Import transactions thứ 2
- Tạo thêm warehouses
- Bắt đầu transfer operations

### 1 Tháng - Hiện Tại:
- Advanced operations
- Inter-branch transfers
- Adjustment transactions
- Direct branch imports

## Business Logic Realistic

### 1. Product Categories:
**Electronics:**
- High value, low turnover
- Careful stock management
- Smaller quantities per transaction

**Fashion:**
- Medium value, high turnover
- Frequent imports/exports
- Larger quantities per transaction

**Home & Garden:**
- Low turnover, stable stock
- Infrequent but consistent transactions
- Medium quantities

### 2. Warehouse Operations:
**Main Warehouse:**
- Primary stock holder
- Source for distribution
- Receives most imports

**Branch Warehouses:**
- Receive transfers from main
- Handle local sales
- Some direct imports

**Special Warehouses:**
- Defect warehouse for returns
- Transit warehouse for distribution

### 3. Transaction Patterns:
- **Morning imports** (8-10 AM)
- **Afternoon exports** (2-4 PM)
- **Evening adjustments** (6-8 PM)
- **Weekend transfers** (Saturday)

## Usage Examples

### Chạy Seeders:
```bash
# Chạy tất cả
php artisan migrate:fresh --seed

# Chạy từng bước
php artisan db:seed --class=ProductSeeder
php artisan db:seed --class=InventoryTransactionSeeder
php artisan db:seed --class=WarehouseSeeder
php artisan db:seed --class=AdvancedInventoryTransactionSeeder
```

### Kiểm Tra Kết Quả:
```php
// Tổng transactions
$total = InventoryTransaction::count();

// Transactions theo type
$byType = InventoryTransaction::select('transaction_type', DB::raw('count(*) as count'))
    ->groupBy('transaction_type')->get();

// Stock hiện tại
$currentStock = Inventory::with(['product', 'warehouse'])->get();

// Lịch sử cho 1 sản phẩm
$history = InventoryTransaction::where('product_id', 1)
    ->orderBy('created_at')->get();
```

## Benefits

### 1. **Realistic Data**:
- Dữ liệu mô phỏng hoạt động thực tế
- Timeline logic hợp lý
- Business patterns realistic

### 2. **Complete Coverage**:
- Tất cả transaction types
- Multi-warehouse operations
- Historical data với timestamps

### 3. **Testing Ready**:
- Dữ liệu đủ để test tất cả features
- Edge cases được cover
- Performance testing với volume realistic

### 4. **Demo Ready**:
- Dữ liệu đẹp để demo
- Stories logic để present
- Statistics meaningful

## Customization

### Thay Đổi Stock Levels:
Sửa mảng `$stockData` trong `InventoryTransactionSeeder.php`

### Thêm Warehouses:
Sửa mảng `$warehouses` trong `WarehouseSeeder.php`

### Thay Đổi Timeline:
Sửa `$currentDate` và logic dates trong các seeders

### Custom Transaction Logic:
Extend `AdvancedInventoryTransactionSeeder.php` với business rules riêng

Hệ thống seeders đã hoàn thiện và sẵn sàng tạo dữ liệu mẫu realistic cho toàn bộ hệ thống quản lý tồn kho!
