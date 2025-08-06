# Column Visibility Tests

## Mục tiêu
Kiểm tra chức năng ẩn/hiện cột trong bảng danh sách hóa đơn, bao gồm toggle columns và persistence.

## Test Cases

| ID | Mô tả | Bước thực hiện | Kết quả mong đợi |
|----|-------|----------------|------------------|
| CV01 | Kiểm tra button Column Visibility | Tìm button để toggle column visibility | Button hiển thị với icon phù hợp |
| CV02 | Kiểm tra dropdown panel | Click vào column visibility button | Hiển thị dropdown với list các cột |
| CV03 | Kiểm tra toggle cột "Khách hàng" | Uncheck checkbox "Khách hàng" | Cột khách hàng bị ẩn trong table |
| CV04 | Kiểm tra show lại cột đã ẩn | Check lại checkbox "Khách hàng" | Cột khách hàng hiển thị lại |
| CV05 | Kiểm tra toggle multiple columns | Ẩn/hiện nhiều cột cùng lúc | Các cột được ẩn/hiện đúng |
| CV06 | Kiểm tra ẩn tất cả cột (trừ required) | Uncheck tất cả checkbox có thể | Chỉ giữ lại các cột bắt buộc |
| CV07 | Kiểm tra responsive với ít cột | Ẩn nhiều cột và xem responsive | Table vẫn responsive và hiển thị đúng |
| CV08 | Kiểm tra header update | Toggle cột và xem table header | Header tự động cập nhật theo cột hiển thị |
| CV09 | Kiểm tra data consistency | Toggle cột và xem data | Data vẫn đúng và không bị lỗi |
| CV10 | Kiểm tra close dropdown | Click outside dropdown | Dropdown tự động đóng |
| CV11 | Kiểm tra persistence (nếu có) | Toggle cột, reload trang | Setting được giữ nguyên (nếu có localStorage) |
| CV12 | Kiểm tra với horizontal scroll | Ẩn/hiện cột khi table có scroll | Scroll position và behavior đúng |

## Kết quả Test

| Test ID | Status | Notes | Timestamp |
|---------|--------|-------|-----------|
| CV01 | ⏳ PENDING | | |
| CV02 | ⏳ PENDING | | |
| CV03 | ⏳ PENDING | | |
| CV04 | ⏳ PENDING | | |
| CV05 | ⏳ PENDING | | |
| CV06 | ⏳ PENDING | | |
| CV07 | ⏳ PENDING | | |
| CV08 | ⏳ PENDING | | |
| CV09 | ⏳ PENDING | | |
| CV10 | ⏳ PENDING | | |
| CV11 | ⏳ PENDING | | |
| CV12 | ⏳ PENDING | | |

## Technical Details

### Available Columns
- ☑️ Checkbox (Always visible - required)
- ☑️ Mã hóa đơn (Invoice Code)
- ☑️ Khách hàng (Customer)
- ☑️ Tổng tiền (Total Amount)
- ☑️ Đã thanh toán (Paid Amount)
- ☑️ Trạng thái (Status)
- ☑️ Phương thức TT (Payment Method)
- ☑️ Kênh bán (Sales Channel)
- ☑️ Ngày tạo (Created Date)
- ☑️ Người bán (Seller)
- ☑️ Người tạo (Creator)

### Expected Behavior
- Checkbox toggles show/hide column
- Header automatically updates
- Data remains consistent
- Responsive behavior maintained
- Dropdown closes when clicking outside

### Implementation Method
- CSS `display: none` for hidden columns
- JavaScript toggle functionality
- No table re-rendering (performance)
- Possible localStorage persistence

## Test Environment
- **URL**: http://yukimart.local/admin/invoices
- **Column Visibility Button**: In toolbar area
- **Implementation**: CSS display toggle
- **Browser**: Test across different browsers
