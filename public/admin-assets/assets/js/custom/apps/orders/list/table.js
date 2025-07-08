"use strict";

// Class definition
var KTOrdersList = function () {
    // Define shared variables
    var table = document.getElementById('kt_orders_table');
    var datatable;
    var toolbarBase;
    var toolbarSelected;
    var selectedCount;

    // Private functions
    var initOrderTable = function () {
        // Init datatable
        datatable = $(table).DataTable({
            "responsive": true,
            "searchDelay": 500,
            "processing": true,
            "serverSide": true,
            "order": [[1, 'desc']], // Order by created date
            "stateSave": false,
            "ajax": {
                "url": "/admin/order/ajax",
                "type": "GET",
                "data": function(d) {
                    // Add filters from modal and quick filters
                    d.status = $('[data-kt-orders-table-filter="status"]').val();
                    d.delivery_status = $('[data-kt-orders-table-filter="delivery_status"]').val();
                    d.payment_status = $('[data-kt-orders-table-filter="payment_status"]').val();
                    d.channel = $('[data-kt-orders-table-filter="channel"]').val();
                    d.branch_shop_id = $('[data-kt-orders-table-filter="branch_shop_id"]').val();
                    d.date_from = $('[data-kt-orders-table-filter="date_from"]').val();
                    d.date_to = $('[data-kt-orders-table-filter="date_to"]').val();
                    d.amount_from = $('[data-kt-orders-table-filter="amount_from"]').val();
                    d.amount_to = $('[data-kt-orders-table-filter="amount_to"]').val();
                    d.customer = $('[data-kt-orders-table-filter="customer"]').val();
                    return d;
                }
            },
            "columns": [
                { 
                    data: 'id', 
                    name: 'id', 
                    orderable: false, 
                    searchable: false, 
                    render: function(data, type, row) {
                        return '<div class="form-check form-check-sm form-check-custom form-check-solid">' +
                               '<input class="form-check-input widget-9-check" type="checkbox" value="' + data + '" />' +
                               '</div>';
                    }
                },
                { data: 'order_code', name: 'order_code' },
                { data: 'customer_name', name: 'customer_name' },
                { data: 'total_quantity', name: 'total_quantity', className: 'text-center' },
                { data: 'final_amount', name: 'final_amount', className: 'text-end' },
                { data: 'amount_paid', name: 'amount_paid', className: 'text-end' },
                {
                    data: 'status',
                    name: 'status',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                },
                {
                    data: 'payment_status',
                    name: 'payment_status',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                },
                {
                    data: 'delivery_status',
                    name: 'delivery_status',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                },
                { data: 'channel', name: 'channel', className: 'text-center' },
                { data: 'created_at', name: 'created_at', className: 'text-center' },
                { 
                    data: 'actions', 
                    name: 'actions', 
                    orderable: false, 
                    searchable: false,
                    className: 'text-end'
                }
            ],
            "columnDefs": [
                {
                    targets: 0,
                    width: '30px',
                    className: 'text-center',
                    render: function (data, type, row) {
                        return `
                            <div class="form-check form-check-sm form-check-custom form-check-solid">
                                <input class="form-check-input widget-9-check" type="checkbox" value="${row.id}" />
                            </div>`;
                    }
                },
                {
                    targets: 1,
                    render: function (data, type, row) {
                        return `
                            <div class="d-flex align-items-center">
                                <div class="d-flex flex-column">
                                    <a href="#" class="text-gray-800 text-hover-primary mb-1 fw-bold" onclick="showOrderDetail(${row.id})">${data}</a>
                                    <span class="text-muted fs-7">${row.branch_name || 'N/A'}</span>
                                </div>
                            </div>`;
                    }
                },
                {
                    targets: 2,
                    render: function (data, type, row) {
                        return `
                            <div class="d-flex flex-column">
                                <span class="text-gray-800 fw-bold">${data}</span>
                                <span class="text-muted fs-7">${row.customer_phone || ''}</span>
                            </div>`;
                    }
                },
                {
                    targets: 3,
                    className: 'text-center',
                    render: function (data) {
                        return `<span class="badge badge-light-info">${data}</span>`;
                    }
                },
                {
                    targets: 4,
                    className: 'text-end',
                    render: function (data) {
                        return `<span class="fw-bold text-primary">${formatCurrency(data)}</span>`;
                    }
                },
                {
                    targets: 5,
                    className: 'text-end',
                    render: function (data, type, row) {
                        const percentage = row.final_amount > 0 ? (data / row.final_amount * 100) : 0;
                        const color = percentage >= 100 ? 'success' : percentage > 0 ? 'warning' : 'danger';
                        return `
                            <div class="d-flex flex-column">
                                <span class="fw-bold text-${color}">${formatCurrency(data)}</span>
                                <span class="text-muted fs-7">${percentage.toFixed(0)}%</span>
                            </div>`;
                    }
                },
                {
                    targets: 6,
                    render: function (data) {
                        const statusMap = {
                            'processing': { class: 'warning', text: 'Đang xử lý', icon: 'ki-time' },
                            'completed': { class: 'success', text: 'Hoàn thành', icon: 'ki-check-circle' },
                            'cancelled': { class: 'danger', text: 'Đã hủy', icon: 'ki-cross-circle' },
                            'failed': { class: 'dark', text: 'Thất bại', icon: 'ki-cross' }
                        };
                        const status = statusMap[data] || { class: 'secondary', text: data, icon: 'ki-question' };
                        return `
                            <span class="badge badge-light-${status.class}">
                                <i class="ki-duotone ${status.icon} fs-7 me-1">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                ${status.text}
                            </span>`;
                    }
                },
                {
                    targets: 7,
                    render: function (data) {
                        const statusMap = {
                            'unpaid': { class: 'danger', text: 'Chưa thanh toán', icon: 'ki-cross-circle' },
                            'partial': { class: 'warning', text: 'Một phần', icon: 'ki-time' },
                            'paid': { class: 'success', text: 'Đã thanh toán', icon: 'ki-check-circle' },
                            'overpaid': { class: 'info', text: 'Thanh toán thừa', icon: 'ki-arrow-up-circle' },
                            'refunded': { class: 'dark', text: 'Đã hoàn tiền', icon: 'ki-undo' }
                        };
                        const status = statusMap[data] || { class: 'secondary', text: data, icon: 'ki-question' };
                        return `
                            <span class="badge badge-light-${status.class}">
                                <i class="ki-duotone ${status.icon} fs-7 me-1">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                ${status.text}
                            </span>`;
                    }
                },
                {
                    targets: 8,
                    render: function (data) {
                        const statusMap = {
                            'pending': { class: 'secondary', text: 'Chờ xử lý', icon: 'ki-time' },
                            'picking': { class: 'info', text: 'Đang chuẩn bị', icon: 'ki-package' },
                            'delivering': { class: 'primary', text: 'Đang giao', icon: 'ki-delivery' },
                            'delivered': { class: 'success', text: 'Đã giao', icon: 'ki-check-circle' },
                            'returning': { class: 'warning', text: 'Đang trả', icon: 'ki-undo' },
                            'returned': { class: 'danger', text: 'Đã trả', icon: 'ki-cross-circle' }
                        };
                        const status = statusMap[data] || { class: 'secondary', text: data, icon: 'ki-question' };
                        return `
                            <span class="badge badge-light-${status.class}">
                                <i class="ki-duotone ${status.icon} fs-7 me-1">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                    <span class="path4"></span>
                                    <span class="path5"></span>
                                </i>
                                ${status.text}
                            </span>`;
                    }
                },
                {
                    targets: 9,
                    render: function (data) {
                        const channelMap = {
                            'direct': { class: 'primary', text: 'Trực tiếp', icon: 'ki-shop' },
                            'online': { class: 'success', text: 'Online', icon: 'ki-global' },
                            'pos': { class: 'info', text: 'POS', icon: 'ki-tablet' },
                            'other': { class: 'secondary', text: 'Khác', icon: 'ki-dots-horizontal' }
                        };
                        const channel = channelMap[data] || { class: 'secondary', text: data, icon: 'ki-question' };
                        return `
                            <span class="badge badge-light-${channel.class}">
                                <i class="ki-duotone ${channel.icon} fs-7 me-1">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                ${channel.text}
                            </span>`;
                    }
                },
                {
                    targets: 10,
                    render: function (data) {
                        // Data is already formatted from server side
                        return data || '';
                    }
                },
                {
                    targets: 11,
                    className: 'text-end',
                    render: function (data, type, row) {
                        return `
                            <div class="d-flex justify-content-end flex-shrink-0">
                                <div class="btn-group" role="group">
                                    <button class="btn btn-icon btn-light btn-active-light-primary btn-sm" onclick="showOrderDetail(${row.id})" title="Xem chi tiết">
                                        <i class="fa fa-eye fs-5"></i>
                                    </button>
                                    <button class="btn btn-icon btn-light btn-active-light-success btn-sm" onclick="showPaymentModal(${row.id})" title="Ghi nhận thanh toán">
                                        <i class="fa fa-dollar fs-5">
                                           
                                        </i>
                                    </button>
                                    <button class="btn btn-icon btn-light btn-active-light-warning btn-sm" onclick="showStatusModal(${row.id})" title="Cập nhật trạng thái">
                                        <i class="fa fa-pencil fs-5">
                                          
                                        </i>
                                    </button>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-icon btn-light btn-active-light-info btn-sm dropdown-toggle" data-bs-toggle="dropdown" title="Thêm thao tác">
                                            <i class="fa fa-dots-vertical fs-5">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                                <span class="path3"></span>
                                            </i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="#" onclick="editOrder(${row.id})">
                                                <i class="fa fa-pencil fs-6 me-2">
                                                 
                                                </i>Chỉnh sửa
                                            </a></li>
                                            <li><a class="dropdown-item" href="#" onclick="duplicateOrder(${row.id})">
                                                <i class="fa fa-copy fs-6 me-2">
                                                 
                                                </i>Nhân bản
                                            </a></li>
                                            <li><a class="dropdown-item" href="#" onclick="printOrder(${row.id})">
                                                <i class="fa fa-printer fs-6 me-2">
                                                   
                                                </i>In đơn hàng
                                            </a></li>
                                            <li><a class="dropdown-item" href="#" onclick="exportOrder(${row.id})">
                                                <i class="fa fa-file-down fs-6 me-2">
                                                
                                                </i>Xuất PDF
                                            </a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item" href="#" onclick="showQuickActions(${row.id})">
                                                <i class="fa fa-flash fs-6 me-2">
                                                  
                                                </i>Thao tác nhanh
                                            </a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger" href="#" onclick="deleteOrder(${row.id})">
                                                <i class="fa fa-trash fs-6 me-2">
                                                   
                                                </i>Xóa đơn hàng
                                            </a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>`;
                    }
                }
            ],
            "language": {
                "lengthMenu": "Hiển thị _MENU_ đơn hàng",
                "zeroRecords": "Không tìm thấy đơn hàng nào",
                "info": "Hiển thị _START_ đến _END_ của _TOTAL_ đơn hàng",
                "infoEmpty": "Hiển thị 0 đến 0 của 0 đơn hàng",
                "infoFiltered": "(lọc từ _MAX_ tổng số đơn hàng)",
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
            initToggleToolbar();
            handleDeleteRows();
            handleStatusUpdate();
            KTMenu.createInstances();
        });
    }

    // Search Datatable
    var handleSearchDatatable = function () {
        const filterSearch = document.querySelector('[data-kt-orders-table-filter="search"]');
        if (filterSearch) {
            filterSearch.addEventListener('keyup', function (e) {
                datatable.search(e.target.value).draw();
            });
        }
    }

    // Filter functions
    var handleFilterDatatable = function () {
        // Status filter
        const filterStatus = document.querySelector('[data-kt-orders-table-filter="status"]');
        if (filterStatus) {
            filterStatus.addEventListener('change', function (e) {
                datatable.draw();
            });
        }

        // Delivery status filter
        const filterDeliveryStatus = document.querySelector('[data-kt-orders-table-filter="delivery_status"]');
        if (filterDeliveryStatus) {
            filterDeliveryStatus.addEventListener('change', function (e) {
                datatable.draw();
            });
        }

        // Payment status filter
        const filterPaymentStatus = document.querySelector('[data-kt-orders-table-filter="payment_status"]');
        if (filterPaymentStatus) {
            filterPaymentStatus.addEventListener('change', function (e) {
                datatable.draw();
            });
        }

        // Channel filter
        const filterChannel = document.querySelector('[data-kt-orders-table-filter="channel"]');
        if (filterChannel) {
            filterChannel.addEventListener('change', function (e) {
                datatable.draw();
            });
        }

        // Date range filter
        const filterDateFrom = document.querySelector('[data-kt-orders-table-filter="date_from"]');
        const filterDateTo = document.querySelector('[data-kt-orders-table-filter="date_to"]');
        
        if (filterDateFrom) {
            filterDateFrom.addEventListener('change', function (e) {
                datatable.draw();
            });
        }
        
        if (filterDateTo) {
            filterDateTo.addEventListener('change', function (e) {
                datatable.draw();
            });
        }

        // Reset filter
        const resetButton = document.querySelector('[data-kt-orders-table-filter="reset"]');
        if (resetButton) {
            resetButton.addEventListener('click', function () {
                // Reset all filters
                if (filterStatus) filterStatus.value = '';
                if (filterDeliveryStatus) filterDeliveryStatus.value = '';
                const filterPaymentStatus = document.querySelector('[data-kt-orders-table-filter="payment_status"]');
                if (filterPaymentStatus) filterPaymentStatus.value = '';
                if (filterChannel) filterChannel.value = '';
                if (filterDateFrom) filterDateFrom.value = '';
                if (filterDateTo) filterDateTo.value = '';
                
                // Redraw table
                datatable.search('').draw();
            });
        }
    }

    // Delete order
    var handleDeleteRows = function () {
        // Select all delete buttons
        const deleteButtons = table.querySelectorAll('[data-kt-orders-table-filter="delete_row"]');

        deleteButtons.forEach(d => {
            // Delete button on click
            d.addEventListener('click', function (e) {
                e.preventDefault();

                // Select parent row
                const parent = e.target.closest('tr');

                // Get order code
                const orderCode = parent.querySelectorAll('td')[1].innerText;
                const orderId = this.getAttribute('data-order-id');

                // SweetAlert2 confirmation
                Swal.fire({
                    text: "Bạn có chắc chắn muốn xóa đơn hàng " + orderCode + "?",
                    icon: "warning",
                    showCancelButton: true,
                    buttonsStyling: false,
                    confirmButtonText: "Có, xóa!",
                    cancelButtonText: "Không, hủy",
                    customClass: {
                        confirmButton: "btn fw-bold btn-danger",
                        cancelButton: "btn fw-bold btn-active-light-primary"
                    }
                }).then(function (result) {
                    if (result.value) {
                        // Delete request
                        $.ajax({
                            url: "/admin/order/delete/" + orderId,
                            type: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        text: "Đã xóa đơn hàng " + orderCode + ".",
                                        icon: "success",
                                        buttonsStyling: false,
                                        confirmButtonText: "Ok, đã hiểu!",
                                        customClass: {
                                            confirmButton: "btn fw-bold btn-primary",
                                        }
                                    }).then(function () {
                                        // Reload datatable
                                        datatable.ajax.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        text: response.message || "Có lỗi xảy ra khi xóa đơn hàng.",
                                        icon: "error",
                                        buttonsStyling: false,
                                        confirmButtonText: "Ok, đã hiểu!",
                                        customClass: {
                                            confirmButton: "btn fw-bold btn-primary",
                                        }
                                    });
                                }
                            },
                            error: function() {
                                Swal.fire({
                                    text: "Có lỗi xảy ra khi xóa đơn hàng.",
                                    icon: "error",
                                    buttonsStyling: false,
                                    confirmButtonText: "Ok, đã hiểu!",
                                    customClass: {
                                        confirmButton: "btn fw-bold btn-primary",
                                    }
                                });
                            }
                        });
                    }
                });
            })
        });
    }

    // Update order status
    var handleStatusUpdate = function () {
        // Select all status update buttons
        const statusButtons = table.querySelectorAll('[data-kt-orders-table-filter="update_status"]');

        statusButtons.forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault();
                
                const orderId = this.getAttribute('data-order-id');
                const currentStatus = this.getAttribute('data-current-status');
                
                // Show status update modal
                showStatusUpdateModal(orderId, currentStatus);
            });
        });
    }

    // Show status update modal
    var showStatusUpdateModal = function(orderId, currentStatus) {
        // Use the existing modal in the HTML instead of creating a new one
        const modal = document.getElementById('statusUpdateModal');
        if (!modal) {
            console.error('Status update modal not found');
            return;
        }

        // Populate the form with current values
        document.getElementById('updateOrderId').value = orderId;
        document.getElementById('orderStatus').value = currentStatus || '';

        // Clear other fields
        document.getElementById('deliveryStatus').value = '';
        document.getElementById('paymentStatus').value = '';
        document.getElementById('internalNotes').value = '';

        // Show the modal
        $('#statusUpdateModal').modal('show');
    }

    // Init toggle toolbar
    var initToggleToolbar = function () {
        // Toggle selected action toolbar
        // Select all checkboxes
        const checkboxes = table.querySelectorAll('[type="checkbox"]');

        // Select elements
        const deleteSelected = document.querySelector('[data-kt-orders-table-select="delete_selected"]');

        // Toggle delete selected toolbar
        checkboxes.forEach(c => {
            // Checkbox on click event
            c.addEventListener('click', function () {
                setTimeout(function () {
                    toggleToolbars();
                }, 50);
            });
        });

        // Deleted selected rows
        if (deleteSelected) {
            deleteSelected.addEventListener('click', function () {
                // SweetAlert2 confirmation
                Swal.fire({
                    text: "Bạn có chắc chắn muốn xóa các đơn hàng đã chọn?",
                    icon: "warning",
                    showCancelButton: true,
                    buttonsStyling: false,
                    confirmButtonText: "Có, xóa!",
                    cancelButtonText: "Không, hủy",
                    customClass: {
                        confirmButton: "btn fw-bold btn-danger",
                        cancelButton: "btn fw-bold btn-active-light-primary"
                    }
                }).then(function (result) {
                    if (result.value) {
                        // Collect selected IDs
                        const selectedIds = [];
                        checkboxes.forEach(c => {
                            if (c.checked && c.value !== "1") {
                                selectedIds.push(c.value);
                            }
                        });

                        if (selectedIds.length > 0) {
                            // Delete request
                            $.ajax({
                                url: "/admin/order/bulk-delete",
                                type: 'POST',
                                data: {
                                    ids: selectedIds,
                                    _token: $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function(response) {
                                    if (response.success) {
                                        Swal.fire({
                                            text: "Đã xóa các đơn hàng đã chọn.",
                                            icon: "success",
                                            buttonsStyling: false,
                                            confirmButtonText: "Ok, đã hiểu!",
                                            customClass: {
                                                confirmButton: "btn fw-bold btn-primary",
                                            }
                                        }).then(function () {
                                            // Reload datatable
                                            datatable.ajax.reload();
                                        });
                                    } else {
                                        Swal.fire({
                                            text: response.message || "Có lỗi xảy ra khi xóa đơn hàng.",
                                            icon: "error",
                                            buttonsStyling: false,
                                            confirmButtonText: "Ok, đã hiểu!",
                                            customClass: {
                                                confirmButton: "btn fw-bold btn-primary",
                                            }
                                        });
                                    }
                                },
                                error: function() {
                                    Swal.fire({
                                        text: "Có lỗi xảy ra khi xóa đơn hàng.",
                                        icon: "error",
                                        buttonsStyling: false,
                                        confirmButtonText: "Ok, đã hiểu!",
                                        customClass: {
                                            confirmButton: "btn fw-bold btn-primary",
                                        }
                                    });
                                }
                            });
                        }
                    }
                });
            });
        }
    }

    // Toggle toolbars
    const toggleToolbars = function () {
        // Define variables
        const toolbarBase = document.querySelector('[data-kt-orders-table-toolbar="base"]');
        const toolbarSelected = document.querySelector('[data-kt-orders-table-toolbar="selected"]');
        const selectedCount = document.querySelector('[data-kt-orders-table-select="selected_count"]');

        // Select refreshed checkbox DOM elements
        const allCheckboxes = table.querySelectorAll('tbody [type="checkbox"]');

        // Detect checkboxes state & count
        let checkedState = false;
        let count = 0;

        // Count checked boxes
        allCheckboxes.forEach(c => {
            if (c.checked) {
                checkedState = true;
                count++;
            }
        });

        // Toggle toolbars
        if (checkedState) {
            if (selectedCount) selectedCount.innerHTML = count;
            if (toolbarBase) toolbarBase.classList.add('d-none');
            if (toolbarSelected) toolbarSelected.classList.remove('d-none');
        } else {
            if (toolbarBase) toolbarBase.classList.remove('d-none');
            if (toolbarSelected) toolbarSelected.classList.add('d-none');
        }
    }

    // Public methods
    return {
        init: function () {
            table = document.querySelector('#kt_orders_table');

            if (!table) {
                return;
            }

            initOrderTable();
            initToggleToolbar();
            handleSearchDatatable();
            handleFilterDatatable();
            handleDeleteRows();
            handleStatusUpdate();
        },

        // Public method to reload table
        reload: function() {
            if (datatable) {
                datatable.ajax.reload();
            }
        },

        // Public method to get datatable instance
        getDataTable: function() {
            return datatable;
        }
    }
}();

