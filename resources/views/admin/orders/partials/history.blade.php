<!--begin::Card-->
<div class="card mb-5 mb-xl-10">
    <!--begin::Card header-->
    <div class="card-header border-0">
        <!--begin::Card title-->
        <div class="card-title m-0">
            <h3 class="fw-bold m-0">{{ __('order.order_timeline') }}</h3>
        </div>
        <!--end::Card title-->
    </div>
    <!--begin::Card header-->
    <!--begin::Card body-->
    <div class="card-body border-top p-9">
        @if(isset($order->timeline) && count($order->timeline) > 0)
            <!--begin::Timeline-->
            <div class="timeline-label">
                @foreach($order->timeline as $event)
                <!--begin::Item-->
                <div class="timeline-item">
                    <!--begin::Label-->
                    <div class="timeline-label fw-bold text-gray-800 fs-6">{{ $event['date']->format('H:i') }}</div>
                    <!--end::Label-->
                    <!--begin::Badge-->
                    <div class="timeline-badge">
                        <i class="fa fa-genderless text-{{ $event['type'] }} fs-1"></i>
                    </div>
                    <!--end::Badge-->
                    <!--begin::Text-->
                    <div class="fw-muted text-muted ps-3">
                        <strong>{{ $event['title'] }}</strong>
                        <br>
                        {{ $event['description'] }}
                        <br>
                        <span class="text-muted fs-7">{{ $event['date']->format('d/m/Y H:i') }}</span>
                    </div>
                    <!--end::Text-->
                </div>
                <!--end::Item-->
                @endforeach
            </div>
            <!--end::Timeline-->
        @else
            <div class="text-center py-10">
                <div class="text-muted">{{ __('order.no_timeline_data') }}</div>
            </div>
        @endif
    </div>
    <!--end::Card body-->
</div>
<!--end::Card-->

<!--begin::Card-->
<div class="card mb-5 mb-xl-10">
    <!--begin::Card header-->
    <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse" data-bs-target="#kt_account_order_notes" aria-expanded="true" aria-controls="kt_account_order_notes">
        <!--begin::Card title-->
        <div class="card-title m-0">
            <h3 class="fw-bold m-0">{{ __('order.order_notes') }}</h3>
        </div>
        <!--end::Card title-->
    </div>
    <!--begin::Card header-->
    <!--begin::Content-->
    <div id="kt_account_order_notes" class="collapse show">
        <!--begin::Card body-->
        <div class="card-body border-top p-9">
            @if($order->notes)
                <div class="bg-light-primary rounded p-6">
                    <div class="fw-semibold text-gray-800 fs-6">{{ $order->notes }}</div>
                </div>
            @else
                <div class="text-center py-10">
                    <div class="text-muted">{{ __('order.no_notes') }}</div>
                </div>
            @endif
        </div>
        <!--end::Card body-->
    </div>
    <!--end::Content-->
</div>
<!--end::Card-->

