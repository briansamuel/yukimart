/**
 * Invoice Selection Module for Return Orders
 * Handles invoice selection modal and related functionality
 */

class InvoiceSelection {
    constructor() {
        this.modal = null;
        this.currentPage = 1;
        this.isLoading = false;
        this.targetTabId = null;
        
        this.init();
    }

    init() {
        this.modal = $('#invoiceSelectionModal');
        this.bindEvents();
    }

    bindEvents() {
        // Search button
        $('#invoiceSearchBtn').on('click', () => this.search());
        
        // Search input enter key
        $('#invoiceSearchInput').on('keypress', (e) => {
            if (e.which === 13) this.search();
        });
        
        // Filter changes
        $('#invoiceTimeFilter, #invoiceCustomerFilter').on('change', () => this.search());
        
        // Modal events
        this.modal.on('show.bs.modal', () => this.onModalShow());
        this.modal.on('hidden.bs.modal', () => this.onModalHide());
    }

    show(tabId) {
        this.targetTabId = tabId;
        this.modal.modal('show');
    }

    onModalShow() {
        this.loadInvoices(1);
    }

    onModalHide() {
        this.reset();
    }

    reset() {
        this.currentPage = 1;
        this.targetTabId = null;
        $('#invoiceSearchInput').val('');
        $('#invoiceTimeFilter').val('this_month');
        $('#invoiceCustomerFilter').val('');
    }

    search() {
        this.currentPage = 1;
        this.loadInvoices(1);
    }

    loadInvoices(page = 1) {
        if (this.isLoading) return;
        
        this.isLoading = true;
        this.showLoading();
        
        const params = this.getSearchParams(page);
        
        $.ajax({
            url: '/admin/invoices/api/list',
            method: 'GET',
            data: params,
            timeout: 10000
        })
        .done((response) => this.handleLoadSuccess(response))
        .fail((xhr) => this.handleLoadError(xhr))
        .always(() => {
            this.isLoading = false;
            this.hideLoading();
        });
    }

    getSearchParams(page) {
        return {
            page: page,
            per_page: 10,
            search: $('#invoiceSearchInput').val().trim(),
            time_filter: $('#invoiceTimeFilter').val(),
            customer_filter: $('#invoiceCustomerFilter').val().trim()
        };
    }

    handleLoadSuccess(response) {
        if (response.success) {
            this.renderInvoiceList(response.data);
            this.renderPagination(response.pagination);
        } else {
            this.showError('Không thể tải danh sách hóa đơn');
        }
    }

    handleLoadError(xhr) {
        let message = 'Lỗi khi tải danh sách hóa đơn';
        if (xhr.status === 404) {
            message = 'Không tìm thấy API endpoint';
        } else if (xhr.status === 500) {
            message = 'Lỗi server, vui lòng thử lại';
        } else if (xhr.statusText === 'timeout') {
            message = 'Timeout, vui lòng thử lại';
        }
        this.showError(message);
    }

    renderInvoiceList(invoices) {
        const tbody = $('#invoiceListTableBody');
        tbody.empty();

        if (!invoices || invoices.length === 0) {
            tbody.append(this.getEmptyStateHtml());
            return;
        }

        invoices.forEach(invoice => {
            tbody.append(this.getInvoiceRowHtml(invoice));
        });
    }

    getEmptyStateHtml() {
        return `
            <tr>
                <td colspan="6" class="text-center text-muted py-4">
                    <i class="fas fa-inbox fa-2x mb-2"></i><br>
                    Không tìm thấy hóa đơn nào
                </td>
            </tr>
        `;
    }

    getInvoiceRowHtml(invoice) {
        return `
            <tr>
                <td>
                    <strong class="text-primary">${this.escapeHtml(invoice.invoice_code)}</strong>
                </td>
                <td>${this.formatDateTime(invoice.created_at)}</td>
                <td>${this.escapeHtml(invoice.creator_name || 'N/A')}</td>
                <td>
                    <strong>${this.escapeHtml(invoice.customer_name || 'Khách lẻ')}</strong>
                    ${invoice.customer_phone ? '<br><small class="text-muted">' + this.escapeHtml(invoice.customer_phone) + '</small>' : ''}
                </td>
                <td>
                    <strong class="text-success">${this.formatCurrency(invoice.total_amount)}</strong>
                </td>
                <td>
                    <button type="button" class="btn btn-primary btn-sm"
                            onclick="invoiceSelection.selectInvoiceWithData('${invoice.id}', '${this.escapeHtml(invoice.invoice_code)}', ${JSON.stringify(invoice).replace(/"/g, '&quot;')})">
                        <i class="fas fa-check me-1"></i>Chọn
                    </button>
                </td>
            </tr>
        `;
    }

