<?php

namespace App\Services;

use App\Models\{
    RecipeVersion,
    ProductionLog,
    ProductionMaterial,
    PreparedInventory,
    ProcurementItem,
    InventoryMovement,
    User
};
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductionService
{
    /**
     * Produce food items based on a recipe version
     */
    public function produce(
        int $recipeVersionId,
        int $quantityProduced,
        User $chef,
        bool $storeAsPrepared = false,
        ?string $preparedItemName = null,
        ?string $expiryDate = null
    ): ProductionLog {
        return DB::transaction(function () use ($recipeVersionId, $quantityProduced, $chef, $storeAsPrepared, $preparedItemName, $expiryDate) {
            $recipeVersion = RecipeVersion::with('items.rawMaterial')
                ->findOrFail($recipeVersionId);

            // 1. Create production log
            $production = ProductionLog::create([
                'recipe_version_id' => $recipeVersion->id,
                'section_id' => $recipeVersion->recipe->section_id,
                'chef_id' => $chef->id,
                'quantity_produced' => $quantityProduced,
                'production_date' => now(),
            ]);

            // 2. Consume raw materials FIFO per recipe item
            foreach ($recipeVersion->items as $recipeItem) {
                $requiredQty =
                    $recipeItem->quantity_required * $quantityProduced;

                $this->consumeMaterial(
                    $production,
                    $recipeItem->raw_material_id,
                    $requiredQty,
                    $chef
                );
            }

            // 3. Store as prepared inventory if applicable
            if ($storeAsPrepared) {
                if (!$preparedItemName) {
                    throw ValidationException::withMessages([
                        'prepared_item_name' => 'Prepared item name is required'
                    ]);
                }

                PreparedInventory::create([
                    'production_log_id' => $production->id,
                    'section_id' => $production->section_id,
                    'item_name' => $preparedItemName,
                    'quantity' => $quantityProduced,
                    'expiry_date' => $expiryDate,
                    'status' => 'available'
                ]);
            }

            return $production;
        });
    }

    /**
     * Consume raw material FIFO and lock cost
     */
    protected function consumeMaterial(
        ProductionLog $production,
        int $rawMaterialId,
        float $quantityNeeded,
        User $chef
    ): void {
        $remaining = $quantityNeeded;

        $batches = ProcurementItem::where('raw_material_id', $rawMaterialId)
            ->whereRaw('quantity > received_quantity')
            ->orderBy('created_at')
            ->lockForUpdate()
            ->get();

        if ($batches->isEmpty()) {
            throw ValidationException::withMessages([
                'raw_material' => 'No stock available for production'
            ]);
        }

        foreach ($batches as $batch) {
            if ($remaining <= 0)
                break;

            $available = $batch->quantity - $batch->received_quantity;
            $used = min($available, $remaining);

            // Lock cost at production time
            ProductionMaterial::create([
                'production_log_id' => $production->id,
                'raw_material_id' => $rawMaterialId,
                'procurement_item_id' => $batch->id,
                'quantity_used' => $used,
                'unit_cost' => $batch->unit_cost
            ]);

            // Inventory movement
            InventoryMovement::create([
                'raw_material_id' => $rawMaterialId,
                'procurement_item_id' => $batch->id,
                'from_location' => 'store',
                'to_location' => 'production',
                'quantity' => $used,
                'movement_type' => 'issue_to_chef',
                'performed_by' => $chef->id,
            ]);

            $batch->increment('received_quantity', $used);
            $remaining -= $used;
        }

        if ($remaining > 0) {
            throw ValidationException::withMessages([
                'raw_material' => 'Insufficient stock during production'
            ]);
        }
    }
}

//CONTROLLER
// public function produce(Request $request, ProductionService $service)
// {
//     $production = $service->produce(
//         recipeVersionId: $request->recipe_version_id,
//         quantityProduced: $request->quantity,
//         chef: auth()->user(),
//         storeAsPrepared: $request->store_as_prepared,
//         preparedItemName: $request->item_name,
//         expiryDate: $request->expiry_date
//     );

//     return response()->json([
//         'message' => 'Production logged successfully',
//         'production_id' => $production->id
//     ]);
// }
