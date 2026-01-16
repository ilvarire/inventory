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
     * Top selling items
     */
    protected function getTopSellingItems(
        string $startDate,
        string $endDate,
        int $limit = 5
    ): array {
        return SaleItem::select(
            'item_name',
            DB::raw('SUM(quantity) as total_sold')
        )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('item_name')
            ->orderByDesc('total_sold')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Waste summary
     */
    public function getWasteReport(
        string $startDate,
        string $endDate
    ): array {
        return WasteLog::select(
            'reason',
            DB::raw('SUM(cost_amount) as total_cost')
        )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('reason')
            ->get()
            ->toArray();
    }

    /**
     * Expense breakdown
     */
    public function getExpenseReport(
        string $startDate,
        string $endDate
    ): array {
        return [
            'general' => Expense::whereNull('section_id')
                ->whereBetween('expense_date', [$startDate, $endDate])
                ->sum('amount'),

            'by_section' => Expense::select(
                'section_id',
                DB::raw('SUM(amount) as total')
            )
                ->whereNotNull('section_id')
                ->whereBetween('expense_date', [$startDate, $endDate])
                ->groupBy('section_id')
                ->get()
                ->toArray(),
        ];
    }
}

//Typical endpoints
// GET /api/reports/admin-dashboard
// GET /api/reports/section-dashboard/{sectionId}
// GET /api/reports/inventory-health
// GET /api/reports/waste
// GET /api/reports/expenses
