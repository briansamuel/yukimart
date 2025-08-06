# Test Case: Comprehensive Workflow - QuickOrder System

## Mô tả
Test cases toàn diện cho các workflow phức tạp trong QuickOrder System, bao gồm end-to-end scenarios và edge cases.

## Test Categories

### 1. Multi-Tab Workflow Tests

#### TC-WORKFLOW-001: Tạo đồng thời nhiều loại đơn
**Mô tả**: Kiểm tra tạo đồng thời Order, Invoice, và Return trong các tab khác nhau
**Độ ưu tiên**: High
**Điều kiện tiên quyết**: Đã đăng nhập, có sản phẩm và hóa đơn sẵn

**Bước thực hiện**:
1. Tạo tab Order, thêm sản phẩm A
2. Tạo tab Invoice, thêm sản phẩm B  
3. Tạo tab Return, chọn hóa đơn có sản phẩm C
4. Chuyển đổi giữa các tab
5. Hoàn thành tất cả các đơn

**Kết quả mong đợi**:
- Tất cả tab hoạt động độc lập
- Data không bị mix giữa các tab
- Tất cả đơn được tạo thành công
- Prefix đúng: ORD, HD, TH

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

#### TC-WORKFLOW-002: Tab switching với data preservation
**Mô tả**: Kiểm tra data được preserve khi switch tab
**Độ ưu tiên**: High

**Bước thực hiện**:
1. Tab 1: Thêm 3 sản phẩm, nhập customer
2. Tab 2: Thêm 2 sản phẩm khác
3. Switch qua lại giữa tab 1 và 2
4. Kiểm tra data còn nguyên

**Kết quả mong đợi**:
- Data tab 1: 3 sản phẩm + customer info
- Data tab 2: 2 sản phẩm
- Calculations đúng cho mỗi tab
- UI state preserved

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### 2. Customer Integration Tests

#### TC-WORKFLOW-003: Customer modal integration
**Mô tả**: Test customer modal trong tất cả tab types
**Độ ưu tiên**: High

**Bước thực hiện**:
1. Tab Order: Chọn customer, mở modal, kiểm tra info
2. Tab Invoice: Chọn customer khác, test modal
3. Tab Return: Customer từ hóa đơn, test modal
4. Kiểm tra customer history, points, debt

**Kết quả mong đợi**:
- Modal hiển thị đúng customer info
- Tabs trong modal hoạt động (Info, History, Debt, Points)
- Data chính xác cho từng customer
- Modal đóng/mở smooth

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

#### TC-WORKFLOW-004: Customer search và autocomplete
**Mô tả**: Test tìm kiếm customer với autocomplete
**Độ ưu tiên**: Medium

**Bước thực hiện**:
1. Nhập 1-2 ký tự tên customer
2. Kiểm tra dropdown suggestions
3. Click chọn customer từ dropdown
4. Test với phone number search
5. Test với customer code search

**Kết quả mong đợi**:
- Autocomplete hiển thị sau 2 ký tự
- Suggestions chính xác và relevant
- Click chọn fill đúng customer info
- Search by phone/code hoạt động

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### 3. Product Search & Barcode Tests

#### TC-WORKFLOW-005: Barcode scanning simulation
**Mô tả**: Test quét mã vạch (simulation với keyboard input)
**Độ ưu tiên**: High

**Bước thực hiện**:
1. Focus vào search box (F3)
2. Nhập barcode của sản phẩm có sẵn
3. Press Enter
4. Kiểm tra sản phẩm được thêm tự động

**Kết quả mong đợi**:
- F3 focus vào search box
- Barcode input tự động search
- Sản phẩm thêm vào cart với quantity = 1
- Search box clear sau khi thêm

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

#### TC-WORKFLOW-006: Product search với multiple criteria
**Mô tả**: Test tìm sản phẩm theo tên, SKU, barcode
**Độ ưu tiên**: Medium

