<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\RawMaterial;
use App\Models\ProcurementItem;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Display a listing of raw materials with current stock levels.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', RawMaterial::class);
        $query = RawMaterial::with(['section', 'batches']);

        // Filter by category
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        // Search by name
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $materials = $query->get()->map(function ($material) {
            $stockBalance = $this->inventoryService->getStockBalance($material->id);
            $isLowStock = $this->inventoryService->checkLowStock($material->id);

            return [
                'id' => $material->id,
                'name' => $material->name,
                'unit' => $material->unit,
                'category' => $material->category,
                'section' => $material->section,
                'current_stock' => $stockBalance,
                'min_quantity' => $material->min_quantity,
                'reorder_quantity' => $material->reorder_quantity,
                'is_low_stock' => $isLowStock,
                'supplier' => $material->preferred_supplier_id,
            ];
        });

        return response()->json($materials);
    }

    /**
     * Display the specified raw material with batch information.
     */
    public function show(RawMaterial $material)
    {
        $this->authorize('viewAny', RawMaterial::class);
        $stockBalance = $this->inventoryService->getStockBalance($material->id);
        $isLowStock = $this->inventoryService->checkLowStock($material->id);

        // Get available batches (FIFO order)
        $batches = ProcurementItem::where('raw_material_id', $material->id)
            ->whereRaw('quantity > received_quantity')
            ->orderBy('created_at')
            ->with('procurement')
            ->get()
            ->map(function ($batch) {
                return [
                    'id' => $batch->id,
                    'procurement_id' => $batch->procurement_id,
                    'supplier' => $batch->procurement->supplier_id ?? null,
                    'quantity' => $batch->quantity,
                    'received_quantity' => $batch->received_quantity,
                    'available' => $batch->quantity - $batch->received_quantity,
                    'unit_cost' => $batch->unit_cost,
                    'quality_note' => $batch->quality_note,
                    'expiry_date' => $batch->expiry_date,
                    'created_at' => $batch->created_at,
                ];
            });

        return response()->json([
            'material' => $material,
            'current_stock' => $stockBalance,
            'is_low_stock' => $isLowStock,
            'batches' => $batches,
        ]);
    }

    /**
     * Display movement history for a raw material.
     */
    public function movements(RawMaterial $material, Request $request)
    {
        $this->authorize('viewAny', RawMaterial::class);
        $query = $material->inventoryMovements()
            ->with(['performer', 'approver', 'batch']);

        // Filter by movement type
        if ($request->has('movement_type')) {
            $query->where('movement_type', $request->movement_type);
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->where('created_at', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->where('created_at', '<=', $request->end_date);
        }

        $movements = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json($movements);
    }

    /**
     * Get low stock items.
     */
    public function lowStock()
    {
        $this->authorize('viewAny', RawMaterial::class);
        $materials = RawMaterial::all()->filter(function ($material) {
            return $this->inventoryService->checkLowStock($material->id);
        })->map(function ($material) {
            $stockBalance = $this->inventoryService->getStockBalance($material->id);

            return [
                'id' => $material->id,
                'name' => $material->name,
                'unit' => $material->unit,
                'category' => $material->category,
                'current_stock' => $stockBalance,
                'min_quantity' => $material->min_quantity,
                'reorder_quantity' => $material->reorder_quantity,
                'deficit' => $material->min_quantity - $stockBalance,
                'supplier' => $material->preferred_supplier_id,
            ];
        })->values();

        return response()->json($materials);
    }

    /**
     * Get expiring items.
     */
    public function expiring(Request $request)
    {
        $this->authorize('viewAny', RawMaterial::class);
        $days = $request->get('days', 7);

        $expiringBatches = ProcurementItem::whereNotNull('expiry_date')
            ->where('expiry_date', '<=', now()->addDays($days))
            ->where('expiry_date', '>=', now())
            ->whereRaw('quantity > received_quantity')
            ->with(['rawMaterial', 'procurement'])
            ->orderBy('expiry_date')
            ->get()
            ->map(function ($batch) {
                return [
                    'raw_material' => $batch->rawMaterial->name,
                    'supplier' => $batch->procurement->supplier_id ?? null,
                    'available_quantity' => $batch->quantity - $batch->received_quantity,
                    'unit_cost' => $batch->unit_cost,
                    'expiry_date' => $batch->expiry_date,
                    'days_until_expiry' => now()->diffInDays($batch->expiry_date),
                ];
            });

        return response()->json($expiringBatches);
    }
    /**
     * Update an inventory movement (Admin only).
     */
    public function updateMovement(Request $request, \App\Models\InventoryMovement $movement)
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Only admins can edit movements'], 403);
        }

        $validated = $request->validate([
            'quantity' => 'required|numeric|min:0.01',
            'movement_type' => 'required|in:procurement,issue_to_chef,return_to_store,waste,sale,adjustment',
            'from_location' => 'nullable|string|max:255',
            'to_location' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $oldQuantity = (float) $movement->quantity;
            $oldType = $movement->movement_type;
            $newQuantity = (float) $validated['quantity'];
            $newType = $validated['movement_type'];

            // Adjust batch received_quantity if movement is linked to a batch
            if ($movement->procurement_item_id) {
                $batch = ProcurementItem::find($movement->procurement_item_id);
                if ($batch) {
                    // Reverse old effect on batch
                    $outTypes = ['issue_to_chef', 'waste', 'sale'];
                    if (in_array($oldType, $outTypes)) {
                        $batch->decrement('received_quantity', $oldQuantity);
                    }

                    // Apply new effect on batch
                    if (in_array($newType, $outTypes)) {
                        $batch->increment('received_quantity', $newQuantity);
                    }
                }
            }

            $movement->update([
                'quantity' => $newQuantity,
                'movement_type' => $newType,
                'from_location' => $validated['from_location'] ?? $movement->from_location,
                'to_location' => $validated['to_location'] ?? $movement->to_location,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Movement updated successfully',
                'data' => $movement->fresh(['performer', 'approver', 'batch'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete an inventory movement (Admin only).
     */
    public function destroyMovement(\App\Models\InventoryMovement $movement)
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Only admins can delete movements'], 403);
        }

        try {
            DB::beginTransaction();

            // Reverse batch received_quantity if movement is linked to a batch
            if ($movement->procurement_item_id) {
                $batch = ProcurementItem::find($movement->procurement_item_id);
                if ($batch) {
                    $outTypes = ['issue_to_chef', 'waste', 'sale'];
                    if (in_array($movement->movement_type, $outTypes)) {
                        $batch->decrement('received_quantity', $movement->quantity);
                    }
                }
            }

            $movement->delete();

            DB::commit();

            return response()->json([
                'message' => 'Movement deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
