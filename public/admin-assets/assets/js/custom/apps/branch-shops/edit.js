"use strict";

// Class definition
var KTBranchShopEdit = function () {
    // Shared variables
    const element = document.getElementById('kt_branch_shop_edit_form');
    const form = document.getElementById('kt_branch_shop_edit_form');
    const modal = document.getElementById('kt_modal_edit_branch_shop');

    // Init edit form
    var initEditForm = function() {

        // Init form validation rules. For more info check the FormValidation plugin's official documentation:https://formvalidation.io/
        var validator = FormValidation.formValidation(
            form,
            {
                fields: {
                    'name': {
                        validators: {
                            notEmpty: {
                                message: 'Tên chi nhánh là bắt buộc'
                            }
                        }
                    },
                    'address': {
                        validators: {
                            notEmpty: {
                                message: 'Địa chỉ là bắt buộc'
                            }
                        }
                    },
                    'province': {
                        validators: {
                            notEmpty: {
                                message: 'Tỉnh/Thành phố là bắt buộc'
                            }
                        }
                    },
                    'district': {
                        validators: {
                            notEmpty: {
                                message: 'Quận/Huyện là bắt buộc'
                            }
                        }
                    },
                    'ward': {
                        validators: {
                            notEmpty: {
                                message: 'Phường/Xã là bắt buộc'
                            }
                        }
                    },
                    'phone': {
                        validators: {
                            regexp: {
                                regexp: /^[0-9+\-\s\(\)]+$/,
                                message: 'Số điện thoại không hợp lệ'
                            }
                        }
                    },
                    'email': {
                        validators: {
                            emailAddress: {
                                message: 'Email không hợp lệ'
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
        const submitButton = document.getElementById('kt_branch_shop_edit_submit');
        submitButton.addEventListener('click', function (e) {
            // Prevent default button action
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

                        // Simulate form submission. For more info check the plugin's official documentation: https://sweetalert2.github.io/
                        setTimeout(function() {
                            // Remove loading indication
                            submitButton.removeAttribute('data-kt-indicator');

                            // Enable button
                            submitButton.disabled = false;

                            // Submit form via AJAX
                            submitForm();
                        }, 2000);
                    }
                });
            }
        });
    }

    // Submit form
    var submitForm = function() {
        // Get form data
        const formData = new FormData(form);
        const actionUrl = form.getAttribute('action');

        // Submit form via AJAX
        fetch(actionUrl, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                Swal.fire({
                    text: data.message || "Cập nhật chi nhánh thành công!",
                    icon: "success",
                    buttonsStyling: false,
                    confirmButtonText: "Ok, đã hiểu!",
                    customClass: {
                        confirmButton: "btn btn-primary"
                    }
                }).then(function (result) {
                    if (result.isConfirmed) {
                        // Redirect to branch shops list
                        window.location.href = '/admin/branch-shops';
                    }
                });
            } else {
                // Show error message
                let errorMessage = data.message || 'Có lỗi xảy ra khi cập nhật chi nhánh';
                
                if (data.errors) {
                    errorMessage += ':\n';
                    if (Array.isArray(data.errors)) {
                        errorMessage += data.errors.join('\n');
                    } else {
                        Object.keys(data.errors).forEach(key => {
                            if (Array.isArray(data.errors[key])) {
                                errorMessage += data.errors[key].join('\n') + '\n';
                            } else {
                                errorMessage += data.errors[key] + '\n';
                            }
                        });
                    }
                }

                Swal.fire({
                    text: errorMessage,
                    icon: "error",
                    buttonsStyling: false,
                    confirmButtonText: "Ok, đã hiểu!",
                    customClass: {
                        confirmButton: "btn btn-primary"
                    }
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                text: "Có lỗi xảy ra khi cập nhật chi nhánh. Vui lòng thử lại.",
                icon: "error",
                buttonsStyling: false,
                confirmButtonText: "Ok, đã hiểu!",
                customClass: {
                    confirmButton: "btn btn-primary"
                }
            });
        })
        .finally(() => {
            // Remove loading indication
            const submitButton = document.getElementById('kt_branch_shop_edit_submit');
            submitButton.removeAttribute('data-kt-indicator');
            submitButton.disabled = false;
        });
    }

    return {
        // Public functions
        init: function () {
            initEditForm();
        }
    };
}();

// On document ready
KTUtil.onDOMContentLoaded(function () {
    KTBranchShopEdit.init();
});
