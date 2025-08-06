# Test Case: Integration Tests - Payments Module

## Mô tả
Kiểm tra tích hợp của Payments module với các modules khác trong hệ thống, bao gồm Invoices, Returns, Orders, và QuickOrder.

## Test Cases

### TC-PAYMENT-INT-001: Invoice payment integration
**Mô tả**: Kiểm tra tích hợp payment với invoice creation
**Độ ưu tiên**: High
**Điều kiện tiên quyết**: Có invoices và payment records

**Bước thực hiện**:
1. Navigate to invoices page
2. Find invoice với payment
3. Check payment reference
4. Navigate to payments page
5. Find corresponding payment record

**Kết quả mong đợi**:
- Payment record exists với reference_type = "invoice"
- Payment amount matches invoice total
- Payment reference_id matches invoice ID
- Payment prefix format: "TT{invoice_id}"
- Payment status consistent với invoice status

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-PAYMENT-INT-002: QuickOrder invoice payment creation
**Mô tả**: Kiểm tra payment tự động tạo khi tạo invoice từ QuickOrder
**Độ ưu tiên**: High

**Bước thực hiện**:
1. Navigate to QuickOrder
2. Create invoice tab
3. Add products và complete invoice
4. Check payment creation
5. Verify payment details

**Kết quả mong đợi**:
- Payment automatically created
- Payment type = "receipt"
- Payment amount = invoice total
- Payment method matches selection
- Bank account correctly assigned
- Reference links to invoice

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-PAYMENT-INT-003: Return order refund integration
**Mô tả**: Kiểm tra payment refund khi tạo return order
**Độ ưu tiên**: High

**Bước thực hiện**:
1. Create return order từ QuickOrder
2. Complete return với refund
3. Check payment record creation
4. Verify refund payment details

**Kết quả mong đợi**:
- Refund payment created
- Payment type = "disbursement"
- Payment amount = refund amount
- Reference links to return order
- Bank account for refund correct

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-PAYMENT-INT-004: Bank account integration
**Mô tả**: Kiểm tra tích hợp với bank accounts
**Độ ưu tiên**: Medium

**Bước thực hiện**:
1. Check payments với different bank accounts
2. Verify bank account details
3. Test filtering by bank account
4. Check balance calculations

**Kết quả mong đợi**:
- Bank account info displayed correctly
- Account balances accurate
- Filtering by account works
- Account types handled properly

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-PAYMENT-INT-005: Branch shop integration
**Mô tả**: Kiểm tra tích hợp với branch shops
**Độ ưu tiên**: Medium

**Bước thực hiện**:
1. Check payments from different branches
2. Verify branch filtering
3. Test user branch permissions
4. Check branch-specific data

**Kết quả mong đợi**:
- Only branch-accessible payments shown
- Branch filtering accurate
- Permissions respected
- Branch info displayed correctly

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-PAYMENT-INT-006: Customer payment history
**Mô tả**: Kiểm tra payment history của customers
**Độ ưu tiên**: Medium

**Bước thực hiện**:
1. Find customer với payment history
2. Check payments related to customer
3. Verify customer payment tracking
4. Test customer payment summary

**Kết quả mong đợi**:
- Customer payments tracked correctly
- Payment history accurate
- Customer debt calculations correct
- Payment references maintained

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-PAYMENT-INT-007: Multi-module workflow
**Mô tả**: Kiểm tra workflow across multiple modules
**Độ ưu tiên**: High

**Bước thực hiện**:
1. Create order in QuickOrder
2. Convert to invoice
3. Check payment creation
4. Create return from invoice
5. Check refund payment
6. Verify all records linked

**Kết quả mong đợi**:
- Complete workflow functional
- All payments created correctly
- References maintained throughout
- Data consistency across modules

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-PAYMENT-INT-008: Payment method consistency
**Mô tả**: Kiểm tra consistency của payment methods
**Độ ưu tiên**: Medium

