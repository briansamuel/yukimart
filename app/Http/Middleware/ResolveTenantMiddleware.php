<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Tenant;
use App\Services\TenantContextService;
use App\Traits\TenantScoped;

class ResolveTenantMiddleware
{
    /**
     * The tenant context service
     */
    protected TenantContextService $tenantContext;

    /**
     * Create a new middleware instance
     */
    public function __construct(TenantContextService $tenantContext)
    {
        $this->tenantContext = $tenantContext;
    }

    /**
     * Handle an incoming request
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            // Skip tenant resolution for certain routes
            if ($this->shouldSkipTenantResolution($request)) {
                return $next($request);
            }

            // Resolve tenant from various sources
            $tenant = $this->resolveTenant($request);

            if (!$tenant) {
                return $this->handleMissingTenant($request);
            }

            // Validate tenant status
            if (!$this->validateTenantStatus($tenant)) {
                return $this->handleInvalidTenant($request, $tenant);
            }

            // Set tenant context
            $this->tenantContext->setCurrentTenant($tenant);
            TenantScoped::setCurrentTenant($tenant);

            // Set permissions team ID for Spatie Permission package
            setPermissionsTeamId($tenant->id);

            // Add tenant to request for easy access
            $request->attributes->set('tenant', $tenant);

            // Update tenant activity
            $this->updateTenantActivity($tenant, $request);

            return $next($request);

        } catch (\Exception $e) {
            Log::error('Tenant resolution failed', [
                'error' => $e->getMessage(),
                'request_url' => $request->url(),
                'user_agent' => $request->userAgent(),
                'ip' => $request->ip()
            ]);

            return $this->handleTenantResolutionError($request, $e);
        }
    }

    /**
     * Resolve tenant from request
     */
    protected function resolveTenant(Request $request): ?Tenant
    {
        // Try multiple resolution strategies in order of priority
        $strategies = [
            'resolveFromRouteParameter',
            'resolveFromSubdomain',
            'resolveFromDomain',
            'resolveFromSession',
            'resolveFromUser',
            'resolveFromHeader'
        ];

        foreach ($strategies as $strategy) {
            $tenant = $this->$strategy($request);
            if ($tenant) {
                Log::info("Tenant resolved using strategy: {$strategy}", [
                    'tenant_id' => $tenant->id,
                    'tenant_slug' => $tenant->slug
                ]);
                return $tenant;
            }
        }

        return null;
    }

    /**
     * Resolve tenant from route parameter
     */
    protected function resolveFromRouteParameter(Request $request): ?Tenant
    {
        $tenantSlug = $request->route('tenant');
        
        if ($tenantSlug) {
            return Tenant::where('slug', $tenantSlug)
                        ->active()
                        ->first();
        }

        return null;
    }

    /**
     * Resolve tenant from subdomain
     */
    protected function resolveFromSubdomain(Request $request): ?Tenant
    {
        $host = $request->getHost();
        $subdomain = $this->extractSubdomain($host);

        if ($subdomain && $subdomain !== 'www') {
            return Tenant::where('subdomain', $subdomain)
                        ->active()
                        ->first();
        }

        return null;
    }

    /**
     * Resolve tenant from custom domain
     */
    protected function resolveFromDomain(Request $request): ?Tenant
    {
        $host = $request->getHost();

        return Tenant::where('domain', $host)
                    ->active()
                    ->first();
    }

    /**
     * Resolve tenant from user session
     */
    protected function resolveFromSession(Request $request): ?Tenant
    {
        $tenantId = session('current_tenant_id');

        if ($tenantId) {
            return Tenant::find($tenantId);
        }

        return null;
    }

    /**
     * Resolve tenant from authenticated user
     */
    protected function resolveFromUser(Request $request): ?Tenant
    {
        $user = $request->user();

        if ($user) {
            // Get user's primary tenant
            return $user->primaryTenant;
        }

        return null;
    }

