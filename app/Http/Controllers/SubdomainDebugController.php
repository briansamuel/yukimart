<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tenant;

class SubdomainDebugController extends Controller
{
    /**
     * Simple debug endpoint for subdomain middleware
     */
    public function simple(Request $request)
    {
        $tenant = $request->attributes->get('tenant');
        
        return response()->json([
            'success' => true,
            'middleware_working' => $tenant ? true : false,
            'tenant_id' => $tenant ? $tenant->id : null,
            'tenant_name' => $tenant ? $tenant->name : null,
            'tenant_slug' => $tenant ? $tenant->slug : null,
            'tenant_subdomain' => $tenant ? $tenant->subdomain : null,
            'host' => $request->getHost(),
            'subdomain' => explode('.', $request->getHost())[0] ?? 'none',
            'session_tenant_id' => session('current_tenant_id'),
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Test API endpoint for tenant info
     */
    public function tenantInfo(Request $request)
    {
        $tenant = $request->attributes->get('tenant');
        
        return response()->json([
            'success' => true,
            'tenant' => $tenant ? [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
                'subdomain' => $tenant->subdomain,
                'status' => $tenant->status,
                'plan_type' => $tenant->plan_type,
                'max_users' => $tenant->max_users,
                'current_users' => $tenant->current_users,
            ] : null,
            'host' => $request->getHost(),
            'subdomain' => explode('.', $request->getHost())[0] ?? 'none',
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Test all tenants subdomain mapping
     */
    public function testAllTenants(Request $request)
    {
        $currentTenant = $request->attributes->get('tenant');
        $allTenants = Tenant::where('status', 'active')->get();
        
        $tenantMappings = [];
        foreach ($allTenants as $tenant) {
            $subdomain = $tenant->subdomain ?: $tenant->slug;
            $tenantMappings[] = [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
                'subdomain' => $tenant->subdomain,
                'url' => "http://{$subdomain}.yukimart.local",
                'test_url' => "http://{$subdomain}.yukimart.local/debug/simple",
                'is_current' => $currentTenant && $currentTenant->id === $tenant->id,
            ];
        }
        
        return response()->json([
            'success' => true,
            'current_tenant' => $currentTenant ? [
                'id' => $currentTenant->id,
                'name' => $currentTenant->name,
                'slug' => $currentTenant->slug,
                'subdomain' => $currentTenant->subdomain,
            ] : null,
            'host' => $request->getHost(),
            'subdomain' => explode('.', $request->getHost())[0] ?? 'none',
            'total_tenants' => $allTenants->count(),
            'tenant_mappings' => $tenantMappings,
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Test subdomain resolution logic
     */
    public function testResolution(Request $request)
    {
        $host = $request->getHost();
        $parts = explode('.', $host);
        $subdomain = $parts[0] ?? 'none';
        
        // Test direct subdomain lookup
        $tenantBySubdomain = null;
        if ($subdomain !== 'none') {
            $tenantBySubdomain = Tenant::where('subdomain', $subdomain)
                                      ->where('status', 'active')
                                      ->first();
        }
        
        // Test slug lookup
        $tenantBySlug = null;
        if ($subdomain !== 'none') {
            $tenantBySlug = Tenant::where('slug', $subdomain)
                                  ->where('status', 'active')
                                  ->first();
        }
        
        // Test custom mappings
        $customMappings = [
            'tenant1' => 'techmart',
            'tenant2' => 'fashion', 
            'tenant3' => 'foodbev',
            'yukimart' => 'default',
        ];
        
        $tenantByMapping = null;
        if (isset($customMappings[$subdomain])) {
            $tenantByMapping = Tenant::where('slug', $customMappings[$subdomain])
                                     ->where('status', 'active')
                                     ->first();
        }
        
        // Get current tenant from middleware
        $currentTenant = $request->attributes->get('tenant');
        
        return response()->json([
            'success' => true,
            'host_info' => [
                'host' => $host,
                'parts' => $parts,
                'extracted_subdomain' => $subdomain,
            ],
            'resolution_tests' => [
                'by_subdomain' => $tenantBySubdomain ? [
                    'id' => $tenantBySubdomain->id,
                    'name' => $tenantBySubdomain->name,
                    'slug' => $tenantBySubdomain->slug,
                    'subdomain' => $tenantBySubdomain->subdomain,
                ] : null,
                'by_slug' => $tenantBySlug ? [
                    'id' => $tenantBySlug->id,
                    'name' => $tenantBySlug->name,
                    'slug' => $tenantBySlug->slug,
                    'subdomain' => $tenantBySlug->subdomain,
                ] : null,
                'by_mapping' => $tenantByMapping ? [
                    'id' => $tenantByMapping->id,
                    'name' => $tenantByMapping->name,
                    'slug' => $tenantByMapping->slug,
                    'subdomain' => $tenantByMapping->subdomain,
                    'mapped_from' => $subdomain,
                    'mapped_to' => $customMappings[$subdomain],
                ] : null,
            ],
            'middleware_result' => $currentTenant ? [
                'id' => $currentTenant->id,
                'name' => $currentTenant->name,
                'slug' => $currentTenant->slug,
                'subdomain' => $currentTenant->subdomain,
            ] : null,
            'session_tenant_id' => session('current_tenant_id'),
            'custom_mappings' => $customMappings,
            'timestamp' => now()->toISOString(),
        ]);
    }
}
