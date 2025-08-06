# Test Case: Tạo Hóa đơn - Quick Order System

## Mô tả
Kiểm tra chức năng tạo hóa đơn trong Quick Order System bao gồm tạo hóa đơn, thanh toán, và tích hợp payment.

## Test Cases

### TC-INVOICE-001: Tạo hóa đơn cơ bản
**Mô tả**: Kiểm tra tạo hóa đơn với 1 sản phẩm
**Độ ưu tiên**: High
**Điều kiện tiên quyết**: Có tab Invoice active

**Bước thực hiện**:
1. Tạo tab Invoice
2. Thêm 1 sản phẩm vào tab
3. Click "TẠO HÓA ĐƠN"
4. Kiểm tra kết quả

**Kết quả mong đợi**:
- Hóa đơn được tạo thành công với prefix "HD"
- Invoice status = "paid"
- Payment status = "paid"
- Payment được tạo tự động với prefix "TT{invoice_id}"
- Tab được clear sau khi tạo thành công

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-INVOICE-002: Tạo hóa đơn với thanh toán tiền mặt
**Mô tả**: Kiểm tra tạo hóa đơn với phương thức thanh toán tiền mặt
**Độ ưu tiên**: High
**Điều kiện tiên quyết**: Có tab Invoice active

**Bước thực hiện**:
1. Thêm sản phẩm vào tab Invoice
2. Chọn payment method = "cash"
3. Nhập số tiền thanh toán = tổng tiền
4. Tạo hóa đơn

**Kết quả mong đợi**:
- Hóa đơn được tạo với payment_method = "cash"
- Payment được tạo với method = "cash"
- Bank account = tài khoản tiền mặt mặc định
- Amount = final amount của hóa đơn

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-INVOICE-003: Tạo hóa đơn với thanh toán chuyển khoản
**Mô tả**: Kiểm tra tạo hóa đơn với phương thức chuyển khoản
**Độ ưu tiên**: High
**Điều kiện tiên quyết**: Có tài khoản ngân hàng trong hệ thống

**Bước thực hiện**:
1. Thêm sản phẩm vào tab Invoice
2. Chọn payment method = "bank_transfer"
3. Chọn bank account
4. Tạo hóa đơn

**Kết quả mong đợi**:
- Hóa đơn được tạo với payment_method = "bank_transfer"
- Payment được tạo với bank_account_id đúng
- Reference type = "invoice"
- Reference ID = invoice ID

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-INVOICE-004: Tính toán tổng tiền hóa đơn
**Mô tả**: Kiểm tra tính toán tổng tiền với giảm giá và phí khác
**Độ ưu tiên**: High
**Điều kiện tiên quyết**: Có tab Invoice active

**Bước thực hiện**:
1. Thêm sản phẩm với subtotal = 100,000đ
2. Áp dụng giảm giá 10,000đ
3. Thêm phí khác 5,000đ
4. Kiểm tra final amount
5. Tạo hóa đơn

**Kết quả mong đợi**:
- Final amount = 100,000 - 10,000 + 5,000 = 95,000đ
- Payment amount = 95,000đ
- Tính toán chính xác trong database
- Hiển thị đúng trên UI

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-INVOICE-005: Thanh toán một phần
**Mô tả**: Kiểm tra tạo hóa đơn với thanh toán một phần
**Độ ưu tiên**: Medium
**Điều kiện tiên quyết**: Có tab Invoice active

**Bước thực hiện**:
1. Thêm sản phẩm với tổng tiền 100,000đ
2. Nhập số tiền thanh toán 50,000đ
3. Tạo hóa đơn
4. Kiểm tra trạng thái

**Kết quả mong đợi**:
- Hóa đơn được tạo với status = "processing" hoặc "partial_paid"
- Payment được tạo với amount = 50,000đ
- Remaining amount = 50,000đ
- Payment status phản ánh đúng trạng thái

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-INVOICE-006: Thanh toán thừa
**Mô tả**: Kiểm tra tạo hóa đơn với số tiền thanh toán > tổng tiền
**Độ ưu tiên**: Medium
**Điều kiện tiên quyết**: Có tab Invoice active

