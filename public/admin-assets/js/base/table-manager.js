/**
 * Base Table Manager Class
 * Common functionality for all table pages (orders, invoices, payments, etc.)
 */

class BaseTableManager {
    constructor(config) {
        this.config = {
            tableId: '',
            containerId: '',
            ajaxUrl: '',
            module: '',
            storageKey: '',
            defaultFilters: {},
            defaultPerPage: 10,
            ...config
        };
        
        this.table = null;
        this.tableContainer = null;
        this.currentFilters = { ...this.config.defaultFilters };
        this.lastResponseData = null;
        this.isInitialized = false;
        this.currentRequest = null;
        this.selectedItems = new Set();

        // Loading states management
        this.loadingStates = {
            table: false,
            search: false,
            filter: false,
            pagination: false,
            export: false
        };
        
        // Bind methods to preserve context
        this.loadData = this.loadData.bind(this);
        this.handleScroll = this.handleScroll.bind(this);
        this.handleResize = this.handleResize.bind(this);
        
        this.init();
    }
    
    init() {
        console.log(`Initializing ${this.config.module} Table Manager...`);
        
        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setup());
        } else {
            this.setup();
        }
    }
    
    setup() {
        this.tableContainer = document.getElementById(this.config.containerId);
        this.table = document.getElementById(this.config.tableId);
        
        if (!this.tableContainer || !this.table) {
            console.warn(`${this.config.module} table elements not found, retrying...`);
            setTimeout(() => this.setup(), 100);
            return;
        }
        
        this.initScrollIndicators();
        this.initResponsiveHandlers();
        this.initPagination();
        this.initSearch();
        this.initColumnVisibility();
        this.initSelectAll();
        this.initBulkActions();
        
        this.isInitialized = true;
        console.log(`${this.config.module} Table Manager initialized successfully`);
    }
    
    // Scroll indicators for horizontal scroll
    initScrollIndicators() {
        console.log('Initializing scroll indicators...');
        
        if (this.tableContainer) {
            this.tableContainer.addEventListener('scroll', this.handleScroll);
            this.updateScrollIndicators();
        }
    }
    
    updateScrollIndicators() {
        if (!this.tableContainer) return;
        
        const hasScroll = this.tableContainer.scrollWidth > this.tableContainer.clientWidth;
        const hasScrollLeft = this.tableContainer.scrollLeft > 0;
        const hasScrollRight = this.tableContainer.scrollLeft < (this.tableContainer.scrollWidth - this.tableContainer.clientWidth);
        
        //this.tableContainer.classList.toggle('has-scroll', hasScroll);
        //this.tableContainer.classList.toggle('has-scroll-left', hasScrollLeft);
        //this.tableContainer.classList.toggle('has-scroll-right', hasScrollRight);
    }
    
    handleScroll() {
        if (this.scrollTimeout) {
            clearTimeout(this.scrollTimeout);
        }
        
        this.scrollTimeout = setTimeout(() => {
            this.updateScrollIndicators();
        }, 10);
    }
    
    // Responsive handlers
    initResponsiveHandlers() {
        console.log('Initializing responsive handlers...');
        
        window.addEventListener('resize', this.handleResize);
        this.updateResponsiveState();
    }
    
    handleResize() {
        if (this.resizeTimeout) {
            clearTimeout(this.resizeTimeout);
        }
        
        this.resizeTimeout = setTimeout(() => {
            this.updateResponsiveState();
            this.updateScrollIndicators();
        }, 100);
    }
    
    updateResponsiveState() {
        const windowWidth = window.innerWidth;
        let currentBreakpoint = 'xl';
        
        if (windowWidth < 576) currentBreakpoint = 'mobile';
        else if (windowWidth < 768) currentBreakpoint = 'mobile-lg';
        else if (windowWidth < 992) currentBreakpoint = 'tablet';
        else if (windowWidth < 1200) currentBreakpoint = 'desktop';
        else if (windowWidth < 1400) currentBreakpoint = 'desktop-lg';
        
        if (this.tableContainer) {
            this.tableContainer.className = this.tableContainer.className.replace(/\b\w+-table-\w+\b/g, '');
            this.tableContainer.classList.add(`${this.config.module}-table-${currentBreakpoint}`);
        }
    }
    
    // Pagination system
    initPagination() {
        console.log('Initializing pagination...');

        // Load saved per page state
        this.loadPerPageState();

        // Initialize per page selector
        const perPageSelect = document.getElementById(`kt_${this.config.module}_per_page`);
        if (perPageSelect) {
            // Set saved per page value
            perPageSelect.value = this.currentFilters.per_page;

            perPageSelect.addEventListener('change', (e) => {
                this.currentFilters.per_page = parseInt(e.target.value);
                this.currentFilters.page = 1; // Reset to first page
                this.savePerPageState(); // Save state
                this.loadData();
            });
        }

        // Initialize pagination click events
        this.bindPaginationEvents();
    }
    
    bindPaginationEvents() {
        const paginationContainer = document.getElementById(`kt_${this.config.module}_table_pagination`);
        if (!paginationContainer) return;
        
        paginationContainer.addEventListener('click', (e) => {
            e.preventDefault();
            
            const link = e.target.closest('.page-link');
            if (!link) return;
            
            const listItem = link.closest('.page-item');
            if (listItem.classList.contains('disabled') || listItem.classList.contains('active')) return;
            
            let targetPage = this.currentFilters.page || 1;
            
            if (listItem.classList.contains('previous')) {
                targetPage = Math.max(1, targetPage - 1);
            } else if (listItem.classList.contains('next')) {
                const totalPages = this.calculateTotalPages();
                targetPage = Math.min(totalPages, targetPage + 1);
            } else {
                const pageText = link.textContent.trim();
                if (!isNaN(pageText)) {
                    targetPage = parseInt(pageText);
                }
            }
            
            if (targetPage !== this.currentFilters.page) {
                this.currentFilters.page = targetPage;
                this.loadData();
            }
        });
    }
    
    updatePagination(data) {
        const recordsTotal = data.recordsTotal || 0;
        const recordsFiltered = data.recordsFiltered || recordsTotal;
        const currentPage = this.currentFilters.page || 1;
        const perPage = this.currentFilters.per_page || this.config.defaultPerPage;
        const totalPages = Math.ceil(recordsFiltered / perPage);
        
        // Update info text
        this.updatePaginationInfo(recordsFiltered, currentPage, perPage);
        
        // Update per page selector
        this.updatePerPageSelector(perPage);
        
        // Update pagination controls
        this.updatePaginationControls(currentPage, totalPages);
    }
    
    updatePaginationInfo(total, currentPage, perPage) {
        const infoElement = document.getElementById(`kt_${this.config.module}_table_info`);
        if (!infoElement) return;
        
        if (total > 0) {
            const start = ((currentPage - 1) * perPage) + 1;
            const end = Math.min(currentPage * perPage, total);
            infoElement.textContent = `Hiển thị ${start} đến ${end} của ${total} kết quả`;
        } else {
            infoElement.textContent = 'Hiển thị 0 đến 0 của 0 kết quả';
        }
    }
    
    updatePerPageSelector(currentPerPage) {
        const perPageSelect = document.getElementById(`kt_${this.config.module}_per_page`);
        if (perPageSelect && perPageSelect.value != currentPerPage) {
            perPageSelect.value = currentPerPage;
        }
    }
    
    updatePaginationControls(currentPage, totalPages) {
        const paginationContainer = document.getElementById(`kt_${this.config.module}_table_pagination`);
        if (!paginationContainer) return;
        
        paginationContainer.innerHTML = '';
        
        // Previous button
        const prevDisabled = currentPage <= 1;
        const prevItem = this.createPaginationItem('previous', 'Trước', prevDisabled);
        paginationContainer.appendChild(prevItem);
        
        // Page numbers
        const pageNumbers = this.generatePageNumbers(currentPage, totalPages);
        pageNumbers.forEach(pageInfo => {
            const pageItem = this.createPaginationItem(
                pageInfo.type,
                pageInfo.label,
                pageInfo.disabled,
                pageInfo.active
            );
            paginationContainer.appendChild(pageItem);
        });
        
        // Next button
        const nextDisabled = currentPage >= totalPages;
        const nextItem = this.createPaginationItem('next', 'Tiếp', nextDisabled);
        paginationContainer.appendChild(nextItem);
    }
    
    generatePageNumbers(currentPage, totalPages) {
        const pages = [];
        const maxVisiblePages = 5;
        
        if (totalPages <= maxVisiblePages) {
            for (let i = 1; i <= totalPages; i++) {
                pages.push({
                    type: 'page',
                    label: i.toString(),
                    disabled: false,
                    active: i === currentPage
                });
            }
        } else {
            let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
            let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);
            
            if (endPage - startPage < maxVisiblePages - 1) {
                startPage = Math.max(1, endPage - maxVisiblePages + 1);
            }
            
            if (startPage > 1) {
                pages.push({ type: 'page', label: '1', disabled: false, active: false });
                if (startPage > 2) {
                    pages.push({ type: 'ellipsis', label: '...', disabled: true, active: false });
                }
            }
            
            for (let i = startPage; i <= endPage; i++) {
                pages.push({
                    type: 'page',
                    label: i.toString(),
                    disabled: false,
                    active: i === currentPage
                });
            }
            
            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    pages.push({ type: 'ellipsis', label: '...', disabled: true, active: false });
                }
                pages.push({ type: 'page', label: totalPages.toString(), disabled: false, active: false });
            }
        }
        
        return pages;
    }
    
    createPaginationItem(type, label, disabled = false, active = false) {
        const li = document.createElement('li');
        li.className = `page-item ${type}`;
        
        if (disabled) li.classList.add('disabled');
        if (active) li.classList.add('active');
        
        const a = document.createElement('a');
        a.href = '#';
        a.className = 'page-link';
        
        if (type === 'previous') {
            a.innerHTML = `<i class="fas fa-chevron-left"></i>`;
        } else if (type === 'next') {
            a.innerHTML = `<i class="fas fa-chevron-right"></i>`;
        } else {
            a.textContent = label;
        }
        
        li.appendChild(a);
        return li;
    }
    
    calculateTotalPages() {
        if (!this.lastResponseData) return 1;
        const total = this.lastResponseData.recordsFiltered || 0;
        const perPage = this.currentFilters.per_page || this.config.defaultPerPage;
        return Math.ceil(total / perPage);
    }
    
    // Abstract methods to be implemented by subclasses
    initSearch() {
        throw new Error('initSearch method must be implemented by subclass');
    }
    
    initColumnVisibility() {
        throw new Error('initColumnVisibility method must be implemented by subclass');
    }
    
    initSelectAll() {
        throw new Error('initSelectAll method must be implemented by subclass');
    }
    
    initBulkActions() {
        throw new Error('initBulkActions method must be implemented by subclass');
    }
    
    loadData() {
        throw new Error('loadData method must be implemented by subclass');
    }
    
    renderData(data) {
        throw new Error('renderData method must be implemented by subclass');
    }
    
    // ===== LOADING STATES MANAGEMENT =====

    showLoading(component, message) {
        this.loadingStates[component] = true;

        switch(component) {
            case 'table':
                this.showTableLoading(message || 'Đang tải dữ liệu...');
                break;
            case 'search':
                this.showSearchLoading();
                break;
            case 'filter':
                this.showFilterLoading();
                break;
            case 'pagination':
                this.showPaginationLoading();
                break;
            case 'export':
                this.showExportLoading();
                break;
        }
    }

    hideLoading(component) {
        this.loadingStates[component] = false;

        switch(component) {
            case 'table':
                this.hideTableLoading();
                break;
            case 'search':
                this.hideSearchLoading();
                break;
            case 'filter':
                this.hideFilterLoading();
                break;
            case 'pagination':
                this.hidePaginationLoading();
                break;
            case 'export':
                this.hideExportLoading();
                break;
        }
    }

    showTableLoading(message) {
        if (!this.tableContainer) return;

        // Remove existing overlay
        const existingOverlay = this.tableContainer.querySelector('.table-loading-overlay');
        if (existingOverlay) {
            existingOverlay.remove();
        }

        // Create loading overlay
        const overlay = document.createElement('div');
        overlay.className = 'table-loading-overlay';
        overlay.innerHTML = `
            <div class="d-flex align-items-center">
                <div class="loading-spinner"></div>
                <div class="loading-text">${message}</div>
            </div>
        `;

        // Make container relative if not already
        if (getComputedStyle(this.tableContainer).position === 'static') {
            this.tableContainer.style.position = 'relative';
        }

        this.tableContainer.appendChild(overlay);
    }

    hideTableLoading() {
        if (!this.tableContainer) return;

        const overlay = this.tableContainer.querySelector('.table-loading-overlay');
        if (overlay) {
            overlay.remove();
        }
    }

    showSearchLoading() {
        const searchInput = document.querySelector(`input[data-kt-${this.config.module}-table-filter="search"]`);
        if (searchInput) {
            searchInput.classList.add('search-loading');
        }
    }

    hideSearchLoading() {
        const searchInput = document.querySelector(`input[data-kt-${this.config.module}-table-filter="search"]`);
        if (searchInput) {
            searchInput.classList.remove('search-loading');
        }
    }

    showFilterLoading() {
        const filterForm = document.getElementById(`kt_${this.config.module}_filter_form`);
        if (filterForm) {
            filterForm.classList.add('filter-loading');
        }
    }

    hideFilterLoading() {
        const filterForm = document.getElementById(`kt_${this.config.module}_filter_form`);
        if (filterForm) {
            filterForm.classList.remove('filter-loading');
        }
    }

    showPaginationLoading() {
        const pagination = document.querySelector('.pagination');
        if (pagination) {
            pagination.classList.add('pagination-loading');
        }
    }

    hidePaginationLoading() {
        const pagination = document.querySelector('.pagination');
        if (pagination) {
            pagination.classList.remove('pagination-loading');
        }
    }

    showExportLoading() {
        const exportBtn = document.querySelector(`[data-kt-${this.config.module}-table-toolbar="export"]`);
        if (exportBtn) {
            exportBtn.classList.add('btn-loading');
            exportBtn.disabled = true;
        }
    }

    hideExportLoading() {
        const exportBtn = document.querySelector(`[data-kt-${this.config.module}-table-toolbar="export"]`);
        if (exportBtn) {
            exportBtn.classList.remove('btn-loading');
            exportBtn.disabled = false;
        }
    }

    // Legacy methods for backward compatibility
    showLoadingState() {
        this.showLoading('table');
    }

    hideLoadingState() {
        this.hideLoading('table');
    }

    // ===== TOAST NOTIFICATIONS SYSTEM =====

    showSuccessToast(message, title = 'Thành công') {
        if (typeof toastr !== 'undefined') {
            toastr.success(message, title, {
                closeButton: true,
                progressBar: true,
                positionClass: 'toast-top-right',
                timeOut: 3000
            });
        } else {
            console.log(`SUCCESS: ${title} - ${message}`);
        }
    }

    showErrorToast(message, title = 'Lỗi') {
        if (typeof toastr !== 'undefined') {
            toastr.error(message, title, {
                closeButton: true,
                progressBar: true,
                positionClass: 'toast-top-right',
                timeOut: 5000
            });
        } else {
            console.error(`ERROR: ${title} - ${message}`);
        }
    }

    showWarningToast(message, title = 'Cảnh báo') {
        if (typeof toastr !== 'undefined') {
            toastr.warning(message, title, {
                closeButton: true,
                progressBar: true,
                positionClass: 'toast-top-right',
                timeOut: 4000
            });
        } else {
            console.warn(`WARNING: ${title} - ${message}`);
        }
    }

    showInfoToast(message, title = 'Thông tin') {
        if (typeof toastr !== 'undefined') {
            toastr.info(message, title, {
                closeButton: true,
                progressBar: true,
                positionClass: 'toast-top-right',
                timeOut: 3000
            });
        } else {
            console.info(`INFO: ${title} - ${message}`);
        }
    }

    // ===== ENHANCED SEARCH FUNCTIONALITY =====

    initEnhancedSearch() {
        const searchInput = document.querySelector(`input[data-kt-${this.config.module}-table-filter="search"]`);
        if (!searchInput) return;

        // Create search wrapper if not exists
        let searchWrapper = searchInput.closest('.search-wrapper');
        if (!searchWrapper) {
            searchWrapper = document.createElement('div');
            searchWrapper.className = 'search-wrapper position-relative';
            searchInput.parentNode.insertBefore(searchWrapper, searchInput);
            searchWrapper.appendChild(searchInput);
        }

        // Create search actions
        const actionsDiv = document.createElement('div');
        actionsDiv.className = 'search-actions';
        actionsDiv.innerHTML = `
            <div class="search-loading-indicator"></div>
            <button type="button" class="search-clear-btn" title="Xóa tìm kiếm">
                <i class="fas fa-times"></i>
            </button>
            <div class="search-icon">
                <i class="fas fa-search"></i>
            </div>
        `;

        // Remove existing actions
        const existingActions = searchWrapper.querySelector('.search-actions');
        if (existingActions) {
            existingActions.remove();
        }

        searchWrapper.appendChild(actionsDiv);

        // Bind clear button
        const clearBtn = actionsDiv.querySelector('.search-clear-btn');
        clearBtn.addEventListener('click', () => {
            searchInput.value = '';
            searchInput.focus();
            this.currentFilters.search = '';
            this.currentFilters.page = 1;
            this.loadData();
        });

        // Update clear button visibility
        const updateClearButton = () => {
            clearBtn.style.display = searchInput.value.trim() ? 'flex' : 'none';
        };

        searchInput.addEventListener('input', updateClearButton);
        updateClearButton();
    }

    // Public methods for external use
    refreshScrollIndicators() {
        this.updateScrollIndicators();
    }
    
    refresh() {
        this.loadData();
    }
    
    // Cleanup
    destroy() {
        if (this.tableContainer) {
            this.tableContainer.removeEventListener('scroll', this.handleScroll);
        }
        
        window.removeEventListener('resize', this.handleResize);
        
        if (this.scrollTimeout) {
            clearTimeout(this.scrollTimeout);
        }
        
        if (this.resizeTimeout) {
            clearTimeout(this.resizeTimeout);
        }
    }

    // Per page state management
    loadPerPageState() {
        const storageKey = `${this.config.module}_per_page_state`;
        const savedPerPage = localStorage.getItem(storageKey);

        if (savedPerPage) {
            this.currentFilters.per_page = parseInt(savedPerPage);
            console.log(`Loaded per page state for ${this.config.module}:`, savedPerPage);
        } else {
            this.currentFilters.per_page = this.config.defaultPerPage;
            console.log(`Using default per page for ${this.config.module}:`, this.config.defaultPerPage);
        }
    }

    savePerPageState() {
        const storageKey = `${this.config.module}_per_page_state`;
        localStorage.setItem(storageKey, this.currentFilters.per_page.toString());
        console.log(`Saved per page state for ${this.config.module}:`, this.currentFilters.per_page);
    }

    // ===== COMMON FUNCTIONS FOR REUSE =====

    // Generic select all functionality
    initSelectAll() {
        console.log('Initializing select all functionality...');

        const selectAllId = this.getSelectAllId();
        const selectAllCheckbox = document.getElementById(selectAllId);

        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', (e) => {
                const isChecked = e.target.checked;
                const checkboxes = this.getRowCheckboxes();

                checkboxes.forEach(checkbox => {
                    checkbox.checked = isChecked;
                    const itemId = checkbox.value;

                    if (isChecked) {
                        this.selectedItems.add(itemId);
                    } else {
                        this.selectedItems.delete(itemId);
                    }
                });

                this.updateBulkActionsVisibility();
                this.updateSelectedCount();
            });
        }
    }

    // Generic bulk actions functionality
    initBulkActions() {
        console.log('Initializing bulk actions...');

        // Bulk delete
        const bulkDeleteBtn = document.getElementById('bulk-delete-btn');
        if (bulkDeleteBtn) {
            bulkDeleteBtn.addEventListener('click', () => {
                if (this.selectedItems.size === 0) return;

                const itemName = this.getItemName();
                if (confirm(`Bạn có chắc chắn muốn xóa ${this.selectedItems.size} ${itemName} đã chọn?`)) {
                    this.bulkDelete();
                }
            });
        }

        // Bulk status update
        const bulkStatusBtn = document.getElementById('bulk-status-btn');
        if (bulkStatusBtn) {
            bulkStatusBtn.addEventListener('click', () => {
                if (this.selectedItems.size === 0) return;
                this.showBulkStatusModal();
            });
        }

        // Bulk export
        const bulkExportBtn = document.getElementById('bulk-export-btn');
        if (bulkExportBtn) {
            bulkExportBtn.addEventListener('click', () => {
                if (this.selectedItems.size === 0) return;
                this.bulkExport();
            });
        }
    }

    // Common bulk action visibility and count updates
    updateBulkActionsVisibility() {
        const bulkActionsContainer = document.querySelector('.bulk-actions-container');
        if (bulkActionsContainer) {
            bulkActionsContainer.style.display = this.selectedItems.size > 0 ? 'flex' : 'none';
        }
    }

    updateSelectedCount() {
        const countElement = document.querySelector('.selected-count');
        if (countElement) {
            countElement.textContent = this.selectedItems.size;
        }
    }

    // Common error rendering
    renderError(message) {
        const tbody = this.table.querySelector('tbody');
        if (tbody) {
            const colCount = this.table.querySelector('thead tr').children.length;
            tbody.innerHTML = `<tr><td colspan="${colCount}" class="text-center text-danger">${message}</td></tr>`;
        }
    }

    // Common utility methods
    formatCurrency(amount) {
        if (!amount) return '0 ₫';
        return new Intl.NumberFormat('vi-VN').format(amount) + ' ₫';
    }

    formatDate(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleDateString('vi-VN');
    }

    formatTime(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        return date.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
    }

    // ===== ABSTRACT METHODS - MUST BE IMPLEMENTED BY CHILD CLASSES =====

    // Get the select all checkbox ID for this module
    getSelectAllId() {
        // Override in child classes
        throw new Error('getSelectAllId() must be implemented by child class');
    }

    // Get row checkboxes for this module
    getRowCheckboxes() {
        // Override in child classes
        throw new Error('getRowCheckboxes() must be implemented by child class');
    }

    // Get item name for confirmation messages
    getItemName() {
        // Override in child classes
        throw new Error('getItemName() must be implemented by child class');
    }

    // Bulk actions - to be implemented by child classes
    bulkDelete() {
        console.log(`Bulk deleting ${this.config.module}:`, Array.from(this.selectedItems));
        // Override in child classes
    }

    showBulkStatusModal() {
        console.log(`Showing bulk status modal for ${this.config.module}:`, Array.from(this.selectedItems));
        // Override in child classes
    }

    bulkExport() {
        console.log(`Bulk exporting ${this.config.module}:`, Array.from(this.selectedItems));
        // Override in child classes
    }

    
    getStatusBadge(status) {
        const badges = {
            'processing': '<span class="badge badge-warning">Đang xử lý</span>',
            'completed': '<span class="badge badge-success">Hoàn thành</span>',
            'cancelled': '<span class="badge badge-dark">Đã hủy</span>',
            'undeliverable': '<span class="badge badge-danger">Không giao được</span>'
        };
        return badges[status] || '<span class="badge badge-secondary">Đang xử lý</span>';
    }

    getPaymentMethodDisplay(method) {
        const methods = {
            'cash': 'Tiền mặt',
            'card': 'Thẻ',
            'transfer': 'Chuyển khoản',
            'check': 'Séc',
            'other': 'Khác'
        };
        return methods[method] || 'N/A';
    }

    getSalesChannelDisplay(channel) {
        const channels = {
            'offline': 'Cửa hàng',
            'marketplace': 'Marketplace',
            'online': 'Online',
            'direct': 'Direct',
            'phone': 'Điện thoại',
            'social': 'Mạng xã hội',
            'other': 'Khác',
        };
        return channels[channel] || 'N/A';
    }

    getPaymentStatusBadge(status) {
        const badges = {
            'paid': '<span class="badge badge-success">Đã thanh toán</span>',
            'partial': '<span class="badge badge-warning">Thanh toán một phần</span>',
            'unpaid': '<span class="badge badge-danger">Chưa thanh toán</span>'
        };
        return badges[status] || '<span class="badge badge-danger">Chưa thanh toán</span>';
    }

    getDeliveryStatusBadge(status) {
        const badges = {
            'pending': '<span class="badge badge-secondary">Chờ xử lý</span>',
            'picking': '<span class="badge badge-info">Lấy hàng</span>',
            'shipping': '<span class="badge badge-primary">Giao hàng</span>',
            'delivered': '<span class="badge badge-success">Đã giao hàng</span>',
            'failed': '<span class="badge badge-danger">Giao thất bại</span>',
            'returned': '<span class="badge badge-warning">Hoàn trả</span>'
        };
        return badges[status] || '<span class="badge badge-secondary">Chờ xử lý</span>';
    }
    
    getPaymentMethodBadge(method) {
        const badges = {
            'cash': '<span class="badge badge-success">Tiền mặt</span>',
            'bank_transfer': '<span class="badge badge-info">Chuyển khoản</span>',
            'credit_card': '<span class="badge badge-primary">Thẻ tín dụng</span>',
            'e_wallet': '<span class="badge badge-warning">Ví điện tử</span>',
            'cod': '<span class="badge badge-secondary">COD</span>'
        };
        return badges[method] || '<span class="badge badge-light">N/A</span>';
    }
    
}

// Export for use in other files
window.BaseTableManager = BaseTableManager;
