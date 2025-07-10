"use strict";

/**
 * Invoice List Management
 * Handles DataTable, filters, search, and time panel functionality
 */
var KTInvoicesList = function () {
    var table;
    var dt;
    var filterForm;
    var searchInput;
    var currentRequest;
    var initialized = false;

    // State management keys
    var STORAGE_KEYS = {
        FILTERS: 'invoice_filters_state',
        COLUMNS: 'invoice_columns_state',
        COLUMN_VISIBILITY: 'invoice_column_visibility_state'
    };

    /**
     * Save state to localStorage
     */
    var saveState = function(key, data) {
        try {
            localStorage.setItem(key, JSON.stringify(data));
            console.log('State saved:', key, data);
        } catch (error) {
            console.warn('Failed to save state:', error);
        }
    };

    /**
     * Load state from localStorage
     */
    var loadState = function(key, defaultValue = null) {
        try {
            var data = localStorage.getItem(key);
            if (data) {
                var parsed = JSON.parse(data);
                console.log('State loaded:', key, parsed);
                return parsed;
            }
        } catch (error) {
            console.warn('Failed to load state:', error);
        }
        return defaultValue;
    };

    /**
     * Save filter state
     */
    var saveFilterState = function() {
        if (!filterForm) return;

        var filterData = {};

        // Save form inputs
        $(filterForm).find('input, select').each(function() {
            var $input = $(this);
            var name = $input.attr('name') || $input.attr('id');

            if (name) {
                if ($input.is(':checkbox')) {
                    // Handle checkbox arrays (like status[], sales_channel[])
                    if (name.endsWith('[]')) {
                        if (!filterData[name]) {
                            filterData[name] = [];
                        }
                        if ($input.is(':checked')) {
                            filterData[name].push($input.val());
                        }
                    } else {
                        filterData[name] = $input.is(':checked');
                    }
                } else if ($input.is(':radio')) {
                    if ($input.is(':checked')) {
                        filterData[name] = $input.val();
                    }
                } else if ($input.is('select[multiple]')) {
                    filterData[name] = $input.val() || [];
                } else {
                    filterData[name] = $input.val();
                }
            }
        });

        saveState(STORAGE_KEYS.FILTERS, filterData);
    };

    /**
     * Restore filter state
     */
    var restoreFilterState = function() {
        var filterData = loadState(STORAGE_KEYS.FILTERS);
        if (!filterData || !filterForm) return;

        console.log('Restoring filter state:', filterData);

        // Restore form inputs
        Object.keys(filterData).forEach(function(name) {
            var value = filterData[name];

            if (name === 'time_filter') {
                // Special handling for time filter
                var $radio = $(filterForm).find('input[name="time_filter"][value="' + value + '"]');
                if ($radio.length > 0) {
                    $radio.prop('checked', true);

                    // Update the display text
                    var timeLabel = getTimeFilterLabel(value);
                    $('#time_filter_trigger span:first').text(timeLabel);

                    // Update hidden input
                    $('#time_this_month').val(value);
                }
            } else if (name.endsWith('[]')) {
                // Handle checkbox arrays (like status[], sales_channel[])
                var baseName = name.replace('[]', '');
                if (Array.isArray(value)) {
                    // Uncheck all first
                    $(filterForm).find('input[name="' + name + '"]').prop('checked', false);
                    // Check selected values
                    value.forEach(function(val) {
                        $(filterForm).find('input[name="' + name + '"][value="' + val + '"]').prop('checked', true);
                    });
                }
            } else {
                var $input = $(filterForm).find('[name="' + name + '"], #' + name);

                if ($input.length > 0) {
                    if ($input.is(':checkbox')) {
                        $input.prop('checked', value);
                    } else if ($input.is(':radio')) {
                        $(filterForm).find('input[name="' + name + '"][value="' + value + '"]').prop('checked', true);
                    } else if ($input.is('select[multiple]')) {
                        $input.val(value).trigger('change');
                    } else {
                        $input.val(value).trigger('change');
                    }
                }
            }
        });
    };

    /**
     * Save column visibility state
     */
    var saveColumnVisibilityState = function() {
        if (!dt) return;

        var columnStates = {};
        var columnCount = dt.columns().count();

        for (var i = 0; i < columnCount; i++) {
            var column = dt.column(i);
            if (column) {
                columnStates[i] = column.visible();
            }
        }

        saveState(STORAGE_KEYS.COLUMN_VISIBILITY, columnStates);
    };

    /**
     * Restore column visibility state
     */
    var restoreColumnVisibilityState = function() {
        var columnStates = loadState(STORAGE_KEYS.COLUMN_VISIBILITY);
        if (!columnStates || !dt) return;

        console.log('Restoring column visibility state:', columnStates);

        Object.keys(columnStates).forEach(function(columnIndex) {
            var index = parseInt(columnIndex);
            var isVisible = columnStates[columnIndex];
            var column = dt.column(index);

            if (column && column.visible() !== isVisible) {
                column.visible(isVisible, false); // Don't redraw yet

                // Update corresponding checkbox
                $('.column-toggle[value="' + index + '"]').prop('checked', isVisible);
            }
        });

        // Redraw table once after all changes
        dt.draw(false);

        // Force header sync
        setTimeout(function() {
            forceHeaderSync();
        }, 100);
    };

    /**
     * Clear all saved states
     */
    var clearAllStates = function() {
        try {
            localStorage.removeItem(STORAGE_KEYS.FILTERS);
            localStorage.removeItem(STORAGE_KEYS.COLUMNS);
            localStorage.removeItem(STORAGE_KEYS.COLUMN_VISIBILITY);
            console.log('All states cleared');
        } catch (error) {
            console.warn('Failed to clear states:', error);
        }
    };

    /**
     * Initialize DataTable
     */
    var initTable = function () {
        table = document.querySelector('#kt_invoices_table');

        if (!table) {
            console.error('Table #kt_invoices_table not found');
            return;
        }

        console.log('Initializing DataTable for:', table);

        // Destroy existing DataTable if it exists
        if ($.fn.DataTable.isDataTable(table)) {
            $(table).DataTable().destroy();
        }

        dt = $(table).DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: invoiceAjaxUrl, // Will be set from blade template
                type: "GET",
                data: function (d) {
                    // Add filter parameters
                    d.search_term = $('#invoice_search').val();

                    // Get barcode search from URL parameter
                    var urlParams = new URLSearchParams(window.location.search);
                    if (urlParams.has('Code')) {
                        d.Code = urlParams.get('Code');
                    }

                    // Get time filter value from checked radio or default
                    d.time_filter = $('input[name="time_filter"]:checked').val() || 'this_month';

                    // Debug log
                    console.log('AJAX data being sent:', {
                        search_term: d.search_term,
                        time_filter: d.time_filter,
                        Code: d.Code
                    });

                    // Status filters (checkboxes)
                    d.status_filters = [];
                    $('#kt_invoice_filter_form input[type="checkbox"][id^="status_"]:checked').each(function() {
                        d.status_filters.push($(this).val());
                    });

                    // Delivery status filters
                    d.delivery_status = [];
                    $('#kt_invoice_filter_form input[type="checkbox"][id^="delivery_"]:checked').each(function() {
                        d.delivery_status.push($(this).val());
                    });

                    // Sales channel filters (checkboxes)
                    d.sales_channel = [];
                    $('#kt_invoice_filter_form input[type="checkbox"][id^="sales_channel_"]:checked').each(function() {
                        d.sales_channel.push($(this).val());
                    });

                    // Select filters
                    d.creator_id = $('select[name="creator_id"]').val();
                    d.seller_id = $('select[name="seller_id"]').val();
                    d.delivery_partner = $('select[name="delivery_partner"]').val();
                    d.delivery_area = $('select[name="delivery_area"]').val();
                    d.payment_method = $('select[name="payment_method"]').val();
                    d.price_list = $('select[name="price_list"]').val();
                    d.other_income_type = $('select[name="other_income_type"]').val();

                    // Delivery time filter
                    d.delivery_time_filter = $('input[name="delivery_time_filter"]:checked').val();

                    // Add date range if custom time is selected
                    if (d.time_filter === 'custom') {
                        d.date_from = $('#date_from').val();
                        d.date_to = $('#date_to').val();
                    }
                },
                beforeSend: function(xhr) {
                    // Cancel previous request if exists
                    if (currentRequest && currentRequest !== xhr) {
                        currentRequest.abort();
                    }
                    currentRequest = xhr;
                    console.log('Sending AJAX request to:', invoiceAjaxUrl);
                },
                error: function(xhr, error, thrown) {
                    console.error('DataTable AJAX error:', error, thrown);
                    console.error('Response:', xhr.responseText);
                }
            },
            columns: [
                {
                    data: 'checkbox',
                    name: 'checkbox',
                    title: '',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                },
                { data: 'invoice_number', name: 'invoice_number', title: 'Mã hóa đơn' },
                { data: 'customer_display', name: 'customer_display', title: 'Khách hàng' },
                { data: 'total_amount', name: 'total_amount', title: 'Tổng tiền' },
                { data: 'amount_paid', name: 'amount_paid', title: 'Đã thanh toán' },
                { data: 'payment_status', name: 'payment_status', title: 'Trạng thái' },
                { data: 'payment_method', name: 'payment_method', title: 'Phương thức TT' },
                { data: 'sales_channel', name: 'sales_channel', title: 'Kênh bán' },
                { data: 'created_at', name: 'created_at', title: 'Ngày tạo' },
                { data: 'seller', name: 'seller', title: 'Người bán', visible: false },
                { data: 'creator', name: 'creator', title: 'Người tạo', visible: false },
                { data: 'discount', name: 'discount', title: 'Giảm giá', visible: false },
                { data: 'email', name: 'email', title: 'Email', visible: false },
                { data: 'phone', name: 'phone', title: 'Phone', visible: false },
                { data: 'address', name: 'address', title: 'Địa chỉ', visible: false },
                { data: 'branch_shop', name: 'branch_shop', title: 'Chi nhánh', visible: false },
                { data: 'notes', name: 'notes', title: 'Ghi chú', visible: false }
            ],
            order: [[8, 'desc']], // Order by created_at desc
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            dom: 'rtip',
            scrollX: true,
            responsive: false,
            buttons: [
                {
                    extend: 'colvis',
                    text: '<i class="fas fa-list"></i>',
                    className: 'btn btn-light-primary btn-sm',
                    titleAttr: 'Chọn cột hiển thị'
                }
            ],
            language: {
                processing: "Đang xử lý...",
                search: "Tìm kiếm:",
                lengthMenu: "Hiển thị _MENU_ mục",
                info: "Hiển thị _START_ đến _END_ của _TOTAL_ mục",
                infoEmpty: "Hiển thị 0 đến 0 của 0 mục",
                infoFiltered: "(lọc từ _MAX_ tổng số mục)",
                paginate: {
                    first: "Đầu",
                    last: "Cuối",
                    next: "Tiếp",
                    previous: "Trước"
                },
                emptyTable: "Không có dữ liệu trong bảng",
                zeroRecords: "Không tìm thấy kết quả phù hợp"
            },
            drawCallback: function(settings) {
                // Data loaded successfully
                console.log('Invoice data loaded:', settings.json);

                // Store data for row expansion
                if (settings.json && settings.json.data) {
                    window.invoiceData = {};
                    settings.json.data.forEach(function(item, index) {
                        if (item.detail_panel) {
                            window.invoiceData[index] = item.detail_panel;
                        }
                    });
                }

                // Sync column visibility checkboxes with actual column state
                syncColumnVisibilityCheckboxes();
            },
            initComplete: function() {
                // Move column visibility button to custom container
                var buttons = new $.fn.dataTable.Buttons(this, {
                    buttons: [
                        {
                            extend: 'colvis',
                            text: '<i class="fas fa-list"></i>',
                            className: 'btn btn-success',
                            titleAttr: 'Chọn cột hiển thị'
                        }
                    ]
                });

                buttons.container().appendTo('#column-visibility-container');

                // Add header checkbox after DataTable is initialized
                addHeaderCheckbox();

                // Initialize select all checkbox functionality
                initSelectAllCheckbox();

                // Initialize bulk actions
                initBulkActions();


            }
        });

        // Expose DataTable instance globally for column visibility
        window.invoiceTable = dt;

        // Apply any pending column changes
        if (window.pendingColumnChanges) {
            console.log('Applying pending column changes:', window.pendingColumnChanges);

            setTimeout(function() {
                Object.keys(window.pendingColumnChanges).forEach(function(columnIndex) {
                    var isVisible = window.pendingColumnChanges[columnIndex];
                    var column = dt.column(parseInt(columnIndex));

                    if (column && column.visible !== undefined) {
                        column.visible(isVisible);
                        console.log('Applied pending column change:', columnIndex, isVisible);
                    }
                });

                // Clear pending changes and redraw
                window.pendingColumnChanges = {};
                dt.columns.adjust().draw(false);
            }, 100);
        }

        // Add event listener for opening and closing details - click anywhere on row
        $('#kt_invoices_table tbody').on('click', 'tr', function (e) {
            // Prevent expansion when clicking on action buttons or checkboxes
            if ($(e.target).closest('.btn, .form-check-input, .dropdown').length > 0) {
                return;
            }

            var tr = $(this);
            var row = dt.row(tr);
            var rowIndex = row.index();

            if (row.child.isShown()) {
                // This row is already open - close it
                row.child.hide();
                tr.removeClass('shown');
            } else {
                // Close any other open rows
                dt.rows().every(function() {
                    if (this.child.isShown()) {
                        this.child.hide();
                        $(this.node()).removeClass('shown');
                    }
                });

                // Open this row
                if (window.invoiceData && window.invoiceData[rowIndex]) {
                    row.child(window.invoiceData[rowIndex]).show();
                    tr.addClass('shown');
                } else {
                    // Show loading state
                    row.child('<div class="invoice-details-loading text-center p-4"><i class="fa fa-spinner fa-spin"></i><br>Đang tải...</div>').show();
                    tr.addClass('shown');

                    // Get invoice ID from row data
                    var rowData = row.data();
                    var invoiceId = rowData.id;

                    console.log('Row expansion clicked:', {
                        rowIndex: rowIndex,
                        rowData: rowData,
                        invoiceId: invoiceId
                    });

                    if (!invoiceId) {
                        console.error('Invoice ID not found in row data');
                        row.child('<div class="alert alert-danger">Không tìm thấy ID hóa đơn</div>').show();
                        return;
                    }

                    // Load detail panel via AJAX
                    $.ajax({
                        url: '/admin/invoices/' + invoiceId + '/detail-panel',
                        method: 'GET',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        success: function(response) {
                            console.log('AJAX response received:', response);

                            if (response.success) {
                                // Cache the data
                                if (!window.invoiceData) {
                                    window.invoiceData = {};
                                }
                                window.invoiceData[rowIndex] = response.html;

                                // Update the row content
                                row.child(response.html).show();
                                console.log('Detail panel loaded successfully');

                                // Initialize Bootstrap tabs for the loaded detail panel
                                setTimeout(function() {
                                    initDetailPanelTabs(tr);
                                }, 100);
                            } else {
                                console.error('Server returned error:', response);
                                row.child('<div class="alert alert-danger">Không thể tải thông tin chi tiết: ' + (response.message || 'Unknown error') + '</div>').show();
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('AJAX error:', {
                                status: status,
                                error: error,
                                responseText: xhr.responseText,
                                url: '/admin/invoices/' + invoiceId + '/detail-panel'
                            });
                            row.child('<div class="alert alert-danger">Lỗi khi tải thông tin chi tiết: ' + error + '</div>').show();
                        }
                    });
                }
            }
        });
    };

    /**
     * Initialize search functionality
     */
    var initSearch = function () {
        searchInput = document.querySelector('#invoice_search');

        if (searchInput) {
            let searchTimeout;

            searchInput.addEventListener('input', function (e) {
                // Cancel previous request
                if (currentRequest) {
                    currentRequest.abort();
                }

                // Clear previous timeout
                clearTimeout(searchTimeout);

                // Set new timeout for debouncing
                searchTimeout = setTimeout(function() {
                    dt.ajax.reload();
                }, 500);
            });
        }
    };

    /**
     * Initialize filter form and time panel
     */
    var initFilters = function () {
        filterForm = document.querySelector('#kt_invoice_filter_form');

        if (filterForm) {
            // Time filter change
            $(filterForm).on('change', 'input[name="time_filter"]', function() {
                saveFilterState();
                dt.ajax.reload();
            });

            // Status filter change
            $(filterForm).on('change', 'input[type="checkbox"]', function() {
                saveFilterState();
                dt.ajax.reload();
            });

            // Select2 change
            $(filterForm).on('change', 'select', function() {
                saveFilterState();
                dt.ajax.reload();
            });

            // Time option buttons
            $(filterForm).on('click', '.time-option', function(e) {
                e.preventDefault();
                e.stopPropagation();

                var clickedButton = $(this);
                var panel = $('#time_options_panel');
                var icon = $('#time_dropdown_icon');
                var trigger = $('#time_filter_trigger');

                // Add selecting class for underline effect
                clickedButton.addClass('selecting');

                // Remove active class from all buttons
                $('.time-option').removeClass('active btn-primary').addClass('btn-light-primary');

                // Add active class to clicked button
                clickedButton.removeClass('btn-light-primary').addClass('btn-primary active');

                // Update the time filter value
                var timeValue = clickedButton.data('value');
                var timeLabel = getTimeFilterLabel(timeValue);

                // Update the hidden input value and radio button
                $('#time_this_month').val(timeValue);
                $('input[name="time_filter"]').val([timeValue]); // Set checked radio

                // Update the display text
                trigger.find('span:first').text(timeLabel);

                // Hide the panel after selection with animation
                setTimeout(function() {
                    panel.addClass('hiding');
                    setTimeout(function() {
                        panel.removeClass('show hiding');
                        icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
                        trigger.removeClass('selecting');
                        clickedButton.removeClass('selecting');
                    }, 300);
                }, 500); // Wait 500ms to show the selection effect

                // Save filter state and reload data
                saveFilterState();
                dt.ajax.reload();
            });

            // Toggle time panel when clicking on the trigger
            $('#time_filter_trigger').off('click.timePanel').on('click.timePanel', function(e) {
                e.preventDefault();
                e.stopPropagation();

                var panel = $('#time_options_panel');
                var icon = $('#time_dropdown_icon');
                var label = $(this);

                console.log('Time filter trigger clicked');
                console.log('Panel classes before:', panel.attr('class'));
                console.log('Panel has show class:', panel.hasClass('show'));
                console.log('Panel has hiding class:', panel.hasClass('hiding'));

                // Close column visibility panel first
                $('#column_visibility_panel').removeClass('show');
                $('#column_visibility_trigger').removeClass('active');

                // Toggle panel
                if (panel.hasClass('show')) {
                    // Hide panel
                    console.log('Hiding panel');
                    panel.removeClass('show').addClass('hiding');
                    icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
                    label.removeClass('selecting');

                    setTimeout(function() {
                        console.log('Removing hiding class');
                        panel.removeClass('hiding');
                        console.log('Panel classes after hide:', panel.attr('class'));
                    }, 300);
                } else {
                    // Show panel
                    console.log('Showing panel');
                    panel.removeClass('hiding').addClass('show');
                    icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
                    label.addClass('selecting');
                    console.log('Panel classes after show:', panel.attr('class'));
                }
            });

            // Close panel when clicking outside
            $(document).off('click.timePanel').on('click.timePanel', function(e) {
                var panel = $('#time_options_panel');
                var trigger = $('#time_filter_trigger');
                var icon = $('#time_dropdown_icon');

                if (panel.hasClass('show') &&
                    !panel.is(e.target) &&
                    panel.has(e.target).length === 0 &&
                    !trigger.is(e.target) &&
                    trigger.has(e.target).length === 0) {

                    console.log('Closing time panel from outside click');
                    panel.removeClass('show').addClass('hiding');
                    icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
                    trigger.removeClass('selecting');

                    setTimeout(function() {
                        panel.removeClass('hiding');
                    }, 300);
                }
            });

            // Close button handler
            $(filterForm).on('click', '#close_time_panel', function(e) {
                e.preventDefault();
                e.stopPropagation();

                var panel = $('#time_options_panel');
                var trigger = $('#time_filter_trigger');
                var icon = $('#time_dropdown_icon');

                console.log('Close button clicked');
                panel.removeClass('show').addClass('hiding');
                icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
                trigger.removeClass('selecting');

                setTimeout(function() {
                    panel.removeClass('hiding');
                }, 300);
            });

            // Initialize Select2 for all selects except special ones
            $(filterForm).find('select').each(function() {
                var $select = $(this);
                if (!$select.hasClass('select2-hidden-accessible') &&
                    !$select.attr('id').includes('creator_filter') &&
                    !$select.attr('id').includes('seller_filter')) {
                    $select.select2({
                        placeholder: $select.data('placeholder') || 'Chọn...',
                        allowClear: true
                    });
                }
            });

            // Initialize Tagify for sales channel
            initSalesChannelTagify();

            // Initialize user filters
            initUserFilters();

            // Initialize default time filter
            var defaultTimeValue = 'this_month';
            $('#time_this_month').val(defaultTimeValue);
            $('#time_filter_trigger span:first').text(getTimeFilterLabel(defaultTimeValue));
        }
    };

    /**
     * Initialize Sales Channel Tagify
     */
    var initSalesChannelTagify = function() {
        var input = document.querySelector('#sales_channel_tags');
        if (input) {
            var tagify = new Tagify(input, {
                whitelist: [
                    { value: 'offline', label: 'Cửa hàng' },
                    { value: 'online', label: 'Website' },
                    { value: 'marketplace', label: 'Marketplace' },
                    { value: 'social_media', label: 'Mạng xã hội' },
                    { value: 'phone_order', label: 'Điện thoại' }
                ],
                maxTags: 5,
                dropdown: {
                    maxItems: 20,
                    classname: 'tags-look',
                    enabled: 0,
                    closeOnSelect: false
                },
                templates: {
                    tag: function(tagData) {
                        return `<tag title="${tagData.label || tagData.value}"
                                    contenteditable='false'
                                    spellcheck='false'
                                    tabIndex="-1"
                                    class="tagify__tag ${tagData.class ? tagData.class : ''}"
                                    ${this.getAttributes(tagData)}>
                                    <x title='' class='tagify__tag__removeBtn' role='button' aria-label='remove tag'></x>
                                    <div>
                                        <span class='tagify__tag-text'>${tagData.label || tagData.value}</span>
                                    </div>
                                </tag>`;
                    },
                    dropdownItem: function(tagData) {
                        return `<div ${this.getAttributes(tagData)}
                                    class='tagify__dropdown__item ${tagData.class ? tagData.class : ''}'
                                    tabindex="0"
                                    role="option">
                                    ${tagData.label || tagData.value}
                                </div>`;
                    }
                }
            });

            // Listen for changes
            tagify.on('change', function(e) {
                saveFilterState();
                dt.ajax.reload();
            });
        }
    };

    /**
     * Initialize User Filters (Creator and Seller)
     */
    var initUserFilters = function() {
        // Initialize Creator filter
        $('#creator_filter').select2({
            placeholder: 'Chọn người tạo',
            allowClear: true,
            ajax: {
                url: '/admin/invoices/filter-users',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term,
                        page: params.page
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;
                    return {
                        results: data.data || [],
                        pagination: {
                            more: (params.page * 30) < data.total_count
                        }
                    };
                },
                cache: true
            }
        });

        // Initialize Seller filter
        $('#seller_filter').select2({
            placeholder: 'Chọn người bán',
            allowClear: true,
            ajax: {
                url: '/admin/invoices/filter-users',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term,
                        page: params.page
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;
                    return {
                        results: data.data || [],
                        pagination: {
                            more: (params.page * 30) < data.total_count
                        }
                    };
                },
                cache: true
            }
        });

        // Listen for changes
        $('#creator_filter, #seller_filter').on('change', function() {
            saveFilterState();
            dt.ajax.reload();
        });
    };

    /**
     * Get time filter label mapping
     */
    var getTimeFilterLabel = function(value) {
        var labels = {
            'today': 'Hôm nay',
            'yesterday': 'Hôm qua',
            'this_week': 'Tuần này',
            'last_week': 'Tuần trước',
            '7_days': '7 ngày qua',
            'this_month': 'Tháng này',
            'last_month': 'Tháng trước',
            'this_month_lunar': 'Tháng này (âm lịch)',
            'last_month_lunar': 'Tháng trước (âm lịch)',
            '30_days': '30 ngày qua',
            'this_quarter': 'Quý này',
            'last_quarter': 'Quý trước',
            'this_year': 'Năm này',
            'last_year': 'Năm trước',
            'this_year_lunar': 'Năm này (âm lịch)',
            'last_year_lunar': 'Năm trước (âm lịch)'
        };
        return labels[value] || value;
    };



    /**
     * Format currency for display
     */
    var formatCurrency = function(amount) {
        return new Intl.NumberFormat('vi-VN').format(amount);
    };

    /**
     * Debug function to show column mapping
     */
    var debugColumnMapping = function() {
        if (!dt || !dt.column) {
            return;
        }

        console.log('=== COLUMN MAPPING DEBUG ===');
        var $headers = $(dt.table().header()).find('th');

        console.log('Headers in DOM:', $headers.length);
        console.log('Columns in DataTable:', dt.columns().count());

        $headers.each(function(index) {
            var $header = $(this);
            var headerText = $header.text().trim();
            var column = dt.column(index);

            console.log(`Index ${index}:`, {
                headerText: headerText,
                headerVisible: $header.is(':visible'),
                columnExists: !!column,
                columnVisible: column ? column.visible() : 'N/A'
            });
        });
        console.log('=== END COLUMN MAPPING ===');
    };

    /**
     * Force header sync with column visibility
     */
    var forceHeaderSync = function() {
        if (!dt || !dt.column) {
            return;
        }

        console.log('Starting header sync...');
        debugColumnMapping();

        // Get all header cells
        var $headers = $(dt.table().header()).find('th');

        console.log('Total headers found:', $headers.length);
        console.log('Total columns in DataTable:', dt.columns().count());

        // NEW APPROACH: Use DataTables column().header() to get the correct header for each column
        // This avoids index mismatch issues when columns are hidden
        var columnCount = dt.columns().count();

        console.log(`Syncing ${columnCount} columns using column().header() method`);

        for (var columnIndex = 0; columnIndex < columnCount; columnIndex++) {
            var column = dt.column(columnIndex);
            if (column && column.visible !== undefined) {
                var isVisible = column.visible();

                try {
                    // Use DataTables API to get the actual header for this column
                    var headerNode = column.header();
                    var $header = $(headerNode);

                    console.log(`Header sync - Column ${columnIndex}: DataTable visible=${isVisible}, Header exists=${$header.length > 0}, Header currently visible=${$header.length > 0 ? $header.is(':visible') : 'N/A'}`);

                    // Only process if header exists
                    if ($header.length > 0) {
                        // Don't hide the first column (checkbox column)
                        if (columnIndex === 0) {
                            $header.show(); // Always show checkbox column
                            $header.css('display', ''); // Remove any inline display styles

                            // Ensure checkbox select all is visible
                            var $checkbox = $header.find('input[type="checkbox"]');
                            if ($checkbox.length > 0) {
                                $checkbox.show();
                                $checkbox.closest('.form-check').show();
                            }

                            console.log(`Column ${columnIndex} (checkbox): Always visible`);
                        } else {
                            // Only change header visibility if it doesn't match DataTable state
                            if (isVisible && !$header.is(':visible')) {
                                $header.show();
                                console.log(`Column ${columnIndex}: Showing header (was hidden, should be visible)`);
                            } else if (!isVisible && $header.is(':visible')) {
                                $header.hide();
                                console.log(`Column ${columnIndex}: Hiding header (was visible, should be hidden)`);
                            } else {
                                console.log(`Column ${columnIndex}: Header already in correct state (${isVisible ? 'visible' : 'hidden'})`);
                            }
                        }
                    } else {
                        console.log(`Column ${columnIndex}: No header found via column().header()`);
                    }
                } catch (error) {
                    console.log(`Column ${columnIndex}: Error getting header - ${error.message}`);
                }
            }
        }

        // Force table layout recalculation
        dt.columns.adjust();

        console.log('Header sync completed');
    };

    /**
     * Sync column visibility checkboxes with actual DataTable column state
     */
    var syncColumnVisibilityCheckboxes = function() {
        if (!dt || !dt.column) {
            return;
        }

        $('.column-toggle').each(function() {
            var columnIndex = parseInt($(this).val());
            var column = dt.column(columnIndex);

            if (column && column.visible !== undefined) {
                var isVisible = column.visible();
                $(this).prop('checked', isVisible);

                console.log('Synced checkbox for column', columnIndex, 'visible:', isVisible);
            }
        });

        // Force header sync after all checkboxes are synced
        forceHeaderSync();
    };

    /**
     * Initialize column visibility
     */
    var initColumnVisibility = function () {
        // Toggle column visibility panel when clicking on the trigger
        $('#column_visibility_trigger').off('click.columnPanel').on('click.columnPanel', function(e) {
            e.preventDefault();
            e.stopPropagation();

            var panel = $('#column_visibility_panel');
            var trigger = $(this);

            console.log('Column visibility trigger clicked');

            // Close time panel first
            $('#time_options_panel').removeClass('show');
            $('#time_filter_trigger').removeClass('active');

            if (panel.hasClass('show')) {
                panel.removeClass('show');
                trigger.removeClass('active');
            } else {
                panel.addClass('show');
                trigger.addClass('active');

                // Sync checkboxes when panel opens
                syncColumnVisibilityCheckboxes();
            }
        });

        // Handle column toggle
        $('.column-toggle').off('change.columnToggle').on('change.columnToggle', function() {
            var columnIndex = parseInt($(this).val());
            var isVisible = $(this).is(':checked');

            console.log('Column toggle clicked:', {
                columnIndex: columnIndex,
                isVisible: isVisible,
                dtExists: !!dt,
                dtInitialized: dt && dt.settings && dt.settings().length > 0
            });

            // Debug column mapping before change
            debugColumnMapping();

            if (dt && dt.column && dt.settings && dt.settings().length > 0) {
                try {
                    // Debug: Log all column visibility states before change
                    console.log('All columns visibility before change:');
                    for (var i = 0; i < dt.columns().count(); i++) {
                        console.log(`Column ${i}: visible = ${dt.column(i).visible()}`);
                    }

                    // Use specific column selector to avoid index confusion
                    var column = dt.column(columnIndex);

                    // Check if column exists
                    if (column && column.visible !== undefined) {
                        console.log('Before toggle:', {
                            columnIndex: columnIndex,
                            currentVisibility: column.visible(),
                            targetVisibility: isVisible
                        });

                        // Use DataTables API to set column visibility for ONLY this column
                        column.visible(isVisible, false); // false = don't redraw yet

                        console.log('After toggle:', {
                            columnIndex: columnIndex,
                            newVisibility: column.visible()
                        });

                        // Debug: Log all column visibility states after change
                        console.log('All columns visibility after change:');
                        for (var i = 0; i < dt.columns().count(); i++) {
                            console.log(`Column ${i}: visible = ${dt.column(i).visible()}`);
                        }

                        // Force all headers to sync with column visibility
                        forceHeaderSync();

                        // Now redraw the table
                        dt.draw(false); // false = don't reset paging

                        // Save column visibility state
                        saveColumnVisibilityState();

                        console.log('Column visibility updated successfully:', {
                            columnIndex: columnIndex,
                            isVisible: isVisible,
                            actualVisibility: column.visible()
                        });
                    } else {
                        console.error('Column not found:', columnIndex);
                    }
                } catch (error) {
                    console.error('Error updating column visibility:', error);
                }
            } else {
                console.warn('DataTable not ready for column toggle:', {
                    dt: !!dt,
                    dtColumn: !!(dt && dt.column),
                    dtSettings: !!(dt && dt.settings)
                });

                // Store the change for later application
                if (!window.pendingColumnChanges) {
                    window.pendingColumnChanges = {};
                }
                window.pendingColumnChanges[columnIndex] = isVisible;
                console.log('Stored pending column change:', columnIndex, isVisible);
            }
        });

        // Prevent panel close when clicking inside panel
        $('#column_visibility_panel').off('click.columnPanel').on('click.columnPanel', function(e) {
            e.stopPropagation();
        });

        // Close panel when clicking outside
        $(document).off('click.columnPanel').on('click.columnPanel', function(e) {
            var panel = $('#column_visibility_panel');
            var trigger = $('#column_visibility_trigger');

            if (panel.hasClass('show') &&
                !$(e.target).closest('#column_visibility_trigger, #column_visibility_panel').length) {
                panel.removeClass('show');
                trigger.removeClass('active');
            }
        });
    };

    /**
     * Initialize all components
     */
    var init = function () {
        if (initialized) {
            console.log('KTInvoicesList already initialized, skipping...');
            return;
        }

        console.log('Initializing KTInvoicesList...');
        initTable();
        initSearch();
        initFilters();
        initColumnVisibility();

        // Restore states after initialization
        setTimeout(function() {
            restoreFilterState();
            restoreColumnVisibilityState();
        }, 500);

        initialized = true;
    };

    // Public methods
    return {
        init: function () {
            init();
        },
        reload: function() {
            if (dt) {
                dt.ajax.reload();
            }
        },
        getTable: function() {
            return dt;
        },
        clearStates: function() {
            clearAllStates();
        },
        saveFilterState: function() {
            saveFilterState();
        },
        saveColumnState: function() {
            saveColumnVisibilityState();
        }
    };
}();

