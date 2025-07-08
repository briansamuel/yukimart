"use strict";

// Class definition
var KTBranchShopManageUsers = function () {
    // Shared variables
    var modal;
    var addUserModal;
    var form;
    var submitButton;
    var validator;
    var table;
    var dt;
    var currentBranchShopId;
    var currentBranchShopName;

    // Init form inputs
    var initForm = function () {
        // Form validation
        validator = FormValidation.formValidation(
            form,
            {
                fields: {
                    'user_id': {
                        validators: {
                            notEmpty: {
                                message: window.translations?.users?.user_required || 'Vui lòng chọn người dùng'
                            }
                        }
                    },
                    'role_in_shop': {
                        validators: {
                            notEmpty: {
                                message: window.translations?.branch_shop?.role_required || 'Vui lòng chọn vai trò'
                            }
                        }
                    }
                },
                plugins: {
                    trigger: new FormValidation.plugins.Trigger(),
                    bootstrap: new FormValidation.plugins.Bootstrap5({
                        rowSelector: '.fv-row',
                        eleInvalidClass: '',
                        eleValidClass: ''
                    })
                }
            }
        );

        // Initialize Select2 for user selection
        $('#user_select').select2({
            ajax: {
                url: '/admin/users/dropdown/available',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term,
                        branch_shop_id: currentBranchShopId
                    };
                },
                processResults: function (data) {
                    return {
                        results: data.data || []
                    };
                },
                cache: true
            },
            placeholder: window.translations?.users?.select_user || 'Chọn người dùng',
            minimumInputLength: 0,
            allowClear: true
        });

        // Initialize Select2 for role selection
        $('#role_select').select2({
            placeholder: window.translations?.branch_shop?.select_role || 'Chọn vai trò',
            allowClear: true
        });

        // Initialize date picker
        $("#start_date").flatpickr({
            dateFormat: "Y-m-d",
            defaultDate: "today"
        });
    }

    // Init users table
    var initUsersTable = function () {
        // Init datatable
        dt = $(table).DataTable({
            searchDelay: 500,
            processing: true,
            serverSide: true,
            order: [[0, 'asc']],
            ajax: {
                url: '/admin/branch-shops/' + currentBranchShopId + '/users/data',
                type: 'GET'
            },
            columns: [
                { data: 'user_name' },
                { data: 'user_email' },
                { data: 'role_label' },
                { data: 'formatted_start_date' },
                { data: 'status_badge' },
                { data: 'is_primary_badge' },
                { data: 'actions', orderable: false, searchable: false }
            ],
            columnDefs: [
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
                                    <a href="#" class="menu-link px-3" data-kt-user-branch-edit="true" data-user-id="${row.user_id}">
                                        <i class="fas fa-pencil fs-5 me-2"></i>
                                        ${window.translations?.common?.edit || 'Chỉnh sửa'}
                                    </a>
                                </div>
                                <div class="menu-item px-3">
                                    <a href="#" class="menu-link px-3 text-danger" data-kt-user-branch-remove="true" data-user-id="${row.user_id}" data-user-name="${row.user_name}">
                                        <i class="fas fa-trash fs-5 me-2"></i>
                                        ${window.translations?.common?.remove || 'Xóa'}
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
            }
        });

        // Re-init functions on every table re-draw
        dt.on('draw', function () {
            handleUserActions();
            KTMenu.createInstances();
        });
    }

    // Handle user actions (edit, remove)
    var handleUserActions = function () {
        // Handle edit user
        const editButtons = document.querySelectorAll('[data-kt-user-branch-edit="true"]');
        editButtons.forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault();
                const userId = this.getAttribute('data-user-id');
                // TODO: Implement edit functionality
                console.log('Edit user:', userId);
            });
        });

        // Handle remove user
        const removeButtons = document.querySelectorAll('[data-kt-user-branch-remove="true"]');
        removeButtons.forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault();
                const userId = this.getAttribute('data-user-id');
                const userName = this.getAttribute('data-user-name');
                
                Swal.fire({
                    text: `Bạn có chắc chắn muốn xóa ${userName} khỏi chi nhánh này?`,
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
                        removeUserFromBranch(userId);
                    }
                });
            });
        });
    }

    // Remove user from branch
    var removeUserFromBranch = function (userId) {
        $.ajax({
            url: `/admin/branch-shops/${currentBranchShopId}/users/${userId}`,
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
                        dt.ajax.reload();
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
                let message = 'Có lỗi xảy ra khi xóa người dùng';
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

    // Submit form
    var handleSubmit = function () {
        submitButton.addEventListener('click', function (e) {
            e.preventDefault();

            if (validator) {
                validator.validate().then(function (status) {
                    if (status == 'Valid') {
                        submitButton.setAttribute('data-kt-indicator', 'on');
                        submitButton.disabled = true;

                        // Get form data
                        var formData = new FormData(form);
                        formData.append('branch_shop_id', currentBranchShopId);

                        // Submit form via AJAX
                        fetch('/admin/branch-shops/' + currentBranchShopId + '/users', {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            submitButton.removeAttribute('data-kt-indicator');
                            submitButton.disabled = false;

                            if (data.success) {
                                Swal.fire({
                                    text: data.message,
                                    icon: "success",
                                    buttonsStyling: false,
                                    confirmButtonText: window.translations?.common?.ok_got_it || "Ok, đã hiểu!",
                                    customClass: {
                                        confirmButton: "btn fw-bold btn-primary",
                                    }
                                }).then(function () {
                                    addUserModal.hide();
                                    form.reset();
                                    $('#user_select').val(null).trigger('change');
                                    $('#role_select').val(null).trigger('change');
                                    dt.ajax.reload();
                                });
                            } else {
                                Swal.fire({
                                    text: data.message,
                                    icon: "error",
                                    buttonsStyling: false,
                                    confirmButtonText: window.translations?.common?.ok_got_it || "Ok, đã hiểu!",
                                    customClass: {
                                        confirmButton: "btn fw-bold btn-primary",
                                    }
                                });
                            }
                        })
                        .catch(error => {
                            submitButton.removeAttribute('data-kt-indicator');
                            submitButton.disabled = false;

                            Swal.fire({
                                text: "Có lỗi xảy ra khi thêm người dùng. Vui lòng thử lại.",
                                icon: "error",
                                buttonsStyling: false,
                                confirmButtonText: window.translations?.common?.ok_got_it || "Ok, đã hiểu!",
                                customClass: {
                                    confirmButton: "btn fw-bold btn-primary",
                                }
                            });
                        });
                    }
                });
            }
        });
    }

    return {
        // Public functions
        init: function () {
            modal = new bootstrap.Modal(document.querySelector('#kt_modal_manage_branch_shop_users'));
            addUserModal = new bootstrap.Modal(document.querySelector('#kt_modal_add_user_to_branch'));
            form = document.querySelector('#kt_modal_add_user_form');
            submitButton = form.querySelector('[data-kt-users-modal-action="submit"]');
            table = document.querySelector('#kt_branch_shop_users_table');

            if (!form || !table) {
                return;
            }

            initForm();
            handleSubmit();

            // Handle add user button
            document.getElementById('btn_add_user_to_branch').addEventListener('click', function () {
                addUserModal.show();
            });

            // Handle search
            document.getElementById('search_users').addEventListener('keyup', function (e) {
                if (dt) {
                    dt.search(e.target.value).draw();
                }
            });
        },

        openModal: function (branchShopId, branchShopName) {
            currentBranchShopId = branchShopId;
            currentBranchShopName = branchShopName;

            // Update modal title
            document.getElementById('modal_branch_shop_name').textContent = `Quản lý nhân viên - ${branchShopName}`;
            document.getElementById('modal_branch_shop_subtitle').textContent = `Chi nhánh: ${branchShopName}`;

            // Initialize table
            if (dt) {
                dt.destroy();
            }
            initUsersTable();

            // Show modal
            modal.show();
        }
    };
}();

// On document ready
KTUtil.onDOMContentLoaded(function () {
    KTBranchShopManageUsers.init();
});
