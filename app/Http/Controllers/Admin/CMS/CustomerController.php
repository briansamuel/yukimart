<?php

namespace App\Http\Controllers\Admin\CMS;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers
     */
    public function index()
    {
        return view('admin.customers.index');
    }

    /**
     * Get customers data for DataTables
     */
    public function getData(Request $request)
    {
        try {
            $query = Customer::withCount(['orders'])
                ->withSum('orders', 'final_amount')
                ->with(['orders' => function($q) {
                    $q->latest()->limit(1);
                }]);

            // Apply search
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('address', 'like', "%{$search}%");
                });
            }

            // Apply filters
            if ($request->has('status') && $request->status !== '') {
                $query->where('status', $request->status);
            }

            if ($request->has('customer_type') && $request->customer_type !== '') {
                $query->where('customer_type', $request->customer_type);
            }

            // Get total count before pagination
            $totalRecords = $query->count();

            // Apply ordering
            $orderColumn = $request->order[0]['column'] ?? 0;
            $orderDirection = $request->order[0]['dir'] ?? 'desc';
            
            $columns = ['id', 'name', 'email', 'phone', 'orders_count', 'orders_sum_final_amount', 'created_at'];
            if (isset($columns[$orderColumn])) {
                $query->orderBy($columns[$orderColumn], $orderDirection);
            } else {
                $query->orderBy('created_at', 'desc');
            }

            // Apply pagination
            $start = $request->start ?? 0;
            $length = $request->length ?? 10;
            $customers = $query->skip($start)->take($length)->get();

            $data = $customers->map(function($customer) {
                $lastOrder = $customer->orders->first();
                
                return [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                    'address' => $customer->address,
                    'customer_type' => $customer->customer_type,
                    'customer_type_display' => $customer->customer_type_display,
                    'status' => $customer->status,
                    'status_badge' => $customer->status_badge,
                    'orders_count' => $customer->orders_count,
                    'total_spent' => number_format($customer->orders_sum_final_amount ?? 0, 0, ',', '.'),
                    'last_order_date' => $lastOrder ? $lastOrder->created_at->format('d/m/Y') : '',
                    'created_at' => $customer->created_at->format('d/m/Y H:i'),
                    'avatar_url' => $customer->avatar_url,
                ];
            });

            return response()->json([
                'draw' => intval($request->draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalRecords,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Lỗi khi tải dữ liệu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for creating a new customer
     */
    public function create()
    {
        return view('admin.customers.create');
    }

    /**
     * Store a newly created customer
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'ward' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'customer_type' => 'required|in:individual,business',
            'status' => 'required|in:active,inactive',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'notes' => 'nullable|string|max:1000',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'name.required' => __('customer.name_required'),
            'email.required' => __('customer.email_required'),
            'email.email' => __('customer.email_invalid'),
            'email.unique' => __('customer.email_unique'),
            'customer_type.required' => __('customer.type_required'),
            'status.required' => __('customer.status_required'),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => __('customer.validation_failed'),
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $request->all();

            // Handle avatar upload
            if ($request->hasFile('avatar')) {
                $avatar = $request->file('avatar');
                $avatarName = time() . '_' . \Str::slug($data['name']) . '.' . $avatar->getClientOriginalExtension();
                $avatar->storeAs('public/customers', $avatarName);
                $data['avatar'] = 'customers/' . $avatarName;
            }

            $customer = Customer::create($data);

            return response()->json([
                'success' => true,
                'message' => __('customer.created_successfully'),
                'data' => $customer
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('customer.create_failed') . ': ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified customer
     */
    public function show(Customer $customer)
    {
        $customer->load(['orders.orderItems.product']);
        
        // Get customer statistics
        $stats = [
            'total_orders' => $customer->orders->count(),
            'total_spent' => $customer->orders->sum('final_amount'),
            'avg_order_value' => $customer->orders->avg('final_amount'),
            'last_order_date' => $customer->orders->max('created_at'),
            'first_order_date' => $customer->orders->min('created_at'),
        ];

        // Get recent orders
        $recentOrders = $customer->orders()
            ->with(['orderItems.product'])
            ->latest()
            ->limit(10)
            ->get();

        // Get order statistics by month
        $monthlyStats = $customer->orders()
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as orders_count, SUM(final_amount) as total_amount')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

        return view('admin.customers.show', compact('customer', 'stats', 'recentOrders', 'monthlyStats'));
    }

    /**
     * Show the form for editing the specified customer
     */
    public function edit(Customer $customer)
    {
        return view('admin.customers.edit', compact('customer'));
    }

    /**
     * Update the specified customer
     */
    public function update(Request $request, Customer $customer)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email,' . $customer->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'ward' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'customer_type' => 'required|in:individual,business',
            'status' => 'required|in:active,inactive',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'notes' => 'nullable|string|max:1000',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'name.required' => __('customer.name_required'),
            'email.required' => __('customer.email_required'),
            'email.email' => __('customer.email_invalid'),
            'email.unique' => __('customer.email_unique'),
            'customer_type.required' => __('customer.type_required'),
            'status.required' => __('customer.status_required'),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => __('customer.validation_failed'),
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $request->all();

            // Handle avatar upload
            if ($request->hasFile('avatar')) {
                // Delete old avatar
                if ($customer->avatar) {
                    \Storage::delete('public/' . $customer->avatar);
                }
                
                $avatar = $request->file('avatar');
                $avatarName = time() . '_' . \Str::slug($data['name']) . '.' . $avatar->getClientOriginalExtension();
                $avatar->storeAs('public/customers', $avatarName);
                $data['avatar'] = 'customers/' . $avatarName;
            }

            $customer->update($data);

            return response()->json([
                'success' => true,
                'message' => __('customer.updated_successfully'),
                'data' => $customer
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('customer.update_failed') . ': ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified customer
     */
    public function destroy(Customer $customer)
    {
        try {
            // Check if customer has orders
            if ($customer->orders()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => __('customer.has_orders')
                ], 422);
            }

            // Delete avatar
            if ($customer->avatar) {
                \Storage::delete('public/' . $customer->avatar);
            }

            $customer->delete();

            return response()->json([
                'success' => true,
                'message' => __('customer.deleted_successfully')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('customer.delete_failed') . ': ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get customer statistics
     */
    public function getStatistics()
    {
        try {
            // Calculate total revenue from all completed orders
            $totalRevenue = Order::where('status', 'completed')->sum('final_amount');

            $stats = [
                'total_customers' => Customer::count(),
                'active_customers' => Customer::where('status', 'active')->count(),
                'inactive_customers' => Customer::where('status', 'inactive')->count(),
                'individual_customers' => Customer::where('customer_type', 'individual')->count(),
                'business_customers' => Customer::where('customer_type', 'business')->count(),
                'new_customers_this_month' => Customer::whereMonth('created_at', Carbon::now()->month)->count(),
                'customers_with_orders' => Customer::has('orders')->count(),
                'total_revenue' => number_format($totalRevenue, 0, ',', '.'),
                'top_customers' => Customer::withSum('orders', 'final_amount')
                    ->orderBy('orders_sum_final_amount', 'desc')
                    ->limit(10)
                    ->get(['id', 'name', 'email'])
                    ->map(function($customer) {
                        return [
                            'id' => $customer->id,
                            'name' => $customer->name,
                            'email' => $customer->email,
                            'total_spent' => $customer->orders_sum_final_amount ?? 0,
                        ];
                    }),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải thống kê: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get active customers for dropdown
     */
    public function getActiveCustomers()
    {
        try {
            $customers = Customer::where('status', 'active')
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'phone'])
                ->map(function($customer) {
                    return [
                        'id' => $customer->id,
                        'text' => $customer->name . ' (' . $customer->email . ')',
                        'name' => $customer->name,
                        'email' => $customer->email,
                        'phone' => $customer->phone,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $customers
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải danh sách khách hàng: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get customer statistics and detailed information.
     */
    public function statistics(Customer $customer)
    {
        try {
            Log::info('CustomerController::statistics - Start', [
                'customer_id' => $customer->id,
                'customer_name' => $customer->name
            ]);

            // Get customer stats
            Log::info('CustomerController::statistics - Getting stats');
            $stats = $customer->getStats();
            Log::info('CustomerController::statistics - Stats retrieved', ['stats' => $stats]);

            // Get order history
            Log::info('CustomerController::statistics - Getting order history');
            $orderHistory = $customer->getOrderHistory();
            Log::info('CustomerController::statistics - Order history retrieved', [
                'order_count' => count($orderHistory)
            ]);

            // Get debt details (unpaid and partial orders)
            Log::info('CustomerController::statistics - Getting debt details');
            $debtDetails = $customer->orders()
                ->whereIn('payment_status', ['unpaid', 'partial'])
                ->with(['seller', 'creator'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function($order) {
                    // Ưu tiên seller, nếu không có thì dùng creator
                    $sellerName = 'N/A';
                    if ($order->seller) {
                        $sellerName = $order->seller->full_name;
                    } elseif ($order->creator) {
                        $sellerName = $order->creator->full_name;
                    }

                    return [
                        'order_code' => $order->order_code ?? 'N/A',
                        'date' => $order->created_at ? $order->created_at->format('d/m/Y H:i') : 'N/A',
                        'seller' => $sellerName,
                        'total' => $order->final_amount ?? 0,
                        'paid' => $order->amount_paid ?? 0,
                    ];
                });
            Log::info('CustomerController::statistics - Debt details retrieved', [
                'debt_count' => $debtDetails->count()
            ]);

            // Get points history (placeholder - you may need to create a points_transactions table)
            $pointsHistory = [
                [
                    'date' => '01/07/2025 10:30',
                    'type' => 'Tích điểm mua hàng',
                    'points' => 50,
                    'note' => 'Đơn hàng HD008076',
                    'balance' => $customer->points ?? 0
                ],
                [
                    'date' => '25/06/2025 14:20',
                    'type' => 'Sử dụng điểm',
                    'points' => -20,
                    'note' => 'Đổi quà tặng',
                    'balance' => ($customer->points ?? 0) - 50
                ]
            ];

            $responseData = [
                'total_debt' => $stats['total_debt'] ?? 0,
                'total_points' => $stats['total_points'] ?? 0,
                'total_spent' => $stats['total_spent'] ?? 0,
                'purchase_count' => $stats['purchase_count'] ?? 0,
                'net_sales' => $stats['net_sales'] ?? 0,
                'order_history' => $orderHistory,
                'debt_details' => $debtDetails,
                'points_history' => $pointsHistory,
            ];

            Log::info('CustomerController::statistics - Success', [
                'response_data_keys' => array_keys($responseData)
            ]);

            return response()->json([
                'success' => true,
                'data' => $responseData
            ]);

        } catch (\Exception $e) {
            Log::error('CustomerController::statistics - Exception caught', [
                'customer_id' => $customer->id ?? 'unknown',
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'stack_trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Không thể tải thông tin khách hàng: ' . $e->getMessage(),
                'error_details' => config('app.debug') ? [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ] : null
            ], 500);
        }
    }
}
