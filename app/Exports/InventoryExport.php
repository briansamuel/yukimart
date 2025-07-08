<?php

namespace App\Exports;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Warehouse;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class InventoryExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * Get the data collection
     */
    public function collection()
    {
        $query = Inventory::with(['product', 'warehouse'])
            ->join('products', 'inventories.product_id', '=', 'products.id')
            ->join('warehouses', 'inventories.warehouse_id', '=', 'warehouses.id')
            ->select([
                'inventories.*',
                'products.product_name',
                'products.sku',
                'products.barcode',
                'products.cost_price',
                'products.sale_price',
                'products.reorder_point',
                'products.product_category',
                'warehouses.name as warehouse_name',
                'warehouses.code as warehouse_code'
            ]);

        // Apply filters
        if (!empty($this->filters['warehouse_id'])) {
            $query->where('inventories.warehouse_id', $this->filters['warehouse_id']);
        }

        if (!empty($this->filters['product_category'])) {
            $query->where('products.product_category', $this->filters['product_category']);
        }

        if (!empty($this->filters['low_stock_only'])) {
            $query->whereRaw('inventories.quantity <= products.reorder_point');
        }

        if (!empty($this->filters['out_of_stock_only'])) {
            $query->where('inventories.quantity', '<=', 0);
        }

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('products.product_name', 'like', "%{$search}%")
                  ->orWhere('products.sku', 'like', "%{$search}%")
                  ->orWhere('warehouses.name', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('products.product_name')
                    ->orderBy('warehouses.name')
                    ->get();
    }

    /**
     * Define the headings
     */
    public function headings(): array
    {
        return [
            'STT',
            'Mã SKU',
            'Tên sản phẩm',
            'Mã vạch',
            'Danh mục',
            'Mã kho',
            'Tên kho',
            'Tồn kho hiện tại',
            'Định mức tối thiểu',
            'Trạng thái tồn kho',
            'Giá vốn',
            'Giá bán',
            'Giá trị tồn kho',
            'Cập nhật lần cuối'
        ];
    }

    /**
     * Map the data for each row
     */
    public function map($inventory): array
    {
        static $index = 0;
        $index++;

        // Determine stock status
        $stockStatus = 'Đủ hàng';
        if ($inventory->quantity <= 0) {
            $stockStatus = 'Hết hàng';
        } elseif ($inventory->quantity <= $inventory->reorder_point) {
            $stockStatus = 'Sắp hết';
        }

        // Calculate inventory value
        $inventoryValue = $inventory->quantity * $inventory->cost_price;

        return [
            $index,
            $inventory->sku,
            $inventory->product_name,
            $inventory->barcode ?? '',
            $inventory->product_category ?? '',
            $inventory->warehouse_code,
            $inventory->warehouse_name,
            $inventory->quantity,
            $inventory->reorder_point,
            $stockStatus,
            number_format($inventory->cost_price, 0, ',', '.'),
            number_format($inventory->sale_price, 0, ',', '.'),
            number_format($inventoryValue, 0, ',', '.'),
            $inventory->updated_at ? $inventory->updated_at->format('d/m/Y H:i') : ''
        ];
    }

    /**
     * Define column widths
     */
    public function columnWidths(): array
    {
        return [
            'A' => 5,   // STT
            'B' => 15,  // Mã SKU
            'C' => 30,  // Tên sản phẩm
            'D' => 15,  // Mã vạch
            'E' => 20,  // Danh mục
            'F' => 10,  // Mã kho
            'G' => 20,  // Tên kho
            'H' => 12,  // Tồn kho
            'I' => 12,  // Định mức
            'J' => 15,  // Trạng thái
            'K' => 15,  // Giá vốn
            'L' => 15,  // Giá bán
            'M' => 18,  // Giá trị tồn kho
            'N' => 18,  // Cập nhật
        ];
    }

    /**
     * Apply styles to the worksheet
     */
    public function styles(Worksheet $sheet)
    {
        // Header row styling
        $sheet->getStyle('A1:N1')->applyFromArray([
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

        // Data rows styling
        $lastRow = $sheet->getHighestRow();
        if ($lastRow > 1) {
            $sheet->getStyle("A2:N{$lastRow}")->applyFromArray([
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
            $sheet->getStyle("A2:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // STT
            $sheet->getStyle("F2:F{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Mã kho
            $sheet->getStyle("H2:I{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Tồn kho, Định mức
            $sheet->getStyle("J2:J{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Trạng thái

            // Right align numeric columns
            $sheet->getStyle("K2:M{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT); // Giá cả

            // Apply conditional formatting for stock status
            for ($row = 2; $row <= $lastRow; $row++) {
                $stockStatus = $sheet->getCell("J{$row}")->getValue();
                
                if ($stockStatus === 'Hết hàng') {
                    $sheet->getStyle("J{$row}")->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'FFE6E6'],
                        ],
                        'font' => [
                            'color' => ['rgb' => 'CC0000'],
                            'bold' => true,
                        ],
                    ]);
                } elseif ($stockStatus === 'Sắp hết') {
                    $sheet->getStyle("J{$row}")->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'FFF2CC'],
                        ],
                        'font' => [
                            'color' => ['rgb' => 'CC6600'],
                            'bold' => true,
                        ],
                    ]);
                } else {
                    $sheet->getStyle("J{$row}")->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'E6F3E6'],
                        ],
                        'font' => [
                            'color' => ['rgb' => '006600'],
                            'bold' => true,
                        ],
                    ]);
                }
            }
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
        return 'Báo cáo tồn kho';
    }
}
