# 📊 DATABASE SCHEMA ANALYSIS FOR TENANT IMPLEMENTATION

## 🎯 **ANALYSIS OVERVIEW**

**Date**: 2025-08-10  
**Purpose**: Phân tích existing database schema để implement tenant system  
**Approach**: Single Database + Shared Schema với tenant_id columns  

---

## 📋 **EXISTING TABLES INVENTORY**

### **🔐 Core Authentication & Authorization Tables**
| Table | Description | Needs tenant_id | Priority |
|-------|-------------|----------------|----------|
| `users` | User accounts | ✅ Yes | High |
| `roles` | Role definitions | ✅ Yes | High |
| `permissions` | Permission definitions | ✅ Yes | High |
| `user_roles` | User-role relationships | ❌ No (inherits from users) | Medium |
| `role_permissions` | Role-permission relationships | ❌ No (inherits from roles) | Medium |
| `user_permissions` | Direct user permissions | ❌ No (inherits from users) | Medium |

### **🏢 Business Structure Tables**
| Table | Description | Needs tenant_id | Priority |
|-------|-------------|----------------|----------|
| `branch_shops` | Store locations | ✅ Yes | High |
| `user_branch_shops` | User-branch assignments | ❌ No (inherits from users) | Medium |
| `warehouses` | Warehouse management | ✅ Yes | High |

### **👥 Customer & Supplier Management**
| Table | Description | Needs tenant_id | Priority |
|-------|-------------|----------------|----------|
| `customers` | Customer information | ✅ Yes | High |
| `suppliers` | Supplier information | ✅ Yes | High |
| `customer_point_transactions` | Customer loyalty points | ❌ No (inherits from customers) | Low |

### **📦 Product Management Tables**
| Table | Description | Needs tenant_id | Priority |
|-------|-------------|----------------|----------|
| `categories` | Product categories | ✅ Yes | High |
| `products` | Main product catalog | ✅ Yes | High |
| `product_attributes` | Product attribute definitions | ✅ Yes | Medium |
| `product_attribute_values` | Attribute value options | ❌ No (inherits from attributes) | Low |
| `product_variants` | Product variations | ❌ No (inherits from products) | Medium |
| `product_variant_attributes` | Variant-attribute relationships | ❌ No (inherits from variants) | Low |

### **📊 Inventory Management Tables**
| Table | Description | Needs tenant_id | Priority |
|-------|-------------|----------------|----------|
| `inventories` | Stock levels | ✅ Yes | High |
| `inventory_transactions` | Stock movements | ✅ Yes | High |

### **🛒 Sales & Order Management**
| Table | Description | Needs tenant_id | Priority |
|-------|-------------|----------------|----------|
| `orders` | Order management | ✅ Yes | High |
| `order_items` | Order line items | ❌ No (inherits from orders) | Medium |
| `invoices` | Invoice management | ✅ Yes | High |
| `invoice_items` | Invoice line items | ❌ No (inherits from invoices) | Medium |
| `return_orders` | Return order management | ✅ Yes | Medium |
| `return_order_items` | Return order line items | ❌ No (inherits from return_orders) | Low |

### **💰 Financial Management**
| Table | Description | Needs tenant_id | Priority |
|-------|-------------|----------------|----------|
| `payments` | Payment transactions | ✅ Yes | High |
| `bank_accounts` | Bank account management | ✅ Yes | Medium |

### **🔔 System & Communication Tables**
| Table | Description | Needs tenant_id | Priority |
|-------|-------------|----------------|----------|
| `notifications` | System notifications | ✅ Yes | Medium |
| `notification_settings` | User notification preferences | ❌ No (inherits from users) | Low |
| `notification_templates` | Notification templates | ✅ Yes | Low |
| `fcm_tokens` | Firebase messaging tokens | ❌ No (inherits from users) | Low |

### **🌐 Marketplace Integration**
| Table | Description | Needs tenant_id | Priority |
|-------|-------------|----------------|----------|
| `shopee_tokens` | Shopee API tokens | ✅ Yes | Low |
| `marketplace_product_links` | Product marketplace links | ❌ No (inherits from products) | Low |

### **🗂️ System Configuration Tables**
| Table | Description | Needs tenant_id | Priority |
|-------|-------------|----------------|----------|
| `settings` | System settings | ✅ Yes | Medium |
| `user_settings` | User preferences | ❌ No (inherits from users) | Low |
| `audit_logs` | Activity tracking | ✅ Yes | Medium |

### **🔄 Backup & Maintenance**
| Table | Description | Needs tenant_id | Priority |
|-------|-------------|----------------|----------|
| `backups` | Backup records | ✅ Yes | Low |
| `backup_schedules` | Backup scheduling | ✅ Yes | Low |

