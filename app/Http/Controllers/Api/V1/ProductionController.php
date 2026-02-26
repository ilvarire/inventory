<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ProductionLog;
use App\Models\ProductionMaterial;
use App\Models\PreparedInventory;
use App\Models\RecipeVersion;
use App\Models\ProcurementItem;
use App\Services\CostingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductionController extends Controller
{
    protected CostingService $costingService;
    protected \App\Services\InventoryService $inventoryService;

    public function __construct(CostingService $costingService, \App\Services\InventoryService $inventoryService)
    {
        $this->costingService = $costingService;
        $this->inventoryService = $inventoryService;
    }

    /**
     * Display a listing of production logs.
     */
    public function index(Request $request)
    {
        // Authorization handled by route middleware

        $user = auth()->user();
        $query = ProductionLog::with(['recipe', 'section', 'chef']);

        // Chef can only see production from their section
        if ($user->isChef()) {
            $query->where('section_id', $user->section_id);
        }

        // Filter by section
        if ($request->has('section_id') && $request->section_id) {
            $query->where('section_id', $request->section_id);
        }

        // Filter by date range - use whereDate for proper date comparison
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('production_date', '>=', $request->start_date);
        }
        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('production_date', '<=', $request->end_date);
        }

        $productions = $query->orderBy('production_date', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json($productions);
    }

    /**
     * Store a newly created production log.
     */
    public function store(Request $request)
    {
        // Authorization handled by route middleware
        $validated = $request->validate([
            'recipe_id' => 'required|exists:recipes,id',
            'material_request_id' => 'required|exists:material_requests,id',
            'production_date' => 'required|date',
            'actual_yield' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $recipe = \App\Models\Recipe::with('items.rawMaterial')->findOrFail($validated['recipe_id']);

            // Verify recipe belongs to chef's section
            if (auth()->user()->isChef() && $recipe->section_id != auth()->user()->section_id) {
                throw ValidationException::withMessages([
                    'recipe_id' => 'Recipe does not belong to your section'
                ]);
            }

            // --- Material Request Validation ---
            $materialRequest = \App\Models\MaterialRequest::with('items')
                ->findOrFail($validated['material_request_id']);

            // Must belong to the authenticated chef
            if ($materialRequest->chef_id !== auth()->id()) {
                throw ValidationException::withMessages([
                    'material_request_id' => 'This material request does not belong to you'
                ]);
            }

            // Must be fulfilled
            if ($materialRequest->status !== 'fulfilled') {
                throw ValidationException::withMessages([
                    'material_request_id' => 'This material request has not been fulfilled yet'
                ]);
            }

            // Must be unused
            if ($materialRequest->used_in_production) {
                throw ValidationException::withMessages([
                    'material_request_id' => 'This material request has already been used in production'
                ]);
            }

            // --- Ingredient Match Validation ---
            $recipeRawMaterialIds = $recipe->items->pluck('raw_material_id')->sort()->values()->toArray();
            $requestRawMaterialIds = $materialRequest->items->pluck('raw_material_id')->unique()->sort()->values()->toArray();

            $missingIngredients = array_diff($recipeRawMaterialIds, $requestRawMaterialIds);

            if (!empty($missingIngredients)) {
                // Get names of missing ingredients for a helpful error message
                $missingNames = \App\Models\RawMaterial::whereIn('id', $missingIngredients)
                    ->pluck('name')
                    ->toArray();

                throw ValidationException::withMessages([
                    'material_request_id' => 'Material request is missing ingredients required by the recipe: ' . implode(', ', $missingNames)
                ]);
            }

            // Calculate variance
            $variance = $validated['actual_yield'] - ($recipe->expected_yield ?? 0);

            // Create production log
            $production = ProductionLog::create([
                'recipe_id' => $recipe->id,
                'section_id' => $recipe->section_id,
                'chef_id' => auth()->id(),
                'material_request_id' => $materialRequest->id,
                'quantity_produced' => $validated['actual_yield'],
                'production_date' => $validated['production_date'],
                'variance' => $variance,
                'notes' => $validated['notes'] ?? null,
            ]);

            // Record materials used (for Costing/Yield tracking)
            foreach ($recipe->items as $item) {
                $qtyUsed = $item->quantity_required * $validated['actual_yield'];

                // Log Production Material (for Costing/Yield tracking)
                // Note: Since materials are already issued via Material Requests, 
                // we don't deduct from inventory here to avoid double-deduction.
                // We record the cost based on the latest procurement price for reporting.
                $unitCost = $item->rawMaterial->procurementItems()->latest()->value('unit_cost') ?? 0;

                \App\Models\ProductionMaterial::create([
                    'production_log_id' => $production->id,
                    'raw_material_id' => $item->raw_material_id,
                    'quantity_used' => $qtyUsed,
                    'unit_cost' => $unitCost
                ]);
            }

            // Create or update prepared inventory
            $existingInventory = \App\Models\PreparedInventory::where('recipe_id', $recipe->id)
                ->where('section_id', $recipe->section_id)
                ->whereIn('status', ['available', 'sold'])
                ->orderByRaw("CASE WHEN status = 'available' THEN 1 ELSE 2 END")
                ->first();

            if ($existingInventory) {
                // Aggregate quantity to existing record
                $existingInventory->quantity += $validated['actual_yield'];

                // Ensure status is available (in case it was sold)
                $existingInventory->status = 'available';

                // Update selling price to match current recipe price
                $existingInventory->selling_price = $recipe->selling_price ?? 0;

                $existingInventory->save();
            } else {
                // Create new record
                \App\Models\PreparedInventory::create([
                    'production_log_id' => $production->id,
                    'recipe_id' => $recipe->id,
                    'item_name' => $recipe->name,
                    'quantity' => $validated['actual_yield'],
                    'unit' => $recipe->yield_unit ?? 'units',
                    'selling_price' => $recipe->selling_price ?? 0,
                    'status' => 'available',
                    'section_id' => $recipe->section_id,
                    'expiry_date' => $validated['expiry_date'] ?? null,
                ]);
            }

            // Mark the material request as used
            $materialRequest->update(['used_in_production' => true]);

            DB::commit();

            return response()->json([
                'message' => 'Production logged successfully',
                'data' => $production->load('recipe')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Display the specified production log.
     */
    public function show(ProductionLog $production)
    {
        // Authorization handled by route middleware

        // Auto-clear matching notifications when viewing the production
        $notificationService = app(\App\Services\NotificationService::class);
        $notificationService->markAsReadByActionUrl(
            "/productions/{$production->id}",
            auth()->user()
        );

        $production->load(['recipe.items.rawMaterial', 'section', 'chef']);

        try {
            $totalCost = $this->costingService->getProductionCost($production->id);
            $costPerUnit = $this->costingService->getCostPerUnit($production->id);
        } catch (\Exception $e) {
            $totalCost = 0;
            $costPerUnit = 0;
        }

        return response()->json([
            'production' => $production,
            'total_cost' => $totalCost,
            'cost_per_unit' => $costPerUnit,
        ]);
    }

    /**
     * Approve a production log (Manager only).
     */
    public function approve(ProductionLog $production)
    {
        $this->authorize('approve', $production);

        // Auto-clear matching notifications for the approver
        $notificationService = app(\App\Services\NotificationService::class);
        $notificationService->markAsReadByActionUrl(
            "/productions/{$production->id}",
            auth()->user()
        );

        return response()->json([
            'message' => 'Production approved successfully',
            'data' => $production
        ]);
    }
}
