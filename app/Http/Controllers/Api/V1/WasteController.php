<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\WasteLog;
use App\Models\RawMaterial;
use App\Models\PreparedInventory;
use App\Models\InventoryMovement;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class WasteController extends Controller
{
    /**
     * Display a listing of waste logs.
     */
    public function index(Request $request)
    {
        // Authorization handled by route middleware

        $query = WasteLog::with(['section', 'rawMaterial', 'preparedItem', 'loggedBy', 'approvedBy']);

        // Filter by section
        if ($request->has('section_id')) {
            $query->where('section_id', $request->section_id);
        }

        // Filter by reason
        if ($request->has('reason')) {
            $query->where('reason', $request->reason);
        }

        // Filter by date range - use whereDate for proper date comparison
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Filter by approval status
        if ($request->has('approved')) {
            if ($request->approved === 'true') {
                $query->whereNotNull('approved_by');
            } else {
                $query->whereNull('approved_by');
            }
        }

        $wasteLogs = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json($wasteLogs);
    }

    /**
     * Store a newly created waste log.
     */
    public function store(Request $request)
    {
        // Authorization handled by route middleware

        // Determine waste type based on user role
        $user = auth()->user();
        $wasteType = null;

        if ($user->role->name === 'Procurement' || $user->role->name === 'Store Keeper') {
            $wasteType = 'raw_material';
        } elseif ($user->role->name === 'Chef') {
            $wasteType = 'prepared_food';
        } else {
            // Admin/Manager can log both types, use the provided waste_type
            $wasteType = $request->input('waste_type', 'raw_material');
        }

        // Build validation rules based on waste type
        $rules = [
            'section_id' => 'required|exists:sections,id',
            'quantity' => 'required|numeric|min:0.01',
            'reason' => 'required|string|in:spoilage,expiry,damage,handling_error,other',
            'notes' => 'nullable|string|max:500',
        ];

        if ($wasteType === 'raw_material') {
            $rules['raw_material_id'] = 'required|exists:raw_materials,id';
        } else {
            $rules['prepared_inventory_id'] = 'required|exists:prepared_inventories,id';
        }

        $validated = $request->validate($rules);

        // Calculate cost amount
        $costAmount = 0;

        if ($wasteType === 'raw_material') {
            $rawMaterial = RawMaterial::findOrFail($request->raw_material_id);
            // Get average cost from recent procurements
            $avgCost = \DB::table('procurement_items')
                ->where('raw_material_id', $rawMaterial->id)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->avg('unit_cost');
            $costAmount = $validated['quantity'] * ($avgCost ?? 0);
        } else {
            // For prepared food, get selling price
            $preparedItem = PreparedInventory::findOrFail($request->prepared_inventory_id);
            $costAmount = $validated['quantity'] * ($preparedItem->selling_price ?? 0);
        }

        \DB::beginTransaction();
        try {
            // Create waste log with auto-approved status
            $wasteLog = WasteLog::create([
                'raw_material_id' => $wasteType === 'raw_material' ? $request->raw_material_id : null,
                'production_log_id' => $wasteType === 'prepared_food' ? $request->prepared_inventory_id : null,
                'section_id' => $validated['section_id'],
                'quantity' => $validated['quantity'],
                'reason' => $validated['reason'],
                'cost_amount' => $costAmount,
                'logged_by' => auth()->id(),
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            // Immediately update inventory quantities
            if ($wasteType === 'raw_material') {

                // Create inventory movement record
                InventoryMovement::create([
                    'raw_material_id' => $request->raw_material_id,
                    'from_location' => 'store',
                    'to_location' => 'waste',
                    'quantity' => $validated['quantity'],
                    'movement_type' => 'waste',
                    'performed_by' => auth()->id(),
                ]);
            } else {
                // Decrement prepared inventory quantity
                PreparedInventory::where('id', $request->prepared_inventory_id)
                    ->decrement('quantity', $validated['quantity']);
            }

            \DB::commit();

            return response()->json([
                'message' => 'Waste logged successfully. Inventory updated.',
                'data' => $wasteLog->load(['rawMaterial', 'section', 'loggedBy'])
            ], 201);
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['message' => 'Failed to log waste: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified waste log.
     */
    public function show(WasteLog $waste)
    {
        // Authorization handled by route middleware

        $waste->load(['section', 'rawMaterial', 'preparedItem', 'loggedBy', 'approvedBy']);

        return response()->json($waste);
    }

    /**
     * Approve a waste log.
     */
    public function approve(WasteLog $waste)
    {
        // Authorization handled by route middleware

        if ($waste->status === 'approved') {
            return response()->json(['message' => 'Waste log already approved'], 400);
        }

        if ($waste->status === 'rejected') {
            return response()->json(['message' => 'Cannot approve a rejected waste log'], 400);
        }

        \DB::beginTransaction();
        try {
            // Update waste log status
            $waste->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            // Update inventory
            if ($waste->raw_material_id) {

                // Create inventory movement record
                InventoryMovement::create([
                    'raw_material_id' => $waste->raw_material_id,
                    'from_location' => 'store',
                    'to_location' => 'waste',
                    'quantity' => $waste->quantity,
                    'movement_type' => 'waste',
                    'performed_by' => auth()->id(),
                ]);
            } elseif ($waste->production_log_id) {
                // Decrement prepared inventory quantity
                PreparedInventory::where('production_log_id', $waste->production_log_id)
                    ->decrement('quantity', $waste->quantity);
            }

            \DB::commit();

            return response()->json([
                'message' => 'Waste approved successfully. Inventory updated.',
                'data' => $waste->load(['rawMaterial', 'section', 'loggedBy', 'approvedBy'])
            ]);
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['message' => 'Failed to approve waste: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Reject a waste log.
     */
    public function reject(Request $request, WasteLog $waste)
    {
        // Authorization handled by route middleware

        if ($waste->status === 'approved') {
            return response()->json(['message' => 'Cannot reject an approved waste log'], 400);
        }

        if ($waste->status === 'rejected') {
            return response()->json(['message' => 'Waste log already rejected'], 400);
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $waste->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        return response()->json([
            'message' => 'Waste log rejected successfully',
            'data' => $waste->load(['rawMaterial', 'section', 'loggedBy'])
        ]);
    }
}
