/**
 * Payment Table Manager
 * Extends BaseTableManager for payment-specific functionality
 */

class PaymentTableManager extends BaseTableManager {
    constructor() {
        super({
            tableId: 'payments_custom_table',
            containerId: 'payments_table_container',
            ajaxUrl: window.paymentAjaxUrl || '/admin/payment/ajax',
            module: 'payments',
            storageKey: 'payments_column_visibility',
            defaultFilters: {
                page: 1,
                per_page: 25,
                search: '',
                time_filter_display: 'this_month',
                date_from: '',
                date_to: '',
                payment_type: '',
                status: '',
                creator_id: '',
                staff_id: '',
                bank_account_id: '',
                reference_type: ''
            },
            defaultPerPage: 25
        });

        this.expandedRows = new Set(); // Track expanded rows
        this.summaryData = null; // Store summary data
    }

    // Override parent's init to add detail panel functionality
    init() {
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
        this.initSearch();
        this.initColumnVisibility();
        this.initSelectAll();
        this.initBulkActions();
        this.initResetFilters();

        // Create border spans for detail panel visual separation
        this.createBorderSpans();

        // Initialize detail panel
        this.initDetailPanel();

        this.isInitialized = true;
        console.log('PaymentTableManager initialized with virtual scrollbar from BaseTableManager');

        // Load initial data
        this.loadData();
    }

