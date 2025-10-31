<?php

namespace App\Http\Controllers\Tenant\Reports;

use App\Http\Controllers\Tenant\BaseTenantController;
use App\Services\Tenant\Analytics\SalesAnalyticsService;
use App\Services\Tenant\Analytics\ProfitAnalyticsService;
use App\Services\Tenant\Analytics\BranchAnalyticsService;
use App\Services\Tenant\Analytics\RankingAnalyticsService;
use App\Models\BranchShop;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BusinessReportController extends BaseTenantController
{
    protected $salesService;
    protected $profitService;
    protected $branchService;
    protected $rankingService;

    public function __construct(
        SalesAnalyticsService $salesService,
        ProfitAnalyticsService $profitService,
        BranchAnalyticsService $branchService,
        RankingAnalyticsService $rankingService
    ) {
        parent::__construct();
        $this->salesService = $salesService;
        $this->profitService = $profitService;
        $this->branchService = $branchService;
        $this->rankingService = $rankingService;
    }

    /**
     * Display business analytics dashboard
     */
    public function index(Request $request)
    {
        $tenantId = $this->getCurrentTenantId();

        // Get filter parameters
        $fromDate = $request->input('from_date', now()->startOfMonth()->toDateString());
        $toDate = $request->input('to_date', now()->toDateString());
        $branchShopId = $request->input('branch_shop_id');

        // Get KPI data
        $kpi = $this->salesService->getKpiOverview($tenantId, $fromDate, $toDate, $branchShopId);

        // Get cost and profit data
        $revenue = $kpi['revenue']['total'];
        $returnAmount = $kpi['returns']['total'];
        $netRevenue = $revenue - $returnAmount;
        $costOfGoods = $this->profitService->getTotalCostOfGoods($tenantId, $fromDate, $toDate, $branchShopId);
        $grossProfit = $this->profitService->getGrossProfit($tenantId, $fromDate, $toDate, $branchShopId, $netRevenue);

        // Add profit data to KPI
        $kpi['total_cost'] = [
            'total' => $costOfGoods,
            'avg_per_day' => $kpi['revenue']['avg_per_day'] > 0 ? $costOfGoods / ($this->getDayCount($fromDate, $toDate)) : 0,
            'change_vs_prev' => 0,
        ];

        $kpi['gross_profit'] = [
            'total' => $grossProfit,
            'avg_per_day' => $kpi['revenue']['avg_per_day'] > 0 ? $grossProfit / ($this->getDayCount($fromDate, $toDate)) : 0,
            'change_vs_prev' => 0,
        ];

        // Get chart data
        $chartData = $this->salesService->getDailyTimeSeries($tenantId, $fromDate, $toDate, $branchShopId);

        // Get branch breakdown
        $branchTable = $this->branchService->getBranchBreakdown($tenantId, $fromDate, $toDate);

        // Get top rankings
        $topCategories = $this->rankingService->topProductCategories($tenantId, $fromDate, $toDate, 10);
        $topProducts = $this->rankingService->topProducts($tenantId, $fromDate, $toDate, 10);
        $topCustomers = $this->rankingService->topCustomers($tenantId, $fromDate, $toDate, 10);
        $topChannels = $this->rankingService->topChannels($tenantId, $fromDate, $toDate, 10);
        $topSellers = $this->rankingService->topSellers($tenantId, $fromDate, $toDate, 10);

        // Get branches for filter
        $branches = BranchShop::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->select('id', 'name')
            ->get();

        return view('tenant.reports.business-dashboard', [
            'filters' => [
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'branch_shop_id' => $branchShopId,
            ],
            'branches' => $branches,
            'kpi' => $kpi,
            'chartData' => $chartData,
            'branchTable' => $branchTable,
            'topCategories' => $topCategories,
            'topProducts' => $topProducts,
            'topCustomers' => $topCustomers,
            'topChannels' => $topChannels,
            'topSellers' => $topSellers,
        ]);
    }

    /**
     * Get day count between two dates
     */
    protected function getDayCount($fromDate, $toDate)
    {
        $from = Carbon::parse($fromDate);
        $to = Carbon::parse($toDate);
        return $to->diffInDays($from) + 1;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // TODO: Implement create method
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // TODO: Implement store method
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // TODO: Implement show method
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        // TODO: Implement edit method
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // TODO: Implement update method
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // TODO: Implement destroy method
    }
}