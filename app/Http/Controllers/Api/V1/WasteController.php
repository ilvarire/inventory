<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\WasteLog;
use App\Models\RawMaterial;
use App\Models\PreparedInventory;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class WasteController extends Controller
{
    /**
     * Display a listing of waste logs.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', WasteLog::class);

        $query = WasteLog::with(['section', 'rawMaterial', 'loggedBy', 'approvedBy']);

        // Filter by section
        if ($request->has('section_id')) {
            $query->where('section_id', $request->section_id);
        }

        // Filter by reason
        if ($request->has('reason')) {
            $query->where('reason', $request->reason);
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->where('created_at', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->where('created_at', '<=', $request->end_date);
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
        $this->authorize('create', WasteLog::class);

        $validated = $request->validate([
            'waste_type' => 'required|in:raw_material,prepared_food',
            'raw_material_id' => 'required_if:waste_type,raw_material|exists:raw_materials,id',
            'production_log_id' => 'required_if:waste_type,prepared_food|exists:production_logs,id',
            'quantity' => 'required|numeric|min:0.01',
            'reason' => 'required|string|in:spoilage,expiry,damage,handling_error,other',
            'notes' => 'nullable|string|max:500',
        ]);

        // Calculate cost amount
        $costAmount = 0;

        if ($validated['waste_type'] === 'raw_material') {
            $rawMaterial = RawMaterial::findOrFail($validated['raw_material_id']);
            // Get average cost from recent batches
            $avgCost = $rawMaterial->batches()
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->avg('unit_cost');
            $costAmount = $validated['quantity'] * ($avgCost ?? 0);
        } else {
            // For prepared food, get cost from production log
            $preparedItem = PreparedInventory::where('production_log_id', $validated['production_log_id'])
                ->first();
            if ($preparedItem) {
                // This would need CostingService to calculate
                $costAmount = $validated['quantity'] * 10; // Placeholder
            }
        }

        $wasteLog = WasteLog::create([
            'raw_material_id' => $validated['raw_material_id'] ?? null,
            'production_log_id' => $validated['production_log_id'] ?? null,
            'section_id' => auth()->user()->section_id,
            'quantity' => $validated['quantity'],
            'reason' => $validated['reason'],
            'cost_amount' => $costAmount,
            'logged_by' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Waste logged successfully. Awaiting manager approval.',
            'data' => $wasteLog->load(['rawMaterial', 'section'])
        ], 201);
    }

    /**
     * Display the specified waste log.
     */
    public function show(WasteLog $waste)
    {
        $this->authorize('view', $waste);

        $waste->load(['section', 'rawMaterial', 'loggedBy', 'approvedBy']);

        return response()->json($waste);
    }

    /**
     * Approve a waste log.
     */
    public function approve(WasteLog $waste)
    {
        $this->authorize('approve', $waste);

        if ($waste->approved_by) {
            throw ValidationException::withMessages([
                'waste' => 'Waste log already approved'
            ]);
        }

        $waste->update([
            'approved_by' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Waste approved successfully',
            'data' => $waste
        ]);
    }
}