// Expose globally
window.KTInvoicesList = KTInvoicesList;

// Debug function to check DataTable state
window.debugColumnVisibility = function() {
    console.log('=== DataTable Debug Info ===');
    console.log('dt exists:', !!dt);
    console.log('dt.column exists:', !!(dt && dt.column));
    console.log('dt.settings exists:', !!(dt && dt.settings));

    if (dt && dt.column) {
        console.log('Total columns:', dt.columns().count());

        // Check each column visibility
        for (let i = 0; i < dt.columns().count(); i++) {
            const column = dt.column(i);
            console.log(`Column ${i}:`, {
                visible: column.visible(),
                header: $(column.header()).text()
            });
        }
    }

    // Check checkboxes
    console.log('=== Checkboxes ===');
    $('.column-toggle').each(function() {
        const index = $(this).val();
        const checked = $(this).is(':checked');
        const label = $(this).next('label').text();
        console.log(`Checkbox ${index} (${label}): ${checked}`);
    });

    // Check headers
    console.log('=== Headers ===');
    if (dt && dt.table) {
        $(dt.table().header()).find('th').each(function(index) {
            const headerText = $(this).text().trim();
            const isVisible = $(this).is(':visible');
            const column = dt.column(index);
            const columnVisible = column ? column.visible() : 'N/A';
            console.log(`Header ${index} (${headerText}): DOM visible=${isVisible}, Column visible=${columnVisible}`);
        });
    }
};

