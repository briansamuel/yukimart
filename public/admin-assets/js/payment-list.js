"use strict";

var KTPaymentsList = function () {
    var table = document.getElementById('kt_payments_table');
    var dt, filterSearch;

    var initPaymentTable = function () {
        dt = $(table).DataTable({
            searchDelay: 500,
            processing: true,
            serverSide: true,
            order: [[1, 'desc']],
            stateSave: true,
            ajax: {
                url: '/admin/payments/data',
                data: function (d) {
                    d.payment_type = $('[data-kt-payment-table-filter="payment_type"]').val();
                    d.payment_method = $('[data-kt-payment-table-filter="payment_method"]').val();
                    d.status = $('[data-kt-payment-table-filter="status"]').val();
                    d.date_from = $('[data-kt-payment-table-filter="date_from"]').val();
                    d.date_to = $('[data-kt-payment-table-filter="date_to"]').val();
                }
            },
            columns: [
                { data: 'id', orderable: false, searchable: false },
                { data: 'payment_number' },
                { data: 'payment_type_display' },
                { data: 'customer_name' },
                { data: 'payment_date' },
                { data: 'payment_method_display' },
                { data: 'bank_account_info' },
                { data: 'formatted_amount' },
                { data: 'status_badge' },
                { data: 'creator_name' },
                { data: 'actions', orderable: false, searchable: false }
            ],
            columnDefs: [
                {
                    targets: 0,
                    orderable: false,
                    render: function (data) {
                        return `
                            <div class="form-check form-check-sm form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" value="${data}" />
                            </div>`;
                    }
                },
                {
                    targets: 6,
                    orderable: false,
                    render: function (data) {
                        return data;
                    }
                },
                {
                    targets: 8,
                    orderable: false,
                    render: function (data) {
                        return data;
                    }
                },
                {
                    targets: -1,
                    data: null,
                    orderable: false,
                    className: 'text-end',
                    render: function (data, type, row) {
                        return data;
                    },
                }
            ],
            language: {
                url: '/admin-assets/plugins/localization/vi.json'
            }
        });

        table.querySelectorAll('tbody tr').forEach(row => {
            const checkbox = row.querySelector('[type="checkbox"]');
            if (checkbox) {
                checkbox.addEventListener('click', function () {
                    setTimeout(function () {
                        toggleToolbars();
                    }, 50);
                });
            }
        });
    }

    var handleSearchDatatable = function () {
        filterSearch = document.querySelector('[data-kt-payment-table-filter="search"]');
        filterSearch.addEventListener('keyup', function (e) {
            dt.search(e.target.value).draw();
        });
    }

    var handleFilterDatatable = function () {
        const filterButton = document.querySelector('[data-kt-payment-table-filter="filter"]');
        filterButton.addEventListener('click', function () {
            dt.ajax.reload();
        });
    }

    var handleResetForm = function () {
        const resetButton = document.querySelector('[data-kt-payment-table-filter="reset"]');
        resetButton.addEventListener('click', function () {
            $('[data-kt-payment-table-filter="payment_type"]').val(null).trigger('change');
            $('[data-kt-payment-table-filter="payment_method"]').val(null).trigger('change');
            $('[data-kt-payment-table-filter="status"]').val(null).trigger('change');
            $('[data-kt-payment-table-filter="date_from"]').val('');
            $('[data-kt-payment-table-filter="date_to"]').val('');
            dt.ajax.reload();
        });
    }

    var toggleToolbars = function () {
        const container = document.querySelector('#kt_payments_table');
        const allCheckboxes = container.querySelectorAll('tbody [type="checkbox"]');
        let checkedState = false;
        let count = 0;

        allCheckboxes.forEach(c => {
            if (c.checked) {
                checkedState = true;
                count++;
            }
        });

        const toolbarBase = document.querySelector('[data-kt-payment-table-toolbar="base"]');
        const toolbarSelected = document.querySelector('[data-kt-payment-table-toolbar="selected"]');
        const selectedCount = document.querySelector('[data-kt-payment-table-select="selected_count"]');

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

            initPaymentTable();
            handleSearchDatatable();
            handleFilterDatatable();
            handleResetForm();
        }
    };
}();

// Global functions
window.viewPayment = function(id) {
    fetch(`/admin/payments/${id}/details`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const payment = data.data;
                let content = `
                    <div class="d-flex flex-column">
                        <div class="row mb-7">
                            <div class="col-md-6">
                                <label class="fw-bold text-muted">Số phiếu</label>
                                <div class="fw-bold fs-6">${payment.payment_number}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="fw-bold text-muted">Loại phiếu</label>
                                <div class="fw-bold fs-6">${payment.payment_type_display}</div>
                            </div>
                        </div>
                        <div class="row mb-7">
                            <div class="col-md-6">
                                <label class="fw-bold text-muted">Khách hàng</label>
                                <div class="fw-bold fs-6">${payment.customer ? payment.customer.name : 'N/A'}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="fw-bold text-muted">Ngày thanh toán</label>
                                <div class="fw-bold fs-6">${payment.payment_date}</div>
                            </div>
                        </div>
                        <div class="row mb-7">
                            <div class="col-md-6">
                                <label class="fw-bold text-muted">Phương thức</label>
                                <div class="fw-bold fs-6">${payment.payment_method_display}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="fw-bold text-muted">Trạng thái</label>
                                <div class="fw-bold fs-6">${payment.status_badge}</div>
                            </div>
                        </div>
                `;

                if (payment.bank_account) {
                    content += `
                        <div class="row mb-7">
                            <div class="col-md-6">
                                <label class="fw-bold text-muted">Tài khoản ngân hàng</label>
                                <div class="fw-bold fs-6">${payment.bank_account.bank_name}</div>
                                <div class="text-muted">${payment.bank_account.account_number}</div>
                            </div>
                        </div>
                    `;
                }

                content += `
                        <div class="row mb-7">
                            <div class="col-12">
                                <label class="fw-bold text-muted">Số tiền</label>
                                <div class="fw-bold fs-4 text-primary">${payment.formatted_amount}</div>
                            </div>
                        </div>
                `;

                if (payment.description) {
                    content += `
                        <div class="row mb-7">
                            <div class="col-12">
                                <label class="fw-bold text-muted">Mô tả</label>
                                <div class="fw-bold fs-6">${payment.description}</div>
                            </div>
                        </div>
                    `;
                }

                content += `</div>`;

                document.getElementById('payment_details_content').innerHTML = content;
                $('#kt_modal_view_payment').modal('show');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Lỗi!', 'Không thể tải thông tin phiếu thu/chi.', 'error');
        });
};

window.approvePayment = function(id) {
    $('#kt_modal_approve_payment').modal('show');
    document.getElementById('kt_modal_approve_payment_form').setAttribute('data-payment-id', id);
};

window.cancelPayment = function(id) {
    $('#kt_modal_cancel_payment').modal('show');
    document.getElementById('kt_modal_cancel_payment_form').setAttribute('data-payment-id', id);
};

// On document ready
KTUtil.onDOMContentLoaded(function () {
    KTPaymentsList.init();
});
