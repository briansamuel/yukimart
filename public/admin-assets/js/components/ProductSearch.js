/**
 * ProductSearch - Shared component for product search functionality
 */
class ProductSearch {
    constructor(options = {}) {
        this.options = {
            inputSelector: '#barcodeInput',
            suggestionsSelector: '#productSuggestions',
            searchUrl: '/admin/quick-order/search-product',
            debounceDelay: 300,
            minSearchLength: 1,
            maxSuggestions: 10,
            ...options
        };
        
        this.searchTimeout = null;
        this.currentRequest = null;
        this.suggestions = [];
        
        this.init();
    }

    /**
     * Initialize product search
     */
    init() {
        this.bindEvents();
        this.setupKeyboardShortcuts();
    }

    /**
     * Bind events
     */
    bindEvents() {
        // Input events
        $(this.options.inputSelector).on('input', (e) => {
            this.handleInput(e.target.value);
        });

        $(this.options.inputSelector).on('keydown', (e) => {
            this.handleKeydown(e);
        });

        $(this.options.inputSelector).on('focus', () => {
            this.showSuggestions();
        });

        $(this.options.inputSelector).on('blur', () => {
            // Delay hiding to allow clicks on suggestions
            setTimeout(() => this.hideSuggestions(), 200);
        });

        // Suggestion clicks
        $(document).on('click', `${this.options.suggestionsSelector} .suggestion-item`, (e) => {
            e.preventDefault();
            const productId = $(e.currentTarget).data('product-id');
            this.selectProduct(productId);
        });

        // Click outside to hide
        $(document).on('click', (e) => {
            if (!$(e.target).closest(this.options.inputSelector).length && 
                !$(e.target).closest(this.options.suggestionsSelector).length) {
                this.hideSuggestions();
            }
        });
    }

    /**
     * Setup keyboard shortcuts
     */
    setupKeyboardShortcuts() {
        // F3 to focus search
        $(document).on('keydown', (e) => {
            if (e.key === 'F3') {
                e.preventDefault();
                this.focusSearch();
            }
        });
    }

    /**
     * Handle input changes
     */
    handleInput(query) {
        // Clear previous timeout
        if (this.searchTimeout) {
            clearTimeout(this.searchTimeout);
        }

        // Cancel previous request
        if (this.currentRequest) {
            this.currentRequest.abort();
            this.currentRequest = null;
        }

        query = query.trim();

        if (query.length < this.options.minSearchLength) {
            this.hideSuggestions();
            return;
        }

        // Debounce search
        this.searchTimeout = setTimeout(() => {
            this.searchProducts(query);
        }, this.options.debounceDelay);
    }

    /**
     * Handle keyboard navigation
     */
    handleKeydown(e) {
        const suggestions = $(this.options.suggestionsSelector);
        const items = suggestions.find('.suggestion-item');
        const activeItem = items.filter('.active');

        switch (e.key) {
            case 'ArrowDown':
                e.preventDefault();
                if (activeItem.length === 0) {
                    items.first().addClass('active');
                } else {
                    const next = activeItem.removeClass('active').next('.suggestion-item');
                    if (next.length > 0) {
                        next.addClass('active');
                    } else {
                        items.first().addClass('active');
                    }
                }
                break;

            case 'ArrowUp':
                e.preventDefault();
                if (activeItem.length === 0) {
                    items.last().addClass('active');
                } else {
                    const prev = activeItem.removeClass('active').prev('.suggestion-item');
                    if (prev.length > 0) {
                        prev.addClass('active');
                    } else {
                        items.last().addClass('active');
                    }
                }
                break;

            case 'Enter':
                e.preventDefault();
                if (activeItem.length > 0) {
                    const productId = activeItem.data('product-id');
                    this.selectProduct(productId);
                } else {
                    // Search by exact barcode/SKU
                    this.searchExact($(e.target).val());
                }
                break;

            case 'Escape':
                this.hideSuggestions();
                $(e.target).blur();
                break;
        }
    }

    /**
     * Search products via AJAX
     */
    searchProducts(query) {
        this.currentRequest = $.ajax({
            url: this.options.searchUrl,
            method: 'POST',
            data: {
                query: query,
                limit: this.options.maxSuggestions,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: (response) => {
                this.currentRequest = null;
                if (response.success && response.data) {
                    this.suggestions = response.data;
                    this.renderSuggestions();
                } else {
                    this.suggestions = [];
                    this.hideSuggestions();
                }
            },
            error: (xhr) => {
                this.currentRequest = null;
                if (xhr.statusText !== 'abort') {
                    console.error('Product search failed:', xhr);
                    this.hideSuggestions();
                }
            }
        });
    }

    /**
     * Search for exact product by barcode/SKU
     */
    searchExact(query) {
        $.ajax({
            url: this.options.searchUrl,
            method: 'POST',
            data: {
                query: query,
                exact: true,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: (response) => {
                if (response.success && response.data.length > 0) {
                    this.selectProduct(response.data[0].id);
                } else {
                    toastr.warning('Không tìm thấy sản phẩm với mã: ' + query);
                }
            },
            error: () => {
                toastr.error('Có lỗi xảy ra khi tìm kiếm sản phẩm');
            }
        });
    }

    /**
     * Render suggestions
     */
    renderSuggestions() {
        const container = $(this.options.suggestionsSelector);
        
        if (this.suggestions.length === 0) {
            this.hideSuggestions();
            return;
        }

        let html = '';
        this.suggestions.forEach(product => {
            const stockStatus = this.getStockStatus(product);
            html += `
                <div class="suggestion-item" data-product-id="${product.id}">
                    <div class="product-info">
                        <div class="product-name">${product.name}</div>
                        <div class="product-details">
                            <span class="sku">SKU: ${product.sku}</span>
                            <span class="barcode">Barcode: ${product.barcode || product.sku}</span>
                        </div>
                        <div class="stock-info ${stockStatus.class}">${stockStatus.text}</div>
                    </div>
                    <div class="product-price">${this.formatCurrency(product.price)}</div>
                </div>
            `;
        });

        container.html(html).addClass('show');
    }

    /**
     * Get stock status
     */
    getStockStatus(product) {
        const stock = product.stock_quantity || 0;
        
        if (stock <= 0) {
            return { class: 'out-of-stock', text: 'Hết hàng' };
        } else if (stock <= 5) {
            return { class: 'low-stock', text: `Tồn: ${stock}` };
        } else {
            return { class: 'in-stock', text: `Tồn: ${stock}` };
        }
    }

    /**
     * Format currency
     */
    formatCurrency(amount) {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(amount);
    }

    /**
     * Select product
     */
    selectProduct(productId) {
        const product = this.suggestions.find(p => p.id == productId);
        if (!product) return;

        // Clear input and hide suggestions
        $(this.options.inputSelector).val('');
        this.hideSuggestions();

        // Trigger product selected event
        $(document).trigger('productSelected', [product]);

        // Focus back to input
        this.focusSearch();
    }

    /**
     * Show suggestions
     */
    showSuggestions() {
        if (this.suggestions.length > 0) {
            $(this.options.suggestionsSelector).addClass('show');
        }
    }

    /**
     * Hide suggestions
     */
    hideSuggestions() {
        $(this.options.suggestionsSelector).removeClass('show');
    }

    /**
     * Focus search input
     */
    focusSearch() {
        $(this.options.inputSelector).focus();
    }

    /**
     * Clear search
     */
    clearSearch() {
        $(this.options.inputSelector).val('');
        this.hideSuggestions();
    }
}
