/**
 * Quick Order Modals JavaScript
 * Handles modal interactions and events
 */

$(document).ready(function() {
    setupModalEvents();
});

/**
 * Setup modal events
 */
function setupModalEvents() {
    // Discount modal events
    setupDiscountModal();
    
    // Other charges modal events
    setupOtherChargesModal();
    
    // Customer info modal events
    setupCustomerInfoModal();
    
    // Invoice selection modal events
    setupInvoiceSelectionModal();
}

/**
 * Setup discount modal
 */
function setupDiscountModal() {
    // Format discount input
    $('#discountInput').on('input', function() {
        formatCurrencyInput(this);
    });
    
    // Apply discount on Enter
    $('#discountInput').on('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            applyDiscount();
        }
    });
    
    // Discount type change
    $('#discountType').on('change', function() {
        const type = $(this).val();
        const label = type === 'percent' ? '%' : 'VND';
        $('#discountLabel').text(label);
    });
}

/**
 * Setup other charges modal
 */
function setupOtherChargesModal() {
    // Format new charge amount input
    $('#newChargeAmount').on('input', function() {
        formatCurrencyInput(this);
    });
    
    // Add charge on Enter
    $('#newChargeAmount').on('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            addOtherCharge();
        }
    });
    
    // Charge checkbox change
    $(document).on('change', '.charge-checkbox', function() {
        updateTotalOtherCharges();
    });
    
    // Select all charges
    $('#selectAllCharges').on('change', function() {
        toggleAllCharges(this);
    });
}

/**
 * Setup customer info modal
 */
function setupCustomerInfoModal() {
    // Tab switching
    $('#customerInfoTabs button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
        const target = $(e.target).data('bs-target');
        
        // Load data based on active tab
        if (target === '#customer-history') {
            loadCustomerHistory();
        } else if (target === '#customer-debt') {
            loadCustomerDebt();
        } else if (target === '#customer-points') {
            loadCustomerPoints();
        }
    });
}

/**
 * Setup invoice selection modal
 */
function setupInvoiceSelectionModal() {
    // Search invoices
    $('#invoiceSearchInput').on('input', debounce(function() {
        searchInvoices();
    }, 300));
    
    // Time filter change
    $('#invoiceTimeFilter').on('change', function() {
        searchInvoices();
    });
    
    // Customer filter
    $('#invoiceCustomerFilter').on('input', debounce(function() {
        searchInvoices();
    }, 300));
}

/**
 * Show customer info modal
 */
function showCustomerInfo(customerId) {
    if (!customerId) return;
    
    // Load customer data
    $.ajax({
        url: `/admin/customers/${customerId}`,
        method: 'GET',
        success: function(response) {
            if (response.success) {
                populateCustomerModal(response.data);
                $('#customerInfoModal').modal('show');
            } else {
                toastr.error('Không thể tải thông tin khách hàng');
            }
        },
        error: function() {
            toastr.error('Có lỗi xảy ra khi tải thông tin khách hàng');
        }
    });
}

/**
 * Populate customer modal
 */
