<?php

use Illuminate\Support\Facades\Route;

// TODO: Create Business controllers
/*
use App\Http\Controllers\Business\ProductController;
use App\Http\Controllers\Business\OrderController;
use App\Http\Controllers\Business\InvoiceController;
use App\Http\Controllers\Business\CustomerController;
use App\Http\Controllers\Business\PaymentController;
use App\Http\Controllers\Business\ReturnController;
use App\Http\Controllers\Business\InventoryController;
use App\Http\Controllers\Business\ReportController;
use App\Http\Controllers\Business\SettingController;
use App\Http\Controllers\Business\DashboardController;
*/

/*
|--------------------------------------------------------------------------
| Business Routes
|--------------------------------------------------------------------------
|
| These routes handle all tenant-specific business operations.
| All routes are automatically scoped to the current tenant context.
|
*/

// TODO: Create Business controllers before enabling these routes
/*
Route::prefix('admin/business')->name('admin.business.')->group(function () {
    
    // Business middleware - tenant context required
    Route::middleware(['auth:admin', 'tenant.resolve', 'layout.mode'])->group(function () {
        
        // Business Dashboard (TODO: Create DashboardController)
        // Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        
        // Product Management
        Route::prefix('products')->name('products.')->group(function () {
            Route::get('/', [ProductController::class, 'index'])->name('index');
            Route::get('/create', [ProductController::class, 'create'])->name('create');
            Route::post('/', [ProductController::class, 'store'])->name('store');
            Route::get('/{product}', [ProductController::class, 'show'])->name('show');
            Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('edit');
            Route::put('/{product}', [ProductController::class, 'update'])->name('update');
            Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');
            
            // Product search and utilities
            Route::get('/search/query', [ProductController::class, 'search'])->name('search');
            Route::get('/barcode/{barcode}', [ProductController::class, 'findByBarcode'])->name('barcode');
            Route::post('/bulk-update', [ProductController::class, 'bulkUpdate'])->name('bulk-update');
            
            // Product variants
            Route::prefix('{product}/variants')->name('variants.')->group(function () {
                Route::get('/', [ProductController::class, 'variants'])->name('index');
                Route::post('/', [ProductController::class, 'storeVariant'])->name('store');
                Route::put('/{variant}', [ProductController::class, 'updateVariant'])->name('update');
                Route::delete('/{variant}', [ProductController::class, 'destroyVariant'])->name('destroy');
            });
        });
        
        // Order Management
        Route::prefix('orders')->name('orders.')->group(function () {
            Route::get('/', [OrderController::class, 'index'])->name('index');
            Route::get('/create', [OrderController::class, 'create'])->name('create');
            Route::post('/', [OrderController::class, 'store'])->name('store');
            Route::get('/{order}', [OrderController::class, 'show'])->name('show');
            Route::get('/{order}/edit', [OrderController::class, 'edit'])->name('edit');
            Route::put('/{order}', [OrderController::class, 'update'])->name('update');
            Route::delete('/{order}', [OrderController::class, 'destroy'])->name('destroy');
            
            // Order status management
            Route::post('/{order}/status', [OrderController::class, 'updateStatus'])->name('update-status');
            Route::post('/{order}/cancel', [OrderController::class, 'cancel'])->name('cancel');
            Route::post('/{order}/fulfill', [OrderController::class, 'fulfill'])->name('fulfill');
            
            // Order utilities
            Route::get('/{order}/print', [OrderController::class, 'print'])->name('print');
            Route::get('/{order}/pdf', [OrderController::class, 'pdf'])->name('pdf');
            Route::post('/bulk-update', [OrderController::class, 'bulkUpdate'])->name('bulk-update');
        });
        
        // Invoice Management
        Route::prefix('invoices')->name('invoices.')->group(function () {
            Route::get('/', [InvoiceController::class, 'index'])->name('index');
            Route::get('/create', [InvoiceController::class, 'create'])->name('create');
            Route::post('/', [InvoiceController::class, 'store'])->name('store');
            Route::get('/{invoice}', [InvoiceController::class, 'show'])->name('show');
            Route::get('/{invoice}/edit', [InvoiceController::class, 'edit'])->name('edit');
            Route::put('/{invoice}', [InvoiceController::class, 'update'])->name('update');
            Route::delete('/{invoice}', [InvoiceController::class, 'destroy'])->name('destroy');
            
            // Invoice from order
            Route::post('/from-order/{order}', [InvoiceController::class, 'createFromOrder'])->name('from-order');
            
            // Invoice utilities
            Route::get('/{invoice}/print', [InvoiceController::class, 'print'])->name('print');
            Route::get('/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('pdf');
            Route::post('/{invoice}/send-email', [InvoiceController::class, 'sendEmail'])->name('send-email');
            Route::post('/bulk-update', [InvoiceController::class, 'bulkUpdate'])->name('bulk-update');
        });
        
        // Customer Management
        Route::prefix('customers')->name('customers.')->group(function () {
            Route::get('/', [CustomerController::class, 'index'])->name('index');
            Route::get('/create', [CustomerController::class, 'create'])->name('create');
            Route::post('/', [CustomerController::class, 'store'])->name('store');
            Route::get('/{customer}', [CustomerController::class, 'show'])->name('show');
            Route::get('/{customer}/edit', [CustomerController::class, 'edit'])->name('edit');
            Route::put('/{customer}', [CustomerController::class, 'update'])->name('update');
            Route::delete('/{customer}', [CustomerController::class, 'destroy'])->name('destroy');
            
            // Customer utilities
            Route::get('/search/query', [CustomerController::class, 'search'])->name('search');
            Route::post('/bulk-update', [CustomerController::class, 'bulkUpdate'])->name('bulk-update');
            Route::post('/import', [CustomerController::class, 'import'])->name('import');
            Route::get('/export', [CustomerController::class, 'export'])->name('export');
            
            // Customer orders and invoices
            Route::get('/{customer}/orders', [CustomerController::class, 'orders'])->name('orders');
            Route::get('/{customer}/invoices', [CustomerController::class, 'invoices'])->name('invoices');
            Route::get('/{customer}/payments', [CustomerController::class, 'payments'])->name('payments');
        });
        
        // Payment Management
        Route::prefix('payments')->name('payments.')->group(function () {
            Route::get('/', [PaymentController::class, 'index'])->name('index');
            Route::get('/create', [PaymentController::class, 'create'])->name('create');
            Route::post('/', [PaymentController::class, 'store'])->name('store');
            Route::get('/{payment}', [PaymentController::class, 'show'])->name('show');
            Route::get('/{payment}/edit', [PaymentController::class, 'edit'])->name('edit');
            Route::put('/{payment}', [PaymentController::class, 'update'])->name('update');
            Route::delete('/{payment}', [PaymentController::class, 'destroy'])->name('destroy');
            
            // Payment utilities
            Route::post('/{payment}/verify', [PaymentController::class, 'verify'])->name('verify');
            Route::post('/{payment}/refund', [PaymentController::class, 'refund'])->name('refund');
            Route::get('/{payment}/receipt', [PaymentController::class, 'receipt'])->name('receipt');
        });
        
        // Return Order Management
        Route::prefix('returns')->name('returns.')->group(function () {
            Route::get('/', [ReturnController::class, 'index'])->name('index');
            Route::get('/create', [ReturnController::class, 'create'])->name('create');
            Route::post('/', [ReturnController::class, 'store'])->name('store');
            Route::get('/{return}', [ReturnController::class, 'show'])->name('show');
            Route::get('/{return}/edit', [ReturnController::class, 'edit'])->name('edit');
            Route::put('/{return}', [ReturnController::class, 'update'])->name('update');
            Route::delete('/{return}', [ReturnController::class, 'destroy'])->name('destroy');
            
            // Return from order/invoice
            Route::post('/from-order/{order}', [ReturnController::class, 'createFromOrder'])->name('from-order');
            Route::post('/from-invoice/{invoice}', [ReturnController::class, 'createFromInvoice'])->name('from-invoice');
            
            // Return utilities
            Route::post('/{return}/approve', [ReturnController::class, 'approve'])->name('approve');
            Route::post('/{return}/reject', [ReturnController::class, 'reject'])->name('reject');
            Route::post('/{return}/process', [ReturnController::class, 'process'])->name('process');
        });
        
        // Inventory Management
        Route::prefix('inventory')->name('inventory.')->group(function () {
            Route::get('/', [InventoryController::class, 'index'])->name('index');
            Route::get('/adjustments', [InventoryController::class, 'adjustments'])->name('adjustments');
            Route::post('/adjust', [InventoryController::class, 'adjust'])->name('adjust');
            Route::get('/transfers', [InventoryController::class, 'transfers'])->name('transfers');
            Route::post('/transfer', [InventoryController::class, 'transfer'])->name('transfer');
            
            // Inventory reports
            Route::get('/low-stock', [InventoryController::class, 'lowStock'])->name('low-stock');
            Route::get('/movements', [InventoryController::class, 'movements'])->name('movements');
            Route::get('/valuation', [InventoryController::class, 'valuation'])->name('valuation');
        });
        
        // Reports
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/sales', [ReportController::class, 'sales'])->name('sales');
            Route::get('/products', [ReportController::class, 'products'])->name('products');
            Route::get('/customers', [ReportController::class, 'customers'])->name('customers');
            Route::get('/inventory', [ReportController::class, 'inventory'])->name('inventory');
            Route::get('/financial', [ReportController::class, 'financial'])->name('financial');
            Route::get('/tax', [ReportController::class, 'tax'])->name('tax');
            
            // Report exports
            Route::get('/sales/export', [ReportController::class, 'exportSales'])->name('sales.export');
            Route::get('/products/export', [ReportController::class, 'exportProducts'])->name('products.export');
            Route::get('/customers/export', [ReportController::class, 'exportCustomers'])->name('customers.export');
        });
        
        // Business Settings
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/general', [SettingController::class, 'general'])->name('general');
            Route::post('/general', [SettingController::class, 'updateGeneral'])->name('general.update');
            
            Route::get('/business', [SettingController::class, 'business'])->name('business');
            Route::post('/business', [SettingController::class, 'updateBusiness'])->name('business.update');
            
            Route::get('/payment', [SettingController::class, 'payment'])->name('payment');
            Route::post('/payment', [SettingController::class, 'updatePayment'])->name('payment.update');
            
            Route::get('/notification', [SettingController::class, 'notification'])->name('notification');
            Route::post('/notification', [SettingController::class, 'updateNotification'])->name('notification.update');
            
            Route::get('/integration', [SettingController::class, 'integration'])->name('integration');
            Route::post('/integration', [SettingController::class, 'updateIntegration'])->name('integration.update');
        });
        
    });
    
});

// Business API Routes
Route::prefix('admin/business/api')->name('admin.business.api.')->group(function () {
    
    Route::middleware(['auth:admin', 'tenant.resolve'])->group(function () {
        
        // Product API
        Route::prefix('products')->name('products.')->group(function () {
            Route::get('/', [ProductController::class, 'apiIndex'])->name('index');
            Route::get('/search', [ProductController::class, 'apiSearch'])->name('search');
            Route::get('/{product}', [ProductController::class, 'apiShow'])->name('show');
        });
        
        // Order API
        Route::prefix('orders')->name('orders.')->group(function () {
            Route::get('/', [OrderController::class, 'apiIndex'])->name('index');
            Route::get('/{order}', [OrderController::class, 'apiShow'])->name('show');
            Route::post('/', [OrderController::class, 'apiStore'])->name('store');
            Route::put('/{order}', [OrderController::class, 'apiUpdate'])->name('update');
        });
        
        // Customer API
        Route::prefix('customers')->name('customers.')->group(function () {
            Route::get('/', [CustomerController::class, 'apiIndex'])->name('index');
            Route::get('/search', [CustomerController::class, 'apiSearch'])->name('search');
            Route::get('/{customer}', [CustomerController::class, 'apiShow'])->name('show');
        });
        
        // Invoice API
        Route::prefix('invoices')->name('invoices.')->group(function () {
            Route::get('/', [InvoiceController::class, 'apiIndex'])->name('index');
            Route::get('/{invoice}', [InvoiceController::class, 'apiShow'])->name('show');
        });
        
        // Payment API
        Route::prefix('payments')->name('payments.')->group(function () {
            Route::get('/', [PaymentController::class, 'apiIndex'])->name('index');
            Route::get('/{payment}', [PaymentController::class, 'apiShow'])->name('show');
        });
        
        // Dashboard API (TODO: Create DashboardController)
        // Route::get('/dashboard/stats', [DashboardController::class, 'apiStats'])->name('dashboard.stats');
        // Route::get('/dashboard/charts', [DashboardController::class, 'apiCharts'])->name('dashboard.charts');
        
    });

});
*/
