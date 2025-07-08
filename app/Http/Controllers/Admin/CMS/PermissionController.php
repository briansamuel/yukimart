<?php

namespace App\Http\Controllers\Admin\CMS;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PermissionController extends Controller
{
    protected $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    /**
     * Display a listing of permissions
     */
    public function index(Request $request)
    {
        $permissions = $this->permissionService->getAllPermissions($request);
        $modules = Permission::getModules();
        $actions = Permission::getActions();
        
        return view('admin.permissions.index', compact('permissions', 'modules', 'actions'));
    }

    /**
     * Get permissions data for DataTables
     */
    public function getData(Request $request)
    {
        try {
            $permissions = $this->permissionService->getPermissionsForDataTable($request);

            return response()->json($permissions);
        } catch (\Exception $e) {
            return response()->json([
                'draw' => intval($request->get('draw')),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Lỗi khi tải dữ liệu: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Show the form for creating a new permission
     */
    public function create()
    {
        $modules = Permission::getModules();
        $actions = Permission::getActions();
        
        return view('admin.permissions.create', compact('modules', 'actions'));
    }

    /**
     * Store a newly created permission
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:permissions,name',
            'display_name' => 'required|string|max:255',
            'module' => 'required|string|max:255',
            'action' => 'required|string|max:255',
            'description' => 'nullable|string'
        ], [
            'name.required' => __('permissions.validation.name_required'),
            'name.unique' => __('permissions.validation.name_unique'),
            'display_name.required' => __('permissions.validation.display_name_required'),
            'module.required' => __('permissions.validation.module_required'),
            'action.required' => __('permissions.validation.action_required')
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $result = $this->permissionService->createPermission($request->all());
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => __('permissions.messages.created_success'),
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
                'message' => __('permissions.messages.created_error')
            ], 500);
        }
    }

    /**
     * Display the specified permission
     */
    public function show($id)
    {
        $permission = $this->permissionService->getPermissionById($id);
        
        if (!$permission) {
            abort(404, __('permissions.messages.not_found'));
        }
        
        return view('admin.permissions.show', compact('permission'));
    }

    /**
     * Show the form for editing the specified permission
     */
    public function edit($id)
    {
        $permission = $this->permissionService->getPermissionById($id);
        
        if (!$permission) {
            abort(404, __('permissions.messages.not_found'));
        }
        
        $modules = Permission::getModules();
        $actions = Permission::getActions();
        
        return view('admin.permissions.edit', compact('permission', 'modules', 'actions'));
    }

    /**
     * Update the specified permission
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:permissions,name,' . $id,
            'display_name' => 'required|string|max:255',
            'module' => 'required|string|max:255',
            'action' => 'required|string|max:255',
            'description' => 'nullable|string'
        ], [
            'name.required' => __('permissions.validation.name_required'),
            'name.unique' => __('permissions.validation.name_unique'),
            'display_name.required' => __('permissions.validation.display_name_required'),
            'module.required' => __('permissions.validation.module_required'),
            'action.required' => __('permissions.validation.action_required')
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $result = $this->permissionService->updatePermission($id, $request->all());
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => __('permissions.messages.updated_success'),
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
                'message' => __('permissions.messages.updated_error')
            ], 500);
        }
    }

    /**
     * Remove the specified permission
     */
    public function destroy($id)
    {
        try {
            $result = $this->permissionService->deletePermission($id);
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => __('permissions.messages.deleted_success')
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
                'message' => __('permissions.messages.deleted_error')
            ], 500);
        }
    }

    /**
     * Toggle permission status
     */
    public function toggleStatus($id)
    {
        try {
            $result = $this->permissionService->togglePermissionStatus($id);
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => __('permissions.messages.status_updated'),
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
                'message' => __('permissions.messages.status_error')
            ], 500);
        }
    }

    /**
     * Get permissions by module
     */
    public function getByModule(Request $request)
    {
        $module = $request->get('module');
        
        try {
            $permissions = $this->permissionService->getPermissionsByModule($module);
            
            return response()->json([
                'success' => true,
                'data' => $permissions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('permissions.messages.module_error')
            ], 500);
        }
    }

    /**
     * Bulk delete permissions
     */
    public function bulkDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:permissions,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $result = $this->permissionService->bulkDeletePermissions($request->ids);
            
            return response()->json([
                'success' => $result['success'],
                'message' => $result['message']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('permissions.messages.bulk_delete_error')
            ], 500);
        }
    }

    /**
     * Generate permissions for module
     */
    public function generateForModule(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'module' => 'required|string|max:255',
            'actions' => 'required|array',
            'actions.*' => 'string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $result = $this->permissionService->generatePermissionsForModule(
                $request->module, 
                $request->actions
            );
            
            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'data' => $result['data'] ?? null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('permissions.messages.generate_error')
            ], 500);
        }
    }
}
