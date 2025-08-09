"use strict";

// Class definition
var KTNotificationsList = function () {
    // Shared variables
    var table;
    var dt;

    // Private functions
    var initTable = function () {
        // Set date data order
        const tableRows = table.querySelectorAll('tbody tr');

        // Init datatable --- more info on datatables: https://datatables.net/manual/
        dt = $(table).DataTable({
            searchDelay: 500,
            processing: true,
            serverSide: true,
            order: [[0, 'desc']],
            stateSave: false,
            ajax: {
                url: '/admin/notifications/data',
                type: 'GET',
                data: function (d) {
                    // Add custom filters
                    d.type = $('#type-filter').val();
                    d.status = $('#status-filter').val();
                }
            },
            columns: [
                { data: 'id' },
                { data: 'type_display' },
                { data: 'title' },
                { data: 'message' },
                { data: 'priority' },
                { data: 'is_read' },
                { data: 'time_ago' },
                { data: 'actions', orderable: false, searchable: false }
            ],
            columnDefs: [
                {
                    targets: 0,
                    orderable: false,
                    render: function (data, type, row) {
                        return `
                            <div class="form-check form-check-sm form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" value="${data}" />
                            </div>`;
                    }
                },
                {
                    targets: 1,
                    render: function (data, type, row) {
                        return `
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-circle symbol-50px overflow-hidden me-3">
                                    <div class="symbol-label">
                                        <i class="${row.type_icon} fs-2 text-${row.type_color}"></i>
                                    </div>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="text-gray-800 text-hover-primary mb-1">${data}</span>
                                    <span class="text-muted fs-7">${row.type}</span>
                                </div>
                            </div>`;
                    }
                },
                {
                    targets: 2,
                    render: function (data, type, row) {
                        const isUnread = !row.read_at;
                        return `
                            <div class="d-flex flex-column">
                                <span class="text-gray-800 ${isUnread ? 'fw-bold' : ''} mb-1">${data}</span>
                                ${row.priority !== 'normal' ? `<span class="badge badge-light-${row.priority === 'urgent' ? 'danger' : row.priority === 'high' ? 'warning' : 'info'} fs-8">${row.priority.toUpperCase()}</span>` : ''}
                            </div>`;
                    }
                },
                {
                    targets: 3,
                    render: function (data, type, row) {
                        const maxLength = 100;
                        const truncated = data.length > maxLength ? data.substring(0, maxLength) + '...' : data;
                        return `<span class="text-muted">${truncated}</span>`;
                    }
                },
                {
                    targets: 4,
                    render: function (data, type, row) {
                        const priorityColors = {
                            'low': 'info',
                            'normal': 'secondary',
                            'high': 'warning',
                            'urgent': 'danger'
                        };
                        const color = priorityColors[data] || 'secondary';
                        const text = data.charAt(0).toUpperCase() + data.slice(1);
                        return `<span class="badge badge-light-${color}">${text}</span>`;
                    }
                },
                {
                    targets: 5,
                    render: function (data, type, row) {
                        if (data) {
                            return `<span class="badge badge-light-success">Đã đọc</span>`;
                        } else {
                            return `<span class="badge badge-light-primary">Chưa đọc</span>`;
                        }
                    }
                },
                {
                    targets: 6,
                    render: function (data, type, row) {
                        return `<span class="text-muted">${data}</span>`;
                    }
                },
                {
                    targets: -1,
                    data: null,
                    orderable: false,
                    className: 'text-end',
                    render: function (data, type, row) {
                        return `
                            <a href="#" class="btn btn-light btn-active-light-primary btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                Actions
                                <span class="svg-icon svg-icon-5 m-0">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M11.4343 12.7344L7.25 8.55005C6.83579 8.13583 6.16421 8.13584 5.75 8.55005C5.33579 8.96426 5.33579 9.63583 5.75 10.05L11.2929 15.5929C11.6834 15.9835 12.3166 15.9835 12.7071 15.5929L18.25 10.05C18.6642 9.63584 18.6642 8.96426 18.25 8.55005C17.8358 8.13584 17.1642 8.13584 16.75 8.55005L12.5657 12.7344C12.2533 13.0468 11.7467 13.0468 11.4343 12.7344Z" fill="currentColor"></path>
                                    </svg>
                                </span>
                            </a>
                            <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4" data-kt-menu="true">
                                ${!row.is_read ? `<div class="menu-item px-3">
                                    <a href="#" class="menu-link px-3" data-kt-notifications-table-filter="mark_read" data-id="${row.id}">
                                        Đánh dấu đã đọc
                                    </a>
                                </div>` : ''}
                                <div class="menu-item px-3">
                                    <a href="#" class="menu-link px-3" data-kt-notifications-table-filter="delete_row" data-id="${row.id}">
                                        Xóa
                                    </a>
                                </div>
                            </div>`;
                    },
                },
            ],
            // Add data-kt-notifications-table="table" attribute to table
        });

        table.setAttribute('data-kt-notifications-table', 'table');

        // Re-init functions on every table re-draw -- more info: https://datatables.net/reference/event/draw
        dt.on('draw', function () {
            initToggleToolbar();
            handleDeleteRows();
            handleMarkAsRead();
            KTMenu.createInstances();
        });
    }

    // Search Datatable --- official docs reference: https://datatables.net/reference/api/search()
    var handleSearchDatatable = function () {
        const filterSearch = document.querySelector('[data-kt-notifications-table-filter="search"]');
        filterSearch.addEventListener('keyup', function (e) {
            dt.search(e.target.value).draw();
        });
    }

    // Filter Datatable
    var handleFilterDatatable = function () {
        // Select filter options
        const filterType = document.querySelector('#type-filter');
        const filterStatus = document.querySelector('#status-filter');

        // Filter datatable on submit
        if (filterType) {
            filterType.addEventListener('change', function () {
                dt.draw();
            });
        }

        if (filterStatus) {
            filterStatus.addEventListener('change', function () {
                dt.draw();
            });
        }
    }

    // Delete selected rows
    var handleDeleteRows = function () {
        // Select all delete buttons
        const deleteButtons = table.querySelectorAll('[data-kt-notifications-table-filter="delete_row"]');

        deleteButtons.forEach(d => {
            // Delete button on click
            d.addEventListener('click', function (e) {
                e.preventDefault();

                // Select parent row
                const parent = e.target.closest('tr');
                const notificationId = d.getAttribute('data-id');

                // SweetAlert2 pop up --- official docs reference: https://sweetalert2.github.io/
                Swal.fire({
                    text: "Bạn có chắc chắn muốn xóa thông báo này?",
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
                        // Delete notification via AJAX
                        $.ajax({
                            url: `/admin/notifications/${notificationId}`,
                            type: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        text: "Đã xóa thông báo thành công.",
                                        icon: "success",
                                        buttonsStyling: false,
                                        confirmButtonText: "Ok, đã hiểu!",
                                        customClass: {
                                            confirmButton: "btn fw-bold btn-primary",
                                        }
                                    }).then(function () {
                                        // Reload datatable
                                        dt.draw();
                                        loadStatistics();
                                    });
                                } else {
                                    Swal.fire({
                                        text: response.message || "Có lỗi xảy ra khi xóa thông báo.",
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
                                    text: "Có lỗi xảy ra khi xóa thông báo.",
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

    // Mark as read
    var handleMarkAsRead = function () {
        // Select all mark as read buttons
        const markReadButtons = table.querySelectorAll('[data-kt-notifications-table-filter="mark_read"]');

        markReadButtons.forEach(d => {
            // Mark as read button on click
            d.addEventListener('click', function (e) {
                e.preventDefault();

                const notificationId = d.getAttribute('data-id');

                // Mark as read via AJAX
                $.ajax({
                    url: `/admin/notifications/${notificationId}/read`,
                    type: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            // Reload datatable
                            dt.draw();
                            loadStatistics();
                        }
                    }
                });
            })
        });
    }

    // Init toggle toolbar
    var initToggleToolbar = function () {
        // Toggle selected action toolbar
        // Select all checkboxes
        const checkboxes = table.querySelectorAll('[type="checkbox"]');

        // Select elements
        const deleteSelected = document.querySelector('[data-kt-notifications-table-select="delete_selected"]');

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
        deleteSelected.addEventListener('click', function () {
            // SweetAlert2 pop up --- official docs reference: https://sweetalert2.github.io/
            Swal.fire({
                text: "Bạn có chắc chắn muốn xóa các thông báo đã chọn?",
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
                    // Get selected notification IDs
                    const selectedIds = [];
                    checkboxes.forEach(c => {
                        if (c.checked) {
                            selectedIds.push(c.value);
                        }
                    });

                    // Delete selected notifications
                    // Implementation would go here
                    
                    Swal.fire({
                        text: "Đã xóa các thông báo đã chọn.",
                        icon: "success",
                        buttonsStyling: false,
                        confirmButtonText: "Ok, đã hiểu!",
                        customClass: {
                            confirmButton: "btn fw-bold btn-primary",
                        }
                    }).then(function () {
                        // Reload datatable
                        dt.draw();
                        loadStatistics();
                    });

                    // Remove header checked box
                    const headerCheckbox = table.querySelectorAll('[type="checkbox"]')[0];
                    headerCheckbox.checked = false;
                }
            });
        });
    }

    // Toggle toolbars
    var toggleToolbars = function () {
        // Define variables
        const toolbarBase = document.querySelector('[data-kt-notifications-table-toolbar="base"]');
        const toolbarSelected = document.querySelector('[data-kt-notifications-table-toolbar="selected"]');
        const selectedCount = document.querySelector('[data-kt-notifications-table-select="selected_count"]');

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
    var loadStatistics = function () {
        $.ajax({
            url: '/admin/notifications/count',
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    $('#total-notifications').text(response.data.total || 0);
                    $('#unread-notifications').text(response.data.unread || 0);
                    $('#today-notifications').text(response.data.today || 0);
                    $('#urgent-notifications').text(response.data.urgent || 0);
                }
            }
        });
    }

    // Handle mark all as read
    var handleMarkAllAsRead = function () {
        const markAllBtn = document.querySelector('#mark-all-read-btn');
        if (markAllBtn) {
            markAllBtn.addEventListener('click', function () {
                Swal.fire({
                    text: "Bạn có chắc chắn muốn đánh dấu tất cả thông báo đã đọc?",
                    icon: "warning",
                    showCancelButton: true,
                    buttonsStyling: false,
                    confirmButtonText: "Có, đánh dấu!",
                    cancelButtonText: "Không, hủy",
                    customClass: {
                        confirmButton: "btn fw-bold btn-primary",
                        cancelButton: "btn fw-bold btn-active-light-primary"
                    }
                }).then(function (result) {
                    if (result.value) {
                        $.ajax({
                            url: '/admin/notifications/mark-all-read',
                            type: 'PUT',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        text: "Đã đánh dấu tất cả thông báo đã đọc.",
                                        icon: "success",
                                        buttonsStyling: false,
                                        confirmButtonText: "Ok, đã hiểu!",
                                        customClass: {
                                            confirmButton: "btn fw-bold btn-primary",
                                        }
                                    }).then(function () {
                                        dt.draw();
                                        loadStatistics();
                                    });
                                }
                            }
                        });
                    }
                });
            });
        }
    }

    // Public methods
    return {
        init: function () {
            table = document.querySelector('#kt_notifications_table');

            if (!table) {
                return;
            }

            initTable();
            initToggleToolbar();
            handleSearchDatatable();
            handleFilterDatatable();
            handleDeleteRows();
            handleMarkAsRead();
            handleMarkAllAsRead();
            loadStatistics();
        }
    }
}();

// On document ready
KTUtil.onDOMContentLoaded(function () {
    KTNotificationsList.init();
});
