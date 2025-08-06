# Test Case: QuickOrder Return Creation - Returns Module

## Mô tả
Kiểm tra chức năng tạo return order thông qua QuickOrder system, bao gồm complete workflow từ invoice selection đến return completion.

## Test Cases

### TC-RETURN-QO-001: Access return creation
**Mô tả**: Kiểm tra truy cập tạo return từ returns listing
**Độ ưu tiên**: High
**Điều kiện tiên quyết**: Đã đăng nhập, có invoices trong database

**Bước thực hiện**:
1. Navigate to http://yukimart.local/admin/returns
2. Click "Tạo đơn trả hàng" button
3. Verify redirect to QuickOrder với type=return
4. Check initial state

**Kết quả mong đợi**:
- Redirect to /admin/quick-order?type=return
- Tab "Trả hàng 1" được tạo và active
- Invoice selection modal tự động mở
- UI hiển thị return-specific elements

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-RETURN-QO-002: Invoice selection modal ⭐
**Mô tả**: Kiểm tra modal chọn hóa đơn (recently fixed)
**Độ ưu tiên**: High

**Bước thực hiện**:
1. Verify modal opens automatically
2. Check invoice list loads
3. Verify only branch-specific invoices shown ⭐
4. Test search và filter functionality

**Kết quả mong đợi**:
- Modal opens với title "Chọn hóa đơn để trả hàng"
- Only invoices from user's branch shops shown ⭐
- Invoices have status "paid"
- Search và time filter work
- Loading states appropriate

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-RETURN-QO-003: Invoice selection và loading
**Mô tả**: Kiểm tra chọn hóa đơn và load items
**Độ ưu tiên**: High

**Bước thực hiện**:
1. Select an invoice from modal
2. Click "Chọn" button
3. Verify modal closes
4. Check invoice items load into return tab

**Kết quả mong đợi**:
- Modal closes smoothly
- Invoice info displays in return tab
- Customer info populated from invoice
- Invoice items load với returnable quantities
- Return tab UI updates appropriately

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-RETURN-QO-004: Return items selection
**Mô tả**: Kiểm tra chọn items để return
**Độ ưu tiên**: High

**Bước thực hiện**:
1. After invoice loaded, check items list
2. Select items to return
3. Adjust return quantities
4. Verify calculations update

**Kết quả mong đợi**:
- All invoice items displayed
- Returnable quantities shown correctly
- Quantity selectors functional
- Return calculations update real-time
- Cannot exceed original quantities

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-RETURN-QO-005: Exchange items addition
**Mô tả**: Kiểm tra thêm exchange items
**Độ ưu tiên**: Medium

**Bước thực hiện**:
1. Use exchange search box (F7)
2. Search for products to exchange
3. Add exchange items
4. Verify exchange calculations

**Kết quả mong đợi**:
- F7 focuses exchange search
- Product search works for exchange
- Exchange items added to separate section
- Exchange calculations correct
- Net refund amount calculated

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-RETURN-QO-006: Return calculations
**Mô tả**: Kiểm tra tính toán return amounts
**Độ ưu tiên**: High

**Bước thực hiện**:
1. Select return items với different values
2. Add exchange items
3. Apply return fees if applicable
4. Verify final calculations

**Expected Calculations**:
- Tổng giá gốc hàng mua
- Tổng tiền hàng trả
- Giảm giá (if applicable)
- Phí trả hàng
- Hoàn trả thu khác
- Tổng tiền trả
- Khách cần trả/thanh toán

**Kết quả mong đợi**:
- All calculations accurate
- Real-time updates
- Currency formatting correct
- Net amount calculated properly

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-RETURN-QO-007: Payment method selection
**Mô tả**: Kiểm tra chọn phương thức thanh toán refund
**Độ ưu tiên**: Medium

**Bước thực hiện**:
1. Select payment method for refund
2. Test different payment methods
3. Verify payment calculations
4. Check quick amount buttons

**Kết quả mong đợi**:
- Payment methods selectable
- Refund amount calculations correct
- Quick amount buttons functional
- Payment method affects processing

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-RETURN-QO-008: Customer modal integration
**Mô tả**: Kiểm tra customer modal trong return tab
**Độ ưu tiên**: Medium

