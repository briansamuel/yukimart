<?php

namespace App\Http\Controllers\Tenant\Settings\Shop;

use App\Http\Controllers\Tenant\BaseTenantController;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Exception;

class UserManagerController extends BaseTenantController
{
    /**
     * Display user management page
     */
    public function index()
    {
        return view('tenant.settings.shop.user-manager.index');
    }

    /**
     * Get users data for AJAX with pagination
     */
    public function getData(Request $request)
    {
        try {
            $tenantId = $this->getCurrentTenantId();

            // Set permissions team ID for Spatie (multi-tenant support)
            setPermissionsTeamId($tenantId);

            $query = User::where('tenant_id', $tenantId)
                ->with(['roles']); // Eager load Spatie roles

            // Search
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            // Filter by branch
            if ($request->has('branch') && !empty($request->branch)) {
                $query->whereHas('branchShops', function($q) use ($request) {
                    $q->where('branch_shop_id', $request->branch);
                });
            }

            // Filter by role
            if ($request->has('role') && !empty($request->role)) {
                $roleIds = is_array($request->role) ? $request->role : [$request->role];
                $query->whereHas('userRoles', function($q) use ($roleIds) {
                    $q->whereIn('role_id', $roleIds);
                });
            }

            // Filter by status
            if ($request->has('status') && !empty($request->status)) {
                $statusArray = is_array($request->status) ? $request->status : explode(',', $request->status);
                $query->whereIn('status', $statusArray);
            }

            // Pagination
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 1);

            $users = $query->orderBy('created_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            // Format data
            $data = $users->map(function ($user) {
                // Get roles using Spatie
                $roles = $user->roles->map(function($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                        'display_name' => $role->display_name ?? ucfirst(str_replace('_', ' ', $role->name)),
                    ];
                });

                return [
                    'id' => $user->id,
                    'full_name' => $user->full_name ?? 'N/A',
                    'email' => $user->email ?? 'N/A',
                    'phone' => $user->phone ?? 'N/A',
                    'avatar' => $user->avatar ?? '',
                    'status' => $user->status ?? 'active',
                    'status_label' => $this->getStatusLabel($user->status ?? 'active'),
                    'roles' => $roles, // Array of roles
                    'created_at' => $user->created_at ? $user->created_at->format('d/m/Y H:i') : 'N/A',
                ];
            });

            return response()->json([
                'draw' => $request->input('draw', 1),
                'recordsTotal' => $users->total(),
                'recordsFiltered' => $users->total(),
                'data' => $data,
                'success' => true,
                'pagination' => [
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Error in UserManager getData: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'draw' => intval($request->input('draw', 1)),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'success' => false,
                'error' => 'Có lỗi xảy ra khi tải dữ liệu: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get roles data for AJAX with pagination
     */
    public function getRoles(Request $request)
    {
        try {
            $tenantId = $this->getCurrentTenantId();

            $query = \App\Models\Role::where('tenant_id', $tenantId)
                ->where('is_active', true);

            // Search
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('display_name', 'like', "%{$search}%");
                });
            }

            // Pagination for Select2
            $perPage = $request->input('per_page', 20);
            $page = $request->input('page', 1);

            $roles = $query->ordered()
                ->paginate($perPage, ['*'], 'page', $page);

            // Format data for Select2
            $data = $roles->map(function ($role) {
                return [
                    'id' => $role->id,
                    'text' => $role->display_name ?? $role->name,
                    'name' => $role->name,
                ];
            });

            return response()->json([
                'results' => $data,
                'pagination' => [
                    'more' => $roles->hasMorePages()
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Error in UserManager getRoles: ' . $e->getMessage());

            return response()->json([
                'results' => [],
                'pagination' => ['more' => false]
            ]);
        }
    }

    /**
     * Get roles data for table with user count
     */
    public function getRolesData(Request $request)
    {
        try {
            $tenantId = $this->getCurrentTenantId();

            // Optimized query with LEFT JOIN instead of withCount() for better performance
            $query = \App\Models\Role::select('roles.*', DB::raw('COUNT(DISTINCT CASE WHEN user_roles.is_active = 1 THEN user_roles.user_id END) as users_count'))
                ->leftJoin('user_roles', 'roles.id', '=', 'user_roles.role_id')
                ->leftJoin('users', function($join) use ($tenantId) {
                    $join->on('user_roles.user_id', '=', 'users.id')
                         ->where('users.tenant_id', '=', $tenantId);
                })
                ->where('roles.tenant_id', $tenantId)
                ->groupBy('roles.id', 'roles.tenant_id', 'roles.name', 'roles.display_name',
                         'roles.description', 'roles.is_active',
                         'roles.sort_order', 'roles.created_at', 'roles.updated_at');

            // Search
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('roles.name', 'like', "%{$search}%")
                      ->orWhere('roles.display_name', 'like', "%{$search}%")
                      ->orWhere('roles.description', 'like', "%{$search}%");
                });
            }

            // Filter by status
            if ($request->has('status') && !empty($request->status)) {
                $query->where('roles.is_active', $request->status === 'active');
            }

            // Pagination
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 1);

            $roles = $query->orderBy('roles.sort_order')->orderBy('roles.name')
                ->paginate($perPage, ['*'], 'page', $page);

            // Format data
            $data = $roles->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'display_name' => $role->display_name ?? ucfirst(str_replace('_', ' ', $role->name)),
                    'description' => $role->description ?? 'Chưa có',
                    'users_count' => $role->users_count,
                    'is_active' => $role->is_active,
                ];
            });

            return response()->json([
                'draw' => $request->input('draw', 1),
                'recordsTotal' => $roles->total(),
                'recordsFiltered' => $roles->total(),
                'data' => $data,
                'success' => true,
                'pagination' => [
                    'current_page' => $roles->currentPage(),
                    'last_page' => $roles->lastPage(),
                    'per_page' => $roles->perPage(),
                    'total' => $roles->total(),
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Error in UserManager getRolesData: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'draw' => intval($request->input('draw', 1)),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'success' => false,
                'error' => 'Có lỗi xảy ra khi tải dữ liệu: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Store new user
     */
    public function store(Request $request)
    {
        try {
            $tenantId = $this->getCurrentTenantId();

            // Validation
            $validator = Validator::make($request->all(), [
                'full_name' => 'required|string|max:255',
                'phone' => 'required|string|max:20',
                'email' => 'required|email|max:255|unique:users,email',
                'username' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('users')->where(function ($query) use ($tenantId) {
                        return $query->where('tenant_id', $tenantId);
                    })
                ],
                'password' => 'required|string|min:6|confirmed',
                'role_id' => 'nullable|exists:roles,id',
            ], [
                'full_name.required' => 'Vui lòng nhập tên hiển thị',
                'phone.required' => 'Vui lòng nhập số điện thoại',
                'email.required' => 'Vui lòng nhập email',
                'email.email' => 'Email không hợp lệ',
                'email.unique' => 'Email đã tồn tại',
                'username.required' => 'Vui lòng nhập tên đăng nhập',
                'username.unique' => 'Tên đăng nhập đã tồn tại trong hệ thống',
                'password.required' => 'Vui lòng nhập mật khẩu',
                'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự',
                'password.confirmed' => 'Mật khẩu xác nhận không khớp',
                'role_id.exists' => 'Vai trò không tồn tại',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu không hợp lệ',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Create user
            $user = User::create([
                'tenant_id' => $tenantId,
                'full_name' => $request->full_name,
                'phone' => $request->phone,
                'email' => $request->email,
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'address' => $request->address ?? '',
                'description' => $request->notes,
                'status' => 'active',
                'active_code' => '',
                'is_root' => false,
            ]);

            // Assign role if provided (using Spatie)
            if ($request->role_id) {
                // Set permissions team ID for Spatie (multi-tenant support)
                setPermissionsTeamId($tenantId);

                // Assign role using Spatie
                $user->assignRole($request->role_id);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tạo tài khoản thành công',
                'data' => $user
            ]);

        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Error in UserManager store: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo tài khoản: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user detail for editing
     * Note: No $id parameter to avoid conflict with subdomain route parameter
     * Use request()->route('id') instead
     */
    public function show(Request $request)
    {
        try {
            // Get id from route parameters explicitly to avoid conflict with subdomain parameter
            $id = request()->route('id');
            $tenantId = $this->getCurrentTenantId();

            // Find user in current tenant
            $user = User::where('id', $id)
                        ->where('tenant_id', $tenantId)
                        ->with(['roles']) // Load Spatie roles
                        ->firstOrFail();

            // Get roles using Spatie
            $roles = $user->roles->map(function($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'display_name' => $role->display_name ?? ucfirst(str_replace('_', ' ', $role->name)),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'full_name' => $user->full_name,
                    'username' => $user->username,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'roles' => $roles // Return array of roles
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user information
     * Note: No $id parameter to avoid conflict with subdomain route parameter
     * Use request()->route('id') instead
     */
    public function update(Request $request)
    {
        try {
            // Get id from route parameters explicitly to avoid conflict with subdomain parameter
            $id = request()->route('id');
            $tenantId = $this->getCurrentTenantId();
            $currentUser = Auth::user();

            // Find user
            $user = User::where('id', $id)
                ->where('tenant_id', $tenantId)
                ->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy người dùng'
                ], 404);
            }

            // Check permission: Only admin/owner can update (using Spatie)
            setPermissionsTeamId($tenantId);
            $hasPermission = $currentUser->hasAnyRole(['admin', 'owner']);

            if (!$hasPermission) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không có quyền cập nhật thông tin người dùng'
                ], 403);
            }

            // Validation rules
            $rules = [
                'full_name' => 'required|string|max:255',
                'phone' => 'required|string|max:20',
                'email' => [
                    'required',
                    'email',
                    'max:255',
                    Rule::unique('users')->ignore($user->id)
                ],
                'username' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('users')->ignore($user->id)
                ],
                'role_id' => 'nullable|exists:roles,id',
            ];

            $messages = [
                'full_name.required' => 'Vui lòng nhập tên hiển thị',
                'phone.required' => 'Vui lòng nhập số điện thoại',
                'email.required' => 'Vui lòng nhập email',
                'email.email' => 'Email không hợp lệ',
                'email.unique' => 'Email đã tồn tại',
                'username.required' => 'Vui lòng nhập tên đăng nhập',
                'username.unique' => 'Tên đăng nhập đã tồn tại',
                'role_id.exists' => 'Vai trò không tồn tại',
            ];

            // Add password validation if provided
            if ($request->filled('password')) {
                $rules['password'] = 'required|string|min:6|confirmed';
                $messages['password.required'] = 'Vui lòng nhập mật khẩu';
                $messages['password.min'] = 'Mật khẩu phải có ít nhất 6 ký tự';
                $messages['password.confirmed'] = 'Mật khẩu xác nhận không khớp';
            }

            // Validation
            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu không hợp lệ',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Prepare update data
            $updateData = [
                'full_name' => $request->full_name,
                'username' => $request->username,
                'phone' => $request->phone,
                'email' => $request->email,
            ];

            // Update password if provided
            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            // Update user
            $user->update($updateData);

            // Update role if provided (using Spatie)
            if ($request->role_id) {
                // Set permissions team ID for Spatie (multi-tenant support)
                setPermissionsTeamId($tenantId);

                // Sync role using Spatie (replaces all existing roles with new one)
                $user->syncRoles([$request->role_id]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật thông tin thành công',
                'data' => $user
            ]);

        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Error in UserManager update: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Change user password
     * Note: No $id parameter to avoid conflict with subdomain route parameter
     * Use request()->route('id') instead
     * Note: No old password verification required (admin can reset any user password)
     */
    public function changePassword(Request $request)
    {
        try {
            // Get id from route parameters explicitly to avoid conflict with subdomain parameter
            $id = request()->route('id');

            // Find user (no tenant check for password change)
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy người dùng'
                ], 404);
            }

            // Validation - No old password required
            $validator = Validator::make($request->all(), [
                'new_password' => 'required|string|min:6|confirmed',
            ], [
                'new_password.required' => 'Vui lòng nhập mật khẩu mới',
                'new_password.min' => 'Mật khẩu mới phải có ít nhất 6 ký tự',
                'new_password.confirmed' => 'Mật khẩu xác nhận không khớp',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu không hợp lệ',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Update password directly (no old password verification)
            $user->update([
                'password' => Hash::make($request->new_password)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Đổi mật khẩu thành công'
            ]);

        } catch (Exception $e) {
            Log::error('Error in UserManager changePassword: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi đổi mật khẩu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Deactivate user
     * Note: No $id parameter to avoid conflict with subdomain route parameter
     * Use request()->route('id') instead
     */
    public function deactivate()
    {
        try {
            // Get id from route parameters explicitly to avoid conflict with subdomain parameter
            $id = request()->route('id');

            $tenantId = $this->getCurrentTenantId();
            $currentUser = Auth::user();

            // Check if trying to deactivate self
            if ($currentUser->id == $id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không thể ngừng hoạt động tài khoản của chính mình'
                ], 403);
            }

            // Find user
            $user = User::where('id', $id)
                ->where('tenant_id', $tenantId)
                ->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy người dùng'
                ], 404);
            }

            // Update status
            $user->update([
                'status' => 'deactive'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Ngừng hoạt động tài khoản thành công'
            ]);

        } catch (Exception $e) {
            Log::error('Error in UserManager deactivate: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi ngừng hoạt động: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get status label
     */
    private function getStatusLabel($status)
    {
        $labels = [
            'active' => '<span class="badge badge-light-success">Hoạt động</span>',
            'inactive' => '<span class="badge badge-light-danger">Không hoạt động</span>',
        ];

        return $labels[$status] ?? '<span class="badge badge-light-secondary">N/A</span>';
    }

    /**
     * Assign roles to user
     * Note: No $id parameter to avoid conflict with subdomain route parameter
     * Use request()->route('id') instead
     */
    public function assignRoles(Request $request)
    {
        try {
            // Get id from route parameters explicitly to avoid conflict with subdomain parameter
            $id = request()->route('id');
            $tenantId = $this->getCurrentTenantId();

            // Validate request
            $request->validate([
                'roles' => 'required|array',
                'roles.*' => 'exists:roles,id'
            ]);

            // Find user in current tenant
            $user = User::where('id', $id)
                        ->where('tenant_id', $tenantId)
                        ->firstOrFail();

            // Set permissions team ID for Spatie (multi-tenant support)
            setPermissionsTeamId($tenantId);

            // Sync roles using Spatie
            $user->syncRoles($request->roles);

            return response()->json([
                'success' => true,
                'message' => 'Gán vai trò thành công'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }
}

