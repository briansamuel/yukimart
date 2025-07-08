@extends('admin.main-content')

@section('title', 'Điều Chỉnh Tồn Kho')

@section('content')
<div class="d-flex flex-column flex-column-fluid">
    <!--begin::Toolbar-->
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                    Điều Chỉnh Tồn Kho
                </h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-400 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('admin.inventory.dashboard') }}" class="text-muted text-hover-primary">Tồn Kho</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-400 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-muted">Điều Chỉnh</li>
                </ul>
            </div>
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <a href="{{ route('admin.inventory.dashboard') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left"></i> Quay Lại
                </a>
            </div>
        </div>
    </div>
    <!--end::Toolbar-->

    <!--begin::Content-->
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-xxl">
            
            <!--begin::Adjustment Form-->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <h3 class="fw-bold m-0">Thông Tin Điều Chỉnh</h3>
                    </div>
                </div>
                <div class="card-body">
                    <form id="adjustment-form" method="POST" action="{{ route('inventory.process-adjustment') }}">
                        @csrf
                        
                        <!--begin::Adjustment Info-->
                        <div class="row mb-8">
                            <div class="col-md-6">
                                <label class="required fs-6 fw-semibold mb-2">Kho Điều Chỉnh</label>
                                <select class="form-select form-select-solid" name="warehouse_id" required>
                                    <option value="">Chọn kho điều chỉnh</option>
                                    @foreach($warehouses ?? [] as $warehouse)
                                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }} ({{ $warehouse->code }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="fs-6 fw-semibold mb-2">Số Phiếu Điều Chỉnh</label>
                                <input type="text" class="form-control form-control-solid" name="reference_number" 
                                       placeholder="Nhập số phiếu (tự động nếu để trống)" value="DC{{ date('YmdHis') }}">
                            </div>
                        </div>

                        <div class="row mb-8">
                            <div class="col-md-6">
                                <label class="required fs-6 fw-semibold mb-2">Loại Điều Chỉnh</label>
                                <select class="form-select form-select-solid" name="adjustment_type" required>
                                    <option value="">Chọn loại điều chỉnh</option>
                                    <option value="stocktake">Kiểm Kê</option>
                                    <option value="damage">Hàng Hỏng</option>
                                    <option value="expired">Hàng Hết Hạn</option>
                                    <option value="lost">Mất Hàng</option>
                                    <option value="found">Tìm Thấy Hàng</option>
                                    <option value="correction">Sửa Lỗi</option>
                                    <option value="other">Khác</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="fs-6 fw-semibold mb-2">Ngày Điều Chỉnh</label>
                                <input type="date" class="form-control form-control-solid" name="adjustment_date" 
                                       value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>

                        <div class="row mb-8">
                            <div class="col-12">
                                <label class="required fs-6 fw-semibold mb-2">Lý Do Điều Chỉnh</label>
                                <textarea class="form-control form-control-solid" name="reason" rows="3" 
                                          placeholder="Mô tả chi tiết lý do điều chỉnh tồn kho..." required></textarea>
                            </div>
                        </div>
                        <!--end::Adjustment Info-->

                        <!--begin::Products Section-->
                        <div class="separator separator-dashed my-8"></div>
                        
                        <div class="d-flex justify-content-between align-items-center mb-5">
                            <h4 class="fw-bold">Danh Sách Sản Phẩm Điều Chỉnh</h4>
                            <button type="button" class="btn btn-primary btn-sm" id="add-product-btn">
                                <i class="fas fa-plus"></i> Thêm Sản Phẩm
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-row-bordered table-row-gray-100 align-middle gs-0 gy-3" id="products-table">
                                <thead>
                                    <tr class="fw-bold text-muted">
                                        <th class="min-w-200px">Sản Phẩm</th>
                                        <th class="min-w-100px text-center">Tồn Kho Hiện Tại</th>
                                        <th class="min-w-100px text-center">Tồn Kho Thực Tế</th>
                                        <th class="min-w-100px text-center">Chênh Lệch</th>
                                        <th class="min-w-150px text-center">Lý Do</th>
                                        <th class="min-w-50px text-center">Thao Tác</th>
                                    </tr>
                                </thead>
                                <tbody id="products-tbody">
                                    <!-- Products will be added here dynamically -->
                                </tbody>
                            </table>
                        </div>
                        <!--end::Products Section-->

                        <!--begin::Summary-->
                        <div class="row mt-8">
                            <div class="col-md-6 offset-md-6">
                                <div class="card card-flush bg-light">
                                    <div class="card-body">
                                        <h5 class="fw-bold mb-4">Tổng Kết Điều Chỉnh</h5>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Tổng sản phẩm:</span>
                                            <span id="total-products" class="fw-bold">0</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Tăng:</span>
                                            <span id="total-increase" class="fw-bold text-success">0</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Giảm:</span>
                                            <span id="total-decrease" class="fw-bold text-danger">0</span>
                                        </div>
                                        <div class="separator my-3"></div>
                                        <div class="d-flex justify-content-between">
                                            <span class="fw-bold">Chênh lệch ròng:</span>
                                            <span id="net-difference" class="fw-bold fs-4">0</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end::Summary-->

                        <!--begin::Actions-->
                        <div class="d-flex justify-content-end mt-8">
                            <button type="button" class="btn btn-light me-3" onclick="window.history.back()">Hủy</button>
                            <button type="submit" class="btn btn-primary" id="submit-btn">
                                <span class="indicator-label">
                                    <i class="fas fa-save"></i> Lưu Điều Chỉnh
                                </span>
                                <span class="indicator-progress">
                                    Đang xử lý... <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                </span>
                            </button>
                        </div>
                        <!--end::Actions-->
                    </form>
                </div>
            </div>
            <!--end::Adjustment Form-->

        </div>
    </div>
    <!--end::Content-->
</div>

<!--begin::Product Selection Modal-->
<div class="modal fade" id="product-selection-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Chọn Sản Phẩm Điều Chỉnh</h3>
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal">
                    <i class="fas fa-times fs-2"></i>
                </div>
            </div>
            <div class="modal-body">
                <div class="mb-5">
                    <input type="text" class="form-control" id="product-search" placeholder="Tìm kiếm sản phẩm theo tên hoặc SKU...">
                </div>
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-row-bordered align-middle">
                        <thead>
                            <tr class="fw-bold text-muted">
                                <th>Sản Phẩm</th>
                                <th class="text-center">SKU</th>
                                <th class="text-center">Tồn Kho</th>
                                <th class="text-center">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody id="product-list">
                            <!-- Products will be loaded here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!--end::Product Selection Modal-->
@endsection

@section('scripts')
<script src="{{ asset('admin-assets/assets/js/custom/inventory/adjustment.js') }}"></script>
<script>
let productRowIndex = 0;
let selectedProducts = [];

$(document).ready(function() {
    // Load products for selection
    loadProducts();
    
    // Product search
    $('#product-search').on('keyup', function() {
        const searchTerm = $(this).val();
        loadProducts(searchTerm);
    });
    
    // Add product button
    $('#add-product-btn').on('click', function() {
        $('#product-selection-modal').modal('show');
    });
    
    // Form submission
    $('#adjustment-form').on('submit', function(e) {
        e.preventDefault();
        
        if (selectedProducts.length === 0) {
            Swal.fire('Lỗi', 'Vui lòng thêm ít nhất một sản phẩm', 'error');
            return;
        }
        
        // Check if all products have actual quantities
        let hasEmptyActual = false;
        $('#products-tbody tr').each(function() {
            const actualQty = $(this).find('.actual-quantity-input').val();
            if (actualQty === '' || actualQty === null) {
                hasEmptyActual = true;
                $(this).find('.actual-quantity-input').addClass('is-invalid');
            } else {
                $(this).find('.actual-quantity-input').removeClass('is-invalid');
            }
        });
        
        if (hasEmptyActual) {
            Swal.fire('Lỗi', 'Vui lòng nhập tồn kho thực tế cho tất cả sản phẩm', 'error');
            return;
        }
        
        const submitBtn = $('#submit-btn');
        submitBtn.attr('data-kt-indicator', 'on');

        // Collect actual product data from form inputs
        const productsData = [];
        $('#products-tbody tr').each(function() {
            const row = $(this);
            const productId = row.data('product-id');
            const currentStock = parseInt(row.find('.current-stock').text()) || 0;
            const actualStock = parseInt(row.find('.actual-quantity-input').val()) || 0;
            const reason = row.find('input[name*="[reason]"]').val() || '';

            if (productId) {
                productsData.push({
                    id: productId,
                    current_stock: currentStock,
                    actual_stock: actualStock,
                    reason: reason
                });
            }
        });

        // Validate that we have valid product data
        if (productsData.length === 0) {
            Swal.fire('Lỗi', 'Vui lòng thêm ít nhất một sản phẩm để điều chỉnh', 'error');
            submitBtn.removeAttr('data-kt-indicator');
            return;
        }

        // Prepare form data
        const formData = new FormData(this);
        formData.append('products', JSON.stringify(productsData));
        
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        title: 'Thành Công!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.href = '{{ route("inventory.dashboard") }}';
                    });
                } else {
                    Swal.fire('Lỗi', response.message, 'error');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                Swal.fire('Lỗi', response?.message || 'Có lỗi xảy ra', 'error');
            },
            complete: function() {
                submitBtn.removeAttr('data-kt-indicator');
            }
        });
    });
});

