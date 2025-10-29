# Manual Testing Guide - Branch Shops Tenant Filter

## 📋 Mục đích
Hướng dẫn test thủ công chức năng filter branch shops theo tenant_id

## ✅ Chuẩn bị

### 1. Đảm bảo có dữ liệu test:
- Ít nhất 2 tenants trong database
- Mỗi tenant có ít nhất 2-3 branch shops
- User có quyền truy cập nhiều tenants

### 2. Đăng nhập:
- URL: http://yukimart.local/admin/login
- Email: yukimart@gmail.com
- Password: 123456

---

## 🧪 Test Cases

### TC-BS-001: Hiển thị chỉ branch shops của tenant hiện tại

**Bước thực hiện:**
1. Đăng nhập vào hệ thống
2. Truy cập `/admin/branch-shops`
3. Kiểm tra danh sách branch shops hiển thị

**Kết quả mong đợi:**
- ✅ Chỉ hiển thị branch shops thuộc tenant hiện tại
- ✅ Không có branch shops từ tenant khác
- ✅ Số lượng branch shops khớp với database

**Cách verify:**
```sql
-- Lấy tenant_id hiện tại từ session
SELECT * FROM branch_shops WHERE tenant_id = [current_tenant_id];
```

---

### TC-BS-002: Không thể truy cập branch shop của tenant khác

**Bước thực hiện:**
1. Đăng nhập vào hệ thống
2. Lấy ID của branch shop thuộc tenant khác (từ database)
3. Truy cập URL: `/admin/branch-shops/{id}/edit` với ID đó
4. Quan sát kết quả

**Kết quả mong đợi:**
- ✅ Redirect về `/admin/branch-shops`
- ✅ Hiển thị thông báo lỗi: "Không tìm thấy chi nhánh hoặc bạn không có quyền truy cập"
- ✅ Không thể xem/edit branch shop của tenant khác

**Cách verify:**
```sql
-- Tìm branch shop của tenant khác
SELECT id, name, tenant_id 
FROM branch_shops 
WHERE tenant_id != [current_tenant_id] 
LIMIT 1;
```

---

### TC-BS-003: Tự động gán tenant_id khi tạo mới

**Bước thực hiện:**
1. Đăng nhập vào hệ thống
2. Lưu lại current tenant_id (có thể xem trong session hoặc UI)
3. Truy cập `/admin/branch-shops/create`
4. Điền form với dữ liệu test:
   - Code: `TEST-001`
   - Name: `Test Branch Shop`
   - Address: `123 Test Street`
   - Province: `TP.HCM`
   - District: `Quận 1`
   - Ward: `Phường Bến Nghé`
   - Phone: `0123456789`
   - Email: `test@yukimart.vn`
   - Status: `Active`
   - Shop Type: `Standard`
5. Submit form
6. Kiểm tra branch shop vừa tạo trong database

**Kết quả mong đợi:**
- ✅ Branch shop được tạo thành công
- ✅ `tenant_id` = current tenant_id (tự động gán)
- ✅ Không cần điền tenant_id trong form

**Cách verify:**
```sql
-- Kiểm tra branch shop vừa tạo
SELECT id, code, name, tenant_id 
FROM branch_shops 
WHERE code = 'TEST-001';
```

---

### TC-BS-004: Danh sách cập nhật khi switch tenant

**Bước thực hiện:**
1. Đăng nhập vào hệ thống
2. Truy cập `/admin/branch-shops`
3. Đếm số lượng branch shops hiện tại (ghi lại số lượng)
4. Switch sang tenant khác (nếu có chức năng tenant switcher)
5. Reload trang hoặc truy cập lại `/admin/branch-shops`
6. Đếm số lượng branch shops mới

**Kết quả mong đợi:**
- ✅ Số lượng branch shops thay đổi sau khi switch tenant
- ✅ Danh sách branch shops khác nhau giữa các tenant
- ✅ Mỗi tenant chỉ thấy branch shops của mình

**Cách verify:**
```sql
-- Đếm branch shops của từng tenant
SELECT tenant_id, COUNT(*) as total
FROM branch_shops
GROUP BY tenant_id;
```

---

### TC-BS-005: Thống kê được filter theo tenant

