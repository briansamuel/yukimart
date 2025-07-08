<?php

namespace App\Services;

use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SupplierService
{
    protected $supplier;

    public function __construct(Supplier $supplier)
    {
        $this->supplier = $supplier;
    }

    /**
     * Get list of suppliers with pagination and filters.
     */
    public function getList($params = [])
    {
        $query = $this->supplier->with('branch');

        // Search functionality
        if (!empty($params['search'])) {
            $query->search($params['search']);
        }

        // Status filter
        if (!empty($params['status'])) {
            $query->where('status', $params['status']);
        }

        // Branch filter
        if (!empty($params['branch_id'])) {
            $query->where('branch_id', $params['branch_id']);
        }

        // Group filter
        if (!empty($params['group'])) {
            $query->where('group', $params['group']);
        }

        // Sorting
        $sortBy = $params['sort_by'] ?? 'created_at';
        $sortOrder = $params['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $params['per_page'] ?? 15;
        
        if (isset($params['paginate']) && $params['paginate'] === false) {
            return $query->get();
        }

        return $query->paginate($perPage);
    }

    /**
     * Create a new supplier.
     */
    public function create($data)
    {
        DB::beginTransaction();
        try {
            // Generate code if not provided
            if (empty($data['code'])) {
                $data['code'] = Supplier::generateCode();
            }

            // Create supplier
            $supplier = $this->supplier->create($data);

            DB::commit();
            return $supplier;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Update supplier.
     */
    public function update($id, $data)
    {
        DB::beginTransaction();
        try {
            $supplier = $this->findById($id);
            
            if (!$supplier) {
                throw new \Exception('Supplier not found');
            }

            $supplier->update($data);

            DB::commit();
            return $supplier;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Delete supplier.
     */
    public function delete($id)
    {
        DB::beginTransaction();
        try {
            $supplier = $this->findById($id);
            
            if (!$supplier) {
                throw new \Exception('Supplier not found');
            }

            // Check if supplier has products
            if ($supplier->hasProducts()) {
                throw new \Exception('Cannot delete supplier that has products assigned');
            }

            $supplier->delete();

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Delete multiple suppliers.
     */
    public function deleteMany($ids)
    {
        DB::beginTransaction();
        try {
            $suppliers = $this->supplier->whereIn('id', $ids)->get();
            
            foreach ($suppliers as $supplier) {
                if ($supplier->hasProducts()) {
                    throw new \Exception("Cannot delete supplier '{$supplier->name}' that has products assigned");
                }
            }

            $this->supplier->whereIn('id', $ids)->delete();

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Find supplier by ID.
     */
    public function findById($id)
    {
        return $this->supplier->with('branch')->find($id);
    }

    /**
     * Find supplier by key.
     */
    public function findByKey($key, $value)
    {
        return $this->supplier->with('branch')->where($key, $value)->first();
    }

    /**
     * Get all active suppliers for dropdown.
     */
    public function getActiveSuppliers()
    {
        return $this->supplier->active()
            ->select('id', 'name', 'code', 'company', 'phone', 'email')
            ->orderBy('name')
            ->get()
            ->map(function($supplier) {
                return [
                    'id' => $supplier->id,
                    'name' => $supplier->name,
                    'code' => $supplier->code,
                    'company' => $supplier->company,
                    'phone' => $supplier->phone,
                    'email' => $supplier->email,
                    'display_name' => $this->formatSupplierDisplayName($supplier)
                ];
            });
    }

    /**
     * Format supplier display name for dropdown.
     */
    private function formatSupplierDisplayName($supplier)
    {
        $displayName = $supplier->name;

        if ($supplier->company && $supplier->company !== $supplier->name) {
            $displayName = $supplier->company . ' - ' . $supplier->name;
        }

        if ($supplier->code) {
            $displayName .= ' (' . $supplier->code . ')';
        }

        return $displayName;
    }

    /**
     * Get supplier groups.
     */
    public function getGroups()
    {
        return $this->supplier->whereNotNull('group')
            ->distinct()
            ->pluck('group')
            ->filter()
            ->sort()
            ->values();
    }

    /**
     * Get supplier statistics.
     */
    public function getStatistics()
    {
        return [
            'total' => $this->supplier->count(),
            'active' => $this->supplier->active()->count(),
            'inactive' => $this->supplier->inactive()->count(),
            'with_products' => $this->supplier->has('products')->count(),
            'groups' => $this->getGroups()->count(),
        ];
    }

    /**
     * Total rows count.
     */
    public function totalRows($params = [])
    {
        $query = $this->supplier->query();

        if (!empty($params['status'])) {
            $query->where('status', $params['status']);
        }

        if (!empty($params['branch_id'])) {
            $query->where('branch_id', $params['branch_id']);
        }

        if (!empty($params['group'])) {
            $query->where('group', $params['group']);
        }

        return $query->count();
    }

    /**
     * Get recent suppliers.
     */
    public function getRecent($limit = 5)
    {
        return $this->supplier->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Check if code is unique.
     */
    public function isCodeUnique($code, $excludeId = null)
    {
        $query = $this->supplier->where('code', $code);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return !$query->exists();
    }

}