**Bước thực hiện**:
1. Search by product name (partial)
2. Search by exact SKU
3. Search by barcode
4. Search với special characters
5. Search với empty/whitespace

**Kết quả mong đợi**:
- Tất cả search methods hoạt động
- Results relevant và accurate
- No results state hiển thị đúng
- Error handling cho invalid input

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### 4. Calculation & Payment Tests

#### TC-WORKFLOW-007: Complex calculation scenarios
**Mô tả**: Test tính toán phức tạp với discount, tax, multiple items
**Độ ưu tiên**: High

**Bước thực hiện**:
1. Thêm 5 sản phẩm với giá khác nhau
2. Áp dụng discount percentage
3. Áp dụng discount amount
4. Thay đổi quantity các items
5. Remove một số items

**Kết quả mong đợi**:
- Subtotal tính đúng
- Discount áp dụng chính xác
- Final total đúng
- Real-time calculation updates

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

#### TC-WORKFLOW-008: Payment method selection
**Mô tả**: Test chọn phương thức thanh toán
**Độ ưu tiên**: Medium

**Bước thực hiện**:
1. Test Cash payment
2. Test Bank transfer
3. Test Card payment
4. Test E-wallet
5. Test mixed payment methods

**Kết quả mong đợi**:
- Tất cả payment methods selectable
- UI update theo payment method
- Quick amount buttons hoạt động
- Payment calculation đúng

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### 5. Return Order Workflow Tests

#### TC-WORKFLOW-009: Return order complete workflow
**Mô tả**: Test complete workflow tạo return order
**Độ ưu tiên**: High

**Bước thực hiện**:
1. Tạo tab Return
2. Chọn hóa đơn từ modal
3. Kiểm tra items load từ hóa đơn
4. Chọn items để return
5. Thêm exchange items
6. Complete return order

**Kết quả mong đợi**:
- Invoice selection modal hoạt động
- Items load đúng từ hóa đơn
- Return quantities validation
- Exchange items calculation
- Return order tạo thành công với prefix TH

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

#### TC-WORKFLOW-010: Return với exchange items
**Mô tả**: Test return có kèm exchange items
**Độ ưu tiên**: Medium

**Bước thực hiện**:
1. Chọn items để return (value: 100k)
2. Thêm exchange items (value: 80k)
3. Kiểm tra calculation
4. Complete transaction

**Kết quả mong đợi**:
- Return amount: 100k
- Exchange amount: 80k
- Customer receives: 20k
- Inventory updates correctly

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### 6. Error Handling & Edge Cases

#### TC-WORKFLOW-011: Network error handling
**Mô tả**: Test xử lý lỗi network/server
**Độ ưu tiên**: Medium

**Bước thực hiện**:
1. Simulate network disconnect
2. Try to create order
3. Reconnect network
4. Retry operation

**Kết quả mong đợi**:
- Error message hiển thị
- Data không bị mất
- Retry mechanism hoạt động
- User experience smooth

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

#### TC-WORKFLOW-012: Concurrent user scenarios
**Mô tả**: Test multiple users cùng lúc
**Độ ưu tiên**: Low

**Bước thực hiện**:
1. User A tạo order với product X
2. User B cũng tạo order với product X
3. Check inventory conflicts
4. Check order numbering

**Kết quả mong đợi**:
- No inventory oversell
- Unique order numbers
- Proper error messages
- Data consistency

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

## Test Execution Notes

### Prerequisites
- Login: yukimart@gmail.com / 123456
- URL: http://yukimart.local/admin/quick-order
- Browser: Chrome (Playwright)
- Test data: Existing products, customers, invoices

### Test Environment Setup
1. Clear browser cache
2. Login to admin panel
3. Navigate to QuickOrder
4. Verify initial state

### Success Criteria
- All workflows complete successfully
- No JavaScript errors in console
- Data consistency maintained
- UI responsive and user-friendly

### Failure Criteria
- Any workflow fails to complete
- Data corruption or loss
- UI breaks or becomes unresponsive
- Console errors during execution
