# Test Case: Edge Cases - QuickOrder System

## Mô tả
Test cases cho các trường hợp biên và tình huống đặc biệt trong QuickOrder System.

## Test Categories

### 1. Data Boundary Tests

#### TC-EDGE-001: Maximum items per order
**Mô tả**: Test giới hạn số lượng items trong một đơn
**Độ ưu tiên**: Medium

**Bước thực hiện**:
1. Thêm 50+ items vào một tab
2. Kiểm tra performance
3. Test scroll behavior
4. Try to create order

**Kết quả mong đợi**:
- System handle large number of items
- UI remains responsive
- Scroll works smoothly
- Order creation successful

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

#### TC-EDGE-002: Maximum quantity per item
**Mô tả**: Test số lượng tối đa cho một sản phẩm
**Độ ưu tiên**: Medium

**Bước thực hiện**:
1. Thêm sản phẩm với quantity = 999999
2. Test với quantity > stock
3. Test với quantity = 0
4. Test với negative quantity

**Kết quả mong đợi**:
- Validation cho max quantity
- Warning khi > stock
- Error cho invalid quantities
- Proper error messages

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

#### TC-EDGE-003: Very long product names/descriptions
**Mô tả**: Test hiển thị với tên sản phẩm rất dài
**Độ ưu tiên**: Low

**Bước thực hiện**:
1. Thêm sản phẩm có tên > 100 characters
2. Kiểm tra hiển thị trong cart
3. Test trong invoice/order creation
4. Check responsive behavior

**Kết quả mong đợi**:
- Text truncation hoặc wrap properly
- UI không bị break
- Tooltip hiển thị full name
- Responsive design maintained

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### 2. Empty State Tests

#### TC-EDGE-004: Empty cart operations
**Mô tả**: Test operations với cart trống
**Độ ưu tiên**: High

**Bước thực hiện**:
1. Try to create order với cart trống
2. Try to create invoice với cart trống
3. Check button states
4. Check validation messages

**Kết quả mong đợi**:
- Create buttons disabled
- Clear validation messages
- No JavaScript errors
- Proper user guidance

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

#### TC-EDGE-005: No search results
**Mô tả**: Test khi search không có kết quả
**Độ ưu tiên**: Medium

**Bước thực hiện**:
1. Search với keyword không tồn tại
2. Search với special characters
3. Search với very long string
4. Check empty state display

**Kết quả mong đợi**:
- "No results found" message
- Suggestions for alternative search
- No JavaScript errors
- Search box remains functional

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

#### TC-EDGE-006: No invoices for return
**Mô tả**: Test return tab khi không có hóa đơn
**Độ ưu tiên**: Medium

**Bước thực hiện**:
1. Tạo tab Return
2. Open invoice selection modal
3. Check khi filter không có results
4. Test với user không có invoices

**Kết quả mong đợi**:
- Empty state trong modal
- Clear message về no invoices
- Modal vẫn functional
- Proper guidance for user

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### 3. Invalid Input Tests

#### TC-EDGE-007: Invalid customer data
**Mô tả**: Test với customer data không hợp lệ
**Độ ưu tiên**: Medium

**Bước thực hiện**:
1. Nhập customer name với special chars
2. Nhập phone number không hợp lệ
3. Test với empty customer fields
4. Test customer search với invalid input

**Kết quả mong đợi**:
- Input validation working
- Error messages clear
- No system crashes
- Data sanitization proper

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

#### TC-EDGE-008: Invalid discount values
**Mô tả**: Test với giá trị discount không hợp lệ
**Độ ưu tiên**: High

**Bước thực hiện**:
1. Nhập discount > 100%
2. Nhập negative discount
3. Nhập discount > total amount
4. Test với non-numeric input

**Kết quả mong đợi**:
- Validation prevents invalid values
- Error messages displayed
- Calculations remain correct
- UI doesn't break

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

#### TC-EDGE-009: Invalid payment amounts
**Mô tả**: Test với số tiền thanh toán không hợp lệ
**Độ ưu tiên**: High

**Bước thực hiện**:
1. Nhập payment < total amount
2. Nhập negative payment
3. Test với very large numbers
4. Test với decimal precision

**Kết quả mong đợi**:
- Payment validation working
- Change calculation correct
- Error handling proper
- No calculation errors

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### 4. Browser & Performance Edge Cases

#### TC-EDGE-010: Browser back/forward buttons
**Mô tả**: Test browser navigation buttons
**Độ ưu tiên**: Medium

