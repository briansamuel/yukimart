/**
 * Quick Order Tabs Management
 * Handles multiple order tabs functionality
 */

class QuickOrderTabs {
    constructor() {
        this.tabs = new Map();
        this.activeTabId = null;
        this.tabCounter = 0;
        this.maxTabs = 10; // Maximum number of tabs allowed
        
        this.init();
    }

    init() {
        this.bindEvents();
        this.createInitialTab();
        this.loadTabsFromStorage();
    }

    bindEvents() {
        // Add new tab button
        document.getElementById('addNewOrderTabBtn').addEventListener('click', () => {
            this.createNewTab();
        });

        // Handle tab close with delegation
        document.addEventListener('click', (e) => {
            if (e.target.closest('.close-tab-btn')) {
                const tabId = e.target.closest('.close-tab-btn').dataset.tabId;
                this.closeTab(tabId);
            }
        });

        // Handle tab switching with delegation
        document.addEventListener('click', (e) => {
            if (e.target.closest('[data-bs-toggle="tab"]')) {
                const tabId = e.target.closest('[data-bs-toggle="tab"]').dataset.tabId;
                this.switchToTab(tabId);
            }
        });

        // Auto-save tabs every 30 seconds
        setInterval(() => {
            this.saveTabsToStorage();
        }, 30000);

        // Save tabs before page unload
        window.addEventListener('beforeunload', () => {
            this.saveTabsToStorage();
        });
    }

    createInitialTab() {
        this.createNewTab('Đơn hàng #1', true);
    }

    createNewTab(title = null, isInitial = false) {
        if (this.tabs.size >= this.maxTabs) {
            this.showNotification('Đã đạt giới hạn tối đa ' + this.maxTabs + ' tabs', 'warning');
            return null;
        }

        this.tabCounter++;
        const tabId = 'order-tab-' + this.tabCounter;
        const tabTitle = title || `Đơn hàng #${this.tabCounter}`;

        // Create tab navigation item
        const tabNav = this.createTabNavigation(tabId, tabTitle, isInitial);
        
        // Create tab content
        const tabContent = this.createTabContent(tabId, isInitial);

        // Add to tabs map
        this.tabs.set(tabId, {
            id: tabId,
            title: tabTitle,
            orderItems: [],
            customer_id: null,
            branch_shop_id: null,
            payment_method: 'cash',
            notes: '',
            created_at: new Date().toISOString(),
            modified_at: new Date().toISOString()
        });

        // Append to DOM
        document.getElementById('orderTabsNav').appendChild(tabNav);
        document.getElementById('orderTabsContent').appendChild(tabContent);

        // Initialize QuickOrder for this tab
        this.initializeTabQuickOrder(tabId);

        // Switch to new tab
        this.switchToTab(tabId);

        // Show notification
        if (!isInitial) {
            this.showNotification(`Đã tạo ${tabTitle}`, 'success');
        }

        return tabId;
    }

    createTabNavigation(tabId, title, isActive = false) {
        const li = document.createElement('li');
        li.className = 'nav-item';
        li.innerHTML = `
            <a class="nav-link ${isActive ? 'active' : ''}" 
               id="${tabId}-tab" 
               data-bs-toggle="tab" 
               data-bs-target="#${tabId}" 
               data-tab-id="${tabId}"
               type="button" 
               role="tab" 
               aria-controls="${tabId}" 
               aria-selected="${isActive}">
                <span class="tab-title">${title}</span>
                <span class="tab-items-count badge badge-light-primary ms-2">0</span>
                ${this.tabs.size > 0 ? `<button type="button" class="btn btn-sm btn-icon btn-light-danger ms-2 close-tab-btn" data-tab-id="${tabId}" title="Đóng tab">
                    <i class="fas fa-times fs-7"></i>
                </button>` : ''}
            </a>
        `;
        return li;
    }

    createTabContent(tabId, isActive = false) {
        const div = document.createElement('div');
        div.className = `tab-pane fade ${isActive ? 'show active' : ''}`;
        div.id = tabId;
        div.setAttribute('role', 'tabpanel');
        div.setAttribute('aria-labelledby', `${tabId}-tab`);
        div.setAttribute('data-tab-id', tabId);

        // Clone the template content
        const template = document.getElementById('orderTabTemplate');
        div.innerHTML = template.innerHTML;

        // Update IDs to be unique for this tab
        this.updateTabContentIds(div, tabId);

        return div;
    }

