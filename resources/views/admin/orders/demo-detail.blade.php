@extends('admin.index')

@section('title', 'Demo Chi tiết đơn hàng')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Demo Chi tiết đơn hàng với Tên sản phẩm và Thông tin người bán</h3>
                </div>
                <div class="card-body">
                    <!-- Demo Order Detail -->
                    @php
                        // Tạo dữ liệu demo
                        $demoOrder = (object) [
                            'id' => 1,
                            'order_code' => 'ORD-2025-001',
                            'status' => 'completed',
                            'payment_status' => 'paid',
                            'delivery_status' => 'delivered',
                            'channel_display' => 'Online',
                            'final_amount' => 2500000,
                            'amount_paid' => 2500000,
                            'discount_amount' => 100000,
                            'created_at' => now(),
                            'due_date' => now()->addDays(7),
                            'status_badge' => '<span class="badge badge-light-success">Hoàn thành</span>',
                            'payment_status_badge' => '<span class="badge badge-light-success">Đã thanh toán</span>',
                            'delivery_status_badge' => '<span class="badge badge-light-success">Đã giao</span>',
                            'branch' => (object) ['name' => 'Chi nhánh Quận 1'],
                            'customer' => (object) [
                                'name' => 'Nguyễn Văn A',
                                'phone' => '0901234567',
                                'email' => 'nguyenvana@email.com',
                                'address' => '123 Nguyễn Huệ, Quận 1, TP.HCM'
                            ],
                            'creator' => (object) [
                                'name' => 'Admin User',
                                'email' => 'admin@yukimart.com'
                            ],
                            'seller' => (object) [
                                'name' => 'Trần Thị B',
                                'email' => 'tranthib@yukimart.com',
                                'phone' => '0987654321'
                            ],
                            'updater' => (object) [
                                'name' => 'Manager User'
                            ],
                            'updated_at' => now(),
                            'orderItems' => collect([
                                (object) [
                                    'product_name' => 'iPhone 15 Pro Max 256GB',
                                    'product_sku' => 'IP15PM256',
                                    'quantity' => 1,
                                    'unit_price' => 1500000,
                                    'total_price' => 1500000,
                                    'product' => (object) [
                                        'image' => null,
                                        'description' => 'Điện thoại thông minh cao cấp với chip A17 Pro, camera 48MP và màn hình Super Retina XDR 6.7 inch',
                                        'category' => (object) ['name' => 'Điện thoại']
                                    ]
                                ],
                                (object) [
                                    'product_name' => 'Samsung Galaxy S24 Ultra',
                                    'product_sku' => 'SGS24U',
                                    'quantity' => 1,
                                    'unit_price' => 1100000,
                                    'total_price' => 1100000,
                                    'product' => (object) [
                                        'image' => null,
                                        'description' => 'Smartphone Android flagship với S Pen, camera 200MP và màn hình Dynamic AMOLED 2X 6.8 inch',
                                        'category' => (object) ['name' => 'Điện thoại']
                                    ]
                                ]
                            ])
                        ];
                        $order = $demoOrder;
                    @endphp

                    @include('admin.orders.partials.detail', ['order' => $order])
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    console.log('Demo chi tiết đơn hàng đã được tải');
</script>
@endsection
