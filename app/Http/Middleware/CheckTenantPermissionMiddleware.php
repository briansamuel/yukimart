<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\TenantContextService;
use Symfony\Component\HttpFoundation\Response;

class CheckTenantPermissionMiddleware
{
    protected $tenantContext;

    public function __construct(TenantContextService $tenantContext)
    {
        $this->tenantContext = $tenantContext;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $permission = null): Response
    {
        $user = auth('tenant')->user();

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }
            return redirect()->route('tenant.login.show');
        }

        // If no specific permission provided, try to determine from route
        if (!$permission) {
            $permission = $this->determinePermissionFromRoute($request);
        }

        // If still no permission, allow access (for general pages)
        if (!$permission) {
            return $next($request);
        }

        // Check permission
        if (!$this->tenantContext->userCanPerformAction($permission, $user)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không có quyền thực hiện hành động này.'
                ], 403);
            }
            
            abort(403, 'Không có quyền truy cập trang này.');
        }

        return $next($request);
    }

    /**
     * Determine permission from route
     */
    protected function determinePermissionFromRoute(Request $request): ?string
    {
        $route = $request->route();
        if (!$route) {
            return null;
        }

        $routeName = $route->getName();
        $method = $request->method();
        
        // Map route patterns to permissions
        $routePermissionMap = [
            // Products
            'admin.products.index' => 'products.view',
            'admin.products.create' => 'products.create',
            'admin.products.store' => 'products.create',
            'admin.products.show' => 'products.view',
            'admin.products.edit' => 'products.edit',
            'admin.products.update' => 'products.edit',
            'admin.products.destroy' => 'products.delete',
            
            // Orders
            'admin.orders.index' => 'orders.view',
            'admin.orders.create' => 'orders.create',
            'admin.orders.store' => 'orders.create',
            'admin.orders.show' => 'orders.view',
            'admin.orders.edit' => 'orders.edit',
            'admin.orders.update' => 'orders.edit',
            'admin.orders.destroy' => 'orders.delete',
            
            // Invoices
            'admin.invoices.index' => 'invoices.view',
            'admin.invoices.create' => 'invoices.create',
            'admin.invoices.store' => 'invoices.create',
            'admin.invoices.show' => 'invoices.view',
            'admin.invoices.edit' => 'invoices.edit',
            'admin.invoices.update' => 'invoices.edit',
            'admin.invoices.destroy' => 'invoices.delete',
            
            // Returns
            'admin.returns.index' => 'returns.view',
            'admin.returns.create' => 'returns.create',
            'admin.returns.store' => 'returns.create',
            'admin.returns.show' => 'returns.view',
            'admin.returns.edit' => 'returns.edit',
            'admin.returns.update' => 'returns.edit',
            'admin.returns.destroy' => 'returns.delete',
            
            // Payments
            'admin.payments.index' => 'payments.view',
            'admin.payments.create' => 'payments.create',
            'admin.payments.store' => 'payments.create',
            'admin.payments.show' => 'payments.view',
            'admin.payments.edit' => 'payments.edit',
            'admin.payments.update' => 'payments.edit',
            'admin.payments.destroy' => 'payments.delete',
            
            // Customers
            'admin.customers.index' => 'customers.view',
            'admin.customers.create' => 'customers.create',
            'admin.customers.store' => 'customers.create',
            'admin.customers.show' => 'customers.view',
            'admin.customers.edit' => 'customers.edit',
            'admin.customers.update' => 'customers.edit',
            'admin.customers.destroy' => 'customers.delete',
            
            // Reports
            'admin.reports.index' => 'reports.view',
            'admin.reports.sales' => 'reports.view',
            'admin.reports.products' => 'reports.view',
            'admin.reports.customers' => 'reports.view',
            'admin.reports.export' => 'reports.export',
            
            // Settings
            'admin.settings.index' => 'settings.view',
            'admin.settings.edit' => 'settings.edit',
            'admin.settings.update' => 'settings.edit',
        ];

        // Check exact route name match
        if (isset($routePermissionMap[$routeName])) {
            return $routePermissionMap[$routeName];
        }

        // Try to determine from URL pattern
        $uri = $request->path();
        
        // Remove admin prefix
        $uri = preg_replace('/^admin\//', '', $uri);
        
        // Extract resource and action
        $segments = explode('/', $uri);
        if (count($segments) >= 1) {
            $resource = $segments[0];
            
            // Map HTTP methods to actions
            $action = $this->mapMethodToAction($method, $segments);
            
            // Check if this is a known resource
            $knownResources = [
                'products', 'orders', 'invoices', 'returns', 
                'payments', 'customers', 'reports', 'settings'
            ];
            
            if (in_array($resource, $knownResources) && $action) {
                return "{$resource}.{$action}";
            }
        }

        return null;
    }

    /**
     * Map HTTP method to permission action
     */
    protected function mapMethodToAction(string $method, array $segments): ?string
    {
        switch ($method) {
            case 'GET':
                // If there's an ID in the URL, it's likely a show/edit action
                if (count($segments) >= 2 && is_numeric($segments[1])) {
                    // Check if it's an edit form
                    if (count($segments) >= 3 && $segments[2] === 'edit') {
                        return 'edit';
                    }
                    return 'view';
                }
                // Check for create form
                if (count($segments) >= 2 && $segments[1] === 'create') {
                    return 'create';
                }
                return 'view';
                
            case 'POST':
                return 'create';
                
            case 'PUT':
            case 'PATCH':
                return 'edit';
                
            case 'DELETE':
                return 'delete';
                
            default:
                return null;
        }
    }
}
