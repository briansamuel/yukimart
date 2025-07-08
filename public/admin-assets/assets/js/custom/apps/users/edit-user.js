"use strict";

// Class definition
var KTUsersEditUser = function () {
    // Shared variables
    const element = document.getElementById('kt_user_form');
    const form = document.getElementById('kt_user_form');
    const modal = document.getElementById('kt_modal_add_branch_shop');
    const editModal = document.getElementById('kt_modal_edit_branch_shop');

    // Init add branch shop modal
    var initAddBranchShopModal = function () {
        // Init form validation rules
        var validator = FormValidation.formValidation(
            document.querySelector('#kt_modal_add_branch_shop_form'),
            {
                fields: {
                    'branch_shop_id': {
                        validators: {
                            notEmpty: {
                                message: 'Chi nhánh là bắt buộc'
                            }
                        }
                    },
                    'role_in_shop': {
                        validators: {
                            notEmpty: {
                                message: 'Vai trò là bắt buộc'
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

        // Submit button handler
        const submitButton = document.querySelector('#kt_modal_add_branch_shop_form [data-kt-modal-action="submit"]');
        submitButton.addEventListener('click', e => {
            e.preventDefault();

            // Validate form before submit
            if (validator) {
                validator.validate().then(function (status) {
                    console.log('validated!');

                    if (status == 'Valid') {
                        // Show loading indication
                        submitButton.setAttribute('data-kt-indicator', 'on');

                        // Disable button to avoid multiple click
                        submitButton.disabled = true;

                        // Submit form via AJAX
                        const formData = new FormData(document.querySelector('#kt_modal_add_branch_shop_form'));
                        
                        fetch(document.querySelector('#kt_modal_add_branch_shop_form').action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            // Remove loading indication
                            submitButton.removeAttribute('data-kt-indicator');

                            // Enable button
                            submitButton.disabled = false;

                            if (data.success) {
                                // Show success message
                                Swal.fire({
                                    text: data.message,
                                    icon: "success",
                                    buttonsStyling: false,
                                    confirmButtonText: "Ok, got it!",
                                    customClass: {
                                        confirmButton: "btn btn-primary"
                                    }
                                }).then(function (result) {
                                    if (result.isConfirmed) {
                                        // Hide modal
                                        modal.hide();

                                        // Reload page to show updated data
                                        location.reload();
                                    }
                                });
                            } else {
                                // Show error message
                                Swal.fire({
                                    text: data.message,
                                    icon: "error",
                                    buttonsStyling: false,
                                    confirmButtonText: "Ok, got it!",
                                    customClass: {
                                        confirmButton: "btn btn-primary"
                                    }
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            
                            // Remove loading indication
                            submitButton.removeAttribute('data-kt-indicator');

                            // Enable button
                            submitButton.disabled = false;

                            // Show error message
                            Swal.fire({
                                text: "Có lỗi xảy ra, vui lòng thử lại!",
                                icon: "error",
                                buttonsStyling: false,
                                confirmButtonText: "Ok, got it!",
                                customClass: {
                                    confirmButton: "btn btn-primary"
                                }
                            });
                        });
                    }
                });
            }
        });

        // Cancel button handler
        const cancelButton = document.querySelector('#kt_modal_add_branch_shop_form [data-kt-modal-action="cancel"]');
        cancelButton.addEventListener('click', e => {
            e.preventDefault();

            Swal.fire({
                text: "Bạn có chắc chắn muốn hủy?",
                icon: "warning",
                showCancelButton: true,
                buttonsStyling: false,
                confirmButtonText: "Có, hủy!",
                cancelButtonText: "Không",
                customClass: {
                    confirmButton: "btn btn-primary",
                    cancelButton: "btn btn-active-light"
                }
            }).then(function (result) {
                if (result.value) {
                    form.reset(); // Reset form
                    modal.hide();
                } else if (result.dismiss === 'cancel') {
                    Swal.fire({
                        text: "Form của bạn chưa bị hủy!.",
                        icon: "error",
                        buttonsStyling: false,
                        confirmButtonText: "Ok, got it!",
                        customClass: {
                            confirmButton: "btn btn-primary",
                        }
                    });
                }
            });
        });

        // Close button handler
        const closeButton = document.querySelector('#kt_modal_add_branch_shop [data-kt-modal-action="close"]');
        closeButton.addEventListener('click', e => {
            e.preventDefault();

            Swal.fire({
                text: "Bạn có chắc chắn muốn hủy?",
                icon: "warning",
                showCancelButton: true,
                buttonsStyling: false,
                confirmButtonText: "Có, hủy!",
                cancelButtonText: "Không",
                customClass: {
                    confirmButton: "btn btn-primary",
                    cancelButton: "btn btn-active-light"
                }
            }).then(function (result) {
                if (result.value) {
                    form.reset(); // Reset form
                    modal.hide();
                } else if (result.dismiss === 'cancel') {
                    Swal.fire({
                        text: "Form của bạn chưa bị hủy!.",
                        icon: "error",
                        buttonsStyling: false,
                        confirmButtonText: "Ok, got it!",
                        customClass: {
                            confirmButton: "btn btn-primary",
                        }
                    });
                }
            });
        });
    }

    // Init branch shop actions
    var initBranchShopActions = function () {
        // Edit branch shop assignment
        document.querySelectorAll('[data-kt-user-branch-shop-action="edit"]').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                const branchShopId = this.getAttribute('data-branch-shop-id');
                const row = this.closest('tr');
                
                // Get current data from row
                const branchShopName = row.querySelector('td:first-child a').textContent;
                const roleInShop = row.querySelector('td:nth-child(2) .badge').textContent;
                const startDate = row.querySelector('td:nth-child(3)').textContent;
                const isActive = row.querySelector('td:nth-child(4) .badge').classList.contains('badge-light-success');
                const isPrimary = row.querySelector('td:nth-child(5) .badge') !== null;
                
                // Populate edit modal
                document.getElementById('edit_branch_shop_name').textContent = branchShopName;
                document.getElementById('edit_role_in_shop').value = getRoleValueFromLabel(roleInShop);
                document.getElementById('edit_start_date').value = convertDateFormat(startDate);
                document.getElementById('edit_is_active').checked = isActive;
                document.getElementById('edit_is_primary').checked = isPrimary;
                
                // Set form action
                const form = document.getElementById('kt_modal_edit_branch_shop_form');
                const userId = window.location.pathname.split('/').pop().split('/')[0];
                form.action = `/admin/users/${userId}/branch-shops/${branchShopId}`;
                
                // Show modal
                const editModal = new bootstrap.Modal(document.getElementById('kt_modal_edit_branch_shop'));
                editModal.show();
            });
        });

        // Remove branch shop assignment
        document.querySelectorAll('[data-kt-user-branch-shop-action="remove"]').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                const branchShopId = this.getAttribute('data-branch-shop-id');
                const branchShopName = this.closest('tr').querySelector('td:first-child a').textContent;
                
                Swal.fire({
                    text: `Bạn có chắc chắn muốn gỡ bỏ chi nhánh "${branchShopName}" khỏi người dùng này?`,
                    icon: "warning",
                    showCancelButton: true,
                    buttonsStyling: false,
                    confirmButtonText: "Có, gỡ bỏ!",
                    cancelButtonText: "Hủy",
                    customClass: {
                        confirmButton: "btn btn-danger",
                        cancelButton: "btn btn-active-light"
                    }
                }).then(function (result) {
                    if (result.value) {
                        const userId = window.location.pathname.split('/').pop().split('/')[0];
                        
                        fetch(`/admin/users/${userId}/branch-shops/${branchShopId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Content-Type': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    text: data.message,
                                    icon: "success",
                                    buttonsStyling: false,
                                    confirmButtonText: "Ok, got it!",
                                    customClass: {
                                        confirmButton: "btn btn-primary"
                                    }
                                }).then(function () {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    text: data.message,
                                    icon: "error",
                                    buttonsStyling: false,
                                    confirmButtonText: "Ok, got it!",
                                    customClass: {
                                        confirmButton: "btn btn-primary"
                                    }
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire({
                                text: "Có lỗi xảy ra, vui lòng thử lại!",
                                icon: "error",
                                buttonsStyling: false,
                                confirmButtonText: "Ok, got it!",
                                customClass: {
                                    confirmButton: "btn btn-primary"
                                }
                            });
                        });
                    }
                });
            });
        });
    }

    // Helper functions
    function getRoleValueFromLabel(label) {
        const roleMap = {
            'Quản lý': 'manager',
            'Nhân viên': 'staff',
            'Thu ngân': 'cashier',
            'Bán hàng': 'sales',
            'Thủ kho': 'warehouse_keeper'
        };
        return roleMap[label] || 'staff';
    }

    function convertDateFormat(dateStr) {
        if (dateStr === '-') return '';
        // Convert from d/m/Y to Y-m-d
        const parts = dateStr.split('/');
        if (parts.length === 3) {
            return `${parts[2]}-${parts[1].padStart(2, '0')}-${parts[0].padStart(2, '0')}`;
        }
        return '';
    }

    return {
        // Public functions
        init: function () {
            initAddBranchShopModal();
            initBranchShopActions();
        }
    };
}();

// On document ready
KTUtil.onDOMContentLoaded(function () {
    KTUsersEditUser.init();
});