    renderPagination(pagination) {
        const container = $('#invoiceListPagination');
        const info = $('#invoiceListInfo');

        // Update info
        info.text(`Hiển thị ${pagination.from} đến ${pagination.to} của ${pagination.total} kết quả`);

        // Clear pagination
        container.empty();

        if (pagination.last_page <= 1) return;

        // Build pagination
        let html = '<ul class="pagination pagination-sm mb-0">';
        
        // Previous button
        if (pagination.current_page > 1) {
            html += `<li class="page-item">
                        <a class="page-link" href="#" onclick="invoiceSelection.loadInvoices(${pagination.current_page - 1})">‹</a>
                     </li>`;
        }

        // Page numbers
        const start = Math.max(1, pagination.current_page - 2);
        const end = Math.min(pagination.last_page, pagination.current_page + 2);

        for (let i = start; i <= end; i++) {
            const active = i === pagination.current_page ? 'active' : '';
            html += `<li class="page-item ${active}">
                        <a class="page-link" href="#" onclick="invoiceSelection.loadInvoices(${i})">${i}</a>
                     </li>`;
        }

        // Next button
        if (pagination.current_page < pagination.last_page) {
            html += `<li class="page-item">
                        <a class="page-link" href="#" onclick="invoiceSelection.loadInvoices(${pagination.current_page + 1})">›</a>
                     </li>`;
        }

        html += '</ul>';
        container.html(html);
    }

    selectInvoice(invoiceId, invoiceCode) {
        if (!this.targetTabId) {
            this.showError('Không xác định được tab đích');
            return;
        }

        this.loadInvoiceItems(invoiceId, invoiceCode);
    }

    selectInvoiceWithData(invoiceId, invoiceCode, invoiceData) {
        if (!this.targetTabId) {
            this.showError('Không xác định được tab đích');
            return;
        }

        // Store invoice data for later use
        this.selectedInvoiceData = invoiceData;
        this.loadInvoiceItems(invoiceId, invoiceCode);
    }

    loadInvoiceItems(invoiceId, invoiceCode) {
        this.showLoading();

        $.ajax({
            url: `/admin/invoices/${invoiceId}/items`,
            method: 'GET',
            timeout: 10000
        })
        .done((response) => {
            if (response.success) {
                this.handleInvoiceItemsSuccess(response.data, invoiceCode);
            } else {
                this.showError(response.message || 'Không thể tải chi tiết hóa đơn');
            }
        })
        .fail(() => {
            this.showError('Lỗi khi tải chi tiết hóa đơn');
        })
        .always(() => {
            this.hideLoading();
        });
    }

    handleInvoiceItemsSuccess(items, invoiceCode) {
        // Close modal
        this.modal.modal('hide');

        // Update tab title
        this.updateTabTitle(invoiceCode);

        // Load items into quick order
        this.loadItemsIntoQuickOrder(items, invoiceCode);

        // Show success message
        this.showSuccess(`Đã tải sản phẩm từ hóa đơn ${invoiceCode}`);
    }

    updateTabTitle(invoiceCode) {
        const tab = $(`#tab_${this.targetTabId}`);
        const titleElement = tab.find('.tab-title');
        titleElement.text(`Trả hàng / ${invoiceCode}`);
    }

    loadItemsIntoQuickOrder(items, invoiceCode) {
        if (typeof window.loadInvoiceItemsToReturnTab === 'function') {
            window.loadInvoiceItemsToReturnTab(this.targetTabId, items, invoiceCode, this.selectedInvoiceData);
        } else {
            console.error('loadInvoiceItemsToReturnTab function not found');
        }

        // Clear stored invoice data
        this.selectedInvoiceData = null;
    }

    showLoading() {
        $('#invoiceSearchBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Đang tải...');
    }

    hideLoading() {
        $('#invoiceSearchBtn').prop('disabled', false).html('<i class="fas fa-search"></i> Tìm kiếm');
    }

    showError(message) {
        if (typeof showNotification === 'function') {
            showNotification('error', message);
        } else {
            alert(message);
        }
    }

    showSuccess(message) {
        if (typeof showNotification === 'function') {
            showNotification('success', message);
        } else {
            alert(message);
        }
    }

    // Utility functions
    escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    formatDateTime(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleString('vi-VN');
    }

    formatCurrency(amount) {
        if (!amount) return '0 ₫';
        return new Intl.NumberFormat('vi-VN').format(amount) + ' ₫';
    }
}

// Initialize when DOM is ready
$(document).ready(function() {
    window.invoiceSelection = new InvoiceSelection();
});
