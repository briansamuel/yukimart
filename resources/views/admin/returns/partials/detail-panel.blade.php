<!--begin::Return Detail Panel-->
<div class="detail-panel">
    <div class="card card-flush border-0 ">
        <div class="card-body border-0 p-0 shadow-none">
            
            <!-- Tab Navigation -->
            <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-4 fw-semibold mb-5" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link active" data-bs-toggle="tab" href="#kt_return_info_{{ $return->id }}" aria-selected="true" role="tab">
                        <i class="fas fa-info-circle me-2"></i>Thông tin
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" data-bs-toggle="tab" href="#kt_return_items_{{ $return->id }}" aria-selected="false" role="tab" tabindex="-1">
                        <i class="fas fa-list me-2"></i>Sản phẩm trả
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" data-bs-toggle="tab" href="#kt_return_payment_{{ $return->id }}" aria-selected="false" role="tab" tabindex="-1">
                        <i class="fas fa-credit-card me-2"></i>Lịch sử thanh toán
                    </a>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content">
                <!-- Tab 1: Thông tin -->
                <div class="tab-pane fade show active" id="kt_return_info_{{ $return->id }}" role="tabpanel">
                    <!-- Customer Header -->
                    <div class="d-flex align-items-center justify-content-between mb-6">
                        <div class="d-flex">
                            <h3 class="fw-bold text-gray-800 mx-5">
                                @if($return->customer_id > 0 && $return->customer)
                                    {{ $return->customer->name }}
                                @else
                                    Khách lẻ
                                @endif
                                <i class="fas fa-external-link-alt ms-2 text-primary fs-6"></i>
                            </h3>
                            <div class="fw-semibold text-gray-600 mx-5">{{ $return->return_number }}</div>
                            <span class="badge badge-light-success fs-7 mx-5">{{ ucfirst($return->status) }}</span>
                        </div>
                    </div>

                    <!-- Return Information Grid -->
                    <div class="row g-5 mb-7">
                        <!-- Left Column -->
                        <div class="col-sm-6">
                            <div class="fw-semibold fs-7 text-gray-600 mb-1">Mã đơn trả:</div>
                            <div class="fw-bold fs-6 text-gray-800">{{ $return->return_number }}</div>
                        </div>
                        <div class="col-sm-6">
                            <div class="fw-semibold fs-7 text-gray-600 mb-1">Ngày tạo:</div>
                            <div class="fw-bold fs-6 text-gray-800">{{ $return->created_at->format('d/m/Y H:i') }}</div>
                        </div>
                        <div class="col-sm-6">
                            <div class="fw-semibold fs-7 text-gray-600 mb-1">Tổng tiền:</div>
                            <div class="fw-bold fs-6 text-gray-800">{{ number_format($return->total_amount) }} ₫</div>
                        </div>
                        <div class="col-sm-6">
                            <div class="fw-semibold fs-7 text-gray-600 mb-1">Đã hoàn:</div>
                            <div class="fw-bold fs-6 text-gray-800">{{ number_format($return->refunded_amount ?? 0) }} ₫</div>
                        </div>
                        <div class="col-sm-6">
                            <div class="fw-semibold fs-7 text-gray-600 mb-1">Người tạo:</div>
                            <div class="fw-bold fs-6 text-gray-800">
                                @if($return->creator)
                                    {{ $return->creator->full_name }}
                                @else
                                    N/A
                                @endif
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="fw-semibold fs-7 text-gray-600 mb-1">Chi nhánh:</div>
                            <div class="fw-bold fs-6 text-gray-800">
                                @if($return->branchShop)
                                    {{ $return->branchShop->name }}
                                @else
                                    N/A
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Customer Information -->
                    @if($return->customer_id > 0 && $return->customer)
                    <div class="separator separator-dashed my-5"></div>
                    <h4 class="fw-bold text-gray-800 mb-5">Thông tin khách hàng</h4>
                    <div class="row g-5 mb-7">
                        <div class="col-sm-6">
                            <div class="fw-semibold fs-7 text-gray-600 mb-1">Tên khách hàng:</div>
                            <div class="fw-bold fs-6 text-gray-800">{{ $return->customer->name }}</div>
                        </div>
                        <div class="col-sm-6">
                            <div class="fw-semibold fs-7 text-gray-600 mb-1">Số điện thoại:</div>
                            <div class="fw-bold fs-6 text-gray-800">{{ $return->customer->phone ?? 'N/A' }}</div>
                        </div>
                        <div class="col-sm-12">
                            <div class="fw-semibold fs-7 text-gray-600 mb-1">Email:</div>
                            <div class="fw-bold fs-6 text-gray-800">{{ $return->customer->email ?? 'N/A' }}</div>
                        </div>
                    </div>
                    @endif

                    <!-- Notes -->
                    @if($return->notes)
                    <div class="separator separator-dashed my-5"></div>
                    <h4 class="fw-bold text-gray-800 mb-5">Ghi chú</h4>
                    <div class="fw-semibold fs-6 text-gray-600">{{ $return->notes }}</div>
                    @endif
                </div>

                <!-- Tab 2: Sản phẩm trả -->
                <div class="tab-pane fade" id="kt_return_items_{{ $return->id }}" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-row-dashed table-row-gray-300 gy-7">
                            <thead>
                                <tr class="fw-bold fs-6 text-gray-800 border-bottom-2 border-gray-200">
                                    <th>Sản phẩm</th>
                                    <th>Số lượng</th>
                                    <th>Đơn giá</th>
                                    <th>Thành tiền</th>
                                    <th>Lý do trả</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($return->returnOrderItems ?? [] as $item)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="ms-5">
                                                <div class="fw-bold text-gray-800">
                                                    @if($item->product)
                                                        {{ $item->product->product_name }}
                                                    @else
                                                        {{ $item->product_name }}
                                                    @endif
                                                </div>
                                                @if($item->product && $item->product->sku)
                                                <div class="fw-semibold text-gray-600">SKU: {{ $item->product->sku }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="fw-bold text-gray-800">{{ $item->quantity_returned }}</td>
                                    <td class="fw-bold text-gray-800">{{ number_format($item->unit_price) }} ₫</td>
                                    <td class="fw-bold text-gray-800">{{ number_format($item->line_total) }} ₫</td>
                                    <td class="fw-semibold text-gray-600">{{ $item->notes ?? 'N/A' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-gray-600">Không có sản phẩm nào</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab 3: Lịch sử thanh toán -->
                <div class="tab-pane fade" id="kt_return_payment_{{ $return->id }}" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-row-dashed table-row-gray-300 gy-7">
                            <thead>
                                <tr class="fw-bold fs-6 text-gray-800 border-bottom-2 border-gray-200">
                                    <th>Ngày</th>
                                    <th>Số tiền</th>
                                    <th>Phương thức</th>
                                    <th>Trạng thái</th>
                                    <th>Ghi chú</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($return->payments ?? [] as $payment)
                                <tr>
                                    <td class="fw-bold text-gray-800">{{ $payment->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="fw-bold text-gray-800">{{ number_format($payment->amount) }} ₫</td>
                                    <td class="fw-semibold text-gray-600">{{ $payment->payment_method ?? 'N/A' }}</td>
                                    <td>
                                        @if($payment->status === 'completed')
                                            <span class="badge badge-light-success">Hoàn thành</span>
                                        @elseif($payment->status === 'pending')
                                            <span class="badge badge-light-warning">Đang xử lý</span>
                                        @else
                                            <span class="badge badge-light-danger">Thất bại</span>
                                        @endif
                                    </td>
                                    <td class="fw-semibold text-gray-600">{{ $payment->notes ?? 'N/A' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-gray-600">Chưa có giao dịch nào</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="separator separator-dashed my-5"></div>
            <div class="d-flex justify-content-center gap-3">
                @if(in_array($return->status, ['draft', 'processing', 'pending']))
                <button type="button" class="btn btn-light btn-sm" onclick="editReturn({{ $return->id }})">
                    <i class="fas fa-edit me-2"></i>Chỉnh sửa
                </button>
                @endif
                <button type="button" class="btn btn-info btn-sm" onclick="printReturn({{ $return->id }})">
                    <i class="fas fa-print me-2"></i>In
                </button>
                <button type="button" class="btn btn-success btn-sm" onclick="exportReturn({{ $return->id }})">
                    <i class="fas fa-file-export me-2"></i>Xuất file
                </button>
                @if($return->status === 'draft')
                <button type="button" class="btn btn-danger btn-sm" onclick="deleteReturn({{ $return->id }})">
                    <i class="fas fa-trash me-2"></i>Xóa
                </button>
                @endif
            </div>
        </div>
    </div>
</div>
<!--end::Return Detail Panel-->
