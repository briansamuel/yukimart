# Basic Invoice Listing Tests

## Mục tiêu
Kiểm tra chức năng hiển thị danh sách hóa đơn cơ bản, bao gồm loading, render dữ liệu, và các thành phần UI cơ bản.

## Test Cases

| ID | Mô tả | Bước thực hiện | Kết quả mong đợi |
|----|-------|----------------|------------------|
| BL01 | Kiểm tra tải trang đầu tiên | Truy cập http://yukimart.local/admin/invoices | Trang load thành công, hiển thị danh sách hóa đơn |
| BL02 | Kiểm tra hiển thị header table | Xem header của bảng | Hiển thị đầy đủ các cột: Mã hóa đơn, Khách hàng, Tổng tiền, Đã thanh toán, Trạng thái, Phương thức TT, Kênh bán, Ngày tạo, Người bán, Người tạo |
| BL03 | Kiểm tra hiển thị dữ liệu | Xem dữ liệu trong bảng | Hiển thị đúng dữ liệu hóa đơn với format phù hợp |
| BL04 | Kiểm tra loading state | Quan sát khi trang đang load | Hiển thị loading indicator "Đang tải dữ liệu..." |
| BL05 | Kiểm tra số lượng records mặc định | Đếm số dòng hiển thị | Hiển thị 10 records mặc định trên trang đầu |
| BL06 | Kiểm tra thông tin pagination | Xem thông tin phân trang | Hiển thị đúng thông tin "Hiển thị 1 đến 10 của X kết quả" |
| BL07 | Kiểm tra format tiền tệ | Xem cột Tổng tiền và Đã thanh toán | Hiển thị đúng format tiền tệ VND (VD: 1.801.800 ₫) |
| BL08 | Kiểm tra format ngày tháng | Xem cột Ngày tạo | Hiển thị đúng format ngày (VD: 7/8/2025 11:24) |
| BL09 | Kiểm tra hiển thị trạng thái | Xem cột Trạng thái | Hiển thị đúng trạng thái (VD: Chưa thanh toán, Đã thanh toán) |
| BL10 | Kiểm tra hiển thị khách hàng | Xem cột Khách hàng | Hiển thị tên khách hàng hoặc "Khách lẻ" |
| BL11 | Kiểm tra responsive table | Thay đổi kích thước cửa sổ | Table responsive, có horizontal scroll khi cần |
| BL12 | Kiểm tra checkbox column | Xem cột checkbox đầu tiên | Hiển thị checkbox cho từng row và select-all checkbox ở header |

## Kết quả Test

| Test ID | Status | Notes | Timestamp |
|---------|--------|-------|-----------|
| BL01 | ✅ PASSED | Trang load thành công, hiển thị danh sách hóa đơn | 2025-01-13 11:29 |
| BL02 | ✅ PASSED | Hiển thị đầy đủ 12 cột header bao gồm checkbox, mã hóa đơn, khách hàng, etc. | 2025-01-13 11:29 |
| BL03 | ✅ PASSED | Dữ liệu hiển thị đúng format: mã hóa đơn, tên khách hàng, số tiền, trạng thái | 2025-01-13 11:29 |
| BL04 | ✅ PASSED | Loading state "Đang tải dữ liệu..." hiển thị khi trang đang load | 2025-01-13 11:29 |
| BL05 | ✅ PASSED | Hiển thị đúng 10 records mặc định trên trang đầu | 2025-01-13 11:29 |
| BL06 | ✅ PASSED | Thông tin pagination: "Hiển thị 1 đến 10 của 1853 kết quả" | 2025-01-13 11:29 |
| BL07 | ✅ PASSED | Format tiền tệ VND đúng: "1.801.800 ₫", "1.216.600 ₫" | 2025-01-13 11:29 |
| BL08 | ✅ PASSED | Format ngày tháng đúng: "7/8/2025 11:24", "6/8/2025 02:53" | 2025-01-13 11:29 |
| BL09 | ✅ PASSED | Trạng thái hiển thị đúng: "Chưa thanh toán" | 2025-01-13 11:29 |
| BL10 | ✅ PASSED | Khách hàng hiển thị: "Khách lẻ", "Mã Văn Bảo", "Triệu Văn Tài" | 2025-01-13 11:29 |
| BL11 | ✅ PASSED | Responsive hoạt động tốt, table có horizontal scroll khi cần | 2025-01-13 11:30 |
| BL12 | ✅ PASSED | Checkbox hiển thị: select-all ở header và individual cho từng row | 2025-01-13 11:29 |

## Test Environment
- **URL**: http://yukimart.local/admin/invoices
- **Expected Data**: 1853+ invoices
- **Default Page Size**: 10 records
- **Browser**: Playwright automation