    /**
     * Resolve tenant from request header
     */
    protected function resolveFromHeader(Request $request): ?Tenant
    {
        $tenantId = $request->header('X-Tenant-ID');
        $tenantSlug = $request->header('X-Tenant-Slug');

        if ($tenantId) {
            return Tenant::find($tenantId);
        }

        if ($tenantSlug) {
            return Tenant::where('slug', $tenantSlug)->first();
        }

        return null;
    }

    /**
     * Extract subdomain from host
     */
    protected function extractSubdomain(string $host): ?string
    {
        $parts = explode('.', $host);
        
        // For localhost development
        if ($host === 'localhost' || filter_var($host, FILTER_VALIDATE_IP)) {
            return null;
        }

        // For domains like subdomain.example.com
        if (count($parts) >= 3) {
            return $parts[0];
        }

        return null;
    }

    /**
     * Check if tenant resolution should be skipped
     */
    protected function shouldSkipTenantResolution(Request $request): bool
    {
        $skipRoutes = [
            'api/health',
            'api/status',
            '_debugbar',
            'telescope',
            'horizon',
            'api/auth/login',
            'api/auth/register'
        ];

        // Skip only specific auth routes, not all admin routes
        $skipAuthRoutes = [
            'admin/login',
            'admin/register',
            'admin/password/reset',
            'admin/password/email',
            'admin/password/confirm'
        ];

        $path = $request->path();

        // Check general skip routes
        foreach ($skipRoutes as $skipRoute) {
            if (str_starts_with($path, $skipRoute)) {
                return true;
            }
        }

        // Check auth-specific routes
        foreach ($skipAuthRoutes as $skipRoute) {
            if ($path === $skipRoute || str_starts_with($path, $skipRoute)) {
                return true;
            }
        }

        // Skip for platform routes (not tenant-specific)
        if (str_starts_with($path, 'platform/')) {
            return true;
        }

        return false;
    }

    /**
     * Validate tenant status
     */
    protected function validateTenantStatus(Tenant $tenant): bool
    {
        // Check if tenant is active
        if ($tenant->status !== Tenant::STATUS_ACTIVE && $tenant->status !== Tenant::STATUS_TRIAL) {
            return false;
        }

        // Check if tenant is expired
        if ($tenant->isExpired()) {
            return false;
        }

        return true;
    }

    /**
     * Handle missing tenant
     */
    protected function handleMissingTenant(Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Tenant not found',
                'message' => 'Unable to resolve tenant from request'
            ], 404);
        }

        // Redirect to tenant selection or main site
        return redirect()->route('tenant.select')
                        ->with('error', 'Vui lòng chọn cửa hàng để tiếp tục');
    }

    /**
     * Handle invalid tenant
     */
    protected function handleInvalidTenant(Request $request, Tenant $tenant)
    {
        $message = match($tenant->status) {
            Tenant::STATUS_SUSPENDED => 'Cửa hàng đã bị tạm ngưng',
            Tenant::STATUS_EXPIRED => 'Cửa hàng đã hết hạn sử dụng',
            Tenant::STATUS_INACTIVE => 'Cửa hàng không hoạt động',
            default => 'Cửa hàng không khả dụng'
        };

        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Tenant unavailable',
                'message' => $message,
                'tenant_status' => $tenant->status
            ], 403);
        }

        return redirect()->route('tenant.unavailable')
                        ->with('error', $message)
                        ->with('tenant', $tenant);
    }

    /**
     * Handle tenant resolution error
     */
    protected function handleTenantResolutionError(Request $request, \Exception $e)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Tenant resolution failed',
                'message' => 'An error occurred while resolving tenant context'
            ], 500);
        }

        return redirect()->route('home')
                        ->with('error', 'Đã xảy ra lỗi. Vui lòng thử lại sau.');
    }

    /**
     * Update tenant activity
     */
    protected function updateTenantActivity(Tenant $tenant, Request $request): void
    {
        try {
            $tenant->update([
                'last_activity_at' => now(),
                'last_activity_ip' => $request->ip()
            ]);
        } catch (\Exception $e) {
            // Log but don't fail the request
            Log::warning('Failed to update tenant activity', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
