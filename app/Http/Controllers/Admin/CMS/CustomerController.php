<?php

namespace App\Http\Controllers\Admin\CMS;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerPointTransaction;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
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
            'customer_code' => 'nullable|string|max:50|unique:customers,customer_code',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:customers,email',
            'phone' => 'nullable|string|max:20|regex:/^[0-9+\-\s()]+$/',
            'facebook' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:1000',
            'area' => 'nullable|string|max:255',
            'customer_type' => 'required|in:individual,business',
            'customer_group' => 'nullable|string|max:100',
            'tax_code' => 'nullable|string|max:50',
            'status' => 'required|in:active,inactive',
            'birthday' => 'nullable|date|before:today',
            'notes' => 'nullable|string|max:2000',
            'points' => 'nullable|integer|min:0',
        ], [
            'customer_code.unique' => __('customer.customer_code_unique'),
            'name.required' => __('customer.name_required'),
            'email.required' => __('customer.email_required'),
            'email.email' => __('customer.email_invalid'),
            'email.unique' => __('customer.email_unique'),
            'phone.regex' => __('customer.phone_invalid'),
            'customer_type.required' => __('customer.type_required'),
            'status.required' => __('customer.status_required'),
            'birthday.before' => __('customer.birthday_invalid'),
            'points.min' => __('customer.points_invalid'),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => __('customer.validation_failed'),
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $request->validated();

            // Auto-generate customer code if not provided
            if (empty($data['customer_code'])) {
                $data['customer_code'] = Customer::generateCustomerCode();
            }

            // Set created_by
            $data['created_by'] = auth()->id();

            // Handle avatar upload
            if ($request->hasFile('avatar')) {
                $avatar = $request->file('avatar');
                $avatarName = time() . '_' . Str::slug($data['name']) . '.' . $avatar->getClientOriginalExtension();
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
            'customer_code' => 'nullable|string|max:50|unique:customers,customer_code,' . $customer->id,
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:customers,email,' . $customer->id,
            'phone' => 'required|string|max:20|regex:/^[0-9+\-\s()]+$/',
            'facebook' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:1000',
            'area' => 'nullable|string|max:255',
            'customer_type' => 'nullable|in:individual,business',
            'customer_group' => 'nullable|string|max:100',
            'tax_code' => 'nullable|string|max:50',
            'birthday' => 'nullable|date|before:today',
            'notes' => 'nullable|string|max:2000',
        ], [
            'customer_code.unique' => __('customer.customer_code_unique'),
            'name.required' => __('customer.name_required'),
            'email.email' => __('customer.email_invalid'),
            'email.unique' => __('customer.email_unique'),
            'phone.required' => __('customer.phone_required'),
            'phone.regex' => __('customer.phone_invalid'),
            'customer_type.in' => __('customer.type_invalid'),
            'birthday.before' => __('customer.birthday_invalid'),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => __('customer.validation_failed'),
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $validator->validated();

            // Set updated_by
            $data['updated_by'] = auth()->id();

            // Handle avatar upload
            if ($request->hasFile('avatar')) {
                // Delete old avatar
                if ($customer->avatar) {
                    Storage::delete('public/' . $customer->avatar);
                }

                $avatar = $request->file('avatar');
                $avatarName = time() . '_' . Str::slug($data['name']) . '.' . $avatar->getClientOriginalExtension();
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
                Storage::delete('public/' . $customer->avatar);
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
            // Calculate total revenue from all customer orders (completed status)
            $totalRevenue = \App\Models\Order::whereNotNull('customer_id')
                ->where('customer_id', '>', 0)
                ->where('status', 'completed')
                ->sum('final_amount');

            $stats = [
                'total_customers' => Customer::count(),
                'active_customers' => Customer::where('status', 'active')->count(),
                'inactive_customers' => Customer::where('status', 'inactive')->count(),
                'individual_customers' => Customer::where('customer_type', 'individual')->count(),
                'business_customers' => Customer::where('customer_type', 'business')->count(),
                'new_customers_this_month' => Customer::whereMonth('created_at', Carbon::now()->month)
                    ->whereYear('created_at', Carbon::now()->year)
                    ->count(),
                'new_customers' => Customer::whereMonth('created_at', Carbon::now()->month)
                    ->whereYear('created_at', Carbon::now()->year)
                    ->count(), // Alias for JavaScript compatibility
                'customers_with_orders' => Customer::has('orders')->count(),
                'total_revenue' => number_format($totalRevenue, 0, ',', '.'), // Format for display
                'top_customers' => Customer::withSum('orders', 'final_amount')
                    ->orderBy('orders_sum_final_amount', 'desc')
                    ->limit(10)
                    ->get(['id', 'name', 'email'])
                    ->map(function($customer) {
                        return [
                            'id' => $customer->id,
                            'name' => $customer->name,
                            'email' => $customer->email,
                            'total_spent' => number_format($customer->orders_sum_final_amount ?? 0, 0, ',', '.'),
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

    /**
     * Get customer info with branch shops for Quick Order modal
     */
    public function getCustomerInfo(Customer $customer)
    {
        try {
            // Load customer with branch shop relationship
            $customer->load('branchShop');

            // Get customer statistics
            $stats = $customer->getStats();

            // Get branch shop where customer was created
            $displayBranchShop = $customer->branchShop;

            return response()->json([
                'success' => true,
                'data' => [
                    'customer' => [
                        'id' => $customer->id,
                        'customer_code' => $customer->customer_code,
                        'name' => $customer->name,
                        'phone' => $customer->phone,
                        'email' => $customer->email,
                        'address' => $customer->address,
                        'customer_type' => $customer->customer_type,
                        'customer_group' => $customer->customer_group,
                        'tax_code' => $customer->tax_code,
                        'facebook' => $customer->facebook,
                        'area' => $customer->area,
                        'birthday' => $customer->birthday,
                        'points' => $customer->points,
                        'notes' => $customer->notes,
                        'status' => $customer->status,
                        'created_at' => $customer->created_at,
                    ],
                    'branch_shop' => $displayBranchShop ? [
                        'id' => $displayBranchShop->id,
                        'name' => $displayBranchShop->name,
                        'address' => $displayBranchShop->address,
                        'created_at' => $customer->created_at,
                        'is_primary' => true, // Always primary since it's where customer was created
                    ] : null,
                    'statistics' => $stats,
                    'order_history' => $customer->getOrderHistory(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting customer info: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tải thông tin khách hàng',
                'error' => config('app.debug') ? [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ] : null
            ], 500);
        }
    }

    /**
     * Get customer order history (invoices and return orders)
     */
    public function orderHistory(Customer $customer, Request $request)
    {
        try {
            $perPage = 7; // As requested
            $page = $request->get('page', 1);

            // Get invoices with seller information
            $invoices = $customer->invoices()
                ->with(['seller', 'creator']) // Load seller relationships
                ->select('id', 'invoice_number', 'total_amount', 'status', 'created_at', 'sold_by', 'created_by')
                ->get()
                ->map(function ($invoice) {
                    // Determine seller name - prioritize sold_by, fallback to created_by
                    $sellerName = 'N/A';
                    if ($invoice->seller) {
                        $sellerName = $invoice->seller->name;
                    } elseif ($invoice->creator) {
                        $sellerName = $invoice->creator->name;
                    }

                    return [
                        'id' => $invoice->id,
                        'type' => 'invoice',
                        'code' => $invoice->invoice_number,
                        'amount' => $invoice->total_amount,
                        'status' => $invoice->status,
                        'date' => $invoice->created_at,
                        'seller' => $sellerName,
                        'formatted_amount' => number_format($invoice->total_amount, 0, ',', '.'),
                        'formatted_date' => $invoice->created_at->format('d/m/Y H:i'),
                        'status_text' => $this->getInvoiceStatusText($invoice->status)
                    ];
                });

            // Get return orders (if table exists)
            $returnOrders = collect();
            if (Schema::hasTable('return_orders')) {
                $returnOrders = $customer->returnOrders()
                    ->with(['seller', 'creator']) // Load seller relationships
                    ->select('id', 'return_number', 'total_amount', 'status', 'created_at', 'sold_by', 'created_by')
                    ->get()
                    ->map(function ($returnOrder) {
                        // Determine seller name - prioritize sold_by, fallback to created_by
                        $sellerName = 'N/A';
                        if ($returnOrder->seller) {
                            $sellerName = $returnOrder->seller->name;
                        } elseif ($returnOrder->creator) {
                            $sellerName = $returnOrder->creator->name;
                        }

                        return [
                            'id' => $returnOrder->id,
                            'type' => 'return_order',
                            'code' => $returnOrder->return_number,
                            'amount' => $returnOrder->total_amount,
                            'status' => $returnOrder->status,
                            'date' => $returnOrder->created_at,
                            'seller' => $sellerName,
                            'formatted_amount' => number_format($returnOrder->total_amount, 0, ',', '.'),
                            'formatted_date' => $returnOrder->created_at->format('d/m/Y H:i'),
                            'status_text' => $this->getReturnOrderStatusText($returnOrder->status)
                        ];
                    });
            }

            // Combine and sort by date
            $allOrders = $invoices->concat($returnOrders)
                ->sortByDesc('date')
                ->values();

            // Manual pagination
            $total = $allOrders->count();
            $offset = ($page - 1) * $perPage;
            $items = $allOrders->slice($offset, $perPage)->values();

            $pagination = [
                'current_page' => (int) $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => ceil($total / $perPage),
                'from' => $offset + 1,
                'to' => min($offset + $perPage, $total),
                'has_more_pages' => $page < ceil($total / $perPage)
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'items' => $items,
                    'pagination' => $pagination
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể lấy lịch sử đơn hàng: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get customer point history
     */
    public function pointHistory(Customer $customer, Request $request)
    {
        try {
            $perPage = 7; // As requested
            $page = $request->get('page', 1);

            // Get point transactions
            $pointTransactions = CustomerPointTransaction::where('customer_id', $customer->id)
                ->orderBy('transaction_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($transaction) {
                    // Determine transaction code based on type and reference
                    $transactionCode = $this->getTransactionCode($transaction);

                    // Determine transaction type text for display
                    $transactionType = $this->getPointTransactionTypeText($transaction->type);

                    // Determine transaction value (invoice amount or point value)
                    $transactionValue = $this->getTransactionValue($transaction);

                    return [
                        'id' => $transaction->id,
                        'code' => $transactionCode, // Mã phiếu (HD039940, TTHD036669)
                        'date' => $transaction->transaction_date,
                        'type' => $transaction->type,
                        'type_text' => $transactionType, // Loại (Bán hàng, Thanh toán bằng điểm)
                        'value' => $transactionValue, // Giá trị (353,000)
                        'points' => $transaction->points, // Điểm GD (4180, -5000)
                        'balance_after' => $transaction->balance_after, // Điểm sau GD (57,270)
                        'notes' => $transaction->notes,
                        'amount' => $transaction->amount,
                        'reference_type' => $transaction->reference_type,
                        'reference_id' => $transaction->reference_id,
                        'formatted_date' => $transaction->transaction_date->format('d/m/Y H:i'),
                        'formatted_value' => number_format($transactionValue, 0, ',', '.'),
                        'formatted_points' => $transaction->points > 0 ? '+' . number_format($transaction->points) : number_format($transaction->points),
                        'formatted_balance' => number_format($transaction->balance_after, 0, ',', '.'),
                        'points_class' => $transaction->points > 0 ? 'text-success fw-bold' : 'text-danger fw-bold'
                    ];
                });

            // Manual pagination
            $total = $pointTransactions->count();
            $offset = ($page - 1) * $perPage;
            $items = $pointTransactions->slice($offset, $perPage)->values();

            $pagination = [
                'current_page' => (int) $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => ceil($total / $perPage),
                'from' => $offset + 1,
                'to' => min($offset + $perPage, $total),
                'has_more_pages' => $page < ceil($total / $perPage)
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'items' => $items,
                    'pagination' => $pagination
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể lấy lịch sử điểm: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get invoice status text
     */
    private function getInvoiceStatusText($status)
    {
        $statusMap = [
            'draft' => 'Nháp',
            'pending' => 'Chờ xử lý',
            'processing' => 'Đang xử lý',
            'paid' => 'Đã thanh toán',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
            'refunded' => 'Đã hoàn tiền'
        ];

        return $statusMap[$status] ?? ucfirst($status);
    }

    /**
     * Get return order status text
     */
    private function getReturnOrderStatusText($status)
    {
        $statusMap = [
            'pending' => 'Chờ xử lý',
            'processing' => 'Đang xử lý',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy'
        ];

        return $statusMap[$status] ?? ucfirst($status);
    }

    /**
     * Get point transaction type text
     */
    private function getPointTransactionTypeText($type)
    {
        $typeMap = [
            'purchase' => 'Bán hàng',
            'return' => 'Hoàn điểm trả hàng',
            'adjustment' => 'Điều chỉnh điểm',
            'redeem' => 'Thanh toán bằng điểm',
            'bonus' => 'Điểm thưởng'
        ];

        return $typeMap[$type] ?? ucfirst($type);
    }

    /**
     * Get transaction code based on type and reference
     */
    private function getTransactionCode($transaction)
    {
        if ($transaction->reference_type === 'invoice' && $transaction->reference_id) {
            // Try to get invoice number
            $invoice = \App\Models\Invoice::find($transaction->reference_id);
            if ($invoice && $invoice->invoice_number) {
                return $invoice->invoice_number;
            }
            return 'HD' . str_pad($transaction->reference_id, 6, '0', STR_PAD_LEFT);
        }

        if ($transaction->type === 'redeem') {
            return 'TTHD' . str_pad($transaction->id, 6, '0', STR_PAD_LEFT);
        }

        // Default format
        return 'PT' . str_pad($transaction->id, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Get transaction value (invoice amount or point value)
     */
    private function getTransactionValue($transaction)
    {
        // For purchase transactions, return the invoice amount
        if ($transaction->reference_type === 'invoice' && $transaction->reference_id) {
            $invoice = \App\Models\Invoice::find($transaction->reference_id);
            if ($invoice) {
                return $invoice->total_amount;
            }
        }

        // For redeem transactions, return the point value as amount
        if ($transaction->type === 'redeem') {
            return abs($transaction->points) * 1000; // Assuming 1 point = 1000 VND
        }

        // For other transactions, return the amount if available
        return $transaction->amount ?? abs($transaction->points) * 1000;
    }
}
