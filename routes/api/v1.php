<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\InvoiceController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\PlaygroundController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\DocumentationController;

/*
|--------------------------------------------------------------------------
| API V1 Routes
|--------------------------------------------------------------------------
|
| RESTful API routes for YukiMart mobile application
| Version: 1.0
| Base URL: /api/v1/
|
*/

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::prefix('auth')
    ->middleware(['throttle:auth'])
    ->group(function () {
        // Public authentication routes
        Route::post('/login', [AuthController::class, 'login'])->name('api.v1.auth.login');
        Route::post('/register', [AuthController::class, 'register'])->name('api.v1.auth.register');
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('api.v1.auth.forgot-password');
        Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('api.v1.auth.reset-password');
        
        // Protected authentication routes
        Route::middleware(['api.auth'])->group(function () {
            Route::post('/logout', [AuthController::class, 'logout'])->name('api.v1.auth.logout');
            Route::post('/refresh', [AuthController::class, 'refresh'])->name('api.v1.auth.refresh');
            Route::get('/me', [AuthController::class, 'me'])->name('api.v1.auth.me');
            Route::put('/profile', [AuthController::class, 'updateProfile'])->name('api.v1.auth.profile');
            Route::post('/change-password', [AuthController::class, 'changePassword'])->name('api.v1.auth.change-password');
        });
    });

/*
|--------------------------------------------------------------------------
| Protected API Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['api.auth'])->group(function () {
    
    /*
    |--------------------------------------------------------------------------
    | User Management
    |--------------------------------------------------------------------------
    */
    Route::prefix('user')->group(function () {
        Route::get('/profile', [UserController::class, 'profile'])->name('api.v1.user.profile');
        Route::put('/profile', [UserController::class, 'updateProfile'])->name('api.v1.user.update-profile');
        Route::get('/permissions', [UserController::class, 'permissions'])->name('api.v1.user.permissions');
        Route::get('/branches', [UserController::class, 'branches'])->name('api.v1.user.branches');
    });

    /*
    |--------------------------------------------------------------------------
    | Invoice Management
    |--------------------------------------------------------------------------
    */
    Route::apiResource('invoices', InvoiceController::class)->names([
        'index' => 'api.v1.invoices.index',
        'store' => 'api.v1.invoices.store',
        'show' => 'api.v1.invoices.show',
        'update' => 'api.v1.invoices.update',
        'destroy' => 'api.v1.invoices.destroy',
    ]);
    
    // Additional invoice routes
    Route::prefix('invoices')->group(function () {
        Route::get('/{invoice}/items', [InvoiceController::class, 'items'])->name('api.v1.invoices.items');
        Route::post('/{invoice}/payment', [InvoiceController::class, 'processPayment'])->name('api.v1.invoices.payment');
        Route::put('/{invoice}/status', [InvoiceController::class, 'updateStatus'])->name('api.v1.invoices.status');
        Route::get('/{invoice}/pdf', [InvoiceController::class, 'generatePdf'])->name('api.v1.invoices.pdf');
        Route::post('/bulk-update', [InvoiceController::class, 'bulkUpdate'])->name('api.v1.invoices.bulk-update');
    });

    /*
    |--------------------------------------------------------------------------
    | Order Management
    |--------------------------------------------------------------------------
    */
    Route::apiResource('orders', OrderController::class)->names([
        'index' => 'api.v1.orders.index',
        'store' => 'api.v1.orders.store',
        'show' => 'api.v1.orders.show',
        'update' => 'api.v1.orders.update',
        'destroy' => 'api.v1.orders.destroy',
    ]);

    // Additional order routes
    Route::prefix('orders')->group(function () {
        Route::get('/{order}/items', [OrderController::class, 'items'])->name('api.v1.orders.items');
        Route::put('/{order}/status', [OrderController::class, 'updateStatus'])->name('api.v1.orders.update-status');
        Route::post('/{order}/payment', [OrderController::class, 'recordPayment'])->name('api.v1.orders.record-payment');
    });

    /*
    |--------------------------------------------------------------------------
    | Product Management
    |--------------------------------------------------------------------------
    */
    Route::apiResource('products', ProductController::class)->names([
        'index' => 'api.v1.products.index',
        'store' => 'api.v1.products.store',
        'show' => 'api.v1.products.show',
        'update' => 'api.v1.products.update',
        'destroy' => 'api.v1.products.destroy',
    ]);

    // Additional product routes
    Route::prefix('products')->group(function () {
        Route::get('/search', [ProductController::class, 'search'])->name('api.v1.products.search');
        Route::get('/barcode/{barcode}', [ProductController::class, 'findByBarcode'])->name('api.v1.products.barcode');
        Route::get('/{product}/variants', [ProductController::class, 'variants'])->name('api.v1.products.variants');
        Route::get('/{product}/inventory', [ProductController::class, 'inventory'])->name('api.v1.products.inventory');
    });

    /*
    |--------------------------------------------------------------------------
    | Customer Management
    |--------------------------------------------------------------------------
    */
    Route::apiResource('customers', CustomerController::class)->names([
        'index' => 'api.v1.customers.index',
        'store' => 'api.v1.customers.store',
        'show' => 'api.v1.customers.show',
        'update' => 'api.v1.customers.update',
        'destroy' => 'api.v1.customers.destroy',
    ]);

    // Additional customer routes
    Route::prefix('customers')->group(function () {
        Route::get('/search', [CustomerController::class, 'search'])->name('api.v1.customers.search');
        Route::get('/{customer}/orders', [CustomerController::class, 'orders'])->name('api.v1.customers.orders');
        Route::get('/{customer}/invoices', [CustomerController::class, 'invoices'])->name('api.v1.customers.invoices');
        Route::get('/{customer}/payments', [CustomerController::class, 'payments'])->name('api.v1.customers.payments');
    });

    /*
    |--------------------------------------------------------------------------
    | Payment Management
    |--------------------------------------------------------------------------
    */
    Route::apiResource('payments', PaymentController::class)->names([
        'index' => 'api.v1.payments.index',
        'store' => 'api.v1.payments.store',
        'show' => 'api.v1.payments.show',
        'update' => 'api.v1.payments.update',
        'destroy' => 'api.v1.payments.destroy',
    ]);

    // Additional payment routes
    Route::prefix('payments')->group(function () {
        Route::get('/summary', [PaymentController::class, 'summary'])->name('api.v1.payments.summary');
        Route::post('/bulk-create', [PaymentController::class, 'bulkCreate'])->name('api.v1.payments.bulk-create');
        Route::post('/{payment}/approve', [PaymentController::class, 'approve'])->name('api.v1.payments.approve');
    });

    /*
    |--------------------------------------------------------------------------
    | Utility Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('utils')->group(function () {
        Route::get('/branches', function () {
            return response()->json([
                'success' => true,
                'data' => \App\Models\BranchShop::select('id', 'name', 'address')->get()
            ]);
        })->name('api.v1.utils.branches');
        
        Route::get('/payment-methods', function () {
            return response()->json([
                'success' => true,
                'data' => [
                    ['id' => 'cash', 'name' => 'Tiền mặt'],
                    ['id' => 'card', 'name' => 'Thẻ'],
                    ['id' => 'transfer', 'name' => 'Chuyển khoản'],
                    ['id' => 'check', 'name' => 'Séc'],
                    ['id' => 'points', 'name' => 'Điểm thưởng'],
                    ['id' => 'other', 'name' => 'Khác'],
                ]
            ]);
        })->name('api.v1.utils.payment-methods');
    });
});

/*
|--------------------------------------------------------------------------
| Documentation Routes
|--------------------------------------------------------------------------
*/
Route::prefix('docs')->group(function () {
    Route::get('/openapi', [DocumentationController::class, 'openapi'])->name('api.v1.docs.openapi');
    Route::get('/openapi/download', [DocumentationController::class, 'downloadOpenApi'])->name('api.v1.docs.download');
    Route::post('/postman/sync', [DocumentationController::class, 'syncPostman'])->name('api.v1.docs.postman.sync');
    Route::get('/info', [DocumentationController::class, 'info'])->name('api.v1.docs.info');
});

