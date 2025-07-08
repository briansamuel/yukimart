"use strict";

// Class definition
var KTProductEdit = function () {
    // Shared variables
    var validator;
    var form;
    var submitButton;
    var cancelButton;

    // Init form inputs
    var initForm = function () {
        // Init form validation rules. For more info check the FormValidation plugin's official documentation:https://formvalidation.io/
        validator = FormValidation.formValidation(
            form,
            {
                fields: {
                    'product_name': {
                        validators: {
                            notEmpty: {
                                message: 'Product name is required'
                            }
                        }
                    },
                    'sku': {
                        validators: {
                            notEmpty: {
                                message: 'SKU is required'
                            }
                        }
                    },
                    'cost_price': {
                        validators: {
                            notEmpty: {
                                message: 'Cost price is required'
                            },
                            numeric: {
                                message: 'Cost price must be a number'
                            }
                        }
                    },
                    'sale_price': {
                        validators: {
                            notEmpty: {
                                message: 'Sale price is required'
                            },
                            numeric: {
                                message: 'Sale price must be a number'
                            }
                        }
                    },
                    'product_description': {
                        validators: {
                            notEmpty: {
                                message: 'Product description is required'
                            }
                        }
                    },
                    'product_content': {
                        validators: {
                            notEmpty: {
                                message: 'Product content is required'
                            }
                        }
                    },
                    'product_status': {
                        validators: {
                            notEmpty: {
                                message: 'Product status is required'
                            }
                        }
                    },
                    'product_type': {
                        validators: {
                            notEmpty: {
                                message: 'Product type is required'
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
        submitButton.addEventListener('click', function (e) {
            // Prevent default button action
            e.preventDefault();

            // Validate form before submit
            if (validator) {
                validator.validate().then(function (status) {
                    console.log('validated!');

                    if (status == 'Valid') {
                        // Show simple loading
                        var originalText = submitButton.innerHTML;
                        submitButton.innerHTML = 'Đang cập nhật... <span class="spinner-border spinner-border-sm ms-2"></span>';

                        // Disable button to avoid multiple click
                        submitButton.disabled = true;

                        // Sync TinyMCE content
                        if (typeof tinymce !== 'undefined') {
                            tinymce.triggerSave();
                        }

                        // Submit form
                        var formData = new FormData(form);

                        fetch(form.action || window.location.href, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            // Remove loading indication
                            submitButton.innerHTML = originalText;

                            // Enable button
                            submitButton.disabled = false;

                            if (data.success) {
                                // Show success message
                                Swal.fire({
                                    text: data.message || "Product has been successfully updated!",
                                    icon: "success",
                                    buttonsStyling: false,
                                    confirmButtonText: "Ok, got it!",
                                    customClass: {
                                        confirmButton: "btn btn-primary"
                                    }
                                }).then(function (result) {
                                    if (result.isConfirmed) {
                                        // Redirect to products list or specified URL
                                        if (data.redirect_url) {
                                            window.location.href = data.redirect_url;
                                        } else {
                                            // Stay on the same page for edit
                                            window.location.reload();
                                        }
                                    }
                                });
                            } else {
                                // Prepare detailed error message
                                let errorMessage = data.message || "Sorry, looks like there are some errors detected, please try again.";

                                if (data.errors && data.errors.length > 0) {
                                    errorMessage += "\n\nDetails:\n• " + data.errors.join("\n• ");
                                }

                                // Show error message with details
                                Swal.fire({
                                    title: "Validation Error",
                                    text: errorMessage,
                                    icon: "error",
                                    buttonsStyling: false,
                                    confirmButtonText: "Ok, got it!",
                                    customClass: {
                                        confirmButton: "btn btn-primary"
                                    },
                                    width: '600px'
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);

                            // Remove loading indication
                            submitButton.innerHTML = originalText;

                            // Enable button
                            submitButton.disabled = false;

                            // Show error message
                            Swal.fire({
                                title: "Network Error",
                                text: "A network error occurred. Please check your connection and try again.\n\nError details: " + error.message,
                                icon: "error",
                                buttonsStyling: false,
                                confirmButtonText: "Ok, got it!",
                                customClass: {
                                    confirmButton: "btn btn-primary"
                                },
                                width: '600px'
                            });
                        });
                    }
                });
            }
        });

        // Cancel button handler
        if (cancelButton) {
            cancelButton.addEventListener('click', function (e) {
                e.preventDefault();

                Swal.fire({
                    text: "Are you sure you would like to cancel?",
                    icon: "warning",
                    showCancelButton: true,
                    buttonsStyling: false,
                    confirmButtonText: "Yes, cancel it!",
                    cancelButtonText: "No, return",
                    customClass: {
                        confirmButton: "btn btn-primary",
                        cancelButton: "btn btn-active-light"
                    }
                }).then(function (result) {
                    if (result.value) {
                        window.location.href = '/admin/products'; // Redirect to products list
                    }
                });
            });
        }
    }

    // Auto-generate slug from product name
    var initSlugGeneration = function() {
        const productNameInput = document.querySelector('#product_name');
        const productSlugInput = document.querySelector('#product_slug');

        if (productNameInput && productSlugInput) {
            productNameInput.addEventListener('input', function() {
                if (!productSlugInput.value || productSlugInput.dataset.manual !== 'true') {
                    const slug = this.value
                        .toLowerCase()
                        .replace(/[^a-z0-9\s-]/g, '') // Remove special characters
                        .replace(/\s+/g, '-') // Replace spaces with hyphens
                        .replace(/-+/g, '-') // Replace multiple hyphens with single
                        .trim('-'); // Remove leading/trailing hyphens
                    
                    productSlugInput.value = slug;
                }
            });

            // Mark slug as manually edited if user types in it
            productSlugInput.addEventListener('input', function() {
                this.dataset.manual = 'true';
            });
        }
    }

    // Public methods
    return {
        init: function () {
            // Elements
            form = document.querySelector('#kt_edit_product_form');
            submitButton = document.querySelector('[data-kt-product-action="submit"]');
            cancelButton = document.querySelector('[data-kt-product-action="cancel"]');

            if (form) {
                initForm();
                initSlugGeneration();
            }
        }
    };
}();

// On document ready
KTUtil.onDOMContentLoaded(function () {
    KTProductEdit.init();
});