**Bước thực hiện**:
1. Thêm sản phẩm với tổng tiền 100,000đ
2. Nhập số tiền thanh toán 120,000đ
3. Tạo hóa đơn
4. Kiểm tra tiền thừa

**Kết quả mong đợi**:
- Hóa đơn được tạo với status = "paid"
- Payment amount = 120,000đ
- Change amount = 20,000đ được hiển thị
- Hoặc validation không cho phép thanh toán thừa

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-INVOICE-007: Validation hóa đơn trống
**Mô tả**: Kiểm tra validation khi tạo hóa đơn không có sản phẩm
**Độ ưu tiên**: High
**Điều kiện tiên quyết**: Tab Invoice trống

**Bước thực hiện**:
1. Tạo tab Invoice mới
2. Click "TẠO HÓA ĐƠN" mà không thêm sản phẩm
3. Kiểm tra validation

**Kết quả mong đợi**:
- Button "TẠO HÓA ĐƠN" bị disable
- Hoặc hiển thị thông báo lỗi validation
- Hóa đơn không được tạo

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-INVOICE-008: Prefix hóa đơn đúng format
**Mô tả**: Kiểm tra format prefix hóa đơn
**Độ ưu tiên**: Medium
**Điều kiện tiên quyết**: Có tab Invoice active

**Bước thực hiện**:
1. Tạo hóa đơn
2. Kiểm tra invoice_number được tạo
3. Tạo thêm vài hóa đơn nữa
4. Kiểm tra sequence tăng dần

**Kết quả mong đợi**:
- Format: HD + YYYYMMDD + 4 số sequence
- Ví dụ: HD202412310001, HD202412310002
- Sequence tăng dần theo ngày
- Không trùng lặp

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-INVOICE-009: Prefix payment đúng format
**Mô tả**: Kiểm tra format prefix payment từ hóa đơn
**Độ ưu tiên**: Medium
**Điều kiện tiên quyết**: Có tab Invoice active

**Bước thực hiện**:
1. Tạo hóa đơn và ghi nhận invoice ID
2. Kiểm tra payment code được tạo
3. Verify format

**Kết quả mong đợi**:
- Format: TT + {invoice_id}
- Ví dụ: TT123, TT124
- Reference type = "invoice"
- Reference ID = invoice ID

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-INVOICE-010: Tích hợp với Payment Service
**Mô tả**: Kiểm tra tích hợp với PaymentService
**Độ ưu tiên**: High
**Điều kiện tiên quyết**: Có tab Invoice active

**Bước thực hiện**:
1. Tạo hóa đơn
2. Kiểm tra record trong bảng payments
3. Verify các field được lưu đúng

**Kết quả mong đợi**:
- Payment record được tạo trong bảng payments
- Các field: amount, method, bank_account_id, reference_type, reference_id đúng
- Created_by = current user
- Status = "completed"

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-INVOICE-011: Multiple items trong hóa đơn
**Mô tả**: Kiểm tra tạo hóa đơn với nhiều sản phẩm
**Độ ưu tiên**: High
**Điều kiện tiên quyết**: Có tab Invoice active

**Bước thực hiện**:
1. Thêm 5 sản phẩm khác nhau vào tab
2. Thay đổi số lượng và giá của một số sản phẩm
3. Tạo hóa đơn
4. Kiểm tra invoice_items

**Kết quả mong đợi**:
- Tất cả sản phẩm được lưu trong invoice_items
- Thông tin product_id, quantity, unit_price, line_total đúng
- Sort_order được set đúng thứ tự
- Tổng tiền tính đúng

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-INVOICE-012: Invoice type = normal
**Mô tả**: Kiểm tra invoice_type được set đúng cho hóa đơn thường
**Độ ưu tiên**: Medium
**Điều kiện tiên quyết**: Có tab Invoice active

**Bước thực hiện**:
1. Tạo hóa đơn từ tab Invoice
2. Kiểm tra field invoice_type trong database

**Kết quả mong đợi**:
- invoice_type = "normal" hoặc null (default)
- Khác với invoice_type = "return" của hóa đơn đổi trả
- Phân biệt được với các loại hóa đơn khác

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
