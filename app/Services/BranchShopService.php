<?php

namespace App\Services;

use App\Models\BranchShop;
use App\Models\User;
use App\Models\UserBranchShop;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BranchShopService
{
    protected $branchShop;

    public function __construct(BranchShop $branchShop)
    {
        $this->branchShop = $branchShop;
    }

    /**
     * Get all branch shops with pagination and filters
     */
    public function getList($params = [])
    {
        $search = $params['search'] ?? ['value' => ''];
        $keyword = $search['value'] ?? '';

        $limit = isset($params['length']) ? $params['length'] : 20;
        $offset = isset($params['start']) ? $params['start'] : 0;
        $sort = [];
        $filter = [];

        // Handle column-specific filters
        if (isset($params['columns']) && is_array($params['columns'])) {
            foreach ($params['columns'] as $column_data) {
                if (isset($column_data['search']['value']) && !empty($column_data['search']['value'])) {
                    $columnName = $column_data['data'] ?? '';
                    $searchValue = $column_data['search']['value'];

                    switch ($columnName) {
                        case 'status':
                            $filter['status'] = $searchValue;
                            break;
                        case 'shop_type':
                            $filter['shop_type'] = $searchValue;
                            break;
                        case 'province':
                            $filter['province'] = $searchValue;
                            break;
                        case 'has_delivery':
                            $filter['has_delivery'] = $searchValue === 'true' ? 1 : 0;
                            break;
                    }
                }
            }
        }

        // Handle sorting
        if (isset($params['order'][0]['column']) && isset($params['columns'])) {
            $column_index = intval($params['order'][0]['column']);
            if (isset($params['columns'][$column_index]['data'])) {
                $sort['field'] = $params['columns'][$column_index]['data'];
                $sort['sort'] = $params['order'][0]['dir'] ?? 'desc';
            }
        }

        // Default sorting
        if (empty($sort)) {
            $sort['field'] = 'sort_order';
            $sort['sort'] = 'asc';
        }

        $query = $this->branchShop->with(['manager']);

        // Apply keyword search
        if ($keyword) {
            $query->where(function($q) use ($keyword) {
                $q->where('name', 'LIKE', '%' . $keyword . '%')
                  ->orWhere('code', 'LIKE', '%' . $keyword . '%')
                  ->orWhere('address', 'LIKE', '%' . $keyword . '%')
                  ->orWhere('phone', 'LIKE', '%' . $keyword . '%')
                  ->orWhere('email', 'LIKE', '%' . $keyword . '%');
            });
        }

        // Apply filters
        foreach ($filter as $key => $value) {
            if ($value !== null && $value !== '') {
                $query->where($key, $value);
            }
        }

        // Apply sorting
        if (!empty($sort['field']) && !empty($sort['sort'])) {
            $query->orderBy($sort['field'], $sort['sort']);
        }

        // Get total count before pagination
        $total = $query->count();

        // Apply pagination
        if ($limit > 0) {
            $query->limit($limit)->offset($offset);
        }

        $result = $query->get();

        // Transform data for DataTables
        $transformedData = $result->map(function($branchShop) {
            return [
                'id' => $branchShop->id,
                'code' => $branchShop->code,
                'name' => $branchShop->name,
                'full_address' => $branchShop->full_address,
                'phone' => $branchShop->phone,
                'email' => $branchShop->email,
                'status' => $branchShop->status,
                'status_badge' => $branchShop->status_badge,
                'created_at' => $branchShop->created_at,
                'updated_at' => $branchShop->updated_at,
            ];
        });

        return [
            'data' => $transformedData,
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
        ];
    }

    /**
     * Create new branch shop
     */
    public function create($data)
    {
        try {
            DB::beginTransaction();

            // Generate unique code if not provided
            if (!isset($data['code']) || empty($data['code'])) {
                $data['code'] = $this->generateUniqueCode($data['name']);
            }

            // Set default working days if not provided
            if (!isset($data['working_days']) || empty($data['working_days'])) {
                $data['working_days'] = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            }

            $branchShop = $this->branchShop->create($data);

            DB::commit();
            return $branchShop;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update branch shop
     */
    public function update($id, $data)
    {
        try {
            DB::beginTransaction();

            $branchShop = $this->branchShop->findOrFail($id);
            
            // Check if code is unique (excluding current record)
            if (isset($data['code']) && $data['code'] !== $branchShop->code) {
                if ($this->codeExists($data['code'], $id)) {
                    throw new \Exception('Mã chi nhánh đã tồn tại');
                }
            }

            $branchShop->update($data);

            DB::commit();
            return $branchShop;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete branch shop
     */
    public function delete($id)
    {
        try {
            DB::beginTransaction();

            $branchShop = $this->branchShop->findOrFail($id);
            
            // Check if branch shop has orders
            if ($branchShop->orders()->count() > 0) {
                throw new \Exception('Không thể xóa chi nhánh có đơn hàng');
            }

            $branchShop->delete();

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get branch shop by ID
     */
    public function findById($id)
    {
        return $this->branchShop->with(['manager', 'creator', 'updater'])->findOrFail($id);
    }

    /**
     * Get active branch shops for filter
     */
    public function getActiveForFilter()
    {
        return $this->branchShop->active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->select('id', 'name')
            ->get();
    }

    /**
     * Get active branch shops for dropdown
     */
    public function getActiveForDropdown()
    {
        return $this->branchShop->active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->select('id', 'code', 'name', 'address')
            ->get()
            ->map(function($shop) {
                return [
                    'id' => $shop->id,
                    'text' => $shop->code . ' - ' . $shop->name,
                    'address' => $shop->address
                ];
            });
    }

    /**
     * Get branch shops with delivery service
     */
    public function getWithDelivery()
    {
        return $this->branchShop->active()
            ->withDelivery()
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Generate unique code
     */
    protected function generateUniqueCode($name)
    {
        // Create base code from name
        $baseCode = strtoupper(Str::slug(Str::limit($name, 10, ''), ''));
        
        // If empty, use default
        if (empty($baseCode)) {
            $baseCode = 'SHOP';
        }

        $code = $baseCode;
        $counter = 1;

        // Make it unique
        while ($this->codeExists($code)) {
            $code = $baseCode . str_pad($counter, 2, '0', STR_PAD_LEFT);
            $counter++;
        }

        return $code;
    }

    /**
     * Check if code exists
     */
    public function codeExists($code, $excludeId = null)
    {
        $query = $this->branchShop->where('code', $code);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        return $query->exists();
    }

    /**
     * Get branch shop statistics
     */
    public function getStatistics()
    {
        $total = $this->branchShop->count();
        $active = $this->branchShop->active()->count();
        $withDelivery = $this->branchShop->withDelivery()->count();
        
        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $total - $active,
            'with_delivery' => $withDelivery,
            'without_delivery' => $total - $withDelivery,
        ];
    }

    /**
     * Get managers for dropdown
     */
    public function getManagersForDropdown()
    {
        return User::where('status', 'active')
            ->orderBy('full_name')
            ->select('id', 'full_name', 'email')
            ->get()
            ->map(function($user) {
                return [
                    'id' => $user->id,
                    'text' => $user->full_name . ' (' . $user->email . ')'
                ];
            });
    }

    /**
     * Bulk update status
     */
    public function bulkUpdateStatus($ids, $status)
    {
        try {
            DB::beginTransaction();

            $this->branchShop->whereIn('id', $ids)->update([
                'status' => $status,
                'updated_at' => now()
            ]);

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Bulk delete
     */
    public function bulkDelete($ids)
    {
        try {
            DB::beginTransaction();

            // Check if any branch shop has orders
            $hasOrders = $this->branchShop->whereIn('id', $ids)
                ->whereHas('orders')
                ->exists();

            if ($hasOrders) {
                throw new \Exception('Không thể xóa chi nhánh có đơn hàng');
            }

            $this->branchShop->whereIn('id', $ids)->delete();

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get users data for DataTables
     */
    public function getUsersForDataTable($branchShopId, $request)
    {
        $query = UserBranchShop::with(['user', 'assigner'])
            ->where('branch_shop_id', $branchShopId);

        // Search filter
        if ($request->has('search') && !empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->whereHas('user', function($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Get total count before pagination
        $totalRecords = UserBranchShop::where('branch_shop_id', $branchShopId)->count();
        $filteredRecords = $query->count();

        // Ordering
        if ($request->has('order')) {
            $columns = ['user_name', 'user_email', 'role_in_shop', 'start_date', 'is_active', 'is_primary', 'actions'];
            $orderColumn = $columns[$request->order[0]['column']] ?? 'created_at';
            $orderDirection = $request->order[0]['dir'] ?? 'desc';

            if ($orderColumn === 'user_name') {
                $query->join('users', 'user_branch_shops.user_id', '=', 'users.id')
                      ->orderBy('users.full_name', $orderDirection)
                      ->select('user_branch_shops.*');
            } elseif ($orderColumn === 'user_email') {
                $query->join('users', 'user_branch_shops.user_id', '=', 'users.id')
                      ->orderBy('users.email', $orderDirection)
                      ->select('user_branch_shops.*');
            } else {
                $query->orderBy($orderColumn, $orderDirection);
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Pagination
        $start = $request->get('start', 0);
        $length = $request->get('length', 25);

        if ($length != -1) {
            $query->offset($start)->limit($length);
        }

        $userBranchShops = $query->get();

        // Format data for DataTables
        $data = $userBranchShops->map(function($userBranchShop) {
            return [
                'id' => $userBranchShop->id,
                'user_id' => $userBranchShop->user_id,
                'user_name' => $userBranchShop->user->full_name ?? $userBranchShop->user->username ?? 'N/A',
                'user_email' => $userBranchShop->user->email ?? 'N/A',
                'role_in_shop' => $userBranchShop->role_in_shop,
                'role_label' => $userBranchShop->role_label,
                'start_date' => $userBranchShop->start_date,
                'formatted_start_date' => $userBranchShop->formatted_start_date,
                'is_active' => $userBranchShop->is_active,
                'status_badge' => $userBranchShop->status_badge,
                'is_primary' => $userBranchShop->is_primary,
                'is_primary_badge' => $userBranchShop->is_primary
                    ? '<span class="badge badge-light-primary">Chi nhánh chính</span>'
                    : '<span class="badge badge-light-secondary">Chi nhánh phụ</span>',
                'notes' => $userBranchShop->notes,
                'assigned_by' => $userBranchShop->assigner->name ?? 'N/A',
                'assigned_at' => $userBranchShop->assigned_at,
                'created_at' => $userBranchShop->created_at,
            ];
        });

        return [
            'draw' => intval($request->get('draw')),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data
        ];
    }

    /**
     * Add user to branch shop
     */
    public function addUserToBranchShop($branchShopId, $data)
    {
        try {
            DB::beginTransaction();

            // Check if user already exists in this branch shop
            $exists = UserBranchShop::where('user_id', $data['user_id'])
                ->where('branch_shop_id', $branchShopId)
                ->exists();

            if ($exists) {
                throw new \Exception('Người dùng đã được thêm vào chi nhánh này');
            }

            // If setting as primary, unset other primary branches for this user
            if (isset($data['is_primary']) && $data['is_primary']) {
                UserBranchShop::where('user_id', $data['user_id'])
                    ->update(['is_primary' => false]);
            }

            $userBranchShop = UserBranchShop::create([
                'user_id' => $data['user_id'],
                'branch_shop_id' => $branchShopId,
                'role_in_shop' => $data['role_in_shop'],
                'start_date' => $data['start_date'] ?? now()->toDateString(),
                'notes' => $data['notes'] ?? null,
                'is_primary' => $data['is_primary'] ?? false,
                'is_active' => true,
                'assigned_by' => auth()->id(),
                'assigned_at' => now(),
            ]);

            DB::commit();
            return $userBranchShop->load('user');

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Remove user from branch shop
     */
    public function removeUserFromBranchShop($branchShopId, $userId)
    {
        try {
            DB::beginTransaction();

            $userBranchShop = UserBranchShop::where('user_id', $userId)
                ->where('branch_shop_id', $branchShopId)
                ->firstOrFail();

            $userBranchShop->delete();

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update user in branch shop
     */
    public function updateUserInBranchShop($branchShopId, $userId, $data)
    {
        try {
            DB::beginTransaction();

            $userBranchShop = UserBranchShop::where('user_id', $userId)
                ->where('branch_shop_id', $branchShopId)
                ->firstOrFail();

            // If setting as primary, unset other primary branches for this user
            if (isset($data['is_primary']) && $data['is_primary'] && !$userBranchShop->is_primary) {
                UserBranchShop::where('user_id', $userId)
                    ->where('id', '!=', $userBranchShop->id)
                    ->update(['is_primary' => false]);
            }

            $userBranchShop->update($data);

            DB::commit();
            return $userBranchShop->load('user');

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
