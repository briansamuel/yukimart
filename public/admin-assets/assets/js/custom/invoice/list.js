"use strict";

// Class definition
var KTInvoiceList = function () {
    // Shared variables
    var table = document.getElementById('kt_invoices_table');
    var datatable;

    // Private functions
    var initInvoiceTable = function () {
        // Init datatable
        datatable = $(table).DataTable({
            "responsive": true,
            "searchDelay": 500,
            "processing": true,
            "serverSide": true,
            "order": [[0, 'desc']], // Order by invoice number desc
            "stateSave": false,
            "ajax": {
                "url": window.location.origin + "/admin/invoice/ajax",
                "type": "GET",
                "data": function(d) {
                    // Add filter parameters
                    d.status = $('[data-kt-invoice-table-filter="status"]').val();
                    d.payment_method = $('[data-kt-invoice-table-filter="payment_method"]').val();
                    d.branch_shop_id = $('[data-kt-invoice-table-filter="branch_shop"]').val();
                    d.date_range = $('[data-kt-invoice-table-filter="date_range"]').val();
                    d.amount_from = $('[data-kt-invoice-table-filter="amount_from"]').val();
                    d.amount_to = $('[data-kt-invoice-table-filter="amount_to"]').val();
                    d.customer = $('[data-kt-invoice-table-filter="customer"]').val();
                    return d;
                },
                "error": function(xhr, error, code) {
                    console.error('DataTables AJAX error:', error);
                    console.error('Response:', xhr.responseText);
                    console.error('Status code:', xhr.status);
                }
            },
            "columns": [
                { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false },
                { data: 'invoice_number', name: 'invoice_number' },
                { data: 'customer_display', name: 'customer_display', orderable: false },
                { data: 'total_amount', name: 'total_amount', className: 'text-end' },
                { data: 'amount_paid', name: 'amount_paid', className: 'text-end' },
                { data: 'payment_status', name: 'payment_status' },
                { data: 'payment_method', name: 'payment_method' },
                { data: 'channel', name: 'channel' },
                { data: 'created_at', name: 'created_at' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end' }
            ],
            "columnDefs": [
                {
                    "targets": 0, // Invoice number
                    "render": function (data, type, full, meta) {
                        return `<a href="/admin/invoice/${full.id}" class="text-gray-800 text-hover-primary fw-bold">${data}</a>`;
                    }
                },
                {
                    "targets": 1, // Customer info
                    "render": function (data, type, full, meta) {
                        return `
                            <div class="d-flex flex-column">
                                <span class="text-gray-800 fw-bold">${full.customer_name}</span>
                                <span class="text-muted fs-7">${full.customer_phone}</span>
                            </div>
                        `;
                    }
                },
                {
                    "targets": 2, // Invoice date
                    "render": function (data, type, full, meta) {
                        return data;
                    }
                },
                {
                    "targets": 3, // Due date
                    "render": function (data, type, full, meta) {
                        const isOverdue = full.is_overdue && full.payment_status !== 'paid';
                        const color = isOverdue ? 'text-danger' : 'text-gray-800';
                        return `<span class="${color}">${data}</span>`;
                    }
                },
                {
                    "targets": 4, // Status
                    "render": function (data, type, full, meta) {
                        const badges = {
                            'draft': '<span class="badge badge-light-secondary fs-7 fw-bold">Nháp</span>',
                            'sent': '<span class="badge badge-light-info fs-7 fw-bold">Đã gửi</span>',
                            'paid': '<span class="badge badge-light-success fs-7 fw-bold">Đã thanh toán</span>',
                            'overdue': '<span class="badge badge-light-danger fs-7 fw-bold">Quá hạn</span>',
                            'cancelled': '<span class="badge badge-light-dark fs-7 fw-bold">Đã hủy</span>'
                        };
                        return badges[data] || `<span class="badge badge-light-secondary fs-7 fw-bold">${data}</span>`;
                    }
                },
                {
                    "targets": 5, // Payment status
                    "render": function (data, type, full, meta) {
                        const badges = {
                            'unpaid': '<span class="badge badge-light-warning fs-7 fw-bold">Chưa TT</span>',
                            'partial': '<span class="badge badge-light-primary fs-7 fw-bold">TT một phần</span>',
                            'paid': '<span class="badge badge-light-success fs-7 fw-bold">Đã TT</span>',
                            'overpaid': '<span class="badge badge-light-info fs-7 fw-bold">TT thừa</span>'
                        };
                        return badges[data] || `<span class="badge badge-light-secondary fs-7 fw-bold">${data}</span>`;
                    }
                },
                {
                    "targets": 6, // Total amount
                    "render": function (data, type, full, meta) {
                        return new Intl.NumberFormat('vi-VN', {
                            style: 'currency',
                            currency: 'VND'
                        }).format(data);
                    }
                },
                {
                    "targets": 7, // Remaining amount
                    "render": function (data, type, full, meta) {
                        const amount = parseFloat(data) || 0;
                        const color = amount > 0 ? 'text-danger' : 'text-success';
                        return `<span class="fw-bold ${color}">` + new Intl.NumberFormat('vi-VN', {
                            style: 'currency',
                            currency: 'VND'
                        }).format(amount) + '</span>';
                    }
                },
                {
                    "targets": 8, // Actions
                    "render": function (data, type, full, meta) {
                        return `
                            <div class="d-flex justify-content-end flex-shrink-0">
                                <a href="/admin/invoice/${full.id}" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1" title="Xem chi tiết">
                                    <span class="svg-icon svg-icon-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                            <path d="M17.5 11H6.5C4 11 2 9 2 6.5C2 4 4 2 6.5 2H17.5C20 2 22 4 22 6.5C22 9 20 11 17.5 11ZM15 6.5C15 7.9 16.1 9 17.5 9C18.9 9 20 7.9 20 6.5C20 5.1 18.9 4 17.5 4C16.1 4 15 5.1 15 6.5Z" fill="black"/>
                                            <path opacity="0.3" d="M17.5 22H6.5C4 22 2 20 2 17.5C2 15 4 13 6.5 13H17.5C20 13 22 15 22 17.5C22 20 20 22 17.5 22ZM4 17.5C4 18.9 5.1 20 6.5 20C7.9 20 9 18.9 9 17.5C9 16.1 7.9 15 6.5 15C5.1 15 4 16.1 4 17.5Z" fill="black"/>
                                        </svg>
                                    </span>
                                </a>
                                ${full.status !== 'paid' && full.status !== 'cancelled' ? `
                                <a href="/admin/invoice/${full.id}/edit" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1" title="Chỉnh sửa">
                                    <span class="svg-icon svg-icon-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                            <path opacity="0.3" d="M21.4 8.35303L19.241 10.511L13.485 4.755L15.643 2.59595C16.0248 2.21423 16.5426 1.99988 17.0825 1.99988C17.6224 1.99988 18.1402 2.21423 18.522 2.59595L21.4 5.474C21.7817 5.85581 21.9962 6.37355 21.9962 6.91345C21.9962 7.45335 21.7817 7.97122 21.4 8.35303ZM3.68699 21.932L9.88699 19.865L4.13099 14.109L2.06399 20.309C1.98815 20.5354 1.97703 20.7787 2.03189 21.0111C2.08674 21.2436 2.2054 21.4561 2.37449 21.6248C2.54359 21.7934 2.75641 21.9115 2.989 21.9658C3.22158 22.0201 3.4647 22.0084 3.69099 21.932H3.68699Z" fill="black"/>
                                            <path d="M5.574 21.3L3.692 21.928C3.46591 22.0032 3.22334 22.0141 2.99144 21.9594C2.75954 21.9046 2.54744 21.7864 2.3789 21.6179C2.21036 21.4495 2.09202 21.2375 2.03711 21.0056C1.9822 20.7737 1.99289 20.5312 2.06799 20.3051L2.696 18.422L5.574 21.3Z" fill="black"/>
                                        </svg>
                                    </span>
                                </a>
                                ` : ''}
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm" data-bs-toggle="dropdown" aria-expanded="false" title="Thêm">
                                        <span class="svg-icon svg-icon-3">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                <rect opacity="0.3" x="2" y="2" width="20" height="20" rx="10" fill="black"/>
                                                <rect x="10.8891" y="17.8033" width="12" height="2" rx="1" transform="rotate(-90 10.8891 17.8033)" fill="black"/>
                                                <rect x="6.01041" y="10.9247" width="12" height="2" rx="1" fill="black"/>
                                            </svg>
                                        </span>
                                    </button>
                                    <ul class="dropdown-menu">
                                        ${full.payment_status !== 'paid' ? `
                                        <li><a class="dropdown-item" href="#" onclick="recordPayment(${full.id})">Ghi nhận thanh toán</a></li>
                                        ` : ''}
                                        ${full.status === 'draft' ? `
                                        <li><a class="dropdown-item" href="#" onclick="sendInvoice(${full.id})">Gửi hóa đơn</a></li>
                                        ` : ''}
                                        ${full.status !== 'paid' && full.status !== 'cancelled' ? `
                                        <li><a class="dropdown-item text-danger" href="#" onclick="cancelInvoice(${full.id})">Hủy hóa đơn</a></li>
                                        ` : ''}
                                        ${full.status !== 'paid' ? `
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger" href="#" onclick="deleteInvoice(${full.id})">Xóa hóa đơn</a></li>
                                        ` : ''}
                                    </ul>
                                </div>
                            </div>
                        `;
                    }
                }
            ],
            "language": {
                "lengthMenu": "Hiển thị _MENU_ hóa đơn",
                "zeroRecords": "Không tìm thấy hóa đơn nào",
                "info": "Hiển thị _START_ đến _END_ của _TOTAL_ hóa đơn",
                "infoEmpty": "Hiển thị 0 đến 0 của 0 hóa đơn",
                "infoFiltered": "(lọc từ _MAX_ tổng số hóa đơn)",
                "search": "Tìm kiếm:",
                "paginate": {
                    "first": "Đầu",
                    "last": "Cuối",
                    "next": "Tiếp",
                    "previous": "Trước"
                },
                "processing": "Đang xử lý..."
            }
        });

        // Re-init functions on every table re-draw
        datatable.on('draw', function () {
            KTMenu.createInstances();
        });
    }

    // Search Datatable
    var handleSearchDatatable = function () {
        const filterSearch = document.querySelector('[data-kt-invoice-table-filter="search"]');
        if (filterSearch) {
            filterSearch.addEventListener('keyup', function (e) {
                datatable.search(e.target.value).draw();
            });
        }
    }

    // Filter functions
    var handleFilterDatatable = function () {
        const filterForm = document.querySelector('#kt_invoice_filter_form');
        const resetButton = document.querySelector('#kt_invoice_filter_reset');

        if (filterForm) {
            filterForm.addEventListener('submit', function (e) {
                e.preventDefault();
                datatable.ajax.reload();
            });
        }

        if (resetButton) {
            resetButton.addEventListener('click', function () {
                // Reset all filter inputs
                $('#kt_invoice_filter_form select').val('').trigger('change');
                $('#kt_invoice_filter_form input').val('');

                // Reload table
                datatable.ajax.reload();
            });
        }
    }

    // Initialize date range picker
    var initDateRangePicker = function () {
        const dateRangePicker = document.querySelector('#kt_invoice_daterangepicker');
        if (dateRangePicker) {
            $(dateRangePicker).daterangepicker({
                locale: {
                    format: 'DD/MM/YYYY',
                    separator: ' - ',
                    applyLabel: 'Áp dụng',
                    cancelLabel: 'Hủy',
                    fromLabel: 'Từ',
                    toLabel: 'Đến',
                    customRangeLabel: 'Tùy chọn',
                    weekLabel: 'T',
                    daysOfWeek: ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'],
                    monthNames: ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6',
                        'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'],
                    firstDay: 1
                },
                ranges: {
                    'Hôm nay': [moment(), moment()],
                    'Hôm qua': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    '7 ngày qua': [moment().subtract(6, 'days'), moment()],
                    '30 ngày qua': [moment().subtract(29, 'days'), moment()],
                    'Tháng này': [moment().startOf('month'), moment().endOf('month')],
                    'Tháng trước': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                },
                autoUpdateInput: false
            });

            $(dateRangePicker).on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
            });

            $(dateRangePicker).on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
            });
        }
    }

    // Load statistics
    var loadStatistics = function() {
        $.ajax({
            url: window.location.origin + '/admin/invoice/statistics',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const stats = response.data;
                    $('#total-invoices').text(stats.total_invoices || 0);
                    $('#paid-invoices').text(stats.paid_invoices || 0);
                    $('#unpaid-invoices').text(stats.unpaid_invoices || 0);
                    $('#overdue-invoices').text(stats.overdue_invoices || 0);
                } else {
                    console.error('Statistics API returned error:', response.message);
                    // Set default values on error
                    $('#total-invoices').text('0');
                    $('#paid-invoices').text('0');
                    $('#unpaid-invoices').text('0');
                    $('#overdue-invoices').text('0');
                }
            },
            error: function(xhr, status, error) {
                console.error('Failed to load statistics:', error);
                console.error('Response:', xhr.responseText);
                // Set default values on error
                $('#total-invoices').text('0');
                $('#paid-invoices').text('0');
                $('#unpaid-invoices').text('0');
                $('#overdue-invoices').text('0');
            }
        });
    }

    // Public methods
    return {
        init: function () {
            table = document.querySelector('#kt_invoices_table');

            if (!table) {
                return;
            }

            initInvoiceTable();
            handleSearchDatatable();
            handleFilterDatatable();
            initDateRangePicker();
        },

        // Public method to reload table
        reload: function() {
            if (datatable) {
                datatable.ajax.reload();
                loadStatistics();
            }
        },

        // Public method to get datatable instance
        getDataTable: function() {
            return datatable;
        }
    }
}();