// Test function for detail panel loading
window.testDetailPanel = function(invoiceId) {
    console.log('Testing detail panel for invoice ID:', invoiceId);

    $.ajax({
        url: '/admin/invoices/' + invoiceId + '/detail-panel',
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(response) {
            console.log('Detail panel test success:', response);
        },
        error: function(xhr, status, error) {
            console.error('Detail panel test error:', {
                status: status,
                error: error,
                responseText: xhr.responseText
            });
        }
    });
};

// Test function for test endpoint
window.testDetailPanelSimple = function(invoiceId) {
    console.log('Testing simple detail panel for invoice ID:', invoiceId);

    $.ajax({
        url: '/admin/invoices/test-detail/' + invoiceId,
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(response) {
            console.log('Simple detail panel test success:', response);
        },
        error: function(xhr, status, error) {
            console.error('Simple detail panel test error:', {
                status: status,
                error: error,
                responseText: xhr.responseText
            });
        }
    });
};

// Test function for column visibility
window.testColumnVisibility = function(columnIndex, visible) {
    if (dt && dt.column) {
        const column = dt.column(columnIndex);
        column.visible(visible, false); // false = don't redraw yet

        // Force header sync
        forceHeaderSync();

        // Redraw table
        dt.draw(false);

        console.log(`Set column ${columnIndex} visibility to ${visible}`);

        // Update checkbox
        $(`.column-toggle[value="${columnIndex}"]`).prop('checked', visible);
    } else {
        console.error('DataTable not available');
    }
};

