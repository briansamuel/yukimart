<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hóa đơn {{ $invoice->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            font-size: 14px;
            line-height: 1.4;
            color: #333;
            background: white;
        }
        
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: white;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
        }
        
        .company-info h1 {
            color: #007bff;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .company-info p {
            margin: 2px 0;
            color: #666;
        }
        
        .invoice-title {
            text-align: right;
        }
        
        .invoice-title h2 {
            color: #007bff;
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .invoice-meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        
        .invoice-details, .customer-details {
            width: 48%;
        }
        
        .invoice-details h3, .customer-details h3 {
            color: #007bff;
            font-size: 16px;
            margin-bottom: 10px;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }
        
        .detail-row {
            display: flex;
            margin-bottom: 5px;
        }
        
        .detail-label {
            font-weight: bold;
            width: 120px;
            color: #555;
        }
        
        .detail-value {
            flex: 1;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        .items-table th {
            background: #007bff;
            color: white;
            padding: 12px 8px;
            text-align: left;
            font-weight: bold;
        }
        
        .items-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #eee;
        }
        
        .items-table tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .summary {
            margin-left: auto;
            width: 300px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 15px;
            border-bottom: 1px solid #eee;
        }

        .summary-row.total-row {
            border-bottom: none;
            background: #007bff;
            color: white;
            font-weight: bold;
            font-size: 16px;
        }

        .summary-row.remaining-row {
            border-bottom: none;
            background: #f8f9fa;
            color: #dc3545;
            font-weight: bold;
        }

        .payment-info {
            margin-top: 30px;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }

        .payment-info h3 {
            color: #007bff;
            font-size: 16px;
            margin-bottom: 15px;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }

        .payment-details {
            display: grid;
            grid-template-columns: 1fr;
            gap: 8px;
        }

        .payment-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
        }

        .payment-status {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }

        .payment-status-unpaid {
            background-color: #f8d7da;
            color: #721c24;
        }

        .payment-status-partial {
            background-color: #fff3cd;
            color: #856404;
        }

        .payment-status-paid {
            background-color: #d4edda;
            color: #155724;
        }

        .payment-status-overpaid {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        
        .footer {
            margin-top: 40px;
            text-align: center;
            color: #666;
            font-size: 12px;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            
            .invoice-container {
                max-width: none;
                margin: 0;
                padding: 15px;
            }
            
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Header -->
        <div class="header">
            <div class="company-info">
                <h1>YUKIMART</h1>
                <p><strong>Địa chỉ:</strong> 123 Đường ABC, Quận XYZ, TP.HCM</p>
                <p><strong>Điện thoại:</strong> (028) 1234 5678</p>
                <p><strong>Email:</strong> info@yukimart.com</p>
                <p><strong>Website:</strong> www.yukimart.com</p>
            </div>
            <div class="invoice-title">
                <h2>HÓA ĐƠN SỈ, CTV</h2>
                <p style="color: #666; margin-top: 5px;">Dành cho khách hàng sỉ và cộng tác viên</p>
            </div>
        </div>

        <!-- Invoice Meta -->
        <div class="invoice-meta">
            <div class="invoice-details">
                <h3>Thông tin hóa đơn</h3>
                <div class="detail-row">
                    <span class="detail-label">Mã hóa đơn:</span>
                    <span class="detail-value">{{ $invoice->invoice_number }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Ngày tạo:</span>
                    <span class="detail-value">{{ $invoice->created_at->format('d/m/Y H:i') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Kênh bán:</span>
                    <span class="detail-value">{{ $invoice->sales_channel ?? 'Trực tiếp' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Người tạo:</span>
                    <span class="detail-value">{{ $invoice->creator->name ?? 'N/A' }}</span>
                </div>
            </div>
            
            <div class="customer-details">
                <h3>Thông tin khách hàng</h3>
                <div class="detail-row">
                    <span class="detail-label">Tên khách hàng:</span>
                    <span class="detail-value">{{ $invoice->customer->name ?? 'Khách lẻ' }}</span>
                </div>
                @if($invoice->customer)
                <div class="detail-row">
                    <span class="detail-label">Điện thoại:</span>
                    <span class="detail-value">{{ $invoice->customer->phone ?? 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Địa chỉ:</span>
                    <span class="detail-value">{{ $invoice->customer->address ?? 'N/A' }}</span>
                </div>
                @endif
            </div>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 50px;">STT</th>
                    <th style="width: 120px;">Mã SP</th>
                    <th>Tên sản phẩm</th>
                    <th style="width: 80px;" class="text-center">SL</th>
                    <th style="width: 100px;" class="text-right">Đơn giá</th>
                    <th style="width: 80px;" class="text-right">Giảm giá</th>
                    <th style="width: 120px;" class="text-right">Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->invoiceItems as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item->product_sku ?? 'N/A' }}</td>
                    <td>{{ $item->product_name }}</td>
                    <td class="text-center">{{ number_format($item->quantity, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item->unit_price, 0, ',', '.') }}₫</td>
                    <td class="text-right">{{ number_format($item->discount_amount ?? 0, 0, ',', '.') }}₫</td>
                    <td class="text-right">{{ number_format($item->total_price, 0, ',', '.') }}₫</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Summary -->
        <div class="summary">
            <div class="summary-row">
                <span>Tổng tiền hàng:</span>
                <span>{{ number_format($invoice->subtotal ?? 0, 0, ',', '.') }}₫</span>
            </div>
            @if($invoice->discount_amount > 0)
            <div class="summary-row">
                <span>Giảm giá:</span>
                <span>-{{ number_format($invoice->discount_amount, 0, ',', '.') }}₫</span>
            </div>
            @endif
            @if($invoice->tax_amount > 0)
            <div class="summary-row">
                <span>Thuế ({{ $invoice->tax_rate }}%):</span>
                <span>{{ number_format($invoice->tax_amount, 0, ',', '.') }}₫</span>
            </div>
            @endif
            <div class="summary-row total-row">
                <span><strong>Tổng cộng:</strong></span>
                <span><strong>{{ number_format($invoice->total_amount, 0, ',', '.') }}₫</strong></span>
            </div>
            <div class="summary-row">
                <span>Đã thanh toán:</span>
                <span>{{ number_format($invoice->paid_amount ?? 0, 0, ',', '.') }}₫</span>
            </div>
            <div class="summary-row remaining-row">
                <span><strong>Còn lại:</strong></span>
                <span><strong>{{ number_format($invoice->remaining_amount ?? ($invoice->total_amount - ($invoice->paid_amount ?? 0)), 0, ',', '.') }}₫</strong></span>
            </div>
        </div>

        <!-- Payment Information -->
        <div class="payment-info">
            <h3>Thông tin thanh toán</h3>
            <div class="payment-details">
                <div class="payment-row">
                    <span>Phương thức thanh toán:</span>
                    <span>
                        @switch($invoice->payment_method)
                            @case('cash')
                                Tiền mặt
                                @break
                            @case('card')
                                Thẻ
                                @break
                            @case('transfer')
                                Chuyển khoản
                                @break
                            @case('check')
                                Séc
                                @break
                            @case('other')
                                Khác
                                @break
                            @default
                                Chưa xác định
                        @endswitch
                    </span>
                </div>
                <div class="payment-row">
                    <span>Trạng thái thanh toán:</span>
                    <span class="payment-status payment-status-{{ $invoice->payment_status }}">
                        @switch($invoice->payment_status)
                            @case('unpaid')
                                Chưa thanh toán
                                @break
                            @case('partial')
                                Thanh toán một phần
                                @break
                            @case('paid')
                                Đã thanh toán
                                @break
                            @case('overpaid')
                                Thanh toán thừa
                                @break
                            @default
                                Không xác định
                        @endswitch
                    </span>
                </div>
                @if($invoice->paid_at)
                <div class="payment-row">
                    <span>Ngày thanh toán:</span>
                    <span>{{ $invoice->paid_at->format('d/m/Y H:i') }}</span>
                </div>
                @endif
                @if($invoice->due_date)
                <div class="payment-row">
                    <span>Hạn thanh toán:</span>
                    <span>{{ $invoice->due_date->format('d/m/Y') }}</span>
                </div>
                @endif
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Cảm ơn quý khách đã sử dụng dịch vụ của chúng tôi!</p>
            <p>Hóa đơn được in lúc: {{ now()->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>

    <script>
        // Auto print when page loads
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
