<?php

namespace Database\Seeders;

use App\Models\Recipe;
use App\Models\RecipeVersion;
use App\Models\RecipeItem;
use App\Models\Section;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class RecipeSeeder extends Seeder
{
    public function run(): void
    {
        // Get sections and chef users
        $eatery = Section::where('name', 'Eatery')->first();
        $cafe = Section::where('name', 'Café')->first();
        $lounge = Section::where('name', 'Lounge')->first();
        $grills = Section::where('name', 'Grills')->first();

        // Get chef users - assuming they exist from UserSeeder
        $chefEatery = User::where('email', 'chef.eatery@inventory.com')->first();
        $chefCafe = User::where('email', 'chef.cafe@inventory.com')->first();
        $chefGrills = User::where('email', 'chef.grills@inventory.com')->first();

        $chef = $chefEatery; // Fallback

        if (!$eatery || !$chef)
            return;

        // Recipe 1: Grilled Chicken Breast
        $recipe1 = Recipe::create([
            'name' => 'Grilled Chicken Breast',
            'section_id' => $grills->id ?? $eatery->id,
            'created_by' => $chefGrills->id ?? $chef->id,
            'status' => 'active',
            'expected_yield' => 1,
            'yield_unit' => 'plate',
            'selling_price' => 2500,
            'instructions' => 'Grill chicken until golden brown.'
        ]);

        RecipeItem::create(['recipe_id' => $recipe1->id, 'raw_material_id' => 1, 'quantity_required' => 0.25]); // Chicken Breast
        RecipeItem::create(['recipe_id' => $recipe1->id, 'raw_material_id' => 20, 'quantity_required' => 0.02]); // Olive Oil
        RecipeItem::create(['recipe_id' => $recipe1->id, 'raw_material_id' => 21, 'quantity_required' => 0.005]); // Salt

        // Recipe 2: Jollof Rice
        $recipe2 = Recipe::create([
            'name' => 'Jollof Rice',
            'section_id' => $eatery->id,
            'created_by' => $chefEatery->id ?? $chef->id,
            'status' => 'active',
            'expected_yield' => 5,
            'yield_unit' => 'portions',
            'selling_price' => 1500,
            'instructions' => 'Cook rice in tomato stew base.'
        ]);

        RecipeItem::create(['recipe_id' => $recipe2->id, 'raw_material_id' => 11, 'quantity_required' => 1.0]); // Rice
        RecipeItem::create(['recipe_id' => $recipe2->id, 'raw_material_id' => 6, 'quantity_required' => 0.5]); // Tomatoes
        RecipeItem::create(['recipe_id' => $recipe2->id, 'raw_material_id' => 7, 'quantity_required' => 0.2]); // Onions
        RecipeItem::create(['recipe_id' => $recipe2->id, 'raw_material_id' => 19, 'quantity_required' => 0.1]); // Oil

        // Recipe 3: Chocolate Cake
        $recipe3 = Recipe::create([
            'name' => 'Chocolate Cake',
            'section_id' => $cafe->id ?? $eatery->id,
            'created_by' => $chefCafe->id ?? $chef->id,
            'status' => 'active',
            'expected_yield' => 8,
            'yield_unit' => 'slices',
            'selling_price' => 1200,
            'instructions' => 'Bake at 180 degrees for 45 mins.'
        ]);

        RecipeItem::create(['recipe_id' => $recipe3->id, 'raw_material_id' => 12, 'quantity_required' => 0.5]); // Flour
        RecipeItem::create(['recipe_id' => $recipe3->id, 'raw_material_id' => 27, 'quantity_required' => 0.3]); // Sugar
        RecipeItem::create(['recipe_id' => $recipe3->id, 'raw_material_id' => 4, 'quantity_required' => 4]); // Eggs
    }
}