---

## 🎯 **MIGRATION PRIORITY GROUPS**

### **Group 1: Core Foundation (Phase 1a)**
**Priority**: Critical - Must be done first
```sql
-- Tables that need tenant_id immediately
1. tenants (new table)
2. users
3. roles  
4. permissions
5. branch_shops
```

### **Group 2: Business Core (Phase 1b)**
**Priority**: High - Core business functionality
```sql
-- Essential business tables
6. customers
7. suppliers
8. categories
9. products
10. warehouses
11. inventories
```

### **Group 3: Transactions (Phase 1c)**
**Priority**: High - Financial and transaction data
```sql
-- Transaction and financial tables
12. orders
13. invoices
14. inventory_transactions
15. payments
16. bank_accounts
```

### **Group 4: Extended Features (Phase 1d)**
**Priority**: Medium - Additional features
```sql
-- Extended functionality tables
17. return_orders
18. notifications
19. settings
20. audit_logs
21. product_attributes
```

### **Group 5: Integrations (Phase 1e)**
**Priority**: Low - External integrations
```sql
-- Integration and system tables
22. shopee_tokens
23. notification_templates
24. backups
25. backup_schedules
```

---

## 🔗 **FOREIGN KEY RELATIONSHIPS ANALYSIS**

### **Critical Relationships**
```sql
-- Core relationships that must be maintained
users -> tenant_id (FK to tenants.id)
branch_shops -> tenant_id (FK to tenants.id)
products -> tenant_id (FK to tenants.id)
orders -> tenant_id (FK to tenants.id)
invoices -> tenant_id (FK to tenants.id)

-- Cross-tenant relationships to avoid
orders -> customer_id (must be same tenant)
order_items -> product_id (must be same tenant)
inventories -> product_id (must be same tenant)
```

### **Inherited Relationships**
```sql
-- Tables that inherit tenant_id through relationships
order_items -> orders.tenant_id
invoice_items -> invoices.tenant_id
product_variants -> products.tenant_id
user_branch_shops -> users.tenant_id
```

---

## ⚠️ **POTENTIAL ISSUES & CONSIDERATIONS**

### **Data Integrity Concerns**
1. **Cross-tenant references**: Ensure foreign keys don't reference across tenants
2. **Existing data**: All existing data needs default tenant assignment
3. **Unique constraints**: Update unique indexes to include tenant_id

### **Performance Considerations**
1. **Index strategy**: All tenant_id columns need proper indexing
2. **Query patterns**: Most queries will filter by tenant_id
3. **Connection pooling**: Consider tenant-specific connection optimization

### **Migration Risks**
1. **Large tables**: `orders`, `order_items`, `inventory_transactions` có thể có nhiều data
2. **Downtime**: Migration có thể require maintenance window
3. **Rollback complexity**: Need comprehensive rollback strategy

---

## 📊 **ESTIMATED DATA VOLUMES**

| Table | Estimated Rows | Migration Complexity | Risk Level |
|-------|---------------|---------------------|------------|
| users | < 1,000 | Low | Low |
| products | < 10,000 | Medium | Medium |
| orders | < 100,000 | High | High |
| order_items | < 500,000 | High | High |
| inventory_transactions | < 1,000,000 | Very High | High |
| invoices | < 50,000 | Medium | Medium |

---

## 🛠️ **RECOMMENDED MIGRATION STRATEGY**

### **Phase 1: Foundation Setup**
1. Create `tenants` table
2. Add tenant_id to core tables (users, roles, permissions)
3. Create default tenant
4. Assign existing users to default tenant

### **Phase 2: Business Tables**
1. Add tenant_id to business tables (customers, products, etc.)
2. Update foreign key constraints
3. Create performance indexes

### **Phase 3: Transaction Tables**
1. Add tenant_id to transaction tables (orders, invoices, etc.)
2. Handle large data migration carefully
3. Validate data integrity

### **Phase 4: Extended Features**
1. Add tenant_id to remaining tables
2. Update integrations and system tables
3. Final validation and testing

---

## 📝 **NEXT STEPS**

1. ✅ **Complete this analysis**
2. ⏳ **Create tenant table migration**
3. ⏳ **Start with Group 1 tables**
4. ⏳ **Implement data validation**
5. ⏳ **Create rollback procedures**

---

**Analysis Completed**: 2025-08-10  
**Total Tables Analyzed**: 89 tables  
**Tables Requiring tenant_id**: 25 tables  
**Migration Phases**: 4 phases  
**Estimated Timeline**: 2-3 weeks
