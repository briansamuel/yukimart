<!--begin::Card-->
<div class="card mb-5 mb-xl-10">
    <!--begin::Card header-->
    <div class="card-header border-0">
        <!--begin::Card title-->
        <div class="card-title m-0">
            <h3 class="fw-bold m-0">{{ __('order.payment_information') }}</h3>
        </div>
        <!--end::Card title-->
        <!--begin::Card toolbar-->
        <div class="card-toolbar">
            <button type="button" class="btn btn-sm btn-light-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_record_payment">
                <i class="ki-duotone ki-plus fs-3"></i>
                {{ __('order.record_payment') }}
            </button>
        </div>
        <!--end::Card toolbar-->
    </div>
    <!--begin::Card header-->
    <!--begin::Card body-->
    <div class="card-body border-top p-9">
        <!--begin::Row-->
        <div class="row g-6 g-xl-9 mb-6 mb-xl-9">
            <!--begin::Col-->
            <div class="col-md-6 col-lg-4">
                <!--begin::Card widget-->
                <div class="card card-flush h-md-50 mb-5 mb-xl-10">
                    <!--begin::Header-->
                    <div class="card-header pt-5">
                        <!--begin::Title-->
                        <div class="card-title d-flex flex-column">
                            <!--begin::Info-->
                            <div class="d-flex align-items-center">
                                <!--begin::Currency-->
                                <span class="fs-4 fw-semibold text-gray-400 me-1 align-self-start">₫</span>
                                <!--end::Currency-->
                                <!--begin::Amount-->
                                <span class="fs-2hx fw-bold text-dark me-2 lh-1 ls-n2">{{ number_format($order->final_amount, 0, ',', '.') }}</span>
                                <!--end::Amount-->
                            </div>
                            <!--end::Info-->
                            <!--begin::Subtitle-->
                            <span class="text-gray-400 pt-1 fw-semibold fs-6">{{ __('order.total_amount') }}</span>
                            <!--end::Subtitle-->
                        </div>
                        <!--end::Title-->
                    </div>
                    <!--end::Header-->
                    <!--begin::Card body-->
                    <div class="card-body pt-2 pb-4 d-flex align-items-center">
                        <div class="d-flex flex-center me-5 pt-2">
                            <i class="ki-duotone ki-dollar fs-2tx text-primary">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                            </i>
                        </div>
                        <div class="d-flex flex-column content-justify-center w-100">
                            <div class="d-flex fs-6 fw-semibold align-items-center">
                                <div class="text-gray-500 flex-grow-1 me-4">{{ __('order.order_total') }}</div>
                            </div>
                        </div>
                    </div>
                    <!--end::Card body-->
                </div>
                <!--end::Card widget-->
            </div>
            <!--end::Col-->
            <!--begin::Col-->
            <div class="col-md-6 col-lg-4">
                <!--begin::Card widget-->
                <div class="card card-flush h-md-50 mb-5 mb-xl-10">
                    <!--begin::Header-->
                    <div class="card-header pt-5">
                        <!--begin::Title-->
                        <div class="card-title d-flex flex-column">
                            <!--begin::Info-->
                            <div class="d-flex align-items-center">
                                <!--begin::Currency-->
                                <span class="fs-4 fw-semibold text-gray-400 me-1 align-self-start">₫</span>
                                <!--end::Currency-->
                                <!--begin::Amount-->
                                <span class="fs-2hx fw-bold text-dark me-2 lh-1 ls-n2">{{ number_format($order->amount_paid, 0, ',', '.') }}</span>
                                <!--end::Amount-->
                            </div>
                            <!--end::Info-->
                            <!--begin::Subtitle-->
                            <span class="text-gray-400 pt-1 fw-semibold fs-6">{{ __('order.amount_paid') }}</span>
                            <!--end::Subtitle-->
                        </div>
                        <!--end::Title-->
                    </div>
                    <!--end::Header-->
                    <!--begin::Card body-->
                    <div class="card-body pt-2 pb-4 d-flex align-items-center">
                        <div class="d-flex flex-center me-5 pt-2">
                            <i class="ki-duotone ki-wallet fs-2tx text-success">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                            </i>
                        </div>
                        <div class="d-flex flex-column content-justify-center w-100">
                            <div class="d-flex fs-6 fw-semibold align-items-center">
                                <div class="text-gray-500 flex-grow-1 me-4">{{ __('order.paid_amount') }}</div>
                                <div class="fw-bolder text-gray-700 text-xxl-end">{{ $order->payment_percentage }}%</div>
                            </div>
                        </div>
                    </div>
                    <!--end::Card body-->
                </div>
                <!--end::Card widget-->
            </div>
            <!--end::Col-->
            <!--begin::Col-->
            <div class="col-md-6 col-lg-4">
                <!--begin::Card widget-->
                <div class="card card-flush h-md-50 mb-5 mb-xl-10">
                    <!--begin::Header-->
                    <div class="card-header pt-5">
                        <!--begin::Title-->
                        <div class="card-title d-flex flex-column">
                            <!--begin::Info-->
                            <div class="d-flex align-items-center">
                                <!--begin::Currency-->
                                <span class="fs-4 fw-semibold text-gray-400 me-1 align-self-start">₫</span>
                                <!--end::Currency-->
                                <!--begin::Amount-->
                                <span class="fs-2hx fw-bold me-2 lh-1 ls-n2 {{ $order->remaining_amount > 0 ? 'text-warning' : 'text-success' }}">
                                    {{ number_format($order->remaining_amount, 0, ',', '.') }}
                                </span>
                                <!--end::Amount-->
                            </div>
                            <!--end::Info-->
                            <!--begin::Subtitle-->
                            <span class="text-gray-400 pt-1 fw-semibold fs-6">{{ __('order.remaining_amount') }}</span>
                            <!--end::Subtitle-->
                        </div>
                        <!--end::Title-->
                    </div>
                    <!--end::Header-->
                    <!--begin::Card body-->
                    <div class="card-body pt-2 pb-4 d-flex align-items-center">
                        <div class="d-flex flex-center me-5 pt-2">
                            @if($order->remaining_amount > 0)
                                <i class="ki-duotone ki-arrow-up fs-2tx text-warning">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            @else
                                <i class="ki-duotone ki-check-circle fs-2tx text-success">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            @endif
                        </div>
                        <div class="d-flex flex-column content-justify-center w-100">
                            <div class="d-flex fs-6 fw-semibold align-items-center">
                                <div class="text-gray-500 flex-grow-1 me-4">{{ __('order.outstanding') }}</div>
                                <div class="fw-bolder text-gray-700 text-xxl-end">
                                    @if($order->remaining_amount > 0)
                                        <span class="text-warning">{{ __('order.pending') }}</span>
                                    @else
                                        <span class="text-success">{{ __('order.completed') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Card body-->
                </div>
                <!--end::Card widget-->
            </div>
            <!--end::Col-->
        </div>
        <!--end::Row-->
    </div>
    <!--end::Card body-->
</div>
<!--end::Card-->

<!--begin::Card-->
<div class="card mb-5 mb-xl-10">
    <!--begin::Card header-->
    <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse" data-bs-target="#kt_account_payment_details" aria-expanded="true" aria-controls="kt_account_payment_details">
        <!--begin::Card title-->
        <div class="card-title m-0">
            <h3 class="fw-bold m-0">{{ __('order.payment_details') }}</h3>
        </div>
        <!--end::Card title-->
    </div>
    <!--begin::Card header-->
    <!--begin::Content-->
    <div id="kt_account_payment_details" class="collapse show">
        <!--begin::Card body-->
        <div class="card-body border-top p-9">
            <!--begin::Row-->
            <div class="row mb-6">
                <!--begin::Label-->
                <label class="col-lg-4 col-form-label fw-semibold fs-6">{{ __('order.payment_status') }}</label>
                <!--end::Label-->
                <!--begin::Col-->
                <div class="col-lg-8 fv-row">
                    <span class="badge badge-light-{{ $order->status_info['payment_status']['class'] }}">
                        {{ $order->status_info['payment_status']['label'] }}
                    </span>
                </div>
                <!--end::Col-->
            </div>
            <!--end::Row-->
            <!--begin::Row-->
            <div class="row mb-6">
                <!--begin::Label-->
                <label class="col-lg-4 col-form-label fw-semibold fs-6">{{ __('order.payment_method') }}</label>
                <!--end::Label-->
                <!--begin::Col-->
                <div class="col-lg-8 fv-row">
                    <span class="fw-semibold text-gray-600 fs-6">{{ __('order.' . ($order->payment_method ?? 'cash')) }}</span>
                </div>
                <!--end::Col-->
            </div>
            <!--end::Row-->
            @if($order->payment_date)
            <!--begin::Row-->
            <div class="row mb-6">
                <!--begin::Label-->
                <label class="col-lg-4 col-form-label fw-semibold fs-6">{{ __('order.payment_date') }}</label>
                <!--end::Label-->
                <!--begin::Col-->
                <div class="col-lg-8 fv-row">
                    <span class="fw-semibold text-gray-600 fs-6">{{ $order->payment_date->format('d/m/Y H:i') }}</span>
                </div>
                <!--end::Col-->
            </div>
            <!--end::Row-->
            @endif
            @if($order->due_date)
            <!--begin::Row-->
            <div class="row mb-6">
                <!--begin::Label-->
                <label class="col-lg-4 col-form-label fw-semibold fs-6">{{ __('order.due_date') }}</label>
                <!--end::Label-->
                <!--begin::Col-->
                <div class="col-lg-8 fv-row">
                    <span class="fw-semibold text-gray-600 fs-6">{{ $order->due_date->format('d/m/Y') }}</span>
                    @if($order->due_date->isPast() && $order->remaining_amount > 0)
                        <span class="badge badge-light-danger ms-2">{{ __('order.overdue') }}</span>
                    @endif
                </div>
                <!--end::Col-->
            </div>
            <!--end::Row-->
            @endif
            @if($order->payment_notes)
            <!--begin::Row-->
            <div class="row mb-6">
                <!--begin::Label-->
                <label class="col-lg-4 col-form-label fw-semibold fs-6">{{ __('order.payment_notes') }}</label>
                <!--end::Label-->
                <!--begin::Col-->
                <div class="col-lg-8 fv-row">
                    <span class="fw-semibold text-gray-600 fs-6">{{ $order->payment_notes }}</span>
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
            <h3 class="fw-bold m-0">{{ __('order.payment_progress') }}</h3>
        </div>
        <!--end::Card title-->
    </div>
    <!--begin::Card header-->
    <!--begin::Card body-->
    <div class="card-body border-top p-9">
        <!--begin::Progress-->
        <div class="d-flex flex-column">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="fw-semibold text-gray-600 fs-6">{{ __('order.payment_completion') }}</span>
                <span class="fw-bold text-gray-800 fs-6">{{ $order->payment_percentage }}%</span>
            </div>
            <div class="progress h-8px bg-light-primary">
                <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $order->payment_percentage }}%" aria-valuenow="{{ $order->payment_percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </div>
        <!--end::Progress-->

        <!--begin::Payment breakdown-->
        <div class="mt-8">
            <div class="d-flex align-items-center justify-content-between py-3 border-bottom">
                <span class="fw-semibold text-gray-600 fs-6">{{ __('order.order_total') }}</span>
                <span class="fw-bold text-gray-800 fs-6">{{ number_format($order->final_amount, 0, ',', '.') }}₫</span>
            </div>
            <div class="d-flex align-items-center justify-content-between py-3 border-bottom">
                <span class="fw-semibold text-success fs-6">{{ __('order.amount_paid') }}</span>
                <span class="fw-bold text-success fs-6">{{ number_format($order->amount_paid, 0, ',', '.') }}₫</span>
            </div>
            <div class="d-flex align-items-center justify-content-between py-3">
                <span class="fw-semibold {{ $order->remaining_amount > 0 ? 'text-warning' : 'text-success' }} fs-6">{{ __('order.remaining_balance') }}</span>
                <span class="fw-bold {{ $order->remaining_amount > 0 ? 'text-warning' : 'text-success' }} fs-6">{{ number_format($order->remaining_amount, 0, ',', '.') }}₫</span>
            </div>
        </div>
        <!--end::Payment breakdown-->

        <!--begin::Payment status notice-->
        @if($order->payment_status === 'paid')
            <div class="notice d-flex bg-light-success rounded border-success border border-dashed mt-6 p-6">
                <i class="ki-duotone ki-check-circle fs-2tx text-success me-4"></i>
                <div class="d-flex flex-stack flex-grow-1">
                    <div class="fw-semibold">
                        <h4 class="text-gray-900 fw-bold">{{ __('order.payment_completed') }}</h4>
                        <div class="fs-6 text-gray-700">{{ __('order.payment_completed_description') }}</div>
                    </div>
                </div>
            </div>
        @elseif($order->payment_status === 'partial')
            <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed mt-6 p-6">
                <i class="ki-duotone ki-warning-2 fs-2tx text-warning me-4"></i>
                <div class="d-flex flex-stack flex-grow-1">
                    <div class="fw-semibold">
                        <h4 class="text-gray-900 fw-bold">{{ __('order.partial_payment') }}</h4>
                        <div class="fs-6 text-gray-700">{{ __('order.partial_payment_description') }}</div>
                    </div>
                </div>
            </div>
        @else
            <div class="notice d-flex bg-light-danger rounded border-danger border border-dashed mt-6 p-6">
                <i class="ki-duotone ki-information fs-2tx text-danger me-4"></i>
                <div class="d-flex flex-stack flex-grow-1">
                    <div class="fw-semibold">
                        <h4 class="text-gray-900 fw-bold">{{ __('order.payment_pending') }}</h4>
                        <div class="fs-6 text-gray-700">{{ __('order.payment_pending_description') }}</div>
                    </div>
                </div>
            </div>
        @endif
        <!--end::Payment status notice-->
    </div>
    <!--end::Card body-->
</div>
<!--end::Card-->
