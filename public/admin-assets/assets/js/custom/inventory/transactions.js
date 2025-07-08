"use strict";

// Class definition
var KTInventoryTransactions = function () {
    // Shared variables
    var table = document.getElementById('kt_transactions_table');
    var datatable;
    var filterForm;
    var exportModal;
    var detailModal;

    // Private functions
    var initTransactionTable = function () {
        // Init datatable
        datatable = $(table).DataTable({
            "responsive": true,
            "searchDelay": 500,
            "processing": true,
            "serverSide": true,
            "order": [[0, 'desc']], // Order by date desc
            "stateSave": false,
            "ajax": {
                "url": window.location.origin + "/admin/inventory/transactions/ajax",
                "type": "GET",
                "data": function(d) {
                    // Add filter parameters
                    d.transaction_type = $('[data-kt-transaction-table-filter="transaction_type"]').val();
                    d.warehouse_id = $('[data-kt-transaction-table-filter="warehouse"]').val();
                    d.supplier_id = $('[data-kt-transaction-table-filter="supplier"]').val();
                    d.date_from = $('[data-kt-transaction-table-filter="date_from"]').val();
                    d.date_to = $('[data-kt-transaction-table-filter="date_to"]').val();
                    return d;
                },
                "error": function(xhr, error, code) {
                    console.error('DataTables AJAX error:', error);
                    console.error('Response:', xhr.responseText);
                    console.error('Status code:', xhr.status);
                }
            },
            "columns": [
                { data: 'created_at', name: 'created_at' },
                { data: 'reference_number', name: 'reference_number' },
                { data: null, name: 'product_info', orderable: false },
                { data: 'transaction_type', name: 'transaction_type' },
                { data: 'warehouse_name', name: 'warehouse_name' },
                { data: 'supplier_name', name: 'supplier_name' },
                { data: 'quantity', name: 'quantity', className: 'text-center' },
                { data: 'total_value', name: 'total_value', className: 'text-end' },
                { data: 'created_by', name: 'created_by' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end' }
            ],
            "columnDefs": [
                {
                    "targets": 0, // Date column
                    "render": function (data, type, full, meta) {
                        if (type === 'display') {
                            return moment(data).format('DD/MM/YYYY HH:mm');
                        }
                        return data;
                    }
                },
                {
                    "targets": 1, // Reference number
                    "render": function (data, type, full, meta) {
                        return data || '<span class="text-muted">-</span>';
                    }
                },
                {
                    "targets": 2, // Product info
                    "render": function (data, type, full, meta) {
                        return `
                            <div class="d-flex flex-column">
                                <span class="text-gray-800 fw-bold">${full.product_name || 'N/A'}</span>
                                <span class="text-muted fs-7">${full.product_sku || 'N/A'}</span>
                            </div>
                        `;
                    }
                },
                {
                    "targets": 3, // Transaction type
                    "render": function (data, type, full, meta) {
                        const badges = {
                            'import': '<span class="badge badge-light-success fs-7 fw-bold">Nhập Hàng</span>',
                            'export': '<span class="badge badge-light-danger fs-7 fw-bold">Xuất Hàng</span>',
                            'adjustment': '<span class="badge badge-light-warning fs-7 fw-bold">Điều Chỉnh</span>',
                            'transfer': '<span class="badge badge-light-info fs-7 fw-bold">Chuyển Kho</span>',
                            'return': '<span class="badge badge-light-primary fs-7 fw-bold">Trả Hàng</span>',
                            'damage': '<span class="badge badge-light-dark fs-7 fw-bold">Hàng Hỏng</span>',
                            'initial': '<span class="badge badge-light-secondary fs-7 fw-bold">Tồn Đầu</span>',
                            'sale': '<span class="badge badge-light-info fs-7 fw-bold">Bán Hàng</span>'
                        };
                        return badges[data] || `<span class="badge badge-light-secondary fs-7 fw-bold">${data}</span>`;
                    }
                },
                {
                    "targets": 4, // Warehouse
                    "render": function (data, type, full, meta) {
                        return data || '<span class="text-muted">-</span>';
                    }
                },
                {
                    "targets": 5, // Supplier
                    "render": function (data, type, full, meta) {
                        return data || '<span class="text-muted">-</span>';
                    }
                },
                {
                    "targets": 6, // Quantity
                    "render": function (data, type, full, meta) {
                        const quantity = parseInt(data) || 0;
                        const color = quantity > 0 ? 'text-success' : (quantity < 0 ? 'text-danger' : 'text-muted');
                        const sign = quantity > 0 ? '+' : '';
                        return `<span class="fw-bold ${color}">${sign}${quantity.toLocaleString('vi-VN')}</span>`;
                    }
                },
                {
                    "targets": 7, // Total value
                    "render": function (data, type, full, meta) {
                        if (data && parseFloat(data) > 0) {
                            return new Intl.NumberFormat('vi-VN', {
                                style: 'currency',
                                currency: 'VND'
                            }).format(data);
                        }
                        return '<span class="text-muted">-</span>';
                    }
                },
                {
                    "targets": 8, // Created by
                    "render": function (data, type, full, meta) {
                        return data || '<span class="text-muted">Hệ thống</span>';
                    }
                },
                {
                    "targets": 9, // Actions
                    "render": function (data, type, full, meta) {
                        return `
                            <div class="d-flex justify-content-end flex-shrink-0">
                                <button class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1" 
                                        onclick="viewTransactionDetail(${full.id})" title="Xem chi tiết">
                                    <span class="svg-icon svg-icon-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                            <path d="M17.5 11H6.5C4 11 2 9 2 6.5C2 4 4 2 6.5 2H17.5C20 2 22 4 22 6.5C22 9 20 11 17.5 11ZM15 6.5C15 7.9 16.1 9 17.5 9C18.9 9 20 7.9 20 6.5C20 5.1 18.9 4 17.5 4C16.1 4 15 5.1 15 6.5Z" fill="black"/>
                                            <path opacity="0.3" d="M17.5 22H6.5C4 22 2 20 2 17.5C2 15 4 13 6.5 13H17.5C20 13 22 15 22 17.5C22 20 20 22 17.5 22ZM4 17.5C4 18.9 5.1 20 6.5 20C7.9 20 9 18.9 9 17.5C9 16.1 7.9 15 6.5 15C5.1 15 4 16.1 4 17.5Z" fill="black"/>
                                        </svg>
                                    </span>
                                </button>
                                ${full.can_edit ? `
                                <button class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm" 
                                        onclick="editTransaction(${full.id})" title="Chỉnh sửa">
                                    <span class="svg-icon svg-icon-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                            <path opacity="0.3" d="M21.4 8.35303L19.241 10.511L13.485 4.755L15.643 2.59595C16.0248 2.21423 16.5426 1.99988 17.0825 1.99988C17.6224 1.99988 18.1402 2.21423 18.522 2.59595L21.4 5.474C21.7817 5.85581 21.9962 6.37355 21.9962 6.91345C21.9962 7.45335 21.7817 7.97122 21.4 8.35303ZM3.68699 21.932L9.88699 19.865L4.13099 14.109L2.06399 20.309C1.98815 20.5354 1.97703 20.7787 2.03189 21.0111C2.08674 21.2436 2.2054 21.4561 2.37449 21.6248C2.54359 21.7934 2.75641 21.9115 2.989 21.9658C3.22158 22.0201 3.4647 22.0084 3.69099 21.932H3.68699Z" fill="black"/>
                                            <path d="M5.574 21.3L3.692 21.928C3.46591 22.0032 3.22334 22.0141 2.99144 21.9594C2.75954 21.9046 2.54744 21.7864 2.3789 21.6179C2.21036 21.4495 2.09202 21.2375 2.03711 21.0056C1.9822 20.7737 1.99289 20.5312 2.06799 20.3051L2.696 18.422L5.574 21.3Z" fill="black"/>
                                        </svg>
                                    </span>
                                </button>
                                ` : ''}
                            </div>
                        `;
                    }
                }
            ],
            "language": {
                "lengthMenu": "Hiển thị _MENU_ giao dịch",
                "zeroRecords": "Không tìm thấy giao dịch nào",
                "info": "Hiển thị _START_ đến _END_ của _TOTAL_ giao dịch",
                "infoEmpty": "Hiển thị 0 đến 0 của 0 giao dịch",
                "infoFiltered": "(lọc từ _MAX_ tổng số giao dịch)",
                "search": "Tìm kiếm:",
                "paginate": {
                    "first": "Đầu",
                    "last": "Cuối",
                    "next": "Tiếp",
                    "previous": "Trước"
                },
                "processing": "Đang xử lý..."
            }
        });

        // Re-init functions on every table re-draw
        datatable.on('draw', function () {
            KTMenu.createInstances();
        });
    }

    // Search Datatable
    var handleSearchDatatable = function () {
        const filterSearch = document.querySelector('[data-kt-transaction-table-filter="search"]');
        if (filterSearch) {
            filterSearch.addEventListener('keyup', function (e) {
                datatable.search(e.target.value).draw();
            });
        }
    }

    // Filter functions
    var handleFilterDatatable = function () {
        const filterButton = document.querySelector('[data-kt-transaction-table-filter="filter"]');
        const resetButton = document.querySelector('[data-kt-transaction-table-filter="reset"]');

        if (filterButton) {
            filterButton.addEventListener('click', function () {
                datatable.ajax.reload();
            });
        }

        if (resetButton) {
            resetButton.addEventListener('click', function () {
                // Reset all filter inputs
                $('[data-kt-transaction-table-filter="form"] select').val('').trigger('change');
                $('[data-kt-transaction-table-filter="form"] input').val('');
                
                // Reload table
                datatable.ajax.reload();
            });
        }
    }

    // Load suppliers for filter
    var loadSuppliers = function() {
        $.ajax({
            url: window.location.origin + '/admin/supplier/active',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    let html = '<option value="">Chọn nhà cung cấp</option>';
                    response.data.forEach(function(supplier) {
                        const displayName = supplier.display_name || supplier.name;
                        html += `<option value="${supplier.id}">${displayName}</option>`;
                    });
                    $('[data-kt-transaction-table-filter="supplier"]').html(html);
                }
            },
            error: function() {
                console.log('Failed to load suppliers for filter');
            }
        });
    }

    // Initialize modals
    var initModals = function() {
        exportModal = new bootstrap.Modal(document.getElementById('export-modal'));
        detailModal = new bootstrap.Modal(document.getElementById('transaction-detail-modal'));
    }

    // Load statistics data
    var loadStatistics = function() {
        $.ajax({
            url: window.location.origin + '/admin/inventory/transactions/statistics',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    const stats = response.data;
                    $('#today-imports').text(stats.today_imports || 0);
                    $('#today-exports').text(stats.today_exports || 0);
                    $('#today-adjustments').text(stats.today_adjustments || 0);
                    $('#total-transactions').text(stats.total_transactions || 0);
                } else {
                    console.error('Statistics API returned error:', response.message);
                    // Set default values on error
                    $('#today-imports').text('0');
                    $('#today-exports').text('0');
                    $('#today-adjustments').text('0');
                    $('#total-transactions').text('0');
                }
            },
            error: function(xhr, status, error) {
                console.error('Failed to load statistics:', error);
                console.error('Response:', xhr.responseText);
                // Set default values on error
                $('#today-imports').text('0');
                $('#today-exports').text('0');
                $('#today-adjustments').text('0');
                $('#total-transactions').text('0');
            }
        });
    }

    // Public methods
    return {
        init: function () {
            table = document.querySelector('#kt_transactions_table');

            if (!table) {
                return;
            }

            initTransactionTable();
            initModals();
            handleSearchDatatable();
            handleFilterDatatable();
            loadSuppliers();
            loadStatistics();
        },

        // Public method to reload table
        reload: function() {
            if (datatable) {
                datatable.ajax.reload();
            }
        },

        // Public method to get datatable instance
        getDataTable: function() {
            return datatable;
        }
    }
}();

