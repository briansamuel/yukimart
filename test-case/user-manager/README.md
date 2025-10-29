# User Manager Test Cases

## Mục đích
Test các chức năng của module Quản lý người dùng (User Manager)

## Cấu trúc Test Cases

### 1. UI/UX Tests
- [x] Toolbar styling với borders
- [x] Tab navigation (Tài khoản người dùng / Quản lý vai trò)
- [x] Modal tạo tài khoản với width 800px
- [ ] Filter panel UI
- [ ] Table responsive

### 2. Filter Tests
- [ ] Filter theo trạng thái (Đang hoạt động / Ngừng hoạt động)
- [ ] Filter theo vai trò (AJAX loading)
- [ ] Filter theo chi nhánh
- [ ] Combined filters
- [ ] Reset filters

### 3. User Creation Tests
- [ ] Validation các trường required
- [ ] Validation email format
- [ ] Validation username unique
- [ ] Validation password confirmation
- [ ] AJAX submit form
- [ ] Success notification
- [ ] Table reload sau khi tạo

### 4. Role Management Tests
- [ ] Load danh sách vai trò
- [ ] Hiển thị số lượng tài khoản
- [ ] Tạo vai trò mới
- [ ] Chỉnh sửa vai trò
- [ ] Sao chép vai trò

## Test Environment
- URL: http://tenant1.yukimart.local/admin/settings/user-manager
- Login: yukimart@gmail.com / 123456
- Browser: Chrome (Playwright)

## Test Reports
- [User Creation Tests](./user-creation-tests.md)
- [Filter Tests](./filter-tests.md)
- [Role Management Tests](./role-management-tests.md)

