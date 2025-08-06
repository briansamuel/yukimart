<!--begin::Invoice Detail Panel-->
<div class="detail-panel">
    <div class="card card-flush border-0 ">
        <div class="card-body border-0 p-0 shadow-none">
            
            <!-- Tab Navigation -->
            @php
                $payments = $invoice->payments()->where('payment_type', 'receipt')->count();
                $returnOrders = $invoice->returnOrders()->count();
            @endphp
            <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-4 fw-semibold mb-5" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link active" data-bs-toggle="tab" href="#kt_invoice_info_{{ $invoice->id }}" aria-selected="true" role="tab">
                        <i class="fas fa-info-circle me-2"></i>Thông tin
                    </a>
                </li>
                @if($payments > 0)
                <li class="nav-item" role="presentation">
                    <a class="nav-link" data-bs-toggle="tab" href="#kt_invoice_payment_{{ $invoice->id }}" aria-selected="false" role="tab" tabindex="-1">
                        <i class="fas fa-credit-card me-2"></i>Lịch sử thanh toán
                        <span class="badge badge-light-primary ms-2">{{ $payments }}</span>
                    </a>
                </li>
                @endif
                @if($returnOrders > 0)
                <li class="nav-item" role="presentation">
                    <a class="nav-link" data-bs-toggle="tab" href="#kt_invoice_returns_{{ $invoice->id }}" aria-selected="false" role="tab" tabindex="-1">
                        <i class="fas fa-undo me-2"></i>Lịch sử trả hàng
                        <span class="badge badge-light-warning ms-2">{{ $returnOrders }}</span>
                    </a>
                </li>
                @endif
            </ul>

            <!-- Tab Content -->
            <div class="tab-content">
                <!-- Tab 1: Thông tin -->
                <div class="tab-pane fade show active" id="kt_invoice_info_{{ $invoice->id }}" role="tabpanel">
                    <!-- Customer Header -->
                    <div class="d-flex align-items-center justify-content-between mb-6">
                        <div class="d-flex">
                            <h3 class="fw-bold text-gray-800 mx-5">
                                @if($invoice->customer_id > 0 && $invoice->customer)
                                    {{ $invoice->customer->name }}
                                @else
                                    Khách lẻ
                                @endif
                                <i class="fas fa-external-link-alt ms-2 text-primary fs-6"></i>
                            </h3>
                            <div class="fw-semibold text-gray-600 mx-5">{{ $invoice->invoice_number }}</div>
                            <span class="mx-5">{!! $invoice->status_badge !!}</span>
                        </div>
                    </div>

                    <!-- Invoice Information Row -->
                    <div class="row g-5 mb-6">
                        <div class="col-md-6">
                            <div class="fw-semibold text-gray-600 mb-1">Người tạo:</div>
                            <div class="fw-bold">{{ $invoice->creator->name ?? 'N/A' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="fw-semibold text-gray-600 mb-1">Người bán:</div>
                            <div class="fw-bold">{{ $invoice->creator->name ?? 'N/A' }}</div>
                        </div>
                    </div>

                    <div class="row g-5 mb-6">
                        <div class="col-md-6">
                            <div class="fw-semibold text-gray-600 mb-1">Ngày bán:</div>
                            <div class="fw-bold">{{ $invoice->created_at->format('d/m/Y H:i') }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="fw-semibold text-gray-600 mb-1">Kênh bán:</div>
                            <div class="fw-bold">Bán trực tiếp</div>
                        </div>
                    </div>

                    <div class="row g-5 mb-6">
                        <div class="col-md-6">
                            <div class="fw-semibold text-gray-600 mb-1">Bảng giá:</div>
                            <div class="fw-bold">Sale</div>
                        </div>
                    </div>

                    <!-- Cancellation Information -->
                    @if($invoice->status === 'cancelled' && $invoice->cancelled_at)
                    <div class="alert alert-danger d-flex align-items-center mb-6">
                        <i class="fas fa-exclamation-triangle me-3"></i>
                        <div>
                            <div class="fw-bold">Hóa đơn đã bị hủy</div>
                            <div class="text-muted fs-7">
                                Hủy bởi: {{ $invoice->canceller->full_name ?? $invoice->canceller->name ?? 'N/A' }}
                                vào {{ $invoice->cancelled_at->format('d/m/Y H:i') }}
                                ({{ $invoice->cancelled_at->diffForHumans() }})
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Customer Information -->
                    @if($invoice->customer_id > 0 && $invoice->customer)
                        <div class="row g-5 mb-6">
                            <div class="col-md-6">
                                <div class="fw-semibold text-gray-600 mb-1">Điện thoại:</div>
                                <div class="fw-bold">{{ $invoice->customer->phone ?? 'N/A' }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="fw-semibold text-gray-600 mb-1">Email:</div>
                                <div class="fw-bold">{{ $invoice->customer->email ?? 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="row g-5 mb-6">
                            <div class="col-md-12">
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
                                <div class="fw-semibold pe-10 text-gray-600 fs-7">Tổng tiền hàng ({{ $invoice->invoiceItems->count() }}):</div>
                                <div class="text-end fw-bold fs-6">{{ number_format($invoice->subtotal ?? 0, 0, ',', '.') }}</div>
                            </div>
                            <div class="d-flex flex-stack mb-3">
                                <div class="fw-semibold pe-10 text-gray-600 fs-7">Giảm giá hóa đơn:</div>
                                <div class="text-end fw-bold fs-6 text-danger">{{ number_format($invoice->discount_amount ?? 0, 0, ',', '.') }}</div>
                            </div>
                            <div class="d-flex flex-stack mb-3">
                                <div class="fw-semibold pe-10 text-gray-600 fs-7">Khách cần trả:</div>
                                <div class="text-end fw-bold fs-6">{{ number_format($invoice->total_amount, 0, ',', '.') }}</div>
                            </div>
                            <div class="d-flex flex-stack">
                                <div class="fw-semibold pe-10 text-gray-600 fs-7">Khách đã trả:</div>
                                <div class="text-end fw-bold fs-6 text-success">{{ number_format($invoice->paid_amount, 0, ',', '.') }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons inside Info Tab -->
                    @if($invoice->status !== 'cancelled')
                    <div class="separator separator-dashed my-5"></div>
                    <div class="d-flex justify-content-between">
                        <!-- Left side: Cancel button -->
                        <button type="button" class="btn btn-light btn-sm" onclick="cancelInvoice({{ $invoice->id }})">
                            <i class="fas fa-times me-2"></i>Hủy
                        </button>

                        <!-- Right side: Save and Return buttons -->
                        <div class="d-flex gap-3">
                            <button type="button" class="btn btn-success btn-sm" onclick="saveInvoice({{ $invoice->id }})">
                                <i class="fas fa-save me-2"></i>Lưu
                            </button>
                            <button type="button" class="btn btn-primary btn-sm" onclick="createReturnOrder({{ $invoice->id }})">
                                <i class="fas fa-undo me-2"></i>Trả hàng
                            </button>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Tab 2: Lịch sử thanh toán -->
                @if($payments > 0)
                <div class="tab-pane fade" id="kt_invoice_payment_{{ $invoice->id }}" role="tabpanel">
                    @php
                        $paymentsData = $invoice->payments()->where('payment_type', 'receipt')->orderBy('payment_date', 'desc')->get();
                    @endphp

                    @if($paymentsData->count() > 0)
                        <!-- Payment History Table -->
                        <div class="table-responsive">
                            <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                                <thead>
                                    <tr class="fw-bold text-muted">
                                        <th class="min-w-150px">Mã phiếu thu</th>
                                        <th class="min-w-120px">Ngày thanh toán</th>
                                        <th class="min-w-120px">Phương thức</th>
                                        <th class="min-w-100px text-end">Số tiền</th>
                                        <th class="min-w-100px">Trạng thái</th>
                                        <th class="min-w-120px">Người tạo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($paymentsData as $payment)
                                    <tr>
                                        <td>
                                            <a href="#" class="text-gray-800 text-hover-primary fw-bold">{{ $payment->payment_number }}</a>
                                        </td>
                                        <td class="text-muted fw-semibold">{{ $payment->payment_date->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($payment->payment_method === 'cash')
                                                    <i class="fas fa-money-bill text-success me-2"></i>
                                                    <span class="text-gray-800 fw-bold">Tiền mặt</span>
                                                @elseif($payment->payment_method === 'transfer')
                                                    <i class="fas fa-university text-primary me-2"></i>
                                                    <span class="text-gray-800 fw-bold">Chuyển khoản</span>
                                                @elseif($payment->payment_method === 'card')
                                                    <i class="fas fa-credit-card text-info me-2"></i>
                                                    <span class="text-gray-800 fw-bold">Thẻ</span>
                                                @else
                                                    <i class="fas fa-wallet text-warning me-2"></i>
                                                    <span class="text-gray-800 fw-bold">Khác</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <span class="text-success fw-bold fs-6">{{ number_format($payment->actual_amount, 0, ',', '.') }} ₫</span>
                                        </td>
                                        <td>
                                            @if($payment->status === 'completed')
                                                <span class="badge badge-light-success">Đã thanh toán</span>
                                            @elseif($payment->status === 'pending')
                                                <span class="badge badge-light-warning">Đang xử lý</span>
                                            @else
                                                <span class="badge badge-light-secondary">{{ ucfirst($payment->status) }}</span>
                                            @endif
                                        </td>
                                        <td class="text-muted fw-semibold">
                                            {{ $payment->creator->name ?? 'N/A' }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <!-- No Payment History -->
                        <div class="text-center py-10">
                            <div class="symbol symbol-100px mx-auto mb-5">
                                <span class="symbol-label bg-light-primary">
                                    <i class="fas fa-credit-card text-primary fs-1"></i>
                                </span>
                            </div>
                            <h3 class="text-gray-800 fw-bold mb-3">Chưa có lịch sử thanh toán</h3>
                            <p class="text-muted fs-6">Hóa đơn này chưa có giao dịch thanh toán nào.</p>
                        </div>
                    @endif

                </div>
                @endif

                <!-- Tab 3: Lịch sử trả hàng -->
                @if($returnOrders > 0)
                <div class="tab-pane fade" id="kt_invoice_returns_{{ $invoice->id }}" role="tabpanel">
                    @php
                        $returnOrdersData = $invoice->returnOrders()->orderBy('created_at', 'desc')->get();
                    @endphp

                    @if($returnOrdersData->count() > 0)
                        <!-- Return Orders Table -->
                        <div class="table-responsive">
                            <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                                <thead>
                                    <tr class="fw-bold text-muted">
                                        <th class="min-w-150px">Mã trả hàng</th>
                                        <th class="min-w-120px">Ngày trả</th>
                                        <th class="min-w-100px text-end">Tổng tiền trả</th>
                                        <th class="min-w-100px text-end">Tiền hoàn</th>
                                        <th class="min-w-100px">Trạng thái</th>
                                        <th class="min-w-120px">Người tạo</th>
                                        <th class="min-w-150px">Ghi chú</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($returnOrdersData as $returnOrder)
                                    <tr>
                                        <td>
                                            <a href="/admin/return-orders/{{ $returnOrder->id }}" class="text-gray-800 text-hover-primary fw-bold">{{ $returnOrder->return_number }}</a>
                                        </td>
                                        <td class="text-muted fw-semibold">{{ $returnOrder->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="text-end">
                                            <span class="text-danger fw-bold fs-6">{{ number_format($returnOrder->total_amount, 0, ',', '.') }} ₫</span>
                                        </td>
                                        <td class="text-end">
                                            <span class="text-warning fw-bold fs-6">{{ number_format($returnOrder->total_amount, 0, ',', '.') }} ₫</span>
                                        </td>
                                        <td>
                                            @if($returnOrder->status === 'completed')
                                                <span class="badge badge-light-success">Hoàn thành</span>
                                            @elseif($returnOrder->status === 'processing')
                                                <span class="badge badge-light-primary">Đang xử lý</span>
                                            @elseif($returnOrder->status === 'cancelled')
                                                <span class="badge badge-light-danger">Đã hủy</span>
                                            @else
                                                <span class="badge badge-light-secondary">{{ ucfirst($returnOrder->status) }}</span>
                                            @endif
                                        </td>
                                        <td class="text-muted fw-semibold">
                                            {{ $returnOrder->creator->name ?? 'N/A' }}
                                        </td>
                                        <td class="text-muted fw-semibold">
                                            {{ $returnOrder->notes ?? '-' }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <!-- No Return Orders -->
                        <div class="text-center py-10">
                            <div class="symbol symbol-100px mx-auto mb-5">
                                <span class="symbol-label bg-light-warning">
                                    <i class="fas fa-undo text-warning fs-1"></i>
                                </span>
                            </div>
                            <h3 class="text-gray-800 fw-bold mb-3">Chưa có lịch sử trả hàng</h3>
                            <p class="text-muted fs-6">Hóa đơn này chưa có đơn trả hàng nào.</p>
                        </div>
                    @endif

                </div>
                @endif
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

// Function to save invoice changes
function saveInvoice(invoiceId) {
    console.log('Saving invoice:', invoiceId);

    Swal.fire({
        title: 'Lưu thay đổi',
        text: 'Bạn có muốn lưu các thay đổi cho hóa đơn này?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Lưu',
        cancelButtonText: 'Hủy',
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Đang lưu...',
                text: 'Vui lòng đợi trong giây lát',
                icon: 'info',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Simulate saving (replace with actual AJAX call)
            setTimeout(() => {
                Swal.fire({
                    title: 'Lưu thành công',
                    text: 'Thay đổi đã được lưu',
                    icon: 'success',
                    confirmButtonText: 'OK',
                    timer: 2000
                });
            }, 1500);
        }
    });
}

// Function to cancel invoice
function cancelInvoice(invoiceId) {
    console.log('Cancelling invoice:', invoiceId);

    Swal.fire({
        title: 'Hủy hóa đơn',
        text: 'Bạn có chắc chắn muốn hủy hóa đơn này?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Hủy hóa đơn',
        cancelButtonText: 'Không',
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Đang hủy...',
                text: 'Vui lòng đợi trong giây lát',
                icon: 'info',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Make actual AJAX call to cancel invoice
            $.ajax({
                url: `/admin/invoices/${invoiceId}/cancel`,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    reason: 'Hủy từ detail panel'
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: 'Hủy thành công',
                            text: response.message || 'Hóa đơn đã được hủy',
                            icon: 'success',
                            confirmButtonText: 'OK',
                            timer: 2000
                        }).then(() => {
                            // Reload the page to update status
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Lỗi!',
                            text: response.message || 'Có lỗi xảy ra khi hủy hóa đơn',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Cancel invoice error:', error);
                    Swal.fire({
                        title: 'Lỗi!',
                        text: 'Có lỗi xảy ra khi hủy hóa đơn',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });
        }
    });
}

// Function to create return order from invoice
function createReturnOrder(invoiceId) {
    console.log('Creating return order for invoice:', invoiceId);

    // Open return order in new tab
    const returnUrl = `/admin/quick-order?type=return&invoice=${invoiceId}`;
    window.open(returnUrl, '_blank');
}
</script>