    /**
     * Load saved filter state from localStorage
     */
    loadSavedFilterState() {
        if (typeof window.KTGlobalFilter !== 'undefined') {
            const savedState = window.KTGlobalFilter.loadFilterState('payments');
            if (savedState) {
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
            window.KTGlobalFilter.saveFilterState('payments', this.currentFilters);
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
                    window.KTGlobalFilter.clearFilterState('payments');
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
        const searchInput = document.getElementById('payment_search');
        if (searchInput) searchInput.value = '';

        // Reset time filter to "Tháng này"
        const thisMonthRadio = document.querySelector('input[name="time_filter_display"][value="this_month"]');
        if (thisMonthRadio) thisMonthRadio.checked = true;

        // Reset date inputs
        const dateFrom = document.getElementById('date_from');
        const dateTo = document.getElementById('date_to');
        if (dateFrom) dateFrom.value = '';
        if (dateTo) dateTo.value = '';

        // Reset payment type
        const paymentTypeRadios = document.querySelectorAll('input[name="payment_type"]');
        paymentTypeRadios.forEach(radio => radio.checked = false);

        // Reset status checkboxes
        const statusCheckboxes = document.querySelectorAll('input[name="status[]"]');
        statusCheckboxes.forEach(checkbox => checkbox.checked = false);

        // Reset Select2 dropdowns
        $('#kt_payment_filter_form select').val(null).trigger('change');

        console.log('Filter UI reset to default state');
    }

    /**
     * Initialize search functionality
     */
    initSearch() {
        const searchInput = document.getElementById('payment_search');
        if (!searchInput) return;

        let searchTimeout;
        searchInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                this.currentFilters.search = e.target.value;
                this.currentFilters.page = 1;
                this.loadData();
            }, 500);
        });

        console.log('Payment search initialized');
    }

    /**
     * Initialize column visibility
     */
    initColumnVisibility() {
        if (typeof window.KTColumnVisibility !== 'undefined') {
            const config = {
                storageKey: this.config.storageKey,
                defaultVisibility: {
                    0: true,  // Checkbox
                    1: true,  // Mã phiếu
                    2: true,  // Loại
                    3: true,  // Số tiền
                    4: true,  // Khách hàng
                    5: true,  // Phương thức
                    6: true,  // Ngày thanh toán
                    7: true,  // Người tạo
                    8: false, // Ghi chú
                    9: true,  // Trạng thái
                    10: true  // Actions
                }
            };

            window.KTColumnVisibility.init(config);
            console.log('Payment column visibility initialized');
        }
    }

    /**
     * Initialize select all functionality
     */
    initSelectAll() {
        const selectAllCheckbox = document.querySelector('#payments_custom_table thead input[type="checkbox"]');
        if (!selectAllCheckbox) return;

        selectAllCheckbox.addEventListener('change', (e) => {
            const isChecked = e.target.checked;
            const checkboxes = document.querySelectorAll('#payments_custom_table tbody input[type="checkbox"]');
            
            checkboxes.forEach(checkbox => {
                checkbox.checked = isChecked;
                const paymentId = checkbox.closest('tr')?.dataset.paymentId;
                if (paymentId) {
                    if (isChecked) {
                        this.selectedItems.add(paymentId);
                    } else {
                        this.selectedItems.delete(paymentId);
                    }
                }
            });

            this.updateBulkActionsVisibility();
        });

        // Handle individual checkbox changes
        this.table.addEventListener('change', (e) => {
            if (e.target.type === 'checkbox' && e.target.closest('tbody')) {
                const paymentId = e.target.closest('tr')?.dataset.paymentId;
                if (paymentId) {
                    if (e.target.checked) {
                        this.selectedItems.add(paymentId);
                    } else {
                        this.selectedItems.delete(paymentId);
                    }
                }
                this.updateBulkActionsVisibility();
            }
        });

        console.log('Payment select all initialized');
    }

    /**
     * Initialize bulk actions
     */
    initBulkActions() {
        // Bulk actions will be implemented later
        console.log('Payment bulk actions initialized');
    }

    /**
     * Update bulk actions visibility based on selected items
     */
    updateBulkActionsVisibility() {
        const bulkActionsToolbar = document.querySelector('[data-kt-payment-table-toolbar="selected"]');
        const normalToolbar = document.querySelector('[data-kt-payment-table-toolbar="base"]');
        const selectedCount = document.querySelector('[data-kt-payment-table-select="selected_count"]');

        if (this.selectedItems.size > 0) {
            if (bulkActionsToolbar) bulkActionsToolbar.classList.remove('d-none');
            if (normalToolbar) normalToolbar.classList.add('d-none');
            if (selectedCount) selectedCount.textContent = this.selectedItems.size;
        } else {
            if (bulkActionsToolbar) bulkActionsToolbar.classList.add('d-none');
            if (normalToolbar) normalToolbar.classList.remove('d-none');
        }
    }

    /**
     * Load data from server
     */
    loadData() {
        console.log('Loading payments with filters:', this.currentFilters);

        // Save filter state
        this.saveFilterState();

        // Get filter data from form
        const filterData = this.getFilterData();

        // Merge with current filters
        const params = { ...this.currentFilters, ...filterData };

        // Show loading state
        this.showLoadingState();

        // Load summary data separately
        this.loadSummary(filterData);

        // Make AJAX request
        fetch(`${this.config.ajaxUrl}?${new URLSearchParams(params)}`)
            .then(response => response.json())
            .then(data => {
                console.log('Payment data loaded:', data);

                if (data.data) {
                    this.renderData(data.data);

                    // Transform pagination structure to match BaseTableManager expectations
                    const transformedData = {
                        recordsTotal: data.pagination?.total || 0,
                        recordsFiltered: data.pagination?.total || 0,
                        pagination: data.pagination
                    };

                    this.updatePagination(transformedData);

                    // Update virtual scrollbar after rendering data
                    setTimeout(() => {
                        this.updateVirtualScrollbar();
                        console.log('Virtual scrollbar updated after rendering payments');
                    }, 100);
                } else {
                    console.error('Failed to load payments:', data.message);
                    this.renderError(data.message || 'Lỗi tải dữ liệu');
                }
            })
            .catch(error => {
                console.error('Error loading payments:', error);
                this.renderError('Lỗi kết nối server');
            });
    }

    /**
     * Load summary data
     */
    loadSummary(filterData = null) {
        if (!filterData) {
            filterData = this.getFilterData();
        }

        // Build summary URL
        const summaryUrl = this.config.ajaxUrl.replace('/ajax', '/summary');

        console.log('=== LOADING SUMMARY ===');
        console.log('Summary URL:', summaryUrl);
        console.log('Filter data:', filterData);

        // Show loading state on summary cards
        this.showSummaryLoading();

        fetch(`${summaryUrl}?${new URLSearchParams(filterData)}`)
            .then(response => response.json())
            .then(data => {
                console.log('=== SUMMARY SUCCESS ===');
                console.log('Summary response:', data);

                if (data.success && data.data) {
                    console.log('Updating summary cards with:', data.data);
                    this.updateSummary(data.data);
                } else {
                    console.error('Failed to load summary data:', data);
                    this.showSummaryError('Lỗi: ' + (data.error || 'Không thể tải dữ liệu'));
                }
            })
            .catch(error => {
                console.error('=== SUMMARY ERROR ===');
                console.error('Error:', error);
                this.showSummaryError('Lỗi kết nối server');
            });
    }

    /**
     * Show loading state on summary cards
     */
    showSummaryLoading() {
        const loadingHtml = '<i class="fas fa-spinner fa-spin"></i> Đang tải...';

        const openingBalance = document.getElementById('opening_balance');
        const totalIncome = document.getElementById('total_income');
        const totalExpense = document.getElementById('total_expense');
        const closingBalance = document.getElementById('closing_balance');

        if (openingBalance) openingBalance.innerHTML = loadingHtml;
        if (totalIncome) totalIncome.innerHTML = loadingHtml;
        if (totalExpense) totalExpense.innerHTML = loadingHtml;
        if (closingBalance) closingBalance.innerHTML = loadingHtml;
    }

    /**
     * Show error message on summary cards
     */
    showSummaryError(message) {
        console.log('Showing summary error:', message);
        const errorHtml = `<span class="text-danger">${message}</span>`;

        const openingBalance = document.getElementById('opening_balance');
        const totalIncome = document.getElementById('total_income');
        const totalExpense = document.getElementById('total_expense');
        const closingBalance = document.getElementById('closing_balance');

        if (openingBalance) openingBalance.innerHTML = errorHtml;
        if (totalIncome) totalIncome.innerHTML = errorHtml;
        if (totalExpense) totalExpense.innerHTML = errorHtml;
        if (closingBalance) closingBalance.innerHTML = errorHtml;
    }

    /**
     * Get filter data from form
     */
    getFilterData() {
        const filterForm = document.getElementById('kt_payment_filter_form');
        if (!filterForm) return {};

        const data = {};

        // Get all inputs
        filterForm.querySelectorAll('input, select').forEach(input => {
            const name = input.name || input.id;
            if (!name) return;

            // Skip time_filter_display radio buttons - we'll use hidden #time_filter instead
            if (name === 'time_filter_display') {
                return;
            }

            if (input.type === 'checkbox') {
                if (name.endsWith('[]')) {
                    if (!data[name]) data[name] = [];
                    if (input.checked) {
                        data[name].push(input.value);
                    }
                } else {
                    data[name] = input.checked;
                }
            } else if (input.type === 'radio') {
                if (input.checked) {
                    data[name] = input.value;
                }
            } else {
                data[name] = input.value;
            }
        });

        console.log('Filter data collected:', data);
        return data;
    }

    /**
     * Show loading state
     */
    showLoadingState() {
        const tbody = this.table.querySelector('tbody');
        if (tbody) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="11" class="text-center py-10">
                        <div class="d-flex flex-column align-items-center">
                            <div class="spinner-border text-primary mb-3" role="status">
                                <span class="visually-hidden">Đang tải...</span>
                            </div>
                            <div class="text-muted">Đang tải dữ liệu...</div>
                        </div>
                    </td>
                </tr>
            `;
        }
    }

    /**
     * Render data into table
     */
    renderData(payments) {
        console.log('Rendering payments:', payments.length);

        const tbody = this.table.querySelector('tbody');
        if (!tbody) return;

        if (!payments || payments.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="11" class="text-center py-10">
                        <div class="d-flex flex-column align-items-center">
                            <i class="fas fa-inbox text-muted fs-2x mb-3"></i>
                            <div class="text-muted">Không có dữ liệu</div>
                        </div>
                    </td>
                </tr>
            `;
            return;
        }

        let rowsHTML = '';
        payments.forEach(payment => {
            rowsHTML += this.renderPaymentRow(payment);
        });

        tbody.innerHTML = rowsHTML;

        // Bind row events
        this.bindRowEvents();

        // Update virtual scrollbar after data is rendered
        setTimeout(() => {
            this.updateVirtualScrollbar();
            console.log('Virtual scrollbar updated after rendering payments');
        }, 100);
    }

    /**
     * Render error message
     */
    renderError(message) {
        const tbody = this.table.querySelector('tbody');
        if (tbody) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="11" class="text-center py-10">
                        <div class="d-flex flex-column align-items-center">
                            <i class="fas fa-exclamation-triangle text-danger fs-2x mb-3"></i>
                            <div class="text-danger">${message}</div>
                        </div>
                    </td>
                </tr>
            `;
        }
    }

    /**
     * Render a single payment row
     */
    renderPaymentRow(payment) {
        const customerName = payment.customer ? payment.customer.name : 'Khách lẻ';
        const typeBadge = this.getTypeBadge(payment.payment_type);
        const amountClass = payment.payment_type === 'receipt' ? 'text-success' : 'text-danger';
        const amountPrefix = payment.payment_type === 'receipt' ? '+' : '-';

        // Determine income type based on reference_type
        let incomeType = 'N/A';
        if (payment.reference_type === 'invoice') {
            incomeType = 'Tiền khách trả';
        } else if (payment.reference_type === 'return_order') {
            incomeType = 'Tiền hoàn trả';
        } else if (payment.reference_type === 'manual') {
            incomeType = payment.income_type || 'Thu khác';
        } else {
            incomeType = payment.income_type || 'N/A';
        }

        return `
            <tr data-payment-id="${payment.id}" class="payment-row" style="cursor: pointer;">
                <td>
                    <div class="form-check form-check-sm form-check-custom form-check-solid">
                        <input class="form-check-input" type="checkbox" value="${payment.id}" onclick="event.stopPropagation();" />
                    </div>
                </td>
                <td>
                    <div class="d-flex flex-column">
                        <span class="text-gray-800 text-hover-primary mb-1 fw-bold">${payment.payment_number}</span>
                        <span class="text-muted fs-7">${payment.reference_code || ''}</span>
                    </div>
                </td>
                <td>
                    <div class="d-flex flex-column">
                        <span class="text-gray-800 mb-1">${this.formatDate(payment.payment_date)}</span>
                        <span class="text-muted fs-7">${this.formatTime(payment.created_at)}</span>
                    </div>
                </td>
                <td>
                    <div class="d-flex flex-column">
                        ${typeBadge}
                        <span class="text-muted fs-7">${incomeType}</span>
                    </div>
                </td>
                <td>
                    <div class="d-flex flex-column">
                        <span class="text-gray-800 mb-1">${customerName}</span>
                        <span class="text-muted fs-7">${payment.description || ''}</span>
                    </div>
                </td>
                <td class="text-end">
                    <div class="d-flex flex-column align-items-end">
                        <span class="text-gray-800 fw-bold fs-6 ${amountClass}">${amountPrefix}${this.formatCurrency(payment.amount)}</span>
                        <span class="text-muted fs-7">${payment.payment_method_display || payment.payment_method || 'N/A'}</span>
                    </div>
                </td>
            </tr>
        `;
    }

    /**
     * Helper: Get type badge HTML
     */
    getTypeBadge(type) {
        if (type === 'receipt') {
            return '<span class="badge badge-light-success">Phiếu thu</span>';
        } else if (type === 'disbursement' || type === 'payment') {
            return '<span class="badge badge-light-danger">Phiếu chi</span>';
        }
        return '<span class="badge badge-light-secondary">N/A</span>';
    }

    /**
     * Helper: Format date
     */
    formatDate(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleDateString('vi-VN');
    }

    /**
     * Helper: Format time
     */
    formatTime(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
    }

    /**
     * Helper: Format currency
     */
    formatCurrency(amount) {
        if (!amount) return '0₫';
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(amount);
    }

    /**
     * Update summary cards
     */
    updateSummary(summary) {
        console.log('Updating summary:', summary);

        // Update opening balance
        const openingBalance = document.getElementById('opening_balance');
        if (openingBalance && summary.opening_balance !== undefined) {
            openingBalance.innerHTML = this.formatCurrency(summary.opening_balance);
        }

        // Update total income
        const totalIncome = document.getElementById('total_income');
        if (totalIncome && summary.total_income !== undefined) {
            totalIncome.innerHTML = this.formatCurrency(summary.total_income);
        }

        // Update total expense
        const totalExpense = document.getElementById('total_expense');
        if (totalExpense && summary.total_expense !== undefined) {
            totalExpense.innerHTML = this.formatCurrency(summary.total_expense);
        }

        // Update closing balance
        const closingBalance = document.getElementById('closing_balance');
        if (closingBalance && summary.closing_balance !== undefined) {
            closingBalance.innerHTML = this.formatCurrency(summary.closing_balance);
        }
    }

    /**
     * Bind row events (click to expand detail)
     */
    bindRowEvents() {
        const rows = this.table.querySelectorAll('tbody tr.payment-row');
        rows.forEach(row => {
            row.addEventListener('click', (e) => {
                // Don't trigger if clicking on checkbox
                if (e.target.type === 'checkbox') return;

                const paymentId = row.dataset.paymentId;
                if (paymentId) {
                    this.togglePaymentDetail(paymentId, row);
                }
            });
        });
    }

    /**
     * Initialize detail panel
     */
    initDetailPanel() {
        // Detail panel will be handled by row click events
        console.log('Payment detail panel initialized');
    }

    /**
     * Toggle payment detail panel
     */
    togglePaymentDetail(paymentId, row) {
        // Check if this row is already expanded
        if (this.expandedRows.has(paymentId)) {
            this.collapsePaymentDetail(paymentId, row);
        } else {
            this.expandPaymentDetail(paymentId, row);
        }
    }

    /**
     * Expand payment detail
     */
    expandPaymentDetail(paymentId, row) {
        console.log('Expanding payment detail:', paymentId);

        // Collapse any other expanded rows first
        this.expandedRows.forEach(id => {
            const otherRow = this.table.querySelector(`tr[data-payment-id="${id}"]`);
            if (otherRow) {
                this.collapsePaymentDetail(id, otherRow);
            }
        });

        // Mark this row as expanded
        this.expandedRows.add(paymentId);
        row.classList.add('expanded');

        // Insert detail row
        const detailRow = document.createElement('tr');
        detailRow.className = 'payment-detail-row';
        detailRow.dataset.paymentId = paymentId;
        detailRow.innerHTML = `
            <td colspan="11" class="p-0">
                <div class="payment-detail-container">
                    <div class="d-flex justify-content-center align-items-center py-10">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Đang tải...</span>
                        </div>
                    </div>
                </div>
            </td>
        `;

        row.after(detailRow);

        // Load payment detail data
        this.loadPaymentDetail(paymentId);

        // Update border position
        this.updateBorderElementsPosition(row);
    }

    /**
     * Collapse payment detail
     */
    collapsePaymentDetail(paymentId, row) {
        console.log('Collapsing payment detail:', paymentId);

        // Remove from expanded set
        this.expandedRows.delete(paymentId);
        row.classList.remove('expanded');

        // Remove detail row
        const detailRow = this.table.querySelector(`tr.payment-detail-row[data-payment-id="${paymentId}"]`);
        if (detailRow) {
            detailRow.remove();
        }

        // Hide borders
        this.cleanupBorderElements();
    }

    /**
     * Load payment detail data
     */
    loadPaymentDetail(paymentId) {
        const detailUrl = `/admin/payment/${paymentId}/detail`;

        fetch(detailUrl)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.html) {
                    const detailRow = this.table.querySelector(`tr.payment-detail-row[data-payment-id="${paymentId}"]`);
                    if (detailRow) {
                        const container = detailRow.querySelector('.payment-detail-container');
                        if (container) {
                            container.innerHTML = data.html;
                        }
                    }
                } else {
                    console.error('Failed to load payment detail:', data.message);
                }
            })
            .catch(error => {
                console.error('Error loading payment detail:', error);
                const detailRow = this.table.querySelector(`tr.payment-detail-row[data-payment-id="${paymentId}"]`);
                if (detailRow) {
                    const container = detailRow.querySelector('.payment-detail-container');
                    if (container) {
                        container.innerHTML = `
                            <div class="alert alert-danger m-5">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Lỗi tải dữ liệu chi tiết
                            </div>
                        `;
                    }
                }
            });
    }

    /**
     * Create border spans for detail panel visual separation
     */
    createBorderSpans() {
        const tableContainer = document.getElementById(this.config.containerId);
        if (!tableContainer) return;

        // Create left border span
        const leftBorderSpan = document.createElement('div');
        leftBorderSpan.className = 'detail-panel-border-left';
        leftBorderSpan.style.cssText = `
            position: absolute;
            left: 0;
            width: 2px;
            background: #e4e6ea;
            z-index: 5;
            display: none;
        `;

        // Create right border span
        const rightBorderSpan = document.createElement('div');
        rightBorderSpan.className = 'detail-panel-border-right';
        rightBorderSpan.style.cssText = `
            position: absolute;
            right: 0;
            width: 2px;
            background: #e4e6ea;
            z-index: 5;
            display: none;
        `;

        tableContainer.appendChild(leftBorderSpan);
        tableContainer.appendChild(rightBorderSpan);

        this.borderLeft = leftBorderSpan;
        this.borderRight = rightBorderSpan;

        console.log('Border spans created for payment detail panels');
    }

    /**
     * Update border elements position based on current scroll and active row
     */
    updateBorderElementsPosition(activeRow) {
        if (!this.borderLeft || !this.borderRight || !activeRow) {
            return;
        }

        const tableContainer = document.getElementById(this.config.containerId);
        if (!tableContainer) return;

        // Get active row position and height
        const rowRect = activeRow.getBoundingClientRect();
        const containerRect = tableContainer.getBoundingClientRect();
        const rowTop = activeRow.offsetTop;
        const rowHeight = activeRow.offsetHeight;

        // Get detail row
        const detailRow = activeRow.nextElementSibling;
        if (!detailRow || !detailRow.classList.contains('payment-detail-row')) {
            return;
        }

        const detailHeight = detailRow.offsetHeight;
        const totalHeight = rowHeight + detailHeight;

        // Get scroll position
        const scrollLeft = tableContainer.scrollLeft;

        // Position borders
        this.borderLeft.style.top = `${rowTop}px`;
        this.borderLeft.style.left = `${scrollLeft}px`;
        this.borderLeft.style.height = `${totalHeight}px`;
        this.borderLeft.style.display = 'block';

        this.borderRight.style.top = `${rowTop}px`;
        this.borderRight.style.right = `${-scrollLeft}px`;
        this.borderRight.style.height = `${totalHeight}px`;
        this.borderRight.style.display = 'block';

        console.log('Border elements position updated for payment detail');
    }

    /**
     * Setup scroll listener for horizontal table scroll
     */
    setupBorderScrollListener(activeRow) {
        const tableContainer = document.getElementById(this.config.containerId);
        if (!tableContainer) return;

        // Remove existing listener to prevent duplicates
        tableContainer.removeEventListener('scroll', this.borderScrollHandler);

        // Create new handler
        this.borderScrollHandler = () => {
            this.updateBorderElementsPosition(activeRow);
        };

        // Add scroll listener
        tableContainer.addEventListener('scroll', this.borderScrollHandler);

        console.log('Border scroll listener setup for payment table container');
    }

    /**
     * Clean up border elements and listeners when row is collapsed
     */
    cleanupBorderElements() {
        const tableContainer = document.getElementById(this.config.containerId);
        if (tableContainer && this.borderScrollHandler) {
            tableContainer.removeEventListener('scroll', this.borderScrollHandler);
        }

        if (this.borderLeft) {
            this.borderLeft.style.display = 'none';
        }

        if (this.borderRight) {
            this.borderRight.style.display = 'none';
        }

        this.borderScrollHandler = null;

        console.log('Border elements cleaned up for payments');
    }
}

// Export for use
window.PaymentTableManager = PaymentTableManager;

