<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    /**
     * Display a listing of expenses.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Expense::class);

        $query = Expense::with(['section', 'createdBy']);

        // Filter by section
        if ($request->has('section_id')) {
            if ($request->section_id === 'general') {
                $query->whereNull('section_id');
            } else {
                $query->where('section_id', $request->section_id);
            }
        }

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->where('expense_date', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->where('expense_date', '<=', $request->end_date);
        }

        $expenses = $query->orderBy('expense_date', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json($expenses);
    }

    /**
     * Store a newly created expense.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Expense::class);

        $validated = $request->validate([
            'section_id' => 'nullable|exists:sections,id',
            'type' => 'required|string|max:100',
            'amount' => 'required|numeric|min:0',
            'description' => 'required|string|max:500',
            'expense_date' => 'required|date',
        ]);

        $expense = Expense::create([
            'manager_id' => auth()->id(),
            'section_id' => $validated['section_id'] ?? null,
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'description' => $validated['description'],
            'expense_date' => $validated['expense_date'],
        ]);

        return response()->json([
            'message' => 'Expense logged successfully',
            'data' => $expense->load(['section', 'createdBy'])
        ], 201);
    }

    /**
     * Display the specified expense.
     */
    public function show(Expense $expense)
    {
        $this->authorize('view', $expense);

        $expense->load(['section', 'createdBy']);

        return response()->json($expense);
    }
}
