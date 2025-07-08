@extends('admin.main-content')

@section('title', __('Shopee Integration Dashboard'))

@section('content')
<div class="d-flex flex-column flex-column-fluid">
    <!--begin::Toolbar-->
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                    {{ __('Shopee Integration') }}
                </h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">{{ __('Dashboard') }}</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-400 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-muted">{{ __('Shopee Integration') }}</li>
                </ul>
            </div>
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <button type="button" class="btn btn-sm btn-primary" id="connectShopeeBtn">
                    <i class="ki-duotone ki-plus fs-2"></i>
                    {{ __('Connect New Shop') }}
                </button>
            </div>
        </div>
    </div>
    <!--end::Toolbar-->

    <!--begin::Content-->
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-xxl">
            
            <!-- Connection Status Cards -->
            <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
                <div class="col-md-6 col-lg-6 col-xl-6 col-xxl-3 mb-md-5 mb-xl-10">
                    <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end h-md-50 mb-5 mb-xl-10" style="background-color: #F1416C;background-image:url('{{ asset('admin/media/patterns/vector-1.png') }}')">
                        <div class="card-header pt-5">
                            <div class="card-title d-flex flex-column">
                                <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2" id="totalShopsCount">{{ $tokens->count() }}</span>
                                <span class="text-white opacity-75 pt-1 fw-semibold fs-6">{{ __('Connected Shops') }}</span>
                            </div>
                        </div>
                        <div class="card-body d-flex align-items-end pt-0">
                            <div class="d-flex align-items-center flex-column mt-3 w-100">
                                <div class="d-flex justify-content-between w-100 mt-auto mb-2">
                                    <span class="fw-bolder fs-6 text-white opacity-75">{{ __('Active') }}</span>
                                    <span class="fw-bold fs-6 text-white" id="activeShopsCount">{{ $tokens->where('is_active', true)->count() }}</span>
                                </div>
                                <div class="h-8px mx-3 w-100 bg-white bg-opacity-50 rounded">
                                    <div class="bg-white rounded h-8px" role="progressbar" style="width: {{ $tokens->count() > 0 ? ($tokens->where('is_active', true)->count() / $tokens->count()) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-6 col-xl-6 col-xxl-3 mb-md-5 mb-xl-10">
                    <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end h-md-50 mb-5 mb-xl-10" style="background-color: #7239EA;background-image:url('{{ asset('admin/media/patterns/vector-2.png') }}')">
                        <div class="card-header pt-5">
                            <div class="card-title d-flex flex-column">
                                <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2" id="syncedOrdersCount">0</span>
                                <span class="text-white opacity-75 pt-1 fw-semibold fs-6">{{ __('Synced Orders') }}</span>
                            </div>
                        </div>
                        <div class="card-body d-flex align-items-end pt-0">
                            <div class="d-flex align-items-center flex-column mt-3 w-100">
                                <div class="d-flex justify-content-between w-100 mt-auto mb-2">
                                    <span class="fw-bolder fs-6 text-white opacity-75">{{ __('Today') }}</span>
                                    <span class="fw-bold fs-6 text-white" id="todayOrdersCount">0</span>
                                </div>
                                <div class="h-8px mx-3 w-100 bg-white bg-opacity-50 rounded">
                                    <div class="bg-white rounded h-8px" role="progressbar" style="width: 75%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-6 col-xl-6 col-xxl-3 mb-md-5 mb-xl-10">
                    <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end h-md-50 mb-5 mb-xl-10" style="background-color: #50CD89;background-image:url('{{ asset('admin/media/patterns/vector-3.png') }}')">
                        <div class="card-header pt-5">
                            <div class="card-title d-flex flex-column">
                                <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2" id="linkedProductsCount">0</span>
                                <span class="text-white opacity-75 pt-1 fw-semibold fs-6">{{ __('Linked Products') }}</span>
                            </div>
                        </div>
                        <div class="card-body d-flex align-items-end pt-0">
                            <div class="d-flex align-items-center flex-column mt-3 w-100">
                                <div class="d-flex justify-content-between w-100 mt-auto mb-2">
                                    <span class="fw-bolder fs-6 text-white opacity-75">{{ __('Active') }}</span>
                                    <span class="fw-bold fs-6 text-white" id="activeLinksCount">0</span>
                                </div>
                                <div class="h-8px mx-3 w-100 bg-white bg-opacity-50 rounded">
                                    <div class="bg-white rounded h-8px" role="progressbar" style="width: 60%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-6 col-xl-6 col-xxl-3 mb-md-5 mb-xl-10">
                    <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end h-md-50 mb-5 mb-xl-10" style="background-color: #F1BC00;background-image:url('{{ asset('admin/media/patterns/vector-4.png') }}')">
                        <div class="card-header pt-5">
                            <div class="card-title d-flex flex-column">
                                <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2" id="lastSyncTime">{{ __('Never') }}</span>
                                <span class="text-white opacity-75 pt-1 fw-semibold fs-6">{{ __('Last Sync') }}</span>
                            </div>
                        </div>
                        <div class="card-body d-flex align-items-end pt-0">
                            <div class="d-flex align-items-center flex-column mt-3 w-100">
                                <div class="d-flex justify-content-between w-100 mt-auto mb-2">
                                    <span class="fw-bolder fs-6 text-white opacity-75">{{ __('Status') }}</span>
                                    <span class="fw-bold fs-6 text-white" id="syncStatus">{{ __('Ready') }}</span>
                                </div>
                                <div class="h-8px mx-3 w-100 bg-white bg-opacity-50 rounded">
                                    <div class="bg-white rounded h-8px" role="progressbar" style="width: 100%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Connected Shops -->
            <div class="card mb-5 mb-xl-10">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold fs-3 mb-1">{{ __('Connected Shops') }}</span>
                        <span class="text-muted mt-1 fw-semibold fs-7">{{ __('Manage your Shopee shop connections') }}</span>
                    </h3>
                    <div class="card-toolbar">
                        <button type="button" class="btn btn-sm btn-light-primary" id="refreshTokensBtn">
                            <i class="ki-duotone ki-arrows-circle fs-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            {{ __('Refresh All') }}
                        </button>
                    </div>
                </div>
                <div class="card-body py-3">
                    @if($tokens->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4" id="shopsTable">
                                <thead>
                                    <tr class="fw-bold text-muted">
                                        <th class="min-w-150px">{{ __('Shop Name') }}</th>
                                        <th class="min-w-100px">{{ __('Shop ID') }}</th>
                                        <th class="min-w-120px">{{ __('Status') }}</th>
                                        <th class="min-w-120px">{{ __('Expires At') }}</th>
                                        <th class="min-w-120px">{{ __('Last Used') }}</th>
                                        <th class="min-w-100px text-end">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($tokens as $token)
                                    <tr data-shop-id="{{ $token->shop_id }}">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="symbol symbol-45px me-5">
                                                    <img src="{{ asset('admin/media/svg/brand-logos/shopee.svg') }}" alt="Shopee" />
                                                </div>
                                                <div class="d-flex justify-content-start flex-column">
                                                    <span class="text-dark fw-bold text-hover-primary fs-6">{{ $token->shop_name ?: 'Shop ' . $token->shop_id }}</span>
                                                    <span class="text-muted fw-semibold text-muted d-block fs-7">{{ $token->user->name ?? 'System' }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-dark fw-bold d-block fs-6">{{ $token->shop_id }}</span>
                                        </td>
                                        <td>
                                            @if($token->isValid())
                                                <span class="badge badge-light-success">{{ __('Active') }}</span>
                                            @elseif($token->isExpiringSoon())
                                                <span class="badge badge-light-warning">{{ __('Expiring Soon') }}</span>
                                            @else
                                                <span class="badge badge-light-danger">{{ __('Expired') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="text-dark fw-bold d-block fs-6">{{ $token->expired_at->format('Y-m-d H:i') }}</span>
                                            <span class="text-muted fw-semibold d-block fs-7">{{ $token->expired_at->diffForHumans() }}</span>
                                        </td>
                                        <td>
                                            @if($token->last_used_at)
                                                <span class="text-dark fw-bold d-block fs-6">{{ $token->last_used_at->format('Y-m-d H:i') }}</span>
                                                <span class="text-muted fw-semibold d-block fs-7">{{ $token->last_used_at->diffForHumans() }}</span>
                                            @else
                                                <span class="text-muted">{{ __('Never') }}</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex justify-content-end flex-shrink-0">
                                                @if($token->isExpiringSoon() || $token->isExpired())
                                                    <button class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1 refresh-token-btn" data-shop-id="{{ $token->shop_id }}">
                                                        <i class="ki-duotone ki-arrows-circle fs-2">
                                                            <span class="path1"></span>
                                                            <span class="path2"></span>
                                                        </i>
                                                    </button>
                                                @endif
                                                <button class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1 test-connection-btn" data-shop-id="{{ $token->shop_id }}">
                                                    <i class="ki-duotone ki-check-circle fs-2">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                    </i>
                                                </button>
                                                <button class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm revoke-token-btn" data-shop-id="{{ $token->shop_id }}">
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
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-10">
                            <img src="{{ asset('admin/media/illustrations/sketchy-1/2.png') }}" alt="" class="h-200px">
                            <h3 class="text-gray-800 fw-bold mt-5">{{ __('No Shopee shops connected') }}</h3>
                            <p class="text-gray-600 fw-semibold fs-6 mt-2">{{ __('Connect your first Shopee shop to start syncing orders and products') }}</p>
                            <button type="button" class="btn btn-primary mt-5" id="connectFirstShopBtn">
                                <i class="ki-duotone ki-plus fs-2"></i>
                                {{ __('Connect Shopee Shop') }}
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row g-5 g-xl-10">
                <div class="col-xl-6">
                    <div class="card card-flush h-xl-100">
                        <div class="card-header pt-5">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold text-dark">{{ __('Quick Actions') }}</span>
                                <span class="text-gray-400 mt-1 fw-semibold fs-6">{{ __('Manage your Shopee integration') }}</span>
                            </h3>
                        </div>
                        <div class="card-body pt-2">
                            <div class="d-flex flex-column gap-5">
                                <button type="button" class="btn btn-light-primary d-flex align-items-center" id="syncOrdersBtn">
                                    <i class="ki-duotone ki-arrows-circle fs-1 me-3">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                    <div class="d-flex flex-column align-items-start">
                                        <span class="fw-bold">{{ __('Sync Orders') }}</span>
                                        <span class="text-muted fs-7">{{ __('Import new orders from Shopee') }}</span>
                                    </div>
                                </button>
                                
                                <button type="button" class="btn btn-light-success d-flex align-items-center" id="syncInventoryBtn">
                                    <i class="ki-duotone ki-package fs-1 me-3">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                    </i>
                                    <div class="d-flex flex-column align-items-start">
                                        <span class="fw-bold">{{ __('Sync Inventory') }}</span>
                                        <span class="text-muted fs-7">{{ __('Update product stock on Shopee') }}</span>
                                    </div>
                                </button>
                                
                                <button type="button" class="btn btn-light-info d-flex align-items-center" id="linkProductsBtn">
                                    <i class="ki-duotone ki-link fs-1 me-3">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                    <div class="d-flex flex-column align-items-start">
                                        <span class="fw-bold">{{ __('Link Products') }}</span>
                                        <span class="text-muted fs-7">{{ __('Connect local products to Shopee items') }}</span>
                                    </div>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6">
                    <div class="card card-flush h-xl-100">
                        <div class="card-header pt-5">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold text-dark">{{ __('Sync Status') }}</span>
                                <span class="text-gray-400 mt-1 fw-semibold fs-6">{{ __('Recent synchronization activity') }}</span>
                            </h3>
                        </div>
                        <div class="card-body pt-2">
                            <div id="syncStatusContainer">
                                <div class="text-center py-5">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">{{ __('Loading...') }}</span>
                                    </div>
                                    <p class="text-muted mt-3">{{ __('Loading sync status...') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--end::Content-->
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Load initial data
    loadSyncStatus();
    loadStatistics();

    // Connect new shop
    $('#connectShopeeBtn, #connectFirstShopBtn').click(function() {
        window.location.href = '{{ route("admin.shopee.connect") }}';
    });

    // Refresh all tokens
    $('#refreshTokensBtn').click(function() {
        refreshAllTokens();
    });

    // Individual token actions
    $('.refresh-token-btn').click(function() {
        const shopId = $(this).data('shop-id');
        refreshToken(shopId);
    });

    $('.test-connection-btn').click(function() {
        const shopId = $(this).data('shop-id');
        testConnection(shopId);
    });

    $('.revoke-token-btn').click(function() {
        const shopId = $(this).data('shop-id');
        revokeToken(shopId);
    });

    // Quick actions
    $('#syncOrdersBtn').click(function() {
        syncOrders();
    });

    $('#syncInventoryBtn').click(function() {
        syncInventory();
    });

    $('#linkProductsBtn').click(function() {
        // Navigate to product linking page
        window.location.href = '{{ route("admin.products.list") }}';
    });

    function loadSyncStatus() {
        $.get('{{ route("admin.shopee.orders.sync-status") }}')
            .done(function(response) {
                if (response.success) {
                    updateSyncStatusDisplay(response.status);
                }
            })
            .fail(function() {
                $('#syncStatusContainer').html('<p class="text-muted">{{ __("Failed to load sync status") }}</p>');
            });
    }

    function loadStatistics() {
        // Load marketplace orders count
        $.get('{{ route("admin.shopee.orders.marketplace-orders") }}', { limit: 1 })
            .done(function(response) {
                if (response.success) {
                    $('#syncedOrdersCount').text(response.orders.length);
                }
            });

        // Load product links count
        $.get('{{ route("admin.shopee.products.links") }}')
            .done(function(response) {
                if (response.success) {
                    $('#linkedProductsCount').text(response.links.length);
                    const activeLinks = response.links.filter(link => link.status === 'active').length;
                    $('#activeLinksCount').text(activeLinks);
                }
            });
    }

    function updateSyncStatusDisplay(status) {
        let html = '<div class="d-flex flex-column gap-3">';
        
        if (status.last_sync_at) {
            const lastSync = new Date(status.last_sync_at);
            $('#lastSyncTime').text(lastSync.toLocaleDateString());
            
            html += `
                <div class="d-flex justify-content-between">
                    <span class="text-gray-600">{{ __('Last Sync') }}:</span>
                    <span class="fw-bold">${lastSync.toLocaleString()}</span>
                </div>
            `;
        }
        
        if (status.total_synced_orders) {
            html += `
                <div class="d-flex justify-content-between">
                    <span class="text-gray-600">{{ __('Total Orders') }}:</span>
                    <span class="fw-bold">${status.total_synced_orders}</span>
                </div>
            `;
        }
        
        html += '</div>';
        $('#syncStatusContainer').html(html);
    }

    function refreshAllTokens() {
        showLoading('{{ __("Refreshing all tokens...") }}');
        
        $.post('{{ route("admin.shopee.check-expiring-tokens") }}')
            .done(function(response) {
                hideLoading();
                if (response.success) {
                    showSuccess('{{ __("Tokens refreshed successfully") }}');
                    location.reload();
                } else {
                    showError(response.message || '{{ __("Failed to refresh tokens") }}');
                }
            })
            .fail(function() {
                hideLoading();
                showError('{{ __("Failed to refresh tokens") }}');
            });
    }

    function refreshToken(shopId) {
        showLoading('{{ __("Refreshing token...") }}');
        
        $.post('{{ route("admin.shopee.refresh") }}', { shop_id: shopId })
            .done(function(response) {
                hideLoading();
                if (response.success) {
                    showSuccess('{{ __("Token refreshed successfully") }}');
                    location.reload();
                } else {
                    showError(response.error || '{{ __("Failed to refresh token") }}');
                }
            })
            .fail(function() {
                hideLoading();
                showError('{{ __("Failed to refresh token") }}');
            });
    }

    function testConnection(shopId) {
        showLoading('{{ __("Testing connection...") }}');
        
        $.post('{{ route("admin.shopee.test-connection") }}', { shop_id: shopId })
            .done(function(response) {
                hideLoading();
                if (response.success) {
                    showSuccess('{{ __("Connection test successful") }}');
                } else {
                    showError(response.message || '{{ __("Connection test failed") }}');
                }
            })
            .fail(function() {
                hideLoading();
                showError('{{ __("Connection test failed") }}');
            });
    }

    function revokeToken(shopId) {
        if (!confirm('{{ __("Are you sure you want to revoke this token?") }}')) {
            return;
        }
        
        showLoading('{{ __("Revoking token...") }}');
        
        $.post('{{ route("admin.shopee.revoke") }}', { shop_id: shopId })
            .done(function(response) {
                hideLoading();
                if (response.success) {
                    showSuccess('{{ __("Token revoked successfully") }}');
                    location.reload();
                } else {
                    showError(response.error || '{{ __("Failed to revoke token") }}');
                }
            })
            .fail(function() {
                hideLoading();
                showError('{{ __("Failed to revoke token") }}');
            });
    }

    function syncOrders() {
        showLoading('{{ __("Syncing orders...") }}');
        
        $.post('{{ route("admin.shopee.orders.sync") }}')
            .done(function(response) {
                hideLoading();
                if (response.success) {
                    const results = response.results;
                    showSuccess(`{{ __("Orders synced") }}: ${results.success} {{ __("success") }}, ${results.failed} {{ __("failed") }}, ${results.skipped} {{ __("skipped") }}`);
                    loadStatistics();
                } else {
                    showError(response.error || '{{ __("Failed to sync orders") }}');
                }
            })
            .fail(function() {
                hideLoading();
                showError('{{ __("Failed to sync orders") }}');
            });
    }

    function syncInventory() {
        showLoading('{{ __("Syncing inventory...") }}');
        
        $.post('{{ route("admin.shopee.products.bulk-sync-inventory") }}')
            .done(function(response) {
                hideLoading();
                if (response.success) {
                    const results = response.results;
                    showSuccess(`{{ __("Inventory synced") }}: ${results.success} {{ __("success") }}, ${results.failed} {{ __("failed") }}`);
                } else {
                    showError(response.error || '{{ __("Failed to sync inventory") }}');
                }
            })
            .fail(function() {
                hideLoading();
                showError('{{ __("Failed to sync inventory") }}');
            });
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
@endpush