<!--begin::Card-->
<div class="card mb-5 mb-xl-10">
    <!--begin::Card header-->
    <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse" data-bs-target="#kt_account_shipping_info" aria-expanded="true" aria-controls="kt_account_shipping_info">
        <!--begin::Card title-->
        <div class="card-title m-0">
            <h3 class="fw-bold m-0">{{ __('order.shipping_information') }}</h3>
        </div>
        <!--end::Card title-->
    </div>
    <!--begin::Card header-->
    <!--begin::Content-->
    <div id="kt_account_shipping_info" class="collapse show">
        <!--begin::Card body-->
        <div class="card-body border-top p-9">
            <!--begin::Row-->
            <div class="row mb-6">
                <!--begin::Label-->
                <label class="col-lg-4 col-form-label fw-semibold fs-6">{{ __('order.delivery_status') }}</label>
                <!--end::Label-->
                <!--begin::Col-->
                <div class="col-lg-8 fv-row">
                    <span class="badge badge-light-{{ $order->status_info['delivery_status']['class'] }}">
                        {{ $order->status_info['delivery_status']['label'] }}
                    </span>
                </div>
                <!--end::Col-->
            </div>
            <!--end::Row-->
            <!--begin::Row-->
            <div class="row mb-6">
                <!--begin::Label-->
                <label class="col-lg-4 col-form-label fw-semibold fs-6">{{ __('order.shipping_address') }}</label>
                <!--end::Label-->
                <!--begin::Col-->
                <div class="col-lg-8 fv-row">
                    <span class="fw-semibold text-gray-600 fs-6">
                        {{ $order->shipping_address ?? ($order->customer->address ?? __('common.not_set')) }}
                    </span>
                </div>
                <!--end::Col-->
            </div>
            <!--end::Row-->
            <!--begin::Row-->
            <div class="row mb-6">
                <!--begin::Label-->
                <label class="col-lg-4 col-form-label fw-semibold fs-6">{{ __('order.shipping_fee') }}</label>
                <!--end::Label-->
                <!--begin::Col-->
                <div class="col-lg-8 fv-row">
                    <span class="fw-semibold text-gray-600 fs-6">
                        @if($order->shipping_fee > 0)
                            {{ number_format($order->shipping_fee, 0, ',', '.') }}₫
                        @else
                            {{ __('order.free_shipping') }}
                        @endif
                    </span>
                </div>
                <!--end::Col-->
            </div>
            <!--end::Row-->
            @if($order->shipping_method)
            <!--begin::Row-->
            <div class="row mb-6">
                <!--begin::Label-->
                <label class="col-lg-4 col-form-label fw-semibold fs-6">{{ __('order.shipping_method') }}</label>
                <!--end::Label-->
                <!--begin::Col-->
                <div class="col-lg-8 fv-row">
                    <span class="fw-semibold text-gray-600 fs-6">{{ $order->shipping_method }}</span>
                </div>
                <!--end::Col-->
            </div>
            <!--end::Row-->
            @endif
            @if($order->tracking_number)
            <!--begin::Row-->
            <div class="row mb-6">
                <!--begin::Label-->
                <label class="col-lg-4 col-form-label fw-semibold fs-6">{{ __('order.tracking_number') }}</label>
                <!--end::Label-->
                <!--begin::Col-->
                <div class="col-lg-8 fv-row">
                    <span class="fw-semibold text-gray-600 fs-6">{{ $order->tracking_number }}</span>
                    <a href="#" class="btn btn-sm btn-light-primary ms-2">{{ __('order.track_package') }}</a>
                </div>
                <!--end::Col-->
            </div>
            <!--end::Row-->
            @endif
            @if($order->estimated_delivery_date)
            <!--begin::Row-->
            <div class="row mb-6">
                <!--begin::Label-->
                <label class="col-lg-4 col-form-label fw-semibold fs-6">{{ __('order.estimated_delivery') }}</label>
                <!--end::Label-->
                <!--begin::Col-->
                <div class="col-lg-8 fv-row">
                    <span class="fw-semibold text-gray-600 fs-6">{{ $order->estimated_delivery_date->format('d/m/Y') }}</span>
                </div>
                <!--end::Col-->
            </div>
            <!--end::Row-->
            @endif
        </div>
        <!--end::Card body-->
    </div>
    <!--end::Content-->
</div>
<!--end::Card-->

