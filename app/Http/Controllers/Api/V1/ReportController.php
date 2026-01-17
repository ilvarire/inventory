<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ReportingService;
use App\Services\CostingService;
use App\Services\ExportService;
use App\Services\InventoryService;
use App\Exports\SalesReportExport;
use App\Exports\ProfitLossExport;
use App\Exports\WasteReportExport;
use App\Exports\ExpenseReportExport;
use App\Exports\InventoryHealthExport;
use App\Exports\TopSellingItemsExport;
use App\Models\Sale;
use App\Models\WasteLog;
use App\Models\Expense;
use App\Models\RawMaterial;
use App\Models\Section;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    protected ReportingService $reportingService;
    protected CostingService $costingService;
    protected ExportService $exportService;
    protected InventoryService $inventoryService;

    public function __construct(
        ReportingService $reportingService,
        CostingService $costingService,
        ExportService $exportService,
        InventoryService $inventoryService
    ) {
        $this->reportingService = $reportingService;
        $this->costingService = $costingService;
        $this->exportService = $exportService;
        $this->inventoryService = $inventoryService;
    }

    /**
     * Admin/Manager dashboard.
     */
    public function dashboard(Request $request)
    {
        // Only Admin and Manager can access
        if (!auth()->user()->isAdmin() && !auth()->user()->isManager()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->toDateString());

        $dashboard = $this->reportingService->getAdminDashboard($startDate, $endDate);

        return response()->json($dashboard);
    }

    /**
     * Section-specific dashboard.
     */
    public function sectionDashboard(Request $request, int $sectionId)
    {
        // Check section access
        if (!auth()->user()->canAccessSection($sectionId)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->toDateString());

        $dashboard = $this->reportingService->getSectionDashboard($sectionId, $startDate, $endDate);

        return response()->json($dashboard);
    }

    /**
     * Inventory health report.
     */
    public function inventoryHealth()
    {
        $report = $this->reportingService->getInventoryHealth();

        return response()->json($report);
    }

    /**
     * Sales report.
     */
    public function salesReport(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'section_id' => 'nullable|exists:sections,id',
        ]);

        // This would be implemented in ReportingService
        // For now, return a placeholder
        return response()->json([
            'message' => 'Sales report',
            'filters' => $validated,
        ]);
    }

    /**
     * Profit & Loss report.
     */
    public function profitLoss(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'section_id' => 'nullable|exists:sections,id',
        ]);

        if ($validated['section_id'] ?? null) {
            $pnl = $this->costingService->getSectionPnL(
                $validated['section_id'],
                $validated['start_date'],
                $validated['end_date']
            );
        } else {
            // Business-wide P&L
            $profit = $this->costingService->getBusinessProfit(
                $validated['start_date'],
                $validated['end_date']
            );
            $pnl = ['net_profit' => $profit];
        }

        return response()->json($pnl);
    }

    /**
     * Waste report.
     */
    public function wasteReport(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $report = $this->reportingService->getWasteReport(
            $validated['start_date'],
            $validated['end_date']
        );

        return response()->json($report);
    }

    /**
     * Expense report.
     */
    public function expenseReport(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $report = $this->reportingService->getExpenseReport(
            $validated['start_date'],
            $validated['end_date']
        );

        return response()->json($report);
    }

    /**
     * Top selling items.
     */
    public function topSelling(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->toDateString());
        $limit = $request->get('limit', 10);

        // This is already implemented in ReportingService as a protected method
        // We'd need to make it public or create a wrapper
        return response()->json([
            'message' => 'Top selling items report',
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
    }

    // ==================== EXPORT METHODS ====================

    /**
     * Export sales report to Excel
     */
    public function exportSalesExcel(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'section_id' => 'nullable|exists:sections,id',
        ]);

        $export = new SalesReportExport(
            $validated['start_date'],
            $validated['end_date'],
            $validated['section_id'] ?? null
        );

        $filename = $this->exportService->generateFilename('sales_report', 'xlsx');
        return $this->exportService->exportToExcel($export, $filename);
    }

    /**
     * Export sales report to PDF
     */
    public function exportSalesPdf(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'section_id' => 'nullable|exists:sections,id',
        ]);

        $query = Sale::with(['section', 'items.preparedInventory', 'user'])
            ->whereBetween('sale_date', [$validated['start_date'], $validated['end_date']]);

        if ($validated['section_id'] ?? null) {
            $query->where('section_id', $validated['section_id']);
        }

        $sales = $query->get();
        $section = $validated['section_id'] ? Section::find($validated['section_id']) : null;

        $totalRevenue = $sales->sum(fn($sale) => $sale->items->sum(fn($item) => $item->quantity * $item->unit_price));
        $totalCost = $sales->sum(fn($sale) => $sale->items->sum(fn($item) => $item->quantity * $item->cost_price));
        $totalProfit = $totalRevenue - $totalCost;
        $avgMargin = $totalRevenue > 0 ? ($totalProfit / $totalRevenue) * 100 : 0;

        $data = [
            'sales' => $sales,
            'section' => $section,
            'startDate' => $validated['start_date'],
            'endDate' => $validated['end_date'],
            'totalRevenue' => $totalRevenue,
            'totalCost' => $totalCost,
            'totalProfit' => $totalProfit,
            'avgMargin' => $avgMargin,
        ];

        $filename = $this->exportService->generateFilename('sales_report', 'pdf');
        return $this->exportService->exportToPdf('exports.pdf.sales-report', $data, $filename);
    }

    /**
     * Export profit & loss to Excel
     */
    public function exportProfitLossExcel(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'section_id' => 'nullable|exists:sections,id',
        ]);

        $export = new ProfitLossExport(
            $validated['start_date'],
            $validated['end_date'],
            $validated['section_id'] ?? null
        );

        $filename = $this->exportService->generateFilename('profit_loss', 'xlsx');
        return $this->exportService->exportToExcel($export, $filename);
    }

    /**
     * Export profit & loss to PDF
     */
    public function exportProfitLossPdf(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'section_id' => 'nullable|exists:sections,id',
        ]);

        $data = $validated['section_id']
            ? $this->costingService->getSectionPnL($validated['section_id'], $validated['start_date'], $validated['end_date'])
            : $this->getBusinessPnLData($validated['start_date'], $validated['end_date']);

        $section = $validated['section_id'] ? Section::find($validated['section_id']) : null;

        $grossProfit = $data['revenue'] - $data['cost_of_sales'];
        $netProfit = $grossProfit - $data['expenses'] - $data['waste'];
        $grossMargin = $data['revenue'] > 0 ? ($grossProfit / $data['revenue']) * 100 : 0;
        $netMargin = $data['revenue'] > 0 ? ($netProfit / $data['revenue']) * 100 : 0;

        $pdfData = [
            'data' => $data,
            'section' => $section,
            'startDate' => $validated['start_date'],
            'endDate' => $validated['end_date'],
            'grossProfit' => $grossProfit,
            'netProfit' => $netProfit,
            'grossMargin' => $grossMargin,
            'netMargin' => $netMargin,
        ];

        $filename = $this->exportService->generateFilename('profit_loss', 'pdf');
        return $this->exportService->exportToPdf('exports.pdf.profit-loss', $pdfData, $filename);
    }

    /**
     * Export waste report to Excel
     */
    public function exportWasteExcel(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $export = new WasteReportExport(
            $validated['start_date'],
            $validated['end_date']
        );

        $filename = $this->exportService->generateFilename('waste_report', 'xlsx');
        return $this->exportService->exportToExcel($export, $filename);
    }

    /**
     * Export waste report to PDF
     */
    public function exportWastePdf(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $wasteLogs = WasteLog::with(['section', 'rawMaterial', 'productionLog', 'reportedBy', 'approvedBy'])
            ->whereBetween('created_at', [$validated['start_date'], $validated['end_date']])
            ->where('approved', true)
            ->get();

        $wasteByReason = $wasteLogs->groupBy('reason')->map(function ($items, $reason) {
            return [
                'count' => $items->count(),
                'cost' => $items->sum('cost_amount'),
            ];
        });

        $wasteBySection = $wasteLogs->groupBy(fn($w) => $w->section->name ?? 'N/A')->map(function ($items) {
            return [
                'count' => $items->count(),
                'cost' => $items->sum('cost_amount'),
            ];
        });

        $data = [
            'wasteLogs' => $wasteLogs,
            'startDate' => $validated['start_date'],
            'endDate' => $validated['end_date'],
            'wasteByReason' => $wasteByReason,
            'wasteBySection' => $wasteBySection,
            'totalWasteCost' => $wasteLogs->sum('cost_amount'),
        ];

        $filename = $this->exportService->generateFilename('waste_report', 'pdf');
        return $this->exportService->exportToPdf('exports.pdf.waste-report', $data, $filename);
    }

    /**
     * Export expense report to Excel
     */
    public function exportExpensesExcel(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $export = new ExpenseReportExport(
            $validated['start_date'],
            $validated['end_date']
        );

        $filename = $this->exportService->generateFilename('expense_report', 'xlsx');
        return $this->exportService->exportToExcel($export, $filename);
    }

    /**
     * Export expense report to PDF
     */
    public function exportExpensesPdf(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $expenses = Expense::with(['section', 'recordedBy'])
            ->whereBetween('expense_date', [$validated['start_date'], $validated['end_date']])
            ->orderBy('expense_date', 'desc')
            ->get();

        $expensesByType = $expenses->groupBy('type')->map(fn($items) => $items->sum('amount'));
        $generalExpenses = $expenses->whereNull('section_id')->sum('amount');
        $sectionExpenses = $expenses->whereNotNull('section_id')->sum('amount');

        $data = [
            'expenses' => $expenses,
            'startDate' => $validated['start_date'],
            'endDate' => $validated['end_date'],
            'expensesByType' => $expensesByType,
            'totalExpenses' => $expenses->sum('amount'),
            'generalExpenses' => $generalExpenses,
            'sectionExpenses' => $sectionExpenses,
        ];

        $filename = $this->exportService->generateFilename('expense_report', 'pdf');
        return $this->exportService->exportToPdf('exports.pdf.expense-report', $data, $filename);
    }

    /**
     * Export inventory health to Excel
     */
    public function exportInventoryHealthExcel()
    {
        $export = new InventoryHealthExport();

        $filename = $this->exportService->generateFilename('inventory_health', 'xlsx');
        return $this->exportService->exportToExcel($export, $filename);
    }

    /**
     * Export inventory health to PDF
     */
    public function exportInventoryHealthPdf()
    {
        $rawMaterials = RawMaterial::with('preferredSupplier')->get();

        $materials = $rawMaterials->map(function ($material) {
            $currentStock = $this->inventoryService->getStockBalance($material->id);
            return [
                'name' => $material->name,
                'category' => $material->category,
                'unit' => $material->unit,
                'current_stock' => $currentStock,
                'min_quantity' => $material->min_quantity,
                'reorder_quantity' => $material->reorder_quantity,
            ];
        });

        $lowStockItems = $materials->filter(fn($m) => $m['current_stock'] > 0 && $m['current_stock'] <= $m['min_quantity']);
        $outOfStockItems = $materials->filter(fn($m) => $m['current_stock'] <= 0);

        $data = [
            'materials' => $materials,
            'lowStockItems' => $lowStockItems,
            'lowStockCount' => $lowStockItems->count(),
            'outOfStockCount' => $outOfStockItems->count(),
        ];

        $filename = $this->exportService->generateFilename('inventory_health', 'pdf');
        return $this->exportService->exportToPdf('exports.pdf.inventory-health', $data, $filename);
    }

    /**
     * Export top selling items to Excel
     */
    public function exportTopSellingExcel(Request $request)
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $limit = $request->get('limit', 10);

        $export = new TopSellingItemsExport($startDate, $endDate, $limit);

        $filename = $this->exportService->generateFilename('top_selling_items', 'xlsx');
        return $this->exportService->exportToExcel($export, $filename);
    }

    /**
     * Helper method to get business-wide P&L data
     */
    protected function getBusinessPnLData($startDate, $endDate)
    {
        $revenue = \App\Models\SaleItem::whereHas('sale', function ($q) use ($startDate, $endDate) {
            $q->whereBetween('sale_date', [$startDate, $endDate]);
        })->sum(\DB::raw('quantity * unit_price'));

        $costOfSales = \App\Models\SaleItem::whereHas('sale', function ($q) use ($startDate, $endDate) {
            $q->whereBetween('sale_date', [$startDate, $endDate]);
        })->sum(\DB::raw('quantity * cost_price'));

        $expenses = Expense::whereBetween('expense_date', [$startDate, $endDate])->sum('amount');
        $waste = WasteLog::whereBetween('created_at', [$startDate, $endDate])->sum('cost_amount');

        return [
            'revenue' => $revenue,
            'cost_of_sales' => $costOfSales,
            'expenses' => $expenses,
            'waste' => $waste,
        ];
    }
}
