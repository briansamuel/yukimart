<?php

namespace App\Http\Controllers\Tenant\Settings\Shop;

use App\Http\Controllers\Tenant\BaseTenantController;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RoleController extends BaseTenantController
{
    /**
     * Get all roles for current tenant
     */
    public function index(Request $request)
    {
        $tenantId = $this->getCurrentTenantId();

        // Load only tenant-specific roles (tenant isolation)
        $roles = Role::where('tenant_id', $tenantId)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        // Map roles with permissions count
        $rolesData = $roles->map(function($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'display_name' => $role->display_name,
                'description' => $role->description,
                'permissions_count' => $role->permissions()->count(),
                'users_count' => $role->users()->count(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $rolesData
        ]);
    }
    
    /**
     * Get all permissions grouped by module
     */
    public function getPermissions(Request $request)
    {
        // Get all global permissions (tenant_id = null)
        $permissions = Permission::whereNull('tenant_id')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
        
        // Group by module and sub_module
        $grouped = $permissions->groupBy('module')->map(function ($modulePermissions, $module) {
            return $modulePermissions->groupBy('sub_module')->map(function ($subModulePermissions) {
                return $subModulePermissions->map(function ($permission) {
                    return [
                        'id' => $permission->id,
                        'name' => $permission->name,
                        'display_name' => $permission->display_name,
                        'action' => $permission->action,
                    ];
                });
            });
        });
        
        return response()->json([
            'success' => true,
            'data' => $grouped
        ]);
    }
    
    /**
     * Get single role with permissions
     */
    public function show()
    {
        // Get role ID from route parameter (not method parameter to avoid conflict with domain parameter)
        $id = request()->route('id');
        $tenantId = $this->getCurrentTenantId();

        // Load only tenant-specific role (tenant isolation)
        $role = Role::where('tenant_id', $tenantId)
            ->where('id', $id)
            ->first();

        if (!$role) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy vai trò'
            ], 404);
        }

        // Get permissions using Spatie's method
        $permissions = $role->permissions()->get();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $role->id,
                'name' => $role->name,
                'display_name' => $role->display_name,
                'description' => $role->description,
                'permissions' => $permissions->map(function($permission) {
                    return [
                        'id' => $permission->id,
                        'name' => $permission->name,
                        'display_name' => $permission->display_name,
                        'module' => $permission->module,
                        'sub_module' => $permission->sub_module,
                    ];
                })
            ]
        ]);
    }
    
    /**
     * Create new role
     */
    public function store(Request $request)
    {
        $tenantId = $this->getCurrentTenantId();
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'display_name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            DB::beginTransaction();
            
            // Create role
            $role = Role::create([
                'name' => $request->name,
                'guard_name' => 'web',
                'display_name' => $request->display_name ?? $request->name,
                'description' => $request->description,
                'tenant_id' => $tenantId,
                'is_active' => true,
                'sort_order' => Role::where('tenant_id', $tenantId)->max('sort_order') + 1,
            ]);
            
            // Sync permissions
            if ($request->has('permissions') && is_array($request->permissions)) {
                $role->syncPermissions($request->permissions);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Tạo vai trò thành công',
                'data' => $role->load('permissions')
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Update role
     */
    public function update(Request $request)
    {
        // Get role ID from route parameter (not method parameter to avoid conflict with domain parameter)
        $id = request()->route('id');
        $tenantId = $this->getCurrentTenantId();

        // Only allow updating tenant-specific roles, not global roles
        $role = Role::where('tenant_id', $tenantId)
            ->where('id', $id)
            ->first();

        if (!$role) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy vai trò'
            ], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'display_name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            DB::beginTransaction();
            
            // Update role
            $role->update([
                'name' => $request->name,
                'display_name' => $request->display_name ?? $request->name,
                'description' => $request->description,
            ]);
            
            // Sync permissions
            if ($request->has('permissions')) {
                $role->syncPermissions($request->permissions ?? []);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật vai trò thành công',
                'data' => $role->load('permissions')
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Delete role
     */
    public function destroy()
    {
        // Get role ID from route parameter (not method parameter to avoid conflict with domain parameter)
        $id = request()->route('id');
        $tenantId = $this->getCurrentTenantId();

        // Only allow deleting tenant-specific roles, not global roles
        $role = Role::where('tenant_id', $tenantId)
            ->where('id', $id)
            ->first();

        if (!$role) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy vai trò'
            ], 404);
        }
        
        // Check if role is being used
        $usersCount = $role->users()->count();
        if ($usersCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "Không thể xóa vai trò đang được sử dụng bởi {$usersCount} người dùng"
            ], 422);
        }
        
        try {
            $role->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Xóa vai trò thành công'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }
}

