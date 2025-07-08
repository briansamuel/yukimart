<?php
namespace App\Repositories\Product;

use App\Repositories\BaseRepository;

class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    //lấy model tương ứng
    public function getModel()
    {
        return \App\Models\Product::class;
    }

    public function getProduct()
    {
        return $this->model->select('product_name')->take(5)->get();
    }

    public function search($keyword = null, $filter = [], $limit = 20, $offset = 0, $sort = [], $column = ['*'])
    {
        // Check if we need to sort by stock_quantity or filter by stock status
        $needsInventoryJoin = false;
        if (!empty($sort) && $sort['field'] === 'stock_quantity') {
            $needsInventoryJoin = true;
        }

        // Check if filtering by stock status
        if (isset($filter['stock_status'])) {
            $needsInventoryJoin = true;
        }

        if ($needsInventoryJoin) {
            // Join with inventories table for stock-related operations
            $query = $this->model
                ->leftJoin('inventories', 'products.id', '=', 'inventories.product_id')
                ->select(array_merge(
                    is_array($column) && $column !== ['*'] ?
                        array_map(fn($col) => $col === 'stock_quantity' ? 'inventories.quantity as stock_quantity' : "products.{$col}", $column) :
                        ['products.*', 'inventories.quantity as stock_quantity']
                ));
        } else {
            $query = $this->model->select($column);
        }

        // Apply keyword search
        if ($keyword) {
            $query->where(function($q) use ($keyword) {
                $q->where('products.product_name', 'LIKE', '%' . $keyword . '%')
                  ->orWhere('products.product_description', 'LIKE', '%' . $keyword . '%')
                  ->orWhere('products.sku', 'LIKE', '%' . $keyword . '%')
                  ->orWhere('products.barcode', 'LIKE', '%' . $keyword . '%');
            });
        }

        // Apply filters
        foreach ($filter as $key => $value) {
            if ($value !== null && $value !== '') {
                if ($key === 'stock_status') {
                    // Handle stock status filtering
                    switch ($value) {
                        case 'in_stock':
                            $query->where('inventories.quantity', '>', 0)
                                  ->whereRaw('inventories.quantity > products.reorder_point');
                            break;
                        case 'low_stock':
                            $query->where('inventories.quantity', '>', 0)
                                  ->whereRaw('inventories.quantity <= products.reorder_point');
                            break;
                        case 'out_of_stock':
                            $query->where(function($q) {
                                $q->where('inventories.quantity', '<=', 0)
                                  ->orWhereNull('inventories.quantity');
                            });
                            break;
                    }
                } elseif ($key === 'created_at') {
                    $query->whereDate('products.created_at', $value);
                } else {
                    $query->where("products.{$key}", $value);
                }
            }
        }

        // Apply sorting
        if (!empty($sort['field']) && !empty($sort['sort'])) {
            if ($sort['field'] === 'stock_quantity') {
                // Sort by inventory quantity
                $query->orderBy('inventories.quantity', $sort['sort']);
            } else {
                // Add products. prefix for other fields when using join
                $sortField = $needsInventoryJoin && !str_contains($sort['field'], '.') ?
                    "products.{$sort['field']}" : $sort['field'];
                $query->orderBy($sortField, $sort['sort']);
            }
        } else {
            $orderField = $needsInventoryJoin ? 'products.id' : 'id';
            $query->orderBy($orderField, 'desc');
        }

        // Apply pagination
        if ($limit > 0) {
            $query->limit($limit)->offset($offset);
        }

        return $query->get();
    }

    public function totalRow($keyword = null, $filter = [])
    {
        // Check if we need inventory join for filtering
        $needsInventoryJoin = isset($filter['stock_status']);

        if ($needsInventoryJoin) {
            $query = $this->model
                ->leftJoin('inventories', 'products.id', '=', 'inventories.product_id');
        } else {
            $query = $this->model->query();
        }

        // Apply keyword search
        if ($keyword) {
            $query->where(function($q) use ($keyword, $needsInventoryJoin) {
                $prefix = $needsInventoryJoin ? 'products.' : '';
                $q->where($prefix . 'product_name', 'LIKE', '%' . $keyword . '%')
                  ->orWhere($prefix . 'product_description', 'LIKE', '%' . $keyword . '%')
                  ->orWhere($prefix . 'sku', 'LIKE', '%' . $keyword . '%')
                  ->orWhere($prefix . 'barcode', 'LIKE', '%' . $keyword . '%');
            });
        }

        // Apply filters
        foreach ($filter as $key => $value) {
            if ($value !== null && $value !== '') {
                if ($key === 'stock_status') {
                    // Handle stock status filtering
                    switch ($value) {
                        case 'in_stock':
                            $query->where('inventories.quantity', '>', 0)
                                  ->whereRaw('inventories.quantity > products.reorder_point');
                            break;
                        case 'low_stock':
                            $query->where('inventories.quantity', '>', 0)
                                  ->whereRaw('inventories.quantity <= products.reorder_point');
                            break;
                        case 'out_of_stock':
                            $query->where(function($q) {
                                $q->where('inventories.quantity', '<=', 0)
                                  ->orWhereNull('inventories.quantity');
                            });
                            break;
                    }
                } elseif ($key === 'created_at') {
                    $field = $needsInventoryJoin ? 'products.created_at' : 'created_at';
                    $query->whereDate($field, $value);
                } else {
                    $field = $needsInventoryJoin ? "products.{$key}" : $key;
                    $query->where($field, $value);
                }
            }
        }

        return $query->count();
    }

    public function takeNew($quantity, $filter = [])
    {
        $query = $this->model->query();

        // Apply filters
        foreach ($filter as $key => $value) {
            if ($value !== null && $value !== '') {
                $query->where($key, $value);
            }
        }

        return $query->orderBy('created_at', 'desc')->take($quantity)->get();
    }
}
