"use strict";

// Class definition
var KTCustomersList = function () {
    // Define shared variables
    var table = document.getElementById('kt_customers_table');
    var datatable;

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
                'type': 'GET'
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
                        return '<div class="d-flex align-items-center">' +
                               '<div class="symbol symbol-circle symbol-50px overflow-hidden me-3">' +
                               '<div class="symbol-label fs-3 bg-light-primary text-primary">' + data.charAt(0).toUpperCase() + '</div>' +
                               '</div>' +
                               '<div class="d-flex flex-column">' +
                               '<span class="text-gray-800 fw-bold">' + data + '</span>' +
                               '<span class="text-muted">' + (row.email || '') + '</span>' +
                               '</div>' +
                               '</div>';
                    }
                },
                { 
                    data: 'phone', 
                    name: 'phone', 
                    render: function(data, type, row) {
                        return data || '-';
                    }
                },
                { 
                    data: 'customer_type_display', 
                    name: 'customer_type', 
                    render: function(data, type, row) {
                        return '<span class="badge badge-light-info">' + data + '</span>';
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
                        return '<a href="/admin/customers/' + data + '" class="btn btn-sm btn-light">Xem</a>';
                    }
                }
            ],
            "columnDefs": [
                { orderable: false, targets: 0 },
                { orderable: false, targets: 8 }
            ]
        });
    };

    // Search Datatable
    var handleSearchDatatable = function () {
        var filterSearch = document.querySelector('[data-kt-customer-table-filter="search"]');
        
        if (filterSearch) {
            filterSearch.addEventListener('keyup', function (e) {
                datatable.search(e.target.value).draw();
            });
        }
    };

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
            handleSearchDatatable();
            loadStatistics();
        }
    };
}();

// On document ready
if (typeof KTUtil !== 'undefined') {
    KTUtil.onDOMContentLoaded(function () {
        KTCustomersList.init();
    });
} else {
    document.addEventListener('DOMContentLoaded', function() {
        KTCustomersList.init();
    });
}
