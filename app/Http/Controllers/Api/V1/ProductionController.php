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

    public function __construct(CostingService $costingService)
    {
        $this->costingService = $costingService;
    }

    /**
     * Display a listing of production logs.
     */
    public function index(Request $request)
    {
        // Authorization handled by route middleware

        $user = auth()->user();
        $query = ProductionLog::with(['recipeVersion.recipe', 'section', 'chef']);

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
            'production_date' => 'required|date',
            'actual_yield' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Get the latest recipe version
            $recipe = \App\Models\Recipe::with([
                'versions' => function ($q) {
                    $q->latest()->limit(1);
                }
            ])->findOrFail($validated['recipe_id']);

            $latestVersion = $recipe->versions->first();

            if (!$latestVersion) {
                throw ValidationException::withMessages([
                    'recipe_id' => 'Recipe has no versions. Please create a recipe version first.'
                ]);
            }

            // Verify recipe belongs to chef's section
            if (auth()->user()->isChef() && $recipe->section_id != auth()->user()->section_id) {
                throw ValidationException::withMessages([
                    'recipe_id' => 'Recipe does not belong to your section'
                ]);
            }

            // Calculate variance
            $variance = $validated['actual_yield'] - ($recipe->expected_yield ?? 0);

            // Create production log
            $production = ProductionLog::create([
                'recipe_version_id' => $latestVersion->id,
                'section_id' => $recipe->section_id,
                'chef_id' => auth()->id(),
                'quantity_produced' => $validated['actual_yield'],
                'production_date' => $validated['production_date'],
                'variance' => $variance,
                'notes' => $validated['notes'] ?? null,
            ]);

            // Create prepared inventory for the produced items
            \App\Models\PreparedInventory::create([
                'production_log_id' => $production->id,
                'recipe_id' => $recipe->id,
                'item_name' => $recipe->name,
                'quantity' => $validated['actual_yield'],
                'unit' => $recipe->yield_unit ?? 'units',
                'selling_price' => $recipe->selling_price ?? 0,
                'status' => 'available',
                'section_id' => $recipe->section_id,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Production logged successfully',
                'data' => $production->load('recipeVersion.recipe')
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

        $production->load(['recipeVersion.recipe', 'recipeVersion.items.rawMaterial', 'section', 'chef']);

        $totalCost = $this->costingService->getProductionCost($production->id);
        $costPerUnit = $this->costingService->getCostPerUnit($production->id);

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

        // Add approved_by and approved_at fields if needed
        // For now, we'll just return success
        return response()->json([
            'message' => 'Production approved successfully',
            'data' => $production
        ]);
    }
}
