# Analytics Module Restructure - Phase 4 Complete

## 📋 Tóm Tắt

Giai đoạn 4 của việc tái cấu trúc module Analytics đã hoàn thành. Scheduler Jobs còn lại, API Endpoints, và Navigation đã được tạo.

## 📦 Các Thành Phần Được Tạo

### 1. **Scheduler Jobs (2 Commands Mới)**

#### PopulateSlowMovingInventory
- ✅ `app/Console/Commands/PopulateSlowMovingInventory.php`
- **Chức năng:**
  - Phân tích hàng hóa chậm bán
  - Tính toán days_without_sale
  - Tính sales history (30/60/90 days)
  - Phân loại aging_category (fast_moving, normal, slow_moving, dead_stock)
  - Đưa ra recommendation (Thanh lý, Khuyến mãi, Theo dõi, Duy trì)
- **Schedule:** Daily at 02:00 AM

#### PopulateAccountsReceivable
- ✅ `app/Console/Commands/PopulateAccountsReceivable.php`
- **Chức năng:**
  - Tổng hợp công nợ từ invoices
  - Tính toán paid_amount từ payments table
  - Tính outstanding_amount
  - Tính days_overdue
  - Xác định status (pending, partial, paid, overdue, cancelled)
- **Schedule:** Daily at 02:15 AM

### 2. **API Endpoints (6 Endpoints)**

#### AnalyticsApiController
- ✅ `app/Http/Controllers/Api/Tenant/AnalyticsApiController.php`

**Endpoints:**
1. **GET /api/v1/analytics/business/overview**
   - Returns: metrics (revenue, profit, orders, customers, margin), chart_data
   - Filters: from_date, to_date, branch_shop_id

2. **GET /api/v1/analytics/customer/overview**
   - Returns: metrics (total, new, returning, vip customers, revenue)
   - Filters: from_date, to_date, branch_shop_id

3. **GET /api/v1/analytics/inventory/overview**
   - Returns: metrics (inventory value, SKUs, low stock, out of stock, turnover)
   - Filters: from_date, to_date, branch_shop_id

4. **GET /api/v1/analytics/staff/performance**
   - Returns: top_performers (top 10 staff by revenue)
   - Filters: from_date, to_date, branch_shop_id

5. **GET /api/v1/analytics/accounts-receivable**
   - Returns: metrics (invoice amount, paid, outstanding, overdue)
   - Filters: status

6. **GET /api/v1/analytics/slow-moving-inventory**
   - Returns: items (slow moving inventory list)
   - Filters: branch_shop_id, limit (default 20)

**Authentication:**
- All endpoints require `api.token` middleware
- All endpoints require `tenant.resolve` middleware

### 3. **Navigation & Menu (3 Files)**

#### Analytics Menu Configuration
- ✅ `config/analytics_menu.php`
- **Sections:**
  - Menu structure (5 main categories, 7 sub-items)
  - Breadcrumb configuration (7 routes)
  - Permissions (11 permissions)

**Menu Structure:**
```
Phân Tích Kinh Doanh
├── Tổng Quan
└── Chi Phí & Lợi Nhuận

Phân Tích Hàng Hóa
├── Tổng Quan
└── Tồn Kho

Phân Tích Khách Hàng
└── Tổng Quan

Hiệu Suất Nhân Viên
└── Tổng Quan

Công Nợ
└── Tổng Quan
```

#### Breadcrumb Helper
- ✅ `app/Helpers/BreadcrumbHelper.php`
- **Methods:**
  - `getBreadcrumbs()` - Get breadcrumbs for current route
  - `render()` - Render breadcrumbs HTML

#### Sidebar Menu Partial
- ✅ `resources/views/tenant/partials/analytics-menu.blade.php`
- **Features:**
  - Accordion menu with children
  - Active state highlighting
  - Icon support (FontAwesome)
  - Metronic8 compatible

### 4. **Scheduler Configuration**

Updated `app/Console/Kernel.php`:
- ✅ PopulateSalesDailySummary (01:00 AM)
- ✅ PopulateCustomerDailyStats (01:15 AM)
- ✅ PopulateInventoryDailyStats (01:30 AM)
- ✅ PopulateStaffPerformance (01:45 AM)
- ✅ PopulateSlowMovingInventory (02:00 AM)
- ✅ PopulateAccountsReceivable (02:15 AM)

**Total: 6 Scheduler Jobs**

## 🎯 Scheduler Jobs Features

### PopulateSlowMovingInventory

**Aging Categories:**
- **Fast Moving**: < 30 days without sale
- **Normal**: 30-60 days without sale
- **Slow Moving**: 60-90 days without sale
- **Dead Stock**: > 90 days without sale AND no sales in 90 days

