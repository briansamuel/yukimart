# Module Phân Tích (Analytics Dashboard) - Triển Khai Hoàn Chỉnh

## 📋 Tổng Quan

Module Phân Tích cung cấp dashboard tổng quan kinh doanh cho Tenant với các chỉ số KPI, biểu đồ xu hướng, và bảng phân tích chi tiết.

## 🏗️ Kiến Trúc Triển Khai

### 1. **Repositories** (Lớp Truy Vấn Dữ Liệu)

#### `app/Repositories/Invoice/InvoiceRepositoryInterface.php`
- Interface định nghĩa các method truy vấn hóa đơn

#### `app/Repositories/Invoice/InvoiceRepository.php`
- Triển khai các method:
  - `getByDateRange()` - Lấy hóa đơn theo khoảng thời gian
  - `groupByDate()` - Group hóa đơn theo ngày
  - `groupByBranch()` - Group hóa đơn theo chi nhánh
  - `groupByCustomer()` - Group hóa đơn theo khách hàng
  - `groupByChannel()` - Group hóa đơn theo kênh bán
  - `groupBySeller()` - Group hóa đơn theo nhân viên bán
  - `getTotalRevenue()` - Tính tổng doanh thu
  - `getTotalReturn()` - Tính tổng trả hàng
  - `countInvoices()` - Đếm số hóa đơn

#### `app/Repositories/Invoice/InvoiceItemRepository.php`
- Triển khai các method:
  - `groupByCategory()` - Group chi tiết hóa đơn theo nhóm hàng
  - `groupByProduct()` - Group chi tiết hóa đơn theo hàng hóa
  - `getTotalLineAmount()` - Tính tổng doanh thu từ chi tiết hóa đơn
  - `getByInvoice()` - Lấy chi tiết hóa đơn

### 2. **Services** (Lớp Xử Lý Logic)

#### `app/Services/Tenant/Analytics/SalesAnalyticsService.php`
- Tính toán các chỉ số bán hàng:
  - `getKpiOverview()` - Lấy KPI tổng quan (số hóa đơn, doanh thu, trả hàng, doanh thu thuần)
  - `getDailyTimeSeries()` - Lấy dữ liệu theo ngày cho biểu đồ
  - `getRevenueByChannel()` - Doanh thu theo kênh bán
  - `getRevenueBySeller()` - Doanh thu theo nhân viên bán

#### `app/Services/Tenant/Analytics/ProfitAnalyticsService.php`
- Tính toán lợi nhuận:
  - `getTotalCostOfGoods()` - Tính tổng giá vốn (cost_price * quantity)
  - `getGrossProfit()` - Tính lợi nhuận gộp
  - `getProfitMargin()` - Tính tỷ lệ lợi nhuận
  - `getCostByCategory()` - Giá vốn theo nhóm hàng
  - `getCostByProduct()` - Giá vốn theo hàng hóa

#### `app/Services/Tenant/Analytics/BranchAnalyticsService.php`
- Phân tích theo chi nhánh:
  - `getBranchBreakdown()` - Phân rã doanh thu, giá vốn, lợi nhuận theo chi nhánh
  - `getBranchComparison()` - So sánh hiệu suất giữa các chi nhánh
  - `getTopBranch()` - Lấy chi nhánh có doanh thu cao nhất

#### `app/Services/Tenant/Analytics/RankingAnalyticsService.php`
- Xếp hạng Top N:
  - `topProductCategories()` - Top 10 nhóm hàng
  - `topProducts()` - Top 10 hàng hóa
  - `topCustomers()` - Top 10 khách hàng
  - `topChannels()` - Top 10 kênh bán
  - `topSellers()` - Top 10 nhân viên bán

### 3. **Controllers** (Lớp Điều Khiển)

#### `app/Http/Controllers/Tenant/Reports/BusinessReportController.php`
- `index()` - Hiển thị dashboard phân tích kinh doanh
  - Lấy filter từ request (from_date, to_date, branch_shop_id)
  - Gọi các service để lấy dữ liệu
  - Truyền dữ liệu đến view

### 4. **Views** (Giao Diện)

#### `resources/views/tenant/reports/business-dashboard.blade.php`
- Layout dashboard chính
- Hiển thị:
  - Bộ lọc (khoảng thời gian, chi nhánh)
  - 6 thẻ KPI (Số hóa đơn, Doanh thu, Trả hàng, Doanh thu thuần, Giá vốn, Lợi nhuận)
  - Biểu đồ đường (Chart.js) - Xu hướng doanh thu theo ngày
  - Bảng phân tích theo chi nhánh
  - 5 bảng Top 10 (Nhóm hàng, Hàng hóa, Khách hàng, Kênh bán, Nhân viên)

### 5. **Service Provider**

#### `app/Providers/AnalyticsServiceProvider.php`
- Đăng ký các Repository và Service vào container
- Đảm bảo dependency injection hoạt động đúng

### 6. **Routes**

#### `routes/tenant.php`
- Route `/admin/report/sales` -> `BusinessReportController@index`

## 📊 Dữ Liệu Được Sử Dụng

