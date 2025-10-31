<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Models\Tenant;
use App\Services\TenantContextService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TenantController extends Controller
{
    /**
     * The tenant context service
     */
    protected TenantContextService $tenantContext;

    /**
     * Create a new controller instance
     */
    public function __construct(TenantContextService $tenantContext)
    {
        $this->tenantContext = $tenantContext;
        $this->middleware('auth')->except(['select', 'unavailable']);
    }

    /**
     * Show tenant selection page
     */
    public function select(): View
    {
        $user = Auth::user();
        $availableTenants = collect();

        if ($user) {
            $availableTenants = $this->tenantContext->getAvailableTenantsForUser($user);
        }

        return view('tenant.select', [
            'tenants' => $availableTenants,
            'currentTenant' => $this->tenantContext->getCurrentTenant()
        ]);
    }

    /**
     * Switch to a specific tenant
     */
    public function switch(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'tenant_id' => 'required|integer|exists:tenants,id'
        ]);

        $tenantId = $request->input('tenant_id');
        $user = Auth::user();

        $success = $this->tenantContext->switchToTenant($tenantId, $user);

        if (!$success) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể chuyển đổi cửa hàng'
                ], 403);
            }

            return redirect()->back()
                           ->with('error', 'Không thể chuyển đổi cửa hàng');
        }

        $tenant = Tenant::find($tenantId);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Đã chuyển sang cửa hàng: {$tenant->name}",
                'tenant' => [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'slug' => $tenant->slug
                ],
                'redirect_url' => route('dashboard')
            ]);
        }

        return redirect()->route('dashboard')
                        ->with('success', "Đã chuyển sang cửa hàng: {$tenant->name}");
    }

    /**
     * Get current tenant information
     */
    public function current(): JsonResponse
    {
        $tenant = $this->tenantContext->getCurrentTenant();

        if (!$tenant) {
            return response()->json([
                'tenant' => null,
                'message' => 'No tenant context'
            ]);
        }

        $user = Auth::user();
        $userRole = $this->tenantContext->getUserRoleInCurrentTenant($user);
        $statistics = $this->tenantContext->getTenantStatistics();

        return response()->json([
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
                'status' => $tenant->status,
                'plan_type' => $tenant->plan_type,
                'features' => $tenant->features
            ],
            'user_role' => $userRole,
            'statistics' => $statistics
        ]);
    }

    /**
     * Get available tenants for current user
     */
    public function available(): JsonResponse
    {
        $user = Auth::user();
        $tenants = $this->tenantContext->getAvailableTenantsForUser($user);

        return response()->json([
            'tenants' => $tenants->map(function ($tenant) {
                return [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'slug' => $tenant->slug,
                    'status' => $tenant->status,
                    'plan_type' => $tenant->plan_type,
                    'role' => $tenant->pivot->role,
                    'is_primary' => $tenant->pivot->is_primary
                ];
            })
        ]);
    }

    /**
     * Show tenant unavailable page
     */
    public function unavailable(): View
    {
        $tenant = session('tenant');
        
        return view('tenant.unavailable', [
            'tenant' => $tenant
        ]);
    }

    /**
     * Show tenant inactive user page
     */
    public function inactive(): View
    {
        return view('tenant.inactive');
    }

    /**
     * Get tenant settings
     */
    public function settings(): JsonResponse
    {
        $tenant = $this->tenantContext->getCurrentTenant();

        if (!$tenant) {
            return response()->json([
                'error' => 'No tenant context'
            ], 400);
        }

        // Get public settings only
        $settings = $tenant->tenantSettings()
                          ->where('is_public', true)
                          ->get()
                          ->mapWithKeys(function ($setting) {
                              return [$setting->key => $setting->typed_value];
                          });

        return response()->json([
            'settings' => $settings
        ]);
    }

    /**
     * Update tenant setting
     */
    public function updateSetting(Request $request): JsonResponse
    {
        $request->validate([
            'key' => 'required|string',
            'value' => 'required'
        ]);

        $user = Auth::user();
        
        // Check if user can modify settings
        if (!$this->tenantContext->userCanPerformAction('settings.edit', $user)) {
            return response()->json([
                'error' => 'Insufficient permissions'
            ], 403);
        }

        $key = $request->input('key');
        $value = $request->input('value');

        $success = $this->tenantContext->setTenantSetting($key, $value);

        if (!$success) {
            return response()->json([
                'error' => 'Failed to update setting'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Setting updated successfully'
        ]);
    }

    /**
     * Get tenant statistics
     */
    public function statistics(): JsonResponse
    {
        $statistics = $this->tenantContext->getTenantStatistics();

        return response()->json([
            'statistics' => $statistics
        ]);
    }

    /**
     * Clear tenant context
     */
    public function clear(): JsonResponse|RedirectResponse
    {
        $this->tenantContext->clearContext();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Tenant context cleared'
            ]);
        }

        return redirect()->route('tenant.select')
                        ->with('info', 'Đã xóa ngữ cảnh cửa hàng');
    }

    /**
     * Validate tenant access for user
     */
    public function validateAccess(Request $request): JsonResponse
    {
        $request->validate([
            'tenant_id' => 'required|integer|exists:tenants,id',
            'permission' => 'nullable|string'
        ]);

        $tenantId = $request->input('tenant_id');
        $permission = $request->input('permission');
        $user = Auth::user();

        $tenant = Tenant::find($tenantId);
        
        if (!$tenant) {
            return response()->json([
                'has_access' => false,
                'reason' => 'Tenant not found'
            ]);
        }

        // Check if user belongs to tenant
        $tenantUser = $user->tenants()->where('tenants.id', $tenantId)->first();
        
        if (!$tenantUser) {
            return response()->json([
                'has_access' => false,
                'reason' => 'User not member of tenant'
            ]);
        }

        // Check if user is active
        if (!$tenantUser->pivot->is_active) {
            return response()->json([
                'has_access' => false,
                'reason' => 'User inactive in tenant'
            ]);
        }

        // Check specific permission if provided
        if ($permission) {
            $permissions = $tenantUser->pivot->permissions ?? [];
            $hasPermission = in_array($permission, $permissions) || in_array('*', $permissions);
            
            if (!$hasPermission) {
                return response()->json([
                    'has_access' => false,
                    'reason' => 'Insufficient permissions'
                ]);
            }
        }

        return response()->json([
            'has_access' => true,
            'role' => $tenantUser->pivot->role,
            'permissions' => $tenantUser->pivot->permissions
        ]);
    }
}
