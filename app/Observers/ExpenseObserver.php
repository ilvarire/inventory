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
            'entity_type' => Expense::class,
            'entity_id' => $expense->id,
            'after' => json_encode($expense->toArray()),
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
            'entity_type' => Expense::class,
            'entity_id' => $expense->id,
            'before' => json_encode($expense->getOriginal()),
            'after' => json_encode($expense->getChanges()),
        ]);
    }
}
