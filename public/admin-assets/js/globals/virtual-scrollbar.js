/**
 * Virtual Horizontal Scrollbar for kt_table_responsive_container
 * Tạo scrollbar ảo thay thế cho scrollbar gốc
 */

class VirtualScrollbar {
    constructor(container) {
        this.container = container;
        this.isDragging = false;
        this.startX = 0;
        this.startScrollLeft = 0;
        this.thumbWidth = 0;
        this.trackWidth = 0;
        this.isHovered = false;

        this.init();
    }
    
    init() {
        this.updateThumbSize();
        this.bindEvents();

        // Update thumb position on scroll
        this.container.addEventListener('scroll', () => {
            this.updateThumbPosition();
        });

        // Update on resize
        window.addEventListener('resize', () => {
            this.updateThumbSize();
            this.updateThumbPosition();
        });

        // Track hover state - only for table area, not filter sidebars
        this.container.addEventListener('mouseenter', (e) => {
            // Check if entering from a filter sidebar or similar component
            const isFromFilterArea = e.relatedTarget?.closest('.filter-sidebar, .app-aside, [class*="filter"], .choices__list, .choices__inner');
            if (!isFromFilterArea) {
                this.isHovered = true;
            }
        });

        this.container.addEventListener('mouseleave', (e) => {
            // Check if leaving to a filter sidebar or similar component
            const isToFilterArea = e.relatedTarget?.closest('.filter-sidebar, .app-aside, [class*="filter"], .choices__list, .choices__inner');
            if (!isToFilterArea) {
                this.isHovered = false;
            }
        });

        // Also track mouse movement to be more precise
        this.container.addEventListener('mousemove', (e) => {
            const target = e.target;
            const isInFilterArea = target.closest('.filter-sidebar, .app-aside, [class*="filter"], .choices__list, .choices__inner');
            this.isHovered = !isInFilterArea;
        });

        // Add wheel event for horizontal scrolling (only when hovered)
        this.container.addEventListener('wheel', (e) => {
            this.handleWheel(e);
        });

        // Initial position
        this.updateThumbPosition();
    }
    
    updateThumbSize() {
        const containerWidth = this.container.clientWidth;
        const scrollWidth = this.container.scrollWidth;
        
        if (scrollWidth <= containerWidth) {
            // No scrolling needed, hide virtual scrollbar
            this.container.style.setProperty('--virtual-thumb-width', '0px');
            return;
        }
        
        // Calculate thumb width based on content ratio
        this.trackWidth = containerWidth - 4; // Account for padding
        this.thumbWidth = Math.max(20, (containerWidth / scrollWidth) * this.trackWidth);
        
        this.container.style.setProperty('--virtual-thumb-width', this.thumbWidth + 'px');
    }
    
    updateThumbPosition() {
        const scrollLeft = this.container.scrollLeft;
        const maxScrollLeft = this.container.scrollWidth - this.container.clientWidth;
        
        if (maxScrollLeft <= 0) {
            this.container.style.setProperty('--virtual-thumb-left', '2px');
            return;
        }
        
        const scrollRatio = scrollLeft / maxScrollLeft;
        const maxThumbLeft = this.trackWidth - this.thumbWidth;
        const thumbLeft = 2 + (scrollRatio * maxThumbLeft);
        
        this.container.style.setProperty('--virtual-thumb-left', thumbLeft + 'px');
    }
    
    bindEvents() {
        // Mouse events for dragging
        this.container.addEventListener('mousedown', (e) => {
            if (this.isClickOnThumb(e)) {
                this.startDrag(e);
            } else if (this.isClickOnTrack(e)) {
                this.jumpToPosition(e);
            }
        });
        
        document.addEventListener('mousemove', (e) => {
            if (this.isDragging) {
                this.drag(e);
            }
        });
        
        document.addEventListener('mouseup', () => {
            this.stopDrag();
        });
        
        // Touch events for mobile
        this.container.addEventListener('touchstart', (e) => {
            if (this.isClickOnThumb(e.touches[0])) {
                this.startDrag(e.touches[0]);
            }
        });
        
        document.addEventListener('touchmove', (e) => {
            if (this.isDragging) {
                e.preventDefault();
                this.drag(e.touches[0]);
            }
        });
        
        document.addEventListener('touchend', () => {
            this.stopDrag();
        });
    }
    
