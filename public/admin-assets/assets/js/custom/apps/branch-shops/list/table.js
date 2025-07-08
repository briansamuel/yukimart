"use strict";

var KTBranchShopsList = function () {
    var table = document.getElementById('kt_branch_shops_table');
    var dt;
    var filterStatus;
    var filterShopType;
    var toolbarBase;
    var toolbarSelected;
    var selectedCount;

    var initTable = function () {
        dt = $(table).DataTable({
            searchDelay: 500,
            processing: true,
            serverSide: true,
            order: [[1, 'asc']],
            stateSave: true,
            ajax: {
                url: '/admin/branch-shops/data',
                type: 'GET',
                data: function (d) {
                    // Add custom filters
                    d.status = $('[data-kt-branch-shop-table-filter="status"]').val();
                    d.shop_type = $('[data-kt-branch-shop-table-filter="shop_type"]').val();
                }
            },
            columns: [
                { data: 'id', orderable: false, searchable: false },
                { data: 'code' },
                { data: 'name' },
                { data: 'full_address' },
                { data: 'phone' },
                { data: 'status_badge' },
                { data: 'id', orderable: false, searchable: false }
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
                    targets: 5,
                    orderable: false,
                    render: function (data, type, row) {
                        return row.status_badge;
                    }
                },
                {
                    targets: -1,
                    data: null,
                    orderable: false,
                    className: 'text-end',
                    render: function (data, type, row) {
                        return `
                            <a href="#" class="btn btn-light btn-active-light-primary btn-flex btn-center btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                ${window.translations?.common?.actions || 'Thao tác'}
                                <i class="ki-duotone ki-down fs-5 ms-1"></i>
                            </a>
                            <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-150px py-4" data-kt-menu="true">
                                <div class="menu-item px-3">
                                    <a href="/admin/branch-shops/${row.id}" class="menu-link px-3">
                                        <i class="fas fa-eye fs-5 me-2">
                                        </i>
                                        ${window.translations?.branch_shop?.view_details || 'Xem chi tiết'}
                                    </a>
                                </div>
                                <div class="menu-item px-3">
                                    <a href="/admin/branch-shops/${row.id}/edit" class="menu-link px-3">
                                        <i class="fas fa-pencil fs-5 me-2">
                                        </i>
                                        ${window.translations?.branch_shop?.edit_branch_shop || 'Chỉnh sửa'}
                                    </a>
                                </div>
                                <div class="menu-item px-3">
                                    <a href="#" class="menu-link px-3" data-kt-branch-shop-manage-users="true" data-branch-shop-id="${row.id}" data-branch-shop-name="${row.name}">
                                        <i class="fas fa-users fs-5 me-2">
                                        </i>
                                        ${window.translations?.branch_shop?.manage_users || 'Quản lý nhân viên'}
                                    </a>
                                </div>
                                <div class="menu-item px-3">
                                    <a href="#" class="menu-link px-3 text-danger" data-kt-branch-shop-table-filter="delete_row" data-id="${row.id}">
                                        <i class="fas fa-trash fs-5 me-2">
                                        </i>
                                        ${window.translations?.branch_shop?.delete_branch_shop || 'Xóa'}
                                    </a>
                                </div>
                            </div>
                        `;
                    },
                },
            ],
            language: {
                processing: window.translations?.datatable?.processing || "Đang xử lý...",
                search: window.translations?.datatable?.search || "Tìm kiếm:",
                lengthMenu: window.translations?.datatable?.lengthMenu || "Hiển thị _MENU_ mục",
                info: window.translations?.datatable?.info || "Hiển thị _START_ đến _END_ của _TOTAL_ mục",
                infoEmpty: window.translations?.datatable?.infoEmpty || "Hiển thị 0 đến 0 của 0 mục",
                infoFiltered: window.translations?.datatable?.infoFiltered || "(được lọc từ _MAX_ mục)",
                loadingRecords: window.translations?.datatable?.loadingRecords || "Đang tải...",
                zeroRecords: window.translations?.datatable?.zeroRecords || "Không tìm thấy dữ liệu",
                emptyTable: window.translations?.datatable?.emptyTable || "Không có dữ liệu trong bảng",
                paginate: {
                    first: window.translations?.datatable?.first || "Đầu",
                    previous: window.translations?.datatable?.previous || "Trước",
                    next: window.translations?.datatable?.next || "Tiếp",
                    last: window.translations?.datatable?.last || "Cuối"
                }
            },
            responsive: true,
            dom: `<"row"<"col-sm-6 d-flex align-items-center justify-content-start"l><"col-sm-6 d-flex align-items-center justify-content-end"f>>
                  <"table-responsive"t>
                  <"row"<"col-sm-12 col-md-5 d-flex align-items-center justify-content-center justify-content-md-start"i><"col-sm-12 col-md-7 d-flex align-items-center justify-content-center justify-content-md-end"p>>`,
        });

     

        // Re-init functions on every table re-draw
        dt.on('draw', function () {
            initToggleToolbar();
            handleDeleteRows();
            handleManageUsers();
            KTMenu.createInstances();
        });
    }

    // Search Datatable
    var handleSearchDatatable = function () {
        const filterSearch = document.querySelector('[data-kt-branch-shop-table-filter="search"]');
        filterSearch.addEventListener('keyup', function (e) {
            dt.search(e.target.value).draw();
        });
    }

    // Filter Datatable
    var handleFilterDatatable = function () {
        // Select filter options
        filterStatus = $('[data-kt-branch-shop-table-filter="status"]');
        filterShopType = $('[data-kt-branch-shop-table-filter="shop_type"]');
        const filterButton = document.querySelector('[data-kt-branch-shop-table-filter="filter"]');

        // Filter datatable on submit
        filterButton.addEventListener('click', function () {
            dt.draw();
        });
    }

    // Reset Filter
    var handleResetForm = function () {
        // Select reset button
        const resetButton = document.querySelector('[data-kt-branch-shop-table-filter="reset"]');

        // Reset datatable
        resetButton.addEventListener('click', function () {
            // Reset payment type
            filterStatus.val(null).trigger('change');
            filterShopType.val(null).trigger('change');

            // Reset datatable
            dt.search('').draw();
        });
    }

    // Init toggle toolbar
    var initToggleToolbar = function () {
        // Toggle selected action toolbar
        var checkboxes = table.querySelectorAll('[type="checkbox"]');
        
        // Select elements
        toolbarBase = document.querySelector('[data-kt-branch-shop-table-toolbar="base"]');
        toolbarSelected = document.querySelector('[data-kt-branch-shop-table-toolbar="selected"]');
        selectedCount = document.querySelector('[data-kt-branch-shop-table-select="selected_count"]');
        
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

    // Delete selected rows
    var handleDeleteRows = function () {
        // Select all delete buttons
        const deleteButtons = document.querySelectorAll('[data-kt-branch-shop-table-filter="delete_row"]');

        deleteButtons.forEach(d => {
            // Delete button on click
            d.addEventListener('click', function (e) {
                e.preventDefault();

                // Select parent row
                const parent = e.target.closest('tr');
                const branchShopId = e.target.getAttribute('data-id');

                // SweetAlert2 pop up
                Swal.fire({
                    text: window.translations?.branch_shop?.confirm_delete || "Bạn có chắc chắn muốn xóa chi nhánh này?",
                    icon: "warning",
                    showCancelButton: true,
                    buttonsStyling: false,
                    confirmButtonText: window.translations?.common?.yes_delete || "Có, xóa!",
                    cancelButtonText: window.translations?.common?.no_cancel || "Không, hủy",
                    customClass: {
                        confirmButton: "btn fw-bold btn-danger",
                        cancelButton: "btn fw-bold btn-active-light-primary"
                    }
                }).then(function (result) {
                    if (result.value) {
                        // Delete request
                        $.ajax({
                            url: `/admin/branch-shops/${branchShopId}`,
                            type: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function (response) {
                                if (response.success) {
                                    Swal.fire({
                                        text: response.message,
                                        icon: "success",
                                        buttonsStyling: false,
                                        confirmButtonText: window.translations?.common?.ok_got_it || "Ok, đã hiểu!",
                                        customClass: {
                                            confirmButton: "btn fw-bold btn-primary",
                                        }
                                    }).then(function () {
                                        // Reload table
                                        dt.draw();
                                    });
                                } else {
                                    Swal.fire({
                                        text: response.message,
                                        icon: "error",
                                        buttonsStyling: false,
                                        confirmButtonText: window.translations?.common?.ok_got_it || "Ok, đã hiểu!",
                                        customClass: {
                                            confirmButton: "btn fw-bold btn-primary",
                                        }
                                    });
                                }
                            },
                            error: function (xhr) {
                                let message = window.translations?.branch_shop?.delete_error || 'Có lỗi xảy ra khi xóa chi nhánh';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    message = xhr.responseJSON.message;
                                }
                                Swal.fire({
                                    text: message,
                                    icon: "error",
                                    buttonsStyling: false,
                                    confirmButtonText: window.translations?.common?.ok_got_it || "Ok, đã hiểu!",
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

        // Select all delete selected button
        const deleteSelected = document.querySelector('[data-kt-branch-shop-table-select="delete_selected"]');
        deleteSelected.addEventListener('click', function () {
            // SweetAlert2 pop up
            Swal.fire({
                text: window.translations?.branch_shop?.confirm_delete_selected || "Bạn có chắc chắn muốn xóa các chi nhánh đã chọn?",
                icon: "warning",
                showCancelButton: true,
                buttonsStyling: false,
                confirmButtonText: window.translations?.common?.yes_delete || "Có, xóa!",
                cancelButtonText: window.translations?.common?.no_cancel || "Không, hủy",
                customClass: {
                    confirmButton: "btn fw-bold btn-danger",
                    cancelButton: "btn fw-bold btn-active-light-primary"
                }
            }).then(function (result) {
                if (result.value) {
                    // Get selected IDs
                    var ids = [];
                    table.find('[type="checkbox"]:checked').each(function () {
                        if ($(this).val() !== '1') { // Exclude header checkbox
                            ids.push($(this).val());
                        }
                    });

                    if (ids.length > 0) {
                        // Delete request
                        $.ajax({
                            url: '/admin/branch-shops/bulk-action',
                            type: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            data: {
                                action: 'delete',
                                ids: ids
                            },
                            success: function (response) {
                                if (response.success) {
                                    Swal.fire({
                                        text: response.message,
                                        icon: "success",
                                        buttonsStyling: false,
                                        confirmButtonText: "Ok, đã hiểu!",
                                        customClass: {
                                            confirmButton: "btn fw-bold btn-primary",
                                        }
                                    }).then(function () {
                                        // Reload table
                                        dt.draw();
                                        // Reset selection
                                        table.find('[data-kt-check="true"]').prop('checked', false);
                                        toggleToolbars(false);
                                    });
                                } else {
                                    Swal.fire({
                                        text: response.message,
                                        icon: "error",
                                        buttonsStyling: false,
                                        confirmButtonText: "Ok, đã hiểu!",
                                        customClass: {
                                            confirmButton: "btn fw-bold btn-primary",
                                        }
                                    });
                                }
                            },
                            error: function (xhr) {
                                let message = 'Có lỗi xảy ra khi xóa các chi nhánh';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    message = xhr.responseJSON.message;
                                }
                                Swal.fire({
                                    text: message,
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

    // Handle manage users
    var handleManageUsers = function () {
        // Select all manage users buttons
        const manageUsersButtons = document.querySelectorAll('[data-kt-branch-shop-manage-users="true"]');

        manageUsersButtons.forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault();

                const branchShopId = this.getAttribute('data-branch-shop-id');
                const branchShopName = this.getAttribute('data-branch-shop-name');

                // Open manage users modal
                if (typeof KTBranchShopManageUsers !== 'undefined') {
                    KTBranchShopManageUsers.openModal(branchShopId, branchShopName);
                } else {
                    console.error('KTBranchShopManageUsers not found');
                }
            });
        });
    }

    // Public methods
    return {
        init: function () {
            if (!table) {
                return;
            }

            initTable();
            initToggleToolbar();
            handleSearchDatatable();
            handleResetForm();
            handleDeleteRows();
            handleManageUsers();
            handleFilterDatatable();
        }
    }
}();

// On document ready
KTUtil.onDOMContentLoaded(function () {
    KTBranchShopsList.init();
});
