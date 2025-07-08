<?php

namespace App\Http\Controllers\Admin\CMS;

use App\Http\Controllers\Controller;
use App\Services\SupplierService;
use App\Services\ValidationService;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;

class SupplierController extends Controller
{
    protected $request;
    protected $validator;
    protected $supplierService;
    protected $language;

    public function __construct(Request $request, ValidationService $validator, SupplierService $supplierService)
    {
        $this->request = $request;
        $this->validator = $validator;
        $this->supplierService = $supplierService;
        $this->language = App::currentLocale();
    }

    /**
     * Display a listing of suppliers.
     */
    public function index()
    {
        return view('admin.suppliers.index');
    }

    /**
     * Get suppliers list via AJAX for DataTables.
     */
    public function ajaxGetList()
    {
        try {
            $params = $this->request->all();

            // DataTables parameters
            $draw = $params['draw'] ?? 1;
            $start = $params['start'] ?? 0;
            $length = $params['length'] ?? 10;
            $searchValue = $params['search']['value'] ?? '';

            // Build query using Supplier model
            $query = Supplier::with('branch');

            // Apply search
            if (!empty($searchValue)) {
                $query->where(function($q) use ($searchValue) {
                    $q->where('name', 'like', "%{$searchValue}%")
                      ->orWhere('code', 'like', "%{$searchValue}%")
                      ->orWhere('company', 'like', "%{$searchValue}%")
                      ->orWhere('phone', 'like', "%{$searchValue}%")
                      ->orWhere('email', 'like', "%{$searchValue}%");
                });
            }

            // Get total count before pagination
            $totalRecords = $query->count();

            // Apply pagination
            $suppliers = $query->skip($start)
                              ->take($length)
                              ->orderBy('created_at', 'desc')
                              ->get();

            // Format data for DataTables
            $data = $suppliers->map(function($supplier) {
                return [
                    'id' => $supplier->id,
                    'code' => $supplier->code ?? '-',
                    'name' => $supplier->name,
                    'company' => $supplier->company ?? '-',
                    'phone' => $supplier->phone ?? '-',
                    'email' => $supplier->email ?? '-',
                    'branch' => [
                        'name' => $supplier->branch->name ?? '-'
                    ],
                    'status_badge' => $supplier->status_badge,
                    'supplier_edit' => route('supplier.edit', $supplier->id),
                    'action' => $this->generateActionButtons($supplier)
                ];
            });

            return response()->json([
                'draw' => intval($draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalRecords,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'draw' => $params['draw'] ?? 1,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Error loading suppliers: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Generate action buttons for supplier.
     */
    private function generateActionButtons($supplier)
    {
        $buttons = [];

        // View button
        $buttons[] = '<a href="' . route('supplier.detail', $supplier->id) . '" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1" title="Xem chi tiết">
                        <span class="svg-icon svg-icon-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path d="M17.5 11H6.5C4 11 2 9 2 6.5C2 4 4 2 6.5 2H17.5C20 2 22 4 22 6.5C22 9 20 11 17.5 11ZM15 6.5C15 7.9 16.1 9 17.5 9C18.9 9 20 7.9 20 6.5C20 5.1 18.9 4 17.5 4C16.1 4 15 5.1 15 6.5Z" fill="black"/>
                                <path opacity="0.3" d="M17.5 22H6.5C4 22 2 20 2 17.5C2 15 4 13 6.5 13H17.5C20 13 22 15 22 17.5C22 20 20 22 17.5 22ZM4 17.5C4 18.9 5.1 20 6.5 20C7.9 20 9 18.9 9 17.5C9 16.1 7.9 15 6.5 15C5.1 15 4 16.1 4 17.5Z" fill="black"/>
                            </svg>
                        </span>
                      </a>';

        // Edit button
        $buttons[] = '<a href="' . route('supplier.edit', $supplier->id) . '" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1" title="Chỉnh sửa">
                        <span class="svg-icon svg-icon-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path opacity="0.3" d="M21.4 8.35303L19.241 10.511L13.485 4.755L15.643 2.59595C16.0248 2.21423 16.5426 1.99988 17.0825 1.99988C17.6224 1.99988 18.1402 2.21423 18.522 2.59595L21.4 5.474C21.7817 5.85581 21.9962 6.37355 21.9962 6.91345C21.9962 7.45335 21.7817 7.97122 21.4 8.35303ZM3.68699 21.932L9.88699 19.865L4.13099 14.109L2.06399 20.309C1.98815 20.5354 1.97703 20.7787 2.03189 21.0111C2.08674 21.2436 2.2054 21.4561 2.37449 21.6248C2.54359 21.7934 2.75641 21.9115 2.989 21.9658C3.22158 22.0201 3.4647 22.0084 3.69099 21.932H3.68699Z" fill="black"/>
                                <path d="M5.574 21.3L3.692 21.928C3.46591 22.0032 3.22334 22.0141 2.99144 21.9594C2.75954 21.9046 2.54744 21.7864 2.3789 21.6179C2.21036 21.4495 2.09202 21.2375 2.03711 21.0056C1.9822 20.7737 1.99289 20.5312 2.06799 20.3051L2.696 18.422L5.574 21.3Z" fill="black"/>
                            </svg>
                        </span>
                      </a>';

        // Delete button
        $buttons[] = '<button class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm" data-kt-suppliers-table-filter="delete_row" data-supplier-id="' . $supplier->id . '" title="Xóa">
                        <span class="svg-icon svg-icon-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path d="M5 9C5 8.44772 5.44772 8 6 8H18C18.5523 8 19 8.44772 19 9S18.5523 10 18 10H6C5.44772 10 5 9.55228 5 9Z" fill="black"/>
                                <path opacity="0.5" d="M5 5C5 4.44772 5.44772 4 6 4H18C18.5523 4 19 4.44772 19 5S18.5523 6 18 6H6C5.44772 6 5 5.55228 5 5Z" fill="black"/>
                                <path opacity="0.5" d="M5 13C5 12.4477 5.44772 12 6 12H18C18.5523 12 19 12.4477 19 13S18.5523 14 18 14H6C5.44772 14 5 13.5523 5 13Z" fill="black"/>
                            </svg>
                        </span>
                      </button>';

        return '<div class="d-flex justify-content-end flex-shrink-0">' . implode('', $buttons) . '</div>';
    }

    /**
     * Show the form for creating a new supplier.
     */
    public function add()
    {
        $groups = $this->supplierService->getGroups();

        return view('admin.suppliers.add', compact('groups'));
    }

    /**
     * Store a newly created supplier.
     */
    public function addAction()
    {
        DB::beginTransaction();
        try {
            $params = $this->request->all();
            $user = auth()->user();
            $params['created_by_user'] = $user->id;
            $params['updated_by_user'] = $user->id;

            $validator = $this->validator->make($params, 'add_supplier_fields');

            if ($validator->fails()) {
                $errors = $validator->errors();
                $errorMessages = [];

                foreach ($errors->all() as $error) {
                    $errorMessages[] = $error;
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed. Please check the following errors:',
                    'errors' => $errorMessages,
                    'detailed_errors' => $errors->toArray()
                ], 422);
            }

            $supplier = $this->supplierService->create($params);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Supplier created successfully!',
                'data' => $supplier,
                'redirect' => route('supplier.list')
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error creating supplier: ' . $e->getMessage(),
                'errors' => [$e->getMessage()]
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified supplier.
     */
    public function edit($id = 0)
    {
        if (!$id) {
            abort(404);
        }

        $supplier = $this->supplierService->findById($id);

        if (!$supplier) {
            abort(404);
        }

        $groups = $this->supplierService->getGroups();

        return view('admin.suppliers.edit', compact('supplier', 'groups'));
    }

    /**
     * Update the specified supplier.
     */
    public function editAction($id = 0)
    {
        DB::beginTransaction();
        
        if (!$id) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid supplier ID provided.',
                'errors' => ['Supplier ID is required and must be a valid number.']
            ], 400);
        }

        try {
            $params = $this->request->all();
            $user = auth()->user();
            $params['updated_by_user'] = $user->id;

            $validator = $this->validator->make($params, 'edit_supplier_fields');

            if ($validator->fails()) {
                $errors = $validator->errors();
                $errorMessages = [];

                foreach ($errors->all() as $error) {
                    $errorMessages[] = $error;
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed. Please check the following errors:',
                    'errors' => $errorMessages,
                    'detailed_errors' => $errors->toArray()
                ], 422);
            }

            $supplier = $this->supplierService->update($id, $params);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Supplier updated successfully!',
                'data' => $supplier,
                'redirect' => route('supplier.list')
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error updating supplier: ' . $e->getMessage(),
                'errors' => [$e->getMessage()]
            ], 500);
        }
    }

