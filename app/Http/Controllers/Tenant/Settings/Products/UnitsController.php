<?php

namespace App\Http\Controllers\Tenant\Settings\Products;

use App\Http\Controllers\Tenant\BaseTenantController;
use App\Services\UnitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;

class UnitsController extends BaseTenantController
{
    protected $unitService;

    public function __construct(UnitService $unitService)
    {
        parent::__construct();
        $this->unitService = $unitService;
    }

    /**
     * Display units management page
     */
    public function index()
    {
        return view('tenant.settings.products.units.index');
    }

    /**
     * Get units data for AJAX
     */
    public function getData(Request $request)
    {
        try {
            $filters = [
                'search' => $request->input('search'),
                'is_active' => $request->input('is_active'),
            ];

            $units = $this->unitService->getAllUnits($filters);

            // Format data
            $data = $units->map(function ($unit) {
                return [
                    'id' => $unit->id,
                    'name' => $unit->name,
                    'is_active' => $unit->is_active,
                    'is_active_label' => $unit->is_active ? 'Đang hoạt động' : 'Tạm ngưng',
                    'sort_order' => $unit->sort_order,
                    'created_at' => $unit->created_at ? $unit->created_at->format('d/m/Y H:i') : 'N/A',
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data,
                'total' => $units->count(),
            ]);

        } catch (Exception $e) {
            Log::error('Error in UnitsController@getData: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy dữ liệu'
            ], 500);
        }
    }

    /**
     * Store new unit
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'is_active' => 'boolean',
            ], [
                'name.required' => 'Tên đơn vị tính không được để trống',
                'name.max' => 'Tên đơn vị tính không được vượt quá 255 ký tự',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu không hợp lệ',
                    'errors' => $validator->errors()
                ], 422);
            }

            $result = $this->unitService->createUnit($request->all());

            return response()->json($result, $result['success'] ? 200 : 500);

        } catch (Exception $e) {
            Log::error('Error in UnitsController@store: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo đơn vị tính'
            ], 500);
        }
    }

    /**
     * Update unit
     */
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'is_active' => 'boolean',
            ], [
                'name.required' => 'Tên đơn vị tính không được để trống',
                'name.max' => 'Tên đơn vị tính không được vượt quá 255 ký tự',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu không hợp lệ',
                    'errors' => $validator->errors()
                ], 422);
            }

            $result = $this->unitService->updateUnit($id, $request->all());

            return response()->json($result, $result['success'] ? 200 : 500);

        } catch (Exception $e) {
            Log::error('Error in UnitsController@update: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật đơn vị tính'
            ], 500);
        }
    }

    /**
     * Delete unit
     */
    public function destroy($id)
    {
        try {
            $result = $this->unitService->deleteUnit($id);

            return response()->json($result, $result['success'] ? 200 : 500);

        } catch (Exception $e) {
            Log::error('Error in UnitsController@destroy: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa đơn vị tính'
            ], 500);
        }
    }

    /**
     * Toggle unit status
     */
    public function toggleStatus($id)
    {
        try {
            $result = $this->unitService->toggleStatus($id);

            return response()->json($result, $result['success'] ? 200 : 500);

        } catch (Exception $e) {
            Log::error('Error in UnitsController@toggleStatus: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật trạng thái'
            ], 500);
        }
    }

    /**
     * Update sort order
     */
    public function updateSortOrder(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'sort_data' => 'required|array',
                'sort_data.*.id' => 'required|integer',
                'sort_data.*.sort_order' => 'required|integer',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu không hợp lệ',
                    'errors' => $validator->errors()
                ], 422);
            }

            $result = $this->unitService->updateSortOrder($request->input('sort_data'));

            return response()->json($result, $result['success'] ? 200 : 500);

        } catch (Exception $e) {
            Log::error('Error in UnitsController@updateSortOrder: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật thứ tự'
            ], 500);
        }
    }
}

