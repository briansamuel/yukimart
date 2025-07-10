"use strict";

var KTReturnOrdersList = function () {
    var table = document.getElementById('kt_return_orders_table');
    var dt, filterSearch;

    var initReturnOrderTable = function () {
        dt = $(table).DataTable({
            searchDelay: 500,
            processing: true,
            serverSide: true,
            order: [[1, 'desc']],
            stateSave: true,
            ajax: {
                url: '/admin/return-orders/data',
                type: "GET",
                data: function (d) {
                    // Add search term
                    d.search_term = $('#return_order_search').val();

                    // Get filter form data
                    const filterForm = document.querySelector('#kt_return_order_filter_form');
                    let activeTimeRadio = null;
                    let activeTimeButton = null;

                    if (filterForm) {
                        // Time filter - prioritize radio button over active button
                        activeTimeRadio = filterForm.querySelector('input[name="time_filter"]:checked');
                        activeTimeButton = filterForm.querySelector('.time-option.active');

                        if (activeTimeRadio) {
                            d.time_filter = activeTimeRadio.value;

                            // If custom is selected, get date range
                            if (activeTimeRadio.value === 'custom') {
                                const dateFrom = filterForm.querySelector('#date_from');
                                const dateTo = filterForm.querySelector('#date_to');
                                if (dateFrom && dateFrom.value) {
                                    d.date_from = dateFrom.value;
                                }
                                if (dateTo && dateTo.value) {
                                    d.date_to = dateTo.value;
                                }
                            }
                        } else if (activeTimeButton) {
                            d.time_filter = activeTimeButton.getAttribute('data-value');
                        }

                        // Status filter
                        const statusCheckboxes = filterForm.querySelectorAll('input[name="status[]"]:checked');
                        if (statusCheckboxes.length > 0) {
                            d.status = Array.from(statusCheckboxes).map(cb => cb.value);
                        }

                        // Reason filter
                        const reasonCheckboxes = filterForm.querySelectorAll('input[name="reason[]"]:checked');
                        if (reasonCheckboxes.length > 0) {
                            d.reason = Array.from(reasonCheckboxes).map(cb => cb.value);
                        }

                        // Creator filter
                        const creatorSelect = filterForm.querySelector('#creator_filter');
                        if (creatorSelect && creatorSelect.value) {
                            d.creator_id = creatorSelect.value;
                        }

                        // Customer filter
                        const customerSelect = filterForm.querySelector('#customer_filter');
                        if (customerSelect && customerSelect.value) {
                            d.customer_id = customerSelect.value;
                        }

                        // Refund method filter
                        const refundCheckboxes = filterForm.querySelectorAll('input[name="refund_method[]"]:checked');
                        if (refundCheckboxes.length > 0) {
                            d.refund_method = Array.from(refundCheckboxes).map(cb => cb.value);
                        }

                        // Amount range filter
                        const amountFrom = filterForm.querySelector('input[name="amount_from"]');
                        const amountTo = filterForm.querySelector('input[name="amount_to"]');
                        if (amountFrom && amountFrom.value) {
                            d.amount_from = amountFrom.value;
                        }
                        if (amountTo && amountTo.value) {
                            d.amount_to = amountTo.value;
                        }
                    }

                    console.log('Return Order AJAX data:', d);
                    console.log('Active time radio:', activeTimeRadio ? activeTimeRadio.value : 'none');
                    console.log('Active time button:', activeTimeButton ? activeTimeButton.getAttribute('data-value') : 'none');
                },
                error: function(xhr, error, thrown) {
                    console.error('DataTable AJAX error:', error, thrown);
                    console.error('Response:', xhr.responseText);
                }
            },
            columns: [
                { data: 'checkbox', orderable: false, searchable: false },
                { data: 'return_number' },
                { data: 'invoice_number' },
                { data: 'customer_name' },
                { data: 'return_date' },
                { data: 'reason_display' },
                { data: 'total_amount' },
                { data: 'status_badge', orderable: false },
                { data: 'creator_name' }
            ],
            columnDefs: [
                {
                    targets: 0,
                    orderable: false,
                    searchable: false
                },
                {
                    targets: 7,
                    orderable: false,
                    render: function (data) {
                        return data;
                    }
                }
            ],
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'colvis',
                    text: '<i class="fas fa-list"></i> Cột hiển thị',
                    className: 'btn btn-success btn-sm',
                    columns: ':not(:first-child):not(:nth-child(2))' // Exclude dt-control and checkbox columns
                }
            ],
            language: {
                processing: "Đang xử lý...",
                search: "Tìm kiếm:",
                lengthMenu: "Hiển thị _MENU_ mục",
                info: "Hiển thị _START_ đến _END_ của _TOTAL_ mục",
                infoEmpty: "Hiển thị 0 đến 0 của 0 mục",
                infoFiltered: "(được lọc từ _MAX_ mục)",
                loadingRecords: "Đang tải...",
                zeroRecords: "Không tìm thấy dữ liệu",
                emptyTable: "Không có dữ liệu",
                paginate: {
                    first: "Đầu",
                    previous: "Trước",
                    next: "Sau",
                    last: "Cuối"
                }
            }
        });

        // Add event listeners for checkboxes
        $(table).on('change', 'tbody [type="checkbox"]', function () {
            setTimeout(function () {
                toggleToolbars();
            }, 50);
        });

        // Add event listener for row click to show details
        $('#kt_return_orders_table tbody').on('click', 'tr', function (e) {
            // Don't trigger if clicking on checkbox or already expanded row
            if ($(e.target).is('input[type="checkbox"]') || $(e.target).closest('.form-check').length > 0) {
                return;
            }

            var tr = $(this);
            var row = dt.row(tr);

            // Skip if this is a child row (detail row)
            if (tr.hasClass('child')) {
                return;
            }

            // Skip if clicking inside the detail panel content
            if ($(e.target).closest('.return-order-detail-panel').length > 0) {
                return;
            }

            if (row.child.isShown()) {
                // This row is already open - close it
                row.child.hide();
                tr.removeClass('shown');
            } else {
                // Close any other open rows first
                dt.rows().every(function() {
                    if (this.child.isShown()) {
                        this.child.hide();
                        $(this.node()).removeClass('shown');
                    }
                });

                // Open this row
                var returnOrderId = row.data().id;

                // Show loading
                row.child('<div class="text-center p-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>').show();
                tr.addClass('shown');

                // Load detail content via AJAX
                $.ajax({
                    url: '/admin/return-orders/' + returnOrderId + '/detail',
                    type: 'GET',
                    success: function(response) {
                        row.child(response).show();
                    },
                    error: function() {
                        row.child('<div class="alert alert-danger">Không thể tải thông tin chi tiết</div>').show();
                    }
                });
            }
        });
    }

    var handleSearchDatatable = function () {
        filterSearch = document.querySelector('#return_order_search');
        if (filterSearch) {
            filterSearch.addEventListener('keyup', function (e) {
                dt.search(e.target.value).draw();
            });
        }
    }

    var handleFilterDatatable = function () {
        // Handle filter form submission
        const filterForm = document.querySelector('#kt_return_order_filter_form');
        if (filterForm) {
            filterForm.addEventListener('change', function () {
                dt.ajax.reload();
            });
        }

        // Handle time filter options
        const timeOptions = document.querySelectorAll('.time-option');
        timeOptions.forEach(option => {
            option.addEventListener('click', function () {
                // Remove active class from all options
                timeOptions.forEach(opt => {
                    opt.classList.remove('active');
                    opt.classList.remove('btn-primary');
                    opt.classList.add('btn-light-primary');
                });

                // Add active class to clicked option
                this.classList.add('active');
                this.classList.remove('btn-light-primary');
                this.classList.add('btn-primary');

                // Update the main label
                const label = document.querySelector('#time_filter_trigger span');
                if (label) {
                    label.textContent = this.textContent;
                }

                // Update hidden radio button
                const radioButton = document.querySelector('#time_this_month');
                if (radioButton) {
                    radioButton.value = this.getAttribute('data-value');
                    radioButton.checked = true;
                }

                // Uncheck custom radio if it exists
                const customRadio = document.querySelector('#time_custom');
                if (customRadio) {
                    customRadio.checked = false;
                }

                // Close the panel
                const timePanel = document.getElementById('time_options_panel');
                if (timePanel) {
                    timePanel.style.display = 'none';
                }

                // Reload table
                dt.ajax.reload();
            });
        });


    }

    var handleResetForm = function () {
        // Add reset button to filter form
        const filterForm = document.querySelector('#kt_return_order_filter_form');
        if (filterForm) {
            // Create reset button if it doesn't exist
            let resetButton = filterForm.querySelector('.filter-reset-btn');
            if (!resetButton) {
                resetButton = document.createElement('button');
                resetButton.type = 'button';
                resetButton.className = 'btn btn-light-secondary w-100 filter-reset-btn';
                resetButton.innerHTML = '<i class="fas fa-undo me-2"></i>Đặt lại bộ lọc';
                filterForm.appendChild(resetButton);
            }

            resetButton.addEventListener('click', function () {
                // Reset all form inputs
                filterForm.reset();

                // Reset select2 elements
                filterForm.querySelectorAll('select').forEach(select => {
                    if ($(select).data('select2')) {
                        $(select).val(null).trigger('change');
                    }
                });

                // Reset checkboxes
                filterForm.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
                    checkbox.checked = false;
                });

                // Reset time filter to default
                const defaultTimeOption = document.querySelector('.time-option[data-value="this_month"]');
                if (defaultTimeOption) {
                    document.querySelectorAll('.time-option').forEach(opt => opt.classList.remove('active'));
                    defaultTimeOption.classList.add('active');

                    const label = document.querySelector('#time_filter_trigger span');
                    if (label) {
                        label.textContent = 'Tháng này';
                    }
                }

                // Reload table
                dt.ajax.reload();
            });
        }
    }

    var toggleToolbars = function () {
        const container = document.querySelector('#kt_return_orders_table');
        const allCheckboxes = container.querySelectorAll('tbody [type="checkbox"]');
        let checkedState = false;
        let count = 0;

        allCheckboxes.forEach(c => {
            if (c.checked) {
                checkedState = true;
                count++;
            }
        });

        const toolbarBase = document.querySelector('[data-kt-return-order-table-toolbar="base"]');
        const toolbarSelected = document.querySelector('[data-kt-return-order-table-toolbar="selected"]');
        const selectedCount = document.querySelector('[data-kt-return-order-table-select="selected_count"]');

        if (checkedState) {
            selectedCount.innerHTML = count;
            toolbarBase.classList.add('d-none');
            toolbarSelected.classList.remove('d-none');
        } else {
            toolbarBase.classList.remove('d-none');
            toolbarSelected.classList.add('d-none');
        }
    }

    return {
        init: function () {
            if (!table) {
                return;
            }

            initReturnOrderTable();
            handleSearchDatatable();
            handleFilterDatatable();
            handleResetForm();
        }
    };
}();

