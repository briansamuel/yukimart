<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductUnit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ProductUnitService
{
    /**
     * Get all units for a product
     */
    public function getProductUnits($productId, $tenantId)
    {
        try {
            return ProductUnit::where('product_id', $productId)
                ->where('tenant_id', $tenantId)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();
        } catch (Exception $e) {
            Log::error('Error in getProductUnits: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Get base unit for a product
     */
    public function getBaseUnit($productId, $tenantId)
    {
        try {
            return ProductUnit::where('product_id', $productId)
                ->where('tenant_id', $tenantId)
                ->where('is_base_unit', true)
                ->first();
        } catch (Exception $e) {
            Log::error('Error in getBaseUnit: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Save product units
     * 
     * @param Product $product
     * @param array $unitsData
     * @return array
     */
    public function saveProductUnits(Product $product, array $unitsData)
    {
        DB::beginTransaction();
        
        try {
            $tenantId = $product->tenant_id;

            // Delete existing units
            ProductUnit::where('product_id', $product->id)
                ->where('tenant_id', $tenantId)
                ->delete();

            $createdUnits = [];

            // Create new units
            foreach ($unitsData as $unitData) {
                $unit = ProductUnit::create([
                    'product_id' => $product->id,
                    'tenant_id' => $tenantId,
                    'unit_name' => $unitData['unit_name'],
                    'sale_price' => $unitData['sale_price'] ?? 0,
                    'is_direct_sale' => $unitData['is_direct_sale'] ?? true,
                    'conversion_rate' => $unitData['conversion_rate'] ?? 1,
                    'is_base_unit' => $unitData['is_base_unit'] ?? false,
                    'sort_order' => $unitData['sort_order'] ?? 0,
                    'is_active' => $unitData['is_active'] ?? true,
                ]);

                $createdUnits[] = $unit;
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Đã lưu đơn vị tính thành công',
                'data' => $createdUnits
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error in saveProductUnits: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lưu đơn vị tính: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }

    /**
     * Create a single unit
     */
    public function createUnit($productId, $tenantId, array $unitData)
    {
        try {
            $unit = ProductUnit::create([
                'product_id' => $productId,
                'tenant_id' => $tenantId,
                'unit_name' => $unitData['unit_name'],
                'sale_price' => $unitData['sale_price'] ?? 0,
                'is_direct_sale' => $unitData['is_direct_sale'] ?? true,
                'conversion_rate' => $unitData['conversion_rate'] ?? 1,
                'is_base_unit' => $unitData['is_base_unit'] ?? false,
                'sort_order' => $unitData['sort_order'] ?? 0,
                'is_active' => $unitData['is_active'] ?? true,
            ]);

            return [
                'success' => true,
                'message' => 'Đã tạo đơn vị thành công',
                'data' => $unit
            ];

        } catch (Exception $e) {
            Log::error('Error in createUnit: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo đơn vị: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Update a unit
     */
    public function updateUnit($unitId, array $unitData)
    {
        try {
            $unit = ProductUnit::find($unitId);
            
            if (!$unit) {
                return [
                    'success' => false,
                    'message' => 'Đơn vị không tồn tại',
                    'data' => null
                ];
            }

            $unit->update($unitData);

            return [
                'success' => true,
                'message' => 'Đã cập nhật đơn vị thành công',
                'data' => $unit
            ];

        } catch (Exception $e) {
            Log::error('Error in updateUnit: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật đơn vị: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Delete a unit
     */
    public function deleteUnit($unitId)
    {
        try {
            $unit = ProductUnit::find($unitId);
            
            if (!$unit) {
                return [
                    'success' => false,
                    'message' => 'Đơn vị không tồn tại'
                ];
            }

            $unit->delete();

            return [
                'success' => true,
                'message' => 'Đã xóa đơn vị thành công'
            ];

        } catch (Exception $e) {
            Log::error('Error in deleteUnit: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa đơn vị: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Calculate price based on conversion rate
     */
    public function calculateUnitPrice($basePrice, $conversionRate)
    {
        return $basePrice * $conversionRate;
    }

    /**
     * Convert quantity to base unit
     */
    public function toBaseUnit($quantity, $conversionRate)
    {
        return $quantity * $conversionRate;
    }

    /**
     * Convert quantity from base unit
     */
    public function fromBaseUnit($baseQuantity, $conversionRate)
    {
        return $conversionRate > 0 ? $baseQuantity / $conversionRate : 0;
    }
}

