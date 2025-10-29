# 📊 PHASE 1: DATABASE SCHEMA DESIGN & MIGRATION

## 🎯 **OBJECTIVE**
Thiết kế và implement database schema cho tenant system với approach Single DB + Shared Schema

## ⏱️ **TIMELINE**
**Estimated Duration**: 1-2 tuần  
**Start Date**: 2025-08-10  
**Target Completion**: 2025-08-24  

---

## 📋 **STEP-BY-STEP IMPLEMENTATION**

### **Step 1.1: Analyze Current Database Schema** ⏱️ 1 ngày
**Objective**: Phân tích existing tables và xác định strategy

#### **Tasks:**
- [ ] List tất cả existing tables
- [ ] Identify tables cần tenant_id
- [ ] Analyze foreign key relationships  
- [ ] Document current indexes
- [ ] Plan migration order

#### **Deliverables:**
- Database schema analysis report
- Migration dependency map
- Risk assessment document

---

### **Step 1.2: Create Tenants Table** ⏱️ 1 ngày
**Objective**: Tạo core tenants table

#### **Migration File**: `create_tenants_table.php`
```sql
CREATE TABLE tenants (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    subdomain VARCHAR(100) UNIQUE,
    domain VARCHAR(255) UNIQUE,
    status ENUM('active', 'inactive', 'suspended', 'trial') DEFAULT 'active',
    plan_type ENUM('basic', 'premium', 'enterprise') DEFAULT 'basic',
    settings JSON,
    max_users INT DEFAULT 10,
    max_branch_shops INT DEFAULT 1,
    storage_limit BIGINT DEFAULT 1073741824, -- 1GB in bytes
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL
);
```

#### **Tasks:**
- [ ] Create migration file
- [ ] Add indexes for performance
- [ ] Add validation rules
- [ ] Test migration up/down

---

### **Step 1.3: Add tenant_id to Core Tables** ⏱️ 2-3 ngày
**Objective**: Thêm tenant_id column vào existing tables

#### **Priority Tables (Phase 1a):**
1. `users` - Core user management
2. `branch_shops` - Store locations  
3. `roles` - Role management
4. `permissions` - Permission system

#### **Migration File**: `add_tenant_id_to_core_tables.php`
```sql
-- Users table
ALTER TABLE users ADD COLUMN tenant_id BIGINT NOT NULL DEFAULT 1;
ALTER TABLE users ADD CONSTRAINT fk_users_tenant 
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE;

-- Branch shops table  
ALTER TABLE branch_shops ADD COLUMN tenant_id BIGINT NOT NULL DEFAULT 1;
ALTER TABLE branch_shops ADD CONSTRAINT fk_branch_shops_tenant
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE;

-- Roles table
ALTER TABLE roles ADD COLUMN tenant_id BIGINT NOT NULL DEFAULT 1;
ALTER TABLE roles ADD CONSTRAINT fk_roles_tenant
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE;

-- Permissions table  
ALTER TABLE permissions ADD COLUMN tenant_id BIGINT NOT NULL DEFAULT 1;
ALTER TABLE permissions ADD CONSTRAINT fk_permissions_tenant
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE;
```

#### **Tasks:**
- [ ] Create migration for core tables
- [ ] Test with existing data
- [ ] Verify foreign key constraints
- [ ] Document changes

---

### **Step 1.4: Add tenant_id to Business Tables** ⏱️ 3-4 ngày  
**Objective**: Thêm tenant_id vào business logic tables

#### **Business Tables (Phase 1b):**
1. `customers` - Customer management
2. `suppliers` - Supplier management
3. `categories` - Product categories
4. `products` - Product catalog
5. `product_variants` - Product variations
6. `inventories` - Inventory management
7. `inventory_transactions` - Stock movements

#### **Migration File**: `add_tenant_id_to_business_tables.php`
```sql
-- Customers
ALTER TABLE customers ADD COLUMN tenant_id BIGINT NOT NULL DEFAULT 1;
ALTER TABLE customers ADD CONSTRAINT fk_customers_tenant
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE;

-- Suppliers  
ALTER TABLE suppliers ADD COLUMN tenant_id BIGINT NOT NULL DEFAULT 1;
ALTER TABLE suppliers ADD CONSTRAINT fk_suppliers_tenant
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE;

-- Categories
ALTER TABLE categories ADD COLUMN tenant_id BIGINT NOT NULL DEFAULT 1;
ALTER TABLE categories ADD CONSTRAINT fk_categories_tenant
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE;

-- Products
ALTER TABLE products ADD COLUMN tenant_id BIGINT NOT NULL DEFAULT 1;
ALTER TABLE products ADD CONSTRAINT fk_products_tenant
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE;

-- Product variants
ALTER TABLE product_variants ADD COLUMN tenant_id BIGINT NOT NULL DEFAULT 1;
ALTER TABLE product_variants ADD CONSTRAINT fk_product_variants_tenant
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE;

-- Inventories
ALTER TABLE inventories ADD COLUMN tenant_id BIGINT NOT NULL DEFAULT 1;
ALTER TABLE inventories ADD CONSTRAINT fk_inventories_tenant
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE;

-- Inventory transactions
ALTER TABLE inventory_transactions ADD COLUMN tenant_id BIGINT NOT NULL DEFAULT 1;
ALTER TABLE inventory_transactions ADD CONSTRAINT fk_inventory_transactions_tenant
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE;
```

