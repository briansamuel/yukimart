/**
 * Performance Monitor Utility
 * Monitors and optimizes frontend performance
 */

class PerformanceMonitor {
    constructor() {
        this.metrics = {};
        this.observers = {};
        this.init();
    }

    init() {
        this.setupPerformanceObserver();
        this.setupIntersectionObserver();
        this.setupMutationObserver();
        this.monitorAjaxRequests();
        this.optimizeImages();
    }

    // Performance Observer for monitoring web vitals
    setupPerformanceObserver() {
        if ('PerformanceObserver' in window) {
            // Monitor LCP (Largest Contentful Paint)
            const lcpObserver = new PerformanceObserver((entryList) => {
                const entries = entryList.getEntries();
                const lastEntry = entries[entries.length - 1];
                this.metrics.lcp = lastEntry.startTime;
                this.logMetric('LCP', lastEntry.startTime);
            });

            try {
                lcpObserver.observe({ entryTypes: ['largest-contentful-paint'] });
            } catch (e) {
                console.warn('LCP observer not supported');
            }

            // Monitor FID (First Input Delay)
            const fidObserver = new PerformanceObserver((entryList) => {
                const entries = entryList.getEntries();
                entries.forEach(entry => {
                    this.metrics.fid = entry.processingStart - entry.startTime;
                    this.logMetric('FID', entry.processingStart - entry.startTime);
                });
            });

            try {
                fidObserver.observe({ entryTypes: ['first-input'] });
            } catch (e) {
                console.warn('FID observer not supported');
            }

            // Monitor CLS (Cumulative Layout Shift)
            let clsValue = 0;
            const clsObserver = new PerformanceObserver((entryList) => {
                const entries = entryList.getEntries();
                entries.forEach(entry => {
                    if (!entry.hadRecentInput) {
                        clsValue += entry.value;
                        this.metrics.cls = clsValue;
                        this.logMetric('CLS', clsValue);
                    }
                });
            });

            try {
                clsObserver.observe({ entryTypes: ['layout-shift'] });
            } catch (e) {
                console.warn('CLS observer not supported');
            }
        }
    }

