"use strict";

// Class definition
var KTCustomersList = function () {
    // Define shared variables
    var table = document.getElementById('kt_customers_table');
    var datatable;
    var toolbarBase;
    var toolbarSelected;
    var selectedCount;

    // Private functions
    var initCustomerTable = function () {
        if (!table) {
            return;
        }

        // Init datatable
        datatable = $(table).DataTable({
            "info": false,
            'order': [],
            'pageLength': 10,
            'processing': true,
            'serverSide': true,
            'ajax': {
                'url': '/admin/customers/data',
                'type': 'GET',
                'data': function(d) {
                    // Add any additional parameters here
                }
            },
            'columns': [
                {
                    data: 'id',
                    name: 'id',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        return '<div class="form-check form-check-sm form-check-custom form-check-solid">' +
                               '<input class="form-check-input widget-13-check" type="checkbox" value="' + data + '" />' +
                               '</div>';
                    }
                },
                {
                    data: 'name',
                    name: 'name',
                    render: function(data, type, row) {
                        var avatarHtml = row.avatar_url ?
                            '<div class="symbol-label"><img src="' + row.avatar_url + '" alt="' + data + '" class="w-100" /></div>' :
                            '<div class="symbol-label fs-3 bg-light-primary text-primary">' + data.charAt(0).toUpperCase() + '</div>';

                        return '<div class="d-flex align-items-center">' +
                               '<div class="symbol symbol-circle symbol-50px overflow-hidden me-3">' +
                               '<a href="/admin/customers/' + row.id + '">' + avatarHtml + '</a>' +
                               '</div>' +
                               '<div class="d-flex flex-column">' +
                               '<a href="/admin/customers/' + row.id + '" class="text-gray-800 text-hover-primary mb-1">' + data + '</a>' +
                               '<span class="text-muted">' + (row.email || '') + '</span>' +
                               '</div>' +
                               '</div>';
                    }
                },
                {
                    data: 'phone',
                    name: 'phone',
                    render: function(data, type, row) {
                        return '<div class="d-flex flex-column">' +
                               '<span class="text-gray-800 mb-1">' + (data || '-') + '</span>' +
                               '<span class="text-muted">' + (row.email || '-') + '</span>' +
                               '</div>';
                    }
                },
                {
                    data: 'customer_type_display',
                    name: 'customer_type',
                    render: function(data, type, row) {
                        var badgeClass = row.customer_type === 'business' ? 'badge-light-primary' : 'badge-light-info';
                        return '<span class="badge ' + badgeClass + '">' + data + '</span>';
                    }
                },
                {
                    data: 'orders_count',
                    name: 'orders_count',
                    render: function(data, type, row) {
                        return '<span class="text-gray-800 fw-bold">' + data + '</span>';
                    }
                },
                {
                    data: 'total_spent',
                    name: 'total_spent',
                    render: function(data, type, row) {
                        return '<span class="text-gray-800 fw-bold">' + data + '₫</span>';
                    }
                },
                {
                    data: 'last_order_date',
                    name: 'last_order_date',
                    render: function(data, type, row) {
                        return data || '-';
                    }
                },
                {
                    data: 'status_badge',
                    name: 'status',
                    render: function(data, type, row) {
                        return data;
                    }
                },
                {
                    data: 'id',
                    name: 'actions',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        return '<a href="#" class="btn btn-light btn-active-light-primary btn-flex btn-center btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">' +
                               'Thao tác <i class="ki-duotone ki-down fs-5 ms-1"></i>' +
                               '</a>' +
                               '<div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4" data-kt-menu="true">' +
                               '<div class="menu-item px-3"><a href="/admin/customers/' + data + '" class="menu-link px-3">Xem</a></div>' +
                               '<div class="menu-item px-3"><a href="/admin/customers/' + data + '/edit" class="menu-link px-3">Sửa</a></div>' +
                               '<div class="menu-item px-3"><a href="#" class="menu-link px-3" data-kt-customer-table-filter="delete_row" data-customer-id="' + data + '" data-customer-name="' + row.name + '">Xóa</a></div>' +
                               '</div>';
                    }
                }
            ],
            "columnDefs": [
                { orderable: false, targets: 0 },
                { orderable: false, targets: 8 }
            ]
        });

        // Re-init functions on every table re-draw
        datatable.on('draw', function () {
            initToggleToolbar();
            handleDeleteRows();
            toggleToolbars();
            if (typeof KTMenu !== 'undefined') {
                KTMenu.createInstances();
            }
        });
    };

    // Search Datatable --- official docs reference: https://datatables.net/reference/api/search()
    var handleSearchDatatable = function () {
        const filterSearch = document.querySelector('[data-kt-customer-table-filter="search"]');

        // Only proceed if search input exists
        if (!filterSearch) {
            return;
        }

        filterSearch.addEventListener('keyup', function (e) {
            datatable.search(e.target.value).draw();
        });
    }

    // Filter Datatable
    var handleFilterDatatable = function () {
        // Select filter options
        const filterForm = document.querySelector('[data-kt-customer-table-filter="form"]');

        // Only proceed if filter form exists
        if (!filterForm) {
            return;
        }

        const filterButton = filterForm.querySelector('[data-kt-customer-table-filter="filter"]');
        const selectOptions = filterForm.querySelectorAll('select');

        // Only proceed if filter button exists
        if (!filterButton) {
            return;
        }

        // Filter datatable on submit
        filterButton.addEventListener('click', function () {
            var filterString = '';

            // Get filter values
            selectOptions.forEach((item, index) => {
                if (item.value && item.value !== '') {
                    if (index !== 0) {
                        filterString += ' ';
                    }
                    filterString += item.value;
                }
            });

            // Filter datatable --- official docs reference: https://datatables.net/reference/api/search()
            datatable.search(filterString).draw();
        });
    }

    // Delete customer
    var handleDeleteRows = function () {
        // Select all delete buttons
        const deleteButtons = table.querySelectorAll('[data-kt-customer-table-filter="delete_row"]');

        deleteButtons.forEach(d => {
            // Delete button on click
            d.addEventListener('click', function (e) {
                e.preventDefault();

                // Select parent row
                const parent = e.target.closest('tr');

                // Get customer name
                const customerName = d.getAttribute('data-customer-name');
                const customerId = d.getAttribute('data-customer-id');

                // SweetAlert2 pop up --- official docs reference: https://sweetalert2.github.io/
                Swal.fire({
                    text: "Bạn có chắc chắn muốn xóa khách hàng " + customerName + "?",
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
                        fetch(`/admin/customers/${customerId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Content-Type': 'application/json',
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    text: "Bạn đã xóa khách hàng " + customerName + "!.",
                                    icon: "success",
                                    buttonsStyling: false,
                                    confirmButtonText: "Ok, đã hiểu!",
                                    customClass: {
                                        confirmButton: "btn fw-bold btn-primary",
                                    }
                                }).then(function () {
                                    // Reload datatable
                                    datatable.ajax.reload();
                                    // Update statistics
                                    loadStatistics();
                                });
                            } else {
                                Swal.fire({
                                    text: data.message || "Có lỗi xảy ra khi xóa khách hàng.",
                                    icon: "error",
                                    buttonsStyling: false,
                                    confirmButtonText: "Ok, đã hiểu!",
                                    customClass: {
                                        confirmButton: "btn fw-bold btn-primary",
                                    }
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire({
                                text: "Có lỗi xảy ra khi xóa khách hàng.",
                                icon: "error",
                                buttonsStyling: false,
                                confirmButtonText: "Ok, đã hiểu!",
                                customClass: {
                                    confirmButton: "btn fw-bold btn-primary",
                                }
                            });
                        });
                    } else if (result.dismiss === 'cancel') {
                        Swal.fire({
                            text: customerName + " không bị xóa.",
                            icon: "error",
                            buttonsStyling: false,
                            confirmButtonText: "Ok, đã hiểu!",
                            customClass: {
                                confirmButton: "btn fw-bold btn-primary",
                            }
                        });
                    }
                });
            })
        });
    }

    // Reset Filter
    var handleResetForm = function () {
        // Select reset button
        const resetButton = document.querySelector('[data-kt-customer-table-filter="reset"]');

        // Only proceed if reset button exists
        if (!resetButton) {
            return;
        }

        // Reset datatable
        resetButton.addEventListener('click', function () {
            // Select filter options
            const filterForm = document.querySelector('[data-kt-customer-table-filter="form"]');

            if (filterForm) {
                const selectOptions = filterForm.querySelectorAll('select');

                // Reset select2 values -- more info: https://select2.org/programmatic-control/add-select-clear-items
                selectOptions.forEach(select => {
                    $(select).val('').trigger('change');
                });
            }

            // Reset datatable --- official docs reference: https://datatables.net/reference/api/search()
            datatable.search('').draw();
        });
    }

    // Init toggle toolbar
    var initToggleToolbar = function () {
        // Toggle selected action toolbar
        // Select all checkboxes
        const checkboxes = table.querySelectorAll('[type="checkbox"]');

        // Select elements
        toolbarBase = document.querySelector('[data-kt-customer-table-toolbar="base"]');
        toolbarSelected = document.querySelector('[data-kt-customer-table-toolbar="selected"]');
        selectedCount = document.querySelector('[data-kt-customer-table-select="selected_count"]');

        // Toggle delete selected toolbar
        checkboxes.forEach(c => {
            // Checkbox on click event
            c.addEventListener('click', function () {
                setTimeout(function () {
                    toggleToolbars();
                }, 50);
            });
        });
    }

    // Toggle toolbars
    var toggleToolbars = function () {
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
            selectedCount.innerHTML = count;
            toolbarBase.classList.add('d-none');
            toolbarSelected.classList.remove('d-none');
        } else {
            toolbarBase.classList.remove('d-none');
            toolbarSelected.classList.add('d-none');
        }
    }

    // Load statistics
    var loadStatistics = function() {
        fetch('/admin/customers/statistics')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    var totalCustomersEl = document.getElementById('total_customers');
                    var activeCustomersEl = document.getElementById('active_customers');
                    var newCustomersEl = document.getElementById('new_customers');
                    var totalRevenueEl = document.getElementById('total_revenue');

                    if (totalCustomersEl) totalCustomersEl.textContent = data.data.total_customers;
                    if (activeCustomersEl) activeCustomersEl.textContent = data.data.active_customers;
                    if (newCustomersEl) newCustomersEl.textContent = data.data.new_customers_this_month;
                    if (totalRevenueEl) totalRevenueEl.textContent = data.data.total_revenue + '₫';
                }
            })
            .catch(error => {
                console.error('Error loading statistics:', error);
            });
    };

    // Public methods
    return {
        init: function () {
            if (!table) {
                return;
            }

            initCustomerTable();
            initToggleToolbar();
            handleSearchDatatable();
            handleFilterDatatable();
            handleDeleteRows();
            handleResetForm();
            loadStatistics();
        }
    }
}();

// On document ready
KTUtil.onDOMContentLoaded(function () {
    KTCustomersList.init();
});
