<!-- Customer Header -->
<div class="d-flex align-items-center justify-content-between mb-6">
    <div class="d-flex">
        <h3 class="fw-bold text-gray-800 mx-5">
            @if ($order->customer_id > 0 && $order->customer)
                {{ $order->customer->name }}
            @else
                Khách lẻ
            @endif
            <i class="fas fa-external-link-alt ms-2 text-primary fs-6"></i>
        </h3>
        <div class="fw-semibold text-gray-600 mx-5">{{ $order->order_code }}</div>
        <span class="badge badge-light-success fs-7 mx-5">{{ ucfirst($order->status) }}</span>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <!--begin::Order Info Card-->
        <div class="card card-flush h-100">
            <div class="card-body p-6">
                <!--begin::Order Header-->
                <div class="d-flex align-items-center mb-6">
                    <div class="symbol symbol-50px me-3">
                        <div class="symbol-label bg-light-primary">
                            <i class="fas fa-shopping-cart fs-2 text-primary"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h3 class="fw-bold text-gray-800 mb-1">{{ $order->order_code }}</h3>
                        <div class="text-muted fs-7">{{ $order->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                    <div class="text-end">
                        {!! $order->status_badge !!}
                    </div>
                </div>
                <!--end::Order Header-->

                <!--begin::Order Details-->
                <div class="separator separator-dashed my-6"></div>

                <div class="row mb-4">
                    <div class="col-4 text-muted fw-semibold">Người tạo:</div>
                    <div class="col-8">{{ $order->creator->name ?? 'N/A' }}</div>
                </div>

                <div class="row mb-4">
                    <div class="col-4 text-muted fw-semibold">Người nhận:</div>
                    <div class="col-8 fw-bold">
                        @if ($order->customer_id == 0)
                            Khách lẻ
                        @else
                            {{ $order->customer->name ?? 'N/A' }}
                        @endif
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-4 text-muted fw-semibold">Kênh bán:</div>
                    <div class="col-8">
                        <span class="badge badge-light-info">
                            <i class="fas fa-store me-1"></i>{{ $order->channel_display }}
                        </span>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-4 text-muted fw-semibold">Ngày tạo:</div>
                    <div class="col-8">{{ $order->created_at->format('d/m/Y H:i') }}</div>
                </div>

                <div class="row mb-4">
                    <div class="col-4 text-muted fw-semibold">Bảng giá:</div>
                    <div class="col-8">Bảng giá chung</div>
                </div>
                <!--end::Order Details-->

                <!--begin::Delivery Address-->
                <div class="separator separator-dashed my-6"></div>

                <div class="d-flex align-items-center mb-4">
                    <i class="fas fa-map-marker-alt text-primary me-2"></i>
                    <h6 class="fw-bold text-gray-800 mb-0">Từ giao đến:</h6>
                </div>

                <div class="bg-light-primary p-4 rounded mb-6">
                    <div class="fw-bold text-gray-800 mb-2">
                        @if ($order->customer_id == 0)
                            Khách lẻ
                        @else
                            {{ $order->customer->name ?? 'N/A' }}
                        @endif
                    </div>
                    <div class="text-muted mb-1">
                        @if ($order->customer && $order->customer->phone)
                            <i class="fas fa-phone me-1"></i>{{ $order->customer->phone }}
                        @else
                            <i class="fas fa-phone me-1"></i>N/A
                        @endif
                    </div>
                    <div class="text-muted">
                        @if ($order->customer && $order->customer->address)
                            <i class="fas fa-map-marker-alt me-1"></i>{{ $order->customer->address }}
                        @else
                            <i class="fas fa-map-marker-alt me-1"></i>Chưa có địa chỉ
                        @endif
                    </div>
                </div>
                <!--end::Delivery Address-->

                <!--begin::Delivery Info-->
                <div class="d-flex align-items-center mb-4">
                    <i class="fas fa-truck text-primary me-2"></i>
                    <h6 class="fw-bold text-gray-800 mb-0">Địa chỉ lấy hàng:</h6>
                </div>

                <div class="bg-light-info p-4 rounded mb-6">
                    <div class="fw-bold text-gray-800 mb-2">524 Lý Thường Kiệt</div>
                    <div class="text-muted mb-1">
                        <i class="fas fa-phone me-1"></i>0395930980
                    </div>
                    <div class="text-muted">
                        <i class="fas fa-map-marker-alt me-1"></i>Phường 07, Quận Tân Bình, Hồ Chí Minh - Phường 1, Hồ
                        Chí Minh - Quận Tân Bình
                    </div>
                </div>
                <!--end::Delivery Info-->

                <!--begin::Status Info-->
                <div class="row mb-4">
                    <div class="col-4 text-muted fw-semibold">Mã vận đơn:</div>
                    <div class="col-8">Chưa có</div>
                </div>

                <div class="row mb-4">
                    <div class="col-4 text-muted fw-semibold">Người giao:</div>
                    <div class="col-8">
                        <span class="badge badge-light-success">
                            <i class="fas fa-check-circle me-1"></i>SHOPEE
                        </span>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-4 text-muted fw-semibold">Thu hộ COD:</div>
                    <div class="col-8">
                        <span class="badge badge-light-warning">
                            <i class="fas fa-money-bill me-1"></i>0 g
                        </span>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-4 text-muted fw-semibold">Kích thước:</div>
                    <div class="col-8">Giao thường</div>
                </div>

                <div class="row mb-4">
                    <div class="col-4 text-muted fw-semibold">Dịch vụ:</div>
                    <div class="col-8">Giao thường</div>
                </div>

                <div class="row mb-4">
                    <div class="col-4 text-muted fw-semibold">Phí trả ĐTCH:</div>
                    <div class="col-8">
                        <span class="text-info fw-bold">
                            <i class="fas fa-info-circle me-1"></i>32,275
                        </span>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-4 text-muted fw-semibold">Thời gian giao hàng:</div>
                    <div class="col-8">Chưa có</div>
                </div>
                <!--end::Status Info-->
            </div>
        </div>
        <!--end::Order Info Card-->
    </div>

    <div class="col-md-12">
        <!--begin::Product Table-->
        <div class="card card-flush h-100">
            <div class="card-header">
                <div class="card-title">
                    <h3 class="fw-bold">Mã hàng</h3>
                </div>
            </div>
            <div class="card-body p-0">
                <!--begin::Table-->
                <div class="table-responsive">
                    <table class="table table-row-bordered table-row-gray-100 align-middle gs-0 gy-3 mb-0">
                        <thead class="bg-light">
                            <tr class="fw-bold text-muted">
                                <th class="min-w-200px ps-6">Tên hàng</th>
                                <th class="min-w-80px text-center">Số lượng</th>
                                <th class="min-w-100px text-end">Đơn giá</th>
                                <th class="min-w-100px text-end">Giảm giá</th>
                                <th class="min-w-120px text-end pe-6">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($order->orderItems && $order->orderItems->count() > 0)
                                @foreach ($order->orderItems as $item)
                                    <tr>
                                        <td class="ps-6">
                                            <div class="d-flex align-items-center">
                                                <div class="symbol symbol-40px me-3">
                                                    @if ($item->product && $item->product->image)
                                                        <img src="{{ asset('storage/' . $item->product->image) }}"
                                                            alt="{{ $item->product_name ?? ($item->product ? $item->product->product_name : 'N/A') }}"
                                                            class="symbol-label">
                                                    @else
                                                        <div class="symbol-label bg-light-primary">
                                                            <i class="fas fa-box fs-6 text-primary"></i>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="d-flex flex-column">
                                                    <span class="text-dark fw-bold text-hover-primary fs-6">
                                                        {{ $item->product_name ?? ($item->product ? $item->product->product_name : 'N/A') }}
                                                    </span>
                                                    @if ($item->product_sku ?? ($item->product ? $item->product->product_sku : null))
                                                        <span class="text-muted fs-7">
                                                            {{ $item->product_sku ?? $item->product->product_sku }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="fw-bold">{{ $item->quantity }}</span>
                                        </td>
                                        <td class="text-end">
                                            <span
                                                class="fw-bold">{{ number_format($item->unit_price, 0, ',', '.') }}</span>
                                        </td>
                                        <td class="text-end">
                                            <span class="text-muted">41,000</span>
                                        </td>
                                        <td class="text-end pe-6">
                                            <span
                                                class="fw-bold text-primary">{{ number_format($item->total_price, 0, ',', '.') }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-6">
                                        Không có sản phẩm nào
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                <!--end::Table-->

                <!--begin::Summary-->
                <div class="border-top">
                    <div class="p-6">
                        <div class="row mb-3">
                            <div class="col-8 text-end fw-semibold text-muted">Tổng tiền hàng
                                ({{ $order->orderItems->count() ?? 0 }}):</div>
                            <div class="col-4 text-end fw-bold">
                                {{ number_format($order->orderItems->sum('total_price') ?? 0, 0, ',', '.') }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-8 text-end fw-semibold text-muted">Giảm giá phiếu đặt:</div>
                            <div class="col-4 text-end">0</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-8 text-end fw-semibold text-muted">Phí lệch vận chuyển:</div>
                            <div class="col-4 text-end">22,000</div>
                        </div>
                        <div class="separator separator-dashed my-4"></div>
                        <div class="row">
                            <div class="col-8 text-end fw-bold fs-4 text-gray-800">Tổng cộng:</div>
                            <div class="col-4 text-end fw-bold fs-3 text-primary">
                                {{ number_format($order->final_amount, 0, ',', '.') }}</div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-8 text-end fw-semibold text-muted">Khách cần trả:</div>
                            <div class="col-4 text-end fw-bold text-danger">0</div>
                        </div>
                    </div>
                </div>
                <!--end::Summary-->
            </div>
        </div>
        <!--end::Product Table-->
    </div>
</div>

<!--begin::Order Notes-->
@if ($order->notes || $order->internal_notes || $order->payment_notes)
    <div class="row mt-6">
        <div class="col-12">
            <div class="card card-flush">
                <div class="card-header">
                    <div class="card-title">
                        <h3 class="fw-bold">Ghi chú đơn hàng</h3>
                    </div>
                </div>
                <div class="card-body">
                    @if ($order->notes)
                        <div class="mb-4">
                            <h6 class="fw-bold text-gray-600 mb-2">Ghi chú đơn hàng:</h6>
                            <p class="text-gray-800 mb-0">{{ $order->notes }}</p>
                        </div>
                    @endif
                    @if ($order->internal_notes)
                        <div class="mb-4">
                            <h6 class="fw-bold text-gray-600 mb-2">Ghi chú nội bộ:</h6>
                            <p class="text-gray-800 mb-0">{{ $order->internal_notes }}</p>
                        </div>
                    @endif
                    @if ($order->payment_notes)
                        <div class="mb-4">
                            <h6 class="fw-bold text-gray-600 mb-2">Ghi chú thanh toán:</h6>
                            <p class="text-gray-800 mb-0">{{ $order->payment_notes }}</p>
                        </div>
                    @endif

                    <!-- Default note from image -->
                    <div class="bg-light-warning p-4 rounded">
                        <p class="mb-0 text-gray-800">
                            Đơn hàng tự động tạo từ đơn Shopee 250723USWJRBXP, người mua duonghuynh4duonghuynh
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
<!--end::Order Notes-->
