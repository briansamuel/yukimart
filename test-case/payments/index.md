# Danh sách Test Case cho Trang Payments

Tài liệu này chứa các test case để kiểm tra chức năng của trang Payments.

## Danh mục Test Case

1. [Kiểm tra Phân trang](pagination-tests.md)
2. [Kiểm tra Bộ lọc Thời gian](time-filter-tests.md)
3. [Kiểm tra Bộ lọc Loại Thanh toán](payment-type-tests.md)
4. [Kiểm tra Bộ lọc Phương thức Thanh toán](payment-method-tests.md)
5. [Kiểm tra Bộ lọc Trạng thái](status-tests.md)
6. [Kiểm tra Bộ lọc Chi nhánh](branch-shop-tests.md)
7. [Kiểm tra Bộ lọc Người tạo](creator-tests.md)
8. [Kiểm tra Bộ lọc Nhân viên](staff-tests.md)
9. [Kiểm tra Bộ lọc Kết hợp](combined-filter-tests.md)
10. [Kiểm tra Tìm kiếm](search-tests.md)
11. [Kiểm tra Tính toán Tổng kết](summary-tests.md)
12. [Kiểm tra Giao diện và Trải nghiệm người dùng](ui-ux-tests.md)
13. [Kiểm tra Hiệu suất](performance-tests.md)
14. [Kiểm tra Bảo mật](security-tests.md)
15. [Kiểm tra Tính năng Xuất dữ liệu](export-tests.md)

## Hướng dẫn sử dụng

1. Mỗi file chứa các test case cho một nhóm chức năng cụ thể
2. Mỗi test case có ID, mô tả, bước thực hiện và kết quả mong đợi
3. Thực hiện test theo thứ tự từ cơ bản đến nâng cao
4. Ghi lại kết quả sau mỗi test case

## Môi trường Test

- **URL**: http://yukimart.local/admin/payments
- **Backend Endpoint**: `/test-payment-pagination`
- **Summary Endpoint**: `/test-payment-summary-direct`
- **Tổng số bản ghi**: 2,425 payments