/**
 * Quick Order JavaScript
 * Handles all functionality for the quick order system
 */


// Global variables
let orderTabs = [];
let activeTabId = null;
let tabCounter = 0;
let searchTimeout = null;
let currentSearchRequest = null;
let currentProductSuggestions = []; // Store current product suggestions
let exchangeSearchTimeout = null;
let currentExchangeSearchRequest = null;
let defaultBranchShop = null;
let bankAccounts = [];
let customers = [];
let sellers = [];
let currentUserId = null;
let paidAmountUserEdited = false;

// Invoice pagination variables
let currentInvoicePage = 1;
let currentInvoiceStart = 0;
let currentInvoiceLength = 7;

/**
 * Initialize Quick Order System
 */
function initializeQuickOrder() {
    // Load initial data
    loadInitialData();

    // Setup global event listeners
    setupGlobalEventListeners();

    // Setup customer search
    setupCustomerSearch();

    // Check URL parameters FIRST before loading drafts
    const urlParams = new URLSearchParams(window.location.search);
    const tabType = urlParams.get('type');
    const invoiceId = urlParams.get('invoice_id');

    // Handle return tab creation from /admin/returns OR with invoice_id BEFORE loading drafts
    if (tabType === 'return') {
        const referrer = document.referrer;
        const isFromReturnsPage = referrer && referrer.includes('/admin/returns');
        const hasInvoiceId = invoiceId && invoiceId.trim() !== '';

        console.log(`🔍 Return tab request detected:`, {
            tabType: tabType,
            referrer: referrer,
            isFromReturnsPage: isFromReturnsPage,
            hasInvoiceId: hasInvoiceId,
            invoiceId: invoiceId,
            existingTabsCount: orderTabs ? orderTabs.length : 0
        });

        if (isFromReturnsPage || hasInvoiceId) {
            // Ensure orderTabs is initialized
            if (!orderTabs) {
                orderTabs = [];
            }

            // Check if there's already a return tab without invoice
            const existingReturnTab = orderTabs.find(tab => tab.type === 'return' && !hasSelectedInvoice(tab.id));

            if (!existingReturnTab) {
                console.log(`✅ Creating new return tab from ${isFromReturnsPage ? '/admin/returns' : 'invoice link'}`);
                const newTabId = addNewTab('return');

                // If we have invoice_id, load the invoice data
                if (hasInvoiceId) {
                    console.log(`🔄 Loading invoice data for ID: ${invoiceId}`);
                    setTimeout(() => {
                        loadInvoiceDataForReturn(newTabId, invoiceId);
                    }, 500); // Small delay to ensure tab is fully created
                }

                // Focus on barcode input and return early
                $('#barcodeInput').focus();
                setInterval(updateCurrentTime, 1000);
                updateCurrentTime();
                return;
            } else {
                console.log(`ℹ️ Return tab already exists, switching to it:`, existingReturnTab.id);
                switchTab(existingReturnTab.id);

                // If we have invoice_id, load the invoice data into existing tab
                if (hasInvoiceId) {
                    console.log(`🔄 Loading invoice data into existing tab for ID: ${invoiceId}`);
                    setTimeout(() => {
                        loadInvoiceDataForReturn(existingReturnTab.id, invoiceId);
                    }, 500);
                }

                // Focus on barcode input and return early
                $('#barcodeInput').focus();
                setInterval(updateCurrentTime, 1000);
                updateCurrentTime();
                return;
            }
        } else {
            console.log(`ℹ️ Return tab request not from /admin/returns and no invoice_id, ignoring`);
        }
    }

    // Try to load drafts after handling URL parameters
    const draftsLoaded = loadDrafts();

    // Initialize first tab if no drafts loaded and no tabs exist
    if (!draftsLoaded && (!orderTabs || orderTabs.length === 0)) {
        const defaultTabType = tabType || 'order';
        console.log(`Creating initial tab of type: ${defaultTabType}`);
        addNewTab(defaultTabType);
    }

    // Focus on barcode input
    $('#barcodeInput').focus();

    // Update time every second
    setInterval(updateCurrentTime, 1000);
    updateCurrentTime();
}

/**
 * Load initial data from server
 */
function loadInitialData() {
    // Load default branch shop
    if (typeof window.defaultBranchShop !== 'undefined') {
        defaultBranchShop = window.defaultBranchShop;
    }
    
    // Load bank accounts
    if (typeof window.bankAccounts !== 'undefined') {
        bankAccounts = window.bankAccounts;
    }
    
    // Load customers
    if (typeof window.customers !== 'undefined') {
        customers = window.customers;
    }
    
    // Load sellers
    if (typeof window.sellers !== 'undefined') {
        sellers = window.sellers;
    }

    // Load current user ID
    if (typeof window.currentUserId !== 'undefined') {
        currentUserId = window.currentUserId;
    }
}

/**
 * Setup global event listeners
 */
function setupGlobalEventListeners() {
    // Barcode input with product search
    $('#barcodeInput').on('input', handleBarcodeInput);
    $('#barcodeInput').on('keydown', handleBarcodeKeydown);
    
    // Hide suggestions when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.barcode-input-container').length) {
            $('#productSuggestions').removeClass('show');
        }
    });
    
    // Global keyboard shortcuts
    $(document).keydown(handleGlobalKeyboardShortcuts);

    // Exchange search input handlers (for return tabs)
    $(document).on('input', '[id$="_exchangeSearchInput"]', handleExchangeSearchInput);
    $(document).on('keydown', '[id$="_exchangeSearchInput"]', handleExchangeSearchKeydown);

    // Hide exchange suggestions when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.exchange-search-container').length) {
            $('[id$="_exchangeProductSuggestions"]').removeClass('show');
        }
    });
}

/**
 * Handle barcode input
 */
function handleBarcodeInput() {
    const query = $(this).val().trim();
    
    // Clear previous timeout
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
    
    // Cancel previous request if still pending
    if (currentSearchRequest) {
        currentSearchRequest.abort();
        currentSearchRequest = null;
    }
    
    if (query.length >= 2) {
        // Set new timeout for debounce (300ms delay)
        searchTimeout = setTimeout(function() {
            searchProductSuggestions(query);
        }, 300);
    } else {
        $('#productSuggestions').removeClass('show');
    }
}

/**
 * Handle barcode input keydown events
 */
function handleBarcodeKeydown(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        const query = $(this).val().trim();
        if (query) {
            addProductByBarcode(query);
            $(this).val('');
            $('#productSuggestions').removeClass('show');
        }
    }
}

/**
 * Handle exchange search input
 */
function handleExchangeSearchInput() {
    const query = $(this).val().trim();
    const tabId = $(this).attr('id').replace('_exchangeSearchInput', '');

    // Clear previous timeout
    if (exchangeSearchTimeout) {
        clearTimeout(exchangeSearchTimeout);
    }

    // Cancel previous request if still pending
    if (currentExchangeSearchRequest) {
        currentExchangeSearchRequest.abort();
        currentExchangeSearchRequest = null;
    }

    if (query.length >= 2) {
        // Set new timeout for debounce (300ms delay)
        exchangeSearchTimeout = setTimeout(function() {
            searchExchangeProductSuggestions(query, tabId);
        }, 300);
    } else {
        $(`#${tabId}_exchangeProductSuggestions`).removeClass('show');
    }
}

/**
 * Handle exchange search input keydown events
 */
function handleExchangeSearchKeydown(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        const query = $(this).val().trim();
        const tabId = $(this).attr('id').replace('_exchangeSearchInput', '');
        if (query) {
            addExchangeProductBySearch(query, tabId);
            $(this).val('');
            $(`#${tabId}_exchangeProductSuggestions`).removeClass('show');
        }
    }
}

/**
 * Handle global keyboard shortcuts
 */
function handleGlobalKeyboardShortcuts(e) {
    // F3 - Focus barcode input
    if (e.key === 'F3') {
        e.preventDefault();
        $('#barcodeInput').focus();
    }
    
    // F7 - Focus exchange search (for return tabs)
    if (e.key === 'F7' && activeTabId) {
        e.preventDefault();
        const tab = orderTabs.find(t => t.id === activeTabId);
        if (tab && tab.type === 'return') {
            $(`#${activeTabId}_exchangeSearchInput`).focus();
        }
    }
    
    // Ctrl+N - New order tab
    if (e.ctrlKey && e.key === 'n') {
        e.preventDefault();
        addNewTab('order');
    }
    
    // Ctrl+I - New invoice tab
    if (e.ctrlKey && e.key === 'i') {
        e.preventDefault();
        addNewTab('invoice');
    }
    
    // Ctrl+R - New return tab
    if (e.ctrlKey && e.key === 'r') {
        e.preventDefault();
        addNewTab('return');
    }
}

/**
 * Search for product suggestions
 */
function searchProductSuggestions(query) {
    // Store the current request so we can cancel it if needed
    currentSearchRequest = $.ajax({
        url: '/admin/quick-order/search-product',
        method: 'POST',
        data: {
            query: query,
            limit: 10,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            currentSearchRequest = null; // Clear the request reference
            
            if (response.success && response.data.length > 0) {
                displayProductSuggestions(response.data);
            } else {
                $('#productSuggestions').removeClass('show');
            }
        },
        error: function(xhr, status, error) {
            currentSearchRequest = null; // Clear the request reference
            
            if (status !== 'abort') { // Don't show error for aborted requests
                console.error('Product search error:', error);
                $('#productSuggestions').removeClass('show');
            }
        }
    });
}

/**
 * Display product suggestions
 */
function displayProductSuggestions(products) {
    // Store current suggestions for later use
    currentProductSuggestions = products;

    let html = '';

    products.forEach(product => {
        const stockClass = product.stock_quantity > 10 ? 'product-suggestion-stock' :
                          product.stock_quantity > 0 ? 'product-suggestion-stock low' :
                          'product-suggestion-stock out';
        const stockText = product.stock_quantity > 0 ? `Tồn: ${product.stock_quantity}` : 'Hết hàng';

        html += `
            <div class="product-suggestion" data-product-id="${product.id}" onclick="addProductFromSuggestion(${product.id})">
                <img src="${product.image || '/admin-assets/assets/media/svg/files/blank-image.svg'}" class="product-suggestion-image" alt="">
                <div class="product-suggestion-info">
                    <div class="product-suggestion-name">${product.name}</div>
                    <div class="product-suggestion-details">
                        <span>SKU: ${product.sku || 'N/A'}</span>
                        ${product.barcode ? `<span>Barcode: ${product.barcode}</span>` : ''}
                    </div>
                    <div class="${stockClass}">${stockText}</div>
                </div>
                <div class="product-suggestion-price-container">
                    <div class="product-suggestion-price">${formatCurrency(product.price)}</div>
                </div>
            </div>
        `;
    });

    $('#productSuggestions').html(html).addClass('show');
}

/**
 * Add product from suggestion
 */
function addProductFromSuggestion(productId) {
    console.log('Adding product from suggestion:', productId);
    console.log('Current suggestions:', currentProductSuggestions);
    console.log('Active tab ID:', activeTabId);

    // Find product in stored suggestions
    const productData = currentProductSuggestions.find(p => p.id == productId);

    if (!productData) {
        console.error('Product not found in suggestions:', productId);
        toastr.error('Không tìm thấy sản phẩm');
        return;
    }

    if (!activeTabId) {
        console.error('No active tab');
        toastr.error('Vui lòng tạo tab trước');
        return;
    }

    console.log('Adding product to tab:', productData);
    addProductToTab(activeTabId, productData);

    // Clear input and hide suggestions
    $('#barcodeInput').val('');
    $('#productSuggestions').removeClass('show');
    $('#barcodeInput').focus();
}

/**
 * Add product by barcode/SKU
 */
function addProductByBarcode(query) {
    if (!activeTabId) return;
    
    $.ajax({
        url: '/admin/quick-order/search-product',
        method: 'POST',
        data: {
            query: query,
            limit: 1,
            exact: true,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success && response.data.length > 0) {
                addProductToTab(activeTabId, response.data[0]);
            } else {
                toastr.warning('Không tìm thấy sản phẩm với mã: ' + query);
            }
        },
        error: function(xhr, status, error) {
            console.error('Product search error:', error);
            toastr.error('Lỗi khi tìm kiếm sản phẩm');
        }
    });
}

/**
 * Search for exchange product suggestions
 */
function searchExchangeProductSuggestions(query, tabId) {
    // Store the current request so we can cancel it if needed
    currentExchangeSearchRequest = $.ajax({
        url: '/admin/quick-order/search-product',
        method: 'POST',
        data: {
            query: query,
            limit: 10,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            currentExchangeSearchRequest = null; // Clear the request reference
            console.log('🔍 Exchange search response:', response);
            if (response.success && response.data.length > 0) {
                console.log('🔍 First product:', response.data[0]);
                displayExchangeProductSuggestions(response.data, tabId);
            } else {
                $(`#${tabId}_exchangeProductSuggestions`).removeClass('show');
            }
        },
        error: function(xhr, status, error) {
            currentExchangeSearchRequest = null;
            if (xhr.statusText !== 'abort') { // Don't show error for aborted requests
                console.error('Exchange product search error:', error);
            }
        }
    });
}

/**
 * Display exchange product suggestions
 */
function displayExchangeProductSuggestions(products, tabId) {
    const suggestionsContainer = $(`#${tabId}_exchangeProductSuggestions`);
    let html = '';

    products.forEach(function(product) {
        const stockClass = product.stock_quantity > 10 ? 'product-suggestion-stock' :
                          product.stock_quantity > 0 ? 'product-suggestion-stock low' :
                          'product-suggestion-stock out';
        const stockText = product.stock_quantity > 0 ? `Tồn: ${product.stock_quantity}` : 'Hết hàng';

        html += `
            <div class="product-suggestion" data-product-id="${product.id}" data-tab-id="${tabId}">
                <img src="${product.image || '/admin-assets/assets/media/svg/files/blank-image.svg'}" class="product-suggestion-image" alt="">
                <div class="product-suggestion-info">
                    <div class="product-suggestion-name">${product.product_name || product.name || product.title || product.display_name || 'Sản phẩm'}</div>
                    <div class="product-suggestion-details">
                        <span>SKU: ${product.sku || 'N/A'}</span>
                        ${product.barcode ? `<span>Barcode: ${product.barcode}</span>` : ''}
                    </div>
                    <div class="${stockClass}">${stockText}</div>
                </div>
                <div class="product-suggestion-price-container">
                    <div class="product-suggestion-price">${formatCurrency(product.price)}</div>
                </div>
            </div>
        `;
    });

    suggestionsContainer.html(html).addClass('show');

    // Handle suggestion clicks
    suggestionsContainer.find('.product-suggestion').on('click', function() {
        const productId = $(this).data('product-id');
        const tabId = $(this).data('tab-id');
        const product = products.find(p => p.id == productId);
        if (product) {
            // ✅ INVENTORY VALIDATION: Check stock before adding
            const stockQuantity = product.stock_quantity || 0;
            if (stockQuantity <= 0) {
                const productName = product.name || product.product_name || product.title || product.display_name || 'Sản phẩm';
                toastr.warning(`Sản phẩm "${productName}" đã hết hàng, không thể thêm vào hàng đổi`);
                console.log('❌ Exchange product click rejected: out of stock', { product, stockQuantity });
                return;
            }

            addExchangeProductToTab(tabId, product);
            $(`#${tabId}_exchangeSearchInput`).val('');
            suggestionsContainer.removeClass('show');
        }
    });
}

