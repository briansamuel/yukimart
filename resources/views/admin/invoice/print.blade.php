<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>In Hóa Đơn - {{ $invoice->invoice_number }}</title>
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

        .company-logo {
            max-width: 150px;
            height: auto;
            margin-bottom: 10px;
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
            padding: 12px 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
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
            margin-bottom: 30px;
        }

        .totals-table {
            width: 300px;
        }

        .totals-table tr td {
            padding: 8px 12px;
            border-bottom: 1px solid #eee;
        }

        .totals-table tr:last-child td {
            border-bottom: 2px solid #2c3e50;
            font-weight: bold;
            font-size: 16px;
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
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            text-align: center;
            font-size: 12px;
            color: #666;
        }

        .payment-info {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }

        .payment-info h4 {
            margin-bottom: 10px;
            color: #2c3e50;
        }

        .notes {
            margin-top: 20px;
            padding: 15px;
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            border-radius: 0 5px 5px 0;
        }

        .notes h4 {
            margin-bottom: 10px;
            color: #856404;
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
                padding: 0;
                box-shadow: none;
            }

            .no-print {
                display: none !important;
            }

            .page-break {
                page-break-before: always;
            }
        }

        /* Status badges */
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
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
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Header -->
        <div class="invoice-header">
            <div class="company-info">
                <div class="company-name">YukiMart</div>
                <div class="company-details">
                    <strong>Địa chỉ:</strong> 123 Đường ABC, Quận XYZ, TP.HCM<br>
                    <strong>Điện thoại:</strong> (028) 1234 5678<br>
                    <strong>Email:</strong> info@yukimart.com<br>
                    <strong>Website:</strong> www.yukimart.com
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
                <div class="section-title">Thông tin khách hàng</div>
                <div class="customer-info">
                    <strong>{{ $invoice->customer ? $invoice->customer->name : 'Khách lẻ' }}</strong><br>
                    @if($invoice->customer && $invoice->customer->phone)
                        Điện thoại: {{ $invoice->customer->phone }}<br>
                    @endif
                    @if($invoice->customer && $invoice->customer->email)
                        Email: {{ $invoice->customer->email }}<br>
                    @endif
                    @if($invoice->customer && $invoice->customer->address)
                        Địa chỉ: {{ $invoice->customer->address }}
                    @endif
                </div>
            </div>
            <div class="ship-to">
                <div class="section-title">Thông tin đơn hàng</div>
                <div class="customer-info">
                    <strong>Trạng thái:</strong> 
                    <span class="status-badge status-{{ $invoice->status == 'completed' ? 'completed' : 'processing' }}">
                        {{ $invoice->status == 'completed' ? 'Hoàn thành' : 'Đang xử lý' }}
                    </span><br>
                    <strong>Thanh toán:</strong> 
                    <span class="status-badge status-{{ $invoice->payment_status }}">
                        {{ $invoice->payment_status == 'paid' ? 'Đã thanh toán' : ($invoice->payment_status == 'partial' ? 'Thanh toán một phần' : 'Chưa thanh toán') }}
                    </span><br>
                    <strong>Phương thức TT:</strong> {{ $invoice->payment_method }}<br>
                    <strong>Kênh bán:</strong> {{ $invoice->sales_channel }}<br>
                    @if($invoice->branchShop)
                        <strong>Chi nhánh:</strong> {{ $invoice->branchShop->name }}
                    @endif
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 5%">#</th>
                    <th style="width: 45%">Sản phẩm</th>
                    <th style="width: 15%" class="text-center">Số lượng</th>
                    <th style="width: 15%" class="text-right">Đơn giá</th>
                    <th style="width: 20%" class="text-right">Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->invoiceItems as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $item->product ? $item->product->name : $item->product_name }}</strong>
                        @if($item->product && $item->product->sku)
                            <br><small>SKU: {{ $item->product->sku }}</small>
                        @endif
                    </td>
                    <td class="text-center">{{ number_format($item->quantity, 0) }}</td>
                    <td class="text-right">{{ number_format($item->unit_price, 0) }} ₫</td>
                    <td class="text-right">{{ number_format($item->total_price, 0) }} ₫</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals-section">
            <table class="totals-table">
                <tr>
                    <td class="label">Tạm tính:</td>
                    <td class="amount">{{ number_format($invoice->subtotal, 0) }} ₫</td>
                </tr>
                @if($invoice->discount_amount > 0)
                <tr>
                    <td class="label">Giảm giá:</td>
                    <td class="amount">-{{ number_format($invoice->discount_amount, 0) }} ₫</td>
                </tr>
                @endif
                @if($invoice->tax_amount > 0)
                <tr>
                    <td class="label">Thuế:</td>
                    <td class="amount">{{ number_format($invoice->tax_amount, 0) }} ₫</td>
                </tr>
                @endif
                @if($invoice->other_amount > 0)
                <tr>
                    <td class="label">Phí khác:</td>
                    <td class="amount">{{ number_format($invoice->other_amount, 0) }} ₫</td>
                </tr>
                @endif
                <tr>
                    <td class="label">Tổng cộng:</td>
                    <td class="amount">{{ number_format($invoice->total_amount, 0) }} ₫</td>
                </tr>
            </table>
        </div>

        <!-- Payment Information -->
        @if($invoice->payment_status == 'paid' || $invoice->paid_amount > 0)
        <div class="payment-info">
            <h4>Thông tin thanh toán</h4>
            <p><strong>Đã thanh toán:</strong> {{ number_format($invoice->paid_amount, 0) }} ₫</p>
            @if($invoice->total_amount - $invoice->paid_amount > 0)
                <p><strong>Còn lại:</strong> {{ number_format($invoice->total_amount - $invoice->paid_amount, 0) }} ₫</p>
            @endif
        </div>
        @endif

        <!-- Notes -->
        @if($invoice->notes)
        <div class="notes">
            <h4>Ghi chú</h4>
            <p>{{ $invoice->notes }}</p>
        </div>
        @endif

        <!-- Footer -->
        <div class="invoice-footer">
            <p><strong>Cảm ơn quý khách đã mua hàng!</strong></p>
            <p>Hóa đơn được in vào {{ now()->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>

    <script>
        // Auto print when page loads
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
