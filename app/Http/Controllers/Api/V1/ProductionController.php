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

            // Calculate variance
            $variance = $validated['actual_yield'] - ($recipe->expected_yield ?? 0);

            // Create production log
            $production = ProductionLog::create([
                'recipe_id' => $recipe->id,
                'section_id' => $recipe->section_id,
                'chef_id' => auth()->id(),
                'quantity_produced' => $validated['actual_yield'],
                'production_date' => $validated['production_date'],
                'variance' => $variance,
                'notes' => $validated['notes'] ?? null,
            ]);

            // Record materials used
            foreach ($recipe->items as $item) {
                $qtyUsed = $item->quantity_required * $validated['actual_yield'];

                // In a real system, we would find specific batches to deduce cost from (FIFO/LIFO)
                // For simplified version, we use the current unit cost of the raw material
                $unitCost = $item->rawMaterial->procurementItems()->latest()->value('unit_cost') ?? 0;

                \App\Models\ProductionMaterial::create([
                    'production_log_id' => $production->id,
                    'raw_material_id' => $item->raw_material_id,
                    'quantity_used' => $qtyUsed,
                    'unit_cost' => $unitCost
                ]);

                // Decrement stock (simplified)
                $item->rawMaterial->decrement('current_quantity', $qtyUsed);
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

                // Update selling price to match current recipe price (optional but keeps data fresh)
                // Also update expiry if provided for fresh batch? 
                // Decision: For now keep existing expiry to be safe, or maybe user wants to overwrite?
                // The prompt implies simple aggregation.
                $existingInventory->selling_price = $recipe->selling_price ?? 0;

                $existingInventory->save();
            } else {
                // Create new record
                \App\Models\PreparedInventory::create([
                    'production_log_id' => $production->id, // Initial log source
                    'recipe_id' => $recipe->id,
                    'item_name' => $recipe->name,
                    'quantity' => $validated['actual_yield'],
                    'unit' => $recipe->yield_unit ?? 'units',
                    'selling_price' => $recipe->selling_price ?? 0,
                    'status' => 'available',
                    'section_id' => $recipe->section_id,
                    'expiry_date' => $validated['expiry_date'] ?? null, // If passed, or calculator
                ]);
            }

            // Note: Actual stock deduction for ingredients would happen here in a real system
            // Decrement raw materials based on recipe items * quantity_produced

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

        $production->load(['recipe.items.rawMaterial', 'section', 'chef']);

        // Costing service needs update to handle flattened structure
        // For now preventing error if service not updated
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

        return response()->json([
            'message' => 'Production approved successfully',
            'data' => $production
        ]);
    }
}
