<?php

namespace App\Services;

use App\Models\{
    Section,
    Sale,
    SaleItem,
    RawMaterial,
    ProcurementItem,
    PreparedInventory,
    WasteLog,
    Expense,
    ProductionLog
};
use Illuminate\Support\Facades\DB;

class ReportingService
{
    protected CostingService $costing;

    public function __construct(CostingService $costing)
    {
        $this->costing = $costing;
    }

    /**
     * Admin dashboard summary
     */
    public function getAdminDashboard(
        string $startDate,
        string $endDate
    ): array {
        return [
            'revenue' => SaleItem::whereBetween('created_at', [$startDate, $endDate])
                ->sum(DB::raw('quantity * unit_price')),

            'profit' => $this->costing->getBusinessProfit(
                $startDate,
                $endDate
            ),

            'expenses' => Expense::whereBetween('expense_date', [$startDate, $endDate])
                ->sum('amount'),

            'waste_cost' => WasteLog::whereBetween('created_at', [$startDate, $endDate])
                ->sum('cost_amount'),

            'low_stock_items' => $this->getLowStockItems(),

            'top_selling_items' => $this->getTopSellingItems($startDate, $endDate),
        ];
    }

    /**
     * Section dashboard
     */
    public function getSectionDashboard(
        int $sectionId,
        string $startDate,
        string $endDate
    ): array {
        return [
            'production_batches' => ProductionLog::where('section_id', $sectionId)
                ->whereBetween('production_date', [$startDate, $endDate])
                ->count(),

            'profit' => $this->costing->getSectionProfit(
                $sectionId,
                $startDate,
                $endDate
            ),

            'waste_cost' => WasteLog::where('section_id', $sectionId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('cost_amount'),

            'expenses' => Expense::where('section_id', $sectionId)
                ->whereBetween('expense_date', [$startDate, $endDate])
                ->sum('amount'),

            'prepared_inventory' => PreparedInventory::where('section_id', $sectionId)
                ->get(),
        ];
    }

    /**
     * Inventory health report
     */
    public function getInventoryHealth(): array
    {
        return RawMaterial::with('procurementItems')->get()->map(function ($item) {
            $available = $item->procurementItems->sum(
                fn($batch) => $batch->quantity - $batch->received_quantity
            );

            return [
                'raw_material' => $item->name,
                'available_quantity' => $available,
                'minimum_required' => $item->minimum_quantity,
                'status' => $available <= $item->minimum_quantity
                    ? 'reorder_required'
                    : 'ok'
            ];
        })->toArray();
    }

    /**
     * Get low stock items
     */
    protected function getLowStockItems(): array
    {
        return collect($this->getInventoryHealth())
            ->where('status', 'reorder_required')
            ->values()
            ->toArray();
    }



    /**
     * Waste summary
     */
    public function getWasteReport(
        string $startDate,
        string $endDate
    ): array {
        $query = WasteLog::with(['section', 'rawMaterial', 'preparedItem'])
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->where('status', 'approved'); // Only count approved waste

        $wasteLogs = $query->get();

        // Totals
        $totalCost = $wasteLogs->sum('cost_amount');
        $totalCount = $wasteLogs->count();
        $averageCost = $totalCount > 0 ? $totalCost / $totalCount : 0;

        // Group by Reason
        $byReason = $wasteLogs->groupBy('reason')->map(function ($items, $reason) {
            return [
                'reason' => $reason,
                'cost' => $items->sum('cost_amount'),
                'count' => $items->count(),
            ];
        })->values()->toArray();

        // Group by Section
        $bySection = $wasteLogs->groupBy(fn($item) => $item->section->name ?? 'Unassigned')->map(function ($items, $sectionName) {
            return [
                'section_name' => $sectionName,
                'cost' => $items->sum('cost_amount'),
                'count' => $items->count(),
            ];
        })->values()->toArray();

        // Top Wasted Materials/Items
        $topMaterials = $wasteLogs->groupBy(function ($item) {
            if ($item->raw_material_id) {
                return 'raw_' . $item->raw_material_id;
            }
            return 'prep_' . $item->production_log_id;
        })->map(function ($items) {
            $first = $items->first();

            $name = 'Unknown Item';
            $unit = '';

            if ($first->raw_material_id) {
                $name = $first->rawMaterial->name ?? 'Unknown Material';
                $unit = $first->rawMaterial->unit ?? '';
            } elseif ($first->production_log_id) {
                $name = $first->preparedItem->item_name ?? 'Unknown Prepared Item';
                $unit = $first->preparedItem->unit ?? '';
            }

            return [
                'material_id' => $first->raw_material_id ?? $first->production_log_id,
                'material_name' => $name,
                'unit' => $unit,
                'total_quantity' => $items->sum('quantity'),
                'total_cost' => $items->sum('cost_amount'),
                'incident_count' => $items->count(),
            ];
        })->sortByDesc('total_cost')->take(5)->values()->toArray();

        return [
            'total_waste_cost' => $totalCost,
            'total_waste_count' => $totalCount,
            'average_waste_cost' => $averageCost,
            'by_reason' => $byReason,
            'by_section' => $bySection,
            'top_wasted_materials' => $topMaterials,
        ];
    }

    /**
     * Top selling items
     */
    public function getTopSellingItems(
        string $startDate,
        string $endDate,
        int $limit = 10
    ): array {
        return SaleItem::select(
            'item_name',
            DB::raw('SUM(quantity) as total_sold'),
            DB::raw('SUM(quantity * unit_price) as total_revenue'),
            DB::raw('SUM(quantity * cost_price) as total_cost')
        )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('item_name')
            ->orderByDesc('total_sold')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'item_name' => $item->item_name,
                    'total_sold' => (float) $item->total_sold,
                    'total_revenue' => (float) $item->total_revenue,
                    'total_profit' => (float) ($item->total_revenue - $item->total_cost),
                    'margin' => $item->total_revenue > 0
                        ? round((($item->total_revenue - $item->total_cost) / $item->total_revenue) * 100, 1)
                        : 0
                ];
            })
            ->toArray();
    }

    /**
     * Expense breakdown
     */
    public function getExpenseReport(
        string $startDate,
        string $endDate
    ): array {
        $query = Expense::with(['section', 'recordedBy'])
            ->whereDate('expense_date', '>=', $startDate)
            ->whereDate('expense_date', '<=', $endDate)
            ->where('status', 'approved');

        $expenses = $query->get();
        $totalExpenses = $expenses->sum('amount');

        // Group by Category
        $byCategory = $expenses->groupBy('category')->map(function ($items, $category) {
            return [
                'category' => $category,
                'amount' => $items->sum('amount'),
                'count' => $items->count(),
                'percentage' => 0 // To be calculated
            ];
        })->values();

        // Calculate percentages
        $byCategory = $byCategory->map(function ($cat) use ($totalExpenses) {
            $cat['percentage'] = $totalExpenses > 0 ? round(($cat['amount'] / $totalExpenses) * 100, 1) : 0;
            return $cat;
        })->sortByDesc('amount')->values()->toArray();

        // Group by Section
        $bySection = $expenses->groupBy(fn($e) => $e->section->name ?? 'General')->map(function ($items, $sectionName) {
            return [
                'section_name' => $sectionName,
                'amount' => $items->sum('amount'),
                'count' => $items->count()
            ];
        })->values()->sortByDesc('amount')->toArray();

        // Recent Expenses (Top 10)
        $recentExpenses = $expenses->sortByDesc('expense_date')->take(10)->values()->map(function ($expense) {
            return [
                'id' => $expense->id,
                'date' => $expense->expense_date,
                'category' => $expense->category,
                'description' => $expense->description,
                'amount' => $expense->amount,
                'section' => $expense->section->name ?? 'General',
                'recorded_by' => $expense->recordedBy->name ?? 'Unknown'
            ];
        })->toArray();

        return [
            'total_expenses' => $totalExpenses,
            'expense_count' => $expenses->count(),
            'by_category' => $byCategory,
            'by_section' => $bySection,
            'recent_expenses' => $recentExpenses
        ];
    }
}

//Typical endpoints
// GET /api/reports/admin-dashboard
// GET /api/reports/section-dashboard/{sectionId}
// GET /api/reports/inventory-health
// GET /api/reports/waste
// GET /api/reports/expenses
