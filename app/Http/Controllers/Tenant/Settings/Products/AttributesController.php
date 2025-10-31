<?php

namespace App\Http\Controllers\Tenant\Settings\Products;

use App\Http\Controllers\Tenant\BaseTenantController;
use App\Services\AttributeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;

class AttributesController extends BaseTenantController
{
    protected $attributeService;

    public function __construct(AttributeService $attributeService)
    {
        parent::__construct();
        $this->attributeService = $attributeService;
    }

    /**
     * Display attributes management page
     */
    public function index()
    {
        return view('tenant.settings.products.attributes.index');
    }

    /**
     * Get attributes data for AJAX
     */
    public function getData(Request $request)
    {
        try {
            $filters = [
                'search' => $request->input('search'),
                'status' => $request->input('status'),
            ];

            $attributes = $this->attributeService->getAllAttributes($filters);

            // Format data
            $data = $attributes->map(function ($attribute) {
                return [
                    'id' => $attribute->id,
                    'name' => $attribute->name,
                    'slug' => $attribute->slug,
                    'type' => $attribute->type,
                    'status' => $attribute->status,
                    'status_label' => $attribute->status === 'active' ? 'Đang hoạt động' : 'Tạm ngưng',
                    'values_count' => $attribute->values->count(),
                    'values' => $attribute->values->map(function($value) {
                        return [
                            'id' => $value->id,
                            'value' => $value->value,
                            'color_code' => $value->color_code,
                        ];
                    }),
                    'created_at' => $attribute->created_at ? $attribute->created_at->format('d/m/Y H:i') : 'N/A',
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data,
                'total' => $attributes->count(),
            ]);

        } catch (Exception $e) {
            Log::error('Error in AttributesController@getData: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy dữ liệu'
            ], 500);
        }
    }

    /**
     * Store new attribute
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'type' => 'in:select,color,text,number',
                'status' => 'in:active,inactive',
            ], [
                'name.required' => 'Tên thuộc tính không được để trống',
                'name.max' => 'Tên thuộc tính không được vượt quá 255 ký tự',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu không hợp lệ',
                    'errors' => $validator->errors()
                ], 422);
            }

            $result = $this->attributeService->createAttribute($request->all());

            return response()->json($result, $result['success'] ? 200 : 500);

        } catch (Exception $e) {
            Log::error('Error in AttributesController@store: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo thuộc tính'
            ], 500);
        }
    }

    /**
     * Update attribute
     */
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'type' => 'in:select,color,text,number',
                'status' => 'in:active,inactive',
            ], [
                'name.required' => 'Tên thuộc tính không được để trống',
                'name.max' => 'Tên thuộc tính không được vượt quá 255 ký tự',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu không hợp lệ',
                    'errors' => $validator->errors()
                ], 422);
            }

            $result = $this->attributeService->updateAttribute($id, $request->all());

            return response()->json($result, $result['success'] ? 200 : 500);

        } catch (Exception $e) {
            Log::error('Error in AttributesController@update: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật thuộc tính'
            ], 500);
        }
    }

    /**
     * Delete attribute
     */
    public function destroy($id)
    {
        try {
            $result = $this->attributeService->deleteAttribute($id);

            return response()->json($result, $result['success'] ? 200 : 500);

        } catch (Exception $e) {
            Log::error('Error in AttributesController@destroy: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa thuộc tính'
            ], 500);
        }
    }

    /**
     * Store new attribute value
     */
    public function storeValue(Request $request, $attributeId)
    {
        try {
            $validator = Validator::make($request->all(), [
                'value' => 'required|string|max:255',
                'color_code' => 'nullable|string|max:7',
                'price_adjustment' => 'nullable|numeric',
                'status' => 'in:active,inactive',
            ], [
                'value.required' => 'Giá trị thuộc tính không được để trống',
                'value.max' => 'Giá trị thuộc tính không được vượt quá 255 ký tự',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu không hợp lệ',
                    'errors' => $validator->errors()
                ], 422);
            }

            $result = $this->attributeService->createAttributeValue($attributeId, $request->all());

            return response()->json($result, $result['success'] ? 200 : 500);

        } catch (Exception $e) {
            Log::error('Error in AttributesController@storeValue: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo giá trị thuộc tính'
            ], 500);
        }
    }

    /**
     * Update attribute value
     */
    public function updateValue(Request $request, $attributeId, $valueId)
    {
        try {
            $validator = Validator::make($request->all(), [
                'value' => 'required|string|max:255',
                'color_code' => 'nullable|string|max:7',
                'price_adjustment' => 'nullable|numeric',
                'status' => 'in:active,inactive',
            ], [
                'value.required' => 'Giá trị thuộc tính không được để trống',
                'value.max' => 'Giá trị thuộc tính không được vượt quá 255 ký tự',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu không hợp lệ',
                    'errors' => $validator->errors()
                ], 422);
            }

            $result = $this->attributeService->updateAttributeValue($valueId, $request->all());

            return response()->json($result, $result['success'] ? 200 : 500);

        } catch (Exception $e) {
            Log::error('Error in AttributesController@updateValue: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật giá trị thuộc tính'
            ], 500);
        }
    }

    /**
     * Delete attribute value
     */
    public function destroyValue($attributeId, $valueId)
    {
        try {
            $result = $this->attributeService->deleteAttributeValue($valueId);

            return response()->json($result, $result['success'] ? 200 : 500);

        } catch (Exception $e) {
            Log::error('Error in AttributesController@destroyValue: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa giá trị thuộc tính'
            ], 500);
        }
    }
}

