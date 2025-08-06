<!--begin::Order Detail Panel-->
<div class="detail-panel">
    <div class="card card-flush border-0 ">
        <div class="card-body border-0 p-0 shadow-none">
            <!--begin::Order Header-->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start mb-6">
                <div class="d-flex align-items-center mb-3 mb-md-0">
                    <h3 class="fw-bold text-gray-800 me-3">{{ $order->order_code }}</h3>
                    {!! $order->status_badge !!}
                </div>
                <div class="text-end">
                    <div class="fw-semibold text-gray-600 mb-1">Tổng tiền:</div>
                    <div class="fw-bold fs-3 text-primary">{{ number_format($order->final_amount, 0, ',', '.') }} ₫</div>
                </div>
            </div>
            <!--end::Order Header-->

            <!-- Tab Navigation -->
            <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-4 fw-semibold mb-5" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link active" data-bs-toggle="tab" href="#kt_order_info_{{ $order->id }}" aria-selected="true" role="tab">
                        <i class="fas fa-info-circle me-2"></i>Thông tin
                    </a>
                </li>
                <li class="nav-item order-invoices-tab" role="presentation" style="display: none;" data-order-id="{{ $order->id }}">
                    <a class="nav-link" data-bs-toggle="tab" href="#kt_order_invoices_{{ $order->id }}" aria-selected="false" role="tab" tabindex="-1">
                        <i class="fas fa-file-invoice me-2"></i>Lịch sử hóa đơn
                    </a>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content">
                <!-- Tab 1: Thông tin -->
                <div class="tab-pane fade show active" id="kt_order_info_{{ $order->id }}" role="tabpanel">
                    @include('admin.orders.partials.detail', ['order' => $order])
                </div>
                <!--end:::Tab pane-->

                <!-- Tab 2: Lịch sử hóa đơn -->
                <div class="tab-pane fade" id="kt_order_invoices_{{ $order->id }}" role="tabpanel">
                    <div class="order-invoices-container">
                        <div class="d-flex justify-content-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Đang tải...</span>
                            </div>
                            <div class="ms-3 text-muted">Đang tải danh sách hóa đơn...</div>
                        </div>
                    </div>
                </div>
                <!--end:::Tab pane-->
            </div>
            <!--end:::Tab content-->
        </div>
    </div>
</div>
<!--end::Order Detail Panel-->

<script>
// Initialize Bootstrap tabs for this detail panel
$(document).ready(function() {
    // Initialize tabs for this specific order detail panel
    var orderId = '{{ $order->id }}';
    var tabContainer = '#kt_order_info_' + orderId;

    // Define functions first before calling them
    /**
     * Check if order has invoices and show/hide invoice tab
     */
    window.checkOrderInvoices = function(orderId) {
        fetch(`/admin/orders/${orderId}/invoices`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data && data.data.length > 0) {
                    // Show invoice tab if order has invoices
                    $('.order-invoices-tab[data-order-id="' + orderId + '"]').show();
                } else {
                    // Hide invoice tab if order has no invoices
                    $('.order-invoices-tab[data-order-id="' + orderId + '"]').hide();
                }
            })
            .catch(error => {
                console.error('Error checking order invoices:', error);
                // Hide invoice tab on error
                $('.order-invoices-tab[data-order-id="' + orderId + '"]').hide();
            });
    }

    /**
     * Load order invoices data
     */
    window.loadOrderInvoices = function(orderId, targetTab) {
        console.log('Loading invoices for order:', orderId);

        fetch(`/admin/orders/${orderId}/invoices`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderOrderInvoices(data.data, targetTab);
                    $(targetTab).addClass('invoices-loaded');
                } else {
                    showInvoicesError('Không thể tải danh sách hóa đơn: ' + data.message, targetTab);
                }
            })
            .catch(error => {
                console.error('Error loading order invoices:', error);
                showInvoicesError('Lỗi khi tải danh sách hóa đơn', targetTab);
            });
    }

    /**
     * Render order invoices table
     */
    window.renderOrderInvoices = function(invoices, targetTab) {
        var html = '';

        if (invoices.length === 0) {
            html = `
                <div class="text-center py-5">
                    <i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Chưa có hóa đơn nào</h5>
                    <p class="text-muted">Đơn hàng này chưa có hóa đơn liên kết.</p>
                </div>
            `;
        } else {
            html = `
                <div class="table-responsive">
                    <table class="table table-row-bordered table-row-gray-100 align-middle gs-0 gy-3">
                        <thead>
                            <tr class="fw-bold text-muted">
                                <th class="min-w-150px">Mã hóa đơn</th>
                                <th class="min-w-120px">Thời gian</th>
                                <th class="min-w-100px">Người tạo</th>
                                <th class="min-w-100px text-end">Giá trị</th>
                                <th class="min-w-80px text-center">Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            invoices.forEach(function(invoice) {
                html += `
                    <tr>
                        <td>
                            <a href="${invoice.detail_url}" class="text-primary fw-bold text-hover-primary" target="_blank">
                                ${invoice.invoice_number}
                            </a>
                        </td>
                        <td class="text-muted">${invoice.invoice_date}</td>
                        <td class="text-muted">${invoice.creator_name}</td>
                        <td class="text-end fw-bold">${invoice.total_amount_formatted}</td>
                        <td class="text-center">${invoice.status_badge}</td>
                    </tr>
                `;
            });

            html += `
                        </tbody>
                    </table>
                </div>
            `;
        }

        $(targetTab).find('.order-invoices-container').html(html);
    }

    /**
     * Show invoices error message
     */
    window.showInvoicesError = function(message, targetTab) {
        var html = `
            <div class="text-center py-5">
                <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                <h5 class="text-muted">Lỗi tải dữ liệu</h5>
                <p class="text-muted">${message}</p>
                <button class="btn btn-sm btn-primary" onclick="window.loadOrderInvoices('{{ $order->id }}', '${targetTab}')">
                    <i class="fas fa-redo me-1"></i>Thử lại
                </button>
            </div>
        `;

        $(targetTab).find('.order-invoices-container').html(html);
    }

    // Now call the function after it's defined
    checkOrderInvoices(orderId);

    // Initialize Bootstrap tab functionality
    $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        var target = e.target.getAttribute('href');
        console.log('Order tab switched:', target);
        
        // If switching to invoices tab and not loaded yet, load invoices
        if (target.includes('kt_order_invoices_') && !$(target).hasClass('invoices-loaded')) {
            loadOrderInvoices(orderId, target);
        }
    });

    // Ensure tabs work properly
    setTimeout(function() {
        $('a[data-bs-toggle="tab"]').each(function() {
            var $this = $(this);
            var target = $this.attr('href');

            $this.off('click.orderTab').on('click.orderTab', function(e) {
                e.preventDefault();
                e.stopPropagation();

                // Remove active class from all tabs in this panel
                $this.closest('.nav-tabs').find('.nav-link').removeClass('active');
                $this.addClass('active');

                // Hide all tab panes in this panel
                $this.closest('.card-body').find('.tab-pane').removeClass('show active');

                // Show target tab pane
                $(target).addClass('show active');

                console.log('Order tab clicked:', target);
                
                // If switching to invoices tab and not loaded yet, load invoices
                if (target.includes('kt_order_invoices_') && !$(target).hasClass('invoices-loaded')) {
                    loadOrderInvoices(orderId, target);
                }
            });
        });
    }, 100);
});
</script>
