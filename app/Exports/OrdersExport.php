<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class OrdersExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $orders;

    public function __construct($orders)
    {
        $this->orders = $orders;
    }

    /**
     * Return the collection of orders to export.
     */
    public function collection()
    {
        return $this->orders;
    }

    /**
     * Define the headings for the Excel file.
     */
    public function headings(): array
    {
        return [
            'Mã đơn hàng',
            'Khách hàng',
            'Số điện thoại',
            'Email',
            'Địa chỉ',
            'Tổng tiền',
            'Đã thanh toán',
            'Còn lại',
            'Trạng thái',
            'TT Thanh toán',
            'TT Giao hàng',
            'Kênh bán',
            'Chi nhánh',
            'Người tạo',
            'Người bán',
            'Ngày tạo',
            'Ngày cập nhật',
            'Ghi chú'
        ];
    }

    /**
     * Map each order to the Excel row format.
     */
    public function map($order): array
    {
        return [
            $order->order_code ?? '',
            $order->customer_name ?? 'Khách lẻ',
            $order->customer ? $order->customer->phone : '',
            $order->customer ? $order->customer->email : '',
            $order->customer ? $order->customer->address : '',
            number_format($order->final_amount, 0, ',', '.') . ' ₫',
            number_format($order->amount_paid, 0, ',', '.') . ' ₫',
            number_format($order->final_amount - $order->amount_paid, 0, ',', '.') . ' ₫',
            $this->getStatusLabel($order->status),
            $this->getPaymentStatusLabel($order->payment_status),
            $this->getDeliveryStatusLabel($order->delivery_status),
            $order->channel ?? 'direct',
            $order->branchShop ? $order->branchShop->name : '',
            $order->creator ? $order->creator->name : '',
            $order->seller ? $order->seller->name : '',
            $order->created_at ? $order->created_at->format('d/m/Y H:i') : '',
            $order->updated_at ? $order->updated_at->format('d/m/Y H:i') : '',
            $order->notes ?? ''
        ];
    }

    /**
     * Apply styles to the Excel file.
     */
    public function styles(Worksheet $sheet)
    {
        // Header row styling
        $sheet->getStyle('A1:R1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 12
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ]);

        // Data rows styling
        $lastRow = $this->orders->count() + 1;
        $sheet->getStyle("A2:R{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC']
                ]
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);

        // Currency columns alignment
        $sheet->getStyle("F2:H{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // Auto-fit row heights
        for ($i = 1; $i <= $lastRow; $i++) {
            $sheet->getRowDimension($i)->setRowHeight(-1);
        }

        return $sheet;
    }

    /**
     * Define column widths.
     */
    public function columnWidths(): array
    {
        return [
            'A' => 20, // Mã đơn hàng
            'B' => 25, // Khách hàng
            'C' => 15, // Số điện thoại
            'D' => 25, // Email
            'E' => 30, // Địa chỉ
            'F' => 15, // Tổng tiền
            'G' => 15, // Đã thanh toán
            'H' => 15, // Còn lại
            'I' => 15, // Trạng thái
            'J' => 18, // TT Thanh toán
            'K' => 18, // TT Giao hàng
            'L' => 12, // Kênh bán
            'M' => 20, // Chi nhánh
            'N' => 20, // Người tạo
            'O' => 20, // Người bán
            'P' => 18, // Ngày tạo
            'Q' => 18, // Ngày cập nhật
            'R' => 25  // Ghi chú
        ];
    }

    /**
     * Get status label in Vietnamese.
     */
    private function getStatusLabel($status)
    {
        $statusMap = [
            'pending' => 'Chờ xử lý',
            'processing' => 'Đang xử lý',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
            'shipped' => 'Đã giao hàng',
            'delivered' => 'Đã giao',
            'failed_delivery' => 'Không giao được'
        ];

        return $statusMap[$status] ?? $status;
    }

    /**
     * Get payment status label in Vietnamese.
     */
    private function getPaymentStatusLabel($paymentStatus)
    {
        $statusMap = [
            'unpaid' => 'Chưa thanh toán',
            'partial' => 'Thanh toán một phần',
            'paid' => 'Đã thanh toán',
            'refunded' => 'Đã hoàn tiền'
        ];

        return $statusMap[$paymentStatus] ?? $paymentStatus;
    }

    /**
     * Get delivery status label in Vietnamese.
     */
    private function getDeliveryStatusLabel($deliveryStatus)
    {
        $statusMap = [
            'pending' => 'Chờ xử lý',
            'preparing' => 'Lấy hàng',
            'shipping' => 'Giao hàng',
            'delivered' => 'Giao thành công',
            'returned' => 'Chuyển hoàn',
            'return_completed' => 'Đã chuyển hoàn',
            'cancelled' => 'Đã hủy'
        ];

        return $statusMap[$deliveryStatus] ?? $deliveryStatus;
    }
}
