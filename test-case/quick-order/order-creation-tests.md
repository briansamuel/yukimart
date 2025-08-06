# Test Case: Tạo Đơn hàng - Quick Order System

## Mô tả
Kiểm tra chức năng tạo đơn hàng trong Quick Order System bao gồm thêm sản phẩm, tính toán, và lưu đơn hàng.

## Test Cases

### TC-ORDER-001: Tạo đơn hàng cơ bản
**Mô tả**: Kiểm tra tạo đơn hàng với 1 sản phẩm
**Độ ưu tiên**: High
**Điều kiện tiên quyết**: Có tab Order active

**Bước thực hiện**:
1. Tìm và thêm 1 sản phẩm vào tab
2. Kiểm tra thông tin hiển thị
3. Click "TẠO ĐƠN HÀNG"
4. Kiểm tra kết quả

**Kết quả mong đợi**:
- Sản phẩm hiển thị trong danh sách với đầy đủ thông tin
- Tổng tiền được tính đúng
- Button "TẠO ĐƠN HÀNG" enabled
- Đơn hàng được tạo thành công với prefix "ORD"
- Tab được clear sau khi tạo thành công

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-ORDER-002: Tạo đơn hàng nhiều sản phẩm
**Mô tả**: Kiểm tra tạo đơn hàng với nhiều sản phẩm khác nhau
**Độ ưu tiên**: High
**Điều kiện tiên quyết**: Có tab Order active

**Bước thực hiện**:
1. Thêm 3 sản phẩm khác nhau vào tab
2. Kiểm tra danh sách sản phẩm
3. Kiểm tra tổng tiền
4. Tạo đơn hàng

**Kết quả mong đợi**:
- Tất cả sản phẩm hiển thị trong danh sách
- Tổng tiền = sum(quantity × price) của tất cả sản phẩm
- Đơn hàng được tạo với tất cả items
- Order items được lưu đúng thông tin

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-ORDER-003: Thay đổi số lượng sản phẩm
**Mô tả**: Kiểm tra thay đổi số lượng sản phẩm trong đơn hàng
**Độ ưu tiên**: High
**Điều kiện tiên quyết**: Có sản phẩm trong tab Order

**Bước thực hiện**:
1. Thêm sản phẩm vào tab
2. Thay đổi số lượng từ 1 thành 3
3. Kiểm tra cập nhật tổng tiền
4. Thay đổi số lượng thành 0
5. Kiểm tra sản phẩm có bị xóa không

**Kết quả mong đợi**:
- Tổng tiền cập nhật real-time khi thay đổi số lượng
- Khi số lượng = 0, sản phẩm bị xóa khỏi danh sách
- Tab count cập nhật đúng
- Validation số lượng > 0

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-ORDER-004: Thay đổi giá sản phẩm
**Mô tả**: Kiểm tra thay đổi giá bán sản phẩm
**Độ ưu tiên**: Medium
**Điều kiện tiên quyết**: Có sản phẩm trong tab Order

**Bước thực hiện**:
1. Thêm sản phẩm vào tab
2. Thay đổi giá từ giá gốc thành giá khác
3. Kiểm tra cập nhật tổng tiền
4. Nhập giá âm hoặc 0
5. Kiểm tra validation

**Kết quả mong đợi**:
- Tổng tiền cập nhật khi thay đổi giá
- Validation giá phải > 0
- Giá được format đúng định dạng tiền tệ
- Giá âm không được chấp nhận

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-ORDER-005: Xóa sản phẩm khỏi đơn hàng
**Mô tả**: Kiểm tra xóa sản phẩm khỏi danh sách
**Độ ưu tiên**: Medium
**Điều kiện tiên quyết**: Có nhiều sản phẩm trong tab Order

**Bước thực hiện**:
1. Thêm 3 sản phẩm vào tab
2. Click nút xóa của sản phẩm thứ 2
3. Kiểm tra danh sách còn lại
4. Kiểm tra tổng tiền

**Kết quả mong đợi**:
- Sản phẩm bị xóa khỏi danh sách
- Tổng tiền được tính lại đúng
- Tab count giảm đi 1
- Các sản phẩm khác không bị ảnh hưởng

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-ORDER-006: Áp dụng giảm giá
**Mô tả**: Kiểm tra áp dụng giảm giá cho đơn hàng
**Độ ưu tiên**: Medium
**Điều kiện tiên quyết**: Có sản phẩm trong tab Order

**Bước thực hiện**:
1. Thêm sản phẩm với tổng tiền 100,000đ
2. Nhập giảm giá 10,000đ
3. Kiểm tra tổng tiền cuối
4. Nhập giảm giá > tổng tiền
5. Kiểm tra validation

