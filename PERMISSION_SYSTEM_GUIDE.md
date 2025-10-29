# YukiMart Permission System Guide

## 🎯 **Overview**

The YukiMart Multi-Tenant System implements a comprehensive permission system that ensures secure access control across platform and tenant levels.

## 🏗️ **Architecture**

### **Two-Level Permission System**

#### **1. Platform Level**
- **Users**: Superadmin, Admin, Dev, Manager, Support
- **Access**: Can manage platform and access any tenant
- **Permissions**: System-wide administrative capabilities

#### **2. Tenant Level**
- **Users**: Owner, Admin, Manager, Staff, Viewer
- **Access**: Limited to specific tenant only
- **Permissions**: Business operation capabilities within tenant

## 👥 **User Roles & Permissions**

### **🏢 Platform Roles**

#### **Super Administrator**
```
Email: superadmin@yukimart.local
Password: 123456
Permissions: ALL (*)
Access: 
- Platform dashboard
- All tenant management
- System configuration
- User management
- Can switch to any tenant
```

#### **Platform Administrator**
```
Email: admin@yukimart.local
Password: 123456
Permissions:
- platform.access
- platform.tenants.manage
- platform.users.manage
- platform.analytics.view
- tenant.access (all tenants)
```

#### **Development Manager**
```
Email: dev@yukimart.local
Password: 123456
Permissions:
- platform.access
- platform.tenants.manage
- platform.settings.manage
- platform.analytics.view
- tenant.access (all tenants)
```

#### **Platform Manager**
```
Email: manager@yukimart.local
Password: 123456
Permissions:
- platform.access
- platform.tenants.manage
- platform.analytics.view
- tenant.access (all tenants)
```

#### **System Support**
```
Email: support@yukimart.local
Password: 123456
Permissions:
- platform.access
- platform.analytics.view
- tenant.access (all tenants)
```

### **🏪 Tenant Roles**

#### **Owner**
```
Permissions: ALL tenant operations (*)
Can do:
- All business operations
- User management within tenant
- Settings management
- Financial operations
- Reports and analytics
```

#### **Admin**
```
Permissions:
- users.view, users.create, users.edit, users.delete
- products.view, products.create, products.edit, products.delete
- orders.view, orders.create, orders.edit, orders.delete
- invoices.view, invoices.create, invoices.edit, invoices.delete
- returns.view, returns.create, returns.edit, returns.delete
- payments.view, payments.create, payments.edit, payments.delete
- customers.view, customers.create, customers.edit, customers.delete
- reports.view, reports.export
- settings.view, settings.edit
```

#### **Manager**
```
Permissions:
- products.view, products.create, products.edit
- orders.view, orders.create, orders.edit
- invoices.view, invoices.create, invoices.edit
- returns.view, returns.create, returns.edit
- payments.view, payments.create, payments.edit
- customers.view, customers.create, customers.edit
- reports.view
```

#### **Staff**
```
Permissions:
- products.view
- orders.view, orders.create
- invoices.view, invoices.create
- customers.view, customers.create
```

#### **Viewer**
```
Permissions:
- products.view
- orders.view
- invoices.view
- customers.view
```

## 🔧 **Technical Implementation**

### **Middleware Stack**

#### **1. ResolveTenantMiddleware**
```php
// Resolves tenant context from subdomain
// Skips: auth routes, platform routes
// Sets: current tenant in session/context
```

#### **2. TenantAuthMiddleware**
```php
// Verifies user belongs to tenant
// Platform users: automatic access
// Tenant users: check TenantUser relationship
```

#### **3. CheckTenantPermissionMiddleware**
```php
// Checks specific permissions
// Auto-detects permissions from routes
// Maps HTTP methods to actions
```

### **Permission Checking Flow**

```
1. User makes request to admin route
2. ResolveTenantMiddleware resolves tenant from subdomain
3. TenantAuthMiddleware checks if user belongs to tenant
4. CheckTenantPermissionMiddleware checks specific permission
5. If all pass, request proceeds to controller
```

### **Permission Resolution Logic**

```php
// 1. Check if platform user
if (user has platform role) {
    return true; // Platform users have all permissions
}

// 2. Check explicit permissions
if (permission in user.permissions) {
    return true;
}

// 3. Check role-based permissions
if (role has permission) {
    return true;
}

return false;
```

## 🛠️ **Configuration**

### **Route Protection**

All admin routes automatically protected by:
```php
$this->middleware(['auth:admin', 'tenant.resolve', 'tenant.auth', 'tenant.permission']);
```

### **Permission Mapping**

Routes automatically mapped to permissions:
```php
'admin.products.index' => 'products.view'
'admin.products.create' => 'products.create'
'admin.products.edit' => 'products.edit'
'admin.products.destroy' => 'products.delete'
```

