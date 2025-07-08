"use strict";

var KTProductCategoriesList = function () {
    // Define shared variables
    var table = document.getElementById('kt_table_product_categories');
    var datatable;
    var toolbarBase;
    var toolbarSelected;
    var selectedCount;

    // Private functions
    var initProductCategoriesTable = function () {
        // Set date data order
        const tableRows = table.querySelectorAll('tbody tr');

        // Init datatable --- more info on datatables: https://datatables.net/manual/
        datatable = $(table).DataTable({
            "info": false,
            'order': [],
            'pageLength': 10,
            'processing': true,
            'serverSide': true,
            'ajax': {
                'url': '/admin/product-categories/data',
                'type': 'GET',
                'data': function (d) {
                    // Add custom filters
                    d.status = $('[data-kt-product-category-table-filter="status"]').val();
                    d.parent = $('[data-kt-product-category-table-filter="parent"]').val();
                    d.search = $('[data-kt-product-category-table-filter="search"]').val();
                }
            },
            'columns': [
                { data: 'checkbox', orderable: false, searchable: false },
                { data: 'name' },
                { data: 'parent_name' },
                { data: 'products_count' },
                { data: 'status' },
                { data: 'sort_order' },
                { data: 'created_at' },
                { data: 'actions', orderable: false, searchable: false }
            ],
            "columnDefs": [
                {
                    "targets": 0,
                    "orderable": false,
                    "render": function (data, type, full, meta) {
                        return `
                            <div class="form-check form-check-sm form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" value="${full.id}" />
                            </div>`;
                    }
                },
                {
                    "targets": 1,
                    "render": function (data, type, full, meta) {
                        var level = full.level || 0;
                        var indent = '—'.repeat(level);
                        var image = full.image ? `<img src="${full.image}" class="w-35px h-35px rounded me-3" alt="${full.name}">` : 
                                   `<div class="symbol symbol-35px me-3"><div class="symbol-label bg-light-primary"><i class="ki-duotone ki-category fs-3 text-primary"><span class="path1"></span><span class="path2"></span></i></div></div>`;
                        
                        return `
                            <div class="d-flex align-items-center">
                                ${image}
                                <div class="d-flex flex-column">
                                    <a href="/admin/product-categories/${full.id}" class="text-gray-800 text-hover-primary mb-1">${indent} ${full.name}</a>
                                    <span class="text-muted fs-7">${full.slug}</span>
                                </div>
                            </div>`;
                    }
                },
                {
                    "targets": 2,
                    "render": function (data, type, full, meta) {
                        return full.parent_name || '<span class="text-muted">—</span>';
                    }
                },
                {
                    "targets": 3,
                    "render": function (data, type, full, meta) {
                        return `<span class="badge badge-light-info">${full.products_count}</span>`;
                    }
                },
                {
                    "targets": 4,
                    "render": function (data, type, full, meta) {
                        var status = full.is_active;
                        if (status) {
                            return '<span class="badge badge-light-success">Hoạt động</span>';
                        } else {
                            return '<span class="badge badge-light-danger">Không hoạt động</span>';
                        }
                    }
                },
                {
                    "targets": 5,
                    "render": function (data, type, full, meta) {
                        return `<span class="text-muted">${full.sort_order}</span>`;
                    }
                },
                {
                    "targets": 6,
                    "render": function (data, type, full, meta) {
                        return moment(full.created_at).format('DD/MM/YYYY HH:mm');
                    }
                },
                {
                    "targets": -1,
                    "data": null,
                    "orderable": false,
                    "className": "text-end",
                    "render": function (data, type, full, meta) {
                        return `
                            <a href="#" class="btn btn-light btn-active-light-primary btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                Thao tác
                                <i class="ki-duotone ki-down fs-5 m-0"></i>
                            </a>
                            <!--begin::Menu-->
                            <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4" data-kt-menu="true">
                                <!--begin::Menu item-->
                                <div class="menu-item px-3">
                                    <a href="/admin/product-categories/${full.id}" class="menu-link px-3">Xem</a>
                                </div>
                                <!--end::Menu item-->
                                <!--begin::Menu item-->
                                <div class="menu-item px-3">
                                    <a href="/admin/product-categories/${full.id}/edit" class="menu-link px-3">Sửa</a>
                                </div>
                                <!--end::Menu item-->
                                <!--begin::Menu item-->
                                <div class="menu-item px-3">
                                    <a href="#" class="menu-link px-3" data-kt-product-category-table-filter="delete_row" data-kt-product-category-id="${full.id}">Xóa</a>
                                </div>
                                <!--end::Menu item-->
                            </div>
                            <!--end::Menu-->
                        `;
                    },
                },
            ],
        });

        // Re-init functions on every table re-draw -- more info: https://datatables.net/reference/event/draw
        datatable.on('draw', function () {
            initToggleToolbar();
            handleDeleteRows();
            toggleToolbars();
        });
    }

    // Search Datatable --- official docs reference: https://datatables.net/reference/api/search()
    var handleSearchDatatable = function () {
        const filterSearch = document.querySelector('[data-kt-product-category-table-filter="search"]');
        filterSearch.addEventListener('keyup', function (e) {
            datatable.search(e.target.value).draw();
        });
    }

    // Filter Datatable
    var handleFilterDatatable = function () {
        // Select filter options
        const filterForm = document.querySelector('[data-kt-product-category-table-filter="form"]');
        const filterButton = filterForm.querySelector('[data-kt-product-category-table-filter="filter"]');
        const selectOptions = filterForm.querySelectorAll('select');

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

    // Reset Filter
    var handleResetForm = function () {
        // Select reset button
        const resetButton = document.querySelector('[data-kt-product-category-table-filter="reset"]');

        // Reset datatable
        resetButton.addEventListener('click', function () {
            // Select filter options
            const filterForm = document.querySelector('[data-kt-product-category-table-filter="form"]');
            const selectOptions = filterForm.querySelectorAll('select');

            // Reset select2 values -- more info: https://select2.org/programmatic-control/add-select-clear-items
            selectOptions.forEach(select => {
                $(select).val('').trigger('change');
            });

            // Reset datatable --- official docs reference: https://datatables.net/reference/api/search()
            datatable.search('').draw();
        });
    }

    // Delete category
    var handleDeleteRows = function () {
        // Select all delete buttons
        const deleteButtons = table.querySelectorAll('[data-kt-product-category-table-filter="delete_row"]');

        deleteButtons.forEach(d => {
            // Delete button on click
            d.addEventListener('click', function (e) {
                e.preventDefault();

                // Select parent row
                const parent = e.target.closest('tr');

                // Get category name
                const categoryName = parent.querySelectorAll('td')[1].innerText;
                const categoryId = d.getAttribute('data-kt-product-category-id');

                // SweetAlert2 pop up --- official docs reference: https://sweetalert2.github.io/
                Swal.fire({
                    text: "Bạn có chắc chắn muốn xóa danh mục " + categoryName + "?",
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
                        // Delete via AJAX
                        $.ajax({
                            url: `/admin/product-categories/${categoryId}`,
                            type: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        text: "Bạn đã xóa danh mục " + categoryName + "!.",
                                        icon: "success",
                                        buttonsStyling: false,
                                        confirmButtonText: "Ok, đã hiểu!",
                                        customClass: {
                                            confirmButton: "btn fw-bold btn-primary",
                                        }
                                    }).then(function () {
                                        // Reload datatable
                                        datatable.draw();
                                    });
                                } else {
                                    Swal.fire({
                                        text: response.message || "Có lỗi xảy ra khi xóa danh mục.",
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
                                    text: "Có lỗi xảy ra khi xóa danh mục.",
                                    icon: "error",
                                    buttonsStyling: false,
                                    confirmButtonText: "Ok, đã hiểu!",
                                    customClass: {
                                        confirmButton: "btn fw-bold btn-primary",
                                    }
                                });
                            }
                        });
                    } else if (result.dismiss === 'cancel') {
                        Swal.fire({
                            text: categoryName + " không bị xóa.",
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

    // Init toggle toolbar
    var initToggleToolbar = function () {
        // Toggle selected action toolbar
        // Select all checkboxes
        const checkboxes = table.querySelectorAll('[type="checkbox"]');

        // Select elements
        toolbarBase = document.querySelector('[data-kt-product-category-table-toolbar="base"]');
        toolbarSelected = document.querySelector('[data-kt-product-category-table-toolbar="selected"]');
        selectedCount = document.querySelector('[data-kt-product-category-table-select="selected_count"]');
        const deleteSelected = document.querySelector('[data-kt-product-category-table-select="delete_selected"]');

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
                text: "Bạn có chắc chắn muốn xóa các danh mục đã chọn?",
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
                    // Get selected IDs
                    const selectedIds = [];
                    const selectedCheckboxes = table.querySelectorAll('tbody [type="checkbox"]:checked');
                    
                    selectedCheckboxes.forEach(checkbox => {
                        selectedIds.push(checkbox.value);
                    });

                    if (selectedIds.length > 0) {
                        // Delete via AJAX
                        $.ajax({
                            url: '/admin/product-categories/bulk-delete',
                            type: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            data: {
                                ids: selectedIds
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        text: "Bạn đã xóa tất cả các danh mục đã chọn!.",
                                        icon: "success",
                                        buttonsStyling: false,
                                        confirmButtonText: "Ok, đã hiểu!",
                                        customClass: {
                                            confirmButton: "btn fw-bold btn-primary",
                                        }
                                    }).then(function () {
                                        // Reload datatable
                                        datatable.draw();
                                        
                                        // Clear header checkbox
                                        const headerCheckbox = table.querySelectorAll('[type="checkbox"]')[0];
                                        headerCheckbox.checked = false;
                                    });
                                } else {
                                    Swal.fire({
                                        text: response.message || "Có lỗi xảy ra khi xóa danh mục.",
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
                                    text: "Có lỗi xảy ra khi xóa danh mục.",
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

    // Public methods
    return {
        init: function () {
            if (!table) {
                return;
            }

            initProductCategoriesTable();
            initToggleToolbar();
            handleSearchDatatable();
            handleResetForm();
            handleDeleteRows();
            handleFilterDatatable();
        }
    }
}();

// On document ready
KTUtil.onDOMContentLoaded(function () {
    KTProductCategoriesList.init();
});