function loadProducts(search = '') {
    const warehouseId = $('select[name="warehouse_id"]').val();
    
    $.ajax({
        url: '{{ route("products.ajax.getList") }}',
        method: 'GET',
        data: {
            search: { value: search },
            warehouse_id: warehouseId,
            length: 100
        },
        success: function(response) {
            let html = '';
            response.data.forEach(function(product) {
                const stockQty = product.stock_quantity || 0;
                
                html += `
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="ms-3">
                                    <div class="fw-bold">${product.product_name}</div>
                                    <div class="text-muted fs-7">${product.product_description || ''}</div>
                                </div>
                            </div>
                        </td>
                        <td class="text-center">${product.sku}</td>
                        <td class="text-center">
                            <span class="fw-bold">${stockQty}</span>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-primary" 
                                    onclick="selectProduct(${product.id}, '${product.product_name}', '${product.sku}', ${stockQty})">
                                Chọn
                            </button>
                        </td>
                    </tr>
                `;
            });
            $('#product-list').html(html);
        }
    });
}

function selectProduct(id, name, sku, currentStock) {
    // Check if product already selected
    if (selectedProducts.find(p => p.id == id)) {
        Swal.fire('Thông Báo', 'Sản phẩm đã được thêm vào danh sách', 'info');
        return;
    }
    
    const rowHtml = `
        <tr data-product-id="${id}">
            <td>
                <div class="fw-bold">${name}</div>
                <div class="text-muted fs-7">${sku}</div>
                <input type="hidden" name="products[${productRowIndex}][id]" value="${id}">
            </td>
            <td class="text-center">
                <span class="fw-bold current-stock">${currentStock}</span>
                <input type="hidden" name="products[${productRowIndex}][current_stock]" value="${currentStock}">
            </td>
            <td class="text-center">
                <input type="number" class="form-control form-control-sm text-center actual-quantity-input" 
                       name="products[${productRowIndex}][actual_stock]" value="${currentStock}" min="0" required>
            </td>
            <td class="text-center">
                <span class="fw-bold difference-display">0</span>
            </td>
            <td class="text-center">
                <input type="text" class="form-control form-control-sm" 
                       name="products[${productRowIndex}][reason]" placeholder="Lý do...">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-light-danger" onclick="removeProduct(this, ${id})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `;
    
    $('#products-tbody').append(rowHtml);
    selectedProducts.push({ id: id, name: name, sku: sku, current_stock: currentStock });
    productRowIndex++;
    
    $('#product-selection-modal').modal('hide');
    updateSummary();
    
    // Add event listener for actual quantity changes
    $(`tr[data-product-id="${id}"] .actual-quantity-input`).on('input', function() {
        updateDifference($(this).closest('tr'));
        updateSummary();
    });
}

