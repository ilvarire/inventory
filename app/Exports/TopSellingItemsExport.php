<?php

namespace App\Exports;

use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TopSellingItemsExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    protected $startDate;
    protected $endDate;
    protected $limit;

    public function __construct($startDate = null, $endDate = null, $limit = 10)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->limit = $limit;
    }

    public function collection()
    {
        $query = SaleItem::with('preparedInventory.productionLog.recipeVersion.recipe')
            ->select(
                'prepared_inventory_id',
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('SUM(quantity * unit_price) as total_revenue'),
                DB::raw('SUM(quantity * cost_price) as total_cost'),
                DB::raw('SUM(quantity * (unit_price - cost_price)) as total_profit')
            )
            ->groupBy('prepared_inventory_id');

        if ($this->startDate && $this->endDate) {
            $query->whereHas('sale', function ($q) {
                $q->whereBetween('sale_date', [$this->startDate, $this->endDate]);
            });
        }

        $items = $query->orderBy('total_quantity', 'desc')
            ->limit($this->limit)
            ->get();

        return $items->map(function ($item, $index) {
            $recipeName = $item->preparedInventory->productionLog->recipeVersion->recipe->name ?? 'Unknown';
            $profitMargin = $item->total_revenue > 0
                ? (($item->total_profit / $item->total_revenue) * 100)
                : 0;

            return [
                'Rank' => $index + 1,
                'Item Name' => $recipeName,
                'Total Quantity Sold' => number_format($item->total_quantity, 0),
                'Total Revenue' => number_format($item->total_revenue, 2),
                'Total Cost' => number_format($item->total_cost, 2),
                'Total Profit' => number_format($item->total_profit, 2),
                'Profit Margin %' => number_format($profitMargin, 2),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Rank',
            'Item Name',
            'Total Quantity Sold',
            'Total Revenue',
            'Total Cost',
            'Total Profit',
            'Profit Margin %',
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
        return 'Top Selling Items';
    }
}
