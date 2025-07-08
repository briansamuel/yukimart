/**
 * Quick Order JavaScript
 * Handles barcode scanning, product search, and order management
 */

class QuickOrder {
    constructor(tabId = null) {
        this.tabId = tabId;
        this.orderItems = [];
        this.currentOrder = {
            customer_id: null,
            branch_shop_id: null,
            payment_method: 'cash',
            notes: ''
        };
        this.lastScannedTime = null;
        this.scanTimeout = null;
        this.selectedProduct = null;

        this.init();
    }

    init() {
        this.bindEvents();
        this.loadSession();
        this.loadStatistics();
        this.focusBarcodeInput();
        
        // Auto-save session every 30 seconds
        setInterval(() => this.autoSaveSession(), 30000);
    }

    bindEvents() {
        // Barcode input events
        $(this.getTabSelector('barcodeInput')).on('keypress', (e) => this.handleBarcodeInput(e));
        $(this.getTabSelector('barcodeInput')).on('input', (e) => this.handleBarcodeChange(e));
        $(this.getTabSelector('searchBarcodeBtn')).on('click', () => this.searchBarcode());

        // Order management events
        $(this.getTabSelector('createOrderBtn')).on('click', () => this.createOrder());
        $(this.getTabSelector('previewOrderBtn')).on('click', () => this.previewOrder());

        // Global buttons (not tab-specific)
        if (!this.tabId) {
            $('#clearOrderBtn').on('click', () => this.clearOrder());
            $('#saveSessionBtn').on('click', () => this.saveSession());
        }

        // Form events
        $(this.getTabSelector('customerSelect') + ', ' + this.getTabSelector('branchShopSelect') + ', ' + this.getTabSelector('paymentMethodSelect')).on('change', () => {
            this.updateOrderInfo();
            this.validateOrder();
        });
        $(this.getTabSelector('orderNotes')).on('input', () => this.updateOrderInfo());

        // Manual item events
        $(this.getTabSelector('addManualItemBtn')).on('click', () => this.showAddManualItemModal());
        $(this.getTabSelector('addManualItemConfirmBtn')).on('click', () => this.addManualItem());
        $(this.getTabSelector('productSearchInput')).on('input', (e) => this.searchProducts(e.target.value));

        // Keep barcode input focused (only for active tab)
        $(document).on('click', (e) => {
            if (this.isActiveTab() && !$(e.target).is('input, select, textarea, button, a')) {
                this.focusBarcodeInput();
            }
        });

        // Handle keyboard shortcuts (only for active tab)
        $(document).on('keydown', (e) => {
            if (this.isActiveTab()) {
                this.handleKeyboardShortcuts(e);
            }
        });
    }

    focusBarcodeInput() {
        setTimeout(() => {
            const tabPrefix = this.tabId ? `#${this.tabId}-` : '#';
            $(`${tabPrefix}barcodeInput`).focus();
        }, 100);
    }

    isActiveTab() {
        if (!this.tabId) return true; // Default behavior for non-tabbed mode
        return window.quickOrderTabs && window.quickOrderTabs.getActiveTabId() === this.tabId;
    }

    getTabSelector(selector) {
        const tabPrefix = this.tabId ? `#${this.tabId}-` : '#';
        return `${tabPrefix}${selector.replace('#', '')}`;
    }

    handleBarcodeInput(e) {
        if (e.which === 13) { // Enter key
            e.preventDefault();
            this.searchBarcode();
        }
    }

    handleBarcodeChange(e) {
        const barcode = e.target.value.trim();
        
        // Clear previous timeout
        if (this.scanTimeout) {
            clearTimeout(this.scanTimeout);
        }

        // Auto-search after 500ms of no input (for barcode scanners)
        if (barcode.length >= 3) {
            this.scanTimeout = setTimeout(() => {
                this.searchBarcode();
            }, 500);
        }
    }

