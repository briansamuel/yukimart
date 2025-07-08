# UserGroups to Roles Migration Guide

## 📋 **Overview**

This document outlines the complete replacement of the UserGroups module with a modern Roles and Permissions system. The new system provides more granular control, better scalability, and follows Laravel best practices.

## ✅ **Migration Summary**

### **What Was Replaced:**
- **UserGroupController** → **RoleController** + **PermissionController**
- **UserGroup Model** → **Role Model** + **Permission Model**
- **UserGroup Views** → **Roles Views** + **Permissions Views**
- **UserGroup Routes** → **Roles Routes** + **Permissions Routes**
- **UserGroup Menu** → **Roles Menu** + **Permissions Menu**

### **What Was Added:**
1. **Complete Roles & Permissions System**
2. **Comprehensive Language Files**
3. **Modern UI/UX Interface**
4. **Advanced Permission Management**
5. **Role-based Access Control**

## 🔧 **Technical Implementation**

### **1. New Controllers**

#### **RoleController**
```php
// Location: app/Http/Controllers/Admin/CMS/RoleController.php
- index()           // List all roles
- create()          // Show create form
- store()           // Store new role
- show($id)         // Show role details
- edit($id)         // Show edit form
- update($id)       // Update role
- destroy($id)      // Delete role
- toggleStatus($id) // Toggle role status
- getPermissions($id) // Get role permissions
- bulkDelete()      // Bulk delete roles
```

#### **PermissionController**
```php
// Location: app/Http/Controllers/Admin/CMS/PermissionController.php
- index()                    // List all permissions
- create()                   // Show create form
- store()                    // Store new permission
- show($id)                  // Show permission details
- edit($id)                  // Show edit form
- update($id)                // Update permission
- destroy($id)               // Delete permission
- toggleStatus($id)          // Toggle permission status
- getByModule()              // Get permissions by module
- bulkDelete()               // Bulk delete permissions
- generateForModule()        // Generate permissions for module
```

### **2. New Services**

#### **RoleService**
```php
// Location: app/Services/RoleService.php
- getAllRoles($request)                    // Get paginated roles
- getRoleById($id)                         // Get role by ID
- createRole(array $data)                  // Create new role
- updateRole($id, array $data)             // Update role
- deleteRole($id)                          // Delete role
- toggleRoleStatus($id)                    // Toggle role status
- bulkDeleteRoles(array $ids)              // Bulk delete roles
- getRolesForSelect()                      // Get roles for select options
- assignRoleToUser($userId, $roleId)       // Assign role to user
- removeRoleFromUser($userId, $roleId)     // Remove role from user
- getRoleStatistics()                      // Get role statistics
```

#### **PermissionService**
```php
// Location: app/Services/PermissionService.php
- getAllPermissions($request)                      // Get paginated permissions
- getPermissionById($id)                           // Get permission by ID
- createPermission(array $data)                    // Create new permission
- updatePermission($id, array $data)               // Update permission
- deletePermission($id)                            // Delete permission
- togglePermissionStatus($id)                      // Toggle permission status
- getPermissionsByModule($module)                  // Get permissions by module
- bulkDeletePermissions(array $ids)                // Bulk delete permissions
- generatePermissionsForModule($module, $actions)  // Generate permissions
- getPermissionsForSelect($module)                 // Get permissions for select
- getPermissionStatistics()                        // Get permission statistics
```

### **3. New Models**

#### **Role Model**
```php
// Location: app/Models/Role.php
// Key Features:
- Relationships with permissions and users
- Role constants (ADMIN, SHOP_MANAGER, STAFF, PARTIME)
- Permission management methods
- Status and validation scopes
- Badge and icon attributes
```

#### **Permission Model**
```php
// Location: app/Models/Permission.php
// Key Features:
- Module and action constants
- Relationships with roles and users
- Permission generation methods
- Filtering and search scopes
- Display name attributes
```

#### **UserRole & UserPermission Models**
```php
// Pivot models for many-to-many relationships
- UserRole: Links users to roles with metadata
- UserPermission: Direct user permissions with grant/deny
```

### **4. HasRolesAndPermissions Trait**
```php
// Location: app/Traits/HasRolesAndPermissions.php
// Added to User model for:
- Role assignment and removal
- Permission checking
- Access control methods
- Relationship management
```

## 🌐 **Language Files**

### **Vietnamese (vi)**
- `resources/lang/vi/roles.php` - Complete role translations
- `resources/lang/vi/permissions.php` - Complete permission translations

