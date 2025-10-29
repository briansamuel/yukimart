<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use App\Models\Tenant;
use App\Models\Product;
use App\Models\Order;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Supplier;
use App\Services\TenantContextService;

class TenantRouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     */
    public const HOME = '/dashboard';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();
        $this->configureTenantRouteBindings();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            // Tenant-specific routes (loaded directly, handles its own subdomain routing)
            require base_path('routes/tenant.php');

            // Old tenant routes with prefix (DISABLED - tenant.php now handles subdomain routing)
            // Route::middleware(['web', 'tenant.resolve'])
            //     ->prefix('{tenant}')
            //     ->group(base_path('routes/tenant.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('tenant-api', function (Request $request) {
            $tenant = app(TenantContextService::class)->getCurrentTenant();
            $rateLimit = $tenant?->api_rate_limit ?? 1000;
            
            return Limit::perMinute($rateLimit)->by(
                $tenant?->id . ':' . ($request->user()?->id ?: $request->ip())
            );
        });
    }

    /**
     * Configure tenant-aware route model bindings
     */
    protected function configureTenantRouteBindings(): void
    {
        // Tenant binding
        Route::bind('tenant', function (string $value) {
            return Tenant::where('slug', $value)
                        ->active()
                        ->firstOrFail();
        });

        // Tenant-scoped model bindings
        $this->bindTenantScopedModel('product', Product::class);
        $this->bindTenantScopedModel('order', Order::class);
        $this->bindTenantScopedModel('invoice', Invoice::class);
        $this->bindTenantScopedModel('customer', Customer::class);
        $this->bindTenantScopedModel('supplier', Supplier::class);

        // Custom bindings for specific use cases
        $this->bindCustomTenantModels();
    }

    /**
     * Bind tenant-scoped model
     */
    protected function bindTenantScopedModel(string $key, string $modelClass): void
    {
        Route::bind($key, function (string $value) use ($modelClass) {
            $tenantContext = app(TenantContextService::class);
            $tenant = $tenantContext->getCurrentTenant();

            if (!$tenant) {
                abort(404, 'Tenant context not found');
            }

            // Try to find by ID first
            if (is_numeric($value)) {
                $model = $modelClass::where('id', $value)
                                  ->where('tenant_id', $tenant->id)
                                  ->first();
                
                if ($model) {
                    return $model;
                }
            }

            // Try to find by slug if model has slug field
            if (method_exists($modelClass, 'getRouteKeyName')) {
                $routeKey = (new $modelClass)->getRouteKeyName();
                
                $model = $modelClass::where($routeKey, $value)
                                  ->where('tenant_id', $tenant->id)
                                  ->first();
                
                if ($model) {
                    return $model;
                }
            }

            abort(404, "Model not found in current tenant");
        });
    }

    /**
     * Bind custom tenant models with special logic
     */
    protected function bindCustomTenantModels(): void
    {
        // Product with SKU support
        Route::bind('product_sku', function (string $value) {
            $tenantContext = app(TenantContextService::class);
            $tenant = $tenantContext->getCurrentTenant();

            if (!$tenant) {
                abort(404, 'Tenant context not found');
            }

            $product = Product::where('tenant_id', $tenant->id)
                             ->where(function ($query) use ($value) {
                                 $query->where('sku', $value)
                                       ->orWhere('barcode', $value)
                                       ->orWhere('id', $value);
                             })
                             ->first();

            if (!$product) {
                abort(404, 'Product not found');
            }

            return $product;
        });

        // Order with multiple identifier support
        Route::bind('order_ref', function (string $value) {
            $tenantContext = app(TenantContextService::class);
            $tenant = $tenantContext->getCurrentTenant();

            if (!$tenant) {
                abort(404, 'Tenant context not found');
            }

            $order = Order::where('tenant_id', $tenant->id)
                         ->where(function ($query) use ($value) {
                             $query->where('order_number', $value)
                                   ->orWhere('reference', $value)
                                   ->orWhere('id', $value);
                         })
                         ->first();

            if (!$order) {
                abort(404, 'Order not found');
            }

            return $order;
        });

        // Invoice with number support
        Route::bind('invoice_number', function (string $value) {
            $tenantContext = app(TenantContextService::class);
            $tenant = $tenantContext->getCurrentTenant();

            if (!$tenant) {
                abort(404, 'Tenant context not found');
            }

            $invoice = Invoice::where('tenant_id', $tenant->id)
                             ->where(function ($query) use ($value) {
                                 $query->where('invoice_number', $value)
                                       ->orWhere('id', $value);
                             })
                             ->first();

            if (!$invoice) {
                abort(404, 'Invoice not found');
            }

            return $invoice;
        });

        // Customer with multiple identifier support
        Route::bind('customer_ref', function (string $value) {
            $tenantContext = app(TenantContextService::class);
            $tenant = $tenantContext->getCurrentTenant();

            if (!$tenant) {
                abort(404, 'Tenant context not found');
            }

            $customer = Customer::where('tenant_id', $tenant->id)
                              ->where(function ($query) use ($value) {
                                  $query->where('customer_code', $value)
                                        ->orWhere('phone', $value)
                                        ->orWhere('email', $value)
                                        ->orWhere('id', $value);
                              })
                              ->first();

            if (!$customer) {
                abort(404, 'Customer not found');
            }

            return $customer;
        });
    }

    /**
     * Get the route key for the given model
     */
    protected function getRouteKey($model): string
    {
        if (method_exists($model, 'getRouteKeyName')) {
            return $model->getRouteKeyName();
        }

        return 'id';
    }

    /**
     * Register tenant-aware route patterns
     */
    public function registerTenantRoutePatterns(): void
    {
        Route::pattern('tenant', '[a-z0-9\-]+');
        Route::pattern('product', '[0-9]+');
        Route::pattern('order', '[0-9]+');
        Route::pattern('invoice', '[0-9]+');
        Route::pattern('customer', '[0-9]+');
        Route::pattern('supplier', '[0-9]+');
        Route::pattern('product_sku', '[a-zA-Z0-9\-_]+');
        Route::pattern('order_ref', '[a-zA-Z0-9\-_]+');
        Route::pattern('invoice_number', '[a-zA-Z0-9\-_]+');
        Route::pattern('customer_ref', '[a-zA-Z0-9\-_@.]+');
    }

    /**
     * Register tenant route macros
     */
    public function registerTenantRouteMacros(): void
    {
        // Macro for tenant-scoped routes
        Route::macro('tenantScoped', function (string $prefix, \Closure $routes) {
            Route::prefix($prefix)
                 ->middleware(['tenant.resolve', 'tenant.auth'])
                 ->group($routes);
        });

        // Macro for API tenant routes
        Route::macro('tenantApi', function (string $prefix, \Closure $routes) {
            Route::prefix('api/' . $prefix)
                 ->middleware(['api', 'tenant.resolve', 'tenant.auth'])
                 ->group($routes);
        });

        // Macro for admin tenant routes
        Route::macro('tenantAdmin', function (string $prefix, \Closure $routes) {
            Route::prefix($prefix)
                 ->middleware(['tenant.resolve', 'tenant.auth:users.manage'])
                 ->group($routes);
        });
    }
}
