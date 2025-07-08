<!--begin::Card-->
<div class="card mb-5 mb-xl-10">
    <!--begin::Card header-->
    <div class="card-header border-0">
        <!--begin::Card title-->
        <div class="card-title m-0">
            <h3 class="fw-bold m-0">{{ __('product.pricing_information') }}</h3>
        </div>
        <!--end::Card title-->
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
                                <span class="fs-2hx fw-bold text-dark me-2 lh-1 ls-n2">{{ number_format($product->cost_price, 0, ',', '.') }}</span>
                                <!--end::Amount-->
                            </div>
                            <!--end::Info-->
                            <!--begin::Subtitle-->
                            <span class="text-gray-400 pt-1 fw-semibold fs-6">{{ __('product.cost_price') }}</span>
                            <!--end::Subtitle-->
                        </div>
                        <!--end::Title-->
                    </div>
                    <!--end::Header-->
                    <!--begin::Card body-->
                    <div class="card-body pt-2 pb-4 d-flex align-items-center">
                        <div class="d-flex flex-center me-5 pt-2">
                            <i class="ki-duotone ki-price-tag fs-2tx text-info">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                            </i>
                        </div>
                        <div class="d-flex flex-column content-justify-center w-100">
                            <div class="d-flex fs-6 fw-semibold align-items-center">
                                <div class="text-gray-500 flex-grow-1 me-4">{{ __('product.base_cost') }}</div>
                                <div class="fw-bolder text-gray-700 text-xxl-end">{{ __('product.per_unit') }}</div>
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
                                <span class="fs-2hx fw-bold text-dark me-2 lh-1 ls-n2">{{ number_format($product->sale_price, 0, ',', '.') }}</span>
                                <!--end::Amount-->
                            </div>
                            <!--end::Info-->
                            <!--begin::Subtitle-->
                            <span class="text-gray-400 pt-1 fw-semibold fs-6">{{ __('product.sale_price') }}</span>
                            <!--end::Subtitle-->
                        </div>
                        <!--end::Title-->
                    </div>
                    <!--end::Header-->
                    <!--begin::Card body-->
                    <div class="card-body pt-2 pb-4 d-flex align-items-center">
                        <div class="d-flex flex-center me-5 pt-2">
                            <i class="ki-duotone ki-dollar fs-2tx text-success">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                            </i>
                        </div>
                        <div class="d-flex flex-column content-justify-center w-100">
                            <div class="d-flex fs-6 fw-semibold align-items-center">
                                <div class="text-gray-500 flex-grow-1 me-4">{{ __('product.selling_price') }}</div>
                                <div class="fw-bolder text-gray-700 text-xxl-end">{{ __('product.per_unit') }}</div>
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
                                <!--begin::Amount-->
                                <span class="fs-2hx fw-bold text-dark me-2 lh-1 ls-n2">{{ number_format($product->profit_margin, 1) }}%</span>
                                <!--end::Amount-->
                            </div>
                            <!--end::Info-->
                            <!--begin::Subtitle-->
                            <span class="text-gray-400 pt-1 fw-semibold fs-6">{{ __('product.profit_margin') }}</span>
                            <!--end::Subtitle-->
                        </div>
                        <!--end::Title-->
                    </div>
                    <!--end::Header-->
                    <!--begin::Card body-->
                    <div class="card-body pt-2 pb-4 d-flex align-items-center">
                        <div class="d-flex flex-center me-5 pt-2">
                            @if($product->profit_margin > 0)
                                <i class="ki-duotone ki-arrow-up fs-2tx text-success">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            @else
                                <i class="ki-duotone ki-arrow-down fs-2tx text-danger">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            @endif
                        </div>
                        <div class="d-flex flex-column content-justify-center w-100">
                            <div class="d-flex fs-6 fw-semibold align-items-center">
                                <div class="text-gray-500 flex-grow-1 me-4">{{ __('product.margin_percentage') }}</div>
                                <div class="fw-bolder text-gray-700 text-xxl-end">
                                    @if($product->profit_margin > 0)
                                        <span class="text-success">{{ __('product.profitable') }}</span>
                                    @else
                                        <span class="text-danger">{{ __('product.loss') }}</span>
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
    <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse" data-bs-target="#kt_account_pricing_details" aria-expanded="true" aria-controls="kt_account_pricing_details">
        <!--begin::Card title-->
        <div class="card-title m-0">
            <h3 class="fw-bold m-0">{{ __('product.pricing_details') }}</h3>
        </div>
        <!--end::Card title-->
    </div>
    <!--begin::Card header-->
    <!--begin::Content-->
    <div id="kt_account_pricing_details" class="collapse show">
        <!--begin::Card body-->
        <div class="card-body border-top p-9">
            <!--begin::Row-->
            <div class="row mb-6">
                <!--begin::Label-->
                <label class="col-lg-4 col-form-label fw-semibold fs-6">{{ __('product.cost_price') }}</label>
                <!--end::Label-->
                <!--begin::Col-->
                <div class="col-lg-8 fv-row">
                    <span class="fw-semibold text-gray-800 fs-6">{{ number_format($product->cost_price, 0, ',', '.') }}₫</span>
                    <div class="text-muted fs-7">{{ __('product.cost_price_description') }}</div>
                </div>
                <!--end::Col-->
            </div>
            <!--end::Row-->
            <!--begin::Row-->
            <div class="row mb-6">
                <!--begin::Label-->
                <label class="col-lg-4 col-form-label fw-semibold fs-6">{{ __('product.sale_price') }}</label>
                <!--end::Label-->
                <!--begin::Col-->
                <div class="col-lg-8 fv-row">
                    <span class="fw-semibold text-gray-800 fs-6">{{ number_format($product->sale_price, 0, ',', '.') }}₫</span>
                    <div class="text-muted fs-7">{{ __('product.sale_price_description') }}</div>
                </div>
                <!--end::Col-->
            </div>
            <!--end::Row-->
            <!--begin::Row-->
            <div class="row mb-6">
                <!--begin::Label-->
                <label class="col-lg-4 col-form-label fw-semibold fs-6">{{ __('product.regular_price') }}</label>
                <!--end::Label-->
                <!--begin::Col-->
                <div class="col-lg-8 fv-row">
                    <span class="fw-semibold text-gray-600 fs-6">{{ $product->regular_price ? number_format($product->regular_price, 0, ',', '.') . '₫' : __('common.not_set') }}</span>
                    <div class="text-muted fs-7">{{ __('product.regular_price_description') }}</div>
                </div>
                <!--end::Col-->
            </div>
            <!--end::Row-->
            <!--begin::Row-->
            <div class="row mb-6">
                <!--begin::Label-->
                <label class="col-lg-4 col-form-label fw-semibold fs-6">{{ __('product.profit_per_unit') }}</label>
                <!--end::Label-->
                <!--begin::Col-->
                <div class="col-lg-8 fv-row">
                    @php
                        $profitPerUnit = $product->sale_price - $product->cost_price;
                    @endphp
                    <span class="fw-semibold fs-6 {{ $profitPerUnit > 0 ? 'text-success' : 'text-danger' }}">
                        {{ number_format($profitPerUnit, 0, ',', '.') }}₫
                    </span>
                    <div class="text-muted fs-7">{{ __('product.profit_per_unit_description') }}</div>
                </div>
                <!--end::Col-->
            </div>
            <!--end::Row-->
            <!--begin::Row-->
            <div class="row mb-6">
                <!--begin::Label-->
                <label class="col-lg-4 col-form-label fw-semibold fs-6">{{ __('product.profit_margin') }}</label>
                <!--end::Label-->
                <!--begin::Col-->
                <div class="col-lg-8 fv-row">
                    <span class="fw-semibold fs-6 {{ $product->profit_margin > 0 ? 'text-success' : 'text-danger' }}">
                        {{ number_format($product->profit_margin, 2) }}%
                    </span>
                    <div class="text-muted fs-7">{{ __('product.profit_margin_description') }}</div>
                </div>
                <!--end::Col-->
            </div>
            <!--end::Row-->
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
            <h3 class="fw-bold m-0">{{ __('product.pricing_analysis') }}</h3>
        </div>
        <!--end::Card title-->
    </div>
    <!--begin::Card header-->
    <!--begin::Card body-->
    <div class="card-body border-top p-9">
        <!--begin::Notice-->
        @if($product->profit_margin < 10)
            <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed mb-9 p-6">
                <!--begin::Icon-->
                <i class="ki-duotone ki-warning-2 fs-2tx text-warning me-4"></i>
                <!--end::Icon-->
                <!--begin::Wrapper-->
                <div class="d-flex flex-stack flex-grow-1">
                    <!--begin::Content-->
                    <div class="fw-semibold">
                        <h4 class="text-gray-900 fw-bold">{{ __('product.low_margin_warning') }}</h4>
                        <div class="fs-6 text-gray-700">{{ __('product.low_margin_description') }}</div>
                    </div>
                    <!--end::Content-->
                </div>
                <!--end::Wrapper-->
            </div>
        @elseif($product->profit_margin > 50)
            <div class="notice d-flex bg-light-success rounded border-success border border-dashed mb-9 p-6">
                <!--begin::Icon-->
                <i class="ki-duotone ki-check-circle fs-2tx text-success me-4"></i>
                <!--end::Icon-->
                <!--begin::Wrapper-->
                <div class="d-flex flex-stack flex-grow-1">
                    <!--begin::Content-->
                    <div class="fw-semibold">
                        <h4 class="text-gray-900 fw-bold">{{ __('product.high_margin_notice') }}</h4>
                        <div class="fs-6 text-gray-700">{{ __('product.high_margin_description') }}</div>
                    </div>
                    <!--end::Content-->
                </div>
                <!--end::Wrapper-->
            </div>
        @else
            <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed mb-9 p-6">
                <!--begin::Icon-->
                <i class="ki-duotone ki-information fs-2tx text-primary me-4"></i>
                <!--end::Icon-->
                <!--begin::Wrapper-->
                <div class="d-flex flex-stack flex-grow-1">
                    <!--begin::Content-->
                    <div class="fw-semibold">
                        <h4 class="text-gray-900 fw-bold">{{ __('product.healthy_margin_notice') }}</h4>
                        <div class="fs-6 text-gray-700">{{ __('product.healthy_margin_description') }}</div>
                    </div>
                    <!--end::Content-->
                </div>
                <!--end::Wrapper-->
            </div>
        @endif
        <!--end::Notice-->

        <!--begin::Pricing recommendations-->
        <div class="d-flex flex-column gap-5">
            <div class="d-flex align-items-center">
                <span class="fw-semibold text-gray-800 flex-grow-1">{{ __('product.recommended_min_price') }}</span>
                <span class="fw-bold text-gray-600">{{ number_format($product->cost_price * 1.2, 0, ',', '.') }}₫</span>
            </div>
            <div class="d-flex align-items-center">
                <span class="fw-semibold text-gray-800 flex-grow-1">{{ __('product.recommended_max_price') }}</span>
                <span class="fw-bold text-gray-600">{{ number_format($product->cost_price * 2, 0, ',', '.') }}₫</span>
            </div>
            <div class="d-flex align-items-center">
                <span class="fw-semibold text-gray-800 flex-grow-1">{{ __('product.break_even_price') }}</span>
                <span class="fw-bold text-gray-600">{{ number_format($product->cost_price, 0, ',', '.') }}₫</span>
            </div>
        </div>
        <!--end::Pricing recommendations-->
    </div>
    <!--end::Card body-->
</div>
<!--end::Card-->
