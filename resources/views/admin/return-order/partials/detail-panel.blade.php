{{-- Return Order Detail Panel --}}
<div class="return-order-detail-panel">
    <div class="card card-flush border-0 shadow-none">
        <div class="card-body p-0">
        {{-- Tab Navigation --}}
        <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-4 fw-semibold mb-5" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link active" data-bs-toggle="tab" href="#kt_return_order_info_{{ $returnOrder->id }}" aria-selected="true" role="tab">
                    <i class="fas fa-info-circle me-2"></i>Thông tin
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" data-bs-toggle="tab" href="#kt_return_order_items_{{ $returnOrder->id }}" aria-selected="false" role="tab" tabindex="-1">
                    <i class="fas fa-list me-2"></i>Hóa đơn đổi hàng
                </a>
            </li>
        </ul>

        {{-- Tab Content --}}
        <div class="tab-content">
            {{-- Thông tin Tab --}}
            <div class="tab-pane fade show active" id="kt_return_order_info_{{ $returnOrder->id }}" role="tabpanel">
                {{-- Form Header --}}
                <div class="row mb-5">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center">
                            <h3 class="fw-bold text-gray-800 me-3">
                                {{ $returnOrder->customer ? $returnOrder->customer->name : 'Khách lẻ' }}
                            </h3>
                            <span class="badge badge-light-{{ $returnOrder->status === 'approved' ? 'success' : ($returnOrder->status === 'pending' ? 'warning' : 'danger') }} fs-7 fw-bold">
                                {{ $returnOrder->status === 'approved' ? 'Đã trả' : ($returnOrder->status === 'pending' ? 'Chờ duyệt' : 'Từ chối') }}
                            </span>
                        </div>
                    </div>
                    <div class="col-md-6 text-end">
                        <span class="text-gray-600 fs-6">{{ $returnOrder->return_order_code }}</span>
                    </div>
                </div>

                {{-- Form Fields --}}
                <div class="row g-5 mb-7">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-gray-600 fs-7">Người tạo:</label>
                        <div class="fw-bold text-gray-800 fs-6">{{ $returnOrder->creator->name ?? 'N/A' }}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-gray-600 fs-7">Người nhận trả:</label>
                        <select class="form-select form-select-sm" data-control="select2" data-placeholder="Chọn người nhận">
                            <option value="">Chọn người nhận</option>
                            @foreach($branchUsers as $user)
                                <option value="{{ $user->id }}" {{ $returnOrder->receiver_id == $user->id ? 'selected' : '' }}>
                                    {{ $user->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-gray-600 fs-7">Ngày trả:</label>
                        <input type="datetime-local" class="form-control form-control-sm"
                               value="{{ $returnOrder->return_date ? $returnOrder->return_date->format('Y-m-d\TH:i') : '' }}">
                    </div>
                </div>

                <div class="row g-5 mb-7">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-gray-600 fs-7">Mã hóa đơn:</label>
                        <div class="fw-bold text-primary fs-6">
                            <a href="{{ url('admin/invoices?Code=' . ($returnOrder->invoice->invoice_number ?? '')) }}"
                               class="text-primary text-hover-primary">
                                {{ $returnOrder->invoice->invoice_number ?? 'N/A' }}
                            </a>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-gray-600 fs-7">Kênh bán:</label>
                        <div class="fw-bold text-gray-800 fs-6">{{ $returnOrder->invoice->sales_channel === 'online' ? 'Bán trực tiếp' : 'Bán trực tiếp' }}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-gray-600 fs-7">Bảng giá:</label>
                        <div class="fw-bold text-gray-800 fs-6">{{ $returnOrder->invoice->price_list ?? 'Sale' }}</div>
                    </div>
                </div>

                {{-- Products Table --}}
                <div class="table-responsive">
                    <table class="table table-row-bordered table-row-gray-100 align-middle gs-0 gy-3">
                        <thead>
                            <tr class="fw-bold text-muted">
                                <th class="min-w-150px">Mã hàng</th>
                                <th class="min-w-200px">Tên hàng</th>
                                <th class="min-w-100px text-center">Số lượng</th>
                                <th class="min-w-100px text-end">Giá trả hàng</th>
                                <th class="min-w-100px text-end">Giảm giá</th>
                                <th class="min-w-100px text-end">Giá nhập lại</th>
                                <th class="min-w-100px text-end">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($returnOrder->returnOrderItems as $item)
                            <tr>
                                <td>
                                    <span class="text-primary fw-bold">{{ $item->product_sku ?? $item->product->sku ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    <span class="text-gray-800 fw-semibold">{{ $item->product_name ?? $item->product->name ?? 'N/A' }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="fw-bold">{{ number_format($item->quantity_returned ?? 0) }}</span>
                                </td>
                                <td class="text-end">
                                    <span class="fw-bold">{{ number_format($item->unit_price ?? 0) }}</span>
                                </td>
                                <td class="text-end">
                                    <span class="fw-bold">0</span>
                                </td>
                                <td class="text-end">
                                    <span class="fw-bold">{{ number_format($item->unit_price ?? 0) }}</span>
                                </td>
                                <td class="text-end">
                                    <span class="fw-bold">{{ number_format($item->line_total ?? 0) }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">Không có sản phẩm nào</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Summary Section --}}
                <div class="row justify-content-end mt-5">
                    <div class="col-md-4">
                        <div class="table-responsive">
                            <table class="table table-borderless">
                                @php
                                    $itemsTotal = $returnOrder->returnOrderItems->sum('line_total');
                                    $itemsCount = $returnOrder->returnOrderItems->count();
                                @endphp
                                <tr>
                                    <td class="text-end fw-semibold text-gray-600">Tổng tiền hàng trả ({{ $itemsCount }}):</td>
                                    <td class="text-end fw-bold fs-6">{{ number_format($itemsTotal) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-end fw-semibold text-gray-600">Giảm giá phiếu trả:</td>
                                    <td class="text-end fw-bold fs-6">0</td>
                                </tr>
                                <tr>
                                    <td class="text-end fw-semibold text-gray-600">Phí trả hàng:</td>
                                    <td class="text-end fw-bold fs-6">0</td>
                                </tr>
                                <tr class="border-top">
                                    <td class="text-end fw-bold text-gray-800">Tổng tiền hóa đơn trả:</td>
                                    <td class="text-end fw-bold fs-5 text-primary">{{ number_format($itemsTotal) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-end fw-bold text-gray-800">Tổng tiền hóa đơn đổi hàng:</td>
                                    <td class="text-end fw-bold fs-5 text-primary">{{ number_format($itemsTotal) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-end fw-semibold text-gray-600">Cần trả khách:</td>
                                    <td class="text-end fw-bold fs-6">0</td>
                                </tr>
                                <tr>
                                    <td class="text-end fw-semibold text-gray-600">Đã trả khách:</td>
                                    <td class="text-end fw-bold fs-6">0</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="d-flex justify-content-between mt-5">
                    <div>
                        <button type="button" class="btn btn-light btn-sm me-3">
                            <i class="fas fa-times me-2"></i>Hủy
                        </button>
                        <button type="button" class="btn btn-light btn-sm me-3">
                            <i class="fas fa-copy me-2"></i>Sao chép
                        </button>
                        <button type="button" class="btn btn-light btn-sm">
                            <i class="fas fa-file-export me-2"></i>Xuất file
                        </button>
                    </div>
                    <div>
                        <button type="button" class="btn btn-primary btn-sm me-3">
                            <i class="fas fa-save me-2"></i>Lưu
                        </button>
                        <button type="button" class="btn btn-light btn-sm">
                            <i class="fas fa-print me-2"></i>In
                        </button>
                    </div>
                </div>
            </div>

            {{-- Hóa đơn đổi hàng Tab --}}
            <div class="tab-pane fade" id="kt_return_order_items_{{ $returnOrder->id }}" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                        <thead>
                            <tr class="fw-bold text-muted">
                                <th class="min-w-150px">Mã hàng</th>
                                <th class="min-w-200px">Tên hàng</th>
                                <th class="min-w-100px text-end">Số lượng</th>
                                <th class="min-w-100px text-end">Giá trả hàng</th>
                                <th class="min-w-100px text-end">Giảm giá</th>
                                <th class="min-w-100px text-end">Giá nhập lại</th>
                                <th class="min-w-100px text-end">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($returnOrder->returnOrderItems as $item)
                            <tr>
                                <td>
                                    <div class="text-dark fw-bold text-hover-primary fs-6">{{ $item->product->sku ?? 'N/A' }}</div>
                                </td>
                                <td>
                                    <div class="text-dark fw-bold text-hover-primary fs-6">{{ $item->product->name ?? 'N/A' }}</div>
                                </td>
                                <td class="text-end">
                                    <span class="text-dark fw-bold fs-6">{{ number_format($item->quantity_returned) }}</span>
                                </td>
                                <td class="text-end">
                                    <span class="text-dark fw-bold fs-6">{{ number_format($item->unit_price) }}</span>
                                </td>
                                <td class="text-end">
                                    <span class="text-dark fw-bold fs-6">0</span>
                                </td>
                                <td class="text-end">
                                    <span class="text-dark fw-bold fs-6">{{ number_format($item->unit_price) }}</span>
                                </td>
                                <td class="text-end">
                                    <span class="text-dark fw-bold fs-6">{{ number_format($item->line_total) }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Summary Section --}}
                <div class="row justify-content-end mt-5">
                    <div class="col-md-4">
                        <div class="table-responsive">
                            <table class="table table-borderless">
                                <tbody>
                                    <tr>
                                        <td class="text-end fw-semibold text-gray-600">Tổng số lượng:</td>
                                        <td class="text-end fw-bold text-gray-800 fs-6">{{ $returnOrder->returnOrderItems->sum('quantity_returned') }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-end fw-semibold text-gray-600">Tổng tiền hàng:</td>
                                        <td class="text-end fw-bold text-gray-800 fs-6">{{ number_format($returnOrder->subtotal ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-end fw-semibold text-gray-600">Giảm giá phiếu nhập:</td>
                                        <td class="text-end fw-bold text-gray-800 fs-6">0</td>
                                    </tr>
                                    <tr>
                                        <td class="text-end fw-semibold text-gray-600">Tổng tiền hóa đơn mua:</td>
                                        <td class="text-end fw-bold text-gray-800 fs-6">{{ number_format($returnOrder->total_amount ?? 0) }}</td>
                                    </tr>
                                    <tr class="border-top">
                                        <td class="text-end fw-bold text-primary fs-5">Tổng tiền hóa đơn trả ({{ $returnOrder->code }}):</td>
                                        <td class="text-end fw-bold text-primary fs-5">{{ number_format($returnOrder->total_amount ?? 0) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-end fw-semibold text-gray-600">Khách cần trả:</td>
                                        <td class="text-end fw-bold text-gray-800 fs-6">0</td>
                                    </tr>
                                    <tr>
                                        <td class="text-end fw-semibold text-gray-600">Khách đã trả:</td>
                                        <td class="text-end fw-bold text-gray-800 fs-6">0</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Notes Section --}}
                <div class="row mt-5">
                    <div class="col-12">
                        <div class="form-group">
                            <label class="form-label fw-bold text-gray-700">Ghi chú...</label>
                            <textarea class="form-control form-control-solid" rows="3" readonly>{{ $returnOrder->notes ?? '' }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="row mt-5">
                    <div class="col-12 d-flex justify-content-start gap-3">
                        <button type="button" class="btn btn-light-primary btn-sm">
                            <i class="fas fa-undo me-2"></i>Hủy
                        </button>
                        <button type="button" class="btn btn-light-secondary btn-sm">
                            <i class="fas fa-file-export me-2"></i>Xuất file
                        </button>
                        <button type="button" class="btn btn-primary btn-sm">
                            <i class="fas fa-save me-2"></i>Lưu
                        </button>
                        <button type="button" class="btn btn-light-info btn-sm">
                            <i class="fas fa-print me-2"></i>In
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Action Buttons Outside Tabs --}}
    <div class="d-flex justify-content-center mt-4 pt-4 border-top">
        <button type="button" class="btn btn-light btn-sm me-3" id="btn-cancel-return-{{ $returnOrder->id }}">
            <i class="fas fa-times me-2"></i>Hủy
        </button>
        <button type="button" class="btn btn-light btn-sm me-3" id="btn-copy-return-{{ $returnOrder->id }}">
            <i class="fas fa-copy me-2"></i>Sao chép
        </button>
        <button type="button" class="btn btn-light btn-sm me-3" id="btn-export-return-{{ $returnOrder->id }}">
            <i class="fas fa-file-export me-2"></i>Xuất file
        </button>
        <button type="button" class="btn btn-primary btn-sm me-3" id="btn-save-return-{{ $returnOrder->id }}">
            <i class="fas fa-save me-2"></i>Lưu
        </button>
        <button type="button" class="btn btn-light btn-sm" id="btn-print-return-{{ $returnOrder->id }}">
            <i class="fas fa-print me-2"></i>In
        </button>
    </div>
</div>
