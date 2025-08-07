<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Http\Resources\V1\CustomerResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers with pagination and filters
     */
    public function index(Request $request)
    {
        try {
            $query = Customer::with(['branchShop']);
            
            // Apply filters
            if ($request->filled('customer_type')) {
                $query->where('customer_type', $request->customer_type);
            }
            
            if ($request->filled('customer_group')) {
                $query->where('customer_group', $request->customer_group);
            }
            
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            
            if ($request->filled('branch_shop_id')) {
                $query->where('branch_shop_id', $request->branch_shop_id);
            }
            
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('customer_code', 'like', "%{$search}%");
                });
            }
            
            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);
            
            // Pagination
            $perPage = $request->get('per_page', 15);
            $customers = $query->paginate($perPage);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Customers retrieved successfully',
                'data' => CustomerResource::collection($customers),
                'meta' => [
                    'current_page' => $customers->currentPage(),
                    'last_page' => $customers->lastPage(),
                    'per_page' => $customers->perPage(),
                    'total' => $customers->total(),
                    'from' => $customers->firstItem(),
                    'to' => $customers->lastItem(),
                ]
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Customer listing failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve customers',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Display the specified customer
     */
    public function show($id)
    {
        try {
            $customer = Customer::with(['branchShop', 'orders', 'invoices'])->findOrFail($id);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Customer retrieved successfully',
                'data' => new CustomerResource($customer)
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Customer not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }
    
    /**
     * Store a newly created customer
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'phone' => 'required|string|max:20|unique:customers,phone',
                'email' => 'nullable|email|max:255|unique:customers,email',
                'facebook' => 'nullable|string|max:255',
                'address' => 'nullable|string|max:500',
                'area' => 'nullable|string|max:255',
                'customer_type' => 'required|in:individual,business',
                'customer_group' => 'nullable|string|max:100',
                'tax_code' => 'nullable|string|max:50',
                'status' => 'required|in:active,inactive',
                'notes' => 'nullable|string|max:1000',
                'birthday' => 'nullable|date',
                'points' => 'nullable|integer|min:0',
                'branch_shop_id' => 'required|exists:branch_shops,id',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $customerData = $request->all();
            $customerData['customer_code'] = $this->generateCustomerCode();
            $customerData['created_by'] = auth()->id();
            
            $customer = Customer::create($customerData);
            
            // Load relationships for response
            $customer->load(['branchShop']);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Customer created successfully',
                'data' => new CustomerResource($customer)
            ], 201);
            
        } catch (\Exception $e) {
            Log::error('Customer creation failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create customer',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Update the specified customer
     */
    public function update(Request $request, $id)
    {
        try {
            $customer = Customer::findOrFail($id);
            
            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|string|max:255',
                'phone' => 'sometimes|string|max:20|unique:customers,phone,' . $id,
                'email' => 'nullable|email|max:255|unique:customers,email,' . $id,
                'facebook' => 'nullable|string|max:255',
                'address' => 'nullable|string|max:500',
                'area' => 'nullable|string|max:255',
                'customer_type' => 'sometimes|in:individual,business',
                'customer_group' => 'nullable|string|max:100',
                'tax_code' => 'nullable|string|max:50',
                'status' => 'sometimes|in:active,inactive',
                'notes' => 'nullable|string|max:1000',
                'birthday' => 'nullable|date',
                'points' => 'nullable|integer|min:0',
                'branch_shop_id' => 'sometimes|exists:branch_shops,id',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $updateData = $request->all();
            $updateData['updated_by'] = auth()->id();
            
            $customer->update($updateData);
            
            // Load relationships for response
            $customer->load(['branchShop']);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Customer updated successfully',
                'data' => new CustomerResource($customer)
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Customer update failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update customer',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Remove the specified customer
     */
    public function destroy($id)
    {
        try {
            $customer = Customer::findOrFail($id);
            
            // Check if customer has orders or invoices
            if ($customer->orders()->count() > 0 || $customer->invoices()->count() > 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot delete customer with existing orders or invoices'
                ], 422);
            }
            
            $customer->delete();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Customer deleted successfully'
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Customer deletion failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete customer',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get customer statistics
     */
    public function statistics(Request $request)
    {
        try {
            $query = Customer::query();
            
            // Apply filters
            if ($request->filled('branch_shop_id')) {
                $query->where('branch_shop_id', $request->branch_shop_id);
            }
            
            $stats = [
                'total_customers' => $query->count(),
                'active_customers' => $query->where('status', 'active')->count(),
                'inactive_customers' => $query->where('status', 'inactive')->count(),
                'by_type' => $query->groupBy('customer_type')
                    ->selectRaw('customer_type, count(*) as count')
                    ->get()
                    ->keyBy('customer_type'),
                'by_group' => $query->whereNotNull('customer_group')
                    ->groupBy('customer_group')
                    ->selectRaw('customer_group, count(*) as count')
                    ->get()
                    ->keyBy('customer_group'),
                'total_points' => $query->sum('points'),
            ];
            
            return response()->json([
                'status' => 'success',
                'message' => 'Customer statistics retrieved successfully',
                'data' => $stats
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Customer statistics failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve customer statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Generate unique customer code
     */
    private function generateCustomerCode()
    {
        do {
            $code = 'KH' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (Customer::where('customer_code', $code)->exists());
        
        return $code;
    }
}
