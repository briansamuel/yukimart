@props(['currentTenant' => null, 'availableTenants' => collect()])

<div class="dropdown" id="tenantSwitcher">
    <button class="btn btn-light dropdown-toggle d-flex align-items-center" 
            type="button" 
            id="tenantSwitcherDropdown" 
            data-bs-toggle="dropdown" 
            aria-expanded="false">
        <i class="fas fa-store me-2"></i>
        <div class="d-flex flex-column align-items-start">
            @if($currentTenant)
                <span class="fw-bold">{{ $currentTenant->name }}</span>
                <small class="text-muted">{{ $currentTenant->slug }}</small>
            @else
                <span class="text-muted">Chọn cửa hàng</span>
            @endif
        </div>
    </button>
    
    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="tenantSwitcherDropdown" style="min-width: 300px;">
        @if($currentTenant)
            <li class="dropdown-header d-flex align-items-center justify-content-between">
                <span>Cửa hàng hiện tại</span>
                <span class="badge bg-success">Đang hoạt động</span>
            </li>
            <li>
                <div class="dropdown-item-text">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="fw-bold">{{ $currentTenant->name }}</div>
                            <small class="text-muted">{{ $currentTenant->slug }}</small>
                        </div>
                        <div class="ms-2">
                            <span class="badge bg-primary">
                                {{ $currentTenant->plan_type === 'trial' ? 'Dùng thử' : 
                                   ($currentTenant->plan_type === 'basic' ? 'Cơ bản' : 
                                   ($currentTenant->plan_type === 'premium' ? 'Cao cấp' : 
                                   ($currentTenant->plan_type === 'enterprise' ? 'Doanh nghiệp' : 'Tùy chỉnh'))) }}
                            </span>
                        </div>
                    </div>
                </div>
            </li>
            <li><hr class="dropdown-divider"></li>
        @endif
        
        @if($availableTenants->isNotEmpty())
            <li class="dropdown-header">Cửa hàng khả dụng</li>
            @foreach($availableTenants as $tenant)
                @if(!$currentTenant || $tenant->id !== $currentTenant->id)
                    <li>
                        <a class="dropdown-item tenant-switch-item" 
                           href="#" 
                           data-tenant-id="{{ $tenant->id }}"
                           data-tenant-name="{{ $tenant->name }}">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <div class="fw-medium">{{ $tenant->name }}</div>
                                    <small class="text-muted">{{ $tenant->slug }}</small>
                                </div>
                                <div class="ms-2">
                                    <span class="badge bg-outline-secondary">
                                        {{ $tenant->pivot->role === 'owner' ? 'Chủ' : 
                                           ($tenant->pivot->role === 'admin' ? 'Admin' : 
                                           ($tenant->pivot->role === 'manager' ? 'QL' : 
                                           ($tenant->pivot->role === 'staff' ? 'NV' : 'Xem'))) }}
                                    </span>
                                </div>
                            </div>
                        </a>
                    </li>
                @endif
            @endforeach
            <li><hr class="dropdown-divider"></li>
        @endif
        
        <li>
            <a class="dropdown-item" href="{{ route('tenant.select') }}">
                <i class="fas fa-list me-2"></i>
                Xem tất cả cửa hàng
            </a>
        </li>
        
        @if(auth()->user() && auth()->user()->can('create', \App\Models\Tenant::class))
            <li>
                <a class="dropdown-item" href="{{ route('tenant.create') }}">
                    <i class="fas fa-plus me-2"></i>
                    Tạo cửa hàng mới
                </a>
            </li>
        @endif
        
        @if($currentTenant)
            <li><hr class="dropdown-divider"></li>
            <li>
                <a class="dropdown-item" href="{{ route('tenant.settings') }}">
                    <i class="fas fa-cog me-2"></i>
                    Cài đặt cửa hàng
                </a>
            </li>
            <li>
                <a class="dropdown-item" href="{{ route('tenant.statistics') }}">
                    <i class="fas fa-chart-bar me-2"></i>
                    Thống kê sử dụng
                </a>
            </li>
        @endif
    </ul>
</div>

<!-- Loading overlay for tenant switching -->
<div id="tenantSwitchingOverlay" class="d-none">
    <div class="position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" 
         style="background: rgba(0,0,0,0.5); z-index: 9999;">
        <div class="bg-white rounded p-4 text-center">
            <div class="spinner-border text-primary mb-3" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mb-0">Đang chuyển đổi cửa hàng...</p>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Handle tenant switching from dropdown
    $('.tenant-switch-item').on('click', function(e) {
        e.preventDefault();
        
        const tenantId = $(this).data('tenant-id');
        const tenantName = $(this).data('tenant-name');
        
        switchTenant(tenantId, tenantName);
    });
    
    // Function to switch tenant
    function switchTenant(tenantId, tenantName) {
        // Show loading overlay
        $('#tenantSwitchingOverlay').removeClass('d-none');
        
        // Disable dropdown
        $('#tenantSwitcherDropdown').prop('disabled', true);
        
        // Make AJAX request
        $.ajax({
            url: '{{ route("tenant.switch") }}',
            method: 'POST',
            data: {
                tenant_id: tenantId,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    // Show success message
                    if (typeof toastr !== 'undefined') {
                        toastr.success(response.message);
                    }
                    
                    // Reload page to update context
                    setTimeout(function() {
                        window.location.reload();
                    }, 500);
                } else {
                    handleSwitchError(response.message || 'Không thể chuyển đổi cửa hàng');
                }
            },
            error: function(xhr) {
                let message = 'Đã xảy ra lỗi khi chuyển đổi cửa hàng';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                handleSwitchError(message);
            }
        });
    }
    
    // Function to handle switch errors
    function handleSwitchError(message) {
        // Hide loading overlay
        $('#tenantSwitchingOverlay').addClass('d-none');
        
        // Re-enable dropdown
        $('#tenantSwitcherDropdown').prop('disabled', false);
        
        // Show error message
        if (typeof toastr !== 'undefined') {
            toastr.error(message);
        } else {
            alert(message);
        }
    }
    
    // Auto-refresh tenant list periodically (every 5 minutes)
    setInterval(function() {
        refreshTenantList();
    }, 300000);
    
    // Function to refresh tenant list
    function refreshTenantList() {
        $.ajax({
            url: '{{ route("tenant.available") }}',
            method: 'GET',
            success: function(response) {
                // Update available tenants if needed
                // This is a placeholder for future enhancement
            },
            error: function() {
                // Silently fail - not critical
            }
        });
    }
});
</script>
@endpush

@push('styles')
<style>
#tenantSwitcher .dropdown-toggle {
    border: 1px solid #e4e6ef;
    background: #fff;
    min-width: 200px;
}

#tenantSwitcher .dropdown-toggle:hover {
    background: #f8f9fa;
}

#tenantSwitcher .dropdown-item {
    padding: 0.75rem 1rem;
}

#tenantSwitcher .dropdown-item:hover {
    background: #f8f9fa;
}

#tenantSwitcher .dropdown-header {
    font-weight: 600;
    color: #5e6278;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

#tenantSwitcher .badge {
    font-size: 0.65rem;
}

.tenant-switch-item {
    transition: all 0.2s ease;
}

.tenant-switch-item:hover {
    background: #f1f3ff !important;
    color: #3f4254;
}
</style>
@endpush