<!--begin::Card-->
<div class="card mb-5 mb-xl-10">
    <!--begin::Card header-->
    <div class="card-header border-0">
        <!--begin::Card title-->
        <div class="card-title m-0">
            <h3 class="fw-bold m-0">{{ __('order.system_information') }}</h3>
        </div>
        <!--end::Card title-->
    </div>
    <!--begin::Card header-->
    <!--begin::Card body-->
    <div class="card-body border-top p-9">
        <!--begin::Row-->
        <div class="row mb-6">
            <!--begin::Label-->
            <label class="col-lg-4 col-form-label fw-semibold fs-6">{{ __('order.order_id') }}</label>
            <!--end::Label-->
            <!--begin::Col-->
            <div class="col-lg-8 fv-row">
                <span class="fw-semibold text-gray-800 fs-6">#{{ $order->id }}</span>
            </div>
            <!--end::Col-->
        </div>
        <!--end::Row-->
        <!--begin::Row-->
        <div class="row mb-6">
            <!--begin::Label-->
            <label class="col-lg-4 col-form-label fw-semibold fs-6">{{ __('order.created_by') }}</label>
            <!--end::Label-->
            <!--begin::Col-->
            <div class="col-lg-8 fv-row">
                <span class="fw-semibold text-gray-600 fs-6">
                    @if($order->creator)
                        {{ $order->creator->name }}
                    @else
                        {{ __('common.unknown') }}
                    @endif
                </span>
            </div>
            <!--end::Col-->
        </div>
        <!--end::Row-->
        <!--begin::Row-->
        <div class="row mb-6">
            <!--begin::Label-->
            <label class="col-lg-4 col-form-label fw-semibold fs-6">{{ __('order.sold_by') }}</label>
            <!--end::Label-->
            <!--begin::Col-->
            <div class="col-lg-8 fv-row">
                <span class="fw-semibold text-gray-600 fs-6">
                    @if($order->seller)
                        {{ $order->seller->name }}
                    @else
                        {{ __('common.unknown') }}
                    @endif
                </span>
            </div>
            <!--end::Col-->
        </div>
        <!--end::Row-->
        <!--begin::Row-->
        <div class="row mb-6">
            <!--begin::Label-->
            <label class="col-lg-4 col-form-label fw-semibold fs-6">{{ __('order.branch') }}</label>
            <!--end::Label-->
            <!--begin::Col-->
            <div class="col-lg-8 fv-row">
                <span class="fw-semibold text-gray-600 fs-6">
                    @if($order->branch)
                        {{ $order->branch->name }}
                    @else
                        {{ __('common.unknown') }}
                    @endif
                </span>
            </div>
            <!--end::Col-->
        </div>
        <!--end::Row-->
        <!--begin::Row-->
        <div class="row mb-6">
            <!--begin::Label-->
            <label class="col-lg-4 col-form-label fw-semibold fs-6">{{ __('order.created_at') }}</label>
            <!--end::Label-->
            <!--begin::Col-->
            <div class="col-lg-8 fv-row">
                <span class="fw-semibold text-gray-600 fs-6">{{ $order->created_at->format('d/m/Y H:i:s') }}</span>
                <div class="text-muted fs-7">{{ $order->created_at->diffForHumans() }}</div>
            </div>
            <!--end::Col-->
        </div>
        <!--end::Row-->
        <!--begin::Row-->
        <div class="row mb-6">
            <!--begin::Label-->
            <label class="col-lg-4 col-form-label fw-semibold fs-6">{{ __('order.updated_at') }}</label>
            <!--end::Label-->
            <!--begin::Col-->
            <div class="col-lg-8 fv-row">
                <span class="fw-semibold text-gray-600 fs-6">{{ $order->updated_at->format('d/m/Y H:i:s') }}</span>
                <div class="text-muted fs-7">{{ $order->updated_at->diffForHumans() }}</div>
            </div>
            <!--end::Col-->
        </div>
        <!--end::Row-->
        <!--begin::Row-->
        <div class="row mb-6">
            <!--begin::Label-->
            <label class="col-lg-4 col-form-label fw-semibold fs-6">{{ __('order.ip_address') }}</label>
            <!--end::Label-->
            <!--begin::Col-->
            <div class="col-lg-8 fv-row">
                <span class="fw-semibold text-gray-600 fs-6">{{ $order->ip_address ?? __('common.not_recorded') }}</span>
            </div>
            <!--end::Col-->
        </div>
        <!--end::Row-->
        <!--begin::Row-->
        <div class="row mb-6">
            <!--begin::Label-->
            <label class="col-lg-4 col-form-label fw-semibold fs-6">{{ __('order.user_agent') }}</label>
            <!--end::Label-->
            <!--begin::Col-->
            <div class="col-lg-8 fv-row">
                <span class="fw-semibold text-gray-600 fs-6">{{ $order->user_agent ?? __('common.not_recorded') }}</span>
            </div>
            <!--end::Col-->
        </div>
        <!--end::Row-->
    </div>
    <!--end::Card body-->
</div>
<!--end::Card-->
