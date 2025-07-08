"use strict";

// Class definition
var KTOrderDetail = function () {
    // Private variables
    var orderId = null;
    var recordPaymentModal = null;
    var updateStatusModal = null;
    var printOrderModal = null;
    var cancelOrderModal = null;

    // Private functions
    var initOrderId = function() {
        // Extract order ID from URL or data attribute
        var urlParts = window.location.pathname.split('/');
        orderId = urlParts[urlParts.length - 1];
        console.log('Order ID:', orderId);
    };

    var initModals = function() {
        // Initialize record payment modal
        recordPaymentModal = new bootstrap.Modal(document.getElementById('kt_modal_record_payment'));
        
        // Initialize update status modal
        updateStatusModal = new bootstrap.Modal(document.getElementById('kt_modal_update_status'));
        
        // Initialize print order modal
        printOrderModal = new bootstrap.Modal(document.getElementById('kt_modal_print_order'));
        
        // Initialize cancel order modal
        cancelOrderModal = new bootstrap.Modal(document.getElementById('kt_modal_cancel_order'));
    };

    var initRecordPaymentForm = function() {
        const form = document.getElementById('kt_modal_record_payment_form');
        const submitButton = form.querySelector('[data-kt-payment-modal-action="submit"]');

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
            formData.append('order_id', orderId);

            // Submit via AJAX
            fetch(`/admin/order/${orderId}/record-payment`, {
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
                        text: data.message || "Payment recorded successfully!",
                        icon: "success",
                        buttonsStyling: false,
                        confirmButtonText: "Ok, got it!",
                        customClass: {
                            confirmButton: "btn btn-primary"
                        }
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            recordPaymentModal.hide();
                            // Reload page to show updated data
                            window.location.reload();
                        }
                    });
                } else {
                    // Show error message
                    Swal.fire({
                        text: data.message || "An error occurred while recording payment.",
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
                    text: "An error occurred while recording payment.",
                    icon: "error",
                    buttonsStyling: false,
                    confirmButtonText: "Ok, got it!",
                    customClass: {
                        confirmButton: "btn btn-primary"
                    }
                });
            });
        });
    };

    var initUpdateStatusForm = function() {
        const form = document.getElementById('kt_modal_update_status_form');
        const submitButton = form.querySelector('[data-kt-status-modal-action="submit"]');

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
            formData.append('order_id', orderId);

            // Submit via AJAX
            fetch(`/admin/order/${orderId}/update-status`, {
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
                        text: data.message || "Status updated successfully!",
                        icon: "success",
                        buttonsStyling: false,
                        confirmButtonText: "Ok, got it!",
                        customClass: {
                            confirmButton: "btn btn-primary"
                        }
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            updateStatusModal.hide();
                            // Reload page to show updated data
                            window.location.reload();
                        }
                    });
                } else {
                    // Show error message
                    Swal.fire({
                        text: data.message || "An error occurred while updating status.",
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
                    text: "An error occurred while updating status.",
                    icon: "error",
                    buttonsStyling: false,
                    confirmButtonText: "Ok, got it!",
                    customClass: {
                        confirmButton: "btn btn-primary"
                    }
                });
            });
        });
    };

    var initPrintActions = function() {
        // Print invoice
        const printInvoiceBtn = document.getElementById('btn_print_invoice');
        if (printInvoiceBtn) {
            printInvoiceBtn.addEventListener('click', function() {
                window.open(`/admin/order/${orderId}/print/invoice`, '_blank');
                printOrderModal.hide();
            });
        }

        // Print receipt
        const printReceiptBtn = document.getElementById('btn_print_receipt');
        if (printReceiptBtn) {
            printReceiptBtn.addEventListener('click', function() {
                window.open(`/admin/order/${orderId}/print/receipt`, '_blank');
                printOrderModal.hide();
            });
        }

        // Print shipping label
        const printShippingBtn = document.getElementById('btn_print_shipping_label');
        if (printShippingBtn) {
            printShippingBtn.addEventListener('click', function() {
                window.open(`/admin/order/${orderId}/print/shipping-label`, '_blank');
                printOrderModal.hide();
            });
        }

        // Export PDF
        const exportPdfBtn = document.getElementById('btn_export_pdf');
        if (exportPdfBtn) {
            exportPdfBtn.addEventListener('click', function() {
                window.open(`/admin/order/${orderId}/export/pdf`, '_blank');
                printOrderModal.hide();
            });
        }
    };

    var initCancelOrderForm = function() {
        const form = document.getElementById('kt_modal_cancel_order_form');
        const cancelButton = document.getElementById('btn_cancel_order');
        
        if (form && cancelButton) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();

                // Validate form
                if (!form.checkValidity()) {
                    form.reportValidity();
                    return;
                }

                // Show loading indication
                cancelButton.setAttribute('data-kt-indicator', 'on');
                cancelButton.disabled = true;

                // Prepare form data
                const formData = new FormData(form);
                formData.append('order_id', orderId);

                // Submit via AJAX
                fetch(`/admin/order/${orderId}/cancel`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    // Remove loading indication
                    cancelButton.removeAttribute('data-kt-indicator');
                    cancelButton.disabled = false;

                    if (data.success) {
                        // Show success message
                        Swal.fire({
                            text: data.message || "Order cancelled successfully!",
                            icon: "success",
                            buttonsStyling: false,
                            confirmButtonText: "Ok, got it!",
                            customClass: {
                                confirmButton: "btn btn-primary"
                            }
                        }).then(function (result) {
                            if (result.isConfirmed) {
                                cancelOrderModal.hide();
                                // Reload page to show updated data
                                window.location.reload();
                            }
                        });
                    } else {
                        // Show error message
                        Swal.fire({
                            text: data.message || "An error occurred while cancelling order.",
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
                    cancelButton.removeAttribute('data-kt-indicator');
                    cancelButton.disabled = false;

                    console.error('Error:', error);
                    Swal.fire({
                        text: "An error occurred while cancelling order.",
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
        // Initialize Select2 for payment method
        const paymentMethodSelect = document.querySelector('[name="payment_method"]');
        if (paymentMethodSelect) {
            $(paymentMethodSelect).select2({
                dropdownParent: $('#kt_modal_record_payment')
            });
        }

        // Initialize Select2 for order status
        const orderStatusSelect = document.querySelector('[name="order_status"]');
        if (orderStatusSelect) {
            $(orderStatusSelect).select2({
                dropdownParent: $('#kt_modal_update_status')
            });
        }

        // Initialize Select2 for delivery status
        const deliveryStatusSelect = document.querySelector('[name="delivery_status"]');
        if (deliveryStatusSelect) {
            $(deliveryStatusSelect).select2({
                dropdownParent: $('#kt_modal_update_status')
            });
        }

        // Initialize Select2 for cancellation reason
        const cancellationReasonSelect = document.querySelector('[name="cancellation_reason"]');
        if (cancellationReasonSelect) {
            $(cancellationReasonSelect).select2({
                dropdownParent: $('#kt_modal_cancel_order')
            });
        }
    };

    // Public methods
    return {
        init: function () {
            initOrderId();
            initModals();
            initRecordPaymentForm();
            initUpdateStatusForm();
            initPrintActions();
            initCancelOrderForm();
            initSelect2();
        }
    };
}();

// On document ready
KTUtil.onDOMContentLoaded(function () {
    KTOrderDetail.init();
});