function populateCustomerModal(data) {
    const customer = data.customer;
    const statistics = data.statistics;
    const branchShop = data.branch_shop;

    // Header with branch shop info
    let headerText = customer.name;
    if (branchShop) {
        headerText += ` Chi nhánh hiện tại: ${branchShop.name}`;
    }
    $('#customerModalName').text(headerText);
    $('#customerModalCode').text(`(${customer.customer_code || 'N/A'})`);

    // Store customer ID for other functions
    $('#customerModalName').data('customer-id', customer.id);

    // Stats - Updated according to requirements
    $('#customerDebtAmount').text(formatCurrency(statistics.total_debt || 0)); // Nợ: Số tiền hóa đơn chưa thanh toán
    $('#customerPointCount').text(statistics.current_points || 0); // Điểm: Số điểm tích lũy hiện tại (số dư)
    $('#customerTotalSpent').text(statistics.total_points_earned || 0); // Tổng điểm: Tổng số điểm tích lũy (không bao gồm điểm bị trừ)
    $('#customerPurchaseCount').text(statistics.completed_invoices || 0); // Số lần mua: Tổng số hóa đơn thành công
    $('#customerNetSales').text(formatCurrency(statistics.net_sales || 0)); // Tổng bán trừ trả hàng

    // Info tab
    $('#customerModalCustomerCode').val(customer.customer_code || '');
    $('#customerModalFullName').val(customer.name || '');
    $('#customerModalPhone').val(customer.phone || '');
    $('#customerModalAddress').val(customer.address || '');
    $('#customerModalArea').val(customer.area || '');
    $('#customerModalType').val(customer.customer_type || '');
    $('#customerModalTaxCode').val(customer.tax_code || '');
    $('#customerModalEmail').val(customer.email || '');
    $('#customerModalFacebook').val(customer.facebook || '');
    $('#customerModalGroup').val(customer.customer_group || '');
    $('#customerModalNotes').val(customer.notes || '');
    $('#customerModalBirthday').val(customer.birthday || '');
}

/**
 * Load customer order history
 */
function loadCustomerHistory(page = 1) {
    const customerId = $('#customerModalName').data('customer-id');
    if (!customerId) return;

    $.ajax({
        url: `/admin/customers/${customerId}/order-history?page=${page}`,
        method: 'GET',
        success: function(response) {
            if (response.success) {
                populateCustomerHistory(response.data.items);
                renderOrderHistoryPagination(response.data.pagination);
            }
        },
        error: function() {
            $('#customerOrderHistoryTable').html('<tr><td colspan="5" class="text-center">Có lỗi xảy ra</td></tr>');
        }
    });
}

/**
 * Populate customer order history
 */
function populateCustomerHistory(orders) {
    let html = '';

    if (orders.length === 0) {
        html = '<tr><td colspan="5" class="text-center">Chưa có lịch sử mua hàng</td></tr>';
    } else {
        orders.forEach(order => {
            const statusBadge = getStatusBadge(order.status, order.status_text);

            // Create link for invoice code
            let codeDisplay = order.code || 'N/A';
            if (order.code && order.code !== 'N/A') {
                codeDisplay = `<a href="/admin/invoices?code=${order.code}" target="_blank" class="text-primary fw-bold">${order.code}</a>`;
            }

            html += `
                <tr>
                    <td>${codeDisplay}</td>
                    <td>${order.formatted_date}</td>
                    <td>${order.seller || 'N/A'}</td>
                    <td class="text-end"><strong>${order.formatted_amount || '0'}</strong></td>
                    <td>${statusBadge}</td>
                </tr>
            `;
        });
    }

    $('#customerOrderHistoryTable').html(html);
}

/**
 * Render order history pagination
 */
function renderOrderHistoryPagination(pagination) {
    if (pagination.total <= pagination.per_page) {
        $('#orderHistoryPagination').html('');
        return;
    }

    let html = '<nav><ul class="pagination pagination-sm">';

    // Previous button
    if (pagination.current_page > 1) {
        html += `<li class="page-item"><a class="page-link" href="#" onclick="loadCustomerHistory(${pagination.current_page - 1})">‹</a></li>`;
    }

    // Page numbers
    for (let i = 1; i <= pagination.last_page; i++) {
        const active = i === pagination.current_page ? 'active' : '';
        html += `<li class="page-item ${active}"><a class="page-link" href="#" onclick="loadCustomerHistory(${i})">${i}</a></li>`;
    }

    // Next button
    if (pagination.current_page < pagination.last_page) {
        html += `<li class="page-item"><a class="page-link" href="#" onclick="loadCustomerHistory(${pagination.current_page + 1})">›</a></li>`;
    }

    html += '</ul></nav>';
    html += `<small class="text-muted">Hiển thị ${pagination.from} - ${pagination.to} trên tổng số ${pagination.total} giao dịch</small>`;

    $('#orderHistoryPagination').html(html);
}

