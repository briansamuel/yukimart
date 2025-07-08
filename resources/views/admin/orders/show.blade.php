@extends('admin.index')
@section('page-header', __('order.order_detail'))
@section('page-sub_header', $order->order_code)

@section('content')
<div class="d-flex flex-column flex-lg-row">
    <!--begin::Sidebar-->
    <div class="flex-column flex-lg-row-auto w-lg-250px w-xl-350px mb-10">
        <!--begin::Card-->
        <div class="card mb-5 mb-xl-8">
            <!--begin::Card body-->
            <div class="card-body">
                <!--begin::Summary-->
                <!--begin::Order Info-->
                <div class="d-flex flex-center flex-column py-5">
                    <!--begin::Avatar-->
                    <div class="symbol symbol-100px symbol-circle mb-7">
                        <div class="symbol-label fs-3 bg-light-primary text-primary">
                            {{ strtoupper(substr($order->order_code, 0, 2)) }}
                        </div>
                    </div>
                    <!--end::Avatar-->
                    <!--begin::Name-->
                    <a href="#" class="fs-3 text-gray-800 text-hover-primary fw-bold mb-3">{{ $order->order_code }}</a>
                    <!--end::Name-->
                    <!--begin::Position-->
                    <div class="mb-9">
                        <!--begin::Badge-->
                        <div class="badge badge-lg badge-light-{{ $order->status_info['status']['class'] }} d-inline">
                            <i class="ki-duotone ki-{{ $order->status_info['status']['icon'] }} fs-6 me-1"></i>
                            {{ $order->status_info['status']['label'] }}
                        </div>
                        <!--end::Badge-->
                        <div class="badge badge-lg badge-light-{{ $order->status_info['payment_status']['class'] }} d-inline ms-2">
                            {{ $order->status_info['payment_status']['label'] }}
                        </div>
                    </div>
                    <!--end::Position-->
                </div>
                <!--end::Order Info-->
                <!--end::Summary-->

                <!--begin::Details toggle-->
                <div class="d-flex flex-stack fs-4 py-3">
                    <div class="fw-bold rotate collapsible" data-bs-toggle="collapse" href="#kt_order_view_details" role="button" aria-expanded="false" aria-controls="kt_order_view_details">{{ __('order.details') }}
                    <span class="ms-2 rotate-180">
                        <i class="ki-duotone ki-down fs-3"></i>
                    </span></div>
                </div>
                <!--end::Details toggle-->
                <div class="separator"></div>
                <!--begin::Details content-->
                <div id="kt_order_view_details" class="collapse show">
                    <div class="pb-5 fs-6">
                        <!--begin::Details item-->
                        <div class="fw-bold mt-5">{{ __('order.customer') }}</div>
                        <div class="text-gray-600">
                            @if($order->customer_id == 0)
                                Khách lẻ
                            @else
                                {{ $order->customer->name ?? __('common.not_set') }}
                            @endif
                        </div>
                        <!--begin::Details item-->
                        <!--begin::Details item-->
                        <div class="fw-bold mt-5">{{ __('order.phone') }}</div>
                        <div class="text-gray-600">{{ $order->customer->phone ?? __('common.not_set') }}</div>
                        <!--begin::Details item-->
                        <!--begin::Details item-->
                        <div class="fw-bold mt-5">{{ __('order.branch') }}</div>
                        <div class="text-gray-600">{{ $order->branch->name ?? __('common.not_set') }}</div>
                        <!--begin::Details item-->
                        <!--begin::Details item-->
                        <div class="fw-bold mt-5">{{ __('order.channel') }}</div>
                        <div class="text-gray-600">{{ __('order.' . $order->channel) }}</div>
                        <!--begin::Details item-->
                        <!--begin::Details item-->
                        <div class="fw-bold mt-5">{{ __('order.created_at') }}</div>
                        <div class="text-gray-600">{{ $order->created_at->format('d/m/Y H:i') }}</div>
                        <!--begin::Details item-->
                        <!--begin::Details item-->
                        <div class="fw-bold mt-5">{{ __('order.updated_at') }}</div>
                        <div class="text-gray-600">{{ $order->updated_at->format('d/m/Y H:i') }}</div>
                        <!--begin::Details item-->
                    </div>
                </div>
                <!--end::Details content-->
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Card-->

        <!--begin::Connected Accounts-->
        <div class="card mb-5 mb-xl-8">
            <!--begin::Card header-->
            <div class="card-header border-0">
                <div class="card-title">
                    <h3 class="fw-bold m-0">{{ __('order.quick_actions') }}</h3>
                </div>
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-2">
                <!--begin::Notice-->
                <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed mb-9 p-6">
                    <!--begin::Icon-->
                    <i class="ki-duotone ki-design-1 fs-2tx text-primary me-4"></i>
                    <!--end::Icon-->
                    <!--begin::Wrapper-->
                    <div class="d-flex flex-stack flex-grow-1">
                        <!--begin::Content-->
                        <div class="fw-semibold">
                            <div class="fs-6 text-gray-700">{{ __('order.quick_actions_description') }}</div>
                        </div>
                        <!--end::Content-->
                    </div>
                    <!--end::Wrapper-->
                </div>
                <!--end::Notice-->

                <!--begin::Action buttons-->
                <div class="d-flex flex-column gap-3">
                    <a href="{{ route('admin.order.edit', $order->id) }}" class="btn btn-light-primary btn-sm">
                        <i class="ki-duotone ki-pencil fs-3">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        {{ __('order.edit_order') }}
                    </a>
                    
                    <button type="button" class="btn btn-light-success btn-sm" data-bs-toggle="modal" data-bs-target="#kt_modal_record_payment">
                        <i class="ki-duotone ki-wallet fs-3">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                        {{ __('order.record_payment') }}
                    </button>
                    
                    <button type="button" class="btn btn-light-warning btn-sm" data-bs-toggle="modal" data-bs-target="#kt_modal_update_status">
                        <i class="ki-duotone ki-setting-2 fs-3">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        {{ __('order.update_status') }}
                    </button>
                    
                    <button type="button" class="btn btn-light-info btn-sm" data-bs-toggle="modal" data-bs-target="#kt_modal_print_order">
                        <i class="ki-duotone ki-printer fs-3">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        {{ __('order.print_order') }}
                    </button>
                    
                    @if($order->status !== 'completed')
                    <button type="button" class="btn btn-light-danger btn-sm" data-bs-toggle="modal" data-bs-target="#kt_modal_cancel_order">
                        <i class="ki-duotone ki-cross fs-3">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        {{ __('order.cancel_order') }}
                    </button>
                    @endif
                </div>
                <!--end::Action buttons-->
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Connected Accounts-->
    </div>
    <!--end::Sidebar-->

    <!--begin::Content-->
    <div class="flex-lg-row-fluid ms-lg-15">
        <!--begin:::Tabs-->
        <ul class="nav nav-custom nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-4 fw-semibold mb-8">
            <!--begin:::Tab item-->
            <li class="nav-item">
                <a class="nav-link text-active-primary pb-4 active" data-bs-toggle="tab" href="#kt_order_view_overview_tab">{{ __('order.overview') }}</a>
            </li>
            <!--end:::Tab item-->
            <!--begin:::Tab item-->
            <li class="nav-item">
                <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab" href="#kt_order_view_items_tab">{{ __('order.items') }}</a>
            </li>
            <!--end:::Tab item-->
            <!--begin:::Tab item-->
            <li class="nav-item">
                <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab" href="#kt_order_view_payment_tab">{{ __('order.payment') }}</a>
            </li>
            <!--end:::Tab item-->
            <!--begin:::Tab item-->
            <li class="nav-item">
                <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab" href="#kt_order_view_history_tab">{{ __('order.history') }}</a>
            </li>
            <!--end:::Tab item-->
        </ul>
        <!--end:::Tabs-->

        <!--begin:::Tab content-->
        <div class="tab-content" id="myTabContent">
            <!--begin:::Tab pane-->
            <div class="tab-pane fade show active" id="kt_order_view_overview_tab" role="tabpanel">
                @include('admin.orders.partials.overview', ['order' => $order])
            </div>
            <!--end:::Tab pane-->
            <!--begin:::Tab pane-->
            <div class="tab-pane fade" id="kt_order_view_items_tab" role="tabpanel">
                @include('admin.orders.partials.items', ['order' => $order])
            </div>
            <!--end:::Tab pane-->
            <!--begin:::Tab pane-->
            <div class="tab-pane fade" id="kt_order_view_payment_tab" role="tabpanel">
                @include('admin.orders.partials.payment', ['order' => $order])
            </div>
            <!--end:::Tab pane-->
            <!--begin:::Tab pane-->
            <div class="tab-pane fade" id="kt_order_view_history_tab" role="tabpanel">
                @include('admin.orders.partials.history', ['order' => $order])
            </div>
            <!--end:::Tab pane-->
        </div>
        <!--end:::Tab content-->
    </div>
    <!--end::Content-->
</div>

@include('admin.orders.partials.modals', ['order' => $order])
@endsection

@section('scripts')
    <script src="{{ asset('admin-assets/assets/js/custom/apps/orders/detail.js') }}"></script>
@endsection
