"use strict";

// Class definition
var KTProductDetail = function () {
    // Private variables
    var productId = null;
    var adjustStockModal = null;
    var duplicateModal = null;
    var deleteModal = null;

    // Private functions
    var initProductId = function() {
        // Extract product ID from URL or data attribute
        var urlParts = window.location.pathname.split('/');
        productId = urlParts[urlParts.length - 1];
        console.log('Product ID:', productId);
    };

    var initModals = function() {
        // Initialize adjust stock modal
        adjustStockModal = new bootstrap.Modal(document.getElementById('kt_modal_adjust_stock'));
        
        // Initialize duplicate modal
        duplicateModal = new bootstrap.Modal(document.getElementById('kt_modal_duplicate_product'));
        
        // Initialize delete modal
        deleteModal = new bootstrap.Modal(document.getElementById('kt_modal_delete_product'));
    };

    var initAdjustStockForm = function() {
        const form = document.getElementById('kt_modal_adjust_stock_form');
        const submitButton = form.querySelector('[data-kt-users-modal-action="submit"]');
        const cancelButton = form.querySelector('[data-kt-users-modal-action="cancel"]');

        // Submit button handler
        submitButton.addEventListener('click', function (e) {
            e.preventDefault();

            // Validate form
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            // Show loading indication
            submitButton.setAttribute('data-kt-indicator', 'on');
            submitButton.disabled = true;

            // Prepare form data
            const formData = new FormData(form);
            formData.append('product_id', productId);

            // Submit via AJAX
            fetch(`/admin/products/${productId}/adjust-stock`, {
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
                submitButton.disabled = false;

                if (data.success) {
                    // Show success message
                    Swal.fire({
                        text: data.message || "Stock adjusted successfully!",
                        icon: "success",
                        buttonsStyling: false,
                        confirmButtonText: "Ok, got it!",
                        customClass: {
                            confirmButton: "btn btn-primary"
                        }
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            adjustStockModal.hide();
                            // Reload page to show updated data
                            window.location.reload();
                        }
                    });
                } else {
                    // Show error message
                    Swal.fire({
                        text: data.message || "An error occurred while adjusting stock.",
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
                // Remove loading indication
                submitButton.removeAttribute('data-kt-indicator');
                submitButton.disabled = false;

                console.error('Error:', error);
                Swal.fire({
                    text: "An error occurred while adjusting stock.",
                    icon: "error",
                    buttonsStyling: false,
                    confirmButtonText: "Ok, got it!",
                    customClass: {
                        confirmButton: "btn btn-primary"
                    }
                });
            });
        });

        // Cancel button handler
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
                    form.reset();
                    adjustStockModal.hide();
                }
            });
        });
    };

    var initDuplicateProduct = function() {
        const duplicateButton = document.getElementById('btn_duplicate_product');
        
        if (duplicateButton) {
            duplicateButton.addEventListener('click', function (e) {
                e.preventDefault();

                // Show loading indication
                duplicateButton.setAttribute('data-kt-indicator', 'on');
                duplicateButton.disabled = true;

                // Submit via AJAX
                fetch(`/admin/products/${productId}/duplicate`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    // Remove loading indication
                    duplicateButton.removeAttribute('data-kt-indicator');
                    duplicateButton.disabled = false;

                    if (data.success) {
                        // Show success message
                        Swal.fire({
                            text: data.message || "Product duplicated successfully!",
                            icon: "success",
                            buttonsStyling: false,
                            confirmButtonText: "Ok, got it!",
                            customClass: {
                                confirmButton: "btn btn-primary"
                            }
                        }).then(function (result) {
                            if (result.isConfirmed) {
                                duplicateModal.hide();
                                // Redirect to the new product
                                if (data.redirect_url) {
                                    window.location.href = data.redirect_url;
                                } else {
                                    window.location.reload();
                                }
                            }
                        });
                    } else {
                        // Show error message
                        Swal.fire({
                            text: data.message || "An error occurred while duplicating product.",
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
                    // Remove loading indication
                    duplicateButton.removeAttribute('data-kt-indicator');
                    duplicateButton.disabled = false;

                    console.error('Error:', error);
                    Swal.fire({
                        text: "An error occurred while duplicating product.",
                        icon: "error",
                        buttonsStyling: false,
                        confirmButtonText: "Ok, got it!",
                        customClass: {
                            confirmButton: "btn btn-primary"
                        }
                    });
                });
            });
        }
    };

    var initDeleteProduct = function() {
        const form = document.getElementById('kt_modal_delete_product_form');
        const deleteButton = document.getElementById('btn_delete_product');
        const productName = document.querySelector('[data-product-name]')?.getAttribute('data-product-name') || '';
        
        if (form && deleteButton) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();

                const confirmName = form.querySelector('[name="confirm_name"]').value;
                
                // Validate product name confirmation
                if (confirmName !== productName) {
                    Swal.fire({
                        text: "Product name confirmation does not match. Please type the exact product name.",
                        icon: "error",
                        buttonsStyling: false,
                        confirmButtonText: "Ok, got it!",
                        customClass: {
                            confirmButton: "btn btn-primary"
                        }
                    });
                    return;
                }

                // Show loading indication
                deleteButton.setAttribute('data-kt-indicator', 'on');
                deleteButton.disabled = true;

                // Submit via AJAX
                fetch(`/admin/products/delete/${productId}`, {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    // Remove loading indication
                    deleteButton.removeAttribute('data-kt-indicator');
                    deleteButton.disabled = false;

                    if (data.success) {
                        // Show success message
                        Swal.fire({
                            text: data.message || "Product deleted successfully!",
                            icon: "success",
                            buttonsStyling: false,
                            confirmButtonText: "Ok, got it!",
                            customClass: {
                                confirmButton: "btn btn-primary"
                            }
                        }).then(function (result) {
                            if (result.isConfirmed) {
                                // Redirect to products list
                                window.location.href = '/admin/products';
                            }
                        });
                    } else {
                        // Show error message
                        Swal.fire({
                            text: data.message || "An error occurred while deleting product.",
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
                    // Remove loading indication
                    deleteButton.removeAttribute('data-kt-indicator');
                    deleteButton.disabled = false;

                    console.error('Error:', error);
                    Swal.fire({
                        text: "An error occurred while deleting product.",
                        icon: "error",
                        buttonsStyling: false,
                        confirmButtonText: "Ok, got it!",
                        customClass: {
                            confirmButton: "btn btn-primary"
                        }
                    });
                });
            });
        }
    };

    var initSelect2 = function() {
        // Initialize Select2 for adjustment type
        const adjustmentTypeSelect = document.querySelector('[name="adjustment_type"]');
        if (adjustmentTypeSelect) {
            $(adjustmentTypeSelect).select2({
                dropdownParent: $('#kt_modal_adjust_stock')
            });
        }
    };

    // Public methods
    return {
        init: function () {
            initProductId();
            initModals();
            initAdjustStockForm();
            initDuplicateProduct();
            initDeleteProduct();
            initSelect2();
        }
    };
}();

// On document ready
KTUtil.onDOMContentLoaded(function () {
    KTProductDetail.init();
});
