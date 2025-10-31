# 🚀 TENANT MIGRATION EXECUTION GUIDE

## 📋 **PRE-EXECUTION CHECKLIST**

### **Environment Preparation**
- [ ] **Backup Database**: Create full database backup
- [ ] **Staging Test**: Test migrations on staging environment first
- [ ] **Maintenance Mode**: Plan maintenance window if needed
- [ ] **Team Notification**: Notify team about migration schedule
- [ ] **Rollback Plan**: Ensure rollback procedures are ready

### **Technical Requirements**
- [ ] **Laravel Version**: Ensure Laravel 9+ compatibility
- [ ] **Database Version**: MySQL 8.0+ or MariaDB 10.3+
- [ ] **PHP Version**: PHP 8.1+
- [ ] **Memory Limit**: Increase PHP memory limit for large datasets
- [ ] **Execution Time**: Increase max_execution_time for migration

---

## 🔧 **STEP-BY-STEP EXECUTION**

### **Step 1: Backup Current Database**
```bash
# Create full database backup
mysqldump -u username -p yukimart > backup_before_tenant_migration_$(date +%Y%m%d_%H%M%S).sql

# Verify backup file
ls -la backup_before_tenant_migration_*.sql
```

### **Step 2: Check Migration Status**
```bash
# Check current migration status
php artisan migrate:status

# Ensure all existing migrations are applied
php artisan migrate
```

### **Step 3: Execute Tenant Migrations (Sequential Order)**

#### **3.1: Create Tenants Table**
```bash
# Execute first migration
php artisan migrate --path=database/migrations/2025_08_10_120000_create_tenants_table.php

# Verify table creation
php artisan tinker
>>> Schema::hasTable('tenants')
>>> DB::table('tenants')->count()
```

#### **3.2: Create Tenant Relationship Tables**
```bash
# Execute relationship tables migration
php artisan migrate --path=database/migrations/2025_08_10_120001_create_tenant_relationship_tables.php

# Verify tables creation
php artisan tinker
>>> Schema::hasTable('tenant_users')
>>> Schema::hasTable('tenant_settings')
>>> Schema::hasTable('tenant_invitations')
>>> Schema::hasTable('tenant_activity_logs')
```

#### **3.3: Add tenant_id to Core Tables**
```bash
# Execute core tables migration
php artisan migrate --path=database/migrations/2025_08_10_120002_add_tenant_id_to_core_tables.php

# Verify columns added
php artisan tinker
>>> Schema::hasColumn('users', 'tenant_id')
>>> Schema::hasColumn('roles', 'tenant_id')
>>> Schema::hasColumn('permissions', 'tenant_id')
>>> Schema::hasColumn('branch_shops', 'tenant_id')
```

#### **3.4: Add tenant_id to Business Tables**
```bash
# Execute business tables migration
php artisan migrate --path=database/migrations/2025_08_10_120003_add_tenant_id_to_business_tables.php

# Verify columns added
php artisan tinker
>>> Schema::hasColumn('customers', 'tenant_id')
>>> Schema::hasColumn('products', 'tenant_id')
>>> Schema::hasColumn('inventories', 'tenant_id')
```

#### **3.5: Add tenant_id to Transaction Tables**
```bash
# Execute transaction tables migration
php artisan migrate --path=database/migrations/2025_08_10_120004_add_tenant_id_to_transaction_tables.php

# Verify columns added
php artisan tinker
>>> Schema::hasColumn('orders', 'tenant_id')
>>> Schema::hasColumn('invoices', 'tenant_id')
>>> Schema::hasColumn('payments', 'tenant_id')
```

#### **3.6: Create Default Tenant and Migrate Data**
```bash
# Execute data migration (CRITICAL STEP)
php artisan migrate --path=database/migrations/2025_08_10_120005_create_default_tenant_and_migrate_data.php

# Verify default tenant creation
php artisan tinker
>>> $tenant = DB::table('tenants')->first()
>>> $tenant->name
>>> DB::table('users')->where('tenant_id', 1)->count()
>>> DB::table('tenant_users')->count()
```

### **Step 4: Post-Migration Validation**

#### **4.1: Data Integrity Check**
```bash
# Run validation script
php artisan tinker

# Check tenant data
>>> $tenant = DB::table('tenants')->first()
>>> $tenant

# Check user-tenant relationships
>>> DB::table('tenant_users')->count()
>>> DB::table('users')->whereNull('tenant_id')->count() // Should be 0

# Check product data
>>> DB::table('products')->whereNull('tenant_id')->count() // Should be 0
>>> DB::table('products')->where('tenant_id', 1)->count()

# Check order data
>>> DB::table('orders')->whereNull('tenant_id')->count() // Should be 0
>>> DB::table('orders')->where('tenant_id', 1)->count()
```

#### **4.2: Foreign Key Validation**
```bash
# Check foreign key constraints
php artisan tinker

# Test foreign key constraints
>>> try {
...     DB::table('users')->insert(['tenant_id' => 999, 'email' => 'test@test.com']);
... } catch (Exception $e) {
...     echo "Foreign key constraint working: " . $e->getMessage();
... }
```

