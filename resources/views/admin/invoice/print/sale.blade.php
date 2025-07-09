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
            max-width: 750px;
            margin: 0 auto;
            padding: 20px;
            background: white;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 20px;
            border-radius: 10px;
        }
        
        .company-info h1 {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .company-info p {
            margin: 2px 0;
            opacity: 0.9;
            font-size: 13px;
        }
        
        .invoice-title h2 {
            font-size: 26px;
            font-weight: bold;
            text-align: right;
        }
        
        .sale-badge {
            background: #ffc107;
            color: #212529;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            margin-top: 5px;
            display: inline-block;
        }
        
        .invoice-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .meta-section {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #28a745;
        }
        
        .meta-section h3 {
            color: #28a745;
            font-size: 16px;
            margin-bottom: 10px;
            font-weight: bold;
        }
        
        .meta-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            padding: 2px 0;
        }
        
        .meta-label {
            font-weight: 600;
            color: #555;
            width: 120px;
        }
        
        .meta-value {
            flex: 1;
            text-align: right;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .items-table th {
            background: #28a745;
            color: white;
            padding: 12px 10px;
            text-align: left;
            font-weight: bold;
            font-size: 13px;
        }
        
        .items-table td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .items-table tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        .items-table tr:hover {
            background: #e8f5e8;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .summary-container {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 25px;
        }
        
        .summary {
            width: 350px;
            border: 2px solid #28a745;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 15px;
            border-bottom: 1px solid #eee;
        }
        
        .summary-row.total-row {
            border-bottom: none;
            background: #28a745;
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
        
        .commission-info {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .commission-info h4 {
            color: #856404;
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .commission-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        
        .footer {
            text-align: center;
            color: #666;
            font-size: 12px;
            border-top: 2px solid #28a745;
            padding-top: 15px;
        }
        
        .footer p {
            margin: 3px 0;
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
                <p>123 Đường ABC, Quận XYZ, TP.HCM</p>
                <p>ĐT: (028) 1234 5678 | Email: info@yukimart.com</p>
                <p>Website: www.yukimart.com</p>
            </div>
            <div class="invoice-title">
                <h2>HÓA ĐƠN SALE</h2>
                <div class="sale-badge">KÊNH BÁN HÀNG</div>
            </div>
        </div>

        <!-- Invoice Meta -->
        <div class="invoice-meta">
            <div class="meta-section">
                <h3>Thông tin hóa đơn</h3>
                <div class="meta-row">
                    <span class="meta-label">Mã hóa đơn:</span>
                    <span class="meta-value">{{ $invoice->invoice_number }}</span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Ngày tạo:</span>
                    <span class="meta-value">{{ $invoice->created_at->format('d/m/Y H:i') }}</span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Kênh bán:</span>
                    <span class="meta-value">{{ $invoice->sales_channel ?? 'Sale' }}</span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Nhân viên sale:</span>
                    <span class="meta-value">{{ $invoice->creator->name ?? 'N/A' }}</span>
                </div>
            </div>
            
            <div class="meta-section">
                <h3>Thông tin khách hàng</h3>
                <div class="meta-row">
                    <span class="meta-label">Tên khách hàng:</span>
                    <span class="meta-value">{{ $invoice->customer->name ?? 'Khách lẻ' }}</span>
                </div>
                @if($invoice->customer)
                <div class="meta-row">
                    <span class="meta-label">Điện thoại:</span>
                    <span class="meta-value">{{ $invoice->customer->phone ?? 'N/A' }}</span>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Địa chỉ:</span>
                    <span class="meta-value">{{ $invoice->customer->address ?? 'N/A' }}</span>
                </div>
                @endif
                <div class="meta-row">
                    <span class="meta-label">Loại khách:</span>
                    <span class="meta-value">
                        @if($invoice->customer && $invoice->customer->customer_type == 'wholesale')
                            Khách sỉ
                        @else
                            Khách lẻ
                        @endif
                    </span>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 40px;">STT</th>
                    <th style="width: 100px;">Mã SP</th>
                    <th>Tên sản phẩm</th>
                    <th style="width: 60px;" class="text-center">SL</th>
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

        <!-- Commission Info -->
        <div class="commission-info">
            <h4><i class="fas fa-percentage"></i> Thông tin hoa hồng</h4>
            <div class="commission-row">
                <span>Tỷ lệ hoa hồng:</span>
                <span><strong>5%</strong></span>
            </div>
            <div class="commission-row">
                <span>Hoa hồng dự kiến:</span>
                <span><strong>{{ number_format($invoice->total_amount * 0.05, 0, ',', '.') }}₫</strong></span>
            </div>
        </div>

        <!-- Summary -->
        <div class="summary-container">
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
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>Cảm ơn quý khách đã tin tưởng và sử dụng dịch vụ!</strong></p>
            <p>Hóa đơn được in lúc: {{ now()->format('d/m/Y H:i:s') }}</p>
            <p>Mọi thắc mắc xin liên hệ hotline: (028) 1234 5678</p>
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
