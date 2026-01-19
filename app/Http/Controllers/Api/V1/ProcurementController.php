<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Procurement;
use App\Models\ProcurementItem;
use App\Models\InventoryMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProcurementController extends Controller
{
    /**
     * Display a listing of procurements.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Procurement::class);

        $query = Procurement::with(['user', 'items.rawMaterial']);

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

        $procurements = $query->orderBy('purchase_date', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json($procurements);
    }

    /**
     * Store a newly created procurement.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Procurement::class);

        $validated = $request->validate([
            'supplier_id' => 'required|string|max:255',
            'purchase_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.raw_material_id' => 'required|exists:raw_materials,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'items.*.quality_note' => 'nullable|string',
            'items.*.expiry_date' => 'nullable|date|after:today',
        ]);

        try {
            DB::beginTransaction();

            // Create procurement
            $procurement = Procurement::create([
                'procurement_user_id' => auth()->id(),
                'supplier_id' => $validated['supplier_id'],
                'purchase_date' => $validated['purchase_date'],
                'status' => 'received',
            ]);

            // Create procurement items (batches)
            foreach ($validated['items'] as $item) {
                $procurementItem = ProcurementItem::create([
                    'procurement_id' => $procurement->id,
                    'raw_material_id' => $item['raw_material_id'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'received_quantity' => 0, // Will be updated as materials are issued
                    'quality_note' => $item['quality_note'] ?? null,
                    'expiry_date' => $item['expiry_date'] ?? null,
                ]);

                // Create inventory movement for procurement
                InventoryMovement::create([
                    'raw_material_id' => $item['raw_material_id'],
                    'procurement_item_id' => $procurementItem->id,
                    'from_location' => 'supplier',
                    'to_location' => 'store',
                    'quantity' => $item['quantity'],
                    'movement_type' => 'procurement',
                    'performed_by' => auth()->id(),
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Procurement created successfully',
                'data' => $procurement->load(['items.rawMaterial'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Display the specified procurement.
     */
    public function show(Procurement $procurement)
    {
        $this->authorize('view', $procurement);

        $procurement->load(['user', 'items.rawMaterial']);

        return response()->json($procurement);
    }
}
