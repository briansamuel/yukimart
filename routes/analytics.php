<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'tenant.resolve'])
    ->prefix('admin/analytics')
    ->name('admin.analytics.')
    ->group(function () {
        
        // Business Analytics
        Route::prefix('business')->name('business.')->group(function () {
            Route::get('/overview', [
                \App\Http\Controllers\Tenant\Modules\Analytics\Business\OverviewController::class,
                'index'
            ])->name('overview');

            Route::get('/expense-profit', [
                \App\Http\Controllers\Tenant\Modules\Analytics\Business\ExpenseProfitController::class,
                'index'
            ])->name('expense-profit');
        });

        // Product Analytics
        Route::prefix('product')->name('product.')->group(function () {
            Route::get('/overview', [
                \App\Http\Controllers\Tenant\Modules\Analytics\Product\OverviewController::class,
                'index'
            ])->name('overview');

            Route::get('/inventory', [
                \App\Http\Controllers\Tenant\Modules\Analytics\Product\InventoryController::class,
                'index'
            ])->name('inventory');
        });

        // Customer Analytics
        Route::prefix('customer')->name('customer.')->group(function () {
            Route::get('/overview', [
                \App\Http\Controllers\Tenant\Modules\Analytics\Customer\OverviewController::class,
                'index'
            ])->name('overview');
        });

        // Performance Analytics
        Route::prefix('performance')->name('performance.')->group(function () {
            Route::get('/overview', [
                \App\Http\Controllers\Tenant\Modules\Analytics\Performance\OverviewController::class,
                'index'
            ])->name('overview');
        });

        // Accounts Receivable Analytics
        Route::prefix('accounts-receivable')->name('accounts-receivable.')->group(function () {
            Route::get('/overview', [
                \App\Http\Controllers\Tenant\Modules\Analytics\AccountsReceivable\OverviewController::class,
                'index'
            ])->name('overview');
        });
    });

