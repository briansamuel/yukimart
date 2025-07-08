@extends('admin.index')
@section('page-header', 'Order Inventory Transaction Demo')
@section('page-sub_header', 'Automatic inventory transaction creation when orders are created')

@section('content')
<div class="row g-6 g-xl-9">
    <!--begin::Col-->
    <div class="col-md-6 col-xl-4">
        <!--begin::Card-->
        <div class="card">
            <!--begin::Card header-->
            <div class="card-header border-0 pt-6">
                <!--begin::Card title-->
                <div class="card-title">
                    <h3 class="fw-bold m-0">Recent Sale Transactions</h3>
                </div>
                <!--end::Card title-->
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-0">
                @php
                    $saleTransactions = \App\Models\InventoryTransaction::where('transaction_type', 'sale')
                        ->with(['product'])
                        ->orderBy('created_at', 'desc')
                        ->limit(10)
                        ->get();
                @endphp
                
                @forelse($saleTransactions as $transaction)
                    <!--begin::Item-->
                    <div class="d-flex align-items-center mb-6">
                        <!--begin::Symbol-->
                        <div class="symbol symbol-45px me-5">
                            <div class="symbol-label bg-light-danger text-danger">
                                <i class="ki-duotone ki-arrow-down fs-1">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </div>
                        </div>
                        <!--end::Symbol-->
                        <!--begin::Description-->
                        <div class="d-flex align-items-center flex-wrap w-100">
                            <!--begin::Title-->
                            <div class="mb-1 pe-3 flex-grow-1">
                                <span class="fs-6 text-gray-800 text-hover-primary fw-bold">
                                    {{ $transaction->product->product_name ?? 'Unknown Product' }}
                                </span>
                                <div class="text-gray-400 fw-semibold fs-7">
                                    {{ $transaction->notes }}
                                </div>
                            </div>
                            <!--end::Title-->
                            <!--begin::Label-->
                            <div class="d-flex flex-column align-items-end">
                                <span class="text-danger fw-bold fs-6">{{ $transaction->quantity }}</span>
                                <div class="text-gray-400 fw-semibold fs-7">
                                    {{ $transaction->created_at->format('d/m H:i') }}
                                </div>
                            </div>
                            <!--end::Label-->
                        </div>
                        <!--end::Description-->
                    </div>
                    <!--end::Item-->
                @empty
                    <div class="text-center py-10">
                        <div class="text-muted">No sale transactions found</div>
                    </div>
                @endforelse
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Card-->
    </div>
    <!--end::Col-->
    
    <!--begin::Col-->
    <div class="col-md-6 col-xl-4">
        <!--begin::Card-->
        <div class="card">
            <!--begin::Card header-->
            <div class="card-header border-0 pt-6">
                <!--begin::Card title-->
                <div class="card-title">
                    <h3 class="fw-bold m-0">Transaction Types</h3>
                </div>
                <!--end::Card title-->
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-0">
                @php
                    $transactionTypes = \App\Models\InventoryTransaction::getTransactionTypes();
                    $typeCounts = [];
                    foreach ($transactionTypes as $type => $label) {
                        $typeCounts[$type] = \App\Models\InventoryTransaction::where('transaction_type', $type)->count();
                    }
                @endphp
                
                @foreach($transactionTypes as $type => $label)
                    @php
                        $count = $typeCounts[$type];
                        $percentage = array_sum($typeCounts) > 0 ? round(($count / array_sum($typeCounts)) * 100, 1) : 0;
                        
                        $typeConfig = [
                            'import' => ['class' => 'success', 'icon' => 'arrow-up'],
                            'export' => ['class' => 'warning', 'icon' => 'arrow-down'],
                            'sale' => ['class' => 'danger', 'icon' => 'basket'],
                            'transfer' => ['class' => 'info', 'icon' => 'arrows-loop'],
                            'adjustment' => ['class' => 'primary', 'icon' => 'setting-2'],
                            'initial' => ['class' => 'secondary', 'icon' => 'flag']
                        ];
                        
                        $config = $typeConfig[$type] ?? ['class' => 'secondary', 'icon' => 'question'];
                    @endphp
                    
                    <!--begin::Item-->
                    <div class="d-flex align-items-center mb-6">
                        <!--begin::Symbol-->
                        <div class="symbol symbol-45px me-5">
                            <div class="symbol-label bg-light-{{ $config['class'] }} text-{{ $config['class'] }}">
                                <i class="ki-duotone ki-{{ $config['icon'] }} fs-1">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </div>
                        </div>
                        <!--end::Symbol-->
                        <!--begin::Description-->
                        <div class="d-flex align-items-center flex-wrap w-100">
                            <!--begin::Title-->
                            <div class="mb-1 pe-3 flex-grow-1">
                                <span class="fs-6 text-gray-800 fw-bold">{{ $label }}</span>
                                <div class="text-gray-400 fw-semibold fs-7">
                                    Type: {{ $type }}
                                </div>
                            </div>
                            <!--end::Title-->
                            <!--begin::Label-->
                            <div class="d-flex flex-column align-items-end">
                                <span class="text-{{ $config['class'] }} fw-bold fs-6">{{ $count }}</span>
                                <div class="text-gray-400 fw-semibold fs-7">
                                    {{ $percentage }}%
                                </div>
                            </div>
                            <!--end::Label-->
                        </div>
                        <!--end::Description-->
                    </div>
                    <!--end::Item-->
                @endforeach
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Card-->
    </div>
    <!--end::Col-->
    
    <!--begin::Col-->
    <div class="col-md-6 col-xl-4">
        <!--begin::Card-->
        <div class="card">
            <!--begin::Card header-->
            <div class="card-header border-0 pt-6">
                <!--begin::Card title-->
                <div class="card-title">
                    <h3 class="fw-bold m-0">Implementation Guide</h3>
                </div>
                <!--end::Card title-->
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-0">
                <!--begin::Step 1-->
                <div class="mb-8">
                    <h5 class="text-gray-800 fw-bold mb-3">1. Automatic Creation</h5>
                    <div class="bg-light-primary rounded p-4">
                        <div class="text-gray-700 fs-7">
                            When an order is created successfully, inventory transactions with 
                            <code>transaction_type = 'sale'</code> are automatically created for each order item.
                        </div>
                    </div>
                </div>
                <!--end::Step 1-->
                
                <!--begin::Step 2-->
                <div class="mb-8">
                    <h5 class="text-gray-800 fw-bold mb-3">2. Stock Updates</h5>
                    <div class="bg-light-success rounded p-4">
                        <div class="text-gray-700 fs-7">
                            Product inventory quantities are automatically decreased by the sold amount, 
                            maintaining accurate stock levels in real-time.
                        </div>
                    </div>
                </div>
                <!--end::Step 2-->
                
                <!--begin::Step 3-->
                <div class="mb-8">
                    <h5 class="text-gray-800 fw-bold mb-3">3. Audit Trail</h5>
                    <div class="bg-light-info rounded p-4">
                        <div class="text-gray-700 fs-7">
                            Complete transaction history is maintained with references to the original order, 
                            providing full traceability for all inventory movements.
                        </div>
                    </div>
                </div>
                <!--end::Step 3-->
                
                <!--begin::Test command-->
                <div class="alert alert-primary d-flex align-items-center p-5">
                    <i class="ki-duotone ki-shield-tick fs-2hx text-primary me-4">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    <div class="d-flex flex-column">
                        <h4 class="mb-1 text-primary">Test Command</h4>
                        <span>Run: <code>php artisan test:order-inventory-transaction</code></span>
                    </div>
                </div>
                <!--end::Test command-->
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Card-->
    </div>
    <!--end::Col-->
