<?php

namespace App\Http\Controllers\Admin\CMS;

use App\Helpers\ArrayHelper;
use App\Helpers\Message;
use App\Helpers\UploadImage;
use App\Http\Controllers\Controller;
use App\Services\LogsUserService;
use App\Services\RoleService;
use App\Services\ValidationService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Session;

class UsersController extends Controller
{
    protected $request;
    protected $userService;
    protected $validator;
    protected $roleService;

    function __construct(Request $request, ValidationService $validator, UserService $userService, RoleService $roleService)
    {
        $this->request = $request;
        $this->validator = $validator;
        $this->userService = $userService;
        $this->roleService = $roleService;
    }


    /**
     * METHOD index - View List News
     *
     * @return void
     */

    public function index()
    {

        return view('admin.users.index');
    }


    /**
     * METHOD index - View List News
     *
     * @return void
     */

    public function ajaxGetList()
    {
        $params = $this->request->all();

        $result = $this->userService->getList($params);

        return response()->json($result);
    }

    /**
     * METHOD viewInsert - VIEW ADD, EDIT NEWS
     *
     * @return void
     */

    public function add()
    {
        $roles = $this->roleService->getRolesForSelect();

        return view('admin.users.add', ['roles' => $roles]);
    }

    public function addAction()
    {
        $params = $this->request->only('email', 'username', 'full_name', 'password', 'group_id', 'profile_avatar');
        $params = ArrayHelper::removeArrayNull($params);
        $validator = $this->validator->make($params, 'add_user_fields');
        if ($validator->fails()) {
            return response()->json(Message::get(1, $lang = '', $validator->errors()->all()), 400);
        }

        $upload = UploadImage::uploadAvatar($params['profile_avatar'], 'user');
        if (!$upload['success']) {
            return response()->json(Message::get(13, $lang = '', []), 400);
        }

        $params['url_avatar'] = $upload['url'];

        $add = $this->userService->add($params);
        if ($add) {
            //add log
            $log['action'] = "Thêm mới 1 User có id = " . $add;
            $log['content'] = json_encode($params);
            $log['ip'] = $this->request->ip();
            LogsUserService::add($log);

            $this->userService->sendMailActiveUser($params['email']);
            $data['success'] = true;
            $data['message'] = "Thêm mới User thành công !!!";
        } else {
            $data['message'] = "Lỗi khi thêm mới User !";
        }

        return response()->json($data);
    }

    public function deleteMany()
    {
        $params = $this->request->only('ids', 'total');
        if (!isset($params['ids'])) {
            return response()->json(Message::get(26, $lang = '', []), 400);
        }
        $delete = $this->userService->deleteMany($params['ids']);
        if (!$delete) {
            return response()->json(Message::get(12, $lang = '', []), 400);
        }

        Message::alertFlash("Bạn đã xóa tổng cộng " . $params['total'] . " User thành công !!!", 'success');

        //add log
        $log['action'] = "Xóa " . $params['total'] . " User thành công";
        $log['content'] = json_encode($params);
        $log['ip'] = $this->request->ip();
        LogsUserService::add($log);

        $data['success'] = true;
        $data['message'] = "Bạn đã xóa tổng cộng " . $params['total'] . " User thành công !!!";
        return response()->json($data);
    }

    public function delete($id)
    {
        $user = $this->userService->detail($id);
        $delete = $this->userService->delete($id);
        if ($delete) {
            //add log
            $log['action'] = "Xóa User thành công có ID = " . $id;
            $log['content'] = json_encode($user);
            $log['ip'] = $this->request->ip();
            LogsUserService::add($log);

            Message::alertFlash('Bạn đã xóa user thành công', 'success');
        } else {
            Message::alertFlash('Bạn đã xóa user không thành công', 'danger');
        }

        return redirect("user");
    }

    public function detail($id)
    {
        $userInfo = $this->userService->detail($id);
        $roles = $this->roleService->getRolesForSelect();

        return view('admin.users.detail', ['userInfo' => $userInfo, 'roles' => $roles]);
    }

    public function edit($id)
    {
        $user = User::with(['roles', 'branchShops'])->findOrFail($id);
        $roles = \App\Models\Role::active()->ordered()->get();
        $availableBranchShops = \App\Models\BranchShop::active()
            ->whereNotIn('id', $user->branchShops->pluck('id'))
            ->get();

        return view('admin.users.edit', compact('user', 'roles', 'availableBranchShops'));
    }