**Bước thực hiện**:
1. Check payment methods in QuickOrder
2. Verify same methods in Payments
3. Test method-specific processing
4. Check method validation

**Kết quả mong đợi**:
- Payment methods consistent across modules
- Method-specific rules applied
- Validation consistent
- Processing appropriate for each method

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-PAYMENT-INT-009: Real-time data sync
**Mô tả**: Kiểm tra real-time data synchronization
**Độ ưu tiên**: Medium

**Bước thực hiện**:
1. Open payments page
2. Create invoice in another tab
3. Check if payment appears
4. Test data refresh mechanisms

**Kết quả mong đợi**:
- Data syncs appropriately
- Refresh mechanisms work
- No stale data displayed
- Consistency maintained

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-PAYMENT-INT-010: Error propagation
**Mô tả**: Kiểm tra error handling across modules
**Độ ưu tiên**: Medium

**Bước thực hiện**:
1. Simulate payment creation failure
2. Check impact on invoice creation
3. Test error recovery
4. Verify data integrity

**Kết quả mong đợi**:
- Errors handled gracefully
- No partial data corruption
- Recovery mechanisms work
- User informed appropriately

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-PAYMENT-INT-011: Audit trail integration
**Mô tả**: Kiểm tra audit trail cho payments
**Độ ưu tiên**: Low

**Bước thực hiện**:
1. Create/edit/delete payments
2. Check audit logs
3. Verify audit trail completeness
4. Test audit log filtering

**Kết quả mong đợi**:
- All payment actions logged
- Audit trail complete
- User actions tracked
- Timestamps accurate

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-PAYMENT-INT-012: Reporting integration
**Mô tả**: Kiểm tra integration với reporting system
**Độ ưu tiên**: Medium

**Bước thực hiện**:
1. Generate payment reports
2. Check data accuracy
3. Verify calculations
4. Test different report types

**Kết quả mong đợi**:
- Reports accurate
- Calculations correct
- Data consistent với payments
- Export functionality works

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-PAYMENT-INT-013: Permission integration
**Mô tả**: Kiểm tra permission system integration
**Độ ưu tiên**: Medium

**Bước thực hiện**:
1. Test với different user roles
2. Check permission enforcement
3. Verify access controls
4. Test permission inheritance

**Kết quả mong đợi**:
- Permissions enforced correctly
- Role-based access working
- No unauthorized operations
- Consistent across modules

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-PAYMENT-INT-014: Data migration compatibility
**Mô tả**: Kiểm tra compatibility với data migration
**Độ ưu tiên**: Low

**Bước thực hiện**:
1. Check legacy payment data
2. Verify migration completeness
3. Test backward compatibility
4. Check data format consistency

**Kết quả mong đợi**:
- Legacy data accessible
- Migration complete
- No data loss
- Format consistency maintained

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-PAYMENT-INT-015: API integration
**Mô tả**: Kiểm tra API integration cho payments
**Độ ưu tiên**: Low

**Bước thực hiện**:
1. Test payment API endpoints
2. Check API response format
3. Verify API authentication
4. Test API error handling

**Kết quả mong đợi**:
- API endpoints functional
- Response format consistent
- Authentication working
- Error handling appropriate

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

## Test Execution Notes

### Prerequisites
- Login: yukimart@gmail.com / 123456
- Multiple modules accessible
- Test data across modules
- Different user roles available

### Test Environment Setup
1. Ensure cross-module data exists
2. Set up different user permissions
3. Prepare test scenarios
4. Configure bank accounts

### Integration Points to Verify
- Payment ↔ Invoice
- Payment ↔ Return Order
- Payment ↔ Bank Account
- Payment ↔ Customer
- Payment ↔ Branch Shop
- Payment ↔ User Permissions

### Success Criteria
- All integrations functional
- Data consistency maintained
- No broken references
- Performance acceptable
- Error handling robust

### Failure Criteria
- Integration failures
- Data inconsistency
- Broken references
- Performance issues
- Poor error handling
