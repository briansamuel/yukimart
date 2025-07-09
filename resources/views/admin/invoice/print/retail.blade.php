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
            font-size: 13px;
            line-height: 1.3;
            color: #333;
            background: white;
        }
        
        .invoice-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 15px;
            background: white;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 15px;
        }
        
        .company-info h1 {
            color: #17a2b8;
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .company-info p {
            margin: 1px 0;
            color: #666;
            font-size: 12px;
        }
        
        .invoice-title {
            margin: 15px 0 10px 0;
        }
        
        .invoice-title h2 {
            color: #17a2b8;
            font-size: 18px;
            font-weight: bold;
        }
        
        .invoice-meta {
            margin-bottom: 20px;
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
        }
        
        .meta-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }
        
        .meta-label {
            font-weight: bold;
            color: #555;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 12px;
        }
        
        .items-table th {
            background: #17a2b8;
            color: white;
            padding: 8px 5px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
        }
        
        .items-table td {
            padding: 6px 5px;
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
            border: 1px solid #ddd;
            border-radius: 5px;
            overflow: hidden;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 10px;
            border-bottom: 1px solid #eee;
            font-size: 13px;
        }
        
        .summary-row.total-row {
            border-bottom: none;
            background: #17a2b8;
            color: white;
            font-weight: bold;
            font-size: 14px;
        }

        .summary-row.remaining-row {
            border-bottom: none;
            background: #f8f9fa;
            color: #dc3545;
            font-weight: bold;
        }
        
        .payment-info {
            margin-top: 15px;
            padding: 10px;
            background: #e7f3ff;
            border-radius: 5px;
            font-size: 12px;
        }
        
        .footer {
            margin-top: 20px;
            text-align: center;
            color: #666;
            font-size: 11px;
            border-top: 1px dashed #ccc;
            padding-top: 10px;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            
            .invoice-container {
                max-width: none;
                margin: 0;
                padding: 10px;
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
                <p>123 Đường ABC, Quận XYZ, TP.HCM</p>
                <p>ĐT: (028) 1234 5678 | Email: info@yukimart.com</p>
            </div>
            <div class="invoice-title">
                <h2>HÓA ĐƠN BÁN LẺ</h2>
            </div>
        </div>

        <!-- Invoice Meta -->
        <div class="invoice-meta">
            <div class="meta-row">
                <span class="meta-label">Mã hóa đơn:</span>
                <span>{{ $invoice->invoice_number }}</span>
            </div>
            <div class="meta-row">
                <span class="meta-label">Ngày:</span>
                <span>{{ $invoice->created_at->format('d/m/Y H:i') }}</span>
            </div>
            <div class="meta-row">
                <span class="meta-label">Khách hàng:</span>
                <span>{{ $invoice->customer->name ?? 'Khách lẻ' }}</span>
            </div>
            @if($invoice->customer && $invoice->customer->phone)
            <div class="meta-row">
                <span class="meta-label">Điện thoại:</span>
                <span>{{ $invoice->customer->phone }}</span>
            </div>
            @endif
            <div class="meta-row">
                <span class="meta-label">Thu ngân:</span>
                <span>{{ $invoice->creator->name ?? 'N/A' }}</span>
            </div>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 30px;">STT</th>
                    <th>Tên sản phẩm</th>
                    <th style="width: 50px;" class="text-center">SL</th>
                    <th style="width: 80px;" class="text-right">Đơn giá</th>
                    <th style="width: 90px;" class="text-right">Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->invoiceItems as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        {{ $item->product_name }}
                        @if($item->product_sku)
                        <br><small style="color: #666;">{{ $item->product_sku }}</small>
                        @endif
                    </td>
                    <td class="text-center">{{ number_format($item->quantity, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item->unit_price, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item->total_price, 0, ',', '.') }}</td>
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
                <span>Tổng thanh toán:</span>
                <span>{{ number_format($invoice->total_amount, 0, ',', '.') }}₫</span>
            </div>
            <div class="summary-row">
                <span>Đã thanh toán:</span>
                <span>{{ number_format($invoice->paid_amount ?? 0, 0, ',', '.') }}₫</span>
            </div>
            <div class="summary-row remaining-row">
                <span>Còn lại:</span>
                <span>{{ number_format($invoice->remaining_amount ?? ($invoice->total_amount - ($invoice->paid_amount ?? 0)), 0, ',', '.') }}₫</span>
            </div>
        </div>

        <!-- Payment Info -->
        @if($invoice->payment_method)
        <div class="payment-info">
            <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                <span><strong>Phương thức thanh toán:</strong></span>
                <span>
                    @switch($invoice->payment_method)
                        @case('cash')
                            Tiền mặt
                            @break
                        @case('transfer')
                            Chuyển khoản
                            @break
                        @case('card')
                            Thẻ
                            @break
                        @case('e_wallet')
                            Ví điện tử
                            @break
                        @default
                            {{ $invoice->payment_method }}
                    @endswitch
                </span>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span><strong>Trạng thái:</strong></span>
                <span>
                    @if($invoice->payment_status == 'paid')
                        Đã thanh toán
                    @elseif($invoice->payment_status == 'partial')
                        Thanh toán một phần
                    @else
                        Chưa thanh toán
                    @endif
                </span>
            </div>
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p><strong>Cảm ơn quý khách và hẹn gặp lại!</strong></p>
            <p>Hóa đơn được in lúc: {{ now()->format('d/m/Y H:i:s') }}</p>
            <p style="margin-top: 5px;">
                <small>Quý khách vui lòng kiểm tra hàng hóa khi nhận. Hàng đã bán không đổi trả.</small>
            </p>
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
