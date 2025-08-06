# Test Case: Trả hàng - Quick Order System

## Mô tả
Kiểm tra chức năng trả hàng trong Quick Order System bao gồm trả hàng đơn thuần và trả hàng kèm đổi hàng.

## Test Cases

### TC-RETURN-001: Trả hàng đơn thuần
**Mô tả**: Kiểm tra tạo đơn trả hàng không đổi hàng
**Độ ưu tiên**: High
**Điều kiện tiên quyết**: Có tab Return active và có hóa đơn/đơn hàng để trả

**Bước thực hiện**:
1. Tạo tab Return
2. Chọn hóa đơn gốc để trả hàng
3. Chọn sản phẩm và số lượng trả
4. Không thêm sản phẩm đổi
5. Click "TẠO TRẢ HÀNG"

**Kết quả mong đợi**:
- Return order được tạo với prefix "TH"
- Chỉ có return_order_items, không có exchange items
- Return type = "return_only"
- Status = "completed"
- Refund amount = giá trị sản phẩm trả

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-RETURN-002: Trả hàng và đổi hàng
**Mô tả**: Kiểm tra tạo đơn trả hàng kèm đổi hàng
**Độ ưu tiên**: High
**Điều kiện tiên quyết**: Có tab Return active

**Bước thực hiện**:
1. Tạo tab Return
2. Chọn hóa đơn gốc để trả hàng
3. Chọn sản phẩm trả (giá trị 100,000đ)
4. Thêm sản phẩm đổi (giá trị 80,000đ)
5. Tạo trả hàng

**Kết quả mong đợi**:
- Return order được tạo với prefix "TH"
- Exchange invoice được tạo với prefix "HDD_TH"
- Invoice type = "return"
- Net amount = 100,000 - 80,000 = 20,000đ (hoàn lại khách)
- Payment được tạo cho phần chênh lệch

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-RETURN-003: Đổi hàng giá trị cao hơn
**Mô tả**: Kiểm tra đổi hàng khi sản phẩm mới có giá cao hơn
**Độ ưu tiên**: High
**Điều kiện tiên quyết**: Có tab Return active

**Bước thực hiện**:
1. Chọn sản phẩm trả (giá trị 50,000đ)
2. Thêm sản phẩm đổi (giá trị 80,000đ)
3. Tạo trả hàng
4. Kiểm tra payment

**Kết quả mong đợi**:
- Net amount = 50,000 - 80,000 = -30,000đ (khách phải trả thêm)
- Exchange invoice với total = 80,000đ
- Payment với amount = 30,000đ (khách trả thêm)
- Return amount = 50,000đ

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-RETURN-004: Prefix return order đúng format
**Mô tả**: Kiểm tra format prefix của return order
**Độ ưu tiên**: Medium
**Điều kiện tiên quyết**: Có tab Return active

**Bước thực hiện**:
1. Tạo return order
2. Kiểm tra return_order_code
3. Tạo thêm vài return order
4. Verify sequence

**Kết quả mong đợi**:
- Format: TH + YYYYMMDD + 4 số sequence
- Ví dụ: TH202412310001, TH202412310002
- Sequence tăng dần theo ngày
- Không trùng lặp

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-RETURN-005: Prefix exchange invoice đúng format
**Mô tả**: Kiểm tra format prefix của exchange invoice
**Độ ưu tiên**: Medium
**Điều kiện tiên quyết**: Có tab Return active với exchange items

**Bước thực hiện**:
1. Tạo return order có đổi hàng
2. Kiểm tra exchange invoice number
3. Verify format

**Kết quả mong đợi**:
- Format: HDD_TH + YYYYMMDD + 4 số sequence
- Ví dụ: HDD_TH202412310001
- Invoice type = "return"
- Khác biệt với hóa đơn thường

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-RETURN-006: Validation return order trống
**Mô tả**: Kiểm tra validation khi không có sản phẩm trả
**Độ ưu tiên**: High
**Điều kiện tiên quyết**: Tab Return trống

**Bước thực hiện**:
1. Tạo tab Return
2. Không chọn sản phẩm trả nào
3. Click "TẠO TRẢ HÀNG"