#### **4.3: Index Performance Check**
```sql
-- Check index usage
EXPLAIN SELECT * FROM users WHERE tenant_id = 1;
EXPLAIN SELECT * FROM products WHERE tenant_id = 1 AND status = 'active';
EXPLAIN SELECT * FROM orders WHERE tenant_id = 1 AND created_at >= '2025-01-01';

-- Check index existence
SHOW INDEX FROM users WHERE Key_name LIKE '%tenant%';
SHOW INDEX FROM products WHERE Key_name LIKE '%tenant%';
SHOW INDEX FROM orders WHERE Key_name LIKE '%tenant%';
```

---

## 🔍 **VALIDATION COMMANDS**

### **Quick Validation Script**
```bash
# Create validation script
cat > validate_tenant_migration.php << 'EOF'
<?php
require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== TENANT MIGRATION VALIDATION ===\n";

// Check tenants table
$tenantCount = DB::table('tenants')->count();
echo "Tenants created: {$tenantCount}\n";

// Check tenant_id columns
$tables = ['users', 'products', 'orders', 'invoices', 'customers'];
foreach ($tables as $table) {
    $hasColumn = Schema::hasColumn($table, 'tenant_id');
    $nullCount = DB::table($table)->whereNull('tenant_id')->count();
    echo "{$table}: tenant_id column = " . ($hasColumn ? 'YES' : 'NO') . ", null values = {$nullCount}\n";
}

// Check tenant-user relationships
$tenantUserCount = DB::table('tenant_users')->count();
echo "Tenant-user relationships: {$tenantUserCount}\n";

echo "=== VALIDATION COMPLETE ===\n";
EOF

# Run validation
php validate_tenant_migration.php
```

---

## 🚨 **TROUBLESHOOTING**

### **Common Issues & Solutions**

#### **Issue 1: Foreign Key Constraint Errors**
```bash
# If foreign key errors occur
SET FOREIGN_KEY_CHECKS = 0;
# Run migration
SET FOREIGN_KEY_CHECKS = 1;
```

#### **Issue 2: Memory Limit Exceeded**
```bash
# Increase PHP memory limit
php -d memory_limit=2G artisan migrate --path=database/migrations/...
```

#### **Issue 3: Execution Time Limit**
```bash
# Increase execution time
php -d max_execution_time=300 artisan migrate --path=database/migrations/...
```

#### **Issue 4: Unique Constraint Violations**
```sql
-- Check for duplicate data before migration
SELECT email, COUNT(*) FROM users GROUP BY email HAVING COUNT(*) > 1;
SELECT sku, COUNT(*) FROM products GROUP BY sku HAVING COUNT(*) > 1;
```

---

## 🔄 **ROLLBACK PROCEDURES**

### **Emergency Rollback**
```bash
# Rollback all tenant migrations (in reverse order)
php artisan migrate:rollback --path=database/migrations/2025_08_10_120005_create_default_tenant_and_migrate_data.php
php artisan migrate:rollback --path=database/migrations/2025_08_10_120004_add_tenant_id_to_transaction_tables.php
php artisan migrate:rollback --path=database/migrations/2025_08_10_120003_add_tenant_id_to_business_tables.php
php artisan migrate:rollback --path=database/migrations/2025_08_10_120002_add_tenant_id_to_core_tables.php
php artisan migrate:rollback --path=database/migrations/2025_08_10_120001_create_tenant_relationship_tables.php
php artisan migrate:rollback --path=database/migrations/2025_08_10_120000_create_tenants_table.php
```

### **Database Restore (Last Resort)**
```bash
# Restore from backup
mysql -u username -p yukimart < backup_before_tenant_migration_YYYYMMDD_HHMMSS.sql
```

---

## 📊 **POST-MIGRATION CHECKLIST**

### **Functional Testing**
- [ ] **User Login**: Test user authentication
- [ ] **Data Access**: Verify data is accessible
- [ ] **CRUD Operations**: Test create, read, update, delete
- [ ] **Relationships**: Verify foreign key relationships work
- [ ] **Performance**: Check query performance with tenant_id

### **Data Validation**
- [ ] **Record Counts**: Verify all records migrated
- [ ] **Data Integrity**: Check for data corruption
- [ ] **Relationships**: Validate foreign key relationships
- [ ] **Unique Constraints**: Verify unique constraints work
- [ ] **Indexes**: Confirm indexes are created and used

### **System Health**
- [ ] **Application Startup**: Ensure application starts normally
- [ ] **Error Logs**: Check for any migration-related errors
- [ ] **Performance**: Monitor query performance
- [ ] **Memory Usage**: Check for memory leaks
- [ ] **Disk Space**: Verify adequate disk space

---

## 📞 **SUPPORT & ESCALATION**

### **If Issues Occur**
1. **Stop Migration**: Immediately stop if critical errors occur
2. **Document Issue**: Record exact error messages and steps
3. **Check Logs**: Review Laravel and database logs
4. **Rollback**: Use rollback procedures if necessary
5. **Escalate**: Contact senior developer or DBA

### **Emergency Contacts**
- **Technical Lead**: [Contact Information]
- **Database Administrator**: [Contact Information]
- **DevOps Team**: [Contact Information]

---

**Document Version**: 1.0  
**Last Updated**: 2025-08-10  
**Next Review**: After migration completion
