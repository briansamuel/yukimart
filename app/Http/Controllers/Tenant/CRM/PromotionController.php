<?php

namespace App\Http\Controllers\Tenant\CRM;

use App\Http\Controllers\Tenant\BaseTenantController;
use App\Models\Promotion;
use App\Models\BranchShop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class PromotionController extends BaseTenantController
{
    /**
     * Display a listing of promotions.
     */
    public function index()
    {
        return view('admin.promotions.index');
    }

    /**
     * AJAX endpoint for promotions listing
     */
    public function ajaxGetPromotions(Request $request)
    {
        try {
            $params = $request->all();

            // Filters
            $filters = [
                'status' => $params['status'] ?? null,
                'type' => $params['type'] ?? null,
                'apply_to' => $params['apply_to'] ?? null,
                'branch_shop_id' => $params['branch_shop_id'] ?? null,
                'date_from' => $params['date_from'] ?? null,
                'date_to' => $params['date_to'] ?? null,
                'search' => $params['search'] ?? null,
            ];

            $perPage = $params['per_page'] ?? 10;
            $page = $params['page'] ?? 1;

            // Build query
            $query = Promotion::with(['branchShop', 'creator', 'updater']);

            // Apply filters
            if (!empty($filters['status'])) {
                $statuses = is_array($filters['status']) ? $filters['status'] : [$filters['status']];
                $query->whereIn('status', $statuses);
            }

            if (!empty($filters['type'])) {
                $query->where('type', $filters['type']);
            }

            if (!empty($filters['apply_to'])) {
                $query->where('apply_to', $filters['apply_to']);
            }

            if (!empty($filters['branch_shop_id'])) {
                $query->where('branch_shop_id', $filters['branch_shop_id']);
            }

            if (!empty($filters['date_from'])) {
                $query->whereDate('start_date', '>=', $filters['date_from']);
            }

            if (!empty($filters['date_to'])) {
                $query->whereDate('end_date', '<=', $filters['date_to']);
            }

            if (!empty($filters['search'])) {
                $search = $filters['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('promotion_code', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Get paginated results
            $promotions = $query->orderBy('created_at', 'desc')->paginate($perPage, ['*'], 'page', $page);

            // Format data
            $data = $promotions->map(function ($promotion) {
                return [
                    'id' => $promotion->id,
                    'promotion_code' => $promotion->promotion_code ?? 'N/A',
                    'name' => $promotion->name ?? 'N/A',
                    'type' => $promotion->type ?? 'N/A',
                    'type_label' => $promotion->getTypeLabel(),
                    'discount_value' => $promotion->discount_value ?? 0,
                    'discount_value_formatted' => $this->formatDiscountValue($promotion),
                    'min_order_value' => $promotion->min_order_value ?? 0,
                    'min_order_value_formatted' => number_format($promotion->min_order_value ?? 0, 0, ',', '.') . ' ₫',
                    'start_date' => $promotion->start_date ? $promotion->start_date->format('d/m/Y') : 'N/A',
                    'end_date' => $promotion->end_date ? $promotion->end_date->format('d/m/Y') : 'N/A',
                    'usage_count' => $promotion->usage_count ?? 0,
                    'usage_limit' => $promotion->usage_limit ?? 'Không giới hạn',
                    'apply_to' => $promotion->apply_to ?? 'N/A',
                    'branch_shop_name' => $promotion->branchShop->name ?? 'N/A',
                    'status' => $promotion->status ?? 'active',
                    'status_label' => $promotion->getStatusLabel(),
                    'created_at' => $promotion->created_at ? $promotion->created_at->format('d/m/Y H:i') : 'N/A',
                    'created_by_name' => $promotion->creator->full_name ?? 'N/A',
                ];
            });

            return response()->json([
                'draw' => $params['draw'] ?? 1,
                'recordsTotal' => $promotions->total(),
                'recordsFiltered' => $promotions->total(),
                'data' => $data,
                'success' => true,
                'message' => 'Promotions data loaded successfully'
            ]);

        } catch (Exception $e) {
            Log::error('Error in ajaxGetPromotions: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'draw' => intval($params['draw'] ?? 1),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'success' => false,
                'error' => 'Có lỗi xảy ra khi tải dữ liệu: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Format discount value based on type
     */
    private function formatDiscountValue($promotion)
    {
        if ($promotion->type === 'percentage') {
            return $promotion->discount_value . '%';
        } elseif ($promotion->type === 'fixed_amount') {
            return number_format($promotion->discount_value, 0, ',', '.') . ' ₫';
        } elseif ($promotion->type === 'buy_x_get_y') {
            return "Mua {$promotion->buy_quantity} tặng {$promotion->get_quantity}";
        }
        return 'N/A';
    }

    /**
     * Get filter options for status
     */
    public function getFilterStatuses()
    {
        return response()->json([
            ['value' => 'active', 'label' => 'Đang hoạt động'],
            ['value' => 'inactive', 'label' => 'Không hoạt động'],
            ['value' => 'expired', 'label' => 'Hết hạn'],
        ]);
    }

    /**
     * Get filter options for types
     */
    public function getFilterTypes()
    {
        return response()->json([
            ['value' => 'percentage', 'label' => 'Giảm giá %'],
            ['value' => 'fixed_amount', 'label' => 'Giảm giá cố định'],
            ['value' => 'buy_x_get_y', 'label' => 'Mua X tặng Y'],
        ]);
    }

    /**
     * Bulk delete promotions
     */
    public function bulkDelete(Request $request)
    {
        try {
            $ids = $request->input('ids', []);

            if (empty($ids)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không có khuyến mãi nào được chọn'
                ], 400);
            }

            Promotion::whereIn('id', $ids)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Đã xóa ' . count($ids) . ' khuyến mãi'
            ]);

        } catch (Exception $e) {
            Log::error('Error in bulkDelete: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa khuyến mãi'
            ], 500);
        }
    }

    /**
     * Export promotions to Excel
     */
    public function export(Request $request)
    {
        // TODO: Implement export functionality
        return response()->json([
            'success' => false,
            'message' => 'Chức năng xuất Excel đang được phát triển'
        ]);
    }
}