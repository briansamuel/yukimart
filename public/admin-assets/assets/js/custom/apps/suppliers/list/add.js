"use strict";

// Class definition
var KTSupplierAdd = function () {
    // Shared variables
    var form;
    var submitButton;
    var cancelButton;
    var validator;

    // Private functions
    var initForm = function() {
        // Init form validation rules
        validator = FormValidation.formValidation(
            form,
            {
                fields: {
                    'name': {
                        validators: {
                            notEmpty: {
                                message: 'Tên nhà cung cấp là bắt buộc'
                            }
                        }
                    },
                    'code': {
                        validators: {
                            remote: {
                                message: 'Mã nhà cung cấp đã tồn tại',
                                method: 'POST',
                                url: '/admin/supplier/check-code',
                                data: function() {
                                    return {
                                        code: form.querySelector('[name="code"]').value,
                                        _token: $('meta[name="csrf-token"]').attr('content')
                                    };
                                }
                            }
                        }
                    },
                    'email': {
                        validators: {
                            emailAddress: {
                                message: 'Email không hợp lệ'
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
                    'status': {
                        validators: {
                            notEmpty: {
                                message: 'Trạng thái là bắt buộc'
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

        // Handle form submit
        submitButton.addEventListener('click', function (e) {
            e.preventDefault();

            // Validate form before submit
            if (validator) {
                validator.validate().then(function (status) {
                    console.log('validated!');

                    if (status == 'Valid') {
                        // Show simple loading
                        var originalText = submitButton.innerHTML;
                        submitButton.innerHTML = 'Đang thêm... <span class="spinner-border spinner-border-sm ms-2"></span>';

                        // Disable button to avoid multiple click
                        submitButton.disabled = true;

                        // Submit form via AJAX
                        var formData = new FormData(form);

                        $.ajax({
                            url: form.action,
                            type: 'POST',
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function(response) {
                                // Remove loading indication
                                submitButton.innerHTML = originalText;
                                submitButton.disabled = false;

                                if (response.success) {
                                    // Show success message
                                    Swal.fire({
                                        text: response.message || "Nhà cung cấp đã được thêm thành công!",
                                        icon: "success",
                                        buttonsStyling: false,
                                        confirmButtonText: "Ok, đã hiểu!",
                                        customClass: {
                                            confirmButton: "btn btn-primary"
                                        }
                                    }).then(function (result) {
                                        if (result.isConfirmed) {
                                            // Redirect to suppliers list
                                            window.location.href = "/admin/supplier";
                                        }
                                    });
                                } else {
                                    // Show error message
                                    Swal.fire({
                                        text: response.message || "Có lỗi xảy ra khi thêm nhà cung cấp.",
                                        icon: "error",
                                        buttonsStyling: false,
                                        confirmButtonText: "Ok, đã hiểu!",
                                        customClass: {
                                            confirmButton: "btn btn-primary"
                                        }
                                    });

                                    // Show validation errors if any
                                    if (response.errors) {
                                        for (let field in response.errors) {
                                            validator.updateFieldStatus(field, 'Invalid', 'remote');
                                        }
                                    }
                                }
                            },
                            error: function(xhr) {
                                // Remove loading indication
                                submitButton.innerHTML = originalText;
                                submitButton.disabled = false;

                                let errorMessage = "Có lỗi xảy ra khi thêm nhà cung cấp.";
                                
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMessage = xhr.responseJSON.message;
                                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                                    // Handle validation errors
                                    let errors = xhr.responseJSON.errors;
                                    for (let field in errors) {
                                        validator.updateFieldStatus(field, 'Invalid', 'remote');
                                    }
                                    errorMessage = "Vui lòng kiểm tra lại thông tin đã nhập.";
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
                        });
                    }
                });
            }
        });

        // Handle cancel button
        cancelButton.addEventListener('click', function (e) {
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
                    window.location.href = "/admin/supplier"; // Redirect to suppliers list
                }
            });
        });
    }

    // Auto-generate supplier code
    var initCodeGeneration = function() {
        const nameField = form.querySelector('[name="name"]');
        const codeField = form.querySelector('[name="code"]');

        if (nameField && codeField) {
            nameField.addEventListener('blur', function() {
                if (!codeField.value && nameField.value) {
                    // Generate code from name
                    let code = generateSupplierCode(nameField.value);
                    codeField.value = code;
                    
                    // Trigger validation for code field
                    if (validator) {
                        validator.revalidateField('code');
                    }
                }
            });
        }
    }

    // Generate supplier code from name
    var generateSupplierCode = function(name) {
        // Remove Vietnamese accents and special characters
        let code = name.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
        
        // Convert to uppercase and remove spaces
        code = code.toUpperCase().replace(/[^A-Z0-9]/g, '');
        
        // Take first 6 characters and add SUP prefix
        code = 'SUP' + code.substring(0, 6);
        
        // Add random number if too short
        if (code.length < 6) {
            code += Math.floor(Math.random() * 1000).toString().padStart(3, '0');
        }
        
        return code;
    }

    // Load branches for dropdown
    var loadBranches = function() {
        const branchSelect = form.querySelector('[name="branch_id"]');
        if (branchSelect) {
            $.ajax({
                url: '/admin/branch/active', // Assuming this endpoint exists
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        let html = '<option value="">Chọn chi nhánh</option>';
                        response.data.forEach(function(branch) {
                            html += `<option value="${branch.id}">${branch.name}</option>`;
                        });
                        branchSelect.innerHTML = html;
                    }
                },
                error: function() {
                    console.log('Failed to load branches');
                }
            });
        }
    }

    // Public methods
    return {
        init: function () {
            // Elements
            form = document.querySelector('#kt_add_supplier_form');
            submitButton = document.querySelector('[data-kt-supplier-action="submit"]');
            cancelButton = document.querySelector('[data-kt-supplier-action="cancel"]');

            if (form) {
                initForm();
                initCodeGeneration();
                loadBranches();
            }
        }
    };
}();

// On document ready
KTUtil.onDOMContentLoaded(function () {
    KTSupplierAdd.init();
});
