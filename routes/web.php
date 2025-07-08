<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\LoginController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Laravel File Manager Routes


Route::get('/', function () {
    return view('welcome');
});

// Language change route
Route::get('/change-language/{language}', function ($language) {
    if (in_array($language, ['en', 'vi'])) {
        session(['locale' => $language]);
        app()->setLocale($language);
    }
    return redirect()->back();
})->name('change-language');

// Admin routes
Route::prefix('admin')->group(function () {
    // Login routes
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [LoginController::class, 'login'])->name('admin.login.post');
    Route::post('/logout', [LoginController::class, 'logout'])->name('admin.logout');

    // Protected admin routes
    Route::middleware(['auth'])->group(function () {
        Route::get('/', function () {
            return view('admin.dashboard');
        })->name('admin.dashboard');

        // Dashboard API routes
        Route::get('/dashboard/stats', [\App\Http\Controllers\Admin\DashboardController::class, 'getStats'])->name('admin.dashboard.stats');
        Route::get('/dashboard/recent-orders', [\App\Http\Controllers\Admin\DashboardController::class, 'getRecentOrders'])->name('admin.dashboard.recent-orders');
        Route::get('/dashboard/top-products', [\App\Http\Controllers\Admin\DashboardController::class, 'getTopProducts'])->name('admin.dashboard.top-products');

    });
});


