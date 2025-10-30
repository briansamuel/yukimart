<?php

namespace App\Http\Controllers\Tenant\Modules\Analytics\AccountsReceivable;

use App\Http\Controllers\Tenant\BaseTenantController;
use App\Models\Analytics\AccountsReceivable;
use App\Models\BranchShop;
use Illuminate\Http\Request;

class OverviewController extends BaseTenantController
{
    /**
     * Display accounts receivable analytics
     */
    public function index(Request $request)
    {
        $tenantId = $this->getCurrentTenantId();
        
        $branchShopId = $request->input('branch_shop_id');
        $statusFilter = $request->input('status');

        // Get all receivables
        $query = AccountsReceivable::byTenant($tenantId);

        if ($branchShopId) {
            // Note: AccountsReceivable doesn't have branch_shop_id directly
            // This would need to be joined with invoices table
        }

        if ($statusFilter) {
            $query->byStatus($statusFilter);
        }

        $receivables = $query->with('customer', 'invoice')->get();

        // Calculate metrics
        $metrics = $this->calculateMetrics($receivables);

        // Get overdue receivables
        $overdueReceivables = AccountsReceivable::byTenant($tenantId)
            ->overdue()
            ->orderByDesc('days_overdue')
            ->limit(20)
            ->with('customer')
            ->get();

        // Get receivables by status
        $receivablesByStatus = $this->getReceivablesByStatus($tenantId);

        // Get aging analysis
        $agingAnalysis = $this->getAgingAnalysis($tenantId);

        // Get branches for filter
        $branches = BranchShop::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->select('id', 'name')
            ->get();

        return view('tenant.modules.analytics.accounts_receivable.overview', [
            'filters' => [
                'branch_shop_id' => $branchShopId,
                'status' => $statusFilter,
            ],
            'branches' => $branches,
            'metrics' => $metrics,
            'overdueReceivables' => $overdueReceivables,
            'receivablesByStatus' => $receivablesByStatus,
            'agingAnalysis' => $agingAnalysis,
        ]);
    }

    /**
     * Calculate receivables metrics
     */
    protected function calculateMetrics($receivables)
    {
        $totalInvoiceAmount = $receivables->sum('invoice_amount');
        $totalPaidAmount = $receivables->sum('paid_amount');
        $totalOutstanding = $receivables->sum('outstanding_amount');
        
        $pendingCount = $receivables->where('status', 'pending')->count();
        $partialCount = $receivables->where('status', 'partial')->count();
        $paidCount = $receivables->where('status', 'paid')->count();
        $overdueCount = $receivables->where('status', 'overdue')->count();
        
        $overdueAmount = $receivables->where('status', 'overdue')->sum('outstanding_amount');

        return [
            'total_invoice_amount' => $totalInvoiceAmount,
            'total_paid_amount' => $totalPaidAmount,
            'total_outstanding' => $totalOutstanding,
            'pending_count' => $pendingCount,
            'partial_count' => $partialCount,
            'paid_count' => $paidCount,
            'overdue_count' => $overdueCount,
            'overdue_amount' => $overdueAmount,
            'collection_rate' => $totalInvoiceAmount > 0 ? ($totalPaidAmount / $totalInvoiceAmount) * 100 : 0,
            'outstanding_percentage' => $totalInvoiceAmount > 0 ? ($totalOutstanding / $totalInvoiceAmount) * 100 : 0,
        ];
    }

    /**
     * Get receivables grouped by status
     */
    protected function getReceivablesByStatus($tenantId)
    {
        $statuses = ['pending', 'partial', 'paid', 'overdue', 'cancelled'];
        $result = [];

        foreach ($statuses as $status) {
            $data = AccountsReceivable::byTenant($tenantId)
                ->byStatus($status)
                ->selectRaw('COUNT(*) as count, SUM(outstanding_amount) as amount')
                ->first();

            $result[$status] = [
                'count' => (int) $data->count,
                'amount' => (float) $data->amount,
            ];
        }

        return $result;
    }

    /**
     * Get aging analysis (0-30, 31-60, 61-90, 90+)
     */
    protected function getAgingAnalysis($tenantId)
    {
        $ranges = [
            '0-30' => [0, 30],
            '31-60' => [31, 60],
            '61-90' => [61, 90],
            '90+' => [91, 999],
        ];

        $result = [];

        foreach ($ranges as $label => $range) {
            $data = AccountsReceivable::byTenant($tenantId)
                ->outstanding()
                ->whereBetween('days_overdue', $range)
                ->selectRaw('COUNT(*) as count, SUM(outstanding_amount) as amount')
                ->first();

            $result[$label] = [
                'count' => (int) $data->count,
                'amount' => (float) $data->amount,
            ];
        }

        return $result;
    }
}

