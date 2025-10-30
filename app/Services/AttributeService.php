<?php

namespace App\Services;

use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class AttributeService
{
    /**
     * Get all attributes for current tenant
     */
    public function getAllAttributes($filters = [])
    {
        $query = ProductAttribute::with('values');

        // Filter by status
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Search by name
        if (isset($filters['search']) && !empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        // Order by sort_order
        $query->ordered();

        return $query->get();
    }

    /**
     * Get attribute by ID with values
     */
    public function getAttributeById($id)
    {
        return ProductAttribute::with('values')->find($id);
    }

    /**
     * Create new attribute
     */
    public function createAttribute(array $data)
    {
        DB::beginTransaction();
        
        try {
            // Get max sort_order
            $maxSortOrder = ProductAttribute::max('sort_order') ?? 0;
            
            $attribute = ProductAttribute::create([
                'name' => $data['name'],
                'slug' => Str::slug($data['name']),
                'type' => $data['type'] ?? 'select',
                'description' => $data['description'] ?? null,
                'is_required' => $data['is_required'] ?? false,
                'is_variation' => $data['is_variation'] ?? true,
                'is_visible' => $data['is_visible'] ?? true,
                'sort_order' => $maxSortOrder + 1,
                'status' => $data['status'] ?? 'active',
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Tạo thuộc tính thành công',
                'data' => $attribute
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error in createAttribute: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo thuộc tính: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Update attribute
     */
    public function updateAttribute($id, array $data)
    {
        DB::beginTransaction();
        
        try {
            $attribute = ProductAttribute::findOrFail($id);
            
            $updateData = [
                'name' => $data['name'] ?? $attribute->name,
                'type' => $data['type'] ?? $attribute->type,
                'description' => $data['description'] ?? $attribute->description,
                'is_required' => $data['is_required'] ?? $attribute->is_required,
                'is_variation' => $data['is_variation'] ?? $attribute->is_variation,
                'is_visible' => $data['is_visible'] ?? $attribute->is_visible,
                'sort_order' => $data['sort_order'] ?? $attribute->sort_order,
                'status' => $data['status'] ?? $attribute->status,
            ];

            if (isset($data['name']) && $data['name'] !== $attribute->name) {
                $updateData['slug'] = Str::slug($data['name']);
            }

            $attribute->update($updateData);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Cập nhật thuộc tính thành công',
                'data' => $attribute
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error in updateAttribute: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật thuộc tính: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Delete attribute
     */
    public function deleteAttribute($id)
    {
        DB::beginTransaction();
        
        try {
            $attribute = ProductAttribute::findOrFail($id);
            
            // Delete all values first
            $attribute->allValues()->delete();
            
            // Delete attribute
            $attribute->delete();

            DB::commit();

            return [
                'success' => true,
                'message' => 'Xóa thuộc tính thành công'
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error in deleteAttribute: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa thuộc tính: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Create attribute value
     */
    public function createAttributeValue($attributeId, array $data)
    {
        DB::beginTransaction();
        
        try {
            $attribute = ProductAttribute::findOrFail($attributeId);
            
            // Get max sort_order for this attribute
            $maxSortOrder = ProductAttributeValue::where('attribute_id', $attributeId)->max('sort_order') ?? 0;
            
            $value = ProductAttributeValue::create([
                'attribute_id' => $attributeId,
                'value' => $data['value'],
                'slug' => Str::slug($data['value']),
                'color_code' => $data['color_code'] ?? null,
                'image' => $data['image'] ?? null,
                'description' => $data['description'] ?? null,
                'sort_order' => $maxSortOrder + 1,
                'price_adjustment' => $data['price_adjustment'] ?? 0,
                'status' => $data['status'] ?? 'active',
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Tạo giá trị thuộc tính thành công',
                'data' => $value
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error in createAttributeValue: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo giá trị thuộc tính: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Update attribute value
     */
    public function updateAttributeValue($id, array $data)
    {
        DB::beginTransaction();
        
        try {
            $value = ProductAttributeValue::findOrFail($id);
            
            $updateData = [
                'value' => $data['value'] ?? $value->value,
                'color_code' => $data['color_code'] ?? $value->color_code,
                'image' => $data['image'] ?? $value->image,
                'description' => $data['description'] ?? $value->description,
                'sort_order' => $data['sort_order'] ?? $value->sort_order,
                'price_adjustment' => $data['price_adjustment'] ?? $value->price_adjustment,
                'status' => $data['status'] ?? $value->status,
            ];

            if (isset($data['value']) && $data['value'] !== $value->value) {
                $updateData['slug'] = Str::slug($data['value']);
            }

            $value->update($updateData);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Cập nhật giá trị thuộc tính thành công',
                'data' => $value
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error in updateAttributeValue: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật giá trị thuộc tính: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Delete attribute value
     */
    public function deleteAttributeValue($id)
    {
        DB::beginTransaction();
        
        try {
            $value = ProductAttributeValue::findOrFail($id);
            $value->delete();

            DB::commit();

            return [
                'success' => true,
                'message' => 'Xóa giá trị thuộc tính thành công'
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error in deleteAttributeValue: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa giá trị thuộc tính: ' . $e->getMessage()
            ];
        }
    }
}

