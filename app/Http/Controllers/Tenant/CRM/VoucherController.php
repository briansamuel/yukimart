<?php

namespace App\Http\Controllers\Tenant\CRM;

use App\Http\Controllers\Tenant\BaseTenantController;
use App\Models\Voucher;
use App\Models\BranchShop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class VoucherController extends BaseTenantController
{
    /**
     * Display a listing of vouchers.
     */
    public function index()
    {
        return view('admin.vouchers.index');
    }

    /**
     * AJAX endpoint for vouchers listing
     */
    public function ajaxGetVouchers(Request $request)
    {
        try {
            $params = $request->all();

            // Filters
            $filters = [
                'status' => $params['status'] ?? null,
                'type' => $params['type'] ?? null,
                'is_public' => $params['is_public'] ?? null,
                'branch_shop_id' => $params['branch_shop_id'] ?? null,
                'date_from' => $params['date_from'] ?? null,
                'date_to' => $params['date_to'] ?? null,
                'search' => $params['search'] ?? null,
            ];

            $perPage = $params['per_page'] ?? 10;
            $page = $params['page'] ?? 1;

            // Build query
            $query = Voucher::with(['branchShop', 'creator', 'updater']);

            // Apply filters
            if (!empty($filters['status'])) {
                $statuses = is_array($filters['status']) ? $filters['status'] : [$filters['status']];
                $query->whereIn('status', $statuses);
            }

            if (!empty($filters['type'])) {
                $query->where('type', $filters['type']);
            }

            if (isset($filters['is_public']) && $filters['is_public'] !== '') {
                $query->where('is_public', $filters['is_public']);
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
                      ->orWhere('voucher_code', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Get paginated results
            $vouchers = $query->orderBy('created_at', 'desc')->paginate($perPage, ['*'], 'page', $page);

            // Format data
            $data = $vouchers->map(function ($voucher) {
                return [
                    'id' => $voucher->id,
                    'voucher_code' => $voucher->voucher_code ?? 'N/A',
                    'name' => $voucher->name ?? 'N/A',
                    'type' => $voucher->type ?? 'N/A',
                    'type_label' => $voucher->getTypeLabel(),
                    'discount_value' => $voucher->discount_value ?? 0,
                    'discount_value_formatted' => $this->formatDiscountValue($voucher),
                    'min_order_value' => $voucher->min_order_value ?? 0,
                    'min_order_value_formatted' => number_format($voucher->min_order_value ?? 0, 0, ',', '.') . ' ₫',
                    'start_date' => $voucher->start_date ? $voucher->start_date->format('d/m/Y') : 'N/A',
                    'end_date' => $voucher->end_date ? $voucher->end_date->format('d/m/Y') : 'N/A',
                    'used_quantity' => $voucher->used_quantity ?? 0,
                    'total_quantity' => $voucher->total_quantity ?? 'Không giới hạn',
                    'remaining_quantity' => $voucher->getRemainingQuantity() ?? 'Không giới hạn',
                    'is_public' => $voucher->is_public ? 'Công khai' : 'Riêng tư',
                    'branch_shop_name' => $voucher->branchShop->name ?? 'N/A',
                    'status' => $voucher->status ?? 'active',
                    'status_label' => $voucher->getStatusLabel(),
                    'created_at' => $voucher->created_at ? $voucher->created_at->format('d/m/Y H:i') : 'N/A',
                    'created_by_name' => $voucher->creator->full_name ?? 'N/A',
                ];
            });

            return response()->json([
                'draw' => $params['draw'] ?? 1,
                'recordsTotal' => $vouchers->total(),
                'recordsFiltered' => $vouchers->total(),
                'data' => $data,
                'success' => true,
                'message' => 'Vouchers data loaded successfully'
            ]);

        } catch (Exception $e) {
            Log::error('Error in ajaxGetVouchers: ' . $e->getMessage(), [
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
    private function formatDiscountValue($voucher)
    {
        if ($voucher->type === 'percentage') {
            return $voucher->discount_value . '%';
        } elseif ($voucher->type === 'fixed_amount') {
            return number_format($voucher->discount_value, 0, ',', '.') . ' ₫';
        } elseif ($voucher->type === 'freeship') {
            return 'Miễn phí vận chuyển';
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
            ['value' => 'used_up', 'label' => 'Đã hết'],
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
            ['value' => 'freeship', 'label' => 'Miễn phí vận chuyển'],
        ]);
    }

    /**
     * Bulk delete vouchers
     */
    public function bulkDelete(Request $request)
    {
        try {
            $ids = $request->input('ids', []);
            
            if (empty($ids)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không có voucher nào được chọn'
                ], 400);
            }

            Voucher::whereIn('id', $ids)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Đã xóa ' . count($ids) . ' voucher'
            ]);

        } catch (Exception $e) {
            Log::error('Error in bulkDelete: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa voucher'
            ], 500);
        }
    }

    /**
     * Export vouchers to Excel
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

