# Branch Switcher Implementation Summary

## Ngày: 2025-10-26

## Yêu cầu từ User (CẬP NHẬT)

1. **branchSwitcher** hiển thị chi nhánh của người dùng (user đăng nhập)
2. Mỗi user có thể liên kết nhiều chi nhánh (owner, admin, manager)
3. **Admin, Manager** chỉ có thể có **1 chi nhánh chính** = cột `is_primary`
4. **Owner** không bị giới hạn, có thể chọn bất kỳ chi nhánh nào
5. Hiển thị chi nhánh chính ở đây và danh sách các chi nhánh có thể chọn (thuộc về user đăng nhập)
6. **[CẬP NHẬT]** Khi user chọn chi nhánh khác: **Cập nhật is_primary trong DB VÀ lưu vào session**
   - Set chi nhánh được chọn: `is_primary = true`
   - Set tất cả chi nhánh còn lại của user: `is_primary = false`
   - Lưu vào session
   - Reload trang

---

## Thay đổi đã thực hiện

### 1. **app/Services/BranchContextService.php**

#### Sửa `switchToBranchShop()` method (lines 137-197)

**Logic mới:**
```php
/**
 * Switch to a specific branch shop
 * Updates is_primary in database AND saves to session
 * Sets selected branch as is_primary = true, all other branches = false
 */
public function switchToBranchShop(int $branchShopId): bool
{
    // ... validation code ...

    // Update is_primary in database
    // Step 1: Set all branches to is_primary = false for this user
    $user->branchShops()->updateExistingPivot(
        $user->branchShops()->pluck('branch_shops.id')->toArray(),
        ['is_primary' => false]
    );

    // Step 2: Set selected branch to is_primary = true
    $user->branchShops()->updateExistingPivot($branchShopId, ['is_primary' => true]);

    // Step 3: Save to session
    $this->setCurrentBranchShop($branchShop);

    Log::info('Branch shop switched successfully (DB + session)', [
        'user_id' => $user->id,
        'branch_shop_id' => $branchShopId,
        'branch_shop_name' => $branchShop->name,
        'tenant_id' => $currentTenantId,
        'is_primary_updated' => true  // ← Updated to true
    ]);

    return true;
}
```

**Lý do:**
- Khi user chọn chi nhánh khác → Cập nhật `is_primary` trong database
- Chi nhánh được chọn trở thành chi nhánh chính mới
- Lưu vào session để sử dụng ngay lập tức
- Khi logout và login lại → Vẫn load chi nhánh mới (vì đã cập nhật is_primary)

---

### 2. **app/Providers/BranchContextServiceProvider.php**

#### Sửa View Composer cho `branch-switcher` component (lines 42-102)

**Thêm logic phân quyền:**

```php
// Get available branch shops based on user role
// Owner: All branch shops in tenant
// Admin/Manager: Only branch shops they are assigned to (from user_branch_shops)
$availableBranchShops = collect();

if ($canSeeBranchSwitcher && $currentTenantId) {
    if ($userRole === \App\Models\TenantUser::ROLE_OWNER) {
        // Owner can see all branch shops in tenant
        $availableBranchShops = \App\Models\BranchShop::where('tenant_id', $currentTenantId)
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    } else {
        // Admin/Manager can only see their assigned branch shops
        $availableBranchShops = $branchContextService->getAvailableBranchShops();
    }
}
```

**Lý do:**
- **Owner**: Xem tất cả chi nhánh trong tenant (không bị giới hạn bởi user_branch_shops)
- **Admin/Manager**: Chỉ xem chi nhánh được gán trong bảng `user_branch_shops`

---

### 3. **resources/views/components/branch-switcher.blade.php**

#### Thêm props `userRole` (line 1)

```php
@props(['currentBranchShop' => null, 'availableBranchShops' => collect(), 'canSeeBranchSwitcher' => false, 'userRole' => null])
```

#### Hiển thị badge "Chính" cho chi nhánh is_primary (lines 38-42)

**Current Branch Shop:**
```php
<div class="ms-2">
    @if($currentBranchShop->pivot && $currentBranchShop->pivot->is_primary)
        <span class="badge bg-warning text-dark me-1">
            <i class="fas fa-star"></i> Chính
        </span>
    @endif
    <span class="badge bg-primary">
        {{ $currentBranchShop->shop_type === 'flagship' ? 'Flagship' : ... }}
    </span>
</div>
```

**Available Branch Shops:**
```php
<div class="fw-medium">
    {{ $branchShop->name }}
    @if($branchShop->pivot && $branchShop->pivot->is_primary)
        <span class="badge bg-warning text-dark ms-1" style="font-size: 0.7rem;">
            <i class="fas fa-star"></i> Chính
        </span>
    @endif
</div>
```

#### Hiển thị text phân biệt Owner vs Admin/Manager (lines 57-61)

```php
<li class="dropdown-header">
    Chi nhánh khả dụng
    @if($userRole === 'owner')
        <small class="text-muted ms-2">(Tất cả chi nhánh)</small>
    @else
        <small class="text-muted ms-2">(Chi nhánh được gán)</small>
    @endif
</li>
```

