"use strict";

// Class definition
var KTProductsExport = function () {
    // Shared variables
    var modal;
    var form;
    var submitButton;
    var cancelButton;

    // Init form inputs
    var initForm = function () {
        // Submit button handler
        submitButton.addEventListener('click', function (e) {
            // Prevent default button action
            e.preventDefault();

            // Show loading indication
            submitButton.setAttribute('data-kt-indicator', 'on');

            // Disable button to avoid multiple click
            submitButton.disabled = true;

            // Get form data
            var formData = new FormData(form);
            var status = formData.get('status');
            var format = formData.get('format');

            // Simulate export process (replace with actual export logic)
            setTimeout(function() {
                // Remove loading indication
                submitButton.removeAttribute('data-kt-indicator');

                // Enable button
                submitButton.disabled = false;

                // Show success message
                Swal.fire({
                    text: "Products have been successfully exported!",
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

                        // Reset form
                        form.reset();

                        // Here you would typically trigger the actual download
                        // For now, we'll just show a success message
                        console.log('Exporting products with status:', status, 'in format:', format);
                    }
                });
            }, 2000);
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
                    form.reset(); // Reset form
                    modal.hide(); // Hide modal
                }
            });
        });
    }

    // Public methods
    return {
        init: function () {
            // Elements
            modal = new bootstrap.Modal(document.querySelector('#kt_modal_export_products'));
            form = document.querySelector('#kt_modal_export_products_form');
            submitButton = form.querySelector('[data-kt-users-modal-action="submit"]');
            cancelButton = form.querySelector('[data-kt-users-modal-action="cancel"]');

            if (form) {
                initForm();
            }
        }
    };
}();

// On document ready
KTUtil.onDOMContentLoaded(function () {
    KTProductsExport.init();
});
