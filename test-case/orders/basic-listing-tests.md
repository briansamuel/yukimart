# Test Case: Basic Listing - Orders Module

## Mô tả
Kiểm tra chức năng hiển thị danh sách đơn hàng cơ bản, bao gồm load data, render UI, và các thao tác cơ bản.

## Test Cases

### TC-ORDER-LIST-001: Page load và initial display
**Mô tả**: Kiểm tra trang load và hiển thị ban đầu
**Độ ưu tiên**: High
**Điều kiện tiên quyết**: Đã đăng nhập, có orders trong database

**Bước thực hiện**:
1. Navigate to http://yukimart.local/admin/orders
2. Wait for page load complete
3. Kiểm tra elements hiển thị
4. Check console for errors

**Kết quả mong đợi**:
- Page load trong < 3 seconds
- Header "Quản lý đơn hàng" hiển thị
- Table với columns: Mã đơn, Khách hàng, Tổng tiền, Trạng thái, etc.
- Pagination controls hiển thị
- Filter sidebar hiển thị
- No JavaScript errors in console

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-ORDER-LIST-002: Data loading và display
**Mô tả**: Kiểm tra data được load và hiển thị đúng
**Độ ưu tiên**: High

**Bước thực hiện**:
1. Kiểm tra số lượng orders hiển thị
2. Verify order data accuracy
3. Check formatting (currency, dates)
4. Verify status badges

**Kết quả mong đợi**:
- Orders hiển thị với đầy đủ thông tin
- Currency format: "1.000.000 ₫"
- Date format: "dd/mm/yyyy HH:mm"
- Status badges với colors đúng
- Customer names clickable (if applicable)

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-ORDER-LIST-003: Table headers và columns
**Mô tả**: Kiểm tra table headers và column structure
**Độ ưu tiên**: Medium

**Bước thực hiện**:
1. Verify all expected columns present
2. Check column widths và alignment
3. Test column sorting (if available)
4. Check responsive behavior

**Expected Columns**:
- Checkbox (select)
- Mã đơn hàng
- Khách hàng
- Tổng tiền
- Đã thanh toán
- Trạng thái
- Phương thức TT
- Kênh bán
- Ngày tạo
- Người bán
- Người tạo
- Actions

**Kết quả mong đợi**:
- All columns visible và properly aligned
- Headers clear và descriptive
- Sortable columns có indicators
- Responsive design works

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-ORDER-LIST-004: Empty state handling
**Mô tả**: Kiểm tra hiển thị khi không có orders
**Độ ưu tiên**: Medium

**Bước thực hiện**:
1. Apply filters để no results
2. Check empty state display
3. Verify empty state message
4. Test clear filters functionality

**Kết quả mong đợi**:
- Empty state message hiển thị
- "Không tìm thấy đơn hàng" message
- Clear filters button available
- No broken UI elements

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-ORDER-LIST-005: Loading states
**Mô tả**: Kiểm tra loading indicators
**Độ ưu tiên**: Medium

**Bước thực hiện**:
1. Refresh page và observe loading
2. Apply filters và check loading
3. Change pagination và check loading
4. Verify loading indicators

**Kết quả mong đợi**:
- Loading spinner/skeleton hiển thị
- Loading text appropriate
- UI disabled during loading
- Loading completes properly

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-ORDER-LIST-006: Row actions
**Mô tả**: Kiểm tra actions trên mỗi row
**Độ ưu tiên**: High

**Bước thực hiện**:
1. Hover over order row
2. Check available actions
3. Test action buttons
4. Verify action permissions

**Expected Actions**:
- View details
- Edit order
- Delete order
- Convert to invoice (if applicable)
- Print order

**Kết quả mong đợi**:
- Actions hiển thị on hover/click
- Buttons functional
- Permissions respected
- Tooltips clear

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-ORDER-LIST-007: Checkbox selection
**Mô tả**: Kiểm tra checkbox selection functionality
**Độ ưu tiên**: High

**Bước thực hiện**:
1. Click individual checkboxes
2. Test select all checkbox
3. Check selection state persistence
4. Verify bulk actions appear

**Kết quả mong đợi**:
- Individual selection works
- Select all selects all visible
- Selection state maintained
- Bulk action buttons appear when selected

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-ORDER-LIST-008: Status badges và colors
**Mô tả**: Kiểm tra status display và color coding
**Độ ưu tiên**: Medium

**Bước thực hiện**:
1. Identify orders với different statuses
2. Verify badge colors
3. Check status text
4. Test status consistency

**Expected Status Colors**:
- Draft: Gray
- Processing: Blue
- Completed: Green
- Cancelled: Red
- Pending: Yellow

**Kết quả mong đợi**:
- Colors match status appropriately
- Text readable on colored backgrounds
- Consistent styling across rows
- Status meanings clear

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-ORDER-LIST-009: Customer information display
**Mô tả**: Kiểm tra hiển thị thông tin khách hàng
**Độ ưu tiên**: Medium

**Bước thực hiện**:
1. Check customer name display
2. Verify phone number format
3. Test "Khách lẻ" display
4. Check customer links (if applicable)

**Kết quả mong đợi**:
- Customer names displayed correctly
- Phone numbers formatted properly
- "Khách lẻ" shown for walk-in customers
- Customer info clickable if linked

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-ORDER-LIST-010: Currency và number formatting
**Mô tả**: Kiểm tra format số tiền và numbers
**Độ ưu tiên**: Medium

**Bước thực hiện**:
1. Check total amount formatting
2. Verify paid amount formatting
3. Test large numbers display
4. Check decimal handling

**Kết quả mong đợi**:
- Currency format: "1.000.000 ₫"
- Thousands separators correct
- Decimal places appropriate
- Large numbers readable

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-ORDER-LIST-011: Date và time formatting
**Mô tả**: Kiểm tra format ngày tháng
**Độ ưu tiên**: Medium

**Bước thực hiện**:
1. Check created date format
2. Verify time display
3. Test date sorting (if available)
4. Check timezone handling

**Kết quả mong đợi**:
- Date format: "dd/mm/yyyy"
- Time format: "HH:mm"
- Consistent formatting across rows
- Timezone appropriate for locale

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-ORDER-LIST-012: Responsive design
**Mô tả**: Kiểm tra responsive behavior
**Độ ưu tiên**: Medium

**Bước thực hiện**:
1. Test on desktop (1920x1080)
2. Test on tablet (768x1024)
3. Test on mobile (375x667)
4. Check horizontal scroll

**Kết quả mong đợi**:
- Table responsive on all sizes
- Horizontal scroll available when needed
- Important columns visible on mobile
- UI elements accessible

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

## Test Execution Notes

### Prerequisites
- Login: yukimart@gmail.com / 123456
- URL: http://yukimart.local/admin/orders
- Browser: Chrome (Playwright)
- Test data: At least 10 orders với different statuses

### Test Environment Setup
1. Clear browser cache
2. Login to admin panel
3. Navigate to orders page
4. Verify initial state

### Performance Benchmarks
- Page load: < 3 seconds
- Data loading: < 2 seconds
- UI interactions: < 500ms
- Memory usage: < 100MB

### Success Criteria
- All basic listing functionality works
- Data displays accurately
- UI is responsive và user-friendly
- No console errors
- Performance meets benchmarks

### Failure Criteria
- Page fails to load
- Data missing or incorrect
- UI broken or unresponsive
- Console errors present
- Performance below benchmarks
