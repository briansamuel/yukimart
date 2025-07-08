@extends('admin.index')

@section('title', 'Test Chi tiết đơn hàng')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Test Chi tiết đơn hàng - Kiểm tra lỗi</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h5>Thông tin debug:</h5>
                        <ul>
                            <li><strong>OrderService:</strong> {{ class_exists('App\Services\OrderService') ? 'Tồn tại' : 'Không tồn tại' }}</li>
                            <li><strong>Order Model:</strong> {{ class_exists('App\Models\Order') ? 'Tồn tại' : 'Không tồn tại' }}</li>
                            <li><strong>User Model:</strong> {{ class_exists('App\Models\User') ? 'Tồn tại' : 'Không tồn tại' }}</li>
                            <li><strong>OrderItem Model:</strong> {{ class_exists('App\Models\OrderItem') ? 'Tồn tại' : 'Không tồn tại' }}</li>
                        </ul>
                    </div>

                    @php
                        try {
                            // Kiểm tra có đơn hàng nào không
                            $orderCount = \App\Models\Order::count();
                            echo "<div class='alert alert-success'>Tổng số đơn hàng: {$orderCount}</div>";
                            
                            if ($orderCount > 0) {
                                // Lấy đơn hàng đầu tiên
                                $firstOrder = \App\Models\Order::first();
                                echo "<div class='alert alert-info'>Đơn hàng đầu tiên: ID = {$firstOrder->id}, Code = {$firstOrder->order_code}</div>";
                                
                                // Test OrderService
                                $orderService = new \App\Services\OrderService();
                                $testOrder = $orderService->getOrderById($firstOrder->id);
                                echo "<div class='alert alert-success'>OrderService hoạt động tốt! Đã load được đơn hàng: {$testOrder->order_code}</div>";
                                
                                // Hiển thị thông tin seller
                                if ($testOrder->seller) {
                                    echo "<div class='alert alert-success'>Người bán: {$testOrder->seller->name}</div>";
                                } else {
                                    echo "<div class='alert alert-warning'>Đơn hàng chưa có thông tin người bán (sold_by = {$testOrder->sold_by})</div>";
                                }
                                
                                // Hiển thị thông tin orderItems
                                $itemCount = $testOrder->orderItems->count();
                                echo "<div class='alert alert-info'>Số lượng sản phẩm: {$itemCount}</div>";
                                
                            } else {
                                echo "<div class='alert alert-warning'>Không có đơn hàng nào trong database</div>";
                            }
                            
                        } catch (\Exception $e) {
                            echo "<div class='alert alert-danger'>Lỗi: " . $e->getMessage() . "</div>";
                            echo "<div class='alert alert-danger'>File: " . $e->getFile() . " Line: " . $e->getLine() . "</div>";
                        }
                    @endphp

                    <hr>
                    
                    <h5>Tạo đơn hàng test:</h5>
                    <button class="btn btn-primary" onclick="createTestOrder()">Tạo đơn hàng test</button>
                    
                    <div id="test-result" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function createTestOrder() {
    // Tạo đơn hàng test với AJAX
    fetch('/admin/order/create-test', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            customer_name: 'Khách hàng test',
            customer_phone: '0901234567',
            products: [
                {
                    product_id: 1,
                    quantity: 1,
                    unit_price: 100000
                }
            ]
        })
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('test-result').innerHTML = 
            '<div class="alert alert-' + (data.success ? 'success' : 'danger') + '">' + 
            data.message + 
            '</div>';
        
        if (data.success) {
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        }
    })
    .catch(error => {
        document.getElementById('test-result').innerHTML = 
            '<div class="alert alert-danger">Lỗi: ' + error.message + '</div>';
    });
}
</script>
@endsection
