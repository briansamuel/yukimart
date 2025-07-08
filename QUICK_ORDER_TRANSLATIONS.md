# 🌐 Quick Order Translation Keys

## 📋 Tóm tắt

Đã thêm tất cả các translation keys cần thiết cho hệ thống Quick Order vào file language tiếng Việt và tiếng Anh.

## ✅ Translation Keys đã thêm

### Core Quick Order Keys
- `quick_order` - Đặt hàng nhanh / Quick Order
- `barcode_scanner` - Máy quét mã vạch / Barcode Scanner
- `scan_barcode` - Quét mã vạch / Scan Barcode
- `enter_barcode` - Nhập mã vạch / Enter Barcode
- `last_scanned` - Mã vừa quét / Last Scanned
- `clear_order` - Xóa đơn hàng / Clear Order
- `save_session` - Lưu phiên làm việc / Save Session
- `add_manual_item` - Thêm sản phẩm thủ công / Add Manual Item
- `search_product` - Tìm sản phẩm / Search Product
- `custom_price` - Giá tùy chỉnh / Custom Price
- `pos` - POS / POS
- `today_stats` - Thống kê hôm nay / Today Stats

### Product & Order Keys
- `product` - Sản phẩm / Product
- `sku_barcode` - SKU/Mã vạch / SKU/Barcode
- `price` - Giá / Price
- `quantity` - Số lượng / Quantity
- `total` - Tổng cộng / Total
- `actions` - Thao tác / Actions
- `order_items` - Sản phẩm trong đơn / Order Items
- `0_items` - 0 sản phẩm / 0 items
- `order_summary` - Tóm tắt đơn hàng / Order Summary

### Customer & Payment Keys
- `customer` - Khách hàng / Customer
- `select_customer` - Chọn khách hàng / Select customer
- `branch_shop` - Chi nhánh / Branch Shop
- `select_branch_shop` - Chọn chi nhánh / Select branch shop
- `payment_method` - Phương thức thanh toán / Payment Method
- `cash` - Tiền mặt / Cash
- `card` - Thẻ / Card
- `bank_transfer` - Chuyển khoản / Bank Transfer
- `e_wallet` - Ví điện tử / E-Wallet

### Order Totals Keys
- `subtotal` - Tạm tính / Subtotal
- `discount` - Giảm giá / Discount
- `notes` - Ghi chú / Notes
- `order_notes_optional` - Ghi chú đơn hàng (tùy chọn) / Order notes (optional)

### Action Keys
- `please_wait` - Vui lòng đợi... / Please wait...
- `search` - Tìm kiếm / Search
- `create_order` - Tạo đơn hàng / Create Order
- `preview_order` - Xem trước đơn hàng / Preview Order
- `cancel` - Hủy / Cancel
- `add_item` - Thêm sản phẩm / Add Item
- `dashboard` - Bảng điều khiển / Dashboard

### Interface Text Keys
- `scan_or_enter_barcode_dots` - Quét hoặc nhập mã vạch... / Scan or enter barcode...
- `focus_on_field_and_scan` - Tập trung vào trường này và quét mã vạch, hoặc nhập thủ công / Focus on this field and scan barcode, or type manually
- `search_by_name_sku_barcode` - Tìm theo tên, SKU, hoặc mã vạch / Search by name, SKU, or barcode
- `enter_quantity` - Nhập số lượng / Enter quantity
- `leave_empty_to_use_product_price` - Để trống để sử dụng giá sản phẩm / Leave empty to use product price
- `scan_or_enter_product_barcode` - Quét hoặc nhập mã vạch sản phẩm / Scan or enter product barcode
- `no_items_in_order` - Chưa có sản phẩm trong đơn hàng / No items in order
- `scan_barcodes_to_start` - Quét mã vạch hoặc thêm sản phẩm thủ công để bắt đầu tạo đơn hàng / Scan barcodes or add items manually to start building your order

### Statistics Keys
- `revenue` - Doanh thu / Revenue
- `orders` - Đơn hàng / Orders
- `none` - Không có / None

## 📁 Files đã cập nhật

### Language Files
- `resources/lang/vi/order.php` - Thêm 40+ translation keys tiếng Việt
- `resources/lang/en/order.php` - Thêm 40+ translation keys tiếng Anh

### View Files
- `resources/views/admin/quick-order/index.blade.php` - Cập nhật tất cả hardcoded text thành translation keys
- `resources/views/admin/left-aside.blade.php` - Cập nhật menu Quick Order

## 🔧 Cách sử dụng

Tất cả các text trong giao diện Quick Order đã được chuyển đổi từ hardcoded text sang translation keys với format:

```php
{{ __('order.translation_key') }}
```

Ví dụ:
- `{{ __('order.quick_order') }}` → "Đặt hàng nhanh" (VI) / "Quick Order" (EN)
- `{{ __('order.barcode_scanner') }}` → "Máy quét mã vạch" (VI) / "Barcode Scanner" (EN)
- `{{ __('order.create_order') }}` → "Tạo đơn hàng" (VI) / "Create Order" (EN)

## ✅ Kết quả

- ✅ Tất cả text trong Quick Order đã được internationalize
- ✅ Hỗ trợ đầy đủ tiếng Việt và tiếng Anh
- ✅ Không còn hardcoded text nào trong giao diện
- ✅ Dễ dàng thêm ngôn ngữ mới trong tương lai
- ✅ Consistent naming convention cho translation keys

## 🎯 Lưu ý

- Tất cả translation keys đều có prefix `order.` để tránh conflict
- Keys được đặt tên theo convention: `snake_case`
- Tiếng Việt sử dụng tone marks đầy đủ
- Tiếng Anh sử dụng proper capitalization
- Có thể dễ dàng thêm ngôn ngữ khác bằng cách tạo file `resources/lang/{locale}/order.php`
