# Test Cases - Module Phân Tích (Analytics Dashboard)

## 📋 Danh Sách Test Cases

### 1. **Dashboard Tổng Quan**

#### Test 1.1: Truy Cập Dashboard
- **URL**: `http://{tenant}.yukimart.local/admin/report/sales`
- **Yêu cầu**: Đã đăng nhập
- **Kỳ vọng**: 
  - Hiển thị dashboard với các KPI cards
  - Hiển thị biểu đồ xu hướng
  - Hiển thị bảng phân tích

#### Test 1.2: Lọc Theo Khoảng Thời Gian
- **Bước**:
  1. Chọn "Từ ngày": 2025-10-01
  2. Chọn "Đến ngày": 2025-10-31
  3. Click "Lọc"
- **Kỳ vọng**: 
  - Dữ liệu cập nhật theo khoảng thời gian
  - KPI cards hiển thị số liệu mới
  - Biểu đồ cập nhật

#### Test 1.3: Lọc Theo Chi Nhánh
- **Bước**:
  1. Chọn chi nhánh từ dropdown
  2. Click "Lọc"
- **Kỳ vọng**: 
  - Dữ liệu chỉ hiển thị của chi nhánh được chọn
  - Bảng chi nhánh chỉ hiển thị chi nhánh đó

#### Test 1.4: Lọc Tất Cả Chi Nhánh
- **Bước**:
  1. Chọn "Tất cả chi nhánh"
  2. Click "Lọc"
- **Kỳ vọng**: 
  - Dữ liệu hiển thị tất cả chi nhánh
  - Bảng chi nhánh hiển thị tất cả chi nhánh

### 2. **KPI Cards**

#### Test 2.1: Số Hóa Đơn
- **Kỳ vọng**: 
  - Hiển thị tổng số hóa đơn
  - Hiển thị TB/ngày
  - Hiển thị % so với kỳ trước (xanh nếu tăng, đỏ nếu giảm)

#### Test 2.2: Doanh Thu
- **Kỳ vọng**: 
  - Hiển thị tổng doanh thu (định dạng tiền tệ)
  - Hiển thị TB/ngày
  - Hiển thị % so với kỳ trước

#### Test 2.3: Giá Trị Trả
- **Kỳ vọng**: 
  - Hiển thị tổng giá trị trả hàng
  - Hiển thị TB/ngày

#### Test 2.4: Doanh Thu Thuần
- **Kỳ vọng**: 
  - Hiển thị doanh thu - trả hàng
  - Hiển thị TB/ngày

#### Test 2.5: Tổng Giá Vốn
- **Kỳ vọng**: 
  - Hiển thị tổng giá vốn (cost_price * quantity)
  - Hiển thị TB/ngày

#### Test 2.6: Lợi Nhuận Gộp
- **Kỳ vọng**: 
  - Hiển thị doanh thu thuần - giá vốn
  - Hiển thị TB/ngày

### 3. **Biểu Đồ Xu Hướng**

#### Test 3.1: Biểu Đồ Hiển Thị
- **Kỳ vọng**: 
  - Biểu đồ line chart hiển thị
  - Có 3 đường: Doanh thu (xanh), Trả hàng (đỏ), Doanh thu thuần (xanh lá)
  - Trục X: Ngày
  - Trục Y: Số tiền (VND)

#### Test 3.2: Hover Biểu Đồ
- **Bước**: Hover chuột vào điểm trên biểu đồ
- **Kỳ vọng**: 
  - Hiển thị tooltip với ngày và giá trị
  - Tooltip hiển thị đúng định dạng tiền tệ

#### Test 3.3: Zoom Biểu Đồ
- **Bước**: Scroll chuột trên biểu đồ
- **Kỳ vọng**: 
  - Biểu đồ có thể zoom in/out
  - Hoặc biểu đồ responsive

### 4. **Bảng Chi Nhánh**

#### Test 4.1: Hiển Thị Bảng
- **Kỳ vọng**: 
  - Bảng hiển thị tất cả chi nhánh
  - Cột: Chi nhánh, Doanh thu, Trả hàng, Doanh thu thuần, Giá vốn, Lợi nhuận
  - Dữ liệu định dạng tiền tệ

#### Test 4.2: Tính Toán Đúng
- **Kỳ vọng**: 
  - Doanh thu thuần = Doanh thu - Trả hàng
  - Lợi nhuận = Doanh thu thuần - Giá vốn
  - Giá vốn = SUM(cost_price * quantity)

### 5. **Bảng Top 10**

#### Test 5.1: Top 10 Nhóm Hàng
- **Kỳ vọng**: 
  - Hiển thị top 10 nhóm hàng theo doanh thu
  - Cột: Nhóm hàng, Doanh thu, TB/Đơn
  - Sắp xếp giảm dần theo doanh thu

