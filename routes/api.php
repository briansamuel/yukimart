<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductBarcodeController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\InvoiceController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\DashboardController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// API v1 Routes
Route::prefix('v1')->group(function () {

    // Health Check (no auth required)
    Route::get('/health', [HealthController::class, 'index'])->name('api.v1.health');

    // Authentication Routes
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login'])->name('api.v1.auth.login');
        Route::post('/register', [AuthController::class, 'register'])->name('api.v1.auth.register');

        // Protected auth routes
        Route::middleware(['api.token'])->group(function () {
            Route::get('/profile', [AuthController::class, 'profile'])->name('api.v1.auth.profile');
            Route::post('/logout', [AuthController::class, 'logout'])->name('api.v1.auth.logout');
            Route::post('/refresh', [AuthController::class, 'refresh'])->name('api.v1.auth.refresh');
        });
    });

    // Protected API routes
    Route::middleware(['api.token'])->group(function () {
        Route::get('/user', function (Request $request) {
            return response()->json([
                'status' => 'success',
                'data' => $request->user()
            ]);
        })->name('api.v1.user');

        // Invoice Management Routes
        Route::prefix('invoices')->group(function () {
            Route::get('/', [InvoiceController::class, 'index'])->name('api.v1.invoices.index');
            Route::post('/', [InvoiceController::class, 'store'])->name('api.v1.invoices.store');
            Route::get('/statistics', [InvoiceController::class, 'statistics'])->name('api.v1.invoices.statistics');
            Route::get('/{id}', [InvoiceController::class, 'show'])->name('api.v1.invoices.show');
            Route::put('/{id}', [InvoiceController::class, 'update'])->name('api.v1.invoices.update');
            Route::delete('/{id}', [InvoiceController::class, 'destroy'])->name('api.v1.invoices.destroy');
        });

        // Customer Management Routes
        Route::prefix('customers')->group(function () {
            Route::get('/', [CustomerController::class, 'index'])->name('api.v1.customers.index');
            Route::post('/', [CustomerController::class, 'store'])->name('api.v1.customers.store');
            Route::get('/statistics', [CustomerController::class, 'statistics'])->name('api.v1.customers.statistics');
            Route::get('/{id}', [CustomerController::class, 'show'])->name('api.v1.customers.show');
            Route::put('/{id}', [CustomerController::class, 'update'])->name('api.v1.customers.update');
            Route::delete('/{id}', [CustomerController::class, 'destroy'])->name('api.v1.customers.destroy');
        });

        // Product Management Routes
        Route::prefix('products')->group(function () {
            Route::get('/', [ProductController::class, 'index'])->name('api.v1.products.index');
            Route::post('/', [ProductController::class, 'store'])->name('api.v1.products.store');
            Route::get('/search-barcode', [ProductController::class, 'searchByBarcode'])->name('api.v1.products.search-barcode');
            Route::get('/{id}', [ProductController::class, 'show'])->name('api.v1.products.show');
            Route::put('/{id}', [ProductController::class, 'update'])->name('api.v1.products.update');
            Route::delete('/{id}', [ProductController::class, 'destroy'])->name('api.v1.products.destroy');
        });

        // Order Management Routes
        Route::prefix('orders')->group(function () {
            Route::get('/', [OrderController::class, 'index'])->name('api.v1.orders.index');
            Route::post('/', [OrderController::class, 'store'])->name('api.v1.orders.store');
            Route::get('/{id}', [OrderController::class, 'show'])->name('api.v1.orders.show');
            Route::put('/{id}', [OrderController::class, 'update'])->name('api.v1.orders.update');
            Route::delete('/{id}', [OrderController::class, 'destroy'])->name('api.v1.orders.destroy');
        });

        // Payment Management Routes
        Route::prefix('payments')->group(function () {
            Route::get('/', [PaymentController::class, 'index'])->name('api.v1.payments.index');
            Route::post('/', [PaymentController::class, 'store'])->name('api.v1.payments.store');
            Route::get('/statistics', [PaymentController::class, 'statistics'])->name('api.v1.payments.statistics');
            Route::get('/{id}', [PaymentController::class, 'show'])->name('api.v1.payments.show');
            Route::put('/{id}', [PaymentController::class, 'update'])->name('api.v1.payments.update');
            Route::delete('/{id}', [PaymentController::class, 'destroy'])->name('api.v1.payments.destroy');
        });

        // Dashboard Routes
        Route::prefix('dashboard')->group(function () {
            Route::get('/', [DashboardController::class, 'index'])->name('api.v1.dashboard.index');
            Route::get('/stats', [DashboardController::class, 'getStats'])->name('api.v1.dashboard.stats');
            Route::get('/recent-orders', [DashboardController::class, 'getRecentOrders'])->name('api.v1.dashboard.recent-orders');
            Route::get('/top-products', [DashboardController::class, 'getTopProducts'])->name('api.v1.dashboard.top-products');
            Route::get('/revenue-data', [DashboardController::class, 'getRevenueData'])->name('api.v1.dashboard.revenue-data');
            Route::get('/top-products-data', [DashboardController::class, 'getTopProductsData'])->name('api.v1.dashboard.top-products-data');
            Route::get('/recent-activities', [DashboardController::class, 'getRecentActivities'])->name('api.v1.dashboard.recent-activities');
            Route::get('/low-stock-products', [DashboardController::class, 'getLowStockProducts'])->name('api.v1.dashboard.low-stock-products');
        });

        // Notifications
        Route::prefix('notifications')->group(function () {
            Route::get('/', [NotificationController::class, 'index'])->name('api.v1.notifications.index');
            Route::get('/statistics', [NotificationController::class, 'statistics'])->name('api.v1.notifications.statistics');
            Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('api.v1.notifications.mark_all_read');
            Route::get('/{id}', [NotificationController::class, 'show'])->name('api.v1.notifications.show');
            Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('api.v1.notifications.mark_read');
            Route::post('/{id}/unread', [NotificationController::class, 'markAsUnread'])->name('api.v1.notifications.mark_unread');
            Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('api.v1.notifications.destroy');
            Route::post('/', [NotificationController::class, 'store'])->name('api.v1.notifications.store'); // Admin only
        });
    });
});

// Legacy Product Barcode API Routes (for internal use - no auth required)
Route::prefix('products')->group(function () {
    Route::get('/barcode/{barcode}', [ProductBarcodeController::class, 'findByBarcode'])->name('api.products.barcode');
    Route::get('/search', [ProductBarcodeController::class, 'search'])->name('api.products.search');
    Route::post('/barcode/validate', [ProductBarcodeController::class, 'validateBarcode'])->name('api.products.barcode.validate');
});

// Public API routes (if needed for external integrations)
Route::prefix('public')->group(function () {
    // Add public API routes here if needed
});
