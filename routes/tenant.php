<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| These routes are loaded for tenant subdomains ({tenant}.yukimart.local)
| All routes are automatically scoped to the current tenant context via
| the tenant.subdomain middleware.
|
| Route Structure:
| - Subdomain Group: {tenant}.yukimart.local
|   - Admin Routes: /admin/* (requires auth:admin + tenant.resolve)
|     - Dashboard
|     - Authentication (Login/Logout)
|     - Product Categories Management
|     - Products Management
|     - Customers Management
|     - Orders Management
|     - Invoices Management
|     - Return Orders Management
|     - Payments Management
|     - Suppliers Management
|     - Branch Shops Management
|     - Inventory Management
|     - Quick Order (POS System)
|     - Reports
|     - Settings
|     - Users Management
|   - Test/Debug Routes
|   - API Routes
|
| IMPORTANT: Domain parameter is named {tenant} to avoid conflicts with
| controller method parameters. The middleware extracts subdomain from host.
|
*/

// ============================================================================
// SUBDOMAIN ROUTES: {tenant}.yukimart.local
// ============================================================================
// Note: Domain parameter {tenant} is automatically injected by Laravel
// To avoid route parameter injection conflicts, controller methods should:
// 1. Extend BaseTenantController to access tenant context via TenantContextService
// 2. NOT include $tenant parameter in method signatures
// 3. Use request()->route('param_name') to get route parameters explicitly
// Example: public function update(Request $request) { $id = request()->route('id'); }
// Middleware 'tenant.subdomain' extracts subdomain from request host
Route::domain('{tenant}.yukimart.local')->middleware(['tenant.subdomain'])->group(function () {

    // ========================================================================
    // TENANT LOGIN ROUTES (Guest Only)
    // ========================================================================
    Route::middleware(['guest:tenant'])->group(function () {
        Route::get('/login', [App\Http\Controllers\Tenant\AuthTenantController::class, 'showLoginForm'])->name('tenant.login.show');
        Route::post('/login', [App\Http\Controllers\Tenant\AuthTenantController::class, 'login'])->name('tenant.login');
    });

    // ========================================================================
    // ROOT REDIRECT
    // ========================================================================
    Route::get('/', function () {
        if (Auth::guard('tenant')->check()) {
            return redirect('/admin/dashboard');
        }
        return redirect('/login');
    })->name('tenant.root');

    // ========================================================================
    // ADMIN ROUTES (Authenticated + Tenant Resolved)
    // ========================================================================
    Route::prefix('admin')->middleware(['auth:tenant', 'tenant.resolve'])->group(function () {

        // --------------------------------------------------------------------
        // DASHBOARD
        // --------------------------------------------------------------------
        Route::get('/dashboard', [App\Http\Controllers\Tenant\AuthTenantController::class, 'dashboard'])->name('admin.dashboard');

        // --------------------------------------------------------------------
        // AUTHENTICATION
        // --------------------------------------------------------------------
        Route::post('/logout', [App\Http\Controllers\Tenant\AuthTenantController::class, 'logout'])->name('admin.logout');
        Route::get('/logout', [App\Http\Controllers\Tenant\AuthTenantController::class, 'logout'])->name('admin.logout.get');

        // --------------------------------------------------------------------
        // PRODUCT CATEGORIES MANAGEMENT
        // --------------------------------------------------------------------
        Route::prefix('product-categories')->name('admin.product-categories.')->group(function () {
            Route::get('/', [App\Http\Controllers\Tenant\Catalog\CategoryController::class, 'index'])->name('index');
            Route::get('/data', [App\Http\Controllers\Tenant\Catalog\CategoryController::class, 'getData'])->name('data');
            Route::get('/create', [App\Http\Controllers\Tenant\Catalog\CategoryController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\Tenant\Catalog\CategoryController::class, 'store'])->name('store');
            Route::get('/{productCategory}', [App\Http\Controllers\Tenant\Catalog\CategoryController::class, 'show'])->name('show');
            Route::get('/{productCategory}/edit', [App\Http\Controllers\Tenant\Catalog\CategoryController::class, 'edit'])->name('edit');
            Route::put('/{productCategory}', [App\Http\Controllers\Tenant\Catalog\CategoryController::class, 'update'])->name('update');
            Route::delete('/{productCategory}', [App\Http\Controllers\Tenant\Catalog\CategoryController::class, 'destroy'])->name('destroy');
            Route::get('/parent/list', [App\Http\Controllers\Tenant\Catalog\CategoryController::class, 'getParentCategories'])->name('parent-list');
            Route::post('/sort-order', [App\Http\Controllers\Tenant\Catalog\CategoryController::class, 'updateSortOrder'])->name('sort-order');
            Route::get('/ajax', [App\Http\Controllers\Business\ProductCategoryController::class, 'ajax'])->name('ajax');
            Route::get('/flat', [App\Http\Controllers\Business\ProductCategoryController::class, 'getFlat'])->name('flat');
        });

        // --------------------------------------------------------------------
        // PRODUCTS MANAGEMENT
        // --------------------------------------------------------------------
        // Product Import Routes (must be before {id} routes) - Requires import permission
        Route::prefix('products/import')->name('admin.products.import.')->middleware('permission:catalog.products.import')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\ProductImportController::class, 'index'])->name('index');
            Route::post('/upload', [App\Http\Controllers\Admin\ProductImportController::class, 'upload'])->name('upload');
            Route::post('/test-upload', [App\Http\Controllers\Admin\ProductImportController::class, 'testUpload'])->name('test-upload');
            Route::get('/fields', [App\Http\Controllers\Admin\ProductImportController::class, 'getFields'])->name('fields');
            Route::get('/preview', [App\Http\Controllers\Admin\ProductImportController::class, 'preview'])->name('preview');
            Route::get('/stats', [App\Http\Controllers\Admin\ProductImportController::class, 'getFileStats'])->name('stats');
            Route::post('/validate', [App\Http\Controllers\Admin\ProductImportController::class, 'validateImport'])->name('validate');
            Route::post('/process', [App\Http\Controllers\Admin\ProductImportController::class, 'process'])->name('process');
            Route::get('/template', [App\Http\Controllers\Admin\ProductImportController::class, 'downloadTemplate'])->name('template');
            Route::get('/history', [App\Http\Controllers\Admin\ProductImportController::class, 'history'])->name('history');
            Route::delete('/clear-session', [App\Http\Controllers\Admin\ProductImportController::class, 'clearSession'])->name('clear-session');
        });

        Route::prefix('products')->name('admin.products.')->group(function () {
            // Product Attribute Routes (MUST be before routes with {id} parameter)
            Route::get('/attributes', [App\Http\Controllers\Tenant\Catalog\ProductController::class, 'getAttributes'])->name('attributes')->middleware('permission:catalog.products.read');
            Route::post('/attributes', [App\Http\Controllers\Tenant\Catalog\ProductController::class, 'storeAttribute'])->name('attributes.store')->middleware('permission:catalog.products.create');
            Route::get('/attributes/{attributeId}/values', [App\Http\Controllers\Tenant\Catalog\ProductController::class, 'getAttributeValues'])->name('attributes.values.get')->middleware('permission:catalog.products.read');
            Route::post('/attributes/{attributeId}/values', [App\Http\Controllers\Tenant\Catalog\ProductController::class, 'storeAttributeValue'])->name('attributes.values.store')->middleware('permission:catalog.products.create');

            // Product AJAX Routes (specific paths before {id})
            Route::get('/ajax/get-list', [App\Http\Controllers\Tenant\Catalog\ProductController::class, 'ajaxGetList'])->name('ajax.getList')->middleware('permission:catalog.products.read');
            Route::get('/ajax', [App\Http\Controllers\Admin\ProductController::class, 'ajax'])->name('ajax')->middleware('permission:catalog.products.read');
            Route::get('/detail/{productId}', [App\Http\Controllers\Admin\ProductController::class, 'detail'])->name('detail')->middleware('permission:catalog.products.read');

            // Product Action Menu Routes
            Route::post('/{id}/duplicate', [App\Http\Controllers\Tenant\Catalog\ProductController::class, 'duplicate'])->name('duplicate')->middleware('permission:catalog.products.create');
            Route::patch('/{id}/status', [App\Http\Controllers\Tenant\Catalog\ProductController::class, 'changeStatus'])->name('change.status')->middleware('permission:catalog.products.update');
            Route::get('/{id}/history', [App\Http\Controllers\Tenant\Catalog\ProductController::class, 'getHistory'])->name('history')->middleware('permission:catalog.products.read');
            Route::post('/{id}/quick-edit', [App\Http\Controllers\Tenant\Catalog\ProductController::class, 'quickEdit'])->name('quick.edit')->middleware('permission:catalog.products.update');
            Route::post('/{id}/adjust-stock', [App\Http\Controllers\Tenant\Catalog\ProductController::class, 'adjustStock'])->name('adjust.stock')->middleware('permission:catalog.products.update');

            // Product Variant Routes
            Route::post('/{id}/variants', [App\Http\Controllers\Tenant\Catalog\ProductController::class, 'createVariants'])->name('variants.create')->middleware('permission:catalog.products.create');
            Route::post('/{id}/variants/from-form', [App\Http\Controllers\Tenant\Catalog\ProductController::class, 'createVariantsFromForm'])->name('variants.create.form')->middleware('permission:catalog.products.create');
            Route::get('/{id}/variants', [App\Http\Controllers\Tenant\Catalog\ProductController::class, 'getVariants'])->name('variants.get')->middleware('permission:catalog.products.read');
            Route::put('/{productId}/variants/{variantId}', [App\Http\Controllers\Tenant\Catalog\ProductController::class, 'updateVariant'])->name('variants.update')->middleware('permission:catalog.products.update');
            Route::delete('/{productId}/variants/{variantId}', [App\Http\Controllers\Tenant\Catalog\ProductController::class, 'deleteVariant'])->name('variants.delete')->middleware('permission:catalog.products.delete');
            Route::post('/{id}/variants/bulk-update-prices', [App\Http\Controllers\Tenant\Catalog\ProductController::class, 'bulkUpdateVariantPrices'])->name('variants.bulk.update.prices')->middleware('permission:catalog.products.update');
        });

        // Category Resource Routes (with permissions)
        Route::resource('categories', App\Http\Controllers\Tenant\Catalog\CategoryController::class)->names([
            'index' => 'admin.categories.index',
            'create' => 'admin.categories.create',
            'store' => 'admin.categories.store',
            'show' => 'admin.categories.show',
            'edit' => 'admin.categories.edit',
            'update' => 'admin.categories.update',
            'destroy' => 'admin.categories.destroy'
        ])->middleware([
            'index' => 'permission:catalog.categories.read',
            'create' => 'permission:catalog.categories.create',
            'store' => 'permission:catalog.categories.create',
            'show' => 'permission:catalog.categories.read',
            'edit' => 'permission:catalog.categories.update',
            'update' => 'permission:catalog.categories.update',
            'destroy' => 'permission:catalog.categories.delete',
        ]);

        // Product Resource Routes (with permissions)
        Route::resource('products', App\Http\Controllers\Admin\ProductController::class)->names([
            'index' => 'admin.products.index',
            'create' => 'admin.products.create',
            'store' => 'admin.products.store',
            'show' => 'admin.products.show',
            'edit' => 'admin.products.edit',
            'update' => 'admin.products.update',
            'destroy' => 'admin.products.destroy'
        ])->middleware([
            'index' => 'permission:catalog.products.read',
            'create' => 'permission:catalog.products.create',
            'store' => 'permission:catalog.products.create',
            'show' => 'permission:catalog.products.read',
            'edit' => 'permission:catalog.products.update',
            'update' => 'permission:catalog.products.update',
            'destroy' => 'permission:catalog.products.delete',
        ]);

        // Image upload route (legacy)
        Route::post('/upload-image', [App\Http\Controllers\Tenant\Catalog\ProductController::class, 'uploadImage'])->name('admin.upload.image');

        // --------------------------------------------------------------------
        // CUSTOMERS MANAGEMENT
        // --------------------------------------------------------------------
        Route::prefix('customers')->name('admin.customers.')->group(function () {
            Route::get('/', [App\Http\Controllers\Tenant\CRM\CustomerController::class, 'index'])->name('index');
            Route::get('/ajax', [App\Http\Controllers\Tenant\CRM\CustomerController::class, 'ajaxGetCustomers'])->name('ajax');
            Route::get('/data', [App\Http\Controllers\Tenant\CRM\CustomerController::class, 'getData'])->name('data');
            Route::get('/statistics', [App\Http\Controllers\Tenant\CRM\CustomerController::class, 'getStatistics'])->name('statistics');
            Route::get('/active/list', [App\Http\Controllers\Tenant\CRM\CustomerController::class, 'getActiveCustomers'])->name('active-list');
            Route::get('/filter/statuses', [App\Http\Controllers\Tenant\CRM\CustomerController::class, 'getFilterStatuses'])->name('filter.statuses');
            Route::post('/bulk-delete', [App\Http\Controllers\Tenant\CRM\CustomerController::class, 'bulkDelete'])->name('bulk.delete');
            Route::get('/export', [App\Http\Controllers\Tenant\CRM\CustomerController::class, 'exportCustomers'])->name('export');
            Route::get('/create', [App\Http\Controllers\Tenant\CRM\CustomerController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\Tenant\CRM\CustomerController::class, 'store'])->name('store');
            Route::get('/{customer}/info', [App\Http\Controllers\Tenant\CRM\CustomerController::class, 'getCustomerInfo'])->name('info');
            Route::get('/{customer}/order-history', [App\Http\Controllers\Tenant\CRM\CustomerController::class, 'orderHistory'])->name('order-history');
            Route::get('/{customer}/point-history', [App\Http\Controllers\Tenant\CRM\CustomerController::class, 'pointHistory'])->name('point-history');
            Route::get('/{customer}/statistics', [App\Http\Controllers\Tenant\CRM\CustomerController::class, 'statistics'])->name('statistics.detail');
            Route::get('/{customer}', [App\Http\Controllers\Tenant\CRM\CustomerController::class, 'show'])->name('show');
            Route::put('/{customer}', [App\Http\Controllers\Tenant\CRM\CustomerController::class, 'update'])->name('update');
            Route::get('/{customer}/edit', [App\Http\Controllers\Tenant\CRM\CustomerController::class, 'edit'])->name('edit');
            Route::delete('/{customer}', [App\Http\Controllers\Tenant\CRM\CustomerController::class, 'destroy'])->name('destroy');
        });

        // --------------------------------------------------------------------
        // ORDERS MANAGEMENT
        // --------------------------------------------------------------------
        // Additional Order Routes (must be defined BEFORE resource routes to avoid conflicts)
        Route::prefix('orders')->name('admin.order.')->group(function () {
            // AJAX & Detail endpoints (must be before resource routes)
            Route::get('/ajax', [App\Http\Controllers\Tenant\Sales\OrderController::class, 'ajaxGetOrders'])->name('ajax');
            Route::get('/get/{order_id}', [App\Http\Controllers\Tenant\Sales\OrderController::class, 'get'])->name('get');
            Route::get('/detail/{order_id}', [App\Http\Controllers\Tenant\Sales\OrderController::class, 'detail'])->name('detail');
            Route::get('/print/{orderId}', [App\Http\Controllers\Tenant\Sales\OrderController::class, 'print'])->name('print');
        });

        // Order Resource Routes (defined after specific routes)
        Route::resource('orders', App\Http\Controllers\Tenant\Sales\OrderController::class)->names([
            'index' => 'admin.order.list', // Use 'list' instead of 'index' to match view expectations
            'create' => 'admin.order.create',
            'store' => 'admin.order.store',
            'show' => 'admin.order.show',
            'edit' => 'admin.order.edit',
            'update' => 'admin.order.update',
            'destroy' => 'admin.order.destroy'
        ]);

        // --------------------------------------------------------------------
        // INVOICES MANAGEMENT
        // --------------------------------------------------------------------
        Route::prefix('invoices')->name('admin.invoice.')->group(function () {
            // Static routes first (no parameters)
            Route::get('/', [App\Http\Controllers\Tenant\Sales\InvoiceController::class, 'index'])->name('list');
            Route::get('/ajax', [App\Http\Controllers\Tenant\Sales\InvoiceController::class, 'getInvoicesAjax'])->name('ajax');
            Route::get('/api/list', [App\Http\Controllers\Tenant\Sales\InvoiceController::class, 'getInvoicesForReturn'])->name('api.list');
            Route::get('/filter-users', [App\Http\Controllers\Tenant\Sales\InvoiceController::class, 'getFilterUsers'])->name('filter-users');
            Route::get('/create', [App\Http\Controllers\Tenant\Sales\InvoiceController::class, 'create'])->name('create');
            Route::post('/create', [App\Http\Controllers\Tenant\Sales\InvoiceController::class, 'store'])->name('store');
            Route::get('/statistics', [App\Http\Controllers\Tenant\Sales\InvoiceController::class, 'getStatistics'])->name('statistics');
            Route::get('/export/excel', [App\Http\Controllers\Tenant\Sales\InvoiceController::class, 'exportExcel'])->name('export.excel');
            Route::get('/export/pdf', [App\Http\Controllers\Tenant\Sales\InvoiceController::class, 'exportPdf'])->name('export.pdf');
            Route::post('/bulk-cancel', [App\Http\Controllers\Tenant\Sales\InvoiceController::class, 'bulkCancel'])->name('bulk-cancel');
            Route::post('/from-order/{order_id}', [App\Http\Controllers\Tenant\Sales\InvoiceController::class, 'createFromOrder'])->name('from-order');

            // Specific parameter routes (must come before generic /{id} route)
            Route::get('/{id}/details', [App\Http\Controllers\Tenant\Sales\InvoiceController::class, 'getInvoiceDetails'])->name('details');
            Route::get('/{id}/items', [App\Http\Controllers\Tenant\Sales\InvoiceController::class, 'getInvoiceItems'])->name('items');
            Route::get('/{id}/detail-panel', [App\Http\Controllers\Tenant\Sales\InvoiceController::class, 'getDetailPanel'])->name('detail-panel');
            Route::get('/{id}/payment-history', [App\Http\Controllers\Tenant\Sales\InvoiceController::class, 'getPaymentHistory'])->name('payment-history');
            Route::get('/{id}/edit', [App\Http\Controllers\Tenant\Sales\InvoiceController::class, 'edit'])->name('edit');
            Route::get('/{id}/print', [App\Http\Controllers\Tenant\Sales\InvoiceController::class, 'print'])->name('print');
            Route::post('/{id}/payment', [App\Http\Controllers\Tenant\Sales\InvoiceController::class, 'recordPayment'])->name('payment');
            Route::post('/{id}/send', [App\Http\Controllers\Tenant\Sales\InvoiceController::class, 'sendInvoice'])->name('send');
            Route::post('/{id}/cancel', [App\Http\Controllers\Tenant\Sales\InvoiceController::class, 'cancelInvoice'])->name('cancel');

            // Generic parameter routes (must come last)
            Route::get('/{id}', [App\Http\Controllers\Tenant\Sales\InvoiceController::class, 'show'])->name('show');
            Route::put('/{id}', [App\Http\Controllers\Tenant\Sales\InvoiceController::class, 'update'])->name('update');
            Route::delete('/{id}', [App\Http\Controllers\Tenant\Sales\InvoiceController::class, 'destroy'])->name('delete');
        });

        // --------------------------------------------------------------------
        // RETURN ORDERS MANAGEMENT
        // --------------------------------------------------------------------
        Route::prefix('returns')->name('admin.return.')->group(function () {
            Route::get('/', [App\Http\Controllers\Tenant\Sales\ReturnController::class, 'index'])->name('list');
            Route::get('/ajax', [App\Http\Controllers\Tenant\Sales\ReturnController::class, 'getReturnsAjax'])->name('ajax');
            Route::get('/filter-users', [App\Http\Controllers\Tenant\Sales\ReturnController::class, 'getFilterUsers'])->name('filter-users');
            Route::get('/create', [App\Http\Controllers\Tenant\Sales\ReturnController::class, 'create'])->name('create');
            Route::post('/create', [App\Http\Controllers\Tenant\Sales\ReturnController::class, 'store'])->name('store');
            Route::get('/statistics', [App\Http\Controllers\Tenant\Sales\ReturnController::class, 'getStatistics'])->name('statistics');
            Route::get('/export/excel', [App\Http\Controllers\Tenant\Sales\ReturnController::class, 'exportExcel'])->name('export.excel');
            Route::get('/{id}', [App\Http\Controllers\Tenant\Sales\ReturnController::class, 'show'])->name('show');
            Route::get('/{id}/detail-panel', [App\Http\Controllers\Tenant\Sales\ReturnController::class, 'getDetailPanel'])->name('detail-panel');
            Route::get('/{id}/payment-history', [App\Http\Controllers\Tenant\Sales\ReturnController::class, 'getPaymentHistory'])->name('payment-history');
            Route::get('/{id}/edit', [App\Http\Controllers\Tenant\Sales\ReturnController::class, 'edit'])->name('edit');
            Route::put('/{id}', [App\Http\Controllers\Tenant\Sales\ReturnController::class, 'update'])->name('update');
            Route::delete('/{id}', [App\Http\Controllers\Tenant\Sales\ReturnController::class, 'destroy'])->name('delete');
            Route::post('/{id}/payment', [App\Http\Controllers\Tenant\Sales\ReturnController::class, 'recordPayment'])->name('payment');
            Route::post('/{id}/send', [App\Http\Controllers\Tenant\Sales\ReturnController::class, 'sendReturn'])->name('send');
            Route::post('/{id}/cancel', [App\Http\Controllers\Tenant\Sales\ReturnController::class, 'cancelReturn'])->name('cancel');
            Route::get('/{id}/print', [App\Http\Controllers\Tenant\Sales\ReturnController::class, 'print'])->name('print');
            Route::post('/from-invoice/{invoice_id}', [App\Http\Controllers\Tenant\Sales\ReturnController::class, 'createFromInvoice'])->name('from-invoice');
            Route::post('/bulk/update-status', [App\Http\Controllers\Tenant\Sales\ReturnController::class, 'bulkUpdateStatus'])->name('bulk.update-status');
            Route::post('/bulk/cancel', [App\Http\Controllers\Tenant\Sales\ReturnController::class, 'bulkCancel'])->name('bulk.cancel');
            Route::post('/bulk/delete', [App\Http\Controllers\Tenant\Sales\ReturnController::class, 'bulkDelete'])->name('bulk.delete');
            Route::get('/{id}/export/pdf', [App\Http\Controllers\Tenant\Sales\ReturnController::class, 'exportPdf'])->name('export.pdf');
        });

        // --------------------------------------------------------------------
        // PAYMENTS MANAGEMENT
        // --------------------------------------------------------------------
        Route::prefix('payments')->name('admin.payment.')->group(function () {
            Route::get('/', [App\Http\Controllers\Tenant\Cashbook\PaymentController::class, 'index'])->name('list');
            Route::get('/ajax', [App\Http\Controllers\Tenant\Cashbook\PaymentController::class, 'getPaymentsAjax'])->name('ajax');
            Route::get('/summary', [App\Http\Controllers\Tenant\Cashbook\PaymentController::class, 'getSummary'])->name('summary');
            Route::get('/statistics/summary', [App\Http\Controllers\Tenant\Cashbook\PaymentController::class, 'getStatistics'])->name('statistics');
            Route::get('/create', [App\Http\Controllers\Tenant\Cashbook\PaymentController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\Tenant\Cashbook\PaymentController::class, 'store'])->name('store');
            Route::get('/{payment}', [App\Http\Controllers\Tenant\Cashbook\PaymentController::class, 'show'])->name('show');
            Route::get('/{payment}/edit', [App\Http\Controllers\Tenant\Cashbook\PaymentController::class, 'edit'])->name('edit');
            Route::put('/{payment}', [App\Http\Controllers\Tenant\Cashbook\PaymentController::class, 'update'])->name('update');
            Route::post('/{payment}/approve', [App\Http\Controllers\Tenant\Cashbook\PaymentController::class, 'approve'])->name('approve');
            Route::post('/{payment}/cancel', [App\Http\Controllers\Tenant\Cashbook\PaymentController::class, 'cancel'])->name('cancel');
            Route::get('/{payment}/details', [App\Http\Controllers\Tenant\Cashbook\PaymentController::class, 'getDetails'])->name('details');
            Route::get('/{payment}/print', [App\Http\Controllers\Tenant\Cashbook\PaymentController::class, 'print'])->name('print');
            Route::post('/invoices/{invoice}/payment', [App\Http\Controllers\Tenant\Cashbook\PaymentController::class, 'createInvoicePayment'])->name('invoice-payment');
        });

        // --------------------------------------------------------------------
        // SUPPLIERS MANAGEMENT
        // --------------------------------------------------------------------
        Route::prefix('suppliers')->name('admin.supplier.')->group(function () {
            Route::get('/', [App\Http\Controllers\Tenant\Purchasing\SupplierController::class, 'index'])->name('list');
            Route::get('/ajax', [App\Http\Controllers\Tenant\Purchasing\SupplierController::class, 'ajaxGetList'])->name('ajax');
            Route::get('/add', [App\Http\Controllers\Tenant\Purchasing\SupplierController::class, 'add'])->name('add');
            Route::post('/add', [App\Http\Controllers\Tenant\Purchasing\SupplierController::class, 'addAction'])->name('add.action');
            Route::get('/edit/{supplier_id}', [App\Http\Controllers\Tenant\Purchasing\SupplierController::class, 'edit'])->name('edit');
            Route::post('/edit/{supplier_id}', [App\Http\Controllers\Tenant\Purchasing\SupplierController::class, 'editAction'])->name('edit.action');
            Route::get('/detail/{supplier_id}', [App\Http\Controllers\Tenant\Purchasing\SupplierController::class, 'detail'])->name('detail');
            Route::delete('/delete/{supplier_id}', [App\Http\Controllers\Tenant\Purchasing\SupplierController::class, 'delete'])->name('delete');
            Route::post('/delete', [App\Http\Controllers\Tenant\Purchasing\SupplierController::class, 'deleteMany'])->name('delete.many');
            Route::get('/active', [App\Http\Controllers\Tenant\Purchasing\SupplierController::class, 'getActiveSuppliers'])->name('active');
            Route::post('/check-code', [App\Http\Controllers\Tenant\Purchasing\SupplierController::class, 'checkCodeUnique'])->name('check.code');
            Route::get('/statistics', [App\Http\Controllers\Tenant\Purchasing\SupplierController::class, 'getStatistics'])->name('statistics');
        });

        // --------------------------------------------------------------------
        // BRANCH SHOPS MANAGEMENT
        // --------------------------------------------------------------------
        Route::prefix('branch-shops')->name('admin.branch-shops.')->group(function () {
            Route::get('/', [App\Http\Controllers\Tenant\Settings\BranchManagerController::class, 'index'])->name('index');
            Route::get('/data', [App\Http\Controllers\Tenant\Settings\BranchManagerController::class, 'getData'])->name('data');
            Route::get('/create', [App\Http\Controllers\Tenant\Settings\BranchManagerController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\Tenant\Settings\BranchManagerController::class, 'store'])->name('store');
            Route::get('/active', [App\Http\Controllers\Tenant\Settings\BranchManagerController::class, 'getActiveBranchShops'])->name('active');
            Route::get('/available', [App\Http\Controllers\Tenant\Settings\BranchManagerController::class, 'getAvailableBranchShops'])->name('available');
            Route::post('/switch', [App\Http\Controllers\Tenant\Settings\BranchManagerController::class, 'switchBranchShop'])->name('switch');
            Route::get('/dropdown/active', [App\Http\Controllers\Tenant\Settings\BranchManagerController::class, 'getActiveForDropdown'])->name('dropdown.active');
            Route::get('/dropdown/managers', [App\Http\Controllers\Tenant\Settings\BranchManagerController::class, 'getManagersForDropdown'])->name('dropdown.managers');
            Route::get('/{id}', [App\Http\Controllers\Tenant\Settings\BranchManagerController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [App\Http\Controllers\Tenant\Settings\BranchManagerController::class, 'edit'])->name('edit');
            Route::put('/{id}', [App\Http\Controllers\Tenant\Settings\BranchManagerController::class, 'update'])->name('update');
            Route::delete('/{id}', [App\Http\Controllers\Tenant\Settings\BranchManagerController::class, 'destroy'])->name('destroy');
            Route::post('/bulk-action', [App\Http\Controllers\Tenant\Settings\BranchManagerController::class, 'bulkAction'])->name('bulk-action');
            Route::get('/statistics/summary', [App\Http\Controllers\Tenant\Settings\BranchManagerController::class, 'getStatistics'])->name('statistics');
            Route::get('/{branchShop}/users/data', [App\Http\Controllers\Tenant\Settings\BranchManagerController::class, 'getUsersData'])->name('users.data');
            Route::post('/{branchShop}/users', [App\Http\Controllers\Tenant\Settings\BranchManagerController::class, 'addUser'])->name('users.add');
            Route::delete('/{branchShop}/users/{user}', [App\Http\Controllers\Tenant\Settings\BranchManagerController::class, 'removeUser'])->name('users.remove');
            Route::put('/{branchShop}/users/{user}', [App\Http\Controllers\Tenant\Settings\BranchManagerController::class, 'updateUser'])->name('users.update');
        });

        // --------------------------------------------------------------------
        // USERS MANAGEMENT
        // --------------------------------------------------------------------
        Route::prefix('users')->name('admin.users.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\CMS\UsersController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\Admin\CMS\UsersController::class, 'add'])->name('create');
            Route::post('/', [App\Http\Controllers\Admin\CMS\UsersController::class, 'addAction'])->name('store');
            Route::get('/{id}', [App\Http\Controllers\Admin\CMS\UsersController::class, 'detail'])->name('show');
            Route::get('/{id}/edit', [App\Http\Controllers\Admin\CMS\UsersController::class, 'edit'])->name('edit');
            Route::put('/{id}', [App\Http\Controllers\Admin\CMS\UsersController::class, 'update'])->name('update');
            Route::delete('/{id}', [App\Http\Controllers\Admin\CMS\UsersController::class, 'delete'])->name('destroy');
            Route::post('/bulk-delete', [App\Http\Controllers\Admin\CMS\UsersController::class, 'deleteMany'])->name('bulk-delete');
            Route::get('/ajax/get-list', [App\Http\Controllers\Admin\CMS\UsersController::class, 'ajaxGetList'])->name('ajax.getList');
            Route::get('/dropdown/available', [App\Http\Controllers\Admin\CMS\UsersController::class, 'getAvailableForDropdown'])->name('dropdown.available');
            Route::get('/dropdown/list', [App\Http\Controllers\Admin\CMS\UsersController::class, 'listForDropdown'])->name('dropdown.list');

            // Branch Shop Management for Users
            Route::post('/{userId}/assign-branch-shop', [App\Http\Controllers\Admin\CMS\UsersController::class, 'assignBranchShop'])->name('assign-branch-shop');
            Route::put('/{userId}/branch-shops/{branchShopId}', [App\Http\Controllers\Admin\CMS\UsersController::class, 'updateBranchShop'])->name('update-branch-shop');
            Route::delete('/{userId}/branch-shops/{branchShopId}', [App\Http\Controllers\Admin\CMS\UsersController::class, 'removeBranchShop'])->name('remove-branch-shop');
        });

        // --------------------------------------------------------------------
        // WAREHOUSES MANAGEMENT
        // --------------------------------------------------------------------
        Route::prefix('warehouses')->name('admin.warehouses.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\CMS\WarehouseController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\Admin\CMS\WarehouseController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\Admin\CMS\WarehouseController::class, 'store'])->name('store');
            Route::get('/dropdown', [App\Http\Controllers\Admin\CMS\WarehouseController::class, 'getForDropdown'])->name('dropdown');
            Route::get('/{id}', [App\Http\Controllers\Admin\CMS\WarehouseController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [App\Http\Controllers\Admin\CMS\WarehouseController::class, 'edit'])->name('edit');
            Route::put('/{id}', [App\Http\Controllers\Admin\CMS\WarehouseController::class, 'update'])->name('update');
            Route::delete('/{id}', [App\Http\Controllers\Admin\CMS\WarehouseController::class, 'destroy'])->name('destroy');
        });

        // --------------------------------------------------------------------
        // NOTIFICATIONS MANAGEMENT
        // --------------------------------------------------------------------
        Route::prefix('notifications')->name('admin.notifications.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\CMS\NotificationController::class, 'index'])->name('index');
            Route::get('/data', [App\Http\Controllers\Admin\CMS\NotificationController::class, 'getData'])->name('data');
            Route::get('/recent', [App\Http\Controllers\Admin\CMS\NotificationController::class, 'getRecent'])->name('recent');
            Route::get('/count', [App\Http\Controllers\Admin\CMS\NotificationController::class, 'getCount'])->name('count');
            Route::get('/unread-count', [App\Http\Controllers\Admin\CMS\NotificationController::class, 'getUnreadCount'])->name('unread-count');
            Route::get('/types', [App\Http\Controllers\Admin\CMS\NotificationController::class, 'getTypes'])->name('types');
            Route::get('/statistics', [App\Http\Controllers\Admin\CMS\NotificationController::class, 'getStatistics'])->name('statistics');
            Route::post('/', [App\Http\Controllers\Admin\CMS\NotificationController::class, 'store'])->name('store');
            Route::put('/{id}/read', [App\Http\Controllers\Admin\CMS\NotificationController::class, 'markAsRead'])->name('mark-read');
            Route::put('/mark-all-read', [App\Http\Controllers\Admin\CMS\NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
            Route::delete('/{id}', [App\Http\Controllers\Admin\CMS\NotificationController::class, 'destroy'])->name('destroy');
            Route::delete('/cleanup/expired', [App\Http\Controllers\Admin\CMS\NotificationController::class, 'cleanupExpired'])->name('cleanup-expired');
            Route::delete('/cleanup/old', [App\Http\Controllers\Admin\CMS\NotificationController::class, 'cleanupOld'])->name('cleanup-old');
        });

        // --------------------------------------------------------------------
        // INVENTORY MANAGEMENT
        // --------------------------------------------------------------------
        Route::prefix('inventory')->name('admin.inventory.')->group(function () {
            Route::get('/', [App\Http\Controllers\Tenant\Inventory\InventoryController::class, 'index'])->name('dashboard');

            // Import/Export Routes
            Route::get('/import', [App\Http\Controllers\Tenant\Inventory\InventoryController::class, 'import'])->name('import');
            Route::post('/import', [App\Http\Controllers\Tenant\Inventory\InventoryController::class, 'processImport'])->name('process-import');
            Route::get('/export', [App\Http\Controllers\Tenant\Inventory\InventoryController::class, 'export'])->name('export');
            Route::post('/export', [App\Http\Controllers\Tenant\Inventory\InventoryController::class, 'processExport'])->name('process-export');

            // Adjustment Routes
            Route::get('/adjustment', [App\Http\Controllers\Tenant\Inventory\InventoryController::class, 'adjustment'])->name('adjustment');
            Route::post('/adjustment', [App\Http\Controllers\Tenant\Inventory\InventoryController::class, 'processAdjustment'])->name('process-adjustment');

            // Transaction Routes
            Route::get('/transactions', [App\Http\Controllers\Tenant\Inventory\InventoryController::class, 'transactions'])->name('transactions');
            Route::get('/transactions/ajax', [App\Http\Controllers\Tenant\Inventory\InventoryController::class, 'getTransactionsAjax'])->name('transactions.ajax');
            Route::get('/transactions/statistics', [App\Http\Controllers\Tenant\Inventory\InventoryController::class, 'getTransactionStatistics'])->name('transactions.statistics');
            Route::get('/transactions/{id}', [App\Http\Controllers\Tenant\Inventory\InventoryController::class, 'getTransactionDetail'])->name('transaction.detail');

            // Report Routes
            Route::get('/report', [App\Http\Controllers\Tenant\Inventory\InventoryController::class, 'report'])->name('report');
            Route::get('/export-transactions', [App\Http\Controllers\Tenant\Inventory\InventoryController::class, 'exportTransactions'])->name('export-transactions');

            // Stock Check Routes
            Route::get('/stock-check', [App\Http\Controllers\Tenant\Inventory\InventoryController::class, 'stockCheck'])->name('stock-check');
        });

        // --------------------------------------------------------------------
        // INVENTORY IMPORT/EXPORT
        // --------------------------------------------------------------------
        Route::prefix('inventory/import-export')->name('admin.inventory.import-export.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\CMS\InventoryImportExportController::class, 'index'])->name('index');
            Route::post('/export', [App\Http\Controllers\Admin\CMS\InventoryImportExportController::class, 'export'])->name('export');
            Route::post('/import', [App\Http\Controllers\Admin\CMS\InventoryImportExportController::class, 'import'])->name('import');
            Route::get('/template', [App\Http\Controllers\Admin\CMS\InventoryImportExportController::class, 'downloadTemplate'])->name('template');
            Route::get('/history', [App\Http\Controllers\Admin\CMS\InventoryImportExportController::class, 'getHistory'])->name('history');
            Route::get('/summary', [App\Http\Controllers\Admin\CMS\InventoryImportExportController::class, 'getSummary'])->name('summary');
            Route::post('/validate', [App\Http\Controllers\Admin\CMS\InventoryImportExportController::class, 'validateImportFile'])->name('validate');
            Route::get('/warehouses', [App\Http\Controllers\Admin\CMS\InventoryImportExportController::class, 'getWarehouses'])->name('warehouses');
            Route::get('/categories', [App\Http\Controllers\Admin\CMS\InventoryImportExportController::class, 'getProductCategories'])->name('categories');
        });

        // --------------------------------------------------------------------
        // FILE MANAGER
        // --------------------------------------------------------------------
        Route::prefix('filemanager')->name('admin.filemanager.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\FileManagerController::class, 'index'])->name('index');
            Route::get('/contents', [App\Http\Controllers\Admin\FileManagerController::class, 'getContents'])->name('contents');
            Route::post('/upload', [App\Http\Controllers\Admin\FileManagerController::class, 'upload'])->name('upload');
            Route::post('/upload-single', [App\Http\Controllers\Admin\FileManagerController::class, 'uploadSingle'])->name('upload.single');
            Route::delete('/delete', [App\Http\Controllers\Admin\FileManagerController::class, 'delete'])->name('delete');
            Route::delete('/delete-multiple', [App\Http\Controllers\Admin\FileManagerController::class, 'deleteMultiple'])->name('delete.multiple');
            Route::put('/rename', [App\Http\Controllers\Admin\FileManagerController::class, 'rename'])->name('rename');
            Route::post('/create-folder', [App\Http\Controllers\Admin\FileManagerController::class, 'createFolder'])->name('create.folder');
            Route::put('/move', [App\Http\Controllers\Admin\FileManagerController::class, 'move'])->name('move');
            Route::put('/copy', [App\Http\Controllers\Admin\FileManagerController::class, 'copy'])->name('copy');
            Route::get('/file-info', [App\Http\Controllers\Admin\FileManagerController::class, 'getFileInfo'])->name('file.info');
            Route::get('/search', [App\Http\Controllers\Admin\FileManagerController::class, 'search'])->name('search');
        });

        // --------------------------------------------------------------------
        // FILTERS API
        // --------------------------------------------------------------------
        Route::prefix('filters')->name('admin.filters.')->group(function () {
            Route::get('/all', [App\Http\Controllers\Admin\FilterController::class, 'getAllFilters'])->name('all');
            Route::get('/creators', [App\Http\Controllers\Admin\FilterController::class, 'getCreators'])->name('creators');
            Route::get('/sellers', [App\Http\Controllers\Admin\FilterController::class, 'getSellers'])->name('sellers');
            Route::get('/channels', [App\Http\Controllers\Admin\FilterController::class, 'getChannels'])->name('channels');
            Route::get('/branch-shops', [App\Http\Controllers\Admin\FilterController::class, 'getBranchShops'])->name('branch-shops');
            Route::get('/categories', [App\Http\Controllers\Admin\FilterController::class, 'getCategories'])->name('categories');
            Route::get('/product-categories', [App\Http\Controllers\Admin\FilterController::class, 'getProductCategories'])->name('product-categories');
            Route::get('/customers', [App\Http\Controllers\Admin\FilterController::class, 'getCustomers'])->name('customers');
            Route::get('/suppliers', [App\Http\Controllers\Admin\FilterController::class, 'getSuppliers'])->name('suppliers');
            Route::get('/payment-methods', [App\Http\Controllers\Admin\FilterController::class, 'getPaymentMethods'])->name('payment-methods');
            Route::get('/bank-accounts', [App\Http\Controllers\Admin\FilterController::class, 'getBankAccounts'])->name('bank-accounts');
        });

        // --------------------------------------------------------------------
        // QUICK ORDER (POS SYSTEM)
        // --------------------------------------------------------------------
        Route::prefix('quick-order')->name('admin.quick-order.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\QuickOrderController::class, 'index'])->name('index');
            Route::post('/', [App\Http\Controllers\Admin\QuickOrderController::class, 'store'])->name('store');
            Route::get('/session', [App\Http\Controllers\Admin\QuickOrderController::class, 'getSession'])->name('session.get');
            Route::post('/session', [App\Http\Controllers\Admin\QuickOrderController::class, 'saveSession'])->name('session.save');
            Route::delete('/session', [App\Http\Controllers\Admin\QuickOrderController::class, 'clearSession'])->name('session.clear');
            Route::get('/statistics', [App\Http\Controllers\Admin\QuickOrderController::class, 'getStatistics'])->name('statistics');
            Route::post('/validate', [App\Http\Controllers\Admin\QuickOrderController::class, 'validateOrder'])->name('validate');
            Route::post('/search-product', [App\Http\Controllers\Admin\QuickOrderController::class, 'searchProduct'])->name('search-product');
            Route::get('/invoices-for-return', [App\Http\Controllers\Admin\QuickOrderController::class, 'getInvoicesForReturn'])->name('invoices-for-return');
            Route::get('/invoice-items/{invoiceId}', [App\Http\Controllers\Admin\QuickOrderController::class, 'getInvoiceItems'])->name('invoice-items');
            Route::post('/return-orders', [App\Http\Controllers\Admin\QuickOrderController::class, 'storeReturnOrder'])->name('return-orders.store');
            Route::get('/sellers-by-branch', [App\Http\Controllers\Admin\QuickOrderController::class, 'getSellersByBranch'])->name('sellers-by-branch');
        });

        // --------------------------------------------------------------------
        // QUICK INVOICE
        // --------------------------------------------------------------------
        Route::prefix('quick-invoice')->name('admin.quick-invoice.')->group(function () {
            Route::post('/', [App\Http\Controllers\Admin\QuickInvoiceController::class, 'store'])->name('store');
        });

        // ====================================================================
        // TENANT SETTINGS
        // ====================================================================
        // Settings routes are organized by category for better maintainability
        // Each category has its own controller namespace and routes
        //
        // Current Categories:
        // - Shop: Retailer Info, User Manager, Branch Manager
        //
        // Future Categories (to be added):
        // - Data: Import/Export, Backup/Restore
        // - Devices: POS Devices, Printers, Scanners
        // - Integrations: Payment Gateways, Shipping Providers
        // ====================================================================

        Route::prefix('settings')->name('admin.settings.')->group(function () {

            // ----------------------------------------------------------------
            // CATEGORY: SHOP (Cửa h?ng)
            // ----------------------------------------------------------------
            // Retailer Information - Store details, logo, contact info
            Route::get('/retailer-info', [App\Http\Controllers\Tenant\Settings\Shop\RetailerInfoController::class, 'index'])
                ->name('retailer-info');
            Route::post('/retailer-info', [App\Http\Controllers\Tenant\Settings\Shop\RetailerInfoController::class, 'update'])
                ->name('retailer-info.update');

            // User Manager - Manage tenant users
            Route::get('/user-manager', [App\Http\Controllers\Tenant\Settings\Shop\UserManagerController::class, 'index'])
                ->name('user-manager');
            Route::get('/user-manager/data', [App\Http\Controllers\Tenant\Settings\Shop\UserManagerController::class, 'getData'])
                ->name('user-manager.data');
            Route::get('/user-manager/roles', [App\Http\Controllers\Tenant\Settings\Shop\UserManagerController::class, 'getRoles'])
                ->name('user-manager.roles');
            Route::get('/user-manager/roles-data', [App\Http\Controllers\Tenant\Settings\Shop\UserManagerController::class, 'getRolesData'])
                ->name('user-manager.roles-data');
            Route::post('/user-manager/store', [App\Http\Controllers\Tenant\Settings\Shop\UserManagerController::class, 'store'])
                ->name('user-manager.store');
            Route::get('/user-manager/{id}/show', [App\Http\Controllers\Tenant\Settings\Shop\UserManagerController::class, 'show'])
                ->name('user-manager.show');
            Route::post('/user-manager/{id}/update', [App\Http\Controllers\Tenant\Settings\Shop\UserManagerController::class, 'update'])
                ->name('user-manager.update');
            Route::post('/user-manager/{id}/change-password', [App\Http\Controllers\Tenant\Settings\Shop\UserManagerController::class, 'changePassword'])
                ->name('user-manager.change-password');
            Route::post('/user-manager/{id}/deactivate', [App\Http\Controllers\Tenant\Settings\Shop\UserManagerController::class, 'deactivate'])
                ->name('user-manager.deactivate');
            Route::post('/user-manager/{id}/assign-roles', [App\Http\Controllers\Tenant\Settings\Shop\UserManagerController::class, 'assignRoles'])
                ->name('user-manager.assign-roles');

            // Role Manager - Manage roles and permissions
            Route::get('/roles', [App\Http\Controllers\Tenant\Staff\RoleController::class, 'index'])
                ->name('roles.index');
            Route::get('/roles/permissions', [App\Http\Controllers\Tenant\Staff\RoleController::class, 'getPermissions'])
                ->name('roles.permissions');
            Route::get('/roles/{id}', [App\Http\Controllers\Tenant\Staff\RoleController::class, 'show'])
                ->name('roles.show');
            Route::post('/roles', [App\Http\Controllers\Tenant\Staff\RoleController::class, 'store'])
                ->name('roles.store');
            Route::put('/roles/{id}', [App\Http\Controllers\Tenant\Staff\RoleController::class, 'update'])
                ->name('roles.update');
            Route::delete('/roles/{id}', [App\Http\Controllers\Tenant\Staff\RoleController::class, 'destroy'])
                ->name('roles.destroy');

            // Branch Manager - Manage branch shops
            Route::get('/branch-manager', [App\Http\Controllers\Tenant\Settings\Shop\BranchManagerController::class, 'index'])
                ->name('branch-manager');
            Route::get('/branch-manager/data', [App\Http\Controllers\Tenant\Settings\Shop\BranchManagerController::class, 'getData'])
                ->name('branch-manager.data');

            // ----------------------------------------------------------------
            // FUTURE CATEGORIES (Placeholder for expansion)
            // ----------------------------------------------------------------
            // Route::prefix('data')->name('data.')->group(function () {
            //     // Data management routes
            // });
            //
            // Route::prefix('devices')->name('devices.')->group(function () {
            //     // Device management routes
            // });
            //
            // Route::prefix('integrations')->name('integrations.')->group(function () {
            //     // Integration management routes
            // });

        }); // End Settings Group

        // --------------------------------------------------------------------
        // PRICING MANAGEMENT
        // --------------------------------------------------------------------
        Route::prefix('pricing')->name('admin.pricing.')->group(function () {
            Route::get('/', [App\Http\Controllers\Tenant\PlaceholderController::class, 'pricing'])->name('index');
        });

        // --------------------------------------------------------------------
        // WAREHOUSE MANAGEMENT
        // --------------------------------------------------------------------
        Route::prefix('transfer')->name('admin.transfer.')->group(function () {
            Route::get('/', [App\Http\Controllers\Tenant\PlaceholderController::class, 'transfer'])->name('index');
        });

        Route::prefix('stock-check')->name('admin.stock-check.')->group(function () {
            Route::get('/', [App\Http\Controllers\Tenant\PlaceholderController::class, 'stockCheck'])->name('index');
        });

        Route::prefix('disposal')->name('admin.disposal.')->group(function () {
            Route::get('/', [App\Http\Controllers\Tenant\PlaceholderController::class, 'disposal'])->name('index');
        });

        // --------------------------------------------------------------------
        // PURCHASE MANAGEMENT
        // --------------------------------------------------------------------
        Route::prefix('purchase')->name('admin.purchase.')->group(function () {
            Route::get('/', [App\Http\Controllers\Tenant\PlaceholderController::class, 'purchase'])->name('index');
        });

        Route::prefix('purchase-return')->name('admin.purchase-return.')->group(function () {
            Route::get('/', [App\Http\Controllers\Tenant\PlaceholderController::class, 'purchaseReturn'])->name('index');
        });

        // --------------------------------------------------------------------
        // SHIPPING MANAGEMENT
        // --------------------------------------------------------------------
        Route::prefix('shipping-partner')->name('admin.shipping-partner.')->group(function () {
            Route::get('/', [App\Http\Controllers\Tenant\PlaceholderController::class, 'shippingPartner'])->name('index');
        });

        Route::prefix('waybill')->name('admin.waybill.')->group(function () {
            Route::get('/', [App\Http\Controllers\Tenant\PlaceholderController::class, 'waybill'])->name('index');
        });

        // --------------------------------------------------------------------
        // PROMOTION & VOUCHER
        // --------------------------------------------------------------------
        Route::prefix('promotions')->name('admin.promotion.')->group(function () {
            Route::get('/', [App\Http\Controllers\Tenant\CRM\PromotionController::class, 'index'])->name('index');
            Route::get('/ajax', [App\Http\Controllers\Tenant\CRM\PromotionController::class, 'ajaxGetPromotions'])->name('ajax');
            Route::get('/filter/statuses', [App\Http\Controllers\Tenant\CRM\PromotionController::class, 'getFilterStatuses'])->name('filter.statuses');
            Route::get('/filter/types', [App\Http\Controllers\Tenant\CRM\PromotionController::class, 'getFilterTypes'])->name('filter.types');
            Route::post('/bulk-delete', [App\Http\Controllers\Tenant\CRM\PromotionController::class, 'bulkDelete'])->name('bulk.delete');
            Route::get('/export', [App\Http\Controllers\Tenant\CRM\PromotionController::class, 'export'])->name('export');
        });

        Route::prefix('vouchers')->name('admin.voucher.')->group(function () {
            Route::get('/', [App\Http\Controllers\Tenant\CRM\VoucherController::class, 'index'])->name('index');
            Route::get('/ajax', [App\Http\Controllers\Tenant\CRM\VoucherController::class, 'ajaxGetVouchers'])->name('ajax');
            Route::get('/filter/statuses', [App\Http\Controllers\Tenant\CRM\VoucherController::class, 'getFilterStatuses'])->name('filter.statuses');
            Route::get('/filter/types', [App\Http\Controllers\Tenant\CRM\VoucherController::class, 'getFilterTypes'])->name('filter.types');
            Route::post('/bulk-delete', [App\Http\Controllers\Tenant\CRM\VoucherController::class, 'bulkDelete'])->name('bulk.delete');
            Route::get('/export', [App\Http\Controllers\Tenant\CRM\VoucherController::class, 'export'])->name('export');
        });

        // --------------------------------------------------------------------
        // EMPLOYEE MANAGEMENT
        // --------------------------------------------------------------------
        Route::prefix('employees')->name('admin.employees.')->group(function () {
            Route::get('/', [App\Http\Controllers\Tenant\PlaceholderController::class, 'employees'])->name('index');
        });

        Route::prefix('schedule')->name('admin.schedule.')->group(function () {
            Route::get('/', [App\Http\Controllers\Tenant\PlaceholderController::class, 'schedule'])->name('index');
        });

        Route::prefix('attendance')->name('admin.attendance.')->group(function () {
            Route::get('/', [App\Http\Controllers\Tenant\PlaceholderController::class, 'attendance'])->name('index');
        });

        Route::prefix('payroll')->name('admin.payroll.')->group(function () {
            Route::get('/', [App\Http\Controllers\Tenant\PlaceholderController::class, 'payroll'])->name('index');
        });

        Route::prefix('commission')->name('admin.commission.')->group(function () {
            Route::get('/', [App\Http\Controllers\Tenant\PlaceholderController::class, 'commission'])->name('index');
        });

        Route::prefix('employee-settings')->name('admin.employee-settings.')->group(function () {
            Route::get('/', [App\Http\Controllers\Tenant\PlaceholderController::class, 'employeeSettings'])->name('index');
        });

        // --------------------------------------------------------------------
        // FUND MANAGEMENT
        // --------------------------------------------------------------------
        Route::prefix('fund-transfer')->name('admin.fund-transfer.')->group(function () {
            Route::get('/', [App\Http\Controllers\Tenant\PlaceholderController::class, 'fundTransfer'])->name('index');
        });

        Route::prefix('fund-report')->name('admin.fund-report.')->group(function () {
            Route::get('/', [App\Http\Controllers\Tenant\PlaceholderController::class, 'fundReport'])->name('index');
        });

        // --------------------------------------------------------------------
        // ANALYTICS
        // --------------------------------------------------------------------
        Route::prefix('analytics')->name('admin.analytics.')->group(function () {
            Route::get('/business', [App\Http\Controllers\Tenant\PlaceholderController::class, 'analyticsBusiness'])->name('business');
            Route::get('/products', [App\Http\Controllers\Tenant\PlaceholderController::class, 'analyticsProducts'])->name('products');
            Route::get('/customers', [App\Http\Controllers\Tenant\PlaceholderController::class, 'analyticsCustomers'])->name('customers');
            Route::get('/performance', [App\Http\Controllers\Tenant\PlaceholderController::class, 'analyticsPerformance'])->name('performance');
        });

        // --------------------------------------------------------------------
        // REPORTS
        // --------------------------------------------------------------------
        Route::prefix('report')->name('admin.report.')->group(function () {
            Route::get('/end-of-day', [App\Http\Controllers\Tenant\PlaceholderController::class, 'reportEndOfDay'])->name('end-of-day');
            Route::get('/sales', [App\Http\Controllers\Tenant\PlaceholderController::class, 'reportSales'])->name('sales');
            Route::get('/orders', [App\Http\Controllers\Tenant\PlaceholderController::class, 'reportOrders'])->name('orders');
            Route::get('/products', [App\Http\Controllers\Tenant\PlaceholderController::class, 'reportProducts'])->name('products');
            Route::get('/customers', [App\Http\Controllers\Tenant\PlaceholderController::class, 'reportCustomers'])->name('customers');
            Route::get('/suppliers', [App\Http\Controllers\Tenant\PlaceholderController::class, 'reportSuppliers'])->name('suppliers');
            Route::get('/employees', [App\Http\Controllers\Tenant\PlaceholderController::class, 'reportEmployees'])->name('employees');
            Route::get('/channels', [App\Http\Controllers\Tenant\PlaceholderController::class, 'reportChannels'])->name('channels');
            Route::get('/finance', [App\Http\Controllers\Tenant\PlaceholderController::class, 'reportFinance'])->name('finance');
        });

    }); // End Admin Routes Group

}); // End Subdomain Routes Group

