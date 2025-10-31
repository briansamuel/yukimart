<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Http\Resources\V1\InvoiceResource;
use App\Http\Resources\V1\InvoiceListResource;
use App\Http\Traits\ApiOptimizationTrait;
use App\Http\Requests\Api\V1\Invoice\InvoiceListRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvoiceController extends Controller
{
    use ApiOptimizationTrait;
    /**
     * Display a listing of invoices with pagination and filters
     */
    public function index(InvoiceListRequest $request)
    {
        $startTime = microtime(true);

        try {
            // Generate cache key for this request
            $cacheKey = $this->generateCacheKey($request, 'invoices_list');

            // Try to get cached response
            $cachedResponse = $this->cacheResponse($cacheKey, null, 2); // 2 minutes cache

            if ($cachedResponse) {
                $response = response()->json($cachedResponse);
                return $this->addPerformanceHeaders($response, $startTime);
            }

            // Parse include parameter for dynamic relationship loading
            $includes = $this->parseIncludes($request->get('include', 'branchShop,seller,creator'));

            // Base query with optimized eager loading
            $query = Invoice::query();

            // Apply dynamic includes
            if (!empty($includes)) {
                $query->with($includes);
            }

            // Apply filters with optimization
            $this->applyFilters($query, $request);

            // Apply sorting
            $this->applySorting($query, $request);

            // Optimized pagination
            $invoices = $this->optimizePagination($query, $request);

            // Transform to resource collection
            $resourceCollection = InvoiceListResource::collection($invoices);

            // Parse fields for field selection
            $fields = $this->parseFields($request->get('fields'));

            // Prepare response data
            $responseData = [
                'status' => 'success',
                'message' => 'Invoices retrieved successfully',
                'data' => $resourceCollection,
                'meta' => [
                    'current_page' => $invoices->currentPage(),
                    'last_page' => $invoices->lastPage(),
                    'per_page' => $invoices->perPage(),
                    'total' => $invoices->total(),
                    'from' => $invoices->firstItem(),
                    'to' => $invoices->lastItem(),
                    'execution_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms'
                ]
            ];

            // Apply field filtering if requested
            if (!empty($fields)) {
                $responseData = $this->filterFields($responseData, $fields);
            }

            // Cache the response
            $this->cacheResponse($cacheKey, $responseData, 2);

            // Create response with optimization headers
            $response = response()->json($responseData, 200);
            $response = $this->addPerformanceHeaders($response, $startTime);
            $response = $this->addRateLimitHeaders($response);

            return $response;
            
        } catch (\Exception $e) {
            Log::error('Invoice listing failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve invoices',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Display the specified invoice
     */
    public function show($id)
    {
        try {
            $invoice = Invoice::with([
                'customer',
                'branchShop',
                'invoiceItems.product:id,product_name,product_thumbnail,sku',
                'creator:id,username,full_name,email',
                'seller:id,username,full_name,email',
                'payments:id,reference_type,reference_id,payment_number,payment_type,payment_method,amount,actual_amount,status,payment_date,description,notes,created_by,created_at,updated_at',
                'payments.creator:id,username,full_name,email'
            ])->findOrFail($id);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Invoice retrieved successfully',
                'data' => new InvoiceResource($invoice)
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invoice not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }
    
    /**
     * Store a newly created invoice
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'customer_id' => 'nullable|exists:customers,id',
                'customer_name' => 'required_if:customer_id,null|string|max:255',
                'branch_shop_id' => 'required|exists:branch_shops,id',
                'invoice_type' => 'required|in:sale,service,other',
                'sales_channel' => 'nullable|in:offline,online,marketplace,social_media,phone_order',
                'invoice_date' => 'required|date',
                'due_date' => 'nullable|date|after_or_equal:invoice_date',
                'payment_terms' => 'nullable|string',
                'notes' => 'nullable|string',
                'terms_conditions' => 'nullable|string',
                'reference_number' => 'nullable|string|max:100',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'nullable|exists:products,id',
                'items.*.product_name' => 'required|string|max:255',
                'items.*.product_sku' => 'nullable|string|max:100',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.unit' => 'required|string|max:50',
                'items.*.unit_price' => 'required|numeric|min:0',
                'items.*.discount_rate' => 'nullable|numeric|min:0|max:100',
                'items.*.discount_amount' => 'nullable|numeric|min:0',
                'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
                'items.*.notes' => 'nullable|string',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            DB::beginTransaction();
            
            $invoiceData = $request->only([
                'customer_id', 'customer_name', 'branch_shop_id', 'invoice_type', 'sales_channel',
                'invoice_date', 'due_date', 'payment_terms', 'notes',
                'terms_conditions', 'reference_number'
            ]);
            
            // Set defaults
            $invoiceData['status'] = 'draft';
            $invoiceData['payment_status'] = 'unpaid';
            $invoiceData['created_by'] = auth()->id();
            $invoiceData['sold_by'] = auth()->id();
            
            // Calculate totals
            $subtotal = 0;
            $totalTax = 0;
            $totalDiscount = 0;
            
            foreach ($request->items as $item) {
                $lineSubtotal = $item['quantity'] * $item['unit_price'];
                $lineDiscount = $item['discount_amount'] ?? (($item['discount_rate'] ?? 0) / 100 * $lineSubtotal);
                $lineTax = ($item['tax_rate'] ?? 0) / 100 * ($lineSubtotal - $lineDiscount);
                
                $subtotal += $lineSubtotal;
                $totalDiscount += $lineDiscount;
                $totalTax += $lineTax;
            }
            
            $invoiceData['subtotal'] = $subtotal;
            $invoiceData['discount_amount'] = $totalDiscount;
            $invoiceData['tax_amount'] = $totalTax;
            $invoiceData['total_amount'] = $subtotal - $totalDiscount + $totalTax;
            
            $invoice = Invoice::create($invoiceData);
            
            // Create invoice items
            foreach ($request->items as $index => $item) {
                $lineSubtotal = $item['quantity'] * $item['unit_price'];
                $lineDiscount = $item['discount_amount'] ?? (($item['discount_rate'] ?? 0) / 100 * $lineSubtotal);
                $lineTax = ($item['tax_rate'] ?? 0) / 100 * ($lineSubtotal - $lineDiscount);
                $lineTotal = $lineSubtotal - $lineDiscount + $lineTax;
                
                $invoice->invoiceItems()->create([
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'product_sku' => $item['product_sku'] ?? null,
                    'product_description' => $item['product_description'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'],
                    'unit_price' => $item['unit_price'],
                    'discount_rate' => $item['discount_rate'] ?? 0,
                    'discount_amount' => $lineDiscount,
                    'tax_rate' => $item['tax_rate'] ?? 0,
                    'tax_amount' => $lineTax,
                    'line_total' => $lineTotal,
                    'notes' => $item['notes'] ?? null,
                    'sort_order' => $index + 1,
                ]);
            }
            
            DB::commit();
            
            // Load relationships for response
            $invoice->load(['customer', 'branchShop', 'invoiceItems.product', 'creator', 'seller']);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Invoice created successfully',
                'data' => new InvoiceResource($invoice)
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Invoice creation failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create invoice',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Update the specified invoice
     */
    public function update(Request $request, $id)
    {
        try {
            $invoice = Invoice::findOrFail($id);
            
            // Check if invoice can be updated
            if (in_array($invoice->status, ['paid', 'cancelled'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot update paid or cancelled invoice'
                ], 422);
            }
            
            $validator = Validator::make($request->all(), [
                'customer_id' => 'nullable|exists:customers,id',
                'customer_name' => 'required_if:customer_id,null|string|max:255',
                'invoice_type' => 'sometimes|in:sale,service,other',
                'sales_channel' => 'sometimes|in:offline,online,marketplace,social_media,phone_order',
                'invoice_date' => 'sometimes|date',
                'due_date' => 'nullable|date|after_or_equal:invoice_date',
                'payment_terms' => 'nullable|string',
                'notes' => 'nullable|string',
                'terms_conditions' => 'nullable|string',
                'reference_number' => 'nullable|string|max:100',
                'status' => 'sometimes|in:draft,sent,processing,completed,cancelled',
                'payment_status' => 'sometimes|in:unpaid,partial,paid,overdue',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $updateData = $request->only([
                'customer_id', 'customer_name', 'invoice_type', 'sales_channel', 'invoice_date',
                'due_date', 'payment_terms', 'notes', 'terms_conditions',
                'reference_number', 'status', 'payment_status'
            ]);
            
            $updateData['updated_by'] = auth()->id();
            
            $invoice->update($updateData);
            
            // Load relationships for response
            $invoice->load(['customer', 'branchShop', 'invoiceItems.product', 'creator', 'seller']);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Invoice updated successfully',
                'data' => new InvoiceResource($invoice)
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Invoice update failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update invoice',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Remove the specified invoice
     */
    public function destroy($id)
    {
        try {
            $invoice = Invoice::findOrFail($id);
            
            // Check if invoice can be deleted
            if ($invoice->status === 'paid') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot delete paid invoice'
                ], 422);
            }
            
            $invoice->delete();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Invoice deleted successfully'
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Invoice deletion failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete invoice',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get invoice statistics
     */
    public function statistics(Request $request)
    {
        try {
            $query = Invoice::query();
            
            // Apply date filters
            if ($request->filled('date_from')) {
                $query->whereDate('invoice_date', '>=', $request->date_from);
            }
            
            if ($request->filled('date_to')) {
                $query->whereDate('invoice_date', '<=', $request->date_to);
            }
            
            if ($request->filled('branch_shop_id')) {
                $query->where('branch_shop_id', $request->branch_shop_id);
            }
            
            // Clone query for different aggregations
            $baseQuery = clone $query;
            $statusQuery = clone $query;

            // Calculate payment statistics from payments table
            $paidInvoices = DB::table('payments')
                ->where('reference_type', 'invoice')
                ->whereIn('reference_id', function($subQuery) use ($query) {
                    $subQuery->select('id')->from('invoices');
                    if (request()->filled('date_from')) {
                        $subQuery->whereDate('invoice_date', '>=', request()->date_from);
                    }
                    if (request()->filled('date_to')) {
                        $subQuery->whereDate('invoice_date', '<=', request()->date_to);
                    }
                    if (request()->filled('branch_shop_id')) {
                        $subQuery->where('branch_shop_id', request()->branch_shop_id);
                    }
                })
                ->sum('amount');

            $stats = [
                'total_invoices' => $baseQuery->count(),
                'total_amount' => $baseQuery->sum('total_amount'),
                'paid_amount' => (float) $paidInvoices,
                'outstanding_amount' => $baseQuery->sum('total_amount') - $paidInvoices,
                'by_status' => $statusQuery->groupBy('status')
                    ->selectRaw('status, count(*) as count, sum(total_amount) as total')
                    ->get()
                    ->keyBy('status'),
            ];
            
            return response()->json([
                'status' => 'success',
                'message' => 'Statistics retrieved successfully',
                'data' => $stats
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Invoice statistics failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Parse include parameter for dynamic relationship loading
     */
    private function parseIncludes($includeParam)
    {
        if (empty($includeParam)) {
            return [];
        }

        $includes = [];
        $availableIncludes = [
            'branchShop' => 'branchShop:id,name',
            'seller' => 'seller:id,username,full_name',
            'creator' => 'creator:id,username,full_name',
            'customer' => 'customer:id,customer_name,email,phone',
            'items' => [
                'invoiceItems:id,invoice_id,product_id,product_name,product_sku,quantity,unit_price',
                'invoiceItems.product:id,product_thumbnail'
            ],
            'payments' => [
                'payments:id,reference_type,reference_id,payment_number,payment_method,amount,actual_amount,status,payment_date,created_by,created_at',
                'payments.creator:id,username,full_name'
            ]
        ];

        $requestedIncludes = explode(',', $includeParam);

        foreach ($requestedIncludes as $include) {
            $include = trim($include);
            if (isset($availableIncludes[$include])) {
                if (is_array($availableIncludes[$include])) {
                    $includes = array_merge($includes, $availableIncludes[$include]);
                } else {
                    $includes[] = $availableIncludes[$include];
                }
            }
        }

        return $includes;
    }

    /**
     * Apply filters to query with optimization
     */
    private function applyFilters($query, $request)
    {
        // Status filter
        if ($request->filled('status')) {
            if (is_array($request->status)) {
                $query->whereIn('status', $request->status);
            } else {
                $query->where('status', $request->status);
            }
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('invoice_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('invoice_date', '<=', $request->date_to);
        }

        // Customer filter
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // Customer phone filter
        if ($request->filled('customer_phone')) {
            $query->where('customer_phone', 'like', '%' . $request->customer_phone . '%');
        }

        // Branch shop filter
        if ($request->filled('branch_shop_id')) {
            $query->where('branch_shop_id', $request->branch_shop_id);
        }

        // Sales channel filter
        if ($request->filled('sales_channel')) {
            $query->where('sales_channel', $request->sales_channel);
        }

        // Seller filter
        if ($request->filled('sold_by')) {
            $query->where('sold_by', $request->sold_by);
        }

        // Creator filter
        if ($request->filled('created_by')) {
            $query->where('created_by', $request->created_by);
        }

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }
    }

    /**
     * Apply sorting to query
     */
    private function applySorting($query, $request)
    {
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        // Validate sort fields
        $allowedSortFields = [
            'id', 'invoice_number', 'invoice_date', 'due_date',
            'total_amount', 'status', 'created_at', 'updated_at'
        ];

        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }
    }
}