</div>

<!--begin::Row-->
<div class="row g-6 g-xl-9 mt-6">
    <!--begin::Col-->
    <div class="col-12">
        <!--begin::Card-->
        <div class="card">
            <!--begin::Card header-->
            <div class="card-header border-0 pt-6">
                <!--begin::Card title-->
                <div class="card-title">
                    <h3 class="fw-bold m-0">Recent Orders with Inventory Transactions</h3>
                </div>
                <!--end::Card title-->
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-0">
                @php
                    $recentOrders = \App\Models\Order::with(['customer', 'orderItems.product'])
                        ->orderBy('created_at', 'desc')
                        ->limit(5)
                        ->get();
                @endphp
                
                <!--begin::Table-->
                <div class="table-responsive">
                    <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                        <!--begin::Table head-->
                        <thead>
                            <tr class="fw-bold text-muted">
                                <th class="min-w-150px">Order Code</th>
                                <th class="min-w-140px">Customer</th>
                                <th class="min-w-120px">Items</th>
                                <th class="min-w-120px">Total Amount</th>
                                <th class="min-w-120px">Transactions</th>
                                <th class="min-w-100px text-end">Actions</th>
                            </tr>
                        </thead>
                        <!--end::Table head-->
                        <!--begin::Table body-->
                        <tbody>
                            @forelse($recentOrders as $order)
                                @php
                                    $transactions = \App\Models\InventoryTransaction::where('reference_type', 'App\\Models\\Order')
                                        ->where('reference_id', $order->id)
                                        ->where('transaction_type', 'sale')
                                        ->count();
                                @endphp
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="symbol symbol-45px me-5">
                                                <div class="symbol-label bg-light-primary text-primary fw-bold">
                                                    {{ strtoupper(substr($order->order_code, -2)) }}
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-start flex-column">
                                                <a href="{{ route('admin.order.show', $order->id) }}" class="text-dark fw-bold text-hover-primary fs-6">
                                                    {{ $order->order_code }}
                                                </a>
                                                <span class="text-muted fw-semibold text-muted d-block fs-7">
                                                    {{ $order->created_at->format('d/m/Y H:i') }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="text-dark fw-bold fs-6">{{ $order->customer->name ?? 'N/A' }}</span>
                                            <span class="text-muted fw-semibold fs-7">{{ $order->customer->phone ?? 'N/A' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-dark fw-bold fs-6">{{ $order->orderItems->count() }}</span>
                                        <span class="text-muted fw-semibold fs-7">items</span>
                                    </td>
                                    <td>
                                        <span class="text-dark fw-bold fs-6">{{ number_format($order->final_amount, 0, ',', '.') }}₫</span>
                                    </td>
                                    <td>
                                        @if($transactions > 0)
                                            <span class="badge badge-light-success">
                                                <i class="ki-duotone ki-check fs-7 me-1"></i>
                                                {{ $transactions }} transactions
                                            </span>
                                        @else
                                            <span class="badge badge-light-warning">
                                                <i class="ki-duotone ki-warning-2 fs-7 me-1"></i>
                                                No transactions
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.order.show', $order->id) }}" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm">
                                            <i class="ki-duotone ki-eye fs-3">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                                <span class="path3"></span>
                                            </i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-10">
                                        <div class="text-muted">No orders found</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <!--end::Table body-->
                    </table>
                </div>
                <!--end::Table-->
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Card-->
    </div>
    <!--end::Col-->
</div>
<!--end::Row-->

<!--begin::Row-->
<div class="row g-6 g-xl-9 mt-6">
    <!--begin::Col-->
    <div class="col-12">
        <!--begin::Card-->
        <div class="card">
            <!--begin::Card header-->
            <div class="card-header border-0 pt-6">
                <!--begin::Card title-->
                <div class="card-title">
                    <h3 class="fw-bold m-0">Code Examples</h3>
                </div>
                <!--end::Card title-->
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-0">
                <div class="row g-6">
                    <!--begin::Col-->
                    <div class="col-md-6">
                        <!--begin::Code example 1-->
                        <div class="mb-8">
                            <h5 class="text-gray-800 fw-bold mb-3">Creating Order with Auto Transactions</h5>
                            <div class="bg-light-primary rounded p-4">
                                <code class="text-gray-700">
                                    $orderService = app(OrderService::class);<br>
                                    $result = $orderService->createOrder([<br>
                                    &nbsp;&nbsp;&nbsp;&nbsp;'customer_id' => 1,<br>
                                    &nbsp;&nbsp;&nbsp;&nbsp;'items' => json_encode([<br>
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;['product_id' => 1, 'quantity' => 5]<br>
                                    &nbsp;&nbsp;&nbsp;&nbsp;])<br>
                                    ]);<br><br>
                                    // Inventory transactions created automatically!
                                </code>
                            </div>
                        </div>
                        <!--end::Code example 1-->
                        
                        <!--begin::Code example 2-->
                        <div class="mb-8">
                            <h5 class="text-gray-800 fw-bold mb-3">Querying Sale Transactions</h5>
                            <div class="bg-light-success rounded p-4">
                                <code class="text-gray-700">
                                    $saleTransactions = InventoryTransaction::<br>
                                    &nbsp;&nbsp;&nbsp;&nbsp;where('transaction_type', 'sale')<br>
                                    &nbsp;&nbsp;&nbsp;&nbsp;->with(['product', 'creator'])<br>
                                    &nbsp;&nbsp;&nbsp;&nbsp;->get();<br><br>
                                    foreach ($saleTransactions as $transaction) {<br>
                                    &nbsp;&nbsp;&nbsp;&nbsp;echo $transaction->notes;<br>
                                    }
                                </code>
                            </div>
                        </div>
                        <!--end::Code example 2-->
                    </div>
                    <!--end::Col-->
                    
                    <!--begin::Col-->
                    <div class="col-md-6">
                        <!--begin::Code example 3-->
                        <div class="mb-8">
                            <h5 class="text-gray-800 fw-bold mb-3">Transaction Data Structure</h5>
                            <div class="bg-light-info rounded p-4">
                                <code class="text-gray-700">
                                    [<br>
                                    &nbsp;&nbsp;&nbsp;&nbsp;'product_id' => 123,<br>
                                    &nbsp;&nbsp;&nbsp;&nbsp;'transaction_type' => 'sale',<br>
                                    &nbsp;&nbsp;&nbsp;&nbsp;'quantity' => -5, // Negative for sale<br>
                                    &nbsp;&nbsp;&nbsp;&nbsp;'old_quantity' => 100,<br>
                                    &nbsp;&nbsp;&nbsp;&nbsp;'new_quantity' => 95,<br>
                                    &nbsp;&nbsp;&nbsp;&nbsp;'reference_type' => 'App\\Models\\Order',<br>
                                    &nbsp;&nbsp;&nbsp;&nbsp;'reference_id' => 456,<br>
                                    &nbsp;&nbsp;&nbsp;&nbsp;'notes' => 'Bán hàng - Đơn hàng ORD-001'<br>
                                    ]
                                </code>
                            </div>
                        </div>
                        <!--end::Code example 3-->
                        
                        <!--begin::Code example 4-->
                        <div class="mb-8">
                            <h5 class="text-gray-800 fw-bold mb-3">Stock Reconciliation</h5>
                            <div class="bg-light-warning rounded p-4">
                                <code class="text-gray-700">
                                    $product = Product::with('inventory')->find(1);<br>
                                    $currentStock = $product->inventory->quantity;<br><br>
                                    $calculatedStock = InventoryTransaction::<br>
                                    &nbsp;&nbsp;&nbsp;&nbsp;where('product_id', 1)<br>
                                    &nbsp;&nbsp;&nbsp;&nbsp;->sum('quantity');<br><br>
                                    $isConsistent = ($currentStock === $calculatedStock);
                                </code>
                            </div>
                        </div>
                        <!--end::Code example 4-->
                    </div>
                    <!--end::Col-->
                </div>
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Card-->
    </div>
    <!--end::Col-->
</div>
<!--end::Row-->
@endsection
