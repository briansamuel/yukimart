# Branch Shops Tenant Filter Tests

## 📋 Tổng Quan

Bộ test cases này kiểm tra chức năng filter branch shops theo tenant_id, đảm bảo:
- Mỗi tenant chỉ thấy branch shops của mình
- Không thể truy cập branch shops của tenant khác
- Tenant_id được tự động gán khi tạo mới
- Danh sách branch shops cập nhật khi switch tenant

## 🎯 Test Cases

### TC-BS-001: Display only branch shops of current tenant
**Mô tả**: Kiểm tra danh sách branch shops chỉ hiển thị các chi nhánh thuộc tenant hiện tại

**Bước thực hiện**:
1. Đăng nhập với user yukimart@gmail.com
2. Truy cập trang /admin/branch-shops
3. Lấy tenant_id hiện tại từ API
4. Kiểm tra tất cả branch shops trong table
5. Verify qua API rằng tất cả branch shops đều thuộc tenant hiện tại

**Kết quả mong đợi**:
- Chỉ hiển thị branch shops có tenant_id = current tenant
- Số lượng từ UI khớp với API
- Không có branch shops từ tenant khác

---

### TC-BS-002: Return 404 when accessing branch shop from another tenant
**Mô tả**: Kiểm tra authorization khi user cố truy cập branch shop không thuộc tenant của họ

**Bước thực hiện**:
1. Đăng nhập với user yukimart@gmail.com
2. Lấy ID của branch shop thuộc tenant khác (ID 9999)
3. Truy cập /admin/branch-shops/9999/edit
4. Kiểm tra response

**Kết quả mong đợi**:
- Redirect về /admin/branch-shops
- Hiển thị error message "không có quyền truy cập"
- Không thể xem/edit branch shop của tenant khác

---

### TC-BS-003: Auto-assign tenant_id when creating new branch shop
**Mô tả**: Kiểm tra tenant_id được tự động gán khi tạo branch shop mới

**Bước thực hiện**:
1. Đăng nhập với user yukimart@gmail.com
2. Lấy current tenant_id
3. Truy cập /admin/branch-shops/create
4. Điền form với dữ liệu test
5. Submit form
6. Kiểm tra branch shop vừa tạo qua API

**Kết quả mong đợi**:
- Branch shop được tạo thành công
- tenant_id = current tenant_id
- Không cần điền tenant_id trong form

**Dữ liệu test**:
```javascript
{
  code: `TEST-${timestamp}`,
  name: `Test Branch Shop ${timestamp}`,
  address: '123 Test Street',
  province: 'TP.HCM',
  district: 'Quận 1',
  ward: 'Phường Bến Nghé',
  phone: '0123456789',
  email: `test${timestamp}@yukimart.vn`,
  status: 'active',
  shop_type: 'standard'
}
```

---

### TC-BS-004: Update branch shops list when switching tenant
**Mô tả**: Kiểm tra danh sách branch shops cập nhật khi switch sang tenant khác

**Bước thực hiện**:
1. Đăng nhập với user yukimart@gmail.com
2. Truy cập /admin/branch-shops
3. Đếm số lượng branch shops hiện tại
4. Lấy danh sách available tenants
5. Switch sang tenant khác qua API
6. Reload trang
7. Đếm số lượng branch shops mới
8. Switch về tenant ban đầu

**Kết quả mong đợi**:
- Số lượng branch shops thay đổi sau khi switch tenant
- Danh sách branch shops khác nhau giữa các tenant
- Switch tenant thành công

**Note**: Test sẽ skip nếu chỉ có 1 tenant trong hệ thống

---

### TC-BS-005: Filter statistics by tenant
**Mô tả**: Kiểm tra thống kê branch shops được filter theo tenant

**Bước thực hiện**:
1. Đăng nhập với user yukimart@gmail.com
2. Truy cập /admin/branch-shops
3. Lấy statistics từ API endpoint /admin/branch-shops/statistics/summary
4. Đếm số lượng branch shops trong table
5. So sánh statistics với table count

**Kết quả mong đợi**:
- Statistics.total khớp hoặc gần với số lượng trong table
- Statistics chỉ tính branch shops của tenant hiện tại
- Các số liệu active, inactive, with_delivery đều đúng

---

## 🚀 Chạy Tests

### Chạy tất cả tests
```bash
cd test-case
npm test branch-shops/
```

### Chạy test cụ thể
```bash
npm test branch-shops/tenant-filter.test.js
```

### Chạy với browser hiển thị
```bash
npm run test:headed branch-shops/
```

### Chạy với debug mode
```bash
npm run test:debug branch-shops/tenant-filter.test.js
```

## 📊 Test Results

### Expected Results
| Test ID | Description | Expected Status |
|---------|-------------|-----------------|
| TC-BS-001 | Display only current tenant's branch shops | ✅ PASS |
| TC-BS-002 | Return 404 for other tenant's branch shop | ✅ PASS |
| TC-BS-003 | Auto-assign tenant_id on create | ✅ PASS |
| TC-BS-004 | Update list when switching tenant | ✅ PASS |
| TC-BS-005 | Filter statistics by tenant | ✅ PASS |

### Actual Results
_Sẽ được cập nhật sau khi chạy tests_

## 🔧 Cấu Hình

### Test Environment
- **Base URL**: `http://yukimart.local`
- **Browser**: Chromium (default)
- **Login**: yukimart@gmail.com / 123456
- **Timeout**: 30 seconds per action

### Prerequisites
- Có ít nhất 2 tenants trong database
- Mỗi tenant có ít nhất 1 branch shop
- User yukimart@gmail.com có quyền truy cập cả 2 tenants

## 📝 Notes

1. **Tenant Switching**: Test TC-BS-004 yêu cầu có ít nhất 2 tenants. Nếu chỉ có 1 tenant, test sẽ tự động skip.

2. **Test Data Cleanup**: Test TC-BS-003 tạo branch shop mới với code `TEST-{timestamp}`. Có thể cần cleanup sau khi test.

3. **Authorization**: Test TC-BS-002 sử dụng hardcoded ID 9999 cho branch shop của tenant khác. Có thể cần điều chỉnh ID này tùy theo database.

4. **API Endpoints**: Tests sử dụng các API endpoints:
   - `/api/tenant/current` - Lấy tenant hiện tại
   - `/api/tenant/available` - Lấy danh sách tenants
   - `/api/tenant/switch` - Switch tenant
   - `/admin/branch-shops/data` - Lấy danh sách branch shops
   - `/admin/branch-shops/statistics/summary` - Lấy thống kê

## 🐛 Troubleshooting

### Test fails với "No branch shops found"
- Kiểm tra database có branch shops cho tenant hiện tại
- Verify tenant_id trong bảng branch_shops

### Test fails với "Cannot switch tenant"
- Kiểm tra user có quyền truy cập nhiều tenants
- Verify API endpoint `/api/tenant/switch` hoạt động

### Test fails với "Authorization check not working"
- Kiểm tra BranchShopService đã filter theo tenant_id
- Verify middleware tenant.resolve đang hoạt động

## 📚 Related Documentation

- [BranchShopService.php](../../app/Services/BranchShopService.php)
- [BranchShopController.php](../../app/Http/Controllers/Admin/CMS/BranchShopController.php)
- [TenantContextService.php](../../app/Services/TenantContextService.php)

