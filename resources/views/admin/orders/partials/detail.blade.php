<div class="row">
    <div class="col-md-6">
        <!--begin::Order Info-->
        <div class="card card-flush mb-6">
            <div class="card-header">
                <div class="card-title">
                    <h3 class="fw-bold">Thông tin đơn hàng</h3>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-sm-4 fw-bold">Mã đơn hàng:</div>
                    <div class="col-sm-8">{{ $order->order_code }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 fw-bold">Trạng thái:</div>
                    <div class="col-sm-8">{!! $order->status_badge !!}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 fw-bold">Thanh toán:</div>
                    <div class="col-sm-8">{!! $order->payment_status_badge !!}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 fw-bold">Giao hàng:</div>
                    <div class="col-sm-8">{!! $order->delivery_status_badge !!}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 fw-bold">Kênh bán:</div>
                    <div class="col-sm-8">{{ $order->channel_display }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 fw-bold">Chi nhánh:</div>
                    <div class="col-sm-8">{{ $order->branch->name ?? 'N/A' }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 fw-bold">Ngày tạo:</div>
                    <div class="col-sm-8">{{ $order->created_at->format('d/m/Y H:i') }}</div>
                </div>
                @if($order->due_date)
                <div class="row mb-3">
                    <div class="col-sm-4 fw-bold">Ngày đến hạn:</div>
                    <div class="col-sm-8">{{ $order->due_date->format('d/m/Y') }}</div>
                </div>
                @endif
                @if($order->seller)
                <div class="row mb-3">
                    <div class="col-sm-4 fw-bold">Nhân viên bán hàng:</div>
                    <div class="col-sm-8">
                        <div class="d-flex align-items-center">
                            <div class="symbol symbol-30px me-2">
                                <div class="symbol-label bg-light-success">
                                    <i class="fas fa-user fs-7 text-success"></i>
                                </div>
                            </div>
                            <span class="fw-bold text-success">{{ $order->seller->full_name }}</span>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
        <!--end::Order Info-->

        <!--begin::Customer Info-->
        <div class="card card-flush mb-6">
            <div class="card-header">
                <div class="card-title">
                    <h3 class="fw-bold">Thông tin khách hàng</h3>
                </div>
            </div>
            <div class="card-body">
                @if($order->customer_id == 0)
                <div class="row mb-3">
                    <div class="col-sm-4 fw-bold">Tên khách hàng:</div>
                    <div class="col-sm-8">Khách lẻ</div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 fw-bold">Số điện thoại:</div>
                    <div class="col-sm-8">N/A</div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 fw-bold">Email:</div>
                    <div class="col-sm-8">N/A</div>
                </div>
                @elseif($order->customer)
                <div class="row mb-3">
                    <div class="col-sm-4 fw-bold">Tên khách hàng:</div>
                    <div class="col-sm-8">{{ $order->customer->name }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 fw-bold">Số điện thoại:</div>
                    <div class="col-sm-8">{{ $order->customer->phone ?? 'N/A' }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 fw-bold">Email:</div>
                    <div class="col-sm-8">{{ $order->customer->email ?? 'N/A' }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4 fw-bold">Địa chỉ:</div>
                    <div class="col-sm-8">{{ $order->customer->address ?? 'N/A' }}</div>
                </div>
                @else
                <p class="text-muted">Không có thông tin khách hàng</p>
                @endif
            </div>
        </div>
        <!--end::Customer Info-->
    </div>

    <div class="col-md-6">
        <!--begin::Payment Info-->
        <div class="card card-flush mb-6">
            <div class="card-header">
                <div class="card-title">
                    <h3 class="fw-bold">Thông tin thanh toán</h3>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-sm-5 fw-bold">Tổng tiền:</div>
                    <div class="col-sm-7 text-end">
                        <span class="fw-bold text-primary fs-4">{{ number_format($order->final_amount, 0, ',', '.') }} ₫</span>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-5 fw-bold">Đã thanh toán:</div>
                    <div class="col-sm-7 text-end">
                        <span class="fw-bold text-success fs-5">{{ number_format($order->amount_paid, 0, ',', '.') }} ₫</span>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-5 fw-bold">Còn lại:</div>
                    <div class="col-sm-7 text-end">
                        @php
                            $remaining = $order->final_amount - $order->amount_paid;
                            $color = $remaining > 0 ? 'danger' : ($remaining < 0 ? 'warning' : 'success');
                        @endphp
                        <span class="fw-bold text-{{ $color }} fs-5">{{ number_format($remaining, 0, ',', '.') }} ₫</span>
                    </div>
                </div>
                @if($order->discount_amount > 0)
                <div class="row mb-3">
                    <div class="col-sm-5 fw-bold">Giảm giá:</div>
                    <div class="col-sm-7 text-end">
                        <span class="text-warning">-{{ number_format($order->discount_amount, 0, ',', '.') }} ₫</span>
                    </div>
                </div>
                @endif
                @if($order->payment_method)
                <div class="row mb-3">
                    <div class="col-sm-5 fw-bold">Phương thức:</div>
                    <div class="col-sm-7">{{ $order->payment_method_display }}</div>
                </div>
                @endif
                @if($order->payment_reference)
                <div class="row mb-3">
                    <div class="col-sm-5 fw-bold">Mã tham chiếu:</div>
                    <div class="col-sm-7">{{ $order->payment_reference }}</div>
                </div>
                @endif
                @if($order->payment_date)
                <div class="row mb-3">
                    <div class="col-sm-5 fw-bold">Ngày thanh toán:</div>
                    <div class="col-sm-7">{{ $order->payment_date->format('d/m/Y H:i') }}</div>
                </div>
                @endif
            </div>
        </div>
        <!--end::Payment Info-->

        <!--begin::Staff Info-->
        <div class="card card-flush mb-6">
            <div class="card-header">
                <div class="card-title">
                    <h3 class="fw-bold">Thông tin nhân viên</h3>
                </div>
            </div>
            <div class="card-body">
                @if($order->creator)
                <div class="row mb-3">
                    <div class="col-sm-4 fw-bold">Người tạo:</div>
                    <div class="col-sm-8">
                        <div class="d-flex align-items-center">
                            <div class="symbol symbol-35px me-3">
                                <div class="symbol-label bg-light-primary">
                                    <i class="fas fa-user fs-6 text-primary"></i>
                                </div>
                            </div>
                            <div class="d-flex flex-column">
                                <span class="fw-bold">{{ $order->creator->name }}</span>
                                @if($order->creator->email)
                                <span class="text-muted fs-7">{{ $order->creator->email }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                @if($order->seller)
                <div class="row mb-3">
                    <div class="col-sm-4 fw-bold">Người bán:</div>
                    <div class="col-sm-8">
                        <div class="d-flex align-items-center">
                            <div class="symbol symbol-35px me-3">
                                <div class="symbol-label bg-light-success">
                                    <i class="fas fa-handshake fs-6 text-success"></i>
                                </div>
                            </div>
                            <div class="d-flex flex-column">
                                <span class="fw-bold text-success">{{ $order->seller->name }}</span>
                                @if($order->seller->email)
                                <span class="text-muted fs-7">{{ $order->seller->email }}</span>
                                @endif
                                @if($order->seller->phone)
                                <span class="text-muted fs-7">
                                    <i class="fas fa-phone me-1"></i>{{ $order->seller->phone }}
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                @if($order->updater)
                <div class="row mb-3">
                    <div class="col-sm-4 fw-bold">Cập nhật cuối:</div>
                    <div class="col-sm-8">
                        <div class="d-flex align-items-center">
                            <div class="symbol symbol-35px me-3">
                                <div class="symbol-label bg-light-warning">
                                    <i class="fas fa-edit fs-6 text-warning"></i>
                                </div>
                            </div>
                            <div class="d-flex flex-column">
                                <span class="fw-bold">{{ $order->updater->name }}</span>
                                <span class="text-muted fs-7">{{ $order->updated_at->format('d/m/Y H:i') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
        <!--end::Staff Info-->
    </div>
</div>

<!--begin::Order Items-->
@if($order->orderItems && $order->orderItems->count() > 0)
<div class="card card-flush">
    <div class="card-header">
        <div class="card-title">
            <h3 class="fw-bold">Chi tiết sản phẩm ({{ $order->orderItems->count() }} sản phẩm)</h3>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-row-bordered table-row-gray-100 align-middle gs-0 gy-3">
                <thead>
                    <tr class="fw-bold text-muted">
                        <th class="min-w-250px">Sản phẩm</th>
                        <th class="min-w-100px text-center">Số lượng</th>
                        <th class="min-w-100px text-end">Đơn giá</th>
                        <th class="min-w-100px text-end">Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->orderItems as $item)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                @if($item->product && $item->product->image)
                                <div class="symbol symbol-60px me-3">
                                    <img src="{{ asset('storage/' . $item->product->image) }}" alt="{{ $item->product_name ?? ($item->product ? $item->product->product_name : 'N/A') }}" class="symbol-label">
                                </div>
                                @else
                                <div class="symbol symbol-60px me-3">
                                    <div class="symbol-label bg-light-primary">
                                        <i class="fas fa-box fs-2 text-primary"></i>
                                    </div>
                                </div>
                                @endif
                                <div class="d-flex flex-column">
                                    <span class="text-dark fw-bold text-hover-primary fs-4 mb-2">
                                        {{ $item->product_name ?? ($item->product ? $item->product->product_name : 'N/A') }}
                                    </span>
                                    @if($item->product_sku ?? ($item->product ? $item->product->product_sku : null))
                                    <span class="text-muted fs-7 mb-1">
                                        <i class="fas fa-barcode me-1"></i>
                                        <strong>SKU:</strong> {{ $item->product_sku ?? $item->product->product_sku }}
                                    </span>
                                    @endif
                                    @if($item->product && $item->product->category)
                                    <span class="badge badge-light-info fs-8 align-self-start">
                                        <i class="fas fa-tag me-1"></i>
                                        {{ $item->product->category->name }}
                                    </span>
                                    @endif
                                    @if($item->product && $item->product->description)
                                    <span class="text-muted fs-8 mt-1" style="max-width: 300px;">
                                        {{ Str::limit($item->product->description, 80) }}
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-light-info fs-6 px-3 py-2">{{ $item->quantity }}</span>
                        </td>
                        <td class="text-end">
                            <span class="text-dark fw-bold fs-5">{{ number_format($item->unit_price, 0, ',', '.') }} ₫</span>
                        </td>
                        <td class="text-end">
                            <span class="text-dark fw-bold fs-4 text-primary">{{ number_format($item->total_price, 0, ',', '.') }} ₫</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-top">
                        <td colspan="3" class="text-end fw-bold fs-5">Tổng cộng:</td>
                        <td class="text-end">
                            <span class="fw-bold text-primary fs-3">{{ number_format($order->final_amount, 0, ',', '.') }} ₫</span>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endif
<!--end::Order Items-->

<!--begin::Notes-->
@if($order->notes || $order->internal_notes || $order->payment_notes)
<div class="card card-flush mt-6">
    <div class="card-header">
        <div class="card-title">
            <h3 class="fw-bold">Ghi chú</h3>
        </div>
    </div>
    <div class="card-body">
        @if($order->notes)
        <div class="mb-4">
            <h6 class="fw-bold text-gray-600">Ghi chú đơn hàng:</h6>
            <p class="text-gray-800">{{ $order->notes }}</p>
        </div>
        @endif
        @if($order->internal_notes)
        <div class="mb-4">
            <h6 class="fw-bold text-gray-600">Ghi chú nội bộ:</h6>
            <p class="text-gray-800">{{ $order->internal_notes }}</p>
        </div>
        @endif
        @if($order->payment_notes)
        <div class="mb-4">
            <h6 class="fw-bold text-gray-600">Ghi chú thanh toán:</h6>
            <p class="text-gray-800">{{ $order->payment_notes }}</p>
        </div>
        @endif
    </div>
</div>
@endif
<!--end::Notes-->