    isClickOnThumb(event) {
        const rect = this.container.getBoundingClientRect();
        const x = event.clientX - rect.left;
        const y = event.clientY - rect.top;
        
        const thumbLeft = parseFloat(this.container.style.getPropertyValue('--virtual-thumb-left') || '2px');
        const thumbWidth = parseFloat(this.container.style.getPropertyValue('--virtual-thumb-width') || '0px');
        const thumbTop = this.container.clientHeight - 10;
        
        return x >= thumbLeft && x <= thumbLeft + thumbWidth && 
               y >= thumbTop && y <= thumbTop + 8;
    }
    
    isClickOnTrack(event) {
        const rect = this.container.getBoundingClientRect();
        const y = event.clientY - rect.top;
        const trackTop = this.container.clientHeight - 12;
        
        return y >= trackTop && y <= trackTop + 12;
    }
    
    startDrag(event) {
        this.isDragging = true;
        this.startX = event.clientX;
        this.startScrollLeft = this.container.scrollLeft;
        
        // Add dragging class for visual feedback
        this.container.classList.add('virtual-scrollbar-dragging');
        
        // Prevent text selection
        document.body.style.userSelect = 'none';
    }
    
    drag(event) {
        if (!this.isDragging) return;
        
        const deltaX = event.clientX - this.startX;
        const maxScrollLeft = this.container.scrollWidth - this.container.clientWidth;
        const scrollRatio = deltaX / (this.trackWidth - this.thumbWidth);
        const newScrollLeft = this.startScrollLeft + (scrollRatio * maxScrollLeft);
        
        this.container.scrollLeft = Math.max(0, Math.min(maxScrollLeft, newScrollLeft));
    }
    
    stopDrag() {
        if (!this.isDragging) return;
        
        this.isDragging = false;
        this.container.classList.remove('virtual-scrollbar-dragging');
        document.body.style.userSelect = '';
    }
    
    jumpToPosition(event) {
        const rect = this.container.getBoundingClientRect();
        const x = event.clientX - rect.left;
        const clickRatio = (x - 2) / this.trackWidth;
        const maxScrollLeft = this.container.scrollWidth - this.container.clientWidth;

        this.container.scrollLeft = clickRatio * maxScrollLeft;
    }

    handleWheel(event) {
        // Check if the event originated from a filter sidebar or other scrollable area
        const target = event.target;
        const isInFilterSidebar = target.closest('.filter-sidebar, .app-aside, [class*="filter"], .choices__list, .choices__inner');

        // If event is from filter sidebar or similar components, don't interfere
        if (isInFilterSidebar) {
            return; // Let default behavior handle it
        }

        // Check if any element in the event path is a filter sidebar
        const eventPath = event.composedPath ? event.composedPath() : (event.path || []);
        const hasFilterSidebarInPath = eventPath.some(element => {
            return element.nodeType === 1 && element.closest &&
                   element.closest('.filter-sidebar, .app-aside, [class*="filter"], .choices__list, .choices__inner');
        });

        if (hasFilterSidebarInPath) {
            return; // Let default behavior handle it
        }

        // Only handle when mouse is hovering over this container
        if (!this.isHovered) {
            return; // Let default behavior handle it
        }

        // Only handle horizontal scrolling when there's horizontal overflow
        const hasHorizontalScroll = this.container.scrollWidth > this.container.clientWidth;

        if (!hasHorizontalScroll) {
            return; // Let default behavior handle it
        }

        // Check if shift key is pressed for horizontal scroll
        if (event.shiftKey) {
            event.preventDefault();
            const scrollAmount = event.deltaY * 0.5; // Adjust scroll speed
            this.container.scrollLeft += scrollAmount;
            return;
        }

        // For table containers, convert vertical wheel to horizontal scroll
        // when there's no vertical scrolling needed
        const hasVerticalScroll = this.container.scrollHeight > this.container.clientHeight;

        if (!hasVerticalScroll && Math.abs(event.deltaY) > Math.abs(event.deltaX)) {
            event.preventDefault();
            const scrollAmount = event.deltaY * 0.3; // Slower horizontal scroll
            this.container.scrollLeft += scrollAmount;
        }

        // Otherwise, let default behavior handle vertical scrolling
    }
}

// Auto-initialize for all kt_table_responsive_container elements
document.addEventListener('DOMContentLoaded', function() {
    const containers = document.querySelectorAll('.kt_table_responsive_container');
    containers.forEach(container => {
        new VirtualScrollbar(container);
    });
});

// Export for manual initialization
window.VirtualScrollbar = VirtualScrollbar;