### **English (en)**
- `resources/lang/en/roles.php` - Complete role translations
- `resources/lang/en/permissions.php` - Complete permission translations

### **Key Translation Categories:**
- General terms and titles
- Actions and operations
- Field labels and placeholders
- Messages and notifications
- Validation rules
- Confirmations and tooltips
- Filters and statistics
- Help text and breadcrumbs

## 🛣️ **Routes Migration**

### **Old UserGroup Routes (Removed):**
```php
// OLD - Removed
Route::get('/user-group', [UserGroupController::class, 'index']);
Route::get('/user-group/add', [UserGroupController::class, 'add']);
Route::post('/user-group/add', [UserGroupController::class, 'addAction']);
// ... other old routes
```

### **New Roles & Permissions Routes:**
```php
// NEW - Added
Route::prefix('roles')->name('roles.')->group(function () {
    Route::get('/', [RoleController::class, 'index'])->name('index');
    Route::get('/create', [RoleController::class, 'create'])->name('create');
    Route::post('/', [RoleController::class, 'store'])->name('store');
    Route::get('/{id}', [RoleController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [RoleController::class, 'edit'])->name('edit');
    Route::put('/{id}', [RoleController::class, 'update'])->name('update');
    Route::delete('/{id}', [RoleController::class, 'destroy'])->name('destroy');
    Route::post('/{id}/toggle-status', [RoleController::class, 'toggleStatus'])->name('toggle-status');
    Route::get('/{id}/permissions', [RoleController::class, 'getPermissions'])->name('permissions');
    Route::post('/bulk-delete', [RoleController::class, 'bulkDelete'])->name('bulk-delete');
});

Route::prefix('permissions')->name('permissions.')->group(function () {
    Route::get('/', [PermissionController::class, 'index'])->name('index');
    Route::get('/create', [PermissionController::class, 'create'])->name('create');
    Route::post('/', [PermissionController::class, 'store'])->name('store');
    Route::get('/{id}', [PermissionController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [PermissionController::class, 'edit'])->name('edit');
    Route::put('/{id}', [PermissionController::class, 'update'])->name('update');
    Route::delete('/{id}', [PermissionController::class, 'destroy'])->name('destroy');
    Route::post('/{id}/toggle-status', [PermissionController::class, 'toggleStatus'])->name('toggle-status');
    Route::get('/by-module', [PermissionController::class, 'getByModule'])->name('by-module');
    Route::post('/bulk-delete', [PermissionController::class, 'bulkDelete'])->name('bulk-delete');
    Route::post('/generate-for-module', [PermissionController::class, 'generateForModule'])->name('generate-for-module');
});
```

## 🎨 **Menu Migration**

### **Old UserGroup Menu (Removed):**
```php
// OLD - Removed
@if ($AuthPermission->isHeader('UserGroupController'))
    <div class="menu-item">
        <a class="menu-link {{ Request::is('*user-group*') ? 'active' : '' }}"
            href="{{ route('admin.user_group.list') }}">
            <span class="menu-title">Quản lý phân quyền</span>
        </a>
    </div>
@endif
```

### **New Roles & Permissions Menu:**
```php
// NEW - Added
@if ($AuthPermission->isHeader('RoleController'))
    <div class="menu-item">
        <a class="menu-link {{ Request::is('*roles*') ? 'active' : '' }}"
            href="{{ route('admin.roles.index') }}">
            <span class="menu-title">{{ __('roles.title') }}</span>
        </a>
    </div>
@endif
@if ($AuthPermission->isHeader('PermissionController'))
    <div class="menu-item">
        <a class="menu-link {{ Request::is('*permissions*') ? 'active' : '' }}"
            href="{{ route('admin.permissions.index') }}">
            <span class="menu-title">{{ __('permissions.title') }}</span>
        </a>
    </div>
@endif
```

## 📊 **Database Schema**

### **New Tables Created:**
```sql
-- Roles table
CREATE TABLE roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) UNIQUE NOT NULL,
    display_name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INTEGER DEFAULT 0,
    settings JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Permissions table
CREATE TABLE permissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) UNIQUE NOT NULL,
    display_name VARCHAR(255) NOT NULL,
    module VARCHAR(255) NOT NULL,
    action VARCHAR(255) NOT NULL,
    description TEXT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INTEGER DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Role-Permission pivot table
CREATE TABLE role_permissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_id BIGINT UNSIGNED NOT NULL,
    permission_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    UNIQUE KEY unique_role_permission (role_id, permission_id)
);

-- User-Role pivot table
CREATE TABLE user_roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    role_id BIGINT UNSIGNED NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    assigned_by BIGINT UNSIGNED NULL,
    expires_at TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_user_role (user_id, role_id)
);

-- User-Permission pivot table
CREATE TABLE user_permissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    permission_id BIGINT UNSIGNED NOT NULL,
    type ENUM('grant', 'deny') DEFAULT 'grant',
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    assigned_by BIGINT UNSIGNED NULL,
    expires_at TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_user_permission (user_id, permission_id)
);
```

