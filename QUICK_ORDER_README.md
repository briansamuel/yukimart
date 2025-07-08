# 🧾 Quick Order System - Hệ thống đặt hàng nhanh bằng mã vạch

## 📋 Tổng quan

Hệ thống đặt hàng nhanh cho phép nhân viên sử dụng máy quét mã vạch để thêm sản phẩm vào đơn hàng một cách nhanh chóng và hiệu quả, không cần gõ tay.

## ✨ Tính năng chính

- **🔍 Quét mã vạch**: Hỗ trợ máy quét mã vạch và nhập thủ công
- **⚡ Tự động tìm kiếm**: Tự động tìm sản phẩm sau khi quét/nhập barcode
- **📱 Giao diện thân thiện**: Giao diện POS tối ưu cho việc bán hàng nhanh
- **💾 Lưu phiên làm việc**: Tự động lưu đơn hàng tạm thời
- **🎯 Thêm sản phẩm thủ công**: Tìm kiếm và thêm sản phẩm bằng tay
- **✅ Kiểm tra tồn kho**: Validation tồn kho real-time
- **💰 Tùy chỉnh giá**: Cho phép thay đổi giá bán
- **👀 Xem trước đơn hàng**: Preview trước khi tạo đơn hàng
- **⌨️ Phím tắt**: Hỗ trợ keyboard shortcuts
- **📊 Thống kê**: Thống kê đơn hàng và doanh thu

## 🚀 Cài đặt và Thiết lập

### 1. Chạy Migration

```bash
php artisan migrate
```

### 2. Thêm Barcode cho sản phẩm hiện có

```bash
php artisan db:seed --class=AddBarcodeToProductsSeeder
```

### 3. Tạo dữ liệu test (tùy chọn)

```bash
php artisan test:quick-order --setup
```

### 4. Test hệ thống

```bash
php artisan test:quick-order
```

## 📖 Hướng dẫn sử dụng

### Truy cập trang Quick Order

1. Đăng nhập vào admin panel
2. Vào menu **Quick Order** (có badge POS)
3. Hoặc truy cập trực tiếp: `/admin/quick-order`

### Quy trình đặt hàng nhanh

1. **Focus vào ô Barcode**: Trang sẽ tự động focus vào ô nhập barcode
2. **Quét mã vạch**: Sử dụng máy quét hoặc nhập thủ công
3. **Sản phẩm tự động thêm**: Sản phẩm sẽ được thêm vào đơn hàng
4. **Chỉnh sửa nếu cần**: Thay đổi số lượng, giá bán
5. **Chọn thông tin đơn hàng**: Khách hàng, chi nhánh, phương thức thanh toán
6. **Tạo đơn hàng**: Click "Tạo đơn hàng" để hoàn tất

### Thêm sản phẩm thủ công

1. Click nút **"Thêm sản phẩm thủ công"**
2. Tìm kiếm sản phẩm theo tên, SKU, hoặc barcode
3. Chọn sản phẩm từ kết quả tìm kiếm
4. Nhập số lượng và giá tùy chỉnh (nếu cần)
5. Click **"Thêm sản phẩm"**

### Phím tắt

- **F2**: Focus vào ô barcode
- **Ctrl + Enter**: Tạo đơn hàng
- **Ctrl + N**: Xóa đơn hàng hiện tại

## 🔧 API Endpoints

### Tìm sản phẩm theo barcode
```
GET /api/products/barcode/{barcode}
```

### Tìm kiếm sản phẩm
```
GET /api/products/search?q={query}&limit={limit}
```

### Validate barcode
```
POST /api/products/barcode/validate
```

## 🗂️ Cấu trúc Files

```
app/
├── Http/Controllers/
│   ├── Admin/QuickOrderController.php
│   └── Api/ProductBarcodeController.php
├── Services/QuickOrderService.php
├── Models/
│   ├── Product.php (updated)
│   ├── Customer.php (updated)
│   └── BranchShop.php
└── Console/Commands/TestQuickOrderSystem.php

database/
├── migrations/
│   └── 2024_01_20_000001_add_barcode_to_products_table.php
├── factories/ProductFactory.php (updated)
└── seeders/AddBarcodeToProductsSeeder.php

resources/
├── views/admin/quick-order/index.blade.php
└── lang/
    ├── vi/order.php (updated)
    └── en/order.php (updated)

public/admin/js/quick-order.js

routes/
├── admin.php (updated)
└── api.php (updated)
```

## 🧪 Testing

### Test tự động
```bash
php artisan test:quick-order
```

### Test thủ công
1. Mở file `test-quick-order.html` trong browser
2. Test các API endpoints
3. Kiểm tra kết nối database
4. Test giao diện Quick Order

### Test với dữ liệu thật
1. Tạo sản phẩm có barcode
2. Tạo khách hàng active
3. Tạo chi nhánh cửa hàng active
4. Test quy trình đặt hàng hoàn chỉnh

## 🔍 Troubleshooting

### Lỗi "Customer::active() not found"
- Đảm bảo Customer model có scope `active()`
- Kiểm tra trường `status` trong bảng customers

### Lỗi "BranchShop::active() not found"
- Đảm bảo BranchShop model có scope `active()`
- Kiểm tra trường `status` trong bảng branch_shops

### API không hoạt động
- Kiểm tra routes trong `routes/api.php`
- Đảm bảo không có middleware authentication conflict
- Kiểm tra CSRF token

### Barcode không tìm thấy sản phẩm
- Đảm bảo sản phẩm có barcode
- Kiểm tra trạng thái sản phẩm là 'publish'
- Chạy seeder để thêm barcode cho sản phẩm hiện có

### JavaScript errors
- Kiểm tra file `public/admin/js/quick-order.js`
- Đảm bảo jQuery và SweetAlert2 được load
- Kiểm tra console browser để xem lỗi chi tiết

## 📝 Ghi chú

- Hệ thống tự động lưu session mỗi 30 giây
- Barcode phải có ít nhất 3 ký tự
- Hỗ trợ tìm kiếm theo SKU nếu không tìm thấy barcode
- Tự động kiểm tra tồn kho trước khi thêm sản phẩm
- Hỗ trợ đa ngôn ngữ (Tiếng Việt/English)

## 🆘 Hỗ trợ

Nếu gặp vấn đề, hãy:
1. Chạy `php artisan test:quick-order` để kiểm tra hệ thống
2. Kiểm tra logs trong `storage/logs/laravel.log`
3. Sử dụng file `test-quick-order.html` để test API
4. Đảm bảo đã chạy migration và seeder đầy đủ
