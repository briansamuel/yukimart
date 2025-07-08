<?php

namespace App\Http\Controllers\Admin\CMS;

use App\Http\Controllers\Controller;
use App\Services\ReportsService;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ReportsController extends Controller
{
    protected $reportsService;

    public function __construct(ReportsService $reportsService)
    {
        $this->reportsService = $reportsService;
    }

    /**
     * Display reports dashboard
     */
    public function index()
    {
        $warehouses = Warehouse::active()->orderBy('name')->get();
        $categories = Product::select('product_category')
            ->whereNotNull('product_category')
            ->where('product_category', '!=', '')
            ->distinct()
            ->orderBy('product_category')
            ->pluck('product_category');

        return view('admin.reports.index', compact('warehouses', 'categories'));
    }

    /**
     * Get sales report
     */
    public function getSalesReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'group_by' => 'nullable|in:day,week,month,year',
            'customer_id' => 'nullable|exists:customers,id',
            'branch_shop_id' => 'nullable|exists:branch_shops,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $filters = [
                'date_from' => $request->date_from ? Carbon::parse($request->date_from) : Carbon::now()->startOfMonth(),
                'date_to' => $request->date_to ? Carbon::parse($request->date_to) : Carbon::now()->endOfMonth(),
                'group_by' => $request->group_by ?? 'day',
                'customer_id' => $request->customer_id,
                'branch_shop_id' => $request->branch_shop_id,
            ];

            $report = $this->reportsService->getSalesReport($filters);

            return response()->json([
                'success' => true,
                'data' => $report
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tạo báo cáo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get inventory report
     */
    public function getInventoryReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'product_category' => 'nullable|string',
            'stock_status' => 'nullable|in:low_stock,out_of_stock,in_stock',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $filters = [
                'warehouse_id' => $request->warehouse_id,
                'product_category' => $request->product_category,
                'stock_status' => $request->stock_status,
            ];

            $report = $this->reportsService->getInventoryReport($filters);

            return response()->json([
                'success' => true,
                'data' => $report
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tạo báo cáo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get product performance report
     */
    public function getProductPerformanceReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'product_category' => 'nullable|string',
            'order_by' => 'nullable|in:total_revenue,total_sold,profit,profit_margin',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $filters = [
                'date_from' => $request->date_from ? Carbon::parse($request->date_from) : Carbon::now()->startOfMonth(),
                'date_to' => $request->date_to ? Carbon::parse($request->date_to) : Carbon::now()->endOfMonth(),
                'product_category' => $request->product_category,
                'order_by' => $request->order_by ?? 'total_revenue',
                'limit' => $request->limit ?? 20,
            ];

            $report = $this->reportsService->getProductPerformanceReport($filters);

            return response()->json([
                'success' => true,
                'data' => $report
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tạo báo cáo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get customer analytics
     */
    public function getCustomerAnalytics(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $filters = [
                'date_from' => $request->date_from ? Carbon::parse($request->date_from) : Carbon::now()->startOfMonth(),
                'date_to' => $request->date_to ? Carbon::parse($request->date_to) : Carbon::now()->endOfMonth(),
                'limit' => $request->limit ?? 20,
            ];

            $report = $this->reportsService->getCustomerAnalytics($filters);

            return response()->json([
                'success' => true,
                'data' => $report
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tạo báo cáo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get financial summary
     */
    public function getFinancialSummary(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $filters = [
                'date_from' => $request->date_from ? Carbon::parse($request->date_from) : Carbon::now()->startOfMonth(),
                'date_to' => $request->date_to ? Carbon::parse($request->date_to) : Carbon::now()->endOfMonth(),
            ];

            $report = $this->reportsService->getFinancialSummary($filters);

            return response()->json([
                'success' => true,
                'data' => $report
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tạo báo cáo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get dashboard analytics
     */
    public function getDashboardAnalytics(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'period' => 'nullable|in:day,week,month,quarter,year',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $period = $request->period ?? 'month';
            $analytics = $this->reportsService->getDashboardAnalytics($period);

            return response()->json([
                'success' => true,
                'data' => $analytics
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải analytics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export report to Excel
     */
    public function exportReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'report_type' => 'required|in:sales,inventory,product_performance,customer_analytics,financial',
            'format' => 'required|in:xlsx,csv,pdf',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $reportType = $request->report_type;
            $format = $request->format;
            
            $filters = [
                'date_from' => $request->date_from ? Carbon::parse($request->date_from) : Carbon::now()->startOfMonth(),
                'date_to' => $request->date_to ? Carbon::parse($request->date_to) : Carbon::now()->endOfMonth(),
            ];

            // Get report data based on type
            switch ($reportType) {
                case 'sales':
                    $data = $this->reportsService->getSalesReport($filters);
                    $exportClass = \App\Exports\SalesReportExport::class;
                    break;
                case 'inventory':
                    $data = $this->reportsService->getInventoryReport($filters);
                    $exportClass = \App\Exports\InventoryReportExport::class;
                    break;
                case 'product_performance':
                    $data = $this->reportsService->getProductPerformanceReport($filters);
                    $exportClass = \App\Exports\ProductPerformanceReportExport::class;
                    break;
                case 'customer_analytics':
                    $data = $this->reportsService->getCustomerAnalytics($filters);
                    $exportClass = \App\Exports\CustomerAnalyticsReportExport::class;
                    break;
                case 'financial':
                    $data = $this->reportsService->getFinancialSummary($filters);
                    $exportClass = \App\Exports\FinancialReportExport::class;
                    break;
                default:
                    throw new \Exception('Loại báo cáo không hợp lệ');
            }

            $filename = $reportType . '_report_' . date('Y-m-d_H-i-s') . '.' . $format;
            
            return \Maatwebsite\Excel\Facades\Excel::download(
                new $exportClass($data), 
                $filename
            );

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi xuất báo cáo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available filters for dropdowns
     */
    public function getFilters()
    {
        try {
            $warehouses = Warehouse::active()
                ->select('id', 'code', 'name')
                ->orderBy('name')
                ->get()
                ->map(function($warehouse) {
                    return [
                        'id' => $warehouse->id,
                        'text' => $warehouse->code . ' - ' . $warehouse->name
                    ];
                });

            $categories = Product::select('product_category')
                ->whereNotNull('product_category')
                ->where('product_category', '!=', '')
                ->distinct()
                ->orderBy('product_category')
                ->pluck('product_category')
                ->map(function($category) {
                    return [
                        'id' => $category,
                        'text' => $category
                    ];
                });

            $customers = Customer::select('id', 'name', 'email')
                ->orderBy('name')
                ->limit(100)
                ->get()
                ->map(function($customer) {
                    return [
                        'id' => $customer->id,
                        'text' => $customer->name . ' (' . $customer->email . ')'
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'warehouses' => $warehouses,
                    'categories' => $categories,
                    'customers' => $customers,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải bộ lọc: ' . $e->getMessage()
            ], 500);
        }
    }
}
