# Test Case: Tìm kiếm Sản phẩm - Quick Order System

## Mô tả
Kiểm tra chức năng tìm kiếm sản phẩm trong Quick Order System bao gồm tìm kiếm theo tên, SKU, barcode và các tính năng nâng cao.

## Test Cases

### TC-SEARCH-001: Tìm kiếm theo tên sản phẩm
**Mô tả**: Kiểm tra tìm kiếm sản phẩm bằng tên
**Độ ưu tiên**: High
**Điều kiện tiên quyết**: Có tab active và focus vào ô tìm kiếm

**Bước thực hiện**:
1. Nhập "dao" vào ô tìm kiếm
2. Đợi suggestions hiển thị
3. Kiểm tra kết quả

**Kết quả mong đợi**:
- Hiển thị danh sách sản phẩm có tên chứa "dao"
- Suggestions hiển thị trong 300ms (debounce)
- Hiển thị tên, SKU, barcode, giá, tồn kho
- Tối đa 10 suggestions

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-SEARCH-002: Tìm kiếm theo SKU
**Mô tả**: Kiểm tra tìm kiếm sản phẩm bằng SKU
**Độ ưu tiên**: High
**Điều kiện tiên quyết**: Có tab active và biết SKU sản phẩm

**Bước thực hiện**:
1. Nhập SKU sản phẩm vào ô tìm kiếm
2. Đợi suggestions hiển thị
3. Kiểm tra kết quả

**Kết quả mong đợi**:
- Hiển thị sản phẩm có SKU tương ứng
- Kết quả chính xác và ưu tiên exact match
- Hiển thị đầy đủ thông tin sản phẩm

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-SEARCH-003: Tìm kiếm theo Barcode
**Mô tả**: Kiểm tra tìm kiếm sản phẩm bằng barcode
**Độ ưu tiên**: High
**Điều kiện tiên quyết**: Có tab active và biết barcode sản phẩm

**Bước thực hiện**:
1. Nhập barcode sản phẩm vào ô tìm kiếm
2. Đợi suggestions hiển thị
3. Kiểm tra kết quả

**Kết quả mong đợi**:
- Hiển thị sản phẩm có barcode tương ứng
- Exact match được ưu tiên
- Hiển thị thông tin barcode trong suggestions

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-SEARCH-004: Keyboard Navigation trong Suggestions
**Mô tả**: Kiểm tra điều hướng bằng phím trong danh sách suggestions
**Độ ưu tiên**: Medium
**Điều kiện tiên quyết**: Có suggestions hiển thị

**Bước thực hiện**:
1. Tìm kiếm để hiển thị suggestions
2. Nhấn phím Arrow Down để di chuyển xuống
3. Nhấn phím Arrow Up để di chuyển lên
4. Nhấn Enter để chọn sản phẩm
5. Nhấn Escape để đóng suggestions

**Kết quả mong đợi**:
- Arrow Down/Up di chuyển highlight giữa các items
- Enter chọn item được highlight
- Escape đóng suggestions và blur input
- Navigation loop (cuối → đầu, đầu → cuối)

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-SEARCH-005: F3 Shortcut Focus
**Mô tả**: Kiểm tra phím tắt F3 để focus vào ô tìm kiếm
**Độ ưu tiên**: Medium
**Điều kiện tiên quyết**: Đang ở trang Quick Order

**Bước thực hiện**:
1. Click vào vùng khác (không focus ô tìm kiếm)
2. Nhấn phím F3
3. Kiểm tra focus

**Kết quả mong đợi**:
- Ô tìm kiếm được focus tự động
- Cursor hiển thị trong ô input
- Có thể nhập text ngay lập tức

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-SEARCH-006: Debounce Search Requests
**Mô tả**: Kiểm tra debounce để tránh spam requests
**Độ ưu tiên**: Medium
**Điều kiện tiên quyết**: Có tab active

**Bước thực hiện**:
1. Nhập nhanh liên tiếp "d-a-o" (mỗi ký tự cách nhau <100ms)
2. Mở Network tab trong DevTools
3. Kiểm tra số lượng requests

