<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/admin/dashboard';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        // Register explicit route model bindings to avoid conflicts with {tenant} subdomain parameter
        $this->registerModelBindings();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            // Load platform routes FIRST to avoid subdomain conflicts
            Route::middleware('web')
                ->group(base_path('routes/platform.php'));

            // Load tenant routes (handles subdomain routing) with web middleware
            Route::middleware('web')
                ->group(base_path('routes/tenant.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            Route::middleware('web')->namespace('Admin')
                ->group(base_path('routes/admin.php'));

            Route::middleware('web')
                ->group(base_path('routes/business.php'));
        });
    }

    /**
     * Register explicit route model bindings.
     *
     * NOTE: Route model binding is NOT used for tenant-scoped models like Product
     * because binding happens BEFORE middleware execution, so tenant context is not available yet.
     * Instead, we manually query products in controller methods using tenant scope.
     *
     * @return void
     */
    protected function registerModelBindings()
    {
        // No bindings for tenant-scoped models
        // Products are manually queried in controllers after tenant middleware runs
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        // General API rate limiting
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
        });

        // Authentication endpoints - more restrictive
        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        // Mobile API rate limiting - higher limits for authenticated users
        RateLimiter::for('mobile-api', function (Request $request) {
            if ($request->user()) {
                return Limit::perMinute(120)->by($request->user()->id);
            }
            return Limit::perMinute(30)->by($request->ip());
        });
    }
}
