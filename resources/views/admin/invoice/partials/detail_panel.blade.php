<div class="invoice-detail-panel p-4 bg-light-primary border-start border-primary border-5">
    <div class="row">
        <!-- Invoice Information -->
        <div class="col-md-6">
            <div class="card card-flush h-100">
                <div class="card-header">
                    <h6 class="card-title fw-bold text-primary">Thông tin hóa đơn</h6>
                </div>
                <div class="card-body pt-0">
                    <div class="row mb-3">
                        <div class="col-5 fw-semibold text-gray-600">Mã hóa đơn:</div>
                        <div class="col-7 fw-bold">{{ $invoice->invoice_number }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-5 fw-semibold text-gray-600">Ngày tạo:</div>
                        <div class="col-7">{{ $invoice->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-5 fw-semibold text-gray-600">Kênh bán:</div>
                        <div class="col-7">
                            @if($invoice->channel === 'online')
                                <span class="badge badge-light-info">Online</span>
                            @elseif($invoice->channel === 'pos')
                                <span class="badge badge-light-success">POS</span>
                            @else
                                <span class="badge badge-light-primary">Trực tiếp</span>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-5 fw-semibold text-gray-600">Chi nhánh:</div>
                        <div class="col-7">{{ $invoice->branchShop->name ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer Information -->
        <div class="col-md-6">
            <div class="card card-flush h-100">
                <div class="card-header">
                    <h6 class="card-title fw-bold text-success">Thông tin khách hàng</h6>
                </div>
                <div class="card-body pt-0">
                    @if($invoice->customer_id > 0 && $invoice->customer)
                        <div class="row mb-3">
                            <div class="col-4 fw-semibold text-gray-600">Tên:</div>
                            <div class="col-8 fw-bold">{{ $invoice->customer->name }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-4 fw-semibold text-gray-600">Điện thoại:</div>
                            <div class="col-8">{{ $invoice->customer->phone ?? 'N/A' }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-4 fw-semibold text-gray-600">Email:</div>
                            <div class="col-8">{{ $invoice->customer->email ?? 'N/A' }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-4 fw-semibold text-gray-600">Địa chỉ:</div>
                            <div class="col-8">{{ $invoice->customer->address ?? 'N/A' }}</div>
                        </div>
                    @else
                        <div class="text-center text-muted">
                            <i class="fas fa-user-slash fs-2x mb-3"></i>
                            <p class="fw-bold">Khách lẻ</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <!-- Payment Information -->
        <div class="col-md-6">
            <div class="card card-flush h-100">
                <div class="card-header">
                    <h6 class="card-title fw-bold text-warning">Thông tin thanh toán</h6>
                </div>
                <div class="card-body pt-0">
                    <div class="row mb-3">
                        <div class="col-5 fw-semibold text-gray-600">Tổng tiền:</div>
                        <div class="col-7 fw-bold text-primary">{{ number_format($invoice->total_amount, 0, ',', '.') }}đ</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-5 fw-semibold text-gray-600">Đã thanh toán:</div>
                        <div class="col-7 fw-bold text-success">{{ number_format($invoice->amount_paid, 0, ',', '.') }}đ</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-5 fw-semibold text-gray-600">Phương thức:</div>
                        <div class="col-7">
                            @if($invoice->payment_method === 'cash')
                                <span class="badge badge-light-success">Tiền mặt</span>
                            @elseif($invoice->payment_method === 'transfer')
                                <span class="badge badge-light-info">Chuyển khoản</span>
                            @elseif($invoice->payment_method === 'card')
                                <span class="badge badge-light-primary">Thẻ</span>
                            @else
                                <span class="badge badge-light-secondary">Khác</span>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-5 fw-semibold text-gray-600">Trạng thái:</div>
                        <div class="col-7">
                            @if($invoice->payment_status === 'paid')
                                <span class="badge badge-light-success">Đã thanh toán</span>
                            @elseif($invoice->payment_status === 'partial')
                                <span class="badge badge-light-warning">Thanh toán một phần</span>
                            @else
                                <span class="badge badge-light-danger">Chưa thanh toán</span>
                            @endif
                        </div>
                    </div>

                    @if($invoice->payment_method === 'transfer')
                        <!-- Bank Transfer Information -->
                        <div class="separator separator-dashed my-3"></div>
                        <div class="row mb-2">
                            <div class="col-12">
                                <h6 class="fw-bold text-info mb-3">
                                    <i class="fas fa-university me-2"></i>Thông tin chuyển khoản
                                </h6>
                            </div>
                        </div>

                        @if($invoice->reference_number)
                            <div class="row mb-3">
                                <div class="col-5 fw-semibold text-gray-600">Mã giao dịch:</div>
                                <div class="col-7 fw-bold text-info">{{ $invoice->reference_number }}</div>
                            </div>
                        @endif

                        <div class="row mb-3">
                            <div class="col-5 fw-semibold text-gray-600">Ngân hàng:</div>
                            <div class="col-7">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-university text-primary me-2"></i>
                                    <span class="fw-semibold">Vietcombank</span>
                                </div>
                                <div class="text-muted fs-7">Chi nhánh Hà Nội</div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-5 fw-semibold text-gray-600">Số tài khoản:</div>
                            <div class="col-7">
                                <div class="d-flex align-items-center">
                                    <span class="fw-bold text-dark">1234567890</span>
                                    <button class="btn btn-sm btn-light-primary ms-2" onclick="copyToClipboard('1234567890')" title="Copy số tài khoản">
                                        <i class="fas fa-copy fs-8"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-5 fw-semibold text-gray-600">Chủ tài khoản:</div>
                            <div class="col-7 fw-semibold">CONG TY YUKIMART</div>
                        </div>

                        @if($invoice->paid_at)
                            <div class="row mb-3">
                                <div class="col-5 fw-semibold text-gray-600">Thời gian CK:</div>
                                <div class="col-7">
                                    <span class="fw-semibold text-success">{{ $invoice->paid_at->format('d/m/Y H:i') }}</span>
                                </div>
                            </div>
                        @endif

                        <div class="row mb-3">
                            <div class="col-5 fw-semibold text-gray-600">Nội dung CK:</div>
                            <div class="col-7">
                                <div class="bg-light-info p-2 rounded">
                                    <span class="fw-semibold text-info">{{ $invoice->invoice_number }} {{ $invoice->customer->name ?? 'Khach le' }}</span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Additional Information -->
        <div class="col-md-6">
            <div class="card card-flush h-100">
                <div class="card-header">
                    <h6 class="card-title fw-bold text-info">Thông tin bổ sung</h6>
                </div>
                <div class="card-body pt-0">
                    <div class="row mb-3">
                        <div class="col-5 fw-semibold text-gray-600">Người tạo:</div>
                        <div class="col-7">{{ $invoice->creator->name ?? 'N/A' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-5 fw-semibold text-gray-600">Cập nhật:</div>
                        <div class="col-7">{{ $invoice->updated_at->format('d/m/Y H:i') }}</div>
                    </div>
                    @if($invoice->notes)
                        <div class="row mb-3">
                            <div class="col-5 fw-semibold text-gray-600">Ghi chú:</div>
                            <div class="col-7">{{ $invoice->notes }}</div>
                        </div>
                    @endif
                    
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Show success message
        toastr.success('Đã copy số tài khoản: ' + text);
    }, function(err) {
        // Fallback for older browsers
        var textArea = document.createElement("textarea");
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        try {
            document.execCommand('copy');
            toastr.success('Đã copy số tài khoản: ' + text);
        } catch (err) {
            toastr.error('Không thể copy số tài khoản');
        }
        document.body.removeChild(textArea);
    });
}
</script>
