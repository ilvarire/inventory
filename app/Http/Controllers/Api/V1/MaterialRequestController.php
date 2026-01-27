<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\MaterialRequest;
use App\Models\MaterialRequestItem;
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
            'items.*.raw_material_id' => 'required|exists:raw_materials,id',
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

            // Notify admins/managers
            $approvers = \App\Models\User::whereHas('role', function ($q) {
                $q->whereIn('name', ['Admin', 'Manager']);
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

    /**
     * Display the specified material request.
     */
    public function show(MaterialRequest $materialRequest)
    {
        $this->authorize('view', $materialRequest);

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

            foreach ($materialRequest->items as $item) {
                // Issue materials using FIFO from InventoryService
                $this->inventoryService->issueToChef(
                    rawMaterialId: $item->raw_material_id,
                    quantity: $item->quantity,
                    toLocation: $materialRequest->section->name,
                    performedBy: auth()->user(),
                    approvedBy: $materialRequest->approver
                );
            }

            $materialRequest->update([
                'status' => 'fulfilled',
                'fulfilled_by' => auth()->id(),
                'fulfilled_at' => now(),
            ]);

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
}
