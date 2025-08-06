# Test Case: Customer Modal - Invoices Module

## Mô tả
Kiểm tra chức năng Customer Modal trong trang Invoices, bao gồm hiển thị thông tin khách hàng, tabs, và các interactions.

## Test Cases

### TC-INVOICE-MODAL-001: Mở customer modal
**Mô tả**: Kiểm tra mở customer modal từ invoice listing
**Độ ưu tiên**: High
**Điều kiện tiên quyết**: Có invoices với customers trong database

**Bước thực hiện**:
1. Navigate to invoices page
2. Click vào customer name trong invoice row
3. Verify modal opens
4. Check modal content loading

**Kết quả mong đợi**:
- Modal opens smoothly
- Customer information loads
- Modal title shows customer name
- Loading states appropriate
- Modal backdrop functional

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-INVOICE-MODAL-002: Customer information tab
**Mô tả**: Kiểm tra tab "Thông tin" trong customer modal
**Độ ưu tiên**: High

**Bước thực hiện**:
1. Open customer modal
2. Verify "Thông tin" tab is active by default
3. Check all customer fields displayed
4. Verify data accuracy

**Expected Fields**:
- Mã khách hàng
- Tên khách hàng
- Điện thoại
- Email
- Địa chỉ
- Loại khách hàng
- Nhóm khách hàng
- Ngày sinh
- Giới tính
- Ghi chú

**Kết quả mong đợi**:
- All fields displayed correctly
- Data matches database
- Fields properly formatted
- Read-only mode appropriate

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-INVOICE-MODAL-003: Customer statistics display
**Mô tả**: Kiểm tra hiển thị thống kê khách hàng
**Độ ưu tiên**: High

**Bước thực hiện**:
1. Open customer modal
2. Check statistics section
3. Verify calculations
4. Test with different customers

**Expected Statistics**:
- Tổng nợ (debt amount)
- Điểm tích lũy (loyalty points)
- Số đơn hàng
- Tổng chi tiêu
- Chi nhánh hiện tại

**Kết quả mong đợi**:
- Statistics calculated correctly
- Numbers formatted properly
- Real-time data displayed
- Consistent across customers

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-INVOICE-MODAL-004: Lịch sử bán/trả hàng tab
**Mô tả**: Kiểm tra tab "Lịch sử bán/trả hàng"
**Độ ưu tiên**: High

**Bước thực hiện**:
1. Open customer modal
2. Click "Lịch sử bán/trả hàng" tab
3. Verify invoice history loads
4. Check data accuracy và formatting

**Kết quả mong đợi**:
- Tab switches successfully
- Invoice history displays
- Data includes: Invoice code, Date, Amount, Status
- Links to invoices functional
- Pagination if many records

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-INVOICE-MODAL-005: Dư nợ tab
**Mô tả**: Kiểm tra tab "Dư nợ"
**Độ ưu tiên**: Medium

**Bước thực hiện**:
1. Open customer modal
2. Click "Dư nợ" tab
3. Verify debt information loads
4. Check debt calculations

**Kết quả mong đợi**:
- Debt information displayed
- Outstanding amounts shown
- Payment history if applicable
- Calculations accurate
- Currency formatting correct

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-INVOICE-MODAL-006: Lịch sử điểm tab
**Mô tả**: Kiểm tra tab "Lịch sử điểm"
**Độ ưu tiên**: Medium

**Bước thực hiện**:
1. Open customer modal
2. Click "Lịch sử điểm" tab
3. Verify points history loads
4. Check point transactions

**Kết quả mong đợi**:
- Points history displayed
- Transactions show: Date, Type, Points, Description
- Running balance calculated
- Data chronologically ordered

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-INVOICE-MODAL-007: Modal navigation và controls
**Mô tả**: Kiểm tra navigation và controls trong modal
**Độ ưu tiên**: Medium

**Bước thực hiện**:
1. Open customer modal
2. Test tab switching
3. Test close button
4. Test backdrop click
5. Test ESC key

**Kết quả mong đợi**:
- Tab switching smooth
- Close button works
- Backdrop click closes modal
- ESC key closes modal
- No data loss on close

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-INVOICE-MODAL-008: Modal với "Khách lẻ"
**Mô tả**: Kiểm tra modal với walk-in customers
**Độ ưu tiên**: Medium