**Kết quả mong đợi**:
- Button "TẠO TRẢ HÀNG" bị disable
- Hoặc hiển thị thông báo lỗi validation
- Return order không được tạo

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-RETURN-007: Chọn hóa đơn gốc
**Mô tả**: Kiểm tra chọn hóa đơn/đơn hàng gốc để trả hàng
**Độ ưu tiên**: High
**Điều kiện tiên quyết**: Có hóa đơn/đơn hàng trong hệ thống

**Bước thực hiện**:
1. Click vào dropdown "Chọn hóa đơn gốc"
2. Tìm kiếm theo mã hóa đơn
3. Chọn một hóa đơn
4. Kiểm tra danh sách sản phẩm hiển thị

**Kết quả mong đợi**:
- Dropdown hiển thị danh sách hóa đơn/đơn hàng
- Có thể tìm kiếm theo mã
- Khi chọn, hiển thị danh sách sản phẩm của hóa đơn đó
- Chỉ hiển thị sản phẩm có thể trả (chưa trả hết)

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-RETURN-008: Số lượng trả không vượt quá đã mua
**Mô tả**: Kiểm tra validation số lượng trả
**Độ ưu tiên**: High
**Điều kiện tiên quyết**: Đã chọn hóa đơn gốc

**Bước thực hiện**:
1. Chọn sản phẩm đã mua 2 cái
2. Nhập số lượng trả = 3
3. Kiểm tra validation

**Kết quả mong đợi**:
- Validation không cho phép trả > số lượng đã mua
- Hiển thị thông báo lỗi rõ ràng
- Số lượng tối đa có thể trả được hiển thị

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-RETURN-009: Tính toán refund amount
**Mô tả**: Kiểm tra tính toán số tiền hoàn lại
**Độ ưu tiên**: High
**Điều kiện tiên quyết**: Có sản phẩm trả

**Bước thực hiện**:
1. Chọn trả sản phẩm A (2 cái × 50,000đ = 100,000đ)
2. Chọn trả sản phẩm B (1 cái × 30,000đ = 30,000đ)
3. Kiểm tra tổng refund amount

**Kết quả mong đợi**:
- Refund amount = 100,000 + 30,000 = 130,000đ
- Tính toán real-time khi thay đổi
- Hiển thị đúng định dạng tiền tệ

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-RETURN-010: Lý do trả hàng
**Mô tả**: Kiểm tra nhập lý do trả hàng
**Độ ưu tiên**: Medium
**Điều kiện tiên quyết**: Có sản phẩm trả

**Bước thực hiện**:
1. Chọn sản phẩm trả
2. Nhập lý do trả hàng
3. Tạo return order
4. Kiểm tra lý do được lưu

**Kết quả mong đợi**:
- Lý do trả hàng được lưu trong database
- Hỗ trợ ký tự Unicode
- Có thể để trống (không bắt buộc)

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-RETURN-011: Return order items được lưu đúng
**Mô tả**: Kiểm tra return order items được lưu chính xác
**Độ ưu tiên**: High
**Điều kiện tiên quyết**: Tạo return order thành công

**Bước thực hiện**:
1. Tạo return order với nhiều sản phẩm
2. Kiểm tra bảng return_order_items
3. Verify thông tin

**Kết quả mong đợi**:
- Mỗi sản phẩm trả tạo 1 record trong return_order_items
- Thông tin product_id, quantity, unit_price, line_total đúng
- Original_order_item_id hoặc original_invoice_item_id được set
- Sort_order đúng thứ tự

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-RETURN-012: Inventory được cập nhật khi trả hàng
**Mô tả**: Kiểm tra inventory được cộng lại khi trả hàng
**Độ ưu tiên**: High
**Điều kiện tiên quyết**: Biết số tồn kho hiện tại

**Bước thực hiện**:
1. Ghi nhận số tồn kho ban đầu
2. Tạo return order trả 2 sản phẩm
3. Kiểm tra số tồn kho sau khi trả

**Kết quả mong đợi**:
- Số tồn kho tăng lên 2
- Inventory transaction được tạo với type = "return"
- Quantity > 0 (cộng vào kho)
- Reference đến return order

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
