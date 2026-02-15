<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\MaterialRequest;
use App\Models\MaterialRequestItem;
use App\Models\InventoryMovement;
use App\Services\InventoryService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MaterialRequestController extends Controller
{
    protected InventoryService $inventoryService;
    protected NotificationService $notificationService;

    public function __construct(InventoryService $inventoryService, NotificationService $notificationService)
    {
        $this->inventoryService = $inventoryService;
        $this->notificationService = $notificationService;
    }

    /**
     * Display a listing of material requests.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', MaterialRequest::class);

        $user = auth()->user();
        $query = MaterialRequest::with(['chef', 'section', 'approver', 'fulfiller', 'items.rawMaterial']);

        // Chef can only see their own requests
        if ($user->isChef()) {
            $query->where('chef_id', $user->id);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by section
        if ($request->has('section_id')) {
            $query->where('section_id', $request->section_id);
        }

        $materialRequests = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json($materialRequests);
    }

    /**
     * Store a newly created material request.
     */
    public function store(Request $request)
    {
        $this->authorize('create', MaterialRequest::class);

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.raw_material_id' => 'required|distinct|exists:raw_materials,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ]);

        try {
            DB::beginTransaction();

            $materialRequest = MaterialRequest::create([
                'chef_id' => auth()->id(),
                'section_id' => auth()->user()->section_id,
                'status' => 'pending',
            ]);

            foreach ($validated['items'] as $item) {
                MaterialRequestItem::create([
                    'material_request_id' => $materialRequest->id,
                    'raw_material_id' => $item['raw_material_id'],
                    'quantity' => $item['quantity'],
                ]);
            }

            // Notify admins/managers/store keepers
            $approvers = \App\Models\User::whereHas('role', function ($q) {
                $q->whereIn('name', ['Admin', 'Manager', 'Store Keeper']);
            })->get();

            foreach ($approvers as $approver) {
                $this->notificationService->sendPendingApprovalAlert(
                    manager: $approver,
                    type: 'material_request',
                    id: $materialRequest->id,
                    requester: auth()->user(),
                    details: ['section' => $materialRequest->section->name]
                );
            }

            DB::commit();

            return response()->json([
                'message' => 'Material request created successfully',
                'data' => $materialRequest->load(['items.rawMaterial', 'section'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function show(MaterialRequest $materialRequest)
    {
        $this->authorize('view', $materialRequest);

        // Auto-clear matching notifications when viewing the request
        $this->notificationService->markAsReadByActionUrl(
            "/material-requests/{$materialRequest->id}",
            auth()->user()
        );

        $materialRequest->load(['chef', 'section', 'approver', 'fulfiller', 'items.rawMaterial']);

        return response()->json($materialRequest);
    }

    /**
     * Approve a material request.
     */
    public function approve(MaterialRequest $materialRequest, Request $request)
    {
        $this->authorize('approve', $materialRequest);

        if ($materialRequest->status !== 'pending') {
            throw ValidationException::withMessages([
                'status' => 'Only pending requests can be approved'
            ]);
        }

        $materialRequest->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        // Auto-clear matching notifications for the approver
        $this->notificationService->markAsReadByActionUrl(
            "/material-requests/{$materialRequest->id}",
            auth()->user()
        );

        // Notify chef
        if ($materialRequest->chef) {
            $this->notificationService->sendApprovalStatusChanged(
                requester: $materialRequest->chef,
                type: 'material_request',
                id: $materialRequest->id,
                status: 'approved',
                approver: auth()->user()
            );
        }

        return response()->json([
            'message' => 'Material request approved successfully',
            'data' => $materialRequest->load(['items.rawMaterial'])
        ]);
    }

    /**
     * Reject a material request.
     */
    public function reject(MaterialRequest $materialRequest, Request $request)
    {
        $this->authorize('reject', $materialRequest);

        if ($materialRequest->status !== 'pending') {
            throw ValidationException::withMessages([
                'status' => 'Only pending requests can be rejected'
            ]);
        }

        $validated = $request->validate([
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        $materialRequest->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        // Auto-clear matching notifications for the approver
        $this->notificationService->markAsReadByActionUrl(
            "/material-requests/{$materialRequest->id}",
            auth()->user()
        );

        // Notify chef
        if ($materialRequest->chef) {
            $this->notificationService->sendApprovalStatusChanged(
                requester: $materialRequest->chef,
                type: 'material_request',
                id: $materialRequest->id,
                status: 'rejected',
                approver: auth()->user(),
                notes: $validated['rejection_reason'] ?? null
            );
        }

        return response()->json([
            'message' => 'Material request rejected',
            'data' => $materialRequest
        ]);
    }

    /**
     * Fulfill an approved material request.
     */
    public function fulfill(MaterialRequest $materialRequest, Request $request)
    {
        $this->authorize('fulfill', $materialRequest);

        if ($materialRequest->status !== 'approved') {
            throw ValidationException::withMessages([
                'status' => 'Only approved requests can be fulfilled'
            ]);
        }

        try {
            DB::beginTransaction();

            // Lock the record AND eagerly load items to prevent lazy-load issues
            $materialRequest = MaterialRequest::with('items')
                ->lockForUpdate()
                ->find($materialRequest->id);

            if (!$materialRequest) {
                DB::rollBack();
                return response()->json(['message' => 'Request not found or already deleted'], 404);
            }

            // Re-check status after acquiring lock
            if ($materialRequest->status !== 'approved') {
                DB::rollBack();
                throw ValidationException::withMessages([
                    'status' => 'Request is no longer in approved status (already fulfilled or status changed)'
                ]);
            }

            // IDEMPOTENCY CHECK: If movements already exist for this request, don't duplicate
            $existingMovements = InventoryMovement::where('reference_id', $materialRequest->id)
                ->where('movement_type', 'issue_to_chef')
                ->exists();

            if ($existingMovements) {
                // Movements already created — just mark as fulfilled if not already
                $materialRequest->update([
                    'status' => 'fulfilled',
                    'fulfilled_by' => auth()->id(),
                    'fulfilled_at' => now(),
                ]);
                DB::commit();
                return response()->json([
                    'message' => 'Material request was already processed — marked as fulfilled',
                    'data' => $materialRequest
                ]);
            }

            // Get items snapshot BEFORE processing (prevent re-loading during loop)
            $items = $materialRequest->items->toArray();

            \Log::info('FULFILL_START', [
                'material_request_id' => $materialRequest->id,
                'total_items' => count($items),
                'fulfilled_by' => auth()->id(),
                'timestamp' => now()->toDateTimeString(),
            ]);

            foreach ($items as $item) {
                \Log::info('FULFILL_ITEM', [
                    'material_request_id' => $materialRequest->id,
                    'raw_material_id' => $item['raw_material_id'],
                    'quantity' => $item['quantity'],
                ]);

                // Issue materials using FIFO from InventoryService
                $this->inventoryService->issueToChef(
                    rawMaterialId: $item['raw_material_id'],
                    quantity: $item['quantity'],
                    toLocation: $materialRequest->section->name,
                    performedBy: auth()->user(),
                    approvedBy: $materialRequest->approver,
                    referenceId: $materialRequest->id
                );
            }

            $materialRequest->update([
                'status' => 'fulfilled',
                'fulfilled_by' => auth()->id(),
                'fulfilled_at' => now(),
            ]);

            // Auto-clear matching notifications for the fulfiller (Store Keeper/Manager)
            $this->notificationService->markAsReadByActionUrl(
                "/material-requests/{$materialRequest->id}",
                auth()->user()
            );

            // Notify chef
            if ($materialRequest->chef) {
                $this->notificationService->sendApprovalStatusChanged(
                    requester: $materialRequest->chef,
                    type: 'material_request',
                    id: $materialRequest->id,
                    status: 'fulfilled',
                    approver: auth()->user()
                );
            }

            // Notify admins/managers/store keepers (about fulfillment)
            $watchers = \App\Models\User::whereHas('role', function ($q) {
                $q->whereIn('name', ['Admin', 'Manager', 'Store Keeper']);
            })->where('id', '!=', auth()->id()) // Don't notify self
                ->get();

            foreach ($watchers as $watcher) {
                $this->notificationService->sendApprovalStatusChanged(
                    requester: $watcher, // Re-using this method, conceptually 'recipient'
                    type: 'material_request',
                    id: $materialRequest->id,
                    status: 'fulfilled',
                    approver: auth()->user()
                );
            }

            DB::commit();

            return response()->json([
                'message' => 'Material request fulfilled successfully',
                'data' => $materialRequest
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Remove the specified material request from storage.
     */
    public function destroy(MaterialRequest $materialRequest)
    {
        $this->authorize('delete', $materialRequest);

        try {
            DB::beginTransaction();

            // Lock record to prevent concurrent fulfillment
            $materialRequest = MaterialRequest::lockForUpdate()->find($materialRequest->id);

            if (!$materialRequest) {
                DB::rollBack();
                return response()->json(['message' => 'Request already deleted'], 404);
            }

            // If it was fulfilled, we need to replenish inventory
            if ($materialRequest->status === 'fulfilled') {
                // Find associated inventory movements
                // We enabled reference_id in InventoryService, but for older records it might be null.
                // If reference_id is present, we use it.
                // If not, we might have to rely on timestamp/user heuristics or just accept we can't fully revert perfectly for legacy data.
                // For this implementation, we rely on reference_id which we just added for new records.

                $movements = \App\Models\InventoryMovement::where('reference_id', $materialRequest->id)
                    ->where('movement_type', 'issue_to_chef')
                    ->get();

                foreach ($movements as $movement) {
                    // Revert ProcurementItem usage (replenish the batch)
                    if ($movement->procurement_item_id) {
                        $batch = \App\Models\ProcurementItem::find($movement->procurement_item_id);
                        if ($batch) {
                            // We use 'received_quantity' as the 'used' tracker in InventoryService logic
                            // So we DECREMENT received_quantity to "free up" the stock
                            // (Logic: Current Used = received_quantity. To RESTORE, we reduce Used amount)
                            $batch->decrement('received_quantity', $movement->quantity);
                        }
                    }

                    // Delete the movement to clear history
                    $movement->delete();
                }

                // If no reference_id movements found (legacy data), we technically fail to revert stock.
                // User asked to "handle failure gracefully".
                // We should probably log this or just proceed with soft delete.
                // Proceeding is safer than guessing.
            }

            // Soft delete items
            $materialRequest->items()->delete();

            // Soft delete request
            $materialRequest->delete();

            DB::commit();

            return response()->json(['message' => 'Material request deleted successfully']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to delete material request: ' . $e->getMessage()], 500);
        }
    }
}