### Bảng Chính
- `invoices` - Hóa đơn bán hàng
  - Cột: `invoice_date`, `total_amount`, `invoice_type`, `branch_shop_id`, `customer_id`, `sales_channel`, `sold_by`
- `invoice_items` - Chi tiết hóa đơn
  - Cột: `invoice_id`, `product_id`, `quantity`, `line_total`
- `products` - Hàng hóa
  - Cột: `cost_price`, `category_id`
- `categories` - Nhóm hàng
- `customers` - Khách hàng
- `branch_shops` - Chi nhánh
- `users` - Nhân viên bán hàng

## 🔄 Luồng Dữ Liệu

```
Request (from_date, to_date, branch_shop_id)
    ↓
BusinessReportController@index
    ↓
Services (Sales, Profit, Branch, Ranking)
    ↓
Repositories (Invoice, InvoiceItem)
    ↓
Database Query
    ↓
View (business-dashboard.blade.php)
    ↓
Response (HTML + Chart.js)
```

## 📈 Các Chỉ Số KPI

### 1. **Số Hóa Đơn**
- Tổng số hóa đơn bán hàng
- Trung bình/ngày
- % so với kỳ trước

### 2. **Doanh Thu**
- Tổng doanh thu bán hàng
- Trung bình/ngày
- % so với kỳ trước

### 3. **Giá Trị Trả**
- Tổng giá trị phiếu trả hàng
- Trung bình/ngày

### 4. **Doanh Thu Thuần**
- Doanh thu - Trả hàng
- Trung bình/ngày

### 5. **Tổng Giá Vốn**
- Tính từ: `SUM(product.cost_price * invoice_item.quantity)`
- Trung bình/ngày

### 6. **Lợi Nhuận Gộp**
- Doanh thu thuần - Tổng giá vốn
- Trung bình/ngày

## 🎯 Biểu Đồ

### Biểu Đồ Đường (Line Chart)
- Trục X: Ngày
- Trục Y: Số tiền (VND)
- 3 đường:
  1. Doanh thu (xanh dương)
  2. Trả hàng (đỏ)
  3. Doanh thu thuần (xanh lá)

## 📋 Bảng Phân Tích

### 1. Bảng Chi Nhánh
- Cột: Chi nhánh, Doanh thu, Trả hàng, Doanh thu thuần, Giá vốn, Lợi nhuận

### 2. Top 10 Nhóm Hàng
- Cột: Nhóm hàng, Doanh thu, TB/Đơn

### 3. Top 10 Hàng Hóa
- Cột: Hàng hóa, Doanh thu, TB/Đơn

### 4. Top 10 Khách Hàng
- Cột: Khách hàng, Doanh thu, TB/Đơn

### 5. Top 10 Kênh Bán
- Cột: Kênh bán, Doanh thu, TB/Đơn

### 6. Top 10 Nhân Viên
- Cột: Nhân viên, Doanh thu, TB/Đơn

## 🔐 Phân Quyền

- Middleware: `auth:tenant`, `tenant.resolve`
- Quyền: `analytics.view` (có thể thêm sau)

## 🚀 Cách Sử Dụng

### 1. Truy Cập Dashboard
```
URL: http://{tenant}.yukimart.local/admin/report/sales
```

### 2. Lọc Dữ Liệu
- Chọn khoảng thời gian (from_date, to_date)
- Chọn chi nhánh (branch_shop_id)
- Click "Lọc"

### 3. Xem Dữ Liệu
- KPI cards hiển thị các chỉ số chính
- Biểu đồ hiển thị xu hướng theo ngày
- Bảng hiển thị chi tiết phân tích

## 📝 Ghi Chú

### Tính Giá Vốn
- Sử dụng `product.cost_price` nhân với `invoice_item.quantity`
- Không có cột cost trong `invoice_items`, tính động từ product

### So Sánh Kỳ Trước
- Tính khoảng thời gian trước có cùng độ dài
- Ví dụ: 2025-10-01 → 2025-10-29 (29 ngày) → kỳ trước = 2025-09-02 → 2025-09-30

### Khách Lẻ
- Hóa đơn có `customer_id = null` được coi là khách lẻ

### Kênh Bán
- Lấy từ cột `sales_channel` trong `invoices`
- Giá trị: offline, online, marketplace, social_media, phone_order

## 🔧 Mở Rộng Trong Tương Lai

1. **Thêm Phân Tích Theo Khoảng Thời Gian**
   - Tuần, Tháng, Quý, Năm

2. **Thêm Export Dữ Liệu**
   - Export Excel, PDF

3. **Thêm Biểu Đồ Khác**
   - Biểu đồ cột, tròn, vùng

4. **Thêm Phân Tích Chuyên Sâu**
   - Phân tích chi tiết theo từng nhóm hàng
   - Phân tích chi tiết theo từng khách hàng

5. **Thêm Cảnh Báo**
   - Cảnh báo khi doanh thu giảm
   - Cảnh báo khi hàng hóa bán chậm

## 📞 Hỗ Trợ

Nếu có vấn đề, vui lòng kiểm tra:
1. Middleware `tenant.resolve` có hoạt động đúng không
2. Dữ liệu trong database có đúng không
3. Service Provider có được đăng ký không

