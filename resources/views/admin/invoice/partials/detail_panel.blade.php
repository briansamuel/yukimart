<div class="invoice-detail-panel">
    <div class="card card-flush border-0 shadow-none">
        <div class="card-body p-0">
            <!-- Tab Navigation -->
            <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-4 fw-semibold mb-5" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link active" data-bs-toggle="tab" href="#kt_invoice_info_{{ $invoice->id }}" aria-selected="true" role="tab">
                        <i class="fas fa-info-circle me-2"></i>Thông tin
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" data-bs-toggle="tab" href="#kt_invoice_payment_{{ $invoice->id }}" aria-selected="false" role="tab" tabindex="-1">
                        <i class="fas fa-credit-card me-2"></i>Lịch sử thanh toán
                    </a>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content">
                <!-- Tab 1: Thông tin -->
                <div class="tab-pane fade show active" id="kt_invoice_info_{{ $invoice->id }}" role="tabpanel">
                    <!-- Customer Header -->
                    <div class="d-flex align-items-center justify-content-between mb-6">
                        <div>
                            <h3 class="fw-bold text-gray-800 mb-1">
                                @if($invoice->customer_id > 0 && $invoice->customer)
                                    {{ $invoice->customer->name }}
                                @else
                                    Khách lẻ
                                @endif
                            </h3>
                            <span class="badge badge-light-success fs-7">{{ ucfirst($invoice->status) }}</span>
                        </div>
                    </div>

                    <!-- Invoice Information Row -->
                    <div class="row g-5 mb-6">
                        <div class="col-md-4">
                            <div class="fw-semibold text-gray-600 mb-1">Người tạo:</div>
                            <div class="fw-bold">{{ $invoice->creator->name ?? 'N/A' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="fw-semibold text-gray-600 mb-1">Ngày tạo:</div>
                            <div class="fw-bold">{{ $invoice->created_at->format('d/m/Y H:i') }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="fw-semibold text-gray-600 mb-1">Kênh bán:</div>
                            <div class="fw-bold">
                                @if($invoice->channel === 'online')
                                    Online
                                @elseif($invoice->channel === 'pos')
                                    POS
                                @else
                                    Trực tiếp
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="row g-5 mb-6">
                        <div class="col-md-4">
                            <div class="fw-semibold text-gray-600 mb-1">Mã hóa đơn:</div>
                            <div class="fw-bold text-primary">{{ $invoice->invoice_number }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="fw-semibold text-gray-600 mb-1">Chi nhánh:</div>
                            <div class="fw-bold">{{ $invoice->branchShop->name ?? 'N/A' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="fw-semibold text-gray-600 mb-1">Bảng giá:</div>
                            <div class="fw-bold">Sale</div>
                        </div>
                    </div>

                    <!-- Customer Information -->
                    @if($invoice->customer_id > 0 && $invoice->customer)
                        <div class="row g-5 mb-6">
                            <div class="col-md-4">
                                <div class="fw-semibold text-gray-600 mb-1">Điện thoại:</div>
                                <div class="fw-bold">{{ $invoice->customer->phone ?? 'N/A' }}</div>
                            </div>
                            <div class="col-md-4">
                                <div class="fw-semibold text-gray-600 mb-1">Email:</div>
                                <div class="fw-bold">{{ $invoice->customer->email ?? 'N/A' }}</div>
                            </div>
                            <div class="col-md-4">
                                <div class="fw-semibold text-gray-600 mb-1">Địa chỉ:</div>
                                <div class="fw-bold">{{ $invoice->customer->address ?? 'N/A' }}</div>
                            </div>
                        </div>
                    @endif

                    <!-- Products Table -->
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
                                    <td class="text-end">{{ number_format($item->unit_price, 0, ',', '.') }}₫</td>
                                    <td class="text-end text-danger">{{ number_format($item->discount_amount ?? 0, 0, ',', '.') }}₫</td>
                                    <td class="text-end fw-bold">{{ number_format($item->selling_price, 0, ',', '.') }}₫</td>
                                    <td class="text-end fw-bold text-primary">{{ number_format($item->total_amount, 0, ',', '.') }}₫</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">Không có sản phẩm nào</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Summary -->
                    <div class="d-flex justify-content-end">
                        <div class="mw-300px">
                            <div class="d-flex flex-stack mb-3">
                                <div class="fw-semibold pe-10 text-gray-600 fs-7">Tổng tiền hàng:</div>
                                <div class="text-end fw-bold fs-6">{{ number_format($invoice->subtotal ?? 0, 0, ',', '.') }}₫</div>
                            </div>
                            <div class="d-flex flex-stack mb-3">
                                <div class="fw-semibold pe-10 text-gray-600 fs-7">Giảm giá:</div>
                                <div class="text-end fw-bold fs-6 text-danger">{{ number_format($invoice->discount_amount ?? 0, 0, ',', '.') }}₫</div>
                            </div>
                            <div class="d-flex flex-stack mb-3">
                                <div class="fw-semibold pe-10 text-gray-600 fs-7">Khách cần trả:</div>
                                <div class="text-end fw-bold fs-6">{{ number_format($invoice->total_amount, 0, ',', '.') }}₫</div>
                            </div>
                            <div class="d-flex flex-stack">
                                <div class="fw-semibold pe-10 text-gray-600 fs-7">Khách đã trả:</div>
                                <div class="text-end fw-bold fs-6 text-success">{{ number_format($invoice->amount_paid, 0, ',', '.') }}₫</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab 2: Lịch sử thanh toán -->
                <div class="tab-pane fade" id="kt_invoice_payment_{{ $invoice->id }}" role="tabpanel">
                    <!-- Payment Summary -->
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
                                    <div class="fw-bold fs-5 text-primary">{{ number_format($invoice->total_amount, 0, ',', '.') }}₫</div>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted fs-7">Đã thanh toán</div>
                                    <div class="fw-bold fs-5 text-success">{{ number_format($invoice->amount_paid, 0, ',', '.') }}₫</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="card mb-5">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-5">
                                <div class="d-flex align-items-center flex-grow-1">
                                    <div class="symbol symbol-45px me-5">
                                        @if($invoice->payment_method === 'cash')
                                            <span class="symbol-label bg-light-success">
                                                <i class="fas fa-money-bill text-success fs-2x"></i>
                                            </span>
                                        @elseif($invoice->payment_method === 'transfer')
                                            <span class="symbol-label bg-light-primary">
                                                <i class="fas fa-university text-primary fs-2x"></i>
                                            </span>
                                        @elseif($invoice->payment_method === 'card')
                                            <span class="symbol-label bg-light-info">
                                                <i class="fas fa-credit-card text-info fs-2x"></i>
                                            </span>
                                        @else
                                            <span class="symbol-label bg-light-warning">
                                                <i class="fas fa-wallet text-warning fs-2x"></i>
                                            </span>
                                        @endif
                                    </div>
                                    <div class="d-flex flex-column">
                                        <span class="text-gray-800 fw-bold fs-6">
                                            @if($invoice->payment_method === 'cash')
                                                Tiền mặt
                                            @elseif($invoice->payment_method === 'transfer')
                                                Chuyển khoản
                                            @elseif($invoice->payment_method === 'card')
                                                Thẻ
                                            @else
                                                Khác
                                            @endif
                                        </span>
                                        <span class="text-muted fw-semibold">Phương thức thanh toán</span>
                                    </div>
                                </div>
                                <div>
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
                                <div class="separator separator-dashed my-5"></div>
                                <div class="mb-5">
                                    <h3 class="fw-bold text-gray-800 mb-3">Thông tin chuyển khoản</h3>
                                    <div class="row g-5">
                                        <div class="col-md-6">
                                            <div class="fw-semibold text-gray-600 mb-1">Ngân hàng:</div>
                                            <div class="fw-bold d-flex align-items-center">
                                                <i class="fas fa-university text-primary me-2"></i>
                                                Vietcombank
                                                <span class="text-muted fs-7 ms-2">(Chi nhánh Hà Nội)</span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="fw-semibold text-gray-600 mb-1">Số tài khoản:</div>
                                            <div class="fw-bold d-flex align-items-center">
                                                <span class="text-dark">1234567890</span>
                                                <button class="btn btn-sm btn-icon btn-light-primary ms-2" onclick="copyToClipboard('1234567890')" title="Copy số tài khoản">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="fw-semibold text-gray-600 mb-1">Chủ tài khoản:</div>
                                            <div class="fw-bold">CONG TY YUKIMART</div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="fw-semibold text-gray-600 mb-1">Nội dung CK:</div>
                                            <div class="bg-light-info p-2 rounded">
                                                <span class="fw-semibold text-info">{{ $invoice->invoice_number }} {{ $invoice->customer->name ?? 'Khach le' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if($invoice->notes)
                                <div class="separator separator-dashed my-5"></div>
                                <div>
                                    <h3 class="fw-bold text-gray-800 mb-3">Ghi chú</h3>
                                    <div class="bg-light-warning p-4 rounded">
                                        {{ $invoice->notes }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
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

// Initialize Bootstrap tabs for this detail panel
$(document).ready(function() {
    // Initialize tabs for this specific invoice detail panel
    var invoiceId = '{{ $invoice->id }}';
    var tabContainer = '#kt_invoice_info_' + invoiceId;

    // Initialize Bootstrap tab functionality
    $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        console.log('Tab switched:', e.target.getAttribute('href'));
    });

    // Ensure tabs work properly
    setTimeout(function() {
        $('a[data-bs-toggle="tab"]').each(function() {
            var $this = $(this);
            var target = $this.attr('href');

            $this.off('click.invoiceTab').on('click.invoiceTab', function(e) {
                e.preventDefault();
                e.stopPropagation();

                // Remove active class from all tabs in this panel
                $this.closest('.nav-tabs').find('.nav-link').removeClass('active');
                $this.addClass('active');

                // Hide all tab panes in this panel
                $this.closest('.card-body').find('.tab-pane').removeClass('show active');

                // Show target tab pane
                $(target).addClass('show active');

                console.log('Invoice tab clicked:', target);
            });
        });
    }, 100);
});
</script>
