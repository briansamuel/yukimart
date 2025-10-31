@extends('layouts.app')

@section('title', 'Chọn Cửa Hàng')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-store me-2"></i>
                        Chọn Cửa Hàng
                    </h4>
                </div>
                
                <div class="card-body p-6">
                    @if($tenants->isEmpty())
                        <div class="text-center py-8">
                            <div class="mb-4">
                                <i class="fas fa-store-slash text-muted" style="font-size: 4rem;"></i>
                            </div>
                            <h5 class="text-muted mb-3">Không có cửa hàng nào</h5>
                            <p class="text-muted mb-4">
                                Bạn chưa được thêm vào cửa hàng nào hoặc chưa có cửa hàng nào được tạo.
                            </p>
                            <div class="d-flex gap-3 justify-content-center">
                                <a href="{{ route('tenant.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>
                                    Tạo Cửa Hàng Mới
                                </a>
                                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>
                                    Quay Lại
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="mb-4">
                            <p class="text-muted">
                                Chọn cửa hàng bạn muốn truy cập. Bạn có thể chuyển đổi giữa các cửa hàng bất kỳ lúc nào.
                            </p>
                        </div>

                        <div class="row g-4">
                            @foreach($tenants as $tenant)
                                <div class="col-md-6">
                                    <div class="card tenant-card h-100 {{ $currentTenant && $currentTenant->id === $tenant->id ? 'border-primary' : '' }}" 
                                         data-tenant-id="{{ $tenant->id }}">
                                        <div class="card-body d-flex flex-column">
                                            <div class="d-flex align-items-start mb-3">
                                                <div class="flex-grow-1">
                                                    <h5 class="card-title mb-1">{{ $tenant->name }}</h5>
                                                    <small class="text-muted">{{ $tenant->slug }}</small>
                                                </div>
                                                <div class="ms-3">
                                                    @if($currentTenant && $currentTenant->id === $tenant->id)
                                                        <span class="badge bg-success">
                                                            <i class="fas fa-check me-1"></i>
                                                            Hiện tại
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span class="text-muted small">Vai trò:</span>
                                                    <span class="badge bg-info">
                                                        {{ $tenant->pivot->role === 'owner' ? 'Chủ sở hữu' : 
                                                           ($tenant->pivot->role === 'admin' ? 'Quản trị viên' : 
                                                           ($tenant->pivot->role === 'manager' ? 'Quản lý' : 
                                                           ($tenant->pivot->role === 'staff' ? 'Nhân viên' : 'Xem'))) }}
                                                    </span>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span class="text-muted small">Gói:</span>
                                                    <span class="badge bg-secondary">
                                                        {{ $tenant->plan_type === 'trial' ? 'Dùng thử' : 
                                                           ($tenant->plan_type === 'basic' ? 'Cơ bản' : 
                                                           ($tenant->plan_type === 'premium' ? 'Cao cấp' : 
                                                           ($tenant->plan_type === 'enterprise' ? 'Doanh nghiệp' : 'Tùy chỉnh'))) }}
                                                    </span>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="text-muted small">Trạng thái:</span>
                                                    <span class="badge {{ $tenant->status === 'active' ? 'bg-success' : 
                                                                         ($tenant->status === 'trial' ? 'bg-warning' : 'bg-danger') }}">
                                                        {{ $tenant->status === 'active' ? 'Hoạt động' : 
                                                           ($tenant->status === 'trial' ? 'Dùng thử' : 
                                                           ($tenant->status === 'suspended' ? 'Tạm ngưng' : 'Hết hạn')) }}
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="mt-auto">
                                                @if($currentTenant && $currentTenant->id === $tenant->id)
                                                    <a href="{{ route('dashboard') }}" class="btn btn-success w-100">
                                                        <i class="fas fa-arrow-right me-2"></i>
                                                        Tiếp Tục
                                                    </a>
                                                @else
                                                    <button type="button" 
                                                            class="btn btn-primary w-100 btn-switch-tenant"
                                                            data-tenant-id="{{ $tenant->id }}"
                                                            data-tenant-name="{{ $tenant->name }}">
                                                        <i class="fas fa-exchange-alt me-2"></i>
                                                        Chuyển Đổi
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="text-center mt-6">
                            <div class="d-flex gap-3 justify-content-center">
                                <a href="{{ route('tenant.create') }}" class="btn btn-outline-primary">
                                    <i class="fas fa-plus me-2"></i>
                                    Tạo Cửa Hàng Mới
                                </a>
                                @if($currentTenant)
                                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-arrow-left me-2"></i>
                                        Quay Lại Dashboard
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mb-0">Đang chuyển đổi cửa hàng...</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.tenant-card {
    transition: all 0.3s ease;
    cursor: pointer;
}

.tenant-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.tenant-card.border-primary {
    border-width: 2px !important;
}

.card-body {
    min-height: 200px;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Handle tenant switching
    $('.btn-switch-tenant').on('click', function() {
        const tenantId = $(this).data('tenant-id');
        const tenantName = $(this).data('tenant-name');
        
        // Show loading modal
        $('#loadingModal').modal('show');
        
        // Disable all buttons
        $('.btn-switch-tenant').prop('disabled', true);
        
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
                    toastr.success(response.message);
                    
                    // Redirect to dashboard
                    setTimeout(function() {
                        window.location.href = response.redirect_url || '{{ route("dashboard") }}';
                    }, 1000);
                } else {
                    $('#loadingModal').modal('hide');
                    $('.btn-switch-tenant').prop('disabled', false);
                    toastr.error(response.message || 'Không thể chuyển đổi cửa hàng');
                }
            },
            error: function(xhr) {
                $('#loadingModal').modal('hide');
                $('.btn-switch-tenant').prop('disabled', false);
                
                let message = 'Đã xảy ra lỗi khi chuyển đổi cửa hàng';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                
                toastr.error(message);
            }
        });
    });
    
    // Handle card click
    $('.tenant-card').on('click', function(e) {
        if (!$(e.target).hasClass('btn') && !$(e.target).closest('.btn').length) {
            const button = $(this).find('.btn-switch-tenant');
            if (button.length) {
                button.click();
            }
        }
    });
});
</script>
@endpush
