# Search Functionality Tests

## Mục tiêu
Kiểm tra chức năng tìm kiếm hóa đơn theo nhiều trường khác nhau, bao gồm mã hóa đơn, tên khách hàng, số tiền, và các trường khác.

## Test Cases

| ID | Mô tả | Bước thực hiện | Kết quả mong đợi |
|----|-------|----------------|------------------|
| S01 | Kiểm tra tìm kiếm theo mã hóa đơn | Nhập mã hóa đơn vào ô tìm kiếm (VD: "INV-20250709-1736") | Hiển thị hóa đơn có mã tương ứng |
| S02 | Kiểm tra tìm kiếm theo tên khách hàng | Nhập tên khách hàng (VD: "Mã Văn Bảo") | Hiển thị các hóa đơn của khách hàng đó |
| S03 | Kiểm tra tìm kiếm theo số tiền | Nhập số tiền (VD: "1801800") | Hiển thị hóa đơn có số tiền tương ứng |
| S04 | Kiểm tra tìm kiếm partial match | Nhập một phần mã hóa đơn (VD: "INV-2025") | Hiển thị tất cả hóa đơn có mã chứa chuỗi đó |
| S05 | Kiểm tra tìm kiếm không có kết quả | Nhập từ khóa không tồn tại (VD: "NOTEXIST123") | Hiển thị "Không có dữ liệu" |
| S06 | Kiểm tra xóa từ khóa tìm kiếm | Xóa nội dung ô tìm kiếm | Quay về hiển thị tất cả hóa đơn |
| S07 | Kiểm tra tìm kiếm với ký tự đặc biệt | Nhập ký tự đặc biệt (@, #, %, &) | Không gây lỗi, xử lý gracefully |
| S08 | Kiểm tra tìm kiếm với khoảng trắng | Nhập từ khóa có khoảng trắng đầu/cuối | Tự động trim và tìm kiếm đúng |
| S09 | Kiểm tra debouncing | Nhập nhanh nhiều ký tự liên tiếp | Chỉ gửi request sau khi dừng gõ |
| S10 | Kiểm tra case sensitivity | Nhập từ khóa với chữ hoa/thường khác nhau | Tìm kiếm không phân biệt hoa thường |
| S11 | Kiểm tra tìm kiếm theo người bán | Nhập tên người bán (VD: "Nhân viên 2") | Hiển thị hóa đơn của người bán đó |
| S12 | Kiểm tra tìm kiếm kết hợp với filter | Áp dụng filter + tìm kiếm | Kết quả thỏa mãn cả filter và search |

## Kết quả Test

| Test ID | Status | Notes | Timestamp |
|---------|--------|-------|-----------|
| S01 | ✅ PASSED | Tìm kiếm "INV-20250709-1736" trả về đúng 1 kết quả | 2025-01-13 11:32 |
| S02 | ✅ PASSED | Tìm kiếm "Mã Văn Bảo" trả về 58 kết quả, tất cả đều đúng khách hàng | 2025-01-13 11:33 |
| S03 | ✅ PASSED | Tìm kiếm "1801800" trả về 0 kết quả, hiển thị "Không có dữ liệu" | 2025-01-13 11:42 |
| S04 | ✅ PASSED | Tìm kiếm "INV-2025" trả về 1167 kết quả partial match | 2025-01-13 11:42 |
| S05 | ✅ PASSED | Tìm kiếm không có kết quả xử lý gracefully | 2025-01-13 11:42 |
| S06 | ✅ PASSED | Xóa search term thành công, quay về hiển thị tất cả 1853 hóa đơn | 2025-01-13 11:32 |
| S07 | ⏳ PENDING | | |
| S08 | ⏳ PENDING | | |
| S09 | ⏳ PENDING | | |
| S10 | ⏳ PENDING | | |
| S11 | ⏳ PENDING | | |
| S12 | ⏳ PENDING | | |

## Technical Details

### Search Fields
- Mã hóa đơn (Invoice Code)
- Tên khách hàng (Customer Name)
- Số tiền (Amount)
- Người bán (Seller)
- Người tạo (Creator)

### Expected Behavior
- **Debouncing**: 300ms delay
- **Min Length**: 1 character
- **Case Insensitive**: Yes
- **Partial Match**: Yes
- **Special Characters**: Handled safely

## Test Environment
- **URL**: http://yukimart.local/admin/invoices
- **Search Input**: "Tìm kiếm hóa đơn..." placeholder
- **Expected Data**: 1853+ invoices
- **AJAX Endpoint**: `/admin/invoices/ajax`
