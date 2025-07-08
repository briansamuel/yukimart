<!--begin::Stock Status Filter-->
<div class="w-100 mw-150px me-3 position-relative">
    <!--begin::Select2-->
    <select class="form-select form-select-solid" data-control="select2" data-hide-search="false"
        data-placeholder="Stock Status" data-kt-products-table-filter="stock_status" id="kt_product_stock_status">
        <option value="">All Stock Status</option>
        <option value="in_stock">
            <i class="fas fa-check-circle text-success me-2"></i>In Stock
        </option>
        <option value="low_stock">
            <i class="fas fa-exclamation-triangle text-warning me-2"></i>Low Stock
        </option>
        <option value="out_of_stock">
            <i class="fas fa-times-circle text-danger me-2"></i>Out of Stock
        </option>
    </select>
    <!--end::Select2-->

    <!--begin::Clear Button-->
    <button type="button" class="btn btn-sm btn-icon btn-light position-absolute"
            id="kt_clear_stock_status"
            style="right: 35px; top: 50%; transform: translateY(-50%); z-index: 10; display: none;"
            title="Clear stock status filter">
        <i class="fas fa-times fs-7"></i>
    </button>
    <!--end::Clear Button-->
</div>
<!--end::Stock Status Filter-->

<style>
/* Custom styling for stock status options */
.select2-container--default .select2-results__option[data-select2-id*="in_stock"] {
    color: #50cd89;
}

.select2-container--default .select2-results__option[data-select2-id*="low_stock"] {
    color: #ffc700;
}

.select2-container--default .select2-results__option[data-select2-id*="out_of_stock"] {
    color: #f1416c;
}

/* Selected option styling */
.select2-container--default .select2-selection__rendered[title*="In Stock"] {
    color: #50cd89 !important;
    font-weight: 600;
}

.select2-container--default .select2-selection__rendered[title*="Low Stock"] {
    color: #ffc700 !important;
    font-weight: 600;
}

.select2-container--default .select2-selection__rendered[title*="Out of Stock"] {
    color: #f1416c !important;
    font-weight: 600;
}
</style>

<script>
// Initialize stock status filter with custom formatting
document.addEventListener('DOMContentLoaded', function() {
    if (typeof $ !== 'undefined' && $.fn.select2) {
        const stockStatusSelect = $('#kt_product_stock_status');
        const clearButton = $('#kt_clear_stock_status');

        stockStatusSelect.select2({
            templateResult: function(option) {
                if (!option.id) {
                    return option.text;
                }
                
                var iconClass = '';
                var colorClass = '';
                
                switch(option.id) {
                    case 'in_stock':
                        iconClass = 'fas fa-check-circle';
                        colorClass = 'text-success';
                        break;
                    case 'low_stock':
                        iconClass = 'fas fa-exclamation-triangle';
                        colorClass = 'text-warning';
                        break;
                    case 'out_of_stock':
                        iconClass = 'fas fa-times-circle';
                        colorClass = 'text-danger';
                        break;
                    default:
                        return option.text;
                }
                
                return $('<span><i class="' + iconClass + ' ' + colorClass + ' me-2"></i>' + option.text + '</span>');
            },
            templateSelection: function(option) {
                if (!option.id) {
                    return option.text;
                }
                
                var iconClass = '';
                var colorClass = '';
                
                switch(option.id) {
                    case 'in_stock':
                        iconClass = 'fas fa-check-circle';
                        colorClass = 'text-success';
                        break;
                    case 'low_stock':
                        iconClass = 'fas fa-exclamation-triangle';
                        colorClass = 'text-warning';
                        break;
                    case 'out_of_stock':
                        iconClass = 'fas fa-times-circle';
                        colorClass = 'text-danger';
                        break;
                    default:
                        return option.text;
                }
                
                return $('<span><i class="' + iconClass + ' ' + colorClass + ' me-2"></i>' + option.text + '</span>');
            }
        });

        // Show/hide clear button based on selection
        stockStatusSelect.on('change', function() {
            const selectedValue = $(this).val();
            if (selectedValue && selectedValue !== '') {
                clearButton.fadeIn(200);
            } else {
                clearButton.fadeOut(200);
            }
        });

        // Clear button click handler
        clearButton.on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            // Clear the select2 dropdown
            stockStatusSelect.val('').trigger('change');

            // Hide the clear button
            clearButton.fadeOut(200);

            // Trigger filter update if DataTable exists
            if (typeof datatable !== 'undefined' && datatable) {
                datatable.draw();
            }

            // Show feedback
            if (typeof toastr !== 'undefined') {
                toastr.info('Stock status filter cleared', 'Filter Cleared');
            }
        });

        // Add hover effects
        clearButton.hover(
            function() {
                $(this).addClass('btn-light-danger');
                $(this).find('i').addClass('text-danger');
            },
            function() {
                $(this).removeClass('btn-light-danger');
                $(this).find('i').removeClass('text-danger');
            }
        );
    }
});
</script>
