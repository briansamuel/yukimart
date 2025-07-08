<!--begin::Card-->
<div class="card mb-5 mb-xl-10">
    <!--begin::Card header-->
    <div class="card-header border-0">
        <!--begin::Card title-->
        <div class="card-title m-0">
            <h3 class="fw-bold m-0">{{ __('product.stock_overview') }}</h3>
        </div>
        <!--end::Card title-->
        <!--begin::Action-->
        <div class="card-toolbar">
            <button type="button" class="btn btn-sm btn-light-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_adjust_stock">
                <i class="ki-duotone ki-plus fs-3"></i>
                {{ __('product.adjust_stock') }}
            </button>
        </div>
        <!--end::Action-->
    </div>
    <!--begin::Card header-->
    <!--begin::Card body-->
    <div class="card-body border-top p-9">
        <!--begin::Row-->
        <div class="row g-6 g-xl-9 mb-6 mb-xl-9">
            <!--begin::Col-->
            <div class="col-md-6 col-lg-4 col-xl-3">
                <!--begin::Card widget 4-->
                <div class="card card-flush h-md-50 mb-5 mb-xl-10">
                    <!--begin::Header-->
                    <div class="card-header pt-5">
                        <!--begin::Title-->
                        <div class="card-title d-flex flex-column">
                            <!--begin::Info-->
                            <div class="d-flex align-items-center">
                                <!--begin::Currency-->
                                <span class="fs-4 fw-semibold text-gray-400 me-1 align-self-start">{{ __('product.units') }}</span>
                                <!--end::Currency-->
                                <!--begin::Amount-->
                                <span class="fs-2hx fw-bold text-dark me-2 lh-1 ls-n2">{{ number_format($product->stock_quantity) }}</span>
                                <!--end::Amount-->
                            </div>
                            <!--end::Info-->
                            <!--begin::Subtitle-->
                            <span class="text-gray-400 pt-1 fw-semibold fs-6">{{ __('product.current_stock') }}</span>
                            <!--end::Subtitle-->
                        </div>
                        <!--end::Title-->
                    </div>
                    <!--end::Header-->
                    <!--begin::Card body-->
                    <div class="card-body pt-2 pb-4 d-flex align-items-center">
                        <!--begin::Chart-->
                        <div class="d-flex flex-center me-5 pt-2">
                            <div id="kt_card_widget_4_chart" style="min-width: 70px; min-height: 70px" data-kt-size="70" data-kt-line="11"></div>
                        </div>
                        <!--end::Chart-->
                        <!--begin::Labels-->
                        <div class="d-flex flex-column content-justify-center w-100">
                            <!--begin::Label-->
                            <div class="d-flex fs-6 fw-semibold align-items-center">
                                <!--begin::Bullet-->
                                <div class="bullet w-8px h-6px rounded-2 bg-danger me-3"></div>
                                <!--end::Bullet-->
                                <!--begin::Label-->
                                <div class="text-gray-500 flex-grow-1 me-4">{{ __('product.reserved') }}</div>
                                <!--end::Label-->
                                <!--begin::Stats-->
                                <div class="fw-bolder text-gray-700 text-xxl-end">{{ number_format($product->reserved_quantity) }}</div>
                                <!--end::Stats-->
                            </div>
                            <!--end::Label-->
                            <!--begin::Label-->
                            <div class="d-flex fs-6 fw-semibold align-items-center my-3">
                                <!--begin::Bullet-->
                                <div class="bullet w-8px h-6px rounded-2 bg-primary me-3"></div>
                                <!--end::Bullet-->
                                <!--begin::Label-->
                                <div class="text-gray-500 flex-grow-1 me-4">{{ __('product.available') }}</div>
                                <!--end::Label-->
                                <!--begin::Stats-->
                                <div class="fw-bolder text-gray-700 text-xxl-end">{{ number_format($product->available_quantity) }}</div>
                                <!--end::Stats-->
                            </div>
                            <!--end::Label-->
                        </div>
                        <!--end::Labels-->
                    </div>
                    <!--end::Card body-->
                </div>
                <!--end::Card widget 4-->
            </div>
            <!--end::Col-->
            <!--begin::Col-->
            <div class="col-md-6 col-lg-4 col-xl-3">
                <!--begin::Card widget 5-->
                <div class="card card-flush h-md-50 mb-5 mb-xl-10">
                    <!--begin::Header-->
                    <div class="card-header pt-5">
                        <!--begin::Title-->
                        <div class="card-title d-flex flex-column">
                            <!--begin::Info-->
                            <div class="d-flex align-items-center">
                                <!--begin::Amount-->
                                <span class="fs-2hx fw-bold text-dark me-2 lh-1 ls-n2">
                                    @if($product->stock_status === 'in_stock')
                                        <i class="ki-duotone ki-check-circle fs-1 text-success"></i>
                                    @elseif($product->stock_status === 'low_stock')
                                        <i class="ki-duotone ki-warning-2 fs-1 text-warning"></i>
                                    @else
                                        <i class="ki-duotone ki-cross-circle fs-1 text-danger"></i>
                                    @endif
                                </span>
                                <!--end::Amount-->
                            </div>
                            <!--end::Info-->
                            <!--begin::Subtitle-->
                            <span class="text-gray-400 pt-1 fw-semibold fs-6">{{ __('product.stock_status') }}</span>
                            <!--end::Subtitle-->
                        </div>
                        <!--end::Title-->
                    </div>
                    <!--end::Header-->
                    <!--begin::Card body-->
                    <div class="card-body pt-2 pb-4 d-flex align-items-center">
                        <div class="d-flex flex-column content-justify-center w-100">
                            <!--begin::Label-->
                            <div class="d-flex fs-6 fw-semibold align-items-center">
                                <!--begin::Label-->
                                <div class="text-gray-500 flex-grow-1 me-4">{{ __('product.status') }}</div>
                                <!--end::Label-->
                                <!--begin::Stats-->
                                <div class="fw-bolder text-gray-700 text-xxl-end">
                                    @if($product->stock_status === 'in_stock')
                                        <span class="badge badge-light-success">{{ __('product.in_stock') }}</span>
                                    @elseif($product->stock_status === 'low_stock')
                                        <span class="badge badge-light-warning">{{ __('product.low_stock') }}</span>
                                    @else
                                        <span class="badge badge-light-danger">{{ __('product.out_of_stock') }}</span>
                                    @endif
                                </div>
                                <!--end::Stats-->
                            </div>
                            <!--end::Label-->
                            <!--begin::Label-->
                            <div class="d-flex fs-6 fw-semibold align-items-center my-3">
                                <!--begin::Label-->
                                <div class="text-gray-500 flex-grow-1 me-4">{{ __('product.reorder_point') }}</div>
                                <!--end::Label-->
                                <!--begin::Stats-->
                                <div class="fw-bolder text-gray-700 text-xxl-end">{{ number_format($product->reorder_point) }}</div>
                                <!--end::Stats-->
                            </div>
                            <!--end::Label-->
                        </div>
                    </div>
                    <!--end::Card body-->
                </div>
                <!--end::Card widget 5-->
            </div>
            <!--end::Col-->
            <!--begin::Col-->
            <div class="col-md-6 col-lg-4 col-xl-3">
                <!--begin::Card widget 6-->
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
                                <span class="fs-2hx fw-bold text-dark me-2 lh-1 ls-n2">{{ number_format($product->stock_value, 0, ',', '.') }}</span>
                                <!--end::Amount-->
                            </div>
                            <!--end::Info-->
                            <!--begin::Subtitle-->
                            <span class="text-gray-400 pt-1 fw-semibold fs-6">{{ __('product.stock_value') }}</span>
                            <!--end::Subtitle-->
                        </div>
                        <!--end::Title-->
                    </div>
                    <!--end::Header-->
                    <!--begin::Card body-->
                    <div class="card-body pt-2 pb-4 d-flex align-items-center">
                        <div class="d-flex flex-column content-justify-center w-100">
                            <!--begin::Label-->
                            <div class="d-flex fs-6 fw-semibold align-items-center">
                                <!--begin::Label-->
                                <div class="text-gray-500 flex-grow-1 me-4">{{ __('product.cost_basis') }}</div>
                                <!--end::Label-->
                                <!--begin::Stats-->
                                <div class="fw-bolder text-gray-700 text-xxl-end">{{ number_format($product->cost_price, 0, ',', '.') }}₫</div>
                                <!--end::Stats-->
                            </div>
                            <!--end::Label-->
                        </div>
                    </div>
                    <!--end::Card body-->
                </div>
                <!--end::Card widget 6-->
            </div>
            <!--end::Col-->
            <!--begin::Col-->
            <div class="col-md-6 col-lg-4 col-xl-3">
                <!--begin::Card widget 7-->
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
                                <span class="fs-2hx fw-bold text-dark me-2 lh-1 ls-n2">{{ number_format($product->retail_value, 0, ',', '.') }}</span>
                                <!--end::Amount-->
                            </div>
                            <!--end::Info-->
                            <!--begin::Subtitle-->
                            <span class="text-gray-400 pt-1 fw-semibold fs-6">{{ __('product.retail_value') }}</span>
                            <!--end::Subtitle-->
                        </div>
                        <!--end::Title-->
                    </div>
                    <!--end::Header-->
                    <!--begin::Card body-->
                    <div class="card-body pt-2 pb-4 d-flex align-items-center">
                        <div class="d-flex flex-column content-justify-center w-100">
                            <!--begin::Label-->
                            <div class="d-flex fs-6 fw-semibold align-items-center">
                                <!--begin::Label-->
                                <div class="text-gray-500 flex-grow-1 me-4">{{ __('product.sale_price') }}</div>
                                <!--end::Label-->
                                <!--begin::Stats-->
                                <div class="fw-bolder text-gray-700 text-xxl-end">{{ number_format($product->sale_price, 0, ',', '.') }}₫</div>
                                <!--end::Stats-->
                            </div>
                            <!--end::Label-->
                        </div>
                    </div>
                    <!--end::Card body-->
                </div>
                <!--end::Card widget 7-->
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
    <div class="card-header border-0">
        <!--begin::Card title-->
        <div class="card-title m-0">
            <h3 class="fw-bold m-0">{{ __('product.recent_transactions') }}</h3>
        </div>
        <!--end::Card title-->
    </div>
    <!--begin::Card header-->
    <!--begin::Card body-->
    <div class="card-body border-top p-9">
        @if($product->inventoryTransactions && $product->inventoryTransactions->count() > 0)
            <!--begin::Table-->
            <div class="table-responsive">
                <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                    <!--begin::Table head-->
                    <thead>
                        <tr class="fw-bold text-muted">
                            <th class="min-w-150px">{{ __('product.date') }}</th>
                            <th class="min-w-140px">{{ __('product.type') }}</th>
                            <th class="min-w-120px">{{ __('product.quantity') }}</th>
                            <th class="min-w-120px">{{ __('product.reference') }}</th>
                            <th class="min-w-100px">{{ __('product.notes') }}</th>
                        </tr>
                    </thead>
                    <!--end::Table head-->
                    <!--begin::Table body-->
                    <tbody>
                        @foreach($product->inventoryTransactions as $transaction)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="d-flex justify-content-start flex-column">
                                        <span class="text-dark fw-bold text-hover-primary fs-6">{{ $transaction->created_at->format('d/m/Y') }}</span>
                                        <span class="text-muted fw-semibold text-muted d-block fs-7">{{ $transaction->created_at->format('H:i') }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($transaction->type === 'in')
                                    <span class="badge badge-light-success">{{ __('product.stock_in') }}</span>
                                @elseif($transaction->type === 'out')
                                    <span class="badge badge-light-danger">{{ __('product.stock_out') }}</span>
                                @else
                                    <span class="badge badge-light-warning">{{ __('product.adjustment') }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="text-dark fw-bold d-block fs-6">
                                    @if($transaction->type === 'in')
                                        +{{ number_format($transaction->quantity) }}
                                    @else
                                        -{{ number_format($transaction->quantity) }}
                                    @endif
                                </span>
                            </td>
                            <td>
                                <span class="text-muted fw-semibold text-muted d-block fs-7">{{ $transaction->reference ?: '-' }}</span>
                            </td>
                            <td>
                                <span class="text-muted fw-semibold text-muted d-block fs-7">{{ $transaction->notes ?: '-' }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <!--end::Table body-->
                </table>
            </div>
            <!--end::Table-->
        @else
            <div class="text-center py-10">
                <div class="text-muted">{{ __('product.no_transactions') }}</div>
            </div>
        @endif
    </div>
    <!--end::Card body-->
</div>
<!--end::Card-->
