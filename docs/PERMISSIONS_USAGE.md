# Hướng dẫn sử dụng Permissions System

## 1. Tổng quan

Hệ thống permissions sử dụng **Spatie Laravel Permission** package (v6.22.0) với multi-tenant support.

### Cấu trúc Permission

Permissions được đặt tên theo format: `{module}.{sub_module}.{action}`

Ví dụ:
- `settings.roles.read` - Xem danh sách vai trò
- `settings.roles.create` - Tạo vai trò mới
- `settings.roles.update` - Cập nhật vai trò
- `settings.roles.delete` - Xóa vai trò
- `catalog.products.read` - Xem danh sách sản phẩm
- `catalog.products.create` - Tạo sản phẩm mới

## 2. Sử dụng trong Routes

### 2.1. Middleware `permission`

Kiểm tra user có permission cụ thể:

```php
Route::get('/roles', [RoleController::class, 'index'])
    ->middleware('permission:settings.roles.read');
```

### 2.2. Middleware `role`

Kiểm tra user có role cụ thể:

```php
Route::get('/admin/dashboard', [DashboardController::class, 'index'])
    ->middleware('role:admin');
```

### 2.3. Middleware `role_or_permission`

Kiểm tra user có role HOẶC permission:

```php
Route::get('/reports', [ReportController::class, 'index'])
    ->middleware('role_or_permission:admin|reports.view');
```

### 2.4. Multiple Permissions

Kiểm tra nhiều permissions (user phải có TẤT CẢ):

```php
Route::post('/products', [ProductController::class, 'store'])
    ->middleware('permission:catalog.products.create,catalog.categories.read');
```

## 3. Sử dụng trong Controller

### 3.1. Authorize trong method

```php
public function index()
{
    // Throw 403 nếu không có permission
    $this->authorize('settings.roles.read');
    
    // Hoặc check và return custom response
    if (!auth()->user()->can('settings.roles.read')) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }
    
    // Your code here
}
```

### 3.2. Check multiple permissions

```php
// Check user có TẤT CẢ permissions
if (auth()->user()->hasAllPermissions(['catalog.products.create', 'catalog.products.update'])) {
    // Do something
}

// Check user có BẤT KỲ permission nào
if (auth()->user()->hasAnyPermission(['catalog.products.create', 'catalog.products.update'])) {
    // Do something
}
```

### 3.3. Check role

```php
if (auth()->user()->hasRole('admin')) {
    // Do something
}

// Check multiple roles
if (auth()->user()->hasAnyRole(['admin', 'manager'])) {
    // Do something
}
```

## 4. Sử dụng trong Blade Views

### 4.1. Directive `@can`

```blade
@can('settings.roles.create')
    <button class="btn btn-primary">Tạo vai trò</button>
@endcan

@can('settings.roles.update')
    <a href="{{ route('admin.settings.roles.edit', $role->id) }}">Sửa</a>
@endcan

@can('settings.roles.delete')
    <button class="btn-delete" data-id="{{ $role->id }}">Xóa</button>
@endcan
```

### 4.2. Directive `@cannot`

```blade
@cannot('settings.roles.delete')
    <p class="text-muted">Bạn không có quyền xóa vai trò</p>
@endcannot
```

### 4.3. Directive `@canany`

Hiển thị nếu user có BẤT KỲ permission nào:

```blade
@canany(['settings.roles.update', 'settings.roles.delete'])
    <div class="actions">
        @can('settings.roles.update')
            <button>Sửa</button>
        @endcan
        
        @can('settings.roles.delete')
            <button>Xóa</button>
        @endcan
    </div>
@endcanany
```

### 4.4. Directive `@role`

```blade
@role('admin')
    <div class="admin-panel">
        <!-- Admin only content -->
    </div>
@endrole

@hasrole('admin|manager')
    <div class="management-panel">
        <!-- Admin or Manager content -->
    </div>
@endhasrole
```