/**
 * Add exchange product by search query
 */
function addExchangeProductBySearch(query, tabId) {
    $.ajax({
        url: '/admin/quick-order/search-product',
        method: 'POST',
        data: {
            query: query,
            limit: 1,
            exact: true,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success && response.data.length > 0) {
                addExchangeProductToTab(tabId, response.data[0]);
            } else {
                toastr.warning('Không tìm thấy sản phẩm với mã: ' + query);
            }
        },
        error: function(xhr, status, error) {
            console.error('Exchange product search error:', error);
            toastr.error('Lỗi khi tìm kiếm sản phẩm');
        }
    });
}

/**
 * Add exchange product to tab
 */
function addExchangeProductToTab(tabId, productData) {
    console.log('addExchangeProductToTab called with:', { tabId, productData });

    const tab = orderTabs.find(t => t.id === tabId);
    if (!tab) {
        console.error('Tab not found:', tabId);
        return;
    }

    if (tab.type !== 'return') {
        console.error('Exchange products can only be added to return tabs');
        return;
    }

    // ✅ INVENTORY VALIDATION: Check if product has sufficient stock
    const stockQuantity = productData.stock_quantity || 0;
    if (stockQuantity <= 0) {
        const productName = productData.name || productData.product_name || productData.title || productData.display_name || 'Sản phẩm';
        toastr.warning(`Sản phẩm "${productName}" đã hết hàng, không thể thêm vào hàng đổi`);
        console.log('❌ Exchange product rejected: out of stock', { productData, stockQuantity });
        return;
    }

    // Initialize exchangeItems if not exists
    if (!tab.exchangeItems) {
        tab.exchangeItems = [];
    }

    // Check if product already exists in exchange items
    const existingItemIndex = tab.exchangeItems.findIndex(item => item.id === productData.id);

    if (existingItemIndex !== -1) {
        // Check if increasing quantity would exceed stock
        const newQuantity = tab.exchangeItems[existingItemIndex].quantity + 1;
        if (newQuantity > stockQuantity) {
            const productName = productData.name || productData.product_name || productData.title || productData.display_name || 'Sản phẩm';
            toastr.warning(`Không thể tăng số lượng "${productName}". Tồn kho chỉ còn ${stockQuantity}`);
            console.log('❌ Exchange quantity increase rejected: exceeds stock', { productData, currentQuantity: tab.exchangeItems[existingItemIndex].quantity, stockQuantity });
            return;
        }

        // Increase quantity
        tab.exchangeItems[existingItemIndex].quantity = newQuantity;
        toastr.success(`Đã tăng số lượng "${productData.name}" lên ${newQuantity}`);
    } else {
        // Add new exchange item
        const newItem = {
            id: productData.id,
            name: productData.name,
            sku: productData.sku,
            barcode: productData.barcode,
            price: productData.price,
            quantity: 1,
            total: productData.price
        };

        tab.exchangeItems.push(newItem);
        toastr.success(`Đã thêm "${productData.name}" vào hàng đổi`);
    }

    // Update UI
    updateExchangeItemsList(tabId);
    updateOrderTotals();

    console.log('✅ Exchange item added successfully');
}

/**
 * Update current time display
 */
function updateCurrentTime() {
    const now = new Date();
    const timeString = now.toLocaleString('vi-VN', {
        day: '2-digit',
        month: '2-digit', 
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
    $('#currentTime').text(timeString);
}

/**
 * Format currency
 */
function formatCurrency(amount) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(amount);
}

/**
 * Format currency input
 */
function formatCurrencyInput(input) {
    let value = $(input).val().replace(/[^\d]/g, '');
    if (value) {
        $(input).val(formatCurrency(parseInt(value)));
    }
}

/**
 * Parse currency value
 */
function parseCurrency(value) {
    if (typeof value === 'string') {
        return parseInt(value.replace(/[^\d]/g, '')) || 0;
    }
    return parseInt(value) || 0;
}

/**
 * Add new tab
 */
function addNewTab(type = 'order') {
    // Check maximum tabs limit
    const MAX_TABS = 10;
    if (orderTabs.length >= MAX_TABS) {
        toastr.warning(`Đã đạt giới hạn tối đa ${MAX_TABS} tabs`);
        return null;
    }

    tabCounter++;
    const tabId = 'tab_' + tabCounter;
    let tabName;
    if (type === 'invoice') {
        tabName = 'Hóa đơn ' + tabCounter;
    } else if (type === 'return') {
        tabName = 'Trả hàng ' + tabCounter;
    } else {
        tabName = 'Đơn hàng ' + tabCounter;
    }

    // Create tab data
    const tabData = {
        id: tabId,
        name: tabName,
        type: type, // 'order', 'invoice', or 'return'
        number: tabCounter,
        items: [],
        exchangeItems: [], // For return tabs
        customer_id: '',
        customer_search: '',
        branch_shop_id: (defaultBranchShop || window.defaultBranchShop) ? (defaultBranchShop || window.defaultBranchShop).id : '',
        branch_shop_name: (defaultBranchShop || window.defaultBranchShop) ? (defaultBranchShop || window.defaultBranchShop).name : '',
        sold_by: currentUserId || window.currentUserId || '',
        payment_method: 'cash',
        bank_account_id: '',
        channel: 'offline',
        notes: '',
        discount_amount: 0,
        other_charges_amount: 0,
        paid_amount: 0,
        created_at: new Date().toISOString()
    };

    // Add to tabs array
    orderTabs.push(tabData);

    // Create tab element
    const tabElement = $(`
        <div class="order-tab ${type}" id="${tabId}" onclick="switchTab('${tabId}')">
            <span class="tab-title">${tabName}</span>
            <span class="tab-count">(0)</span>
            <button type="button" class="tab-close" onclick="closeTab('${tabId}')">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `);

    // Add tab to container
    $('#orderTabsContainer').append(tabElement);

    // Create tab content
    createTabContent(tabId, tabData);

    // Switch to new tab
    switchTab(tabId);

    // Setup default values
    setupTabDefaults(tabId);

    return tabId;
}

/**
 * Create tab content
 */
function createTabContent(tabId, tabData) {
    console.log(`Creating content for tab: ${tabId}`);

    const template = $('#orderTabTemplate');
    if (template.length === 0) {
        console.error('Tab template not found!');
        return;
    }

    let tabContent = template.clone();
    tabContent.attr('id', tabId + '_content');
    tabContent.attr('data-tab-id', tabId);
    tabContent.attr('data-tab-type', tabData.type); // Add tab type for CSS targeting
    tabContent.addClass('h-100');
    tabContent.show();

    // Replace TAB_ID placeholders with actual tab ID
    let htmlContent = tabContent.html();
    if (htmlContent) {
        htmlContent = htmlContent.replace(/TAB_ID_/g, tabId + '_');
        tabContent.html(htmlContent);
    } else {
        console.error('Template content is empty!');
        return;
    }

    // Add tab content to container
    const container = $('#orderTabsContent');
    if (container.length === 0) {
        console.error('Tab content container not found!');
        return;
    }
    container.append(tabContent);

    // Bind events for this tab
    bindTabEvents(tabId);

    // Setup tab-specific UI based on type
    setupTabTypeUI(tabId, tabData.type);

    console.log(`Content created successfully for tab: ${tabId}`);
}

/**
 * Setup tab type specific UI
 */
function setupTabTypeUI(tabId, type) {
    const tabContent = $(`#${tabId}_content`);

    console.log(`Setting up tab type UI for ${tabId} with type ${type}`);
    console.log('Tab content found:', tabContent.length);

    if (type === 'return') {
        // Show return-specific elements
        const returnHeader = tabContent.find(`#${tabId}_returnOrderHeader`);
        const exchangeSearch = tabContent.find(`#${tabId}_exchangeSearchSection`);
        const returnSummary = tabContent.find(`#${tabId}_returnSummarySection`);
        const regularSummary = tabContent.find(`#${tabId}_regularSummarySection`);
        const exchangeItemsList = tabContent.find(`#${tabId}_exchangeItemsList`);

        console.log('Return elements found:', {
            returnHeader: returnHeader.length,
            exchangeSearch: exchangeSearch.length,
            returnSummary: returnSummary.length,
            regularSummary: regularSummary.length,
            exchangeItemsList: exchangeItemsList.length
        });

        returnHeader.show();
        exchangeSearch.show();
        returnSummary.show();
        regularSummary.hide();
        exchangeItemsList.show(); // Always show exchange items list

        // Exchange-summary-block visibility will be controlled by updateReturnOrderTotals
        // based on whether there are exchange items or not

        // Setup click handler for return invoice selection
        const selectedInvoiceInfo = tabContent.find(`#${tabId}_selectedInvoiceInfo`);
        console.log('Setting up click handler for selected invoice info:', selectedInvoiceInfo.length);

        selectedInvoiceInfo.off('click.invoiceSelection').on('click.invoiceSelection', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Selected invoice info clicked, opening modal for tab:', tabId);
            openInvoiceSelectionModal(tabId);
        });

        selectedInvoiceInfo.css({
            'cursor': 'pointer',
            'color': '#28a745',
            'text-decoration': 'underline'
        });
        selectedInvoiceInfo.attr('title', 'Click để chọn hóa đơn');

        // Hide regular create order button and show return action buttons
        tabContent.find('#createOrderBtn').hide();
        tabContent.find('.return-action-buttons').show();

        // Setup return button click handler
        const returnButton = tabContent.find(`#${tabId}_returnButton`);
        returnButton.off('click.returnOrder').on('click.returnOrder', function() {
            createReturnOrder(tabId);
        });

        // Setup print button click handler
        const printButton = tabContent.find(`#${tabId}_printButton`);
        printButton.off('click.printReturn').on('click.printReturn', function() {
            printReturnOrder(tabId);
        });

        // Update totals immediately for return tabs to ensure UI is properly initialized
        setTimeout(() => {
            updateOrderTotals();
        }, 100);
    } else {
        // Hide return-specific elements
        tabContent.find(`#${tabId}_returnOrderHeader`).hide();
        tabContent.find(`#${tabId}_exchangeSearchSection`).hide();
        tabContent.find(`#${tabId}_returnSummarySection`).hide();
        tabContent.find(`#${tabId}_exchangeItemsList`).hide();
        tabContent.find(`#${tabId}_regularSummarySection`).show();

        // Show regular create order button and hide return action buttons
        tabContent.find('#createOrderBtn').show();
        tabContent.find('.return-action-buttons').hide();

        // Set button text based on type
        const buttonText = type === 'invoice' ? 'TẠO HÓA ĐƠN' : 'THANH TOÁN';
        const createBtn = tabContent.find('#createOrderBtn .btn-text');
        if (createBtn.length === 0) {
            // Fallback if .btn-text doesn't exist
            tabContent.find('#createOrderBtn').text(buttonText);
        } else {
            createBtn.text(buttonText);
        }
    }
}

/**
 * Switch to tab
 */
function switchTab(tabId) {
    console.log(`Switching to tab: ${tabId}`);

    // Update active tab
    activeTabId = tabId;

    // Update tab appearance
    $('.order-tab').removeClass('active');
    $(`#${tabId}`).addClass('active');

    // Update tab content
    $('[data-tab-id]').hide();
    $(`#${tabId}_content`).show();

    // Update order totals for active tab
    updateOrderTotals();

    // Focus barcode input
    $('#barcodeInput').focus();

    // Auto-open invoice selection modal for return tabs without selected invoice
    const tab = orderTabs.find(t => t.id === tabId);
    if (tab && tab.type === 'return') {
        // Check if invoice is already selected using helper function
        if (!hasSelectedInvoice(tabId)) {
            // No invoice selected, auto-open modal after a short delay
            setTimeout(() => {
                console.log('Auto-opening invoice selection modal for return tab:', tabId);
                if (typeof openInvoiceSelectionModal === 'function') {
                    openInvoiceSelectionModal(tabId);
                } else {
                    console.warn('openInvoiceSelectionModal function not found');
                }
            }, 300);
        } else {
            console.log('Return tab already has selected invoice');
        }
    }

    console.log(`Switched to tab: ${tabId}`);
}

/**
 * Close tab
 */
function closeTab(tabId) {
    const tab = orderTabs.find(t => t.id === tabId);
    if (!tab) return;

    // Check if tab has items
    if (tab.items.length > 0) {
        // Show confirmation modal
        $('#closeTabName').text(tab.name);
        $('#closeTabNameInBody').text(tab.name);
        $('#confirmCloseTabModal').modal('show');

        // Set up confirmation handler
        $('#confirmCloseTabBtn').off('click').on('click', function() {
            performCloseTab(tabId);
            $('#confirmCloseTabModal').modal('hide');
        });
    } else {
        performCloseTab(tabId);
    }
}

/**
 * Perform actual tab close
 */
function performCloseTab(tabId) {
    // Remove from tabs array
    orderTabs = orderTabs.filter(t => t.id !== tabId);

    // Remove tab element and content
    $(`#${tabId}`).remove();
    $(`#${tabId}_content`).remove();

    // If this was active tab, switch to another
    if (activeTabId === tabId) {
        if (orderTabs.length > 0) {
            switchTab(orderTabs[0].id);
        } else {
            activeTabId = null;
            // Create new tab if no tabs left
            addNewTab('order');
        }
    }

    // Save updated tabs to localStorage
    saveDrafts();

    console.log('Tab closed and drafts saved:', tabId);
}

/**
 * Add product to tab
 */
