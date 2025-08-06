/**
 * Simple Return Table Manager
 * Basic implementation for return orders management
 */
class ReturnTableManager extends BaseTableManager {
    constructor() {
        // Pass config to parent constructor
        super({
            tableId: 'kt_returns_table',
            containerId: 'kt_returns_table_container',
            ajaxUrl: window.returnAjaxUrl || '/admin/returns/ajax',
            module: 'returns',
            storageKey: 'returns_table_state',
            defaultFilters: {
                page: 1,
                per_page: 10
            },
            defaultPerPage: 10
        });

        // Performance optimization properties
        this.detailCache = new Map(); // Cache for detail panel data
        this.cacheExpiry = 5 * 60 * 1000; // 5 minutes cache expiry
        this.pendingRequests = new Map(); // Track pending AJAX requests
        this.debounceTimers = new Map(); // Debounce timers
        this.lazyLoadedTabs = new Set(); // Track lazy-loaded tabs
    }

    init() {
        console.log('Initializing ReturnTableManager...');

        // Call parent init first to get all base functionality
        super.init();

        // Add return-specific functionality
        this.bindRowEvents();
        this.bindGlobalFunctions();

        console.log('ReturnTableManager initialized successfully');
    }

    // Bind action functions to global scope for onclick handlers
    bindGlobalFunctions() {
        window.editReturn = (returnId) => this.editReturn(returnId);
        window.printReturn = (returnId) => this.printReturn(returnId);
        window.exportReturn = (returnId) => this.exportReturn(returnId);
        window.deleteReturn = (returnId) => this.deleteReturn(returnId);
        console.log('Global action functions bound successfully');
    }

    // Override parent's abstract methods
    getSelectAllId() {
        return 'select-all-returns';
    }

    getRowCheckboxes() {
        return document.querySelectorAll('#returns-table-body input[type="checkbox"]');
    }

    getItemName() {
        return 'đơn trả hàng';
    }

