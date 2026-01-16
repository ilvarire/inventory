<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ReportingService;
use App\Services\CostingService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    protected ReportingService $reportingService;
    protected CostingService $costingService;

    public function __construct(
        ReportingService $reportingService,
        CostingService $costingService
    ) {
        $this->reportingService = $reportingService;
        $this->costingService = $costingService;
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
}
