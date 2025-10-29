/**
 * Product Table Manager
 * Extends BaseTableManager for product-specific functionality
 */

class ProductTableManager extends BaseTableManager {
    constructor() {
        super({
            tableId: 'kt_products_table',
            containerId: 'kt_products_table_container',
            ajaxUrl: window.productRoutes?.data || '/admin/products/ajax',
            module: 'products',
            storageKey: 'products_column_visibility',
            defaultFilters: {
                page: 1,
                per_page: 25,
                search: '',
                time_filter_display: 'this_month',
                date_from: '',
                date_to: '',
                branch_shop_ids: [],
                category_ids: [],
                status: [],
                stock_status: [],
                price_from: '',
                price_to: '',
                created_by: []
            },
            defaultPerPage: 25
        });

        this.expandedRows = new Set(); // Track expanded rows
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

        this.isInitialized = true;
        console.log('ProductTableManager initialized with virtual scrollbar from BaseTableManager');

        // Load initial data
        this.loadData();
    }

    /**
     * Load saved filter state from localStorage
     */
    loadSavedFilterState() {
        if (typeof window.KTGlobalFilter !== 'undefined' && typeof window.KTGlobalFilter.loadFilterState === 'function') {
            const savedState = window.KTGlobalFilter.loadFilterState('products');
            if (savedState) {
                // Merge saved state with current filters
                this.currentFilters = { ...this.currentFilters, ...savedState };
                console.log('Loaded saved filter state:', this.currentFilters);
            }
        } else {
            console.warn('KTGlobalFilter.loadFilterState is not available yet');
        }
    }