**Bước thực hiện**:
1. Find invoice với "Khách lẻ"
2. Try to click customer name
3. Check modal behavior
4. Verify appropriate handling

**Kết quả mong đợi**:
- Modal either doesn't open for "Khách lẻ"
- Or shows appropriate message
- No JavaScript errors
- User feedback clear

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-INVOICE-MODAL-009: Modal responsive design
**Mô tả**: Kiểm tra modal trên different screen sizes
**Độ ưu tiên**: Medium

**Bước thực hiện**:
1. Open modal on desktop
2. Test on tablet size
3. Test on mobile size
4. Check content adaptation

**Kết quả mong đợi**:
- Modal responsive on all sizes
- Content readable và accessible
- Tabs work on mobile
- Close button accessible

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-INVOICE-MODAL-010: Modal performance
**Mô tả**: Kiểm tra performance của modal
**Độ ưu tiên**: Low

**Bước thực hiện**:
1. Open modal với customer có nhiều history
2. Measure load time
3. Test tab switching speed
4. Check memory usage

**Kết quả mong đợi**:
- Modal opens < 1 second
- Tab switching < 500ms
- No memory leaks
- Smooth animations

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-INVOICE-MODAL-011: Modal data refresh
**Mô tả**: Kiểm tra data refresh trong modal
**Độ ưu tiên**: Low

**Bước thực hiện**:
1. Open customer modal
2. Keep modal open
3. Create new invoice for same customer (other tab)
4. Check if modal data updates

**Kết quả mong đợi**:
- Data refreshes automatically or
- Manual refresh option available
- Real-time updates if applicable
- Data consistency maintained

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-INVOICE-MODAL-012: Modal error handling
**Mô tả**: Kiểm tra error handling trong modal
**Độ ưu tiên**: Medium

**Bước thực hiện**:
1. Try to open modal với invalid customer
2. Simulate network error during load
3. Test với customer không có permissions
4. Check error messages

**Kết quả mong đợi**:
- Appropriate error messages
- Modal handles errors gracefully
- No broken UI states
- User can recover from errors

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-INVOICE-MODAL-013: Multiple modal instances
**Mô tả**: Kiểm tra multiple modals
**Độ ưu tiên**: Low

**Bước thực hiện**:
1. Open customer modal
2. Try to open another customer modal
3. Check modal stacking behavior
4. Test close behavior

**Kết quả mong đợi**:
- Only one modal open at a time or
- Proper modal stacking
- Close behavior consistent
- No UI conflicts

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-INVOICE-MODAL-014: Modal accessibility
**Mô tả**: Kiểm tra accessibility của modal
**Độ ưu tiên**: Low

**Bước thực hiện**:
1. Open modal
2. Test keyboard navigation
3. Test screen reader compatibility
4. Check focus management

**Kết quả mong đợi**:
- Keyboard navigation works
- Focus trapped in modal
- ARIA labels appropriate
- Screen reader friendly

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-INVOICE-MODAL-015: Modal integration với invoice actions
**Mô tả**: Kiểm tra modal integration với invoice operations
**Độ ưu tiên**: Medium

**Bước thực hiện**:
1. Open customer modal from invoice
2. Note customer info
3. Perform invoice actions (edit, delete)
4. Check modal behavior

**Kết quả mong đợi**:
- Modal remains functional
- Data consistency maintained
- No conflicts với invoice operations
- Proper state management

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

## Test Execution Notes

### Prerequisites
- Login: yukimart@gmail.com / 123456
- URL: http://yukimart.local/admin/invoices
- Browser: Chrome (Playwright)
- Test data: Invoices với different customers

### Test Environment Setup
1. Ensure customers have varied data
2. Some customers with extensive history
3. Some customers with debt/points
4. Include "Khách lẻ" invoices

### Performance Benchmarks
- Modal open: < 1 second
- Tab switching: < 500ms
- Data loading: < 2 seconds
- Memory usage: Stable

### Success Criteria
- All modal functionality works
- Data displays accurately
- UI responsive và accessible
- No console errors
- Performance meets benchmarks

### Failure Criteria
- Modal fails to open
- Data missing or incorrect
- UI broken or unresponsive
- Console errors present
- Performance issues
