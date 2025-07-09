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

    <!--begin::Action Buttons-->
    <div class="row mt-4">
        <div class="col-12">
            <div class="d-flex justify-content-end pt-4 border-top">
                <button type="button" class="btn btn-primary me-3" onclick="updateInvoice({{ $invoice->id }})">
                    <i class="fas fa-edit me-2"></i>Cập nhật
                </button>
                <button type="button" class="btn btn-success me-3" onclick="showPrintModal({{ $invoice->id }})">
                    <i class="fas fa-print me-2"></i>In
                </button>
                <button type="button" class="btn btn-info me-3" onclick="exportInvoice({{ $invoice->id }})">
                    <i class="fas fa-file-export me-2"></i>Xuất file
                </button>
                <button type="button" class="btn btn-secondary me-3" onclick="sendInvoice({{ $invoice->id }})">
                    <i class="fas fa-paper-plane me-2"></i>Gửi
                </button>
                @if($invoice->payment_status != 'paid')
                <button type="button" class="btn btn-warning" onclick="recordPayment({{ $invoice->id }})">
                    <i class="fas fa-dollar-sign me-2"></i>Ghi nhận TT
                </button>
                @endif
            </div>
        </div>
    </div>
    <!--end::Action Buttons-->
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

function updateInvoice(invoiceId) {
    console.log('Update invoice:', invoiceId);

    // Redirect to invoice edit page
    const editUrl = `/admin/invoices/${invoiceId}/edit`;
    window.location.href = editUrl;
}

function exportInvoice(invoiceId) {
    console.log('Export invoice:', invoiceId);

    // Show loading message
    Swal.fire({
        title: 'Đang xuất file...',
        text: 'Vui lòng đợi trong giây lát',
        icon: 'info',
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Create download link
    const exportUrl = `/admin/invoices/${invoiceId}/export`;
    const link = document.createElement('a');
    link.href = exportUrl;
    link.download = `invoice-${invoiceId}.pdf`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    // Close loading and show success
    setTimeout(() => {
        Swal.fire({
            title: 'Xuất file thành công',
            text: 'File đã được tải xuống',
            icon: 'success',
            confirmButtonText: 'OK',
            timer: 2000
        });
    }, 1000);
}

function sendInvoice(invoiceId) {
    console.log('Send invoice:', invoiceId);

    Swal.fire({
        title: 'Gửi hóa đơn',
        text: 'Bạn có muốn gửi hóa đơn này qua email?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Gửi',
        cancelButtonText: 'Hủy',
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Đang gửi...',
                text: 'Vui lòng đợi trong giây lát',
                icon: 'info',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Simulate sending (replace with actual AJAX call)
            setTimeout(() => {
                Swal.fire({
                    title: 'Gửi thành công',
                    text: 'Hóa đơn đã được gửi qua email',
                    icon: 'success',
                    confirmButtonText: 'OK',
                    timer: 2000
                });
            }, 2000);
        }
    });
}

function recordPayment(invoiceId) {
    console.log('Record payment for invoice:', invoiceId);

    Swal.fire({
        title: 'Ghi nhận thanh toán',
        html: `
            <div class="mb-3">
                <label class="form-label">Số tiền thanh toán:</label>
                <input type="number" id="paymentAmount" class="form-control" placeholder="Nhập số tiền">
            </div>
            <div class="mb-3">
                <label class="form-label">Phương thức thanh toán:</label>
                <select id="paymentMethod" class="form-select">
                    <option value="cash">Tiền mặt</option>
                    <option value="transfer">Chuyển khoản</option>
                    <option value="card">Thẻ</option>
                    <option value="other">Khác</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Ghi chú:</label>
                <textarea id="paymentNote" class="form-control" rows="2" placeholder="Ghi chú (tùy chọn)"></textarea>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Ghi nhận',
        cancelButtonText: 'Hủy',
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#d33',
        preConfirm: () => {
            const amount = document.getElementById('paymentAmount').value;
            const method = document.getElementById('paymentMethod').value;
            const note = document.getElementById('paymentNote').value;

            if (!amount || amount <= 0) {
                Swal.showValidationMessage('Vui lòng nhập số tiền hợp lệ');
                return false;
            }

            return { amount, method, note };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const { amount, method, note } = result.value;

            // Show loading
            Swal.fire({
                title: 'Đang xử lý...',
                text: 'Vui lòng đợi trong giây lát',
                icon: 'info',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Simulate payment recording (replace with actual AJAX call)
            setTimeout(() => {
                Swal.fire({
                    title: 'Ghi nhận thành công',
                    text: `Đã ghi nhận thanh toán ${new Intl.NumberFormat('vi-VN').format(amount)}đ`,
                    icon: 'success',
                    confirmButtonText: 'OK',
                    timer: 2000
                }).then(() => {
                    // Reload the detail panel to update payment status
                    location.reload();
                });
            }, 1500);
        }
    });
}
</script>



<script>
function showPrintModal(invoiceId) {
    console.log('Show print modal for invoice:', invoiceId);

    Swal.fire({
        title: 'Chọn template in',
        html: `
            <div class="mb-3">
                <label class="form-label">Chọn template:</label>
                <select id="printTemplate" class="form-select">
                    <option value="standard">Template mặc định</option>
                    <option value="retail">Template bán lẻ</option>
                    <option value="sale">Template khuyến mãi</option>
                </select>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'In hóa đơn',
        cancelButtonText: 'Hủy',
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        preConfirm: () => {
            const template = document.getElementById('printTemplate').value;
            return { template };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const { template } = result.value;
            console.log('Print invoice:', invoiceId, 'with template:', template);

            // Create print content URL
            const printUrl = `/admin/invoices/${invoiceId}/print?template=${template}`;

            // Create iframe for printing
            const iframe = document.createElement('iframe');
            iframe.style.position = 'absolute';
            iframe.style.top = '-9999px';
            iframe.style.left = '-9999px';
            iframe.style.width = '0px';
            iframe.style.height = '0px';
            iframe.style.border = 'none';

            document.body.appendChild(iframe);

            iframe.onload = function() {
                try {
                    // Print the iframe content
                    iframe.contentWindow.focus();
                    iframe.contentWindow.print();

                    // Remove iframe after printing
                    setTimeout(() => {
                        document.body.removeChild(iframe);
                    }, 1000);
                } catch (error) {
                    console.error('Print error:', error);
                    // Fallback: open in new window if iframe fails
                    window.open(printUrl, '_blank', 'width=800,height=600,scrollbars=yes,resizable=yes');
                    document.body.removeChild(iframe);
                }
            };

            iframe.src = printUrl;
        }
    });
}
</script>