function addProductToTab(tabId, productData) {
    console.log('addProductToTab called with:', { tabId, productData });

    const tab = orderTabs.find(t => t.id === tabId);
    if (!tab) {
        console.error('Tab not found:', tabId);
        return;
    }

    console.log('Found tab:', tab);

    // Special validation for return tabs
    if (tab.type === 'return') {
        // Check if invoice is selected
        if (!tab.invoice_id) {
            toastr.error('Vui lòng chọn hóa đơn trước khi thêm sản phẩm trả hàng');
            return;
        }

        // Check if product belongs to the selected invoice
        const invoiceHasProduct = tab.items.some(item => item.id === productData.id);
        if (!invoiceHasProduct) {
            toastr.error(`Sản phẩm "${productData.name}" không thuộc hóa đơn đã chọn`);
            return;
        }

        // For return tabs, we update the quantity of existing invoice items
        const existingItemIndex = tab.items.findIndex(item => item.id === productData.id);
        if (existingItemIndex !== -1) {
            const item = tab.items[existingItemIndex];
            if (item.quantity < item.max_quantity) {
                item.quantity += 1;
                toastr.success(`Đã tăng số lượng trả "${item.name}" lên ${item.quantity}`);
            } else {
                toastr.warning(`Số lượng trả không được vượt quá ${item.max_quantity} (số lượng trong hóa đơn)`);
                return;
            }
        }
    } else {
        // Regular tab logic
        const existingItemIndex = tab.items.findIndex(item => item.id === productData.id);
        console.log('Existing item index:', existingItemIndex);

        if (existingItemIndex !== -1) {
            // Increase quantity
            tab.items[existingItemIndex].quantity += 1;
            console.log('Increased quantity for existing item');
        } else {
            // Add new item
            const newItem = {
                id: productData.id,
                name: productData.name,
                sku: productData.sku,
                barcode: productData.barcode,
                price: productData.price,
                quantity: 1,
                stock_quantity: productData.stock_quantity,
                image: productData.image
            };
            tab.items.push(newItem);
            console.log('Added new item:', newItem);
        }

        // Show success message
        toastr.success(`Đã thêm ${productData.name} vào ${tab.name}`);
    }

    console.log('Tab items after update:', tab.items);

    // Update UI
    updateTabDisplay(tabId);
    updateOrderTotals();

    // Auto-save drafts when adding products
    saveDrafts();
}

/**
 * Update tab display
 */
function updateTabDisplay(tabId) {
    const tab = orderTabs.find(t => t.id === tabId);
    if (!tab) return;

    // Update tab count
    $(`#${tabId} .tab-count`).text(`(${tab.items.length})`);

    // Update items list
    updateItemsList(tabId);

    // Update create order button state
    updateCreateOrderButton(tabId);
}

/**
 * Update items list
 */