// Initialize when DOM is ready
KTUtil.onDOMContentLoaded(function () {
    KTInvoicesList.init();
});

// Global functions for action buttons
function printInvoice(invoiceId) {
    console.log('Print invoice:', invoiceId);

    // Open print page in new window
    const printUrl = `/admin/invoices/${invoiceId}/print`;
    const printWindow = window.open(printUrl, '_blank', 'width=800,height=600,scrollbars=yes,resizable=yes');

    if (!printWindow) {
        Swal.fire({
            title: 'Lỗi',
            text: 'Không thể mở cửa sổ in. Vui lòng kiểm tra cài đặt popup blocker.',
            icon: 'error',
            confirmButtonText: 'OK'
        });
    }
}

// Show print template modal
function showPrintModal(invoiceId) {
    console.log('Show print modal for invoice:', invoiceId);

    // Store invoice ID for later use
    window.currentInvoiceId = invoiceId;

    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('print_template_modal'));
    modal.show();

    // Add click handlers for template cards
    document.querySelectorAll('.template-card').forEach(card => {
        card.addEventListener('click', function() {
            const template = this.dataset.template;
            printInvoiceWithTemplate(invoiceId, template);
            modal.hide();
        });
    });
}

// Print invoice with specific template
function printInvoiceWithTemplate(invoiceId, template) {
    console.log('Print invoice with template:', invoiceId, template);

    // Build print URL with template parameter
    const printUrl = `/admin/invoices/${invoiceId}/print?template=${template}`;
    const printWindow = window.open(printUrl, '_blank', 'width=800,height=600,scrollbars=yes,resizable=yes');

    if (!printWindow) {
        Swal.fire({
            title: 'Lỗi',
            text: 'Không thể mở cửa sổ in. Vui lòng kiểm tra cài đặt popup blocker.',
            icon: 'error',
            confirmButtonText: 'OK'
        });
    }
}

