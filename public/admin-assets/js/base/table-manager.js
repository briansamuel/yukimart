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
        this.initVirtualScrollbar();
        this.initSearch();
        this.initColumnVisibility();
        this.initSelectAll();
        this.initBulkActions();
        
        this.isInitialized = true;
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

    // ===== VIRTUAL SCROLLBAR FUNCTIONALITY =====

    /**
     * Initialize virtual scrollbar for table container
     */
    initVirtualScrollbar(options = {}) {
        if (!this.tableContainer) return;

        console.log(`Initializing virtual scrollbar for ${this.config.module} table`);

        // Default virtual scrollbar options with hardcoded excluded selectors
        const defaultOptions = {
            enabled: true,
            excludedSelectors: [
                '.filter-sidebar',
                '#orders_filter_sidebar',
                '.dropdown-menu',
                '.modal',
                '.popover',
                '.tooltip'
            ]
        };

        // Merge options: config options override defaults, parameter options override both
        this.virtualScrollbarOptions = {
            ...defaultOptions,
            ...options
        };

        // If virtual scrollbar is disabled, skip initialization
        if (!this.virtualScrollbarOptions.enabled) {
            console.log('Virtual scrollbar disabled for', this.config.module);
            return;
        }

        // Create virtual scrollbar container
        this.createVirtualScrollbar();

        // Setup scroll synchronization
        this.setupScrollSync();

        // Update on content changes
        this.updateVirtualScrollbar();

        // Add window resize listener
        window.addEventListener('resize', () => {
            this.updateVirtualScrollbar();
        });
    }

    /**
     * Create virtual scrollbar HTML and styles
     */
    createVirtualScrollbar() {
        // Remove existing virtual scrollbars if any
        const existingScrollbars = document.querySelectorAll('.virtual-scrollbar-container, .fixed-virtual-scrollbar');
        existingScrollbars.forEach(el => el.remove());

        // Remove existing styles
        const existingStyles = document.querySelectorAll('style[data-fixed-scrollbar]');
        existingStyles.forEach(el => el.remove());

        console.log('Virtual scrollbar styles added');

        // Calculate initial thumb height
        const containerHeight = this.tableContainer.clientHeight;
        const scrollHeight = this.tableContainer.scrollHeight;
        const screenHeight = window.innerHeight;

        const contentRatio = containerHeight / scrollHeight;
        const minThumbHeight = 40;
        const maxThumbHeight = screenHeight * 0.3;
        let thumbHeight = contentRatio * screenHeight * 0.6;
        thumbHeight = Math.max(minThumbHeight, Math.min(maxThumbHeight, thumbHeight));

        // Add CSS styles for fixed virtual scrollbar with dynamic thumb height
        const style = document.createElement('style');
        style.setAttribute('data-fixed-scrollbar', 'true');
        style.textContent = `
            .fixed-virtual-scrollbar {
                position: fixed !important;
                right: 0 !important;
                top: 0 !important;
                width: 8px !important;
                height: 100% !important;
                background: rgba(0, 0, 0, 0.05) !important;
                border-radius: 0 !important;
                z-index: 9999 !important;
                opacity: 0.6 !important;
                transition: opacity 0.3s ease !important;
                display: block !important;
            }

            .fixed-virtual-scrollbar:hover {
                opacity: 1 !important;
                background: rgba(0, 0, 0, 0.08) !important;
            }

            .fixed-virtual-thumb {
                position: absolute !important;
                top: 0 !important;
                left: 0 !important;
                width: 100% !important;
                height: ${thumbHeight}px !important;
                background: rgba(0, 0, 0, 0.3) !important;
                border-radius: 4px !important;
                cursor: pointer !important;
                transition: all 0.15s cubic-bezier(0.4, 0, 0.2, 1) !important;
                min-height: 40px !important;
                transform: translateZ(0) !important;
            }

            .fixed-virtual-thumb:hover {
                background: rgba(0, 0, 0, 0.5) !important;
            }

            .fixed-virtual-thumb:active {
                background: rgba(0, 0, 0, 0.7) !important;
            }
        `;

        document.head.appendChild(style);

        // Create fixed virtual scrollbar HTML
        const scrollbarHtml = `
            <div class="fixed-virtual-scrollbar">
                <div class="fixed-virtual-thumb"></div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', scrollbarHtml);
        console.log('Virtual scrollbar HTML created');
    }

    /**
     * Setup scroll synchronization between table and virtual scrollbar
     */
    setupScrollSync() {
        const fixedScrollbar = document.querySelector('.fixed-virtual-scrollbar');
        const fixedThumb = document.querySelector('.fixed-virtual-thumb');

        if (!fixedScrollbar || !fixedThumb) return;

        // Calculate dimensions
        const containerHeight = this.tableContainer.clientHeight;
        const scrollHeight = this.tableContainer.scrollHeight;
        const screenHeight = window.innerHeight;

        const contentRatio = containerHeight / scrollHeight;
        const minThumbHeight = 40;
        const maxThumbHeight = screenHeight * 0.3;
        let thumbHeight = contentRatio * screenHeight * 0.6;
        thumbHeight = Math.max(minThumbHeight, Math.min(maxThumbHeight, thumbHeight));

        fixedThumb.style.setProperty('height', thumbHeight + 'px', 'important');

        // Update thumb position function - use class method for consistency
        const updateThumbPosition = () => {
            this.updateVirtualScrollbarPosition();
        };

        updateThumbPosition();

        // Drag functionality
        let isDragging = false;
        let startY = 0;
        let startScrollTop = 0;

        fixedThumb.addEventListener('mousedown', (e) => {
            isDragging = true;
            startY = e.clientY;
            startScrollTop = this.tableContainer.scrollTop;
            document.addEventListener('mousemove', onMouseMove);
            document.addEventListener('mouseup', onMouseUp);
            e.preventDefault();
        });

        const onMouseMove = (e) => {
            if (!isDragging) return;

            const deltaY = e.clientY - startY;
            const scrollbarHeight = screenHeight - thumbHeight;
            const scrollRatio = deltaY / scrollbarHeight;
            const maxScrollTop = scrollHeight - containerHeight;
            const newScrollTop = startScrollTop + (scrollRatio * maxScrollTop);

            this.tableContainer.scrollTop = Math.max(0, Math.min(maxScrollTop, newScrollTop));
        };

        const onMouseUp = () => {
            isDragging = false;
            document.removeEventListener('mousemove', onMouseMove);
            document.removeEventListener('mouseup', onMouseUp);
        };

        // Improved wheel scroll functionality
        fixedScrollbar.addEventListener('wheel', (e) => {
            e.preventDefault();
            e.stopPropagation();

            const delta = e.deltaY;

            // Dynamic scroll sensitivity
            let scrollAmount;
            if (Math.abs(delta) > 120) {
                scrollAmount = 120; // Very fast
            } else if (Math.abs(delta) > 80) {
                scrollAmount = 80; // Fast
            } else if (Math.abs(delta) > 40) {
                scrollAmount = 50; // Medium
            } else {
                scrollAmount = 25; // Slow
            }

            const currentScrollTop = this.tableContainer.scrollTop;
            const maxScrollTop = scrollHeight - containerHeight;
            const newScrollTop = currentScrollTop + (delta > 0 ? scrollAmount : -scrollAmount);

            this.tableContainer.scrollTop = Math.max(0, Math.min(maxScrollTop, newScrollTop));
        }, { passive: false });

        // Listen to table scroll
        this.tableContainer.addEventListener('scroll', updateThumbPosition);

        // Global wheel event listener for scrolling anywhere on screen (except excluded elements)
        const rawGlobalWheelHandler = (e) => {
            console.log('Global wheel handler triggered, target:', e.target);
            console.log('this context:', this);
            console.log('this.isElementExcluded:', typeof this.isElementExcluded);

            // Check if the event target is within any excluded elements
            if (this.isElementExcluded && this.isElementExcluded(e.target)) {
                console.log('Event excluded by isElementExcluded');
                return; // Don't handle scroll events from excluded elements
            }

            // Allow wheel events on the table container itself, but exclude specific child elements
            // that should handle their own scroll (like .table-responsive when it has its own scrollbar)
            if (this.tableContainer.contains(e.target) && e.target !== this.tableContainer) {
                // Check if target is a scrollable element that should handle its own scroll
                const scrollableChild = e.target.closest('.table-responsive');
                if (scrollableChild && scrollableChild !== this.tableContainer) {
                    // Only exclude if the scrollable child actually has overflow
                    const computedStyle = window.getComputedStyle(scrollableChild);
                    const hasHorizontalScroll = computedStyle.overflowX === 'auto' || computedStyle.overflowX === 'scroll';
                    const hasVerticalScroll = computedStyle.overflowY === 'auto' || computedStyle.overflowY === 'scroll';

                    if (hasHorizontalScroll || hasVerticalScroll) {
                        console.log('Event excluded - target is within scrollable child element');
                        return; // Let the scrollable child handle its own scroll events
                    }
                }
            }

            // Prevent default scroll behavior on body/document
            e.preventDefault();
            e.stopPropagation();

            const delta = e.deltaY;

            // Dynamic scroll sensitivity (same as virtual scrollbar)
            let scrollAmount;
            if (Math.abs(delta) > 120) {
                scrollAmount = 120; // Very fast
            } else if (Math.abs(delta) > 80) {
                scrollAmount = 80; // Fast
            } else if (Math.abs(delta) > 40) {
                scrollAmount = 50; // Medium
            } else {
                scrollAmount = 25; // Slow
            }

            // Get current scroll dimensions
            const currentScrollTop = this.tableContainer.scrollTop;
            const currentScrollHeight = this.tableContainer.scrollHeight;
            const currentContainerHeight = this.tableContainer.clientHeight;
            const maxScrollTop = currentScrollHeight - currentContainerHeight;
            const newScrollTop = currentScrollTop + (delta > 0 ? scrollAmount : -scrollAmount);

            // Smooth scroll the table container
            const targetScrollTop = Math.max(0, Math.min(maxScrollTop, newScrollTop));
            this.smoothScrollTo(targetScrollTop);

            console.log('Global wheel scroll:', {
                delta,
                scrollAmount,
                direction: delta > 0 ? 'down' : 'up',
                newScrollTop: this.tableContainer.scrollTop,
                maxScrollTop
            });
        };

        // Throttle wheel events for smoother scrolling
        let wheelTimeout = null;
        const globalWheelHandler = (e) => {
            // Clear existing timeout
            if (wheelTimeout) {
                clearTimeout(wheelTimeout);
            }

            // Immediate response for better UX
            rawGlobalWheelHandler(e);

            // Throttle subsequent events
            wheelTimeout = setTimeout(() => {
                wheelTimeout = null;
            }, 16); // ~60fps
        };

        // Add global wheel event listener to document
        document.addEventListener('wheel', globalWheelHandler, { passive: false });

        // Store reference for cleanup
        this.globalWheelHandler = globalWheelHandler;

        // Add wheel event listeners to excluded elements to prevent virtual scrollbar
        this.setupExcludedElementListeners();
    }

    /**
     * Check if element is excluded from virtual scrollbar wheel events
     * @param {Element} target - The target element to check
     * @returns {boolean} - True if element should be excluded
     */
    isElementExcluded(target) {
        if (!this.virtualScrollbarOptions || !this.virtualScrollbarOptions.excludedSelectors) {
            console.log('No excluded selectors configured');
            return false;
        }

        console.log('Checking excluded selectors for target:', target);
        console.log('Excluded selectors:', this.virtualScrollbarOptions.excludedSelectors);

        // Check each excluded selector
        for (const selector of this.virtualScrollbarOptions.excludedSelectors) {
            try {
                const excludedElement = document.querySelector(selector);
                console.log(`Checking selector "${selector}":`, excludedElement);
                if (excludedElement && excludedElement.contains(target)) {
                    console.log(`Target is excluded by selector: ${selector}`);
                    return true;
                }
            } catch (error) {
                console.warn(`Invalid selector in excludedSelectors: ${selector}`, error);
            }
        }

        console.log('Target is not excluded');
        return false;
    }

    /**
     * Auto-detect filter sidebar selectors on the page
     * @returns {Array} Array of detected selectors
     */
    detectFilterSidebarSelectors() {
        const detectedSelectors = [];

        // Common filter sidebar selectors to check
        const possibleSelectors = [
            '.filter-sidebar',
            '.app-aside',
            `#${this.config.module}_filter_sidebar`,
            '.sidebar-filter',
            '.filter-panel'
        ];

        possibleSelectors.forEach(selector => {
            try {
                const element = document.querySelector(selector);
                if (element) {
                    detectedSelectors.push(selector);
                    console.log(`Detected filter sidebar: ${selector}`);
                }
            } catch (error) {
                console.warn(`Failed to check selector: ${selector}`, error);
            }
        });

        return detectedSelectors;
    }

    /**
     * Setup wheel event listeners on excluded elements to prevent virtual scrollbar
     */
    setupExcludedElementListeners() {
        console.log('setupExcludedElementListeners called');
        console.log('virtualScrollbarOptions:', this.virtualScrollbarOptions);

        if (!this.virtualScrollbarOptions || !this.virtualScrollbarOptions.excludedSelectors) {
            console.log('No excluded selectors found, returning early');
            return;
        }

        console.log('Setting up excluded element listeners for:', this.virtualScrollbarOptions.excludedSelectors);
        this.excludedElementListeners = [];

        // Add wheel event listeners to each excluded element
        this.virtualScrollbarOptions.excludedSelectors.forEach(selector => {
            try {
                const element = document.querySelector(selector);
                if (element) {
                    const wheelHandler = (e) => {
                        // Stop the event from bubbling to prevent virtual scrollbar
                        e.stopPropagation();
                        console.log(`Wheel event stopped for excluded element: ${selector}`);
                    };

                    element.addEventListener('wheel', wheelHandler, { passive: false });

                    // Store reference for cleanup
                    this.excludedElementListeners.push({
                        element: element,
                        handler: wheelHandler,
                        selector: selector
                    });

                    console.log(`Added wheel listener to excluded element: ${selector}`);
                }
            } catch (error) {
                console.warn(`Failed to add wheel listener to selector: ${selector}`, error);
            }
        });
    }

    /**
     * Add excluded selectors to virtual scrollbar options
     * @param {string|Array} selectors - Selector(s) to exclude from wheel events
     */
    addExcludedSelectors(selectors) {
        if (!this.virtualScrollbarOptions) {
            this.virtualScrollbarOptions = { excludedSelectors: [] };
        }

        if (!this.virtualScrollbarOptions.excludedSelectors) {
            this.virtualScrollbarOptions.excludedSelectors = [];
        }

        const selectorsArray = Array.isArray(selectors) ? selectors : [selectors];
        this.virtualScrollbarOptions.excludedSelectors.push(...selectorsArray);
    }

    /**
     * Remove excluded selectors from virtual scrollbar options
     * @param {string|Array} selectors - Selector(s) to remove from exclusion
     */
    removeExcludedSelectors(selectors) {
        if (!this.virtualScrollbarOptions || !this.virtualScrollbarOptions.excludedSelectors) {
            return;
        }

        const selectorsArray = Array.isArray(selectors) ? selectors : [selectors];
        selectorsArray.forEach(selector => {
            const index = this.virtualScrollbarOptions.excludedSelectors.indexOf(selector);
            if (index > -1) {
                this.virtualScrollbarOptions.excludedSelectors.splice(index, 1);
            }
        });
    }

    /**
     * Update virtual scrollbar position based on table scroll
     */
    updateVirtualScrollbarPosition() {
        const fixedThumb = document.querySelector('.fixed-virtual-thumb');

        if (!this.tableContainer || !fixedThumb) return;

        const containerHeight = this.tableContainer.clientHeight;
        const scrollHeight = this.tableContainer.scrollHeight;
        const screenHeight = window.innerHeight;

        if (scrollHeight <= containerHeight) return;

        const scrollTop = this.tableContainer.scrollTop;
        const maxScrollTop = scrollHeight - containerHeight;
        const scrollPercentage = maxScrollTop > 0 ? scrollTop / maxScrollTop : 0;

        // Get thumb height from computed style, not inline style
        const thumbHeight = parseFloat(getComputedStyle(fixedThumb).height) || 332;
        const maxThumbTop = screenHeight - thumbHeight;
        const thumbTop = scrollPercentage * maxThumbTop;

        fixedThumb.style.setProperty('top', thumbTop + 'px', 'important');

        console.log('Thumb position updated:', {
            scrollTop: scrollTop,
            scrollPercentage: (scrollPercentage * 100).toFixed(2) + '%',
            thumbTop: thumbTop + 'px',
            thumbHeight: thumbHeight + 'px'
        });
    }

    /**
     * Update virtual scrollbar dimensions and visibility
     */
    updateVirtualScrollbar() {
        const fixedScrollbar = document.querySelector('.fixed-virtual-scrollbar');
        const fixedThumb = document.querySelector('.fixed-virtual-thumb');

        if (!this.tableContainer || !fixedScrollbar || !fixedThumb) return;

        const containerHeight = this.tableContainer.clientHeight;
        const scrollHeight = this.tableContainer.scrollHeight;
        const needsScrollbar = scrollHeight > containerHeight;

        if (needsScrollbar) {
            // Show virtual scrollbar
            fixedScrollbar.style.setProperty('display', 'block', 'important');

            // Calculate thumb height proportional to visible content
            const screenHeight = window.innerHeight;
            const contentRatio = containerHeight / scrollHeight;
            const minThumbHeight = 40;
            const maxThumbHeight = screenHeight * 0.3;
            let thumbHeight = contentRatio * screenHeight * 0.6;
            thumbHeight = Math.max(minThumbHeight, Math.min(maxThumbHeight, thumbHeight));

            fixedThumb.style.setProperty('height', thumbHeight + 'px', 'important');

            console.log('Virtual scrollbar updated - needs scrollbar:', needsScrollbar);
        } else {
            // Hide virtual scrollbar
            fixedScrollbar.style.setProperty('display', 'none', 'important');
        }

        // Update thumb position
        this.updateVirtualScrollbarPosition();
    }

    /**
     * Smooth scroll to target position
     * @param {number} targetScrollTop - Target scroll position
     */
    smoothScrollTo(targetScrollTop) {
        // Cancel any existing animation
        if (this.scrollAnimation) {
            cancelAnimationFrame(this.scrollAnimation);
        }

        const startScrollTop = this.tableContainer.scrollTop;
        const distance = targetScrollTop - startScrollTop;
        const duration = 150; // 150ms for smooth but responsive scrolling
        const startTime = performance.now();

        const easeOutCubic = (t) => {
            return 1 - Math.pow(1 - t, 3);
        };

        const animate = (currentTime) => {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            const easedProgress = easeOutCubic(progress);

            const currentScrollTop = startScrollTop + (distance * easedProgress);
            this.tableContainer.scrollTop = currentScrollTop;

            if (progress < 1) {
                this.scrollAnimation = requestAnimationFrame(animate);
            } else {
                this.scrollAnimation = null;
            }
        };

        this.scrollAnimation = requestAnimationFrame(animate);
    }

    /**
     * Cleanup virtual scrollbar event listeners
     */
    cleanupVirtualScrollbar() {
        if (this.globalWheelHandler) {
            document.removeEventListener('wheel', this.globalWheelHandler);
            this.globalWheelHandler = null;
        }

        // Clean up excluded element listeners
        if (this.excludedElementListeners) {
            this.excludedElementListeners.forEach(({ element, handler, selector }) => {
                element.removeEventListener('wheel', handler);
                console.log(`Removed wheel listener from excluded element: ${selector}`);
            });
            this.excludedElementListeners = null;
        }
    }

}

// Export for use in other files
window.BaseTableManager = BaseTableManager;
