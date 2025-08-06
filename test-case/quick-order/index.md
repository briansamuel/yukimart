# Danh sách Test Case cho Trang Quick Order

Tài liệu này chứa các test case để kiểm tra chức năng của trang Quick Order System.

## Danh mục Test Case

### 🔧 Core Functionality Tests
1. [Kiểm tra Quản lý Tab](tab-management-tests.md)
2. [Kiểm tra Tìm kiếm Sản phẩm](product-search-tests.md)
3. [Kiểm tra Tạo Đơn hàng](order-creation-tests.md)
4. [Kiểm tra Tạo Hóa đơn](invoice-creation-tests.md)
5. [Kiểm tra Trả hàng](return-order-tests.md)

### 🎨 UI/UX Tests
6. [Kiểm tra Giao diện và Trải nghiệm](ui-ux-tests.md)
7. [Kiểm tra Keyboard Shortcuts](keyboard-shortcuts-tests.md)
8. [Kiểm tra Responsive Design](responsive-tests.md)
9. [Kiểm tra Form Validation](form-validation-tests.md)
10. [Kiểm tra Currency Formatting](currency-formatting-tests.md)

### 🔗 Integration Tests
11. [Kiểm tra Tích hợp Database](database-integration-tests.md)
12. [Kiểm tra Tích hợp Payment](payment-integration-tests.md)
13. [Kiểm tra Cập nhật Inventory](inventory-integration-tests.md)
14. [Kiểm tra Prefix Generation](prefix-generation-tests.md)

### ⚡ Performance & Edge Cases
15. [Kiểm tra Hiệu suất](performance-tests.md)
16. [Kiểm tra Edge Cases](edge-cases-tests.md)
17. [Kiểm tra Error Handling](error-handling-tests.md)
18. [Kiểm tra Concurrent Operations](concurrent-tests.md)

### 🔒 Security Tests
19. [Kiểm tra Bảo mật](security-tests.md)
20. [Kiểm tra Authorization](authorization-tests.md)

## Hướng dẫn sử dụng

1. **Thứ tự test**: Thực hiện theo thứ tự từ Core Functionality → UI/UX → Integration → Performance → Security
2. **Môi trường**: Sử dụng Chrome browser với Playwright
3. **Dữ liệu test**: Sử dụng dữ liệu có sẵn trong database
4. **Ghi lại kết quả**: Cập nhật status sau mỗi test case
5. **Report**: Tổng hợp kết quả trong file `report.md`

## Môi trường Test

- **URL**: http://yukimart.local/admin/quick-order
- **Login**: yukimart@gmail.com / 123456
- **Browser**: Chrome (Playwright)
- **Backend Endpoints**:
  - `/admin/quick-order/search-product` - Tìm kiếm sản phẩm
  - `/admin/quick-order` - Tạo đơn hàng
  - `/admin/quick-invoice` - Tạo hóa đơn
  - `/admin/return-orders` - Tạo trả hàng

## Test Data Requirements

### Products
- Sản phẩm có tồn kho > 0
- Sản phẩm hết hàng (stock = 0)
- Sản phẩm có barcode
- Sản phẩm không có barcode

### Customers
- Khách hàng có sẵn
- Khách lẻ (customer_id = 0)

### Branch Shops
- Chi nhánh mặc định
- Chi nhánh khác

### Bank Accounts
- Tài khoản tiền mặt
- Tài khoản ngân hàng

## Expected Outcomes

### Success Criteria
- ✅ Tất cả core functionality hoạt động đúng
- ✅ UI/UX responsive và user-friendly
- ✅ Integration với database chính xác
- ✅ Performance tốt với large datasets
- ✅ Security và authorization đúng

### Failure Criteria
- ❌ Lỗi JavaScript console
- ❌ Database inconsistency
- ❌ UI broken hoặc không responsive
- ❌ Performance chậm (>3s response time)
- ❌ Security vulnerabilities

## Test Execution Plan

### Phase 1: Core Functionality (Priority: High)
- Tab Management
- Product Search
- Order/Invoice/Return Creation

### Phase 2: UI/UX (Priority: Medium)
- Interface responsiveness
- Keyboard shortcuts
- Form validation

### Phase 3: Integration (Priority: High)
- Database operations
- Payment integration
- Inventory updates

### Phase 4: Performance & Edge Cases (Priority: Medium)
- Large datasets
- Concurrent operations
- Error scenarios

### Phase 5: Security (Priority: High)
- Authorization checks
- Input validation
- CSRF protection