**Bước thực hiện**:
1. Thêm items vào cart
2. Navigate away using browser back
3. Return using browser forward
4. Check data preservation

**Kết quả mong đợi**:
- Data preserved hoặc proper warning
- No JavaScript errors
- Graceful handling of navigation
- User experience smooth

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

#### TC-EDGE-011: Browser refresh during operation
**Mô tả**: Test refresh browser trong quá trình operation
**Độ ưu tiên**: Medium

**Bước thực hiện**:
1. Thêm items vào cart
2. Start creating order
3. Refresh browser mid-process
4. Check data recovery

**Kết quả mong đợi**:
- Graceful handling of refresh
- Data loss warning if applicable
- No corrupted state
- Clean restart possible

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

#### TC-EDGE-012: Multiple tabs same page
**Mô tả**: Test mở multiple browser tabs cùng QuickOrder
**Độ ưu tiên**: Low

**Bước thực hiện**:
1. Mở 2 browser tabs QuickOrder
2. Create orders trong cả 2 tabs
3. Check data conflicts
4. Check order numbering

**Kết quả mong đợi**:
- No data conflicts
- Unique order numbers
- Independent operations
- No session conflicts

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### 5. Network & Connectivity Edge Cases

#### TC-EDGE-013: Slow network conditions
**Mô tả**: Test với network chậm
**Độ ưu tiên**: Medium

**Bước thực hiện**:
1. Simulate slow network (throttling)
2. Try product search
3. Try order creation
4. Check loading states

**Kết quả mong đợi**:
- Loading indicators shown
- Timeout handling proper
- User feedback clear
- No hanging operations

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

#### TC-EDGE-014: Intermittent connectivity
**Mô tả**: Test với kết nối không ổn định
**Độ ưu tiên**: Medium

**Bước thực hiện**:
1. Start operation
2. Disconnect network briefly
3. Reconnect network
4. Check operation completion

**Kết quả mong đợi**:
- Retry mechanism works
- Error recovery proper
- Data integrity maintained
- User informed of issues

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### 6. Session & Authentication Edge Cases

#### TC-EDGE-015: Session timeout during operation
**Mô tả**: Test khi session timeout trong quá trình làm việc
**Độ ưu tiên**: High

**Bước thực hiện**:
1. Thêm items vào cart
2. Wait for session timeout
3. Try to create order
4. Check authentication handling

**Kết quả mong đợi**:
- Redirect to login page
- Data preservation if possible
- Clear error message
- Smooth re-authentication

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

#### TC-EDGE-016: Permission changes during session
**Mô tả**: Test khi quyền user thay đổi
**Độ ưu tiên**: Low

**Bước thực hiện**:
1. Start with full permissions
2. Admin changes user permissions
3. Try to complete operations
4. Check permission enforcement

**Kết quả mong đợi**:
- Permission checks enforced
- Appropriate error messages
- No unauthorized operations
- Graceful degradation

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### 7. Data Consistency Edge Cases

#### TC-EDGE-017: Concurrent inventory updates
**Mô tả**: Test khi inventory thay đổi đồng thời
**Độ ưu tiên**: High

**Bước thực hiện**:
1. Add product to cart (stock = 5)
2. Another user buys same product (stock = 2)
3. Try to order quantity = 4
4. Check inventory validation

**Kết quả mong đợi**:
- Real-time stock validation
- Error message for insufficient stock
- Suggested quantity adjustment
- No overselling

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

#### TC-EDGE-018: Product price changes
**Mô tả**: Test khi giá sản phẩm thay đổi
**Độ ưu tiên**: Medium

**Bước thực hiện**:
1. Add product to cart (price = 100k)
2. Admin changes price to 120k
3. Complete order
4. Check price consistency

**Kết quả mong đợi**:
- Price locked when added to cart
- Or notification of price change
- Consistent pricing in order
- Clear user communication

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

## Test Execution Guidelines

### Test Environment
- Use Chrome DevTools for network throttling
- Test with different screen sizes
- Clear cache between tests
- Monitor console for errors

### Data Preparation
- Ensure test products with various states
- Have customers with different data
- Prepare invoices for return testing
- Set up different user permissions

### Success Criteria
- All edge cases handled gracefully
- No system crashes or data corruption
- Clear error messages for users
- Consistent user experience

### Documentation
- Record all edge cases found
- Document workarounds if needed
- Note performance impacts
- Suggest improvements
