# Danh sách Test Case cho Trang Returns (Trả hàng)

Tài liệu này chứa các test case để kiểm tra chức năng của trang Returns Management.

## Danh mục Test Case

### 1. **Kiểm tra Cơ bản**
1. [Kiểm tra Hiển thị Danh sách](basic-listing-tests.md) - Load, render, display returns
2. [Kiểm tra Phân trang](pagination-tests.md) - Navigation, page size, data loading ⭐ (Vừa fix)
3. [Kiểm tra Tìm kiếm](search-tests.md) - Tìm kiếm return orders
4. [Kiểm tra Xuất Excel](export-tests.md) - Export functionality

### 2. **Kiểm tra Return Creation Workflow**
5. [Kiểm tra Tạo Return từ QuickOrder](quickorder-return-tests.md) - Create via QuickOrder ⭐ (Core)
6. [Kiểm tra Chọn Hóa đơn](invoice-selection-tests.md) - Invoice selection modal ⭐ (Vừa fix)
7. [Kiểm tra Return Items](return-items-tests.md) - Select items to return
8. [Kiểm tra Exchange Items](exchange-items-tests.md) - Add exchange items

### 3. **Kiểm tra Calculations**
9. [Kiểm tra Return Calculations](return-calculations-tests.md) - Return amount calculations
10. [Kiểm tra Exchange Calculations](exchange-calculations-tests.md) - Exchange calculations
11. [Kiểm tra Refund Calculations](refund-calculations-tests.md) - Final refund amount
12. [Kiểm tra Fee Calculations](fee-calculations-tests.md) - Return fees

### 4. **Kiểm tra Status Management**
13. [Kiểm tra Status Workflow](status-workflow-tests.md) - Return status transitions
14. [Kiểm tra Status Validation](status-validation-tests.md) - Status change rules
15. [Kiểm tra Approval Process](approval-process-tests.md) - Return approval workflow

### 5. **Kiểm tra Integration**
16. [Kiểm tra Invoice Integration](invoice-integration-tests.md) - Link với original invoice
17. [Kiểm tra Inventory Integration](inventory-integration-tests.md) - Stock adjustments
18. [Kiểm tra Payment Integration](payment-integration-tests.md) - Refund payments
19. [Kiểm tra Customer Integration](customer-integration-tests.md) - Customer data sync

### 6. **Kiểm tra Filters & Search**
20. [Kiểm tra Time Filter](time-filter-tests.md) - Date range filtering
21. [Kiểm tra Status Filter](status-filter-tests.md) - Return status filtering
22. [Kiểm tra Customer Filter](customer-filter-tests.md) - Filter by customer
23. [Kiểm tra Invoice Filter](invoice-filter-tests.md) - Filter by original invoice

### 7. **Kiểm tra UI/UX**
24. [Kiểm tra Responsive Design](responsive-tests.md) - Mobile/tablet compatibility
25. [Kiểm tra Detail Panels](detail-panel-tests.md) - Expand return details
26. [Kiểm tra Loading States](loading-states-tests.md) - Loading indicators
27. [Kiểm tra Error States](error-states-tests.md) - Error handling

### 8. **Kiểm tra Bulk Actions**
28. [Kiểm tra Bulk Selection](bulk-selection-tests.md) - Select multiple returns
29. [Kiểm tra Bulk Status Update](bulk-status-tests.md) - Update status in bulk
30. [Kiểm tra Bulk Approval](bulk-approval-tests.md) - Approve multiple returns
31. [Kiểm tra Bulk Export](bulk-export-tests.md) - Export selected returns

### 9. **Kiểm tra Business Rules**
32. [Kiểm tra Return Limits](return-limits-tests.md) - Quantity và time limits
33. [Kiểm tra Return Eligibility](return-eligibility-tests.md) - Item return eligibility
34. [Kiểm tra Partial Returns](partial-returns-tests.md) - Partial quantity returns
35. [Kiểm tra Multiple Returns](multiple-returns-tests.md) - Multiple returns from same invoice

### 10. **Kiểm tra Edge Cases**
36. [Kiểm tra Edge Cases](edge-cases-tests.md) - Boundary conditions
37. [Kiểm tra Error Handling](error-handling-tests.md) - Error scenarios
38. [Kiểm tra Performance](performance-tests.md) - Large datasets
39. [Kiểm tra Security](security-tests.md) - Authorization và validation

## Test Environment

### Setup Requirements
- **URL**: http://yukimart.local/admin/returns
- **Login**: yukimart@gmail.com / 123456
- **Browser**: Chrome (Playwright)
- **Test Data**: Existing returns và invoices in database

### Test Data Requirements

#### Returns
- Returns với các status khác nhau (processing, completed, cancelled, rejected)
- Returns từ các customers khác nhau
- Returns với exchange items
- Returns với different refund amounts

#### Related Data
- Invoices với paid status (for creating returns)
- Products với stock levels
- Customers với return history
- Branch shops với return permissions

## Test Execution Strategy

### Phase 1: Core Functionality (Days 1-2)
1. Basic listing và pagination ⭐ (Fixed)
2. Return creation workflow
3. Invoice selection ⭐ (Fixed)
4. Calculations testing

### Phase 2: Advanced Features (Days 3-4)
1. Status management
2. Integration testing
3. Bulk actions
4. Business rules validation

### Phase 3: Quality Assurance (Days 5-6)
1. UI/UX testing
2. Edge cases
3. Performance testing
4. Security testing

## Success Criteria

### Functional Requirements
- ✅ Return creation workflow complete
- ✅ Invoice selection works correctly ⭐
- ✅ Calculations accurate
- ✅ Status transitions follow rules
- ✅ Integration với inventory/payments works

### Performance Requirements
- ✅ Page load < 3 seconds
- ✅ Return creation < 5 seconds
- ✅ Invoice selection < 2 seconds
- ✅ Bulk operations < 10 seconds

### UI/UX Requirements
- ✅ Responsive design works
- ✅ Loading states clear
- ✅ Error messages helpful
- ✅ Workflow intuitive

## Recent Fixes ⭐

### Pagination Fix (Completed)
- **Issue**: Pagination hiển thị 8 items nhưng không load data
- **Fix**: Updated ReturnController response format
- **Status**: ✅ Fixed và tested

### Invoice Selection Fix (Completed)
- **Issue**: Modal load tất cả invoices thay vì theo chi nhánh
- **Fix**: Improved branch shop filtering logic
- **Status**: ✅ Fixed và tested

## Test Tools

### Playwright Tests
- UI interaction testing
- End-to-end return workflows
- Cross-browser compatibility
- Visual regression testing

### Unit Tests
- Controller logic testing
- Service layer testing
- Model validation testing
- API endpoint testing

### Integration Tests
- Return-Invoice integration
- Inventory updates
- Payment processing
- Customer data sync

## Getting Started

1. **Start với basic listing tests** (foundation)
2. **Test pagination fix** ⭐ (recently fixed)
3. **Test invoice selection fix** ⭐ (recently fixed)
4. **Follow return creation workflow**
5. **Document all findings** with screenshots

## Notes

- Focus on recently fixed features first ⭐
- Test return workflow end-to-end
- Verify business rules compliance
- Check integration với other modules
- Document any regressions found
