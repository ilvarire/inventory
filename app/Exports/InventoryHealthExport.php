<?php

namespace App\Exports;

use App\Models\RawMaterial;
use App\Services\InventoryService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InventoryHealthExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $inventoryService;

    public function __construct()
    {
        $this->inventoryService = app(InventoryService::class);
    }

    public function collection()
    {
        return RawMaterial::with('preferredSupplier')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Category',
            'Unit',
            'Current Stock',
            'Min Quantity',
            'Reorder Quantity',
            'Status',
            'Preferred Supplier',
        ];
    }

    public function map($material): array
    {
        $currentStock = $this->inventoryService->getStockBalance($material->id);

        $status = 'OK';
        if ($currentStock <= 0) {
            $status = 'OUT OF STOCK';
        } elseif ($currentStock <= $material->min_quantity) {
            $status = 'LOW STOCK';
        } elseif ($currentStock >= $material->reorder_quantity * 2) {
            $status = 'OVERSTOCKED';
        }

        return [
            $material->id,
            $material->name,
            $material->category,
            $material->unit,
            number_format($currentStock, 2),
            number_format($material->min_quantity, 2),
            number_format($material->reorder_quantity, 2),
            $status,
            $material->preferredSupplier->name ?? 'N/A',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        return 'Inventory Health';
    }
}
