# Test Case: Quản lý Tab - Quick Order System

## Mô tả
Kiểm tra chức năng quản lý tab trong Quick Order System bao gồm tạo, chuyển đổi, và đóng tab.

## Test Cases

### TC-TAB-001: Tạo Tab Đơn hàng mới
**Mô tả**: Kiểm tra tạo tab đơn hàng mới
**Độ ưu tiên**: High
**Điều kiện tiên quyết**: Đã đăng nhập và truy cập trang Quick Order

**Bước thực hiện**:
1. Click vào dropdown "Tạo mới"
2. Chọn "Đơn hàng"

**Kết quả mong đợi**:
- Tab mới được tạo với tên "Đơn hàng X" (X là số thứ tự)
- Tab được active tự động
- Hiển thị form tạo đơn hàng
- Tab count hiển thị (0)

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-TAB-002: Tạo Tab Hóa đơn mới
**Mô tả**: Kiểm tra tạo tab hóa đơn mới
**Độ ưu tiên**: High
**Điều kiện tiên quyết**: Đã đăng nhập và truy cập trang Quick Order

**Bước thực hiện**:
1. Click vào dropdown "Tạo mới"
2. Chọn "Hóa đơn"

**Kết quả mong đợi**:
- Tab mới được tạo với tên "Hóa đơn X"
- Tab được active tự động
- Hiển thị form tạo hóa đơn
- Button "TẠO HÓA ĐƠN" hiển thị

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-TAB-003: Tạo Tab Trả hàng mới
**Mô tả**: Kiểm tra tạo tab trả hàng mới
**Độ ưu tiên**: High
**Điều kiện tiên quyết**: Đã đăng nhập và truy cập trang Quick Order

**Bước thực hiện**:
1. Click vào dropdown "Tạo mới"
2. Chọn "Trả hàng"

**Kết quả mong đợi**:
- Tab mới được tạo với tên "Trả hàng X"
- Tab được active tự động
- Hiển thị form trả hàng
- Button "TẠO TRẢ HÀNG" hiển thị

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-TAB-004: Chuyển đổi giữa các Tab
**Mô tả**: Kiểm tra chuyển đổi giữa các tab
**Độ ưu tiên**: High
**Điều kiện tiên quyết**: Có ít nhất 2 tab được tạo

**Bước thực hiện**:
1. Tạo 2 tab khác nhau (Đơn hàng và Hóa đơn)
2. Click vào tab đầu tiên
3. Click vào tab thứ hai
4. Kiểm tra nội dung hiển thị

**Kết quả mong đợi**:
- Tab được chuyển đổi thành công
- Nội dung form thay đổi theo loại tab
- Tab active được highlight
- Dữ liệu của mỗi tab được giữ nguyên

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-TAB-005: Đóng Tab trống
**Mô tả**: Kiểm tra đóng tab không có sản phẩm
**Độ ưu tiên**: Medium
**Điều kiện tiên quyết**: Có tab trống (không có sản phẩm)

**Bước thực hiện**:
1. Tạo tab mới
2. Click vào nút "X" trên tab
3. Xác nhận đóng tab

**Kết quả mong đợi**:
- Tab được đóng ngay lập tức
- Không có dialog xác nhận
- Chuyển sang tab khác nếu có
- Nếu không có tab nào, hiển thị trạng thái trống

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-TAB-006: Đóng Tab có sản phẩm
**Mô tả**: Kiểm tra đóng tab đã có sản phẩm
**Độ ưu tiên**: High
**Điều kiện tiên quyết**: Có tab với ít nhất 1 sản phẩm

**Bước thực hiện**:
1. Tạo tab và thêm sản phẩm
2. Click vào nút "X" trên tab
3. Kiểm tra dialog xác nhận
4. Click "OK" để xác nhận đóng

**Kết quả mong đợi**:
- Hiển thị dialog xác nhận với thông tin số sản phẩm
- Khi xác nhận, tab được đóng
- Dữ liệu tab bị xóa
- Chuyển sang tab khác nếu có

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-TAB-007: Hủy đóng Tab có sản phẩm
**Mô tả**: Kiểm tra hủy việc đóng tab có sản phẩm
**Độ ưu tiên**: Medium
**Điều kiện tiên quyết**: Có tab với ít nhất 1 sản phẩm

**Bước thực hiện**:
1. Tạo tab và thêm sản phẩm
2. Click vào nút "X" trên tab
3. Click "Cancel" trong dialog xác nhận

**Kết quả mong đợi**:
- Dialog đóng lại
- Tab vẫn còn và giữ nguyên dữ liệu
- Tab vẫn ở trạng thái active

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-TAB-008: Giới hạn số lượng Tab
**Mô tả**: Kiểm tra giới hạn tối đa số tab có thể tạo
**Độ ưu tiên**: Medium
**Điều kiện tiên quyết**: Đã đăng nhập và truy cập trang Quick Order

**Bước thực hiện**:
1. Tạo liên tiếp nhiều tab (>10 tab)
2. Kiểm tra thông báo khi đạt giới hạn

**Kết quả mong đợi**:
- Khi đạt giới hạn (10 tabs), hiển thị thông báo warning
- Không thể tạo thêm tab mới
- Các tab hiện tại vẫn hoạt động bình thường

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-TAB-009: Cập nhật Tab Count
**Mô tả**: Kiểm tra cập nhật số lượng sản phẩm trong tab
**Độ ưu tiên**: High
**Điều kiện tiên quyết**: Có tab đang active

**Bước thực hiện**:
1. Tạo tab mới
2. Thêm 1 sản phẩm
3. Thêm thêm 2 sản phẩm nữa
4. Xóa 1 sản phẩm

**Kết quả mong đợi**:
- Tab count cập nhật từ (0) → (1) → (3) → (2)
- Số lượng hiển thị chính xác trong tên tab
- Cập nhật real-time khi thay đổi

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-TAB-010: Lưu và Khôi phục Tab State
**Mô tả**: Kiểm tra lưu trạng thái tab khi refresh page
**Độ ưu tiên**: Medium
**Điều kiện tiên quyết**: Có tab với dữ liệu

**Bước thực hiện**:
1. Tạo tab và thêm sản phẩm
2. Refresh trang (F5)
3. Kiểm tra trạng thái tab

**Kết quả mong đợi**:
- Tab được khôi phục sau refresh
- Dữ liệu sản phẩm được giữ nguyên
- Tab active được duy trì

**Kết quả thực tế**: [ ]
**Ghi chú**: 

## Tổng kết

**Tổng số test case**: 10
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
