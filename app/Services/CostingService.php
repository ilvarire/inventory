<?php

namespace App\Services;

use App\Models\{
    ProductionLog,
    ProductionMaterial,
    ProcurementItem,
    PreparedInventory,
    Recipe,
    Sale,
    SaleItem,
    Expense,
    WasteLog
};
use Illuminate\Support\Facades\DB;

class CostingService
{
    /**
     * Get the latest procurement unit cost for each raw material.
     * Returns an associative array: [raw_material_id => unit_cost]
     */
    public function getLatestProcurementPrices(): array
    {
        $latestPrices = [];

        $items = ProcurementItem::select('raw_material_id', 'unit_cost', 'created_at')
            ->whereHas('procurement', function ($q) {
                $q->where('status', 'received');
            })
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($items as $item) {
            if (!isset($latestPrices[$item->raw_material_id])) {
                $latestPrices[$item->raw_material_id] = (float) $item->unit_cost;
            }
        }

        return $latestPrices;
    }

    /**
     * Get the cost per unit for a recipe using latest procurement prices.
     * = sum of (ingredient.quantity_required × latest procurement unit_cost)
     */
    public function getRecipeCostPerUnit(int $recipeId, ?array $latestPrices = null): float
    {
        if ($latestPrices === null) {
            $latestPrices = $this->getLatestProcurementPrices();
        }

        $recipe = Recipe::with('items')->find($recipeId);
        if (!$recipe)
            return 0;

        $costPerUnit = 0;
        foreach ($recipe->items as $item) {
            $unitCost = $latestPrices[$item->raw_material_id] ?? 0;
            $costPerUnit += $item->quantity_required * $unitCost;
        }

        return $costPerUnit;
    }

    /**
     * Calculate recipe-based COGS for a collection of sales.
     * For each sold item: traces to recipe → sums ingredient costs at latest procurement prices.
     * This gives the true "cost of raw materials used" for what was sold.
     */
    public function getRecipeBasedCOGS($sales, ?array $latestPrices = null): float
    {
        if ($latestPrices === null) {
            $latestPrices = $this->getLatestProcurementPrices();
        }

        $recipeCostCache = [];
        $materialCosts = 0;

        foreach ($sales as $sale) {
            $items = $sale->relationLoaded('items') ? $sale->items : $sale->items()->get();

            foreach ($items as $saleItem) {
                // Priority 1: Use the snapshot cost_price recorded at the time of sale
                if ($saleItem->cost_price > 0) {
                    $materialCosts += $saleItem->cost_price * $saleItem->quantity;
                    continue;
                }

                // Priority 2: Fallback to Recipe-based calculation (for legacy data)
                $preparedInventory = $saleItem->relationLoaded('preparedInventory')
                    ? $saleItem->preparedInventory
                    : $saleItem->preparedInventory()->first();

                if (!$preparedInventory || !$preparedInventory->recipe_id) {
                    continue;
                }

                $recipeId = $preparedInventory->recipe_id;

                if (!isset($recipeCostCache[$recipeId])) {
                    $recipeCostCache[$recipeId] = $this->getRecipeCostPerUnit($recipeId, $latestPrices);
                }

                $materialCosts += $recipeCostCache[$recipeId] * $saleItem->quantity;
            }
        }

        return $materialCosts;
    }

    /**
     * Get total production cost for a production log
     */
    public function getProductionCost(int $productionLogId): float
    {
        return ProductionMaterial::where('production_log_id', $productionLogId)
            ->sum(DB::raw('quantity_used * unit_cost'));
    }

    /**
     * Get cost per unit (plate/item) from production materials
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
     * Get profit for a single sale (using recipe-based COGS)
     */
    public function getSaleProfit(int $saleId): float
    {
        $sale = Sale::with(['items.preparedInventory'])->findOrFail($saleId);

        $revenue = $sale->items->sum(function ($item) {
            return $item->quantity * $item->unit_price;
        });

        $cost = $this->getRecipeBasedCOGS(collect([$sale]));

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
            ->with(['items.preparedInventory'])
            ->get();

        $revenue = $sales->sum('total_amount');
        $materialCost = $this->getRecipeBasedCOGS($sales);

        $expenses = Expense::where('section_id', $sectionId)
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->sum('amount');

        $wasteCost = WasteLog::where('section_id', $sectionId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('cost_amount');

        return $revenue - $materialCost - $expenses - $wasteCost;
    }

    /**
     * Get total business profit within date range
     */
    public function getBusinessProfit(
        string $startDate,
        string $endDate
    ): float {
        $sales = Sale::whereBetween('sale_date', [$startDate, $endDate])
            ->with(['items.preparedInventory'])
            ->get();

        $revenue = $sales->sum('total_amount');
        $materialCost = $this->getRecipeBasedCOGS($sales);

        $expenses = Expense::whereBetween('expense_date', [$startDate, $endDate])
            ->sum('amount');

        $wasteCost = WasteLog::whereBetween('created_at', [$startDate, $endDate])
            ->sum('cost_amount');

        return $revenue - $materialCost - $expenses - $wasteCost;
    }

    /**
     * Get detailed P&L for a section
     */
    public function getSectionPnL(
        int $sectionId,
        string $startDate,
        string $endDate
    ): array {
        $sales = Sale::where('section_id', $sectionId)
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->with(['items.preparedInventory'])
            ->get();

        $revenue = $sales->sum('total_amount');
        $costOfSales = $this->getRecipeBasedCOGS($sales);

        return [
            'revenue' => $revenue,
            'cost_of_sales' => $costOfSales,

            'expenses' => Expense::where('section_id', $sectionId)
                ->whereBetween('expense_date', [$startDate, $endDate])
                ->sum('amount'),

            'waste' => WasteLog::where('section_id', $sectionId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('cost_amount'),
        ];
    }
}
