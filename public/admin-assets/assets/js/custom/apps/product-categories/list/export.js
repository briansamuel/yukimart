"use strict";

// Class definition
var KTModalExportCategories = function () {
    var submitButton;
    var cancelButton;
    var closeButton;
    var validator;
    var form;
    var modal;
    var modalEl;

    // Init form inputs
    var initForm = function() {
        // Init date range picker
        const dateRangeInput = form.querySelector('[name="date_range"]');
        
        if (dateRangeInput) {
            $(dateRangeInput).daterangepicker({
                buttonClasses: ' btn',
                applyClass: 'btn-primary',
                cancelClass: 'btn-secondary',
                locale: {
                    format: 'DD/MM/YYYY',
                    separator: ' - ',
                    applyLabel: 'Áp dụng',
                    cancelLabel: 'Hủy',
                    fromLabel: 'Từ',
                    toLabel: 'Đến',
                    customRangeLabel: 'Tùy chọn',
                    weekLabel: 'T',
                    daysOfWeek: ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'],
                    monthNames: ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6',
                        'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'],
                    firstDay: 1
                },
                autoUpdateInput: false
            });

            $(dateRangeInput).on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
            });

            $(dateRangeInput).on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
            });
        }
    }

    // Init form validation
    var initValidation = function() {
        // Init form validation rules
        validator = FormValidation.formValidation(
            form,
            {
                fields: {
                    'format': {
                        validators: {
                            notEmpty: {
                                message: 'Vui lòng chọn định dạng xuất'
                            }
                        }
                    },
                    'export_type': {
                        validators: {
                            notEmpty: {
                                message: 'Vui lòng chọn loại dữ liệu xuất'
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
    var handleSubmit = function() {
        // Handle form submit
        submitButton.addEventListener('click', function (e) {
            // Prevent button default action
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

                        // Get form data
                        const formData = new FormData(form);
                        const params = new URLSearchParams();
                        
                        for (let [key, value] of formData.entries()) {
                            params.append(key, value);
                        }

                        // Create download link
                        const downloadUrl = `/admin/product-categories/export?${params.toString()}`;
                        
                        // Create temporary link and trigger download
                        const link = document.createElement('a');
                        link.href = downloadUrl;
                        link.download = '';
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);

                        // Remove loading indication
                        submitButton.removeAttribute('data-kt-indicator');
                        
                        // Enable button
                        submitButton.disabled = false;

                        // Show success message
                        Swal.fire({
                            text: "Xuất dữ liệu thành công!",
                            icon: "success",
                            buttonsStyling: false,
                            confirmButtonText: "Ok, đã hiểu!",
                            customClass: {
                                confirmButton: "btn btn-primary"
                            }
                        }).then(function (result) {
                            if (result.isConfirmed) {
                                // Hide modal
                                modal.hide();
                                
                                // Reset form
                                form.reset();
                                
                                // Reset Select2
                                $(form).find('select').val('').trigger('change');
                            }
                        });
                    }
                });
            }
        });
    }

    // Cancel button handler
    var handleCancel = function() {
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
                    $(form).find('select').val('').trigger('change'); // Reset Select2
                    modal.hide(); // Hide modal				
                } else if (result.dismiss === 'cancel') {
                    Swal.fire({
                        text: "Form của bạn chưa bị hủy!.",
                        icon: "error",
                        buttonsStyling: false,
                        confirmButtonText: "Ok, đã hiểu!",
                        customClass: {
                            confirmButton: "btn btn-primary",
                        }
                    });
                }
            });
        });
    }

    // Close button handler
    var handleClose = function() {
        // Handle close button
        closeButton.addEventListener('click', function(e){
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
                    $(form).find('select').val('').trigger('change'); // Reset Select2
                    modal.hide(); // Hide modal				
                } else if (result.dismiss === 'cancel') {
                    Swal.fire({
                        text: "Form của bạn chưa bị hủy!.",
                        icon: "error",
                        buttonsStyling: false,
                        confirmButtonText: "Ok, đã hiểu!",
                        customClass: {
                            confirmButton: "btn btn-primary",
                        }
                    });
                }
            });
        })
    }

    return {
        // Public functions
        init: function () {
            // Elements
            modalEl = document.querySelector('#kt_modal_export_categories');

            if (!modalEl) {
                return;
            }

            modal = new bootstrap.Modal(modalEl);

            form = document.querySelector('#kt_modal_export_categories_form');
            submitButton = form.querySelector('[data-kt-categories-export-modal-action="submit"]');
            cancelButton = form.querySelector('[data-kt-categories-export-modal-action="cancel"]');
            closeButton = modalEl.querySelector('[data-kt-categories-export-modal-action="close"]');

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
    KTModalExportCategories.init();
});