/**
 * Load customer debt
 */
function loadCustomerDebt() {
    const customerId = $('#customerModalName').data('customer-id');
    if (!customerId) return;
    
    $.ajax({
        url: `/admin/customers/${customerId}/debt`,
        method: 'GET',
        success: function(response) {
            if (response.success) {
                populateCustomerDebt(response.data);
            }
        },
        error: function() {
            $('#customerDebtTable').html('<tr><td colspan="6" class="text-center">Có lỗi xảy ra</td></tr>');
        }
    });
}

/**
 * Populate customer debt
 */
function populateCustomerDebt(debts) {
    let html = '';
    
    if (debts.length === 0) {
        html = '<tr><td colspan="6" class="text-center">Không có dư nợ</td></tr>';
    } else {
        debts.forEach(debt => {
            html += `
                <tr>
                    <td>${debt.order_code}</td>
                    <td>${formatDateTime(debt.created_at)}</td>
                    <td>${debt.seller_name}</td>
                    <td class="text-end">${formatCurrency(debt.total_amount)}</td>
                    <td class="text-end">${formatCurrency(debt.paid_amount)}</td>
                    <td class="text-end text-danger">${formatCurrency(debt.debt_amount)}</td>
                </tr>
            `;
        });
    }
    
    $('#customerDebtTable').html(html);
}

/**
 * Load customer point history
 */
function loadCustomerPoints(page = 1) {
    const customerId = $('#customerModalName').data('customer-id');
    if (!customerId) return;

    $.ajax({
        url: `/admin/customers/${customerId}/point-history?page=${page}`,
        method: 'GET',
        success: function(response) {
            if (response.success) {
                populateCustomerPoints(response.data.items);
                renderPointHistoryPagination(response.data.pagination);
            }
        },
        error: function() {
            $('#customerPointsTable').html('<tr><td colspan="6" class="text-center">Có lỗi xảy ra</td></tr>');
        }
    });
}

/**
 * Populate customer point history
 */
function populateCustomerPoints(points) {
    let html = '';

    if (points.length === 0) {
        html = '<tr><td colspan="6" class="text-center">Chưa có lịch sử điểm</td></tr>';
    } else {
        points.forEach(point => {
            // Create link for transaction code
            let codeDisplay = point.code || 'N/A';
            if (point.code) {
                if (point.code.startsWith('HD')) {
                    // Invoice link
                    codeDisplay = `<a href="/admin/invoices?code=${point.code}" target="_blank" class="text-primary fw-bold">${point.code}</a>`;
                } else if (point.code.startsWith('TH')) {
                    // Return order link
                    codeDisplay = `<a href="/admin/returns?code=${point.code}" target="_blank" class="text-primary fw-bold">${point.code}</a>`;
                } else {
                    // No link for other codes (like PT - point adjustments)
                    codeDisplay = `<strong>${point.code}</strong>`;
                }
            }

            html += `
                <tr>
                    <td>${codeDisplay}</td>
                    <td>${point.formatted_date}</td>
                    <td>${point.type_text}</td>
                    <td class="text-end"><strong>${point.formatted_value || '0'}</strong></td>
                    <td class="text-end ${point.points_class || ''}">${point.formatted_points || '0'}</td>
                    <td class="text-end"><strong>${point.formatted_balance || '0'}</strong></td>
                </tr>
            `;
        });
    }

    $('#customerPointsTable').html(html);
}

/**
 * Render point history pagination
 */
