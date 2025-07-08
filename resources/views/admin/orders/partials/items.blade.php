<!--begin::Card-->
<div class="card mb-5 mb-xl-10">
    <!--begin::Card header-->
    <div class="card-header border-0">
        <!--begin::Card title-->
        <div class="card-title m-0">
            <h3 class="fw-bold m-0">{{ __('order.order_items') }}</h3>
        </div>
        <!--end::Card title-->
        <!--begin::Card toolbar-->
        <div class="card-toolbar">
            <div class="d-flex align-items-center">
                <span class="badge badge-light-primary fs-7 fw-bold">
                    {{ $order->orderItems->count() }} {{ __('order.items') }}
                </span>
            </div>
        </div>
        <!--end::Card toolbar-->
    </div>
    <!--begin::Card header-->
    <!--begin::Card body-->
    <div class="card-body border-top p-9">
        @if($order->orderItems && $order->orderItems->count() > 0)
            <!--begin::Table-->
            <div class="table-responsive">
                <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                    <!--begin::Table head-->
                    <thead>
                        <tr class="fw-bold text-muted">
                            <th class="min-w-200px">{{ __('order.product') }}</th>
                            <th class="min-w-100px text-center">{{ __('order.quantity') }}</th>
                            <th class="min-w-120px text-end">{{ __('order.unit_price') }}</th>
                            <th class="min-w-120px text-end">{{ __('order.total_price') }}</th>
                            <th class="min-w-100px text-center">{{ __('order.stock_status') }}</th>
                        </tr>
                    </thead>
                    <!--end::Table head-->
                    <!--begin::Table body-->
                    <tbody>
                        @foreach($order->orderItems as $item)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <!--begin::Avatar-->
                                    <div class="symbol symbol-45px me-5">
                                        @if($item->product && $item->product->product_thumbnail)
                                            <img src="{{ asset($item->product->product_thumbnail) }}" alt="{{ $item->product->product_name }}" />
                                        @else
                                            <div class="symbol-label bg-light-primary text-primary fw-bold">
                                                {{ $item->product ? strtoupper(substr($item->product->product_name, 0, 1)) : 'P' }}
                                            </div>
                                        @endif
                                    </div>
                                    <!--end::Avatar-->
                                    <!--begin::Product details-->
                                    <div class="d-flex justify-content-start flex-column">
                                        <span class="text-dark fw-bold text-hover-primary fs-6">
                                            {{ $item->product->product_name ?? __('order.product_not_found') }}
                                        </span>
                                        <span class="text-muted fw-semibold text-muted d-block fs-7">
                                            {{ __('order.sku') }}: {{ $item->product->sku ?? 'N/A' }}
                                        </span>
                                    </div>
                                    <!--end::Product details-->
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="text-dark fw-bold d-block fs-6">{{ number_format($item->quantity) }}</span>
                            </td>
                            <td class="text-end">
                                <span class="text-dark fw-bold d-block fs-6">{{ number_format($item->unit_price, 0, ',', '.') }}₫</span>
                            </td>
                            <td class="text-end">
                                <span class="text-dark fw-bold d-block fs-6">{{ number_format($item->total_price, 0, ',', '.') }}₫</span>
                            </td>
                            <td class="text-center">
                                @if($item->product && $item->product->inventory)
                                    @php
                                        $stockQuantity = $item->product->inventory->quantity;
                                        $stockStatus = $stockQuantity > 10 ? 'in_stock' : ($stockQuantity > 0 ? 'low_stock' : 'out_of_stock');
                                    @endphp
                                    @if($stockStatus === 'in_stock')
                                        <span class="badge badge-light-success">{{ __('order.in_stock') }} ({{ $stockQuantity }})</span>
                                    @elseif($stockStatus === 'low_stock')
                                        <span class="badge badge-light-warning">{{ __('order.low_stock') }} ({{ $stockQuantity }})</span>
                                    @else
                                        <span class="badge badge-light-danger">{{ __('order.out_of_stock') }}</span>
                                    @endif
                                @else
                                    <span class="badge badge-light-secondary">{{ __('order.no_stock_info') }}</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <!--end::Table body-->
                </table>
            </div>
            <!--end::Table-->

            <!--begin::Order totals-->
            <div class="d-flex flex-stack bg-light rounded p-6 mt-8">
                <div class="d-flex flex-column">
                    <div class="d-flex align-items-center fs-6 fw-semibold mb-2">
                        <span class="text-gray-500 flex-grow-1 me-4">{{ __('order.subtotal') }}:</span>
                        <span class="text-gray-800">{{ number_format($order->total_amount, 0, ',', '.') }}₫</span>
                    </div>
                    @if($order->discount_amount > 0)
                    <div class="d-flex align-items-center fs-6 fw-semibold mb-2">
                        <span class="text-gray-500 flex-grow-1 me-4">{{ __('order.discount') }}:</span>
                        <span class="text-danger">-{{ number_format($order->discount_amount, 0, ',', '.') }}₫</span>
                    </div>
                    @endif
                    @if($order->shipping_fee > 0)
                    <div class="d-flex align-items-center fs-6 fw-semibold mb-2">
                        <span class="text-gray-500 flex-grow-1 me-4">{{ __('order.shipping_fee') }}:</span>
                        <span class="text-gray-800">{{ number_format($order->shipping_fee, 0, ',', '.') }}₫</span>
                    </div>
                    @endif
                    @if($order->tax_amount > 0)
                    <div class="d-flex align-items-center fs-6 fw-semibold mb-2">
                        <span class="text-gray-500 flex-grow-1 me-4">{{ __('order.tax') }}:</span>
                        <span class="text-gray-800">{{ number_format($order->tax_amount, 0, ',', '.') }}₫</span>
                    </div>
                    @endif
                    <div class="separator my-3"></div>
                    <div class="d-flex align-items-center fs-4 fw-bold">
                        <span class="text-gray-800 flex-grow-1 me-4">{{ __('order.total') }}:</span>
                        <span class="text-primary">{{ number_format($order->final_amount, 0, ',', '.') }}₫</span>
                    </div>
                </div>
            </div>
            <!--end::Order totals-->
        @else
            <div class="text-center py-10">
                <div class="text-muted">{{ __('order.no_items') }}</div>
            </div>
        @endif
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
            <h3 class="fw-bold m-0">{{ __('order.profit_analysis') }}</h3>
        </div>
        <!--end::Card title-->
    </div>
    <!--begin::Card header-->
    <!--begin::Card body-->
    <div class="card-body border-top p-9">
        @if(isset($order->profit_info))
            <!--begin::Row-->
            <div class="row g-6 g-xl-9 mb-6 mb-xl-9">
                <!--begin::Col-->
                <div class="col-md-6 col-lg-3">
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
                                    <span class="fs-2hx fw-bold text-dark me-2 lh-1 ls-n2">{{ number_format($order->profit_info['total_cost'], 0, ',', '.') }}</span>
                                    <!--end::Amount-->
                                </div>
                                <!--end::Info-->
                                <!--begin::Subtitle-->
                                <span class="text-gray-400 pt-1 fw-semibold fs-6">{{ __('order.total_cost') }}</span>
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
                        </div>
                        <!--end::Card body-->
                    </div>
                    <!--end::Card widget-->
                </div>
                <!--end::Col-->
                <!--begin::Col-->
                <div class="col-md-6 col-lg-3">
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
                                    <span class="fs-2hx fw-bold text-dark me-2 lh-1 ls-n2">{{ number_format($order->profit_info['total_revenue'], 0, ',', '.') }}</span>
                                    <!--end::Amount-->
                                </div>
                                <!--end::Info-->
                                <!--begin::Subtitle-->
                                <span class="text-gray-400 pt-1 fw-semibold fs-6">{{ __('order.total_revenue') }}</span>
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
                        </div>
                        <!--end::Card body-->
                    </div>
                    <!--end::Card widget-->
                </div>
                <!--end::Col-->
                <!--begin::Col-->
                <div class="col-md-6 col-lg-3">
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
                                    <span class="fs-2hx fw-bold me-2 lh-1 ls-n2 {{ $order->profit_info['profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ number_format($order->profit_info['profit'], 0, ',', '.') }}
                                    </span>
                                    <!--end::Amount-->
                                </div>
                                <!--end::Info-->
                                <!--begin::Subtitle-->
                                <span class="text-gray-400 pt-1 fw-semibold fs-6">{{ __('order.profit') }}</span>
                                <!--end::Subtitle-->
                            </div>
                            <!--end::Title-->
                        </div>
                        <!--end::Header-->
                        <!--begin::Card body-->
                        <div class="card-body pt-2 pb-4 d-flex align-items-center">
                            <div class="d-flex flex-center me-5 pt-2">
                                @if($order->profit_info['profit'] >= 0)
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
                        </div>
                        <!--end::Card body-->
                    </div>
                    <!--end::Card widget-->
                </div>
                <!--end::Col-->
                <!--begin::Col-->
                <div class="col-md-6 col-lg-3">
                    <!--begin::Card widget-->
                    <div class="card card-flush h-md-50 mb-5 mb-xl-10">
                        <!--begin::Header-->
                        <div class="card-header pt-5">
                            <!--begin::Title-->
                            <div class="card-title d-flex flex-column">
                                <!--begin::Info-->
                                <div class="d-flex align-items-center">
                                    <!--begin::Amount-->
                                    <span class="fs-2hx fw-bold me-2 lh-1 ls-n2 {{ $order->profit_info['profit_margin'] >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ number_format($order->profit_info['profit_margin'], 1) }}%
                                    </span>
                                    <!--end::Amount-->
                                </div>
                                <!--end::Info-->
                                <!--begin::Subtitle-->
                                <span class="text-gray-400 pt-1 fw-semibold fs-6">{{ __('order.profit_margin') }}</span>
                                <!--end::Subtitle-->
                            </div>
                            <!--end::Title-->
                        </div>
                        <!--end::Header-->
                        <!--begin::Card body-->
                        <div class="card-body pt-2 pb-4 d-flex align-items-center">
                            <div class="d-flex flex-center me-5 pt-2">
                                <i class="ki-duotone ki-chart-pie-4 fs-2tx text-primary">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                </i>
                            </div>
                        </div>
                        <!--end::Card body-->
                    </div>
                    <!--end::Card widget-->
                </div>
                <!--end::Col-->
            </div>
            <!--end::Row-->

            <!--begin::Profit status notice-->
            @if($order->profit_info['profit_status'] === 'profitable')
                <div class="notice d-flex bg-light-success rounded border-success border border-dashed p-6">
                    <i class="ki-duotone ki-check-circle fs-2tx text-success me-4"></i>
                    <div class="d-flex flex-stack flex-grow-1">
                        <div class="fw-semibold">
                            <h4 class="text-gray-900 fw-bold">{{ __('order.profitable_order') }}</h4>
                            <div class="fs-6 text-gray-700">{{ __('order.profitable_description') }}</div>
                        </div>
                    </div>
                </div>
            @elseif($order->profit_info['profit_status'] === 'loss')
                <div class="notice d-flex bg-light-danger rounded border-danger border border-dashed p-6">
                    <i class="ki-duotone ki-warning-2 fs-2tx text-danger me-4"></i>
                    <div class="d-flex flex-stack flex-grow-1">
                        <div class="fw-semibold">
                            <h4 class="text-gray-900 fw-bold">{{ __('order.loss_order') }}</h4>
                            <div class="fs-6 text-gray-700">{{ __('order.loss_description') }}</div>
                        </div>
                    </div>
                </div>
            @else
                <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed p-6">
                    <i class="ki-duotone ki-information fs-2tx text-warning me-4"></i>
                    <div class="d-flex flex-stack flex-grow-1">
                        <div class="fw-semibold">
                            <h4 class="text-gray-900 fw-bold">{{ __('order.break_even_order') }}</h4>
                            <div class="fs-6 text-gray-700">{{ __('order.break_even_description') }}</div>
                        </div>
                    </div>
                </div>
            @endif
            <!--end::Profit status notice-->
        @else
            <div class="text-center py-10">
                <div class="text-muted">{{ __('order.no_profit_data') }}</div>
            </div>
        @endif
    </div>
    <!--end::Card body-->
</div>
<!--end::Card-->
