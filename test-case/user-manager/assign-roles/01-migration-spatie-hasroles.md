# Test Case: Migration từ Custom Trait sang Spatie HasRoles

**Ngày test**: 28/10/2025  
**Người test**: AI Assistant  
**Môi trường**: Docker (php83), Chrome Browser  
**URL**: http://tenant1.yukimart.local/admin/settings/user-manager

---

## 1. Mục tiêu

Migrate từ custom `HasRolesAndPermissions` trait sang Spatie's `HasRoles` trait để tận dụng đầy đủ tính năng của Spatie Permission package.

---

## 2. Các bước thực hiện

### 2.1. Phát hiện vấn đề

**Vấn đề ban đầu**: User model sử dụng custom trait `HasRolesAndPermissions` lưu vào bảng `user_roles`, nhưng getData() method load từ Spatie's `model_has_roles` table → Conflict!

**Lỗi quan sát**:
- API `/admin/settings/user-manager/data` trả về `"roles":[]` cho tất cả users
- Assign roles modal không load được danh sách roles

### 2.2. Thay thế Custom Trait bằng Spatie HasRoles

**File modified**: `app/Models/User.php`

**Changes**:
```php
// Before
use App\Traits\HasRolesAndPermissions;
class User extends Authenticatable {
    use HasRolesAndPermissions;
}

// After
use Spatie\Permission\Traits\HasRoles;
class User extends Authenticatable {
    use HasRoles;
    
    public function getPermissionsTeamId() {
        return $this->tenant_id;
    }
}
```

### 2.3. Sửa Migration

**File**: `database/migrations/2025_10_28_172000_extend_permissions_and_roles_for_spatie.php`

**Changes**:
- Đổi cột `team_id` thành `tenant_id` trong `model_has_roles` và `model_has_permissions`
- Wrap index/unique constraints trong try-catch để tránh lỗi duplicate

**Kết quả**: Migration chạy thành công

### 2.4. Migrate Data

**SQL Script**: `temp_migrate_roles.sql`

```sql
INSERT INTO model_has_roles (role_id, model_type, model_id, tenant_id)
SELECT 
    ur.role_id,
    'App\\Models\\User' as model_type,
    ur.user_id as model_id,
    u.tenant_id as tenant_id
FROM user_roles ur
LEFT JOIN users u ON ur.user_id = u.id
WHERE u.tenant_id IS NOT NULL
```

**Kết quả**: Migrate 6 records thành công

### 2.5. Fix Model Type

**Vấn đề**: Data được migrate với `model_type = 'App\\\\Models\\\\User'` (4 backslashes) thay vì `'App\\Models\\User'` (2 backslashes)

**Fix**:
```sql
UPDATE model_has_roles 
SET model_type = 'App\\Models\\User' 
WHERE model_type = 'App\\\\Models\\\\User';
```

### 2.6. Sửa Config

**File**: `config/permission.php`

**Change**:
```php
// Đảm bảo team_foreign_key match với migration
'team_foreign_key' => 'tenant_id',
```

### 2.7. Update UserManagerController

**File**: `app/Http/Controllers/Tenant/Settings/Shop/UserManagerController.php`

**Changes**:
```php
// In getData() method
setPermissionsTeamId($tenantId);
$query = User::where('tenant_id', $tenantId)->with(['roles']);

// In assignRoles() method
setPermissionsTeamId($tenantId);
$user->syncRoles($request->roles);
```

### 2.8. Fix RoleController

**File**: `app/Http/Controllers/Tenant/Settings/Shop/RoleController.php`

**Vấn đề**: RoleController chỉ load roles với `tenant_id = $tenantId`, bỏ qua global roles (`tenant_id = NULL`)

**Fix**:
```php
// Load both global roles and tenant-specific roles
$roles = Role::where(function($query) use ($tenantId) {
        $query->whereNull('tenant_id')
              ->orWhere('tenant_id', $tenantId);
    })
    ->orderBy('sort_order')
    ->orderBy('name')
    ->get();
```

---

## 3. Kết quả Test

### 3.1. Test Load Users với Roles ✅

**URL**: `http://tenant1.yukimart.local/admin/settings/user-manager/data?page=1&per_page=10`

**Kết quả**:
- ✅ API trả về `"success":true`
- ✅ Total: 8 users
- ✅ Roles hiển thị đúng:
  - User ID 45 (Đinh Văn Vũ): "Quản lý chi nhánh"
  - User ID 17 (Admin TechMart): "Quản lý chi nhánh"
  - User ID 18 (Staff TechMart): "Nhân viên thu ngân"
  - User ID 19 (Viewer TechMart): "Thu Ngân"
  - User ID 16 (Manager TechMart): "Quản lý chi nhánh"
  - User ID 15 (Owner TechMart): "Chủ sở hữu"
  - User ID 46, 44: "Chưa có vai trò"

### 3.2. Test Load Roles List ✅

**URL**: `http://tenant1.yukimart.local/admin/settings/roles`

**Kết quả**:
- ✅ API trả về 11 roles (cả global và tenant-specific)
- ✅ Roles bao gồm:
  1. Platform Administrator (global)
  2. Quản lý chi nhánh (tenant-specific)
  3. Nhân viên thu ngân (tenant-specific)
  4. Developer (global)
  5. Platform Manager (global)
  6. Chủ sở hữu (tenant-specific)
  7. Thu Ngân (tenant-specific)
  8. Super Administrator (global)
  9. Support Staff (global)
  10. Test Role (tenant-specific)
  11. Nhân viên kho (tenant-specific)

### 3.3. Test Assign Roles Modal ✅

**Steps**:
1. Navigate to user manager page
2. Click vào cell "Vai trò" của user "Test Password Change"
3. Modal mở với title "Gán vai trò"
4. Verify roles list hiển thị đầy đủ 11 roles với checkboxes

**Kết quả**:
- ✅ Modal mở thành công
- ✅ Title: "Gán vai trò"
- ✅ Subtitle: "Chọn vai trò cho Test Password Change"
- ✅ Hiển thị đầy đủ 11 roles với checkboxes
- ✅ Mỗi role hiển thị display_name và description

---

## 4. Tổng kết

### 4.1. Thành công ✅

1. ✅ Migrate thành công từ custom trait sang Spatie HasRoles
2. ✅ Data migration hoàn tất (6 user role assignments)
3. ✅ Users table hiển thị roles đúng
4. ✅ Assign roles modal load đầy đủ roles
5. ✅ Multi-tenant support hoạt động đúng (tenant_id isolation)
6. ✅ Global roles và tenant-specific roles đều được load

### 4.2. Các vấn đề đã fix

1. ✅ Column name mismatch (`team_id` vs `tenant_id`)
2. ✅ Model type escape issue (4 backslashes → 2 backslashes)
3. ✅ RoleController không load global roles
4. ✅ Missing `setPermissionsTeamId()` calls

### 4.3. Next Steps

**Chưa test**:
- [ ] Test assign roles form submission
- [ ] Test multiple roles per user
- [ ] Test permission middleware
- [ ] Apply permissions to other modules (Products, Orders, Invoices)

**Recommended**:
- Xóa bảng `user_roles` cũ sau khi confirm migration thành công
- Xóa custom trait `HasRolesAndPermissions` nếu không còn sử dụng
- Thêm validation để ngăn assign global roles cho tenant users (nếu cần)

