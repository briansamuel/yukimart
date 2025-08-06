/**
 * TabManager - Shared component for managing tabs in Quick Order system
 */
class TabManager {
    constructor(options = {}) {
        this.tabs = [];
        this.activeTabId = null;
        this.nextTabNumber = 1;
        this.options = {
            containerSelector: '.quick-order-tabs',
            contentSelector: '.quick-order-content',
            maxTabs: 10,
            defaultType: 'order',
            ...options
        };
        
        this.init();
    }

    /**
     * Initialize tab manager
     */
    init() {
        this.bindEvents();
        this.loadSavedTabs();
    }

    /**
     * Bind events
     */
    bindEvents() {
        // Add tab button
        $(document).on('click', '.add-tab-btn', (e) => {
            e.preventDefault();
            this.createTab();
        });

        // Tab click
        $(document).on('click', '.tab-item', (e) => {
            e.preventDefault();
            const tabId = $(e.currentTarget).data('tab-id');
            this.switchToTab(tabId);
        });

        // Close tab button
        $(document).on('click', '.close-tab-btn', (e) => {
            e.preventDefault();
            e.stopPropagation();
            const tabId = $(e.currentTarget).closest('.tab-item').data('tab-id');
            this.closeTab(tabId);
        });

        // Tab type dropdown
        $(document).on('click', '.tab-type-option', (e) => {
            e.preventDefault();
            const type = $(e.currentTarget).data('type');
            this.createTab(type);
        });
    }

    /**
     * Create new tab
     */
    createTab(type = null) {
        if (this.tabs.length >= this.options.maxTabs) {
            toastr.warning(`Tối đa ${this.options.maxTabs} tabs`);
            return null;
        }

        type = type || this.options.defaultType;
        const tabId = `tab_${this.nextTabNumber++}`;
        
        const tab = {
            id: tabId,
            name: this.getTabName(type),
            type: type,
            number: this.nextTabNumber - 1,
            items: [],
            customer_id: 0,
            branch_shop_id: window.defaultBranchShop?.id || null,
            sold_by: window.currentUserId || null,
            payment_method: 'cash',
            bank_account_id: null,
            channel: 'offline',
            notes: '',
            discount_amount: 0,
            other_charges_amount: 0,
            paid_amount: 0,
            created_at: new Date().toISOString()
        };

        this.tabs.push(tab);
        this.renderTab(tab);
        this.renderTabContent(tab);
        this.switchToTab(tabId);
        this.saveTabs();

        console.log(`Created ${type} tab:`, tab);
        return tab;
    }

    /**
     * Get tab name based on type
     */
    getTabName(type) {
        const names = {
            'order': 'Đơn hàng',
            'invoice': 'Hóa đơn',
            'return': 'Trả hàng'
        };
        return names[type] || 'Tab';
    }

    /**
     * Render tab in UI
     */
    renderTab(tab) {
        const tabHtml = `
            <div class="tab-item" data-tab-id="${tab.id}">
                <span class="tab-name">${tab.name} ${tab.number}</span>
                <span class="tab-count">(${tab.items.length})</span>
                <button class="close-tab-btn" type="button">
                    <i class="fa fa-times"></i>
                </button>
            </div>
        `;
        
        $(this.options.containerSelector).append(tabHtml);
    }

    /**
     * Render tab content
     */
    renderTabContent(tab) {
        const contentHtml = this.getTabContentTemplate(tab);
        $(this.options.contentSelector).append(contentHtml);
    }

    /**
     * Get tab content template based on type
     */
    getTabContentTemplate(tab) {
        // This would be implemented based on specific tab type
        // For now, return basic template
        return `
            <div id="${tab.id}_content" class="tab-content" style="display: none;">
                <div class="tab-content-${tab.type}">
                    <!-- Content will be populated by specific implementations -->
                </div>
            </div>
        `;
    }

    /**
     * Switch to tab
     */
    switchToTab(tabId) {
        if (!tabId || this.activeTabId === tabId) return;

        const tab = this.tabs.find(t => t.id === tabId);
        if (!tab) return;

        // Update active states
        $('.tab-item').removeClass('active');
        $(`.tab-item[data-tab-id="${tabId}"]`).addClass('active');

        $('.tab-content').hide();
        $(`#${tabId}_content`).show();

        this.activeTabId = tabId;
        console.log(`Switched to tab: ${tabId}`);

        // Trigger event
        $(document).trigger('tabSwitched', [tab]);
    }

    /**
     * Close tab
     */
    closeTab(tabId) {
        const tabIndex = this.tabs.findIndex(t => t.id === tabId);
        if (tabIndex === -1) return;

        const tab = this.tabs[tabIndex];
        
        // Check if tab has items
        if (tab.items.length > 0) {
            if (!confirm(`Tab "${tab.name} ${tab.number}" có ${tab.items.length} sản phẩm. Bạn có chắc muốn đóng?`)) {
                return;
            }
        }

        // Remove from arrays
        this.tabs.splice(tabIndex, 1);

        // Remove from DOM
        $(`.tab-item[data-tab-id="${tabId}"]`).remove();
        $(`#${tabId}_content`).remove();

        // Switch to another tab if this was active
        if (this.activeTabId === tabId) {
            if (this.tabs.length > 0) {
                this.switchToTab(this.tabs[0].id);
            } else {
                this.activeTabId = null;
            }
        }

        this.saveTabs();
        console.log(`Closed tab: ${tabId}`);
    }

    /**
     * Get active tab
     */
    getActiveTab() {
        return this.tabs.find(t => t.id === this.activeTabId);
    }

    /**
     * Update tab count
     */
    updateTabCount(tabId, count) {
        $(`.tab-item[data-tab-id="${tabId}"] .tab-count`).text(`(${count})`);
    }

    /**
     * Clear tab (remove all items)
     */
    clearTab(tabId) {
        const tab = this.tabs.find(t => t.id === tabId);
        if (tab) {
            tab.items = [];
            tab.discount_amount = 0;
            tab.other_charges_amount = 0;
            tab.paid_amount = 0;
            tab.notes = '';
            this.updateTabCount(tabId, 0);
            this.saveTabs();
        }
    }

    /**
     * Save tabs to localStorage
     */
    saveTabs() {
        try {
            const tabsData = this.tabs.map(tab => ({
                ...tab,
                // Don't save DOM references or functions
            }));
            localStorage.setItem('quickOrderTabs', JSON.stringify(tabsData));
        } catch (e) {
            console.warn('Failed to save tabs:', e);
        }
    }

    /**
     * Load saved tabs from localStorage
     */
    loadSavedTabs() {
        try {
            const saved = localStorage.getItem('quickOrderTabs');
            if (saved) {
                const tabsData = JSON.parse(saved);
                // Restore tabs logic would go here
                console.log('Loaded saved tabs:', tabsData);
            }
        } catch (e) {
            console.warn('Failed to load saved tabs:', e);
        }
    }

    /**
     * Get all tabs
     */
    getAllTabs() {
        return this.tabs;
    }

    /**
     * Find tab by ID
     */
    findTab(tabId) {
        return this.tabs.find(t => t.id === tabId);
    }
}