    updateTabContentIds(container, tabId) {
        // Update all IDs to be unique for this tab
        const elementsWithIds = container.querySelectorAll('[id]');
        elementsWithIds.forEach(element => {
            const originalId = element.id;
            element.id = `${tabId}-${originalId}`;
            
            // Update any labels that reference this ID
            const labels = container.querySelectorAll(`[for="${originalId}"]`);
            labels.forEach(label => {
                label.setAttribute('for', `${tabId}-${originalId}`);
            });
        });

        // Update any data attributes that reference IDs
        const elementsWithDataTargets = container.querySelectorAll('[data-bs-target]');
        elementsWithDataTargets.forEach(element => {
            const target = element.getAttribute('data-bs-target');
            if (target.startsWith('#')) {
                element.setAttribute('data-bs-target', `#${tabId}-${target.substring(1)}`);
            }
        });
    }

    initializeTabQuickOrder(tabId) {
        // Initialize QuickOrder instance for this specific tab
        const tabData = this.tabs.get(tabId);
        if (tabData) {
            // Create a scoped QuickOrder instance for this tab
            tabData.quickOrderInstance = new QuickOrder(tabId);

            // Focus barcode input for the new tab
            setTimeout(() => {
                if (this.activeTabId === tabId) {
                    tabData.quickOrderInstance.focusBarcodeInput();
                }
            }, 200);
        }
    }

    switchToTab(tabId) {
        if (!this.tabs.has(tabId)) {
            console.error('Tab not found:', tabId);
            return;
        }

        // Save current tab data before switching
        if (this.activeTabId && this.tabs.has(this.activeTabId)) {
            this.saveTabData(this.activeTabId);
        }

        this.activeTabId = tabId;
        
        // Load tab data
        this.loadTabData(tabId);

        // Update tab title if needed
        this.updateTabTitle(tabId);

        // Focus barcode input for the active tab
        setTimeout(() => {
            const barcodeInput = document.querySelector(`#${tabId}-barcodeInput`);
            if (barcodeInput) {
                barcodeInput.focus();
            }
        }, 100);
    }

    closeTab(tabId) {
        if (this.tabs.size <= 1) {
            this.showNotification('Không thể đóng tab cuối cùng', 'warning');
            return;
        }

        const tabData = this.tabs.get(tabId);
        if (!tabData) return;

        // Check if tab has unsaved items
        if (tabData.orderItems && tabData.orderItems.length > 0) {
            if (!confirm('Tab này có sản phẩm chưa lưu. Bạn có chắc muốn đóng?')) {
                return;
            }
        }

        // Remove from DOM
        const tabNav = document.querySelector(`#${tabId}-tab`).parentElement;
        const tabContent = document.getElementById(tabId);
        
        if (tabNav) tabNav.remove();
        if (tabContent) tabContent.remove();

        // Remove from tabs map
        this.tabs.delete(tabId);

        // If this was the active tab, switch to another tab
        if (this.activeTabId === tabId) {
            const remainingTabs = Array.from(this.tabs.keys());
            if (remainingTabs.length > 0) {
                this.switchToTab(remainingTabs[0]);
            }
        }

        this.showNotification(`Đã đóng ${tabData.title}`, 'info');
    }

    saveTabData(tabId) {
        const tabData = this.tabs.get(tabId);
        if (!tabData) return;

        // Get current form data from the tab
        const tabContent = document.getElementById(tabId);
        if (!tabContent) return;

        // Save customer selection
        const customerSelect = tabContent.querySelector('[name="customer_id"]');
        if (customerSelect) {
            tabData.customer_id = customerSelect.value;
        }

        // Save branch shop selection
        const branchShopSelect = tabContent.querySelector('[name="branch_shop_id"]');
        if (branchShopSelect) {
            tabData.branch_shop_id = branchShopSelect.value;
        }

        // Save payment method
        const paymentMethodSelect = tabContent.querySelector('[name="payment_method"]');
        if (paymentMethodSelect) {
            tabData.payment_method = paymentMethodSelect.value;
        }

        // Save notes
        const notesTextarea = tabContent.querySelector('[name="notes"]');
        if (notesTextarea) {
            tabData.notes = notesTextarea.value;
        }

        // Update modified time
        tabData.modified_at = new Date().toISOString();

        // Save order items from QuickOrder instance
        if (tabData.quickOrderInstance) {
            tabData.orderItems = tabData.quickOrderInstance.orderItems || [];
        }
    }

