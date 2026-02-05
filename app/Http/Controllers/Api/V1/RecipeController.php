<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Recipe;
use App\Models\RecipeVersion;
use App\Models\RecipeItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecipeController extends Controller
{
    /**
     * Display a listing of recipes.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Recipe::class);

        $user = auth()->user();
        $query = Recipe::with([
            'section',
            'creator',
            'items.rawMaterial'
        ]);

        // Chef can only see recipes from their section
        if ($user->isChef()) {
            $query->where('section_id', $user->section_id);
        }

        // Filter by section
        if ($request->has('section_id')) {
            $query->where('section_id', $request->section_id);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Search by name
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Sorting
        $sortField = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $allowedSorts = ['name', 'created_at', 'expected_yield', 'selling_price'];

        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $recipes = $query->paginate($request->get('per_page', 15));

        return response()->json($recipes);
    }

    /**
     * Store a newly created recipe.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Recipe::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'section_id' => 'required|exists:sections,id',
            'description' => 'nullable|string',
            'expected_yield' => 'required|numeric|min:0.01',
            'yield_unit' => 'required|string|max:50',
            'selling_price' => 'required|numeric|min:0',
            'instructions' => 'nullable|string',
            'ingredients' => 'required|array|min:1',
            'ingredients.*.raw_material_id' => 'required|exists:raw_materials,id',
            'ingredients.*.quantity' => 'required|numeric|min:0.01',
        ]);

        // Ensure chef can only create recipes for their section
        if (auth()->user()->isChef() && $validated['section_id'] != auth()->user()->section_id) {
            return response()->json([
                'message' => 'You can only create recipes for your section'
            ], 403);
        }

        try {
            DB::beginTransaction();

            $recipe = Recipe::create([
                'name' => $validated['name'],
                'section_id' => $validated['section_id'],
                'description' => $validated['description'] ?? null,
                'expected_yield' => $validated['expected_yield'],
                'yield_unit' => $validated['yield_unit'],
                'selling_price' => $validated['selling_price'],
                'instructions' => $validated['instructions'] ?? null,
                'created_by' => auth()->id(),
                'status' => 'active', // Default directly to active for simplicity
            ]);

            foreach ($validated['ingredients'] as $ingredient) {
                RecipeItem::create([
                    'recipe_id' => $recipe->id,
                    'raw_material_id' => $ingredient['raw_material_id'],
                    'quantity_required' => $ingredient['quantity'],
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Recipe created successfully',
                'data' => $recipe->load('items.rawMaterial')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Display the specified recipe.
     */
    public function show(Recipe $recipe)
    {
        $this->authorize('view', $recipe);

        $recipe->load(['section', 'creator', 'items.rawMaterial']);

        return response()->json($recipe);
    }

    /**
     * Update the specified recipe.
     */
    public function update(Request $request, Recipe $recipe)
    {
        $this->authorize('update', $recipe);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'section_id' => 'sometimes|exists:sections,id',
            'description' => 'nullable|string',
            'expected_yield' => 'sometimes|numeric|min:0.01',
            'yield_unit' => 'sometimes|string|max:50',
            'selling_price' => 'sometimes|numeric|min:0',
            'instructions' => 'nullable|string',
            'status' => 'sometimes|in:draft,active,archived',
            'ingredients' => 'nullable|array|min:1',
            'ingredients.*.raw_material_id' => 'required_with:ingredients|exists:raw_materials,id',
            'ingredients.*.quantity' => 'required_with:ingredients|numeric|min:0.01',
        ]);

        try {
            DB::beginTransaction();

            // Update recipe details
            $recipe->update(\Illuminate\Support\Arr::except($validated, ['ingredients']));

            // Update ingredients if provided
            if (isset($validated['ingredients'])) {
                // Remove old items
                $recipe->items()->delete();

                // Add new items
                foreach ($validated['ingredients'] as $ingredient) {
                    RecipeItem::create([
                        'recipe_id' => $recipe->id,
                        'raw_material_id' => $ingredient['raw_material_id'],
                        'quantity_required' => $ingredient['quantity'],
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Recipe updated successfully',
                'data' => $recipe->load(['section', 'items.rawMaterial'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Remove the specified recipe.
     */
    public function destroy(Recipe $recipe)
    {
        $this->authorize('delete', $recipe);

        $recipe->delete();

        return response()->json([
            'message' => 'Recipe deleted successfully'
        ]);
    }
}
