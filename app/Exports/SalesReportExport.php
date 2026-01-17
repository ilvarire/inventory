<?php

namespace App\Exports;

use App\Models\Sale;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $startDate;
    protected $endDate;
    protected $sectionId;

    public function __construct($startDate, $endDate, $sectionId = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->sectionId = $sectionId;
    }

    public function collection()
    {
        $query = Sale::with(['section', 'items.preparedInventory', 'user'])
            ->whereBetween('sale_date', [$this->startDate, $this->endDate]);

        if ($this->sectionId) {
            $query->where('section_id', $this->sectionId);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Sale ID',
            'Date',
            'Section',
            'Payment Method',
            'Items Count',
            'Total Revenue',
            'Total Cost',
            'Profit',
            'Profit Margin %',
            'Recorded By',
        ];
    }

    public function map($sale): array
    {
        $revenue = $sale->items->sum(fn($item) => $item->quantity * $item->unit_price);
        $cost = $sale->items->sum(fn($item) => $item->quantity * $item->cost_price);
        $profit = $revenue - $cost;
        $margin = $revenue > 0 ? ($profit / $revenue) * 100 : 0;

        return [
            $sale->id,
            $sale->sale_date,
            $sale->section->name ?? 'N/A',
            $sale->payment_method,
            $sale->items->count(),
            number_format($revenue, 2),
            number_format($cost, 2),
            number_format($profit, 2),
            number_format($margin, 2),
            $sale->user->name ?? 'N/A',
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
        return 'Sales Report';
    }
}
