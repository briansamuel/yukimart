<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Services\TenantContextService;
use Symfony\Component\HttpFoundation\Response;

class SubdomainTenantMiddleware
{
    protected $tenantContextService;

    public function __construct(TenantContextService $tenantContextService)
    {
        $this->tenantContextService = $tenantContextService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $subdomain = $this->extractSubdomain($host);

        // If no subdomain or main domain or platform, continue without setting tenant
        if (!$subdomain || $subdomain === 'www' || $subdomain === 'platform') {
            return $next($request);
        }

        // Try to find tenant by subdomain
        $tenant = $this->resolveTenantFromSubdomain($subdomain);

        if ($tenant) {
            // Set tenant context
            $this->tenantContextService->setCurrentTenant($tenant);
            
            // Store in session for consistency
            session(['current_tenant_id' => $tenant->id]);
            
            // Bind tenant to service container
            app()->instance('current_tenant', $tenant);
            
            // Add tenant info to request
            $request->attributes->set('tenant', $tenant);
            $request->attributes->set('tenant_id', $tenant->id);

            // Set default tenant parameter for route URL generator
            // This allows route() helper to automatically use current subdomain
            \URL::defaults(['tenant' => $subdomain]);

            // Log tenant access
            \Log::info("Tenant accessed via subdomain", [
                'tenant_id' => $tenant->id,
                'tenant_name' => $tenant->name,
                'subdomain' => $subdomain,
                'host' => $host,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            
        } else {
            // Subdomain not found - could redirect to tenant selection or 404
            \Log::warning("Unknown subdomain accessed", [
                'subdomain' => $subdomain,
                'host' => $host,
                'ip' => $request->ip(),
            ]);
            
            // For now, continue without tenant (could be customized)
            // return abort(404, 'Tenant not found');
        }

        return $next($request);
    }

    /**
     * Extract subdomain from host
     */
    protected function extractSubdomain(string $host): ?string
    {
        // Remove port if present
        $host = explode(':', $host)[0];
        
        // Split by dots
        $parts = explode('.', $host);
        
        // If we have at least 3 parts (subdomain.domain.tld), extract subdomain
        if (count($parts) >= 3) {
            return $parts[0];
        }
        
        // Check for local development patterns
        if (count($parts) >= 2) {
            // Handle patterns like tenant1.yukimart.local
            $possibleSubdomain = $parts[0];
            $domain = implode('.', array_slice($parts, 1));
            
            // Check if this looks like a tenant subdomain
            if ($this->isValidTenantSubdomain($possibleSubdomain, $domain)) {
                return $possibleSubdomain;
            }
        }
        
        return null;
    }

    /**
     * Check if subdomain is valid for tenant
     */
    protected function isValidTenantSubdomain(string $subdomain, string $domain): bool
    {
        // Define valid domain patterns for tenant subdomains
        $validDomains = [
            'yukimart.local',
            'yukimart.com',
            'localhost',
        ];
        
        // Check if domain is in our valid list
        if (!in_array($domain, $validDomains)) {
            return false;
        }
        
        // Check subdomain format (alphanumeric, hyphens allowed)
        if (!preg_match('/^[a-zA-Z0-9\-]+$/', $subdomain)) {
            return false;
        }
        
        // Exclude common subdomains that shouldn't be tenants
        $excludedSubdomains = [
            'www', 'api', 'admin', 'mail', 'ftp', 'cdn', 'static', 'assets',
            'test', 'staging', 'dev', 'demo', 'app', 'portal', 'dashboard'
        ];
        
        return !in_array($subdomain, $excludedSubdomains);
    }

    /**
     * Resolve tenant from subdomain
     */
    protected function resolveTenantFromSubdomain(string $subdomain): ?Tenant
    {
        // First try direct subdomain match
        $tenant = Tenant::where('subdomain', $subdomain)
                        ->where('status', 'active')
                        ->first();
        
        if ($tenant) {
            return $tenant;
        }
        
        // Try slug match as fallback
        $tenant = Tenant::where('slug', $subdomain)
                        ->where('status', 'active')
                        ->first();
        
        if ($tenant) {
            return $tenant;
        }
        
        // Try custom subdomain mappings
        $subdomainMappings = [
            'tenant1' => 'techmart',
            'tenant2' => 'fashion', 
            'tenant3' => 'foodbev',
            'yukimart' => 'default',
        ];
        
        if (isset($subdomainMappings[$subdomain])) {
            $tenant = Tenant::where('slug', $subdomainMappings[$subdomain])
                            ->where('status', 'active')
                            ->first();
            
            if ($tenant) {
                return $tenant;
            }
        }
        
        return null;
    }

    /**
     * Get current tenant from request
     */
    public static function getCurrentTenantFromRequest(Request $request): ?Tenant
    {
        return $request->attributes->get('tenant');
    }

    /**
     * Get current tenant ID from request
     */
    public static function getCurrentTenantIdFromRequest(Request $request): ?int
    {
        $tenant = $request->attributes->get('tenant');
        return $tenant ? $tenant->id : null;
    }

    /**
     * Check if request has tenant context
     */
    public static function hasTenantContext(Request $request): bool
    {
        return $request->attributes->has('tenant');
    }

    /**
     * Generate tenant URL for subdomain
     */
    public static function generateTenantUrl(Tenant $tenant, string $path = ''): string
    {
        $subdomain = $tenant->subdomain ?: $tenant->slug;
        $domain = config('app.domain', 'yukimart.local');
        $protocol = config('app.env') === 'production' ? 'https' : 'http';
        
        $url = "{$protocol}://{$subdomain}.{$domain}";
        
        if ($path) {
            $url .= '/' . ltrim($path, '/');
        }
        
        return $url;
    }
}
