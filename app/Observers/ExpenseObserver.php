<?php

namespace App\Observers;

use App\Models\Expense;
use App\Models\AuditLog;

class ExpenseObserver
{
    /**
     * Handle the Expense "created" event.
     */
    public function created(Expense $expense): void
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'created',
            'model_type' => Expense::class,
            'model_id' => $expense->id,
            'changes' => json_encode($expense->toArray()),
        ]);
    }

    /**
     * Handle the Expense "updated" event.
     */
    public function updated(Expense $expense): void
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'updated',
            'model_type' => Expense::class,
            'model_id' => $expense->id,
            'changes' => json_encode($expense->getChanges()),
        ]);
    }
}
