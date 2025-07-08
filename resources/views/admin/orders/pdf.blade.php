<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đơn hàng {{ $order->order_code }}</title>
    <style>
        @page {
            margin: 20mm;
            size: A4;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        
        .company-name {
            font-size: 20px;
            font-weight: bold;
            color: #2c5aa0;
            margin-bottom: 5px;
        }
        
        .company-info {
            font-size: 10px;
            color: #666;
        }
        
        .order-title {
            font-size: 16px;
            font-weight: bold;
            margin: 15px 0;
            text-align: center;
            text-transform: uppercase;
        }
        
        .order-info {
            width: 100%;
            margin-bottom: 20px;
        }
        
        .order-info table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .order-info td {
            padding: 5px;
            vertical-align: top;
            width: 50%;
        }
        
        .section-title {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 8px;
            color: #2c5aa0;
            border-bottom: 1px solid #ddd;
            padding-bottom: 3px;
        }
        
        .info-row {
            margin-bottom: 5px;
            font-size: 11px;
        }
        
        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 100px;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        
        .items-table th,
        .items-table td {
            border: 1px solid #ddd;
            padding: 8px 5px;
            text-align: left;
            font-size: 10px;
        }
        
        .items-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            text-align: center;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .total-section {
            margin-top: 15px;
            text-align: right;
        }
        
        .total-row {
            margin-bottom: 5px;
            font-size: 11px;
        }
        
        .total-label {
            display: inline-block;
            width: 120px;
            font-weight: bold;
        }
        
        .total-amount {
            display: inline-block;
            width: 100px;
            text-align: right;
        }
        
        .grand-total {
            font-size: 14px;
            font-weight: bold;
            color: #2c5aa0;
            border-top: 2px solid #333;
            padding-top: 8px;
            margin-top: 8px;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
        
        .signature-section {
            margin-top: 30px;
        }
        
        .signature-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .signature-table td {
            width: 33.33%;
            text-align: center;
            padding: 10px;
            vertical-align: top;
        }
        
        .signature-line {
            border-bottom: 1px solid #333;
            margin-bottom: 5px;
            height: 40px;
        }
        
        .signature-title {
            font-weight: bold;
            font-size: 11px;
        }
        
        .signature-subtitle {
            font-size: 9px;
            color: #666;
        }
        
        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        
        .status-processing { background-color: #fff3cd; color: #856404; }
        .status-completed { background-color: #d1edff; color: #0c5460; }
        .status-cancelled { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="company-name">YUKIMART</div>
        <div class="company-info">
            Địa chỉ: 123 Đường ABC, Quận XYZ, TP. Hồ Chí Minh<br>
            Điện thoại: (028) 1234 5678 | Email: info@yukimart.com | Website: www.yukimart.com
        </div>
    </div>

    <!-- Order Title -->
    <div class="order-title">Đơn hàng {{ $order->order_code }}</div>

    <!-- Order Information -->
    <div class="order-info">
        <table>
            <tr>
                <td>
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
                        <span class="info-label">NV bán:</span>
                        <span>{{ $order->soldBy->name }}</span>
                    </div>
                    @endif
                </td>
                <td>
                    <div class="section-title">Thông tin khách hàng</div>
                    @if($order->customer)
                    <div class="info-row">
                        <span class="info-label">Tên KH:</span>
                        <span>{{ $order->customer->name }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Số ĐT:</span>
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
                </td>
            </tr>
        </table>
    </div>

    <!-- Order Items -->
    @if($order->orderItems && $order->orderItems->count() > 0)
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%">STT</th>
                <th style="width: 45%">Sản phẩm</th>
                <th style="width: 10%">SL</th>
                <th style="width: 20%">Đơn giá</th>
                <th style="width: 20%">Thành tiền</th>
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
    <div style="margin-top: 20px;">
        <div class="section-title">Ghi chú</div>
        <p style="font-size: 11px;">{{ $order->notes }}</p>
    </div>
    @endif

    <!-- Signature Section -->
    <div class="signature-section">
        <table class="signature-table">
            <tr>
                <td>
                    <div class="signature-line"></div>
                    <div class="signature-title">Khách hàng</div>
                    <div class="signature-subtitle">(Ký và ghi rõ họ tên)</div>
                </td>
                <td>
                    <div class="signature-line"></div>
                    <div class="signature-title">Nhân viên bán hàng</div>
                    <div class="signature-subtitle">(Ký và ghi rõ họ tên)</div>
                </td>
                <td>
                    <div class="signature-line"></div>
                    <div class="signature-title">Thủ kho</div>
                    <div class="signature-subtitle">(Ký và ghi rõ họ tên)</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Cảm ơn quý khách đã mua hàng tại YUKIMART!</p>
        <p>Đơn hàng được xuất lúc: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>
