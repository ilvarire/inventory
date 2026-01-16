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
        $query = Recipe::with(['section', 'creator', 'versions']);

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

        $recipes = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

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
        ]);

        // Ensure chef can only create recipes for their section
        if (auth()->user()->isChef() && $validated['section_id'] != auth()->user()->section_id) {
            return response()->json([
                'message' => 'You can only create recipes for your section'
            ], 403);
        }

        $recipe = Recipe::create([
            'name' => $validated['name'],
            'section_id' => $validated['section_id'],
            'created_by' => auth()->id(),
            'status' => 'draft',
        ]);

        return response()->json([
            'message' => 'Recipe created successfully',
            'data' => $recipe
        ], 201);
    }

    /**
     * Display the specified recipe.
     */
    public function show(Recipe $recipe)
    {
        $this->authorize('view', $recipe);

        $recipe->load(['section', 'creator', 'versions.items.rawMaterial']);

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
            'status' => 'sometimes|in:draft,active,archived',
        ]);

        $recipe->update($validated);

        return response()->json([
            'message' => 'Recipe updated successfully',
            'data' => $recipe
        ]);
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

    /**
     * Create a new version for a recipe.
     */
    public function createVersion(Request $request, Recipe $recipe)
    {
        $this->authorize('update', $recipe);

        $validated = $request->validate([
            'ingredients' => 'required|array|min:1',
            'ingredients.*.raw_material_id' => 'required|exists:raw_materials,id',
            'ingredients.*.quantity_required' => 'required|numeric|min:0.01',
        ]);

        try {
            DB::beginTransaction();

            // Get next version number
            $lastVersion = $recipe->versions()->orderBy('version_number', 'desc')->first();
            $versionNumber = $lastVersion ? $lastVersion->version_number + 1 : 1;

            $recipeVersion = RecipeVersion::create([
                'recipe_id' => $recipe->id,
                'version_number' => $versionNumber,
                'created_by' => auth()->id(),
                'effective_date' => now(),
            ]);

            foreach ($validated['ingredients'] as $ingredient) {
                RecipeItem::create([
                    'recipe_version_id' => $recipeVersion->id,
                    'raw_material_id' => $ingredient['raw_material_id'],
                    'quantity_required' => $ingredient['quantity_required'],
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Recipe version created successfully',
                'data' => $recipeVersion->load('items.rawMaterial')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get a specific recipe version.
     */
    public function showVersion(Recipe $recipe, RecipeVersion $version)
    {
        $this->authorize('view', $recipe);

        if ($version->recipe_id !== $recipe->id) {
            return response()->json([
                'message' => 'Version does not belong to this recipe'
            ], 404);
        }

        $version->load('items.rawMaterial');

        return response()->json($version);
    }
}
