<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Procurement;
use App\Models\ProcurementItem;
use App\Models\InventoryMovement;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProcurementController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Display a listing of procurements.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Procurement::class);

        $query = Procurement::with(['user', 'section', 'items.rawMaterial']);

        // Filter by supplier
        if ($request->has('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->where('purchase_date', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->where('purchase_date', '<=', $request->end_date);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $procurements = $query->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json($procurements);
    }

    /**
     * Store a newly created procurement.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Procurement::class);

        try {
            $validated = $request->validate([
                'supplier_id' => 'required|string|max:255',
                'section_id' => 'required|exists:sections,id',
                'purchase_date' => 'required|date',
                'items' => 'required|array|min:1',
                'items.*.raw_material_id' => 'required|exists:raw_materials,id',
                'items.*.quantity' => 'required|numeric|min:0.01',
                'items.*.unit_cost' => 'required|numeric|min:0',
                'items.*.quality_note' => 'nullable|string',
                'items.*.notes' => 'nullable|string',
                'items.*.expiry_date' => 'nullable|date|after:today',
            ]);
        } catch (ValidationException $e) {
            \Illuminate\Support\Facades\Log::error('Procurement Validation Failed', [
                'errors' => $e->errors(),
                'data' => $request->all()
            ]);
            throw $e;
        }

        try {
            DB::beginTransaction();

            // Determine status based on user role
            // Admin can create procurements that are immediately received
            // Procurement users create pending procurements that need approval
            $status = auth()->user()->isAdmin() ? 'received' : 'pending';

            // Create procurement
            $procurement = Procurement::create([
                'procurement_user_id' => auth()->id(),
                'supplier_id' => $validated['supplier_id'],
                'section_id' => $validated['section_id'],
                'purchase_date' => $validated['purchase_date'],
                'status' => $status,
            ]);

            // Create procurement items (batches)
            foreach ($validated['items'] as $item) {
                ProcurementItem::create([
                    'procurement_id' => $procurement->id,
                    'raw_material_id' => $item['raw_material_id'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'received_quantity' => 0, // Will be updated when approved/issued
                    'quality_note' => $item['quality_note'] ?? null,
                    'notes' => $item['notes'] ?? null,
                    'expiry_date' => $item['expiry_date'] ?? null,
                ]);
            }

            // If Admin created it as 'received', create inventory movements immediately
            if ($status === 'received') {
                foreach ($procurement->items as $item) {
                    InventoryMovement::create([
                        'raw_material_id' => $item->raw_material_id,
                        'procurement_item_id' => $item->id,
                        'from_location' => 'supplier',
                        'to_location' => 'store',
                        'quantity' => $item->quantity,
                        'movement_type' => 'procurement',
                        'performed_by' => auth()->id(),
                    ]);

                    // Update raw material quantity
                    \App\Models\RawMaterial::find($item->raw_material_id)
                        ->increment('current_quantity', $item->quantity);
                }
            } else {
                // If pending, notify admins/managers
                $approvers = \App\Models\User::whereHas('role', function ($q) {
                    $q->whereIn('name', ['Admin', 'Store Keeper']);
                })->get();

                foreach ($approvers as $approver) {
                    $this->notificationService->sendPendingApprovalAlert(
                        manager: $approver,
                        type: 'procurement',
                        id: $procurement->id,
                        requester: auth()->user(),
                        details: ['supplier' => $procurement->supplier_id]
                    );
                }
            }

            DB::commit();

            return response()->json([
                'message' => $status === 'received'
                    ? 'Procurement created and received successfully'
                    : 'Procurement created successfully. Awaiting approval.',
                'data' => $procurement->load(['items.rawMaterial'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Procurement creation failed: ' . $e->getMessage());
            \Illuminate\Support\Facades\Log::error($e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * Display the specified procurement.
     */
    public function show(Procurement $procurement)
    {
        $this->authorize('view', $procurement);

        $procurement->load(['user', 'section', 'items.rawMaterial']);

        return response()->json($procurement);
    }

    /**
     * Remove the specified procurement from storage.
     */
    public function destroy(Procurement $procurement)
    {
        $this->authorize('delete', $procurement);

        try {
            DB::beginTransaction();

            // If it was received, we need to revert inventory
            if ($procurement->status === 'received') {
                // Determine if we should revert stock
                // If we implemented robust locking, we would check if stock was already used
                // but for now we will revert based on what was added.
                // NOTE: If stock is now LESS than what we want to deduct (because it was consumed),
                // it might go negative. This is acceptable for correction purposes or we could check.
                // Assuming we force correction.

                foreach ($procurement->items as $item) {
                    // Revert RawMaterial stock
                    $material = \App\Models\RawMaterial::find($item->raw_material_id);
                    if ($material) {
                        $material->decrement('current_quantity', $item->quantity);
                    }

                    // Delete associated inventory movements to keep history clean of this "mistake"
                    // Or we could soft delete checks if we added that, but hard delete for movements is ok for now
                    // especially since the prompt implies "wrongly inputed"
                    InventoryMovement::where('procurement_item_id', $item->id)->delete();
                }
            }

            // Soft delete items
            $procurement->items()->delete();

            // Soft delete procurement
            $procurement->delete();

            DB::commit();

            return response()->json(['message' => 'Procurement deleted successfully']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to delete procurement: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Approve a pending procurement.
     */
    public function approve(Procurement $procurement)
    {
        $this->authorize('approve', $procurement);

        if ($procurement->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending procurements can be approved'
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Lock the procurement record
            $procurement = Procurement::lockForUpdate()->find($procurement->id);

            // Re-check status
            if ($procurement->status !== 'pending') {
                DB::rollBack();
                return response()->json([
                    'message' => 'Procurement is no longer pending (already approved/rejected?)'
                ], 400);
            }

            // Update procurement status
            $procurement->update([
                'status' => 'received',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            // Create inventory movements for all items
            foreach ($procurement->items as $item) {
                InventoryMovement::create([
                    'raw_material_id' => $item->raw_material_id,
                    'procurement_item_id' => $item->id,
                    'from_location' => 'supplier',
                    'to_location' => 'store',
                    'quantity' => $item->quantity,
                    'movement_type' => 'procurement',
                    'performed_by' => auth()->id(),
                ]);

                // Update raw material quantity
                \App\Models\RawMaterial::find($item->raw_material_id)
                    ->increment('current_quantity', $item->quantity);
            }

            // Notify creator
            if ($procurement->user) {
                $this->notificationService->sendApprovalStatusChanged(
                    requester: $procurement->user,
                    type: 'procurement',
                    id: $procurement->id,
                    status: 'approved',
                    approver: auth()->user()
                );
            }

            DB::commit();

            return response()->json([
                'message' => 'Procurement approved successfully',
                'data' => $procurement->fresh(['items.rawMaterial', 'approver'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Reject a pending procurement.
     */
    public function reject(Request $request, Procurement $procurement)
    {
        $this->authorize('approve', $procurement);

        if ($procurement->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending procurements can be rejected'
            ], 400);
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);

        $procurement->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        // Notify creator
        if ($procurement->user) {
            $this->notificationService->sendApprovalStatusChanged(
                requester: $procurement->user,
                type: 'procurement',
                id: $procurement->id,
                status: 'rejected',
                approver: auth()->user(),
                notes: $validated['rejection_reason']
            );
        }

        return response()->json([
            'message' => 'Procurement rejected',
            'data' => $procurement->fresh(['items.rawMaterial', 'approver'])
        ]);
    }
}
