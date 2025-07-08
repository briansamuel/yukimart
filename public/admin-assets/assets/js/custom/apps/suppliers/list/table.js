"use strict";

// Class definition
var KTSuppliersList = function () {
    // Define shared variables
    var table = document.getElementById('kt_datatable_suppliers');
    var datatable;
    var toolbarBase;
    var toolbarSelected;
    var selectedCount;

    // Private functions
    var initSupplierTable = function () {
        // Init datatable --- more info on datatables: https://datatables.net/manual/
        datatable = $(table).DataTable({
            "responsive": true,
            "searchDelay": 500,
            "processing": true,
            "serverSide": true,
            "order": [[1, 'asc']], // Order by supplier code
            "stateSave": false,
            "ajax": {
                "url": "/admin/supplier/ajax",
                "type": "GET",
                "data": function(d) {
                    // Add any additional filters here
                    d.status = $('[data-kt-suppliers-table-filter="status"]').val();
                    d.branch_id = $('[data-kt-suppliers-table-filter="branch"]').val();
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
                { data: 'code', name: 'code' },
                { data: 'name', name: 'name' },
                { data: 'company', name: 'company' },
                { data: 'phone', name: 'phone' },
                { data: 'email', name: 'email' },
                { 
                    data: 'branch.name', 
                    name: 'branch.name', 
                    defaultContent: '-',
                    render: function(data, type, row) {
                        return data || '-';
                    }
                },
                { 
                    data: 'status_badge', 
                    name: 'status', 
                    orderable: false, 
                    searchable: false 
                },
                { 
                    data: 'action', 
                    name: 'action', 
                    orderable: false, 
                    searchable: false,
                    className: 'text-end'
                }
            ],
            "language": {
                "lengthMenu": "Hiển thị _MENU_ nhà cung cấp",
                "zeroRecords": "Không tìm thấy nhà cung cấp nào",
                "info": "Hiển thị _START_ đến _END_ của _TOTAL_ nhà cung cấp",
                "infoEmpty": "Hiển thị 0 đến 0 của 0 nhà cung cấp",
                "infoFiltered": "(lọc từ _MAX_ tổng số nhà cung cấp)",
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
            KTMenu.createInstances();
        });
    }

    // Search Datatable
    var handleSearchDatatable = function () {
        const filterSearch = document.querySelector('[data-kt-suppliers-table-filter="search"]');
        if (filterSearch) {
            filterSearch.addEventListener('keyup', function (e) {
                datatable.search(e.target.value).draw();
            });
        }
    }

    // Delete supplier
    var handleDeleteRows = function () {
        // Select all delete buttons
        const deleteButtons = table.querySelectorAll('[data-kt-suppliers-table-filter="delete_row"]');

        deleteButtons.forEach(d => {
            // Delete button on click
            d.addEventListener('click', function (e) {
                e.preventDefault();

                // Select parent row
                const parent = e.target.closest('tr');

                // Get supplier name
                const supplierName = parent.querySelectorAll('td')[2].innerText;
                const supplierId = this.getAttribute('data-supplier-id');

                // SweetAlert2 confirmation
                Swal.fire({
                    text: "Bạn có chắc chắn muốn xóa nhà cung cấp " + supplierName + "?",
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
                            url: "/admin/supplier/delete/" + supplierId,
                            type: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        text: "Đã xóa nhà cung cấp " + supplierName + ".",
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
                                        text: response.message || "Có lỗi xảy ra khi xóa nhà cung cấp.",
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
                                    text: "Có lỗi xảy ra khi xóa nhà cung cấp.",
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

    // Init toggle toolbar
    var initToggleToolbar = function () {
        // Toggle selected action toolbar
        // Select all checkboxes
        const checkboxes = table.querySelectorAll('[type="checkbox"]');

        // Select elements
        const deleteSelected = document.querySelector('[data-kt-suppliers-table-select="delete_selected"]');

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
                    text: "Bạn có chắc chắn muốn xóa các nhà cung cấp đã chọn?",
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
                                url: "/admin/supplier/delete",
                                type: 'POST',
                                data: {
                                    ids: selectedIds,
                                    _token: $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function(response) {
                                    if (response.success) {
                                        Swal.fire({
                                            text: "Đã xóa các nhà cung cấp đã chọn.",
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
                                            text: response.message || "Có lỗi xảy ra khi xóa nhà cung cấp.",
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
                                        text: "Có lỗi xảy ra khi xóa nhà cung cấp.",
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
        const toolbarBase = document.querySelector('[data-kt-suppliers-table-toolbar="base"]');
        const toolbarSelected = document.querySelector('[data-kt-suppliers-table-toolbar="selected"]');
        const selectedCount = document.querySelector('[data-kt-suppliers-table-select="selected_count"]');

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
            table = document.querySelector('#kt_datatable_suppliers');

            if (!table) {
                return;
            }

            initSupplierTable();
            initToggleToolbar();
            handleSearchDatatable();
            handleDeleteRows();
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

// On document ready
KTUtil.onDOMContentLoaded(function () {
   
    KTSuppliersList.init();
});