    public function editAction($id)
    {
        $params = $this->request->only(['full_name', 'password', 'group_id', 'avatar', 'status']);
        $params = ArrayHelper::removeArrayNull($params);
        $validator = $this->validator->make($params, 'edit_user_fields');
        if ($validator->fails()) {
            return response()->json(Message::get(1, $lang = '', $validator->errors()->all(), 400));
        }

        if (isset($params['avatar'])) {
            $upload = UploadImage::uploadAvatar($params['avatar'], 'user');
            if (!$upload['success']) {
                return response()->json(Message::get(13, $lang = '', []), 400);
            }
            $params['avatar'] = $upload['url'];
        }

        if (isset($params['password']) && $params['password'] != '') {
            $params['password'] = bcrypt($params['password']);
        }


        $edit = $this->userService->edit($id, $params);
        if (!$edit) {
            return response()->json(Message::get(13, $lang = '', []), 400);
        }

        //add log
        $log['action'] = "Cập nhập User thành công có ID = " . $id;
        $log['content'] = json_encode($params);
        $log['ip'] = $this->request->ip();
        LogsUserService::add($log);

        $data['success'] = true;
        $data['message'] = "Cập nhập User thành công !!!";
        return response()->json($data);
    }

    public function editManyAction()
    {
        $params = $this->request->only(['status', 'ids', 'total']);
        $params = ArrayHelper::removeArrayNull($params);
        if (!isset($params['ids'])) {
            return response()->json(Message::get(26, $lang = '', []), 400);
        }
        $update = $this->userService->updateMany($params['ids'], ['status' => $params['status']]);
        if (!$update) {
            return response()->json(Message::get(12, $lang = '', []), 400);
        }

        Message::alertFlash("Bạn đã cập nhập tổng cộng " . $params['total'] . " User thành công !!!", 'success');

        //add log
        $log['action'] = "Cập nhập nhiều User thành công";
        $log['content'] = json_encode($params);
        $log['ip'] = $this->request->ip();
        LogsUserService::add($log);

        $data['success'] = true;
        $data['message'] = "Bạn đã cập nhập tổng cộng " . $params['total'] . " User thành công !!!";
        return response()->json($data);
    }

    /**
     * Update user information
     */
    public function update($id)
    {
        $user = User::findOrFail($id);

        $params = $this->request->only([
            'username', 'email', 'full_name', 'phone', 'address',
            'birth_date', 'password', 'description', 'status', 'roles'
        ]);

        // Validation
        $rules = [
            'username' => 'required|string|max:255|unique:users,username,' . $id,
            'email' => 'required|email|max:255|unique:users,email,' . $id,
            'full_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8',
            'status' => 'required|in:active,inactive,blocked',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id'
        ];

        $validator = \Validator::make($params, $rules);
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Update user data
        $updateData = [
            'username' => $params['username'],
            'email' => $params['email'],
            'full_name' => $params['full_name'],
            'phone' => $params['phone'] ?? null,
            'address' => $params['address'] ?? null,
            'birth_date' => $params['birth_date'] ?? null,
            'description' => $params['description'] ?? null,
            'status' => $params['status'],
        ];

        // Update password if provided
        if (!empty($params['password'])) {
            $updateData['password'] = bcrypt($params['password']);
        }

        $user->update($updateData);

        // Update roles if user has permission
        if (auth()->user()->can('manage_user_roles') && isset($params['roles'])) {
            $user->roles()->sync($params['roles']);
        }

        // Handle avatar upload
        if ($this->request->hasFile('avatar')) {
            // Handle avatar upload logic here
        }

        return redirect()->route('admin.users.edit', $id)
            ->with('success', __('users.messages.updated_success'));
    }

