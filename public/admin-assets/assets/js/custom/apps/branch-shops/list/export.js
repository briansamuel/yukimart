"use strict";

var KTModalExportBranchShops = function () {
    const modalEl = document.getElementById('kt_modal_export_branch_shops');
    const modal = new bootstrap.Modal(modalEl);

    // Init form inputs
    var initForm = function () {
        // Init date range picker
        $("#kt_modal_export_branch_shops_date").daterangepicker({
            buttonClasses: " btn",
            applyClass: "btn-primary",
            cancelClass: "btn-light"
        });
    }

    // Export
    var handleExport = function () {
        const exportButton = document.querySelector('[data-kt-modal-export-action="submit"]');
        exportButton.addEventListener('click', function (e) {
            e.preventDefault();

            // Show loading state
            exportButton.setAttribute('data-kt-indicator', 'on');
            exportButton.disabled = true;

            // Get form data
            const form = document.querySelector('#kt_modal_export_branch_shops_form');
            const formData = new FormData(form);

            // Simulate export process
            setTimeout(function () {
                // Hide loading state
                exportButton.removeAttribute('data-kt-indicator');
                exportButton.disabled = false;

                // Show success message
                Swal.fire({
                    text: "Xuất dữ liệu thành công!",
                    icon: "success",
                    buttonsStyling: false,
                    confirmButtonText: "Ok, đã hiểu!",
                    customClass: {
                        confirmButton: "btn btn-primary"
                    }
                }).then(function () {
                    modal.hide();
                });

            }, 2000);
        });
    }

    // Reset
    var handleReset = function () {
        const resetButton = modalEl.querySelector('[data-kt-modal="close"]');
        resetButton.addEventListener('click', function () {
            // Reset form
            const form = document.querySelector('#kt_modal_export_branch_shops_form');
            form.reset();
        });
    }

    // Public methods
    return {
        init: function () {
            initForm();
            handleExport();
            handleReset();
        }
    }
}();

// On document ready
KTUtil.onDOMContentLoaded(function () {
    KTModalExportBranchShops.init();
});
