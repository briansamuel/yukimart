<?php

namespace App\Http\Controllers\Admin\CMS;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use App\Services\RoleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    protected $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    /**
     * Display a listing of roles
     */
    public function index(Request $request)
    {
        $roles = $this->roleService->getAllRoles($request);
        $permissions = Permission::active()->ordered()->get()->groupBy('module');
    
        return view('admin.roles.index', compact('roles', 'permissions'));
    }

    /**
     * Show the form for creating a new role
     */
    public function create()
    {
        $permissions = Permission::active()->ordered()->get()->groupBy('module');
        
        return view('admin.roles.create', compact('permissions'));
    }

    /**
     * Store a newly created role
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:roles,name',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id'
        ], [
            'name.required' => __('roles.validation.name_required'),
            'name.unique' => __('roles.validation.name_unique'),
            'display_name.required' => __('roles.validation.display_name_required'),
            'permissions.*.exists' => __('roles.validation.permission_exists')
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $result = $this->roleService->createRole($request->all());
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => __('roles.messages.created_success'),
                    'data' => $result['data']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('roles.messages.created_error')
            ], 500);
        }
    }

    /**
     * Display the specified role
     */
    public function show($id)
    {
        $role = $this->roleService->getRoleById($id);
        
        if (!$role) {
            abort(404, __('roles.messages.not_found'));
        }
        
        return view('admin.roles.show', compact('role'));
    }

    /**
     * Show the form for editing the specified role
     */
    public function edit($id)
    {
        $role = $this->roleService->getRoleById($id);
        
        if (!$role) {
            abort(404, __('roles.messages.not_found'));
        }
        
        $permissions = Permission::active()->ordered()->get()->groupBy('module');
        $rolePermissions = $role->permissions->pluck('id')->toArray();
        
        return view('admin.roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    /**
     * Update the specified role
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:roles,name,' . $id,
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id'
        ], [
            'name.required' => __('roles.validation.name_required'),
            'name.unique' => __('roles.validation.name_unique'),
            'display_name.required' => __('roles.validation.display_name_required'),
            'permissions.*.exists' => __('roles.validation.permission_exists')
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $result = $this->roleService->updateRole($id, $request->all());
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => __('roles.messages.updated_success'),
                    'data' => $result['data']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('roles.messages.updated_error')
            ], 500);
        }
    }

    /**
     * Remove the specified role
     */
    public function destroy($id)
    {
        try {
            $result = $this->roleService->deleteRole($id);
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => __('roles.messages.deleted_success')
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('roles.messages.deleted_error')
            ], 500);
        }
    }

    /**
     * Toggle role status
     */
    public function toggleStatus($id)
    {
        try {
            $result = $this->roleService->toggleRoleStatus($id);
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => __('roles.messages.status_updated'),
                    'data' => $result['data']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('roles.messages.status_error')
            ], 500);
        }
    }

    /**
     * Get role permissions
     */
    public function getPermissions($id)
    {
        try {
            $role = $this->roleService->getRoleById($id);
            
            if (!$role) {
                return response()->json([
                    'success' => false,
                    'message' => __('roles.messages.not_found')
                ], 404);
            }
            
            $permissions = $role->getPermissionsByModule();
            
            return response()->json([
                'success' => true,
                'data' => $permissions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('roles.messages.permissions_error')
            ], 500);
        }
    }

    /**
     * Bulk delete roles
     */
    public function bulkDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:roles,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $result = $this->roleService->bulkDeleteRoles($request->ids);
            
            return response()->json([
                'success' => $result['success'],
                'message' => $result['message']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('roles.messages.bulk_delete_error')
            ], 500);
        }
    }
}
