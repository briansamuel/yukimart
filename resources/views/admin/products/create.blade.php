@extends('admin.layouts.app')

@section('title', 'Thêm sản phẩm mới')

@section('content')
<div class="d-flex flex-column flex-column-fluid">
    <!-- Toolbar -->
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
            <!-- Page title -->
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                    Thêm sản phẩm mới
                </h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ url('/admin/dashboard') }}" class="text-muted text-hover-primary">Trang chủ</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-400 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ url('/admin/products') }}" class="text-muted text-hover-primary">Sản phẩm</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-400 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-muted">Thêm mới</li>
                </ul>
            </div>
        </div>
    </div>
    
    <!-- Content -->
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-xxl">
            
            <!-- Form -->
            <form id="product-form" action="{{ url('/admin/products') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="row g-9">
                    <!-- Left Column -->
                    <div class="col-lg-8">
                        <!-- Basic Information -->
                        <div class="card card-flush py-4">
                            <div class="card-header">
                                <div class="card-title">
                                    <h2>Thông tin cơ bản</h2>
                                </div>
                            </div>
                            <div class="card-body pt-0">
                                <!-- Product Name -->
                                <div class="mb-10 fv-row">
                                    <label class="required form-label">Tên sản phẩm</label>
                                    <input type="text" name="product_name" class="form-control mb-2" 
                                           placeholder="Nhập tên sản phẩm" value="{{ old('product_name') }}" required />
                                    @error('product_name')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <!-- Description -->
                                <div class="mb-10">
                                    <label class="form-label">Mô tả sản phẩm</label>
                                    <textarea name="product_description" class="form-control" rows="4" 
                                              placeholder="Nhập mô tả sản phẩm">{{ old('product_description') }}</textarea>
                                    @error('product_description')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Pricing -->
                        <div class="card card-flush py-4 mt-5">
                            <div class="card-header">
                                <div class="card-title">
                                    <h2>Giá cả</h2>
                                </div>
                            </div>
                            <div class="card-body pt-0">
                                <div class="row">
                                    <!-- Cost Price -->
                                    <div class="col-md-6">
                                        <div class="mb-10 fv-row">
                                            <label class="required form-label">Giá vốn</label>
                                            <input type="number" name="cost_price" class="form-control mb-2" 
                                                   placeholder="0" value="{{ old('cost_price') }}" min="0" step="0.01" required />
                                            @error('cost_price')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <!-- Sale Price -->
                                    <div class="col-md-6">
                                        <div class="mb-10 fv-row">
                                            <label class="required form-label">Giá bán</label>
                                            <input type="number" name="sale_price" class="form-control mb-2" 
                                                   placeholder="0" value="{{ old('sale_price') }}" min="0" step="0.01" required />
                                            @error('sale_price')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Inventory -->
                        <div class="card card-flush py-4 mt-5">
                            <div class="card-header">
                                <div class="card-title">
                                    <h2>Kho hàng</h2>
                                </div>
                            </div>
                            <div class="card-body pt-0">
                                <div class="row">
                                    <!-- Initial Stock -->
                                    <div class="col-md-6">
                                        <div class="mb-10">
                                            <label class="form-label">Số lượng ban đầu</label>
                                            <input type="number" name="initial_stock" class="form-control mb-2" 
                                                   placeholder="0" value="{{ old('initial_stock', 0) }}" min="0" />
                                        </div>
                                    </div>
                                    
                                    <!-- Reorder Point -->
                                    <div class="col-md-6">
                                        <div class="mb-10">
                                            <label class="form-label">Điểm đặt hàng lại</label>
                                            <input type="number" name="reorder_point" class="form-control mb-2" 
                                                   placeholder="0" value="{{ old('reorder_point', 0) }}" min="0" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Right Column -->
                    <div class="col-lg-4">
                        <!-- Status -->
                        <div class="card card-flush py-4">
                            <div class="card-header">
                                <div class="card-title">
                                    <h2>Trạng thái</h2>
                                </div>
                            </div>
                            <div class="card-body pt-0">
                                <select name="product_status" class="form-select mb-2" required>
                                    <option value="">Chọn trạng thái</option>
                                    <option value="active" {{ old('product_status') == 'active' ? 'selected' : '' }}>Hoạt động</option>
                                    <option value="inactive" {{ old('product_status') == 'inactive' ? 'selected' : '' }}>Không hoạt động</option>
                                    <option value="out_of_stock" {{ old('product_status') == 'out_of_stock' ? 'selected' : '' }}>Hết hàng</option>
                                </select>
                                @error('product_status')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- Category -->
                        <div class="card card-flush py-4 mt-5">
                            <div class="card-header">
                                <div class="card-title">
                                    <h2>Danh mục</h2>
                                </div>
                            </div>
                            <div class="card-body pt-0">
                                <select name="category_id" class="form-select mb-2">
                                    <option value="">Chọn danh mục</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- Product Details -->
                        <div class="card card-flush py-4 mt-5">
                            <div class="card-header">
                                <div class="card-title">
                                    <h2>Chi tiết sản phẩm</h2>
                                </div>
                            </div>
                            <div class="card-body pt-0">
                                <!-- SKU -->
                                <div class="mb-10">
                                    <label class="form-label">SKU</label>
                                    <input type="text" name="sku" class="form-control mb-2" 
                                           placeholder="Để trống để tự động tạo" value="{{ old('sku') }}" />
                                    <div class="text-muted fs-7">Để trống để hệ thống tự động tạo SKU</div>
                                    @error('sku')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <!-- Barcode -->
                                <div class="mb-10">
                                    <label class="form-label">Barcode</label>
                                    <input type="text" name="barcode" class="form-control mb-2" 
                                           placeholder="Nhập barcode" value="{{ old('barcode') }}" />
                                    @error('barcode')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <!-- Weight -->
                                <div class="mb-10">
                                    <label class="form-label">Trọng lượng (kg)</label>
                                    <input type="number" name="weight" class="form-control mb-2" 
                                           placeholder="0" value="{{ old('weight') }}" min="0" step="0.01" />
                                    @error('weight')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Product Image -->
                        <div class="card card-flush py-4 mt-5">
                            <div class="card-header">
                                <div class="card-title">
                                    <h2>Hình ảnh sản phẩm</h2>
                                </div>
                            </div>
                            <div class="card-body pt-0">
                                <input type="file" name="product_image" class="form-control" accept="image/*" />
                                <div class="text-muted fs-7 mt-2">Chấp nhận: JPG, PNG, GIF. Tối đa 2MB.</div>
                                @error('product_image')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="d-flex justify-content-end mt-10">
                    <a href="{{ url('/admin/products') }}" class="btn btn-light me-5">Hủy</a>
                    <button type="submit" class="btn btn-primary">
                        <span class="indicator-label">Lưu sản phẩm</span>
                        <span class="indicator-progress">Đang xử lý...
                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Form submission
    $('#product-form').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const formData = new FormData(this);
        
        // Show loading state
        submitBtn.attr('data-kt-indicator', 'on');
        submitBtn.prop('disabled', true);
        
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    if (response.redirect) {
                        window.location.href = response.redirect;
                    }
                } else {
                    toastr.error(response.message || 'Có lỗi xảy ra!');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                if (response && response.errors) {
                    // Display validation errors
                    Object.keys(response.errors).forEach(function(key) {
                        const input = form.find(`[name="${key}"]`);
                        const errorDiv = input.siblings('.text-danger');
                        if (errorDiv.length) {
                            errorDiv.text(response.errors[key][0]);
                        } else {
                            input.after(`<div class="text-danger">${response.errors[key][0]}</div>`);
                        }
                    });
                } else {
                    toastr.error(response?.message || 'Có lỗi xảy ra!');
                }
            },
            complete: function() {
                // Hide loading state
                submitBtn.removeAttr('data-kt-indicator');
                submitBtn.prop('disabled', false);
            }
        });
    });
});
</script>
@endpush