// On document ready
KTUtil.onDOMContentLoaded(function () {
    KTInventoryTransactions.init();
});

// Global functions for inline onclick events
window.viewTransactionDetail = function(transactionId) {
    $.ajax({
        url: window.location.origin + `/admin/inventory/transactions/${transactionId}`,
        method: 'GET',
        success: function(response) {
            $('#transaction-detail-content').html(response);
            $('#transaction-detail-modal').modal('show');
        },
        error: function() {
            Swal.fire({
                title: 'Lỗi!',
                text: 'Không thể tải chi tiết giao dịch',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
    });
};

window.editTransaction = function(transactionId) {
    // Placeholder for edit functionality
    Swal.fire({
        title: 'Thông báo',
        text: 'Chức năng chỉnh sửa giao dịch đang được phát triển',
        icon: 'info',
        confirmButtonText: 'OK'
    });
};

window.exportTransactions = function() {
    const form = document.getElementById('export-form');
    const formData = new FormData(form);
    
    // Show loading
    Swal.fire({
        title: 'Đang xuất dữ liệu...',
        text: 'Vui lòng đợi trong giây lát',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Create download request
    $.ajax({
        url: window.location.origin + '/admin/inventory/export-transactions',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        xhrFields: {
            responseType: 'blob'
        },
        success: function(data, status, xhr) {
            // Create download link
            const blob = new Blob([data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
            const url = window.URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `transactions_${new Date().getTime()}.xlsx`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            window.URL.revokeObjectURL(url);
            
            // Close modal and show success
            $('#export-modal').modal('hide');
            Swal.fire({
                title: 'Thành công!',
                text: 'Đã xuất báo cáo thành công',
                icon: 'success',
                confirmButtonText: 'OK'
            });
        },
        error: function() {
            Swal.fire({
                title: 'Lỗi!',
                text: 'Không thể xuất báo cáo',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
    });
};
