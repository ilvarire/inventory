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

        $chef = User::where('email', 'chef.eatery@inventory.com')->first();

        // Recipe 1: Grilled Chicken Breast
        $recipe1 = Recipe::create([
            'name' => 'Grilled Chicken Breast',
            'section_id' => $grills->id,
            'created_by' => $chef->id,
            'status' => 'active'
        ]);

        $version1 = RecipeVersion::create([
            'recipe_id' => $recipe1->id,
            'version_number' => 1,
            'created_by' => $chef->id,
            'effective_date' => Carbon::now()->subMonths(2)
        ]);

        RecipeItem::create(['recipe_version_id' => $version1->id, 'raw_material_id' => 1, 'quantity_required' => 0.25]); // Chicken Breast
        RecipeItem::create(['recipe_version_id' => $version1->id, 'raw_material_id' => 20, 'quantity_required' => 0.02]); // Olive Oil
        RecipeItem::create(['recipe_version_id' => $version1->id, 'raw_material_id' => 21, 'quantity_required' => 0.005]); // Salt
        RecipeItem::create(['recipe_version_id' => $version1->id, 'raw_material_id' => 22, 'quantity_required' => 0.003]); // Black Pepper
        RecipeItem::create(['recipe_version_id' => $version1->id, 'raw_material_id' => 23, 'quantity_required' => 0.01]); // Garlic

        // Recipe 2: Jollof Rice
        $recipe2 = Recipe::create([
            'name' => 'Jollof Rice with Chicken',
            'section_id' => $eatery->id,
            'created_by' => $chef->id,
            'status' => 'active'
        ]);

        $version2 = RecipeVersion::create([
            'recipe_id' => $recipe2->id,
            'version_number' => 1,
            'created_by' => $chef->id,
            'effective_date' => Carbon::now()->subMonths(3)
        ]);

        RecipeItem::create(['recipe_version_id' => $version2->id, 'raw_material_id' => 11, 'quantity_required' => 0.3]); // Rice
        RecipeItem::create(['recipe_version_id' => $version2->id, 'raw_material_id' => 1, 'quantity_required' => 0.15]); // Chicken
        RecipeItem::create(['recipe_version_id' => $version2->id, 'raw_material_id' => 6, 'quantity_required' => 0.1]); // Tomatoes
        RecipeItem::create(['recipe_version_id' => $version2->id, 'raw_material_id' => 7, 'quantity_required' => 0.05]); // Onions
        RecipeItem::create(['recipe_version_id' => $version2->id, 'raw_material_id' => 8, 'quantity_required' => 0.05]); // Bell Peppers
        RecipeItem::create(['recipe_version_id' => $version2->id, 'raw_material_id' => 19, 'quantity_required' => 0.03]); // Vegetable Oil

        // Recipe 3: Beef Steak
        $recipe3 = Recipe::create([
            'name' => 'Beef Steak',
            'section_id' => $grills->id,
            'created_by' => $chef->id,
            'status' => 'active'
        ]);

        $version3 = RecipeVersion::create([
            'recipe_id' => $recipe3->id,
            'version_number' => 1,
            'created_by' => $chef->id,
            'effective_date' => Carbon::now()->subMonths(2)
        ]);

        RecipeItem::create(['recipe_version_id' => $version3->id, 'raw_material_id' => 2, 'quantity_required' => 0.3]); // Beef
        RecipeItem::create(['recipe_version_id' => $version3->id, 'raw_material_id' => 17, 'quantity_required' => 0.02]); // Butter
        RecipeItem::create(['recipe_version_id' => $version3->id, 'raw_material_id' => 21, 'quantity_required' => 0.005]); // Salt
        RecipeItem::create(['recipe_version_id' => $version3->id, 'raw_material_id' => 22, 'quantity_required' => 0.003]); // Black Pepper
        RecipeItem::create(['recipe_version_id' => $version3->id, 'raw_material_id' => 23, 'quantity_required' => 0.01]); // Garlic

        // Recipe 4: Salmon Fillet
        $recipe4 = Recipe::create([
            'name' => 'Grilled Salmon Fillet',
            'section_id' => $grills->id,
            'created_by' => $chef->id,
            'status' => 'active'
        ]);

        $version4 = RecipeVersion::create([
            'recipe_id' => $recipe4->id,
            'version_number' => 1,
            'created_by' => $chef->id,
            'effective_date' => Carbon::now()->subMonths(1)
        ]);

        RecipeItem::create(['recipe_version_id' => $version4->id, 'raw_material_id' => 3, 'quantity_required' => 0.25]); // Salmon
        RecipeItem::create(['recipe_version_id' => $version4->id, 'raw_material_id' => 20, 'quantity_required' => 0.02]); // Olive Oil
        RecipeItem::create(['recipe_version_id' => $version4->id, 'raw_material_id' => 21, 'quantity_required' => 0.005]); // Salt
        RecipeItem::create(['recipe_version_id' => $version4->id, 'raw_material_id' => 22, 'quantity_required' => 0.003]); // Black Pepper

        // Recipe 5: Chicken Soup
        $recipe5 = Recipe::create([
            'name' => 'Chicken Soup',
            'section_id' => $eatery->id,
            'created_by' => $chef->id,
            'status' => 'active'
        ]);

        $version5 = RecipeVersion::create([
            'recipe_id' => $recipe5->id,
            'version_number' => 1,
            'created_by' => $chef->id,
            'effective_date' => Carbon::now()->subMonths(2)
        ]);

        RecipeItem::create(['recipe_version_id' => $version5->id, 'raw_material_id' => 1, 'quantity_required' => 0.2]); // Chicken
        RecipeItem::create(['recipe_version_id' => $version5->id, 'raw_material_id' => 7, 'quantity_required' => 0.05]); // Onions
        RecipeItem::create(['recipe_version_id' => $version5->id, 'raw_material_id' => 10, 'quantity_required' => 0.05]); // Carrots
        RecipeItem::create(['recipe_version_id' => $version5->id, 'raw_material_id' => 21, 'quantity_required' => 0.005]); // Salt
        RecipeItem::create(['recipe_version_id' => $version5->id, 'raw_material_id' => 22, 'quantity_required' => 0.003]); // Black Pepper

        // Recipe 6: Chocolate Cake
        $recipe6 = Recipe::create([
            'name' => 'Chocolate Cake',
            'section_id' => $cafe->id,
            'created_by' => $chef->id,
            'status' => 'active'
        ]);

        $version6 = RecipeVersion::create([
            'recipe_id' => $recipe6->id,
            'version_number' => 1,
            'created_by' => $chef->id,
            'effective_date' => Carbon::now()->subMonths(3)
        ]);

        RecipeItem::create(['recipe_version_id' => $version6->id, 'raw_material_id' => 12, 'quantity_required' => 0.25]); // Flour
        RecipeItem::create(['recipe_version_id' => $version6->id, 'raw_material_id' => 27, 'quantity_required' => 0.2]); // Sugar
        RecipeItem::create(['recipe_version_id' => $version6->id, 'raw_material_id' => 28, 'quantity_required' => 0.1]); // Chocolate
        RecipeItem::create(['recipe_version_id' => $version6->id, 'raw_material_id' => 4, 'quantity_required' => 3]); // Eggs (pieces)
        RecipeItem::create(['recipe_version_id' => $version6->id, 'raw_material_id' => 17, 'quantity_required' => 0.1]); // Butter
        RecipeItem::create(['recipe_version_id' => $version6->id, 'raw_material_id' => 15, 'quantity_required' => 0.15]); // Milk

        // Recipe 7: Fresh Orange Juice (with version history)
        $recipe7 = Recipe::create([
            'name' => 'Fresh Orange Juice',
            'section_id' => $cafe->id,
            'created_by' => $chef->id,
            'status' => 'active'
        ]);

        // Version 1 (old)
        $version7_1 = RecipeVersion::create([
            'recipe_id' => $recipe7->id,
            'version_number' => 1,
            'created_by' => $chef->id,
            'effective_date' => Carbon::now()->subMonths(4)
        ]);
        RecipeItem::create(['recipe_version_id' => $version7_1->id, 'raw_material_id' => 26, 'quantity_required' => 0.3]); // Orange Juice
        RecipeItem::create(['recipe_version_id' => $version7_1->id, 'raw_material_id' => 27, 'quantity_required' => 0.02]); // Sugar

        // Version 2 (current - reduced sugar)
        $version7_2 = RecipeVersion::create([
            'recipe_id' => $recipe7->id,
            'version_number' => 2,
            'created_by' => $chef->id,
            'effective_date' => Carbon::now()->subMonth()
        ]);
        RecipeItem::create(['recipe_version_id' => $version7_2->id, 'raw_material_id' => 26, 'quantity_required' => 0.35]); // Orange Juice (increased)
        RecipeItem::create(['recipe_version_id' => $version7_2->id, 'raw_material_id' => 27, 'quantity_required' => 0.01]); // Sugar (reduced)

        // Recipe 8: Fried Rice
        $recipe8 = Recipe::create([
            'name' => 'Fried Rice',
            'section_id' => $eatery->id,
            'created_by' => $chef->id,
            'status' => 'active'
        ]);

        $version8 = RecipeVersion::create([
            'recipe_id' => $recipe8->id,
            'version_number' => 1,
            'created_by' => $chef->id,
            'effective_date' => Carbon::now()->subMonths(2)
        ]);

        RecipeItem::create(['recipe_version_id' => $version8->id, 'raw_material_id' => 11, 'quantity_required' => 0.3]); // Rice
        RecipeItem::create(['recipe_version_id' => $version8->id, 'raw_material_id' => 4, 'quantity_required' => 2]); // Eggs
        RecipeItem::create(['recipe_version_id' => $version8->id, 'raw_material_id' => 7, 'quantity_required' => 0.05]); // Onions
        RecipeItem::create(['recipe_version_id' => $version8->id, 'raw_material_id' => 10, 'quantity_required' => 0.05]); // Carrots
        RecipeItem::create(['recipe_version_id' => $version8->id, 'raw_material_id' => 19, 'quantity_required' => 0.03]); // Vegetable Oil

        // Recipe 9: Seafood Chowder
        $recipe9 = Recipe::create([
            'name' => 'Seafood Chowder',
            'section_id' => $eatery->id,
            'created_by' => $chef->id,
            'status' => 'active'
        ]);

        $version9 = RecipeVersion::create([
            'recipe_id' => $recipe9->id,
            'version_number' => 1,
            'created_by' => $chef->id,
            'effective_date' => Carbon::now()->subMonths(1)
        ]);

        RecipeItem::create(['recipe_version_id' => $version9->id, 'raw_material_id' => 5, 'quantity_required' => 0.15]); // Shrimp
        RecipeItem::create(['recipe_version_id' => $version9->id, 'raw_material_id' => 3, 'quantity_required' => 0.1]); // Salmon
        RecipeItem::create(['recipe_version_id' => $version9->id, 'raw_material_id' => 18, 'quantity_required' => 0.2]); // Cream
        RecipeItem::create(['recipe_version_id' => $version9->id, 'raw_material_id' => 7, 'quantity_required' => 0.05]); // Onions
        RecipeItem::create(['recipe_version_id' => $version9->id, 'raw_material_id' => 17, 'quantity_required' => 0.02]); // Butter

        // Recipe 10: Spring Rolls (with version history)
        $recipe10 = Recipe::create([
            'name' => 'Spring Rolls',
            'section_id' => $eatery->id,
            'created_by' => $chef->id,
            'status' => 'active'
        ]);

        // Version 1 (old)
        $version10_1 = RecipeVersion::create([
            'recipe_id' => $recipe10->id,
            'version_number' => 1,
            'created_by' => $chef->id,
            'effective_date' => Carbon::now()->subMonths(5)
        ]);
        RecipeItem::create(['recipe_version_id' => $version10_1->id, 'raw_material_id' => 10, 'quantity_required' => 0.05]); // Carrots
        RecipeItem::create(['recipe_version_id' => $version10_1->id, 'raw_material_id' => 7, 'quantity_required' => 0.03]); // Onions
        RecipeItem::create(['recipe_version_id' => $version10_1->id, 'raw_material_id' => 12, 'quantity_required' => 0.1]); // Flour

        // Version 2 (current - added vegetables)
        $version10_2 = RecipeVersion::create([
            'recipe_id' => $recipe10->id,
            'version_number' => 2,
            'created_by' => $chef->id,
            'effective_date' => Carbon::now()->subWeeks(3)
        ]);
        RecipeItem::create(['recipe_version_id' => $version10_2->id, 'raw_material_id' => 10, 'quantity_required' => 0.05]); // Carrots
        RecipeItem::create(['recipe_version_id' => $version10_2->id, 'raw_material_id' => 7, 'quantity_required' => 0.03]); // Onions
        RecipeItem::create(['recipe_version_id' => $version10_2->id, 'raw_material_id' => 8, 'quantity_required' => 0.03]); // Bell Peppers (new)
        RecipeItem::create(['recipe_version_id' => $version10_2->id, 'raw_material_id' => 12, 'quantity_required' => 0.1]); // Flour

        // Recipe 11: Coffee (Café specialty)
        $recipe11 = Recipe::create([
            'name' => 'Espresso Coffee',
            'section_id' => $cafe->id,
            'created_by' => $chef->id,
            'status' => 'active'
        ]);

        $version11 = RecipeVersion::create([
            'recipe_id' => $recipe11->id,
            'version_number' => 1,
            'created_by' => $chef->id,
            'effective_date' => Carbon::now()->subMonths(4)
        ]);

        RecipeItem::create(['recipe_version_id' => $version11->id, 'raw_material_id' => 24, 'quantity_required' => 0.02]); // Coffee Beans
        RecipeItem::create(['recipe_version_id' => $version11->id, 'raw_material_id' => 27, 'quantity_required' => 0.005]); // Sugar

        // Recipe 12: Chicken Wings
        $recipe12 = Recipe::create([
            'name' => 'Spicy Chicken Wings',
            'section_id' => $lounge->id,
            'created_by' => $chef->id,
            'status' => 'active'
        ]);

        $version12 = RecipeVersion::create([
            'recipe_id' => $recipe12->id,
            'version_number' => 1,
            'created_by' => $chef->id,
            'effective_date' => Carbon::now()->subMonths(2)
        ]);

        RecipeItem::create(['recipe_version_id' => $version12->id, 'raw_material_id' => 1, 'quantity_required' => 0.3]); // Chicken
        RecipeItem::create(['recipe_version_id' => $version12->id, 'raw_material_id' => 19, 'quantity_required' => 0.05]); // Vegetable Oil
        RecipeItem::create(['recipe_version_id' => $version12->id, 'raw_material_id' => 21, 'quantity_required' => 0.005]); // Salt
        RecipeItem::create(['recipe_version_id' => $version12->id, 'raw_material_id' => 22, 'quantity_required' => 0.005]); // Black Pepper
        RecipeItem::create(['recipe_version_id' => $version12->id, 'raw_material_id' => 23, 'quantity_required' => 0.01]); // Garlic
    }
}
