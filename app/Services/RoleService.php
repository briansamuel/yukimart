<?php

namespace App\Services;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class RoleService
{
    protected $role;

    public function __construct(Role $role)
    {
        $this->role = $role;
    }

    /**
     * Get all roles with pagination and filters
     */
    public function getAllRoles($request)
    {
        $query = $this->role->with(['permissions']);

        // Search filter
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('display_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
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
     * Get role by ID
     */
    public function getRoleById($id)
    {
        return $this->role->with(['permissions', 'users'])->find($id);
    }

    /**
     * Create new role
     */
    public function createRole(array $data)
    {
        try {
            DB::beginTransaction();

            // Create role
            $role = $this->role->create([
                'name' => $data['name'],
                'display_name' => $data['display_name'],
                'description' => $data['description'] ?? null,
                'is_active' => $data['is_active'] ?? true,
                'sort_order' => $data['sort_order'] ?? 0,
                'settings' => $data['settings'] ?? null,
            ]);

            // Assign permissions
            if (isset($data['permissions']) && is_array($data['permissions'])) {
                $role->syncPermissions($data['permissions']);
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Role created successfully',
                'data' => $role->load('permissions')
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            return [
                'success' => false,
                'message' => 'Failed to create role: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update role
     */
    public function updateRole($id, array $data)
    {
        try {
            DB::beginTransaction();

            $role = $this->role->find($id);
            
            if (!$role) {
                return [
                    'success' => false,
                    'message' => 'Role not found'
                ];
            }

            // Prevent updating system roles
            if (in_array($role->name, ['admin']) && !Auth::user()->isAdmin()) {
                return [
                    'success' => false,
                    'message' => 'Cannot modify system role'
                ];
            }

            // Update role
            $role->update([
                'name' => $data['name'],
                'display_name' => $data['display_name'],
                'description' => $data['description'] ?? null,
                'is_active' => $data['is_active'] ?? $role->is_active,
                'sort_order' => $data['sort_order'] ?? $role->sort_order,
                'settings' => $data['settings'] ?? $role->settings,
            ]);

            // Update permissions
            if (isset($data['permissions']) && is_array($data['permissions'])) {
                $role->syncPermissions($data['permissions']);
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Role updated successfully',
                'data' => $role->load('permissions')
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            return [
                'success' => false,
                'message' => 'Failed to update role: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Delete role
     */
    public function deleteRole($id)
    {
        try {
            $role = $this->role->find($id);
            
            if (!$role) {
                return [
                    'success' => false,
                    'message' => 'Role not found'
                ];
            }

            // Prevent deleting system roles
            if (in_array($role->name, ['admin'])) {
                return [
                    'success' => false,
                    'message' => 'Cannot delete system role'
                ];
            }

            // Check if role has users
            if ($role->users()->count() > 0) {
                return [
                    'success' => false,
                    'message' => 'Cannot delete role that has assigned users'
                ];
            }

            $role->delete();

            return [
                'success' => true,
                'message' => 'Role deleted successfully'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to delete role: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Toggle role status
     */
    public function toggleRoleStatus($id)
    {
        try {
            $role = $this->role->find($id);
            
            if (!$role) {
                return [
                    'success' => false,
                    'message' => 'Role not found'
                ];
            }

            // Prevent deactivating admin role
            if ($role->name === 'admin' && $role->is_active) {
                return [
                    'success' => false,
                    'message' => 'Cannot deactivate admin role'
                ];
            }

            $role->update(['is_active' => !$role->is_active]);

            return [
                'success' => true,
                'message' => 'Role status updated successfully',
                'data' => $role
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to update role status: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Bulk delete roles
     */
    public function bulkDeleteRoles(array $ids)
    {
        try {
            DB::beginTransaction();

            $roles = $this->role->whereIn('id', $ids)->get();
            $deletedCount = 0;
            $errors = [];

            foreach ($roles as $role) {
                // Skip system roles
                if (in_array($role->name, ['admin'])) {
                    $errors[] = "Cannot delete system role: {$role->display_name}";
                    continue;
                }

                // Skip roles with users
                if ($role->users()->count() > 0) {
                    $errors[] = "Cannot delete role with assigned users: {$role->display_name}";
                    continue;
                }

                $role->delete();
                $deletedCount++;
            }

            DB::commit();

            $message = "Deleted {$deletedCount} role(s)";
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
                'message' => 'Failed to delete roles: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get roles for select options
     */
    public function getRolesForSelect()
    {
        return $this->role->active()
                         ->ordered()
                         ->get(['id', 'name', 'display_name'])
                         ->map(function($role) {
                             return [
                                 'id' => $role->id,
                                 'name' => $role->name,
                                 'text' => $role->display_name
                             ];
                         });
    }

    /**
     * Assign role to user
     */
    public function assignRoleToUser($userId, $roleId, $assignedBy = null, $expiresAt = null)
    {
        try {
            $role = $this->role->find($roleId);
            
            if (!$role) {
                return [
                    'success' => false,
                    'message' => 'Role not found'
                ];
            }

            $user = \App\Models\User::find($userId);
            
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User not found'
                ];
            }

            $user->assignRole($role, $assignedBy, $expiresAt);

            return [
                'success' => true,
                'message' => 'Role assigned successfully'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to assign role: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Remove role from user
     */
    public function removeRoleFromUser($userId, $roleId)
    {
        try {
            $user = \App\Models\User::find($userId);
            
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User not found'
                ];
            }

            $role = $this->role->find($roleId);
            
            if (!$role) {
                return [
                    'success' => false,
                    'message' => 'Role not found'
                ];
            }

            $user->removeRole($role);

            return [
                'success' => true,
                'message' => 'Role removed successfully'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to remove role: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get role statistics
     */
    public function getRoleStatistics()
    {
        return [
            'total_roles' => $this->role->count(),
            'active_roles' => $this->role->active()->count(),
            'inactive_roles' => $this->role->where('is_active', false)->count(),
            'roles_with_users' => $this->role->has('users')->count(),
            'roles_without_users' => $this->role->doesntHave('users')->count(),
        ];
    }
}