#### Test 5.2: Top 10 Hàng Hóa
- **Kỳ vọng**: 
  - Hiển thị top 10 hàng hóa theo doanh thu
  - Cột: Hàng hóa, Doanh thu, TB/Đơn
  - Sắp xếp giảm dần theo doanh thu

#### Test 5.3: Top 10 Khách Hàng
- **Kỳ vọng**: 
  - Hiển thị top 10 khách hàng theo doanh thu
  - Cột: Khách hàng, Doanh thu, TB/Đơn
  - Sắp xếp giảm dần theo doanh thu

#### Test 5.4: Top 10 Kênh Bán
- **Kỳ vọng**: 
  - Hiển thị top 10 kênh bán theo doanh thu
  - Cột: Kênh bán, Doanh thu, TB/Đơn
  - Sắp xếp giảm dần theo doanh thu

#### Test 5.5: Top 10 Nhân Viên
- **Kỳ vọng**: 
  - Hiển thị top 10 nhân viên theo doanh thu
  - Cột: Nhân viên, Doanh thu, TB/Đơn
  - Sắp xếp giảm dần theo doanh thu

### 6. **Tính Toán Dữ Liệu**

#### Test 6.1: Tính Doanh Thu Đúng
- **Bước**: 
  1. Tạo 3 hóa đơn với tổng tiền: 100k, 200k, 300k
  2. Xem dashboard
- **Kỳ vọng**: 
  - Doanh thu = 600k
  - Số hóa đơn = 3

#### Test 6.2: Tính Trả Hàng Đúng
- **Bước**: 
  1. Tạo 1 phiếu trả hàng với tổng tiền: 50k
  2. Xem dashboard
- **Kỳ vọng**: 
  - Giá trị trả = 50k
  - Doanh thu thuần = Doanh thu - 50k

#### Test 6.3: Tính Giá Vốn Đúng
- **Bước**: 
  1. Tạo hóa đơn với:
     - Sản phẩm A: cost_price=100k, quantity=2 → cost=200k
     - Sản phẩm B: cost_price=50k, quantity=3 → cost=150k
  2. Xem dashboard
- **Kỳ vọng**: 
  - Tổng giá vốn = 350k

#### Test 6.4: Tính Lợi Nhuận Đúng
- **Kỳ vọng**: 
  - Lợi nhuận = Doanh thu thuần - Tổng giá vốn

### 7. **Hiệu Suất & Tối Ưu**

#### Test 7.1: Tải Trang Nhanh
- **Kỳ vọng**: 
  - Trang tải trong < 3 giây
  - Không có lỗi console

#### Test 7.2: Xử Lý Dữ Liệu Lớn
- **Bước**: 
  1. Tạo 1000+ hóa đơn
  2. Xem dashboard
- **Kỳ vọng**: 
  - Trang vẫn tải nhanh
  - Dữ liệu tính toán đúng

### 8. **Responsive Design**

#### Test 8.1: Desktop
- **Kỳ vọng**: 
  - Layout hiển thị đúng trên desktop
  - Tất cả phần tử hiển thị

#### Test 8.2: Tablet
- **Kỳ vọng**: 
  - Layout responsive trên tablet
  - Bảng có thể scroll ngang

#### Test 8.3: Mobile
- **Kỳ vọng**: 
  - Layout responsive trên mobile
  - Bảng có thể scroll ngang
  - KPI cards xếp chồng

## 🧪 Cách Chạy Test

### 1. Manual Testing
- Truy cập URL dashboard
- Thực hiện các bước test
- Kiểm tra kỳ vọng

### 2. Automated Testing (Playwright)
```bash
cd /mnt/persist/workspace/test-case/analytics
npx playwright test
```

## 📊 Test Data

### Dữ Liệu Mẫu
- Tạo 10 hóa đơn bán hàng
- Tạo 2 phiếu trả hàng
- Tạo 5 chi nhánh
- Tạo 20 sản phẩm
- Tạo 10 khách hàng

### SQL Insert
```sql
-- Tạo hóa đơn mẫu
INSERT INTO invoices (tenant_id, branch_shop_id, customer_id, invoice_type, invoice_date, total_amount, created_by, sold_by)
VALUES (1, 1, 1, 'sale', '2025-10-15', 500000, 1, 1);

-- Tạo chi tiết hóa đơn
INSERT INTO invoice_items (invoice_id, product_id, product_name, quantity, unit_price, line_total)
VALUES (1, 1, 'Sản phẩm A', 2, 250000, 500000);
```

## ✅ Checklist

- [ ] Dashboard tải đúng
- [ ] KPI cards hiển thị đúng
- [ ] Biểu đồ hiển thị đúng
- [ ] Bảng chi nhánh hiển thị đúng
- [ ] Bảng Top 10 hiển thị đúng
- [ ] Lọc theo thời gian hoạt động
- [ ] Lọc theo chi nhánh hoạt động
- [ ] Tính toán dữ liệu đúng
- [ ] Trang tải nhanh
- [ ] Responsive design hoạt động

