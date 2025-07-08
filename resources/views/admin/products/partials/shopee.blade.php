<!--begin::Card-->
<div class="card">
    <!--begin::Card header-->
    <div class="card-header border-0 pt-6">
        <!--begin::Card title-->
        <div class="card-title">
            <h3 class="fw-bold m-0">{{ __('Shopee Integration') }}</h3>
        </div>
        <!--end::Card title-->
        <!--begin::Card toolbar-->
        <div class="card-toolbar">
            <div class="d-flex justify-content-end" data-kt-user-table-toolbar="base">
                <button type="button" class="btn btn-light-primary btn-sm me-3" id="searchShopeeBtn">
                    <i class="ki-duotone ki-magnifier fs-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    {{ __('Search on Shopee') }}
                </button>
                <button type="button" class="btn btn-primary btn-sm" id="linkToShopeeBtn">
                    <i class="ki-duotone ki-plus fs-2"></i>
                    {{ __('Link to Shopee') }}
                </button>
            </div>
        </div>
        <!--end::Card toolbar-->
    </div>
    <!--end::Card header-->

    <!--begin::Card body-->
    <div class="card-body py-4">
        <!--begin::Connection Status-->
        <div class="row mb-6">
            <div class="col-lg-6">
                <div class="card card-flush bg-light-info">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="symbol symbol-50px me-5">
                                <img src="{{ asset('admin/media/svg/brand-logos/shopee.svg') }}" alt="Shopee" class="w-100" />
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-bold text-dark fs-6">{{ __('Shopee Connection Status') }}</div>
                                <div class="text-muted fs-7" id="shopeeConnectionStatus">
                                    <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                    {{ __('Checking connection...') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card card-flush bg-light-success">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="symbol symbol-50px me-5">
                                <div class="symbol-label bg-success">
                                    <i class="ki-duotone ki-package text-white fs-2x">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                    </i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-bold text-dark fs-6">{{ __('Product Links') }}</div>
                                <div class="text-muted fs-7" id="productLinksCount">
                                    <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                    {{ __('Loading...') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Connection Status-->

        <!--begin::Linked Products Table-->
        <div class="table-responsive">
            <table class="table align-middle table-row-dashed fs-6 gy-5" id="shopeeLinksTable">
                <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th class="min-w-125px">{{ __('Shop Name') }}</th>
                        <th class="min-w-125px">{{ __('Shopee Item ID') }}</th>
                        <th class="min-w-125px">{{ __('SKU') }}</th>
                        <th class="min-w-125px">{{ __('Name') }}</th>
                        <th class="min-w-100px">{{ __('Price') }}</th>
                        <th class="min-w-100px">{{ __('Stock') }}</th>
                        <th class="min-w-100px">{{ __('Status') }}</th>
                        <th class="min-w-100px">{{ __('Last Synced') }}</th>
                        <th class="text-end min-w-100px">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 fw-semibold" id="shopeeLinksTableBody">
                    <tr>
                        <td colspan="9" class="text-center py-10">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">{{ __('Loading...') }}</span>
                            </div>
                            <p class="text-muted mt-3">{{ __('Loading Shopee links...') }}</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <!--end::Linked Products Table-->

        <!--begin::Empty State-->
        <div id="emptyState" class="text-center py-10" style="display: none;">
            <img src="{{ asset('admin/media/illustrations/sketchy-1/2.png') }}" alt="" class="h-200px">
            <h3 class="text-gray-800 fw-bold mt-5">{{ __('No Shopee links found') }}</h3>
            <p class="text-gray-600 fw-semibold fs-6 mt-2">{{ __('This product is not linked to any Shopee items yet') }}</p>
            <button type="button" class="btn btn-primary mt-5" id="linkFirstItemBtn">
                <i class="ki-duotone ki-plus fs-2"></i>
                {{ __('Link to Shopee Item') }}
            </button>
        </div>
        <!--end::Empty State-->
    </div>
    <!--end::Card body-->
</div>
<!--end::Card-->

<!--begin::Modals-->
<!--begin::Search Shopee Modal-->
<div class="modal fade" id="searchShopeeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">{{ __('Search Product on Shopee') }}</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                </div>
            </div>
            <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                <form id="searchShopeeForm">
                    <div class="fv-row mb-7">
                        <label class="required fw-semibold fs-6 mb-2">{{ __('Search by SKU') }}</label>
                        <input type="text" name="sku" class="form-control form-control-solid mb-3 mb-lg-0" 
                               placeholder="{{ __('Enter SKU to search') }}" value="{{ $product->sku }}" />
                    </div>
                    <div class="fv-row mb-7">
                        <label class="fw-semibold fs-6 mb-2">{{ __('Shop ID (Optional)') }}</label>
                        <select name="shop_id" class="form-select form-select-solid" data-control="select2" data-placeholder="{{ __('Select shop') }}">
                            <option value="">{{ __('All shops') }}</option>
                        </select>
                    </div>
                </form>
                
                <div id="searchResults" style="display: none;">
                    <div class="separator my-5"></div>
                    <h4 class="fw-bold mb-5">{{ __('Search Results') }}</h4>
                    <div id="searchResultsContainer"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-primary" id="performSearchBtn">
                    {{ __('Search') }}
                </button>
            </div>
        </div>
    </div>
</div>
<!--end::Search Shopee Modal-->

<!--begin::Link Product Modal-->
<div class="modal fade" id="linkProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">{{ __('Link Product to Shopee') }}</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                </div>
            </div>
            <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                <form id="linkProductForm">
                    <input type="hidden" name="product_id" value="{{ $product->id }}" />
                    <div class="fv-row mb-7">
                        <label class="required fw-semibold fs-6 mb-2">{{ __('Shopee Item ID') }}</label>
                        <input type="number" name="shopee_item_id" class="form-control form-control-solid mb-3 mb-lg-0" 
                               placeholder="{{ __('Enter Shopee Item ID') }}" />
                    </div>
                    <div class="fv-row mb-7">
                        <label class="fw-semibold fs-6 mb-2">{{ __('Shop ID (Optional)') }}</label>
                        <select name="shop_id" class="form-select form-select-solid" data-control="select2" data-placeholder="{{ __('Select shop') }}">
                            <option value="">{{ __('Auto-detect') }}</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-primary" id="performLinkBtn">
                    {{ __('Link Product') }}
                </button>
            </div>
        </div>
    </div>
</div>
<!--end::Link Product Modal-->
<!--end::Modals-->

<script>
$(document).ready(function() {
    // Initialize
    loadShopeeStatus();
    loadProductLinks();
    loadShopOptions();

    // Event handlers
    $('#searchShopeeBtn').click(function() {
        $('#searchShopeeModal').modal('show');
    });

    $('#linkToShopeeBtn, #linkFirstItemBtn').click(function() {
        $('#linkProductModal').modal('show');
    });

    $('#performSearchBtn').click(function() {
        performSearch();
    });

    $('#performLinkBtn').click(function() {
        linkProduct();
    });

    // Functions
    function loadShopeeStatus() {
        $.get('{{ route("admin.shopee.status") }}')
            .done(function(response) {
                if (response.connected) {
                    $('#shopeeConnectionStatus').html(`
                        <span class="badge badge-light-success">{{ __('Connected') }}</span>
                        <span class="text-muted ms-2">${response.shop_name || response.shop_id}</span>
                    `);
                } else {
                    $('#shopeeConnectionStatus').html(`
                        <span class="badge badge-light-danger">{{ __('Not Connected') }}</span>
                        <a href="{{ route('admin.shopee.connect') }}" class="text-primary ms-2">{{ __('Connect Now') }}</a>
                    `);
                }
            })
            .fail(function() {
                $('#shopeeConnectionStatus').html(`
                    <span class="badge badge-light-warning">{{ __('Connection Error') }}</span>
                `);
            });
    }

    function loadProductLinks() {
        $.get('{{ route("admin.shopee.products.links") }}', { product_id: {{ $product->id }} })
            .done(function(response) {
                if (response.success) {
                    displayProductLinks(response.links);
                    $('#productLinksCount').text(`${response.links.length} {{ __('links found') }}`);
                } else {
                    showError(response.error || '{{ __("Failed to load product links") }}');
                }
            })
            .fail(function() {
                showError('{{ __("Failed to load product links") }}');
            });
    }

    function loadShopOptions() {
        $.get('{{ route("admin.shopee.status") }}')
            .done(function(response) {
                if (response.connected) {
                    const option = `<option value="${response.shop_id}">${response.shop_name || response.shop_id}</option>`;
                    $('select[name="shop_id"]').append(option);
                }
            });
    }

    function displayProductLinks(links) {
        const tbody = $('#shopeeLinksTableBody');
        
        if (links.length === 0) {
            tbody.hide();
            $('#emptyState').show();
            return;
        }

        tbody.show();
        $('#emptyState').hide();

        let html = '';
        links.forEach(function(link) {
            const statusBadge = link.status === 'active' 
                ? '<span class="badge badge-light-success">{{ __("Active") }}</span>'
                : '<span class="badge badge-light-danger">{{ __("Inactive") }}</span>';

            const lastSynced = link.last_synced_at 
                ? new Date(link.last_synced_at).toLocaleDateString()
                : '{{ __("Never") }}';

            html += `
                <tr>
                    <td>${link.shop_name || 'N/A'}</td>
                    <td>${link.marketplace_item_id}</td>
                    <td>${link.sku}</td>
                    <td>
                        ${link.marketplace_url ? `<a href="${link.marketplace_url}" target="_blank" class="text-primary">${link.name}</a>` : link.name}
                    </td>
                    <td>${link.price ? formatCurrency(link.price) : 'N/A'}</td>
                    <td>${link.stock_quantity || 0}</td>
                    <td>${statusBadge}</td>
                    <td>${lastSynced}</td>
                    <td class="text-end">
                        <div class="d-flex justify-content-end">
                            <button class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1 sync-inventory-btn" 
                                    data-link-id="${link.id}" title="{{ __('Sync Inventory') }}">
                                <i class="ki-duotone ki-arrows-circle fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </button>
                            <button class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm unlink-btn" 
                                    data-link-id="${link.id}" title="{{ __('Unlink') }}">
                                <i class="ki-duotone ki-trash fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                    <span class="path4"></span>
                                    <span class="path5"></span>
                                </i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });

        tbody.html(html);

        // Bind action buttons
        $('.sync-inventory-btn').click(function() {
            const linkId = $(this).data('link-id');
            syncInventory(linkId);
        });

        $('.unlink-btn').click(function() {
            const linkId = $(this).data('link-id');
            unlinkProduct(linkId);
        });
    }

    function performSearch() {
        const btn = $('#performSearchBtn');
        const form = $('#searchShopeeForm');

        // Show simple loading
        const originalText = btn.html();
        btn.html('Đang tìm... <span class="spinner-border spinner-border-sm ms-2"></span>');
        btn.prop('disabled', true);

        const formData = {
            sku: form.find('input[name="sku"]').val(),
            shop_id: form.find('select[name="shop_id"]').val()
        };

        $.post('{{ route("admin.shopee.products.search-by-sku") }}', formData)
            .done(function(response) {
                btn.html(originalText);
                btn.prop('disabled', false);

                if (response.success) {
                    displaySearchResults(response.products);
                } else {
                    showError(response.error || '{{ __("Search failed") }}');
                }
            })
            .fail(function() {
                btn.html(originalText);
                btn.prop('disabled', false);
                showError('{{ __("Search failed") }}');
            });
    }

    function displaySearchResults(products) {
        const container = $('#searchResultsContainer');
        
        if (products.length === 0) {
            container.html('<p class="text-muted">{{ __("No products found") }}</p>');
        } else {
            let html = '<div class="row g-3">';
            products.forEach(function(product) {
                html += `
                    <div class="col-12">
                        <div class="card card-flush border">
                            <div class="card-body p-5">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <h5 class="fw-bold mb-1">${product.item_name}</h5>
                                        <p class="text-muted mb-2">ID: ${product.item_id} | SKU: ${product.item_sku || 'N/A'}</p>
                                        <p class="text-muted mb-0">{{ __('Price') }}: ${formatCurrency(product.price_info?.current_price || 0)}</p>
                                    </div>
                                    <button class="btn btn-sm btn-primary link-item-btn" 
                                            data-item-id="${product.item_id}"
                                            data-item-name="${product.item_name}">
                                        {{ __('Link This Item') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            html += '</div>';
            container.html(html);

            // Bind link buttons
            $('.link-item-btn').click(function() {
                const itemId = $(this).data('item-id');
                const itemName = $(this).data('item-name');
                
                $('#linkProductForm input[name="shopee_item_id"]').val(itemId);
                $('#searchShopeeModal').modal('hide');
                $('#linkProductModal').modal('show');
            });
        }

        $('#searchResults').show();
    }

    function linkProduct() {
        const btn = $('#performLinkBtn');
        const form = $('#linkProductForm');

        // Show simple loading
        const originalText = btn.html();
        btn.html('Đang liên kết... <span class="spinner-border spinner-border-sm ms-2"></span>');
        btn.prop('disabled', true);

        const formData = {
            product_id: form.find('input[name="product_id"]').val(),
            shopee_item_id: form.find('input[name="shopee_item_id"]').val(),
            shop_id: form.find('select[name="shop_id"]').val()
        };

        $.post('{{ route("admin.shopee.products.link") }}', formData)
            .done(function(response) {
                btn.html(originalText);
                btn.prop('disabled', false);

                if (response.success) {
                    showSuccess('{{ __("Product linked successfully") }}');
                    $('#linkProductModal').modal('hide');
                    loadProductLinks();
                } else {
                    showError(response.error || '{{ __("Failed to link product") }}');
                }
            })
            .fail(function() {
                btn.html(originalText);
                btn.prop('disabled', false);
                showError('{{ __("Failed to link product") }}');
            });
    }

    function syncInventory(linkId) {
        showLoading('{{ __("Syncing inventory...") }}');
        
        $.post('{{ route("admin.shopee.products.sync-inventory") }}', { link_id: linkId })
            .done(function(response) {
                hideLoading();
                
                if (response.success) {
                    showSuccess('{{ __("Inventory synced successfully") }}');
                    loadProductLinks();
                } else {
                    showError(response.error || '{{ __("Failed to sync inventory") }}');
                }
            })
            .fail(function() {
                hideLoading();
                showError('{{ __("Failed to sync inventory") }}');
            });
    }

    function unlinkProduct(linkId) {
        if (!confirm('{{ __("Are you sure you want to unlink this product?") }}')) {
            return;
        }
        
        showLoading('{{ __("Unlinking product...") }}');
        
        $.post('{{ route("admin.shopee.products.unlink") }}', { link_id: linkId })
            .done(function(response) {
                hideLoading();
                
                if (response.success) {
                    showSuccess('{{ __("Product unlinked successfully") }}');
                    loadProductLinks();
                } else {
                    showError(response.error || '{{ __("Failed to unlink product") }}');
                }
            })
            .fail(function() {
                hideLoading();
                showError('{{ __("Failed to unlink product") }}');
            });
    }

    function formatCurrency(amount) {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(amount);
    }

    function showLoading(message) {
        Swal.fire({
            title: message,
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    }

    function hideLoading() {
        Swal.close();
    }

    function showSuccess(message) {
        Swal.fire({
            icon: 'success',
            title: '{{ __("Success") }}',
            text: message,
            timer: 3000,
            showConfirmButton: false
        });
    }

    function showError(message) {
        Swal.fire({
            icon: 'error',
            title: '{{ __("Error") }}',
            text: message
        });
    }
});
</script>
