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
                status: ['processing', 'completed'],
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
        // Call parent init first
        super.init();

        // Create border spans for detail panel visual separation
        this.createBorderSpans();

        // Add scroll listener to update border spans position
        const container = document.getElementById('kt_orders_table_container');
        if (container) {
            container.addEventListener('scroll', () => {
                this.updateBorderSpansPosition();
            });
        }

        // Add window resize listener to update detail panel widths
        $(window).on('resize.orderDetailPanels', () => {
            this.updateDetailPanelWidths();
            this.updateBorderSpansPosition();
        });

        // Initialize individual checkbox handling
        this.initIndividualCheckboxes();

        console.log('Order detail panel functionality initialized');
    }
    
    initSearch() {
        console.log('Initializing order search...');

        const searchInput = document.getElementById('kt_orders_search');
        if (searchInput) {
            // Initialize enhanced search
            this.initEnhancedSearch();

            let searchTimeout;

            searchInput.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    this.currentFilters.search = e.target.value.trim();
                    this.currentFilters.page = 1; // Reset to first page
                    this.showLoading('search');
                    this.loadData();
                }, 300);
            });
        }
    }
    
    initColumnVisibility() {
        console.log('Initializing column visibility...');

        // Use global column visibility system
        if (typeof window.KTColumnVisibility !== 'undefined' && window.KTColumnVisibility.init) {
            window.KTColumnVisibility.init({
                storageKey: this.config.storageKey,
                defaultVisibility: {
                    0: true, 1: true, 2: true, 3: true, 4: true, 5: true, 6: true,
                    7: true, 8: true, 9: true, 10: true, 11: true, 12: true, 13: true
                }
            });
        }
    }
    
    initSelectAll() {
        console.log('Initializing select all functionality...');

        const selectAllCheckbox = document.getElementById('kt_orders_select_all');
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', (e) => {
                const isChecked = e.target.checked;
                // Use correct selector for order checkboxes in table body
                const checkboxes = document.querySelectorAll('#kt_orders_table tbody input[type="checkbox"]');

                checkboxes.forEach(checkbox => {
                    checkbox.checked = isChecked;
                    const row = checkbox.closest('tr');
                    const orderId = row ? row.dataset.orderId || checkbox.value : checkbox.value;

                    if (isChecked) {
                        this.selectedItems.add(orderId);
                        // Add selected class to row
                        if (row) {
                            row.classList.add('selected');
                        }
                    } else {
                        this.selectedItems.delete(orderId);
                        // Remove selected class from row
                        if (row) {
                            row.classList.remove('selected');
                        }
                    }
                });

                this.updateBulkActionsVisibility();
                this.updateSelectedCount();
            });
        }
    }
    
    initBulkActions() {
        console.log('Initializing bulk actions...');

        // Bulk delete
        const bulkDeleteBtn = document.getElementById('bulk-delete-btn');
        if (bulkDeleteBtn) {
            bulkDeleteBtn.addEventListener('click', () => {
                if (this.selectedItems.size === 0) return;

                if (confirm(`Bạn có chắc chắn muốn xóa ${this.selectedItems.size} đơn hàng đã chọn?`)) {
                    this.bulkDelete();
                }
            });
        }
        
        // Bulk status update
        const bulkStatusBtn = document.getElementById('bulk-status-btn');
        if (bulkStatusBtn) {
            bulkStatusBtn.addEventListener('click', () => {
                if (this.selectedOrders.size === 0) return;
                this.showBulkStatusModal();
            });
        }
        
        // Bulk export
        const bulkExportBtn = document.getElementById('bulk-export-btn');
        if (bulkExportBtn) {
            bulkExportBtn.addEventListener('click', () => {
                if (this.selectedOrders.size === 0) return;
                this.bulkExport();
            });
        }
    }
    
    loadData() {
        console.log('Loading orders with filters:', this.currentFilters);

        // Cancel previous request
        if (this.currentRequest) {
            this.currentRequest.abort();
        }

        // Show loading state
        this.showLoading('table', 'Đang tải dữ liệu...');
        this.showLoadingState();

        // Build query string
        const params = new URLSearchParams();

        // Check for URL parameter 'code' or 'Code' and add to request
        const urlParams = new URLSearchParams(window.location.search);
        const codeParam = urlParams.get('code') || urlParams.get('Code');
        if (codeParam) {
            params.append('code', codeParam);
            console.log('Adding code parameter to order request:', codeParam);
        }

        Object.keys(this.currentFilters).forEach(key => {
            const value = this.currentFilters[key];
            if (value !== '' && value !== null && value !== undefined) {
                if (Array.isArray(value)) {
                    params.append(key, value.join(','));
                } else {
                    params.append(key, value);
                }
            }
        });

        const url = `${this.config.ajaxUrl}?${params.toString()}`;
        console.log('Fetching orders from:', url);

        this.currentRequest = fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            console.log('Orders response:', response.status, response.ok);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Orders data received:', data.success, data.data ? data.data.length : 0);

            if (data.success && data.data) {
                this.lastResponseData = data;
                this.renderData(data.data);
                this.updatePagination(data);

                // Auto expand row if Code param exists and only 1 result
                this.handleAutoExpansion(data.data);
            } else {
                console.error('Failed to load orders:', data.message || 'Unknown error');
                this.renderError('Không thể tải dữ liệu đơn hàng');
                this.showErrorToast(data.message || 'Không thể tải dữ liệu đơn hàng');
            }
        })
        .catch(error => {
            console.error('Error loading orders:', error);
            this.renderError('Lỗi kết nối khi tải dữ liệu');
            if (error.name !== 'AbortError') {
                this.showErrorToast('Lỗi kết nối khi tải dữ liệu');
            }
        })
        .finally(() => {
            this.currentRequest = null;
            this.hideLoading('table');
            this.hideLoading('search');
            this.hideLoading('filter');
            this.hideLoading('pagination');
            this.refreshScrollIndicators();
        });
    }
    
    renderData(orders) {
        console.log('Rendering orders:', orders.length);

        // Store current data for debugging
        this.currentData = orders;

        const tbody = this.table.querySelector('tbody');
        if (!tbody) {
            console.error('Table tbody not found');
            return;
        }

        if (orders.length === 0) {
            tbody.innerHTML = '<tr><td colspan="14" class="text-center text-muted">Không có dữ liệu</td></tr>';
            return;
        }

        const rows = orders.map(order => this.renderOrderRow(order)).join('');
        tbody.innerHTML = rows;

        // Apply column visibility
        if (typeof window.KTColumnVisibility !== 'undefined') {
            window.KTColumnVisibility.apply({
                tableSelector: '#kt_orders_table'
            }, this.columnVisibility || {});
        }

        // Bind row events
        this.bindRowEvents();
    }
    
    renderOrderRow(order) {

        const customerName = order.customer_display || order.customer_name || 'Khách lẻ';
        const statusBadge = this.getStatusBadge(order.status);
       
        const salesChannelDisplay = this.getSalesChannelDisplay(order.sales_channel);

        return `
            <tr class="order-row" data-order-id="${order.id}" style="cursor: pointer;">
                <td>
                  <div class="form-check form-check-sm form-check-custom form-check-solid">
                        <input class="form-check-input order-checkbox" type="checkbox" value="${order.id}" />
                    </div>
               
                </td>
                <td>
                    <span class="fw-bold text-gray-800">${order.order_code || 'N/A'}</span>
                </td>
                  <td>
                    <div class="d-flex flex-column">
                        <span class="text-gray-800 mb-1">${customerName}</span>
                    </div>
                </td>
                <td>
                    <span class="fw-bold text-success">${this.formatCurrency(order.total_amount)}</span>
                </td>
                <td>
                    <span class="fw-bold text-primary">${this.formatCurrency(order.paid_amount)}</span>
                </td>
                <td>
                    ${statusBadge}
                </td>
                <td>
                    ${this.getPaymentStatusBadge(order.payment_status)}
                </td>
                <td>
                    ${this.getDeliveryStatusBadge(order.delivery_status)}
                </td>
                <td>
                    <span class="badge badge-light-info">${salesChannelDisplay}</span>
                </td>
                <td>
                    <div class="d-flex flex-column">
                        <span class="text-gray-800 mb-1">${this.formatDate(order.created_at)}</span>
                        <span class="text-muted fs-7">${this.formatTime(order.created_at)}</span>
                    </div>
                </td>
                <td>
                    <span class="text-gray-600">${order.seller_name || 'N/A'}</span>
                </td>
                <td>
                    <span class="text-gray-600">${order.creator_name || 'N/A'}</span>
                </td>
                <td>
                    <span class="text-gray-600">${order.customer_email || 'N/A'}</span>
                </td>
                <td>
                    <span class="text-gray-600">${order.branch_name || 'N/A'}</span>
                </td>
            </tr>
        `;
    }
    
    
    bindRowEvents() {
        console.log('Row events bound successfully with event delegation');

        // Use event delegation for checkbox clicks
        const tableBody = document.querySelector('#kt_orders_table tbody');
        if (tableBody) {
            tableBody.addEventListener('change', (e) => {
                if (e.target.type === 'checkbox') {
                    const orderId = e.target.value;
                    const row = e.target.closest('tr');

                    if (e.target.checked) {
                        this.selectedItems.add(orderId);
                        if (row) {
                            row.classList.add('selected');
                        }
                    } else {
                        this.selectedItems.delete(orderId);
                        if (row) {
                            row.classList.remove('selected');
                        }
                    }

                    this.updateBulkActionsVisibility();
                    this.updateSelectedCount();
                    this.updateSelectAllState();
                }
            });

            // Use event delegation for row clicks
            tableBody.addEventListener('click', (e) => {
                // Find the closest row
                const row = e.target.closest('tr.order-row');
                if (!row) return;

                // Don't trigger on checkbox clicks or action buttons
                if (e.target.type === 'checkbox' ||
                    e.target.closest('.btn') ||
                    e.target.closest('.form-check') ||
                    e.target.closest('a')) {
                    console.log('Click not on order row, ignoring');
                    return;
                }

                e.preventDefault();
                e.stopPropagation();

                console.log('Row click detected, preventing default and stopping propagation');

                const orderId = row.dataset.orderId;
                if (orderId) {
                    console.log('Row clicked, order ID:', orderId);
                    this.toggleRowExpansion($(row), orderId);
                    return false;
                }
            });
        }
    }

    toggleOrderExpansion(orderId) {
        console.log('Toggling order expansion for:', orderId);

        // Find the clicked row
        const $row = $(`.order-row[data-order-id="${orderId}"]`);
        if (!$row.length) {
            console.error('Order row not found for ID:', orderId);
            return;
        }

        this.toggleRowExpansion($row, orderId);
    }
    
    updateBulkActionsVisibility() {
        const bulkActionsContainer = document.getElementById('bulk-actions-dropdown');
        if (bulkActionsContainer) {
            bulkActionsContainer.style.display = this.selectedItems.size > 0 ? 'block' : 'none';
        }
    }

    updateSelectedCount() {
        const countElement = document.getElementById('bulk-count');
        if (countElement) {
            countElement.textContent = this.selectedItems.size;
        }
    }

    initIndividualCheckboxes() {
        console.log('Initializing individual checkboxes...');

        // Use event delegation for dynamically loaded checkboxes
        const tableBody = document.querySelector('#kt_orders_table tbody');
        if (tableBody) {
            tableBody.addEventListener('change', (e) => {
                if (e.target.type === 'checkbox') {
                    const checkbox = e.target;
                    const row = checkbox.closest('tr');
                    const orderId = row ? row.dataset.orderId || checkbox.value : checkbox.value;

                    if (checkbox.checked) {
                        this.selectedItems.add(orderId);
                        if (row) {
                            row.classList.add('selected');
                        }
                    } else {
                        this.selectedItems.delete(orderId);
                        if (row) {
                            row.classList.remove('selected');
                        }
                    }

                    this.updateBulkActionsVisibility();
                    this.updateSelectedCount();
                    this.updateSelectAllState();
                }
            });
        }
    }

    updateSelectAllState() {
        const selectAllCheckbox = document.getElementById('kt_orders_select_all');
        const individualCheckboxes = document.querySelectorAll('#kt_orders_table tbody input[type="checkbox"]');

        if (selectAllCheckbox && individualCheckboxes.length > 0) {
            const checkedCount = Array.from(individualCheckboxes).filter(cb => cb.checked).length;

            if (checkedCount === 0) {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = false;
            } else if (checkedCount === individualCheckboxes.length) {
                selectAllCheckbox.checked = true;
                selectAllCheckbox.indeterminate = false;
            } else {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = true;
            }
        }
    }
    
    updateSelectAllState() {
        const selectAllCheckbox = document.getElementById('kt_orders_select_all');
        const checkboxes = document.querySelectorAll('.order-checkbox');

        if (selectAllCheckbox && checkboxes.length > 0) {
            const checkedCount = document.querySelectorAll('.order-checkbox:checked').length;

            if (checkedCount === 0) {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = false;
            } else if (checkedCount === checkboxes.length) {
                selectAllCheckbox.checked = true;
                selectAllCheckbox.indeterminate = false;
            } else {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = true;
            }
        }
    }
    
    renderError(message) {
        const tbody = this.table.querySelector('tbody');
        if (tbody) {
            tbody.innerHTML = `<tr><td colspan="14" class="text-center text-danger">${message}</td></tr>`;
        }
    }
    
    // ===== IMPLEMENT PARENT'S ABSTRACT METHODS =====

    getSelectAllId() {
        return 'select-all-orders';
    }

    getRowCheckboxes() {
        return document.querySelectorAll('#orders-table-body input[type="checkbox"]');
    }

    getItemName() {
        return 'đơn hàng';
    }

    // Use parent's utility methods (formatCurrency, formatDate, formatTime)

    // Implement bulk actions
    bulkDelete() {
        console.log('Bulk deleting orders:', Array.from(this.selectedItems));
        // Implementation for bulk delete
    }

    showBulkStatusModal() {
        console.log('Showing bulk status modal for orders:', Array.from(this.selectedItems));
        // Implementation for bulk status update modal
    }

    bulkExport() {
        console.log('Bulk exporting orders:', Array.from(this.selectedItems));
        // Implementation for bulk export
    }

    /**
     * Toggle row expansion to show/hide detail panel
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
        $('.kt-table-row-active').removeClass('expanded kt-table-row-active');

        // Hide border spans when closing other rows
        this.hideBorderSpans();

        const $nextRow = $row.next('.kt-table-detail-row');
        if ($nextRow.length) {
            console.log('Collapsing row for order:', orderId);
            $nextRow.slideUp(300, function() {
                $nextRow.remove();
            });
            $row.removeClass('expanded kt-table-row-active');
            this.hideBorderSpans();
            this.expandedRows.delete(orderId);
            return;
        }

        console.log('Expanding row for order:', orderId);
        $row.addClass('expanded kt-table-row-active');
        this.expandedRows.add(orderId);

        // Get the table container width to match detail panel width
        const tableContainer = $('#kt_orders_table_container');
        const containerWidth=tableContainer.width();
        
        // Create detail row
        const columnCount = $row.find('td').length;
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
                            <div class="mt-2">Đang tải thông tin hóa đơn...</div>
                        </div>
                    </div>
                </td>
            </tr>
        `);

        // Insert detail row after clicked row
        $row.after($detailRow);

        // Show detail row with animation
        $detailRow.slideDown(300);

        // Show border spans
        this.showBorderSpans();

        // Load detail content via AJAX
        try {
            console.log('About to call loadOrderDetail for order:', orderId);
            this.loadOrderDetail(orderId, $detailRow, $row);
        } catch (error) {
            console.error('Error calling loadOrderDetail:', error);
        }
    }

    /**
     * Load order detail content via AJAX
     */
    loadOrderDetail(orderId, $detailRow, $clickedRow) {
        console.log('Loading order detail for ID:', orderId);

        const url = window.orderRoutes?.detail?.replace(':id', orderId) || `/admin/orders/detail/${orderId}`;
        console.log('Fetching order detail from:', url);

        $.ajax({
            url: url,
            method: 'GET',
            success: (response) => {
                console.log('Order detail loaded successfully');

                // Replace loading placeholder with actual content
                $detailRow.find('.loading-placeholder').replaceWith(response);

                // Update border spans position and height based on clicked row
                this.updateBorderSpansPosition($clickedRow, $detailRow);

                // Initialize any JavaScript components in the detail panel
                this.initDetailPanelComponents($detailRow);
            },
            error: (_, __, error) => {
                console.error('Error loading order detail:', error);

                const errorHtml = `
                    <div class="alert alert-danger m-4">
                        <h5>Lỗi tải thông tin đơn hàng</h5>
                        <p>Không thể tải thông tin chi tiết đơn hàng. Vui lòng thử lại sau.</p>
                        <small>Error: ${error}</small>
                    </div>
                `;

                $detailRow.find('.loading-placeholder').replaceWith(errorHtml);
            }
        });
    }

    /**
     * Initialize components in detail panel
     */
    initDetailPanelComponents($detailRow) {
        console.log('Initializing detail panel components');

        // Bind tab click handlers
        const tabLinks = $detailRow.find('a[data-bs-toggle="tab"]');
        console.log('Binding tab click handler to elements:', tabLinks.length);

        tabLinks.on('click', (e) => {
            console.log('OUR tab click handler executing!');
            const tabId = $(e.target).attr('href');
            console.log('Detail panel tab clicked:', tabId);

            // Update border spans position after tab content changes
            setTimeout(() => {
                console.log('Tab click handler executing, this context:', this.constructor.name);
                console.log('updateBorderSpansPosition method exists:', typeof this.updateBorderSpansPosition);
                console.log('About to update border spans after tab switch to:', tabId);
                this.updateBorderSpansPosition();
                console.log('Border spans updated after tab switch to:', tabId);
            }, 100);
        });

        // Initialize any other components as needed
        // e.g., tooltips, popovers, etc.
    }

    /**
     * Create border spans for visual separation
     */
    createBorderSpans() {
        const container = document.getElementById('kt_orders_table_container');
        if (!container) return;

        // Create left border span
        const leftBorder = document.createElement('div');
        leftBorder.className = 'kt-table-detail-border-left';
        leftBorder.style.display = 'none';
        container.appendChild(leftBorder);

        // Create right border span
        const rightBorder = document.createElement('div');
        rightBorder.className = 'kt-table-detail-border-right';
        rightBorder.style.display = 'none';
        container.appendChild(rightBorder);

        console.log('Border spans created for orders table');
    }

    /**
     * Show border spans
     */
    showBorderSpans() {
        const container = document.getElementById('kt_orders_table_container');
        if (!container) return;

        const leftBorder = container.querySelector('.kt-table-detail-border-left');
        const rightBorder = container.querySelector('.kt-table-detail-border-right');

        if (leftBorder) leftBorder.style.display = 'block';
        if (rightBorder) rightBorder.style.display = 'block';
    }

    /**
     * Hide border spans
     */
    hideBorderSpans() {
        const container = document.getElementById('kt_orders_table_container');
        if (!container) return;

        const leftBorder = container.querySelector('.kt-table-detail-border-left');
        const rightBorder = container.querySelector('.kt-table-detail-border-right');

        if (leftBorder) leftBorder.style.display = 'none';
        if (rightBorder) rightBorder.style.display = 'none';
    }

    /**
     * Update border spans position based on clicked row
     */
    updateBorderSpansPosition($clickedRow = null, $detailRow = null) {
        const container = document.getElementById('kt_orders_table_container');
        if (!container) return;

        const leftBorder = container.querySelector('.kt-table-detail-border-left');
        const rightBorder = container.querySelector('.kt-table-detail-border-right');

        if (!leftBorder || !rightBorder) return;

        // Find the active row if not provided
        if (!$clickedRow) {
            $clickedRow = $('.kt-table-row-active');
        }

        if (!$clickedRow || !$clickedRow.length) return;

        // Get container dimensions and scroll position
        const containerRect = container.getBoundingClientRect();
        const containerScrollLeft = container.scrollLeft;

        // Get clicked row position relative to container
        const clickedRowElement = $clickedRow[0];
        const rowRect = clickedRowElement.getBoundingClientRect();
        const rowTop = rowRect.top - containerRect.top + container.scrollTop;

        // Calculate detail row height
        let detailHeight = 0;
        if ($detailRow && $detailRow.length) {
            detailHeight = $detailRow.outerHeight() || 0;
        } else {
            const existingDetailRow = $clickedRow.next('.kt-table-detail-row');
            if (existingDetailRow.length) {
                detailHeight = existingDetailRow.outerHeight() || 0;
            }
        }

        const totalHeight = rowRect.height + detailHeight;

        // Position borders
        leftBorder.style.top = `${rowTop}px`;
        leftBorder.style.left = `${containerScrollLeft}px`;
        leftBorder.style.height = `${totalHeight}px`;

        rightBorder.style.top = `${rowTop}px`;
        rightBorder.style.right = '0px';
        rightBorder.style.height = `${totalHeight}px`;

        console.log('Border spans position updated for orders');
    }

    /**
     * Update detail panel widths to match table container
     */
    updateDetailPanelWidths() {
        const container = document.getElementById('kt_orders_table_container');
        if (!container) return;

        const detailContainers = container.querySelectorAll('.kt-table-detail-container');
        detailContainers.forEach(detailContainer => {
            detailContainer.style.width = container.style.width || '100%';
        });
    }

    // Auto expansion functionality
    handleAutoExpansion(orders) {
        // Check if URL has Code or code parameter
        const urlParams = new URLSearchParams(window.location.search);
        const codeParam = urlParams.get('Code') || urlParams.get('code');

        if (codeParam && orders.length === 1) {
            console.log('Auto expanding order row for Code:', codeParam);

            // Wait for DOM to be updated, then expand the row
            setTimeout(() => {
                const orderRow = document.querySelector('.order-row');
                if (orderRow) {
                    orderRow.click(); // Trigger row click to expand
                    console.log('Order row auto-expanded');
                }
            }, 500);
        }
    }
}

// Export for use
window.OrderTableManager = OrderTableManager;