**Kết quả mong đợi**:
- Tổng tiền cuối = subtotal - discount
- Validation giảm giá không được > subtotal
- Giảm giá âm không được chấp nhận
- Tổng tiền cuối không được âm

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-ORDER-007: Thêm phí khác
**Mô tả**: Kiểm tra thêm phí khác (phí vận chuyển, phí dịch vụ)
**Độ ưu tiên**: Medium
**Điều kiện tiên quyết**: Có sản phẩm trong tab Order

**Bước thực hiện**:
1. Thêm sản phẩm với tổng tiền 100,000đ
2. Nhập phí khác 5,000đ
3. Kiểm tra tổng tiền cuối
4. Nhập phí âm
5. Kiểm tra validation

**Kết quả mong đợi**:
- Tổng tiền cuối = subtotal - discount + other_amount
- Phí khác có thể = 0 nhưng không được âm
- Tính toán đúng khi có cả giảm giá và phí khác

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-ORDER-008: Chọn khách hàng
**Mô tả**: Kiểm tra chọn khách hàng cho đơn hàng
**Độ ưu tiên**: Medium
**Điều kiện tiên quyết**: Có danh sách khách hàng trong hệ thống

**Bước thực hiện**:
1. Click vào dropdown khách hàng
2. Chọn một khách hàng có sẵn
3. Kiểm tra thông tin hiển thị
4. Tạo đơn hàng và kiểm tra customer_id

**Kết quả mong đợi**:
- Dropdown hiển thị danh sách khách hàng
- Thông tin khách hàng hiển thị đúng (tên + số điện thoại)
- Đơn hàng được tạo với customer_id đúng
- Mặc định là "Khách lẻ" (customer_id = 0)

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-ORDER-009: Chọn chi nhánh
**Mô tả**: Kiểm tra chọn chi nhánh cho đơn hàng
**Độ ưu tiên**: High
**Điều kiện tiên quyết**: Có nhiều chi nhánh trong hệ thống

**Bước thực hiện**:
1. Click vào dropdown chi nhánh
2. Chọn chi nhánh khác với mặc định
3. Tạo đơn hàng và kiểm tra branch_shop_id

**Kết quả mong đợi**:
- Dropdown hiển thị danh sách chi nhánh
- Chi nhánh mặc định được chọn ban đầu
- Đơn hàng được tạo với branch_shop_id đúng
- Validation chi nhánh bắt buộc

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-ORDER-010: Thêm ghi chú
**Mô tả**: Kiểm tra thêm ghi chú cho đơn hàng
**Độ ưu tiên**: Low
**Điều kiện tiên quyết**: Có tab Order active

**Bước thực hiện**:
1. Nhập ghi chú vào ô Notes
2. Tạo đơn hàng
3. Kiểm tra ghi chú được lưu

**Kết quả mong đợi**:
- Ghi chú được lưu trong database
- Hỗ trợ ký tự Unicode
- Không có giới hạn độ dài quá nghiêm ngặt

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-ORDER-011: Validation đơn hàng trống
**Mô tả**: Kiểm tra validation khi tạo đơn hàng không có sản phẩm
**Độ ưu tiên**: High
**Điều kiện tiên quyết**: Tab Order trống

**Bước thực hiện**:
1. Tạo tab Order mới (không thêm sản phẩm)
2. Click "TẠO ĐƠN HÀNG"
3. Kiểm tra thông báo lỗi

**Kết quả mong đợi**:
- Button "TẠO ĐƠN HÀNG" bị disable khi không có sản phẩm
- Hoặc hiển thị thông báo lỗi "Vui lòng thêm ít nhất 1 sản phẩm"
- Đơn hàng không được tạo

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-ORDER-012: Inventory không được cập nhật
**Mô tả**: Kiểm tra inventory không bị trừ khi tạo đơn hàng (status = draft)
**Độ ưu tiên**: High
**Điều kiện tiên quyết**: Biết số tồn kho hiện tại của sản phẩm

**Bước thực hiện**:
1. Ghi nhận số tồn kho ban đầu
2. Tạo đơn hàng với sản phẩm đó
3. Kiểm tra số tồn kho sau khi tạo đơn hàng

**Kết quả mong đợi**:
- Số tồn kho không thay đổi
- Inventory chỉ được cập nhật khi order status = processing/complete
- Order được tạo với status = draft

**Kết quả thực tế**: [ ]
**Ghi chú**: 

## Tổng kết

**Tổng số test case**: 12
**Passed**: [ ]
**Failed**: [ ]
**Skipped**: [ ]

**Ghi chú chung**:
- 
- 
- 

**Khuyến nghị**:
- 
- 
- 
