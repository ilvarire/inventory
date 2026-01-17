<?php

use App\Http\Controllers\Api\V1\ProcurementController;
use App\Http\Controllers\Api\V1\InventoryController;
use App\Http\Controllers\Api\V1\MaterialRequestController;
use App\Http\Controllers\Api\V1\RecipeController;
use App\Http\Controllers\Api\V1\ProductionController;
use App\Http\Controllers\Api\V1\SaleController;
use App\Http\Controllers\Api\V1\ExpenseController;
use App\Http\Controllers\Api\V1\WasteController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes (no authentication required)
Route::prefix('v1')->group(function () {
    // Authentication routes are handled by Laravel Breeze in routes/auth.php
});

// Protected routes (require authentication)
Route::prefix('v1')->middleware(['auth:sanctum'])->group(function () {

    // User info
    Route::get('/user', function (Request $request) {
        return $request->user()->load(['role', 'section']);
    });

    // User Management routes (Admin/Manager only)
    Route::prefix('users')->middleware('role:Admin,Manager')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::post('/', [UserController::class, 'store']);
        Route::get('/roles', [UserController::class, 'roles']);
        Route::get('/sections', [UserController::class, 'sections']);
        Route::get('/{user}', [UserController::class, 'show']);
        Route::put('/{user}', [UserController::class, 'update']);
        Route::delete('/{user}', [UserController::class, 'destroy'])->middleware('role:Admin');
        Route::post('/{user}/toggle-status', [UserController::class, 'toggleStatus']);
    });

    // Procurement routes
    Route::prefix('procurements')->group(function () {
        Route::get('/', [ProcurementController::class, 'index']);
        Route::post('/', [ProcurementController::class, 'store'])
            ->middleware('role:Procurement,Admin');
        Route::get('/{procurement}', [ProcurementController::class, 'show']);
    });

    // Inventory routes
    Route::prefix('inventory')->group(function () {
        Route::get('/', [InventoryController::class, 'index']);
        Route::get('/low-stock', [InventoryController::class, 'lowStock']);
        Route::get('/expiring', [InventoryController::class, 'expiring']);
        Route::get('/{material}', [InventoryController::class, 'show']);
        Route::get('/{material}/movements', [InventoryController::class, 'movements']);
    });

    // Material Request routes
    Route::prefix('material-requests')->group(function () {
        Route::get('/', [MaterialRequestController::class, 'index']);
        Route::post('/', [MaterialRequestController::class, 'store'])
            ->middleware('role:Chef');
        Route::get('/{materialRequest}', [MaterialRequestController::class, 'show']);
        Route::post('/{materialRequest}/approve', [MaterialRequestController::class, 'approve'])
            ->middleware('role:Manager,Admin');
        Route::post('/{materialRequest}/reject', [MaterialRequestController::class, 'reject'])
            ->middleware('role:Manager,Admin');
        Route::post('/{materialRequest}/fulfill', [MaterialRequestController::class, 'fulfill'])
            ->middleware('role:Store Keeper,Manager,Admin');
    });

    // Recipe routes
    Route::prefix('recipes')->group(function () {
        Route::get('/', [RecipeController::class, 'index']);
        Route::post('/', [RecipeController::class, 'store'])
            ->middleware('role:Chef,Manager,Admin');
        Route::get('/{recipe}', [RecipeController::class, 'show']);
        Route::put('/{recipe}', [RecipeController::class, 'update'])
            ->middleware('role:Chef,Manager,Admin');
        Route::delete('/{recipe}', [RecipeController::class, 'destroy'])
            ->middleware('role:Manager,Admin');

        // Recipe version routes
        Route::post('/{recipe}/versions', [RecipeController::class, 'createVersion'])
            ->middleware('role:Chef,Manager,Admin');
        Route::get('/{recipe}/versions/{version}', [RecipeController::class, 'showVersion']);
    });

    // Production routes
    Route::prefix('productions')->group(function () {
        Route::get('/', [ProductionController::class, 'index']);
        Route::post('/', [ProductionController::class, 'store'])
            ->middleware('role:Chef');
        Route::get('/{production}', [ProductionController::class, 'show']);
        Route::post('/{production}/approve', [ProductionController::class, 'approve'])
            ->middleware('role:Manager,Admin');
    });

    // Sales routes
    Route::prefix('sales')->group(function () {
        Route::get('/', [SaleController::class, 'index']);
        Route::post('/', [SaleController::class, 'store'])
            ->middleware('role:Frontline Sales');
        Route::get('/{sale}', [SaleController::class, 'show']);
        Route::get('/{sale}/receipt', [SaleController::class, 'receipt']);
    });

    // Expense routes
    Route::prefix('expenses')->group(function () {
        Route::get('/', [ExpenseController::class, 'index'])
            ->middleware('role:Manager,Admin');
        Route::post('/', [ExpenseController::class, 'store'])
            ->middleware('role:Manager,Admin');
        Route::get('/{expense}', [ExpenseController::class, 'show'])
            ->middleware('role:Manager,Admin');
    });

    // Waste routes
    Route::prefix('waste')->group(function () {
        Route::get('/', [WasteController::class, 'index']);
        Route::post('/', [WasteController::class, 'store']);
        Route::get('/{waste}', [WasteController::class, 'show']);
        Route::post('/{waste}/approve', [WasteController::class, 'approve'])
            ->middleware('role:Manager,Admin');
    });

    // Report routes
    Route::prefix('reports')->group(function () {
        Route::get('/dashboard', [ReportController::class, 'dashboard'])
            ->middleware('role:Manager,Admin');
        Route::get('/sections/{sectionId}/dashboard', [ReportController::class, 'sectionDashboard']);
        Route::get('/inventory-health', [ReportController::class, 'inventoryHealth']);
        Route::get('/sales', [ReportController::class, 'salesReport']);
        Route::get('/profit-loss', [ReportController::class, 'profitLoss'])
            ->middleware('role:Manager,Admin');
        Route::get('/waste', [ReportController::class, 'wasteReport']);
        Route::get('/expenses', [ReportController::class, 'expenseReport'])
            ->middleware('role:Manager,Admin');
        Route::get('/top-selling', [ReportController::class, 'topSelling']);

        // Export routes
        Route::get('/sales/export/excel', [ReportController::class, 'exportSalesExcel']);
        Route::get('/sales/export/pdf', [ReportController::class, 'exportSalesPdf']);

        Route::get('/profit-loss/export/excel', [ReportController::class, 'exportProfitLossExcel'])
            ->middleware('role:Manager,Admin');
        Route::get('/profit-loss/export/pdf', [ReportController::class, 'exportProfitLossPdf'])
            ->middleware('role:Manager,Admin');

        Route::get('/waste/export/excel', [ReportController::class, 'exportWasteExcel']);
        Route::get('/waste/export/pdf', [ReportController::class, 'exportWastePdf']);

        Route::get('/expenses/export/excel', [ReportController::class, 'exportExpensesExcel'])
            ->middleware('role:Manager,Admin');
        Route::get('/expenses/export/pdf', [ReportController::class, 'exportExpensesPdf'])
            ->middleware('role:Manager,Admin');

        Route::get('/inventory-health/export/excel', [ReportController::class, 'exportInventoryHealthExcel']);
        Route::get('/inventory-health/export/pdf', [ReportController::class, 'exportInventoryHealthPdf']);

        Route::get('/top-selling/export/excel', [ReportController::class, 'exportTopSellingExcel']);
    });
});
