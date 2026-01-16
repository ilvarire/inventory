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
        $this->authorize('viewAny', ProductionLog::class);

        $user = auth()->user();
        $query = ProductionLog::with(['recipeVersion.recipe', 'materials.rawMaterial']);

        // Chef can only see production from their section
        if ($user->isChef()) {
            $query->where('section_id', $user->section_id);
        }

        // Filter by section
        if ($request->has('section_id')) {
            $query->where('section_id', $request->section_id);
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->where('production_date', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->where('production_date', '<=', $request->end_date);
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
        $this->authorize('create', ProductionLog::class);

        $validated = $request->validate([
            'recipe_version_id' => 'required|exists:recipe_versions,id',
            'quantity_produced' => 'required|numeric|min:1',
            'production_date' => 'required|date',
            'materials' => 'required|array|min:1',
            'materials.*.raw_material_id' => 'required|exists:raw_materials,id',
            'materials.*.procurement_item_id' => 'required|exists:procurement_items,id',
            'materials.*.quantity_used' => 'required|numeric|min:0.01',
            'expiry_date' => 'nullable|date|after:today',
        ]);

        try {
            DB::beginTransaction();

            // Verify recipe version belongs to chef's section
            $recipeVersion = RecipeVersion::with('recipe')->findOrFail($validated['recipe_version_id']);
            if (auth()->user()->isChef() && $recipeVersion->recipe->section_id != auth()->user()->section_id) {
                throw ValidationException::withMessages([
                    'recipe_version_id' => 'Recipe does not belong to your section'
                ]);
            }

            // Create production log
            $production = ProductionLog::create([
                'recipe_version_id' => $validated['recipe_version_id'],
                'section_id' => $recipeVersion->recipe->section_id,
                'chef_id' => auth()->id(),
                'quantity_produced' => $validated['quantity_produced'],
                'production_date' => $validated['production_date'],
            ]);

            // Record materials used with batch tracking
            foreach ($validated['materials'] as $material) {
                // Get unit cost from the batch
                $batch = ProcurementItem::findOrFail($material['procurement_item_id']);

                ProductionMaterial::create([
                    'production_log_id' => $production->id,
                    'raw_material_id' => $material['raw_material_id'],
                    'procurement_item_id' => $material['procurement_item_id'],
                    'quantity_used' => $material['quantity_used'],
                    'unit_cost' => $batch->unit_cost,
                ]);

                // Update batch received quantity
                $batch->increment('received_quantity', $material['quantity_used']);
            }

            // Calculate total production cost
            $totalCost = $this->costingService->getProductionCost($production->id);
            $costPerUnit = $this->costingService->getCostPerUnit($production->id);

            // Create prepared inventory
            PreparedInventory::create([
                'production_log_id' => $production->id,
                'section_id' => $recipeVersion->recipe->section_id,
                'item_name' => $recipeVersion->recipe->name,
                'quantity' => $validated['quantity_produced'],
                'expiry_date' => $validated['expiry_date'] ?? now()->addDays(3),
                'status' => 'available',
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Production logged successfully',
                'data' => [
                    'production' => $production->load('materials.rawMaterial'),
                    'total_cost' => $totalCost,
                    'cost_per_unit' => $costPerUnit,
                ]
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
        $this->authorize('view', $production);

        $production->load(['recipeVersion.recipe', 'materials.rawMaterial', 'materials.batch']);

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