**Kết quả mong đợi**:
- Chỉ có 1 request cuối cùng được gửi (cho "dao")
- Các request trước đó bị cancel
- Delay 300ms trước khi gửi request

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-SEARCH-007: Click để chọn sản phẩm
**Mô tả**: Kiểm tra chọn sản phẩm bằng click
**Độ ưu tiên**: High
**Điều kiện tiên quyết**: Có suggestions hiển thị

**Bước thực hiện**:
1. Tìm kiếm sản phẩm
2. Click vào một suggestion
3. Kiểm tra kết quả

**Kết quả mong đợi**:
- Sản phẩm được thêm vào tab hiện tại
- Ô tìm kiếm được clear
- Suggestions ẩn đi
- Focus trở lại ô tìm kiếm
- Tab count tăng lên

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-SEARCH-008: Enter để chọn sản phẩm exact
**Mô tả**: Kiểm tra nhấn Enter để tìm exact match
**Độ ưu tiên**: High
**Điều kiện tiên quyết**: Có tab active

**Bước thực hiện**:
1. Nhập exact SKU hoặc barcode
2. Nhấn Enter (không có suggestion nào được highlight)
3. Kiểm tra kết quả

**Kết quả mong đợi**:
- Tìm kiếm exact match trong database
- Nếu tìm thấy: thêm sản phẩm vào tab
- Nếu không tìm thấy: hiển thị thông báo "Không tìm thấy sản phẩm với mã: X"

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-SEARCH-009: Hiển thị trạng thái tồn kho
**Mô tả**: Kiểm tra hiển thị trạng thái tồn kho trong suggestions
**Độ ưu tiên**: Medium
**Điều kiện tiên quyết**: Có sản phẩm với các mức tồn kho khác nhau

**Bước thực hiện**:
1. Tìm kiếm sản phẩm có tồn kho > 5
2. Tìm kiếm sản phẩm có tồn kho 1-5
3. Tìm kiếm sản phẩm hết hàng (tồn kho = 0)

**Kết quả mong đợi**:
- Tồn kho > 5: hiển thị "Tồn: X" với class "in-stock"
- Tồn kho 1-5: hiển thị "Tồn: X" với class "low-stock"
- Tồn kho = 0: hiển thị "Hết hàng" với class "out-of-stock"
- Màu sắc khác nhau cho từng trạng thái

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-SEARCH-010: Click outside để ẩn suggestions
**Mô tả**: Kiểm tra ẩn suggestions khi click bên ngoài
**Độ ưu tiên**: Low
**Điều kiện tiên quyết**: Có suggestions hiển thị

**Bước thực hiện**:
1. Tìm kiếm để hiển thị suggestions
2. Click vào vùng khác ngoài ô tìm kiếm và suggestions
3. Kiểm tra kết quả

**Kết quả mong đợi**:
- Suggestions ẩn đi
- Ô tìm kiếm mất focus
- Không có sản phẩm nào được chọn

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-SEARCH-011: Tìm kiếm với ký tự đặc biệt
**Mô tả**: Kiểm tra tìm kiếm với ký tự đặc biệt và Unicode
**Độ ưu tiên**: Low
**Điều kiện tiên quyết**: Có tab active

**Bước thực hiện**:
1. Nhập ký tự có dấu: "bánh mì"
2. Nhập ký tự đặc biệt: "& % @"
3. Kiểm tra kết quả

**Kết quả mong đợi**:
- Tìm kiếm hoạt động với ký tự Unicode
- Ký tự đặc biệt được escape đúng cách
- Không có lỗi JavaScript
- Kết quả chính xác

**Kết quả thực tế**: [ ]
**Ghi chú**: 

---

### TC-SEARCH-012: Performance với large dataset
**Mô tả**: Kiểm tra hiệu suất tìm kiếm với dataset lớn
**Độ ưu tiên**: Medium
**Điều kiện tiên quyết**: Database có >1000 sản phẩm

**Bước thực hiện**:
1. Nhập từ khóa phổ biến (ví dụ: "a")
2. Đo thời gian response
3. Kiểm tra số lượng kết quả

**Kết quả mong đợi**:
- Response time < 500ms
- Giới hạn 10 kết quả
- Không lag UI
- Kết quả được sắp xếp theo relevance

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
