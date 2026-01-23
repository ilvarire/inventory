<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\RawMaterial;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RawMaterialController extends Controller
{
    /**
     * Display a listing of raw materials.
     */
    public function index(Request $request)
    {
        $query = RawMaterial::with('section');

        // Search by name
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filter by category
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        // Filter by section
        if ($request->has('section_id')) {
            $query->where('section_id', $request->section_id);
        }

        // Filter by supplier
        if ($request->has('supplier_id')) {
            $query->where('preferred_supplier_id', $request->supplier_id);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate
        $perPage = $request->get('per_page', 15);
        $materials = $query->paginate($perPage);

        // Add average unit cost from recent procurements for each material
        $materials->getCollection()->transform(function ($material) {
            $avgCost = \DB::table('procurement_items')
                ->where('raw_material_id', $material->id)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->avg('unit_cost');
            $material->unit_cost = $avgCost ?? 0;
            return $material;
        });

        return response()->json($materials);
    }

    /**
     * Store a newly created raw material.
     */
    public function store(Request $request)
    {
        $this->authorize('create', RawMaterial::class);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:raw_materials,name',
            'unit' => 'required|string|max:50',
            'category' => 'nullable|string|max:255',
            'section_id' => 'nullable|exists:sections,id',
            'min_quantity' => 'required|numeric|min:0',
            'reorder_quantity' => 'required|numeric|min:0|gt:min_quantity',
            'preferred_supplier_id' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $rawMaterial = RawMaterial::create($validator->validated());

        return response()->json([
            'message' => 'Raw material created successfully',
            'data' => $rawMaterial
        ], 201);
    }

    /**
     * Display the specified raw material.
     */
    public function show(RawMaterial $rawMaterial)
    {
        $this->authorize('view', $rawMaterial);

        $rawMaterial->load([
            'batches' => function ($query) {
                $query->latest()->limit(10);
            },
            'inventoryMovements' => function ($query) {
                $query->latest()->limit(20);
            }
        ]);

        // Get current stock level
        $currentStock = $rawMaterial->batches->sum('received_quantity');

        // Get recipes using this material
        $recipes = \App\Models\RecipeItem::where('raw_material_id', $rawMaterial->id)
            ->with('recipeVersion.recipe')
            ->get()
            ->pluck('recipeVersion.recipe')
            ->unique('id')
            ->values();

        return response()->json([
            'material' => $rawMaterial,
            'current_stock' => $currentStock,
            'recipes' => $recipes,
        ]);
    }

    /**
     * Update the specified raw material.
     */
    public function update(Request $request, RawMaterial $rawMaterial)
    {
        $this->authorize('update', $rawMaterial);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255|unique:raw_materials,name,' . $rawMaterial->id,
            'unit' => 'sometimes|required|string|max:50',
            'category' => 'nullable|string|max:255',
            'min_quantity' => 'sometimes|required|numeric|min:0',
            'reorder_quantity' => 'sometimes|required|numeric|min:0',
            'preferred_supplier_id' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Validate reorder_quantity is greater than min_quantity if both are being updated
        $data = $validator->validated();
        $minQty = $data['min_quantity'] ?? $rawMaterial->min_quantity;
        $reorderQty = $data['reorder_quantity'] ?? $rawMaterial->reorder_quantity;

        if ($reorderQty <= $minQty) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => ['reorder_quantity' => ['Reorder quantity must be greater than minimum quantity']]
            ], 422);
        }

        $rawMaterial->update($data);

        return response()->json([
            'message' => 'Raw material updated successfully',
            'data' => $rawMaterial
        ]);
    }

    /**
     * Remove the specified raw material.
     */
    public function destroy(RawMaterial $rawMaterial)
    {
        $this->authorize('delete', $rawMaterial);

        // Check if material is used in recipes
        $recipesCount = \App\Models\RecipeItem::where('raw_material_id', $rawMaterial->id)->count();
        if ($recipesCount > 0) {
            return response()->json([
                'message' => 'Cannot delete raw material',
                'error' => 'This material is used in ' . $recipesCount . ' recipe(s). Please remove it from recipes first.'
            ], 422);
        }

        // Check if material has inventory
        $inventoryCount = $rawMaterial->batches()->sum('received_quantity');
        if ($inventoryCount > 0) {
            return response()->json([
                'message' => 'Cannot delete raw material',
                'error' => 'This material has inventory stock (' . $inventoryCount . ' ' . $rawMaterial->unit . '). Please clear inventory first.'
            ], 422);
        }

        // Check if there are pending procurements
        $pendingProcurements = \App\Models\ProcurementItem::where('raw_material_id', $rawMaterial->id)
            ->whereHas('procurement', function ($query) {
                $query->where('status', 'pending');
            })
            ->count();

        if ($pendingProcurements > 0) {
            return response()->json([
                'message' => 'Cannot delete raw material',
                'error' => 'This material has ' . $pendingProcurements . ' pending procurement(s). Please complete or cancel them first.'
            ], 422);
        }

        $rawMaterial->delete();

        return response()->json([
            'message' => 'Raw material deleted successfully'
        ]);
    }

    /**
     * Get list of categories for filtering.
     */
    public function categories()
    {
        $categories = RawMaterial::select('category')
            ->distinct()
            ->whereNotNull('category')
            ->orderBy('category')
            ->pluck('category');

        return response()->json($categories);
    }

    /**
     * Get list of units.
     */
    public function units()
    {
        return response()->json([
            'kg' => 'Kilogram',
            'liter' => 'Liter',
            'piece' => 'Piece',
            'gram' => 'Gram',
            'ml' => 'Milliliter'
        ]);
    }
}
