<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PermissionService
{
    protected $permission;

    public function __construct(Permission $permission)
    {
        $this->permission = $permission;
    }

    /**
     * Get all permissions with pagination and filters
     */
    public function getAllPermissions($request)
    {
        $query = $this->permission->with(['roles']);

        // Search filter
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('display_name', 'like', "%{$search}%")
                  ->orWhere('module', 'like', "%{$search}%")
                  ->orWhere('action', 'like', "%{$search}%");
            });
        }

        // Module filter
        if ($request->has('module') && !empty($request->module)) {
            $query->where('module', $request->module);
        }

        // Action filter
        if ($request->has('action') && !empty($request->action)) {
            $query->where('action', $request->action);
        }

        // Status filter
        if ($request->has('status') && $request->status !== '') {
            $query->where('is_active', $request->status);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'sort_order');
        $sortDirection = $request->get('sort_direction', 'asc');
        $query->orderBy($sortBy, $sortDirection);

        // Pagination
        $perPage = $request->get('per_page', 25);
        
        return $query->paginate($perPage);
    }

    /**
     * Get permissions data for DataTables
     */
    public function getPermissionsForDataTable($request)
    {
        $query = $this->permission->with(['roles']);

        // Search filter
        if ($request->has('search') && !empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('display_name', 'like', "%{$search}%")
                  ->orWhere('module', 'like', "%{$search}%")
                  ->orWhere('action', 'like', "%{$search}%");
            });
        }

        // Custom filters
        if ($request->has('module') && !empty($request->module)) {
            $query->where('module', $request->module);
        }

        if ($request->has('action') && !empty($request->action)) {
            $query->where('action', $request->action);
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('is_active', $request->status);
        }

        // Get total count before pagination
        $totalRecords = $this->permission->count();
        $filteredRecords = $query->count();

        // Ordering
        if ($request->has('order')) {
            $columns = ['checkbox', 'name', 'display_name', 'module', 'action', 'roles_count', 'status', 'actions'];
            $orderColumn = $columns[$request->order[0]['column']] ?? 'sort_order';
            $orderDirection = $request->order[0]['dir'] ?? 'asc';

            if ($orderColumn !== 'checkbox' && $orderColumn !== 'actions') {
                $query->orderBy($orderColumn, $orderDirection);
            }
        } else {
            $query->orderBy('sort_order', 'asc');
        }

        // Pagination
        $start = $request->get('start', 0);
        $length = $request->get('length', 25);

        if ($length != -1) {
            $query->offset($start)->limit($length);
        }

        $permissions = $query->get();

        // Format data for DataTables
        $data = $permissions->map(function($permission) {
            return [
                'id' => $permission->id,
                'checkbox' => '<div class="form-check form-check-sm form-check-custom form-check-solid"><input class="form-check-input" type="checkbox" value="' . $permission->id . '" /></div>',
                'name' => '<div class="d-flex align-items-center">
                    <div class="symbol symbol-circle symbol-50px overflow-hidden me-3">
                        <div class="symbol-label">
                            <i class="ki-duotone ' . ($permission->icon ?? 'ki-shield-tick') . ' fs-2x text-' . ($permission->badge_class ?? 'primary') . '">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                        </div>
                    </div>
                    <div class="d-flex flex-column">
                        <a href="' . route('admin.permissions.show', $permission->id) . '" class="text-gray-800 text-hover-primary mb-1">' . $permission->name . '</a>
                        <span class="text-muted">' . Str::limit($permission->description ?? '', 50) . '</span>
                    </div>
                </div>',
                'display_name' => '<span class="text-gray-800 fw-bold">' . $permission->display_name . '</span>',
                'module' => '<span class="badge badge-light-info">' . $permission->module_display_name . '</span>',
                'action' => '<span class="badge ' . ($permission->badge_class ?? 'badge-light-primary') . '">' . $permission->action_display_name . '</span>',
                'roles_count' => '<span class="text-gray-800 fw-bold">' . $permission->roles_count . '</span>',
                'status' => $permission->is_active
                    ? '<span class="badge badge-light-success">' . __('permissions.active') . '</span>'
                    : '<span class="badge badge-light-danger">' . __('permissions.inactive') . '</span>',
                'actions' => '<a href="#" class="btn btn-light btn-active-light-primary btn-flex btn-center btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                    ' . __('common.actions') . '
                    <i class="ki-duotone ki-down fs-5 ms-1"></i>
                </a>
                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4" data-kt-menu="true">
                    <div class="menu-item px-3">
                        <a href="' . route('admin.permissions.show', $permission->id) . '" class="menu-link px-3">' . __('permissions.view_permission') . '</a>
                    </div>
                    <div class="menu-item px-3">
                        <a href="' . route('admin.permissions.edit', $permission->id) . '" class="menu-link px-3">' . __('permissions.edit_permission') . '</a>
                    </div>
                    <div class="menu-item px-3">
                        <a href="#" class="menu-link px-3" data-kt-permission-table-filter="delete_row" data-permission-id="' . $permission->id . '">' . __('permissions.delete_permission') . '</a>
                    </div>
                </div>'
            ];
        });

        return [
            'draw' => intval($request->get('draw')),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data
        ];
    }

    /**
     * Get permission by ID
     */
    public function getPermissionById($id)
    {
        return $this->permission->with(['roles', 'users'])->find($id);
    }

    /**
     * Create new permission
     */
    public function createPermission(array $data)
    {
        try {
            // Auto-generate name if not provided
            if (empty($data['name'])) {
                $data['name'] = Permission::generateName($data['module'], $data['action']);
            }

            $permission = $this->permission->create([
                'name' => $data['name'],
                'display_name' => $data['display_name'],
                'module' => $data['module'],
                'action' => $data['action'],
                'description' => $data['description'] ?? null,
                'is_active' => $data['is_active'] ?? true,
                'sort_order' => $data['sort_order'] ?? 0,
            ]);

            return [
                'success' => true,
                'message' => 'Permission created successfully',
                'data' => $permission
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create permission: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update permission
     */
    public function updatePermission($id, array $data)
    {
        try {
            $permission = $this->permission->find($id);
            
            if (!$permission) {
                return [
                    'success' => false,
                    'message' => 'Permission not found'
                ];
            }

            // Auto-generate name if module or action changed
            if (isset($data['module']) || isset($data['action'])) {
                $module = $data['module'] ?? $permission->module;
                $action = $data['action'] ?? $permission->action;
                $data['name'] = Permission::generateName($module, $action);
            }

            $permission->update([
                'name' => $data['name'] ?? $permission->name,
                'display_name' => $data['display_name'],
                'module' => $data['module'] ?? $permission->module,
                'action' => $data['action'] ?? $permission->action,
                'description' => $data['description'] ?? $permission->description,
                'is_active' => $data['is_active'] ?? $permission->is_active,
                'sort_order' => $data['sort_order'] ?? $permission->sort_order,
            ]);

            return [
                'success' => true,
                'message' => 'Permission updated successfully',
                'data' => $permission
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to update permission: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Delete permission
     */
    public function deletePermission($id)
    {
        try {
            $permission = $this->permission->find($id);
            
            if (!$permission) {
                return [
                    'success' => false,
                    'message' => 'Permission not found'
                ];
            }

            // Check if permission is assigned to roles
            if ($permission->roles()->count() > 0) {
                return [
                    'success' => false,
                    'message' => 'Cannot delete permission that is assigned to roles'
                ];
            }

            $permission->delete();

            return [
                'success' => true,
                'message' => 'Permission deleted successfully'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to delete permission: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Toggle permission status
     */
    public function togglePermissionStatus($id)
    {
        try {
            $permission = $this->permission->find($id);
            
            if (!$permission) {
                return [
                    'success' => false,
                    'message' => 'Permission not found'
                ];
            }

            $permission->update(['is_active' => !$permission->is_active]);

            return [
                'success' => true,
                'message' => 'Permission status updated successfully',
                'data' => $permission
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to update permission status: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get permissions by module
     */
    public function getPermissionsByModule($module = null)
    {
        $query = $this->permission->active()->ordered();
        
        if ($module) {
            $query->where('module', $module);
        }
        
        return $query->get();
    }

    /**
     * Bulk delete permissions
     */
    public function bulkDeletePermissions(array $ids)
    {
        try {
            DB::beginTransaction();

            $permissions = $this->permission->whereIn('id', $ids)->get();
            $deletedCount = 0;
            $errors = [];

            foreach ($permissions as $permission) {
                // Skip permissions assigned to roles
                if ($permission->roles()->count() > 0) {
                    $errors[] = "Cannot delete permission assigned to roles: {$permission->display_name}";
                    continue;
                }

                $permission->delete();
                $deletedCount++;
            }

            DB::commit();

            $message = "Deleted {$deletedCount} permission(s)";
            if (!empty($errors)) {
                $message .= ". Errors: " . implode(', ', $errors);
            }

            return [
                'success' => true,
                'message' => $message
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            return [
                'success' => false,
                'message' => 'Failed to delete permissions: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Generate permissions for module
     */
    public function generatePermissionsForModule($module, array $actions)
    {
        try {
            DB::beginTransaction();

            $createdPermissions = [];
            $existingPermissions = [];

            foreach ($actions as $action) {
                $name = Permission::generateName($module, $action);
                
                // Check if permission already exists
                $existing = $this->permission->where('name', $name)->first();
                
                if ($existing) {
                    $existingPermissions[] = $existing->display_name;
                    continue;
                }

                // Create new permission
                $permission = Permission::createIfNotExists(
                    $module,
                    $action,
                    ucfirst($action) . ' ' . ucfirst(str_replace('_', ' ', $module)),
                    "Allow user to {$action} " . str_replace('_', ' ', $module)
                );

                $createdPermissions[] = $permission;
            }

            DB::commit();

            $message = "Created " . count($createdPermissions) . " permission(s) for module: {$module}";
            if (!empty($existingPermissions)) {
                $message .= ". Already existing: " . implode(', ', $existingPermissions);
            }

            return [
                'success' => true,
                'message' => $message,
                'data' => [
                    'created' => $createdPermissions,
                    'existing' => $existingPermissions
                ]
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            return [
                'success' => false,
                'message' => 'Failed to generate permissions: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get permissions for select options
     */
    public function getPermissionsForSelect($module = null)
    {
        $query = $this->permission->active()->ordered();
        
        if ($module) {
            $query->where('module', $module);
        }
        
        return $query->get(['id', 'name', 'display_name', 'module', 'action'])
                    ->map(function($permission) {
                        return [
                            'id' => $permission->id,
                            'name' => $permission->name,
                            'text' => $permission->display_name,
                            'module' => $permission->module,
                            'action' => $permission->action
                        ];
                    });
    }

    /**
     * Get permission statistics
     */
    public function getPermissionStatistics()
    {
        return [
            'total_permissions' => $this->permission->count(),
            'active_permissions' => $this->permission->active()->count(),
            'inactive_permissions' => $this->permission->where('is_active', false)->count(),
            'permissions_by_module' => $this->permission->select('module', DB::raw('count(*) as count'))
                                                      ->groupBy('module')
                                                      ->pluck('count', 'module'),
            'permissions_by_action' => $this->permission->select('action', DB::raw('count(*) as count'))
                                                       ->groupBy('action')
                                                       ->pluck('count', 'action'),
        ];
    }
}
