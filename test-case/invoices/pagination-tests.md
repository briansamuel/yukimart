# Pagination Tests

## Mục tiêu
Kiểm tra chức năng phân trang của danh sách hóa đơn, bao gồm navigation, page size, và thông tin hiển thị.

## Test Cases

| ID | Mô tả | Bước thực hiện | Kết quả mong đợi |
|----|-------|----------------|------------------|
| P01 | Kiểm tra trang đầu tiên | Truy cập trang invoices | Hiển thị trang 1 với 10 kết quả mặc định |
| P02 | Kiểm tra trang thứ hai | Click vào nút trang 2 | Hiển thị trang 2 với 10 kết quả khác |
| P03 | Kiểm tra trang cuối | Click vào trang cuối cùng | Hiển thị trang cuối với số kết quả còn lại |
| P04 | Kiểm tra nút Next | Click vào nút "Tiếp" | Chuyển đến trang tiếp theo |
| P05 | Kiểm tra nút Previous | Click vào nút "Trước" (khi ở trang 2+) | Quay lại trang trước đó |
| P06 | Kiểm tra thông tin phân trang | Xem thông tin phân trang | Hiển thị đúng "Hiển thị X đến Y của Z kết quả" |
| P07 | Kiểm tra pagination với filter | Áp dụng filter rồi test pagination | Pagination hoạt động đúng với dữ liệu đã lọc |
| P08 | Kiểm tra pagination với search | Tìm kiếm rồi test pagination | Pagination hoạt động đúng với kết quả tìm kiếm |
| P09 | Kiểm tra URL parameters | Chuyển trang và xem URL | URL cập nhật với page parameter |
| P10 | Kiểm tra direct page access | Truy cập trực tiếp URL với page parameter | Load đúng trang được chỉ định |
| P11 | Kiểm tra page size consistency | Chuyển qua lại giữa các trang | Mỗi trang hiển thị đúng số lượng records |
| P12 | Kiểm tra pagination khi không có data | Áp dụng filter không có kết quả | Ẩn pagination hoặc hiển thị "0 kết quả" |

## Kết quả Test

| Test ID | Status | Notes | Timestamp |
|---------|--------|-------|-----------|
| P01 | ✅ PASSED | Trang đầu tiên hiển thị đúng 10 kết quả mặc định | 2025-01-13 11:29 |
| P02 | ✅ PASSED | Chuyển sang trang 2 thành công, hiển thị "11 đến 20 của 58 kết quả" | 2025-01-13 11:33 |
| P03 | ⏳ PENDING | | |
| P04 | ⏳ PENDING | | |
| P05 | ⏳ PENDING | | |
| P06 | ✅ PASSED | Thông tin phân trang cập nhật đúng khi chuyển trang | 2025-01-13 11:33 |
| P07 | ⏳ PENDING | | |
| P08 | ✅ PASSED | Pagination hoạt động đúng với search results, giữ nguyên search term | 2025-01-13 11:33 |
| P09 | ⏳ PENDING | | |
| P10 | ⏳ PENDING | | |
| P11 | ⏳ PENDING | | |
| P12 | ⏳ PENDING | | |

## Technical Details

### Pagination Settings
- **Default Page Size**: 10 records
- **Page Parameter**: `page`
- **Per Page Parameter**: `per_page`
- **Total Records**: 1853+ invoices

### Expected Elements
- Page numbers (1, 2, 3, ...)
- "Tiếp" (Next) button
- "Trước" (Previous) button (when applicable)
- Pagination info text
- First/Last page indicators

### AJAX Behavior
- Page changes should trigger AJAX requests
- URL should update with page parameter
- Loading states should be shown
- Data should update without full page reload

## Test Environment
- **URL**: http://yukimart.local/admin/invoices
- **AJAX Endpoint**: `/admin/invoices/ajax`
- **Expected Total**: 1853+ records
- **Page Size**: 10 records per page
