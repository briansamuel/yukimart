"use strict";

var KTModalAddBranchShop = function () {
    var submitButton;
    var cancelButton;
    var closeButton;
    var validator;
    var form;
    var modal;
    var modalEl;

    // Init form inputs
    var initForm = function () {
        // Load managers for dropdown
        loadManagers();

        // Handle delivery checkbox
        const deliveryCheckbox = form.querySelector('input[name="has_delivery"]');
        const deliveryFields = document.getElementById('delivery_fields');

        deliveryCheckbox.addEventListener('change', function () {
            if (this.checked) {
                deliveryFields.style.display = 'block';
            } else {
                deliveryFields.style.display = 'none';
                // Clear delivery fields
                form.querySelector('input[name="delivery_radius"]').value = '';
                form.querySelector('input[name="delivery_fee"]').value = '';
            }
        });
    }

    // Load managers for dropdown
    var loadManagers = function () {
        const managerSelect = form.querySelector('select[name="manager_id"]');
        
        // Clear existing options except placeholder
        $(managerSelect).empty().append('<option></option>');

        // Load managers via AJAX
        $.ajax({
            url: '/admin/branch-shops/dropdown/managers',
            type: 'GET',
            success: function (response) {
                if (response.success && response.data) {
                    response.data.forEach(function (manager) {
                        const option = new Option(manager.text, manager.id, false, false);
                        $(managerSelect).append(option);
                    });
                }
            },
            error: function (xhr) {
                console.error('Error loading managers:', xhr);
            }
        });
    }

    // Init validation
    var initValidation = function () {
        validator = FormValidation.formValidation(
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
                                message: 'Số điện thoại không đúng định dạng'
                            }
                        }
                    },
                    'email': {
                        validators: {
                            emailAddress: {
                                message: 'Email không đúng định dạng'
                            }
                        }
                    },
                    'shop_type': {
                        validators: {
                            notEmpty: {
                                message: 'Loại cửa hàng là bắt buộc'
                            }
                        }
                    },
                    'status': {
                        validators: {
                            notEmpty: {
                                message: 'Trạng thái là bắt buộc'
                            }
                        }
                    },
                    'area': {
                        validators: {
                            numeric: {
                                message: 'Diện tích phải là số'
                            }
                        }
                    },
                    'staff_count': {
                        validators: {
                            integer: {
                                message: 'Số lượng nhân viên phải là số nguyên'
                            }
                        }
                    },
                    'delivery_radius': {
                        validators: {
                            numeric: {
                                message: 'Bán kính giao hàng phải là số'
                            }
                        }
                    },
                    'delivery_fee': {
                        validators: {
                            numeric: {
                                message: 'Phí giao hàng phải là số'
                            }
                        }
                    },
                    'latitude': {
                        validators: {
                            numeric: {
                                message: 'Vĩ độ phải là số'
                            },
                            between: {
                                min: -90,
                                max: 90,
                                message: 'Vĩ độ phải từ -90 đến 90'
                            }
                        }
                    },
                    'longitude': {
                        validators: {
                            numeric: {
                                message: 'Kinh độ phải là số'
                            },
                            between: {
                                min: -180,
                                max: 180,
                                message: 'Kinh độ phải từ -180 đến 180'
                            }
                        }
                    },
                    'sort_order': {
                        validators: {
                            integer: {
                                message: 'Thứ tự sắp xếp phải là số nguyên'
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
                    console.log('validated!');

                    if (status == 'Valid') {
                        submitButton.setAttribute('data-kt-indicator', 'on');
                        submitButton.disabled = true;

                        // Prepare form data
                        const formData = new FormData(form);

                        // Handle working days
                        const workingDays = [];
                        form.querySelectorAll('input[name="working_days[]"]:checked').forEach(function (checkbox) {
                            workingDays.push(checkbox.value);
                        });
                        formData.delete('working_days[]');
                        formData.append('working_days', JSON.stringify(workingDays));

                        // Submit form
                        $.ajax({
                            url: '/admin/branch-shops',
                            type: 'POST',
                            data: formData,
                            processData: false,
                            contentType: false,
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function (response) {
                                submitButton.removeAttribute('data-kt-indicator');
                                submitButton.disabled = false;

                                if (response.success) {
                                    Swal.fire({
                                        text: response.message,
                                        icon: "success",
                                        buttonsStyling: false,
                                        confirmButtonText: "Ok, đã hiểu!",
                                        customClass: {
                                            confirmButton: "btn btn-primary"
                                        }
                                    }).then(function (result) {
                                        if (result.isConfirmed) {
                                            modal.hide();
                                            // Reload the table
                                            if (typeof KTBranchShopsList !== 'undefined') {
                                                location.reload();
                                            }
                                        }
                                    });
                                } else {
                                    Swal.fire({
                                        text: response.message,
                                        icon: "error",
                                        buttonsStyling: false,
                                        confirmButtonText: "Ok, đã hiểu!",
                                        customClass: {
                                            confirmButton: "btn btn-primary"
                                        }
                                    });
                                }
                            },
                            error: function (xhr) {
                                submitButton.removeAttribute('data-kt-indicator');
                                submitButton.disabled = false;

                                let message = 'Có lỗi xảy ra khi tạo chi nhánh';
                                if (xhr.responseJSON) {
                                    if (xhr.responseJSON.message) {
                                        message = xhr.responseJSON.message;
                                    }
                                    if (xhr.responseJSON.errors) {
                                        // Handle validation errors
                                        const errors = xhr.responseJSON.errors;
                                        let errorMessage = '';
                                        Object.keys(errors).forEach(function (key) {
                                            errorMessage += errors[key][0] + '\n';
                                        });
                                        message = errorMessage;
                                    }
                                }

                                Swal.fire({
                                    text: message,
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
    }

    // Cancel button handler
    var handleCancel = function () {
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
                    form.reset();
                    modal.hide();
                }
            });
        });
    }

    // Close button handler
    var handleClose = function () {
        closeButton.addEventListener('click', function (e) {
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
                    form.reset();
                    modal.hide();
                }
            });
        });
    }

    return {
        // Public functions
        init: function () {
            modalEl = document.querySelector('#kt_modal_add_branch_shop');

            if (!modalEl) {
                return;
            }

            modal = new bootstrap.Modal(modalEl);

            form = document.querySelector('#kt_modal_add_branch_shop_form');
            submitButton = document.querySelector('[data-kt-branch-shops-modal-action="submit"]');
            cancelButton = document.querySelector('[data-kt-branch-shops-modal-action="cancel"]');
            closeButton = document.querySelector('[data-kt-branch-shops-modal-action="close"]');

            initForm();
            initValidation();
            handleSubmit();
            handleCancel();
            handleClose();
        }
    };
}();

// On document ready
KTUtil.onDOMContentLoaded(function () {
    KTModalAddBranchShop.init();
});
