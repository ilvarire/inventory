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

use App\Http\Controllers\Api\AuthController;

// Public routes (no authentication required)
Route::prefix('v1')->group(function () {
    // Authentication routes
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('/user', [AuthController::class, 'user'])->middleware('auth:sanctum');
});

// Protected routes (require authentication)
Route::prefix('v1')->middleware(['auth:sanctum', 'throttle.custom:api.read'])->group(function () {

    // User info
    Route::get('/user', function (Request $request) {
        return $request->user()->load(['role', 'section']);
    });
    Route::put('/profile', [AuthController::class, 'updateProfile']);

    // User Management routes (Admin/Manager only)
    Route::prefix('users')->middleware('role:Admin,Manager')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::post('/', [UserController::class, 'store']);
        Route::get('/roles', [UserController::class, 'roles']);
        Route::get('/sections', [UserController::class, 'sections']);
        Route::get('/{user}', [UserController::class, 'show']);
        Route::put('/{user}', [UserController::class, 'update'])->middleware('throttle.custom:api.write');
        Route::delete('/{user}', [UserController::class, 'destroy'])->middleware('role:Admin');
        Route::post('/{user}/toggle-status', [UserController::class, 'toggleStatus'])->middleware('throttle.custom:api.write');
    });

    // Supplier routes
    Route::prefix('suppliers')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\V1\SupplierController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Api\V1\SupplierController::class, 'store'])
            ->middleware(['role:Procurement,Admin', 'throttle.custom:api.write']);
    });

    // Raw Materials routes
    Route::prefix('raw-materials')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\V1\RawMaterialController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Api\V1\RawMaterialController::class, 'store'])
            ->middleware(['role:Admin,Manager,Store Keeper', 'throttle.custom:api.write']);
        Route::get('/categories', [\App\Http\Controllers\Api\V1\RawMaterialController::class, 'categories']);
        Route::get('/units', [\App\Http\Controllers\Api\V1\RawMaterialController::class, 'units']);
        Route::get('/{rawMaterial}', [\App\Http\Controllers\Api\V1\RawMaterialController::class, 'show']);
        Route::put('/{rawMaterial}', [\App\Http\Controllers\Api\V1\RawMaterialController::class, 'update'])
            ->middleware(['role:Admin,Manager,Store Keeper', 'throttle.custom:api.write']);
        Route::delete('/{rawMaterial}', [\App\Http\Controllers\Api\V1\RawMaterialController::class, 'destroy'])
            ->middleware(['role:Admin,Manager', 'throttle.custom:api.write']);
    });

    // Public raw materials list (for material requests - all authenticated users)
    Route::get('/raw-materials-list', function () {
        $materials = \App\Models\RawMaterial::select('id', 'name', 'unit', 'category')->get();

        $materials->transform(function ($material) {
            // Get most recent unit cost
            $lastItem = \App\Models\ProcurementItem::where('raw_material_id', $material->id)
                ->orderBy('created_at', 'desc')
                ->first();

            $material->unit_cost = $lastItem ? $lastItem->unit_cost : 0;
            return $material;
        });

        return response()->json([
            'data' => $materials
        ]);
    });

    // Inventory routes
    Route::prefix('procurements')->group(function () {
        Route::get('/', [ProcurementController::class, 'index']);
        Route::post('/', [ProcurementController::class, 'store'])
            ->middleware(['role:Procurement,Admin', 'throttle.custom:api.write']);
        Route::get('/{procurement}', [ProcurementController::class, 'show']);
        Route::post('/{procurement}/approve', [ProcurementController::class, 'approve'])
            ->middleware(['role:Manager,Admin', 'throttle.custom:api.write']);
        Route::post('/{procurement}/reject', [ProcurementController::class, 'reject'])
            ->middleware(['role:Manager,Admin', 'throttle.custom:api.write']);
    });

    // Prepared Inventory routes (for sales)
    Route::get('/prepared-inventory', function (Request $request) {
        $query = \App\Models\PreparedInventory::query();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return response()->json([
            'data' => $query->get()
        ]);
    });

    // Inventory routes
    Route::prefix('inventory')->group(function () {
        Route::get('/', [InventoryController::class, 'index']);
        Route::get('/low-stock', [InventoryController::class, 'lowStock']);
        Route::get('/expiring', [InventoryController::class, 'expiring']);
        Route::get('/{material}', [InventoryController::class, 'show']);
        Route::get('/{material}/movements', [InventoryController::class, 'movements']);
    });

    // Sections route (for dropdowns)
    Route::get('/sections', function () {
        return response()->json([
            'data' => \App\Models\Section::all()
        ]);
    });

    // Material Request routes
    Route::prefix('material-requests')->group(function () {
        Route::get('/', [MaterialRequestController::class, 'index']);
        Route::post('/', [MaterialRequestController::class, 'store'])
            ->middleware(['role:Chef', 'throttle.custom:api.write']);
        Route::get('/{materialRequest}', [MaterialRequestController::class, 'show']);
        Route::post('/{materialRequest}/approve', [MaterialRequestController::class, 'approve'])
            ->middleware(['role:Manager,Admin', 'throttle.custom:api.write']);
        Route::post('/{materialRequest}/reject', [MaterialRequestController::class, 'reject'])
            ->middleware(['role:Manager,Admin', 'throttle.custom:api.write']);
        Route::post('/{materialRequest}/fulfill', [MaterialRequestController::class, 'fulfill'])
            ->middleware(['role:Store Keeper,Manager,Admin', 'throttle.custom:api.write']);
    });

    // Recipe routes
    Route::prefix('recipes')->group(function () {
        Route::get('/', [RecipeController::class, 'index']);
        Route::post('/', [RecipeController::class, 'store'])
            ->middleware(['role:Admin', 'throttle.custom:api.write']);
        Route::get('/{recipe}', [RecipeController::class, 'show']);
        Route::put('/{recipe}', [RecipeController::class, 'update'])
            ->middleware(['role:Admin', 'throttle.custom:api.write']);
        Route::delete('/{recipe}', [RecipeController::class, 'destroy'])
            ->middleware(['role:Manager,Admin', 'throttle.custom:api.write']);

        // Recipe version routes
        Route::post('/{recipe}/versions', [RecipeController::class, 'createVersion'])
            ->middleware(['role:Chef,Manager,Admin', 'throttle.custom:api.write']);
        Route::get('/{recipe}/versions/{version}', [RecipeController::class, 'showVersion']);
    });

    // Production routes
    Route::prefix('productions')->group(function () {
        Route::get('/', [ProductionController::class, 'index']);
        Route::post('/', [ProductionController::class, 'store'])
            ->middleware(['role:Chef,Admin', 'throttle.custom:api.write']);
        Route::get('/{production}', [ProductionController::class, 'show']);
        Route::post('/{production}/approve', [ProductionController::class, 'approve'])
            ->middleware(['role:Manager,Admin', 'throttle.custom:api.write']);
    });

    // Sales routes
    Route::prefix('sales')->group(function () {
        Route::get('/', [SaleController::class, 'index']);
        Route::post('/', [SaleController::class, 'store'])
            ->middleware(['role:Frontline Sales', 'throttle.custom:api.write']);
        Route::get('/{sale}', [SaleController::class, 'show']);
        Route::get('/{sale}/receipt', [SaleController::class, 'receipt']);
    });

    // Expense routes
    Route::prefix('expenses')->group(function () {
        Route::get('/', [ExpenseController::class, 'index'])
            ->middleware('role:Manager,Admin');
        Route::post('/', [ExpenseController::class, 'store'])
            ->middleware(['role:Manager,Admin', 'throttle.custom:api.write']);
        Route::get('/{expense}', [ExpenseController::class, 'show'])
            ->middleware('role:Manager,Admin');
    });

    // Waste routes
    Route::prefix('waste')->group(function () {
        Route::get('/', [WasteController::class, 'index']);
        Route::post('/', [WasteController::class, 'store'])->middleware('throttle.custom:api.write');
        Route::get('/{waste}', [WasteController::class, 'show']);
        Route::post('/{waste}/approve', [WasteController::class, 'approve'])
            ->middleware(['role:Manager,Admin', 'throttle.custom:api.write']);
    });

    // Report routes
    Route::prefix('reports')->middleware('throttle.custom:api.reports')->group(function () {
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

        // Export routes - stricter limits
        Route::middleware('throttle.custom:api.exports')->group(function () {
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
});