// On document ready
KTUtil.onDOMContentLoaded(function () {
    KTInvoiceList.init();
});

// Global functions for inline onclick events
window.recordPayment = function(invoiceId) {
    // TODO: Implement payment recording modal
    Swal.fire({
        title: 'Ghi nhận thanh toán',
        text: 'Chức năng đang được phát triển',
        icon: 'info',
        confirmButtonText: 'OK'
    });
};

window.sendInvoice = function(invoiceId) {
    Swal.fire({
        title: 'Xác nhận gửi hóa đơn',
        text: 'Bạn có chắc chắn muốn gửi hóa đơn này?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Gửi',
        cancelButtonText: 'Hủy'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/admin/invoice/${invoiceId}/send`,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Thành công!', response.message, 'success');
                        KTInvoiceList.reload();
                    } else {
                        Swal.fire('Lỗi!', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Lỗi!', 'Có lỗi xảy ra khi gửi hóa đơn', 'error');
                }
            });
        }
    });
};

window.cancelInvoice = function(invoiceId) {
    Swal.fire({
        title: 'Hủy hóa đơn',
        input: 'textarea',
        inputLabel: 'Lý do hủy',
        inputPlaceholder: 'Nhập lý do hủy hóa đơn...',
        showCancelButton: true,
        confirmButtonText: 'Hủy hóa đơn',
        cancelButtonText: 'Đóng',
        confirmButtonColor: '#d33'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/admin/invoice/${invoiceId}/cancel`,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    reason: result.value || 'Hủy theo yêu cầu'
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Thành công!', response.message, 'success');
                        KTInvoiceList.reload();
                    } else {
                        Swal.fire('Lỗi!', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Lỗi!', 'Có lỗi xảy ra khi hủy hóa đơn', 'error');
                }
            });
        }
    });
};

window.deleteInvoice = function(invoiceId) {
    Swal.fire({
        title: 'Xác nhận xóa',
        text: 'Bạn có chắc chắn muốn xóa hóa đơn này? Hành động này không thể hoàn tác!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Xóa',
        cancelButtonText: 'Hủy',
        confirmButtonColor: '#d33'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/admin/invoice/${invoiceId}`,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Thành công!', response.message, 'success');
                        KTInvoiceList.reload();
                    } else {
                        Swal.fire('Lỗi!', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Lỗi!', 'Có lỗi xảy ra khi xóa hóa đơn', 'error');
                }
            });
        }
    });
};
