<?php

namespace App\Http\Controllers\Admin\CMS;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class WarehouseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $query = Warehouse::query();

            // Search functionality
            if ($request->filled('search')) {
                $search = $request->get('search');
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%")
                      ->orWhere('address', 'like', "%{$search}%")
                      ->orWhere('manager_name', 'like', "%{$search}%");
                });
            }

            // Status filter
            if ($request->filled('status')) {
                $query->where('status', $request->get('status'));
            }

            $warehouses = $query->orderBy('name')->paginate(15);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => $warehouses,
                    'html' => view('admin.warehouses.partials.table', compact('warehouses'))->render()
                ]);
            }

            return view('admin.warehouses.index', compact('warehouses'));
        } catch (\Exception $e) {
            Log::error('Error loading warehouses: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi tải danh sách kho hàng'
                ], 500);
            }

            return redirect()->back()->with('error', 'Có lỗi xảy ra khi tải danh sách kho hàng');
        }
    }

    /**
     * Show the form for creating a new warehouse
     */
    public function create()
    {
        return view('admin.warehouses.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:warehouses,code',
            'description' => 'nullable|string',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'manager_name' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
            'is_default' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $validator->validated();

            // If this is set as default, unset other defaults
            if ($data['is_default'] ?? false) {
                Warehouse::where('is_default', true)->update(['is_default' => false]);
            }

            $warehouse = Warehouse::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Tạo kho hàng thành công',
                'data' => $warehouse
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating warehouse: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo kho hàng'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $warehouse = Warehouse::findOrFail($id);
            return view('admin.warehouses.show', compact('warehouse'));
        } catch (\Exception $e) {
            return redirect()->route('admin.warehouses.index')
                ->with('error', 'Không tìm thấy kho hàng');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $warehouse = Warehouse::findOrFail($id);
            return view('admin.warehouses.edit', compact('warehouse'));
        } catch (\Exception $e) {
            return redirect()->route('admin.warehouses.index')
                ->with('error', 'Không tìm thấy kho hàng');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:warehouses,code,' . $id,
            'description' => 'nullable|string',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'manager_name' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
            'is_default' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $warehouse = Warehouse::findOrFail($id);
            $data = $validator->validated();

            // If this is set as default, unset other defaults
            if ($data['is_default'] ?? false) {
                Warehouse::where('id', '!=', $id)->where('is_default', true)->update(['is_default' => false]);
            }

            $warehouse->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật kho hàng thành công',
                'data' => $warehouse
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating warehouse: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật kho hàng'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $warehouse = Warehouse::findOrFail($id);

            // Check if warehouse is being used by any branch shops
            $branchShopsCount = $warehouse->branchShops()->count();
            if ($branchShopsCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Không thể xóa kho hàng này vì đang được sử dụng bởi {$branchShopsCount} chi nhánh"
                ], 422);
            }

            $warehouse->delete();

            return response()->json([
                'success' => true,
                'message' => 'Xóa kho hàng thành công'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting warehouse: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa kho hàng'
            ], 500);
        }
    }
}
