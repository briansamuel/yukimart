# Danh sách Test Case cho Trang Orders (Đơn hàng)

Tài liệu này chứa các test case để kiểm tra chức năng của trang Orders Management.

## Danh mục Test Case

### 1. **Kiểm tra Cơ bản**
1. [Kiểm tra Hiển thị Danh sách](basic-listing-tests.md) - Load, render, display orders
2. [Kiểm tra Phân trang](pagination-tests.md) - Navigation, page size, data loading
3. [Kiểm tra Tìm kiếm](search-tests.md) - Tìm kiếm đơn hàng theo nhiều criteria
4. [Kiểm tra Xuất Excel](export-excel-tests.md) - Export functionality

### 2. **Kiểm tra CRUD Operations**
5. [Kiểm tra Tạo Đơn hàng](order-creation-tests.md) - Create new orders
6. [Kiểm tra Xem Chi tiết](order-detail-tests.md) - View order details
7. [Kiểm tra Cập nhật](order-update-tests.md) - Edit order information
8. [Kiểm tra Xóa](order-deletion-tests.md) - Delete orders

### 3. **Kiểm tra Filters & Sorting**
9. [Kiểm tra Bộ lọc Thời gian](time-filter-tests.md) - Date range filtering
10. [Kiểm tra Bộ lọc Trạng thái](status-filter-tests.md) - Order status filtering
11. [Kiểm tra Bộ lọc Người tạo](creator-filter-tests.md) - Filter by creator
12. [Kiểm tra Sắp xếp](sorting-tests.md) - Column sorting functionality

### 4. **Kiểm tra Bulk Actions**
13. [Kiểm tra Bulk Selection](bulk-selection-tests.md) - Select multiple orders
14. [Kiểm tra Bulk Status Update](bulk-status-tests.md) - Update status in bulk
15. [Kiểm tra Bulk Delete](bulk-delete-tests.md) - Delete multiple orders
16. [Kiểm tra Bulk Export](bulk-export-tests.md) - Export selected orders

### 5. **Kiểm tra Status Management**
17. [Kiểm tra Status Workflow](status-workflow-tests.md) - Order status transitions
18. [Kiểm tra Status Validation](status-validation-tests.md) - Status change rules
19. [Kiểm tra Status History](status-history-tests.md) - Track status changes

### 6. **Kiểm tra Integration**
20. [Kiểm tra Customer Integration](customer-integration-tests.md) - Customer data sync
21. [Kiểm tra Product Integration](product-integration-tests.md) - Product data sync
22. [Kiểm tra Inventory Integration](inventory-integration-tests.md) - Stock updates
23. [Kiểm tra Payment Integration](payment-integration-tests.md) - Payment tracking

### 7. **Kiểm tra UI/UX**
24. [Kiểm tra Responsive Design](responsive-tests.md) - Mobile/tablet compatibility
25. [Kiểm tra Column Visibility](column-visibility-tests.md) - Show/hide columns
26. [Kiểm tra Row Expansion](row-expansion-tests.md) - Expand order details
27. [Kiểm tra Loading States](loading-states-tests.md) - Loading indicators

### 8. **Kiểm tra Performance**
28. [Kiểm tra Large Dataset](large-dataset-tests.md) - Performance with many orders
29. [Kiểm tra Concurrent Access](concurrent-access-tests.md) - Multiple users
30. [Kiểm tra Memory Usage](memory-usage-tests.md) - Browser memory

### 9. **Kiểm tra Security**
31. [Kiểm tra Authorization](authorization-tests.md) - User permissions
32. [Kiểm tra Data Validation](data-validation-tests.md) - Input validation
33. [Kiểm tra CSRF Protection](csrf-protection-tests.md) - Security tokens

### 10. **Kiểm tra Error Handling**
34. [Kiểm tra Network Errors](network-error-tests.md) - Connection issues
35. [Kiểm tra Server Errors](server-error-tests.md) - 500 errors handling
36. [Kiểm tra Validation Errors](validation-error-tests.md) - Form validation

## Test Environment

### Setup Requirements
- **URL**: http://yukimart.local/admin/orders
- **Login**: yukimart@gmail.com / 123456
- **Browser**: Chrome (Playwright)
- **Test Data**: Existing orders in database

### Test Data Requirements

#### Orders
- Orders với các status khác nhau (draft, processing, completed, cancelled)
- Orders từ các customers khác nhau
- Orders từ các branch shops khác nhau
- Orders với các payment methods khác nhau
- Orders với date ranges khác nhau

#### Related Data
- Customers với đầy đủ thông tin
- Products với stock levels khác nhau
- Branch shops active
- Users với roles khác nhau

## Test Execution Strategy

### Phase 1: Core Functionality (Days 1-2)
1. Basic listing và pagination
2. Search và filters
3. CRUD operations
4. Status management

### Phase 2: Advanced Features (Days 3-4)
1. Bulk actions
2. Integration testing
3. UI/UX features
4. Export functionality

### Phase 3: Quality Assurance (Days 5-6)
1. Performance testing
2. Security testing
3. Error handling
4. Edge cases

## Success Criteria

### Functional Requirements
- ✅ All CRUD operations work correctly
- ✅ Filters và search return accurate results
- ✅ Bulk actions complete successfully
- ✅ Status transitions follow business rules
- ✅ Data integrity maintained

### Performance Requirements
- ✅ Page load < 3 seconds
- ✅ Search results < 1 second
- ✅ Bulk operations < 5 seconds
- ✅ Export generation < 10 seconds

### UI/UX Requirements
- ✅ Responsive design works on all devices
- ✅ Loading states provide clear feedback
- ✅ Error messages are user-friendly
- ✅ Navigation is intuitive

## Test Reporting

### Daily Reports
- Test cases executed
- Pass/fail status
- Issues found
- Performance metrics

### Module Report
- Overall test coverage
- Critical issues summary
- Performance analysis
- Recommendations

## Test Tools

### Playwright Tests
- UI interaction testing
- End-to-end workflows
- Cross-browser compatibility
- Visual regression testing

### Unit Tests
- Controller logic testing
- Service layer testing
- Model validation testing
- API endpoint testing

### Performance Tests
- Load testing with large datasets
- Memory usage monitoring
- Response time measurement
- Concurrent user simulation

## Getting Started

1. **Review existing test structure** in current directory
2. **Start with basic listing tests** (foundation)
3. **Follow test case templates** for consistency
4. **Use Playwright for UI testing**
5. **Document all findings** with screenshots
6. **Update reports** after each test session

## Notes

- Tests should be executed in order of priority
- Each test case should be independent
- Screenshots should be taken for failed tests
- Performance metrics should be recorded
- All bugs should be documented with reproduction steps