// Global functions
window.viewReturnOrder = function(id) {
    fetch(`/admin/return-orders/${id}/details`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const returnOrder = data.data;
                let content = `
                    <div class="d-flex flex-column">
                        <div class="row mb-7">
                            <div class="col-md-6">
                                <label class="fw-bold text-muted">Mã trả hàng</label>
                                <div class="fw-bold fs-6">${returnOrder.return_number}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="fw-bold text-muted">Hóa đơn gốc</label>
                                <div class="fw-bold fs-6">${returnOrder.invoice.invoice_number}</div>
                            </div>
                        </div>
                        <div class="row mb-7">
                            <div class="col-md-6">
                                <label class="fw-bold text-muted">Khách hàng</label>
                                <div class="fw-bold fs-6">${returnOrder.customer ? returnOrder.customer.name : 'Khách lẻ'}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="fw-bold text-muted">Ngày trả</label>
                                <div class="fw-bold fs-6">${returnOrder.return_date}</div>
                            </div>
                        </div>
                        <div class="row mb-7">
                            <div class="col-md-6">
                                <label class="fw-bold text-muted">Lý do trả</label>
                                <div class="fw-bold fs-6">${returnOrder.reason_display}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="fw-bold text-muted">Trạng thái</label>
                                <div class="fw-bold fs-6">${returnOrder.status_badge}</div>
                            </div>
                        </div>
                        <div class="row mb-7">
                            <div class="col-12">
                                <label class="fw-bold text-muted">Tổng tiền trả</label>
                                <div class="fw-bold fs-4 text-primary">${returnOrder.formatted_total_amount}</div>
                            </div>
                        </div>
                `;

                if (returnOrder.return_order_items && returnOrder.return_order_items.length > 0) {
                    content += `
                        <div class="separator my-5"></div>
                        <h5 class="fw-bold">Sản phẩm trả</h5>
                        <div class="table-responsive">
                            <table class="table table-row-dashed table-row-gray-300 gy-7">
                                <thead>
                                    <tr class="fw-bold fs-6 text-gray-800">
                                        <th>Sản phẩm</th>
                                        <th>Số lượng</th>
                                        <th>Đơn giá</th>
                                        <th>Thành tiền</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;

                    returnOrder.return_order_items.forEach(item => {
                        content += `
                            <tr>
                                <td>${item.product_name}</td>
                                <td>${item.quantity_returned}</td>
                                <td>${item.formatted_unit_price}</td>
                                <td>${item.formatted_line_total}</td>
                            </tr>
                        `;
                    });

                    content += `
                                </tbody>
                            </table>
                        </div>
                    `;
                }

                content += `</div>`;

                document.getElementById('return_order_details_content').innerHTML = content;
                $('#kt_modal_view_return_order').modal('show');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Lỗi!', 'Không thể tải thông tin đơn trả hàng.', 'error');
        });
};

window.approveReturnOrder = function(id) {
    $('#kt_modal_approve_return_order').modal('show');
    document.getElementById('kt_modal_approve_return_order_form').setAttribute('data-return-order-id', id);
};

window.rejectReturnOrder = function(id) {
    $('#kt_modal_reject_return_order').modal('show');
    document.getElementById('kt_modal_reject_return_order_form').setAttribute('data-return-order-id', id);
};

window.completeReturnOrder = function(id) {
    $('#kt_modal_complete_return_order').modal('show');
    document.getElementById('kt_modal_complete_return_order_form').setAttribute('data-return-order-id', id);
};

// On document ready
$(document).ready(function () {
    KTReturnOrdersList.init();
});
