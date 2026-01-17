<?php

namespace App\Exports;

use App\Services\CostingService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProfitLossExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    protected $startDate;
    protected $endDate;
    protected $sectionId;
    protected $costingService;

    public function __construct($startDate, $endDate, $sectionId = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->sectionId = $sectionId;
        $this->costingService = app(CostingService::class);
    }

    public function collection()
    {
        $data = $this->sectionId
            ? $this->costingService->getSectionPnL($this->sectionId, $this->startDate, $this->endDate)
            : $this->getBusinessPnL();

        $grossProfit = $data['revenue'] - $data['cost_of_sales'];
        $netProfit = $grossProfit - $data['expenses'] - $data['waste'];
        $grossMargin = $data['revenue'] > 0 ? ($grossProfit / $data['revenue']) * 100 : 0;
        $netMargin = $data['revenue'] > 0 ? ($netProfit / $data['revenue']) * 100 : 0;

        return new Collection([
            ['REVENUE', ''],
            ['Total Sales Revenue', number_format($data['revenue'], 2)],
            ['', ''],
            ['COST OF SALES', ''],
            ['Cost of Goods Sold', number_format($data['cost_of_sales'], 2)],
            ['', ''],
            ['GROSS PROFIT', number_format($grossProfit, 2)],
            ['Gross Profit Margin', number_format($grossMargin, 2) . '%'],
            ['', ''],
            ['OPERATING EXPENSES', ''],
            ['Total Expenses', number_format($data['expenses'], 2)],
            ['Waste Cost', number_format($data['waste'], 2)],
            ['Total Operating Expenses', number_format($data['expenses'] + $data['waste'], 2)],
            ['', ''],
            ['NET PROFIT', number_format($netProfit, 2)],
            ['Net Profit Margin', number_format($netMargin, 2) . '%'],
        ]);
    }

    protected function getBusinessPnL()
    {
        $profit = $this->costingService->getBusinessProfit($this->startDate, $this->endDate);

        // Get detailed breakdown
        $revenue = \App\Models\SaleItem::whereHas('sale', function ($q) {
            $q->whereBetween('sale_date', [$this->startDate, $this->endDate]);
        })->sum(\DB::raw('quantity * unit_price'));

        $costOfSales = \App\Models\SaleItem::whereHas('sale', function ($q) {
            $q->whereBetween('sale_date', [$this->startDate, $this->endDate]);
        })->sum(\DB::raw('quantity * cost_price'));

        $expenses = \App\Models\Expense::whereBetween('expense_date', [$this->startDate, $this->endDate])
            ->sum('amount');

        $waste = \App\Models\WasteLog::whereBetween('created_at', [$this->startDate, $this->endDate])
            ->sum('cost_amount');

        return [
            'revenue' => $revenue,
            'cost_of_sales' => $costOfSales,
            'expenses' => $expenses,
            'waste' => $waste,
        ];
    }

    public function headings(): array
    {
        return ['Description', 'Amount'];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            2 => ['font' => ['bold' => true]],
            5 => ['font' => ['bold' => true]],
            8 => ['font' => ['bold' => true, 'size' => 12]],
            11 => ['font' => ['bold' => true]],
            16 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }

    public function title(): string
    {
        return 'Profit & Loss';
    }
}