    /**
     * Save current filter state to localStorage
     */
    saveFilterState() {
        if (typeof window.KTGlobalFilter !== 'undefined' && typeof window.KTGlobalFilter.saveFilterState === 'function') {
            window.KTGlobalFilter.saveFilterState('products', this.currentFilters);
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
                if (typeof window.KTGlobalFilter !== 'undefined' && typeof window.KTGlobalFilter.clearFilterState === 'function') {
                    window.KTGlobalFilter.clearFilterState('products');
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
        const searchInput = document.querySelector('input[data-kt-products-table-filter="search"]');
        if (searchInput) searchInput.value = '';

        // Reset time filter to "Tháng này"
        const thisMonthRadio = document.querySelector('input[name="time_filter_display"][value="this_month"]');
        if (thisMonthRadio) thisMonthRadio.checked = true;

        // Reset date inputs
        const dateFrom = document.getElementById('date_from');
        const dateTo = document.getElementById('date_to');
        if (dateFrom) dateFrom.value = '';
        if (dateTo) dateTo.value = '';

        // Reset branch shop filter
        const branchShopFilter = $('#branch_shop_filter');
        if (branchShopFilter.length) {
            branchShopFilter.val(null).trigger('change');
        }

        // Reset category filter
        const categoryFilter = $('#category_filter');
        if (categoryFilter.length) {
            categoryFilter.val(null).trigger('change');
        }

        // Reset creator filter
        const creatorFilter = $('#creator_filter');
        if (creatorFilter.length) {
            creatorFilter.val(null).trigger('change');
        }

        // Reset status checkboxes
        $('input[name="status[]"]').prop('checked', false);
        $('input[name="status[]"][value="active"]').prop('checked', true);

        // Reset stock status checkboxes
        $('input[name="stock_status[]"]').prop('checked', false);
        $('input[name="stock_status[]"][value="in_stock"]').prop('checked', true);

        // Reset price range
        const priceFrom = document.getElementById('price_from');
        const priceTo = document.getElementById('price_to');
        if (priceFrom) priceFrom.value = '';
        if (priceTo) priceTo.value = '';

        console.log('Filter UI reset to default state');
    }

    // ===== PRODUCT-SPECIFIC FUNCTIONALITY =====

    /**
     * Create border spans for detail panel visual separation
     */
    createBorderSpans() {
        const tableContainer = document.getElementById('kt_products_table_container');
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
     * Get status badge HTML for product status
     */
    getStatusBadge(status) {
        const badges = {
            'active': '<span class="badge badge-light-success">Hoạt động</span>',
            'inactive': '<span class="badge badge-light-secondary">Không hoạt động</span>',
            'out_of_stock': '<span class="badge badge-light-danger">Hết hàng</span>'
        };
        return badges[status] || '<span class="badge badge-light">N/A</span>';
    }

    /**
     * Get stock status badge HTML
     */
    getStockStatusBadge(quantity) {
        if (quantity === 0) {
            return '<span class="badge badge-light-danger">Hết hàng</span>';
        } else if (quantity < 10) {
            return '<span class="badge badge-light-warning">Sắp hết</span>';
        } else {
            return '<span class="badge badge-light-success">Còn hàng</span>';
        }
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
     */
    formatDate(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleDateString('vi-VN', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    // ===== REQUIRED IMPLEMENTATIONS FROM BaseTableManager =====

    /**
     * Initialize search functionality
     */
    initSearch() {
        const searchInput = document.querySelector('#kt_products_search');
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

        console.log('Products search initialized');
    }

    /**
     * Initialize column visibility functionality
     */
    initColumnVisibility() {
        // Initialize column visibility with KTColumnVisibility
        if (typeof window.KTColumnVisibility !== 'undefined') {
            const defaultVisibility = {
                0: true,  // Info icon
                1: true,  // Checkbox
                2: true,  // Tên sản phẩm
                3: true,  // SKU
                4: true,  // Giá
                5: true,  // Tồn kho
                6: true,  // Trạng thái
                7: true,  // Ngày tạo
                8: true   // Actions
            };

            this.columnVisibility = window.KTColumnVisibility.init({
                storageKey: 'products_column_visibility',
                defaultVisibility: defaultVisibility,
                triggerSelector: '#column_visibility_trigger',
                panelSelector: '#column_visibility_panel',
                toggleSelector: '.column-toggle',
                tableSelector: '#kt_products_table',
                onToggle: function(columnIndex, isVisible) {
                    console.log('Products column visibility changed:', columnIndex, isVisible);
                }
            });
        } else {
            console.warn('KTColumnVisibility not available');
        }

        console.log('Products column visibility initialized');
    }

    /**
     * Initialize select all functionality
     */
    initSelectAll() {
        const selectAllCheckbox = document.querySelector('#kt_products_select_all');
        if (!selectAllCheckbox) return;

        selectAllCheckbox.addEventListener('change', (e) => {
            const isChecked = e.target.checked;
            const rowCheckboxes = this.getRowCheckboxes();

            rowCheckboxes.forEach(checkbox => {
                checkbox.checked = isChecked;
                const productId = checkbox.value;
                if (isChecked) {
                    this.selectedItems.add(productId);
                } else {
                    this.selectedItems.delete(productId);
                }
            });

            this.updateBulkActionButtons();
        });

        console.log('Products select all initialized');
    }

    /**
     * Initialize bulk actions functionality
     */
    initBulkActions() {
        // Bulk action buttons will be handled by specific event listeners
        console.log('Products bulk actions initialized');
    }

    /**
     * Get row checkboxes
     */
    getRowCheckboxes() {
        return document.querySelectorAll('.product-checkbox');
    }

    /**
     * Update bulk action buttons state
     */
    updateBulkActionButtons() {
        const selectedCount = this.selectedItems.size;
        const bulkActionButtons = document.querySelectorAll('.bulk-action-btn');
        
        bulkActionButtons.forEach(btn => {
            if (selectedCount > 0) {
                btn.disabled = false;
                btn.classList.remove('disabled');
            } else {
                btn.disabled = true;
                btn.classList.add('disabled');
            }
        });
    }

    /**
     * Load products data from server
     */
    loadData() {
        console.log('Loading products data...');

        // Save current filter state
        this.saveFilterState();

        // Cancel previous request if exists
        if (this.abortController) {
            this.abortController.abort();
        }

        // Create new AbortController for this request
        this.abortController = new AbortController();

        // Show loading state
        this.renderLoading();

        // Build request parameters
        const params = {
            page: this.currentFilters.page || 1,
            per_page: this.currentFilters.per_page || this.config.defaultPerPage,
            search: this.currentFilters.search || '',
            time_filter_display: this.currentFilters.time_filter_display || 'this_month',
            date_from: this.currentFilters.date_from || '',
            date_to: this.currentFilters.date_to || '',
            status: this.currentFilters.status || '',
            stock_status: this.currentFilters.stock_status || '',
            price_from: this.currentFilters.price_from || '',
            price_to: this.currentFilters.price_to || ''
        };

        // Add array parameters separately
        const branch_shop_ids = this.currentFilters.branch_shop_ids || [];
        const category_ids = this.currentFilters.category_ids || [];
        const created_by = this.currentFilters.created_by || [];

        console.log('Loading products with params:', params);
        console.log('Branch shop IDs:', branch_shop_ids);
        console.log('Category IDs:', category_ids);
        console.log('Created by:', created_by);

        // Build URL with array parameters
        const urlParams = new URLSearchParams(params);

        // Add array parameters
        if (Array.isArray(branch_shop_ids) && branch_shop_ids.length > 0) {
            branch_shop_ids.forEach(id => urlParams.append('branch_shop_ids[]', id));
        }
        if (Array.isArray(category_ids) && category_ids.length > 0) {
            category_ids.forEach(id => urlParams.append('category_ids[]', id));
        }
        if (Array.isArray(created_by) && created_by.length > 0) {
            created_by.forEach(id => urlParams.append('created_by[]', id));
        }

        // Make AJAX request
        fetch(`${this.config.ajaxUrl}?${urlParams.toString()}`, {
            signal: this.abortController.signal
        })
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

                    // Update virtual scrollbar after rendering data
                    setTimeout(() => {
                        this.updateVirtualScrollbar();
                        console.log('Virtual scrollbar updated after data load');
                    }, 100);
                } else {
                    console.error('Failed to load products:', data.message);
                    this.renderError(data.message || 'Lỗi tải dữ liệu');
                }
            })
            .catch(error => {
                if (error.name !== 'AbortError') {
                    console.error('Error loading products:', error);
                    this.renderError('Lỗi tải dữ liệu sản phẩm');
                }
            })
            .finally(() => {
                this.abortController = null;
            });
    }

    /**
     * Render loading state
     */
    renderLoading() {
        const tbody = this.table.querySelector('tbody');
        if (!tbody) return;

        tbody.innerHTML = `
            <tr>
                <td colspan="9" class="text-center py-10">
                    <div class="d-flex justify-content-center align-items-center">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        <span>Đang tải dữ liệu...</span>
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
        if (!tbody) return;

        tbody.innerHTML = `
            <tr>
                <td colspan="9" class="text-center py-10">
                    <div class="text-danger">${message}</div>
                </td>
            </tr>
        `;
    }

    /**
     * Render data into table
     */
    renderData(data) {
        console.log('renderData called with products:', data.length);

        const tbody = this.table.querySelector('tbody');
        if (!tbody) return;

        if (!data || data.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="9" class="text-center py-10">
                        <div class="text-gray-600">Không có sản phẩm nào</div>
                    </td>
                </tr>
            `;
            return;
        }

        let rowsHTML = '';
        data.forEach(product => {
            rowsHTML += this.renderProductRow(product);
        });

        tbody.innerHTML = rowsHTML;
        console.log('Generated rows HTML length:', rowsHTML.length);

        // Attach event listeners to rows
        this.attachRowEventListeners();

        // Update virtual scrollbar after data is rendered
        setTimeout(() => {
            this.updateVirtualScrollbar();
            console.log('Virtual scrollbar updated after rendering products');
        }, 100);
    }

    /**
     * Render a single product row
     */
    renderProductRow(product) {
        console.log('renderProductRow called with product:', product.id, product.product_name);

        // Render thumbnail and product name (similar to table.js line 72-83)
        const thumbnail = product.product_thumbnail || product.product_image || '/admin-assets/assets/images/upload-thumbnail.png';
        const thumbnailHtml = `
            <div class="d-flex align-items-center">
                <div class="symbol symbol-45px me-5">
                    <img src="${thumbnail}" alt="${product.product_name}" />
                </div>
                <div class="d-flex justify-content-start flex-column">
                    <span class="text-dark fw-bolder fs-6" style="cursor: pointer;">${product.product_name}</span>
                    <span class="text-muted fw-bold text-muted d-block fs-7">${product.product_type || 'Simple'}</span>
                </div>
            </div>
        `;

        // Render price (similar to table.js line 88)
        const priceHtml = `<span class="fw-bolder">${parseFloat(product.sale_price || 0).toLocaleString()} VND</span>`;

        // Render stock status with icon (similar to table.js line 94-130)
        const stockQuantity = product.total_stock || product.stock_quantity || 0;
        const stockStatus = product.stock_status || { status: 'unknown', label: 'Unknown', class: 'secondary' };
        let iconClass = '';
        let statusClass = '';
        let tooltip = '';

        switch(stockStatus.status) {
            case 'in_stock':
                iconClass = 'fas fa-check-circle text-success';
                statusClass = 'stock-status-in-stock';
                tooltip = `In Stock: ${stockQuantity} units available`;
                break;
            case 'low_stock':
                iconClass = 'fas fa-exclamation-triangle text-warning';
                statusClass = 'stock-status-low-stock';
                tooltip = `Low Stock: Only ${stockQuantity} units remaining`;
                break;
            case 'out_of_stock':
                iconClass = 'fas fa-times-circle text-danger';
                statusClass = 'stock-status-out-of-stock';
                tooltip = `Out of Stock: ${stockQuantity} units`;
                break;
            default:
                iconClass = 'fas fa-question-circle';
                statusClass = 'stock-status-loading';
                tooltip = `Stock status unknown`;
        }

        const stockHtml = `
            <span class="stock-status-badge ${statusClass} stock-status-tooltip" data-tooltip="${tooltip}">
                <i class="${iconClass}"></i>
                <span class="stock-quantity-number">${stockQuantity}</span>
                <span class="ms-1">${stockStatus.label}</span>
            </span>
        `;

        // Render status badge (similar to table.js line 135)
        const statusHtml = product.badge_status || `<span class="badge badge-light-secondary">${product.product_status}</span>`;

        // Render date (similar to table.js line 141)
        const dateHtml = this.formatDate(product.created_at);

        // Render actions dropdown (similar to table.js line 149-218)
        const actionsHtml = `
            <div class="dropdown position-relative">
                <button class="btn btn-light btn-active-light-primary btn-sm action-dropdown-btn"
                        type="button"
                        data-product-id="${product.id}"
                        data-product-status="${product.product_status}">
                    Actions
                    <i class="fas fa-chevron-down ms-2 fs-7 dropdown-arrow"></i>
                </button>
                <div class="action-dropdown-menu" style="display: none;">
                    <!-- Product Actions -->
                    <div class="dropdown-header">
                        <small class="text-muted text-uppercase fw-bold">Product Actions</small>
                    </div>

                    <a class="dropdown-item" href="${window.location.origin}/admin/products/${product.id}/edit">
                        <i class="fas fa-edit text-primary me-2"></i>
                        Edit Product
                    </a>

                    <a class="dropdown-item" href="#" onclick="duplicateProduct(${product.id}); return false;">
                        <i class="fas fa-copy text-info me-2"></i>
                        Duplicate
                    </a>

                    <a class="dropdown-item" href="#" onclick="manageStock(${product.id}); return false;">
                        <i class="fas fa-boxes text-warning me-2"></i>
                        Manage Stock
                    </a>

                    <a class="dropdown-item" href="#" onclick="viewProductHistory(${product.id}); return false;">
                        <i class="fas fa-history text-secondary me-2"></i>
                        View History
                    </a>

                    <div class="dropdown-divider"></div>

                    <!-- Status Actions -->
                    <div class="dropdown-header">
                        <small class="text-muted text-uppercase fw-bold">Status Actions</small>
                    </div>

                    ${product.product_status === 'publish' ? `
                        <a class="dropdown-item" href="#" onclick="changeProductStatus(${product.id}, 'draft'); return false;">
                            <i class="fas fa-pause text-warning me-2"></i>
                            Set to Draft
                        </a>
                    ` : `
                        <a class="dropdown-item" href="#" onclick="changeProductStatus(${product.id}, 'publish'); return false;">
                            <i class="fas fa-play text-success me-2"></i>
                            Publish
                        </a>
                    `}

                    <div class="dropdown-divider"></div>

                    <!-- Danger Zone -->
                    <div class="dropdown-header">
                        <small class="text-muted text-uppercase fw-bold">Danger Zone</small>
                    </div>

                    <a class="dropdown-item text-danger" href="#" data-kt-products-table-filter="delete_row" data-id="${product.id}">
                        <i class="fas fa-trash text-danger me-2"></i>
                        Delete Product
                    </a>
                </div>
            </div>
        `;

        return `
            <tr class="product-row" data-product-id="${product.id}">
                <td>
                    <div class="form-check form-check-sm form-check-custom form-check-solid">
                        <input class="form-check-input product-checkbox" type="checkbox" value="${product.id}" />
                    </div>
                </td>
                <td>${thumbnailHtml}</td>
                <td><span class="text-dark fw-bold d-block fs-6">${product.sku || 'N/A'}</span></td>
                <td>${priceHtml}</td>
                <td class="stock-quantity-column">${stockHtml}</td>
                <td>${statusHtml}</td>
                <td><span class="text-dark fw-bold d-block fs-6">${dateHtml}</span></td>
                <td class="text-end">${actionsHtml}</td>
            </tr>
        `;
    }

    /**
     * Attach event listeners to table rows
     */
    attachRowEventListeners() {
        // Row click to expand detail panel
        const rows = this.table.querySelectorAll('.product-row');
        rows.forEach(row => {
            row.addEventListener('click', (e) => {
                // Don't expand if clicking on checkbox, links, action buttons, or dropdown
                if (e.target.closest('.product-checkbox') ||
                    e.target.closest('a') ||
                    e.target.closest('[data-kt-menu-trigger]') ||
                    e.target.closest('.menu') ||
                    e.target.closest('.dropdown') ||
                    e.target.closest('.action-dropdown-btn')) {
                    return;
                }

                const productId = row.dataset.productId;
                console.log('Row clicked, product ID:', productId);
                console.log('About to call toggleRowExpansion');
                this.toggleRowExpansion(row, productId);
            });
        });

        // Checkbox change event
        const checkboxes = this.getRowCheckboxes();
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', (e) => {
                const productId = e.target.value;
                if (e.target.checked) {
                    this.selectedItems.add(productId);
                } else {
                    this.selectedItems.delete(productId);
                }
                this.updateBulkActionButtons();
            });
        });

        // Initialize action dropdown menus
        this.initActionMenus();
    }

    /**
     * Initialize action dropdown menus (similar to table.js line 588-606)
     */
    initActionMenus() {
        const actionButtons = document.querySelectorAll('.action-dropdown-btn');

        actionButtons.forEach(button => {
            // Remove existing event listeners to prevent duplicates
            button.removeEventListener('click', this.handleActionDropdownClick);

            // Add click event listener
            button.addEventListener('click', this.handleActionDropdownClick.bind(this));
        });

        // Close dropdowns when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.dropdown')) {
                this.closeAllActionDropdowns();
            }
        });
    }

    /**
     * Handle action dropdown click (similar to table.js line 609-646)
     */
    handleActionDropdownClick(e) {
        e.preventDefault();
        e.stopPropagation();

        const button = e.currentTarget;
        const dropdown = button.closest('.dropdown');
        const menu = dropdown.querySelector('.action-dropdown-menu');
        const arrow = button.querySelector('.dropdown-arrow');

        // Close all other dropdowns first
        this.closeAllActionDropdowns();

        // Toggle current dropdown
        const isVisible = menu.style.display === 'block';

        if (!isVisible) {
            // Show dropdown
            menu.style.display = 'block';
            button.classList.add('active');
            arrow.style.transform = 'rotate(180deg)';

            // Position dropdown
            this.positionDropdown(button, menu);

            // Add animation
            menu.style.opacity = '0';
            menu.style.transform = 'translateY(-10px)';

            setTimeout(() => {
                menu.style.transition = 'all 0.2s ease';
                menu.style.opacity = '1';
                menu.style.transform = 'translateY(0)';
            }, 10);
        } else {
            // Hide dropdown
            this.hideDropdown(button, menu, arrow);
        }
    }

    /**
     * Position dropdown relative to button (similar to table.js line 649-672)
     */
    positionDropdown(button, menu) {
        const buttonRect = button.getBoundingClientRect();
        const menuWidth = 200; // Fixed width for consistency

        // Calculate position
        let left = buttonRect.right - menuWidth;
        let top = buttonRect.bottom + 5;

        // Adjust if dropdown would go off-screen
        if (left < 10) {
            left = 10;
        }

        if (top + menu.offsetHeight > window.innerHeight - 20) {
            top = buttonRect.top - menu.offsetHeight - 5;
        }

        // Apply positioning
        menu.style.position = 'fixed';
        menu.style.left = left + 'px';
        menu.style.top = top + 'px';
        menu.style.zIndex = '1050';
        menu.style.minWidth = menuWidth + 'px';
    }

    /**
     * Hide dropdown with animation (similar to table.js line 675-685)
     */
    hideDropdown(button, menu, arrow) {
        menu.style.transition = 'all 0.2s ease';
        menu.style.opacity = '0';
        menu.style.transform = 'translateY(-10px)';

        setTimeout(() => {
            menu.style.display = 'none';
            button.classList.remove('active');
            arrow.style.transform = 'rotate(0deg)';
        }, 200);
    }

    /**
     * Close all action dropdowns (similar to table.js line 688-698)
     */
    closeAllActionDropdowns() {
        const openMenus = document.querySelectorAll('.action-dropdown-menu[style*="display: block"]');

        openMenus.forEach(menu => {
            const dropdown = menu.closest('.dropdown');
            const button = dropdown.querySelector('.action-dropdown-btn');
            const arrow = button.querySelector('.dropdown-arrow');

            this.hideDropdown(button, menu, arrow);
        });
    }

    /**
     * Toggle row expansion to show/hide detail panel
     */
    toggleRowExpansion(row, productId) {
        console.log('Toggling row expansion for product:', productId);

        const $row = $(row);

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
        $('.product-row').removeClass('expanded kt-table-row-active');

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
        console.log('Expanding row for product:', productId);
        $row.addClass('expanded kt-table-row-active');

        // Load product detail
        this.loadProductDetail(productId, $row);
    }

    /**
     * Load product detail and insert detail panel
     */
    loadProductDetail(productId, $row) {
        console.log('Loading product detail for ID:', productId);

        // Get table container width for detail panel
        const tableContainer = $('#kt_products_table_container');
        const containerWidth = tableContainer.width();
        const columnCount = this.table.querySelectorAll('thead th').length;

        // Create detail row with loading spinner
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
                            <div class="mt-2">Đang tải thông tin sản phẩm...</div>
                        </div>
                    </div>
                </td>
            </tr>
        `);

        // Insert detail row after clicked row
        $row.after($detailRow);

        // Show detail row with animation
        $detailRow.slideDown(300, () => {
            // Load product detail content via AJAX
            this.loadProductDetailContent(productId, $detailRow, $row);
        });
    }

    /**
     * Load product detail content via AJAX
     */
    loadProductDetailContent(productId, $detailRow, $clickedRow) {
        console.log('Loading product detail content for ID:', productId);

        $.ajax({
            url: `/admin/products/detail/${productId}`,
            type: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: (response) => {
                try {
                    console.log('Product detail loaded successfully');

                    // Check if response has html property (JSON response) or is HTML directly
                    const htmlContent = response.html || response;

                    // Replace loading placeholder with actual content
                    $detailRow.find('.loading-placeholder').replaceWith(htmlContent);

                    // Get table container width and apply to detail container
                    const tableContainer = $('#kt_products_table_container');
                    const containerWidth = tableContainer.width();

                    if (containerWidth) {
                        $detailRow.find('.kt-table-detail-container').css({
                            'width': containerWidth + 'px',
                            'max-width': containerWidth + 'px',
                            'overflow': 'visible',
                            'position': 'relative'
                        });
                    }

                    // Initialize detail panel components
                    this.initDetailPanelComponents($detailRow);

                    // Update border spans position
                    this.updateBorderSpansPosition($clickedRow, $detailRow);

                } catch (error) {
                    console.error('Error processing product detail:', error);
                    const errorHtml = `
                        <div class="alert alert-danger m-4">
                            <h5>Lỗi xử lý dữ liệu sản phẩm</h5>
                            <p>Có lỗi xảy ra khi xử lý dữ liệu. Vui lòng thử lại sau.</p>
                            <small>Error: ${error.message}</small>
                        </div>
                    `;
                    $detailRow.find('.loading-placeholder').replaceWith(errorHtml);
                }
            },
            error: (xhr, status, error) => {
                console.error('Error loading product detail:', error);
                console.error('XHR status:', status);
                console.error('XHR response:', xhr.responseText);
                console.error('XHR status code:', xhr.status);

                const errorHtml = `
                    <div class="alert alert-danger m-4">
                        <h5>Lỗi tải thông tin sản phẩm</h5>
                        <p>Không thể tải thông tin chi tiết sản phẩm. Vui lòng thử lại sau.</p>
                        <small>Error: ${error} (Status: ${xhr.status})</small>
                    </div>
                `;

                $detailRow.find('.loading-placeholder').replaceWith(errorHtml);
            }
        });
    }

    /**
     * Initialize detail panel components (tabs, etc.)
     */
    initDetailPanelComponents($detailRow) {
        // Initialize tabs if TabManager is available
        const tabContainer = $detailRow.find('[data-kt-tabs="true"]');
        if (tabContainer.length && typeof TabManager !== 'undefined') {
            new TabManager(tabContainer[0]);
        }

        // Bind tab click handlers
        const tabLinks = $detailRow.find('.nav-link[data-bs-toggle="tab"]');
        console.log('Binding tab click handler to elements:', tabLinks.length);

        tabLinks.on('click', function(e) {
            e.preventDefault();
            const $this = $(this);
            const targetId = $this.attr('href');

            // Remove active class from all tabs and panes
            $this.closest('.nav').find('.nav-link').removeClass('active');
            $this.closest('.tab-content').find('.tab-pane').removeClass('active show');

            // Add active class to clicked tab and target pane
            $this.addClass('active');
            $(targetId).addClass('active show');
        });
    }

    /**
     * Update border spans position (adapted from order-manager.js)
     */
    updateBorderSpansPosition($clickedRow, $detailRow) {
        setTimeout(() => {
            // Find the active product row with expanded class
            const $activeRow = $('.product-row.expanded.kt-table-row-active');

            if ($activeRow.length === 0) {
                console.log('No active expanded product row found');
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

        // Find the active product row with expanded class
        const $activeRow = $('.product-row.expanded.kt-table-row-active');
        if ($activeRow.length === 0) {
            return;
        }

        // Get active row height
        const activeRowHeight = $activeRow.outerHeight();

        // Set top position to negative height of active row
        const topPosition = -activeRowHeight;

        // Get product detail panel height
        const $detailPanel = this.$activeDetailRow.find('.kt-table-detail-container');
        const detailPanelHeight = $detailPanel.outerHeight();
        const totalHeight = activeRowHeight + detailPanelHeight;

        // Get table container scroll position
        const $tableContainer = $('#kt_products_table_container');
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
        const $tableContainer = $('#kt_products_table_container');

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
        $('#kt_products_table_container').off('scroll.borderUpdate');

        // Clear stored elements
        this.$activeBorderLeft = null;
        this.$activeBorderRight = null;
        this.$activeDetailRow = null;

        console.log('Border elements cleaned up');
    }
}