function sendInvoice(invoiceId) {
    // TODO: Implement send functionality
    console.log('Send invoice:', invoiceId);
    Swal.fire({
        title: 'Gửi hóa đơn',
        text: 'Chức năng gửi hóa đơn đang được phát triển',
        icon: 'info',
        confirmButtonText: 'OK'
    });
}

function recordPayment(invoiceId) {
    // TODO: Implement payment recording
    console.log('Record payment for invoice:', invoiceId);
    Swal.fire({
        title: 'Ghi nhận thanh toán',
        text: 'Chức năng ghi nhận thanh toán đang được phát triển',
        icon: 'info',
        confirmButtonText: 'OK'
    });
}

// Export invoice function
function exportInvoice(invoiceId) {
    console.log('Export invoice:', invoiceId);
    Swal.fire({
        title: 'Xuất file',
        text: 'Chức năng xuất file đang được phát triển',
        icon: 'info',
        confirmButtonText: 'OK'
    });
}

// Send invoice function
function sendInvoice(invoiceId) {
    console.log('Send invoice:', invoiceId);
    Swal.fire({
        title: 'Gửi hóa đơn',
        text: 'Chức năng gửi hóa đơn đang được phát triển',
        icon: 'info',
        confirmButtonText: 'OK'
    });
}

