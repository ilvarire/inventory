<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\PreparedInventory;
use App\Services\CostingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleController extends Controller
{
    protected CostingService $costingService;

    public function __construct(CostingService $costingService)
    {
        $this->costingService = $costingService;
    }

    /**
     * Display a listing of sales.
     */
    public function index(Request $request)
    {
        // Authorization handled by route middleware

        $user = auth()->user();
        $query = Sale::with(['section', 'salesUser', 'items']);

        // Sales user can only see sales from their section
        if ($user->isSales()) {
            $query->where('section_id', $user->section_id);
        }

        // Filter by section
        if ($request->has('section_id') && $request->section_id) {
            $query->where('section_id', $request->section_id);
        }

        // Filter by date range - use whereDate for proper date comparison
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('sale_date', '>=', $request->start_date);
        }
        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('sale_date', '<=', $request->end_date);
        }

        // DEBUG LOGGING
        \Illuminate\Support\Facades\Log::info('SaleController@index called', [
            'user_id' => $user->id,
            'is_admin' => $user->isAdmin(),
            'is_sales' => $user->isSales(),
            'section_id' => $user->section_id,
            'request_params' => $request->all(),
        ]);

        $sales = $query->orderBy('sale_date', 'desc')
            ->paginate($request->get('per_page', 15));

        \Illuminate\Support\Facades\Log::info('SaleController@index query result', [
            'count' => $sales->count(),
            'total' => $sales->total(),
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings()
        ]);

        return response()->json($sales);
    }

    /**
     * Store a newly created sale.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Sale::class);

        $validated = $request->validate([
            'sale_date' => 'required|date',
            'payment_method' => 'required|string|in:cash,card,mobile,transfer',
            'items' => 'required|array|min:1',
            'items.*.prepared_inventory_id' => 'required|exists:prepared_inventories,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Validate prepared inventory availability
            foreach ($validated['items'] as $item) {
                $preparedItem = PreparedInventory::findOrFail($item['prepared_inventory_id']);

                if ($preparedItem->quantity < $item['quantity']) {
                    throw ValidationException::withMessages([
                        'items' => "Insufficient quantity for {$preparedItem->item_name}. Available: {$preparedItem->quantity}"
                    ]);
                }

                if ($preparedItem->status !== 'available') {
                    throw ValidationException::withMessages([
                        'items' => "{$preparedItem->item_name} is not available for sale"
                    ]);
                }
            }

            // Calculate total amount
            $totalAmount = collect($validated['items'])->sum(function ($item) {
                return $item['quantity'] * $item['unit_price'];
            });

            // Create sale
            $sale = Sale::create([
                'section_id' => auth()->user()->section_id,
                'sales_user_id' => auth()->id(),
                'sale_date' => $validated['sale_date'],
                'total_amount' => $totalAmount,
                'payment_method' => $validated['payment_method'],
            ]);

            // Create sale items and update prepared inventory
            foreach ($validated['items'] as $item) {
                $preparedItem = PreparedInventory::findOrFail($item['prepared_inventory_id']);

                // Get cost price from production
                $production = $preparedItem->productionLog;
                $costPerUnit = $this->costingService->getCostPerUnit($production->id);

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'item_name' => $preparedItem->item_name,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'cost_price' => $costPerUnit,
                    'source_type' => 'prepared',
                    'source_id' => $preparedItem->id,
                ]);

                // Decrement prepared inventory
                $preparedItem->decrement('quantity', $item['quantity']);

                // Update status if depleted
                if ($preparedItem->fresh()->quantity == 0) {
                    $preparedItem->update(['status' => 'sold']);
                }
            }

            DB::commit();

            $profit = $this->costingService->getSaleProfit($sale->id);

            return response()->json([
                'message' => 'Sale recorded successfully',
                'data' => [
                    'sale' => $sale->load('items'),
                    'profit' => $profit,
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Display the specified sale.
     */
    public function show(Sale $sale)
    {
        // Authorization handled by route middleware

        $sale->load(['section', 'salesUser', 'items']);

        $profit = $this->costingService->getSaleProfit($sale->id);

        return response()->json([
            'sale' => $sale,
            'profit' => $profit,
        ]);
    }

    /**
     * Generate receipt for a sale.
     */
    public function receipt(Sale $sale)
    {
        $this->authorize('view', $sale);

        $sale->load(['section', 'salesUser', 'items']);

        $profit = $this->costingService->getSaleProfit($sale->id);

        return response()->json([
            'receipt' => [
                'sale_id' => $sale->id,
                'section' => $sale->section->name,
                'date' => $sale->sale_date,
                'items' => $sale->items->map(function ($item) {
                    return [
                        'name' => $item->item_name,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'subtotal' => $item->quantity * $item->unit_price,
                    ];
                }),
                'total' => $sale->total_amount,
                'payment_method' => $sale->payment_method,
                'served_by' => $sale->salesUser->name,
            ]
        ]);
    }
}