    // Load data via AJAX with loading states
    loadData() {
        console.log('Loading return data with filters:', this.getFilterData());

        // Cancel previous request if exists
        if (this.currentRequest) {
            this.currentRequest.abort();
            console.log('Cancelled previous request');
        }

        const filterData = this.getFilterData();
        console.log('Loading returns with filters:', filterData);

        // Show loading states
        this.showLoading('table', 'Đang tải dữ liệu...');
        this.showLoadingState();

        this.currentRequest = $.ajax({
            url: window.returnAjaxUrl,
            type: 'GET',
            data: filterData,
            success: (response) => {
                console.log('Return data loaded successfully:', response);

                if (response.data && Array.isArray(response.data)) {
                    this.renderData(response.data);
                    this.updatePagination(response);

                    // Show success message if needed
                    if (response.message) {
                        this.showSuccessToast(response.message);
                    }
                } else {
                    console.warn('Invalid response format:', response);
                    this.showEmptyState();
                }
            },
            error: (xhr, status, error) => {
                if (xhr.statusText === 'abort') {
                    console.log('Request aborted');
                    return;
                }

                console.error('Error loading return data:', error);
                let errorMessage = 'Có lỗi xảy ra khi tải dữ liệu.';

                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.status === 500) {
                    errorMessage = 'Lỗi server. Vui lòng thử lại sau.';
                } else if (xhr.status === 404) {
                    errorMessage = 'Không tìm thấy dữ liệu.';
                }

                this.showEmptyState(errorMessage);
                this.showErrorToast(errorMessage, 'Lỗi tải dữ liệu');
            },
            complete: () => {
                this.currentRequest = null;
                this.hideLoading('table');
                this.hideLoading('search');
                this.hideLoading('filter');
                this.hideLoading('pagination');
            }
        });
    }

    // Get filter data (enhanced for consistency with invoice-manager.js)
    getFilterData() {
        console.log('getFilterData() called');

        // Get per_page value from selector
        const perPageSelect = document.getElementById('kt_returns_per_page');
        const currentPerPage = perPageSelect ? parseInt(perPageSelect.value) : 10;

        const data = {
            page: this.currentFilters?.page || 1,
            per_page: currentPerPage
        };

        // Search term
        const searchInput = document.querySelector('#return_search');
        if (searchInput && searchInput.value.trim()) {
            data.search = searchInput.value.trim();
        }

        // Get filter form data
        const filterForm = document.getElementById('kt_return_filter_form');
        if (filterForm) {
            // Initialize status array
            data.status = [];

            $(filterForm).find('input, select').each(function() {
                const $input = $(this);
                const name = $input.attr('name') || $input.attr('id');

                if (name) {
                    if ($input.is(':checkbox')) {
                        if (name === 'status[]') {
                            // Handle status checkboxes - collect into status array
                            if ($input.is(':checked')) {
                                data.status.push($input.val());
                            }
                        } else if (name.endsWith('[]')) {
                            // Handle other array checkboxes
                            if (!data[name]) {
                                data[name] = [];
                            }
                            if ($input.is(':checked')) {
                                data[name].push($input.val());
                            }
                        } else {
                            if ($input.is(':checked')) {
                                data[name] = $input.val();
                            }
                        }
                    } else if ($input.is(':radio')) {
                        if ($input.is(':checked')) {
                            data[name] = $input.val();
                        }
                    } else if ($input.is('select, input[type="text"], input[type="date"], input[type="datetime-local"]')) {
                        const value = $input.val();
                        if (value && typeof value === 'string' && value.trim() !== '') {
                            data[name] = value.trim();
                        } else if (value && typeof value !== 'string' && value !== '') {
                            data[name] = value;
                        }
                    }
                }
            });

            // Remove empty status array
            if (data.status && data.status.length === 0) {
                delete data.status;
            }
        } else {
            // Fallback to manual filter collection if form not found
            console.log('Filter form not found, using fallback method');

            // Time filter
            const timeFilter = document.querySelector('input[name="time_filter"]:checked');
            if (timeFilter) {
                data.time_filter = timeFilter.value;
            }

            // Status filter
            const statusCheckboxes = document.querySelectorAll('input[name="status[]"]:checked');
            if (statusCheckboxes.length > 0) {
                data.status = Array.from(statusCheckboxes).map(cb => cb.value);
            }
        }

        console.log('Filter data collected:', data);
        return data;
    }

    // Render returns in table (renamed to renderData for BaseTableManager compatibility)
    renderData(returns) {
        console.log('Rendering returns:', returns);
        
        if (returns.length === 0) {
            this.showEmptyState();
            return;
        }

        const tbody = document.querySelector('#returns-table-body');
        if (!tbody) {
            console.error('Table tbody not found');
            return;
        }

        const rows = returns.map(returnOrder => this.renderReturnRow(returnOrder)).join('');
        tbody.innerHTML = rows;
        
        this.bindRowEvents();
    }

    // Render single return row
    renderReturnRow(returnOrder) {
        const customerName = returnOrder.customer_display || returnOrder.customer_name || 'Khách lẻ';
        const statusBadge = this.getStatusBadge(returnOrder.status);

        return `
            <tr data-return-id="${returnOrder.id}" class="return-row" style="cursor: pointer;">
                <td>
                    <div class="form-check form-check-sm form-check-custom form-check-solid">
                        <input class="form-check-input" type="checkbox" value="${returnOrder.id}" />
                    </div>
                </td>
                <td>
                    <div class="d-flex flex-column">
                        <span class="text-gray-800 text-hover-primary mb-1 fw-bold">${returnOrder.return_number || returnOrder.return_code || 'N/A'}</span>
                    </div>
                </td>
                <td>
                    <div class="d-flex flex-column">
                        <span class="text-gray-800 mb-1">${customerName}</span>
                    </div>
                </td>
                <td class="text-end">
                    <span class="text-gray-800 fw-bold">${this.formatCurrency(returnOrder.total_amount)}</span>
                </td>
                <td class="text-end">
                    <span class="text-gray-800">${this.formatCurrency(returnOrder.amount_paid || returnOrder.paid_amount || 0)}</span>
                </td>
                <td>
                    ${statusBadge}
                </td>
                <td>
                    <span class="text-gray-800">${returnOrder.payment_method || 'N/A'}</span>
                </td>
                <td>
                    <span class="text-gray-800">${returnOrder.sales_channel || 'N/A'}</span>
                </td>
                <td>
                    <div class="d-flex flex-column">
                        <span class="text-gray-800 mb-1">${this.formatDate(returnOrder.created_at)}</span>
                        <span class="text-muted fs-7">${this.formatTime(returnOrder.created_at)}</span>
                    </div>
                </td>
                <td>
                    <span class="text-gray-800">${returnOrder.seller || returnOrder.seller_name || ''}</span>
                </td>
                <td>
                    <span class="text-gray-800">${returnOrder.creator || returnOrder.creator_name || ''}</span>
                </td>
                <td class="text-end">
                    <span class="text-gray-800">${this.formatCurrency(returnOrder.discount || 0)}</span>
                </td>
                <td>
                    <span class="text-gray-800">${returnOrder.email || returnOrder.customer_email || ''}</span>
                </td>
                <td>
                    <span class="text-gray-800">${returnOrder.phone || returnOrder.customer_phone || ''}</span>
                </td>
                <td>
                    <span class="text-gray-800">${returnOrder.address || returnOrder.customer_address || ''}</span>
                </td>
                <td>
                    <span class="text-gray-800">${returnOrder.branch_shop || returnOrder.branch_name || ''}</span>
                </td>
                <td>
                    <span class="text-gray-800">${returnOrder.notes || ''}</span>
                </td>
            </tr>
        `;
    }

    // Get status badge HTML
    getStatusBadge(status) {
        const statusMap = {
            'returned': { class: 'badge-light-success', text: 'Đã trả' },
            'cancelled': { class: 'badge-light-danger', text: 'Đã huỷ' },
            'processing': { class: 'badge-light-warning', text: 'Đang xử lý' },
            'pending': { class: 'badge-light-info', text: 'Chờ xử lý' }
        };

        const statusInfo = statusMap[status] || { class: 'badge-light-secondary', text: status || 'N/A' };
        return `<span class="badge ${statusInfo.class}">${statusInfo.text}</span>`;
    }

    // Bind row events
    bindRowEvents() {
        // Bind checkbox events
        const checkboxes = document.querySelectorAll('#returns-table-body input[type="checkbox"]');
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', (e) => {
                e.stopPropagation();
                const returnId = parseInt(checkbox.value);

                if (checkbox.checked) {
                    this.selectedItems.add(returnId);
                } else {
                    this.selectedItems.delete(returnId);
                }

                this.updateSelectAllState();
                this.updateBulkActionsVisibility();
            });
        });

        // Use event delegation for row click events (similar to invoice-list.js)
        const tbody = $('#returns-table-body');

        // Remove any existing handlers first
        tbody.off('click.rowExpansion');
        $(document).off('click.checkboxPrevention');

        // Prevent checkbox clicks from triggering row expansion
        $(document).on('click.checkboxPrevention', '#returns-table-body input[type="checkbox"]', function(e) {
            e.stopPropagation();
            console.log('Checkbox click propagation stopped');
        });

        // Bind row click events for expansion using event delegation
        const self = this;
        tbody[0].addEventListener('click', function(e) {
            const $row = $(e.target).closest('.return-row');

            if (!$row.length) {
                console.log('Click not on return row, ignoring');
                return;
            }

            // Check if click is on checkbox, button, or form element
            if ($(e.target).is('input[type="checkbox"]') ||
                $(e.target).closest('.form-check, .btn, .dropdown').length) {
                console.log('✅ CHECKBOX/BUTTON DETECTED - Allowing default behavior and returning early');
                return; // Allow default behavior for checkbox/buttons
            }

            console.log('Row click detected, preventing default and stopping propagation');
            // Only prevent default and stop propagation for actual row clicks
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();

            const returnId = $row.data('return-id');
            console.log('Row clicked, return ID:', returnId);
            if (returnId) {
                console.log('About to call toggleRowExpansion');
                self.toggleRowExpansion($row, returnId);
            }
        }, true); // Use capture phase
    }

    // Update select all checkbox state based on individual selections
    updateSelectAllState() {
        const selectAllCheckbox = document.querySelector('#select-all-returns');
        if (!selectAllCheckbox) return;

        const allCheckboxes = this.getRowCheckboxes();
        const totalCheckboxes = allCheckboxes.length;
        const selectedCount = this.selectedItems.size;

        if (selectedCount === 0) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
        } else if (selectedCount === totalCheckboxes) {
            selectAllCheckbox.checked = true;
            selectAllCheckbox.indeterminate = false;
        } else {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = true;
        }

        console.log(`Select all state updated: ${selectedCount}/${totalCheckboxes} selected`);
    }

    // Update bulk actions visibility and selected count
    updateBulkActionsVisibility() {
        // Call parent method
        super.updateBulkActionsVisibility();

        // Update selected count display
        const selectedCount = this.selectedItems.size;
        const countElement = document.querySelector('.selected-count');
        if (countElement) {
            countElement.textContent = selectedCount;
        }

        // Update bulk action button text
        const bulkActionBtn = document.querySelector('.bulk-action-btn');
        if (bulkActionBtn && selectedCount > 0) {
            bulkActionBtn.textContent = `Thao tác (${selectedCount})`;
        }

        console.log(`Bulk actions visibility updated: ${selectedCount} items selected`);
    }

    // Action button functions for detail panel
    editReturn(returnId) {
        console.log('Editing return:', returnId);
        window.location.href = `/admin/returns/${returnId}/edit`;
    }

    printReturn(returnId) {
        console.log('Printing return:', returnId);
        // Open print view in new window
        const printUrl = `/admin/returns/${returnId}/print`;
        window.open(printUrl, '_blank', 'width=800,height=600,scrollbars=yes,resizable=yes');
    }

    exportReturn(returnId) {
        console.log('Exporting return:', returnId);
        // Download export file
        const exportUrl = `/admin/returns/${returnId}/export/pdf`;
        const link = document.createElement('a');
        link.href = exportUrl;
        link.download = `return_${returnId}.pdf`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    deleteReturn(returnId) {
        console.log('Deleting return:', returnId);
        if (confirm('Bạn có chắc chắn muốn xóa đơn trả hàng này?')) {
            $.ajax({
                url: `/admin/returns/${returnId}`,
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: (response) => {
                    console.log('Return deleted successfully:', response);
                    // Reload data
                    this.loadData();
                    // Close detail panel
                    $('.return-detail-row').remove();
                    alert('Đơn trả hàng đã được xóa thành công!');
                },
                error: (xhr, status, error) => {
                    console.error('Error deleting return:', error);
                    alert('Có lỗi xảy ra khi xóa đơn trả hàng!');
                }
            });
        }
    }

    // Toggle row expansion with debouncing (similar to invoice-list.js)
    toggleRowExpansion($row, returnId) {
        console.log('Toggling return expansion for:', returnId);

        // Debounce rapid clicks
        const debounceKey = `toggle_${returnId}`;
        if (this.debounceTimers.has(debounceKey)) {
            console.log('Debouncing rapid click for return:', returnId);
            return;
        }

        // Set debounce timer
        this.debounceTimers.set(debounceKey, setTimeout(() => {
            this.debounceTimers.delete(debounceKey);
        }, 300));

        // Check if detail row already exists
        const $existingDetailRow = $row.next('.return-detail-row');
        if ($existingDetailRow.length) {
            console.log('Detail row exists, collapsing...');
            $existingDetailRow.slideUp(300, () => {
                $existingDetailRow.remove();
            });
            return;
        }

        // Create detail row with loading placeholder (similar to invoice-manager.js)
        const columnCount = $row.find('td').length;
        const $detailRow = $(`
            <tr class="return-detail-row" style="display: none;">
                <td colspan="${columnCount}" class="p-0">
                    <div class="return-detail-container" style="background: #f8f9fa; border-left: 3px solid #009ef7;">
                        <div class="loading-placeholder p-4 text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <div class="mt-2">Đang tải thông tin đơn trả hàng...</div>
                        </div>
                    </div>
                </td>
            </tr>
        `);

        // Insert detail row after clicked row
        $row.after($detailRow);

        // Show detail row with animation
        $detailRow.slideDown(300, () => {
            // Load return detail content
            this.loadReturnDetail(returnId, $detailRow, $row);
        });
    }

    // Load return detail content via AJAX with caching and debouncing
    loadReturnDetail(returnId, $detailRow, $clickedRow) {
        console.log('Loading return detail for ID:', returnId);

        // Check cache first
        const cacheKey = `return_${returnId}`;
        const cachedData = this.getCachedData(cacheKey);

        if (cachedData) {
            console.log('Using cached data for return:', returnId);
            // Replace loading placeholder with cached content
            $detailRow.find('.loading-placeholder').replaceWith(cachedData);
            this.initDetailPanelComponents($detailRow);
            return;
        }

        // Cancel previous request if exists
        if (this.pendingRequests.has(returnId)) {
            this.pendingRequests.get(returnId).abort();
            console.log('Cancelled previous request for return:', returnId);
        }

        // Loading state is already shown by loading-placeholder in detail row creation
        // No need to replace it here

        // Make AJAX request
        const xhr = $.ajax({
            url: `/admin/returns/${returnId}/detail-panel`,
            type: 'GET',
            success: (response) => {
                console.log('Return detail loaded successfully');

                // Cache the response
                this.setCachedData(cacheKey, response);

                // Replace loading placeholder with actual content (similar to invoice-manager.js)
                $detailRow.find('.loading-placeholder').replaceWith(response);

                // Initialize any JavaScript components in the detail panel
                this.initDetailPanelComponents($detailRow);

                // Remove from pending requests
                this.pendingRequests.delete(returnId);
            },
            error: (xhr, status, error) => {
                if (xhr.statusText === 'abort') {
                    console.log('Request aborted for return:', returnId);
                    return;
                }

                console.error('Error loading return detail:', error);

                const errorHtml = `
                    <div class="alert alert-danger m-4">
                        <h5>Lỗi tải thông tin đơn trả hàng</h5>
                        <p>Không thể tải thông tin chi tiết đơn trả hàng. Vui lòng thử lại sau.</p>
                        <small>Error: ${error}</small>
                    </div>
                `;

                // Replace loading placeholder with error content (similar to invoice-manager.js)
                $detailRow.find('.loading-placeholder').replaceWith(errorHtml);

                // Remove from pending requests
                this.pendingRequests.delete(returnId);
            }
        });

        // Store pending request
        this.pendingRequests.set(returnId, xhr);
    }

    // Cache management methods
    getCachedData(key) {
        const cached = this.detailCache.get(key);
        if (!cached) return null;

        // Check if cache is expired
        if (Date.now() - cached.timestamp > this.cacheExpiry) {
            this.detailCache.delete(key);
            console.log('Cache expired for key:', key);
            return null;
        }

        return cached.data;
    }

    setCachedData(key, data) {
        this.detailCache.set(key, {
            data: data,
            timestamp: Date.now()
        });
        console.log('Data cached for key:', key);
    }

    clearCache() {
        this.detailCache.clear();
        console.log('Cache cleared');
    }

    // Initialize detail panel components with lazy loading
    initDetailPanelComponents($detailRow) {
        console.log('Initializing detail panel components');

        // Initialize Bootstrap tabs with lazy loading
        $detailRow.find('a[data-bs-toggle="tab"]').off('click.detailTab').on('click.detailTab', (e) => {
            console.log('Tab click handler executing!');
            e.preventDefault();
            e.stopPropagation();

            const $this = $(e.currentTarget);
            const targetId = $this.attr('href') || $this.data('bs-target');
            const tabKey = targetId.replace('#', '');

            // Remove active class from all tabs and content
            $detailRow.find('.nav-link').removeClass('active');
            $detailRow.find('.tab-pane').removeClass('active show');

            // Add active class to clicked tab
            $this.addClass('active');

            // Show target content
            const $targetPane = $detailRow.find(targetId);
            $targetPane.addClass('active show');

            // Lazy load tab content if not already loaded
            if (!this.lazyLoadedTabs.has(tabKey)) {
                this.lazyLoadTabContent(tabKey, $targetPane);
            }
        });

        // Mark first tab as loaded (it's loaded by default)
        const firstTabId = $detailRow.find('.tab-pane.active').attr('id');
        if (firstTabId) {
            this.lazyLoadedTabs.add(firstTabId);
        }
    }

    // Lazy load tab content
    lazyLoadTabContent(tabKey, $tabPane) {
        console.log('Lazy loading tab content for:', tabKey);

        // Show loading spinner
        const originalContent = $tabPane.html();
        $tabPane.html(`
            <div class="d-flex justify-content-center align-items-center py-5">
                <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <span>Đang tải dữ liệu...</span>
            </div>
        `);

        // Simulate lazy loading (in real implementation, this would be an AJAX call)
        setTimeout(() => {
            $tabPane.html(originalContent);
            this.lazyLoadedTabs.add(tabKey);
            console.log('Tab content loaded for:', tabKey);
        }, 500);
    }

    // Memory cleanup and performance optimization methods
    cleanup() {
        console.log('Cleaning up ReturnTableManager...');

        // Cancel current main request
        if (this.currentRequest) {
            this.currentRequest.abort();
            this.currentRequest = null;
            console.log('Aborted current main request');
        }

        // Cancel all pending detail requests
        this.pendingRequests.forEach((xhr, returnId) => {
            xhr.abort();
            console.log('Aborted pending request for return:', returnId);
        });
        this.pendingRequests.clear();

        // Clear all debounce timers
        this.debounceTimers.forEach((timer, key) => {
            clearTimeout(timer);
            console.log('Cleared debounce timer for:', key);
        });
        this.debounceTimers.clear();

        // Clear cache
        this.clearCache();

        // Clear lazy loaded tabs
        this.lazyLoadedTabs.clear();

        console.log('ReturnTableManager cleanup completed');
    }

    // Optimize performance by clearing old cache entries
    optimizeCache() {
        const now = Date.now();
        let removedCount = 0;

        this.detailCache.forEach((cached, key) => {
            if (now - cached.timestamp > this.cacheExpiry) {
                this.detailCache.delete(key);
                removedCount++;
            }
        });

        if (removedCount > 0) {
            console.log(`Optimized cache: removed ${removedCount} expired entries`);
        }
    }

    // Show empty state
    showEmptyState(message) {
        const tbody = document.querySelector('#returns-table-body');
        if (tbody) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="17">
                        <div class="table-empty-state">
                            <div class="table-empty-icon">
                                <i class="fas fa-inbox"></i>
                            </div>
                            <div class="table-empty-title">Không có dữ liệu</div>
                            <div class="table-empty-message">${message || 'Không tìm thấy đơn trả hàng nào phù hợp với bộ lọc hiện tại.'}</div>
                            <div class="table-error-actions">
                                <button class="btn btn-sm btn-primary" onclick="window.returnTableManager.clearAllFilters()">
                                    <i class="fas fa-filter"></i> Xóa bộ lọc
                                </button>
                            </div>
                        </div>
                    </td>
                </tr>
            `;
        }
    }

    clearAllFilters() {
        // Clear search
        const searchInput = document.querySelector('#return_search');
        if (searchInput) {
            searchInput.value = '';
        }

        // Reset filters
        const filterForm = document.getElementById('kt_return_filter_form');
        if (filterForm) {
            filterForm.reset();
        }

        // Reload data
        this.currentPage = 1;
        this.loadData();

        this.showInfoToast('Đã xóa tất cả bộ lọc', 'Thông tin');
    }

    // Initialize search functionality
    initSearch() {
        console.log('Initializing return search...');
        
        const searchInput = document.querySelector('#return_search');
        if (!searchInput) return;

        let searchTimeout;
        searchInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                this.loadData();
            }, 500);
        });
    }

    // Initialize select all functionality (required by BaseTableManager)
    initSelectAll() {
        console.log('Initializing return select all...');

        // Use parent's generic implementation
        super.initSelectAll();
    }

    // Initialize bulk actions functionality (required by BaseTableManager)
    initBulkActions() {
        console.log('Initializing return bulk actions...');

        // Use parent's generic implementation
        super.initBulkActions();
    }

    // Initialize column visibility functionality (required by BaseTableManager)
    initColumnVisibility() {
        console.log('Initializing return column visibility...');

        // For now, no column visibility for returns
        // Can be implemented later if needed
    }

    // Get select all checkbox ID (required by BaseTableManager)
    getSelectAllId() {
        return 'kt_returns_select_all';
    }



    // Utility methods
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

    // Override updatePagination to use proper pagination logic
    updatePagination(data) {
        console.log('Updating pagination with data:', data);

        // Use parent's pagination implementation which is more robust
        super.updatePagination(data);

        // Additional custom pagination logic if needed
        const recordsFiltered = data.recordsFiltered || 0;
        const currentPage = this.currentFilters.page || 1;
        const perPage = this.currentFilters.per_page || 10;

        console.log(`Pagination updated: page ${currentPage}, ${recordsFiltered} total records, ${perPage} per page`);
    }

    // Override bulk actions
    bulkDelete() {
        const selectedIds = Array.from(this.selectedItems);
        if (selectedIds.length === 0) {
            alert('Vui lòng chọn ít nhất một đơn trả hàng');
            return;
        }
        
        if (confirm('Bạn có chắc chắn muốn huỷ ' + selectedIds.length + ' đơn trả hàng đã chọn?')) {
            console.log('Bulk cancel returns:', selectedIds);
            // Implementation for bulk cancel
        }
    }

    bulkUpdate() {
        const selectedIds = Array.from(this.selectedItems);
        if (selectedIds.length === 0) {
            alert('Vui lòng chọn ít nhất một đơn trả hàng');
            return;
        }
        
        alert('Tính năng cập nhật trạng thái cho ' + selectedIds.length + ' đơn trả hàng sẽ được triển khai.');
    }
}