    /**
     * Remove the specified supplier.
     */
    public function delete($id = 0)
    {
        if (!$id) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid supplier ID provided.'
            ], 400);
        }

        try {
            $this->supplierService->delete($id);

            return response()->json([
                'success' => true,
                'message' => 'Supplier deleted successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting supplier: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove multiple suppliers.
     */
    public function deleteMany()
    {
        $ids = $this->request->input('ids', []);

        if (empty($ids)) {
            return response()->json([
                'success' => false,
                'message' => 'No suppliers selected for deletion.'
            ], 400);
        }

        try {
            $this->supplierService->deleteMany($ids);

            return response()->json([
                'success' => true,
                'message' => 'Selected suppliers deleted successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting suppliers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get supplier details.
     */
    public function detail($id = 0)
    {
        if (!$id) {
            abort(404);
        }

        $supplier = $this->supplierService->findById($id);

        if (!$supplier) {
            abort(404);
        }

        $stats = $supplier->getStats();

        return view('admin.suppliers.detail', compact('supplier', 'stats'));
    }

    /**
     * Get active suppliers for dropdown (AJAX).
     */
    public function getActiveSuppliers()
    {
        $suppliers = $this->supplierService->getActiveSuppliers();

        return response()->json([
            'success' => true,
            'data' => $suppliers
        ]);
    }

    /**
     * Check if supplier code is unique (AJAX).
     */
    public function checkCodeUnique()
    {
        $code = $this->request->input('code');
        $excludeId = $this->request->input('exclude_id');

        $isUnique = $this->supplierService->isCodeUnique($code, $excludeId);

        return response()->json([
            'success' => true,
            'is_unique' => $isUnique
        ]);
    }

    /**
     * Get supplier statistics (AJAX).
     */
    public function getStatistics()
    {
        $stats = $this->supplierService->getStatistics();

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

}
