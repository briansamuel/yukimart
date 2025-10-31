<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlatformController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware(['auth:admin', 'platform.user']);
    }
    
    /**
     * Platform Dashboard
     */
    public function dashboard()
    {
        $stats = $this->getPlatformStats();
        
        return view('admin.platform.dashboard', compact('stats'));
    }
    
    /**
     * Switch to tenant mode
     */
    public function switchToTenant(Request $request)
    {
        $request->validate([
            'tenant_id' => 'required|exists:tenants,id'
        ]);
        
        $tenant = \App\Models\Tenant::findOrFail($request->tenant_id);
        
        // Check if user has access to this tenant
        $user = Auth::guard('admin')->user();
        
        // Platform users can access any tenant
        $platformRoles = ['admin', 'superadmin', 'dev', 'manager'];
        $userRoles = $user->roles()->pluck('name')->toArray();
        $hasPlatformRole = !empty(array_intersect($platformRoles, $userRoles));
        
        if (!$hasPlatformRole) {
            // Check if user belongs to this tenant
            $tenantUser = $user->tenantUsers()->where('tenant_id', $tenant->id)->first();
            if (!$tenantUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have access to this tenant.'
                ], 403);
            }
        }
        
        // Set tenant context in session
        session([
            'current_tenant_id' => $tenant->id,
            'current_tenant' => $tenant->toArray(),
            'current_tenant_role' => $hasPlatformRole ? 'platform_admin' : 'user',
            'is_platform_mode' => false
        ]);
        
        return response()->json([
            'success' => true,
            'message' => "Switched to {$tenant->name}",
            'tenant' => $tenant,
            'redirect_url' => route('admin.dashboard')
        ]);
    }
    
    /**
     * Return to platform mode
     */
    public function returnToPlatform(Request $request)
    {
        // Clear tenant context
        session()->forget([
            'current_tenant_id',
            'current_tenant',
            'current_tenant_role'
        ]);
        
        // Set platform mode
        session(['is_platform_mode' => true]);
        
        return response()->json([
            'success' => true,
            'message' => 'Returned to platform mode',
            'redirect_url' => route('platform.dashboard')
        ]);
    }
    
    /**
     * Get platform statistics
     */
    private function getPlatformStats()
    {
        return [
            'total_tenants' => \App\Models\Tenant::count(),
            'active_tenants' => \App\Models\Tenant::where('status', 'active')->count(),
            'total_users' => \App\Models\User::count(),
            'total_products' => \App\Models\Product::count(),
            'total_orders' => \App\Models\Order::count() ?? 0,
            'recent_tenants' => \App\Models\Tenant::latest()->take(5)->get(),
            'tenant_stats' => \App\Models\Tenant::with(['users', 'products'])
                ->where('status', 'active')
                ->get()
                ->map(function ($tenant) {
                    return [
                        'id' => $tenant->id,
                        'name' => $tenant->name,
                        'subdomain' => $tenant->subdomain,
                        'users_count' => $tenant->users->count(),
                        'products_count' => $tenant->products->count(),
                        'status' => $tenant->status,
                        'created_at' => $tenant->created_at
                    ];
                })
        ];
    }
}