function updateItemsList(tabId) {
    console.log('updateItemsList called for tab:', tabId);

    const tab = orderTabs.find(t => t.id === tabId);
    if (!tab) {
        console.error('Tab not found in updateItemsList:', tabId);
        return;
    }

    console.log('Tab found, items count:', tab.items.length);

    // Try multiple selectors to find the items list
    let itemsList = $(`#${tabId}_orderItemsList`);
    if (itemsList.length === 0) {
        itemsList = $(`#${tabId}_content .order-items-list`);
    }
    if (itemsList.length === 0) {
        itemsList = $(`#${tabId}_content`).find('.order-items-list');
    }

    console.log('Items list element found:', itemsList.length);

    if (itemsList.length === 0) {
        console.error('Items list element not found for tab:', tabId);
        console.log('Available elements in tab content:', $(`#${tabId}_content`).find('*').map(function() { return this.tagName + (this.id ? '#' + this.id : '') + (this.className ? '.' + this.className.replace(/\s+/g, '.') : ''); }).get());
        return;
    }

    if (tab.items.length === 0) {
        itemsList.html(`
            <div class="empty-order">
                <div class="empty-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="empty-text">
                    Quét mã vạch để bắt đầu
                </div>
                <div class="empty-hint">
                    Nhấn F3 để focus vào ô nhập mã vạch
                </div>
            </div>
        `);
    } else {
        let itemsHtml = '';
        tab.items.forEach((item, index) => {
            const isOutOfStock = item.stock_quantity <= 0;
            const stockWarning = item.stock_quantity <= 5 && item.stock_quantity > 0 ?
                '<div class="stock-warning">Sắp hết hàng</div>' : '';

            // Special handling for return tabs
            let returnInfo = '';
            let isNotReturnable = false;
            if (tab.type === 'return') {
                const returnableQty = item.returnable_quantity || 0;
                const returnedQty = item.returned_quantity || 0;
                const originalQty = item.original_quantity || 0;

                isNotReturnable = returnableQty <= 0;
                returnInfo = `
                    <div class="return-info">
                        <div class="return-qty-info">Gốc: ${originalQty} | Đã trả: ${returnedQty} | Có thể trả: ${returnableQty}</div>
                        ${isNotReturnable ? '<div class="stock-warning">Không thể trả thêm</div>' : ''}
                    </div>
                `;
            }

            itemsHtml += `
                <div class="order-item ${isOutOfStock ? 'out-of-stock' : ''} ${isNotReturnable ? 'not-returnable' : ''}">
                    <img src="${item.image || '/admin-assets/assets/media/svg/files/blank-image.svg'}" class="item-image" alt="">
                    <div class="item-info">
                        <div class="item-name">${item.name}</div>
                        <div class="item-sku">${item.sku}</div>
                        <div class="item-unit-price">Đơn giá: ${formatCurrency(item.price)}</div>
                        ${returnInfo}
                        ${stockWarning}
                        ${isOutOfStock ? '<div class="stock-warning">Hết hàng</div>' : ''}
                    </div>
                    <div class="item-quantity">
                        <button type="button" class="qty-btn" onclick="updateQuantity('${tabId}', ${index}, -1)" ${isNotReturnable ? 'disabled' : ''}>
                            <i class="fas fa-minus"></i>
                        </button>
                        <input type="number" class="qty-input" value="${item.quantity}" max="${item.max_quantity || 999}"
                               onchange="setQuantity('${tabId}', ${index}, this.value)" ${isNotReturnable ? 'disabled' : ''}>
                        <button type="button" class="qty-btn" onclick="updateQuantity('${tabId}', ${index}, 1)" ${isNotReturnable ? 'disabled' : ''}>
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                    <div class="item-price">${formatCurrency(item.price * item.quantity)}</div>
                    <button type="button" class="item-remove" onclick="removeItem('${tabId}', ${index})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
        });
        itemsList.html(itemsHtml);
    }
}

/**
 * Update exchange items list
 */
function updateExchangeItemsList(tabId) {
    console.log('updateExchangeItemsList called for tab:', tabId);

    const tab = orderTabs.find(t => t.id === tabId);
    if (!tab || tab.type !== 'return') {
        console.error('Tab not found or not a return tab:', tabId);
        return;
    }

    // Initialize exchangeItems if not exists
    if (!tab.exchangeItems) {
        tab.exchangeItems = [];
    }

    console.log('Exchange items count:', tab.exchangeItems.length);

    // Find exchange items list container
    let exchangeItemsList = $(`#${tabId}_exchangeItemsList .exchange-items-content`);
    if (exchangeItemsList.length === 0) {
        console.error('Exchange items list container not found for tab:', tabId);
        return;
    }

    console.log('Exchange items list element found:', exchangeItemsList.length);

    // Update exchange items count
    const exchangeItemsCount = $(`#${tabId}_exchangeItemsList .exchange-items-count`);
    exchangeItemsCount.text(tab.exchangeItems.length);

    if (tab.exchangeItems.length === 0) {
        // Show empty state
        const emptyState = $(`#${tabId}_emptyExchangeState`);
        emptyState.show();
        exchangeItemsList.find('.exchange-item').remove();
    } else {
        // Hide empty state
        const emptyState = $(`#${tabId}_emptyExchangeState`);
        emptyState.hide();
        let itemsHtml = '';
        tab.exchangeItems.forEach((item, index) => {
            const isOutOfStock = item.stock_quantity <= 0;
            const stockWarning = item.stock_quantity <= 5 && item.stock_quantity > 0 ?
                '<div class="stock-warning">Sắp hết hàng</div>' : '';

            itemsHtml += `
                <div class="order-item ${isOutOfStock ? 'out-of-stock' : ''}">
                    <img src="${item.image || '/admin-assets/assets/media/svg/files/blank-image.svg'}" class="item-image" alt="">
                    <div class="item-info">
                        <div class="item-name">${item.name}</div>
                        <div class="item-sku">${item.sku || item.barcode}</div>
                        <div class="item-unit-price">Đơn giá: ${formatCurrency(item.price)}</div>
                        ${stockWarning}
                        ${isOutOfStock ? '<div class="stock-warning">Hết hàng</div>' : ''}
                    </div>
                    <div class="item-quantity">
                        <button type="button" class="qty-btn" onclick="updateExchangeQuantity('${tabId}', ${index}, -1)">
                            <i class="fas fa-minus"></i>
                        </button>
                        <input type="number" class="qty-input" value="${item.quantity}"
                               onchange="setExchangeQuantity('${tabId}', ${index}, this.value)">
                        <button type="button" class="qty-btn" onclick="updateExchangeQuantity('${tabId}', ${index}, 1)">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                    <div class="item-price">${formatCurrency(item.price * item.quantity)}</div>
                    <button type="button" class="item-remove" onclick="removeExchangeItem('${tabId}', ${index})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
        });

        // Remove existing exchange items and add new ones
        exchangeItemsList.find('.order-item').remove();
        exchangeItemsList.append(itemsHtml);
    }
}

/**
 * Update quantity
 */
function updateQuantity(tabId, itemIndex, change) {
    const tab = orderTabs.find(t => t.id === tabId);
    if (!tab || !tab.items[itemIndex]) return;

    const item = tab.items[itemIndex];
    const newQuantity = item.quantity + change;

    if (newQuantity <= 0) {
        removeItem(tabId, itemIndex);
    } else {
        // For return tabs, check maximum returnable quantity
        if (tab.type === 'return' && item.returnable_quantity !== undefined && newQuantity > item.returnable_quantity) {
            toastr.warning(`Số lượng trả không được vượt quá ${item.returnable_quantity} (có thể trả)`);
            return;
        }

        item.quantity = newQuantity;
        updateTabDisplay(tabId);
        updateOrderTotals();

        // Auto-save drafts when quantity changes
        saveDrafts();
    }
}

/**
 * Set quantity
 */
function setQuantity(tabId, itemIndex, quantity) {
    const tab = orderTabs.find(t => t.id === tabId);
    if (!tab || !tab.items[itemIndex]) return;

    const item = tab.items[itemIndex];
    const newQuantity = parseInt(quantity) || 0;

    if (newQuantity <= 0) {
        removeItem(tabId, itemIndex);
    } else {
        // For return tabs, check maximum returnable quantity
        if (tab.type === 'return' && item.returnable_quantity !== undefined && newQuantity > item.returnable_quantity) {
            toastr.warning(`Số lượng trả không được vượt quá ${item.returnable_quantity} (có thể trả)`);
            // Reset to max returnable quantity
            item.quantity = item.returnable_quantity;
        } else {
            item.quantity = newQuantity;
        }

        updateTabDisplay(tabId);
        updateOrderTotals();

        // Auto-save drafts when quantity changes
        saveDrafts();
    }
}

/**
 * Update exchange item quantity
 */
function updateExchangeQuantity(tabId, itemIndex, change) {
    const tab = orderTabs.find(t => t.id === tabId);
    if (!tab || !tab.exchangeItems || !tab.exchangeItems[itemIndex]) return;

    const item = tab.exchangeItems[itemIndex];
    const newQuantity = item.quantity + change;

    if (newQuantity <= 0) {
        removeExchangeItem(tabId, itemIndex);
    } else {
        item.quantity = newQuantity;
        item.total = item.price * item.quantity;
        updateExchangeItemsList(tabId);
        updateOrderTotals();
    }
}

/**
 * Set exchange item quantity
 */
function setExchangeQuantity(tabId, itemIndex, quantity) {
    const tab = orderTabs.find(t => t.id === tabId);
    if (!tab || !tab.exchangeItems || !tab.exchangeItems[itemIndex]) return;

    const newQuantity = parseInt(quantity) || 0;

    if (newQuantity <= 0) {
        removeExchangeItem(tabId, itemIndex);
    } else {
        const item = tab.exchangeItems[itemIndex];
        item.quantity = newQuantity;
        item.total = item.price * item.quantity;
        updateExchangeItemsList(tabId);
        updateOrderTotals();
    }
}

/**
 * Remove exchange item
 */
function removeExchangeItem(tabId, itemIndex) {
    const tab = orderTabs.find(t => t.id === tabId);
    if (!tab || !tab.exchangeItems) return;

    const item = tab.exchangeItems[itemIndex];
    if (item) {
        tab.exchangeItems.splice(itemIndex, 1);
        toastr.info(`Đã xóa ${item.name} khỏi danh sách hàng đổi`);
        updateExchangeItemsList(tabId);
        updateOrderTotals();
    }
}

/**
 * Remove item
 */
function removeItem(tabId, itemIndex) {
    const tab = orderTabs.find(t => t.id === tabId);
    if (!tab || !tab.items[itemIndex]) return;

    const itemName = tab.items[itemIndex].name;
    tab.items.splice(itemIndex, 1);

    // Update display but keep return-order-header for return tabs
    updateTabDisplay(tabId);
    updateOrderTotals();

    // Auto-save drafts when item removed
    saveDrafts();

    // Show success message
    toastr.info(`Đã xóa ${itemName} khỏi danh sách trả hàng`);

    // Note: For return tabs, we keep the invoice info and return-order-header
    // even when all items are removed, as per requirement
}

/**
 * Update order totals
 */
function updateOrderTotals() {
    if (!activeTabId) return;

    const tab = orderTabs.find(t => t.id === activeTabId);
    if (!tab) return;

    // Use different calculation logic for return tabs
    if (tab.type === 'return') {
        updateReturnOrderTotals(activeTabId);
    } else {
        updateRegularOrderTotals(activeTabId);
    }
}

/**
 * Update regular order/invoice totals
 */
function updateRegularOrderTotals(tabId) {
    const tab = orderTabs.find(t => t.id === tabId);
    if (!tab) return;

    const tabContent = $(`#${tabId}_content`);

    // Calculate subtotal
    let subtotal = 0;
    tab.items.forEach(item => {
        subtotal += item.price * item.quantity;
    });

    // Get discount and other charges
    const discountAmount = tab.discount_amount || 0;
    const otherChargesAmount = tab.other_charges_amount || 0;

    // Calculate total
    const total = subtotal - discountAmount + otherChargesAmount;

    // Update UI
    tabContent.find('#subtotalAmount').text(formatCurrency(subtotal));
    tabContent.find('#discountAmount').val(formatCurrency(discountAmount));
    tabContent.find('#otherAmount').val(formatCurrency(otherChargesAmount));
    tabContent.find('#totalAmount').text(formatCurrency(total));

    // Auto-update paid amount if not manually edited
    if (!paidAmountUserEdited) {
        tab.paid_amount = total;
        tabContent.find('#paidAmount').val(formatCurrency(total));
    }

    // Update create order button
    updateCreateOrderButton(tabId);
}

/**
 * Update return order totals
 */
function updateReturnOrderTotals(tabId) {
    const tab = orderTabs.find(t => t.id === tabId);
    if (!tab) return;

    const tabContent = $(`#${tabId}_content`);

    // Calculate return items totals
    let returnSubtotal = 0;
    let returnQuantity = 0;
    tab.items.forEach(item => {
        returnSubtotal += item.price * item.quantity;
        returnQuantity += item.quantity;
    });

    // Calculate exchange items totals (if any)
    let exchangeSubtotal = 0;
    let exchangeQuantity = 0;
    if (tab.exchangeItems && tab.exchangeItems.length > 0) {
        tab.exchangeItems.forEach(item => {
            exchangeSubtotal += item.price * item.quantity;
            exchangeQuantity += item.quantity;
        });
    }

    // Get discount and other charges
    const returnDiscountAmount = tab.return_discount_amount || 0;
    const returnFeeAmount = tab.return_fee_amount || 0;
    const returnOtherAmount = tab.return_other_amount || 0;

    const exchangeDiscountAmount = tab.exchange_discount_amount || 0;
    const exchangeOtherAmount = tab.exchange_other_amount || 0;

    // Calculate totals
    const returnTotal = returnSubtotal - returnDiscountAmount + returnFeeAmount - returnOtherAmount;
    const exchangeTotal = exchangeSubtotal - exchangeDiscountAmount + exchangeOtherAmount;
    const refundAmount = returnTotal - exchangeTotal;

    // Update Return Summary UI
    tabContent.find(`#${tabId}_originalTotalAmount`).text(formatCurrency(returnSubtotal));
    tabContent.find(`#${tabId}_returnTotalAmount`).text(formatCurrency(returnSubtotal));
    tabContent.find(`#${tabId}_returnDiscountAmount`).text(formatCurrency(returnDiscountAmount));
    tabContent.find(`#${tabId}_returnFeeAmount`).text(formatCurrency(returnFeeAmount));
    tabContent.find(`#${tabId}_returnOtherAmount`).text(formatCurrency(returnOtherAmount));
    tabContent.find(`#${tabId}_returnTotalRefund`).text(formatCurrency(returnTotal));

    // Update return quantity display
    tabContent.find('.return-summary-block .summary-count').text(returnQuantity);

    // Show/Hide Exchange Summary Block based on exchange items
    const exchangeSummaryBlock = tabContent.find('.exchange-summary-block');
    if (exchangeQuantity > 0) {
        exchangeSummaryBlock.show();
        // Update Exchange/Purchase Summary UI
        tabContent.find(`#${tabId}_exchangeSubtotalAmount`).text(formatCurrency(exchangeSubtotal));
        tabContent.find(`#${tabId}_exchangeDiscountAmount`).text(formatCurrency(exchangeDiscountAmount));
        tabContent.find(`#${tabId}_exchangeOtherAmount`).text(formatCurrency(exchangeOtherAmount));
        tabContent.find(`#${tabId}_exchangeTotalAmount`).text(formatCurrency(exchangeTotal));

        // Update exchange quantity display
        tabContent.find('.exchange-summary-block .summary-count').text(exchangeQuantity);
    } else {
        exchangeSummaryBlock.hide();
    }

    // Update Final Calculation Summary
    updateFinalCalculationSummary(tabId, exchangeTotal, returnTotal, refundAmount);

    // Store calculated values in tab
    tab.return_total = returnTotal;
    tab.exchange_total = exchangeTotal;
    tab.refund_amount = refundAmount;

    console.log('Return totals updated:', {
        tabId: tabId,
        returnSubtotal: returnSubtotal,
        returnTotal: returnTotal,
        exchangeSubtotal: exchangeSubtotal,
        exchangeTotal: exchangeTotal,
        refundAmount: refundAmount
    });
}

/**
 * Update final calculation summary with dynamic text and amounts
 */
function updateFinalCalculationSummary(tabId, exchangeTotal, returnTotal, refundAmount) {
    const tabContent = $(`#${tabId}_content`);

    // Determine text based on comparison
    let finalCalculationLabel, paymentLabel;
    let finalCalculationAmount = Math.abs(refundAmount);

    if (exchangeTotal < returnTotal) {
        // Customer gets refund
        finalCalculationLabel = 'Cần trả khách';
        paymentLabel = 'Tiền trả khách';
    } else {
        // Customer needs to pay more
        finalCalculationLabel = 'Khách cần trả';
        paymentLabel = 'Khách cần thanh toán';
    }

    // Update UI
    tabContent.find(`#${tabId}_finalCalculationLabel`).text(finalCalculationLabel);
    tabContent.find(`#${tabId}_finalCalculationAmount`).text(formatCurrency(finalCalculationAmount));
    tabContent.find(`#${tabId}_paymentLabel`).text(paymentLabel);
    tabContent.find(`#${tabId}_paymentAmount`).text(formatCurrency(finalCalculationAmount));

    console.log('Final calculation updated:', {
        tabId: tabId,
        exchangeTotal: exchangeTotal,
        returnTotal: returnTotal,
        refundAmount: refundAmount,
        finalCalculationLabel: finalCalculationLabel,
        paymentLabel: paymentLabel,
        finalCalculationAmount: finalCalculationAmount
    });
}

/**
 * Update create order button
 */
function updateCreateOrderButton(tabId) {
    const tab = orderTabs.find(t => t.id === tabId);
    if (!tab) return;

    const tabContent = $(`#${tabId}_content`);
    const createBtn = tabContent.find('#createOrderBtn');

    if (tab.items.length > 0) {
        createBtn.prop('disabled', false);
    } else {
        createBtn.prop('disabled', true);
    }
}

/**
 * Setup tab defaults
 */
function setupTabDefaults(tabId) {
    const tab = orderTabs.find(t => t.id === tabId);
    if (!tab) return;

    const tabContent = $(`#${tabId}_content`);

    // Set default branch shop
    if (defaultBranchShop) {
        tabContent.find(`#${tabId}_branchShopName`).text(defaultBranchShop.name);
        tabContent.find('#branchShopSelect').val(defaultBranchShop.id);
    }

    // Set default payment method
    tabContent.find('input[name="payment_method"][value="cash"]').prop('checked', true);
    tabContent.find('.payment-method[data-method="cash"]').addClass('active');

    // Hide bank account section initially
    tabContent.find('#bankAccountSection').hide();

    // Set default bank account if available
    if (bankAccounts.length > 0) {
        const defaultAccount = bankAccounts.find(acc => acc.is_default) || bankAccounts[0];
        tabContent.find('#bankAccountId').val(defaultAccount.id);
        tabContent.find('#selectedBankText').text(`${defaultAccount.bank_name} - ${defaultAccount.account_number}`);
    }
}

/**
 * Bind tab events
 */
function bindTabEvents(tabId) {
    const tabContent = $(`#${tabId}_content`);

    // Payment method change
    tabContent.on('change', 'input[name="payment_method"]', function() {
        const method = $(this).val();

        // Update active payment method
        tabContent.find('.payment-method').removeClass('active');
        tabContent.find(`.payment-method[data-method="${method}"]`).addClass('active');

        // Show/hide bank account section
        if (method === 'transfer') {
            tabContent.find('#bankAccountSection').show();
        } else {
            tabContent.find('#bankAccountSection').hide();
        }

        // Update tab data
        const tab = orderTabs.find(t => t.id === tabId);
        if (tab) {
            tab.payment_method = method;
        }
    });

    // Bank account selection
    tabContent.on('click', '.bank-account-option', function() {
        const accountId = $(this).data('account-id');
        const bankName = $(this).data('bank-name');
        const accountNumber = $(this).data('account-number');

        // Update selection
        tabContent.find('.bank-account-option').removeClass('selected');
        $(this).addClass('selected');

        // Update display
        tabContent.find('#selectedBankText').text(`${bankName} - ${accountNumber}`);
        tabContent.find('#bankAccountId').val(accountId);

        // Update tab data
        const tab = orderTabs.find(t => t.id === tabId);
        if (tab) {
            tab.bank_account_id = accountId;
        }

        // Hide dropdown
        tabContent.find('#bankAccountOptions').hide();
    });

    // Bank account dropdown toggle
    tabContent.on('click', '#bankAccountSelect', function() {
        tabContent.find('#bankAccountOptions').toggle();
    });

    // Hide bank account dropdown when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#bankAccountSelect, #bankAccountOptions').length) {
            tabContent.find('#bankAccountOptions').hide();
        }
    });

    // Paid amount input
    tabContent.on('input', '#paidAmount', function() {
        formatCurrencyInput(this);
        paidAmountUserEdited = true;

        const tab = orderTabs.find(t => t.id === tabId);
        if (tab) {
            tab.paid_amount = parseCurrency($(this).val());
        }
    });

    // Notes input
    tabContent.on('input', '#orderNotes', function() {
        const tab = orderTabs.find(t => t.id === tabId);
        if (tab) {
            tab.notes = $(this).val();
        }
    });

    // Create order button
    tabContent.on('click', '#createOrderBtn', function() {
        createOrderOrInvoice(tabId);
    });

    // Setup dropdown event handlers
    setupDropdownHandlers(tabId, tabContent);
}

/**
 * Setup dropdown handlers for a tab
 */
function setupDropdownHandlers(tabId, tabContent) {
    // Seller dropdown
    tabContent.on('click', `#${tabId}_sellerToggle`, function(e) {
        e.stopPropagation();
        if ($(this).hasClass('disabled')) return;

        const menu = tabContent.find(`#${tabId}_sellerMenu`);
        const isVisible = menu.hasClass('show');

        // Close all other dropdowns
        tabContent.find('.info-dropdown-menu').removeClass('show');

        if (!isVisible) {
            menu.addClass('show');
        }
    });

    // Channel dropdown
    tabContent.on('click', `#${tabId}_channelToggle`, function(e) {
        e.stopPropagation();

        const menu = tabContent.find(`#${tabId}_channelMenu`);
        const isVisible = menu.hasClass('show');

        // Close all other dropdowns
        tabContent.find('.info-dropdown-menu').removeClass('show');

        if (!isVisible) {
            menu.addClass('show');
        }
    });

    // Branch shop dropdown
    tabContent.on('click', `#${tabId}_branchShopToggle`, function(e) {
        e.stopPropagation();

        const menu = tabContent.find(`#${tabId}_branchShopMenu`);
        const isVisible = menu.hasClass('show');

        // Close all other dropdowns
        tabContent.find('.info-dropdown-menu').removeClass('show');

        if (!isVisible) {
            menu.addClass('show');
        }
    });

    // Handle dropdown item selections
    tabContent.on('click', '.info-dropdown-item', function(e) {
        e.stopPropagation();

        const dropdown = $(this).closest('.info-dropdown');
        const dropdownId = dropdown.attr('id');

        if (dropdownId.includes('seller')) {
            const sellerId = $(this).data('seller-id');
            const sellerName = $(this).find('span').text();

            // Update UI
            tabContent.find(`#${tabId}_sellerName`).text(sellerName);

            // Update tab data
            const tab = orderTabs.find(t => t.id === tabId);
            if (tab) {
                tab.seller_id = sellerId;
                tab.seller_name = sellerName;
            }
        } else if (dropdownId.includes('channel')) {
            const channel = $(this).data('channel');
            const channelName = $(this).find('span').text();

            // Update UI
            tabContent.find(`#${tabId}_channelName`).text(channelName);

            // Update selected state
            dropdown.find('.info-dropdown-item').removeClass('selected');
            $(this).addClass('selected');

            // Update tab data
            const tab = orderTabs.find(t => t.id === tabId);
            if (tab) {
                tab.channel = channel;
                tab.channel_name = channelName;
            }
        } else if (dropdownId.includes('branchShop')) {
            const branchShopId = $(this).data('branch-id');
            const branchShopName = $(this).find('.info-dropdown-title').text();

            // Update UI
            tabContent.find(`#${tabId}_branchShopName`).text(branchShopName);

            // Update selected state
            dropdown.find('.info-dropdown-item').removeClass('selected');
            $(this).addClass('selected');

            // Update tab data
            const tab = orderTabs.find(t => t.id === tabId);
            if (tab) {
                tab.branch_shop_id = branchShopId;
                tab.branch_shop_name = branchShopName;
            }
        }

        // Close dropdown
        dropdown.find('.info-dropdown-menu').removeClass('show');
    });

    // Close dropdowns when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.info-dropdown').length) {
            tabContent.find('.info-dropdown-menu').removeClass('show');
        }
    });
}

/**
 * Open discount modal
 */
function openDiscountModal() {
    if (!activeTabId) return;

    const tab = orderTabs.find(t => t.id === activeTabId);
    if (!tab) return;

    // Set current discount value
    $('#discountInput').val(tab.discount_amount || 0);
    $('#discountModal').modal('show');
}

/**
 * Apply discount
 */
function applyDiscount() {
    if (!activeTabId) return;

    const tab = orderTabs.find(t => t.id === activeTabId);
    if (!tab) return;

    const discountValue = parseCurrency($('#discountInput').val());
    tab.discount_amount = discountValue;

    updateOrderTotals();
    $('#discountModal').modal('hide');

    toastr.success('Đã áp dụng giảm giá');
}

/**
 * Open other charges modal
 */
function openOtherChargesModal() {
    if (!activeTabId) return;

    $('#otherChargesModal').modal('show');
}

/**
 * Apply other charges
 */
function applyOtherCharges() {
    if (!activeTabId) return;

    const tab = orderTabs.find(t => t.id === activeTabId);
    if (!tab) return;

    // Calculate total from selected charges
    let total = 0;
    $('#otherChargesTableBody input[type="checkbox"]:checked').each(function() {
        total += parseInt($(this).data('amount')) || 0;
    });

    tab.other_charges_amount = total;

    updateOrderTotals();
    $('#otherChargesModal').modal('hide');

    toastr.success('Đã áp dụng các khoản thu khác');
}

/**
 * Add other charge
 */
