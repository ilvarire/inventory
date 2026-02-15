<?php

namespace App\Services;

use App\Models\{
    ProductionLog,
    ProductionMaterial,
    Sale,
    SaleItem,
    Expense,
    WasteLog
};
use Illuminate\Support\Facades\DB;

class CostingService
{
    /**
     * Get total production cost for a production log
     */
    public function getProductionCost(int $productionLogId): float
    {
        return ProductionMaterial::where('production_log_id', $productionLogId)
            ->sum(DB::raw('quantity_used * unit_cost'));
    }

    /**
     * Get cost per unit (plate/item)
     */
    public function getCostPerUnit(int $productionLogId): float
    {
        $production = ProductionLog::findOrFail($productionLogId);

        if ($production->quantity_produced === 0) {
            return 0;
        }

        return $this->getProductionCost($productionLogId)
            / $production->quantity_produced;
    }

    /**
     * Get profit for a single sale
     */
    public function getSaleProfit(int $saleId): float
    {
        $sale = Sale::with('items')->findOrFail($saleId);

        $revenue = $sale->items->sum(function ($item) {
            return $item->quantity * $item->unit_price;
        });

        $cost = $sale->items->sum(function ($item) {
            return $item->quantity * $item->cost_price;
        });

        return $revenue - $cost;
    }

    /**
     * Get profit for a section within date range
     */
    public function getSectionProfit(
        int $sectionId,
        string $startDate,
        string $endDate
    ): float {
        $sales = Sale::where('section_id', $sectionId)
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->pluck('id');

        $salesProfit = $sales->sum(
            fn($saleId) =>
            $this->getSaleProfit($saleId)
        );

        $expenses = Expense::where('section_id', $sectionId)
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->sum('amount');

        $wasteCost = WasteLog::where('section_id', $sectionId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('cost_amount');

        return $salesProfit - $expenses - $wasteCost;
    }

    /**
     * Get total business profit within date range
     */
    public function getBusinessProfit(
        string $startDate,
        string $endDate
    ): float {
        // Revenue from sales
        $revenue = Sale::whereBetween('sale_date', [$startDate, $endDate])
            ->sum('total_amount');

        // Actual procurement costs (not inflated cost_price from sale_items)
        $procurementCosts = \App\Models\ProcurementItem::whereHas('procurement', function ($q) use ($startDate, $endDate) {
            $q->where('status', 'received')
                ->whereBetween('purchase_date', [$startDate, $endDate]);
        })->sum(\DB::raw('quantity * unit_cost'));

        $expenses = Expense::whereBetween('expense_date', [$startDate, $endDate])
            ->sum('amount');

        $wasteCost = WasteLog::whereBetween('created_at', [$startDate, $endDate])
            ->sum('cost_amount');

        return $revenue - $procurementCosts - $expenses - $wasteCost;
    }

    /**
     * Get detailed P&L for a section
     */
    public function getSectionPnL(
        int $sectionId,
        string $startDate,
        string $endDate
    ): array {
        return [
            'revenue' => SaleItem::whereHas('sale', function ($q) use ($sectionId, $startDate, $endDate) {
                $q->where('section_id', $sectionId)
                    ->whereBetween('sale_date', [$startDate, $endDate]);
            })->sum(DB::raw('quantity * unit_price')),

            'cost_of_sales' => SaleItem::whereHas('sale', function ($q) use ($sectionId, $startDate, $endDate) {
                $q->where('section_id', $sectionId)
                    ->whereBetween('sale_date', [$startDate, $endDate]);
            })->sum(DB::raw('quantity * cost_price')),

            'expenses' => Expense::where('section_id', $sectionId)
                ->whereBetween('expense_date', [$startDate, $endDate])
                ->sum('amount'),

            'waste' => WasteLog::where('section_id', $sectionId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('cost_amount'),
        ];
    }
}

//CONTROLLER
// public function sectionProfit(
//     Request $request,
//     CostingService $service
// ) {
//     return response()->json([
//         'profit' => $service->getSectionProfit(
//             $request->section_id,
//             $request->start_date,
//             $request->end_date
//         )
//     ]);
// }

