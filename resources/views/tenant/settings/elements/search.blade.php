<!--begin::Settings Search-->
<div class="mb-5">
    <div class="position-relative">
        <i class="fas fa-search position-absolute top-50 translate-middle-y ms-4 text-gray-500"></i>
        <input type="text"
               id="settings_search_input"
               class="form-control form-control-solid ps-12"
               placeholder="Tìm kiếm thiết lập"
               autocomplete="off" />
        <div id="settings_search_loading" class="position-absolute top-50 translate-middle-y end-0 me-4 d-none">
            <span class="spinner-border spinner-border-sm text-primary" role="status"></span>
        </div>
    </div>

    <!--begin::Search Results Dropdown-->
    <div id="settings_search_results" class="position-absolute bg-white shadow-sm rounded mt-1 d-none" style="z-index: 100; width: calc(100% - 20px); max-height: 400px; overflow-y: auto; border: 1px solid #e4e6ef;">
        <!-- Results will be populated here via AJAX -->
    </div>
    <!--end::Search Results Dropdown-->
</div>
<!--end::Settings Search-->

<style>
.settings-search-item {
    padding: 12px 16px;
    border-bottom: 1px solid #e4e6ef;
    cursor: pointer;
    transition: background-color 0.2s;
}
.settings-search-item:hover {
    background-color: #f5f8fa;
}
.settings-search-item:last-child {
    border-bottom: none;
}
.settings-search-item .header-text {
    font-weight: 600;
    color: #181c32;
    font-size: 14px;
    margin-bottom: 4px;
}
.settings-search-item .sub-text {
    font-size: 12px;
    color: #7e8299;
    line-height: 1.4;
}
.settings-search-item .category-badge {
    display: inline-block;
    padding: 2px 8px;
    background-color: #f1f1f2;
    color: #7e8299;
    border-radius: 4px;
    font-size: 11px;
    margin-bottom: 4px;
}
.search-highlight {
    background-color: #fff4de;
    font-weight: 600;
}
</style>

@push('scripts')
<script>
// Settings search functionality with AJAX
(function() {
    'use strict';

    const searchInput = document.getElementById('settings_search_input');
    const searchResults = document.getElementById('settings_search_results');
    const searchLoading = document.getElementById('settings_search_loading');
    let searchTimeout;
    let currentRequest = null;

    if (!searchInput) return;

    // Handle input
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();

        if (query.length === 0) {
            searchResults.classList.add('d-none');
            searchResults.innerHTML = '';
            searchLoading.classList.add('d-none');
            return;
        }

        if (query.length < 2) {
            return;
        }

        searchTimeout = setTimeout(() => {
            performSearch(query);
        }, 300);
    });

    // Perform AJAX search
    function performSearch(query) {
        // Cancel previous request if exists
        if (currentRequest) {
            currentRequest.abort();
        }

        // Show loading
        searchLoading.classList.remove('d-none');

        // Make AJAX request using fetch
        const controller = new AbortController();
        currentRequest = controller;

        fetch('{{ route('admin.settings.search') }}?q=' + encodeURIComponent(query), {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            signal: controller.signal
        })
        .then(response => response.json())
        .then(results => {
            displayResults(results, query);
        })
        .catch(error => {
            if (error.name !== 'AbortError') {
                console.error('Search error:', error);
                searchResults.innerHTML = `
                    <div class="p-4 text-center text-danger">
                        <i class="fas fa-exclamation-triangle fs-2x mb-3"></i>
                        <p class="mb-0">Có lỗi xảy ra khi tìm kiếm</p>
                    </div>
                `;
                searchResults.classList.remove('d-none');
            }
        })
        .finally(() => {
            searchLoading.classList.add('d-none');
            currentRequest = null;
        });
    }

    // Display search results
    function displayResults(results, query) {
        if (results.length === 0) {
            searchResults.innerHTML = `
                <div class="p-4 text-center text-muted">
                    <i class="fas fa-search fs-2x mb-3"></i>
                    <p class="mb-0">Không tìm thấy thiết lập phù hợp</p>
                </div>
            `;
            searchResults.classList.remove('d-none');
            return;
        }

        let html = '';
        results.forEach(item => {
            const highlightedLabel = highlightText(item.label, query);
            const highlightedDesc = highlightText(item.description, query);

            html += `
                <div class="settings-search-item" data-url="${item.url}">
                    <div class="category-badge">${item.category}</div>
                    <div class="header-text">${highlightedLabel}</div>
                    <div class="sub-text">${highlightedDesc}</div>
                </div>
            `;
        });

        searchResults.innerHTML = html;
        searchResults.classList.remove('d-none');
    }

    // Highlight matching text
    function highlightText(text, query) {
        if (!query) return text;

        const regex = new RegExp(`(${escapeRegex(query)})`, 'gi');
        return text.replace(regex, '<span class="search-highlight">$1</span>');
    }

    // Escape regex special characters
    function escapeRegex(string) {
        return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    // Handle click on search result
    document.addEventListener('click', function(e) {
        const item = e.target.closest('.settings-search-item');
        if (item) {
            // Prevent event bubbling to avoid triggering other click handlers
            e.preventDefault();
            e.stopPropagation();

            const url = item.getAttribute('data-url');

            if (url === '#') {
                // Placeholder - show message
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'info',
                        title: 'Chức năng đang phát triển',
                        text: 'Tính năng này sẽ sớm được cập nhật',
                        confirmButtonText: 'Đóng'
                    });
                }
                return;
            }

            // Navigate to URL
            window.location.href = url;
        }
    });

    // Close search results when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#settings_search_input') && !e.target.closest('#settings_search_results')) {
            searchResults.classList.add('d-none');
        }
    });

    // Function to scroll to section and highlight
    function scrollToSection() {
        if (window.location.hash) {
            const sectionId = window.location.hash.substring(1);
            setTimeout(() => {
                const section = document.querySelector(`[data-section-id="${sectionId}"]`);
                if (section) {
                    section.scrollIntoView({ behavior: 'smooth', block: 'center' });

                    // Highlight the section briefly
                    section.classList.add('bg-light-primary');
                    setTimeout(() => {
                        section.classList.remove('bg-light-primary');
                    }, 2000);
                }
            }, 500);
        }
    }

    // Handle scroll to section after page load
    scrollToSection();

    // Handle scroll to section when hash changes (e.g., when clicking search result)
    window.addEventListener('hashchange', scrollToSection);
})();
</script>
@endpush