**Bước thực hiện**:
1. After invoice selected, customer info shown
2. Click on customer name
3. Verify customer modal opens
4. Check customer information accuracy

**Kết quả mong đợi**:
- Customer modal opens correctly
- Customer info matches invoice
- All modal tabs functional
- Modal closes properly

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-RETURN-QO-009: Return order creation
**Mô tả**: Kiểm tra tạo return order
**Độ ưu tiên**: High

**Bước thực hiện**:
1. Complete return setup
2. Click "TRẢ HÀNG" button
3. Verify return order creation
4. Check return order details

**Kết quả mong đợi**:
- Return order created successfully
- Return number với prefix "TH"
- Status set appropriately
- Inventory adjustments made
- Payment records created
- Tab cleared after creation

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-RETURN-QO-010: Multiple return tabs
**Mô tả**: Kiểm tra multiple return tabs
**Độ ưu tiên**: Medium

**Bước thực hiện**:
1. Create first return tab
2. Create second return tab
3. Switch between tabs
4. Verify data isolation

**Kết quả mong đợi**:
- Multiple return tabs supported
- Each tab independent
- Data preserved when switching
- No cross-tab contamination

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-RETURN-QO-011: Return validation rules
**Mô tả**: Kiểm tra validation rules
**Độ ưu tiên**: High

**Bước thực hiện**:
1. Try to create return without selecting invoice
2. Try to return more than available quantity
3. Try to return already returned items
4. Test other validation scenarios

**Kết quả mong đợi**:
- Cannot create return without invoice
- Quantity validation enforced
- Already returned items handled
- Clear validation messages
- No invalid returns created

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-RETURN-QO-012: Return với "Khách lẻ"
**Mô tả**: Kiểm tra return cho walk-in customers
**Độ ưu tiên**: Medium

**Bước thực hiện**:
1. Select invoice với "Khách lẻ"
2. Complete return process
3. Verify customer handling
4. Check return order creation

**Kết quả mong đợi**:
- "Khách lẻ" invoices selectable
- Customer info shows "Khách lẻ"
- Return process works normally
- Return order created correctly

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-RETURN-QO-013: Error handling
**Mô tả**: Kiểm tra error handling scenarios
**Độ ưu tiên**: Medium

**Bước thực hiện**:
1. Simulate network errors
2. Try invalid operations
3. Test với insufficient permissions
4. Check error recovery

**Kết quả mong đợi**:
- Appropriate error messages
- Graceful error handling
- No data corruption
- Recovery mechanisms work

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-RETURN-QO-014: Performance testing
**Mô tả**: Kiểm tra performance của return workflow
**Độ ưu tiên**: Low

**Bước thực hiện**:
1. Test với large invoice (many items)
2. Test với customer có nhiều invoices
3. Measure response times
4. Check memory usage

**Kết quả mong đợi**:
- Invoice selection < 2 seconds
- Item loading < 3 seconds
- Return creation < 5 seconds
- No memory leaks

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-RETURN-QO-015: Integration với returns listing
**Mô tả**: Kiểm tra integration với returns management
**Độ ưu tiên**: Medium

**Bước thực hiện**:
1. Create return via QuickOrder
2. Navigate to returns listing
3. Verify new return appears
4. Check return details accuracy

**Kết quả mong đợi**:
- New return appears in listing
- All data accurate
- Status correct
- Links functional

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

## Test Execution Notes

### Prerequisites
- Login: yukimart@gmail.com / 123456
- URL: http://yukimart.local/admin/returns
- Browser: Chrome (Playwright)
- Test data: Paid invoices với returnable items

### Test Environment Setup
1. Ensure paid invoices exist
2. Products với adequate stock
3. Customers với purchase history
4. Branch shops configured properly

### Performance Benchmarks
- Invoice selection modal: < 2 seconds
- Item loading: < 3 seconds
- Return creation: < 5 seconds
- UI interactions: < 500ms

### Success Criteria
- Complete return workflow functional
- Invoice selection works correctly ⭐
- Calculations accurate
- Integration seamless
- Performance acceptable

### Failure Criteria
- Workflow breaks at any step
- Incorrect calculations
- Data corruption
- Performance issues
- Console errors