// Utility functions
function formatCurrency(amount) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(amount);
}

// Global functions for modal interactions
function showOrderDetail(orderId) {
    fetch(`/admin/order/${orderId}/detail-modal`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('orderDetailContent').innerHTML = html;
            $('#orderDetailModal').modal('show');
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                text: "Có lỗi xảy ra khi tải chi tiết đơn hàng.",
                icon: "error",
                buttonsStyling: false,
                confirmButtonText: "Đã hiểu!",
                customClass: {
                    confirmButton: "btn btn-primary"
                }
            });
        });
}

function showPaymentModal(orderId) {
    fetch(`/admin/order/${orderId}/get`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const order = data.order;
                document.getElementById('paymentOrderId').value = orderId;
                document.getElementById('orderTotalAmount').textContent = formatCurrency(order.final_amount);
                document.getElementById('orderPaidAmount').textContent = formatCurrency(order.amount_paid);

                // Calculate remaining amount
                const remaining = order.final_amount - order.amount_paid;
                document.getElementById('paymentAmount').value = remaining > 0 ? remaining : 0;

                $('#paymentRecordModal').modal('show');
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

function showStatusModal(orderId) {
    fetch(`/admin/order/${orderId}/get`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const order = data.order;
                document.getElementById('updateOrderId').value = orderId;
                document.getElementById('orderStatus').value = order.status;
                document.getElementById('deliveryStatus').value = order.delivery_status;
                document.getElementById('paymentStatus').value = order.payment_status;
                document.getElementById('internalNotes').value = order.internal_notes || '';

                $('#statusUpdateModal').modal('show');
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

function showQuickActions(orderId) {
    document.getElementById('quickActionOrderId').value = orderId;
    $('#quickActionsModal').modal('show');
}

function editOrder(orderId) {
    window.location.href = `/admin/order/edit/${orderId}`;
}

function duplicateOrder(orderId) {
    Swal.fire({
        text: "Bạn có muốn nhân bản đơn hàng này?",
        icon: "question",
        showCancelButton: true,
        buttonsStyling: false,
        confirmButtonText: "Có, nhân bản!",
        cancelButtonText: "Hủy",
        customClass: {
            confirmButton: "btn btn-primary",
            cancelButton: "btn btn-active-light"
        }
    }).then(function (result) {
        if (result.value) {
            // TODO: Implement duplicate order functionality
            Swal.fire({
                text: "Tính năng nhân bản đơn hàng đang được phát triển.",
                icon: "info",
                buttonsStyling: false,
                confirmButtonText: "Đã hiểu!",
                customClass: {
                    confirmButton: "btn btn-primary"
                }
            });
        }
    });
}

function printOrder(orderId) {
    window.open(`/admin/order/print/${orderId}`, '_blank');
}

function exportOrder(orderId) {
    window.open(`/admin/order/export/${orderId}`, '_blank');
}

function deleteOrder(orderId) {
    Swal.fire({
        text: "Bạn có chắc chắn muốn xóa đơn hàng này?",
        icon: "warning",
        showCancelButton: true,
        buttonsStyling: false,
        confirmButtonText: "Có, xóa!",
        cancelButtonText: "Hủy",
        customClass: {
            confirmButton: "btn btn-danger",
            cancelButton: "btn btn-active-light"
        }
    }).then(function (result) {
        if (result.value) {
            fetch(`/admin/order/delete/${orderId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        text: "Đã xóa đơn hàng thành công.",
                        icon: "success",
                        buttonsStyling: false,
                        confirmButtonText: "Đã hiểu!",
                        customClass: {
                            confirmButton: "btn btn-primary"
                        }
                    });
                    // Reload DataTable
                    $('#kt_orders_table').DataTable().ajax.reload();
                } else {
                    Swal.fire({
                        text: data.message || "Có lỗi xảy ra.",
                        icon: "error",
                        buttonsStyling: false,
                        confirmButtonText: "Đã hiểu!",
                        customClass: {
                            confirmButton: "btn btn-primary"
                        }
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    text: "Có lỗi xảy ra khi xóa đơn hàng.",
                    icon: "error",
                    buttonsStyling: false,
                    confirmButtonText: "Đã hiểu!",
                    customClass: {
                        confirmButton: "btn btn-primary"
                    }
                });
            });
        }
    });
}

// Quick action functions
function markOrderAsPaid() {
    const orderId = document.getElementById('quickActionOrderId').value;
    updateOrderQuickly(orderId, { payment_status: 'paid' }, 'Đã đánh dấu đơn hàng là đã thanh toán');
}

function markOrderAsDelivered() {
    const orderId = document.getElementById('quickActionOrderId').value;
    updateOrderQuickly(orderId, { delivery_status: 'delivered' }, 'Đã đánh dấu đơn hàng là đã giao');
}

function markOrderAsCompleted() {
    const orderId = document.getElementById('quickActionOrderId').value;
    updateOrderQuickly(orderId, {
        status: 'completed',
        delivery_status: 'delivered',
        payment_status: 'paid'
    }, 'Đã hoàn thành đơn hàng');
}

function cancelOrder() {
    const orderId = document.getElementById('quickActionOrderId').value;

    Swal.fire({
        text: "Bạn có chắc chắn muốn hủy đơn hàng này?",
        icon: "warning",
        showCancelButton: true,
        buttonsStyling: false,
        confirmButtonText: "Có, hủy đơn hàng!",
        cancelButtonText: "Không",
        customClass: {
            confirmButton: "btn btn-danger",
            cancelButton: "btn btn-active-light"
        }
    }).then(function (result) {
        if (result.value) {
            updateOrderQuickly(orderId, { status: 'cancelled' }, 'Đã hủy đơn hàng');
        }
    });
}

function updateOrderQuickly(orderId, data, successMessage) {
    fetch(`/admin/order/quick-update/${orderId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            $('#quickActionsModal').modal('hide');
            Swal.fire({
                text: successMessage,
                icon: "success",
                buttonsStyling: false,
                confirmButtonText: "Đã hiểu!",
                customClass: {
                    confirmButton: "btn btn-primary"
                }
            });
            // Reload DataTable
            $('#kt_orders_table').DataTable().ajax.reload();
        } else {
            Swal.fire({
                text: data.message || "Có lỗi xảy ra.",
                icon: "error",
                buttonsStyling: false,
                confirmButtonText: "Đã hiểu!",
                customClass: {
                    confirmButton: "btn btn-primary"
                }
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            text: "Có lỗi xảy ra khi cập nhật đơn hàng.",
            icon: "error",
            buttonsStyling: false,
            confirmButtonText: "Đã hiểu!",
            customClass: {
                confirmButton: "btn btn-primary"
            }
        });
    });
}

// On document ready
KTUtil.onDOMContentLoaded(function () {
    KTOrdersList.init();
});

// Global functions for inline onclick events
window.updateOrderStatus = function(orderId) {
    const button = document.querySelector(`[data-order-id="${orderId}"][data-kt-orders-table-filter="update_status"]`);
    if (button) {
        button.click();
    }
};

window.deleteOrder = function(orderId) {
    const button = document.querySelector(`[data-order-id="${orderId}"][data-kt-orders-table-filter="delete_row"]`);
    if (button) {
        button.click();
    }
};
