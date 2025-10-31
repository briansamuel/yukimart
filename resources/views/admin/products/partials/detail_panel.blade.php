<!--begin::Product Detail Panel-->
<div class="row product-details-expansion">
    <div class="col-md-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-6">
                <!--begin::Header-->
                <div class="d-flex align-items-center mb-6">
                    <div class="symbol symbol-60px me-4">
                        <img src="{{ $product->product_image ?? '/admin-assets/assets/images/upload-thumbnail.png' }}"
                             alt="{{ $product->product_name }}" class="symbol-label" />
                    </div>
                    <div class="flex-grow-1">
                        <h4 class="text-gray-900 fw-bold mb-1">{{ $product->product_name }}</h4>
                        <div class="text-muted fs-6">SKU: {{ $product->sku ?? 'N/A' }}</div>
                    </div>
                    <div class="text-end">
                        <span class="badge badge-light-primary fs-7 fw-bold">{{ $product->product_type ?? 'Simple' }}</span>
                    </div>
                </div>
                <!--end::Header-->

                <!--begin::Tabs Navigation-->
                <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x mb-5 fs-6" id="product-tabs-{{ $product->id }}">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#product-info-{{ $product->id }}">
                            <i class="fas fa-info-circle me-2"></i>Thông tin sản phẩm
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#product-inventory-{{ $product->id }}">
                            <i class="fas fa-boxes me-2"></i>Tồn kho
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#product-sales-{{ $product->id }}">
                            <i class="fas fa-shopping-cart me-2"></i>Liên kết bán hàng
                        </a>
                    </li>
                </ul>
                <!--end::Tabs Navigation-->

                <!--begin::Tabs Content-->
                <div class="tab-content" id="product-tabs-content-{{ $product->id }}">

                    <!--begin::Tab 1: Product Information-->
                    <div class="tab-pane fade show active" id="product-info-{{ $product->id }}">
                        <div class="row g-6">
                            <!--begin::Basic Info-->
                            <div class="col-lg-6">
                                <div class="card bg-light-primary border-0">
                                    <div class="card-body p-4">
                                        <h6 class="text-primary fw-bold mb-3">
                                            <i class="fas fa-info-circle me-2"></i>Thông tin cơ bản
                                        </h6>
                                        <div class="row g-3">
                                            <div class="col-6">
                                                <div class="text-muted fs-7">Tên sản phẩm</div>
                                                <div class="fw-bold fs-6">{{ $product->product_name }}</div>
                                            </div>
                                            <div class="col-6">
                                                <div class="text-muted fs-7">SKU</div>
                                                <div class="fw-bold fs-6">{{ $product->sku ?? 'N/A' }}</div>
                                            </div>
                                            <div class="col-6">
                                                <div class="text-muted fs-7">Trạng thái</div>
                                                <div>
                                                    @if($product->product_status === 'published')
                                                        <span class="badge badge-light-success">Published</span>
                                                    @else
                                                        <span class="badge badge-light-secondary">Draft</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="text-muted fs-7">Ngày tạo</div>
                                                <div class="fw-bold fs-6">{{ $product->created_at->format('d/m/Y') }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--end::Basic Info-->

                            <!--begin::Pricing Info-->
                            <div class="col-lg-6">
                                <div class="card bg-light-success border-0">
                                    <div class="card-body p-4">
                                        <h6 class="text-success fw-bold mb-3">
                                            <i class="fas fa-dollar-sign me-2"></i>Thông tin giá cả
                                        </h6>
                                        <div class="row g-3">
                                            <div class="col-6">
                                                <div class="text-muted fs-7">Giá bán</div>
                                                <div class="fw-bold fs-5 text-success">{{ number_format($product->sale_price ?? 0) }} VND</div>
                                            </div>
                                            <div class="col-6">
                                                <div class="text-muted fs-7">Giá vốn</div>
                                                <div class="fw-bold fs-6">{{ number_format($product->cost_price ?? 0) }} VND</div>
                                            </div>
                                            <div class="col-6">
                                                <div class="text-muted fs-7">Giá thường</div>
                                                <div class="fw-bold fs-6">{{ number_format($product->regular_price ?? 0) }} VND</div>
                                            </div>
                                            <div class="col-6">
                                                <div class="text-muted fs-7">Tỷ suất lợi nhuận</div>
                                                <div class="fw-bold fs-6 text-success">
                                                    @if($product->sale_price && $product->cost_price)
                                                        {{ round((($product->sale_price - $product->cost_price) / $product->sale_price) * 100) }}%
                                                    @else
                                                        N/A
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--end::Pricing Info-->

                            <!--begin::Additional Info-->
                            <div class="col-lg-12">
                                <div class="card bg-light-info border-0">
                                    <div class="card-body p-4">
                                        <h6 class="text-info fw-bold mb-3">
                                            <i class="fas fa-cog me-2"></i>Thông số kỹ thuật
                                        </h6>
                                        <div class="row g-3">
                                            <div class="col-3">
                                                <div class="text-muted fs-7">Trọng lượng</div>
                                                <div class="fw-bold fs-6">{{ $product->weight ?? 'N/A' }}</div>
                                            </div>
                                            <div class="col-3">
                                                <div class="text-muted fs-7">Kích thước</div>
                                                <div class="fw-bold fs-6">{{ $product->dimensions ?? 'N/A' }}</div>
                                            </div>
                                            <div class="col-3">
                                                <div class="text-muted fs-7">Mã vạch</div>
                                                <div class="fw-bold fs-6">{{ $product->barcode ?? 'N/A' }}</div>
                                            </div>
                                            <div class="col-3">
                                                <div class="text-muted fs-7">Cập nhật lần cuối</div>
                                                <div class="fw-bold fs-6">{{ $product->updated_at->format('d/m/Y H:i') }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--end::Additional Info-->
                        </div>
                    </div>
                    <!--end::Tab 1-->


                    <!--begin::Tab 2: Inventory Information-->
                    <div class="tab-pane fade" id="product-inventory-{{ $product->id }}">
                        <div class="row g-6">
                            <!--begin::Current Stock-->
                            <div class="col-lg-6">
                                <div class="card bg-light-warning border-0">
                                    <div class="card-body p-4">
                                        <h6 class="text-warning fw-bold mb-3">
                                            <i class="fas fa-boxes me-2"></i>Tồn kho hiện tại
                                        </h6>
                                        <div class="row g-3">
                                            <div class="col-6">
                                                <div class="text-muted fs-7">Số lượng</div>
                                                <div class="fw-bold fs-3 text-warning">{{ $product->inventory->quantity ?? 0 }}</div>
                                                <div class="text-muted fs-8">đơn vị</div>
                                            </div>
                                            <div class="col-6">
                                                <div class="text-muted fs-7">Trạng thái tồn kho</div>
                                                <div>
                                                    @php
                                                        $quantity = $product->inventory->quantity ?? 0;
                                                        if ($quantity >= 10) {
                                                            $status = 'in_stock';
                                                            $label = 'Còn hàng';
                                                            $icon = 'check-circle';
                                                        } elseif ($quantity > 0) {
                                                            $status = 'low_stock';
                                                            $label = 'Sắp hết';
                                                            $icon = 'exclamation-triangle';
                                                        } else {
                                                            $status = 'out_of_stock';
                                                            $label = 'Hết hàng';
                                                            $icon = 'times-circle';
                                                        }
                                                    @endphp
                                                    <span class="stock-status-badge stock-status-{{ $status }}">
                                                        <i class="fas fa-{{ $icon }}"></i>
                                                        {{ $label }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="text-muted fs-7">Điểm đặt hàng lại</div>
                                                <div class="fw-bold fs-6">{{ $product->reorder_point ?? 0 }} đơn vị</div>
                                            </div>
                                            <div class="col-6">
                                                <div class="text-muted fs-7">Giá trị tồn kho</div>
                                                <div class="fw-bold fs-6 text-warning">
                                                    {{ number_format(($product->inventory->quantity ?? 0) * ($product->cost_price ?? 0)) }} VND
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--end::Current Stock-->

                            <!--begin::Stock Actions-->
                            <div class="col-lg-6">
                                <div class="card bg-light-info border-0">
                                    <div class="card-body p-4">
                                        <h6 class="text-info fw-bold mb-3">
                                            <i class="fas fa-cogs me-2"></i>Thao tác tồn kho
                                        </h6>
                                        <div class="d-grid gap-3">
                                            <button type="button" class="btn btn-light-primary btn-sm" onclick="adjustStock({{ $product->id }}, 'add')">
                                                <i class="fas fa-plus me-2"></i>Thêm tồn kho
                                            </button>
                                            <button type="button" class="btn btn-light-warning btn-sm" onclick="adjustStock({{ $product->id }}, 'remove')">
                                                <i class="fas fa-minus me-2"></i>Giảm tồn kho
                                            </button>
                                            <button type="button" class="btn btn-light-info btn-sm" onclick="viewStockHistory({{ $product->id }})">
                                                <i class="fas fa-history me-2"></i>Lịch sử tồn kho
                                            </button>
                                            <button type="button" class="btn btn-light-success btn-sm" onclick="stockTake({{ $product->id }})">
                                                <i class="fas fa-clipboard-check me-2"></i>Kiểm kê tồn kho
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--end::Stock Actions-->
                        </div>
                    </div>
                    <!--end::Tab 2-->

                    <!--begin::Tab 3: Sales Links-->
                    <div class="tab-pane fade" id="product-sales-{{ $product->id }}">
                        <div class="row g-6">
                            <!--begin::E-commerce Platforms-->
                            <div class="col-lg-8">
                                <div class="card bg-light-success border-0">
                                    <div class="card-body p-4">
                                        <h6 class="text-success fw-bold mb-3">
                                            <i class="fas fa-shopping-cart me-2"></i>Sàn thương mại điện tử
                                        </h6>
                                        <div class="row g-3">
                                            <!--begin::Shopee-->
                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center p-3 bg-white rounded border">
                                                    <div class="symbol symbol-40px me-3">
                                                        <div class="symbol-label" style="background-color: #EE4D2D;">
                                                            <i class="fas fa-shopping-bag text-white"></i>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <div class="fw-bold fs-6">Shopee</div>
                                                        <div class="text-muted fs-7">Sàn giao dịch</div>
                                                    </div>
                                                    <div>
                                                        <button type="button" class="btn btn-sm btn-light-primary" onclick="linkToShopee({{ $product->id }})">
                                                            <i class="fas fa-link me-1"></i>Liên kết
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <!--end::Shopee-->

                                            <!--begin::Lazada-->
                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center p-3 bg-white rounded border">
                                                    <div class="symbol symbol-40px me-3">
                                                        <div class="symbol-label" style="background-color: #FF6600;">
                                                            <i class="fas fa-shopping-cart text-white"></i>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <div class="fw-bold fs-6">Lazada</div>
                                                        <div class="text-muted fs-7">Sàn giao dịch</div>
                                                    </div>
                                                    <div>
                                                        <button type="button" class="btn btn-sm btn-light-warning" onclick="linkToLazada({{ $product->id }})">
                                                            <i class="fas fa-link me-1"></i>Liên kết
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <!--end::Lazada-->

                                            <!--begin::Tiki-->
                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center p-3 bg-white rounded border">
                                                    <div class="symbol symbol-40px me-3">
                                                        <div class="symbol-label" style="background-color: #0073E6;">
                                                            <i class="fas fa-store text-white"></i>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <div class="fw-bold fs-6">Tiki</div>
                                                        <div class="text-muted fs-7">Sàn giao dịch</div>
                                                    </div>
                                                    <div>
                                                        <button type="button" class="btn btn-sm btn-light-info" onclick="linkToTiki({{ $product->id }})">
                                                            <i class="fas fa-link me-1"></i>Liên kết
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <!--end::Tiki-->

                                            <!--begin::Sendo-->
                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center p-3 bg-white rounded border">
                                                    <div class="symbol symbol-40px me-3">
                                                        <div class="symbol-label" style="background-color: #ED3757;">
                                                            <i class="fas fa-shopping-basket text-white"></i>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <div class="fw-bold fs-6">Sendo</div>
                                                        <div class="text-muted fs-7">Sàn giao dịch</div>
                                                    </div>
                                                    <div>
                                                        <button type="button" class="btn btn-sm btn-light-danger" onclick="linkToSendo({{ $product->id }})">
                                                            <i class="fas fa-link me-1"></i>Liên kết
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <!--end::Sendo-->
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--end::E-commerce Platforms-->

                            <!--begin::Sales Statistics-->
                            <div class="col-lg-4">
                                <div class="card bg-light-primary border-0">
                                    <div class="card-body p-4">
                                        <h6 class="text-primary fw-bold mb-3">
                                            <i class="fas fa-chart-line me-2"></i>Thống kê bán hàng
                                        </h6>
                                        <div class="d-grid gap-3">
                                            <div class="text-center">
                                                <div class="text-muted fs-7">Tổng bán hàng</div>
                                                <div class="fw-bold fs-4 text-primary">{{ $product->total_sales ?? 0 }}</div>
                                                <div class="text-muted fs-8">đơn vị đã bán</div>
                                            </div>
                                            <div class="text-center">
                                                <div class="text-muted fs-7">Doanh thu</div>
                                                <div class="fw-bold fs-5 text-success">{{ number_format(($product->total_sales ?? 0) * ($product->sale_price ?? 0)) }} VND</div>
                                            </div>
                                            <div class="text-center">
                                                <div class="text-muted fs-7">Lần bán cuối</div>
                                                <div class="fw-bold fs-6">{{ $product->last_sale_date ? \Carbon\Carbon::parse($product->last_sale_date)->format('d/m/Y') : 'N/A' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--end::Sales Statistics-->
                        </div>
                    </div>
                    <!--end::Tab 3-->

                </div>
                <!--end::Tabs Content-->

                <!--begin::Action Buttons-->
                <div class="d-flex justify-content-between mt-6">
                    <!--begin::Left buttons-->
                    <div>
                        <button type="button" class="btn btn-danger btn-sm me-2" onclick="deleteProduct({{ $product->id }})">
                            <i class="fas fa-trash me-2"></i>Xóa
                        </button>
                        <button type="button" class="btn btn-light btn-sm" onclick="duplicateProduct({{ $product->id }})">
                            <i class="fas fa-copy me-2"></i>Sao chép
                        </button>
                    </div>
                    <!--end::Left buttons-->

                    <!--begin::Right buttons-->
                    <div>
                        <button type="button" class="btn btn-primary btn-sm me-2 edit-product-btn" data-product-id="{{ $product->id }}">
                            <i class="fas fa-edit me-2"></i>Chỉnh sửa sản phẩm
                        </button>
                        <button type="button" class="btn btn-light btn-sm me-2" onclick="printBarcode({{ $product->id }})">
                            <i class="fas fa-print me-2"></i>In tem mã
                        </button>
                        <div class="btn-group">
                            <button type="button" class="btn btn-light btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-ellipsis-h"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#" onclick="importStock({{ $product->id }}); return false;">
                                    <i class="fas fa-box me-2"></i>Nhập hàng
                                </a></li>
                                <li><a class="dropdown-item" href="#" onclick="stopSelling({{ $product->id }}); return false;">
                                    <i class="fas fa-ban me-2"></i>Ngừng kinh doanh
                                </a></li>
                            </ul>
                        </div>
                    </div>
                    <!--end::Right buttons-->
                </div>
                <!--end::Action Buttons-->
            </div>
        </div>
    </div>
</div>
<!--end::Product Detail Panel-->
