<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Services\TenantContextService;
use Illuminate\Support\Facades\Auth;

/**
 * Base controller for all business (tenant-specific) operations
 * 
 * This controller provides common functionality for all tenant-scoped business operations
 * including tenant context management, authentication, and common business logic.
 */
abstract class BaseBusinessController extends Controller
{
    /**
     * The tenant context service
     */
    protected TenantContextService $tenantContext;

    /**
     * Current tenant instance
     */
    protected $currentTenant;

    /**
     * Current authenticated user
     */
    protected $currentUser;

    /**
     * Create a new controller instance
     */
    public function __construct(TenantContextService $tenantContext)
    {
        $this->tenantContext = $tenantContext;

        // Apply tenant-aware middleware
        // Note: Routes already have 'auth:tenant' and 'tenant.resolve' middleware
        // So we don't duplicate them here
        $this->middleware(['layout.mode']);

        // Initialize tenant context after middleware
        $this->middleware(function ($request, $next) {
            $this->initializeTenantContext();
            return $next($request);
        });
    }

    /**
     * Initialize tenant context and user
     */
    protected function initializeTenantContext(): void
    {
        $this->currentTenant = $this->tenantContext->getCurrentTenant();
        $this->currentUser = Auth::guard('tenant')->user();

        // Ensure tenant context is available
        if (!$this->currentTenant) {
            abort(404, 'Tenant context not found');
        }

        // Share tenant data with views
        view()->share([
            'currentTenant' => $this->currentTenant,
            'currentUser' => $this->currentUser,
            'tenantContext' => $this->tenantContext
        ]);
    }

    /**
     * Get current tenant
     */
    protected function getCurrentTenant()
    {
        return $this->currentTenant;
    }

    /**
     * Get current user
     */
    protected function getCurrentUser()
    {
        return $this->currentUser;
    }

    /**
     * Check if current user has permission for tenant operation
     */
    protected function checkTenantPermission(string $permission): bool
    {
        if (!$this->currentUser) {
            return false;
        }

        // Platform users have all permissions
        $platformRoles = ['admin', 'superadmin', 'dev', 'manager'];
        $userRoles = $this->currentUser->roles()->pluck('name')->toArray();
        $hasPlatformRole = !empty(array_intersect($platformRoles, $userRoles));
        
        if ($hasPlatformRole) {
            return true;
        }

        // Check tenant-specific permissions
        $tenantUser = $this->currentUser->tenantUsers()
            ->where('tenant_id', $this->currentTenant->id)
            ->first();

        if (!$tenantUser || !$tenantUser->is_active) {
            return false;
        }

        // Role-based permission check
        $rolePermissions = [
            'owner' => ['*'], // All permissions
            'admin' => [
                'products.*', 'orders.*', 'customers.*', 'invoices.*', 
                'returns.*', 'payments.*', 'inventory.*', 'reports.*', 'settings.*'
            ],
            'manager' => [
                'products.view', 'products.create', 'products.edit',
                'orders.*', 'customers.*', 'invoices.*', 'returns.*', 'payments.*',
                'inventory.view', 'reports.view'
            ],
            'staff' => [
                'products.view', 'orders.create', 'orders.edit', 'orders.view',
                'customers.view', 'customers.create', 'customers.edit',
                'invoices.create', 'invoices.view'
            ],
            'viewer' => [
                'products.view', 'orders.view', 'customers.view', 
                'invoices.view', 'reports.view'
            ]
        ];

        $userPermissions = $rolePermissions[$tenantUser->role] ?? [];
        
        // Check if user has permission
        if (in_array('*', $userPermissions)) {
            return true;
        }

        foreach ($userPermissions as $userPermission) {
            if ($userPermission === $permission) {
                return true;
            }
            
            // Check wildcard permissions
            if (str_ends_with($userPermission, '.*')) {
                $prefix = str_replace('.*', '', $userPermission);
                if (str_starts_with($permission, $prefix)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Authorize tenant operation
     */
    protected function authorizeTenantOperation(string $permission): void
    {
        if (!$this->checkTenantPermission($permission)) {
            abort(403, 'You do not have permission to perform this operation.');
        }
    }

    /**
     * Get tenant-scoped query for model
     */
    protected function getTenantQuery(string $modelClass)
    {
        $model = new $modelClass;
        
        // If model uses tenant scoping, it will automatically filter by tenant
        return $model->newQuery();
    }

    /**
     * Get pagination parameters
     */
    protected function getPaginationParams(): array
    {
        return [
            'page' => request('page', 1),
            'per_page' => min(request('per_page', 20), 100), // Max 100 items per page
            'sort' => request('sort', 'created_at'),
            'direction' => request('direction', 'desc')
        ];
    }

    /**
     * Get search parameters
     */
    protected function getSearchParams(): array
    {
        return [
            'search' => request('search'),
            'status' => request('status'),
            'date_from' => request('date_from'),
            'date_to' => request('date_to'),
            'category_id' => request('category_id'),
            'branch_id' => request('branch_id')
        ];
    }

    /**
     * Return JSON response for business operations
     */
    protected function businessResponse($data = null, string $message = '', int $status = 200): \Illuminate\Http\JsonResponse
    {
        $response = [
            'success' => $status >= 200 && $status < 300,
            'message' => $message,
            'tenant' => [
                'id' => $this->currentTenant->id,
                'name' => $this->currentTenant->name,
                'subdomain' => $this->currentTenant->subdomain
            ]
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $status);
    }

    /**
     * Return error response for business operations
     */
    protected function businessError(string $message, int $status = 400, $errors = null): \Illuminate\Http\JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
            'tenant' => [
                'id' => $this->currentTenant->id ?? null,
                'name' => $this->currentTenant->name ?? null,
                'subdomain' => $this->currentTenant->subdomain ?? null
            ]
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }
}
