@extends('admin.index')
@section('page-header', __('customer.customer_detail'))
@section('page-sub_header', $customer->name)

@section('style')
    <link href="{{ asset('admin-assets/assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
<!--begin::Layout-->
<div class="d-flex flex-column flex-lg-row">
    <!--begin::Sidebar-->
    <div class="flex-column flex-lg-row-auto w-lg-250px w-xl-350px mb-10">
        <!--begin::Card-->
        <div class="card mb-5 mb-xl-8">
            <!--begin::Card body-->
            <div class="card-body">
                <!--begin::Summary-->
                <!--begin::User Info-->
                <div class="d-flex flex-center flex-column py-5">
                    <!--begin::Avatar-->
                    <div class="symbol symbol-100px symbol-circle mb-7">
                        @if($customer->avatar)
                            <img src="{{ asset('storage/' . $customer->avatar) }}" alt="{{ $customer->name }}" />
                        @else
                            <div class="symbol-label fs-3 bg-light-primary text-primary">
                                {{ strtoupper(substr($customer->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    <!--end::Avatar-->
                    <!--begin::Name-->
                    <a href="#" class="fs-3 text-gray-800 text-hover-primary fw-bold mb-3">{{ $customer->name }}</a>
                    <!--end::Name-->
                    <!--begin::Position-->
                    <div class="mb-9">
                        <!--begin::Badge-->
                        <div class="badge badge-lg badge-light-primary d-inline">{{ __('customer.' . $customer->customer_type) }}</div>
                        <!--end::Badge-->
                    </div>
                    <!--end::Position-->
                </div>
                <!--end::User Info-->
                <!--begin::Info-->
                <div class="d-flex flex-wrap flex-center">
                    <!--begin::Stats-->
                    <div class="border border-gray-300 border-dashed rounded py-3 px-3 mb-3">
                        <div class="d-flex align-items-center">
                            <div class="fs-2 fw-bold counted text-dark" data-kt-countup="true" data-kt-countup-value="{{ $stats['total_orders'] }}">{{ $stats['total_orders'] }}</div>
                        </div>
                        <div class="fw-semibold fs-6 text-gray-400">{{ __('customer.total_orders') }}</div>
                    </div>
                    <!--end::Stats-->
                    <!--begin::Stats-->
                    <div class="border border-gray-300 border-dashed rounded py-3 px-3 mx-4 mb-3">
                        <div class="d-flex align-items-center">
                            <div class="fs-2 fw-bold counted text-dark">{{ number_format($stats['total_spent'], 0, ',', '.') }}₫</div>
                        </div>
                        <div class="fw-semibold fs-6 text-gray-400">{{ __('customer.total_spent') }}</div>
                    </div>
                    <!--end::Stats-->
                    <!--begin::Stats-->
                    <div class="border border-gray-300 border-dashed rounded py-3 px-3 mb-3">
                        <div class="d-flex align-items-center">
                            <div class="fs-2 fw-bold counted text-dark">{{ number_format($stats['avg_order_value'], 0, ',', '.') }}₫</div>
                        </div>
                        <div class="fw-semibold fs-6 text-gray-400">{{ __('customer.avg_order_value') }}</div>
                    </div>
                    <!--end::Stats-->
                </div>
                <!--end::Info-->
                <!--end::Summary-->
                <!--begin::Details toggle-->
                <div class="d-flex flex-stack fs-4 py-3">
                    <div class="fw-bold rotate collapsible" data-bs-toggle="collapse" href="#kt_user_view_details" role="button" aria-expanded="false" aria-controls="kt_user_view_details">{{ __('customer.details') }}
                    <span class="ms-2 rotate-180">
                        <i class="ki-duotone ki-down fs-3"></i>
                    </span></div>
                </div>
                <!--end::Details toggle-->
                <div class="separator"></div>
                <!--begin::Details content-->
                <div id="kt_user_view_details" class="collapse show">
                    <div class="pb-5 fs-6">
                        <!--begin::Details item-->
                        <div class="fw-bold mt-5">{{ __('customer.email') }}</div>
                        <div class="text-gray-600">{{ $customer->email ?: '-' }}</div>
                        <!--begin::Details item-->
                        <!--begin::Details item-->
                        <div class="fw-bold mt-5">{{ __('customer.phone') }}</div>
                        <div class="text-gray-600">{{ $customer->phone ?: '-' }}</div>
                        <!--begin::Details item-->
                        <!--begin::Details item-->
                        <div class="fw-bold mt-5">{{ __('customer.address') }}</div>
                        <div class="text-gray-600">{{ $customer->address ?: '-' }}</div>
                        <!--begin::Details item-->
                        <!--begin::Details item-->
                        <div class="fw-bold mt-5">{{ __('customer.date_of_birth') }}</div>
                        <div class="text-gray-600">{{ $customer->date_of_birth ? \Carbon\Carbon::parse($customer->date_of_birth)->format('d/m/Y') : '-' }}</div>
                        <!--begin::Details item-->
                        <!--begin::Details item-->
                        <div class="fw-bold mt-5">{{ __('customer.gender') }}</div>
                        <div class="text-gray-600">{{ $customer->gender ? __('customer.' . $customer->gender) : '-' }}</div>
                        <!--begin::Details item-->
                        <!--begin::Details item-->
                        <div class="fw-bold mt-5">{{ __('customer.status') }}</div>
                        <div class="text-gray-600">
                            @if($customer->status == 'active')
                                <span class="badge badge-light-success">{{ __('customer.active') }}</span>
                            @else
                                <span class="badge badge-light-danger">{{ __('customer.inactive') }}</span>
                            @endif
                        </div>
                        <!--begin::Details item-->
                        <!--begin::Details item-->
                        <div class="fw-bold mt-5">{{ __('customer.created_at') }}</div>
                        <div class="text-gray-600">{{ $customer->created_at->format('d/m/Y H:i') }}</div>
                        <!--begin::Details item-->
                    </div>
                </div>
                <!--end::Details content-->
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Card-->
    </div>
    <!--end::Sidebar-->
    <!--begin::Content-->
    <div class="flex-lg-row-fluid ms-lg-15">
        <!--begin:::Tabs-->
        <ul class="nav nav-custom nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-4 fw-semibold mb-8">
            <!--begin:::Tab item-->
            <li class="nav-item">
                <a class="nav-link text-active-primary pb-4 active" data-bs-toggle="tab" href="#kt_user_view_overview_tab">{{ __('customer.overview') }}</a>
            </li>
            <!--end:::Tab item-->
            <!--begin:::Tab item-->
            <li class="nav-item">
                <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab" href="#kt_user_view_orders_tab">{{ __('customer.orders') }}</a>
            </li>
            <!--end:::Tab item-->
            <!--begin:::Tab item-->
            <li class="nav-item">
                <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab" href="#kt_user_view_statistics_tab">{{ __('customer.statistics') }}</a>
            </li>
            <!--end:::Tab item-->
        </ul>
        <!--end:::Tabs-->
        <!--begin:::Tab content-->
        <div class="tab-content" id="myTabContent">
            <!--begin:::Tab pane-->
            <div class="tab-pane fade show active" id="kt_user_view_overview_tab" role="tabpanel">
                <!--begin::Card-->
                <div class="card card-flush mb-6 mb-xl-9">
                    <!--begin::Card header-->
                    <div class="card-header mt-6">
                        <!--begin::Card title-->
                        <div class="card-title flex-column">
                            <h2 class="mb-1">{{ __('customer.recent_orders') }}</h2>
                            <div class="fs-6 fw-semibold text-muted">{{ __('customer.last_10_orders') }}</div>
                        </div>
                        <!--end::Card title-->
                        <!--begin::Card toolbar-->
                        <div class="card-toolbar">
                            <a href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-light-primary btn-sm">{{ __('customer.edit_customer') }}</a>
                        </div>
                        <!--end::Card toolbar-->
                    </div>
                    <!--end::Card header-->
                    <!--begin::Card body-->
                    <div class="card-body pt-0 pb-5">
                        <!--begin::Table wrapper-->
                        <div class="table-responsive">
                            <!--begin::Table-->
                            <table class="table align-middle table-row-dashed gy-5" id="kt_table_customers_orders">
                                <!--begin::Table head-->
                                <thead class="border-bottom border-gray-200 fs-7 fw-bold">
                                    <!--begin::Table row-->
                                    <tr class="text-start text-muted text-uppercase gs-0">
                                        <th class="min-w-100px">{{ __('customer.order_code') }}</th>
                                        <th class="min-w-100px">{{ __('customer.date') }}</th>
                                        <th class="min-w-100px">{{ __('customer.status') }}</th>
                                        <th class="min-w-100px">{{ __('customer.total') }}</th>
                                        <th class="text-end min-w-75px">{{ __('customer.actions') }}</th>
                                    </tr>
                                    <!--end::Table row-->
                                </thead>
                                <!--end::Table head-->
                                <!--begin::Table body-->
                                <tbody class="fs-6 fw-semibold text-gray-600">
                                    @forelse($recentOrders as $order)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.order.show', $order) }}" class="text-gray-800 text-hover-primary mb-1">{{ $order->order_code }}</a>
                                        </td>
                                        <td>{{ $order->created_at->format('d/m/Y') }}</td>
                                        <td>
                                            @php
                                                $statusClass = match($order->status) {
                                                    'completed' => 'badge-light-success',
                                                    'processing' => 'badge-light-warning',
                                                    'cancelled' => 'badge-light-danger',
                                                    default => 'badge-light-primary'
                                                };
                                            @endphp
                                            <span class="badge {{ $statusClass }}">{{ __('orders.status.' . $order->status) }}</span>
                                        </td>
                                        <td>{{ number_format($order->final_amount, 0, ',', '.') }}₫</td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.order.show', $order) }}" class="btn btn-light btn-active-light-primary btn-sm">{{ __('customer.view') }}</a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-10">
                                            <div class="text-muted">{{ __('customer.no_orders') }}</div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                                <!--end::Table body-->
                            </table>
                            <!--end::Table-->
                        </div>
                        <!--end::Table wrapper-->
                    </div>
                    <!--end::Card body-->
                </div>
                <!--end::Card-->
            </div>
            <!--end:::Tab pane-->
            <!--begin:::Tab pane-->
            <div class="tab-pane fade" id="kt_user_view_orders_tab" role="tabpanel">
                <!--begin::Card-->
                <div class="card card-flush mb-6 mb-xl-9">
                    <!--begin::Card header-->
                    <div class="card-header mt-6">
                        <!--begin::Card title-->
                        <div class="card-title flex-column">
                            <h2 class="mb-1">{{ __('customer.all_orders') }}</h2>
                            <div class="fs-6 fw-semibold text-muted">{{ __('customer.order_history') }}</div>
                        </div>
                        <!--end::Card title-->
                    </div>
                    <!--end::Card header-->
                    <!--begin::Card body-->
                    <div class="card-body pt-0 pb-5">
                        <!--begin::Table wrapper-->
                        <div class="table-responsive">
                            <!--begin::Table-->
                            <table class="table align-middle table-row-dashed gy-5" id="kt_table_all_orders">
                                <!--begin::Table head-->
                                <thead class="border-bottom border-gray-200 fs-7 fw-bold">
                                    <!--begin::Table row-->
                                    <tr class="text-start text-muted text-uppercase gs-0">
                                        <th class="min-w-100px">{{ __('customer.order_code') }}</th>
                                        <th class="min-w-100px">{{ __('customer.date') }}</th>
                                        <th class="min-w-100px">{{ __('customer.items') }}</th>
                                        <th class="min-w-100px">{{ __('customer.status') }}</th>
                                        <th class="min-w-100px">{{ __('customer.total') }}</th>
                                        <th class="text-end min-w-75px">{{ __('customer.actions') }}</th>
                                    </tr>
                                    <!--end::Table row-->
                                </thead>
                                <!--end::Table head-->
                                <!--begin::Table body-->
                                <tbody class="fs-6 fw-semibold text-gray-600">
                                    @foreach($customer->orders as $order)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.order.show', $order) }}" class="text-gray-800 text-hover-primary mb-1">{{ $order->order_code }}</a>
                                        </td>
                                        <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                        <td>{{ $order->orderItems->count() }} {{ __('customer.items') }}</td>
                                        <td>
                                            @php
                                                $statusClass = match($order->status) {
                                                    'completed' => 'badge-light-success',
                                                    'processing' => 'badge-light-warning',
                                                    'cancelled' => 'badge-light-danger',
                                                    default => 'badge-light-primary'
                                                };
                                            @endphp
                                            <span class="badge {{ $statusClass }}">{{ __('orders.status.' . $order->status) }}</span>
                                        </td>
                                        <td>{{ number_format($order->final_amount, 0, ',', '.') }}₫</td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.order.show', $order) }}" class="btn btn-light btn-active-light-primary btn-sm">{{ __('customer.view') }}</a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <!--end::Table body-->
                            </table>
                            <!--end::Table-->
                        </div>
                        <!--end::Table wrapper-->
                    </div>
                    <!--end::Card body-->
                </div>
                <!--end::Card-->
            </div>
            <!--end:::Tab pane-->
            <!--begin:::Tab pane-->
            <div class="tab-pane fade" id="kt_user_view_statistics_tab" role="tabpanel">
                <!--begin::Card-->
                <div class="card card-flush mb-6 mb-xl-9">
                    <!--begin::Card header-->
                    <div class="card-header mt-6">
                        <!--begin::Card title-->
                        <div class="card-title flex-column">
                            <h2 class="mb-1">{{ __('customer.monthly_statistics') }}</h2>
                            <div class="fs-6 fw-semibold text-muted">{{ __('customer.order_statistics_by_month') }}</div>
                        </div>
                        <!--end::Card title-->
                    </div>
                    <!--end::Card header-->
                    <!--begin::Card body-->
                    <div class="card-body pt-0 pb-5">
                        <!--begin::Table wrapper-->
                        <div class="table-responsive">
                            <!--begin::Table-->
                            <table class="table align-middle table-row-dashed gy-5">
                                <!--begin::Table head-->
                                <thead class="border-bottom border-gray-200 fs-7 fw-bold">
                                    <!--begin::Table row-->
                                    <tr class="text-start text-muted text-uppercase gs-0">
                                        <th class="min-w-100px">{{ __('customer.month') }}</th>
                                        <th class="min-w-100px">{{ __('customer.orders_count') }}</th>
                                        <th class="min-w-100px">{{ __('customer.total_amount') }}</th>
                                    </tr>
                                    <!--end::Table row-->
                                </thead>
                                <!--end::Table head-->
                                <!--begin::Table body-->
                                <tbody class="fs-6 fw-semibold text-gray-600">
                                    @forelse($monthlyStats as $stat)
                                    <tr>
                                        <td>{{ $stat->month }}/{{ $stat->year }}</td>
                                        <td>{{ $stat->orders_count }}</td>
                                        <td>{{ number_format($stat->total_amount, 0, ',', '.') }}₫</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-10">
                                            <div class="text-muted">{{ __('customer.no_statistics') }}</div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                                <!--end::Table body-->
                            </table>
                            <!--end::Table-->
                        </div>
                        <!--end::Table wrapper-->
                    </div>
                    <!--end::Card body-->
                </div>
                <!--end::Card-->
            </div>
            <!--end:::Tab pane-->
        </div>
        <!--end:::Tab content-->
    </div>
    <!--end::Content-->
</div>
<!--end::Layout-->
@endsection

@section('vendor-script')
    <script src="{{ asset('admin-assets/assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
@endsection

@section('scripts')
    <script src="{{ asset('admin-assets/assets/js/custom/apps/customers/view.js') }}"></script>
@endsection
