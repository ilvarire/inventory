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
    /**
     * Admin/Manager/Sales dashboard.
     */
    public function dashboard(Request $request)
    {
        $user = auth()->user();

        // Allow Admin, Manager, and Frontline Sales
        if (!$user->isAdmin() && !$user->isManager() && !$user->isSales()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $startDate = $request->get('start_date', now()->toDateString());
        $endDate = $request->get('end_date', now()->toDateString());

        // Prepare query dates (start of day / end of day)
        $queryStartDate = \Carbon\Carbon::parse($startDate)->startOfDay()->toDateTimeString();
        $queryEndDate = \Carbon\Carbon::parse($endDate)->endOfDay()->toDateTimeString();

        // If it's a Frontline Sales user, return their specific stats
        if ($user->isSales()) {
            $todaySales = Sale::where('sales_user_id', $user->id)
                ->whereBetween('sale_date', [$queryStartDate, $queryEndDate])
                ->get();


            return response()->json([
                'revenue' => $todaySales->sum('total_amount'),
                'profit' => 0, // Sales users don't see profit
                'sales_count' => $todaySales->count(),
                'expenses' => 0,
                'waste_cost' => 0,
                'is_personal' => true
            ]);
        }

        // For Admin/Manager, return full dashboard
        $dashboard = $this->reportingService->getAdminDashboard($queryStartDate, $queryEndDate);
        // Ensure sales_count is present for Admin dashboard
        $dashboard['sales_count'] = Sale::whereBetween('sale_date', [$queryStartDate, $queryEndDate])->count();

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

        $startDate = $request->get('start_date', now()->toDateString());
        $endDate = $request->get('end_date', now()->toDateString());

        // Prepare query dates
        $queryStartDate = \Carbon\Carbon::parse($startDate)->startOfDay()->toDateTimeString();
        $queryEndDate = \Carbon\Carbon::parse($endDate)->endOfDay()->toDateTimeString();

        $dashboard = $this->reportingService->getSectionDashboard($sectionId, $queryStartDate, $queryEndDate);

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
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'section_id' => 'nullable|exists:sections,id',
        ]);

        $startDate = $validated['start_date'] ?? now()->subDays(30)->format('Y-m-d');
        $endDate = $validated['end_date'] ?? now()->format('Y-m-d');

        $query = Sale::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        if (isset($validated['section_id'])) {
            $query->where('section_id', $validated['section_id']);
        }

        $sales = $query->with(['section', 'items.preparedInventory'])->get();

        // Calculate totals
        $totalRevenue = $sales->sum('total_amount');
        $totalSales = $sales->count();

        // Calculate recipe-based COGS: for each sold item, trace to recipe,
        // compute ingredient costs using latest procurement prices
        $materialCosts = $this->costingService->getRecipeBasedCOGS($sales);
        $totalProfit = $totalRevenue - $materialCosts;

        $averageSale = $totalSales > 0 ? $totalRevenue / $totalSales : 0;

        // Pre-fetch latest prices once for section-level calculations
        $latestPrices = $this->costingService->getLatestProcurementPrices();

        // Group by section
        $bySection = $sales->groupBy('section_id')->map(function ($sectionSales) use ($latestPrices) {
            $sectionRevenue = $sectionSales->sum('total_amount');
            $sectionCOGS = $this->costingService->getRecipeBasedCOGS($sectionSales, $latestPrices);

            return [
                'section_id' => $sectionSales->first()->section_id,
                'section_name' => $sectionSales->first()->section->name ?? 'N/A',
                'sales_count' => $sectionSales->count(),
                'revenue' => $sectionRevenue,
                'profit' => $sectionRevenue - $sectionCOGS,
            ];
        })->values();

        // Group by payment method
        $byPaymentMethod = $sales->groupBy('payment_method')->map(function ($methodSales, $method) {
            return [
                'payment_method' => ucfirst($method),
                'revenue' => $methodSales->sum('total_amount'),
                'count' => $methodSales->count(),
            ];
        })->values();

        // Daily revenue
        $dailyRevenue = $sales->groupBy(function ($sale) {
            return \Carbon\Carbon::parse($sale->sale_date)->format('Y-m-d');
        })->map(function ($daySales, $date) {
            return [
                'date' => $date,
                'revenue' => $daySales->sum('total_amount'),
                'sales_count' => $daySales->count(),
            ];
        })->values();

        return response()->json([
            'total_revenue' => $totalRevenue,
            'total_sales' => $totalSales,
            'total_profit' => $totalProfit,
            'average_sale' => $averageSale,
            'by_section' => $bySection,
            'by_payment_method' => $byPaymentMethod,
            'daily_revenue' => $dailyRevenue,
        ]);
    }

    /**
     * Profit & Loss report.
     */
    public function profitLoss(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'section_id' => 'nullable|exists:sections,id',
        ]);

        $startDate = $validated['start_date'] ?? now()->subDays(30)->format('Y-m-d');
        $endDate = $validated['end_date'] ?? now()->format('Y-m-d');

        // Get sales with items and their recipes for COGS calculation
        $salesQuery = Sale::whereBetween('sale_date', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        if (isset($validated['section_id'])) {
            $salesQuery->where('section_id', $validated['section_id']);
        }
        $sales = $salesQuery->with('items.preparedInventory')->get();
        $totalRevenue = $sales->sum('total_amount');

        // Calculate recipe-based material costs (COGS)
        // For each sold item: trace to recipe → ingredient costs at latest procurement prices
        $materialCosts = $this->costingService->getRecipeBasedCOGS($sales);

        // Calculate waste costs
        $wasteQuery = WasteLog::whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->where('status', 'approved');
        if (isset($validated['section_id'])) {
            $wasteQuery->where('section_id', $validated['section_id']);
        }
        $wasteCosts = $wasteQuery->sum('cost_amount');

        // Total COGS = material costs of items sold + waste
        $totalCogs = $materialCosts + $wasteCosts;

        // Sales Profit (Revenue - Material Costs only)
        // This matches the cumulative "Profit" from recipe analysis
        $salesProfit = $totalRevenue - $materialCosts;

        // Gross Profit (Sales Profit - Waste)
        $grossProfit = $totalRevenue - $totalCogs;
        $grossMargin = $totalRevenue > 0 ? ($grossProfit / $totalRevenue) * 100 : 0;
        $salesMargin = $totalRevenue > 0 ? ($salesProfit / $totalRevenue) * 100 : 0;

        // Get expenses
        $expenseQuery = Expense::whereBetween('expense_date', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        if (isset($validated['section_id'])) {
            $expenseQuery->where('section_id', $validated['section_id']);
        }
        $expenses = $expenseQuery->get();
        $totalExpenses = $expenses->sum('amount');

        // Group expenses by category
        $expensesByCategory = $expenses->groupBy('category')->map(function ($categoryExpenses, $category) {
            return [
                'category' => $category,
                'amount' => $categoryExpenses->sum('amount'),
            ];
        })->values();

        // Net Profit
        $netProfit = $grossProfit - $totalExpenses;
        $netMargin = $totalRevenue > 0 ? ($netProfit / $totalRevenue) * 100 : 0;

        // Calculate unsold prepared products (available inventory)
        // This shows the potential cost tied up in prepared-but-unsold items
        $unsoldQuery = \App\Models\PreparedInventory::where('status', 'available');
        if (isset($validated['section_id'])) {
            $unsoldQuery->where('section_id', $validated['section_id']);
        }
        $unsoldItems = $unsoldQuery->get();

        $latestPrices = $this->costingService->getLatestProcurementPrices();
        $unsoldPreparedCost = 0;
        $unsoldPreparedRevenue = 0;

        foreach ($unsoldItems as $item) {
            if ($item->recipe_id) {
                $costPerUnit = $this->costingService->getRecipeCostPerUnit($item->recipe_id, $latestPrices);
                $unsoldPreparedCost += $costPerUnit * $item->quantity;
            }
            $unsoldPreparedRevenue += ($item->selling_price ?? 0) * $item->quantity;
        }

        return response()->json([
            'total_revenue' => $totalRevenue,
            'material_costs' => $materialCosts,
            'waste_costs' => $wasteCosts,
            'total_cogs' => $totalCogs,
            'sales_profit' => $salesProfit,
            'sales_margin' => round($salesMargin, 2),
            'gross_profit' => $grossProfit,
            'gross_margin' => round($grossMargin, 2),
            'total_expenses' => $totalExpenses,
            'expenses_by_category' => $expensesByCategory,
            'net_profit' => $netProfit,
            'net_margin' => round($netMargin, 2),
            'unsold_prepared_cost' => $unsoldPreparedCost,
            'unsold_prepared_revenue' => $unsoldPreparedRevenue,
        ]);
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

        $report = $this->reportingService->getTopSellingItems(
            $startDate,
            $endDate,
            $limit
        );

        return response()->json($report);
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
        $rawMaterials = RawMaterial::all();

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
        $sales = Sale::whereBetween('sale_date', [$startDate, $endDate])
            ->with('items.preparedInventory')
            ->get();

        $revenue = $sales->sum('total_amount');

        // Recipe-based COGS: cost of raw materials used to make what was sold
        $costOfSales = $this->costingService->getRecipeBasedCOGS($sales);

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