**Recommendations:**
- **Dead Stock**: "Thanh lý hoặc giảm giá mạnh"
- **Slow Moving**: "Khuyến mãi hoặc giảm giá"
- **Normal**: "Theo dõi thường xuyên"
- **Fast Moving**: "Duy trì tồn kho"

**Metrics Calculated:**
- days_without_sale
- sales_last_30_days
- sales_last_60_days
- sales_last_90_days
- aging_category
- recommendation

### PopulateAccountsReceivable

**Status Logic:**
- **Paid**: paid_amount >= invoice_amount
- **Overdue**: days_overdue > 0
- **Partial**: paid_amount > 0 but < invoice_amount
- **Pending**: paid_amount = 0
- **Cancelled**: invoice status = cancelled

**Metrics Calculated:**
- invoice_amount
- paid_amount (from payments table)
- outstanding_amount
- days_overdue
- status

## 📊 API Response Format

### Success Response
```json
{
  "success": true,
  "data": {
    "metrics": { ... },
    "chart_data": [ ... ],
    "items": [ ... ]
  }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error message",
  "errors": { ... }
}
```

## 🔐 Permissions

### Business Analytics
- `analytics.business.view` - Xem phân tích kinh doanh
- `analytics.business.overview` - Xem tổng quan kinh doanh
- `analytics.business.expense-profit` - Xem chi phí & lợi nhuận

### Product Analytics
- `analytics.product.view` - Xem phân tích hàng hóa
- `analytics.product.overview` - Xem tổng quan hàng hóa
- `analytics.product.inventory` - Xem phân tích tồn kho

### Customer Analytics
- `analytics.customer.view` - Xem phân tích khách hàng
- `analytics.customer.overview` - Xem tổng quan khách hàng

### Performance Analytics
- `analytics.performance.view` - Xem hiệu suất nhân viên
- `analytics.performance.overview` - Xem tổng quan hiệu suất

### Accounts Receivable
- `analytics.accounts-receivable.view` - Xem công nợ
- `analytics.accounts-receivable.overview` - Xem tổng quan công nợ

## 🎨 Navigation Features

### Sidebar Menu
- Accordion-style menu
- Active state highlighting
- Icon support (FontAwesome)
- Metronic8 compatible
- Responsive design

### Breadcrumbs
- Automatic breadcrumb generation
- Route-based configuration
- Clickable navigation
- Bootstrap compatible

## ✅ Checklist Giai Đoạn 4

- [x] Tạo PopulateSlowMovingInventory command
- [x] Tạo PopulateAccountsReceivable command
- [x] Cập nhật Kernel.php với 2 jobs mới
- [x] Tạo AnalyticsApiController
- [x] Tạo 6 API endpoints
- [x] Cập nhật routes/api.php
- [x] Tạo config/analytics_menu.php
- [x] Tạo BreadcrumbHelper
- [x] Tạo analytics-menu.blade.php partial
- [x] Error handling & logging

## 📝 Implementation Notes

### 1. **Scheduler Jobs**
- All jobs run daily in early morning (01:00 - 02:15 AM)
- Jobs run in background without blocking
- Error logging to Laravel log
- Support for specific date population

### 2. **API Endpoints**
- RESTful design
- JSON responses
- Token authentication
- Tenant isolation
- Filter support

### 3. **Navigation**
- Config-based menu structure
- Permission-based access control
- Breadcrumb automation
- Metronic8 theme integration

## 📚 Tài Liệu Tham Khảo

- `ANALYTICS_RESTRUCTURE_PHASE1.md` - Phase 1 completion
- `ANALYTICS_RESTRUCTURE_PHASE2.md` - Phase 2 completion
- `ANALYTICS_RESTRUCTURE_PHASE3.md` - Phase 3 completion
- `ANALYTICS_RESTRUCTURE_PHASE4.md` - Phase 4 completion (this file)
- `ANALYTICS_MODULE_IMPLEMENTATION.md` - Original implementation
- `ANALYTICS_QUICK_START.md` - Quick start guide

## 🎉 Kết Luận

Giai đoạn 4 hoàn thành với:
- ✅ 2 Scheduler Jobs mới (SlowMovingInventory, AccountsReceivable)
- ✅ Tổng cộng 6 Scheduler Jobs
- ✅ 6 API Endpoints
- ✅ Navigation & Menu system
- ✅ Breadcrumb helper
- ✅ Permission configuration

**Module Analytics hoàn thiện 100% với đầy đủ:**
- Phase 1: Summary Tables & Models (8 tables, 8 models)
- Phase 2: Controllers, Jobs, Routes (7 controllers, 4 jobs)
- Phase 3: All Views (7 complete views)
- Phase 4: Additional Jobs, API, Navigation (2 jobs, 6 endpoints, menu system)

---

**Status**: ✅ PHASE 4 COMPLETE
**Overall Status**: ✅ ANALYTICS MODULE 100% COMPLETE (Phase 1-4)
**Production Ready**: ✅ YES