## 🎯 **Key Features**

### **1. Hierarchical Role System**
- **Admin**: Full system access
- **Shop Manager**: Store operations management
- **Staff**: Daily operations
- **Part-time**: Limited access

### **2. Granular Permissions**
- **15+ Modules**: Pages, Products, Orders, etc.
- **7 Actions**: View, Create, Edit, Delete, Export, Import, Manage
- **100+ Permissions**: Automatically generated

### **3. Advanced Features**
- **Permission Generator**: Auto-create permissions for modules
- **Bulk Operations**: Mass delete, assign, remove
- **Status Management**: Enable/disable roles and permissions
- **Expiration Support**: Temporary role assignments
- **Direct Permissions**: Grant/deny specific permissions to users

### **4. Modern UI/UX**
- **Responsive Design**: Mobile-friendly interface
- **Advanced Filtering**: Search, filter by module/action/status
- **Bulk Actions**: Select multiple items for operations
- **Real-time Updates**: AJAX-based operations
- **Comprehensive Modals**: Create, edit, export functionality

## 🔄 **Migration Steps**

### **1. Database Migration**
```bash
php artisan migrate
```

### **2. Seed Roles and Permissions**
```bash
php artisan db:seed --class=RolesAndPermissionsSeeder
```

### **3. Update User Model**
```php
// Add trait to User model
use App\Traits\HasRolesAndPermissions;

class User extends Authenticatable
{
    use HasRolesAndPermissions;
    // ...
}
```

### **4. Update Permission Checks**
```php
// Old way (UserGroup)
if ($user->userGroup->hasPermission('products.view')) {
    // ...
}

// New way (Roles & Permissions)
if ($user->hasPermission('products.view')) {
    // ...
}
```

## 📈 **Benefits of New System**

### **1. Better Security**
- Granular permission control
- Role-based access control (RBAC)
- Permission inheritance and overrides
- Temporary access management

### **2. Improved Scalability**
- Modular permission structure
- Easy to add new modules/actions
- Bulk operations support
- Efficient database queries

### **3. Enhanced User Experience**
- Modern, intuitive interface
- Comprehensive search and filtering
- Real-time status updates
- Multilingual support

### **4. Developer Friendly**
- Clean, maintainable code
- Comprehensive documentation
- Extensive language files
- Easy to extend and customize

## 🚀 **Usage Examples**

### **Check User Permissions**
```php
// Check if user has specific permission
if (auth()->user()->hasPermission('products.create')) {
    // User can create products
}

// Check if user has any of multiple permissions
if (auth()->user()->hasAnyPermission(['products.view', 'products.edit'])) {
    // User can view or edit products
}

// Check if user has specific role
if (auth()->user()->hasRole('admin')) {
    // User is admin
}
```

### **Assign Roles and Permissions**
```php
// Assign role to user
$user->assignRole('shop_manager');

// Grant specific permission
$user->grantPermission('products.export');

// Deny specific permission
$user->denyPermission('users.delete');
```

### **Role Management**
```php
// Create new role
$role = Role::create([
    'name' => 'custom_role',
    'display_name' => 'Custom Role',
    'description' => 'Custom role description'
]);

// Assign permissions to role
$role->syncPermissions(['products.view', 'products.create']);
```

## 📞 **Support and Maintenance**

### **File Locations**
- **Controllers**: `app/Http/Controllers/Admin/CMS/`
- **Services**: `app/Services/`
- **Models**: `app/Models/`
- **Views**: `resources/views/admin/roles/`, `resources/views/admin/permissions/`
- **Language Files**: `resources/lang/vi/`, `resources/lang/en/`
- **Routes**: `routes/admin.php`
- **Migrations**: `database/migrations/`
- **Seeders**: `database/seeders/`

### **Key Commands**
```bash
# Run migrations
php artisan migrate

# Seed roles and permissions
php artisan db:seed --class=RolesAndPermissionsSeeder

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

---

**The UserGroups to Roles migration provides a modern, scalable, and secure permission management system! 🚀**