// ============================================================================
// NON-SUBDOMAIN TENANT ROUTES
// ============================================================================
Route::middleware(['web'])->group(function () {

    // ------------------------------------------------------------------------
    // TENANT SELECTION & MANAGEMENT
    // ------------------------------------------------------------------------
    Route::get('/tenant/select', [App\Http\Controllers\TenantController::class, 'select'])->name('tenant.select');

    // ------------------------------------------------------------------------
    // TENANT API ROUTES (Authenticated)
    // ------------------------------------------------------------------------
    Route::middleware(['auth:tenant'])->group(function () {
        Route::get('/api/tenant/current', [App\Http\Controllers\TenantController::class, 'current'])->name('tenant.current');
        Route::get('/api/tenant/available', [App\Http\Controllers\TenantController::class, 'available'])->name('tenant.available');
        Route::post('/api/tenant/switch', [App\Http\Controllers\TenantController::class, 'switch'])->name('tenant.switch');
        Route::post('/api/tenant/validate-access', [App\Http\Controllers\TenantController::class, 'validateAccess'])->name('tenant.validate-access');
        Route::get('/api/tenant/settings', [App\Http\Controllers\TenantController::class, 'getSettings'])->name('tenant.settings');
        Route::post('/api/tenant/settings/update', [App\Http\Controllers\TenantController::class, 'updateSetting'])->name('tenant.settings.update');
        Route::get('/api/tenant/statistics', [App\Http\Controllers\TenantController::class, 'getStatistics'])->name('tenant.statistics');
    });

}); // End Non-Subdomain Tenant Routes Group

