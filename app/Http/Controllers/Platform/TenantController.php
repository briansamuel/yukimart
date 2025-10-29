<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TenantController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware(['auth:admin', 'platform.user']);
    }
    
    /**
     * Display a listing of tenants
     */
    public function index(Request $request)
    {
        $query = Tenant::query();
        
        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('subdomain', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }
        
        // Status filter
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }
        
        $tenants = $query->withCount(['users', 'products'])
                        ->orderBy('created_at', 'desc')
                        ->paginate(20);
        
        return view('admin.platform.tenants.index', compact('tenants'));
    }
    
    /**
     * Show the form for creating a new tenant
     */
    public function create()
    {
        return view('admin.platform.tenants.create');
    }
    
    /**
     * Store a newly created tenant
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'subdomain' => 'required|string|max:50|unique:tenants,subdomain|alpha_dash',
            'description' => 'nullable|string|max:1000',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'status' => 'required|in:active,inactive,suspended'
        ]);
        
        $tenant = Tenant::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'subdomain' => strtolower($request->subdomain),
            'description' => $request->description,
            'contact_email' => $request->contact_email,
            'contact_phone' => $request->contact_phone,
            'status' => $request->status,
            'created_by' => Auth::guard('admin')->id()
        ]);
        
        return redirect()->route('platform.tenants.index')
                        ->with('success', "Tenant '{$tenant->name}' created successfully.");
    }
    
    /**
     * Display the specified tenant
     */
    public function show(Tenant $tenant)
    {
        $tenant->load(['users', 'products', 'branchShops']);
        
        $stats = [
            'users_count' => $tenant->users->count(),
            'products_count' => $tenant->products->count(),
            'branches_count' => $tenant->branchShops->count(),
            'recent_users' => $tenant->users()->latest()->take(5)->get(),
            'recent_products' => $tenant->products()->latest()->take(5)->get()
        ];
        
        return view('admin.platform.tenants.show', compact('tenant', 'stats'));
    }
    
    /**
     * Show the form for editing the specified tenant
     */
    public function edit(Tenant $tenant)
    {
        return view('admin.platform.tenants.edit', compact('tenant'));
    }
    
    /**
     * Update the specified tenant
     */
    public function update(Request $request, Tenant $tenant)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'subdomain' => 'required|string|max:50|alpha_dash|unique:tenants,subdomain,' . $tenant->id,
            'description' => 'nullable|string|max:1000',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'status' => 'required|in:active,inactive,suspended'
        ]);
        
        $tenant->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'subdomain' => strtolower($request->subdomain),
            'description' => $request->description,
            'contact_email' => $request->contact_email,
            'contact_phone' => $request->contact_phone,
            'status' => $request->status
        ]);
        
        return redirect()->route('platform.tenants.index')
                        ->with('success', "Tenant '{$tenant->name}' updated successfully.");
    }
    
    /**
     * Remove the specified tenant
     */
    public function destroy(Tenant $tenant)
    {
        // Prevent deletion if tenant has users or products
        if ($tenant->users()->count() > 0 || $tenant->products()->count() > 0) {
            return redirect()->route('platform.tenants.index')
                            ->with('error', 'Cannot delete tenant with existing users or products.');
        }
        
        $tenantName = $tenant->name;
        $tenant->delete();
        
        return redirect()->route('platform.tenants.index')
                        ->with('success', "Tenant '{$tenantName}' deleted successfully.");
    }
    
    /**
     * Show tenant switching interface
     */
    public function switch()
    {
        $tenants = Tenant::where('status', 'active')
                        ->withCount(['users', 'products'])
                        ->orderBy('name')
                        ->get();
        
        $currentTenant = session('current_tenant');
        
        return view('admin.platform.tenants.switch', compact('tenants', 'currentTenant'));
    }
    
    /**
     * Switch to specific tenant
     */
    public function switchTo(Request $request, Tenant $tenant)
    {
        if ($tenant->status !== 'active') {
            return redirect()->route('platform.tenants.switch')
                            ->with('error', 'Cannot switch to inactive tenant.');
        }
        
        // Set tenant context
        session([
            'current_tenant_id' => $tenant->id,
            'current_tenant' => $tenant->toArray(),
            'current_tenant_role' => 'platform_admin',
            'is_platform_mode' => false
        ]);
        
        return redirect()->route('admin.dashboard')
                        ->with('success', "Switched to tenant: {$tenant->name}");
    }
    
    /**
     * Get tenant data for AJAX requests
     */
    public function getData(Request $request)
    {
        $query = Tenant::query();
        
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('subdomain', 'like', "%{$search}%");
            });
        }
        
        $tenants = $query->where('status', 'active')
                        ->select('id', 'name', 'subdomain', 'slug', 'status')
                        ->orderBy('name')
                        ->get();
        
        return response()->json([
            'success' => true,
            'data' => $tenants
        ]);
    }
}