function removeProduct(btn, productId) {
    $(btn).closest('tr').remove();
    selectedProducts = selectedProducts.filter(p => p.id != productId);
    updateSummary();
}

function updateDifference(row) {
    const currentStock = parseInt(row.find('.current-stock').text()) || 0;
    const actualStock = parseInt(row.find('.actual-quantity-input').val()) || 0;
    const difference = actualStock - currentStock;
    
    const differenceDisplay = row.find('.difference-display');
    differenceDisplay.text(difference);
    
    // Color coding
    if (difference > 0) {
        differenceDisplay.removeClass('text-danger').addClass('text-success');
    } else if (difference < 0) {
        differenceDisplay.removeClass('text-success').addClass('text-danger');
    } else {
        differenceDisplay.removeClass('text-success text-danger');
    }
}

function updateSummary() {
    let totalProducts = 0;
    let totalIncrease = 0;
    let totalDecrease = 0;
    
    $('#products-tbody tr').each(function() {
        totalProducts++;
        
        const currentStock = parseInt($(this).find('.current-stock').text()) || 0;
        const actualStock = parseInt($(this).find('.actual-quantity-input').val()) || 0;
        const difference = actualStock - currentStock;
        
        if (difference > 0) {
            totalIncrease += difference;
        } else if (difference < 0) {
            totalDecrease += Math.abs(difference);
        }
        
        updateDifference($(this));
    });
    
    const netDifference = totalIncrease - totalDecrease;
    
    $('#total-products').text(totalProducts);
    $('#total-increase').text(`+${totalIncrease}`);
    $('#total-decrease').text(`-${totalDecrease}`);
    
    const netDisplay = $('#net-difference');
    netDisplay.text(netDifference);
    
    if (netDifference > 0) {
        netDisplay.removeClass('text-danger').addClass('text-success');
    } else if (netDifference < 0) {
        netDisplay.removeClass('text-success').addClass('text-danger');
    } else {
        netDisplay.removeClass('text-success text-danger');
    }
}
</script>
@endsection