---

### **Step 1.5: Add tenant_id to Transaction Tables** ⏱️ 2-3 ngày
**Objective**: Thêm tenant_id vào financial và transaction tables

#### **Transaction Tables (Phase 1c):**
1. `orders` - Order management
2. `order_items` - Order line items
3. `invoices` - Invoice management  
4. `invoice_items` - Invoice line items
5. `return_orders` - Return orders
6. `return_order_items` - Return order items
7. `payments` - Payment transactions
8. `bank_accounts` - Bank account management

#### **Migration File**: `add_tenant_id_to_transaction_tables.php`

---

### **Step 1.6: Create Performance Indexes** ⏱️ 1 ngày
**Objective**: Tạo indexes để optimize tenant queries

#### **Migration File**: `create_tenant_performance_indexes.php`
```sql
-- Core indexes for tenant filtering
CREATE INDEX idx_users_tenant_id ON users(tenant_id);
CREATE INDEX idx_branch_shops_tenant_id ON branch_shops(tenant_id);
CREATE INDEX idx_products_tenant_id ON products(tenant_id);
CREATE INDEX idx_orders_tenant_id ON orders(tenant_id);
CREATE INDEX idx_invoices_tenant_id ON invoices(tenant_id);

-- Composite indexes for common queries
CREATE INDEX idx_users_tenant_status ON users(tenant_id, status);
CREATE INDEX idx_products_tenant_category ON products(tenant_id, category_id);
CREATE INDEX idx_orders_tenant_date ON orders(tenant_id, created_at);
CREATE INDEX idx_invoices_tenant_status ON invoices(tenant_id, status);

-- Unique constraints with tenant_id
CREATE UNIQUE INDEX idx_users_tenant_email ON users(tenant_id, email);
CREATE UNIQUE INDEX idx_branch_shops_tenant_code ON branch_shops(tenant_id, shop_code);
CREATE UNIQUE INDEX idx_products_tenant_sku ON products(tenant_id, sku);
```

#### **Tasks:**
- [ ] Analyze query patterns
- [ ] Create appropriate indexes
- [ ] Test query performance
- [ ] Document index strategy

---

### **Step 1.7: Create Tenant Relationship Tables** ⏱️ 1 ngày
**Objective**: Tạo tables cho tenant relationships

#### **Migration File**: `create_tenant_relationship_tables.php`
```sql
-- Tenant users relationship (many-to-many)
CREATE TABLE tenant_users (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    tenant_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    role ENUM('owner', 'admin', 'manager', 'staff') DEFAULT 'staff',
    permissions JSON,
    is_active BOOLEAN DEFAULT TRUE,
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_tenant_user (tenant_id, user_id)
);

-- Tenant settings
CREATE TABLE tenant_settings (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    tenant_id BIGINT NOT NULL,
    key VARCHAR(255) NOT NULL,
    value TEXT,
    type ENUM('string', 'integer', 'boolean', 'json') DEFAULT 'string',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    UNIQUE KEY unique_tenant_setting (tenant_id, key)
);
```

---

## ✅ **VALIDATION & TESTING**

### **Database Integrity Tests**
- [ ] Foreign key constraint validation
- [ ] Data type consistency check
- [ ] Index performance testing
- [ ] Migration rollback testing

### **Performance Tests**  
- [ ] Query performance với tenant_id filtering
- [ ] Index usage analysis
- [ ] Large dataset testing
- [ ] Concurrent access testing

---

## 📊 **PROGRESS TRACKING**

| Step | Status | Estimated Hours | Actual Hours | Notes |
|------|--------|----------------|--------------|-------|
| 1.1 | ⏳ Pending | 8h | - | Schema analysis |
| 1.2 | ⏳ Pending | 8h | - | Tenants table |
| 1.3 | ⏳ Pending | 24h | - | Core tables |
| 1.4 | ⏳ Pending | 32h | - | Business tables |
| 1.5 | ⏳ Pending | 24h | - | Transaction tables |
| 1.6 | ⏳ Pending | 8h | - | Performance indexes |
| 1.7 | ⏳ Pending | 8h | - | Relationship tables |

**Total Estimated**: 112 hours (14 working days)

---

## 🚨 **RISKS & MITIGATION**

| Risk | Mitigation Strategy |
|------|-------------------|
| Migration failure | Comprehensive backup + testing on staging |
| Performance degradation | Proper indexing + query optimization |
| Data integrity issues | Foreign key constraints + validation |
| Rollback complexity | Step-by-step migration approach |

---

## 📝 **NEXT STEPS**
After Phase 1 completion:
1. Proceed to Phase 2: Tenant Models & Relationships
2. Update application code để sử dụng tenant_id
3. Implement tenant-aware queries
4. Test data isolation

---

**Last Updated**: 2025-08-10  
**Phase Lead**: Development Team  
**Review Date**: TBD