    // Intersection Observer for lazy loading optimization
    setupIntersectionObserver() {
        if ('IntersectionObserver' in window) {
            this.observers.intersection = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        this.handleElementInView(entry.target);
                    }
                });
            }, {
                rootMargin: '50px 0px',
                threshold: 0.1
            });

            // Observe lazy-loadable elements
            document.querySelectorAll('[data-lazy]').forEach(el => {
                this.observers.intersection.observe(el);
            });
        }
    }

    // Mutation Observer for DOM changes
    setupMutationObserver() {
        if ('MutationObserver' in window) {
            this.observers.mutation = new MutationObserver((mutations) => {
                mutations.forEach(mutation => {
                    if (mutation.type === 'childList') {
                        mutation.addedNodes.forEach(node => {
                            if (node.nodeType === Node.ELEMENT_NODE) {
                                this.optimizeNewElement(node);
                            }
                        });
                    }
                });
            });

            this.observers.mutation.observe(document.body, {
                childList: true,
                subtree: true
            });
        }
    }

    // Monitor AJAX requests for performance
    monitorAjaxRequests() {
        const originalFetch = window.fetch;
        const originalXHR = window.XMLHttpRequest;

        // Monitor fetch requests
        window.fetch = (...args) => {
            const startTime = performance.now();
            const url = args[0];

            return originalFetch.apply(this, args)
                .then(response => {
                    const endTime = performance.now();
                    this.logAjaxMetric('fetch', url, endTime - startTime, response.status);
                    return response;
                })
                .catch(error => {
                    const endTime = performance.now();
                    this.logAjaxMetric('fetch', url, endTime - startTime, 'error');
                    throw error;
                });
        };

        // Monitor XMLHttpRequest
        const self = this;
        window.XMLHttpRequest = function() {
            const xhr = new originalXHR();
            const originalOpen = xhr.open;
            const originalSend = xhr.send;
            let startTime;
            let url;

            xhr.open = function(method, requestUrl, ...args) {
                url = requestUrl;
                return originalOpen.apply(this, [method, requestUrl, ...args]);
            };

            xhr.send = function(...args) {
                startTime = performance.now();
                
                xhr.addEventListener('loadend', () => {
                    const endTime = performance.now();
                    self.logAjaxMetric('xhr', url, endTime - startTime, xhr.status);
                });

                return originalSend.apply(this, args);
            };

            return xhr;
        };
    }

    // Optimize images for better performance
    optimizeImages() {
        // Add loading="lazy" to images
        document.querySelectorAll('img:not([loading])').forEach(img => {
            img.loading = 'lazy';
        });

        // Convert images to WebP if supported
        if (this.supportsWebP()) {
            document.querySelectorAll('img[data-webp]').forEach(img => {
                img.src = img.dataset.webp;
            });
        }
    }

    // Handle elements coming into view
    handleElementInView(element) {
        // Lazy load images
        if (element.tagName === 'IMG' && element.dataset.src) {
            element.src = element.dataset.src;
            element.removeAttribute('data-src');
        }

        // Lazy load background images
        if (element.dataset.bgSrc) {
            element.style.backgroundImage = `url(${element.dataset.bgSrc})`;
            element.removeAttribute('data-bg-src');
        }

        // Trigger lazy load events
        if (element.dataset.lazy) {
            element.dispatchEvent(new CustomEvent('lazyload'));
            element.removeAttribute('data-lazy');
        }

        this.observers.intersection.unobserve(element);
    }

    // Optimize newly added elements
    optimizeNewElement(element) {
        // Add lazy loading to new images
        if (element.tagName === 'IMG' && !element.hasAttribute('loading')) {
            element.loading = 'lazy';
        }

        // Observe new lazy-loadable elements
        if (element.hasAttribute('data-lazy')) {
            this.observers.intersection.observe(element);
        }

        // Optimize forms
        if (element.tagName === 'FORM') {
            this.optimizeForm(element);
        }

        // Optimize tables
        if (element.tagName === 'TABLE') {
            this.optimizeTable(element);
        }
    }

    // Optimize forms for better UX
    optimizeForm(form) {
        // Add debouncing to search inputs
        form.querySelectorAll('input[type="search"], input[data-search]').forEach(input => {
            this.addDebounce(input, 300);
        });

        // Add validation feedback
        form.querySelectorAll('input, select, textarea').forEach(field => {
            field.addEventListener('blur', () => this.validateField(field));
        });
    }

    // Optimize tables for better performance
    optimizeTable(table) {
        // Add virtual scrolling for large tables
        if (table.rows.length > 100) {
            this.addVirtualScrolling(table);
        }

        // Add column resizing
        if (table.classList.contains('resizable')) {
            this.addColumnResizing(table);
        }
    }

    // Add debouncing to input elements
    addDebounce(element, delay) {
        let timeout;
        const originalHandler = element.oninput;

        element.oninput = function(event) {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                if (originalHandler) {
                    originalHandler.call(this, event);
                }
            }, delay);
        };
    }

    // Validate form fields
    validateField(field) {
        const isValid = field.checkValidity();
        field.classList.toggle('is-valid', isValid);
        field.classList.toggle('is-invalid', !isValid);
    }

    // Check WebP support
    supportsWebP() {
        const canvas = document.createElement('canvas');
        canvas.width = 1;
        canvas.height = 1;
        return canvas.toDataURL('image/webp').indexOf('data:image/webp') === 0;
    }

    // Log performance metrics
    logMetric(name, value) {
        if (window.console && console.log) {
            console.log(`Performance Metric - ${name}: ${Math.round(value)}ms`);
        }

        // Send to analytics if available
        if (window.gtag) {
            gtag('event', 'performance_metric', {
                metric_name: name,
                metric_value: Math.round(value)
            });
        }
    }

    // Log AJAX performance metrics
    logAjaxMetric(type, url, duration, status) {
        if (window.console && console.log) {
            console.log(`AJAX Performance - ${type.toUpperCase()}: ${url} (${Math.round(duration)}ms, ${status})`);
        }

        // Track slow requests
        if (duration > 2000) {
            console.warn(`Slow ${type.toUpperCase()} request detected: ${url} took ${Math.round(duration)}ms`);
        }
    }

    // Get current metrics
    getMetrics() {
        return {
            ...this.metrics,
            navigation: performance.getEntriesByType('navigation')[0],
            resources: performance.getEntriesByType('resource').length
        };
    }

    // Clean up observers
    destroy() {
        Object.values(this.observers).forEach(observer => {
            if (observer && observer.disconnect) {
                observer.disconnect();
            }
        });
    }
}

// Initialize performance monitor when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.performanceMonitor = new PerformanceMonitor();
    });
} else {
    window.performanceMonitor = new PerformanceMonitor();
}
