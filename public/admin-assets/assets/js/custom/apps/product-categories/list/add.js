"use strict";

// Class definition
var KTModalAddCategory = function () {
    var submitButton;
    var cancelButton;
    var closeButton;
    var validator;
    var form;
    var modal;
    var modalEl;

    // Init form inputs
    var initForm = function() {
        // Load parent categories
        loadParentCategories();
        
        // Auto-generate slug from name
        const nameInput = form.querySelector('[name="name"]');
        const slugInput = form.querySelector('[name="slug"]');
        
        nameInput.addEventListener('input', function() {
            if (!slugInput.value || slugInput.dataset.manual !== 'true') {
                slugInput.value = generateSlug(this.value);
            }
        });
        
        slugInput.addEventListener('input', function() {
            this.dataset.manual = 'true';
        });
    }

    // Generate slug from string
    var generateSlug = function(str) {
        return str
            .toLowerCase()
            .trim()
            .replace(/[^\w\s-]/g, '') // remove non-word [a-z0-9_], non-whitespace, non-hyphen characters
            .replace(/[\s_-]+/g, '-') // swap any length of whitespace, underscore, hyphen characters with a single -
            .replace(/^-+|-+$/g, ''); // remove leading, trailing -
    }

    // Load parent categories
    var loadParentCategories = function() {
        const parentSelect = form.querySelector('[name="parent_id"]');
        
        // Clear existing options except the first one
        while (parentSelect.children.length > 1) {
            parentSelect.removeChild(parentSelect.lastChild);
        }
        
        // Load categories via AJAX
        fetch('/admin/product-categories/parent/list')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    data.data.forEach(category => {
                        const option = document.createElement('option');
                        option.value = category.id;
                        option.textContent = category.name;
                        parentSelect.appendChild(option);
                    });
                    
                    // Trigger Select2 update
                    $(parentSelect).trigger('change');
                }
            })
            .catch(error => {
                console.error('Error loading parent categories:', error);
            });
    }

    // Init form validation
    var initValidation = function() {
        // Init form validation rules. For more info check the FormValidation plugin's official documentation:https://formvalidation.io/
        validator = FormValidation.formValidation(
            form,
            {
                fields: {
                    'name': {
                        validators: {
                            notEmpty: {
                                message: 'Tên danh mục là bắt buộc'
                            }
                        }
                    },
                    'slug': {
                        validators: {
                            regexp: {
                                regexp: /^[a-z0-9-]+$/,
                                message: 'Slug chỉ được chứa chữ thường, số và dấu gạch ngang'
                            }
                        }
                    },
                    'is_active': {
                        validators: {
                            notEmpty: {
                                message: 'Vui lòng chọn trạng thái'
                            }
                        }
                    },
                    'sort_order': {
                        validators: {
                            integer: {
                                message: 'Thứ tự phải là số nguyên'
                            },
                            greaterThan: {
                                value: -1,
                                message: 'Thứ tự phải lớn hơn hoặc bằng 0'
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

                        // Create FormData object
                        const formData = new FormData(form);

                        // Submit form via AJAX
                        fetch('/admin/product-categories', {
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
                                    text: "Danh mục đã được tạo thành công!",
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
                                        
                                        // Reload datatable
                                        if (typeof KTProductCategoriesList !== 'undefined') {
                                            $('#kt_table_product_categories').DataTable().ajax.reload();
                                        }
                                    }
                                });
                            } else {
                                // Show error message
                                Swal.fire({
                                    text: data.message || "Có lỗi xảy ra khi tạo danh mục.",
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
                            // Remove loading indication
                            submitButton.removeAttribute('data-kt-indicator');
                            
                            // Enable button
                            submitButton.disabled = false;
                            
                            console.error('Error:', error);
                            
                            // Show error message
                            Swal.fire({
                                text: "Có lỗi xảy ra khi tạo danh mục.",
                                icon: "error",
                                buttonsStyling: false,
                                confirmButtonText: "Ok, đã hiểu!",
                                customClass: {
                                    confirmButton: "btn btn-primary"
                                }
                            });
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
            modalEl = document.querySelector('#kt_modal_add_category');

            if (!modalEl) {
                return;
            }

            modal = new bootstrap.Modal(modalEl);

            form = document.querySelector('#kt_modal_add_category_form');
            submitButton = document.querySelector('#kt_modal_add_category_submit');
            cancelButton = document.querySelector('#kt_modal_add_category_cancel');
            closeButton = document.querySelector('#kt_modal_add_category_close');

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
    KTModalAddCategory.init();
});