function addOtherCharge() {
    const code = $('#newChargeCode').val().trim();
    const description = $('#newChargeDescription').val().trim();
    const amount = parseCurrency($('#newChargeAmount').val());

    if (!code || !description || !amount) {
        toastr.warning('Vui lòng nhập đầy đủ thông tin');
        return;
    }

    // Add new row to table
    const newRow = `
        <tr>
            <td><input type="checkbox" class="charge-checkbox" data-amount="${amount}"></td>
            <td>${code}</td>
            <td>${description}</td>
            <td class="text-end">${formatCurrency(amount)}</td>
            <td class="text-end">0</td>
        </tr>
    `;

    $('#otherChargesTableBody').append(newRow);

    // Clear form
    $('#newChargeCode').val('');
    $('#newChargeDescription').val('');
    $('#newChargeAmount').val('');

    toastr.success('Đã thêm khoản thu mới');
}

/**
 * Toggle all charges
 */
function toggleAllCharges(checkbox) {
    const isChecked = $(checkbox).prop('checked');
    $('.charge-checkbox').prop('checked', isChecked);
    updateTotalOtherCharges();
}

/**
 * Update total other charges
 */
function updateTotalOtherCharges() {
    let total = 0;
    $('.charge-checkbox:checked').each(function() {
        total += parseInt($(this).data('amount')) || 0;
    });

    $('#totalOtherCharges').text(formatCurrency(total));
}

/**
 * Create order or invoice
 */
function createOrderOrInvoice(tabId) {
    const tab = orderTabs.find(t => t.id === tabId);
    if (!tab || tab.items.length === 0) {
        toastr.warning('Vui lòng thêm sản phẩm vào đơn hàng');
        return;
    }

    // Prepare order data
    const orderData = {
        type: tab.type,
        customer_id: tab.customer_id || 0,
        branch_shop_id: tab.branch_shop_id,
        sold_by: tab.sold_by || currentUserId, // Add sold_by field
        payment_method: tab.payment_method,
        bank_account_id: tab.bank_account_id,
        channel: tab.channel,
        notes: tab.notes,
        items: tab.items.map(item => ({
            product_id: item.id, // Map id to product_id
            quantity: item.quantity,
            price: item.price
        })),
        discount_amount: tab.discount_amount || 0,
        other_amount: tab.other_charges_amount || 0, // Fix field name
        amount_paid: tab.paid_amount || 0, // Fix field name
        _token: $('meta[name="csrf-token"]').attr('content')
    };

    // Show loading
    const createBtn = $(`#${tabId}_content #createOrderBtn`);
    const originalText = createBtn.find('.btn-text').text();
    createBtn.prop('disabled', true).find('.btn-text').text('Đang xử lý...');

    // Determine the correct endpoint based on tab type
    let endpoint;
    if (tab.type === 'invoice') {
        endpoint = '/admin/quick-invoice';
    } else {
        endpoint = '/admin/quick-order';
    }

    // Send request
    $.ajax({
        url: endpoint,
        method: 'POST',
        data: orderData,
        success: function(response) {
            if (response.success) {
                toastr.success(response.message || 'Tạo đơn hàng thành công');

                // Clear tab
                clearTab(tabId);

                // Optionally redirect or show receipt
                if (response.redirect_url) {
                    window.open(response.redirect_url, '_blank');
                }
            } else {
                toastr.error(response.message || 'Có lỗi xảy ra');
            }
        },
        error: function(xhr, status, error) {
            console.error('Create order error:', error);
            toastr.error('Có lỗi xảy ra khi tạo đơn hàng');
        },
        complete: function() {
            // Restore button
            createBtn.prop('disabled', false).find('.btn-text').text(originalText);
        }
    });
}

/**
 * Clear tab
 */
function clearTab(tabId) {
    const tab = orderTabs.find(t => t.id === tabId);
    if (!tab) return;

    // Reset tab data
    tab.items = [];
    tab.customer_id = '';
    tab.customer_search = '';
    tab.notes = '';
    tab.discount_amount = 0;
    tab.other_charges_amount = 0;
    tab.paid_amount = 0;

    // Reset UI
    updateTabDisplay(tabId);
    updateOrderTotals();

    // Reset form fields
    const tabContent = $(`#${tabId}_content`);
    tabContent.find('#customerSearch').val('');
    tabContent.find('#orderNotes').val('');
    tabContent.find('#selectedCustomerDisplay').hide();
    tabContent.find('#customerSearchContainer').show();

    // Reset paid amount user edited flag
    paidAmountUserEdited = false;
}

/**
 * Mark paid amount as user edited
 */
function markPaidAmountAsUserEdited() {
    paidAmountUserEdited = true;
}

/**
 * Create new tab (alias for addNewTab for compatibility)
 */
function createNewTab(type = 'order') {
    return addNewTab(type);
}

/**
 * Save drafts to localStorage
 */
function saveDrafts() {
    try {
        const draftsData = {
            tabs: orderTabs,
            activeTabId: activeTabId,
            tabCounter: tabCounter,
            timestamp: new Date().toISOString()
        };

        localStorage.setItem('quickOrderDrafts', JSON.stringify(draftsData));

        // Log return tabs with invoice data for debugging
        const returnTabs = orderTabs.filter(tab => tab.type === 'return');
        console.log('💾 Drafts saved. Return tabs with invoice data:',
            returnTabs.map(tab => ({
                id: tab.id,
                name: tab.name,
                has_invoice_id: !!tab.invoice_id,
                invoice_id: tab.invoice_id,
                invoice_number: tab.invoice_number,
                customer_name: tab.customer_name
            }))
        );
    } catch (error) {
        console.error('❌ Error saving drafts:', error);
    }
}

/**
 * Load drafts from localStorage
 */
function loadDrafts() {
    try {
        const draftsData = localStorage.getItem('quickOrderDrafts');
        if (draftsData) {
            const parsed = JSON.parse(draftsData);

            // Check if drafts are not too old (24 hours)
            const timestamp = new Date(parsed.timestamp);
            const now = new Date();
            const hoursDiff = (now - timestamp) / (1000 * 60 * 60);

            if (hoursDiff < 24 && parsed.tabs && parsed.tabs.length > 0) {
                // Restore drafts
                orderTabs = parsed.tabs;
                tabCounter = parsed.tabCounter || 0;

                // Recreate tabs UI
                parsed.tabs.forEach(tabData => {
                    restoreTab(tabData);
                });

                // Switch to active tab
                if (parsed.activeTabId && orderTabs.find(t => t.id === parsed.activeTabId)) {
                    switchTab(parsed.activeTabId);
                } else if (orderTabs.length > 0) {
                    switchTab(orderTabs[0].id);
                }

                return true; // Drafts loaded
            } else {
                // Clear old or invalid drafts
                clearDrafts();
            }
        }
    } catch (error) {
        console.error('Error loading drafts:', error);
        // Clear corrupted drafts
        clearDrafts();
    }

    return false; // No drafts loaded
}

/**
 * Clear drafts from localStorage
 */
function clearDrafts() {
    try {
        localStorage.removeItem('quickOrderDrafts');
        console.log('Drafts cleared from localStorage');
    } catch (error) {
        console.error('Error clearing drafts:', error);
    }
}

/**
 * Restore tab from saved data
 */
function restoreTab(tabData) {
    // Create tab element
    const tabElement = $(`
        <div class="order-tab ${tabData.type}" id="${tabData.id}" onclick="switchTab('${tabData.id}')">
            <span class="tab-title">${tabData.name}</span>
            <span class="tab-count">(${tabData.items.length})</span>
            <button type="button" class="tab-close" onclick="closeTab('${tabData.id}')">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `);

    // Add tab to container
    $('#orderTabsContainer').append(tabElement);

    // Create tab content
    createTabContent(tabData.id, tabData);

    // Restore tab data
    restoreTabData(tabData.id, tabData);
}

/**
 * Restore tab data
 */
function restoreTabData(tabId, tabData) {
    const tabContent = $(`#${tabId}_content`);

    // Restore customer
    if (tabData.customer_id) {
        tabContent.find('#customerSelect').val(tabData.customer_id);
        tabContent.find('#selectedCustomerText').text(tabData.customer_search);
        tabContent.find('#selectedCustomerDisplay').show();
        tabContent.find('#customerSearchContainer').hide();
    }

    // Restore payment method
    if (tabData.payment_method) {
        tabContent.find(`input[name="payment_method"][value="${tabData.payment_method}"]`).prop('checked', true);
        tabContent.find('.payment-method').removeClass('active');
        tabContent.find(`.payment-method[data-method="${tabData.payment_method}"]`).addClass('active');

        if (tabData.payment_method === 'transfer') {
            tabContent.find('#bankAccountSection').show();
        }
    }

    // Restore bank account
    if (tabData.bank_account_id) {
        tabContent.find('#bankAccountId').val(tabData.bank_account_id);
    }

    // Restore notes
    if (tabData.notes) {
        tabContent.find('#orderNotes').val(tabData.notes);
    }

    // Restore invoice selection for return tabs
    if (tabData.type === 'return' && tabData.invoice_id) {
        // Set hidden field
        tabContent.find(`#${tabId}_selectedInvoiceId`).val(tabData.invoice_id);

        // Update return-order-header if invoice data exists
        if (tabData.invoice_number && tabData.customer_name) {
            const returnOrderHeader = tabContent.find(`#${tabId}_returnOrderHeader`);
            returnOrderHeader.show();
            tabContent.find(`#${tabId}_returnCustomerName`).text(tabData.customer_name);
            tabContent.find(`#${tabId}_returnCustomerPoints`).text(`SĐT: ${tabData.customer_phone || 'N/A'}`);

            // Update order title with employee name and clickable invoice number
            const employeeName = tabData.employee_name || 'N/A';
            const orderTitleElement = tabContent.find(`#${tabId}_returnOrderTitle`);
            orderTitleElement.html(`
                Trả hàng /
                <a href="/admin/invoices?code=${tabData.invoice_number}"
                   target="_blank"
                   style="color: #009ef7; text-decoration: none;"
                   onmouseover="this.style.textDecoration='underline'"
                   onmouseout="this.style.textDecoration='none'">
                   ${tabData.invoice_number}
                </a>
                - ${employeeName}
            `);

            tabContent.find(`#${tabId}_selectedInvoiceInfo`).text(`${tabData.invoice_number} - ${tabData.customer_name}`);
        }
    }

    // Restore exchange items for return tabs
    if (tabData.type === 'return' && tabData.exchangeItems && tabData.exchangeItems.length > 0) {
        console.log('🔄 Restoring exchange items for tab:', tabId, 'Items:', tabData.exchangeItems.length);
        updateExchangeItemsList(tabId);
    }

    // Update display
    updateTabDisplay(tabId);
}

/**
 * Auto-save drafts every 30 seconds
 */
setInterval(function() {
    if (orderTabs.length > 0) {
        saveDrafts();
    }
}, 30000);

/**
 * Save drafts when page unloads
 */
$(window).on('beforeunload', function() {
    saveDrafts();
});

/**
 * Customer search functionality
 */
function setupCustomerSearch() {
    $(document).on('input', '[id$="_customerSearch"]', function() {
        const query = $(this).val().trim();
        const tabId = $(this).attr('id').replace('_customerSearch', '');
        const suggestionsContainer = $(`#${tabId}_customerSuggestions`);

        if (query.length >= 2) {
            // Filter customers
            const filteredCustomers = window.customers.filter(customer =>
                customer.name.toLowerCase().includes(query.toLowerCase()) ||
                customer.phone.includes(query) ||
                (customer.customer_code && customer.customer_code.toLowerCase().includes(query.toLowerCase()))
            );

            if (filteredCustomers.length > 0) {
                let html = '';
                filteredCustomers.forEach(customer => {
                    html += `
                        <div class="autocomplete-suggestion" data-id="${customer.id}" data-name="${customer.name}" data-phone="${customer.phone}" data-code="${customer.customer_code || ''}" data-tab-id="${tabId}">
                            <div class="suggestion-name">${customer.name}</div>
                            <div class="suggestion-details">${customer.customer_code || 'N/A'} - ${customer.phone}</div>
                        </div>
                    `;
                });
                suggestionsContainer.html(html).addClass('show');
            } else {
                suggestionsContainer.removeClass('show');
            }
        } else {
            suggestionsContainer.removeClass('show');
        }
    });

    // Select customer
    $(document).on('click', '.autocomplete-suggestion', function() {
        const customerId = $(this).data('id');
        const customerName = $(this).data('name');
        const customerPhone = $(this).data('phone');
        const customerCode = $(this).data('code');
        const tabId = $(this).data('tab-id');

        // Update tab
        if (tabId) {
            const tab = orderTabs.find(t => t.id === tabId);
            if (tab) {
                tab.customer_id = customerId;
                tab.customer_search = `${customerName} - ${customerPhone}`;

                // Update UI
                const tabContent = $(`#${tabId}_content`);
                tabContent.find(`#${tabId}_customerSearch`).val(customerName);
                tabContent.find(`#${tabId}_selectedCustomerText`)
                    .text(`${customerName} - ${customerPhone}`)
                    .data('customer-id', customerId);
                tabContent.find(`#${tabId}_selectedCustomerDisplay`).show();
                tabContent.find(`#${tabId}_customerSearchContainer`).hide();
                tabContent.find(`#${tabId}_customerSuggestions`).removeClass('show');
            }
        }
    });

    // Remove customer
    $(document).on('click', '[id$="_removeCustomerBtn"]', function() {
        const tabId = $(this).attr('id').replace('_removeCustomerBtn', '');

        if (tabId) {
            const tab = orderTabs.find(t => t.id === tabId);
            if (tab) {
                tab.customer_id = '';
                tab.customer_search = '';

                // Update UI
                const tabContent = $(`#${tabId}_content`);
                tabContent.find(`#${tabId}_customerSelect`).val('');
                tabContent.find(`#${tabId}_customerSearch`).val('');
                tabContent.find(`#${tabId}_selectedCustomerDisplay`).hide();
                tabContent.find(`#${tabId}_customerSearchContainer`).show();
            }
        }
    });

    // Click on customer name to show info modal
    $(document).on('click', '[id$="_selectedCustomerText"]', function() {
        const customerId = $(this).data('customer-id');
        if (customerId) {
            // Show loading state
            const modal = document.querySelector('#customerInfoModal');
            if (modal) {
                // Clear previous data
                $('#customerModalName').text('Đang tải...');
                $('#customerModalCode').text('');
                $('#customerModalBranch').text('');

                const bootstrapModal = new bootstrap.Modal(modal);
                bootstrapModal.show();

                // Load customer data from API
                loadCustomerInfoFromAPI(customerId);
            }
        }
    });

    // Hide suggestions when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.autocomplete-container').length) {
            $('.autocomplete-suggestions').removeClass('show');
        }
    });

    // Save customer button click handler
    $(document).on('click', '#saveCustomerBtn', function() {
        saveCustomerChanges();
    });
}

