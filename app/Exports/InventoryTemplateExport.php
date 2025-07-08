<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class InventoryTemplateExport implements WithMultipleSheets
{
    protected $sampleData;

    public function __construct($sampleData = [])
    {
        $this->sampleData = $sampleData;
    }

    /**
     * Return multiple sheets
     */
    public function sheets(): array
    {
        return [
            'Template' => new InventoryTemplateSheet($this->sampleData),
            'Hướng dẫn' => new InventoryInstructionSheet(),
        ];
    }
}

class InventoryTemplateSheet implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    protected $sampleData;

    public function __construct($sampleData = [])
    {
        $this->sampleData = $sampleData;
    }

    /**
     * Return the data array
     */
    public function array(): array
    {
        if (empty($this->sampleData)) {
            return [
                [
                    'SAMPLE001',
                    'Sản phẩm mẫu 1',
                    'WH001',
                    'Kho chính',
                    100,
                    50,
                    'import',
                    25000,
                    1250000,
                    'Nhập hàng từ nhà cung cấp',
                    'PO-2024-001',
                    'Ghi chú bổ sung'
                ],
                [
                    'SAMPLE002',
                    'Sản phẩm mẫu 2',
                    'WH001',
                    'Kho chính',
                    75,
                    -25,
                    'export',
                    30000,
                    -750000,
                    'Xuất hàng bán lẻ',
                    'SO-2024-001',
                    'Xuất cho đơn hàng #12345'
                ]
            ];
        }

        return array_map(function($item) {
            return [
                $item['product_sku'],
                $item['product_name'],
                $item['warehouse_code'],
                $item['warehouse_name'],
                $item['current_quantity'],
                $item['adjustment_quantity'],
                $item['adjustment_type'],
                $item['unit_cost'],
                $item['total_cost'],
                $item['reason'],
                $item['reference_number'],
                $item['notes']
            ];
        }, $this->sampleData);
    }

    /**
     * Define the headings
     */
    public function headings(): array
    {
        return [
            'Mã SKU (*)',
            'Tên sản phẩm',
            'Mã kho (*)',
            'Tên kho',
            'Tồn kho hiện tại',
            'Số lượng điều chỉnh (*)',
            'Loại điều chỉnh (*)',
            'Đơn giá',
            'Tổng tiền',
            'Lý do',
            'Số tham chiếu',
            'Ghi chú'
        ];
    }

    /**
     * Define column widths
     */
    public function columnWidths(): array
    {
        return [
            'A' => 15,  // Mã SKU
            'B' => 30,  // Tên sản phẩm
            'C' => 12,  // Mã kho
            'D' => 20,  // Tên kho
            'E' => 15,  // Tồn kho hiện tại
            'F' => 18,  // Số lượng điều chỉnh
            'G' => 15,  // Loại điều chỉnh
            'H' => 15,  // Đơn giá
            'I' => 15,  // Tổng tiền
            'J' => 25,  // Lý do
            'K' => 15,  // Số tham chiếu
            'L' => 20,  // Ghi chú
        ];
    }

    /**
     * Apply styles to the worksheet
     */
    public function styles(Worksheet $sheet)
    {
        // Header row styling
        $sheet->getStyle('A1:L1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Required fields styling (columns with *)
        $requiredColumns = ['A1', 'C1', 'F1', 'G1'];
        foreach ($requiredColumns as $cell) {
            $sheet->getStyle($cell)->applyFromArray([
                'font' => [
                    'color' => ['rgb' => 'FFFF00'], // Yellow text for required fields
                ],
            ]);
        }

        // Data rows styling
        $lastRow = $sheet->getHighestRow();
        if ($lastRow > 1) {
            $sheet->getStyle("A2:L{$lastRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CCCCCC'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);

            // Center align specific columns
            $sheet->getStyle("C2:C{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Mã kho
            $sheet->getStyle("G2:G{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Loại điều chỉnh

            // Right align numeric columns
            $sheet->getStyle("E2:F{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT); // Quantities
            $sheet->getStyle("H2:I{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT); // Prices
        }

        // Auto-fit row heights
        $sheet->getDefaultRowDimension()->setRowHeight(20);
        $sheet->getRowDimension(1)->setRowHeight(25);

        // Freeze header row
        $sheet->freezePane('A2');

        return [];
    }

    /**
     * Set the worksheet title
     */
    public function title(): string
    {
        return 'Template';
    }
}

class InventoryInstructionSheet implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    /**
     * Return the instruction data
     */
    public function array(): array
    {
        return [
            ['Cột', 'Tên trường', 'Bắt buộc', 'Mô tả', 'Ví dụ'],
            ['A', 'Mã SKU', 'Có', 'Mã SKU của sản phẩm (phải tồn tại trong hệ thống)', 'PROD001'],
            ['B', 'Tên sản phẩm', 'Không', 'Tên sản phẩm (chỉ để tham khảo)', 'Áo thun nam'],
            ['C', 'Mã kho', 'Có', 'Mã kho (phải tồn tại trong hệ thống)', 'WH001'],
            ['D', 'Tên kho', 'Không', 'Tên kho (chỉ để tham khảo)', 'Kho chính'],
            ['E', 'Tồn kho hiện tại', 'Không', 'Số lượng tồn kho hiện tại (chỉ để tham khảo)', '100'],
            ['F', 'Số lượng điều chỉnh', 'Có', 'Số lượng cần điều chỉnh (dương/âm)', '50 hoặc -25'],
            ['G', 'Loại điều chỉnh', 'Có', 'import (nhập), export (xuất), adjustment (điều chỉnh)', 'import'],
            ['H', 'Đơn giá', 'Không', 'Đơn giá của sản phẩm', '25000'],
            ['I', 'Tổng tiền', 'Không', 'Tổng tiền = Số lượng × Đơn giá', '1250000'],
            ['J', 'Lý do', 'Không', 'Lý do điều chỉnh tồn kho', 'Nhập hàng từ NCC'],
            ['K', 'Số tham chiếu', 'Không', 'Số phiếu nhập/xuất hoặc đơn hàng', 'PO-2024-001'],
            ['L', 'Ghi chú', 'Không', 'Ghi chú bổ sung', 'Hàng mới về'],
            [],
            ['LƯU Ý QUAN TRỌNG:'],
            ['1. Các cột có dấu (*) là bắt buộc phải điền'],
            ['2. Mã SKU và Mã kho phải tồn tại trong hệ thống'],
            ['3. Loại điều chỉnh chỉ nhận: import, export, adjustment'],
            ['4. Số lượng điều chỉnh:'],
            ['   - import: số dương (tăng tồn kho)'],
            ['   - export: số dương (giảm tồn kho)'],
            ['   - adjustment: số tuyệt đối (đặt tồn kho = số này)'],
            ['5. Không được để tồn kho âm (trừ khi được cấu hình)'],
            ['6. File hỗ trợ định dạng: .xlsx, .xls, .csv'],
            ['7. Tối đa 10MB mỗi file'],
        ];
    }

    /**
     * Define the headings (empty for instruction sheet)
     */
    public function headings(): array
    {
        return [];
    }

    /**
     * Define column widths
     */
    public function columnWidths(): array
    {
        return [
            'A' => 8,   // Cột
            'B' => 20,  // Tên trường
            'C' => 12,  // Bắt buộc
            'D' => 50,  // Mô tả
            'E' => 20,  // Ví dụ
        ];
    }

    /**
     * Apply styles to the worksheet
     */
    public function styles(Worksheet $sheet)
    {
        // Header row styling
        $sheet->getStyle('A1:E1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Data rows styling (rows 2-13)
        $sheet->getStyle('A2:E13')->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Important notes styling
        $sheet->getStyle('A15')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'CC0000'],
                'size' => 12,
            ],
        ]);

        $sheet->getStyle('A16:A23')->applyFromArray([
            'font' => [
                'color' => ['rgb' => '333333'],
            ],
            'alignment' => [
                'wrapText' => true,
            ],
        ]);

        // Auto-fit row heights
        $sheet->getDefaultRowDimension()->setRowHeight(20);

        return [];
    }

    /**
     * Set the worksheet title
     */
    public function title(): string
    {
        return 'Hướng dẫn';
    }
}
