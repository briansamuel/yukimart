<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Product;
use App\Models\BranchShop;
use App\Http\Resources\V1\InvoiceResource;
use App\Http\Resources\V1\CustomerResource;
use App\Http\Resources\V1\ProductResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvoiceController extends Controller
{
    /**
     * Display a listing of invoices with pagination and filters
     */
    public function index(Request $request)
    {
        try {
            $query = Invoice::with(['customer', 'branchShop', 'creator', 'seller', 'invoiceItems']);
            
            // Apply filters
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            
            if ($request->filled('payment_status')) {
                $query->where('payment_status', $request->payment_status);
            }
            
            if ($request->filled('customer_id')) {
                $query->where('customer_id', $request->customer_id);
            }
            
            if ($request->filled('branch_shop_id')) {
                $query->where('branch_shop_id', $request->branch_shop_id);
            }
            
            if ($request->filled('date_from')) {
                $query->whereDate('invoice_date', '>=', $request->date_from);
            }
            
            if ($request->filled('date_to')) {
                $query->whereDate('invoice_date', '<=', $request->date_to);
            }
            
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('invoice_number', 'like', "%{$search}%")
                      ->orWhere('customer_name', 'like', "%{$search}%")
                      ->orWhere('reference_number', 'like', "%{$search}%");
                });
            }
            
            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);
            
            // Pagination
            $perPage = $request->get('per_page', 15);
            $invoices = $query->paginate($perPage);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Invoices retrieved successfully',
                'data' => InvoiceResource::collection($invoices),
                'meta' => [
                    'current_page' => $invoices->currentPage(),
                    'last_page' => $invoices->lastPage(),
                    'per_page' => $invoices->perPage(),
                    'total' => $invoices->total(),
                    'from' => $invoices->firstItem(),
                    'to' => $invoices->lastItem(),
                ]
            ], 200);
            
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
                'invoiceItems.product', 
                'creator', 
                'seller',
                'payments'
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
                'customer_id', 'customer_name', 'branch_shop_id', 'invoice_type',
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
                'customer_id', 'customer_name', 'invoice_type', 'invoice_date',
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
}
