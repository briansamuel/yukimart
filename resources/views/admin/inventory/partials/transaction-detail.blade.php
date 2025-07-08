<div class="row">
    <div class="col-md-6">
        <div class="card card-flush">
            <div class="card-header">
                <h3 class="card-title">Thông Tin Giao Dịch</h3>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-4 fw-bold">ID:</div>
                    <div class="col-8">#{{ $transaction->id }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-4 fw-bold">Loại:</div>
                    <div class="col-8">
                        @php
                            $badges = [
                                'import' => '<span class="badge badge-light-success">Nhập Hàng</span>',
                                'export' => '<span class="badge badge-light-danger">Xuất Hàng</span>',
                                'adjustment' => '<span class="badge badge-light-warning">Điều Chỉnh</span>',
                                'transfer' => '<span class="badge badge-light-info">Chuyển Kho</span>',
                                'return' => '<span class="badge badge-light-primary">Trả Hàng</span>',
                                'damage' => '<span class="badge badge-light-dark">Hàng Hỏng</span>',
                                'initial' => '<span class="badge badge-light-secondary">Tồn Đầu</span>',
                                'sale' => '<span class="badge badge-light-info">Bán Hàng</span>'
                            ];
                        @endphp
                        {!! $badges[$transaction->transaction_type] ?? $transaction->transaction_type !!}
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-4 fw-bold">Tham chiếu:</div>
                    <div class="col-8">{{ $transaction->reference_type }}{{ $transaction->reference_id ? '-' . $transaction->reference_id : '' }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-4 fw-bold">Thời gian:</div>
                    <div class="col-8">{{ $transaction->created_at->format('d/m/Y H:i:s') }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-4 fw-bold">Người thực hiện:</div>
                    <div class="col-8">{{ $transaction->creator->full_name ?? 'Hệ thống' }}</div>
                </div>
                @if($transaction->notes)
                <div class="row mb-3">
                    <div class="col-4 fw-bold">Ghi chú:</div>
                    <div class="col-8">{{ $transaction->notes }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card card-flush">
            <div class="card-header">
                <h3 class="card-title">Chi Tiết Sản Phẩm</h3>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-4 fw-bold">Sản phẩm:</div>
                    <div class="col-8">
                        <div class="fw-bold">{{ $transaction->product->product_name ?? 'N/A' }}</div>
                        <div class="text-muted fs-7">{{ $transaction->product->sku ?? 'N/A' }}</div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-4 fw-bold">Kho:</div>
                    <div class="col-8">{{ $transaction->warehouse->name ?? 'N/A' }}</div>
                </div>
                @if($transaction->supplier)
                <div class="row mb-3">
                    <div class="col-4 fw-bold">Nhà cung cấp:</div>
                    <div class="col-8">{{ $transaction->supplier->name }}</div>
                </div>
                @endif
                <div class="row mb-3">
                    <div class="col-4 fw-bold">Tồn kho trước:</div>
                    <div class="col-8">{{ number_format($transaction->old_quantity) }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-4 fw-bold">Thay đổi:</div>
                    <div class="col-8">
                        @php
                            $quantity = $transaction->quantity;
                            $color = $quantity > 0 ? 'text-success' : ($quantity < 0 ? 'text-danger' : 'text-muted');
                            $sign = $quantity > 0 ? '+' : '';
                        @endphp
                        <span class="fw-bold {{ $color }}">{{ $sign }}{{ number_format($quantity) }}</span>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-4 fw-bold">Tồn kho sau:</div>
                    <div class="col-8">{{ number_format($transaction->new_quantity) }}</div>
                </div>
                @if($transaction->unit_cost)
                <div class="row mb-3">
                    <div class="col-4 fw-bold">Đơn giá:</div>
                    <div class="col-8">{{ number_format($transaction->unit_cost, 0, ',', '.') }} ₫</div>
                </div>
                @endif
                @if($transaction->total_value)
                <div class="row mb-3">
                    <div class="col-4 fw-bold">Tổng giá trị:</div>
                    <div class="col-8 fw-bold text-primary">{{ number_format($transaction->total_value, 0, ',', '.') }} ₫</div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@if($transaction->location_from || $transaction->location_to)
<div class="row mt-5">
    <div class="col-12">
        <div class="card card-flush">
            <div class="card-header">
                <h3 class="card-title">Thông Tin Vị Trí</h3>
            </div>
            <div class="card-body">
                @if($transaction->location_from)
                <div class="row mb-3">
                    <div class="col-2 fw-bold">Từ vị trí:</div>
                    <div class="col-10">{{ $transaction->location_from }}</div>
                </div>
                @endif
                @if($transaction->location_to)
                <div class="row mb-3">
                    <div class="col-2 fw-bold">Đến vị trí:</div>
                    <div class="col-10">{{ $transaction->location_to }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endif

<div class="row mt-5">
    <div class="col-12">
        <div class="d-flex justify-content-end">
            <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Đóng</button>
            @if($transaction->created_at->diffInHours(now()) < 24)
            <button type="button" class="btn btn-primary" onclick="editTransaction({{ $transaction->id }})">
                <i class="fas fa-edit"></i> Chỉnh sửa
            </button>
            @endif
        </div>
    </div>
</div>