// Interactive API Documentation (Swagger UI)
Route::get('/docs', function () {
    return view('api.swagger');
})->name('api.v1.docs');

// Standalone API Playground
Route::get('/playground', function () {
    return view('api.playground');
})->name('api.v1.playground');

/*
|--------------------------------------------------------------------------
| API Playground Routes
|--------------------------------------------------------------------------
*/
Route::prefix('playground')->group(function () {
    Route::post('/execute', [PlaygroundController::class, 'executeRequest'])->name('api.v1.playground.execute');
    Route::post('/generate-code', [PlaygroundController::class, 'generateCode'])->name('api.v1.playground.generate-code');
    Route::post('/auth', [PlaygroundController::class, 'getAuthToken'])->name('api.v1.playground.auth');
    Route::get('/stats', [PlaygroundController::class, 'getStatistics'])->name('api.v1.playground.stats');
    Route::post('/validate', [PlaygroundController::class, 'validateEndpoint'])->name('api.v1.playground.validate');
});

/*
|--------------------------------------------------------------------------
| Health Check & Status Routes
|--------------------------------------------------------------------------
*/
Route::get('/health', [DocumentationController::class, 'health'])->name('api.v1.health');

Route::get('/status', function () {
    return response()->json([
        'success' => true,
        'message' => 'API status check',
        'data' => [
            'version' => 'v1',
            'uptime' => now()->diffInSeconds(app()->make('startTime', now())),
            'memory_usage' => memory_get_usage(true),
            'database' => 'connected', // Could add actual DB check
        ]
    ]);
})->name('api.v1.status');
