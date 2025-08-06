<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReturnsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $returns;

    public function __construct($returns)
    {
        $this->returns = $returns;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->returns;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Mã đơn trả',
            'Khách hàng',
            'Số điện thoại',
            'Email',
            'Tổng tiền',
            'Đã hoàn',
            'Trạng thái',
            'Phương thức thanh toán',
            'Kênh bán',
            'Ngày tạo',
            'Người tạo',
            'Chi nhánh',
            'Ghi chú'
        ];
    }

    /**
     * @param mixed $return
     * @return array
     */
    public function map($return): array
    {
        return [
            $return->return_number,
            $return->customer_id > 0 && $return->customer ? $return->customer->name : 'Khách lẻ',
            $return->customer_id > 0 && $return->customer ? $return->customer->phone : '',
            $return->customer_id > 0 && $return->customer ? $return->customer->email : '',
            number_format($return->total_amount) . ' ₫',
            number_format($return->refunded_amount ?? 0) . ' ₫',
            $this->getStatusText($return->status),
            $return->payment_method ?? 'N/A',
            $return->sale_channel ?? 'N/A',
            $return->created_at ? $return->created_at->format('d/m/Y H:i') : '',
            $return->creator ? $return->creator->full_name : 'N/A',
            $return->branchShop ? $return->branchShop->name : 'N/A',
            $return->notes ?? ''
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1 => ['font' => ['bold' => true]],
        ];
    }

    /**
     * Get status text in Vietnamese
     */
    private function getStatusText($status)
    {
        switch ($status) {
            case 'draft':
                return 'Nháp';
            case 'processing':
                return 'Đang xử lý';
            case 'completed':
                return 'Hoàn thành';
            case 'cancelled':
                return 'Đã hủy';
            case 'returned':
                return 'Đã trả';
            default:
                return ucfirst($status);
        }
    }
}
