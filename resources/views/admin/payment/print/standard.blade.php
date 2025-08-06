<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phiếu Thu Chi - {{ $payment->payment_number ?? 'N/A' }}</title>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }

        body {
            font-family: 'Times New Roman', serif;
            font-size: 14px;
            line-height: 1.4;
            margin: 20px;
            color: #000;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 20px;
        }

        .company-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .company-info {
            font-size: 12px;
            margin-bottom: 10px;
        }

        .document-title {
            font-size: 20px;
            font-weight: bold;
            margin-top: 15px;
            text-transform: uppercase;
        }

        .document-code {
            font-size: 14px;
            margin-top: 5px;
        }

        .content {
            margin: 30px 0;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            align-items: flex-start;
        }

        .info-left, .info-right {
            width: 48%;
        }

        .info-item {
            margin-bottom: 8px;
        }

        .label {
            font-weight: bold;
            display: inline-block;
            width: 120px;
        }

        .amount-section {
            margin: 30px 0;
            padding: 20px;
            border: 2px solid #000;
            text-align: center;
        }

        .amount-label {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .amount-number {
            font-size: 24px;
            font-weight: bold;
            color: #d63384;
            margin-bottom: 10px;
        }

        .amount-words {
            font-style: italic;
            font-size: 14px;
        }

        .note-section {
            margin: 20px 0;
        }

        .signatures {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }

        .signature-box {
            text-align: center;
            width: 30%;
        }

        .signature-title {
            font-weight: bold;
            margin-bottom: 60px;
        }

        .signature-name {
            border-top: 1px solid #000;
            padding-top: 5px;
            font-style: italic;
        }

        .print-info {
            margin-top: 30px;
            font-size: 12px;
            color: #666;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">YUKIMART</div>
        <div class="company-info">
            @if($payment->branchShop)
                {{ $payment->branchShop->name }}<br>
                {{ $payment->branchShop->address ?? '' }}
            @else
                Chi nhánh: N/A
            @endif
        </div>
        <div class="document-title">
            @if($payment->payment_type == 'receipt')
                PHIẾU THU
            @else
                PHIẾU CHI
            @endif
        </div>
        <div class="document-code">Số: {{ $payment->payment_number ?? 'N/A' }}</div>
    </div>

    <div class="content">
        <div class="info-row">
            <div class="info-left">
                <div class="info-item">
                    <span class="label">Ngày:</span>
                    {{ $payment->created_at ? $payment->created_at->format('d/m/Y') : 'N/A' }}
                </div>
                <div class="info-item">
                    <span class="label">Người tạo:</span>
                    {{ $payment->creator->full_name ?? 'N/A' }}
                </div>
                @if($payment->reference)
                <div class="info-item">
                    <span class="label">
                        @if($payment->reference_type == 'invoice')
                            Hóa đơn:
                        @elseif($payment->reference_type == 'order')
                            Đơn hàng:
                        @else
                            Tham chiếu:
                        @endif
                    </span>
                    {{ $payment->reference->invoice_number ?? $payment->reference->order_number ?? 'N/A' }}
                </div>
                @endif
            </div>
            <div class="info-right">
                <div class="info-item">
                    <span class="label">Loại:</span>
                    @if($payment->payment_type == 'receipt')
                        Thu tiền
                    @else
                        Chi tiền
                    @endif
                </div>
                @if($payment->bankAccount)
                <div class="info-item">
                    <span class="label">Tài khoản:</span>
                    {{ $payment->bankAccount->bank_name ?? 'N/A' }} - {{ $payment->bankAccount->account_number ?? 'N/A' }}
                </div>
                @else
                <div class="info-item">
                    <span class="label">Tài khoản:</span>
                    Tiền mặt
                </div>
                @endif
            </div>
        </div>

        <div class="amount-section">
            <div class="amount-label">
                @if($payment->payment_type == 'receipt')
                    SỐ TIỀN THU
                @else
                    SỐ TIỀN CHI
                @endif
            </div>
            <div class="amount-number">
                {{ number_format($payment->amount ?? 0, 0, ',', '.') }} VNĐ
            </div>
            <div class="amount-words">
                Bằng chữ: ................................................
            </div>
        </div>

        @if($payment->description)
        <div class="note-section">
            <div class="info-item">
                <span class="label">Nội dung:</span>
                {{ $payment->description ?? '' }}
            </div>
        </div>
        @endif
    </div>

    <div class="signatures">
        <div class="signature-box">
            <div class="signature-title">Người lập phiếu</div>
            <div class="signature-name">{{ $payment->creator->full_name ?? '' }}</div>
        </div>
        <div class="signature-box">
            <div class="signature-title">Kế toán</div>
            <div class="signature-name"></div>
        </div>
        <div class="signature-box">
            <div class="signature-title">Thủ quỹ</div>
            <div class="signature-name"></div>
        </div>
    </div>

    <div class="print-info no-print">
        <p>In lúc: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <script>
        // Auto print when page loads
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