---

### 4. **resources/views/admin/elements/app_account_menu.blade.php**

#### Branch Switcher được include trong app_account_menu (line 214)

```php
<!--begin::Branch switcher-->
@include('components.branch-switcher')
<!--end::Branch switcher-->
```

**Lưu ý:**
- Branch switcher đã có sẵn trong `app_account_menu.blade.php`
- KHÔNG cần thêm vào `tenant-header.blade.php` (sẽ bị trùng)

---

## Luồng hoạt động

### 1. **User đăng nhập lần đầu**

```
1. BranchContextService->getCurrentBranchShop()
2. Check session: current_branch_shop_id → NULL
3. Get user's primary branch shop (is_primary = true)
4. Set to session
5. Return primary branch shop
```

### 2. **User chọn chi nhánh khác từ dropdown**

```
1. User click vào chi nhánh trong dropdown
2. JavaScript gọi AJAX: POST /admin/branch-shops/switch
3. BranchShopController->switchBranchShop()
4. BranchContextService->switchToBranchShop($branchShopId)
5. Validate user có quyền truy cập chi nhánh này không
6. CẬP NHẬT is_primary trong database:
   - Set tất cả chi nhánh của user: is_primary = false
   - Set chi nhánh được chọn: is_primary = true
7. Lưu vào session: Session::put('current_branch_shop_id', $branchShopId)
8. Return success
9. JavaScript reload trang: window.location.reload()
```

### 3. **User logout**

```
1. Session bị clear
2. Lần đăng nhập tiếp theo sẽ load chi nhánh is_primary (đã được cập nhật từ lần switch trước)
```

---

## Database Schema

### Bảng `user_branch_shops`

```sql
CREATE TABLE user_branch_shops (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    branch_shop_id BIGINT UNSIGNED NOT NULL,
    role_in_shop VARCHAR(50),  -- manager, staff, cashier, sales, warehouse_keeper
    start_date DATE,
    end_date DATE,
    is_active BOOLEAN DEFAULT TRUE,
    is_primary BOOLEAN DEFAULT FALSE,  -- ← Chi nhánh chính (mặc định khi login)
    notes TEXT,
    assigned_by BIGINT UNSIGNED,
    assigned_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    UNIQUE KEY unique_user_branch_shop (user_id, branch_shop_id),
    INDEX idx_user_primary_branch (user_id, is_primary)
);
```

**Ý nghĩa `is_primary`:**
- Mỗi user chỉ có **1 chi nhánh** với `is_primary = true`
- Dùng để xác định chi nhánh mặc định khi user đăng nhập
- **CẬP NHẬT** khi user chọn chi nhánh khác từ dropdown (chi nhánh được chọn trở thành chi nhánh chính mới)
- Có thể thay đổi thủ công trong trang quản lý user

---

## Phân quyền

| Role    | Xem chi nhánh                                      | Giới hạn is_primary |
|---------|----------------------------------------------------|---------------------|
| Owner   | Tất cả chi nhánh trong tenant                      | Không               |
| Admin   | Chỉ chi nhánh được gán trong `user_branch_shops`  | Có (1 chi nhánh)    |
| Manager | Chỉ chi nhánh được gán trong `user_branch_shops`  | Có (1 chi nhánh)    |
| Staff   | Không hiển thị branch switcher                     | N/A                 |
| Viewer  | Không hiển thị branch switcher                     | N/A                 |

---

## Testing Checklist

- [ ] Owner login → Xem tất cả chi nhánh trong tenant
- [ ] Admin login → Chỉ xem chi nhánh được gán
- [ ] Manager login → Chỉ xem chi nhánh được gán
- [ ] Staff login → Không thấy branch switcher
- [ ] Chi nhánh is_primary hiển thị badge "Chính"
- [ ] Chọn chi nhánh khác → Cập nhật is_primary trong DB + lưu vào session
- [ ] Reload trang → Chi nhánh vẫn giữ nguyên (từ session)
- [ ] Logout → Session clear
- [ ] Login lại → Load chi nhánh is_primary (đã được cập nhật)
- [ ] Database: is_primary ĐÃ THAY ĐỔI khi switch (chi nhánh mới = true, các chi nhánh khác = false)

---

## Files Changed

1. `app/Services/BranchContextService.php` - Sửa logic switch (DB + session)
2. `app/Providers/BranchContextServiceProvider.php` - Thêm logic phân quyền Owner vs Admin/Manager
3. `resources/views/components/branch-switcher.blade.php` - Hiển thị badge "Chính", text phân biệt role
4. `resources/views/admin/elements/app_account_menu.blade.php` - Đã có sẵn include branch-switcher (line 214)
5. `test-case/branch-switcher/implementation-summary.md` - Cập nhật documentation

---

## Next Steps

1. Test với Playwright để verify tất cả functionality
2. Kiểm tra database để confirm is_primary ĐÃ THAY ĐỔI khi switch
3. Test với các role khác nhau (Owner, Admin, Manager)
4. Verify session persistence sau reload
5. Verify is_primary được cập nhật đúng (chi nhánh mới = true, các chi nhánh khác = false)

