"use strict";

// Global Loading Manager
var KTLoadingManager = function () {
    // Private variables
    var pageLoader;
    var formLoaders = new Map();
    var buttonLoaders = new Map();

    // Create page loader
    var createPageLoader = function() {
        if (document.getElementById('kt_page_loader')) {
            return;
        }

        const loader = document.createElement('div');
        loader.id = 'kt_page_loader';
        loader.className = 'page-loader flex-column bg-dark bg-opacity-25';
        loader.innerHTML = `
            <span class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Đang tải...</span>
            </span>
            <span class="text-gray-800 fs-6 fw-semibold mt-5">Đang tải...</span>
        `;
        
        // Add CSS styles
        loader.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        `;

        document.body.appendChild(loader);
        pageLoader = loader;
    };

    // Show page loader
    var showPageLoader = function(message = 'Đang tải...') {
        if (!pageLoader) {
            createPageLoader();
        }
        
        const messageElement = pageLoader.querySelector('.text-gray-800');
        if (messageElement) {
            messageElement.textContent = message;
        }
        
        pageLoader.style.display = 'flex';
    };

    // Hide page loader
    var hidePageLoader = function() {
        if (pageLoader) {
            pageLoader.style.display = 'none';
        }
    };

    // Show form loader
    var showFormLoader = function(form, message = 'Đang xử lý...') {
        if (!form) return;

        const formId = form.id || 'form_' + Date.now();
        
        // Create overlay
        const overlay = document.createElement('div');
        overlay.className = 'form-loader-overlay';
        overlay.innerHTML = `
            <div class="form-loader-content">
                <span class="spinner-border spinner-border-sm text-primary me-2" role="status"></span>
                <span class="text-gray-800 fs-7">${message}</span>
            </div>
        `;
        
        overlay.style.cssText = `
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            border-radius: 0.475rem;
        `;

        // Make form container relative
        form.style.position = 'relative';
        form.appendChild(overlay);
        
        formLoaders.set(formId, overlay);
        
        // Disable form inputs
        const inputs = form.querySelectorAll('input, select, textarea, button');
        inputs.forEach(input => {
            input.disabled = true;
            input.setAttribute('data-kt-loading-disabled', 'true');
        });
    };

    // Hide form loader
    var hideFormLoader = function(form) {
        if (!form) return;

        const formId = form.id || 'form_' + Date.now();
        const overlay = formLoaders.get(formId);
        
        if (overlay && overlay.parentNode) {
            overlay.parentNode.removeChild(overlay);
            formLoaders.delete(formId);
        }
        
        // Re-enable form inputs
        const inputs = form.querySelectorAll('[data-kt-loading-disabled="true"]');
        inputs.forEach(input => {
            input.disabled = false;
            input.removeAttribute('data-kt-loading-disabled');
        });
    };

    // Show simple button spinner (no data-kt-indicator)
    var showButtonLoader = function(button, message = 'Đang xử lý...') {
        if (!button) return;

        const buttonId = button.id || 'btn_' + Date.now();

        // Store original content
        const originalContent = button.innerHTML;
        buttonLoaders.set(buttonId, originalContent);

        // Set simple loading content
        button.innerHTML = `
            ${message}
            <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
        `;

        button.disabled = true;
    };

    // Hide button loader
    var hideButtonLoader = function(button) {
        if (!button) return;

        const buttonId = button.id || 'btn_' + Date.now();
        const originalContent = buttonLoaders.get(buttonId);

        if (originalContent) {
            button.innerHTML = originalContent;
            buttonLoaders.delete(buttonId);
        }

        button.disabled = false;
    };

    // Auto-handle form submissions - DISABLED
    var initFormHandlers = function() {
        // Disabled automatic form loading
        // Only manual control via showFormLoader/hideFormLoader
    };

    // Auto-handle button clicks - DISABLED
    var initButtonHandlers = function() {
        // Disabled automatic button loading
        // Only manual control via showButtonLoader/hideButtonLoader
    };

    // Handle page navigation
    var initPageHandlers = function() {
        // Show loader on page unload
        window.addEventListener('beforeunload', function() {
            showPageLoader('Đang chuyển trang...');
        });

        // Hide loader when page loads
        window.addEventListener('load', function() {
            hidePageLoader();
        });

        // Handle AJAX navigation
        document.addEventListener('click', function(e) {
            const link = e.target.closest('a[href]:not([href^="#"]):not([href^="javascript:"]):not([target="_blank"])');
            if (link && !link.hasAttribute('data-kt-loading-disabled')) {
                showPageLoader('Đang tải trang...');
            }
        });
    };

    // Handle AJAX requests - DISABLED
    var initAjaxHandlers = function() {
        // Disabled automatic AJAX loading
        // No global fetch/AJAX interception
    };

    // Public methods
    return {
        init: function() {
            createPageLoader();
            initFormHandlers();
            initButtonHandlers();
            initPageHandlers();
            initAjaxHandlers();
        },

        // Public API
        showPageLoader: showPageLoader,
        hidePageLoader: hidePageLoader,
        showFormLoader: showFormLoader,
        hideFormLoader: hideFormLoader,
        showButtonLoader: showButtonLoader,
        hideButtonLoader: hideButtonLoader,

        // Utility methods
        showLoader: function(element, message) {
            if (element.tagName === 'FORM') {
                showFormLoader(element, message);
            } else if (element.tagName === 'BUTTON') {
                showButtonLoader(element, message);
            }
        },

        hideLoader: function(element) {
            if (element.tagName === 'FORM') {
                hideFormLoader(element);
            } else if (element.tagName === 'BUTTON') {
                hideButtonLoader(element);
            }
        }
    };
}();

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    KTLoadingManager.init();
});

// Global access
window.KTLoadingManager = KTLoadingManager;
