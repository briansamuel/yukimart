/**
 * Orders List - Responsive Table Management
 * Handles horizontal scrolling, column visibility, and responsive behavior
 */

class OrdersTableManager {
    constructor() {
        this.tableContainer = null;
        this.table = null;
        this.isInitialized = false;
        this.scrollTimeout = null;
        this.resizeTimeout = null;

        // Bind methods to preserve context
        this.updateScrollIndicators = this.updateScrollIndicators.bind(this);
        this.handleScroll = this.handleScroll.bind(this);
        this.handleResize = this.handleResize.bind(this);
        this.handleColumnVisibilityChange = this.handleColumnVisibilityChange.bind(this);

        this.init();
    }

    init() {
        console.log('Initializing Orders Table Manager...');

        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setup());
        } else {
            this.setup();
        }
    }

    setup() {
        this.tableContainer = document.getElementById('kt_orders_table_container');
        this.table = document.getElementById('kt_orders_table');

        if (!this.tableContainer || !this.table) {
            console.warn('Orders table elements not found');
            return;
        }

        this.initScrollIndicators();
        this.initResponsiveHandlers();
        this.initColumnVisibilityHandlers();
        this.initTableObserver();

        this.isInitialized = true;
        console.log('Orders Table Manager initialized successfully');
    }
    
    initScrollIndicators() {
        console.log('Initializing scroll indicators...');
        
        // Add scroll event listener
        this.tableContainer.addEventListener('scroll', (e) => {
            this.handleScroll(e);
        });
        
        // Initial scroll state check
        this.updateScrollIndicators();
    }
    
    handleScroll() {
        // Debounce scroll events
        if (this.scrollTimeout) {
            clearTimeout(this.scrollTimeout);
        }

        this.scrollTimeout = setTimeout(() => {
            this.updateScrollIndicators();
        }, 10);
    }
    
    updateScrollIndicators() {
        if (!this.tableContainer) return;
        
        const scrollLeft = this.tableContainer.scrollLeft;
        const scrollWidth = this.tableContainer.scrollWidth;
        const clientWidth = this.tableContainer.clientWidth;
        const maxScrollLeft = scrollWidth - clientWidth;
        
        // Check if content is scrollable
        const hasScroll = scrollWidth > clientWidth;
        
        // Check scroll position
        const hasScrollLeft = scrollLeft > 10;
        const hasScrollRight = scrollLeft < (maxScrollLeft - 10);
        
        // Update classes
        //this.tableContainer.classList.toggle('has-scroll', hasScroll);
        //this.tableContainer.classList.toggle('has-scroll-left', hasScrollLeft);
        //this.tableContainer.classList.toggle('has-scroll-right', hasScrollRight);
        
        console.log('Scroll indicators updated:', {
            hasScroll,
            hasScrollLeft,
            hasScrollRight,
            scrollLeft,
            maxScrollLeft
        });
    }
    
    initResponsiveHandlers() {
        console.log('Initializing responsive handlers...');
        
        // Add resize event listener
        window.addEventListener('resize', () => {
            this.handleResize();
        });
        
        // Initial responsive check
        this.handleResize();
    }
    
    handleResize() {
        // Debounce resize events
        if (this.resizeTimeout) {
            clearTimeout(this.resizeTimeout);
        }

        this.resizeTimeout = setTimeout(() => {
            this.updateResponsiveState();
            this.updateScrollIndicators();
        }, 100);
    }
    
    updateResponsiveState() {
        if (!this.tableContainer) return;
        
        const containerWidth = this.tableContainer.clientWidth;
        const tableWidth = this.table.scrollWidth;
        
        // Add responsive classes based on container width
        const breakpoints = {
            'mobile': 576,
            'tablet': 768,
            'desktop': 992,
            'large': 1200,
            'xl': 1400
        };
        
        // Remove all breakpoint classes
        Object.keys(breakpoints).forEach(bp => {
            this.tableContainer.classList.remove(`orders-table-${bp}`);
        });
        
        // Add current breakpoint class
        const currentWidth = window.innerWidth;
        let currentBreakpoint = 'xl';
        
        for (const [name, width] of Object.entries(breakpoints)) {
            if (currentWidth < width) {
                currentBreakpoint = name;
                break;
            }
        }
        
        this.tableContainer.classList.add(`orders-table-${currentBreakpoint}`);
        
        console.log('Responsive state updated:', {
            containerWidth,
            tableWidth,
            currentBreakpoint,
            windowWidth: currentWidth
        });
    }
    
    initColumnVisibilityHandlers() {
        console.log('Initializing column visibility handlers...');
        
        // Listen for column visibility changes
        document.addEventListener('columnVisibilityChanged', (event) => {
            this.handleColumnVisibilityChange(event.detail);
        });
    }
    
    handleColumnVisibilityChange(detail) {
        console.log('Column visibility changed:', detail);

        // Update scroll indicators after column visibility change
        setTimeout(() => {
            this.updateScrollIndicators();
        }, 100);
    }
    
    initTableObserver() {
        console.log('Initializing table observer...');

        // Temporarily disabled observers to focus on CSS sync
        // TODO: Re-enable after CSS sync is complete

        /*
        // Use ResizeObserver to watch for table size changes
        if (window.ResizeObserver) {
            this.resizeObserver = new ResizeObserver((entries) => {
                for (const entry of entries) {
                    if (entry.target === this.table) {
                        this.updateScrollIndicators();
                    }
                }
            });

            this.resizeObserver.observe(this.table);
        }

        // Use MutationObserver to watch for table content changes
        if (window.MutationObserver) {
            this.mutationObserver = new MutationObserver((mutations) => {
                let shouldUpdate = false;

                mutations.forEach((mutation) => {
                    if (mutation.type === 'childList' ||
                        (mutation.type === 'attributes' && mutation.attributeName === 'style')) {
                        shouldUpdate = true;
                    }
                });

                if (shouldUpdate) {
                    setTimeout(() => {
                        this.updateScrollIndicators();
                    }, 50);
                }
            });

            this.mutationObserver.observe(this.table, {
                childList: true,
                subtree: true,
                attributes: true,
                attributeFilter: ['style', 'class']
            });
        }
        */
    }
    
    // Public methods for external use
    
    refreshScrollIndicators() {
        this.updateScrollIndicators();
    }
    
    scrollToColumn(columnIndex) {
        if (!this.table) return;
        
        const targetCell = this.table.querySelector(`thead th:nth-child(${columnIndex + 1})`);
        if (targetCell) {
            const cellLeft = targetCell.offsetLeft;
            const cellWidth = targetCell.offsetWidth;
            const containerWidth = this.tableContainer.clientWidth;
            
            // Calculate scroll position to center the column
            const scrollLeft = cellLeft - (containerWidth / 2) + (cellWidth / 2);
            
            this.tableContainer.scrollTo({
                left: Math.max(0, scrollLeft),
                behavior: 'smooth'
            });
        }
    }
    
    showLoadingState() {
        if (this.tableContainer) {
            this.tableContainer.classList.add('orders-table-loading');
        }
    }
    
    hideLoadingState() {
        if (this.tableContainer) {
            this.tableContainer.classList.remove('orders-table-loading');
        }
    }
    
    destroy() {
        console.log('Destroying Orders Table Manager...');
        
        // Remove event listeners
        if (this.tableContainer) {
            this.tableContainer.removeEventListener('scroll', this.handleScroll);
        }
        
        window.removeEventListener('resize', this.handleResize);
        
        // Disconnect observers
        if (this.resizeObserver) {
            this.resizeObserver.disconnect();
        }
        
        if (this.mutationObserver) {
            this.mutationObserver.disconnect();
        }
        
        // Clear timeouts
        if (this.scrollTimeout) {
            clearTimeout(this.scrollTimeout);
        }
        
        if (this.resizeTimeout) {
            clearTimeout(this.resizeTimeout);
        }
        
        this.isInitialized = false;
    }
}

// Global instance
let ordersTableManager = null;

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    ordersTableManager = new OrdersTableManager();
});

// Export for external use
window.OrdersTableManager = OrdersTableManager;
window.ordersTableManager = ordersTableManager;
