<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DetectPlatform
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $platformHost = config('tenancy.platform_host');
        
        // Detect if this is a platform request
        $isPlatform = $this->isPlatformRequest($host);
        
        // Set platform context in request
        $request->attributes->set('is_platform', $isPlatform);
        
        if ($isPlatform) {
            // Platform-specific configuration
            $this->configurePlatform($request);
        } else {
            // Tenant-specific configuration  
            $this->configureTenant($request);
        }

        return $next($request);
    }

    /**
     * Check if the request is for platform
     */
    private function isPlatformRequest(string $host): bool
    {
        $platformHost = config('tenancy.platform_host');
        $platformPatterns = config('tenancy.detection.platform_patterns', []);
        
        // Direct match
        if ($host === $platformHost) {
            return true;
        }
        
        // Pattern matching
        foreach ($platformPatterns as $pattern) {
            if ($host === $pattern) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Configure platform-specific settings
     */
    private function configurePlatform(Request $request): void
    {
        // Set platform session configuration
        $sessionName = config('tenancy.platform.session_name');
        if ($sessionName) {
            config(['session.cookie' => $sessionName]);
        }
        
        // Set platform context
        app()->instance('platform.context', true);
        app()->instance('tenant.context', null);
        
        // Mark as platform request
        $request->attributes->set('context_type', 'platform');
    }

    /**
     * Configure tenant-specific settings
     */
    private function configureTenant(Request $request): void
    {
        // Set tenant session configuration
        $sessionName = config('tenancy.tenant.session_name');
        if ($sessionName) {
            config(['session.cookie' => $sessionName]);
        }
        
        // Set tenant context (will be populated by tenant resolution middleware)
        app()->instance('platform.context', false);
        
        // Mark as tenant request
        $request->attributes->set('context_type', 'tenant');
    }
}
