<?php

namespace App\Exports;

use App\Models\WasteLog;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class WasteReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        return WasteLog::with(['section', 'rawMaterial', 'productionLog', 'reportedBy', 'approvedBy'])
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->where('approved', true)
            ->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Date',
            'Section',
            'Type',
            'Item',
            'Quantity',
            'Reason',
            'Cost Amount',
            'Reported By',
            'Approved By',
            'Notes',
        ];
    }

    public function map($waste): array
    {
        $item = $waste->waste_type === 'raw_material'
            ? ($waste->rawMaterial->name ?? 'N/A')
            : ($waste->productionLog->recipeVersion->recipe->name ?? 'N/A');

        return [
            $waste->id,
            $waste->created_at->format('Y-m-d'),
            $waste->section->name ?? 'N/A',
            ucfirst(str_replace('_', ' ', $waste->waste_type)),
            $item,
            $waste->quantity,
            ucfirst(str_replace('_', ' ', $waste->reason)),
            number_format($waste->cost_amount, 2),
            $waste->reportedBy->name ?? 'N/A',
            $waste->approvedBy->name ?? 'N/A',
            $waste->notes ?? '',
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
        return 'Waste Report';
    }
}