/**
 * Load customer info from API
 */
function loadCustomerInfoFromAPI(customerId) {
    $.ajax({
        url: `/admin/customers/${customerId}/info`,
        method: 'GET',
        success: function(response) {
            if (response.success && response.data) {
                const customer = response.data.customer;
                const branchShop = response.data.branch_shop;
                const statistics = response.data.statistics;

                // Update modal header
                $('#customerModalName').text(customer.name || 'N/A');
                $('#customerModalCode').text(customer.customer_code ? `(${customer.customer_code})` : '');

                // Update branch info
                if (branchShop) {
                    $('#customerModalBranch').text(`Chi nhánh tạo: ${branchShop.name}`);
                } else {
                    // Get current branch from tab if no branch shop linked
                    const currentBranchText = getCurrentBranchText();
                    $('#customerModalBranch').text(currentBranchText ? `Chi nhánh hiện tại: ${currentBranchText}` : '');
                }

                // Update customer info in modal (if modal has form fields)
                updateCustomerModalFields(customer);

                // Update statistics (if modal has stats section)
                updateCustomerModalStats(statistics);

                // Set customer ID for form
                $('#customerEditForm').data('customer-id', customer.id);

                // Setup tab handlers for customer modal
                setupCustomerModalTabs(customer.id);

                console.log('Customer info loaded successfully:', response.data);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading customer info:', error);
            $('#customerModalName').text('Lỗi tải dữ liệu');
            $('#customerModalCode').text('');
            $('#customerModalBranch').text('');

            // Show error message
            if (xhr.responseJSON && xhr.responseJSON.message) {
                alert('Lỗi: ' + xhr.responseJSON.message);
            } else {
                alert('Có lỗi xảy ra khi tải thông tin khách hàng');
            }
        }
    });
}

/**
 * Get current branch text from active tab
 */
function getCurrentBranchText() {
    if (activeTabId) {
        const branchToggle = $(`#${activeTabId}_branchShopToggle`);
        return branchToggle.length ? branchToggle.text().trim() : '';
    }
    return '';
}

/**
 * Update customer modal fields
 */
function updateCustomerModalFields(customer) {
    // Update form inputs using the correct IDs from modal
    $('#customerModalCustomerCode').val(customer.customer_code || '');
    $('#customerModalFullName').val(customer.name || '');
    $('#customerModalPhone').val(customer.phone || '');
    $('#customerModalEmail').val(customer.email || '');
    $('#customerModalAddress').val(customer.address || '');
    $('#customerModalArea').val(customer.area || '');
    $('#customerModalType').val(customer.customer_type || 'individual');
    $('#customerModalGroup').val(customer.customer_group || '');
    $('#customerModalTaxCode').val(customer.tax_code || '');
    $('#customerModalFacebook').val(customer.facebook || '');
    $('#customerModalBirthday').val(customer.birthday || '');
    $('#customerModalNotes').val(customer.notes || '');
}

/**
 * Update customer modal statistics
 */
function updateCustomerModalStats(statistics) {
    if (statistics) {
        $('#customerDebtAmount').text(formatCurrency(statistics.total_debt || 0));
        $('#customerPointCount').text(statistics.current_points || 0);
        $('#customerTotalSpent').text(statistics.total_points || 0);
        $('#customerPurchaseCount').text(statistics.total_orders || 0);
        $('#customerNetSales').text(formatCurrency(statistics.total_sales || 0));
    }
}

/**
 * Setup customer modal tabs
 */
function setupCustomerModalTabs(customerId) {
    // Store customer ID for tab handlers
    $('#customerModalName').data('customer-id', customerId);

    // Remove any existing handlers
    $('#customerInfoModal .nav-link').off('click.customerTabs');

    // Setup tab click handlers using Bootstrap's shown.bs.tab event
    $('#customerInfoModal .nav-link').on('shown.bs.tab.customerTabs', function(e) {
        const tabId = $(this).attr('data-bs-target');

        // Handle different tabs
        if (tabId === '#customer-history') {
            loadCustomerOrderHistoryNew(customerId);
        } else if (tabId === '#customer-points') {
            loadCustomerPointHistoryNew(customerId);
        }
        // Other tabs are handled by existing code
    });
}

/**
 * Load customer order history (new implementation)
 */
function loadCustomerOrderHistoryNew(customerId, page = 1) {
    const tableBody = $('#customerOrderHistoryTable');

    // Show loading state
    tableBody.html(`
        <tr>
            <td colspan="5" class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Đang tải...</span>
                </div>
                <div class="mt-2">Đang tải lịch sử đơn hàng...</div>
            </td>
        </tr>
    `);

    // Load data from API
    $.ajax({
        url: `/admin/customers/${customerId}/order-history`,
        method: 'GET',
        data: { page: page },
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json'
        },
        success: function(response) {
            if (response.success) {
                renderOrderHistoryTable(response.data, customerId);
            } else {
                tableBody.html(`
                    <tr>
                        <td colspan="5" class="text-center">
                            <div class="alert alert-warning mb-0">
                                <i class="fas fa-exclamation-triangle"></i>
                                ${response.message || 'Không thể tải lịch sử đơn hàng'}
                            </div>
                        </td>
                    </tr>
                `);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading order history:', error);
            tableBody.html(`
                <tr>
                    <td colspan="5" class="text-center">
                        <div class="alert alert-danger mb-0">
                            <i class="fas fa-times-circle"></i>
                            Có lỗi xảy ra khi tải lịch sử đơn hàng
                        </div>
                    </td>
                </tr>
            `);
        }
    });
}

/**
 * Render order history table
 */
function renderOrderHistoryTable(data, customerId) {
    const tableBody = $('#customerOrderHistoryTable');
    const items = data.items || [];
    const pagination = data.pagination || {};

    if (items.length === 0) {
        tableBody.html(`
            <tr>
                <td colspan="5" class="text-center py-4">
                    <i class="fas fa-shopping-cart fa-2x text-muted mb-2"></i>
                    <div class="text-muted">Chưa có đơn hàng nào</div>
                    <small class="text-muted">Khách hàng chưa thực hiện đơn hàng nào.</small>
                </td>
            </tr>
        `);
        return;
    }

    let html = '';

    items.forEach(item => {
        const typeIcon = item.type === 'invoice' ? 'fas fa-file-invoice' : 'fas fa-undo';
        const typeClass = item.type === 'invoice' ? 'text-primary' : 'text-warning';
        const amountClass = item.type === 'return_order' ? 'text-warning' : 'text-success';
        const statusBadgeClass = getStatusBadgeClass(item.status);

        html += `
            <tr>
                <td>
                    <div class="d-flex align-items-center">
                        <i class="${typeIcon} ${typeClass} me-2"></i>
                        <strong>${item.number}</strong>
                    </div>
                </td>
                <td>
                    <div>${item.formatted_date}</div>
                </td>
                <td>
                    <div class="text-muted">N/A</div>
                </td>
                <td>
                    <div class="fw-bold ${amountClass}">
                        ${item.type === 'return_order' ? '-' : ''}${item.formatted_amount}
                    </div>
                </td>
                <td>
                    <span class="badge ${statusBadgeClass}">${item.status_text}</span>
                </td>
            </tr>
        `;
    });

    // Add pagination row if needed
    if (pagination.last_page > 1) {
        html += renderOrderHistoryPaginationRow(pagination, customerId);
    }

    tableBody.html(html);
}

/**
 * Get status badge class
 */
function getStatusBadgeClass(status) {
    const statusClasses = {
        'draft': 'bg-secondary',
        'pending': 'bg-warning',
        'processing': 'bg-info',
        'paid': 'bg-success',
        'completed': 'bg-success',
        'cancelled': 'bg-danger',
        'refunded': 'bg-warning'
    };

    return statusClasses[status] || 'bg-secondary';
}

/**
 * Render order history pagination row
 */
function renderOrderHistoryPaginationRow(pagination, customerId) {
    if (pagination.last_page <= 1) return '';

    let html = `
        <tr>
            <td colspan="5" class="text-center">
                <nav>
                    <ul class="pagination pagination-sm justify-content-center mb-0">
    `;

    // Previous button
    if (pagination.current_page > 1) {
        html += `
            <li class="page-item">
                <a class="page-link" href="#" onclick="loadCustomerOrderHistoryNew(${customerId}, ${pagination.current_page - 1})">
                    <i class="fas fa-chevron-left"></i>
                </a>
            </li>
        `;
    }

    // Page numbers
    const startPage = Math.max(1, pagination.current_page - 2);
    const endPage = Math.min(pagination.last_page, pagination.current_page + 2);

    for (let i = startPage; i <= endPage; i++) {
        const activeClass = i === pagination.current_page ? 'active' : '';
        html += `
            <li class="page-item ${activeClass}">
                <a class="page-link" href="#" onclick="loadCustomerOrderHistoryNew(${customerId}, ${i})">${i}</a>
            </li>
        `;
    }

    // Next button
    if (pagination.current_page < pagination.last_page) {
        html += `
            <li class="page-item">
                <a class="page-link" href="#" onclick="loadCustomerOrderHistoryNew(${customerId}, ${pagination.current_page + 1})">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>
        `;
    }

    html += `
                    </ul>
                </nav>
            </td>
        </tr>
    `;

    return html;
}

/**
 * Load customer point history (new implementation)
 */
function loadCustomerPointHistoryNew(customerId, page = 1) {
    const tableBody = $('#customerPointsTable');

    // Show loading state
    tableBody.html(`
        <tr>
            <td colspan="5" class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Đang tải...</span>
                </div>
                <div class="mt-2">Đang tải lịch sử điểm...</div>
            </td>
        </tr>
    `);

    // Load data from API
    $.ajax({
        url: `/admin/customers/${customerId}/point-history?page=${page}`,
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json'
        },
        success: function(response) {
            if (response.success) {
                renderCustomerPointHistory(response.data, customerId);
            } else {
                tableBody.html(`
                    <tr>
                        <td colspan="5" class="text-center py-4">
                            <i class="fas fa-exclamation-triangle fa-2x text-warning mb-2"></i>
                            <div class="text-muted">Có lỗi xảy ra khi tải lịch sử điểm</div>
                            <small class="text-muted">${response.message || 'Vui lòng thử lại sau'}</small>
                        </td>
                    </tr>
                `);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading point history:', error);
            tableBody.html(`
                <tr>
                    <td colspan="5" class="text-center py-4">
                        <i class="fas fa-exclamation-triangle fa-2x text-danger mb-2"></i>
                        <div class="text-muted">Có lỗi xảy ra khi tải lịch sử điểm</div>
                        <small class="text-muted">Vui lòng thử lại sau</small>
                    </td>
                </tr>
            `);
        }
    });
}

/**
 * Render customer point history
 */
function renderCustomerPointHistory(data, customerId) {
    const tableBody = $('#customerPointsTable');
    const { items, pagination } = data;

    if (!items || items.length === 0) {
        tableBody.html(`
            <tr>
                <td colspan="5" class="text-center py-4">
                    <i class="fas fa-star fa-2x text-muted mb-2"></i>
                    <div class="text-muted">Chưa có lịch sử điểm nào</div>
                    <small class="text-muted">Khách hàng chưa thực hiện giao dịch điểm nào.</small>
                </td>
            </tr>
        `);
        return;
    }

    let html = '';

    // Render point transactions
    items.forEach(transaction => {
        const pointsClass = transaction.points > 0 ? 'text-success' : 'text-danger';
        const typeIcon = getPointTransactionIcon(transaction.type);

        html += `
            <tr>
                <td>
                    <i class="${typeIcon} me-2"></i>
                    ${transaction.formatted_date}
                </td>
                <td>
                    <span class="badge bg-light text-dark">${transaction.type_text}</span>
                </td>
                <td class="text-end ${pointsClass}">
                    <strong>${transaction.points_display}</strong>
                </td>
                <td>
                    <div class="text-truncate" style="max-width: 200px;" title="${transaction.notes || 'N/A'}">
                        ${transaction.notes || 'N/A'}
                    </div>
                </td>
                <td class="text-end">
                    <strong>${transaction.balance_after}</strong>
                </td>
            </tr>
        `;
    });

    // Add pagination if needed
    if (pagination && pagination.last_page > 1) {
        html += renderPointHistoryPaginationRow(pagination, customerId);
    }

    tableBody.html(html);
}

/**
 * Get icon for point transaction type
 */
function getPointTransactionIcon(type) {
    const iconMap = {
        'purchase': 'fas fa-shopping-cart text-success',
        'return': 'fas fa-undo text-warning',
        'adjustment': 'fas fa-edit text-info',
        'redeem': 'fas fa-gift text-danger',
        'bonus': 'fas fa-star text-warning'
    };

    return iconMap[type] || 'fas fa-circle text-muted';
}

/**
 * Render point history pagination row
 */
function renderPointHistoryPaginationRow(pagination, customerId) {
    if (pagination.last_page <= 1) return '';

    let html = `
        <tr>
            <td colspan="5" class="text-center">
                <nav>
                    <ul class="pagination pagination-sm justify-content-center mb-0">
    `;

    // Previous button
    if (pagination.current_page > 1) {
        html += `
            <li class="page-item">
                <a class="page-link" href="#" onclick="loadCustomerPointHistoryNew(${customerId}, ${pagination.current_page - 1})">
                    <i class="fas fa-chevron-left"></i>
                </a>
            </li>
        `;
    }

    // Page numbers
    const startPage = Math.max(1, pagination.current_page - 2);
    const endPage = Math.min(pagination.last_page, pagination.current_page + 2);

    for (let i = startPage; i <= endPage; i++) {
        const activeClass = i === pagination.current_page ? 'active' : '';
        html += `
            <li class="page-item ${activeClass}">
                <a class="page-link" href="#" onclick="loadCustomerPointHistoryNew(${customerId}, ${i})">${i}</a>
            </li>
        `;
    }

    // Next button
    if (pagination.current_page < pagination.last_page) {
        html += `
            <li class="page-item">
                <a class="page-link" href="#" onclick="loadCustomerPointHistoryNew(${customerId}, ${pagination.current_page + 1})">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>
        `;
    }

    html += `
                    </ul>
                </nav>
            </td>
        </tr>
    `;

    return html;
}

/**
 * Save customer changes
 */
function saveCustomerChanges() {
    const customerId = $('#customerEditForm').data('customer-id');
    if (!customerId) {
        alert('Không tìm thấy ID khách hàng');
        return;
    }

    // Validate required fields
    const name = $('#customerModalFullName').val().trim();
    const phone = $('#customerModalPhone').val().trim();

    if (!name) {
        Swal.fire({
            icon: 'warning',
            title: 'Thiếu thông tin',
            text: 'Vui lòng nhập tên khách hàng',
            confirmButtonText: 'OK',
            confirmButtonColor: '#ffc700'
        }).then(() => {
            $('#customerModalFullName').focus();
        });
        return;
    }

    if (!phone) {
        Swal.fire({
            icon: 'warning',
            title: 'Thiếu thông tin',
            text: 'Vui lòng nhập số điện thoại',
            confirmButtonText: 'OK',
            confirmButtonColor: '#ffc700'
        }).then(() => {
            $('#customerModalPhone').focus();
        });
        return;
    }

    // Collect form data
    const formData = {
        name: name,
        phone: phone,
        email: $('#customerModalEmail').val().trim(),
        address: $('#customerModalAddress').val().trim(),
        area: $('#customerModalArea').val().trim(),
        customer_type: $('#customerModalType').val(),
        customer_group: $('#customerModalGroup').val(),
        tax_code: $('#customerModalTaxCode').val().trim(),
        facebook: $('#customerModalFacebook').val().trim(),
        birthday: $('#customerModalBirthday').val(),
        notes: $('#customerModalNotes').val().trim()
    };

    // Show loading state
    const saveBtn = $('#saveCustomerBtn');
    const originalText = saveBtn.html();
    saveBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Đang lưu...');

    // Send AJAX request
    $.ajax({
        url: `/admin/customers/${customerId}`,
        method: 'PUT',
        data: formData,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Thành công!',
                    text: 'Cập nhật thông tin khách hàng thành công!',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#009ef7'
                }).then(() => {
                    // Update customer display in Quick Order
                    updateCustomerDisplayAfterSave(customerId, formData);

                    // Close modal
                    $('#customerInfoModal').modal('hide');
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi',
                    text: response.message || 'Không thể cập nhật thông tin khách hàng',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#f1416c'
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('Error saving customer:', error);
            let errorMessage = 'Có lỗi xảy ra khi lưu thông tin khách hàng';

            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                const errors = Object.values(xhr.responseJSON.errors).flat();
                errorMessage = errors.join('\n');
            }

            Swal.fire({
                icon: 'error',
                title: 'Lỗi',
                text: errorMessage,
                confirmButtonText: 'OK',
                confirmButtonColor: '#f1416c'
            });
        },
        complete: function() {
            // Restore button state
            saveBtn.prop('disabled', false).html(originalText);
        }
    });
}

