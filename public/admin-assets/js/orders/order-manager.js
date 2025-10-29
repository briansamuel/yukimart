/**
 * Order Table Manager
 * Extends BaseTableManager for order-specific functionality
 */

class OrderTableManager extends BaseTableManager {
    constructor() {
        super({
            tableId: 'kt_orders_table',
            containerId: 'kt_orders_table_container',
            ajaxUrl: window.orderRoutes?.data || '/admin/orders/ajax',
            module: 'orders',
            storageKey: 'orders_column_visibility',
            defaultFilters: {
                page: 1,
                per_page: 10,
                search: '',
                time_filter_display: 'this_month',
                date_from: '',
                date_to: '',
                status: ['processing', 'completed', 'draft'],
                delivery_status: '',
                created_by: '',
                sold_by: '',
                sale_channel: '',
                payment_method: '',
                delivery_time_filter: 'all',
                delivery_date_from: '',
                delivery_date_to: ''
            },
            defaultPerPage: 10
        });

        // Use parent's selectedItems instead of selectedOrders
        this.expandedRows = new Set(); // Track expanded rows
    }

    // Override parent's init to add detail panel functionality
    init() {
        // Don't call super.init() directly, instead call setup manually
        // to avoid the abstract method issue
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setup());
        } else {
            this.setup();
        }
    }

    // Override setup to handle initialization properly
    setup() {
        this.tableContainer = document.getElementById(this.config.containerId);
        this.table = document.getElementById(this.config.tableId);

        if (!this.tableContainer || !this.table) {
            console.warn(`${this.config.module} table elements not found, retrying...`);
            setTimeout(() => this.setup(), 100);
            return;
        }

        // Load saved filter state before initializing
        this.loadSavedFilterState();

        // Initialize components in correct order
        this.initScrollIndicators();
        this.initResponsiveHandlers();
        this.initPagination();
        this.initVirtualScrollbar();
        this.initSearch(); // Now this will call our implementation
        this.initColumnVisibility();
        this.initSelectAll();
        this.initBulkActions();
        this.initResetFilters();

        // Create border spans for detail panel visual separation
        this.createBorderSpans();

        this.isInitialized = true;
        console.log('OrderTableManager initialized with virtual scrollbar from BaseTableManager');
    }

    /**
     * Load saved filter state from localStorage
     */
    loadSavedFilterState() {
        if (typeof window.KTGlobalFilter !== 'undefined') {
            const savedState = window.KTGlobalFilter.loadFilterState('orders');
            if (savedState) {
                // Merge saved state with current filters
                this.currentFilters = { ...this.currentFilters, ...savedState };
                console.log('Loaded saved filter state:', this.currentFilters);
            }
        }
    }

    /**
     * Save current filter state to localStorage
     */
    saveFilterState() {
        if (typeof window.KTGlobalFilter !== 'undefined') {
            window.KTGlobalFilter.saveFilterState('orders', this.currentFilters);
        }
    }

    /**
     * Initialize Reset Filters button
     */
    initResetFilters() {
        const resetBtn = document.getElementById('reset_filters_btn');
        if (resetBtn) {
            resetBtn.addEventListener('click', () => {
                console.log('Resetting filters...');

                // Clear localStorage
                if (typeof window.KTGlobalFilter !== 'undefined') {
                    window.KTGlobalFilter.clearFilterState('orders');
                }

                // Reset to default filters
                this.currentFilters = { ...this.config.defaultFilters };

                // Reset UI elements
                this.resetFilterUI();

                // Reload data
                this.loadData();
            });
            console.log('Reset filters button initialized');
        }
    }

    /**
     * Reset all filter UI elements to default state
     */
    resetFilterUI() {
        // Reset search
        const searchInput = document.querySelector('input[data-kt-orders-table-filter="search"]');
        if (searchInput) searchInput.value = '';

        // Reset time filter to "Tháng này"
        const thisMonthRadio = document.querySelector('input[name="time_filter_display"][value="this_month"]');
        if (thisMonthRadio) thisMonthRadio.checked = true;

        // Reset date inputs
        const dateFrom = document.getElementById('date_from');
        const dateTo = document.getElementById('date_to');
        if (dateFrom) dateFrom.value = '';
        if (dateTo) dateTo.value = '';

        // Reset delivery time filter
        const deliveryAllRadio = document.querySelector('input[name="delivery_time_filter"][value="all"]');
        if (deliveryAllRadio) deliveryAllRadio.checked = true;

        // Reset delivery date inputs
        const deliveryDateFrom = document.getElementById('delivery_date_from');
        const deliveryDateTo = document.getElementById('delivery_date_to');
        if (deliveryDateFrom) deliveryDateFrom.value = '';
        if (deliveryDateTo) deliveryDateTo.value = '';

        // Reset all Select2 filters
        $('#status_filter, #delivery_status_filter, #creator_filter, #seller_filter, #sale_channel_filter, #payment_method_filter').val(null).trigger('change');

        console.log('Filter UI reset to default state');
    }

    // ===== ORDER-SPECIFIC FUNCTIONALITY =====

    /**
     * Create border spans for detail panel visual separation
     */
    createBorderSpans() {
        const tableContainer = document.getElementById('kt_orders_table_container');
        if (!tableContainer) return;

        // Create top border span
        const topBorderSpan = document.createElement('div');
        topBorderSpan.className = 'detail-panel-border-top';
        topBorderSpan.style.cssText = `
            position: absolute;
            left: 0;
            right: 0;
            height: 2px;
            background: #e4e6ea;
            z-index: 5;
            display: none;
        `;

        // Create bottom border span
        const bottomBorderSpan = document.createElement('div');
        bottomBorderSpan.className = 'detail-panel-border-bottom';
        bottomBorderSpan.style.cssText = `
            position: absolute;
            left: 0;
            right: 0;
            height: 2px;
            background: #e4e6ea;
            z-index: 5;
            display: none;
        `;

        tableContainer.appendChild(topBorderSpan);
        tableContainer.appendChild(bottomBorderSpan);

        console.log('Border spans created for detail panels');
    }

    /**
     * Get status badge HTML for order status
     */
    getStatusBadge(status) {
        const badges = {
            'draft': '<span class="badge badge-light-secondary">Nháp</span>',
            'pending': '<span class="badge badge-light-warning">Chờ xử lý</span>',
            'processing': '<span class="badge badge-light-primary">Đang xử lý</span>',
            'completed': '<span class="badge badge-light-success">Hoàn thành</span>',
            'cancelled': '<span class="badge badge-light-danger">Đã hủy</span>',
            'refunded': '<span class="badge badge-light-info">Đã hoàn tiền</span>'
        };
        return badges[status] || '<span class="badge badge-light">N/A</span>';
    }

    /**
     * Get delivery status badge HTML
     */
    getDeliveryStatusBadge(status) {
        const badges = {
            'pending': '<span class="badge badge-light-warning">Chờ giao</span>',
            'shipping': '<span class="badge badge-light-primary">Đang giao</span>',
            'delivered': '<span class="badge badge-light-success">Đã giao</span>',
            'failed': '<span class="badge badge-light-danger">Giao thất bại</span>',
            'returned': '<span class="badge badge-light-info">Đã trả lại</span>'
        };
        return badges[status] || '<span class="badge badge-light">N/A</span>';
    }

    /**
     * Get payment status badge HTML
     */
    getPaymentStatusBadge(status) {
        const badges = {
            'pending': '<span class="badge badge-light-warning">Chờ thanh toán</span>',
            'paid': '<span class="badge badge-light-success">Đã thanh toán</span>',
            'unpaid': '<span class="badge badge-light-secondary">Chưa thanh toán</span>',
            'partial': '<span class="badge badge-light-primary">Thanh toán một phần</span>',
            'refunded': '<span class="badge badge-light-info">Đã hoàn tiền</span>',
            'failed': '<span class="badge badge-light-danger">Thanh toán thất bại</span>'
        };
        return badges[status] || '<span class="badge badge-light">N/A</span>';
    }

    /**
     * Get sales channel label
     */
    getSalesChannelLabel(channel) {
        const labels = {
            'direct': 'Direct',
            'store': 'Bán tại cửa hàng',
            'online': 'Bán online',
            'phone': 'Phone',
            'social': 'Social'
        };
        return labels[channel] || channel || 'N/A';
    }

    /**
     * Format currency for display
     */
    formatCurrency(amount) {
        if (!amount) return '0 ₫';
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(amount);
    }

    /**
     * Format date for display
     * Uses global DateUtils for consistent date formatting
     */
    formatDate(dateString) {
        if (!dateString) return 'N/A';

        // Use DateUtils if available, otherwise fallback to basic formatting
        if (window.DateUtils && typeof window.DateUtils.formatDateTime === 'function') {
            const formatted = window.DateUtils.formatDateTime(dateString);
            // formatDateTime returns an object {date, time, full}, we need the full string
            return formatted.full || 'N/A';
        }

        // Fallback: basic date formatting with validation
        try {
            const date = new Date(dateString);
            if (isNaN(date.getTime())) {
                console.warn('OrderManager: Invalid date string:', dateString);
                return 'N/A';
            }
            return date.toLocaleDateString('vi-VN', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit'
            });
        } catch (error) {
            console.error('OrderManager: Error formatting date:', dateString, error);
            return 'N/A';
        }
    }

    // ===== REQUIRED IMPLEMENTATIONS FROM BaseTableManager =====

    /**
     * Initialize search functionality
     */
    initSearch() {
        const searchInput = document.querySelector('#kt_orders_search');
        if (!searchInput) return;

        let searchTimeout;

        searchInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                this.currentFilters.search = e.target.value;
                this.currentFilters.page = 1; // Reset to first page
                this.loadData();
            }, 500); // Debounce 500ms
        });

        console.log('Orders search initialized');
    }



    /**
     * Initialize column visibility functionality
     */
    initColumnVisibility() {
        // Initialize column visibility with KTColumnVisibility
        if (typeof window.KTColumnVisibility !== 'undefined') {
            const defaultVisibility = {
                0: true,  // Checkbox
                1: true,  // Mã đơn hàng
                2: true,  // Khách hàng
                3: true,  // Tổng tiền
                4: true,  // Đã thanh toán
                5: true,  // Trạng thái
                6: true,  // TT Thanh toán
                7: true,  // TT Giao hàng
                8: true,  // Kênh bán
                9: true,  // Ngày tạo
                10: true, // Người bán
                11: true, // Người tạo
                12: false, // Email (hidden by default)
                13: true  // Chi nhánh
            };

            this.columnVisibility = window.KTColumnVisibility.init({
                storageKey: 'orders_column_visibility',
                defaultVisibility: defaultVisibility,
                triggerSelector: '#column_visibility_trigger',
                panelSelector: '#column_visibility_panel',
                toggleSelector: '.column-toggle',
                tableSelector: '#kt_orders_table',
                onToggle: function(columnIndex, isVisible) {
                    console.log('Orders column visibility changed:', columnIndex, isVisible);
                }
            });
        } else {
            console.warn('KTColumnVisibility not available');
        }

        console.log('Orders column visibility initialized');
    }

    /**
     * Initialize select all functionality
     */
    initSelectAll() {
        const selectAllCheckbox = document.querySelector('#kt_orders_select_all');
        if (!selectAllCheckbox) return;

        selectAllCheckbox.addEventListener('change', (e) => {
            const isChecked = e.target.checked;
            const rowCheckboxes = this.getRowCheckboxes();

            rowCheckboxes.forEach(checkbox => {
                checkbox.checked = isChecked;
                const orderId = checkbox.value;
                if (isChecked) {
                    this.selectedItems.add(orderId);
                } else {
                    this.selectedItems.delete(orderId);
                }
            });

            this.updateBulkActionButtons();
        });

        console.log('Orders select all initialized');
    }

    /**
     * Initialize bulk actions functionality
     */
    initBulkActions() {
        // Bulk action buttons will be handled by specific event listeners
        console.log('Orders bulk actions initialized');
    }

    /**
     * Get select all checkbox ID
     */
    getSelectAllId() {
        return 'kt_orders_select_all';
    }

    /**
     * Get row checkboxes
     */
    getRowCheckboxes() {
        return document.querySelectorAll('tbody input[type="checkbox"]');
    }

    /**
     * Get item name for confirmation messages
     */
    getItemName() {
        return 'đơn hàng';
    }

    /**
     * Update bulk action buttons state
     */
    updateBulkActionButtons() {
        const selectedCount = this.selectedItems.size;
        const bulkActionButtons = document.querySelectorAll('.bulk-action-btn');

        bulkActionButtons.forEach(btn => {
            btn.disabled = selectedCount === 0;
            if (selectedCount > 0) {
                btn.textContent = btn.textContent.replace(/\(\d+\)/, `(${selectedCount})`);
            }
        });
    }

    /**
     * Update select all checkbox state
     */
    updateSelectAllState() {
        const selectAllCheckbox = document.querySelector('#kt_orders_select_all');
        if (!selectAllCheckbox) return;

        const rowCheckboxes = this.getRowCheckboxes();
        const checkedCount = Array.from(rowCheckboxes).filter(cb => cb.checked).length;

        if (checkedCount === 0) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
        } else if (checkedCount === rowCheckboxes.length) {
            selectAllCheckbox.checked = true;
            selectAllCheckbox.indeterminate = false;
        } else {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = true;
        }
    }

    /**
     * Load orders data via AJAX
     */
    /**
     * Read current filter values from form
     */
    readFiltersFromForm() {
        const form = document.getElementById('kt_orders_filter_form');
        if (!form) return;

        // Read time filter
        const timeFilter = document.getElementById('time_filter');
        if (timeFilter) {
            this.currentFilters.time_filter_display = timeFilter.value || 'this_month';
        }

        // Read custom date range
        const dateFrom = document.getElementById('date_from');
        const dateTo = document.getElementById('date_to');
        if (dateFrom) this.currentFilters.date_from = dateFrom.value || '';
        if (dateTo) this.currentFilters.date_to = dateTo.value || '';

        // Read status checkboxes
        const statusCheckboxes = form.querySelectorAll('input[name="status[]"]:checked');
        this.currentFilters.status = Array.from(statusCheckboxes).map(cb => cb.value);

        // Read delivery status checkboxes
        const deliveryStatusCheckboxes = form.querySelectorAll('input[type="checkbox"][id^="delivery_"]:checked');
        this.currentFilters.delivery_status = Array.from(deliveryStatusCheckboxes).map(cb => cb.value).join(',');

        // Read creator filter (Select2)
        const creatorSelect = form.querySelector('select[name="creator_id"]');
        if (creatorSelect) {
            const creatorValue = $(creatorSelect).val();
            this.currentFilters.created_by = Array.isArray(creatorValue) ? creatorValue.join(',') : (creatorValue || '');
        }

        // Read seller filter (Select2)
        const sellerSelect = form.querySelector('select[name="seller_id"]');
        if (sellerSelect) {
            const sellerValue = $(sellerSelect).val();
            this.currentFilters.sold_by = Array.isArray(sellerValue) ? sellerValue.join(',') : (sellerValue || '');
        }

        // Read sales channel filter (Select2)
        const channelSelect = form.querySelector('select[name="sales_channel"]');
        if (channelSelect) {
            const channelValue = $(channelSelect).val();
            this.currentFilters.sale_channel = Array.isArray(channelValue) ? channelValue.join(',') : (channelValue || '');
        }

        // Read delivery time filter
        const deliveryTimeFilter = form.querySelector('input[name="delivery_time_filter"]:checked');
        if (deliveryTimeFilter) {
            this.currentFilters.delivery_time_filter = deliveryTimeFilter.value || 'all';
        }

        console.log('Filters read from form:', this.currentFilters);
    }

    loadData() {
        // Read current filter values from form before loading
        this.readFiltersFromForm();

        // Save current filter state
        this.saveFilterState();

        // Cancel previous request if exists
        if (this.currentRequest) {
            this.currentRequest.abort();
        }

        // Show loading state
        this.setLoadingState('table', true);
        this.renderLoading();

        // Build request parameters
        const params = {
            page: this.currentFilters.page || 1,
            per_page: this.currentFilters.per_page || this.config.defaultPerPage,
            length: this.currentFilters.per_page || this.config.defaultPerPage, // For DataTables compatibility
            search: this.currentFilters.search || '',
            time_filter_display: this.currentFilters.time_filter_display || 'this_month',
            date_from: this.currentFilters.date_from || '',
            date_to: this.currentFilters.date_to || '',
            status: Array.isArray(this.currentFilters.status) ? this.currentFilters.status.join(',') : this.currentFilters.status,
            delivery_status: this.currentFilters.delivery_status || '',
            creator_id: this.currentFilters.created_by || '',
            seller_id: this.currentFilters.sold_by || '',
            sales_channel: this.currentFilters.sale_channel || '',
            payment_method: this.currentFilters.payment_method || '',
            delivery_time_filter: this.currentFilters.delivery_time_filter || 'all'
        };

        console.log('Loading orders with params:', params);

        // Make AJAX request
        this.currentRequest = fetch(`${this.config.ajaxUrl}?${new URLSearchParams(params)}`)
            .then(response => response.json())
            .then(data => {
                if (data.success || data.data) {
                    this.lastResponseData = {
                        recordsTotal: data.recordsTotal || data.pagination?.total || 0,
                        recordsFiltered: data.recordsFiltered || data.pagination?.total || 0,
                        data: data.data || []
                    };
                    this.renderData(this.lastResponseData.data);
                    this.updatePagination(this.lastResponseData);
                } else {
                    console.error('Failed to load orders:', data.message);
                    this.renderError(data.message || 'Lỗi tải dữ liệu');
                }
            })
            .catch(error => {
                if (error.name !== 'AbortError') {
                    console.error('Error loading orders:', error);
                    this.renderError('Lỗi kết nối. Vui lòng thử lại.');
                }
            })
            .finally(() => {
                this.setLoadingState('table', false);
                this.currentRequest = null;
            });
    }

    /**
     * Render orders data in table
     */
    renderData(orders) {
        console.log('renderData called with orders:', orders.length);
        const tbody = this.table.querySelector('tbody');

        if (!orders || orders.length === 0) {
            tbody.innerHTML = '<tr><td colspan="14" class="text-center">Không có dữ liệu</td></tr>';
            return;
        }

        const rows = orders.map(order => this.renderOrderRow(order)).join('');
        console.log('Generated rows HTML length:', rows.length);
        tbody.innerHTML = rows;

        // Bind row events
        this.bindRowEvents();

        // Update virtual scrollbar after data is rendered
        setTimeout(() => {
            this.updateVirtualScrollbar();
        }, 100);
    }

    /**
     * Render single order row
     */
    renderOrderRow(order) {
        console.log('renderOrderRow called with order:', order.id, order.order_code);
        return `
            <tr class="order-row" data-order-id="${order.id}">
                <td>
                    <div class="form-check form-check-sm form-check-custom form-check-solid">
                        <input class="form-check-input" type="checkbox" value="${order.id}" />
                    </div>
                </td>
                <td>${order.order_code || 'N/A'}</td>
                <td>
                    <div class="d-flex flex-column">
                        <span class="text-gray-800 fw-bold">${order.customer_name || 'Khách lẻ'}</span>
                        ${order.customer_phone ? `<span class="text-muted fs-7">${order.customer_phone}</span>` : ''}
                    </div>
                </td>
                <td class="text-end">${order.total_amount_formatted || this.formatCurrency(order.total_amount)}</td>
                <td class="text-end">${order.paid_amount_formatted || this.formatCurrency(order.paid_amount || 0)}</td>
                <td>${order.status_label || this.getStatusBadge(order.status)}</td>
                <td>${order.payment_status_label || this.getPaymentStatusBadge(order.payment_status)}</td>
                <td>${order.delivery_status_label || this.getDeliveryStatusBadge(order.delivery_status)}</td>
                <td>${order.sales_channel_label || this.getSalesChannelLabel(order.sales_channel)}</td>
                <td>${this.formatDate(order.created_at)}</td>
                <td>${order.seller_name || 'N/A'}</td>
                <td>${order.creator_name || 'N/A'}</td>
                <td>${order.customer_email || ''}</td>
                <td>${order.branch_shop_name || 'N/A'}</td>
            </tr>
        `;
    }

    /**
     * Bind row events
     */
    bindRowEvents() {
        // Row click events for detail expansion
        this.table.querySelectorAll('.order-row').forEach(row => {
            row.addEventListener('click', (e) => {
                if (e.target.type === 'checkbox' || e.target.closest('.btn')) return;

                const orderId = row.dataset.orderId;
                this.handleRowClick(row, orderId);
            });
        });

        // Checkbox events
        this.initIndividualCheckboxes();
    }

    /**
     * Handle row click to show order details
     */
    handleRowClick(row, orderId) {
        console.log('Row clicked, order ID:', orderId);
        if (orderId) {
            console.log('About to call toggleRowExpansion');
            this.toggleRowExpansion($(row), orderId);
        }
    }

    /**
     * Toggle row expansion to show/hide detail panel (adapted from invoice-manager.js)
     */
    toggleRowExpansion($row, orderId) {
        console.log('Toggling row expansion for order:', orderId);

        // Close any other open detail rows first
        $('.kt-table-detail-row:visible').each(function() {
            const $openRow = $(this);
            $openRow.slideUp(300, function() {
                $openRow.remove();
            });
        });

        // Clean up border elements when closing rows
        this.cleanupBorderElements();

        // Remove expanded class from all rows
        $('.order-row').removeClass('expanded kt-table-row-active');

        // Check if this row is already expanded
        const $existingDetailRow = $row.next('.kt-table-detail-row');
        if ($existingDetailRow.length) {
            console.log('Row already expanded, closing...');
            $existingDetailRow.slideUp(300, function() {
                $existingDetailRow.remove();
            });
            $row.removeClass('expanded kt-table-row-active');
            return;
        }

        // Expand this row
        console.log('Expanding row for order:', orderId);
        $row.addClass('expanded kt-table-row-active');

        // Create placeholder row with width matching table container
        const columnCount = $row.find('td').length;

        // Get the table container width to match detail panel width
        const tableContainer = $('#kt_orders_table_container');
        const containerWidth = tableContainer.width();

        const $detailRow = $(`
            <tr class="kt-table-detail-row" style="display: none;">
                <td colspan="${columnCount}" class="kt-table-detail-row-td p-0">
                    <div class="kt-table-detail-container p-5" style="width: ${containerWidth}px; max-width: ${containerWidth}px; overflow: visible; position: relative;">
                        <div class="kt-table-detail-border-left"></div>
                        <div class="kt-table-detail-border-right"></div>
                        <div class="loading-placeholder p-4 text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <div class="mt-2">Đang tải thông tin đơn hàng...</div>
                        </div>
                    </div>
                </td>
            </tr>
        `);

        // Insert detail row after clicked row
        $row.after($detailRow);

        // Show detail row with animation
        $detailRow.slideDown(300, () => {
            // Load order detail content
            this.loadOrderDetail(orderId, $detailRow, $row);
        });
    }

    /**
     * Load order detail content via AJAX (adapted from invoice-manager.js)
     */
    loadOrderDetail(orderId, $detailRow, $clickedRow) {
        console.log('Loading order detail for ID:', orderId);

        $.ajax({
            url: `/admin/orders/detail/${orderId}`,
            type: 'GET',
            success: (response) => {
                try {
                    console.log('Order detail loaded successfully');
                    console.log('Response type:', typeof response);
                    console.log('Response data:', response);

                    // Check if response has html property (JSON response) or is HTML directly
                    const htmlContent = response.html || response;
                    console.log('HTML content type:', typeof htmlContent);
                    console.log('HTML content length:', htmlContent ? htmlContent.length : 'null');

                    // Replace loading placeholder with actual content
                    $detailRow.find('.loading-placeholder').replaceWith(htmlContent);

                    // Get table container width and apply to detail container
                    const tableContainer = $('#kt_orders_table_container');
                    const containerWidth = tableContainer.width();

                    if (containerWidth) {
                        $detailRow.find('.kt-table-detail-container').css({
                            'width': containerWidth + 'px',
                            'max-width': containerWidth + 'px',
                            'overflow': 'visible',
                            'box-sizing': 'border-box'
                        });
                    }

                    // Update border spans position and height based on clicked row
                    this.updateBorderSpansPosition($clickedRow, $detailRow);

                    // Initialize any JavaScript components in the detail panel
                    this.initDetailPanelComponents($detailRow);
                } catch (error) {
                    console.error('Error in success callback:', error);
                    console.error('Error stack:', error.stack);

                    const errorHtml = `
                        <div class="alert alert-danger m-4">
                            <h5>Lỗi xử lý dữ liệu đơn hàng</h5>
                            <p>Có lỗi xảy ra khi xử lý dữ liệu. Vui lòng thử lại sau.</p>
                            <small>Error: ${error.message}</small>
                        </div>
                    `;
                    $detailRow.find('.loading-placeholder').replaceWith(errorHtml);
                }
            },
            error: (xhr, status, error) => {
                console.error('Error loading order detail:', error);
                console.error('XHR status:', status);
                console.error('XHR response:', xhr.responseText);
                console.error('XHR status code:', xhr.status);

                const errorHtml = `
                    <div class="alert alert-danger m-4">
                        <h5>Lỗi tải thông tin đơn hàng</h5>
                        <p>Không thể tải thông tin chi tiết đơn hàng. Vui lòng thử lại sau.</p>
                        <small>Error: ${error} (Status: ${xhr.status})</small>
                    </div>
                `;

                $detailRow.find('.loading-placeholder').replaceWith(errorHtml);
            }
        });
    }

    /**
     * Initialize individual checkbox handling
     */
    initIndividualCheckboxes() {
        const checkboxes = this.table.querySelectorAll('tbody input[type="checkbox"]');
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', (e) => {
                const orderId = e.target.value;
                if (e.target.checked) {
                    this.selectedItems.add(orderId);
                } else {
                    this.selectedItems.delete(orderId);
                }
                this.updateSelectAllState();
            });
        });
    }

    /**
     * Render loading state with spinner (similar to invoices)
     */
    renderLoading() {
        const tbody = this.table.querySelector('tbody');
        tbody.innerHTML = `
            <tr>
                <td colspan="14" class="text-center py-10">
                    <div class="d-flex flex-column align-items-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Đang tải...</span>
                        </div>
                        <div class="mt-3 text-muted">Đang tải dữ liệu...</div>
                    </div>
                </td>
            </tr>
        `;
    }

    /**
     * Render error state
     */
    renderError(message) {
        const tbody = this.table.querySelector('tbody');
        tbody.innerHTML = `<tr><td colspan="14" class="text-center text-danger">${message}</td></tr>`;
    }

    /**
     * Set loading state for components
     */
    setLoadingState(component, isLoading) {
        if (component === 'table') {
            const tableContainer = this.tableContainer;
            if (isLoading) {
                tableContainer.classList.add('loading');
            } else {
                tableContainer.classList.remove('loading');
            }
        }
    }

    // ===== INHERITED METHODS FROM BaseTableManager =====

    /**
     * Initialize scroll indicators
     */
    initScrollIndicators() {
        console.log('Initializing scroll indicators...');

        // Add scroll event listener
        this.tableContainer.addEventListener('scroll', (e) => {
            this.handleScroll(e);
        });

        // Initial scroll state check
        this.updateScrollIndicators();
    }

    /**
     * Initialize responsive handlers
     */
    initResponsiveHandlers() {
        console.log('Initializing responsive handlers...');

        // Add resize event listener
        window.addEventListener('resize', (e) => {
            this.handleResize(e);
        });

        // Initial responsive check
        this.updateResponsiveState();
    }

    /**
     * Initialize pagination
     */
    initPagination() {
        console.log('Initializing pagination...');

        // Load saved per page setting
        const savedPerPage = localStorage.getItem(`${this.config.module}_per_page`);
        if (savedPerPage) {
            this.currentFilters.per_page = parseInt(savedPerPage);
            console.log(`Loaded per page state for ${this.config.module}:`, savedPerPage);
        }

        // Set the dropdown value to match current per_page
        const perPageSelect = document.querySelector('#kt_orders_per_page');
        if (perPageSelect) {
            perPageSelect.value = this.currentFilters.per_page;

            // Add change event listener
            perPageSelect.addEventListener('change', (e) => {
                const newPerPage = parseInt(e.target.value);
                console.log('Per page changed to:', newPerPage);

                this.currentFilters.per_page = newPerPage;
                this.currentFilters.page = 1; // Reset to first page

                // Save to localStorage
                localStorage.setItem(`${this.config.module}_per_page`, newPerPage);

                // Reload data
                this.loadData();
            });
        }
    }

    /**
     * Initialize virtual scrollbar
     */
    initVirtualScrollbar() {
        // Call parent class method to initialize virtual scrollbar
        super.initVirtualScrollbar();
        console.log('Virtual scrollbar initialized');
    }

    /**
     * Update virtual scrollbar
     */
    updateVirtualScrollbar() {
        // Call parent class method to update virtual scrollbar
        super.updateVirtualScrollbar();
    }

    /**
     * Handle resize events
     */
    handleResize(e) {
        // Handle resize
    }

    /**
     * Update scroll indicators
     */
    updateScrollIndicators() {
        // Update scroll indicators
    }

    /**
     * Update responsive state
     */
    updateResponsiveState() {
        // Update responsive state
    }

    /**
     * Update virtual scrollbar
     */
    updateVirtualScrollbar() {
        // Update virtual scrollbar
    }

    /**
     * Update pagination display
     */
    updatePagination(responseData) {
        console.log('updatePagination called with:', responseData);

        if (!responseData) {
            console.warn('No response data for pagination');
            return;
        }

        // Update pagination info text
        const paginationInfo = document.querySelector('#kt_orders_table_info');
        if (paginationInfo) {
            const start = ((this.currentFilters.page - 1) * this.currentFilters.per_page) + 1;
            const end = Math.min(start + responseData.data.length - 1, responseData.recordsFiltered);
            const total = responseData.recordsFiltered;

            paginationInfo.textContent = `Hiển thị ${start} đến ${end} của ${total} kết quả`;
        }

        // Render pagination buttons
        this.renderPagination(responseData);
    }

    /**
     * Render pagination buttons (Previous, page numbers, Next)
     */
    renderPagination(responseData) {
        const paginationContainer = document.querySelector('#kt_orders_table_pagination');
        if (!paginationContainer) {
            console.warn('Pagination container not found');
            return;
        }

        const totalPages = Math.ceil(responseData.recordsFiltered / this.currentFilters.per_page);
        let paginationHtml = '';

        if (totalPages > 1) {
            // Previous button
            if (this.currentFilters.page > 1) {
                paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="${this.currentFilters.page - 1}">Trước</a></li>`;
            }

            // Page numbers
            const startPage = Math.max(1, this.currentFilters.page - 2);
            const endPage = Math.min(totalPages, this.currentFilters.page + 2);

            for (let i = startPage; i <= endPage; i++) {
                const activeClass = i === this.currentFilters.page ? 'active' : '';
                paginationHtml += `<li class="page-item ${activeClass}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
            }

            // Next button
            if (this.currentFilters.page < totalPages) {
                paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="${this.currentFilters.page + 1}">Tiếp</a></li>`;
            }
        }

        paginationContainer.innerHTML = paginationHtml;

        // Add click handlers for pagination links
        paginationContainer.querySelectorAll('.page-link').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const page = parseInt(link.getAttribute('data-page'));
                if (page && page !== this.currentFilters.page) {
                    this.currentFilters.page = page;
                    this.loadData();
                }
            });
        });
    }

    /**
     * Update select all checkbox state
     */
    updateSelectAllState() {
        const selectAllCheckbox = document.querySelector('#kt_orders_select_all');
        if (!selectAllCheckbox) return;

        const checkboxes = this.table.querySelectorAll('tbody input[type="checkbox"]');
        const checkedCheckboxes = this.table.querySelectorAll('tbody input[type="checkbox"]:checked');

        if (checkboxes.length === 0) {
            selectAllCheckbox.indeterminate = false;
            selectAllCheckbox.checked = false;
        } else if (checkedCheckboxes.length === checkboxes.length) {
            selectAllCheckbox.indeterminate = false;
            selectAllCheckbox.checked = true;
        } else if (checkedCheckboxes.length > 0) {
            selectAllCheckbox.indeterminate = true;
            selectAllCheckbox.checked = false;
        } else {
            selectAllCheckbox.indeterminate = false;
            selectAllCheckbox.checked = false;
        }
    }

    /**
     * Initialize components in detail panel (adapted from invoice-manager.js)
     */
    initDetailPanelComponents($detailRow) {
        console.log('Initializing detail panel components');

        // Initialize Bootstrap tabs
        console.log('Binding tab click handler to elements:', $detailRow.find('a[data-bs-toggle="tab"]').length);
        $detailRow.find('a[data-bs-toggle="tab"]').off('click.detailTab').on('click.detailTab', function(e) {
            console.log('OUR tab click handler executing!');
            e.preventDefault();
            e.stopPropagation();

            const $this = $(this);
            const target = $this.attr('href');

            // Remove active class from all tabs
            $this.closest('.nav-tabs').find('.nav-link').removeClass('active');
            $this.addClass('active');

            // Hide all tab panes
            $this.closest('.card-body').find('.tab-pane').removeClass('show active');

            // Show target tab pane
            $(target).addClass('show active');

            console.log('Detail panel tab clicked:', target);
            console.log('Tab click handler executing, this context:', this);
            console.log('updateBorderSpansPosition method exists:', typeof this.updateBorderSpansPosition);

            // Update border spans position after tab switch (height may change)
            try {
                console.log('About to update border spans after tab switch to:', target);
                this.updateBorderSpansPosition(null, $detailRow);
                console.log('Border spans updated after tab switch to:', target);
            } catch (error) {
                console.error('Error updating border spans after tab switch:', error);
            }
        }.bind(this));

        // Initialize any other components as needed
        // e.g., tooltips, popovers, etc.
    }

    /**
     * Update border spans position (adapted from invoice-manager.js)
     */
    updateBorderSpansPosition($clickedRow, $detailRow) {
        setTimeout(() => {
            // Find the active order row with expanded class
            const $activeRow = $('.order-row.expanded.kt-table-row-active');

            if ($activeRow.length === 0) {
                console.log('No active expanded order row found');
                return;
            }

            // Find border elements within the detail row
            const $borderLeft = $detailRow.find('.kt-table-detail-border-left');
            const $borderRight = $detailRow.find('.kt-table-detail-border-right');

            if ($borderLeft.length === 0 || $borderRight.length === 0) {
                console.log('Border elements not found in detail row');
                return;
            }

            // Store border elements for scroll updates
            this.$activeBorderLeft = $borderLeft;
            this.$activeBorderRight = $borderRight;
            this.$activeDetailRow = $detailRow;

            // Initial positioning
            this.updateBorderElementsPosition();

            // Setup horizontal scroll listener for table container
            this.setupBorderScrollListener();

            console.log('Border elements initialized and scroll listener setup');
        }, 100);
    }

    /**
     * Update border elements position based on current scroll and active row
     */
    updateBorderElementsPosition() {
        if (!this.$activeBorderLeft || !this.$activeBorderRight || !this.$activeDetailRow) {
            return;
        }

        // Find the active order row with expanded class
        const $activeRow = $('.order-row.expanded.kt-table-row-active');
        if ($activeRow.length === 0) {
            return;
        }

        // Get active row height
        const activeRowHeight = $activeRow.outerHeight();

        // Set top position to negative height of active row
        const topPosition = -activeRowHeight;

        // Get order detail panel height
        const $detailPanel = this.$activeDetailRow.find('.kt-table-detail-container');
        const detailPanelHeight = $detailPanel.outerHeight();
        const totalHeight = activeRowHeight + detailPanelHeight;

        // Get table container scroll position
        const $tableContainer = $('#kt_orders_table_container');
        const scrollLeft = $tableContainer.scrollLeft();

        // Calculate border positions based on scroll
        const leftPosition = scrollLeft;
        const rightPosition = scrollLeft;

        // Position border elements with updated left/right based on scroll
        this.$activeBorderLeft.css({
            'position': 'absolute',
            'top': topPosition + 'px',
            'left': leftPosition + 'px',
            'width': '2px',
            'height': totalHeight + 'px',
            'background': '#e4e6ea',
            'z-index': '5',
            'display': 'block'
        });

        this.$activeBorderRight.css({
            'position': 'absolute',
            'top': topPosition + 'px',
            'right': -rightPosition + 'px', // Negative to move with scroll
            'width': '2px',
            'height': totalHeight + 'px',
            'background': '#e4e6ea',
            'z-index': '5',
            'display': 'block'
        });

        console.log('Border elements position updated:', {
            scrollLeft: scrollLeft,
            leftPosition: leftPosition,
            rightPosition: -rightPosition,
            topPosition: topPosition,
            totalHeight: totalHeight
        });
    }

    /**
     * Setup scroll listener for horizontal table scroll
     */
    setupBorderScrollListener() {
        const $tableContainer = $('#kt_orders_table_container');

        // Remove existing listener to prevent duplicates
        $tableContainer.off('scroll.borderUpdate');

        // Add scroll listener
        $tableContainer.on('scroll.borderUpdate', () => {
            this.updateBorderElementsPosition();
        });

        console.log('Border scroll listener setup for table container');
    }

    /**
     * Clean up border elements and listeners when row is collapsed
     */
    cleanupBorderElements() {
        // Remove scroll listener
        $('#kt_orders_table_container').off('scroll.borderUpdate');

        // Clear stored elements
        this.$activeBorderLeft = null;
        this.$activeBorderRight = null;
        this.$activeDetailRow = null;

        console.log('Border elements cleaned up');
    }

    // Virtual scrollbar functionality is now inherited from BaseTableManager
}

// Export for use
window.OrderTableManager = OrderTableManager;