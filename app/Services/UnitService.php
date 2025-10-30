<?php

namespace App\Services;

use App\Models\Unit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class UnitService
{
    /**
     * Get all units for current tenant
     */
    public function getAllUnits($filters = [])
    {
        $query = Unit::query();

        // Filter by status
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
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
     * Get unit by ID
     */
    public function getUnitById($id)
    {
        return Unit::find($id);
    }

    /**
     * Create new unit
     */
    public function createUnit(array $data)
    {
        DB::beginTransaction();
        
        try {
            // Get max sort_order
            $maxSortOrder = Unit::max('sort_order') ?? 0;
            
            $unit = Unit::create([
                'name' => $data['name'],
                'is_active' => $data['is_active'] ?? true,
                'sort_order' => $maxSortOrder + 1,
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Tạo đơn vị tính thành công',
                'data' => $unit
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error in createUnit: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo đơn vị tính: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Update unit
     */
    public function updateUnit($id, array $data)
    {
        DB::beginTransaction();
        
        try {
            $unit = Unit::findOrFail($id);
            
            $unit->update([
                'name' => $data['name'] ?? $unit->name,
                'is_active' => $data['is_active'] ?? $unit->is_active,
                'sort_order' => $data['sort_order'] ?? $unit->sort_order,
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Cập nhật đơn vị tính thành công',
                'data' => $unit
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error in updateUnit: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật đơn vị tính: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Delete unit
     */
    public function deleteUnit($id)
    {
        DB::beginTransaction();
        
        try {
            $unit = Unit::findOrFail($id);
            $unit->delete();

            DB::commit();

            return [
                'success' => true,
                'message' => 'Xóa đơn vị tính thành công'
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error in deleteUnit: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa đơn vị tính: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Toggle unit status
     */
    public function toggleStatus($id)
    {
        DB::beginTransaction();
        
        try {
            $unit = Unit::findOrFail($id);
            $unit->update(['is_active' => !$unit->is_active]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Cập nhật trạng thái thành công',
                'data' => $unit
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error in toggleStatus: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật trạng thái: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Update sort order for multiple units
     */
    public function updateSortOrder(array $sortData)
    {
        DB::beginTransaction();
        
        try {
            foreach ($sortData as $item) {
                Unit::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Cập nhật thứ tự thành công'
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error in updateSortOrder: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật thứ tự: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get active units for dropdown
     */
    public function getActiveUnitsForDropdown()
    {
        return Unit::active()->ordered()->pluck('name', 'id');
    }
}

