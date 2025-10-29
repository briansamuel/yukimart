# 🧪 TENANT MIGRATION TESTING GUIDE

## 📋 **TESTING OVERVIEW**

Hướng dẫn này cung cấp các phương pháp testing toàn diện cho tenant migration system, từ validation cơ bản đến testing production-ready.

---

## 🔍 **TESTING LEVELS**

### **Level 1: Static Validation** ⚡ (2-3 phút)
Kiểm tra migration files mà không thực thi

### **Level 2: Unit Testing** 🧪 (5-10 phút)  
Test từng migration file riêng lẻ

### **Level 3: Integration Testing** 🔗 (10-15 phút)
Test toàn bộ migration flow

### **Level 4: Production Simulation** 🚀 (20-30 phút)
Test với data thực và performance

---

## ⚡ **LEVEL 1: STATIC VALIDATION**

### **Quick File Validation**
```bash
# Validate migration files exist and have correct structure
php scripts/validate_tenant_migrations.php
```

### **Expected Output:**
```
🔍 TENANT MIGRATION VALIDATION
==============================

📁 Checking migration files...
  ✅ 2025_08_10_120000_create_tenants_table.php
  ✅ 2025_08_10_120001_create_tenant_relationship_tables.php
  ✅ 2025_08_10_120002_add_tenant_id_to_core_tables.php
  ✅ 2025_08_10_120003_add_tenant_id_to_business_tables.php
  ✅ 2025_08_10_120004_add_tenant_id_to_transaction_tables.php
  ✅ 2025_08_10_120005_create_default_tenant_and_migrate_data.php

📝 Validating migration content...
  ✅ Tenants table creation found
  ✅ Indexes found in tenants table
  ✅ tenant_users table creation found
  ✅ tenant_settings table creation found
  ✅ tenant_invitations table creation found
  ✅ tenant_activity_logs table creation found

🎉 ALL VALIDATIONS PASSED!
✅ Migration files are ready for execution
```

### **Manual File Check**
```bash
# Check if all migration files exist
ls -la database/migrations/2025_08_10_12000*

# Check file sizes (should not be empty)
wc -l database/migrations/2025_08_10_12000*
```

---

## 🧪 **LEVEL 2: UNIT TESTING**

### **Run PHPUnit Tests**
```bash
# Run all tenant migration tests
php artisan test --filter TenantMigrationTest

# Run specific test methods
php artisan test --filter TenantMigrationTest::test_tenants_table_creation
php artisan test --filter TenantMigrationTest::test_default_tenant_creation_and_data_migration
```

### **Individual Migration Testing**
```bash
# Test each migration individually
php artisan tenant:test-migrations --dry-run
php artisan tenant:test-migrations --verbose
```

### **Expected Test Results:**
```
PASS  Tests\Feature\TenantMigrationTest
✓ test tenants table creation
✓ test tenant relationship tables creation  
✓ test core tables tenant id addition
✓ test business tables tenant id addition
✓ test transaction tables tenant id addition
✓ test default tenant creation and data migration
✓ test migration rollback

Tests:  7 passed
Time:   15.23s
```

---

## 🔗 **LEVEL 3: INTEGRATION TESTING**

### **Full Migration Flow Test**
```bash
# Create test database
mysql -u root -p -e "CREATE DATABASE yukimart_test;"

# Set test environment
cp .env .env.backup
cp .env.testing .env

# Run full migration test
php artisan migrate:fresh
php artisan tenant:test-migrations

# Test rollback
php artisan tenant:test-migrations --rollback
```

### **Database State Validation**
```bash
# Check database state after migration
php artisan tinker

# Validate tenant table
>>> DB::table('tenants')->count()
=> 1

>>> $tenant = DB::table('tenants')->first()
>>> $tenant->name
=> "Default Company"

# Validate tenant_id columns
>>> Schema::hasColumn('users', 'tenant_id')
=> true

>>> Schema::hasColumn('products', 'tenant_id')  
=> true

>>> Schema::hasColumn('orders', 'tenant_id')
=> true

# Check data migration
>>> DB::table('users')->whereNull('tenant_id')->count()
=> 0

>>> DB::table('tenant_users')->count()
=> [number of users]
```

### **Foreign Key Constraint Testing**
```bash
php artisan tinker

# Test foreign key constraints
>>> try {
...     DB::table('users')->insert([
...         'tenant_id' => 999999,
...         'email' => 'test@invalid.com',
...         'username' => 'invalid',
...         'password' => 'test',
...         'full_name' => 'Test',
...         'address' => 'Test',
...         'phone' => '123',
...         'birth_date' => now(),
...         'active_code' => 'test',
...         'group_id' => '1'
...     ]);
... } catch (Exception $e) {
...     echo "Foreign key working: " . $e->getMessage();
... }
```

---

## 🚀 **LEVEL 4: PRODUCTION SIMULATION**

