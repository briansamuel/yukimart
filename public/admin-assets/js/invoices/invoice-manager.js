/**
 * Invoice Table Manager
 * Extends BaseTableManager for invoice-specific functionality
 * Consolidated from invoice-list.js functionality
 */

class InvoiceTableManager extends BaseTableManager {
    constructor() {
        super({
            tableId: 'kt_invoices_table',
            containerId: 'kt_invoices_table_container',
            ajaxUrl: window.invoiceAjaxUrl || '/admin/invoices/ajax',
            module: 'invoices',
            storageKey: 'invoice_column_visibility',
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
                delivery_partner: '',
                delivery_area: '',
                price_list: ''
            },
            defaultPerPage: 10
        });

        // Use parent's selectedItems instead of selectedInvoices
        this.initialized = false;
        this.currentPage = 1;
        this.perPage = 10;

        // Enhanced search properties
        this.searchHistory = JSON.parse(localStorage.getItem('invoice_search_history') || '[]');
        this.currentSearchTerm = '';
        this.searchSuggestions = [];
    }

    // Override parent's init to add detail panel functionality
    init() {
        // Call parent init first
        super.init();

        // Create border spans for detail panel visual separation
        this.createBorderSpans();

        // Add scroll listener to update border spans position
        const container = document.getElementById('kt_invoices_table_container');
        if (container) {
            container.addEventListener('scroll', () => {
                this.updateBorderSpansPosition();
            });
        }

        // Add window resize listener to update detail panel widths (from invoice-list.js)
        $(window).on('resize.invoiceDetailPanels', () => {
            this.updateDetailPanelWidths();
            this.updateBorderSpansPosition();
        });

        // Initialize export functionality
        this.initExportFunctionality();

        console.log('Invoice detail panel functionality initialized');
    }
    
    initSearch() {
        console.log('Initializing invoice search...');

        const searchInput = document.querySelector('input[data-kt-invoice-table-filter="search"]');
        if (searchInput) {
            this.initEnhancedSearch();

            let searchTimeout;
            searchInput.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    this.currentFilters.search = e.target.value.trim();
                    this.currentFilters.page = 1; // Reset to first page
                    this.loadData();
                }, 300);
            });
        }
    }




    // ===== COLUMN VISIBILITY =====

    initColumnVisibility() {
        console.log('Initializing column visibility...');

        if (typeof window.KTColumnVisibility !== 'undefined') {
            this.columnVisibility = window.KTColumnVisibility.init({
                storageKey: this.config.storageKey,
                defaultVisibility: {
                    0: true, 1: true, 2: true, 3: true, 4: true, 5: true, 6: true,
                    7: true, 8: true, 9: true, 10: true, 11: false, 12: true,
                    13: false, 14: false, 15: true, 16: false
                }
            });
        }
    }
    
    // Override parent's abstract methods
    getSelectAllId() {
        return 'select-all-invoices';
    }

    getRowCheckboxes() {
        return document.querySelectorAll('#invoices-table-body input[type="checkbox"]');
    }

    getItemName() {
        return 'hóa đơn';
    }
    
    // Use parent's initBulkActions - no need to override
    
    loadData() {
        console.log('Loading invoices with filters:', this.currentFilters);

        // Cancel previous request
        if (this.currentRequest) {
            this.currentRequest.abort();
        }

        const filterData = this.getFilterData();
        console.log('Loading invoices with filters:', filterData);

        // Show loading state
        this.showLoading('table', 'Đang tải dữ liệu...');
        this.showLoadingState();

        this.currentRequest = $.ajax({
            url: this.config.ajaxUrl,
            type: 'GET',
            data: filterData,
            success: (response) => {
                console.log('Invoice data loaded:', response);

                if (response.data && Array.isArray(response.data)) {
                    this.renderData(response.data);
                    this.updatePagination(response);

                    // Auto expand row if Code param exists and only 1 result
                    this.handleAutoExpansion(response.data);

                    // Update search results count if search is active
                    if (this.currentSearchTerm) {
                        this.updateSearchResultsCount(response.total || response.data.length);
                    }
                } else {
                    this.showErrorState('Có lỗi xảy ra khi tải dữ liệu');
                }
            },
            error: (xhr, status, error) => {
                if (status !== 'abort') {
                    console.error('AJAX Error:', error);

                    // Show user-friendly error message
                    let errorMessage = 'Không thể tải dữ liệu. Vui lòng thử lại.';

                    if (xhr.status === 0) {
                        errorMessage = 'Không có kết nối mạng. Vui lòng kiểm tra kết nối internet.';
                    } else if (xhr.status === 404) {
                        errorMessage = 'Không tìm thấy dữ liệu. Vui lòng liên hệ quản trị viên.';
                    } else if (xhr.status === 500) {
                        errorMessage = 'Lỗi máy chủ. Vui lòng thử lại sau.';
                    } else if (xhr.status === 403) {
                        errorMessage = 'Bạn không có quyền truy cập dữ liệu này.';
                    }

                    this.showErrorState(errorMessage);
                    this.showErrorToast(errorMessage, 'Lỗi tải dữ liệu');
                }
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

    // ===== FILTER DATA MANAGEMENT =====

    getFilterData() {
        console.log('getFilterData() called');

        // Get per_page value from selector
        const perPageSelect = document.getElementById('kt_invoices_per_page');
        const currentPerPage = perPageSelect ? parseInt(perPageSelect.value) : this.perPage;

        const data = {
            page: this.currentFilters.page || 1,
            per_page: currentPerPage
        };

        // Check for URL parameter 'code' or 'Code' and add to request
        const urlParams = new URLSearchParams(window.location.search);
        const codeParam = urlParams.get('code') || urlParams.get('Code');
        if (codeParam) {
            data.code = codeParam;
            console.log('Adding code parameter to request:', codeParam);
        }

        const filterForm = document.getElementById('kt_invoice_filter_form');
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
                    } else if ($input.is('select[multiple]')) {
                        data[name] = $input.val() || [];
                    } else {
                        const value = $input.val();
                        if (value) {
                            // Handle Tagify inputs
                            if (name.endsWith('_tags')) {
                                try {
                                    const tagifyData = JSON.parse(value);
                                    if (Array.isArray(tagifyData) && tagifyData.length > 0) {
                                        const fieldName = name.replace('_tags', '');
                                        data[fieldName] = tagifyData.map(tag => tag.value || tag.id || tag);
                                    }
                                } catch (e) {
                                    data[name] = value;
                                }
                            } else {
                                data[name] = value;
                            }
                        }
                    }
                }
            });
        }

        // Add search term
        const searchInput = document.querySelector('input[data-kt-invoice-table-filter="search"]');
        if (searchInput && searchInput.value) {
            data.search_term = searchInput.value;
        }

        console.log('Filter data collected:', data);
        return data;
    }
    
    // ===== ERROR AND EMPTY STATES =====

    showErrorState(message) {
        const tbody = document.querySelector('#invoices-table-body');
        if (tbody) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="17">
                        <div class="table-error-state">
                            <div class="table-error-icon">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div class="table-error-title">Có lỗi xảy ra</div>
                            <div class="table-error-message">${message}</div>
                            <div class="table-error-actions">
                                <button class="btn btn-sm btn-primary" onclick="window.invoiceTableManager.loadData()">
                                    <i class="fas fa-redo"></i> Thử lại
                                </button>
                                <button class="btn btn-sm btn-light" onclick="location.reload()">
                                    <i class="fas fa-refresh"></i> Tải lại trang
                                </button>
                            </div>
                        </div>
                    </td>
                </tr>
            `;
        }
    }

    showEmptyState(message) {
        const tbody = document.querySelector('#invoices-table-body');
        if (tbody) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="17">
                        <div class="table-empty-state">
                            <div class="table-empty-icon">
                                <i class="fas fa-inbox"></i>
                            </div>
                            <div class="table-empty-title">Không có dữ liệu</div>
                            <div class="table-empty-message">${message || 'Không tìm thấy hóa đơn nào phù hợp với bộ lọc hiện tại.'}</div>
                            <div class="table-error-actions">
                                <button class="btn btn-sm btn-primary" onclick="window.invoiceTableManager.clearAllFilters()">
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
        const searchInput = document.querySelector('input[data-kt-invoice-table-filter="search"]');
        if (searchInput) {
            searchInput.value = '';
        }

        // Reset filters
        const filterForm = document.getElementById('kt_invoice_filter_form');
        if (filterForm) {
            filterForm.reset();
        }

        // Reload data
        this.currentPage = 1;
        this.loadData();

        this.showInfoToast('Đã xóa tất cả bộ lọc', 'Thông tin');
    }

    renderData(invoices) {
        console.log('Rendering invoices:', invoices.length);

        if (invoices.length === 0) {
            this.showEmptyState();
            return;
        }

        // Use renderInvoices from invoice-list.js if available
        if (window.invoiceListRenderFunctions && typeof window.invoiceListRenderFunctions.renderInvoices === 'function') {
            console.log('Using renderInvoices from invoice-list.js');
            window.invoiceListRenderFunctions.renderInvoices(invoices);
        } else {
            console.log('Fallback to local renderInvoices');
            this.renderInvoices(invoices);
        }

        // Bind row events
        this.bindRowEvents();
    }

    renderInvoices(invoices) {
        const tbody = this.table.querySelector('tbody');
        if (!tbody) {
            console.error('Table tbody not found');
            return;
        }

        const rows = invoices.map(invoice => this.renderInvoiceRow(invoice)).join('');
        tbody.innerHTML = rows;

        // Apply column visibility
        if (typeof window.KTColumnVisibility !== 'undefined') {
            window.KTColumnVisibility.apply({
                tableSelector: '#kt_invoices_table'
            }, this.columnVisibility || {});
        }
    }
    
    renderInvoiceRow(invoice) {
        // Use renderInvoiceRow from invoice-list.js if available
        if (window.invoiceListRenderFunctions && typeof window.invoiceListRenderFunctions.renderInvoiceRow === 'function') {
            return window.invoiceListRenderFunctions.renderInvoiceRow(invoice);
        }

        // Fallback to local implementation
        const customerName = invoice.customer_display || invoice.customer_name || 'Khách lẻ';
        const statusBadge = this.getStatusBadge(invoice.status);
        const paymentMethodDisplay = this.getPaymentMethodDisplay(invoice.payment_method);
        const salesChannelDisplay = this.getSalesChannelDisplay(invoice.sales_channel);

        return `
            <tr data-invoice-id="${invoice.id}" class="invoice-row" style="cursor: pointer;">
                
                <td>
                
                    <div class="form-check form-check-sm form-check-custom form-check-solid">
                        <input class="form-check-input" type="checkbox" value="${invoice.id}" />
                    </div>
                </td>
                <td>
                    <div class="d-flex flex-column">
                        <span class="text-gray-800 text-hover-primary mb-1 fw-bold">${invoice.invoice_number || invoice.invoice_code || 'N/A'}</span>
                    </div>
                </td>
                <td>
                    <div class="d-flex flex-column">
                        <span class="text-gray-800 mb-1">${customerName}</span>
                    </div>
                </td>
                <td class="text-end">
                    <span class="text-gray-800 fw-bold">${this.formatCurrency(invoice.total_amount)}</span>
                </td>
                <td class="text-end">
                    <span class="text-gray-800">${this.formatCurrency(invoice.amount_paid || invoice.paid_amount || 0)}</span>
                </td>
                <td>
                    ${statusBadge}
                </td>
                <td>
                    <span class="text-gray-800">${paymentMethodDisplay}</span>
                </td>
                <td>
                    <span class="text-gray-800">${salesChannelDisplay}</span>
                </td>
                <td>
                    <div class="d-flex flex-column">
                        <span class="text-gray-800 mb-1">${this.formatDate(invoice.created_at)}</span>
                        <span class="text-muted fs-7">${this.formatTime(invoice.created_at)}</span>
                    </div>
                </td>
                <td>
                    <span class="text-gray-800">${invoice.seller || invoice.seller_name || ''}</span>
                </td>
                <td>
                    <span class="text-gray-800">${invoice.creator || invoice.creator_name || ''}</span>
                </td>
                <td class="text-end">
                    <span class="text-gray-800">${this.formatCurrency(invoice.discount || 0)}</span>
                </td>
                <td>
                    <span class="text-gray-800">${invoice.email || invoice.customer_email || ''}</span>
                </td>
                <td>
                    <span class="text-gray-800">${invoice.phone || invoice.customer_phone || ''}</span>
                </td>
                <td>
                    <span class="text-gray-800">${invoice.address || invoice.customer_address || ''}</span>
                </td>
                <td>
                    <span class="text-gray-800">${invoice.branch_shop || invoice.branch_name || ''}</span>
                </td>
                <td>
                    <span class="text-gray-800">${invoice.notes || ''}</span>
                </td>
            </tr>
        `;
    }
    
    bindRowEvents() {
        // Bind checkbox events - use correct selector matching invoice-list.js
        const checkboxes = document.querySelectorAll('#invoices-table-body input[type="checkbox"]');
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', (e) => {
                const invoiceId = e.target.value;

                if (e.target.checked) {
                    this.selectedItems.add(invoiceId);
                } else {
                    this.selectedItems.delete(invoiceId);
                }

                this.updateBulkActionsVisibility();
                this.updateSelectedCount();
                this.updateSelectAllState();
            });
        });
        
        // Use event delegation for row click events (from invoice-list.js)
        const tbody = $('#invoices-table-body');

        // Remove any existing handlers first
        tbody.off('click.rowExpansion');
        $(document).off('click.checkboxPrevention');

        // Prevent checkbox clicks from triggering row expansion
        $(document).on('click.checkboxPrevention', '#invoices-table-body input[type="checkbox"]', function(e) {
            e.stopPropagation();
            console.log('Checkbox click propagation stopped');
        });

        // Bind row click events for expansion using event delegation with capture phase
        const self = this;
        tbody[0].addEventListener('click', function(e) {
            const $row = $(e.target).closest('.invoice-row');

            if (!$row.length) {
                console.log('Click not on invoice row, ignoring');
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

            const invoiceId = $row.data('invoice-id');
            console.log('Row clicked, invoice ID:', invoiceId);
            if (invoiceId) {
                console.log('About to call toggleRowExpansion');
                self.toggleRowExpansion($row, invoiceId);
            }

            console.log('Returning false to prevent further event handling');
            return false; // Prevent any further event handling
        }, true); // Use capture phase to get priority

        console.log('Row events bound successfully with event delegation');
    }
    
    toggleInvoiceExpansion(invoiceId) {
        console.log('Toggling invoice expansion for:', invoiceId);

        // Find the clicked row
        const $row = $(`.invoice-row[data-invoice-id="${invoiceId}"]`);
        if (!$row.length) {
            console.error('Invoice row not found for ID:', invoiceId);
            return;
        }

        this.toggleRowExpansion($row, invoiceId);
    }

    /**
     * Toggle row expansion to show/hide detail panel (from invoice-list.js)
     */
    toggleRowExpansion($row, invoiceId) {
        console.log('Toggling row expansion for invoice:', invoiceId);

        // Close any other open detail rows first
        $('.kt-table-detail-row:visible').each(function() {
            const $openRow = $(this);
            $openRow.slideUp(300, function() {
                $openRow.remove();
            });
        });

        // Remove expanded class from all rows
        $('.invoice-row').removeClass('expanded kt-table-row-active');

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
        console.log('Expanding row for invoice:', invoiceId);
        $row.addClass('expanded kt-table-row-active');

        // Create placeholder row with width matching table container
        const columnCount = $row.find('td').length;

        // Get the table container width to match detail panel width
        const tableContainer = $('#kt_invoices_table_container');
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
                            <div class="mt-2">Đang tải thông tin hóa đơn...</div>
                        </div>
                    </div>
                </td>
            </tr>
        `);

        // Insert detail row after clicked row
        $row.after($detailRow);

        // Show detail row with animation
        $detailRow.slideDown(300, () => {
            // Load invoice detail content
            this.loadInvoiceDetail(invoiceId, $detailRow, $row);
        });
    }

    /**
     * Load invoice detail content via AJAX (from invoice-list.js)
     */
    loadInvoiceDetail(invoiceId, $detailRow, $clickedRow) {
        console.log('Loading invoice detail for ID:', invoiceId);

        $.ajax({
            url: `/admin/invoices/${invoiceId}/detail-panel`,
            type: 'GET',
            success: (response) => {
                console.log('Invoice detail loaded successfully');

                // Check if response has html property (JSON response) or is HTML directly
                const htmlContent = response.html || response;

                // Replace loading placeholder with actual content
                $detailRow.find('.loading-placeholder').replaceWith(htmlContent);

                // Get table container width and apply to detail container
                const tableContainer = $('#kt_invoices_table_container');
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
            },
            error: (xhr, status, error) => {
                console.error('Error loading invoice detail:', error);

                const errorHtml = `
                    <div class="alert alert-danger m-4">
                        <h5>Lỗi tải thông tin hóa đơn</h5>
                        <p>Không thể tải thông tin chi tiết hóa đơn. Vui lòng thử lại sau.</p>
                        <small>Error: ${error}</small>
                    </div>
                `;

                $detailRow.find('.loading-placeholder').replaceWith(errorHtml);
            }
        });
    }

    /**
     * Initialize components in detail panel (from invoice-list.js)
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
     * Create border spans for detail panel visual separation
     */
    createBorderSpans() {
        const $container = $('#kt_invoices_table_container');
        if ($container.length === 0) return;

        // Ensure container has relative positioning
        if ($container.css('position') === 'static') {
            $container.css('position', 'relative');
        }

        // Remove existing border spans
        $container.find('.kt-table-detail-border-left, .kt-table-detail-border-right').remove();

        // Create left border span
        const $leftBorder = $('<div>', {
            class: 'kt-table-detail-border-left',
            css: {
                'position': 'absolute',
                'top': '0',
                'left': '0',
                'width': '4px',
                'height': '0',
                'background-color': '#009ef7',
                'z-index': '99',
                'display': 'none'
            }
        });

        // Create right border span
        const $rightBorder = $('<div>', {
            class: 'kt-table-detail-border-right',
            css: {
                'position': 'absolute',
                'top': '0',
                'right': '0',
                'width': '4px',
                'height': '0',
                'background-color': '#009ef7',
                'z-index': '10',
                'display': 'none'
            }
        });

        // Append to container
        //$container.append($leftBorder).append($rightBorder);

        console.log('Border spans created successfully');
    }

    /**
     * Update border spans position (from invoice-list.js)
     */
    updateBorderSpansPosition($clickedRow, $detailRow) {
        setTimeout(() => {
            const $container = $('#kt_invoices_container_body');
            const $borderLeft = $container.find('.kt-table-detail-border-left');
            const $borderRight = $container.find('.kt-table-detail-border-right');

            // Find the active invoice row with expanded class
            const $activeRow=$container.find('.invoice-row.expanded.kt-table-row-active');
            

            if ($activeRow.length === 0) {
                console.log('No active expanded invoice row found');
                return;
            }

            // Calculate position based on the active row position within the container
            const containerOffset = $container.offset();
            const activeRowOffset = $activeRow.offset();
            const relativeTop = activeRowOffset.top - containerOffset.top;

            // Get invoice detail panel height
            const $detailPanel = $detailRow.find('.kt-table-detail-container');
            const detailPanelHeight = $detailPanel.outerHeight();
            const activeRowHeight = $activeRow.outerHeight();
            const totalHeight = activeRowHeight + detailPanelHeight;

            // Position border spans based on active row
            $borderLeft.css({
                'top': relativeTop + 'px',
                'height': totalHeight + 'px',
                'display': 'block'
            });

            $borderRight.css({
                'top': relativeTop + 'px',
                'height': totalHeight + 'px',
                'display': 'block'
            });

            console.log('Border spans updated based on active row:', {
                activeRowFound: $activeRow.length > 0,
                activeRowClasses: $activeRow.attr('class'),
                relativeTop: relativeTop,
                totalHeight: totalHeight,
                activeRowHeight: activeRowHeight,
                detailPanelHeight: detailPanelHeight,
                containerOffset: containerOffset,
                activeRowOffset: activeRowOffset
            });
        }, 100); // Small delay to ensure content is rendered
    }

    /**
     * Update detail panel widths to match table container (from invoice-list.js)
     */
    updateDetailPanelWidths() {
        const tableContainer = $('#kt_invoices_table_container');
        const containerWidth = tableContainer.width();

        if (containerWidth) {
            $('.kt-table-detail-container').css({
                'width': containerWidth + 'px',
                'max-width': containerWidth + 'px'
            });
        }
    }
    
    // Override parent's updateBulkActionsVisibility to match invoice-list.js
    updateBulkActionsVisibility() {
        console.log('updateBulkActionsVisibility called');

        const selectedCount = $('#invoices-table-body input[type="checkbox"]:checked').length;
        console.log('Selected count:', selectedCount);

        const bulkDropdown = $('#bulk-actions-dropdown');
        const bulkCountElement = $('#bulk-count');
        const bulkTextElement = $('#bulk-actions-text');

        console.log('Bulk dropdown found:', bulkDropdown.length);
        console.log('Bulk count element found:', bulkCountElement.length);
        console.log('Bulk text element found:', bulkTextElement.length);

        if (selectedCount > 0) {
            console.log('Showing bulk dropdown...');
            bulkDropdown.show();
            bulkCountElement.text(selectedCount);
            bulkTextElement.text('Thao tác (' + selectedCount + ' hóa đơn)');
        } else {
            console.log('Hiding bulk dropdown...');
            bulkDropdown.hide();
        }
    }

    // Override parent's updateSelectedCount to use jQuery
    updateSelectedCount() {
        const selectedCount = $('#invoices-table-body input[type="checkbox"]:checked').length;
        const countElement = $('.selected-count');
        if (countElement.length) {
            countElement.text(selectedCount);
        }
    }
    
    updateSelectAllState() {
        // Use correct selector matching invoice-list.js
        const selectAllCheckbox = document.getElementById('select-all-invoices');
        const checkboxes = document.querySelectorAll('#invoices-table-body input[type="checkbox"]');

        if (selectAllCheckbox && checkboxes.length > 0) {
            const checkedCount = document.querySelectorAll('#invoices-table-body input[type="checkbox"]:checked').length;

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
    
    // Use parent's renderError, formatCurrency, formatDate, formatTime methods
    
    // Bulk actions
    // ===== CONFIRMATION MODALS =====

    showConfirmationModal(options) {
        const modal = document.createElement('div');
        modal.className = 'confirmation-modal';
        modal.id = 'confirmationModal';

        const iconClass = options.type === 'danger' ? 'fa-exclamation-triangle' :
                         options.type === 'warning' ? 'fa-exclamation-circle' : 'fa-info-circle';

        let detailsHtml = '';
        if (options.details && options.details.length > 0) {
            detailsHtml = '<div class="confirmation-details">';
            options.details.forEach(detail => {
                detailsHtml += `
                    <div class="confirmation-detail-item">
                        <span class="confirmation-detail-label">${detail.label}</span>
                        <span class="confirmation-detail-value">${detail.value}</span>
                    </div>
                `;
            });
            detailsHtml += '</div>';
        }

        modal.innerHTML = `
            <div class="confirmation-content">
                <div class="confirmation-header">
                    <div class="confirmation-icon ${options.type || 'info'}">
                        <i class="fas ${iconClass}"></i>
                    </div>
                    <h4 class="confirmation-title">${options.title}</h4>
                </div>
                <div class="confirmation-message">${options.message}</div>
                ${detailsHtml}
                <div class="confirmation-actions">
                    <button type="button" class="btn btn-light" onclick="this.closest('.confirmation-modal').remove()">
                        ${options.cancelText || 'Hủy'}
                    </button>
                    <button type="button" class="btn btn-${options.type === 'danger' ? 'danger' : 'primary'}" onclick="window.confirmationCallback && window.confirmationCallback(); this.closest('.confirmation-modal').remove();">
                        ${options.confirmText || 'Xác nhận'}
                    </button>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        // Store callback
        window.confirmationCallback = options.onConfirm;

        // Show modal
        setTimeout(() => {
            modal.classList.add('show');
        }, 10);

        // Close on backdrop click
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.remove();
                window.confirmationCallback = null;
            }
        });
    }

    // ===== BULK ACTIONS IMPLEMENTATION =====

    getSelectedInvoiceIds() {
        // Use parent's selectedItems Set
        return Array.from(this.selectedItems);
    }

    // Override parent's initBulkActions to add invoice-specific handlers
    initBulkActions() {
        // Call parent's initBulkActions first
        super.initBulkActions();

        // Add invoice-specific bulk action handlers (from invoice-list.js)
        $('#bulk-update-delivery').off('click').on('click', (e) => {
            e.preventDefault();
            const selectedIds = this.getSelectedInvoiceIds();
            console.log('Bulk update delivery clicked, selected IDs:', selectedIds);

            if (selectedIds.length === 0) {
                alert('Vui lòng chọn ít nhất một hóa đơn để cập nhật giao hàng.');
                return;
            }

            // TODO: Implement bulk update delivery functionality
            alert('Tính năng cập nhật giao hàng cho ' + selectedIds.length + ' hóa đơn sẽ được triển khai.');
        });

        $('#bulk-update-info').off('click').on('click', (e) => {
            e.preventDefault();
            const selectedIds = this.getSelectedInvoiceIds();
            console.log('Bulk update info clicked, selected IDs:', selectedIds);

            if (selectedIds.length === 0) {
                alert('Vui lòng chọn ít nhất một hóa đơn để cập nhật thông tin chung.');
                return;
            }

            // TODO: Implement bulk update info functionality
            alert('Tính năng cập nhật thông tin chung cho ' + selectedIds.length + ' hóa đơn sẽ được triển khai.');
        });

        $('#bulk-cancel').off('click').on('click', (e) => {
            e.preventDefault();
            const selectedIds = this.getSelectedInvoiceIds();
            console.log('Bulk cancel clicked, selected IDs:', selectedIds);

            if (selectedIds.length === 0) {
                alert('Vui lòng chọn ít nhất một hóa đơn để huỷ.');
                return;
            }

            if (confirm('Bạn có chắc chắn muốn huỷ ' + selectedIds.length + ' hóa đơn đã chọn?')) {
                this.bulkCancelInvoices(selectedIds);
            }
        });

        console.log('Invoice-specific bulk actions initialized successfully');
    }

    /**
     * Bulk cancel invoices (from invoice-list.js)
     */
    bulkCancelInvoices(invoiceIds) {
        console.log('Bulk cancelling invoices:', invoiceIds);

        // Show loading state
        const bulkDropdown = $('#bulk-actions-dropdown');
        const originalText = $('#bulk-actions-text').text();
        $('#bulk-actions-text').text('Đang xử lý...');
        bulkDropdown.find('button').prop('disabled', true);

        // Make AJAX request to cancel invoices
        $.ajax({
            url: '/admin/invoices/bulk-cancel',
            type: 'POST',
            data: {
                invoice_ids: invoiceIds,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: (response) => {
                if (response.success) {
                    alert('Đã huỷ ' + response.cancelled_count + ' hóa đơn thành công.');

                    // Reload the table
                    this.loadData();

                    // Clear selections
                    $('#invoices-table-body input[type="checkbox"]').prop('checked', false);
                    $('#select-all-invoices').prop('checked', false);
                    this.selectedItems.clear();
                    this.updateBulkActionsVisibility();
                } else {
                    alert(response.message || 'Có lỗi xảy ra khi huỷ hóa đơn.');
                }
            },
            error: (xhr) => {
                let errorMessage = 'Có lỗi xảy ra khi huỷ hóa đơn.';

                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    try {
                        const errorData = JSON.parse(xhr.responseText);
                        errorMessage = errorData.message || errorMessage;
                    } catch (e) {
                        // Keep default error message
                    }
                }

                alert(errorMessage);
            },
            complete: () => {
                // Reset loading state
                $('#bulk-actions-text').text(originalText);
                bulkDropdown.find('button').prop('disabled', false);
            }
        });
    }

    // Override parent's abstract bulk action methods to use invoice-specific implementations
    bulkDelete() {
        // Use bulk cancel instead for invoices
        this.bulkCancelInvoices(this.getSelectedInvoiceIds());
    }

    showBulkStatusModal() {
        const selectedIds = this.getSelectedInvoiceIds();
        if (selectedIds.length === 0) {
            alert('Vui lòng chọn ít nhất một hóa đơn');
            return;
        }

        // Show status update modal
        console.log('Showing bulk status modal for invoices:', selectedIds);
        alert('Tính năng cập nhật trạng thái cho ' + selectedIds.length + ' hóa đơn sẽ được triển khai.');
    }

    bulkExport() {
        const selectedIds = this.getSelectedInvoiceIds();
        if (selectedIds.length === 0) {
            alert('Vui lòng chọn ít nhất một hóa đơn');
            return;
        }

        // TODO: Implement bulk export functionality
        alert('Tính năng xuất Excel cho ' + selectedIds.length + ' hóa đơn sẽ được triển khai.');
    }

    bulkUpdateStatus(status) {
        const selectedIds = this.getSelectedInvoiceIds();
        if (selectedIds.length === 0) {
            this.showWarningToast('Vui lòng chọn ít nhất một hóa đơn');
            return;
        }

        const statusLabels = {
            'completed': 'Hoàn thành',
            'processing': 'Đang xử lý',
            'cancelled': 'Đã hủy'
        };

        this.showConfirmationModal({
            type: 'warning',
            title: 'Xác nhận cập nhật trạng thái',
            message: `Bạn có chắc chắn muốn cập nhật trạng thái của ${selectedIds.length} hóa đơn thành "${statusLabels[status]}"?`,
            details: [
                { label: 'Số lượng hóa đơn', value: selectedIds.length },
                { label: 'Trạng thái mới', value: statusLabels[status] }
            ],
            confirmText: 'Cập nhật',
            onConfirm: () => {
                this.performBulkStatusUpdate(selectedIds, status);
            }
        });
    }

    performBulkStatusUpdate(invoiceIds, status) {
        this.showLoading('table', 'Đang cập nhật trạng thái...');

        $.ajax({
            url: '/admin/invoices/bulk-update-status',
            type: 'POST',
            data: {
                invoice_ids: invoiceIds,
                status: status,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: (response) => {
                if (response.success) {
                    this.showSuccessToast(`Đã cập nhật trạng thái ${response.updated_count} hóa đơn thành công`);
                    this.clearSelection();
                    this.loadData();
                } else {
                    this.showErrorToast(response.message || 'Có lỗi xảy ra khi cập nhật trạng thái');
                }
            },
            error: (xhr) => {
                let message = 'Có lỗi xảy ra khi cập nhật trạng thái';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                this.showErrorToast(message);
            },
            complete: () => {
                this.hideLoading('table');
            }
        });
    }

    // Duplicate bulkExport method removed - using the one defined above

    performBulkExport(invoiceIds) {
        this.showLoading('export', 'Đang tạo file Excel...');

        // Create form for download
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/admin/invoices/bulk-export';
        form.style.display = 'none';

        // Add CSRF token
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = $('meta[name="csrf-token"]').attr('content');
        form.appendChild(csrfInput);

        // Add invoice IDs
        invoiceIds.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'invoice_ids[]';
            input.value = id;
            form.appendChild(input);
        });

        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);

        this.showSuccessToast('File Excel đang được tạo và sẽ tự động tải xuống');
        this.hideLoading('export');
    }

    // Duplicate bulkDelete method removed - using the one defined above

    performBulkDelete(invoiceIds) {
        this.showLoading('table', 'Đang xóa hóa đơn...');

        $.ajax({
            url: '/admin/invoices/bulk-delete',
            type: 'POST',
            data: {
                invoice_ids: invoiceIds,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: (response) => {
                if (response.success) {
                    this.showSuccessToast(`Đã xóa ${response.deleted_count} hóa đơn thành công`);
                    this.clearSelection();
                    this.loadData();
                } else {
                    this.showErrorToast(response.message || 'Có lỗi xảy ra khi xóa hóa đơn');
                }
            },
            error: (xhr) => {
                let message = 'Có lỗi xảy ra khi xóa hóa đơn';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                this.showErrorToast(message);
            },
            complete: () => {
                this.hideLoading('table');
            }
        });
    }

    clearSelection() {
        // Use correct selector matching invoice-list.js
        const checkboxes = document.querySelectorAll('#invoices-table-body input[type="checkbox"]');
        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
            const row = checkbox.closest('tr');
            if (row) {
                row.classList.remove('selected');
            }
        });

        // Update master checkbox - use correct selector
        const masterCheckbox = document.getElementById('select-all-invoices');
        if (masterCheckbox) {
            masterCheckbox.checked = false;
            masterCheckbox.indeterminate = false;
        }

        this.selectedItems.clear();
        this.updateBulkActionsVisibility();
        this.updateSelectedCount();
        this.updateSelectAllState();
    }

    // ===== ENHANCED SEARCH FUNCTIONALITY =====

    initEnhancedSearch() {
        const searchInput = document.querySelector('input[data-kt-invoice-table-filter="search"]');
        if (!searchInput) return;

        // Wrap search input in container
        const container = document.createElement('div');
        container.className = 'search-input-container';
        searchInput.parentNode.insertBefore(container, searchInput);
        container.appendChild(searchInput);

        // Add enhanced classes
        searchInput.classList.add('search-input-enhanced');

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
        container.appendChild(actionsDiv);

        // Create suggestions dropdown
        const suggestionsDiv = document.createElement('div');
        suggestionsDiv.className = 'search-suggestions';
        suggestionsDiv.id = 'search-suggestions';
        container.appendChild(suggestionsDiv);

        // Bind events
        this.bindSearchEvents(searchInput);
    }

    bindSearchEvents(searchInput) {
        const clearBtn = document.querySelector('.search-clear-btn');
        const suggestions = document.getElementById('search-suggestions');

        // Input events
        searchInput.addEventListener('input', (e) => {
            const value = e.target.value.trim();
            this.currentSearchTerm = value;

            // Show/hide clear button
            if (value) {
                clearBtn.classList.add('show');
            } else {
                clearBtn.classList.remove('show');
                this.hideSuggestions();
            }

            // Show suggestions
            if (value.length >= 2) {
                this.showSearchSuggestions(value);
            } else {
                this.hideSuggestions();
            }
        });

        // Clear button
        clearBtn.addEventListener('click', () => {
            searchInput.value = '';
            this.currentSearchTerm = '';
            clearBtn.classList.remove('show');
            this.hideSuggestions();
            this.hideSearchResults();
            this.loadData();
        });
    }

    showSearchSuggestions(term) {
        const suggestions = document.getElementById('search-suggestions');
        if (!suggestions) return;

        // Generate suggestions
        const suggestionItems = this.generateSearchSuggestions(term);
        this.renderSuggestions(suggestionItems);
    }

    generateSearchSuggestions(term) {
        const suggestions = [];

        // Add invoice number suggestions
        if (/^HD/i.test(term)) {
            suggestions.push({
                type: 'invoice',
                title: term.toUpperCase(),
                subtitle: 'Tìm theo số hóa đơn',
                value: term
            });
        }

        // Add phone number suggestions
        if (/^\d{3,}/.test(term)) {
            suggestions.push({
                type: 'phone',
                title: term,
                subtitle: 'Tìm theo số điện thoại',
                value: term
            });
        }

        // Add customer name suggestions
        if (term.length >= 2 && !/^\d/.test(term)) {
            suggestions.push({
                type: 'customer',
                title: term,
                subtitle: 'Tìm theo tên khách hàng',
                value: term
            });
        }

        return suggestions.slice(0, 6);
    }

    renderSuggestions(suggestions) {
        const container = document.getElementById('search-suggestions');
        if (!container) return;

        if (suggestions.length === 0) {
            container.innerHTML = `
                <div class="search-no-results">
                    <div class="search-no-results-text">Không có gợi ý</div>
                </div>
            `;
        } else {
            let html = '';
            suggestions.forEach((item, index) => {
                html += `
                    <div class="search-suggestion-item" data-value="${item.value}">
                        <div class="search-suggestion-content">
                            <div class="search-suggestion-title">${item.title}</div>
                            <div class="search-suggestion-subtitle">${item.subtitle}</div>
                        </div>
                    </div>
                `;
            });
            container.innerHTML = html;

            // Bind click events
            container.querySelectorAll('.search-suggestion-item').forEach(item => {
                item.addEventListener('click', () => {
                    this.selectSuggestion(item.getAttribute('data-value'));
                });
            });
        }

        container.classList.add('show');
    }

    hideSuggestions() {
        const container = document.getElementById('search-suggestions');
        if (container) {
            container.classList.remove('show');
        }
    }

    selectSuggestion(value) {
        const searchInput = document.querySelector('input[data-kt-invoice-table-filter="search"]');
        if (searchInput) {
            searchInput.value = value;
            this.currentSearchTerm = value;
        }

        this.hideSuggestions();
        this.showSearchResults(value);
        this.currentPage = 1;
        this.loadData();
    }

    showSearchResults(term) {
        const existingInfo = document.querySelector('.search-results-info');
        if (existingInfo) {
            existingInfo.remove();
        }

        const infoDiv = document.createElement('div');
        infoDiv.className = 'search-results-info show';
        infoDiv.innerHTML = `
            <div class="search-results-text">
                Kết quả tìm kiếm cho: "<strong>${term}</strong>"
                <span class="search-results-count" id="search-count">0</span> kết quả
            </div>
            <button class="search-results-clear" onclick="window.invoiceTableManager.clearSearch()">
                Xóa tìm kiếm
            </button>
        `;

        const tableContainer = document.getElementById('kt_invoices_table_container');
        if (tableContainer) {
            tableContainer.parentNode.insertBefore(infoDiv, tableContainer);
        }
    }

    hideSearchResults() {
        const existingInfo = document.querySelector('.search-results-info');
        if (existingInfo) {
            existingInfo.remove();
        }
    }

    clearSearch() {
        const searchInput = document.querySelector('input[data-kt-invoice-table-filter="search"]');
        if (searchInput) {
            searchInput.value = '';
            searchInput.focus();
        }

        const clearBtn = document.querySelector('.search-clear-btn');
        if (clearBtn) {
            clearBtn.classList.remove('show');
        }

        this.currentSearchTerm = '';
        this.hideSearchResults();
        this.hideSuggestions();

        this.currentPage = 1;
        this.loadData();
    }

    updateSearchResultsCount(count) {
        const countElement = document.getElementById('search-count');
        if (countElement) {
            countElement.textContent = count;
        }
    }

    // ===== UTILITY METHODS =====

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

    // Override updatePagination to use renderPagination from invoice-list.js
    updatePagination(data) {
        // Use renderPagination from invoice-list.js if available
        if (window.invoiceListRenderFunctions && typeof window.invoiceListRenderFunctions.renderPagination === 'function') {
            console.log('Using renderPagination from invoice-list.js');
            window.invoiceListRenderFunctions.renderPagination(data);
        } else {
            console.log('Fallback to parent updatePagination');
            super.updatePagination(data);
        }
    }

    // Auto expansion functionality
    handleAutoExpansion(invoices) {
        // Check if URL has Code or code parameter
        const urlParams = new URLSearchParams(window.location.search);
        const codeParam = urlParams.get('Code') || urlParams.get('code');

        if (codeParam && invoices.length === 1) {
            console.log('Auto expanding invoice row for Code:', codeParam);

            // Wait for DOM to be updated, then expand the row
            setTimeout(() => {
                const invoiceRow = document.querySelector('.invoice-row');
                if (invoiceRow) {
                    invoiceRow.click(); // Trigger row click to expand
                    console.log('Invoice row auto-expanded');
                }
            }, 500);
        }
    }

    /**
     * Initialize export functionality
     */
    initExportFunctionality() {
        console.log('Initializing export functionality...');

        // Excel export
        const excelBtn = document.getElementById('export-excel-btn');
        if (excelBtn) {
            excelBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.exportToExcel();
            });
        }

        // PDF export
        const pdfBtn = document.getElementById('export-pdf-btn');
        if (pdfBtn) {
            pdfBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.exportToPdf();
            });
        }

        console.log('Export functionality initialized');
    }

    /**
     * Export to Excel
     */
    exportToExcel() {
        console.log('Exporting to Excel...');

        try {
            // Get current filter data
            const filterData = this.getFilterData();

            // Build URL with filters
            const params = new URLSearchParams(filterData);
            const exportUrl = `/admin/invoices/export/excel?${params.toString()}`;

            // Create temporary link and trigger download
            const link = document.createElement('a');
            link.href = exportUrl;
            link.download = `invoices_${new Date().toISOString().slice(0, 10)}.csv`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            console.log('Excel export initiated');

        } catch (error) {
            console.error('Error exporting to Excel:', error);
            alert('Lỗi khi xuất file Excel. Vui lòng thử lại.');
        }
    }

    /**
     * Export to PDF
     */
    exportToPdf() {
        console.log('Exporting to PDF...');

        try {
            // Get current filter data
            const filterData = this.getFilterData();

            // Build URL with filters
            const params = new URLSearchParams(filterData);
            const exportUrl = `/admin/invoices/export/pdf?${params.toString()}`;

            // Open in new window for PDF
            window.open(exportUrl, '_blank');

            console.log('PDF export initiated');

        } catch (error) {
            console.error('Error exporting to PDF:', error);
            alert('Lỗi khi xuất file PDF. Vui lòng thử lại.');
        }
    }
}

/**
 * Create return order from invoice
 * @param {number} invoiceId - The invoice ID
 */
function createReturnOrder(invoiceId) {
    console.log('Creating return order for invoice:', invoiceId);

    // Redirect to quick-order page with return type and invoice ID
    const returnUrl = `/admin/quick-order?type=return&invoice_id=${invoiceId}`;
    window.location.href = returnUrl;
}

// Export for use
window.InvoiceTableManager = InvoiceTableManager;
window.createReturnOrder = createReturnOrder;
