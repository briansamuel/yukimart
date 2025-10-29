<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Platform\AuthController;

/*
|--------------------------------------------------------------------------
| Platform Management Routes
|--------------------------------------------------------------------------
|
| Platform routes are only accessible via platform.yukimart.local domain
| Platform users have tenant_id = NULL and are completely separated from tenants.
|
*/

// Platform domain routes (platform.yukimart.local/login)
Route::domain(config('tenancy.platform_host', 'platform.yukimart.local'))->group(function () {

    // Debug route
    Route::get('/debug', function () {
        $isAuthenticated = Auth::guard('platform')->check();
        $user = Auth::guard('platform')->user();

        return response()->json([
            'authenticated' => $isAuthenticated,
            'user' => $user ? $user->email : null,
            'routes' => [
                'login' => route('platform.login.show'),
                'dashboard' => route('platform.admin.dashboard'),
            ]
        ]);
    })->name('platform.debug');

    // Test dashboard without middleware
    Route::get('/test-dashboard', function () {
        $user = Auth::guard('platform')->user();
        if (!$user) {
            return 'Not authenticated';
        }
        return "Platform Dashboard Working! Welcome " . $user->email;
    })->name('platform.test.dashboard');

    // Logout route without middleware
    Route::get('/test-logout', function () {
        Auth::guard('platform')->logout();
        session()->flush();
        return 'Logged out successfully. <a href="/login">Login again</a>';
    })->name('platform.test.logout');

    // Test login route without middleware
    Route::get('/test-login', function () {
        $credentials = ['email' => 'superadmin@yukimart.local', 'password' => '123456'];

        if (Auth::guard('platform')->attempt($credentials)) {
            $user = Auth::guard('platform')->user();
            return 'Login successful! User: ' . $user->email . ' <a href="/test-dashboard">Test Dashboard</a>';
        }

        return 'Login failed!';
    })->name('platform.test.login');

    // Root redirect
    Route::get('/', function () {
        if (Auth::guard('platform')->check()) {
            return redirect()->route('platform.admin.dashboard');
        }
        return redirect()->route('platform.login.show');
    })->name('platform.root');

    // Platform login routes
    Route::middleware(['guest:platform'])->group(function () {
        Route::get('/login', [AuthController::class, 'showLoginForm'])->name('platform.login.show');
        Route::post('/login', [AuthController::class, 'login'])->name('platform.login');
    });

    // Admin routes (with /admin prefix)
    Route::prefix('admin')->middleware(['auth:platform'])->group(function () {
        Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('platform.admin.dashboard');
        Route::post('/logout', [AuthController::class, 'logout'])->name('platform.admin.logout');

        // Simple dashboard for testing
        Route::get('/simple', function () {
            $user = Auth::guard('platform')->user();
            return "Platform Admin Dashboard - Welcome " . ($user ? $user->email : 'Guest');
        })->name('platform.admin.simple');

        // Platform management features (future)
        // Route::get('/tenants', [TenantController::class, 'index'])->name('platform.admin.tenants.index');
        // Route::get('/users', [UserController::class, 'index'])->name('platform.admin.users.index');
    });
});

/*
// COMMENTED OUT - Old routes for reference
// Main domain routes (yukimart.local) - DISABLED
Route::domain(config('tenancy.app_domain', 'yukimart.local'))->group(function () {
    Route::prefix('admin')->group(function () {
        Route::middleware(['guest:platform'])->group(function () {
            Route::get('/login', [AuthController::class, 'showLoginForm'])->name('admin.login');
            Route::post('/login', [AuthController::class, 'login'])->name('admin.login.post');
        });

        Route::middleware(['auth:platform'])->group(function () {
            Route::post('/logout', [AuthController::class, 'logout'])->name('admin.logout');
            Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('admin.dashboard');
            Route::get('/', [AuthController::class, 'dashboard'])->name('admin.home');
        });
    });
});
*/
