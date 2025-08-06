"use strict";

/**
 * Example usage of KTColumnVisibility module
 * This file demonstrates how to use the column-visibility.js module in different pages
 */

// Example 1: Basic usage for a simple table
var ExampleBasicUsage = function() {
    var columnVisibility;
    
    var init = function() {
        // Define default visibility for your table columns
        var defaultVisibility = {
            0: true,  // ID column
            1: true,  // Name column
            2: true,  // Email column
            3: false, // Phone column (hidden by default)
            4: false, // Address column (hidden by default)
            5: true   // Actions column
        };
        
        // Initialize column visibility
        columnVisibility = KTColumnVisibility.init({
            storageKey: 'example_table_column_visibility', // Unique key for localStorage
            defaultVisibility: defaultVisibility,
            triggerSelector: '#example_column_visibility_trigger',
            panelSelector: '#example_column_visibility_panel',
            toggleSelector: '.example-column-toggle',
            tableSelector: '#example_table',
            onToggle: function(columnIndex, isVisible, columnVisibility) {
                console.log('Column', columnIndex, 'visibility changed to:', isVisible);
                // Add any custom logic here when column visibility changes
            }
        });
    };
    
    return {
        init: init,
        getVisibility: function() { return columnVisibility; }
    };
}();

// Example 2: Advanced usage with custom callbacks and data reloading
var ExampleAdvancedUsage = function() {
    var columnVisibility;
    var table;
    
    var loadTableData = function() {
        // Your AJAX data loading logic here
        console.log('Loading table data...');
        
        // After data is loaded, apply column visibility
        setTimeout(function() {
            if (columnVisibility && typeof KTColumnVisibility !== 'undefined') {
                KTColumnVisibility.apply({
                    tableSelector: '#advanced_table'
                }, columnVisibility);
            }
        }, 100);
    };
    
    var init = function() {
        table = document.querySelector('#advanced_table');
        if (!table) {
            console.error('Advanced table not found');
            return;
        }
        
        // Define default visibility
        var defaultVisibility = {
            0: true,  // Checkbox
            1: true,  // Product name
            2: true,  // SKU
            3: true,  // Price
            4: false, // Cost
            5: false, // Supplier
            6: true,  // Stock
            7: false, // Category
            8: true,  // Status
            9: true   // Actions
        };
        
        // Initialize column visibility with advanced options
        columnVisibility = KTColumnVisibility.init({
            storageKey: 'advanced_table_column_visibility',
            defaultVisibility: defaultVisibility,
            triggerSelector: '#advanced_column_visibility_trigger',
            panelSelector: '#advanced_column_visibility_panel',
            toggleSelector: '.advanced-column-toggle',
            tableSelector: '#advanced_table',
            onToggle: function(columnIndex, isVisible, columnVisibility) {
                console.log('Advanced table - Column', columnIndex, 'visibility changed to:', isVisible);
                
                // Custom logic: reload data when certain columns are toggled
                if (columnIndex === 4 || columnIndex === 5) { // Cost or Supplier columns
                    console.log('Reloading data due to sensitive column toggle');
                    loadTableData();
                }
                
                // Update any related UI elements
                updateColumnRelatedUI(columnIndex, isVisible);
            }
        });
        
        // Load initial data
        loadTableData();
    };
    
    var updateColumnRelatedUI = function(columnIndex, isVisible) {
        // Example: Update export options based on visible columns
        var exportButton = $('#export_button');
        if (exportButton.length) {
            // Update export button state or options
            console.log('Updating export options for column', columnIndex);
        }
    };
    
    return {
        init: init,
        reload: loadTableData,
        getVisibility: function() { return columnVisibility; },
        setColumnVisibility: function(columnIndex, isVisible) {
            if (columnVisibility && typeof KTColumnVisibility !== 'undefined') {
                KTColumnVisibility.setVisibility(
                    'advanced_table_column_visibility',
                    { tableSelector: '#advanced_table', toggleSelector: '.advanced-column-toggle' },
                    columnIndex,
                    isVisible,
                    columnVisibility
                );
            }
        }
    };
}();

