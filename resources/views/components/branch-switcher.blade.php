@props(['currentBranchShop' => null, 'availableBranchShops' => collect(), 'canSeeBranchSwitcher' => false, 'userRole' => null])

@if($canSeeBranchSwitcher)
<div class="dropdown" id="branchSwitcher">
    <button class="btn btn-light dropdown-toggle d-flex align-items-center"
            type="button"
            id="branchSwitcherDropdown"
            data-bs-toggle="dropdown"
            aria-expanded="false">
        <i class="fas fa-store me-2"></i>
        <div class="d-flex flex-column align-items-start">
            @if($currentBranchShop)
                <span class="fw-bold">{{ $currentBranchShop->name }}</span>
                <small class="text-muted">{{ $currentBranchShop->code }}</small>
            @else
                <span class="text-muted">Chọn chi nhánh</span>
            @endif
        </div>
    </button>
    
    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="branchSwitcherDropdown" style="min-width: 300px;">
        @if($currentBranchShop)
            <li class="dropdown-header d-flex align-items-center justify-content-between">
                <span>Chi nhánh hiện tại</span>
                <span class="badge bg-success">Đang hoạt động</span>
            </li>
            <li>
                <div class="dropdown-item-text">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="fw-bold">{{ $currentBranchShop->name }}</div>
                            <small class="text-muted">{{ $currentBranchShop->code }}</small>
                            @if($currentBranchShop->address)
                                <div class="text-muted small">{{ Str::limit($currentBranchShop->address, 50) }}</div>
                            @endif
                        </div>
                        <div class="ms-2">
                            @if($currentBranchShop->pivot && $currentBranchShop->pivot->is_primary)
                                <span class="badge bg-warning text-dark me-1">
                                    <i class="fas fa-star"></i> Chính
                                </span>
                            @endif
                            <span class="badge bg-primary">
                                {{ $currentBranchShop->shop_type === 'flagship' ? 'Flagship' :
                                   ($currentBranchShop->shop_type === 'standard' ? 'Tiêu chuẩn' :
                                   ($currentBranchShop->shop_type === 'mini' ? 'Mini' : 'Kiosk')) }}
                            </span>
                        </div>
                    </div>
                </div>
            </li>
            <li><hr class="dropdown-divider"></li>
        @endif
        
        @if($availableBranchShops->isNotEmpty())
            <li class="dropdown-header">
                Chi nhánh khả dụng
                @if($userRole === 'owner')
                    <small class="text-muted ms-2">(Tất cả chi nhánh)</small>
                @else
                    <small class="text-muted ms-2">(Chi nhánh được gán)</small>
                @endif
            </li>
            @foreach($availableBranchShops as $branchShop)
                @if(!$currentBranchShop || $branchShop->id !== $currentBranchShop->id)
                    <li>
                        <a class="dropdown-item branch-switch-item"
                           href="#"
                           data-branch-id="{{ $branchShop->id }}"
                           data-branch-name="{{ $branchShop->name }}"
                           data-branch-code="{{ $branchShop->code }}">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <div class="fw-medium">
                                        {{ $branchShop->name }}
                                        @if($branchShop->pivot && $branchShop->pivot->is_primary)
                                            <span class="badge bg-warning text-dark ms-1" style="font-size: 0.7rem;">
                                                <i class="fas fa-star"></i> Chính
                                            </span>
                                        @endif
                                    </div>
                                    <small class="text-muted">{{ $branchShop->code }}</small>
                                    @if($branchShop->address)
                                        <div class="text-muted small">{{ Str::limit($branchShop->address, 50) }}</div>
                                    @endif
                                </div>
                                <div class="ms-2">
                                    <span class="badge bg-outline-secondary">
                                        {{ $branchShop->shop_type === 'flagship' ? 'Flagship' :
                                           ($branchShop->shop_type === 'standard' ? 'Tiêu chuẩn' :
                                           ($branchShop->shop_type === 'mini' ? 'Mini' : 'Kiosk')) }}
                                    </span>
                                    @if($branchShop->status === 'active')
                                        <span class="badge bg-success ms-1">
                                            <i class="fas fa-check-circle"></i>
                                        </span>
                                    @elseif($branchShop->status === 'maintenance')
                                        <span class="badge bg-warning ms-1">
                                            <i class="fas fa-tools"></i>
                                        </span>
                                    @else
                                        <span class="badge bg-secondary ms-1">
                                            <i class="fas fa-pause-circle"></i>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    </li>
                @endif
            @endforeach
        @else
            <li>
                <div class="dropdown-item-text text-center text-muted py-3">
                    <i class="fas fa-store-slash mb-2"></i>
                    <div>Không có chi nhánh khả dụng</div>
                </div>
            </li>
        @endif
        
        <li><hr class="dropdown-divider"></li>
        <li>
            <a class="dropdown-item text-primary" href="{{ route('admin.branch-shops.index') }}">
                <i class="fas fa-cog me-2"></i>
                Quản lý chi nhánh
            </a>
        </li>
    </ul>