**Bước thực hiện:**
1. Đăng nhập vào hệ thống
2. Truy cập `/admin/branch-shops`
3. Kiểm tra các số liệu thống kê (nếu có):
   - Tổng số branch shops
   - Số branch shops active
   - Số branch shops inactive
   - Số branch shops có delivery
4. Đếm số lượng trong table
5. So sánh với database

**Kết quả mong đợi:**
- ✅ Statistics.total khớp với số lượng trong table
- ✅ Statistics chỉ tính branch shops của tenant hiện tại
- ✅ Các số liệu active, inactive, with_delivery đều đúng

**Cách verify:**
```sql
-- Thống kê branch shops của tenant hiện tại
SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
    SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive,
    SUM(CASE WHEN has_delivery = 1 THEN 1 ELSE 0 END) as with_delivery
FROM branch_shops
WHERE tenant_id = [current_tenant_id];
```

---

## 🔍 Kiểm tra Code Changes

### 1. BranchShopService.php

**Kiểm tra các methods đã được filter:**
- ✅ `getList()` - Filter by tenant_id
- ✅ `create()` - Auto-assign tenant_id
- ✅ `update()` - Filter by tenant_id before update
- ✅ `delete()` - Filter by tenant_id before delete
- ✅ `findById()` - Filter by tenant_id
- ✅ `getActiveForFilter()` - Filter by tenant_id
- ✅ `getActiveForDropdown()` - Filter by tenant_id
- ✅ `getAvailableForSwitcher()` - Filter by tenant_id
- ✅ `getWithDelivery()` - Filter by tenant_id
- ✅ `getStatistics()` - Filter by tenant_id
- ✅ `codeExists()` - Filter by tenant_id
- ✅ `bulkUpdateStatus()` - Filter by tenant_id
- ✅ `bulkDelete()` - Filter by tenant_id

### 2. BranchShopController.php

**Kiểm tra error handling:**
- ✅ `show()` - Returns clear error message
- ✅ `edit()` - Returns clear error message
- ✅ `update()` - Returns clear error message
- ✅ `destroy()` - Returns clear error message

---

## 📊 Checklist

### Trước khi test:
- [ ] Database có ít nhất 2 tenants
- [ ] Mỗi tenant có ít nhất 2-3 branch shops
- [ ] User có quyền truy cập nhiều tenants
- [ ] Server đang chạy

### Sau khi test:
- [ ] TC-BS-001: PASS / FAIL
- [ ] TC-BS-002: PASS / FAIL
- [ ] TC-BS-003: PASS / FAIL
- [ ] TC-BS-004: PASS / FAIL
- [ ] TC-BS-005: PASS / FAIL

### Cleanup:
- [ ] Xóa test data (branch shop với code TEST-001)
- [ ] Reset tenant về ban đầu

---

## 🐛 Troubleshooting

### Vấn đề: Không thể login
**Giải pháp:**
- Kiểm tra server đang chạy
- Kiểm tra domain trong hosts file
- Kiểm tra credentials đúng

### Vấn đề: Vẫn thấy branch shops của tenant khác
**Giải pháp:**
- Kiểm tra BranchShopService đã được deploy
- Clear cache: `php artisan cache:clear`
- Kiểm tra session có tenant_id

### Vấn đề: Không thể tạo branch shop mới
**Giải pháp:**
- Kiểm tra validation rules
- Kiểm tra database constraints
- Xem log file: `storage/logs/laravel.log`

---

## 📝 Ghi chú

1. **Tenant Context**: Tenant ID được lấy từ session với key `current_tenant_id`
2. **Authorization**: Implicit authorization thông qua tenant filtering
3. **Service Layer**: Tất cả business logic trong BranchShopService
4. **Error Messages**: Thông báo lỗi bằng tiếng Việt, rõ ràng

---

## ✅ Kết luận

Sau khi hoàn thành tất cả test cases, bạn có thể confirm rằng:
- ✅ Branch shops được filter đúng theo tenant
- ✅ Không thể truy cập branch shops của tenant khác
- ✅ Tenant_id được tự động gán khi tạo mới
- ✅ Danh sách cập nhật khi switch tenant
- ✅ Thống kê được filter đúng theo tenant

