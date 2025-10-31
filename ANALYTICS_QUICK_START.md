# Hướng Dẫn Nhanh - Module Phân Tích (Analytics Dashboard)

## 🚀 Bắt Đầu Nhanh

### 1. **Kiểm Tra Cài Đặt**

Đảm bảo các file sau đã được tạo:

```bash
# Repositories
ls -la app/Repositories/Invoice/

# Services
ls -la app/Services/Tenant/Analytics/

# Controller
ls -la app/Http/Controllers/Tenant/Reports/BusinessReportController.php

# View
ls -la resources/views/tenant/reports/business-dashboard.blade.php

# Service Provider
ls -la app/Providers/AnalyticsServiceProvider.php
```

### 2. **Kiểm Tra Config**

Mở `config/app.php` và kiểm tra:
```php
'providers' => [
    // ...
    App\Providers\AnalyticsServiceProvider::class,
],
```

### 3. **Kiểm Tra Routes**

Mở `routes/tenant.php` và kiểm tra:
```php
Route::get('/sales', [App\Http\Controllers\Tenant\Reports\BusinessReportController::class, 'index'])->name('sales');
```

### 4. **Truy Cập Dashboard**

```
URL: http://{tenant}.yukimart.local/admin/report/sales
```

## 📊 Dữ Liệu Mẫu

Để test module, bạn cần có dữ liệu:

### 1. **Tạo Hóa Đơn Mẫu**

```sql
-- Tạo hóa đơn bán hàng
INSERT INTO invoices (
    tenant_id, branch_shop_id, customer_id, 
    invoice_type, invoice_date, total_amount, 
    sales_channel, created_by, sold_by
) VALUES (
    1, 1, 1, 
    'sale', '2025-10-15', 500000, 
    'offline', 1, 1
);

-- Tạo chi tiết hóa đơn
INSERT INTO invoice_items (
    invoice_id, product_id, product_name, 
    quantity, unit_price, line_total
) VALUES (
    1, 1, 'Sản phẩm A', 
    2, 250000, 500000
);
```

### 2. **Tạo Phiếu Trả Hàng Mẫu**

```sql
INSERT INTO invoices (
    tenant_id, branch_shop_id, customer_id, 
    invoice_type, invoice_date, total_amount, 
    created_by, sold_by
) VALUES (
    1, 1, 1, 
    'return', '2025-10-16', 100000, 
    1, 1
);
```

## 🧪 Test Nhanh

### 1. **Test KPI Cards**
- Truy cập dashboard
- Kiểm tra 6 KPI cards hiển thị
- Kiểm tra số liệu có hợp lý không

### 2. **Test Biểu Đồ**
- Kiểm tra biểu đồ line chart hiển thị
- Kiểm tra có 3 đường (Doanh thu, Trả hàng, Doanh thu thuần)

### 3. **Test Bảng**
- Kiểm tra bảng chi nhánh hiển thị
- Kiểm tra 5 bảng Top 10 hiển thị

### 4. **Test Lọc**
- Chọn khoảng thời gian
- Chọn chi nhánh
- Click "Lọc"
- Kiểm tra dữ liệu cập nhật

## 🔍 Kiểm Tra Lỗi

### 1. **Lỗi 404**
```
Nguyên nhân: Route không được đăng ký
Giải pháp: Kiểm tra routes/tenant.php
```

### 2. **Lỗi 500**
```
Nguyên nhân: Service Provider không được đăng ký
Giải pháp: Kiểm tra config/app.php
```

### 3. **Không Có Dữ Liệu**
```
Nguyên nhân: Không có hóa đơn trong database
Giải pháp: Tạo dữ liệu mẫu
```

### 4. **Biểu Đồ Không Hiển Thị**
```
Nguyên nhân: Chart.js không được load
Giải pháp: Kiểm tra CDN link trong view
```

## 📝 Cấu Trúc Dữ Liệu

### Invoices Table
```
id, tenant_id, branch_shop_id, customer_id, 
invoice_type, invoice_date, total_amount, 
sales_channel, sold_by, created_by, ...
```

### Invoice Items Table
```
id, invoice_id, product_id, product_name, 
quantity, unit_price, line_total, ...
```

### Products Table
```
id, tenant_id, name, sku, category_id, 
cost_price, sale_price, ...
```

## 🎯 Các Chỉ Số Chính

| Chỉ Số | Công Thức | Ví Dụ |
|--------|-----------|-------|
| Doanh Thu | SUM(total_amount) WHERE type='sale' | 1,000,000 ₫ |
| Trả Hàng | SUM(total_amount) WHERE type='return' | 100,000 ₫ |
| Doanh Thu Thuần | Doanh Thu - Trả Hàng | 900,000 ₫ |
| Giá Vốn | SUM(cost_price * qty) | 500,000 ₫ |
| Lợi Nhuận | Doanh Thu Thuần - Giá Vốn | 400,000 ₫ |
| TB/Ngày | Giá Trị / Số Ngày | 13,333 ₫ |

## 🔧 Tùy Chỉnh

### 1. **Thay Đổi Số Lượng Top N**
Mở `app/Services/Tenant/Analytics/RankingAnalyticsService.php`:
```php
public function topProducts($tenantId, $fromDate, $toDate, $limit = 10)
// Thay 10 thành số khác
```

### 2. **Thay Đổi Màu Biểu Đồ**
Mở `resources/views/tenant/reports/business-dashboard.blade.php`:
```javascript
borderColor: '#0d6efd', // Thay màu ở đây
```

### 3. **Thay Đổi Định Dạng Tiền Tệ**
Mở view và tìm `number_format()`:
```php
number_format($value, 0) // Thay 0 thành số chữ số thập phân
```

## 📚 Tài Liệu Tham Khảo

- `ANALYTICS_MODULE_IMPLEMENTATION.md` - Tài liệu chi tiết
- `ANALYTICS_IMPLEMENTATION_SUMMARY.md` - Tóm tắt triển khai
- `test-case/analytics/README.md` - Test cases

## ❓ Câu Hỏi Thường Gặp

### Q: Làm sao để thêm chỉ số mới?
A: Thêm method mới trong Service, sau đó gọi trong Controller

### Q: Làm sao để thay đổi bộ lọc?
A: Cập nhật form trong view và xử lý trong Controller

### Q: Làm sao để export dữ liệu?
A: Thêm method export trong Controller, sử dụng Laravel Excel

### Q: Làm sao để thêm quyền?
A: Thêm middleware `permission:analytics.view` trong route

## 🎉 Hoàn Tất!

Module Phân Tích đã sẵn sàng sử dụng. Hãy truy cập dashboard và kiểm tra!

```
http://{tenant}.yukimart.local/admin/report/sales
```

