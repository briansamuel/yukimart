"use strict";

// Class definition
var KTBranchShopsImport = function () {
    // Shared variables
    var modal;
    var form;
    var submitButton;
    var validator;

    // Init form inputs
    var initForm = function () {
        // File input validation
        validator = FormValidation.formValidation(
            form,
            {
                fields: {
                    'import_file': {
                        validators: {
                            notEmpty: {
                                message: window.translations?.branch_shop?.file_required || 'Vui lòng chọn file để import'
                            },
                            file: {
                                extension: 'xlsx,xls,csv',
                                type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel,text/csv',
                                maxSize: 5242880, // 5MB
                                message: window.translations?.branch_shop?.invalid_file || 'Vui lòng chọn file Excel hoặc CSV hợp lệ (tối đa 5MB)'
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
    }

    // Submit form
    var handleSubmit = function () {
        submitButton.addEventListener('click', function (e) {
            e.preventDefault();

            // Validate form before submit
            if (validator) {
                validator.validate().then(function (status) {
                    if (status == 'Valid') {
                        // Show loading indication
                        submitButton.setAttribute('data-kt-indicator', 'on');
                        submitButton.disabled = true;

                        // Create FormData object
                        var formData = new FormData(form);

                        // Submit form via AJAX
                        fetch('/admin/branch-shops/import', {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            // Hide loading indication
                            submitButton.removeAttribute('data-kt-indicator');
                            submitButton.disabled = false;

                            if (data.success) {
                                // Show success message
                                Swal.fire({
                                    text: data.message,
                                    icon: "success",
                                    buttonsStyling: false,
                                    confirmButtonText: window.translations?.common?.ok_got_it || "Ok, đã hiểu!",
                                    customClass: {
                                        confirmButton: "btn fw-bold btn-primary",
                                    }
                                }).then(function () {
                                    // Hide modal
                                    modal.hide();
                                    
                                    // Reset form
                                    form.reset();
                                    
                                    // Reload DataTable
                                    if (typeof KTBranchShopsList !== 'undefined') {
                                        $('#kt_branch_shops_table').DataTable().ajax.reload();
                                    }
                                });
                            } else {
                                // Show error message
                                let errorMessage = data.message;
                                if (data.errors && Array.isArray(data.errors)) {
                                    errorMessage += '\n\n' + window.translations?.branch_shop?.import_errors || 'Lỗi chi tiết:';
                                    data.errors.forEach(error => {
                                        errorMessage += '\n- ' + error;
                                    });
                                }
                                
                                Swal.fire({
                                    text: errorMessage,
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
                            // Hide loading indication
                            submitButton.removeAttribute('data-kt-indicator');
                            submitButton.disabled = false;

                            // Show error message
                            Swal.fire({
                                text: window.translations?.branch_shop?.import_error || "Có lỗi xảy ra khi import dữ liệu. Vui lòng thử lại.",
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

    // Cancel button handler
    var handleCancel = function () {
        // Select cancel button
        const cancelButton = modal.getElement().querySelector('[data-kt-branch-shops-modal-action="close"]');

        // Reset form on cancel
        cancelButton.addEventListener('click', function (e) {
            e.preventDefault();

            Swal.fire({
                text: window.translations?.common?.cancel_confirmation || "Bạn có chắc chắn muốn hủy?",
                icon: "warning",
                showCancelButton: true,
                buttonsStyling: false,
                confirmButtonText: window.translations?.common?.yes_cancel || "Có, hủy!",
                cancelButtonText: window.translations?.common?.no_return || "Không, quay lại",
                customClass: {
                    confirmButton: "btn fw-bold btn-primary",
                    cancelButton: "btn fw-bold btn-active-light-primary"
                }
            }).then(function (result) {
                if (result.value) {
                    form.reset(); // Reset form
                    modal.hide(); // Hide modal
                }
            });
        });
    }

    // File input change handler
    var handleFileInput = function () {
        const fileInput = form.querySelector('input[name="import_file"]');
        
        fileInput.addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (file) {
                // Validate file size
                if (file.size > 5242880) { // 5MB
                    Swal.fire({
                        text: window.translations?.branch_shop?.file_too_large || "File quá lớn. Vui lòng chọn file nhỏ hơn 5MB.",
                        icon: "error",
                        buttonsStyling: false,
                        confirmButtonText: window.translations?.common?.ok_got_it || "Ok, đã hiểu!",
                        customClass: {
                            confirmButton: "btn fw-bold btn-primary",
                        }
                    });
                    e.target.value = '';
                    return;
                }

                // Validate file type
                const allowedTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel', 'text/csv'];
                if (!allowedTypes.includes(file.type)) {
                    Swal.fire({
                        text: window.translations?.branch_shop?.invalid_file_type || "Loại file không hợp lệ. Vui lòng chọn file Excel (.xlsx, .xls) hoặc CSV.",
                        icon: "error",
                        buttonsStyling: false,
                        confirmButtonText: window.translations?.common?.ok_got_it || "Ok, đã hiểu!",
                        customClass: {
                            confirmButton: "btn fw-bold btn-primary",
                        }
                    });
                    e.target.value = '';
                    return;
                }
            }
        });
    }

    return {
        // Public functions
        init: function () {
            // Elements
            modal = new bootstrap.Modal(document.querySelector('#kt_modal_import_branch_shops'));
            form = document.querySelector('#kt_modal_import_branch_shops_form');
            submitButton = form.querySelector('[data-kt-branch-shops-modal-action="submit"]');

            if (!form) {
                return;
            }

            initForm();
            handleSubmit();
            handleCancel();
            handleFileInput();
        }
    };
}();

// On document ready
KTUtil.onDOMContentLoaded(function () {
    KTBranchShopsImport.init();
});
