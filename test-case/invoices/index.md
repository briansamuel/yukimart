# Danh sách Test Case cho Trang Invoices

Tài liệu này chứa các test case để kiểm tra chức năng của trang Invoices (Hóa đơn).

## Danh mục Test Case

### 1. **Kiểm tra Cơ bản**
1. [Kiểm tra Hiển thị Danh sách Cơ bản](basic-listing-tests.md) - Hiển thị, loading, render dữ liệu
2. [Kiểm tra Phân trang](pagination-tests.md) - Navigation, page size, data loading
3. [Kiểm tra Tìm kiếm](search-tests.md) - Tìm kiếm hóa đơn theo nhiều trường
4. [Kiểm tra Xuất Excel](export-tests.md) - Xuất dữ liệu và tạo file

### 2. **Kiểm tra Bulk Actions** ⭐ (Vừa được fix)
5. [Kiểm tra Bulk Selection](bulk-selection-tests.md) - Checkbox cá nhân và select-all
6. [Kiểm tra Bulk Operations](bulk-operations-tests.md) - Thao tác hàng loạt trên hóa đơn đã chọn

### 3. **Kiểm tra Bộ lọc**
7. [Kiểm tra Bộ lọc Thời gian](time-filter-tests.md) - Lọc theo khoảng thời gian và tùy chỉnh
8. [Kiểm tra Bộ lọc Trạng thái](status-filter-tests.md) - Lọc theo trạng thái hóa đơn
9. [Kiểm tra Bộ lọc Người tạo](creator-filter-tests.md) - Lọc theo người tạo hóa đơn
10. [Kiểm tra Bộ lọc Người bán](seller-filter-tests.md) - Lọc theo nhân viên bán hàng
11. [Kiểm tra Bộ lọc Trạng thái Giao hàng](delivery-status-filter-tests.md) - Lọc theo trạng thái giao hàng
12. [Kiểm tra Bộ lọc Kênh bán](channel-filter-tests.md) - Lọc theo kênh bán hàng
13. [Kiểm tra Bộ lọc Đối tác Giao hàng](delivery-partner-filter-tests.md) - Lọc theo đối tác giao hàng
14. [Kiểm tra Bộ lọc Thời gian Giao hàng](delivery-time-filter-tests.md) - Lọc theo thời gian giao hàng
15. [Kiểm tra Bộ lọc Khu vực Giao hàng](delivery-area-filter-tests.md) - Lọc theo khu vực giao hàng
16. [Kiểm tra Bộ lọc Phương thức Thanh toán](payment-method-filter-tests.md) - Lọc theo phương thức thanh toán
17. [Kiểm tra Bộ lọc Bảng giá](price-list-filter-tests.md) - Lọc theo bảng giá
18. [Kiểm tra Bộ lọc Kết hợp](combined-filter-tests.md) - Kết hợp nhiều bộ lọc

### 4. **Kiểm tra Giao diện và UX**
19. [Kiểm tra Column Visibility](column-visibility-tests.md) - Ẩn/hiện cột
20. [Kiểm tra Responsive Design](responsive-tests.md) - Tương thích mobile và tablet
21. [Kiểm tra Loading States](loading-states-tests.md) - Trạng thái loading và indicators
22. [Kiểm tra Error Handling](error-handling-tests.md) - Xử lý lỗi và recovery

### 5. **Kiểm tra Hiệu suất**
23. [Kiểm tra Hiệu suất với Dữ liệu lớn](performance-tests.md) - Performance với nhiều dữ liệu
24. [Kiểm tra Hiệu suất Bộ lọc](filter-performance-tests.md) - Thời gian phản hồi bộ lọc
25. [Kiểm tra Hiệu suất Tìm kiếm](search-performance-tests.md) - Thời gian phản hồi tìm kiếm

### 6. **Kiểm tra Tích hợp**
26. [Kiểm tra Tích hợp Quick Order](quick-order-integration-tests.md) - Tích hợp với hệ thống POS
27. [Kiểm tra Tích hợp Customer](customer-integration-tests.md) - Tích hợp dữ liệu khách hàng
28. [Kiểm tra Tích hợp Payment](payment-integration-tests.md) - Tích hợp hệ thống thanh toán

### 7. **Kiểm tra Bảo mật**
29. [Kiểm tra Phân quyền](security-tests.md) - Quyền truy cập và phân quyền người dùng
30. [Kiểm tra Validation](validation-tests.md) - Validation input và sanitization

### 8. **Kiểm tra Tương thích Trình duyệt**
31. [Kiểm tra Cross-Browser](browser-compatibility-tests.md) - Chrome, Firefox, Safari, Edge
32. [Kiểm tra JavaScript Compatibility](js-compatibility-tests.md) - Chức năng JavaScript trên các trình duyệt

### 9. **Kiểm tra Regression**
33. [Kiểm tra Bug Fixes](regression-tests.md) - Xác minh các lỗi đã được sửa
34. [Kiểm tra Stability](stability-tests.md) - Đảm bảo các tính năng hiện tại ổn định

## 🎯 Thứ tự Ưu tiên Test

### **Ưu tiên Cao (P1)** - Cần test ngay
1. **Bulk Selection** ⭐ (Vừa được fix - cần verify)
2. Basic Invoice Listing
3. Search Functionality
4. Time Filter
5. Status Filter

### **Ưu tiên Trung bình (P2)**
1. Pagination
2. Export Functionality
3. Column Visibility
4. Creator/Seller Filter
5. Delivery Status Filter

### **Ưu tiên Thấp (P3)**
1. Performance Tests
2. Browser Compatibility
3. Integration Tests
4. Security Tests

## 📊 Mục tiêu Test Coverage

- **Functional Coverage**: 95%
- **UI/UX Coverage**: 90%
- **Browser Coverage**: Chrome, Firefox, Safari, Edge
- **Device Coverage**: Desktop, Tablet, Mobile
- **Performance Benchmarks**: <2s load time, <1s filter response

## 🔧 Môi trường Test

- **URL**: http://yukimart.local/admin/invoices
- **Test User**: yukimart@gmail.com / 123456
- **Browser**: Playwright automation + manual testing
- **Data**: Production-like test data với 1853+ invoices
- **Backend Endpoint**: `/admin/invoices/ajax`
- **Export Endpoint**: `/admin/invoices/export`

## 📝 Báo cáo Test

Mỗi category test sẽ tạo ra:
- ✅ Trạng thái Pass/Fail cho từng test case
- 📊 Metrics hiệu suất khi có thể
- 🐛 Bug reports cho các test failed
- 📸 Screenshots cho các vấn đề UI
- 📋 Summary reports với khuyến nghị

## 🚀 Hướng dẫn Bắt đầu

1. Bắt đầu với [Bulk Selection Tests](bulk-selection-tests.md) ⭐ (Vừa được fix)
2. Tiếp tục với [Basic Invoice Listing](basic-listing-tests.md)
3. Theo thứ tự ưu tiên để test toàn diện
4. Ghi lại tất cả findings trong các file test tương ứng
5. Cập nhật [report.md](report.md) sau mỗi test session

## 📋 Ghi chú

- Tests được thực hiện tuần tự
- Mỗi kết quả test được ghi lại với screenshots và các bước chi tiết
- Các test failed sẽ được retry và ghi lại
- Tham khảo cấu trúc từ [test-case/payments](../payments/) để hiểu format