    async searchBarcode() {
        const barcodeInput = $(this.getTabSelector('barcodeInput'));
        const barcode = barcodeInput.val().trim();

        if (!barcode) {
            this.showError('Please enter a barcode');
            return;
        }

        if (barcode.length < 3) {
            this.showError('Barcode must be at least 3 characters');
            return;
        }

        try {
            this.showLoading('Searching product...');

            const response = await fetch(`/api/products/barcode/${encodeURIComponent(barcode)}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            const data = await response.json();
            this.hideLoading();

            if (data.success && data.data) {
                this.addProductToOrder(data.data);
                this.updateLastScanned(barcode);
                barcodeInput.val('').focus();
            } else {
                this.showError(data.message || 'Product not found');
                this.playErrorSound();
            }

        } catch (error) {
            this.hideLoading();
            this.showError('Error searching product: ' + error.message);
            console.error('Barcode search error:', error);
        }
    }

    addProductToOrder(product, quantity = 1, customPrice = null) {
        // Check if product already exists in order
        const existingItemIndex = this.orderItems.findIndex(item => item.product_id === product.id);
        
        if (existingItemIndex !== -1) {
            // Increase quantity of existing item
            this.orderItems[existingItemIndex].quantity += quantity;
            this.orderItems[existingItemIndex].total_price = 
                (customPrice || this.orderItems[existingItemIndex].unit_price) * this.orderItems[existingItemIndex].quantity;
        } else {
            // Add new item
            const orderItem = {
                product_id: product.id,
                name: product.name,
                sku: product.sku,
                barcode: product.barcode,
                unit_price: customPrice || product.price,
                quantity: quantity,
                total_price: (customPrice || product.price) * quantity,
                image: product.image,
                stock_quantity: product.stock_quantity,
                is_available: product.is_available
            };
            
            this.orderItems.push(orderItem);
        }

        this.updateOrderDisplay();
        this.validateOrder();
        this.playSuccessSound();
        
        // Show success message
        this.showSuccess(`Added ${product.name} to order`);
    }

    updateOrderDisplay() {
        const tbody = $(this.getTabSelector('orderItemsTableBody'));
        const emptyRow = $(this.getTabSelector('emptyOrderRow'));

        if (this.orderItems.length === 0) {
            emptyRow.show();
            return;
        }

        emptyRow.hide();
        
        let html = '';
        let subtotal = 0;
        let totalDiscount = 0;

        this.orderItems.forEach((item, index) => {
            subtotal += item.total_price;
            
            const stockBadge = item.stock_quantity > 0 
                ? `<span class="badge badge-light-success">${item.stock_quantity} in stock</span>`
                : `<span class="badge badge-light-danger">Out of stock</span>`;

            html += `
                <tr data-item-index="${index}">
                    <td>${index + 1}</td>
                    <td>
                        <div class="d-flex align-items-center">
                            ${item.image ? `<img src="${item.image}" alt="" class="w-40px h-40px rounded me-3">` : ''}
                            <div>
                                <div class="fw-bold">${item.name}</div>
                                <div class="text-muted fs-7">${stockBadge}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="fw-bold">${item.sku || 'N/A'}</div>
                        <div class="text-muted fs-7">${item.barcode || 'N/A'}</div>
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm item-price" 
                               value="${item.unit_price}" min="0" step="0.01" 
                               data-item-index="${index}">
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm item-quantity" 
                               value="${item.quantity}" min="1" max="${item.stock_quantity || 999}" 
                               data-item-index="${index}">
                    </td>
                    <td class="fw-bold">${this.formatCurrency(item.total_price)}</td>
                    <td class="text-end">
                        <button class="btn btn-icon btn-sm btn-light-danger remove-item-btn" 
                                data-item-index="${index}" title="Remove item">
                            <i class="fas fa-trash fs-2">
                            </i>
                        </button>
                    </td>
                </tr>
            `;
        });

        tbody.html(html);

        // Bind item events
        $('.item-quantity').on('change', (e) => this.updateItemQuantity(e));
        $('.item-price').on('change', (e) => this.updateItemPrice(e));
        $('.remove-item-btn').on('click', (e) => this.removeItem(e));

        // Update totals
        this.updateOrderTotals(subtotal, totalDiscount);
        
        // Update items count
        $(this.getTabSelector('itemsCountLabel')).text(`${this.orderItems.length} items`);
    }

    updateItemQuantity(e) {
        const index = parseInt($(e.target).data('item-index'));
        const newQuantity = parseInt($(e.target).val()) || 1;
        const item = this.orderItems[index];
        
        if (newQuantity > item.stock_quantity) {
            this.showError(`Only ${item.stock_quantity} items available in stock`);
            $(e.target).val(item.quantity);
            return;
        }

        item.quantity = newQuantity;
        item.total_price = item.unit_price * item.quantity;
        
        this.updateOrderDisplay();
    }

    updateItemPrice(e) {
        const index = parseInt($(e.target).data('item-index'));
        const newPrice = parseFloat($(e.target).val()) || 0;
        const item = this.orderItems[index];
        
        item.unit_price = newPrice;
        item.total_price = item.unit_price * item.quantity;
        
        this.updateOrderDisplay();
    }

    removeItem(e) {
        const index = parseInt($(e.target).closest('button').data('item-index'));
        const item = this.orderItems[index];
        
        if (confirm(`Remove ${item.name} from order?`)) {
            this.orderItems.splice(index, 1);
            this.updateOrderDisplay();
            this.validateOrder();
        }
    }

    updateOrderTotals(subtotal, discount = 0) {
        const total = subtotal - discount;

        $(this.getTabSelector('subtotalAmount')).text(this.formatCurrency(subtotal));
        $(this.getTabSelector('discountAmount')).text(this.formatCurrency(discount));
        $(this.getTabSelector('totalAmount')).text(this.formatCurrency(total));
    }

    updateOrderInfo() {
        this.currentOrder.customer_id = $(this.getTabSelector('customerSelect')).val();
        this.currentOrder.branch_shop_id = $(this.getTabSelector('branchShopSelect')).val();
        this.currentOrder.payment_method = $(this.getTabSelector('paymentMethodSelect')).val();
        this.currentOrder.notes = $(this.getTabSelector('orderNotes')).val();
    }

    validateOrder() {
        const hasItems = this.orderItems.length > 0;
        const hasCustomer = this.currentOrder.customer_id;
        const hasBranchShop = this.currentOrder.branch_shop_id;

        const isValid = hasItems && hasCustomer && hasBranchShop;

        $(this.getTabSelector('createOrderBtn') + ', ' + this.getTabSelector('previewOrderBtn')).prop('disabled', !isValid);

        return isValid;
    }

    async createOrder() {
        if (!this.validateOrder()) {
            this.showError('Please complete all required fields');
            return;
        }

        try {
            this.setButtonLoading(this.getTabSelector('createOrderBtn'), true);
            
            const orderData = {
                customer_id: this.currentOrder.customer_id,
                branch_shop_id: this.currentOrder.branch_shop_id,
                payment_method: this.currentOrder.payment_method,
                notes: this.currentOrder.notes,
                items: this.orderItems.map(item => ({
                    product_id: item.product_id,
                    quantity: item.quantity,
                    price: item.unit_price
                }))
            };

            const response = await fetch('/admin/quick-order', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                body: JSON.stringify(orderData)
            });

            const data = await response.json();
            
            if (data.success) {
                this.showSuccess('Order created successfully!');
                this.clearOrder();
                this.loadStatistics();
                
                // Redirect to order detail
                if (data.data.redirect_url) {
                    setTimeout(() => {
                        window.location.href = data.data.redirect_url;
                    }, 1500);
                }
            } else {
                this.showError(data.message || 'Failed to create order');
            }

        } catch (error) {
            this.showError('Error creating order: ' + error.message);
            console.error('Create order error:', error);
        } finally {
            this.setButtonLoading(this.getTabSelector('createOrderBtn'), false);
        }
    }

    clearOrder() {
        this.orderItems = [];
        this.currentOrder.notes = '';
        $(this.getTabSelector('orderNotes')).val('');
        $(this.getTabSelector('barcodeInput')).val('');
        this.updateOrderDisplay();
        this.validateOrder();
        this.focusBarcodeInput();
    }

    async saveSession() {
        try {
            this.updateOrderInfo();
            
            const sessionData = {
                items: this.orderItems,
                customer_id: this.currentOrder.customer_id,
                branch_shop_id: this.currentOrder.branch_shop_id,
                notes: this.currentOrder.notes
            };

            const response = await fetch('/admin/quick-order/session', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                body: JSON.stringify(sessionData)
            });

            const data = await response.json();
            
            if (data.success) {
                this.showSuccess('Session saved successfully');
            }

        } catch (error) {
            console.error('Save session error:', error);
        }
    }

    async loadSession() {
        try {
            const response = await fetch('/admin/quick-order/session');
            const data = await response.json();
            
            if (data.success && data.data.items) {
                this.orderItems = data.data.items || [];
                
                if (data.data.customer_id) {
                    $(this.getTabSelector('customerSelect')).val(data.data.customer_id).trigger('change');
                }

                if (data.data.branch_shop_id) {
                    $(this.getTabSelector('branchShopSelect')).val(data.data.branch_shop_id).trigger('change');
                }

                if (data.data.notes) {
                    $(this.getTabSelector('orderNotes')).val(data.data.notes);
                }
                
                this.updateOrderDisplay();
                this.validateOrder();
            }

        } catch (error) {
            console.error('Load session error:', error);
        }
    }

    autoSaveSession() {
        if (this.orderItems.length > 0) {
            this.saveSession();
        }
    }

    async loadStatistics() {
        try {
            const response = await fetch('/admin/quick-order/statistics');
            const data = await response.json();

            if (data.success) {
                $(this.getTabSelector('todayOrdersCount')).text(data.data.today.orders);
                $(this.getTabSelector('todayRevenue')).text(data.data.today.formatted_revenue);
            }

        } catch (error) {
            console.error('Load statistics error:', error);
        }
    }

    updateLastScanned(barcode) {
        this.lastScannedTime = new Date();
        $(this.getTabSelector('lastScannedCode')).text(barcode);
        $(this.getTabSelector('lastScannedTime')).text(this.lastScannedTime.toLocaleTimeString());
    }

    handleKeyboardShortcuts(e) {
        // Ctrl+Enter: Create order
        if (e.ctrlKey && e.which === 13) {
            e.preventDefault();
            if (!$(this.getTabSelector('createOrderBtn')).prop('disabled')) {
                this.createOrder();
            }
        }

        // Ctrl+N: Clear order
        if (e.ctrlKey && e.which === 78) {
            e.preventDefault();
            this.clearOrder();
        }

        // F2: Focus barcode input
        if (e.which === 113) {
            e.preventDefault();
            this.focusBarcodeInput();
        }
    }

    // Utility methods
    formatCurrency(amount) {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(amount);
    }

    getApiToken() {
        // Get API token from meta tag or localStorage
        return $('meta[name="api-token"]').attr('content') || localStorage.getItem('api_token');
    }

    setButtonLoading(selector, loading) {
        const btn = $(selector);
        if (loading) {
            const originalText = btn.html();
            btn.data('original-text', originalText);
            btn.html('Đang xử lý... <span class="spinner-border spinner-border-sm ms-2"></span>');
            btn.prop('disabled', true);
        } else {
            const originalText = btn.data('original-text');
            if (originalText) {
                btn.html(originalText);
            }
            btn.prop('disabled', false);
        }
    }

    showLoading(message = 'Loading...') {
        Swal.fire({
            title: message,
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    }

    hideLoading() {
        Swal.close();
    }

    showSuccess(message) {
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: message,
            timer: 3000,
            showConfirmButton: false
        });
    }

    showError(message) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: message
        });
    }

    playSuccessSound() {
        // Play success sound (optional)
        try {
            const audio = new Audio('/admin/sounds/success.mp3');
            audio.volume = 0.3;
            audio.play().catch(() => {});
        } catch (e) {}
    }

    playErrorSound() {
        // Play error sound (optional)
        try {
            const audio = new Audio('/admin/sounds/error.mp3');
            audio.volume = 0.3;
            audio.play().catch(() => {});
        } catch (e) {}
    }

    // Manual item methods
    showAddManualItemModal() {
        $(this.getTabSelector('addManualItemModal')).modal('show');
        $(this.getTabSelector('productSearchInput')).focus();
    }

    async searchProducts(query) {
        if (query.length < 2) {
            $(this.getTabSelector('productSearchResults')).hide();
            return;
        }

        try {
            const response = await fetch(`/api/products/search?q=${encodeURIComponent(query)}&limit=10`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            const data = await response.json();

            if (data.success && data.data.length > 0) {
                this.displaySearchResults(data.data);
            } else {
                $(this.getTabSelector('productSearchResults')).html('<p class="text-muted">No products found</p>').show();
            }

        } catch (error) {
            console.error('Product search error:', error);
            $(this.getTabSelector('productSearchResults')).hide();
        }
    }

    displaySearchResults(products) {
        let html = '<div class="list-group">';

        products.forEach(product => {
            const stockBadge = product.stock_quantity > 0
                ? `<span class="badge badge-success">${product.stock_quantity}</span>`
                : `<span class="badge badge-danger">Out of stock</span>`;

            html += `
                <div class="list-group-item list-group-item-action product-search-item"
                     data-product='${JSON.stringify(product)}'>
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="mb-1">${product.name}</h6>
                            <p class="mb-1 text-muted">${product.sku} | ${product.barcode || 'No barcode'}</p>
                            <small class="text-muted">${product.formatted_price}</small>
                        </div>
                        <div class="text-end">
                            ${stockBadge}
                        </div>
                    </div>
                </div>
            `;
        });

        html += '</div>';

        $(this.getTabSelector('productSearchResults')).html(html).show();

        // Bind click events
        $('.product-search-item').on('click', (e) => {
            const product = JSON.parse($(e.currentTarget).attr('data-product'));
            this.selectProductForManualAdd(product);
        });
    }

    selectProductForManualAdd(product) {
        this.selectedProduct = product;
        $(this.getTabSelector('productSearchInput')).val(product.name);
        $(this.getTabSelector('manualPrice')).val(product.price);
        $(this.getTabSelector('productSearchResults')).hide();
    }

    addManualItem() {
        if (!this.selectedProduct) {
            this.showError('Please select a product');
            return;
        }

        const quantity = parseInt($(this.getTabSelector('manualQuantity')).val()) || 1;
        const customPrice = parseFloat($(this.getTabSelector('manualPrice')).val()) || null;

        if (quantity <= 0) {
            this.showError('Quantity must be greater than 0');
            return;
        }

        if (quantity > this.selectedProduct.stock_quantity) {
            this.showError(`Only ${this.selectedProduct.stock_quantity} items available in stock`);
            return;
        }

        this.setButtonLoading(this.getTabSelector('addManualItemConfirmBtn'), true);

        try {
            this.addProductToOrder(this.selectedProduct, quantity, customPrice);

            // Reset form
            $(this.getTabSelector('addManualItemForm'))[0].reset();
            $(this.getTabSelector('manualQuantity')).val(1);
            this.selectedProduct = null;
            $(this.getTabSelector('productSearchResults')).hide();

            $(this.getTabSelector('addManualItemModal')).modal('hide');
            this.focusBarcodeInput();

        } catch (error) {
            this.showError('Error adding item: ' + error.message);
        } finally {
            this.setButtonLoading(this.getTabSelector('addManualItemConfirmBtn'), false);
        }
    }

    previewOrder() {
        if (!this.validateOrder()) {
            this.showError('Please complete all required fields');
            return;
        }

        // Calculate totals
        const subtotal = this.orderItems.reduce((sum, item) => sum + item.total_price, 0);
        const total = subtotal;

        // Build preview HTML
        let itemsHtml = '';
        this.orderItems.forEach((item, index) => {
            itemsHtml += `
                <tr>
                    <td>${index + 1}</td>
                    <td>${item.name}</td>
                    <td>${item.sku}</td>
                    <td>${this.formatCurrency(item.unit_price)}</td>
                    <td>${item.quantity}</td>
                    <td>${this.formatCurrency(item.total_price)}</td>
                </tr>
            `;
        });

        const customerName = $(this.getTabSelector('customerSelect') + ' option:selected').text();
        const branchShopName = $(this.getTabSelector('branchShopSelect') + ' option:selected').text();
        const paymentMethod = $(this.getTabSelector('paymentMethodSelect') + ' option:selected').text();

        const previewHtml = `
            <div class="order-preview">
                <h5>Order Preview</h5>
                <div class="row mb-3">
                    <div class="col-6"><strong>Customer:</strong> ${customerName}</div>
                    <div class="col-6"><strong>Branch Shop:</strong> ${branchShopName}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-6"><strong>Payment Method:</strong> ${paymentMethod}</div>
                    <div class="col-6"><strong>Items:</strong> ${this.orderItems.length}</div>
                </div>
                ${this.currentOrder.notes ? `<div class="mb-3"><strong>Notes:</strong> ${this.currentOrder.notes}</div>` : ''}

                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>SKU</th>
                            <th>Price</th>
                            <th>Qty</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${itemsHtml}
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="5">Total</th>
                            <th>${this.formatCurrency(total)}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        `;

        Swal.fire({
            title: 'Order Preview',
            html: previewHtml,
            width: '800px',
            showCancelButton: true,
            confirmButtonText: 'Create Order',
            cancelButtonText: 'Back to Edit'
        }).then((result) => {
            if (result.isConfirmed) {
                this.createOrder();
            }
        });
    }
}

// Initialize Quick Order when document is ready
$(document).ready(function() {
    window.quickOrder = new QuickOrder();
});