## 5. Gán Permissions cho User

### 5.1. Gán trực tiếp

```php
$user = User::find(1);

// Gán một permission
$user->givePermissionTo('settings.roles.create');

// Gán nhiều permissions
$user->givePermissionTo(['settings.roles.create', 'settings.roles.update']);

// Revoke permission
$user->revokePermissionTo('settings.roles.delete');

// Sync permissions (remove old, add new)
$user->syncPermissions(['settings.roles.read', 'settings.roles.create']);
```

### 5.2. Gán qua Role

```php
$user = User::find(1);

// Gán role
$user->assignRole('admin');

// Gán nhiều roles
$user->assignRole(['admin', 'manager']);

// Remove role
$user->removeRole('admin');

// Sync roles
$user->syncRoles(['admin']);
```

## 6. Gán Permissions cho Role

```php
$role = Role::findByName('admin');

// Gán permissions cho role
$role->givePermissionTo('settings.roles.create');
$role->givePermissionTo(['settings.roles.create', 'settings.roles.update']);

// Sync permissions
$role->syncPermissions([
    'settings.roles.read',
    'settings.roles.create',
    'settings.roles.update',
    'settings.roles.delete',
]);
```

## 7. Multi-Tenant Support

Hệ thống sử dụng `tenant_id` để phân tách permissions và roles giữa các tenants.

### 7.1. Tạo Role cho Tenant

```php
$role = Role::create([
    'name' => 'manager',
    'guard_name' => 'web',
    'display_name' => 'Quản lý',
    'tenant_id' => $tenantId,
]);
```

### 7.2. Query Roles theo Tenant

```php
$roles = Role::where('tenant_id', $tenantId)->get();
```

### 7.3. Permissions là Global

Permissions được seed globally (`tenant_id = NULL`) và được share giữa tất cả tenants.

## 8. Best Practices

1. **Luôn check permissions trong Controller** - Không chỉ dựa vào middleware
2. **Sử dụng `@can` trong views** - Ẩn UI elements mà user không có quyền truy cập
3. **Đặt tên permissions rõ ràng** - Theo format `{module}.{sub_module}.{action}`
4. **Gán permissions qua Roles** - Thay vì gán trực tiếp cho users
5. **Cache permissions** - Spatie tự động cache, nhưng có thể clear bằng `php artisan permission:cache-reset`

## 9. Troubleshooting

### 9.1. Permission denied sau khi gán

Clear permission cache:

```bash
php artisan permission:cache-reset
```

### 9.2. Check permissions của user

```php
$user = User::find(1);

// Get all permissions
$permissions = $user->getAllPermissions();

// Get all roles
$roles = $user->getRoleNames();

// Check specific permission
$hasPermission = $user->hasPermissionTo('settings.roles.create');
```

### 9.3. Debug trong Blade

```blade
@php
    $user = auth()->user();
    dd($user->getAllPermissions()->pluck('name'));
@endphp
```

## 10. API Reference

### User Methods

- `$user->givePermissionTo($permission)`
- `$user->revokePermissionTo($permission)`
- `$user->syncPermissions($permissions)`
- `$user->hasPermissionTo($permission)`
- `$user->hasAnyPermission($permissions)`
- `$user->hasAllPermissions($permissions)`
- `$user->assignRole($role)`
- `$user->removeRole($role)`
- `$user->syncRoles($roles)`
- `$user->hasRole($role)`
- `$user->hasAnyRole($roles)`
- `$user->hasAllRoles($roles)`

### Role Methods

- `$role->givePermissionTo($permission)`
- `$role->revokePermissionTo($permission)`
- `$role->syncPermissions($permissions)`
- `$role->hasPermissionTo($permission)`

### Blade Directives

- `@can($permission)`
- `@cannot($permission)`
- `@canany($permissions)`
- `@role($role)`
- `@hasrole($role)`
- `@hasanyrole($roles)`
- `@hasallroles($roles)`

