# User Creation Tests

## Test Case 1: Modal Display
**Mục đích:** Kiểm tra modal hiển thị đúng khi click button "Tạo tài khoản"

**Các bước:**
1. Truy cập trang User Manager
2. Click button "+ Tạo tài khoản"
3. Kiểm tra modal hiển thị

**Kết quả mong đợi:**
- ✅ Modal hiển thị với title "Tạo tài khoản người dùng"
- ✅ Modal có width 800px (class `mw-800px`)
- ✅ Tất cả các trường input hiển thị đúng
- ✅ Button "Hủy" và "Lưu" hiển thị

**Kết quả thực tế:**
- ✅ Modal hiển thị đúng với title "Tạo tài khoản người dùng"
- ✅ Modal có class `mw-800px` (verified bằng JavaScript)
- ✅ Tất cả các trường input hiển thị:
  - Tên hiển thị (required)
  - Điện thoại với country code (required)
  - Email
  - Tên đăng nhập (required)
  - Mật khẩu với show/hide toggle (required)
  - Nhập lại mật khẩu với show/hide toggle (required)
  - Vai trò (Select2 dropdown)
  - 2 checkboxes cho notifications
  - Collapsible sections: Thời gian truy cập, Thông tin khác, Ghi chú
- ✅ Button "Hủy" và "Lưu" hiển thị đúng

**Trạng thái:** ✅ PASSED (Tested on 2025-10-27)

---

## Test Case 2: Required Fields Validation
**Mục đích:** Kiểm tra validation các trường bắt buộc

**Các bước:**
1. Mở modal tạo tài khoản
2. Click button "Lưu" mà không điền gì
3. Kiểm tra error messages

**Kết quả mong đợi:**
- ✅ Hiển thị error "Vui lòng nhập tên hiển thị"
- ✅ Hiển thị error "Vui lòng nhập số điện thoại"
- ✅ Hiển thị error "Vui lòng nhập tên đăng nhập"
- ✅ Hiển thị error "Vui lòng nhập mật khẩu"
- ✅ Form không submit

**Trạng thái:** ⏳ Pending

---

## Test Case 3: Email Format Validation
**Mục đích:** Kiểm tra validation format email

**Các bước:**
1. Mở modal tạo tài khoản
2. Nhập email không hợp lệ (ví dụ: "test@")
3. Click button "Lưu"

**Kết quả mong đợi:**
- ✅ Hiển thị error "Email không hợp lệ"
- ✅ Form không submit

**Trạng thái:** ⏳ Pending

---

## Test Case 4: Password Confirmation Validation
**Mục đích:** Kiểm tra validation mật khẩu xác nhận

**Các bước:**
1. Mở modal tạo tài khoản
2. Nhập mật khẩu: "123456"
3. Nhập lại mật khẩu: "654321"
4. Click button "Lưu"

**Kết quả mong đợi:**
- ✅ Hiển thị error "Mật khẩu xác nhận không khớp"
- ✅ Form không submit

**Trạng thái:** ⏳ Pending

---

## Test Case 5: Successful User Creation
**Mục đích:** Kiểm tra tạo user thành công

**Các bước:**
1. Mở modal tạo tài khoản
2. Điền đầy đủ thông tin hợp lệ:
   - Tên hiển thị: "Test User"
   - Điện thoại: "0901234567"
   - Email: "testuser@example.com"
   - Tên đăng nhập: "testuser"
   - Mật khẩu: "123456"
   - Nhập lại mật khẩu: "123456"
3. Chọn vai trò (optional)
4. Click button "Lưu"

**Kết quả mong đợi:**
- ✅ Hiển thị loading indicator trên button "Lưu"
- ✅ AJAX request gửi đến `/admin/settings/user-manager/store`
- ✅ Response trả về success
- ✅ Hiển thị notification "Tạo tài khoản thành công"
- ✅ Modal đóng
- ✅ Table reload và hiển thị user mới

**Trạng thái:** ⏳ Pending

---

## Test Case 6: Duplicate Username
**Mục đích:** Kiểm tra validation username đã tồn tại

**Các bước:**
1. Mở modal tạo tài khoản
2. Điền username đã tồn tại (ví dụ: "owner")
3. Điền các trường khác hợp lệ
4. Click button "Lưu"

**Kết quả mong đợi:**
- ✅ Response trả về error 422
- ✅ Hiển thị error "Tên đăng nhập đã tồn tại"
- ✅ Modal vẫn mở
- ✅ Form không reset

**Trạng thái:** ⏳ Pending

---

## Test Case 7: Cancel Button
**Mục đích:** Kiểm tra button "Hủy" đóng modal

**Các bước:**
1. Mở modal tạo tài khoản
2. Điền một số thông tin
3. Click button "Hủy"

**Kết quả mong đợi:**
- ✅ Modal đóng
- ✅ Form reset về trạng thái ban đầu
- ✅ Không có AJAX request

**Trạng thái:** ⏳ Pending

---

## Test Case 8: Password Toggle
**Mục đích:** Kiểm tra chức năng show/hide password

**Các bước:**
1. Mở modal tạo tài khoản
2. Nhập mật khẩu
3. Click icon eye để show password
4. Click lại để hide password

**Kết quả mong đợi:**
- ✅ Input type thay đổi từ "password" sang "text"
- ✅ Icon thay đổi từ eye sang eye-slash
- ✅ Password hiển thị rõ khi show
- ✅ Password ẩn khi hide

**Trạng thái:** ⏳ Pending

---

## Test Case 9: Role Selection
**Mục đích:** Kiểm tra Select2 dropdown vai trò

**Các bước:**
1. Mở modal tạo tài khoản
2. Click vào dropdown "Vai trò"
3. Search "Quản lý"
4. Chọn vai trò

**Kết quả mong đợi:**
- ✅ Dropdown hiển thị danh sách vai trò
- ✅ AJAX loading từ `/admin/settings/user-manager/roles`
- ✅ Search hoạt động
- ✅ Chọn vai trò thành công

**Trạng thái:** ⏳ Pending

---

## Test Case 10: Collapsible Sections
**Mục đích:** Kiểm tra các section collapsible

**Các bước:**
1. Mở modal tạo tài khoản
2. Click vào "Thời gian truy cập"
3. Click vào "Thông tin khác"
4. Click vào "Ghi chú"

**Kết quả mong đợi:**
- ✅ Section expand/collapse khi click
- ✅ Icon thay đổi (arrow up/down)
- ✅ Nội dung hiển thị/ẩn

**Trạng thái:** ⏳ Pending

---

## Summary
- **Total Test Cases:** 10
- **Passed:** 1 ✅
- **Failed:** 0
- **Pending:** 9

## Test Execution Log

### 2025-10-27
- **TC1: Modal Display** - ✅ PASSED
  - Tested with Playwright
  - Modal width verified: 800px (class `mw-800px`)
  - All form fields displayed correctly
  - Screenshot saved: `modal-800px-width.png`

