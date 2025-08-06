# Export Excel Tests

## Mục tiêu
Kiểm tra chức năng xuất dữ liệu hóa đơn ra file Excel, bao gồm xuất toàn bộ và xuất với filter.

## Test Cases

| ID | Mô tả | Bước thực hiện | Kết quả mong đợi |
|----|-------|----------------|------------------|
| E01 | Kiểm tra button "Xuất Excel" | Kiểm tra button trong toolbar | Button hiển thị với icon và text "Xuất Excel" |
| E02 | Kiểm tra export toàn bộ | Click "Xuất Excel" khi không có filter | Download file Excel chứa tất cả hóa đơn |
| E03 | Kiểm tra export với time filter | Áp dụng time filter rồi click "Xuất Excel" | Download file Excel chứa hóa đơn trong khoảng thời gian |
| E04 | Kiểm tra export với status filter | Áp dụng status filter rồi click "Xuất Excel" | Download file Excel chứa hóa đơn có trạng thái đã chọn |
| E05 | Kiểm tra export với search | Tìm kiếm rồi click "Xuất Excel" | Download file Excel chứa kết quả tìm kiếm |
| E06 | Kiểm tra export với multiple filters | Áp dụng nhiều filter rồi export | Download file Excel chứa dữ liệu thỏa mãn tất cả filter |
| E07 | Kiểm tra tên file download | Download file Excel | Tên file có format phù hợp (VD: invoices_YYYYMMDD.xlsx) |
| E08 | Kiểm tra nội dung file Excel | Mở file Excel đã download | Chứa đầy đủ cột và dữ liệu chính xác |
| E09 | Kiểm tra format dữ liệu trong Excel | Kiểm tra format số tiền, ngày tháng | Format đúng và readable |
| E10 | Kiểm tra export khi không có data | Áp dụng filter không có kết quả rồi export | Download file Excel trống hoặc thông báo lỗi |
| E11 | Kiểm tra loading state | Click export và quan sát | Hiển thị loading indicator trong quá trình export |
| E12 | Kiểm tra error handling | Simulate network error khi export | Hiển thị thông báo lỗi phù hợp |

## Kết quả Test

| Test ID | Status | Notes | Timestamp |
|---------|--------|-------|-----------|
| E01 | ⏳ PENDING | | |
| E02 | ⏳ PENDING | | |
| E03 | ⏳ PENDING | | |
| E04 | ⏳ PENDING | | |
| E05 | ⏳ PENDING | | |
| E06 | ⏳ PENDING | | |
| E07 | ⏳ PENDING | | |
| E08 | ⏳ PENDING | | |
| E09 | ⏳ PENDING | | |
| E10 | ⏳ PENDING | | |
| E11 | ⏳ PENDING | | |
| E12 | ⏳ PENDING | | |

## Technical Details

### Export Button
- Location: Toolbar area
- Icon: Excel icon
- Text: "Xuất Excel"
- Behavior: Triggers download

### Expected Excel Columns
- Mã hóa đơn (Invoice Code)
- Khách hàng (Customer)
- Tổng tiền (Total Amount)
- Đã thanh toán (Paid Amount)
- Trạng thái (Status)
- Phương thức TT (Payment Method)
- Kênh bán (Sales Channel)
- Ngày tạo (Created Date)
- Người bán (Seller)
- Người tạo (Creator)

### File Format
- **Extension**: .xlsx
- **Encoding**: UTF-8
- **Date Format**: DD/MM/YYYY
- **Currency Format**: #,##0 ₫

### Export Endpoint
- **URL**: `/admin/invoices/export`
- **Method**: GET or POST
- **Parameters**: Same as listing filters

## Test Environment
- **URL**: http://yukimart.local/admin/invoices
- **Export Button**: In toolbar area
- **Expected Data**: 1853+ invoices
- **File Size**: Varies based on data volume