/**
 * Update customer display in Quick Order after successful save
 */
function updateCustomerDisplayAfterSave(customerId, formData) {
    // Update customer display text in active tab
    if (activeTabId) {
        const customerDisplay = $(`#${activeTabId}_selectedCustomerText`);
        if (customerDisplay.length && customerDisplay.data('customer-id') == customerId) {
            customerDisplay.text(`${formData.name} - ${formData.phone}`);
        }
    }

    // Update window.customers array if it exists
    if (window.customers) {
        const customerIndex = window.customers.findIndex(c => c.id == customerId);
        if (customerIndex !== -1) {
            Object.assign(window.customers[customerIndex], formData);
        }
    }
}

/**
 * Open invoice selection modal for return tab
 */
function openInvoiceSelectionModal(tabId) {
    console.log('Opening invoice selection modal for tab:', tabId);

    // Store current tab ID for later use
    $('#invoiceSelectionModal').data('tabId', tabId);

    // Clear previous search results
    $('#invoiceListTableBody').empty();
    $('#invoiceSearchInput').val('');
    $('#invoicePagination').hide();

    // Reset pagination state
    currentInvoicePage = 1;
    currentInvoiceStart = 0;
    currentInvoiceLength = 7;

    // Load recent invoices
    loadInvoicesForSelection();

    // Show modal
    $('#invoiceSelectionModal').modal('show');
}

/**
 * Load invoices for selection
 */