// Example 3: Multiple tables on the same page
var ExampleMultipleTables = function() {
    var table1Visibility;
    var table2Visibility;
    
    var initTable1 = function() {
        var defaultVisibility = {
            0: true,  // ID
            1: true,  // Name
            2: false, // Description
            3: true   // Actions
        };
        
        table1Visibility = KTColumnVisibility.init({
            storageKey: 'table1_column_visibility',
            defaultVisibility: defaultVisibility,
            triggerSelector: '#table1_column_visibility_trigger',
            panelSelector: '#table1_column_visibility_panel',
            toggleSelector: '.table1-column-toggle',
            tableSelector: '#table1',
            onToggle: function(columnIndex, isVisible) {
                console.log('Table 1 - Column', columnIndex, 'visibility:', isVisible);
            }
        });
    };
    
    var initTable2 = function() {
        var defaultVisibility = {
            0: true,  // Code
            1: true,  // Title
            2: true,  // Date
            3: false, // Notes
            4: true   // Status
        };
        
        table2Visibility = KTColumnVisibility.init({
            storageKey: 'table2_column_visibility',
            defaultVisibility: defaultVisibility,
            triggerSelector: '#table2_column_visibility_trigger',
            panelSelector: '#table2_column_visibility_panel',
            toggleSelector: '.table2-column-toggle',
            tableSelector: '#table2',
            onToggle: function(columnIndex, isVisible) {
                console.log('Table 2 - Column', columnIndex, 'visibility:', isVisible);
            }
        });
    };
    
    var init = function() {
        initTable1();
        initTable2();
    };
    
    return {
        init: init,
        getTable1Visibility: function() { return table1Visibility; },
        getTable2Visibility: function() { return table2Visibility; }
    };
}();

// Example 4: Integration with DataTables
var ExampleDataTablesIntegration = function() {
    var columnVisibility;
    var dataTable;
    
    var init = function() {
        var defaultVisibility = {
            0: true,  // ID
            1: true,  // Name
            2: true,  // Email
            3: false, // Phone
            4: false, // Address
            5: true   // Actions
        };
        
        // Initialize DataTables first
        dataTable = $('#datatables_example').DataTable({
            // Your DataTables configuration
            columnDefs: [
                { targets: [3, 4], visible: false } // Hide columns by default
            ]
        });
        
        // Then initialize column visibility
        columnVisibility = KTColumnVisibility.init({
            storageKey: 'datatables_column_visibility',
            defaultVisibility: defaultVisibility,
            triggerSelector: '#datatables_column_visibility_trigger',
            panelSelector: '#datatables_column_visibility_panel',
            toggleSelector: '.datatables-column-toggle',
            tableSelector: '#datatables_example',
            onToggle: function(columnIndex, isVisible) {
                console.log('DataTables - Column', columnIndex, 'visibility:', isVisible);
                
                // Update DataTables column visibility
                if (dataTable) {
                    dataTable.column(columnIndex).visible(isVisible);
                }
            }
        });
    };
    
    return {
        init: init,
        getDataTable: function() { return dataTable; },
        getVisibility: function() { return columnVisibility; }
    };
}();

// Usage instructions:
/*
1. Include column-visibility.js before your page-specific JS file:
   <script src="{{ asset('admin-assets/globals/column-visibility.js') }}"></script>
   <script src="{{ asset('admin-assets/js/your-page.js') }}"></script>

2. In your HTML, create the trigger button and panel:
   <button id="your_column_visibility_trigger" class="btn btn-sm btn-light">
       <i class="fas fa-columns"></i> Columns
   </button>
   
   <div id="your_column_visibility_panel" class="column-visibility-panel">
       <div class="panel-content">
           <div class="panel-header">
               <h6 class="mb-0">Column Visibility</h6>
           </div>
           <div class="panel-body">
               <div class="form-check">
                   <input class="form-check-input your-column-toggle" type="checkbox" value="0" id="col_0">
                   <label class="form-check-label" for="col_0">Column 1</label>
               </div>
               <!-- Add more checkboxes for each column -->
           </div>
       </div>
   </div>

3. Initialize in your page JS:
   $(document).ready(function() {
       ExampleBasicUsage.init();
   });
*/