### **Performance Testing**
```bash
# Create large dataset for testing
php artisan db:seed --class=TestDataSeeder

# Test migration performance
time php artisan migrate --path=database/migrations/2025_08_10_120005_create_default_tenant_and_migrate_data.php

# Check query performance
php artisan tinker

>>> DB::enableQueryLog();
>>> DB::table('users')->where('tenant_id', 1)->get();
>>> DB::getQueryLog();
```

### **Memory Usage Testing**
```bash
# Monitor memory usage during migration
php -d memory_limit=512M artisan migrate --path=database/migrations/2025_08_10_120005_create_default_tenant_and_migrate_data.php
```

### **Concurrent Access Testing**
```bash
# Test multiple connections
php artisan tinker

# Terminal 1
>>> DB::table('users')->where('tenant_id', 1)->count()

# Terminal 2  
>>> DB::table('products')->where('tenant_id', 1)->count()

# Terminal 3
>>> DB::table('orders')->where('tenant_id', 1)->count()
```

---

## 🔧 **TROUBLESHOOTING TESTS**

### **Common Test Issues**

#### **Issue 1: Migration File Not Found**
```bash
# Check file permissions
ls -la database/migrations/2025_08_10_12000*

# Fix permissions if needed
chmod 644 database/migrations/2025_08_10_12000*
```

#### **Issue 2: Foreign Key Constraint Errors**
```bash
# Check if tenants table exists first
php artisan tinker
>>> Schema::hasTable('tenants')

# Run migrations in correct order
php artisan migrate --path=database/migrations/2025_08_10_120000_create_tenants_table.php
```

#### **Issue 3: Memory Limit Exceeded**
```bash
# Increase memory limit
php -d memory_limit=1G artisan tenant:test-migrations
```

#### **Issue 4: Test Database Issues**
```bash
# Reset test database
php artisan migrate:fresh --env=testing
php artisan db:seed --env=testing
```

---

## ✅ **TESTING CHECKLIST**

### **Pre-Testing Checklist**
- [ ] **Backup database** before testing
- [ ] **Set test environment** variables
- [ ] **Check file permissions** on migration files
- [ ] **Verify database connection** is working
- [ ] **Ensure adequate disk space** for testing

### **Testing Execution Checklist**
- [ ] **Level 1**: Static validation passed
- [ ] **Level 2**: Unit tests passed (7/7)
- [ ] **Level 3**: Integration tests passed
- [ ] **Level 4**: Performance tests acceptable
- [ ] **Rollback tests**: All rollbacks successful

### **Post-Testing Checklist**
- [ ] **Database state** validated
- [ ] **Foreign key constraints** working
- [ ] **Indexes** created and functional
- [ ] **Data integrity** confirmed
- [ ] **Performance** meets requirements
- [ ] **Documentation** updated with test results

---

## 📊 **TEST RESULTS TEMPLATE**

### **Test Execution Report**
```
TENANT MIGRATION TEST RESULTS
=============================
Date: 2025-08-10
Environment: Testing
Database: MySQL 8.0

LEVEL 1 - Static Validation: ✅ PASSED
- Migration files: 6/6 found
- Content validation: ✅ PASSED
- Structure validation: ✅ PASSED

LEVEL 2 - Unit Testing: ✅ PASSED  
- PHPUnit tests: 7/7 passed
- Individual migrations: 6/6 passed
- Rollback tests: 6/6 passed

LEVEL 3 - Integration Testing: ✅ PASSED
- Full migration flow: ✅ PASSED
- Database state: ✅ VALIDATED
- Foreign keys: ✅ WORKING
- Data migration: ✅ COMPLETE

LEVEL 4 - Production Simulation: ✅ PASSED
- Performance: Acceptable (< 30s)
- Memory usage: Normal (< 512MB)
- Concurrent access: ✅ WORKING

OVERALL STATUS: ✅ READY FOR PRODUCTION
```

---

## 🚀 **NEXT STEPS AFTER TESTING**

### **If All Tests Pass:**
1. **Execute migrations** on staging environment
2. **Validate production readiness**
3. **Schedule production migration**
4. **Proceed to Phase 2** development

### **If Tests Fail:**
1. **Review error messages** carefully
2. **Fix identified issues** in migration files
3. **Re-run tests** until all pass
4. **Document fixes** and lessons learned

---

## 📞 **SUPPORT & ESCALATION**

### **Test Failure Escalation:**
1. **Document exact error** messages and steps
2. **Check troubleshooting** section above
3. **Review migration files** for syntax errors
4. **Contact technical lead** if issues persist

### **Performance Issues:**
1. **Check database server** resources
2. **Review query execution** plans
3. **Consider index optimization**
4. **Escalate to DBA** if needed

---

**Testing Guide Version**: 1.0  
**Last Updated**: 2025-08-10  
**Next Review**: After production deployment
