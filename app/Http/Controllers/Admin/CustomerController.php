<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers.
     */
    public function index(Request $request)
    {
        try {
            // Get customers with pagination
            $customers = Customer::with(['orders'])
                ->when($request->search, function ($query, $search) {
                    return $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%")
                          ->orWhere('phone', 'like', "%{$search}%");
                    });
                })
                ->when($request->status, function ($query, $status) {
                    return $query->where('status', $status);
                })
                ->orderBy('created_at', 'desc')
                ->paginate(15);

            // If AJAX request, return JSON
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => $customers->items(),
                    'pagination' => [
                        'current_page' => $customers->currentPage(),
                        'last_page' => $customers->lastPage(),
                        'per_page' => $customers->perPage(),
                        'total' => $customers->total(),
                    ]
                ]);
            }

            return view('admin.customers.index', compact('customers'));
        } catch (Exception $e) {
            Log::error('Error loading customers: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi tải danh sách khách hàng.'
                ], 500);
            }

            return back()->with('error', 'Có lỗi xảy ra khi tải danh sách khách hàng.');
        }
    }

    /**
     * Show the form for creating a new customer.
     */
    public function create()
    {
        return view('admin.customers.create');
    }

    /**
     * Store a newly created customer in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'nullable|email|unique:customers,email',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:500',
                'birth_date' => 'nullable|date',
                'gender' => 'nullable|in:male,female,other',
                'status' => 'required|in:active,inactive',
                'notes' => 'nullable|string|max:1000',
            ]);

            $customer = Customer::create($validated);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Khách hàng đã được tạo thành công.',
                    'data' => $customer,
                    'redirect' => url('/admin/customers')
                ]);
            }

            return redirect()->route('customers.index')
                ->with('success', 'Khách hàng đã được tạo thành công.');
        } catch (Exception $e) {
            Log::error('Error creating customer: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi tạo khách hàng.',
                    'errors' => $e->getMessage()
                ], 500);
            }

            return back()->withInput()
                ->with('error', 'Có lỗi xảy ra khi tạo khách hàng.');
        }
    }

    /**
     * Display the specified customer.
     */
    public function show(Customer $customer)
    {
        try {
            $customer->load(['orders.orderItems.product']);
            
            return view('admin.customers.show', compact('customer'));
        } catch (Exception $e) {
            Log::error('Error loading customer: ' . $e->getMessage());
            
            return back()->with('error', 'Có lỗi xảy ra khi tải thông tin khách hàng.');
        }
    }

    /**
     * Show the form for editing the specified customer.
     */
    public function edit(Customer $customer)
    {
        return view('admin.customers.edit', compact('customer'));
    }

    /**
     * Update the specified customer in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'nullable|email|unique:customers,email,' . $customer->id,
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:500',
                'birth_date' => 'nullable|date',
                'gender' => 'nullable|in:male,female,other',
                'status' => 'required|in:active,inactive',
                'notes' => 'nullable|string|max:1000',
            ]);

            $customer->update($validated);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Khách hàng đã được cập nhật thành công.',
                    'data' => $customer,
                    'redirect' => url('/admin/customers')
                ]);
            }

            return redirect()->route('customers.index')
                ->with('success', 'Khách hàng đã được cập nhật thành công.');
        } catch (Exception $e) {
            Log::error('Error updating customer: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi cập nhật khách hàng.',
                    'errors' => $e->getMessage()
                ], 500);
            }

            return back()->withInput()
                ->with('error', 'Có lỗi xảy ra khi cập nhật khách hàng.');
        }
    }

    /**
     * Remove the specified customer from storage.
     */
    public function destroy(Customer $customer)
    {
        try {
            // Check if customer has orders
            if ($customer->orders()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể xóa khách hàng đã có đơn hàng.'
                ], 400);
            }

            $customer->delete();

            return response()->json([
                'success' => true,
                'message' => 'Khách hàng đã được xóa thành công.'
            ]);
        } catch (Exception $e) {
            Log::error('Error deleting customer: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa khách hàng.'
            ], 500);
        }
    }

    /**
     * Search customers for AJAX requests.
     */
    public function search(Request $request)
    {
        try {
            $query = $request->get('q', '');
            
            $customers = Customer::where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%")
                  ->orWhere('phone', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get(['id', 'name', 'email', 'phone']);

            return response()->json([
                'success' => true,
                'data' => $customers
            ]);
        } catch (Exception $e) {
            Log::error('Error searching customers: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tìm kiếm khách hàng.'
            ], 500);
        }
    }

    /**
     * Find customer by phone number.
     */
    public function findByPhone($phone)
    {
        try {
            $customer = Customer::where('phone', $phone)->first();
            
            if (!$customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy khách hàng với số điện thoại này.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $customer
            ]);
        } catch (Exception $e) {
            Log::error('Error finding customer by phone: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tìm kiếm khách hàng.'
            ], 500);
        }
    }
}
