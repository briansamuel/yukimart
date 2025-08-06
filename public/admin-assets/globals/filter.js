"use strict";

/**
 * Global Filter System
 * Shared filter functionality for all pages (invoices, payments, orders, etc.)
 */
var KTGlobalFilter = function () {
    // Shared variables
    var timeOptionsPanel;
    var loadDataCallback = null;
    var filterForm;
    var initialized = false;
    var allDataLoaded = false;

    /**
     * Utility function to get URL parameter
     * @param {string} name - Parameter name
     * @returns {string|null} Parameter value or null if not found
     */
    var getUrlParameter = function(name) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(name);
    };

    /**
     * Set the callback function for loading data
     * @param {Function} callback - Function to call when data needs to be reloaded
     */
    var setLoadDataCallback = function(callback) {
        loadDataCallback = callback;
        console.log('Load data callback set:', typeof callback);
    };

    /**
     * Call the load data callback if it exists
     */
    var callLoadDataCallback = function() {
        if (typeof loadDataCallback === 'function') {
            console.log('Calling load data callback...');
            loadDataCallback();
        } else {
            console.warn('Load data callback not set or not a function');
        }
    };

    /**
     * Load filter data from server and populate select element
     * @param {string} url - API endpoint URL
     * @param {jQuery} selectElement - jQuery select element to populate
     * @param {string} placeholder - Placeholder text for the select
     */
    var loadFilterData = function(url, selectElement, placeholder) {
        if (!selectElement || selectElement.length === 0) {
            console.warn('Select element not found for URL:', url);
            return;
        }

        console.log('Loading filter data from:', url);

        // Show loading state
        selectElement.prop('disabled', true);
        selectElement.html('<option value="">Đang tải...</option>');

        // Make AJAX request
        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Filter data loaded successfully:', data);

                if (data.success && data.data) {
                    // Clear existing options
                    selectElement.empty();

                    // Add placeholder option
                    selectElement.append(`<option value="">${placeholder}</option>`);

                    // Add data options
                    data.data.forEach(item => {
                        const option = $('<option></option>')
                            .attr('value', item.value || item.id)
                            .text(item.label || item.name || item.text);
                        selectElement.append(option);
                    });

                    // Re-enable select and trigger Select2 if available
                    selectElement.prop('disabled', false);

                    // Initialize or refresh Select2 if available
                    if (selectElement.hasClass('select2-hidden-accessible')) {
                        selectElement.select2('destroy');
                    }

                    if (selectElement.data('kt-select2') === 'true' || selectElement.hasClass('form-select')) {
                        selectElement.select2({
                            placeholder: placeholder,
                            allowClear: true
                        });
                    }

                    console.log(`Filter data loaded: ${data.data.length} items for ${placeholder}`);
                } else {
                    throw new Error(data.message || 'Invalid response format');
                }
            })
            .catch(error => {
                console.error('Error loading filter data:', error);

                // Show error state
                selectElement.empty();
                selectElement.append(`<option value="">Lỗi tải dữ liệu</option>`);
                selectElement.prop('disabled', false);

                // Show user-friendly error message
                if (typeof toastr !== 'undefined') {
                    toastr.error(`Không thể tải dữ liệu ${placeholder.toLowerCase()}: ${error.message}`);
                }
            });
    };

    /**
     * Load all filter data at once from server
     * @param {string} formSelector - CSS selector for the filter form
     * @param {string} module - Module name (invoices, orders, payments, all)
     */
    var loadAllFilterData = function(formSelector, module = 'all') {
        console.log('Loading all filter data for module:', module);

        const url = `/admin/filters/all?module=${module}`;

        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('All filter data loaded successfully:', data);

                if (data.success && data.data) {
                    const filterData = data.data;

                    // Populate creators filter (Select2)
                    if (filterData.creators && filterData.creators.length > 0) {
                        const creatorSelect = $(formSelector + ' select[name="creator_id"]');
                        populateSelectElement(creatorSelect, filterData.creators, 'Chọn người tạo');
                    }

                    // Populate sellers filter (Select2)
                    if (filterData.sellers && filterData.sellers.length > 0) {
                        const sellerSelect = $(formSelector + ' select[name="seller_id"]');
                        populateSelectElement(sellerSelect, filterData.sellers, 'Chọn người bán');
                    }

                    // Populate channels filter (Select2)
                    if (filterData.channels && filterData.channels.length > 0) {
                        const channelSelect = $(formSelector + ' select[name="sales_channel"]');
                        populateSelectElement(channelSelect, filterData.channels, 'Chọn kênh bán hàng');
                    }

                    // Populate payment methods filter
                    if (filterData.payment_methods && filterData.payment_methods.length > 0) {
                        const paymentMethodSelect = $(formSelector + ' select[name="payment_method"]');
                        populateSelectElement(paymentMethodSelect, filterData.payment_methods, 'Chọn phương thức thanh toán');
                    }

                    console.log('All filter data populated successfully');
                    allDataLoaded = true; // Set flag to indicate all data has been loaded

                    // Mark that data has been loaded for individual filter functions
                    window.filterDataLoaded = {
                        creators: true,
                        sellers: true,
                        paymentMethods: true
                    };
                } else {
                    throw new Error(data.message || 'Invalid response format');
                }
            })
            .catch(error => {
                console.error('Error loading all filter data:', error);
                allDataLoaded = false; // Reset flag on error

                // Show user-friendly error message
                if (typeof toastr !== 'undefined') {
                    toastr.error(`Không thể tải dữ liệu filter: ${error.message}`);
                }
            });
    };

    /**
     * Helper function to populate select element with data
     * @param {jQuery} selectElement - jQuery select element to populate
     * @param {Array} data - Array of data items
     * @param {string} placeholder - Placeholder text
     */
    var populateSelectElement = function(selectElement, data, placeholder) {
        if (!selectElement || selectElement.length === 0) {
            return;
        }

        // Clear existing options
        selectElement.empty();

        // Add placeholder option
        selectElement.append(`<option value="">${placeholder}</option>`);

        // Add data options
        data.forEach(item => {
            const option = $('<option></option>')
                .attr('value', item.value || item.id)
                .text(item.label || item.name || item.text);
            selectElement.append(option);
        });

        // Re-enable select
        selectElement.prop('disabled', false);

        // Initialize or refresh Select2 if available
        if (selectElement.hasClass('select2-hidden-accessible')) {
            selectElement.select2('destroy');
        }

        if (selectElement.data('kt-select2') === 'true' || selectElement.hasClass('form-select')) {
            selectElement.select2({
                placeholder: placeholder,
                allowClear: true
            });
        }
    };

    /**
     * Initialize time filter with dropdown panel
     * @param {string} formSelector - CSS selector for the filter form (e.g., '#kt_invoice_filter_form')
     */
    var initTimeFilter = function(formSelector) {
        console.log('Initializing global time filter for form:', formSelector);

        timeOptionsPanel = document.getElementById('time_options_panel');
        var trigger = document.getElementById('time_filter_trigger');
        var closeBtn = document.getElementById('close_time_panel');

        console.log('Time filter elements found:', {
            timeOptionsPanel: !!timeOptionsPanel,
            trigger: !!trigger,
            closeBtn: !!closeBtn,
            formSelector: formSelector
        });

        if (trigger && timeOptionsPanel) {
            // Show time options panel
            trigger.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                console.log('Time filter trigger clicked');
                
                // Close column visibility panel first
                $('#column_visibility_panel').removeClass('show');
                $('#column_visibility_trigger').removeClass('active');
                
                showTimePanel();
            });

            // Close panel
            if (closeBtn) {
                closeBtn.addEventListener('click', function() {
                    hideTimePanel();
                });
            }

            // Handle time option selection
            $(timeOptionsPanel).on('click', '.time-option', function(e) {
                e.preventDefault();
                var value = $(this).data('value');
                var text = $(this).text();

                console.log('Time option selected:', value, text);

                // Update the trigger text
                $('#time_filter_trigger span').text(text);

                // Ensure the main time filter radio button is checked
                $('#time_this_month').prop('checked', true);

                // Create or update hidden input for time_filter value
                var hiddenInput = $('#time_filter');
                if (hiddenInput.length === 0) {
                    // Create hidden input if it doesn't exist
                    $('<input type="hidden" id="time_filter" name="time_filter" />').appendTo(formSelector);
                    hiddenInput = $('#time_filter');
                }
                hiddenInput.val(value);

                console.log('Hidden time_filter input updated to:', value);

                // Remove active class from all options
                $('.time-option').removeClass('active btn-primary').addClass('btn-light-primary');

                // Add active class to selected option
                $(this).removeClass('btn-light-primary').addClass('btn-primary active');

                // Close panel with animation
                hideTimePanel();

                // Reload data after animation
                setTimeout(function() {
                    callLoadDataCallback();
                }, 300);
            });

            // Close panel when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('#time_filter_trigger, #time_options_panel').length) {
                    hideTimePanel();
                }
            });
        }

        // Handle custom time filter radio button
        $('#time_custom').on('change', function() {
            if ($(this).is(':checked')) {
                $('#custom_date_range').slideDown(300);
                // Don't update trigger text - keep it as "Tháng này"

                // Update hidden input for custom time filter
                var hiddenInput = $('#time_filter');
                if (hiddenInput.length === 0) {
                    $('<input type="hidden" id="time_filter" name="time_filter" />').appendTo(formSelector);
                    hiddenInput = $('#time_filter');
                }
                hiddenInput.val('custom');

                // Set default date range if not already set
                setDefaultCustomDateRange();

                console.log('Custom time filter selected, hidden input updated to: custom');

                // Reload data
                callLoadDataCallback();
            }
        });

        // Handle other time filter radio buttons
        $('input[name="time_filter_display"]:not(#time_custom)').on('change', function() {
            if ($(this).is(':checked')) {
                $('#custom_date_range').slideUp(300);

                // Update hidden time_filter input to match the selected value
                var selectedValue = $(this).val();
                $('#time_filter').val(selectedValue);
                console.log('Time filter updated to:', selectedValue);

                // Reload data when switching back from custom filter
                callLoadDataCallback();
            }
        });

        // Handle date range changes
        $('#date_from, #date_to').on('change', function() {
            if ($('#time_custom').is(':checked')) {
                console.log('Date range changed for custom filter');
                // Save custom date range state
                saveCustomDateRangeState();
                // Reload data when date range changes
                callLoadDataCallback();
            }
        });

        // Load saved custom date range state on initialization
        loadCustomDateRangeState();

        console.log('Global time filter initialized successfully');
    };

    /**
     * Set default custom date range (7 days ago to today)
     */
    var setDefaultCustomDateRange = function() {
        var dateFromInput = $('#date_from');
        var dateToInput = $('#date_to');

        // Only set defaults if both fields are empty
        if (!dateFromInput.val() && !dateToInput.val()) {
            var today = new Date();
            var sevenDaysAgo = new Date();
            sevenDaysAgo.setDate(today.getDate() - 7);

            // Format dates as YYYY-MM-DD for date input
            var dateFromValue = sevenDaysAgo.toISOString().split('T')[0];
            var dateToValue = today.toISOString().split('T')[0];

            dateFromInput.val(dateFromValue);
            dateToInput.val(dateToValue);

            console.log('Default custom date range set:', dateFromValue, 'to', dateToValue);

            // Save the default state
            saveCustomDateRangeState();
        }
    };

    /**
     * Save custom date range state to localStorage
     */
    var saveCustomDateRangeState = function() {
        var dateFrom = $('#date_from').val();
        var dateTo = $('#date_to').val();

        if (dateFrom || dateTo) {
            var state = {
                date_from: dateFrom,
                date_to: dateTo,
                timestamp: new Date().getTime()
            };

            try {
                localStorage.setItem('global_custom_date_range', JSON.stringify(state));
                console.log('Custom date range state saved:', state);
            } catch (e) {
                console.warn('Failed to save custom date range state:', e);
            }
        }
    };

    /**
     * Load custom date range state from localStorage
     */
    var loadCustomDateRangeState = function() {
        try {
            var saved = localStorage.getItem('global_custom_date_range');
            if (saved) {
                var state = JSON.parse(saved);

                // Check if state is not too old (optional: expire after 30 days)
                var now = new Date().getTime();
                var thirtyDaysInMs = 30 * 24 * 60 * 60 * 1000;

                if (state.timestamp && (now - state.timestamp) < thirtyDaysInMs) {
                    if (state.date_from) {
                        $('#date_from').val(state.date_from);
                    }
                    if (state.date_to) {
                        $('#date_to').val(state.date_to);
                    }

                    console.log('Custom date range state loaded:', state);
                    return true;
                }
            }
        } catch (e) {
            console.warn('Failed to load custom date range state:', e);
        }

        return false;
    };

    /**
     * Initialize status filter checkboxes
     * @param {string} formSelector - CSS selector for the filter form
     */
    var initFilterStatus = function(formSelector) {
        console.log('Initializing status filter for form:', formSelector);
        
        // Handle status checkbox changes
        $(formSelector + ' input[name="status[]"]').on('change', function() {
            console.log('Status filter changed:', $(this).val(), $(this).is(':checked'));
            callLoadDataCallback();
        });
        
        console.log('Status filter initialized successfully');
    };

    /**
     * Initialize creators filter (Select2)
     * @param {string} formSelector - CSS selector for the filter form
     */
    var initFilterCreators = function(formSelector) {
        console.log('Initializing creators filter for form:', formSelector);

        const creatorSelect = $(formSelector + ' select[name="creator_id"]');

        // Load creators data from server only if not already loaded by loadAllFilterData
        if (!allDataLoaded && !window.filterDataLoaded?.creators && creatorSelect.length > 0) {
            loadFilterData('/admin/filters/creators?type=all', creatorSelect, 'Chọn người tạo');
        }

        // Handle creators select change
        creatorSelect.on('change', function() {
            console.log('Creators filter changed:', $(this).val());
            callLoadDataCallback();
        });

        console.log('Creators filter initialized successfully');
    };

    /**
     * Initialize sellers filter (Select2)
     * @param {string} formSelector - CSS selector for the filter form
     */
    var initFilterSellers = function(formSelector) {
        console.log('Initializing sellers filter for form:', formSelector);

        const sellerSelect = $(formSelector + ' select[name="seller_id"]');

        // Load sellers data from server only if not already loaded by loadAllFilterData
        if (!allDataLoaded && sellerSelect.length > 0) {
            loadFilterData('/admin/filters/sellers?type=all', sellerSelect, 'Chọn người bán');
        }

        // Handle sellers select change
        sellerSelect.on('change', function() {
            console.log('Sellers filter changed:', $(this).val());
            callLoadDataCallback();
        });

        console.log('Sellers filter initialized successfully');
    };

    /**
     * Initialize sale channels filter
     * @param {string} formSelector - CSS selector for the filter form
     */
    var initFilterSaleChannels = function(formSelector) {
        console.log('Initializing sale channels filter for form:', formSelector);

        const channelSelect = $(formSelector + ' select[name="sales_channel"]');

        // Load sale channels data from server if select element exists and not already loaded
        if (!allDataLoaded && channelSelect.length > 0) {
            loadFilterData('/admin/filters/channels?type=all', channelSelect, 'Chọn kênh bán hàng');
        }

        // Handle sale channels input change
        $(formSelector + ' input[name="sale_channel"]').on('input change', function() {
            console.log('Sale channels filter changed:', $(this).val());
            // Add debounce for input fields
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(function() {
                callLoadDataCallback();
            }, 500);
        });

        // Handle sale channels select change
        channelSelect.on('change', function() {
            console.log('Sale channels select changed:', $(this).val());
            callLoadDataCallback();
        });

        console.log('Sale channels filter initialized successfully');
    };

    /**
     * Initialize payment methods filter (Select2)
     * @param {string} formSelector - CSS selector for the filter form
     */
    var initFilterPaymentMethods = function(formSelector) {
        console.log('Initializing payment methods filter for form:', formSelector);

        const paymentMethodSelect = $(formSelector + ' select[name="payment_method"]');

        // Load payment methods data from server only if not already loaded by loadAllFilterData
        if (!allDataLoaded && !window.filterDataLoaded?.paymentMethods && paymentMethodSelect.length > 0) {
            loadFilterData('/admin/filters/payment-methods?type=all', paymentMethodSelect, 'Chọn phương thức thanh toán');
        }

        // Handle payment methods select change
        paymentMethodSelect.on('change', function() {
            console.log('Payment methods filter changed:', $(this).val());
            callLoadDataCallback();
        });

        console.log('Payment methods filter initialized successfully');
    };

    /**
     * Show time panel with animation
     */
    var showTimePanel = function() {
        if (timeOptionsPanel) {
            timeOptionsPanel.classList.remove('hiding');
            timeOptionsPanel.classList.add('show');
            console.log('Time panel shown');
        }
    };

    /**
     * Hide time panel with animation
     */
    var hideTimePanel = function() {
        if (timeOptionsPanel) {
            timeOptionsPanel.classList.add('hiding');
            setTimeout(function() {
                timeOptionsPanel.classList.remove('show', 'hiding');
            }, 300);
            console.log('Time panel hidden');
        }
    };

    /**
     * Initialize all filters for a specific form
     * @param {string} formSelector - CSS selector for the filter form
     * @param {Function} loadCallback - Function to call when data needs to be reloaded
     * @param {Object} options - Options for which filters to initialize
     */
    var initAllFilters = function(formSelector, loadCallback, options = {}) {
        console.log('Initializing all filters for form:', formSelector);

        // Set the load data callback
        setLoadDataCallback(loadCallback);

        // Store form selector
        filterForm = formSelector;

        // Initialize default time filter input
        if ($(formSelector + ' #time_filter').length === 0) {
            // Check if URL has code or Code parameter (check lowercase first)
            var codeParam = getUrlParameter('code') || getUrlParameter('Code');
            var defaultTimeFilter = codeParam ? 'all_time' : 'this_month'; // Use "Toàn thời gian" if code param exists

            $('<input type="hidden" id="time_filter" name="time_filter" />').val(defaultTimeFilter).appendTo(formSelector);
            console.log('Hidden time_filter input initialized with default value:', defaultTimeFilter, 'Code param:', codeParam);

            // Update trigger text if Code param exists
            if (codeParam) {
                setTimeout(function() {
                    $('#time_filter_trigger span').text('Toàn thời gian');
                    $('.time-option').removeClass('active btn-primary').addClass('btn-light-primary');
                    //$('.time-option[data-value="all_day"]').removeClass('btn-light-primary').addClass('btn-primary active');
                }, 100);
            }
        }

        // Initialize filters first (so Tagify instances are created)
        if (options.timeFilter !== false) {
            initTimeFilter(formSelector);
        }

        if (options.statusFilter !== false) {
            initFilterStatus(formSelector);
        }

        if (options.creatorsFilter !== false) {
            initFilterCreators(formSelector);
        }

        if (options.sellersFilter !== false) {
            initFilterSellers(formSelector);
        }

        if (options.saleChannelsFilter !== false) {
            initFilterSaleChannels(formSelector);
        }

        if (options.paymentMethodsFilter !== false) {
            initFilterPaymentMethods(formSelector);
        }

        // Load all filter data after filters are initialized
        if (options.loadAllData !== false) {
            const module = options.module || 'all';
            console.log('About to call loadAllFilterData with module:', module);
            loadAllFilterData(formSelector, module);
        } else {
            console.log('loadAllData is disabled, skipping bulk data loading');
        }

        initialized = true;
        console.log('All filters initialized successfully for:', formSelector);
    };

    // Public methods
    return {
        // Individual filter initializers
        initTimeFilter: initTimeFilter,
        initFilterStatus: initFilterStatus,
        initFilterCreators: initFilterCreators,
        initFilterSellers: initFilterSellers,
        initFilterSaleChannels: initFilterSaleChannels,
        initFilterPaymentMethods: initFilterPaymentMethods,

        // Utility methods
        setLoadDataCallback: setLoadDataCallback,
        callLoadDataCallback: callLoadDataCallback,
        showTimePanel: showTimePanel,
        hideTimePanel: hideTimePanel,

        // Data loading methods
        loadFilterData: loadFilterData,
        loadAllFilterData: loadAllFilterData,

        // Custom date range methods
        setDefaultCustomDateRange: setDefaultCustomDateRange,
        saveCustomDateRangeState: saveCustomDateRangeState,
        loadCustomDateRangeState: loadCustomDateRangeState,

        // Main initializer
        initAllFilters: initAllFilters,

        // State
        isInitialized: function() {
            return initialized;
        },
        isAllDataLoaded: function() {
            return allDataLoaded;
        }
    };
}();

// Make it globally available
window.KTGlobalFilter = KTGlobalFilter;