// Show print modal function
function showPrintModal(invoiceId) {
    console.log('Show print modal for invoice:', invoiceId);

    // Create modal HTML
    const modalHtml = `
        <div class="modal fade" id="printModal" tabindex="-1" aria-labelledby="printModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="printModalLabel">Chọn template in</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label class="form-label">Template:</label>
                                <select class="form-select" id="printTemplate">
                                    <option value="default">Template mặc định</option>
                                    <option value="simple">Template đơn giản</option>
                                    <option value="detailed">Template chi tiết</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="button" class="btn btn-primary" onclick="printInvoice(${invoiceId})">In hóa đơn</button>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Remove existing modal if any
    const existingModal = document.getElementById('printModal');
    if (existingModal) {
        existingModal.remove();
    }

    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHtml);

    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('printModal'));
    modal.show();
}

// Print invoice function
function printInvoice(invoiceId) {
    const template = document.getElementById('printTemplate').value;
    console.log('Print invoice:', invoiceId, 'with template:', template);

    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('printModal'));
    modal.hide();

    // Show success message
    Swal.fire({
        title: 'In hóa đơn',
        text: `Đang in hóa đơn với template: ${template}`,
        icon: 'success',
        confirmButtonText: 'OK'
    });
}

// Expose functions to global scope
window.showPrintModal = showPrintModal;
window.printInvoice = printInvoice;
window.sendInvoice = sendInvoice;
window.exportInvoice = exportInvoice;
window.recordPayment = recordPayment;

function cancelInvoice(invoiceId) {
    Swal.fire({
        title: 'Hủy hóa đơn',
        text: 'Bạn có chắc chắn muốn hủy hóa đơn này?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Hủy hóa đơn',
        cancelButtonText: 'Không',
        confirmButtonColor: '#f1416c'
    }).then((result) => {
        if (result.isConfirmed) {
            // TODO: Implement cancel functionality
            console.log('Cancel invoice:', invoiceId);
            Swal.fire('Đã hủy!', 'Hóa đơn đã được hủy.', 'success');
        }
    });
}

function deleteInvoice(invoiceId) {
    Swal.fire({
        title: 'Xóa hóa đơn',
        text: 'Bạn có chắc chắn muốn xóa hóa đơn này? Hành động này không thể hoàn tác.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Xóa',
        cancelButtonText: 'Hủy',
        confirmButtonColor: '#f1416c'
    }).then((result) => {
        if (result.isConfirmed) {
            // TODO: Implement delete functionality
            console.log('Delete invoice:', invoiceId);
            Swal.fire('Đã xóa!', 'Hóa đơn đã được xóa.', 'success');
            // Reload table
            KTInvoicesList.reload();
        }
    });
}

// Expose DataTable instance globally for column visibility
window.invoiceTable = null;

/**
 * Add header checkbox after DataTable initialization
 */
function addHeaderCheckbox() {
    // Find the first header cell and add checkbox
    var firstHeaderCell = $('#kt_invoice_table thead tr:first th:first');
    if (firstHeaderCell.length && firstHeaderCell.html().trim() === '') {
        firstHeaderCell.html('<input type="checkbox" id="select-all-invoices" class="form-check-input">');
        console.log('Header checkbox added successfully');
    }
}

/**
 * Initialize select all checkbox functionality
 */
function initSelectAllCheckbox() {
    // Handle select all checkbox
    $(document).on('change', '#select-all-invoices', function() {
        var isChecked = $(this).prop('checked');

        // Check/uncheck all visible row checkboxes
        $('#kt_invoice_table tbody input[type="checkbox"]').prop('checked', isChecked);

        // Update bulk actions visibility
        updateBulkActionsVisibility();
    });

    // Handle individual row checkbox changes
    $(document).on('change', '#kt_invoice_table tbody input[type="checkbox"]', function() {
        var totalCheckboxes = $('#kt_invoice_table tbody input[type="checkbox"]').length;
        var checkedCheckboxes = $('#kt_invoice_table tbody input[type="checkbox"]:checked').length;

        // Update select all checkbox state
        $('#select-all-invoices').prop('checked', totalCheckboxes === checkedCheckboxes);
        $('#select-all-invoices').prop('indeterminate', checkedCheckboxes > 0 && checkedCheckboxes < totalCheckboxes);

        // Update bulk actions visibility
        updateBulkActionsVisibility();
    });
}



/**
 * Initialize bulk actions
 */
function initBulkActions() {
    // Create bulk actions button if it doesn't exist
    if ($('#bulk-actions-btn').length === 0) {
        var bulkActionsHtml = `
            <div class="dropdown" id="bulk-actions-container" style="display: none;">
                <button class="btn btn-primary dropdown-toggle me-2" type="button" id="bulk-actions-btn" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-cogs"></i> Thao tác
                </button>
                <ul class="dropdown-menu" aria-labelledby="bulk-actions-btn">
                    <li><a class="dropdown-item" href="#" onclick="bulkUpdateDelivery()">
                        <i class="fas fa-truck"></i> Cập nhật Giao hàng
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="bulkCancel()">
                        <i class="fas fa-times"></i> Huỷ
                    </a></li>
                </ul>
            </div>
        `;

        // Insert before the "Xuất Excel" button
        $('.btn:contains("Xuất Excel")').before(bulkActionsHtml);
    }
}

/**
 * Update bulk actions visibility based on selected checkboxes
 */
function updateBulkActionsVisibility() {
    var checkedCount = $('#kt_invoice_table tbody input[type="checkbox"]:checked').length;

    if (checkedCount > 0) {
        $('#bulk-actions-container').show();
    } else {
        $('#bulk-actions-container').hide();
    }
}

/**
 * Bulk update delivery status
 */
function bulkUpdateDelivery() {
    var selectedIds = [];
    $('#kt_invoice_table tbody input[type="checkbox"]:checked').each(function() {
        var row = $(this).closest('tr');
        var invoiceId = $(this).val();
        if (invoiceId) {
            selectedIds.push(invoiceId);
        }
    });

    if (selectedIds.length === 0) {
        Swal.fire({
            title: 'Thông báo',
            text: 'Vui lòng chọn ít nhất một hóa đơn',
            icon: 'warning'
        });
        return;
    }

    Swal.fire({
        title: 'Cập nhật trạng thái giao hàng',
        text: `Bạn có muốn cập nhật trạng thái giao hàng cho ${selectedIds.length} hóa đơn đã chọn?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Cập nhật',
        cancelButtonText: 'Hủy'
    }).then((result) => {
        if (result.isConfirmed) {
            // TODO: Implement bulk delivery update
            console.log('Bulk update delivery for IDs:', selectedIds);

            Swal.fire({
                title: 'Thành công',
                text: 'Đã cập nhật trạng thái giao hàng',
                icon: 'success'
            });

            // Reload table
            if (window.invoiceTable) {
                window.invoiceTable.ajax.reload();
            }
        }
    });
}

