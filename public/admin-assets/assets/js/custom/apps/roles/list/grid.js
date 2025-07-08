"use strict";

// Class definition
var KTRolesGrid = function () {
    // Shared variables
    var container;
    var deleteButtons;

    // Private functions
    var initDeleteButtons = function () {
        // Select all delete buttons
        deleteButtons = container.querySelectorAll('[data-kt-roles-grid-filter="delete_row"]');

        deleteButtons.forEach(d => {
            // Delete button on click
            d.addEventListener('click', function (e) {
                e.preventDefault();

                // Select parent card
                const parent = e.target.closest('.col-md-4');

                // Get role name and ID
                const roleName = d.getAttribute('data-role-name');
                const roleId = d.getAttribute('data-role-id');

                // SweetAlert2 pop up --- official docs reference: https://sweetalert2.github.io/
                Swal.fire({
                    text: "Bạn có chắc chắn muốn xóa vai trò " + roleName + "?",
                    icon: "warning",
                    showCancelButton: true,
                    buttonsStyling: false,
                    confirmButtonText: "Có, xóa!",
                    cancelButtonText: "Không, hủy",
                    customClass: {
                        confirmButton: "btn fw-bold btn-danger",
                        cancelButton: "btn fw-bold btn-active-light-primary"
                    }
                }).then(function (result) {
                    if (result.value) {
                        // Delete request
                        fetch(`/admin/roles/${roleId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Content-Type': 'application/json',
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    text: "Bạn đã xóa vai trò " + roleName + "!",
                                    icon: "success",
                                    buttonsStyling: false,
                                    confirmButtonText: "Ok, đã hiểu!",
                                    customClass: {
                                        confirmButton: "btn fw-bold btn-primary",
                                    }
                                }).then(function () {
                                    // Remove card from grid
                                    parent.remove();
                                });
                            } else {
                                Swal.fire({
                                    text: data.message || "Có lỗi xảy ra khi xóa vai trò.",
                                    icon: "error",
                                    buttonsStyling: false,
                                    confirmButtonText: "Ok, đã hiểu!",
                                    customClass: {
                                        confirmButton: "btn fw-bold btn-primary",
                                    }
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire({
                                text: "Có lỗi xảy ra khi xóa vai trò.",
                                icon: "error",
                                buttonsStyling: false,
                                confirmButtonText: "Ok, đã hiểu!",
                                customClass: {
                                    confirmButton: "btn fw-bold btn-primary",
                                }
                            });
                        });
                    } else if (result.dismiss === 'cancel') {
                        Swal.fire({
                            text: roleName + " không bị xóa.",
                            icon: "error",
                            buttonsStyling: false,
                            confirmButtonText: "Ok, đã hiểu!",
                            customClass: {
                                confirmButton: "btn fw-bold btn-primary",
                            }
                        });
                    }
                });
            })
        });
    }

    // Search functionality
    var handleSearch = function () {
        const searchInput = document.querySelector('[data-kt-roles-grid-filter="search"]');
        if (!searchInput) return;

        searchInput.addEventListener('keyup', function (e) {
            const searchTerm = e.target.value.toLowerCase();
            const roleCards = container.querySelectorAll('.col-md-4:not(:last-child)'); // Exclude "Add new" card

            roleCards.forEach(card => {
                const roleName = card.querySelector('h2').textContent.toLowerCase();
                const roleDescription = card.querySelector('.text-muted')?.textContent.toLowerCase() || '';
                
                if (roleName.includes(searchTerm) || roleDescription.includes(searchTerm)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }

    // Filter functionality
    var handleFilter = function () {
        const filterSelect = document.querySelector('[data-kt-roles-grid-filter="status"]');
        if (!filterSelect) return;

        filterSelect.addEventListener('change', function (e) {
            const filterValue = e.target.value;
            const roleCards = container.querySelectorAll('.col-md-4:not(:last-child)'); // Exclude "Add new" card

            roleCards.forEach(card => {
                const statusBadge = card.querySelector('.card-toolbar .badge');
                
                if (filterValue === '' || filterValue === 'all') {
                    card.style.display = '';
                } else if (filterValue === 'active') {
                    if (statusBadge && statusBadge.classList.contains('badge-light-success')) {
                        card.style.display = '';
                    } else {
                        card.style.display = 'none';
                    }
                } else if (filterValue === 'inactive') {
                    if (statusBadge && statusBadge.classList.contains('badge-light-danger')) {
                        card.style.display = '';
                    } else {
                        card.style.display = 'none';
                    }
                }
            });
        });
    }

    // Reset filters
    var handleReset = function () {
        const resetButton = document.querySelector('[data-kt-roles-grid-filter="reset"]');
        if (!resetButton) return;

        resetButton.addEventListener('click', function (e) {
            e.preventDefault();

            // Reset search
            const searchInput = document.querySelector('[data-kt-roles-grid-filter="search"]');
            if (searchInput) {
                searchInput.value = '';
            }

            // Reset filter
            const filterSelect = document.querySelector('[data-kt-roles-grid-filter="status"]');
            if (filterSelect) {
                filterSelect.value = '';
                $(filterSelect).trigger('change');
            }

            // Show all cards
            const roleCards = container.querySelectorAll('.col-md-4:not(:last-child)');
            roleCards.forEach(card => {
                card.style.display = '';
            });
        });
    }

    // Public methods
    return {
        init: function () {
            container = document.querySelector('.row.row-cols-1.row-cols-md-2.row-cols-xl-3');
            
            if (!container) {
                return;
            }

            initDeleteButtons();
            handleSearch();
            handleFilter();
            handleReset();
        }
    }
}();

// On document ready
KTUtil.onDOMContentLoaded(function () {
    KTRolesGrid.init();
});
