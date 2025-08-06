"use strict";

/**
 * Global Column Visibility Manager
 * Reusable component for managing table column visibility across different pages
 */
var KTColumnVisibility = function() {
    
    /**
     * Initialize column visibility for a specific page
     * @param {Object} config Configuration object
     * @param {string} config.storageKey - localStorage key for saving state
     * @param {Object} config.defaultVisibility - Default column visibility state
     * @param {string} config.triggerSelector - Selector for visibility trigger button
     * @param {string} config.panelSelector - Selector for visibility panel
     * @param {string} config.toggleSelector - Selector for column toggle checkboxes
     * @param {string} config.tableSelector - Selector for the table
     * @param {Function} config.onToggle - Callback function when column visibility changes
     */
    var initColumnVisibility = function(config) {
        console.log('Initializing column visibility with config:', config);
        
        // Validate required config
        if (!config.storageKey || !config.defaultVisibility) {
            console.error('Column visibility config missing required properties');
            return;
        }
        
        // Set default selectors if not provided
        var settings = {
            triggerSelector: config.triggerSelector || '#column_visibility_trigger',
            panelSelector: config.panelSelector || '#column_visibility_panel',
            toggleSelector: config.toggleSelector || '.column-toggle',
            tableSelector: config.tableSelector || 'table',
            onToggle: config.onToggle || function() {},
            ...config
        };
        
        // Load column visibility state
        var columnVisibility = loadColumnVisibility(settings.storageKey, settings.defaultVisibility);
        
        // Initialize panel toggle
        initPanelToggle(settings, columnVisibility);
        
        // Initialize checkboxes
        initCheckboxes(settings, columnVisibility);
        
        // Apply initial visibility
        applyColumnVisibility(settings, columnVisibility);
        
        // Return visibility state for external use
        return columnVisibility;
    };
    
    /**
     * Load column visibility state from localStorage
     * @param {string} storageKey - localStorage key
     * @param {Object} defaultVisibility - Default visibility state
     * @returns {Object} Column visibility state
     */
    var loadColumnVisibility = function(storageKey, defaultVisibility) {
        try {
            var saved = localStorage.getItem(storageKey);
            if (saved) {
                var parsed = JSON.parse(saved);
                // Merge with defaults to ensure all columns are defined
                return Object.assign({}, defaultVisibility, parsed);
            }
        } catch (e) {
            console.warn('Failed to load column visibility from localStorage:', e);
        }
        
        return defaultVisibility;
    };
    
    /**
     * Save column visibility state to localStorage
     * @param {string} storageKey - localStorage key
     * @param {Object} columnVisibility - Column visibility state
     */
    var saveColumnVisibility = function(storageKey, columnVisibility) {
        try {
            localStorage.setItem(storageKey, JSON.stringify(columnVisibility));
            console.log('Column visibility saved to localStorage');
        } catch (e) {
            console.warn('Failed to save column visibility to localStorage:', e);
        }
    };
    
    /**
     * Initialize panel toggle functionality
     * @param {Object} settings - Configuration settings
     * @param {Object} columnVisibility - Column visibility state
     */
    var initPanelToggle = function(settings, columnVisibility) {
        var trigger = $(settings.triggerSelector);
        var panel = $(settings.panelSelector);
        
        console.log('Initializing panel toggle - Trigger found:', trigger.length, 'Panel found:', panel.length);
        
        if (trigger.length === 0 || panel.length === 0) {
            console.warn('Column visibility trigger or panel not found');
            return;
        }
        
        // Panel toggle event
        trigger.off('click.columnVisibility').on('click.columnVisibility', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Column visibility trigger clicked');
            
            if (panel.hasClass('show')) {
                panel.removeClass('show');
                trigger.removeClass('active');
                console.log('Panel hidden');
            } else {
                panel.addClass('show');
                trigger.addClass('active');
                console.log('Panel shown');
                
                // Bind toggle events when panel is shown
                bindToggleEvents(settings, columnVisibility);
            }
        });
        
        // Close panel when clicking outside
        $(document).off('click.columnVisibility').on('click.columnVisibility', function(e) {
            if (panel.hasClass('show') && !$(e.target).closest(settings.panelSelector + ', ' + settings.triggerSelector).length) {
                panel.removeClass('show');
                trigger.removeClass('active');
                console.log('Panel hidden by outside click');
            }
        });
    };
    
    /**
     * Initialize checkboxes based on current visibility state
     * @param {Object} settings - Configuration settings
     * @param {Object} columnVisibility - Column visibility state
     */
    var initCheckboxes = function(settings, columnVisibility) {
        Object.keys(columnVisibility).forEach(function(columnIndex) {
            var checkbox = $(settings.toggleSelector + '[value="' + columnIndex + '"]');
            if (checkbox.length) {
                checkbox.prop('checked', columnVisibility[columnIndex]);
                console.log('Set checkbox for column', columnIndex, 'to', columnVisibility[columnIndex]);
            }
        });
    };
    
    /**
     * Bind toggle events to checkboxes
     * @param {Object} settings - Configuration settings
     * @param {Object} columnVisibility - Column visibility state
     */
    var bindToggleEvents = function(settings, columnVisibility) {
        console.log('Binding column toggle events...');
        
        var toggles = $(settings.toggleSelector);
        console.log('Found', toggles.length, 'column toggle elements');
        
        if (toggles.length === 0) {
            console.warn('No column toggle elements found');
            return;
        }
        
        // Remove existing handlers
        toggles.off('change.columnVisibility');
        
        // Bind change event
        toggles.on('change.columnVisibility', function() {
            var columnIndex = parseInt($(this).val());
            var isVisible = $(this).is(':checked');
            
            console.log('Column toggle changed - Column:', columnIndex, 'Visible:', isVisible);
            
            // Update visibility state
            columnVisibility[columnIndex] = isVisible;
            
            // Save to localStorage
            saveColumnVisibility(settings.storageKey, columnVisibility);
            
            // Apply visibility change
            toggleSingleColumn(settings, columnIndex, isVisible);
            
            // Call callback if provided
            if (typeof settings.onToggle === 'function') {
                settings.onToggle(columnIndex, isVisible, columnVisibility);
            }
        });
        
        console.log('Column toggle events bound successfully');
    };
    
    /**
     * Apply column visibility to table headers and cells
     * @param {Object} settings - Configuration settings
     * @param {Object} columnVisibility - Column visibility state
     */
    var applyColumnVisibility = function(settings, columnVisibility) {
        console.log('Applying column visibility...');
        
        Object.keys(columnVisibility).forEach(function(columnIndex) {
            var isVisible = columnVisibility[columnIndex];
            toggleSingleColumn(settings, parseInt(columnIndex), isVisible);
        });
        
        console.log('Column visibility applied');
    };
    
    /**
     * Toggle visibility of a single column
     * @param {Object} settings - Configuration settings
     * @param {number} columnIndex - Column index (0-based)
     * @param {boolean} isVisible - Whether column should be visible
     */
    var toggleSingleColumn = function(settings, columnIndex, isVisible) {
        var columnSelector = 'th:nth-child(' + (columnIndex + 1) + '), td:nth-child(' + (columnIndex + 1) + ')';
        var table = $(settings.tableSelector);
        
        if (table.length === 0) {
            console.warn('Table not found with selector:', settings.tableSelector);
            return;
        }
        
        var elements = table.find(columnSelector);
        
        if (isVisible) {
            elements.show();
        } else {
            elements.hide();
        }
        
        console.log('Toggled column', columnIndex, 'visibility to:', isVisible, '- Elements affected:', elements.length);
    };
    
    /**
     * Update table headers based on column visibility
     * @param {Object} settings - Configuration settings
     * @param {Object} columnVisibility - Column visibility state
     */
    var updateTableHeaders = function(settings, columnVisibility) {
        console.log('Updating table headers...');
        
        var table = $(settings.tableSelector);
        if (table.length === 0) {
            console.warn('Table not found with selector:', settings.tableSelector);
            return;
        }
        
        table.find('thead th').each(function(index) {
            if (columnVisibility.hasOwnProperty(index)) {
                $(this).toggle(columnVisibility[index]);
                console.log('Header column', index, 'visibility:', columnVisibility[index]);
            }
        });
        
        console.log('Table headers updated');
    };
    
    /**
     * Get current column visibility state
     * @param {string} storageKey - localStorage key
     * @param {Object} defaultVisibility - Default visibility state
     * @returns {Object} Current column visibility state
     */
    var getColumnVisibility = function(storageKey, defaultVisibility) {
        return loadColumnVisibility(storageKey, defaultVisibility);
    };
    
    /**
     * Set column visibility for a specific column
     * @param {string} storageKey - localStorage key
     * @param {Object} settings - Configuration settings
     * @param {number} columnIndex - Column index
     * @param {boolean} isVisible - Whether column should be visible
     * @param {Object} columnVisibility - Current column visibility state
     */
    var setColumnVisibility = function(storageKey, settings, columnIndex, isVisible, columnVisibility) {
        if (columnVisibility.hasOwnProperty(columnIndex)) {
            columnVisibility[columnIndex] = isVisible;
            saveColumnVisibility(storageKey, columnVisibility);
            toggleSingleColumn(settings, columnIndex, isVisible);
            
            // Update checkbox state
            var checkbox = $(settings.toggleSelector + '[value="' + columnIndex + '"]');
            if (checkbox.length) {
                checkbox.prop('checked', isVisible);
            }
            
            console.log('Column', columnIndex, 'visibility set to:', isVisible);
        }
    };
    
    // Public API
    return {
        init: initColumnVisibility,
        apply: applyColumnVisibility,
        updateHeaders: updateTableHeaders,
        getVisibility: getColumnVisibility,
        setVisibility: setColumnVisibility,
        toggleColumn: toggleSingleColumn,
        saveState: saveColumnVisibility,
        loadState: loadColumnVisibility
    };
}();

// Make it globally available
window.KTColumnVisibility = KTColumnVisibility;