/**
 * Bulk cancel invoices
 */
function bulkCancel() {
    var selectedIds = [];
    $('#kt_invoice_table tbody input[type="checkbox"]:checked').each(function() {
        var invoiceId = $(this).val();
        if (invoiceId) {
            selectedIds.push(invoiceId);
        }
    });

    if (selectedIds.length === 0) {
        Swal.fire({
            title: 'Thông báo',
            text: 'Vui lòng chọn ít nhất một hóa đơn',
            icon: 'warning'
        });
        return;
    }

    Swal.fire({
        title: 'Hủy hóa đơn',
        text: `Bạn có chắc chắn muốn hủy ${selectedIds.length} hóa đơn đã chọn?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Hủy hóa đơn',
        cancelButtonText: 'Không',
        confirmButtonColor: '#d33'
    }).then((result) => {
        if (result.isConfirmed) {
            // TODO: Implement bulk cancel
            console.log('Bulk cancel for IDs:', selectedIds);

            Swal.fire({
                title: 'Thành công',
                text: 'Đã hủy các hóa đơn đã chọn',
                icon: 'success'
            });

            // Reload table
            if (window.invoiceTable) {
                window.invoiceTable.ajax.reload();
            }
        }
    });
}

// Get selected invoice IDs
function getSelectedInvoiceIds() {
    const selectedIds = [];

    // Get from DataTable API using checkbox method
    if (window.invoiceTable) {
        const table = window.invoiceTable;
        const checkboxes = $('#kt_invoices_table tbody input[type="checkbox"]:checked');

        checkboxes.each(function() {
            const row = $(this).closest('tr');
            const rowData = table.row(row).data();
            if (rowData && rowData.id) {
                selectedIds.push(rowData.id);
            }
        });
    } else {
        // Fallback: get from checkbox value attribute
        const checkboxes = document.querySelectorAll('#kt_invoices_table tbody input[type="checkbox"]:checked');
        checkboxes.forEach(checkbox => {
            const invoiceId = checkbox.value;
            if (invoiceId) {
                selectedIds.push(invoiceId);
            }
        });
    }

    console.log('Selected invoice IDs:', selectedIds);
    return selectedIds;
}

/**
 * Initialize Bootstrap tabs for detail panel
 */
function initDetailPanelTabs(rowElement) {
    console.log('Initializing detail panel tabs for row:', rowElement);

    // Find all tab links in the detail panel
    var $detailPanel = $(rowElement).next('tr').find('.card-body');
    var $tabLinks = $detailPanel.find('a[data-bs-toggle="tab"]');

    console.log('Found tab links:', $tabLinks.length);

    if ($tabLinks.length > 0) {
        // Remove any existing event handlers to prevent duplicates
        $tabLinks.off('click.detailTab');

        // Add click handler for each tab
        $tabLinks.on('click.detailTab', function(e) {
            e.preventDefault();
            e.stopPropagation();

            var $clickedTab = $(this);
            var targetId = $clickedTab.attr('href');

            console.log('Detail panel tab clicked:', targetId);

            // Remove active class from all tabs in this detail panel
            $detailPanel.find('.nav-link').removeClass('active');
            $clickedTab.addClass('active');

            // Hide all tab panes in this detail panel
            $detailPanel.find('.tab-pane').removeClass('show active');

            // Show the target tab pane
            $detailPanel.find(targetId).addClass('show active');

            console.log('Tab switched to:', targetId);
        });

        console.log('Detail panel tabs initialized successfully');
    } else {
        console.log('No tabs found in detail panel');
    }
}
// Initialize when DOM is ready
$(document).ready(function() {
    KTInvoicesList.init();
    // Expose the DataTable instance
    window.invoiceTable = KTInvoicesList.getTable();

    // Expose functions to global scope
    console.log('Exposing functions to global scope...');
    window.showPrintModal = showPrintModal;
    window.sendInvoice = sendInvoice;
    window.exportInvoice = exportInvoice;
    window.recordPayment = recordPayment;
    console.log('Functions exposed:', {
        showPrintModal: typeof window.showPrintModal,
        sendInvoice: typeof window.sendInvoice,
        exportInvoice: typeof window.exportInvoice,
        recordPayment: typeof window.recordPayment
    });
});
