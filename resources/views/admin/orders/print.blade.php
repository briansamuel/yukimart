<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>In đơn hàng {{ $order->order_code }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 14px;
            line-height: 1.6;
            color: #333;
            background: white;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #2c5aa0;
            margin-bottom: 5px;
        }
        
        .company-info {
            font-size: 12px;
            color: #666;
        }
        
        .order-title {
            font-size: 20px;
            font-weight: bold;
            margin: 20px 0;
            text-align: center;
            text-transform: uppercase;
        }
        
        .order-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        
        .order-details, .customer-details {
            width: 48%;
        }
        
        .section-title {
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 10px;
            color: #2c5aa0;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        
        .info-row {
            margin-bottom: 8px;
        }
        
        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 120px;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        .items-table th,
        .items-table td {
            border: 1px solid #ddd;
            padding: 12px 8px;
            text-align: left;
        }
        
        .items-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            text-align: center;
        }
        
        .items-table .text-center {
            text-align: center;
        }
        
        .items-table .text-right {
            text-align: right;
        }
        
        .total-section {
            margin-top: 20px;
            text-align: right;
        }
        
        .total-row {
            margin-bottom: 8px;
        }
        
        .total-label {
            display: inline-block;
            width: 150px;
            font-weight: bold;
        }
        
        .total-amount {
            display: inline-block;
            width: 120px;
            text-align: right;
        }
        
        .grand-total {
            font-size: 18px;
            font-weight: bold;
            color: #2c5aa0;
            border-top: 2px solid #333;
            padding-top: 10px;
            margin-top: 10px;
        }
        
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        
        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
        }
        
        .signature-box {
            width: 30%;
            text-align: center;
        }
        
        .signature-line {
            border-bottom: 1px solid #333;
            margin-bottom: 5px;
            height: 60px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .status-processing { background-color: #fff3cd; color: #856404; }
        .status-completed { background-color: #d1edff; color: #0c5460; }
        .status-cancelled { background-color: #f8d7da; color: #721c24; }
        
        @media print {
            body { margin: 0; }
            .container { max-width: none; margin: 0; padding: 15px; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="company-name">YUKIMART</div>
            <div class="company-info">
                Địa chỉ: 123 Đường ABC, Quận XYZ, TP. Hồ Chí Minh<br>
                Điện thoại: (028) 1234 5678 | Email: info@yukimart.com<br>
                Website: www.yukimart.com
            </div>
        </div>

        <!-- Order Title -->
        <div class="order-title">Đơn hàng {{ $order->order_code }}</div>

        <!-- Order Information -->
        <div class="order-info">
            <div class="order-details">
                <div class="section-title">Thông tin đơn hàng</div>
                <div class="info-row">
                    <span class="info-label">Mã đơn hàng:</span>
                    <span>{{ $order->order_code }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Ngày tạo:</span>
                    <span>{{ $order->created_at->format('d/m/Y H:i') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Trạng thái:</span>
                    <span class="status-badge status-{{ $order->status }}">
                        @switch($order->status)
                            @case('processing') Đang xử lý @break
                            @case('completed') Hoàn thành @break
                            @case('cancelled') Đã hủy @break
                            @default {{ $order->status }}
                        @endswitch
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Kênh bán:</span>
                    <span>{{ $order->channel_display }}</span>
                </div>
                @if($order->branch)
                <div class="info-row">
                    <span class="info-label">Chi nhánh:</span>
                    <span>{{ $order->branch->name }}</span>
                </div>
                @endif
                @if($order->soldBy)
                <div class="info-row">
                    <span class="info-label">Nhân viên bán:</span>
                    <span>{{ $order->soldBy->name }}</span>
                </div>
                @endif
            </div>

            <div class="customer-details">
                <div class="section-title">Thông tin khách hàng</div>
                @if($order->customer)
                <div class="info-row">
                    <span class="info-label">Tên khách hàng:</span>
                    <span>{{ $order->customer->name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Số điện thoại:</span>
                    <span>{{ $order->customer->phone ?? 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span>{{ $order->customer->email ?? 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Địa chỉ:</span>
                    <span>{{ $order->customer->address ?? 'N/A' }}</span>
                </div>
                @else
                <div class="info-row">
                    <span>Khách lẻ</span>
                </div>
                @endif
            </div>
        </div>

        <!-- Order Items -->
        @if($order->orderItems && $order->orderItems->count() > 0)
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 5%">STT</th>
                    <th style="width: 40%">Sản phẩm</th>
                    <th style="width: 15%" class="text-center">Số lượng</th>
                    <th style="width: 20%" class="text-right">Đơn giá</th>
                    <th style="width: 20%" class="text-right">Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->orderItems as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $item->product_name }}</strong>
                        @if($item->product_sku)
                        <br><small>SKU: {{ $item->product_sku }}</small>
                        @endif
                    </td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">{{ number_format($item->unit_price, 0, ',', '.') }} ₫</td>
                    <td class="text-right">{{ number_format($item->total_price, 0, ',', '.') }} ₫</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        <!-- Total Section -->
        <div class="total-section">
            <div class="total-row">
                <span class="total-label">Tổng tiền hàng:</span>
                <span class="total-amount">{{ number_format($order->total_amount, 0, ',', '.') }} ₫</span>
            </div>
            @if($order->discount_amount > 0)
            <div class="total-row">
                <span class="total-label">Giảm giá:</span>
                <span class="total-amount">-{{ number_format($order->discount_amount, 0, ',', '.') }} ₫</span>
            </div>
            @endif
            <div class="total-row grand-total">
                <span class="total-label">Tổng thanh toán:</span>
                <span class="total-amount">{{ number_format($order->final_amount, 0, ',', '.') }} ₫</span>
            </div>
            @if($order->amount_paid > 0)
            <div class="total-row">
                <span class="total-label">Đã thanh toán:</span>
                <span class="total-amount">{{ number_format($order->amount_paid, 0, ',', '.') }} ₫</span>
            </div>
            <div class="total-row">
                <span class="total-label">Còn lại:</span>
                <span class="total-amount">{{ number_format($order->final_amount - $order->amount_paid, 0, ',', '.') }} ₫</span>
            </div>
            @endif
        </div>

        <!-- Notes -->
        @if($order->notes)
        <div style="margin-top: 30px;">
            <div class="section-title">Ghi chú</div>
            <p>{{ $order->notes }}</p>
        </div>
        @endif

        <!-- Signature Section -->
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-line"></div>
                <strong>Khách hàng</strong><br>
                <small>(Ký và ghi rõ họ tên)</small>
            </div>
            <div class="signature-box">
                <div class="signature-line"></div>
                <strong>Nhân viên bán hàng</strong><br>
                <small>(Ký và ghi rõ họ tên)</small>
            </div>
            <div class="signature-box">
                <div class="signature-line"></div>
                <strong>Thủ kho</strong><br>
                <small>(Ký và ghi rõ họ tên)</small>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Cảm ơn quý khách đã mua hàng tại YUKIMART!</p>
            <p>Đơn hàng được in lúc: {{ now()->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>

    <!-- Print Button (hidden when printing) -->
    <div class="no-print" style="position: fixed; top: 20px; right: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">
            In đơn hàng
        </button>
        <button onclick="window.close()" style="padding: 10px 20px; background: #6c757d; color: white; border: none; border-radius: 5px; cursor: pointer; margin-left: 10px;">
            Đóng
        </button>
    </div>

    <script>
        // Auto print when page loads
        window.onload = function() {
            // Uncomment the line below to auto-print
            // window.print();
        }
    </script>
</body>
</html>