function loadInvoicesForSelection() {
    console.log('Loading invoices for selection...');

    // Show loading state
    $('#invoiceListTableBody').html(`
        <tr>
            <td colspan="6" class="text-center">
                <div class="spinner-border spinner-border-sm me-2"></div>
                Đang tải danh sách hóa đơn...
            </td>
        </tr>
    `);

    // AJAX request to load invoices using existing endpoint
    const searchValue = $('#invoiceSearchInput').val() || '';
    const timeFilter = $('#invoiceTimeFilter').val() || 'this_month';
    const customerFilter = $('#invoiceCustomerFilter').val() || '';

    // Prepare data object with proper structure
    const requestData = {
        'search_term': searchValue,
        'time_filter': timeFilter,
        'status': 'completed',
        'customer_filter': customerFilter,
        'status_filter': 'paid', // Only paid invoices for return
        'per_page': currentInvoiceLength,
        'page': currentInvoicePage,
        'draw': currentInvoicePage,
        'start': currentInvoiceStart,
        'length': currentInvoiceLength
    };

    // Add search object if there's a search value
    if (searchValue) {
        requestData['search'] = { 'value': searchValue };
    }

    $.ajax({
        url: '/admin/invoices/ajax',
        method: 'GET',
        data: requestData,
        success: function(response) {
            console.log('Invoice search response:', response);

            // Handle different response formats
            let invoicesData = [];
            let paginationInfo = null;

            // Check if response has DataTables format with pagination info at root level
            if (response.data && Array.isArray(response.data) &&
                (response.recordsTotal !== undefined || response.recordsFiltered !== undefined)) {
                // DataTables format: {data: [...], recordsTotal: 267, recordsFiltered: 267, draw: 1}
                invoicesData = response.data;
                paginationInfo = {
                    recordsTotal: response.recordsTotal || 0,
                    recordsFiltered: response.recordsFiltered || 0,
                    draw: response.draw || 1
                };
                console.log('Using DataTables format with pagination:', paginationInfo);
            } else if (response.data && response.data.data && Array.isArray(response.data.data)) {
                // Nested DataTables format: {data: {data: [...], recordsTotal: 267}}
                invoicesData = response.data.data;
                paginationInfo = {
                    recordsTotal: response.data.recordsTotal || 0,
                    recordsFiltered: response.data.recordsFiltered || 0,
                    draw: response.data.draw || 1
                };
                console.log('Using nested DataTables format with pagination:', paginationInfo);
            } else if (response.data && Array.isArray(response.data)) {
                // Simple array format without pagination
                invoicesData = response.data;
                console.log('Using simple array format without pagination');
            } else if (response.success && response.data && Array.isArray(response.data)) {
                // Success wrapper format
                invoicesData = response.data;
                console.log('Using success wrapper format');
            } else {
                console.warn('Unexpected response format:', response);
                invoicesData = [];
            }

            if (invoicesData.length > 0) {
                displayInvoicesForSelection(invoicesData, paginationInfo);
            } else {
                $('#invoiceListTableBody').html(`
                    <tr>
                        <td colspan="6" class="text-center text-muted">
                            <i class="fas fa-inbox fa-2x mb-2"></i>
                            <br>
                            Không tìm thấy hóa đơn nào
                        </td>
                    </tr>
                `);
                // Hide pagination if no data
                $('#invoicePagination').hide();
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading invoices:', error);
            $('#invoiceListTableBody').html(`
                <tr>
                    <td colspan="6" class="text-center text-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Lỗi khi tải danh sách hóa đơn
                    </td>
                </tr>
            `);
        }
    });
}

/**
 * Display invoices in selection table
 */
function displayInvoicesForSelection(invoices, paginationInfo = null) {
    let html = '';
 
    if (invoices.length === 0) {
        html = `
            <tr>
                <td colspan="6" class="text-center text-muted">
                    Không tìm thấy hóa đơn nào
                </td>
            </tr>
        `;
    } else {
        invoices.forEach(invoice => {
            html += `
                <tr>
                    <td>
                        <strong>${invoice.invoice_number}</strong>
                        <br>
                        <small class="text-muted">${formatDateTime(invoice.created_at)}</small>
                    </td>
                    <td>${formatDateTime(invoice.created_at)}</td>
                    <td>${invoice.seller || invoice.creator || 'N/A'}</td>
                    <td>
                        <strong>${invoice.customer_display || 'Khách lẻ'}</strong>
                        ${invoice.phone ? `<br><small>${invoice.phone}</small>` : ''}
                    </td>
                    <td>
                        <strong class="text-primary">${formatCurrency(invoice.total_amount)}</strong>
                        <br>
                        <small class="text-muted">Hóa đơn</small>
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-primary"
                                onclick="selectInvoiceForReturn(${invoice.id}, '${invoice.invoice_number}')">
                            <i class="fas fa-check me-1"></i>
                            Chọn
                        </button>
                    </td>
                </tr>
            `;
        });
    }

    $('#invoiceListTableBody').html(html);

    // Handle pagination if available
    console.log('displayInvoicesForSelection: Checking pagination:', {
        paginationInfo,
        hasInfo: !!paginationInfo,
        recordsTotal: paginationInfo?.recordsTotal,
        shouldShow: paginationInfo && paginationInfo.recordsTotal > 0
    });

    if (paginationInfo && paginationInfo.recordsTotal > 0) {
        console.log('displayInvoicesForSelection: Showing pagination');
        displayInvoicePagination(paginationInfo);
    } else {
        console.log('displayInvoicesForSelection: Hiding pagination');
        $('#invoicePagination').hide();
    }
}

/**
 * Display pagination for invoice selection
 */
function displayInvoicePagination(paginationInfo) {
    console.log('displayInvoicePagination called with:', paginationInfo);

    const totalRecords = paginationInfo.recordsTotal || 0;
    const filteredRecords = paginationInfo.recordsFiltered || 0;
    const currentPage = Math.floor((currentInvoiceStart || 0) / (currentInvoiceLength || 20)) + 1;
    const totalPages = Math.ceil(filteredRecords / (currentInvoiceLength || 20));

    console.log('displayInvoicePagination: Calculated values:', {
        totalRecords,
        filteredRecords,
        currentPage,
        totalPages,
        currentInvoiceStart,
        currentInvoiceLength
    });

    if (totalPages <= 1) {
        console.log('displayInvoicePagination: Only 1 page or less, hiding pagination');
        $('#invoicePagination').hide();
        return;
    }

    console.log('displayInvoicePagination: Generating pagination HTML');

    let paginationHtml = `
        <div class="d-flex justify-content-between align-items-center">
            <div class="pagination-info">
                <small class="text-muted">
                    Hiển thị ${Math.min((currentPage - 1) * (currentInvoiceLength || 20) + 1, filteredRecords)} -
                    ${Math.min(currentPage * (currentInvoiceLength || 20), filteredRecords)}
                    trong tổng số ${filteredRecords} hóa đơn
                </small>
            </div>
            <nav aria-label="Invoice pagination">
                <ul class="pagination pagination-sm mb-0">
    `;

    // Previous button
    if (currentPage > 1) {
        paginationHtml += `
            <li class="page-item">
                <a class="page-link" href="#" onclick="loadInvoicePage(${currentPage - 1}); return false;">
                    <i class="fas fa-chevron-left"></i>
                </a>
            </li>
        `;
    } else {
        paginationHtml += `
            <li class="page-item disabled">
                <span class="page-link"><i class="fas fa-chevron-left"></i></span>
            </li>
        `;
    }

    // Page numbers
    const startPage = Math.max(1, currentPage - 2);
    const endPage = Math.min(totalPages, currentPage + 2);

    if (startPage > 1) {
        paginationHtml += `
            <li class="page-item">
                <a class="page-link" href="#" onclick="loadInvoicePage(1); return false;">1</a>
            </li>
        `;
        if (startPage > 2) {
            paginationHtml += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
    }

    for (let i = startPage; i <= endPage; i++) {
        if (i === currentPage) {
            paginationHtml += `
                <li class="page-item active">
                    <span class="page-link">${i}</span>
                </li>
            `;
        } else {
            paginationHtml += `
                <li class="page-item">
                    <a class="page-link" href="#" onclick="loadInvoicePage(${i}); return false;">${i}</a>
                </li>
            `;
        }
    }

    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            paginationHtml += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
        paginationHtml += `
            <li class="page-item">
                <a class="page-link" href="#" onclick="loadInvoicePage(${totalPages}); return false;">${totalPages}</a>
            </li>
        `;
    }

    // Next button
    if (currentPage < totalPages) {
        paginationHtml += `
            <li class="page-item">
                <a class="page-link" href="#" onclick="loadInvoicePage(${currentPage + 1}); return false;">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>
        `;
    } else {
        paginationHtml += `
            <li class="page-item disabled">
                <span class="page-link"><i class="fas fa-chevron-right"></i></span>
            </li>
        `;
    }

    paginationHtml += `
                </ul>
            </nav>
        </div>
    `;

    console.log('displayInvoicePagination: Setting HTML and showing pagination');
    $('#invoicePagination').html(paginationHtml).show();
    console.log('displayInvoicePagination: Pagination should now be visible');
}

/**
 * Load specific page of invoices
 */
function loadInvoicePage(page) {
    currentInvoicePage = page;
    currentInvoiceStart = (page - 1) * (currentInvoiceLength || 20);
    loadInvoicesForSelection();
}

/**
 * Select invoice for return
 */
function selectInvoiceForReturn(invoiceId, invoiceNumber) {
    console.log('Selecting invoice for return:', invoiceId, invoiceNumber);

    const tabId = $('#invoiceSelectionModal').data('tabId');
    if (!tabId) {
        console.error('No tab ID found');
        return;
    }

    // Get customer info from the invoice row in modal
    const invoiceRow = $(`button[onclick="selectInvoiceForReturn(${invoiceId}, '${invoiceNumber}')"]`).closest('tr');
    const customerCell = invoiceRow.find('td:nth-child(4)'); // Customer column
    const customerName = customerCell.find('strong').text() || 'Khách lẻ';
    const customerPhone = customerCell.text().replace(customerName, '').trim();

    console.log('Customer info from modal:', {customerName, customerPhone});

    // Load invoice items with customer info
    loadInvoiceItemsForReturn(tabId, invoiceId, invoiceNumber, customerName, customerPhone);

    // Close modal
    $('#invoiceSelectionModal').modal('hide');
}

/**
 * Load invoice data for return from URL parameter
 */
function loadInvoiceDataForReturn(tabId, invoiceId) {
    console.log('🔄 loadInvoiceDataForReturn called with:', {tabId, invoiceId});

    // First get invoice basic info
    $.ajax({
        url: `/admin/invoices/${invoiceId}/detail-panel`,
        method: 'GET',
        success: function(response) {
            console.log('✅ Invoice basic info loaded:', response);

            // Extract invoice info from response
            const $response = $(response);
            const invoiceNumber = $response.find('.invoice-number').text().trim() || `INV-${invoiceId}`;
            const customerName = $response.find('.customer-name').text().trim() || 'Khách lẻ';
            const customerPhone = $response.find('.customer-phone').text().trim() || '';

            console.log('📋 Extracted invoice info:', {invoiceNumber, customerName, customerPhone});

            // Now load invoice items
            loadInvoiceItemsForReturn(tabId, invoiceId, invoiceNumber, customerName, customerPhone);
        },
        error: function(xhr, status, error) {
            console.error('❌ Error loading invoice basic info:', {xhr, status, error});

            // Fallback: try to load items directly with minimal info
            const fallbackInvoiceNumber = `INV-${invoiceId}`;
            console.log('🔄 Fallback: loading items with minimal info');
            loadInvoiceItemsForReturn(tabId, invoiceId, fallbackInvoiceNumber, 'Khách lẻ', '');
        }
    });
}

/**
 * Load invoice items for return
 */
function loadInvoiceItemsForReturn(tabId, invoiceId, invoiceNumber, customerName = 'Khách lẻ', customerPhone = '') {
    console.log('🚀 NEW FIXED loadInvoiceItemsForReturn called with:', {tabId, invoiceId, invoiceNumber, customerName, customerPhone});

    // Use the CORRECT endpoint
    const url = `/admin/quick-order/invoice-items/${invoiceId}`;
    console.log('🎯 Using CORRECT endpoint:', url);

    $.ajax({
        url: url,
        method: 'GET',
        success: function(response) {
            console.log('✅ SUCCESS: Invoice items loaded successfully:', response);

            if (response.success && response.data) {
                console.log('📞 About to call updateReturnTabWithInvoice with:', {
                    tabId: tabId,
                    invoiceData: {
                        id: invoiceId,
                        invoice_number: invoiceNumber,
                        customer_name: customerName,
                        customer_phone: customerPhone,
                        items: response.data
                    }
                });

                // Update the return tab with invoice data
                try {
                    updateReturnTabWithInvoice(tabId, {
                        id: invoiceId,
                        invoice_number: invoiceNumber,
                        customer_name: customerName,
                        customer_phone: customerPhone,
                        employee_name: response.seller_name || response.creator_name || 'N/A',
                        items: response.data
                    });
                    console.log('✅ updateReturnTabWithInvoice call completed successfully');
                } catch (error) {
                    console.error('❌ Error calling updateReturnTabWithInvoice:', error);
                }

                // Close modal
                $('#invoiceSelectionModal').modal('hide');

                toastr.success(`Đã chọn hóa đơn ${invoiceNumber} thành công`);
            } else {
                console.error('❌ Invalid response format:', response);
                toastr.error('Dữ liệu hóa đơn không hợp lệ');
            }
        },
        error: function(xhr, status, error) {
            console.error('❌ AJAX Error:', {xhr, status, error});
            console.error('❌ Response text:', xhr.responseText);
            toastr.error('Lỗi khi tải thông tin hóa đơn: ' + (xhr.responseJSON?.message || error));
        }
    });
}

/**
 * Update return tab with invoice data
 */
function updateReturnTabWithInvoice(tabId, invoiceData) {
    console.log('🔄 updateReturnTabWithInvoice called:', { tabId, invoiceData });

    const tab = orderTabs.find(t => t.id === tabId);
    if (!tab) {
        console.error('❌ Tab not found:', tabId);
        return;
    }

    console.log('✅ Tab found, updating invoice data...');

    // Update tab data with proper validation
    tab.invoice_id = invoiceData.invoice_id || invoiceData.id;
    tab.invoice_number = invoiceData.invoice_number;
    tab.customer_name = invoiceData.customer_name;
    tab.customer_phone = invoiceData.customer_phone;
    tab.employee_name = invoiceData.employee_name;

    console.log('📝 Updated tab data:', {
        invoice_id: tab.invoice_id,
        invoice_number: tab.invoice_number,
        customer_name: tab.customer_name,
        customer_phone: tab.customer_phone
    });

    // Clear existing items and add invoice items with proper validation
    tab.items = [];

    if (invoiceData.items && Array.isArray(invoiceData.items)) {
        console.log(`Processing ${invoiceData.items.length} items from invoice`);

        invoiceData.items.forEach((item, index) => {
            if (!item) {
                console.warn(`Item at index ${index} is null/undefined`);
                return;
            }

            const processedItem = {
                id: item.product_id || item.id,
                name: item.product_name || item.name || 'Sản phẩm không xác định',
                sku: item.product_sku || item.sku || '',
                price: parseFloat(item.price) || parseFloat(item.unit_price) || 0,
                quantity: 0, // Start with 0 for return quantity
                max_quantity: parseInt(item.returnable_quantity) || 0, // Maximum returnable quantity (updated)
                original_quantity: parseInt(item.quantity) || 0, // Store original quantity
                returned_quantity: parseInt(item.returned_quantity) || 0, // Already returned quantity
                returnable_quantity: parseInt(item.returnable_quantity) || 0, // Available for return
                stock_quantity: parseInt(item.stock_quantity) || 0,
                image: item.product_image || item.image || null,
                invoice_item_id: item.invoice_item_id || null // Add invoice_item_id
            };

            tab.items.push(processedItem);
            console.log(`Added item: ${processedItem.name} (max: ${processedItem.max_quantity})`);
        });

        console.log(`Successfully processed ${tab.items.length} items for return`);
    } else {
        console.warn('No valid items array found in invoice data');
        toastr.warning('Hóa đơn không có sản phẩm để trả hàng');
    }

    // Update UI
    const tabContent = $(`#${tabId}_content`);

    // Store invoice ID in hidden field
    tabContent.find(`#${tabId}_selectedInvoiceId`).val(invoiceData.invoice_id);

    // Update return invoice info in header
    tabContent.find(`#${tabId}_selectedInvoiceInfo`).text(`${invoiceData.invoice_number} - ${invoiceData.customer_name}`);

    // Show and update return-order-header
    const returnOrderHeader = tabContent.find(`#${tabId}_returnOrderHeader`);
    console.log('Updating return order header:', {
        header_found: returnOrderHeader.length,
        customer_name: invoiceData.customer_name,
        customer_phone: invoiceData.customer_phone,
        invoice_number: invoiceData.invoice_number
    });

    returnOrderHeader.show();

    // Update customer name
    const customerNameElement = tabContent.find(`#${tabId}_returnCustomerName`);
    customerNameElement.text(invoiceData.customer_name || 'Khách lẻ');
    console.log('Updated customer name element:', customerNameElement.length, customerNameElement.text());

    // Update customer phone/points
    const customerPointsElement = tabContent.find(`#${tabId}_returnCustomerPoints`);
    customerPointsElement.text(`SĐT: ${invoiceData.customer_phone || 'N/A'}`);
    console.log('Updated customer points element:', customerPointsElement.length, customerPointsElement.text());

    // Update order title with employee name and clickable invoice number
    const orderTitleElement = tabContent.find(`#${tabId}_returnOrderTitle`);
    const employeeName = invoiceData.employee_name || 'N/A';
    orderTitleElement.html(`
        Trả hàng /
        <a href="/admin/invoices?code=${invoiceData.invoice_number}"
           target="_blank"
           style="color: #009ef7; text-decoration: none;"
           onmouseover="this.style.textDecoration='underline'"
           onmouseout="this.style.textDecoration='none'">
           ${invoiceData.invoice_number}
        </a>
        - ${employeeName}
    `);
    console.log('Updated order title element:', orderTitleElement.length, orderTitleElement.html());

    // Update tab title
    tab.name = `Trả hàng ${invoiceData.invoice_number}`;
    $(`#${tabId} .tab-title`).text(tab.name);

    // Update items display
    updateTabDisplay(tabId);

    // Auto-save drafts to persist invoice selection
    console.log('💾 Saving drafts to persist invoice selection...');
    saveDrafts();

    // Verify that invoice data was saved correctly
    const savedTab = orderTabs.find(t => t.id === tabId);
    if (savedTab && savedTab.invoice_id) {
        console.log('✅ Invoice data saved successfully:', {
            tabId: savedTab.id,
            invoice_id: savedTab.invoice_id,
            invoice_number: savedTab.invoice_number,
            customer_name: savedTab.customer_name
        });
    } else {
        console.error('❌ Failed to save invoice data for tab:', tabId);
    }

    console.log('✅ Return tab updated successfully');
}

/**
 * Create return order
 */
function createReturnOrder(tabId) {
    const tab = orderTabs.find(t => t.id === tabId);
    if (!tab) {
        toastr.error('Tab không tồn tại');
        return;
    }

    if (!tab.invoice_id) {
        toastr.error('Vui lòng chọn hóa đơn trước khi tạo phiếu trả hàng');
        return;
    }

    // Get items with quantity > 0
    const returnItems = tab.items.filter(item => item.quantity > 0);
    if (returnItems.length === 0) {
        toastr.error('Vui lòng chọn ít nhất một sản phẩm để trả hàng');
        return;
    }

    // Get exchange items with quantity > 0
    const exchangeItems = tab.exchangeItems ? tab.exchangeItems.filter(item => item.quantity > 0) : [];

    // Prepare return order data
    const returnOrderData = {
        invoice_id: tab.invoice_id,
        invoice_number: tab.invoice_number,
        customer_name: tab.customer_name,
        customer_phone: tab.customer_phone,
        employee_name: tab.employee_name,
        return_items: returnItems.map(item => ({
            product_id: item.id,
            product_name: item.name,
            product_sku: item.sku,
            price: item.price,
            quantity: item.quantity,
            original_quantity: item.max_quantity,
            invoice_item_id: item.invoice_item_id
        })),
        exchange_items: exchangeItems.map(item => ({
            product_id: item.id,
            product_name: item.name,
            product_sku: item.sku,
            price: item.price,
            quantity: item.quantity
        })),
        payment_method: 'cash', // Default payment method
        notes: tab.notes || '',
        return_subtotal: tab.returnSubtotal || 0,
        exchange_subtotal: tab.exchangeSubtotal || 0,
        refund_amount: tab.refundAmount || 0,
        _token: $('meta[name="csrf-token"]').attr('content')
    };

    console.log('Creating return order with data:', returnOrderData);

    // Show loading state
    const returnButton = $(`#${tabId}_returnButton`);
    const originalText = returnButton.text();
    returnButton.prop('disabled', true).text('Đang xử lý...');

    // AJAX request to create return order
    $.ajax({
        url: `/admin/returns/from-invoice/${returnOrderData.invoice_id}`,
        method: 'POST',
        data: returnOrderData,
        success: function(response) {
            if (response.success) {
                toastr.success(`Đã tạo phiếu trả hàng ${response.return_order_number || ''} thành công`);

                // Show SweetAlert2 modal instead of confirm()
                Swal.fire({
                    title: 'Tạo phiếu trả hàng thành công!',
                    text: `Phiếu trả hàng ${response.return_order_number || ''} đã được tạo thành công. Bạn có muốn đóng tab này không?`,
                    icon: 'success',
                    showCancelButton: true,
                    buttonsStyling: false,
                    confirmButtonText: 'Đóng tab',
                    cancelButtonText: 'Giữ tab',
                    customClass: {
                        confirmButton: 'btn fw-bold btn-primary',
                        cancelButton: 'btn fw-bold btn-active-light-primary'
                    },
                    allowOutsideClick: false,
                    allowEscapeKey: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        // User clicked "Đóng tab"
                        closeTab(tabId);
                    } else {
                        // User clicked "Giữ tab" or dismissed
                        // Clear items but keep invoice info
                        tab.items.forEach(item => item.quantity = 0);
                        updateTabDisplay(tabId);
                        updateOrderTotals();
                    }
                });
            } else {
                toastr.error(response.message || 'Có lỗi xảy ra khi tạo phiếu trả hàng');
            }
        },
        error: function(xhr, status, error) {
            console.error('Create return order error:', error);
            toastr.error('Có lỗi xảy ra khi tạo phiếu trả hàng');
        },
        complete: function() {
            // Restore button state
            returnButton.prop('disabled', false).text(originalText);
        }
    });
}

/**
 * Print return order
 */
function printReturnOrder(tabId) {
    const tab = orderTabs.find(t => t.id === tabId);
    if (!tab) {
        toastr.error('Tab không tồn tại');
        return;
    }

    if (!tab.invoice_id) {
        toastr.error('Vui lòng chọn hóa đơn trước khi in phiếu trả hàng');
        return;
    }

    // Get items with quantity > 0
    const returnItems = tab.items.filter(item => item.quantity > 0);
    if (returnItems.length === 0) {
        toastr.error('Vui lòng chọn ít nhất một sản phẩm để in phiếu trả hàng');
        return;
    }

    // For now, just show a preview or open print dialog
    toastr.info('Chức năng in phiếu trả hàng đang được phát triển');

    // TODO: Implement print functionality
    // This could open a new window with print-friendly format
    // or generate PDF for printing
}

/**
 * Clear invoice selection for return tab
 */
function clearInvoiceSelection(tabId) {
    console.log('Clearing invoice selection for tab:', tabId);

    const tab = orderTabs.find(t => t.id === tabId);
    if (!tab || tab.type !== 'return') {
        console.warn('Tab not found or not a return tab:', tabId);
        return;
    }

    const tabContent = $(`#${tabId}_content`);

    // Clear hidden field
    tabContent.find(`#${tabId}_selectedInvoiceId`).val('');

    // Clear tab data
    tab.invoice_id = null;
    tab.invoice_number = null;
    tab.customer_name = null;
    tab.customer_phone = null;
    tab.items = [];

    // Hide return-order-header
    tabContent.find(`#${tabId}_returnOrderHeader`).hide();

    // Reset tab title
    tab.name = `Trả hàng ${tab.id.split('_')[1]}`;
    $(`#${tabId} .tab-title`).text(tab.name);

    // Update display
    updateTabDisplay(tabId);

    console.log('Invoice selection cleared for tab:', tabId);
}

/**
 * Check if return tab has selected invoice
 */
function hasSelectedInvoice(tabId) {
    const selectedInvoiceId = $(`#${tabId}_selectedInvoiceId`).val();
    return selectedInvoiceId && selectedInvoiceId.trim() !== '';
}

/**
 * Search invoices
 */
function searchInvoices() {
    loadInvoicesForSelection();
}
