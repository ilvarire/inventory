<?php

namespace Database\Seeders;

use App\Models\Procurement;
use App\Models\ProcurementItem;
use App\Models\InventoryMovement;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ProcurementSeeder extends Seeder
{
    public function run(): void
    {
        $procurementUserId = 3; // Procurement user

        // Create 10 procurement records over the past month
        for ($i = 0; $i < 10; $i++) {
            $procurement = Procurement::create([
                'procurement_user_id' => $procurementUserId,
                'supplier_id' => rand(1, 6), // Random supplier
                'purchase_date' => Carbon::now()->subDays(rand(1, 30)),
                'status' => 'completed',
            ]);

            // Add 2-5 items per procurement
            $itemCount = rand(2, 5);
            for ($j = 0; $j < $itemCount; $j++) {
                $rawMaterialId = rand(1, 29);
                $quantity = rand(10, 100);
                $unitCost = rand(100, 1000);

                $item = ProcurementItem::create([
                    'procurement_id' => $procurement->id,
                    'raw_material_id' => $rawMaterialId,
                    'quantity' => $quantity,
                    'unit_cost' => $unitCost,
                    'received_quantity' => $quantity,
                    'quality_note' => 'Good quality',
                    'expiry_date' => Carbon::now()->addMonths(rand(1, 6)),
                ]);

                // Create inventory movement
                InventoryMovement::create([
                    'raw_material_id' => $rawMaterialId,
                    'procurement_item_id' => $item->id,
                    'from_location' => 'supplier',
                    'to_location' => 'store',
                    'quantity' => $quantity,
                    'movement_type' => 'procurement',
                    'performed_by' => $procurementUserId,
                    'created_at' => $procurement->purchase_date,
                ]);
            }
        }
    }
}
