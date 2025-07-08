@extends('admin.index')
@section('page-header', 'Order Notifications Demo')
@section('page-sub_header', 'Dashboard recent activities with order creation notifications')

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
                    <h3 class="fw-bold m-0">Order Creation Notifications</h3>
                </div>
                <!--end::Card title-->
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-0">
                @php
                    $orderNotifications = \App\Models\Notification::where('type', 'order_create')
                        ->with(['creator'])
                        ->orderBy('created_at', 'desc')
                        ->limit(10)
                        ->get();
                @endphp
                
                @forelse($orderNotifications as $notification)
                    <!--begin::Item-->
                    <div class="d-flex align-items-center mb-6">
                        <!--begin::Symbol-->
                        <div class="symbol symbol-45px me-5">
                            <div class="symbol-label bg-light-primary text-primary">
                                <i class="ki-duotone ki-basket fs-1">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                    <span class="path4"></span>
                                </i>
                            </div>
                        </div>
                        <!--end::Symbol-->
                        <!--begin::Description-->
                        <div class="d-flex align-items-center flex-wrap w-100">
                            <!--begin::Title-->
                            <div class="mb-1 pe-3 flex-grow-1">
                                <span class="fs-6 text-gray-800 text-hover-primary fw-bold">
                                    {{ $notification->data['order_code'] ?? 'N/A' }}
                                </span>
                                <div class="text-gray-400 fw-semibold fs-7">
                                    {{ $notification->data['customer_name'] ?? 'Unknown Customer' }}
                                </div>
                                <div class="text-gray-500 fw-semibold fs-8">
                                    {{ $notification->creator->name ?? 'System' }} • {{ $notification->time_ago }}
                                </div>
                            </div>
                            <!--end::Title-->
                            <!--begin::Label-->
                            <div class="d-flex flex-column align-items-end">
                                <span class="text-primary fw-bold fs-6">
                                    {{ number_format($notification->data['total_amount'] ?? 0, 0, ',', '.') }}₫
                                </span>
                                @if(!$notification->is_read)
                                    <div class="w-8px h-8px rounded-circle bg-primary mt-1"></div>
                                @endif
                            </div>
                            <!--end::Label-->
                        </div>
                        <!--end::Description-->
                    </div>
                    <!--end::Item-->
                @empty
                    <div class="text-center py-10">
                        <div class="text-muted">No order notifications found</div>
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
                    <h3 class="fw-bold m-0">Notification Statistics</h3>
                </div>
                <!--end::Card title-->
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-0">
                @php
                    $notificationStats = \App\Models\Notification::selectRaw('
                        type,
                        count(*) as total,
                        sum(case when read_at is null then 1 else 0 end) as unread,
                        sum(case when read_at is not null then 1 else 0 end) as read_count
                    ')
                    ->groupBy('type')
                    ->get()
                    ->keyBy('type');
                    
                    $totalNotifications = \App\Models\Notification::count();
                    $unreadNotifications = \App\Models\Notification::whereNull('read_at')->count();
                @endphp
                
                <!--begin::Stats-->
                <div class="mb-8">
                    <div class="d-flex align-items-center mb-4">
                        <div class="symbol symbol-40px me-3">
                            <div class="symbol-label bg-light-success text-success">
                                <i class="ki-duotone ki-notification-bing fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                </i>
                            </div>
                        </div>
                        <div class="d-flex flex-column">
                            <span class="fw-bold fs-2 text-gray-900">{{ $totalNotifications }}</span>
                            <span class="text-gray-500 fs-7">Total Notifications</span>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center mb-4">
                        <div class="symbol symbol-40px me-3">
                            <div class="symbol-label bg-light-warning text-warning">
                                <i class="ki-duotone ki-notification-status fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                    <span class="path4"></span>
                                </i>
                            </div>
                        </div>
                        <div class="d-flex flex-column">
                            <span class="fw-bold fs-2 text-gray-900">{{ $unreadNotifications }}</span>
                            <span class="text-gray-500 fs-7">Unread Notifications</span>
                        </div>
                    </div>
                </div>
                <!--end::Stats-->
                
                <!--begin::Types-->
                @foreach($notificationStats as $type => $stats)
                    <div class="d-flex align-items-center mb-4">
                        <div class="symbol symbol-35px me-3">
                            <div class="symbol-label bg-light-info text-info">
                                @if($type === 'order_create')
                                    <i class="ki-duotone ki-basket fs-3">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                        <span class="path4"></span>
                                    </i>
                                @else
                                    <i class="ki-duotone ki-notification-bing fs-3">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                    </i>
                                @endif
                            </div>
                        </div>
                        <div class="d-flex flex-column flex-grow-1">
                            <span class="fw-bold text-gray-800 fs-6">{{ ucfirst(str_replace('_', ' ', $type)) }}</span>
                            <div class="text-gray-500 fs-7">
                                Total: {{ $stats->total }} | Unread: {{ $stats->unread }}
                            </div>
                        </div>
                    </div>
                @endforeach
                <!--end::Types-->
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
                            When an order is created successfully, a notification with 
                            <code>type = 'order_create'</code> is automatically created.
                        </div>
                    </div>
                </div>
                <!--end::Step 1-->
                
                <!--begin::Step 2-->
                <div class="mb-8">
                    <h5 class="text-gray-800 fw-bold mb-3">2. Dashboard Integration</h5>
                    <div class="bg-light-success rounded p-4">
                        <div class="text-gray-700 fs-7">
                            The <code>getRecentActivities()</code> method now fetches order creation 
                            notifications to display in the dashboard recent activities widget.
                        </div>
                    </div>
                </div>
                <!--end::Step 2-->
                
                <!--begin::Step 3-->
                <div class="mb-8">
                    <h5 class="text-gray-800 fw-bold mb-3">3. Rich Data</h5>
                    <div class="bg-light-info rounded p-4">
                        <div class="text-gray-700 fs-7">
                            Each notification contains order details like order code, customer name, 
                            total amount, and action URL for easy navigation.
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
                        <span>Run: <code>php artisan test:order-notifications</code></span>
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
                    <h3 class="fw-bold m-0">Recent Activities Preview (Dashboard Widget)</h3>
                </div>
                <!--end::Card title-->
                <div class="card-toolbar">
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-primary">
                        <i class="ki-duotone ki-element-11 fs-3">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                            <span class="path4"></span>
                        </i>
                        View Dashboard
                    </a>
                </div>
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-0">
                @php
                    $recentActivities = \App\Services\DashboardService::getRecentActivities(10);
                @endphp
                
                @if($recentActivities->count() > 0)
                    <div class="scroll-y me-n5 pe-5" data-kt-scroll="true" data-kt-scroll-height="400px">
                        @foreach($recentActivities as $activity)
                            <!--begin::Item-->
                            <div class="d-flex align-items-center border-bottom border-gray-300 pb-6 mb-6">
                                <!--begin::Symbol-->
                                <div class="symbol symbol-45px me-5">
                                    <div class="symbol-label bg-light-primary text-primary">
                                        <i class="ki-duotone ki-{{ $activity['icon'] ?? 'basket' }} fs-1">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                    </div>
                                </div>
                                <!--end::Symbol-->
                                <!--begin::Content-->
                                <div class="d-flex flex-column flex-grow-1">
                                    <!--begin::Title-->
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="fw-bold text-gray-800 fs-6">
                                            @if(isset($activity['order_code']))
                                                {{ $activity['order_code'] }}
                                            @else
                                                {{ $activity['action'] }}
                                            @endif
                                        </span>
                                        @if(isset($activity['priority_badge']))
                                            {!! $activity['priority_badge'] !!}
                                        @endif
                                    </div>
                                    <!--end::Title-->
                                    <!--begin::Description-->
                                    <div class="text-gray-600 fs-7 mb-2">
                                        @if(isset($activity['customer_name']))
                                            <i class="ki-duotone ki-user fs-7 me-1">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                            {{ $activity['customer_name'] }}
                                        @else
                                            {{ $activity['description'] }}
                                        @endif
                                    </div>
                                    <!--end::Description-->
                                    <!--begin::Info-->
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <i class="ki-duotone ki-profile-user fs-7 text-gray-400 me-1">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                                <span class="path3"></span>
                                                <span class="path4"></span>
                                            </i>
                                            <span class="text-gray-400 fs-7">{{ $activity['user_name'] }}</span>
                                            @if(isset($activity['seller_name']) && $activity['seller_name'] !== $activity['user_name'])
                                                <span class="text-gray-300 fs-8 mx-1">•</span>
                                                <i class="ki-duotone ki-handcart fs-7 text-gray-500 me-1">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                                <span class="text-gray-500 fs-7">{{ $activity['seller_name'] }}</span>
                                            @endif
                                        </div>
                                        @if(isset($activity['formatted_amount']))
                                            <span class="fw-bold text-primary fs-7">{{ $activity['formatted_amount'] }}</span>
                                        @endif
                                    </div>
                                    <!--end::Info-->
                                    <!--begin::Time-->
                                    <div class="d-flex align-items-center justify-content-between mt-2">
                                        <span class="text-gray-400 fs-8">{{ $activity['time_ago'] }}</span>
                                        @if(isset($activity['action_url']) && $activity['action_url'])
                                            <a href="{{ $activity['action_url'] }}" class="btn btn-sm btn-light-primary">
                                                <i class="ki-duotone ki-eye fs-7">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                    <span class="path3"></span>
                                                </i>
                                                View
                                            </a>
                                        @endif
                                    </div>
                                    <!--end::Time-->
                                </div>
                                <!--end::Content-->
                                <!--begin::Status-->
                                @if(isset($activity['is_read']) && !$activity['is_read'])
                                    <div class="w-8px h-8px rounded-circle bg-primary ms-3"></div>
                                @endif
                                <!--end::Status-->
                            </div>
                            <!--end::Item-->
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-10">
                        <div class="symbol symbol-100px symbol-circle mb-7">
                            <div class="symbol-label bg-light-primary text-primary">
                                <i class="ki-duotone ki-notification-bing fs-3x">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                </i>
                            </div>
                        </div>
                        <h3 class="text-gray-800 fw-bold mb-3">No Recent Activities</h3>
                        <p class="text-gray-500 mb-6">Order creation notifications will appear here</p>
                        <a href="{{ route('admin.order.add') }}" class="btn btn-primary">
                            <i class="ki-duotone ki-plus fs-3">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Create First Order
                        </a>
                    </div>
                @endif
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Card-->
    </div>
    <!--end::Col-->
</div>
<!--end::Row-->
@endsection
