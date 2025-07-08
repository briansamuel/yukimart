<?php

namespace App\Http\Controllers\Admin\CMS;

use App\Http\Controllers\Controller;
use App\Services\BranchShopService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BranchShopController extends Controller
{
    protected $branchShopService;

    public function __construct(BranchShopService $branchShopService)
    {
        $this->branchShopService = $branchShopService;
    }

    /**
     * Display a listing of branch shops
     */
    public function index()
    {
        return view('admin.branch-shops.index');
    }

    /**
     * Get branch shops data for DataTables
     */
    public function getData(Request $request)
    {
        try {
            $data = $this->branchShopService->getList($request->all());
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải dữ liệu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for creating a new branch shop
     */
    public function create()
    {
        $managers = $this->branchShopService->getManagersForDropdown();
        return view('admin.branch-shops.create', compact('managers'));
    }

    /**
     * Store a newly created branch shop
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'nullable|string|max:20|unique:branch_shops,code',
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'province' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'ward' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'manager_id' => 'nullable|exists:users,id',
            'status' => 'required|in:active,inactive,maintenance',
            'shop_type' => 'required|in:flagship,standard,mini,kiosk',
            'opening_time' => 'nullable|date_format:H:i',
            'closing_time' => 'nullable|date_format:H:i',
            'area' => 'nullable|numeric|min:0',
            'staff_count' => 'nullable|integer|min:0',
            'has_delivery' => 'boolean',
            'delivery_radius' => 'nullable|numeric|min:0',
            'delivery_fee' => 'nullable|numeric|min:0',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'working_days' => 'nullable|array',
            'sort_order' => 'nullable|integer|min:0',
        ], [
            'name.required' => 'Tên chi nhánh là bắt buộc',
            'address.required' => 'Địa chỉ là bắt buộc',
            'province.required' => 'Tỉnh/Thành phố là bắt buộc',
            'district.required' => 'Quận/Huyện là bắt buộc',
            'ward.required' => 'Phường/Xã là bắt buộc',
            'status.required' => 'Trạng thái là bắt buộc',
            'shop_type.required' => 'Loại cửa hàng là bắt buộc',
            'email.email' => 'Email không đúng định dạng',
            'phone.max' => 'Số điện thoại không được quá 20 ký tự',
            'code.unique' => 'Mã chi nhánh đã tồn tại',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $request->all();
            $data['created_by'] = Auth::id();
            $data['updated_by'] = Auth::id();

            $branchShop = $this->branchShopService->create($data);

            return response()->json([
                'success' => true,
                'message' => 'Tạo chi nhánh thành công',
                'data' => $branchShop
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tạo chi nhánh: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified branch shop
     */
    public function show($id)
    {
        try {
            $branchShop = $this->branchShopService->findById($id);
            return view('admin.branch-shops.show', compact('branchShop'));
        } catch (\Exception $e) {
            return redirect()->route('admin.branch-shops.index')
                ->with('error', 'Không tìm thấy chi nhánh');
        }
    }

    /**
     * Show the form for editing the specified branch shop
     */
    public function edit($id)
    {
        try {
            $branchShop = $this->branchShopService->findById($id);
            $managers = $this->branchShopService->getManagersForDropdown();
            $warehouses = \App\Models\Warehouse::where('status', 'active')->orderBy('name')->get();
            return view('admin.branch-shops.edit', compact('branchShop', 'managers', 'warehouses'));
        } catch (\Exception $e) {
            return redirect()->route('admin.branch-shops.index')
                ->with('error', 'Không tìm thấy chi nhánh');
        }
    }

    /**
     * Update the specified branch shop
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'nullable|string|max:20|unique:branch_shops,code,' . $id,
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'province' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'ward' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'manager_id' => 'nullable|exists:users,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'status' => 'required|in:active,inactive,maintenance',
            'shop_type' => 'required|in:flagship,standard,mini,kiosk',
            'opening_time' => 'nullable|date_format:H:i',
            'closing_time' => 'nullable|date_format:H:i',
            'area' => 'nullable|numeric|min:0',
            'staff_count' => 'nullable|integer|min:0',
            'has_delivery' => 'boolean',
            'delivery_radius' => 'nullable|numeric|min:0',
            'delivery_fee' => 'nullable|numeric|min:0',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'working_days' => 'nullable|array',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $request->all();
            $data['updated_by'] = Auth::id();

            $branchShop = $this->branchShopService->update($id, $data);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật chi nhánh thành công',
                'data' => $branchShop
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi cập nhật chi nhánh: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified branch shop
     */
    public function destroy($id)
    {
        try {
            $this->branchShopService->delete($id);

            return response()->json([
                'success' => true,
                'message' => 'Xóa chi nhánh thành công'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi xóa chi nhánh: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get active branch shops for filters
     */
    public function getActiveBranchShops()
    {
        try {
            $branchShops = $this->branchShopService->getActiveForFilter();

            return response()->json([
                'success' => true,
                'branch_shops' => $branchShops
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải danh sách chi nhánh: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get active branch shops for dropdown
     */
    public function getActiveForDropdown()
    {
        try {
            $branchShops = $this->branchShopService->getActiveForDropdown();

            return response()->json([
                'success' => true,
                'data' => $branchShops
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải danh sách chi nhánh: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get managers for dropdown
     */
    public function getManagersForDropdown()
    {
        try {
            // Get users who can be managers (have manager role or admin role)
            $managers = \App\Models\User::whereHas('roles', function($query) {
                $query->whereIn('name', ['manager', 'admin', 'super-admin']);
            })
            ->where('status', 'active')
            ->select('id', 'full_name', 'email')
            ->orderBy('full_name')
            ->get()
            ->map(function($user) {
                return [
                    'id' => $user->id,
                    'text' => ($user->full_name ?: $user->username) . ' (' . $user->email . ')'
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $managers
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải danh sách quản lý: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk actions
     */
    public function bulkAction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:delete,activate,deactivate,maintenance',
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:branch_shops,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $action = $request->action;
            $ids = $request->ids;

            switch ($action) {
                case 'delete':
                    $this->branchShopService->bulkDelete($ids);
                    $message = 'Xóa các chi nhánh thành công';
                    break;
                case 'activate':
                    $this->branchShopService->bulkUpdateStatus($ids, 'active');
                    $message = 'Kích hoạt các chi nhánh thành công';
                    break;
                case 'deactivate':
                    $this->branchShopService->bulkUpdateStatus($ids, 'inactive');
                    $message = 'Vô hiệu hóa các chi nhánh thành công';
                    break;
                case 'maintenance':
                    $this->branchShopService->bulkUpdateStatus($ids, 'maintenance');
                    $message = 'Chuyển các chi nhánh sang chế độ bảo trì thành công';
                    break;
            }

            return response()->json([
                'success' => true,
                'message' => $message
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi thực hiện thao tác: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get branch shop statistics
     */
    public function getStatistics()
    {
        try {
            $statistics = $this->branchShopService->getStatistics();

            return response()->json([
                'success' => true,
                'data' => $statistics
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải thống kê: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get users data for branch shop
     */
    public function getUsersData(Request $request, $branchShopId)
    {
        try {
            $branchShop = $this->branchShopService->findById($branchShopId);
            if (!$branchShop) {
                return response()->json([
                    'draw' => intval($request->get('draw')),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                    'error' => 'Chi nhánh không tồn tại'
                ]);
            }

            $users = $this->branchShopService->getUsersForDataTable($branchShopId, $request);

            return response()->json($users);
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
     * Add user to branch shop
     */
    public function addUser(Request $request, $branchShopId)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'role_in_shop' => 'required|in:manager,staff,cashier,sales,warehouse_keeper',
            'start_date' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
            'is_primary' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $result = $this->branchShopService->addUserToBranchShop($branchShopId, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Thêm người dùng vào chi nhánh thành công',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi thêm người dùng: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove user from branch shop
     */
    public function removeUser(Request $request, $branchShopId, $userId)
    {
        try {
            $this->branchShopService->removeUserFromBranchShop($branchShopId, $userId);

            return response()->json([
                'success' => true,
                'message' => 'Xóa người dùng khỏi chi nhánh thành công'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi xóa người dùng: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user in branch shop
     */
    public function updateUser(Request $request, $branchShopId, $userId)
    {
        $validator = Validator::make($request->all(), [
            'role_in_shop' => 'required|in:manager,staff,cashier,sales,warehouse_keeper',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'notes' => 'nullable|string|max:1000',
            'is_primary' => 'boolean',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $result = $this->branchShopService->updateUserInBranchShop($branchShopId, $userId, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật thông tin người dùng thành công',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi cập nhật người dùng: ' . $e->getMessage()
            ], 500);
        }
    }
}