    loadTabData(tabId) {
        const tabData = this.tabs.get(tabId);
        if (!tabData) return;

        const tabContent = document.getElementById(tabId);
        if (!tabContent) return;

        // Load customer selection
        const customerSelect = tabContent.querySelector('[name="customer_id"]');
        if (customerSelect && tabData.customer_id) {
            customerSelect.value = tabData.customer_id;
        }

        // Load branch shop selection
        const branchShopSelect = tabContent.querySelector('[name="branch_shop_id"]');
        if (branchShopSelect && tabData.branch_shop_id) {
            branchShopSelect.value = tabData.branch_shop_id;
        }

        // Load payment method
        const paymentMethodSelect = tabContent.querySelector('[name="payment_method"]');
        if (paymentMethodSelect && tabData.payment_method) {
            paymentMethodSelect.value = tabData.payment_method;
        }

        // Load notes
        const notesTextarea = tabContent.querySelector('[name="notes"]');
        if (notesTextarea && tabData.notes) {
            notesTextarea.value = tabData.notes;
        }

        // Load order items to QuickOrder instance
        if (tabData.quickOrderInstance && tabData.orderItems) {
            tabData.quickOrderInstance.orderItems = tabData.orderItems;
            tabData.quickOrderInstance.updateOrderDisplay();
        }
    }

    updateTabTitle(tabId, newTitle = null) {
        const tabData = this.tabs.get(tabId);
        if (!tabData) return;

        if (newTitle) {
            tabData.title = newTitle;
        }

        const tabTitleElement = document.querySelector(`#${tabId}-tab .tab-title`);
        if (tabTitleElement) {
            tabTitleElement.textContent = tabData.title;
        }

        // Update items count
        const itemsCount = tabData.orderItems ? tabData.orderItems.length : 0;
        const countBadge = document.querySelector(`#${tabId}-tab .tab-items-count`);
        if (countBadge) {
            countBadge.textContent = itemsCount;
            countBadge.style.display = itemsCount > 0 ? 'inline' : 'none';
        }
    }

    saveTabsToStorage() {
        try {
            // Save current tab data first
            if (this.activeTabId) {
                this.saveTabData(this.activeTabId);
            }

            const tabsData = {
                tabs: Array.from(this.tabs.entries()).map(([id, data]) => ({
                    id,
                    title: data.title,
                    orderItems: data.orderItems || [],
                    customer_id: data.customer_id,
                    branch_shop_id: data.branch_shop_id,
                    payment_method: data.payment_method,
                    notes: data.notes,
                    created_at: data.created_at,
                    modified_at: data.modified_at
                })),
                activeTabId: this.activeTabId,
                tabCounter: this.tabCounter
            };

            localStorage.setItem('quickOrderTabs', JSON.stringify(tabsData));
        } catch (error) {
            console.error('Failed to save tabs to storage:', error);
        }
    }

    loadTabsFromStorage() {
        try {
            const stored = localStorage.getItem('quickOrderTabs');
            if (!stored) return;

            const tabsData = JSON.parse(stored);
            
            // Clear existing tabs except the initial one
            if (tabsData.tabs && tabsData.tabs.length > 1) {
                // Remove initial tab
                const initialTab = Array.from(this.tabs.keys())[0];
                if (initialTab) {
                    this.closeTab(initialTab);
                }

                // Restore tabs
                tabsData.tabs.forEach((tabData, index) => {
                    const tabId = this.createNewTab(tabData.title, index === 0);
                    if (tabId) {
                        const tab = this.tabs.get(tabId);
                        tab.orderItems = tabData.orderItems || [];
                        tab.customer_id = tabData.customer_id;
                        tab.branch_shop_id = tabData.branch_shop_id;
                        tab.payment_method = tabData.payment_method;
                        tab.notes = tabData.notes;
                        tab.created_at = tabData.created_at;
                        tab.modified_at = tabData.modified_at;
                    }
                });

                // Restore active tab
                if (tabsData.activeTabId && this.tabs.has(tabsData.activeTabId)) {
                    this.switchToTab(tabsData.activeTabId);
                }

                // Restore counter
                this.tabCounter = tabsData.tabCounter || this.tabs.size;
            }
        } catch (error) {
            console.error('Failed to load tabs from storage:', error);
        }
    }

    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        document.body.appendChild(notification);

        // Auto remove after 3 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 3000);
    }

    // Public methods
    getActiveTab() {
        return this.tabs.get(this.activeTabId);
    }

    getActiveTabId() {
        return this.activeTabId;
    }

    getAllTabs() {
        return Array.from(this.tabs.values());
    }

    updateActiveTabItemsCount() {
        if (this.activeTabId) {
            this.updateTabTitle(this.activeTabId);
        }
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.quickOrderTabs = new QuickOrderTabs();
});
