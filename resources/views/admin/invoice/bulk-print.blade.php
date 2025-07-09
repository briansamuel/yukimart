<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>In Nhiều Hóa Đơn</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 14px;
            line-height: 1.6;
            color: #333;
            background: #fff;
        }

        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: #fff;
        }

        .page-break {
            page-break-before: always;
        }

        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
            border-bottom: 2px solid #eee;
            padding-bottom: 20px;
        }

        .company-info {
            flex: 1;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .company-details {
            font-size: 12px;
            color: #666;
            line-height: 1.4;
        }

        .invoice-title {
            text-align: right;
            flex: 1;
        }

        .invoice-title h1 {
            font-size: 32px;
            font-weight: bold;
            color: #e74c3c;
            margin-bottom: 10px;
        }

        .invoice-number {
            font-size: 16px;
            font-weight: bold;
            color: #2c3e50;
        }

        .invoice-date {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }

        .invoice-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .bill-to, .ship-to {
            flex: 1;
            margin-right: 20px;
        }

        .bill-to:last-child, .ship-to:last-child {
            margin-right: 0;
        }

        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }

        .customer-info {
            font-size: 14px;
            line-height: 1.5;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .items-table th,
        .items-table td {
            padding: 8px 6px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            font-size: 12px;
        }

        .items-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #2c3e50;
            border-bottom: 2px solid #ddd;
        }

        .items-table .text-right {
            text-align: right;
        }

        .items-table .text-center {
            text-align: center;
        }

        .totals-section {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 20px;
        }

        .totals-table {
            width: 250px;
        }

        .totals-table tr td {
            padding: 6px 10px;
            border-bottom: 1px solid #eee;
            font-size: 12px;
        }

        .totals-table tr:last-child td {
            border-bottom: 2px solid #2c3e50;
            font-weight: bold;
            font-size: 14px;
            background-color: #f8f9fa;
        }

        .totals-table .label {
            text-align: left;
            color: #666;
        }

        .totals-table .amount {
            text-align: right;
            font-weight: bold;
        }

        .invoice-footer {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #eee;
            text-align: center;
            font-size: 11px;
            color: #666;
        }

        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-paid {
            background-color: #d4edda;
            color: #155724;
        }

        .status-unpaid {
            background-color: #f8d7da;
            color: #721c24;
        }

        .status-partial {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-completed {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .status-processing {
            background-color: #e2e3e5;
            color: #383d41;
        }

        /* Print styles */
        @media print {
            body {
                margin: 0;
                padding: 0;
            }

            .invoice-container {
                max-width: none;
                margin: 0;
                padding: 15px;
                box-shadow: none;
            }

            .no-print {
                display: none !important;
            }

            .page-break {
                page-break-before: always;
            }
        }
    </style>
</head>
<body>
    @foreach($invoices as $index => $invoice)
        @if($index > 0)
            <div class="page-break"></div>
        @endif
        
        <div class="invoice-container">
            <!-- Header -->
            <div class="invoice-header">
                <div class="company-info">
                    <div class="company-name">YukiMart</div>
                    <div class="company-details">
                        <strong>Địa chỉ:</strong> 123 Đường ABC, Quận XYZ, TP.HCM<br>
                        <strong>Điện thoại:</strong> (028) 1234 5678<br>
                        <strong>Email:</strong> info@yukimart.com
                    </div>
                </div>
                <div class="invoice-title">
                    <h1>HÓA ĐƠN</h1>
                    <div class="invoice-number">{{ $invoice->invoice_number }}</div>
                    <div class="invoice-date">Ngày: {{ $invoice->created_at->format('d/m/Y') }}</div>
                </div>
            </div>

            <!-- Customer Information -->
            <div class="invoice-details">
                <div class="bill-to">
                    <div class="section-title">Khách hàng</div>
                    <div class="customer-info">
                        <strong>{{ $invoice->customer ? $invoice->customer->name : 'Khách lẻ' }}</strong><br>
                        @if($invoice->customer && $invoice->customer->phone)
                            ĐT: {{ $invoice->customer->phone }}<br>
                        @endif
                        @if($invoice->customer && $invoice->customer->address)
                            {{ $invoice->customer->address }}
                        @endif
                    </div>
                </div>
                <div class="ship-to">
                    <div class="section-title">Thông tin</div>
                    <div class="customer-info">
                        <strong>TT:</strong> 
                        <span class="status-badge status-{{ $invoice->status == 'completed' ? 'completed' : 'processing' }}">
                            {{ $invoice->status == 'completed' ? 'Hoàn thành' : 'Đang xử lý' }}
                        </span><br>
                        <strong>TT:</strong> 
                        <span class="status-badge status-{{ $invoice->payment_status }}">
                            {{ $invoice->payment_status == 'paid' ? 'Đã TT' : 'Chưa TT' }}
                        </span><br>
                        <strong>PT:</strong> {{ $invoice->payment_method }}
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 5%">#</th>
                        <th style="width: 50%">Sản phẩm</th>
                        <th style="width: 10%" class="text-center">SL</th>
                        <th style="width: 15%" class="text-right">Đơn giá</th>
                        <th style="width: 20%" class="text-right">Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->invoiceItems as $itemIndex => $item)
                    <tr>
                        <td class="text-center">{{ $itemIndex + 1 }}</td>
                        <td>
                            <strong>{{ $item->product ? $item->product->name : $item->product_name }}</strong>
                            @if($item->product && $item->product->sku)
                                <br><small>{{ $item->product->sku }}</small>
                            @endif
                        </td>
                        <td class="text-center">{{ number_format($item->quantity, 0) }}</td>
                        <td class="text-right">{{ number_format($item->unit_price, 0) }}₫</td>
                        <td class="text-right">{{ number_format($item->total_price, 0) }}₫</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Totals -->
            <div class="totals-section">
                <table class="totals-table">
                    <tr>
                        <td class="label">Tạm tính:</td>
                        <td class="amount">{{ number_format($invoice->subtotal, 0) }}₫</td>
                    </tr>
                    @if($invoice->discount_amount > 0)
                    <tr>
                        <td class="label">Giảm giá:</td>
                        <td class="amount">-{{ number_format($invoice->discount_amount, 0) }}₫</td>
                    </tr>
                    @endif
                    @if($invoice->tax_amount > 0)
                    <tr>
                        <td class="label">Thuế:</td>
                        <td class="amount">{{ number_format($invoice->tax_amount, 0) }}₫</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="label">Tổng cộng:</td>
                        <td class="amount">{{ number_format($invoice->total_amount, 0) }}₫</td>
                    </tr>
                </table>
            </div>

            <!-- Footer -->
            <div class="invoice-footer">
                <p><strong>Cảm ơn quý khách!</strong></p>
                <p>In lúc: {{ now()->format('d/m/Y H:i') }}</p>
            </div>
        </div>
    @endforeach

    <script>
        // Auto print when page loads
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