    /**
     * Assign branch shop to user
     */
    public function assignBranchShop($userId)
    {
        $user = User::findOrFail($userId);

        $params = $this->request->only([
            'branch_shop_id', 'role_in_shop', 'start_date',
            'end_date', 'notes', 'is_active', 'is_primary'
        ]);

        // Validation
        $rules = [
            'branch_shop_id' => 'required|exists:branch_shops,id',
            'role_in_shop' => 'required|in:manager,staff,cashier,sales,warehouse_keeper',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'is_active' => 'boolean',
            'is_primary' => 'boolean'
        ];

        $validator = \Validator::make($params, $rules);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 400);
        }

        // Check if user already assigned to this branch shop
        if ($user->branchShops()->where('branch_shop_id', $params['branch_shop_id'])->exists()) {
            return response()->json([
                'success' => false,
                'message' => __('users.messages.branch_shop_already_assigned')
            ], 400);
        }

        // If setting as primary, unset other primary branches
        if (!empty($params['is_primary'])) {
            $user->branchShops()->updateExistingPivot(
                $user->branchShops->pluck('id')->toArray(),
                ['is_primary' => false]
            );
        }

        // Attach branch shop to user
        $user->branchShops()->attach($params['branch_shop_id'], [
            'role_in_shop' => $params['role_in_shop'],
            'start_date' => $params['start_date'] ?? now()->toDateString(),
            'end_date' => $params['end_date'] ?? null,
            'is_active' => $params['is_active'] ?? true,
            'is_primary' => $params['is_primary'] ?? false,
            'notes' => $params['notes'] ?? null,
            'assigned_by' => auth()->id(),
            'assigned_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => __('users.messages.branch_shop_assigned')
        ]);
    }

    /**
     * Update user branch shop assignment
     */
    public function updateBranchShop($userId, $branchShopId)
    {
        $user = User::findOrFail($userId);

        $params = $this->request->only([
            'role_in_shop', 'start_date', 'end_date',
            'notes', 'is_active', 'is_primary'
        ]);

        // Validation
        $rules = [
            'role_in_shop' => 'required|in:manager,staff,cashier,sales,warehouse_keeper',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'is_active' => 'boolean',
            'is_primary' => 'boolean'
        ];

        $validator = Validator::make($params, $rules);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 400);
        }

        // Check if assignment exists
        if (!$user->branchShops()->where('branch_shop_id', $branchShopId)->exists()) {
            return response()->json([
                'success' => false,
                'message' => __('users.messages.branch_shop_not_assigned')
            ], 400);
        }

        // If setting as primary, unset other primary branches
        if (!empty($params['is_primary'])) {
            $user->branchShops()->updateExistingPivot(
                $user->branchShops->where('id', '!=', $branchShopId)->pluck('id')->toArray(),
                ['is_primary' => false]
            );
        }

        // Update assignment
        $user->branchShops()->updateExistingPivot($branchShopId, [
            'role_in_shop' => $params['role_in_shop'],
            'start_date' => $params['start_date'],
            'end_date' => $params['end_date'],
            'is_active' => $params['is_active'] ?? true,
            'is_primary' => $params['is_primary'] ?? false,
            'notes' => $params['notes'],
        ]);

        return response()->json([
            'success' => true,
            'message' => __('users.messages.branch_shop_updated')
        ]);
    }

    /**
     * Remove user from branch shop
     */
    public function removeBranchShop($userId, $branchShopId)
    {
        $user = User::findOrFail($userId);

        // Check if assignment exists
        if (!$user->branchShops()->where('branch_shop_id', $branchShopId)->exists()) {
            return response()->json([
                'success' => false,
                'message' => __('users.messages.branch_shop_not_assigned')
            ], 400);
        }

        // Remove assignment
        $user->branchShops()->detach($branchShopId);

        return response()->json([
            'success' => true,
            'message' => __('users.messages.branch_shop_removed')
        ]);
    }

    /**
     * Get available users for dropdown (not already in the branch shop)
     */
    public function getAvailableForDropdown(Request $request)
    {
        try {
            $search = $request->get('q', '');
            $branchShopId = $request->get('branch_shop_id');

            $query = User::where('status', 'active');

            // Exclude users already in this branch shop
            if ($branchShopId) {
                $query->whereDoesntHave('branchShops', function($q) use ($branchShopId) {
                    $q->where('branch_shop_id', $branchShopId);
                });
            }

            // Search filter
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('username', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            $users = $query->select('id', 'username', 'email', 'full_name')
                ->orderBy('username')
                ->limit(20)
                ->get()
                ->map(function($user) {
                    return [
                        'id' => $user->id,
                        'text' => ($user->full_name ?: $user->username) . ' (' . $user->email . ')'
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $users
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải danh sách người dùng: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get users list for dropdown selection
     */
    public function listForDropdown(Request $request)
    {
        try {
            $search = $request->get('q', '');

            $query = User::where('status', 'active');

            // Search filter
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%")
                      ->orWhere('username', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            $users = $query->select('id', 'full_name', 'username', 'email')
                ->orderBy('full_name')
                ->limit(50)
                ->get()
                ->map(function($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->full_name,
                        'username' => $user->username,
                        'email' => $user->email
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $users
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải danh sách người dùng: ' . $e->getMessage()
            ], 500);
        }
    }
}
