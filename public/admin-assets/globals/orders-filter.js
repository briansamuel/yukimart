/**
 * Orders Filter System
 * Simple filter functions for orders page
 */

// Global filter state
let globalFilters = {};
let filterCallbacks = {};

/**
 * Initialize time filter
 * @param {Function} callback - Function to call when filter changes
 */
function initTimeFilter(callback) {
    console.log('Initializing time filter...');
    
    // Store callback
    filterCallbacks.timeFilter = callback;
    
    // Get saved state
    const savedState = localStorage.getItem('filter_time_state');
    if (savedState) {
        const state = JSON.parse(savedState);
        globalFilters.time_filter_display = state.time_filter_display || 'this_month';
        globalFilters.date_from = state.date_from || '';
        globalFilters.date_to = state.date_to || '';
    } else {
        globalFilters.time_filter_display = 'this_month';
    }
    
    // Set initial state
    const timeRadios = document.querySelectorAll('input[name="time_filter"]');
    timeRadios.forEach(radio => {
        if (radio.value === globalFilters.time_filter_display) {
            radio.checked = true;
        }

        radio.addEventListener('change', function() {
            if (this.checked) {
                globalFilters.time_filter_display = this.value;

                if (this.value === 'custom') {
                    showDateRangePicker();
                } else {
                    hideDateRangePicker();
                    globalFilters.date_from = '';
                    globalFilters.date_to = '';
                }

                saveTimeFilterState();
                if (callback) callback(globalFilters);
            }
        });
    });
    
    // Initialize date range picker if custom is selected
    if (globalFilters.time_filter_display === 'custom') {
        showDateRangePicker();
    }
}

/**
 * Initialize status filter
 * @param {Array} statuses - Array of status values
 * @param {Function} callback - Function to call when filter changes
 */
function initFilterStatus(statuses, callback) {
    console.log('Initializing status filter...', statuses);
    
    filterCallbacks.statusFilter = callback;
    
    // Get saved state
    const savedState = localStorage.getItem('filter_status_state');
    if (savedState) {
        globalFilters.status = JSON.parse(savedState);
    } else {
        // Default to first two statuses
        globalFilters.status = statuses.slice(0, 2);
    }
    
    // Set initial state and bind events
    const statusCheckboxes = document.querySelectorAll('input[name="status[]"]');
    statusCheckboxes.forEach(checkbox => {
        if (globalFilters.status && globalFilters.status.includes(checkbox.value)) {
            checkbox.checked = true;
        }
        
        checkbox.addEventListener('change', function() {
            if (!globalFilters.status) globalFilters.status = [];
            
            if (this.checked) {
                if (!globalFilters.status.includes(this.value)) {
                    globalFilters.status.push(this.value);
                }
            } else {
                globalFilters.status = globalFilters.status.filter(s => s !== this.value);
            }
            
            localStorage.setItem('filter_status_state', JSON.stringify(globalFilters.status));
            if (callback) callback(globalFilters);
        });
    });
}

/**
 * Initialize creator filter
 * @param {String} apiUrl - URL to fetch creators
 * @param {Function} callback - Function to call when filter changes
 */
function initFilterCreators(apiUrl, callback) {
    console.log('Initializing creator filter...', apiUrl);
    
    filterCallbacks.creatorFilter = callback;
    
    const creatorSelect = document.getElementById('creator-filter');
    if (!creatorSelect) return;
    
    // Load creators via AJAX
    fetch(apiUrl)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                data.data.forEach(creator => {
                    const option = document.createElement('option');
                    option.value = creator.value;
                    option.textContent = creator.label;
                    creatorSelect.appendChild(option);
                });
                
                // Initialize Select2 if available
                if (typeof $ !== 'undefined' && $.fn.select2) {
                    $(creatorSelect).select2();
                }
            }
        })
        .catch(error => {
            console.error('Error loading creators:', error);
        });
    
    // Bind change event
    creatorSelect.addEventListener('change', function() {
        globalFilters.creator_id = this.value;
        if (callback) callback(globalFilters);
    });
}

/**
 * Initialize seller filter
 * @param {String} apiUrl - URL to fetch sellers
 * @param {Function} callback - Function to call when filter changes
 */
function initFilterSellers(apiUrl, callback) {
    console.log('Initializing seller filter...', apiUrl);
    
    filterCallbacks.sellerFilter = callback;
    
    const sellerSelect = document.getElementById('seller-filter');
    if (!sellerSelect) return;
    
    // Load sellers via AJAX
    fetch(apiUrl)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                data.data.forEach(seller => {
                    const option = document.createElement('option');
                    option.value = seller.value;
                    option.textContent = seller.label;
                    sellerSelect.appendChild(option);
                });
                
                // Initialize Select2 if available
                if (typeof $ !== 'undefined' && $.fn.select2) {
                    $(sellerSelect).select2();
                }
            }
        })
        .catch(error => {
            console.error('Error loading sellers:', error);
        });
    
    // Bind change event
    sellerSelect.addEventListener('change', function() {
        globalFilters.seller_id = this.value;
        if (callback) callback(globalFilters);
    });
}

/**
 * Show date range picker
 */
function showDateRangePicker() {
    // Create date range picker if it doesn't exist
    let dateRangeContainer = document.getElementById('date-range-container');
    if (!dateRangeContainer) {
        dateRangeContainer = document.createElement('div');
        dateRangeContainer.id = 'date-range-container';
        dateRangeContainer.className = 'mt-5';
        dateRangeContainer.innerHTML = `
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Từ ngày</label>
                    <input type="date" class="form-control form-control-solid" id="date-from" name="date_from" />
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Đến ngày</label>
                    <input type="date" class="form-control form-control-solid" id="date-to" name="date_to" />
                </div>
            </div>
        `;
        
        // Insert after time filter
        const timeFilterContainer = document.getElementById('time-filter-container');
        if (timeFilterContainer) {
            timeFilterContainer.appendChild(dateRangeContainer);
        }
        
        // Bind events
        const dateFromInput = document.getElementById('date-from');
        const dateToInput = document.getElementById('date-to');
        
        if (dateFromInput) {
            dateFromInput.addEventListener('change', function() {
                globalFilters.date_from = this.value;
                saveTimeFilterState();
                if (filterCallbacks.timeFilter) filterCallbacks.timeFilter(globalFilters);
            });
        }
        
        if (dateToInput) {
            dateToInput.addEventListener('change', function() {
                globalFilters.date_to = this.value;
                saveTimeFilterState();
                if (filterCallbacks.timeFilter) filterCallbacks.timeFilter(globalFilters);
            });
        }
    }
    
    // Set values if available
    if (globalFilters.date_from) {
        document.getElementById('date-from').value = globalFilters.date_from;
    }
    if (globalFilters.date_to) {
        document.getElementById('date-to').value = globalFilters.date_to;
    }
    
    dateRangeContainer.style.display = 'block';
}

/**
 * Hide date range picker
 */
function hideDateRangePicker() {
    const dateRangeContainer = document.getElementById('date-range-container');
    if (dateRangeContainer) {
        dateRangeContainer.style.display = 'none';
    }
}

/**
 * Save time filter state to localStorage
 */
function saveTimeFilterState() {
    const state = {
        time_filter_display: globalFilters.time_filter_display,
        date_from: globalFilters.date_from,
        date_to: globalFilters.date_to
    };
    localStorage.setItem('filter_time_state', JSON.stringify(state));
}

/**
 * Get current filter values
 * @returns {Object} Current filter values
 */
function getCurrentFilters() {
    return { ...globalFilters };
}
