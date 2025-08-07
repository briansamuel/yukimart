<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\V1\UserResource;
use App\Http\Requests\Api\V1\Auth\UpdateProfileRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends BaseApiController
{
    /**
     * Get user profile
     */
    public function profile(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            $data = [
                'user' => new UserResource($user),
                'permissions' => $user->getAllPermissions()->pluck('name'),
                'roles' => $user->roles->pluck('name'),
                'branches' => $user->branchShops->map(function ($branch) {
                    return [
                        'id' => $branch->id,
                        'name' => $branch->name,
                        'address' => $branch->address,
                        'phone' => $branch->phone,
                        'role' => $branch->pivot->role_in_shop ?? 'staff',
                        'start_date' => $branch->pivot->start_date,
                        'is_active' => is_null($branch->pivot->end_date)
                    ];
                }),
                'statistics' => [
                    'total_orders' => $user->createdOrders()->count(),
                    'total_invoices' => $user->createdInvoices()->count(),
                    'total_payments' => $user->createdPayments()->count(),
                    'last_login' => $user->last_visit,
                ]
            ];

            return $this->successResponse($data, 'Profile retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Update user profile
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            $data = $request->validated();

            // Remove sensitive fields
            unset($data['password'], $data['email'], $data['status'], $data['group_id']);

            $user->update($data);

            return $this->successResponse(
                new UserResource($user->fresh()),
                'Profile updated successfully'
            );

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Get user permissions
     */
    public function permissions(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            $data = [
                'permissions' => $user->getAllPermissions()->map(function ($permission) {
                    return [
                        'name' => $permission->name,
                        'display_name' => $permission->display_name,
                        'module' => $permission->module,
                        'action' => $permission->action,
                    ];
                }),
                'roles' => $user->roles->map(function ($role) {
                    return [
                        'name' => $role->name,
                        'display_name' => $role->display_name,
                        'description' => $role->description,
                    ];
                })
            ];

            return $this->successResponse($data, 'Permissions retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Get user branches
     */
    public function branches(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            $branches = $user->branchShops->map(function ($branch) {
                return [
                    'id' => $branch->id,
                    'name' => $branch->name,
                    'address' => $branch->address,
                    'phone' => $branch->phone,
                    'email' => $branch->email,
                    'manager' => $branch->manager,
                    'status' => $branch->status,
                    'role' => $branch->pivot->role_in_shop ?? 'staff',
                    'start_date' => $branch->pivot->start_date,
                    'end_date' => $branch->pivot->end_date,
                    'is_active' => is_null($branch->pivot->end_date),
                    'permissions' => $this->getBranchPermissions($branch->pivot->role_in_shop ?? 'staff')
                ];
            });

            return $this->successResponse($branches, 'Branches retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * Get permissions based on branch role
     */
    private function getBranchPermissions(string $role): array
    {
        $permissions = [
            'manager' => [
                'view_all_orders',
                'create_orders',
                'edit_orders',
                'delete_orders',
                'view_all_invoices',
                'create_invoices',
                'edit_invoices',
                'delete_invoices',
                'view_payments',
                'create_payments',
                'edit_payments',
                'view_reports',
                'manage_inventory',
                'manage_customers',
            ],
            'staff' => [
                'view_orders',
                'create_orders',
                'edit_own_orders',
                'view_invoices',
                'create_invoices',
                'edit_own_invoices',
                'view_payments',
                'create_payments',
                'view_customers',
                'create_customers',
            ],
            'cashier' => [
                'view_orders',
                'create_orders',
                'view_invoices',
                'create_invoices',
                'view_payments',
                'create_payments',
                'view_customers',
            ],
            'sales' => [
                'view_orders',
                'create_orders',
                'edit_own_orders',
                'view_customers',
                'create_customers',
                'edit_customers',
            ],
            'warehouse_keeper' => [
                'view_orders',
                'view_invoices',
                'manage_inventory',
                'view_products',
            ],
        ];

        return $permissions[$role] ?? $permissions['staff'];
    }
}