function renderPointHistoryPagination(pagination) {
    if (pagination.total <= pagination.per_page) {
        $('#pointHistoryPagination').html('');
        return;
    }

    let html = '<nav><ul class="pagination pagination-sm">';

    // Previous button
    if (pagination.current_page > 1) {
        html += `<li class="page-item"><a class="page-link" href="#" onclick="loadCustomerPoints(${pagination.current_page - 1})">‹</a></li>`;
    }

    // Page numbers
    for (let i = 1; i <= pagination.last_page; i++) {
        const active = i === pagination.current_page ? 'active' : '';
        html += `<li class="page-item ${active}"><a class="page-link" href="#" onclick="loadCustomerPoints(${i})">${i}</a></li>`;
    }

    // Next button
    if (pagination.current_page < pagination.last_page) {
        html += `<li class="page-item"><a class="page-link" href="#" onclick="loadCustomerPoints(${pagination.current_page + 1})">›</a></li>`;
    }

    html += '</ul></nav>';
    html += `<small class="text-muted">Hiển thị ${pagination.from} - ${pagination.to} trên tổng số ${pagination.total} giao dịch</small>`;

    $('#pointHistoryPagination').html(html);
}

/**
 * Search invoices for return orders
 */
function searchInvoices() {
    const searchTerm = $('#invoiceSearchInput').val().trim();
    const timeFilter = $('#invoiceTimeFilter').val();
    const customerFilter = $('#invoiceCustomerFilter').val().trim();
    
    $.ajax({
        url: '/admin/quick-order/search-invoices',
        method: 'POST',
        data: {
            search: searchTerm,
            time_filter: timeFilter,
            customer_filter: customerFilter,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                populateInvoiceList(response.data);
                updateInvoicePagination(response.pagination);
            }
        },
        error: function() {
            toastr.error('Có lỗi xảy ra khi tìm kiếm hóa đơn');
        }
    });
}

/**
 * Populate invoice list
 */
function populateInvoiceList(invoices) {
    let html = '';
    
    if (invoices.length === 0) {
        html = '<tr><td colspan="6" class="text-center">Không tìm thấy hóa đơn nào</td></tr>';
    } else {
        invoices.forEach(invoice => {
            html += `
                <tr>
                    <td>${invoice.invoice_code}</td>
                    <td>${formatDateTime(invoice.created_at)}</td>
                    <td>${invoice.seller_name}</td>
                    <td>${invoice.customer_name || 'Khách lẻ'}</td>
                    <td class="text-end">${formatCurrency(invoice.total_amount)}</td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-primary" onclick="selectInvoiceForReturn(${invoice.id})">
                            Chọn
                        </button>
                    </td>
                </tr>
            `;
        });
    }
    
    $('#invoiceListTableBody').html(html);
}

/**
 * Update invoice pagination
 */
function updateInvoicePagination(pagination) {
    $('#invoiceListInfo').text(`Hiển thị ${pagination.from} đến ${pagination.to} của ${pagination.total} kết quả`);
    
    // Update pagination links (simplified)
    let paginationHtml = '';
    for (let i = 1; i <= pagination.last_page; i++) {
        const activeClass = i === pagination.current_page ? 'active' : '';
        paginationHtml += `<li class="page-item ${activeClass}"><a class="page-link" href="#" onclick="loadInvoicePage(${i})">${i}</a></li>`;
    }
    
    $('#invoiceListPagination').html(paginationHtml);
}

// Note: selectInvoiceForReturn function moved to quick-order-main.js to avoid conflicts

// Note: populateReturnItems and updateReturnOrderHeader functions moved to quick-order-main.js
// All return order logic is now handled in main.js to avoid conflicts

/**
 * Utility functions
 */
function formatDateTime(dateString) {
    return new Date(dateString).toLocaleString('vi-VN');
}

function getStatusColor(status) {
    const colors = {
        'pending': 'warning',
        'processing': 'info',
        'completed': 'success',
        'cancelled': 'danger'
    };
    return colors[status] || 'secondary';
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Get status badge HTML
 */
function getStatusBadge(status, statusText) {
    const statusClasses = {
        'draft': 'badge-secondary',
        'pending': 'badge-warning',
        'processing': 'badge-info',
        'paid': 'badge-success',
        'completed': 'badge-success',
        'cancelled': 'badge-danger',
        'unpaid': 'badge-warning',
        'partial': 'badge-info'
    };

    const badgeClass = statusClasses[status] || 'badge-secondary';
    return `<span class="badge ${badgeClass}">${statusText}</span>`;
}
