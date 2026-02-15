<?php

namespace App\Services;

use App\Models\{
    RawMaterial,
    ProcurementItem,
    InventoryMovement,
    User
};
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    /**
     * Get current stock balance for a raw material
     */
    public function getStockBalance(int $rawMaterialId): float
    {
        return InventoryMovement::where('raw_material_id', $rawMaterialId)
            ->sum(DB::raw("
                CASE
                    WHEN movement_type IN ('procurement', 'return_to_store') THEN quantity
                    WHEN movement_type IN ('issue_to_chef', 'waste', 'sale') THEN -quantity
                    ELSE 0
                END
            "));
    }

    /**
     * Issue raw material using FIFO batches
     */
    public function issueToChef(
        int $rawMaterialId,
        float $quantity,
        string $toLocation,
        User $performedBy,
        ?User $approvedBy = null,
        ?int $referenceId = null
    ): void {
        // NOTE: Caller MUST wrap this in a DB::transaction() 
        // Do NOT add DB::transaction() here — it creates nested savepoints
        // that can independently commit/rollback, causing data corruption

        $availableStock = $this->getStockBalance($rawMaterialId);

        if ($availableStock < $quantity) {
            $material = RawMaterial::find($rawMaterialId);
            throw ValidationException::withMessages([
                'quantity' => "Insufficient stock for {$material->name}: requested {$quantity}, available {$availableStock}"
            ]);
        }

        $remaining = $quantity;

        $batches = ProcurementItem::where('raw_material_id', $rawMaterialId)
            ->whereRaw('quantity > received_quantity')
            ->orderBy('created_at')
            ->lockForUpdate()
            ->get();

        foreach ($batches as $batch) {
            if ($remaining <= 0)
                break;

            $availableInBatch = $batch->quantity - $batch->received_quantity;
            $used = min($availableInBatch, $remaining);

            InventoryMovement::create([
                'raw_material_id' => $rawMaterialId,
                'procurement_item_id' => $batch->id,
                'from_location' => 'store',
                'to_location' => $toLocation,
                'quantity' => $used,
                'movement_type' => 'issue_to_chef',
                'reference_id' => $referenceId,
                'performed_by' => $performedBy->id,
                'approved_by' => $approvedBy?->id,
            ]);

            $batch->increment('received_quantity', $used);
            $remaining -= $used;
        }

        if ($remaining > 0) {
            throw ValidationException::withMessages([
                'quantity' => "Data integrity error: System indicates stock exists ({$availableStock}), but valid batches could not be found to fulfill request. Discrepancy: {$remaining}."
            ]);
        }
    }

    /**
     * Return unused material to store
     */
    public function returnToStore(
        int $rawMaterialId,
        float $quantity,
        User $performedBy,
        User $approvedBy
    ): void {
        DB::transaction(function () use ($rawMaterialId, $quantity, $performedBy, $approvedBy) {
            InventoryMovement::create([
                'raw_material_id' => $rawMaterialId,
                'quantity' => $quantity,
                'movement_type' => 'return_to_store',
                'from_location' => 'chef',
                'to_location' => 'store',
                'performed_by' => $performedBy->id,
                'approved_by' => $approvedBy->id,
            ]);

            // Restore capacity to batches (LIFO - typically returning what was just taken)
            $remainingToRestore = $quantity;

            $batches = ProcurementItem::where('raw_material_id', $rawMaterialId)
                ->where('received_quantity', '>', 0)
                ->orderBy('created_at', 'desc') // Newest batches first
                ->lockForUpdate()
                ->get();

            foreach ($batches as $batch) {
                if ($remainingToRestore <= 0)
                    break;

                // We can decrement up to the amount currently 'received' (issued)
                $restorable = $batch->received_quantity;
                $restoreAmount = min($restorable, $remainingToRestore);

                $batch->decrement('received_quantity', $restoreAmount);
                $remainingToRestore -= $restoreAmount;
            }

            // If remainingToRestore > 0 here, it means we are returning more than was ever issued from untracked sources? 
            // For now, we allow the Movement to record it (system stock increases), 
            // even if we can't credit a specific batch (maybe legacy data).
        });
    }

    /**
     * Log waste
     */
    public function logWaste(
        int $rawMaterialId,
        float $quantity,
        string $reason,
        User $performedBy,
        User $approvedBy
    ): void {
        InventoryMovement::create([
            'raw_material_id' => $rawMaterialId,
            'quantity' => $quantity,
            'movement_type' => 'waste',
            'from_location' => 'store',
            'to_location' => null,
            'performed_by' => $performedBy->id,
            'approved_by' => $approvedBy->id,
        ]);
    }

    /**
     * Check and trigger low stock alert
     */
    public function checkLowStock(int $rawMaterialId): bool
    {
        $material = RawMaterial::findOrFail($rawMaterialId);
        $balance = $this->getStockBalance($rawMaterialId);

        return $balance <= $material->min_quantity;
    }
}

//CONTROLLER
// public function issue(Request $request, InventoryService $inventory)
// {
//     $inventory->issueToChef(
//         rawMaterialId: $request->raw_material_id,
//         quantity: $request->quantity,
//         toLocation: 'eatery',
//         performedBy: auth()->user(),
//         approvedBy: User::find($request->approved_by)
//     );

//     return response()->json(['message' => 'Issued successfully']);
// }
