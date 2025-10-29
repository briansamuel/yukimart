<?php

namespace App\Http\Controllers\Tenant\Settings;

use App\Http\Controllers\Controller;
use App\Models\BranchShop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class BranchManagerController extends Controller
{
    /**
     * Display branch management page
     */
    public function index()
    {
        return view('tenant.settings.shop.branch-manager.index');
    }

    /**
     * Get branches data for AJAX with pagination
     */
    public function getData(Request $request)
    {
        try {
            $tenantId = Auth::user()->tenant_id;

            $query = BranchShop::where('tenant_id', $tenantId);

            // Search
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%")
                      ->orWhere('address', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            // Filter by status
            if ($request->has('status') && $request->status !== '') {
                $query->where('status', $request->status);
            }

            // Pagination
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 1);

            $branches = $query->orderBy('created_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            // Format data
            $data = $branches->map(function ($branch) {
                return [
                    'id' => $branch->id,
                    'name' => $branch->name ?? 'N/A',
                    'code' => $branch->code ?? 'N/A',
                    'address' => $branch->address ?? 'N/A',
                    'phone' => $branch->phone ?? 'N/A',
                    'email' => $branch->email ?? 'N/A',
                    'status' => $branch->status ?? 'active',
                    'status_label' => $this->getStatusLabel($branch->status ?? 'active'),
                    'created_at' => $branch->created_at ? $branch->created_at->format('d/m/Y H:i') : 'N/A',
                ];
            });

            return response()->json([
                'draw' => $request->input('draw', 1),
                'recordsTotal' => $branches->total(),
                'recordsFiltered' => $branches->total(),
                'data' => $data,
                'success' => true,
                'pagination' => [
                    'current_page' => $branches->currentPage(),
                    'last_page' => $branches->lastPage(),
                    'per_page' => $branches->perPage(),
                    'total' => $branches->total(),
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Error in BranchManager getData: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'draw' => intval($request->input('draw', 1)),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'success' => false,
                'error' => 'Có lỗi xảy ra khi tải d�?liệu: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get status label
     */
    private function getStatusLabel($status)
    {
        $labels = [
            'active' => '<span class="badge badge-light-success">Hoạt ?ộng</span>',
            'inactive' => '<span class="badge badge-light-danger">Không hoạt ?ộng</span>',
            'maintenance' => '<span class="badge badge-light-warning">Bảo trì</span>',
        ];

        return $labels[$status] ?? '<span class="badge badge-light-secondary">N/A</span>';
    }
}