</div>

<!-- Loading overlay for branch switching -->
<div id="branchSwitchingOverlay" class="d-none position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-50" style="z-index: 9999;">
    <div class="d-flex align-items-center justify-content-center h-100">
        <div class="bg-white rounded p-4 text-center">
            <div class="spinner-border text-primary mb-3" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <div class="fw-bold">Đang chuyển chi nhánh...</div>
            <small class="text-muted">Vui lòng đợi trong giây lát</small>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Handle branch switching from dropdown
    $('.branch-switch-item').on('click', function(e) {
        e.preventDefault();
        
        const branchId = $(this).data('branch-id');
        const branchName = $(this).data('branch-name');
        const branchCode = $(this).data('branch-code');
        
        switchBranch(branchId, branchName, branchCode);
    });
    
    // Function to switch branch
    function switchBranch(branchId, branchName, branchCode) {
        // Show loading overlay
        $('#branchSwitchingOverlay').removeClass('d-none');

        // Disable dropdown
        $('#branchSwitcherDropdown').prop('disabled', true);

        // Make AJAX request to switch branch
        $.ajax({
            url: '{{ route("admin.branch-shops.switch") }}',
            method: 'POST',
            data: {
                branch_shop_id: branchId,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    // Save branch ID to localStorage
                    localStorage.setItem('current_branch_shop_id', branchId);
                    localStorage.setItem('current_branch_shop_name', branchName);
                    localStorage.setItem('current_branch_shop_code', branchCode);
                    localStorage.setItem('branch_switch_timestamp', new Date().toISOString());

                    // Show success message
                    if (typeof toastr !== 'undefined') {
                        toastr.success(response.message);
                    }

                    // Reload page to update context
                    setTimeout(function() {
                        window.location.reload();
                    }, 500);
                } else {
                    handleSwitchError(response.message || 'Không thể chuyển chi nhánh');
                }
            },
            error: function(xhr) {
                let message = 'Đã xảy ra lỗi khi chuyển chi nhánh';
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
        $('#branchSwitchingOverlay').addClass('d-none');

        // Re-enable dropdown
        $('#branchSwitcherDropdown').prop('disabled', false);

        // Show error message
        if (typeof toastr !== 'undefined') {
            toastr.error(message);
        } else {
            alert(message);
        }
    }
    
    // Function to get current branch from localStorage
    function getCurrentBranchFromStorage() {
        try {
            const branchId = localStorage.getItem('current_branch_shop_id');
            const branchName = localStorage.getItem('current_branch_shop_name');
            const branchCode = localStorage.getItem('current_branch_shop_code');
            const timestamp = localStorage.getItem('branch_switch_timestamp');
            
            if (branchId && branchName) {
                return {
                    id: parseInt(branchId),
                    name: branchName,
                    code: branchCode,
                    timestamp: timestamp
                };
            }
        } catch (e) {
            console.warn('Failed to get branch from localStorage:', e);
        }
        return null;
    }
    
    // Function to clear branch from localStorage
    function clearBranchFromStorage() {
        localStorage.removeItem('current_branch_shop_id');
        localStorage.removeItem('current_branch_shop_name');
        localStorage.removeItem('current_branch_shop_code');
        localStorage.removeItem('branch_switch_timestamp');
    }
    
    // Expose functions globally for use in other scripts
    window.branchSwitcher = {
        getCurrentBranch: getCurrentBranchFromStorage,
        clearBranch: clearBranchFromStorage,
        switchBranch: switchBranch
    };
    
    // Auto-refresh branch list periodically (every 10 minutes)
    setInterval(function() {
        refreshBranchList();
    }, 600000);
    
    // Function to refresh branch list
    function refreshBranchList() {
        $.ajax({
            url: '{{ route("admin.branch-shops.available") }}',
            method: 'GET',
            success: function(response) {
                // Update available branches if needed
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
@endif
