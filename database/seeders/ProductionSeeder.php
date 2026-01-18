<?php

namespace Database\Seeders;

use App\Models\ProductionLog;
use App\Models\ProductionMaterial;
use App\Models\RecipeVersion;
use App\Models\ProcurementItem;
use App\Models\InventoryMovement;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ProductionSeeder extends Seeder
{
    public function run(): void
    {
        $chef = User::where('email', 'chef.eatery@inventory.com')->first();

        // Get all recipe versions
        $recipeVersions = RecipeVersion::with('items.rawMaterial')->get();

        if ($recipeVersions->isEmpty()) {
            return;
        }

        // Create 25 production logs over the past month
        for ($i = 0; $i < 25; $i++) {
            $recipeVersion = $recipeVersions->random();
            $productionDate = Carbon::now()->subDays(rand(1, 30));
            $quantityProduced = rand(5, 20); // Number of servings/units produced

            $production = ProductionLog::create([
                'recipe_version_id' => $recipeVersion->id,
                'section_id' => $recipeVersion->recipe->section_id,
                'chef_id' => $chef->id,
                'quantity_produced' => $quantityProduced,
                'production_date' => $productionDate
            ]);

            // Track materials used based on recipe items
            foreach ($recipeVersion->items as $recipeItem) {
                $quantityUsed = $recipeItem->quantity_required * $quantityProduced;

                // Get a procurement item for this raw material (for FIFO tracking)
                $procurementItem = ProcurementItem::where('raw_material_id', $recipeItem->raw_material_id)
                    ->where('received_quantity', '>', 0)
                    ->orderBy('created_at', 'asc')
                    ->first();

                if ($procurementItem) {
                    // Create production material record
                    ProductionMaterial::create([
                        'production_log_id' => $production->id,
                        'raw_material_id' => $recipeItem->raw_material_id,
                        'procurement_item_id' => $procurementItem->id,
                        'quantity_used' => $quantityUsed,
                        'unit_cost' => $procurementItem->unit_cost
                    ]);
                }
            }
        }
    }
}
