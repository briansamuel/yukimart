# Time Filter Tests

## Mục tiêu
Kiểm tra chức năng lọc hóa đơn theo thời gian, bao gồm các khoảng thời gian định sẵn và tùy chỉnh.

## Test Cases

| ID | Mô tả | Bước thực hiện | Kết quả mong đợi |
|----|-------|----------------|------------------|
| T01 | Kiểm tra filter "Tháng này" (mặc định) | Kiểm tra radio button "Tháng này" | Được chọn mặc định, hiển thị hóa đơn tháng hiện tại |
| T02 | Kiểm tra filter "Lựa chọn khác" | Click radio button "Lựa chọn khác" | Hiển thị date range picker |
| T03 | Kiểm tra custom date range | Chọn "Lựa chọn khác" và chọn khoảng thời gian | Hiển thị hóa đơn trong khoảng thời gian đã chọn |
| T04 | Kiểm tra date picker UI | Click vào date picker | Hiển thị calendar widget với ngày bắt đầu và kết thúc |
| T05 | Kiểm tra validation date range | Chọn ngày kết thúc trước ngày bắt đầu | Hiển thị lỗi hoặc tự động điều chỉnh |
| T06 | Kiểm tra clear date range | Xóa date range đã chọn | Quay về filter mặc định |
| T07 | Kiểm tra format ngày hiển thị | Chọn date range | Hiển thị đúng format ngày tháng |
| T08 | Kiểm tra AJAX request | Thay đổi time filter | Gửi AJAX request với time parameters đúng |
| T09 | Kiểm tra kết hợp với filter khác | Áp dụng time filter + status filter | Cả hai filter hoạt động đồng thời |
| T10 | Kiểm tra pagination với time filter | Áp dụng time filter rồi chuyển trang | Pagination hoạt động đúng với dữ liệu đã lọc |
| T11 | Kiểm tra reset time filter | Reset về "Tháng này" | Dữ liệu quay về hiển thị tháng hiện tại |
| T12 | Kiểm tra time filter persistence | Reload trang sau khi áp dụng filter | Filter được giữ nguyên (nếu có state management) |

## Kết quả Test

| Test ID | Status | Notes | Timestamp |
|---------|--------|-------|-----------|
| T01 | ⏳ PENDING | | |
| T02 | ✅ PASSED | Radio button "Lựa chọn khác" hoạt động đúng, được checked | 2025-01-13 11:43 |
| T03 | ⏳ PENDING | | |
| T04 | ⏳ PENDING | | |
| T05 | ⏳ PENDING | | |
| T06 | ⏳ PENDING | | |
| T07 | ⏳ PENDING | | |
| T08 | ⏳ PENDING | | |
| T09 | ⏳ PENDING | | |
| T10 | ⏳ PENDING | | |
| T11 | ⏳ PENDING | | |
| T12 | ⏳ PENDING | | |

## Technical Details

### Time Filter Options
- **Tháng này** (This Month) - Default selection
- **Lựa chọn khác** (Custom) - Shows date range picker

### Date Range Picker
- Start date input
- End date input
- Calendar widget
- Date format: DD/MM/YYYY
- Validation for date ranges

### AJAX Parameters
- `time_filter`: "this_month" or "custom"
- `start_date`: YYYY-MM-DD format
- `end_date`: YYYY-MM-DD format

### Expected Behavior
- Default to current month on page load
- Custom date range triggers AJAX request
- Date validation prevents invalid ranges
- Filter state persists during pagination

## Test Environment
- **URL**: http://yukimart.local/admin/invoices
- **Filter Section**: "Thời gian" in sidebar
- **AJAX Endpoint**: `/admin/invoices/ajax`
- **Date Format**: DD/MM/YYYY display, YYYY-MM-DD for API
