<!--begin::Invoice Detail Expansion-->
<div class="invoice-details-expansion">
    <div class="card">
        <div class="card-body">
            <!--begin::Tabs-->
            <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x mb-5 fs-6">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#kt_tab_pane_info_{{ $invoice->id }}">Thông tin</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#kt_tab_pane_payment_{{ $invoice->id }}">Lịch sử thanh toán</a>
                </li>
            </ul>
            <!--end::Tabs-->

            <!--begin::Tab content-->
            <div class="tab-content" id="myTabContent">
                <!--begin::Tab pane Info-->
                <div class="tab-pane fade show active" id="kt_tab_pane_info_{{ $invoice->id }}" role="tabpanel">
                    <div class="row g-6">
                        <!--begin::Left Column-->
                        <div class="col-lg-6">
                            <div class="row g-3">
                                <div class="col-6">
                                    <label class="form-label">Mã hóa đơn:</label>
                                    <div class="fw-bold text-gray-800">{{ $invoice->invoice_number }}</div>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Trạng thái:</label>
                                    <div>
                                        @if($invoice->payment_status == 'paid')
                                            <span class="badge badge-light-success">Hoàn thành</span>
                                        @elseif($invoice->payment_status == 'partial')
                                            <span class="badge badge-light-warning">Thanh toán một phần</span>
                                        @else
                                            <span class="badge badge-light-danger">Chưa thanh toán</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Thời gian:</label>
                                    <div class="fw-bold text-gray-600">{{ $invoice->created_at->format('d/m/Y H:i') }}</div>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Chi nhánh:</label>
                                    <div class="fw-bold text-gray-600">{{ $invoice->branchShop->name ?? 'N/A' }}</div>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Khách hàng:</label>
                                    <div class="fw-bold text-primary">{{ $invoice->customer_display ?? 'Khách lẻ' }}</div>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Người bán:</label>
                                    <div class="fw-bold text-gray-600">{{ $invoice->creator->name ?? 'N/A' }}</div>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Bảng giá:</label>
                                    <div class="fw-bold text-gray-600">Sale</div>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Người tạo:</label>
                                    <div class="fw-bold text-gray-600">{{ $invoice->creator->name ?? 'N/A' }}</div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Mã đặt hàng:</label>
                                    <div class="fw-bold text-gray-600">{{ $invoice->order_code ?? 'N/A' }}</div>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Kênh bán:</label>
                                    <div class="fw-bold text-gray-600">
                                        @switch($invoice->channel)
                                            @case('direct')
                                                Bán trực tiếp
                                                @break
                                            @case('online')
                                                Online
                                                @break
                                            @case('phone')
                                                Điện thoại
                                                @break
                                            @default
                                                {{ $invoice->channel }}
                                        @endswitch
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end::Left Column-->

                        <!--begin::Right Column - Edit Button-->
                        <div class="col-lg-6">
                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-light-primary btn-sm">
                                    <i class="fas fa-edit"></i>
                                    Ghi chú
                                </button>
                            </div>
                        </div>
                        <!--end::Right Column-->
                    </div>

                    <!--begin::Products Table-->
                    <div class="separator my-6"></div>
                    <div class="table-responsive">
                        <table class="table table-row-dashed table-row-gray-300 gy-4">
                            <thead>
                                <tr class="fw-bold fs-6 text-gray-800 border-bottom border-gray-200">
                                    <th>Mã hàng</th>
                                    <th>Tên hàng</th>
                                    <th class="text-end">Số lượng</th>
                                    <th class="text-end">Đơn giá</th>
                                    <th class="text-end">Giảm giá</th>
                                    <th class="text-end">Giá bán</th>
                                    <th class="text-end">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($invoice->invoiceItems ?? [] as $item)
                                <tr>
                                    <td class="fw-bold text-gray-600">{{ $item->product_sku ?? 'N/A' }}</td>
                                    <td class="fw-bold text-gray-800">{{ $item->product_name }}</td>
                                    <td class="text-end fw-bold">{{ number_format($item->quantity, 0, ',', '.') }}</td>
                                    <td class="text-end fw-bold">{{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                    <td class="text-end fw-bold text-danger">{{ number_format($item->discount_amount ?? 0, 0, ',', '.') }}</td>
                                    <td class="text-end fw-bold">{{ number_format($item->unit_price - ($item->discount_amount ?? 0), 0, ',', '.') }}</td>
                                    <td class="text-end fw-bold text-primary">{{ number_format($item->total_price, 0, ',', '.') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-10">Không có sản phẩm nào</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <!--end::Products Table-->
                </div>
                <!--end::Tab pane Info-->

                <!--begin::Tab pane Payment-->
                <div class="tab-pane fade" id="kt_tab_pane_payment_{{ $invoice->id }}" role="tabpanel">
                    <div class="d-flex flex-column">
                        <!--begin::Payment Summary-->
                        <div class="card bg-light-primary mb-5">
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-6">
                                        <div class="text-muted fs-7">Tổng tiền hàng</div>
                                        <div class="fw-bold fs-5">{{ number_format($invoice->subtotal ?? 0, 0, ',', '.') }}₫</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-muted fs-7">Giảm giá</div>
                                        <div class="fw-bold fs-5 text-danger">{{ number_format($invoice->discount_amount ?? 0, 0, ',', '.') }}₫</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-muted fs-7">Tổng cộng</div>
                                        <div class="fw-bold fs-4 text-primary">{{ number_format($invoice->total_amount, 0, ',', '.') }}₫</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-muted fs-7">Đã thanh toán</div>
                                        <div class="fw-bold fs-5 text-success">{{ number_format($invoice->amount_paid ?? 0, 0, ',', '.') }}₫</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end::Payment Summary-->

                        <!--begin::Payment History-->
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Lịch sử thanh toán</h3>
                            </div>
                            <div class="card-body">
                                <div class="timeline">
                                    <div class="timeline-item">
                                        <div class="timeline-line w-40px"></div>
                                        <div class="timeline-icon symbol symbol-circle symbol-40px">
                                            <div class="symbol-label bg-light-success">
                                                <i class="fas fa-dollar-sign fs-2 text-success"></i>
                                            </div>
                                        </div>
                                        <div class="timeline-content mb-10 mt-n1">
                                            <div class="pe-3 mb-5">
                                                <div class="fs-5 fw-semibold mb-2">Thanh toán tiền mặt</div>
                                                <div class="d-flex align-items-center mt-1 fs-6">
                                                    <div class="text-muted me-2 fs-7">{{ $invoice->created_at->format('d/m/Y H:i') }}</div>
                                                </div>
                                            </div>
                                            <div class="overflow-auto pb-5">
                                                <div class="d-flex align-items-center border border-dashed border-gray-300 rounded min-w-750px px-7 py-3 mb-5">
                                                    <div class="flex-grow-1">
                                                        <div class="fs-6 text-gray-800 fw-semibold">Số tiền: {{ number_format($invoice->amount_paid ?? 0, 0, ',', '.') }}₫</div>
                                                        <div class="text-muted fs-7">Phương thức:
                                                            @switch($invoice->payment_method)
                                                                @case('cash')
                                                                    Tiền mặt
                                                                    @break
                                                                @case('transfer')
                                                                    Chuyển khoản
                                                                    @break
                                                                @case('card')
                                                                    Thẻ
                                                                    @break
                                                                @case('e_wallet')
                                                                    Ví điện tử
                                                                    @break
                                                                @default
                                                                    {{ $invoice->payment_method }}
                                                            @endswitch
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end::Payment History-->
                    </div>
                </div>
                <!--end::Tab pane Payment-->
            </div>
            <!--end::Tab content-->
        </div>
    </div>
</div>
<!--end::Invoice Detail Expansion-->