### **HTTP Method Mapping**

```php
GET -> view (or edit if /edit in URL)
POST -> create
PUT/PATCH -> edit
DELETE -> delete
```

## 🧪 **Testing & Debugging**

### **Test Commands**

#### **Test Specific Permission**
```bash
php artisan yukimart:test-permission owner@techmart.local tenant1 products.view
```

#### **Debug User Data**
```bash
php artisan yukimart:debug-tenant-user owner@techmart.local
```

#### **Comprehensive Audit**
```bash
php artisan yukimart:permission-audit --detailed --fix
```

#### **Verify User Setup**
```bash
php artisan yukimart:verify-users --detailed --test-login
```

### **Test Scripts**

#### **Permission Test Script**
```bash
bash test-tenant-permissions.sh
```

#### **Complete System Verification**
```bash
bash verify-complete-setup.sh
```

## 🔍 **Troubleshooting**

### **Common Issues**

#### **"Không có quyền truy cập" Error**

**Possible Causes:**
1. User not active in tenant
2. Invitation not accepted
3. Missing role permissions
4. Middleware not applied

**Solutions:**
```bash
# Check user data
php artisan yukimart:debug-tenant-user user@email.com

# Fix user issues
php artisan yukimart:debug-tenant-user user@email.com --fix

# Run permission audit
php artisan yukimart:permission-audit --fix
```

#### **Platform User Can't Access Tenant**

**Check:**
1. User has platform role
2. Tenant exists and is active
3. Middleware stack is correct

#### **Tenant User Can't Access Features**

**Check:**
1. TenantUser relationship exists
2. User is active (`is_active = true`)
3. Invitation accepted (`invitation_status = 'accepted'`)
4. Role has required permissions

### **Debug Steps**

1. **Check User Authentication**
   ```bash
   # Verify user exists and password is correct
   php artisan tinker
   >>> $user = User::where('email', 'user@email.com')->first();
   >>> Hash::check('123456', $user->password);
   ```

2. **Check Tenant Relationship**
   ```bash
   # Verify tenant user relationship
   >>> $tenantUser = TenantUser::where('user_id', $user->id)->first();
   >>> $tenantUser->is_active;
   >>> $tenantUser->invitation_status;
   ```

3. **Check Permission**
   ```bash
   # Test specific permission
   php artisan yukimart:test-permission user@email.com tenant1 products.view
   ```

## 📊 **Monitoring & Maintenance**

### **Regular Checks**

#### **Weekly**
```bash
# Run permission audit
php artisan yukimart:permission-audit

# Verify user setup
php artisan yukimart:verify-users
```

#### **Monthly**
```bash
# Full system verification
bash verify-complete-setup.sh

# Export audit results
php artisan yukimart:permission-audit --export
```

### **Performance Monitoring**

Monitor these metrics:
- Permission check response times
- Failed permission attempts
- User login success rates
- Tenant access patterns

### **Security Auditing**

Regular security checks:
- Review user permissions
- Check for inactive users
- Audit role assignments
- Monitor failed access attempts

## 🚀 **Best Practices**

### **Development**

1. **Always test permissions** when adding new features
2. **Use middleware** instead of manual permission checks
3. **Follow naming conventions** for permissions
4. **Document custom permissions** in this guide

### **Production**

1. **Regular audits** using provided commands
2. **Monitor logs** for permission failures
3. **Keep user data clean** (active status, accepted invitations)
4. **Backup permission configurations**

### **Security**

1. **Principle of least privilege** - give minimum required permissions
2. **Regular password updates** - change default passwords
3. **Monitor suspicious activity** - failed login attempts
4. **Keep audit trails** - log permission changes

## 📞 **Support**

### **Commands Reference**

```bash
# User management
php artisan yukimart:setup-users
php artisan yukimart:verify-users
php artisan yukimart:debug-tenant-user

# Permission testing
php artisan yukimart:test-permission
php artisan yukimart:permission-audit

# System health
php artisan yukimart:db-health
```

### **Log Files**

Check these logs for issues:
- `storage/logs/laravel.log` - General application logs
- `storage/logs/permission_audit_*.json` - Audit results
- `storage/logs/worker.log` - Background job logs

### **Emergency Procedures**

If permission system fails:

1. **Check middleware registration** in `app/Http/Kernel.php`
2. **Verify tenant context** is being set correctly
3. **Clear all caches** (`config:clear`, `route:clear`)
4. **Run permission audit** with `--fix` option
5. **Check database** for TenantUser relationships

---

**🎉 The YukiMart Permission System is designed for security, scalability, and ease of maintenance!**
