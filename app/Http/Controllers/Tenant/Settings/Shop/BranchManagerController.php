<?php

namespace App\Http\Controllers\Tenant\Settings\Shop;

use App\Http\Controllers\Tenant\BaseTenantController;
use App\Models\BranchShop;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Exception;

class BranchManagerController extends BaseTenantController
{
    /**
     * Display branch management page
     */
    public function index()
    {
        return view('tenant.settings.shop.branch-manager.index');
    }

    /**
     * Get branches data for AJAX with pagination
     */
    public function getData(Request $request)
    {
        try {
            $tenantId = $this->getCurrentTenantId();

            $query = BranchShop::where('tenant_id', $tenantId)
                ->with(['manager', 'warehouse']);

            // Search
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%")
                      ->orWhere('address', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            // Filter by status
            if ($request->has('status') && $request->status !== '') {
                $query->where('status', $request->status);
            }

            // Filter by shop type
            if ($request->has('shop_type') && !empty($request->shop_type)) {
                $query->where('shop_type', $request->shop_type);
            }

            // Pagination
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 1);

            $branches = $query->orderBy('sort_order')
                ->orderBy('created_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            // Format data
            $data = $branches->map(function ($branch) {
                return [
                    'id' => $branch->id,
                    'code' => $branch->code ?? 'N/A',
                    'name' => $branch->name ?? 'N/A',
                    'address' => $branch->address ?? 'N/A',
                    'phone' => $branch->phone ?? 'N/A',
                    'email' => $branch->email ?? 'N/A',
                    'manager_name' => $branch->manager ? $branch->manager->full_name : 'Chưa có',
                    'warehouse_name' => $branch->warehouse ? $branch->warehouse->name : 'Chưa có',
                    'shop_type' => $branch->shop_type ?? 'standard',
                    'shop_type_label' => $branch->shop_type_label,
                    'status' => $branch->status ?? 'active',
                    'status_label' => $this->getStatusLabel($branch->status ?? 'active'),
                    'created_at' => $branch->created_at ? $branch->created_at->format('d/m/Y H:i') : 'N/A',
                ];
            });

            return response()->json([
                'draw' => $request->input('draw', 1),
                'recordsTotal' => $branches->total(),
                'recordsFiltered' => $branches->total(),
                'data' => $data,
                'success' => true,
                'pagination' => [
                    'current_page' => $branches->currentPage(),
                    'last_page' => $branches->lastPage(),
                    'per_page' => $branches->perPage(),
                    'total' => $branches->total(),
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Error in BranchManager getData: ' . $e->getMessage(), [
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
     * Get single branch shop detail
     */
    public function show()
    {
        try {
            $id = request()->route('id');
            $tenantId = $this->getCurrentTenantId();

            $branch = BranchShop::where('tenant_id', $tenantId)
                ->where('id', $id)
                ->with(['manager', 'warehouse'])
                ->first();

            if (!$branch) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy chi nhánh'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $branch->id,
                    'code' => $branch->code,
                    'name' => $branch->name,
                    'address' => $branch->address,
                    'province' => $branch->province,
                    'district' => $branch->district,
                    'ward' => $branch->ward,
                    'phone' => $branch->phone,
                    'email' => $branch->email,
                    'manager_id' => $branch->manager_id,
                    'warehouse_id' => $branch->warehouse_id,
                    'status' => $branch->status,
                    'shop_type' => $branch->shop_type,
                    'description' => $branch->description,
                    'opening_time' => $branch->opening_time ? $branch->opening_time->format('H:i') : null,
                    'closing_time' => $branch->closing_time ? $branch->closing_time->format('H:i') : null,
                    'working_days' => $branch->working_days,
                    'area' => $branch->area,
                    'has_delivery' => $branch->has_delivery,
                    'delivery_radius' => $branch->delivery_radius,
                    'delivery_fee' => $branch->delivery_fee,
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Error in BranchManager show: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store new branch shop
     */
    public function store(Request $request)
    {
        try {
            $tenantId = $this->getCurrentTenantId();

            // Validation
            $validator = Validator::make($request->all(), [
                'code' => [
                    'nullable',
                    'string',
                    'max:20',
                    Rule::unique('branch_shops')->where(function ($query) use ($tenantId) {
                        return $query->where('tenant_id', $tenantId);
                    })
                ],
                'name' => 'required|string|max:255',
                'address' => 'required|string',
                'province' => 'required|string|max:255',
                'district' => 'required|string|max:255',
                'ward' => 'required|string|max:255',
                'phone' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'manager_id' => 'nullable|exists:users,id',
                'warehouse_id' => 'nullable|exists:warehouses,id',
                'status' => 'required|in:active,inactive,maintenance',
                'shop_type' => 'required|in:flagship,standard,mini,kiosk',
                'opening_time' => 'nullable|date_format:H:i',
                'closing_time' => 'nullable|date_format:H:i',
                'area' => 'nullable|numeric|min:0',
                'has_delivery' => 'nullable|boolean',
                'delivery_radius' => 'nullable|numeric|min:0',
                'delivery_fee' => 'nullable|numeric|min:0',
            ], [
                'name.required' => 'Vui lòng nhập tên chi nhánh',
                'address.required' => 'Vui lòng nhập địa chỉ',
                'province.required' => 'Vui lòng chọn tỉnh/thành phố',
                'district.required' => 'Vui lòng chọn quận/huyện',
                'ward.required' => 'Vui lòng chọn phường/xã',
                'status.required' => 'Vui lòng chọn trạng thái',
                'shop_type.required' => 'Vui lòng chọn loại cửa hàng',
                'code.unique' => 'Mã chi nhánh đã tồn tại',
                'email.email' => 'Email không hợp lệ',
                'manager_id.exists' => 'Quản lý không tồn tại',
                'warehouse_id.exists' => 'Kho hàng không tồn tại',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu không hợp lệ',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Auto-generate code if not provided
            $code = $request->code;
            if (empty($code)) {
                $code = 'BR' . str_pad(BranchShop::where('tenant_id', $tenantId)->count() + 1, 4, '0', STR_PAD_LEFT);
            }

            // Create branch shop
            $branch = BranchShop::create([
                'tenant_id' => $tenantId,
                'code' => $code,
                'name' => $request->name,
                'address' => $request->address,
                'province' => $request->province,
                'district' => $request->district,
                'ward' => $request->ward,
                'phone' => $request->phone,
                'email' => $request->email,
                'manager_id' => $request->manager_id,
                'warehouse_id' => $request->warehouse_id,
                'status' => $request->status,
                'shop_type' => $request->shop_type,
                'description' => $request->description,
                'opening_time' => $request->opening_time,
                'closing_time' => $request->closing_time,
                'working_days' => $request->working_days,
                'area' => $request->area,
                'has_delivery' => $request->has_delivery ?? false,
                'delivery_radius' => $request->delivery_radius,
                'delivery_fee' => $request->delivery_fee,
                'sort_order' => BranchShop::where('tenant_id', $tenantId)->max('sort_order') + 1,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tạo chi nhánh thành công',
                'data' => $branch
            ]);

        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Error in BranchManager store: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo chi nhánh: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update branch shop
     */
    public function update(Request $request)
    {
        try {
            $id = request()->route('id');
            $tenantId = $this->getCurrentTenantId();

            $branch = BranchShop::where('tenant_id', $tenantId)
                ->where('id', $id)
                ->first();

            if (!$branch) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy chi nhánh'
                ], 404);
            }

            // Validation
            $validator = Validator::make($request->all(), [
                'code' => [
                    'nullable',
                    'string',
                    'max:20',
                    Rule::unique('branch_shops')->where(function ($query) use ($tenantId) {
                        return $query->where('tenant_id', $tenantId);
                    })->ignore($branch->id)
                ],
                'name' => 'required|string|max:255',
                'address' => 'required|string',
                'province' => 'required|string|max:255',
                'district' => 'required|string|max:255',
                'ward' => 'required|string|max:255',
                'phone' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'manager_id' => 'nullable|exists:users,id',
                'warehouse_id' => 'nullable|exists:warehouses,id',
                'status' => 'required|in:active,inactive,maintenance',
                'shop_type' => 'required|in:flagship,standard,mini,kiosk',
                'opening_time' => 'nullable|date_format:H:i',
                'closing_time' => 'nullable|date_format:H:i',
                'area' => 'nullable|numeric|min:0',
                'has_delivery' => 'nullable|boolean',
                'delivery_radius' => 'nullable|numeric|min:0',
                'delivery_fee' => 'nullable|numeric|min:0',
            ], [
                'name.required' => 'Vui lòng nhập tên chi nhánh',
                'address.required' => 'Vui lòng nhập địa chỉ',
                'province.required' => 'Vui lòng chọn tỉnh/thành phố',
                'district.required' => 'Vui lòng chọn quận/huyện',
                'ward.required' => 'Vui lòng chọn phường/xã',
                'status.required' => 'Vui lòng chọn trạng thái',
                'shop_type.required' => 'Vui lòng chọn loại cửa hàng',
                'code.unique' => 'Mã chi nhánh đã tồn tại',
                'email.email' => 'Email không hợp lệ',
                'manager_id.exists' => 'Quản lý không tồn tại',
                'warehouse_id.exists' => 'Kho hàng không tồn tại',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu không hợp lệ',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Update branch shop
            $branch->update([
                'code' => $request->code ?? $branch->code,
                'name' => $request->name,
                'address' => $request->address,
                'province' => $request->province,
                'district' => $request->district,
                'ward' => $request->ward,
                'phone' => $request->phone,
                'email' => $request->email,
                'manager_id' => $request->manager_id,
                'warehouse_id' => $request->warehouse_id,
                'status' => $request->status,
                'shop_type' => $request->shop_type,
                'description' => $request->description,
                'opening_time' => $request->opening_time,
                'closing_time' => $request->closing_time,
                'working_days' => $request->working_days,
                'area' => $request->area,
                'has_delivery' => $request->has_delivery ?? false,
                'delivery_radius' => $request->delivery_radius,
                'delivery_fee' => $request->delivery_fee,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật chi nhánh thành công',
                'data' => $branch
            ]);

        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Error in BranchManager update: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete branch shop
     */
    public function destroy()
    {
        try {
            $id = request()->route('id');
            $tenantId = $this->getCurrentTenantId();

            $branch = BranchShop::where('tenant_id', $tenantId)
                ->where('id', $id)
                ->first();

            if (!$branch) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy chi nhánh'
                ], 404);
            }

            // Check if branch has orders
            $ordersCount = $branch->orders()->count();
            if ($ordersCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Không thể xóa chi nhánh đang có {$ordersCount} đơn hàng"
                ], 422);
            }

            // Check if branch has invoices
            $invoicesCount = $branch->invoices()->count();
            if ($invoicesCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Không thể xóa chi nhánh đang có {$invoicesCount} hóa đơn"
                ], 422);
            }

            // Soft delete
            $branch->delete();

            return response()->json([
                'success' => true,
                'message' => 'Xóa chi nhánh thành công'
            ]);

        } catch (Exception $e) {
            Log::error('Error in BranchManager destroy: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get managers for dropdown
     */
    public function getManagers(Request $request)
    {
        try {
            $tenantId = $this->getCurrentTenantId();

            $query = User::where('tenant_id', $tenantId)
                ->where('status', 'active');

            // Search
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            $managers = $query->orderBy('full_name')
                ->limit(20)
                ->get(['id', 'full_name', 'email']);

            return response()->json([
                'success' => true,
                'data' => $managers->map(function($user) {
                    return [
                        'id' => $user->id,
                        'text' => $user->full_name . ' (' . $user->email . ')',
                        'full_name' => $user->full_name,
                    ];
                })
            ]);

        } catch (Exception $e) {
            Log::error('Error in BranchManager getManagers: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'data' => []
            ]);
        }
    }

    /**
     * Get warehouses for dropdown
     */
    public function getWarehouses(Request $request)
    {
        try {
            $tenantId = $this->getCurrentTenantId();

            $query = Warehouse::where('tenant_id', $tenantId)
                ->where('status', 'active');

            // Search
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%");
                });
            }

            $warehouses = $query->orderBy('name')
                ->limit(20)
                ->get(['id', 'name', 'code']);

            return response()->json([
                'success' => true,
                'data' => $warehouses->map(function($warehouse) {
                    return [
                        'id' => $warehouse->id,
                        'text' => $warehouse->name . ' (' . $warehouse->code . ')',
                        'name' => $warehouse->name,
                    ];
                })
            ]);

        } catch (Exception $e) {
            Log::error('Error in BranchManager getWarehouses: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'data' => []
            ]);
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
            'maintenance' => '<span class="badge badge-light-warning">Bảo trì</span>',
        ];

        return $labels[$status] ?? '<span class="badge badge-light-secondary">N/A</span>';
    }
}


