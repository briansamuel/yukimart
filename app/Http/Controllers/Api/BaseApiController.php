<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TenantContextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * Base API Controller for Multi-Tenant System
 * 
 * Provides common functionality for all API controllers including
 * tenant context management, response formatting, and error handling.
 */
abstract class BaseApiController extends Controller
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
        
        // Apply API middleware
        $this->middleware(['auth:sanctum', 'tenant.resolve']);
        
        // Initialize context after middleware
        $this->middleware(function ($request, $next) {
            $this->initializeContext();
            return $next($request);
        });
    }

    /**
     * Initialize tenant context and user
     */
    protected function initializeContext(): void
    {
        $this->currentTenant = $this->tenantContext->getCurrentTenant();
        $this->currentUser = Auth::user();
        
        // Ensure tenant context is available for API requests
        if (!$this->currentTenant && !request()->is('api/v1/auth/*')) {
            abort(400, 'Tenant context required');
        }
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
        if (!$this->currentUser || !$this->currentTenant) {
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
            'owner' => ['*'],
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
        
        if (in_array('*', $userPermissions)) {
            return true;
        }

        foreach ($userPermissions as $userPermission) {
            if ($userPermission === $permission) {
                return true;
            }
            
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
     * Return success response
     */
    protected function successResponse($data = null, string $message = '', int $status = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
            'tenant' => $this->currentTenant ? [
                'id' => $this->currentTenant->id,
                'name' => $this->currentTenant->name,
                'subdomain' => $this->currentTenant->subdomain
            ] : null,
            'timestamp' => now()->toISOString()
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $status);
    }

    /**
     * Return error response
     */
    protected function errorResponse(string $message, int $status = 400, $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
            'tenant' => $this->currentTenant ? [
                'id' => $this->currentTenant->id,
                'name' => $this->currentTenant->name,
                'subdomain' => $this->currentTenant->subdomain
            ] : null,
            'timestamp' => now()->toISOString()
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }

    /**
     * Return validation error response
     */
    protected function validationErrorResponse($validator): JsonResponse
    {
        return $this->errorResponse(
            'Validation failed',
            422,
            $validator->errors()
        );
    }

    /**
     * Return paginated response
     */
    protected function paginatedResponse($paginator, string $message = ''): JsonResponse
    {
        return $this->successResponse([
            'items' => $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
                'has_more_pages' => $paginator->hasMorePages(),
                'next_page_url' => $paginator->nextPageUrl(),
                'prev_page_url' => $paginator->previousPageUrl(),
            ]
        ], $message);
    }

    /**
     * Get pagination parameters from request
     */
    protected function getPaginationParams(Request $request): array
    {
        return [
            'page' => $request->get('page', 1),
            'per_page' => min($request->get('per_page', 20), 100),
            'sort' => $request->get('sort', 'created_at'),
            'direction' => $request->get('direction', 'desc')
        ];
    }

    /**
     * Get search parameters from request
     */
    protected function getSearchParams(Request $request): array
    {
        return [
            'search' => $request->get('search'),
            'status' => $request->get('status'),
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            'category_id' => $request->get('category_id'),
            'brand_id' => $request->get('brand_id'),
            'branch_id' => $request->get('branch_id')
        ];
    }

    /**
     * Validate request data
     */
    protected function validateRequest(Request $request, array $rules): \Illuminate\Validation\Validator
    {
        return Validator::make($request->all(), $rules);
    }

    /**
     * Get tenant-scoped query for model
     */
    protected function getTenantQuery(string $modelClass)
    {
        $model = new $modelClass;
        return $model->newQuery();
    }

    /**
     * Handle API exceptions
     */
    protected function handleException(\Exception $e, string $operation = 'operation'): JsonResponse
    {
        \Log::error("API {$operation} failed", [
            'tenant_id' => $this->currentTenant->id ?? null,
            'user_id' => $this->currentUser->id ?? null,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        if (config('app.debug')) {
            return $this->errorResponse(
                "Failed to perform {$operation}: " . $e->getMessage(),
                500,
                ['trace' => $e->getTraceAsString()]
            );
        }

        return $this->errorResponse("Failed to perform {$operation}", 500);
    }

    /**
     * Rate limiting check
     */
    protected function checkRateLimit(string $key, int $maxAttempts = 60, int $decayMinutes = 1): bool
    {
        $limiter = app(\Illuminate\Cache\RateLimiter::class);
        
        if ($limiter->tooManyAttempts($key, $maxAttempts)) {
            return false;
        }
        
        $limiter->hit($key, $decayMinutes * 60);
        return true;
    }

    /**
     * Get rate limit key for current user/tenant
     */
    protected function getRateLimitKey(string $action): string
    {
        $userId = $this->currentUser->id ?? 'guest';
        $tenantId = $this->currentTenant->id ?? 'no-tenant';
        $ip = request()->ip();
        
        return "api:{$action}:{$tenantId}:{$userId}:{$ip}";
    }

    /**
     * Apply rate limiting to action
     */
    protected function applyRateLimit(string $action, int $maxAttempts = 60, int $decayMinutes = 1): void
    {
        $key = $this->getRateLimitKey($action);
        
        if (!$this->checkRateLimit($key, $maxAttempts, $decayMinutes)) {
            abort(429, 'Too many requests. Please try again later.');
        }
    }

    /**
     * Log API activity
     */
    protected function logActivity(string $action, array $data = []): void
    {
        \Log::info("API Activity: {$action}", [
            'tenant_id' => $this->currentTenant->id ?? null,
            'user_id' => $this->currentUser->id ?? null,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'data' => $data,
            'timestamp' => now()->toISOString()
        ]);
    }
}
