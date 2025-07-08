@extends('admin.main-content')

@section('title', 'Nhập Hàng')
@section('page-header', 'Quản Lý Tồn Kho')
@section('page-sub_header', 'Nhập hàng')
@section('content')
    <!--begin::Import Form-->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <h3 class="fw-bold m-0">Thông Tin Nhập Hàng</h3>
            </div>
        </div>
        <div class="card-body">
            <form id="import-form" method="POST" action="{{ route('admin.inventory.process-import') }}">
                @csrf

                <!--begin::Import Info-->
                <div class="row mb-8">
                    <div class="col-md-6">
                        <label class="required fs-6 fw-semibold mb-2">Kho Nhập</label>
                        <select class="form-select form-select-solid" name="warehouse_id" required>
                            <option value="">Chọn kho nhập</option>
                            @foreach ($warehouses ?? [] as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }} ({{ $warehouse->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="fs-6 fw-semibold mb-2">Số Phiếu Nhập</label>
                        <input type="text" class="form-control form-control-solid" name="reference_number"
                            placeholder="Nhập số phiếu nhập (tự động nếu để trống)" value="PN{{ date('YmdHis') }}">
                    </div>
                </div>

                <div class="row mb-8">
                    <div class="col-md-6">
                        <label class="fs-6 fw-semibold mb-2">Nhà Cung Cấp</label>
                        <select class="form-select form-select-solid" name="supplier_id" id="supplier-select">
                            <option value="">Chọn nhà cung cấp</option>
                            <!-- Suppliers will be loaded via AJAX -->
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="fs-6 fw-semibold mb-2">Ngày Nhập</label>
                        <input type="date" class="form-control form-control-solid" name="import_date"
                            value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>

                <div class="row mb-8">
                    <div class="col-12">
                        <label class="fs-6 fw-semibold mb-2">Ghi Chú</label>
                        <textarea class="form-control form-control-solid" name="notes" rows="3"
                            placeholder="Ghi chú về lô hàng nhập..."></textarea>
                    </div>
                </div>
                <!--end::Import Info-->

                <!--begin::Products Section-->
                <div class="separator separator-dashed my-8"></div>

                <div class="d-flex justify-content-between align-items-center mb-5">
                    <h4 class="fw-bold">Danh Sách Sản Phẩm Nhập</h4>
                    <button type="button" class="btn btn-primary btn-sm" id="add-product-btn">
                        <i class="fas fa-plus"></i> Thêm Sản Phẩm
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-row-bordered table-row-gray-100 align-middle gs-0 gy-3" id="products-table">
                        <thead>
                            <tr class="fw-bold text-muted">
                                <th class="min-w-200px">Sản Phẩm</th>
                                <th class="min-w-100px text-center">Số Lượng</th>
                                <th class="min-w-120px text-center">Giá Nhập</th>
                                <th class="min-w-120px text-center">Thành Tiền</th>
                                <th class="min-w-50px text-center">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody id="products-tbody">
                            <!-- Products will be added here dynamically -->
                        </tbody>
                        <tfoot>
                            <tr class="fw-bold">
                                <td colspan="3" class="text-end">Tổng Cộng:</td>
                                <td class="text-center">
                                    <span id="total-amount" class="fs-4 text-primary">0 VND</span>
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <!--end::Products Section-->

                <!--begin::Actions-->
                <div class="d-flex justify-content-end mt-8">
                    <button type="button" class="btn btn-light me-3" onclick="window.history.back()">Hủy</button>
                    <button type="submit" class="btn btn-primary" id="submit-btn">
                        <span class="indicator-label">
                            <i class="fas fa-save"></i> Lưu Phiếu Nhập
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
    <!--end::Import Form-->
    <!--begin::Product Selection Modal-->
    <div class="modal fade" id="product-selection-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Chọn Sản Phẩm</h3>
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal">
                        <i class="fas fa-times fs-2"></i>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="mb-5">
                        <input type="text" class="form-control" id="product-search"
                            placeholder="Tìm kiếm sản phẩm theo tên hoặc SKU...">
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
    <script src="{{ asset('admin-assets/assets/js/custom/inventory/import.js') }}"></script>
    <script>
        let productRowIndex = 0;
        let selectedProducts = [];

        $(document).ready(function() {
            // Load products for selection
            loadProducts();

            // Load suppliers for selection
            loadSuppliers();

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
            $('#import-form').on('submit', function(e) {
                e.preventDefault();

                if (selectedProducts.length === 0) {
                    Swal.fire('Lỗi', 'Vui lòng thêm ít nhất một sản phẩm', 'error');
                    return;
                }

                const submitBtn = $('#submit-btn');
                submitBtn.attr('data-kt-indicator', 'on');

                // Collect actual product data from form inputs
                const productsData = [];
                $('#products-tbody tr').each(function() {
                    const row = $(this);
                    const productId = row.data('product-id');
                    const quantity = parseFloat(row.find('.quantity-input').val()) || 0;
                    const unitCost = parseFloat(row.find('.price-input').val()) || 0;

                    if (productId && quantity > 0) {
                        productsData.push({
                            id: productId,
                            quantity: quantity,
                            unit_cost: unitCost
                        });
                    }
                });

                // Validate that we have valid product data
                if (productsData.length === 0) {
                    Swal.fire('Lỗi', 'Vui lòng nhập số lượng hợp lệ cho các sản phẩm', 'error');
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
                                window.location.href =
                                    '{{ route('admin.inventory.dashboard') }}';
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

        function loadSuppliers() {
            $.ajax({
                url: '{{ route('admin.supplier.active') }}',
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        let html = '<option value="">Chọn nhà cung cấp</option>';
                        response.data.forEach(function(supplier) {
                            // Use display_name from server or fallback to name
                            let displayName = supplier.display_name || supplier.name;
                            html +=
                                `<option value="${supplier.id}" data-code="${supplier.code || ''}" data-company="${supplier.company || ''}">${displayName}</option>`;
                        });
                        $('#supplier-select').html(html);
                        console.log('Loaded ' + response.data.length + ' suppliers successfully');
                    } else {
                        console.log('Failed to load suppliers: ' + response.message);
                        Swal.fire('Lỗi', 'Không thể tải danh sách nhà cung cấp', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.log('AJAX Error loading suppliers:', error);
                    Swal.fire('Lỗi', 'Không thể kết nối để tải danh sách nhà cung cấp', 'error');
                }
            });
        }

        function loadProducts(search = '') {
            $.ajax({
                url: '{{ route('admin.products.ajax.getList') }}',
                method: 'GET',
                data: {
                    search: {
                        value: search
                    },
                    length: 100
                },
                success: function(response) {
                    let html = '';
                    response.data.forEach(function(product) {
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
                        <td class="text-center">${product.stock_quantity || 0}</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-primary" onclick="selectProduct(${product.id}, '${product.product_name}', '${product.sku}', ${product.cost_price || 0})">
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

        function selectProduct(id, name, sku, costPrice) {
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
                <input type="number" class="form-control form-control-sm text-center quantity-input" 
                       name="products[${productRowIndex}][quantity]" value="1" min="1" required>
            </td>
            <td class="text-center">
                <input type="number" class="form-control form-control-sm text-center price-input" 
                       name="products[${productRowIndex}][unit_cost]" value="${costPrice}" min="0" step="0.01" required>
            </td>
            <td class="text-center">
                <span class="fw-bold amount-display">${formatCurrency(costPrice)}</span>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-light-danger" onclick="removeProduct(this, ${id})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `;

            $('#products-tbody').append(rowHtml);
            selectedProducts.push({
                id: id,
                name: name,
                sku: sku
            });
            productRowIndex++;

            $('#product-selection-modal').modal('hide');
            updateTotal();

            // Add event listeners for quantity and price changes
            $(`tr[data-product-id="${id}"] .quantity-input, tr[data-product-id="${id}"] .price-input`).on('input',
                updateTotal);
        }

        function removeProduct(btn, productId) {
            $(btn).closest('tr').remove();
            selectedProducts = selectedProducts.filter(p => p.id != productId);
            updateTotal();
        }

        function updateTotal() {
            let total = 0;
            $('#products-tbody tr').each(function() {
                const quantity = parseFloat($(this).find('.quantity-input').val()) || 0;
                const price = parseFloat($(this).find('.price-input').val()) || 0;
                const amount = quantity * price;

                $(this).find('.amount-display').text(formatCurrency(amount));
                total += amount;
            });

            $('#total-amount').text(formatCurrency(total));
        }

        function formatCurrency(amount) {
            return new Intl.NumberFormat('vi-VN', {
                style: 'currency',
                currency: 'VND'
            }).format(amount);
        }
    </script>
@endsection
