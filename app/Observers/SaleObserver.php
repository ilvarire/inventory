<?php

namespace App\Observers;

use App\Models\Sale;
use App\Models\AuditLog;

class SaleObserver
{
    /**
     * Handle the Sale "created" event.
     */
    public function created(Sale $sale): void
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'created',
            'model_type' => Sale::class,
            'model_id' => $sale->id,
            'changes' => json_encode($sale->toArray()),
        ]);
    }

    /**
     * Handle the Sale "deleted" event.
     */
    public function deleted(Sale $sale): void
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'deleted',
            'model_type' => Sale::class,
            'model_id' => $sale->id,
            'changes' => json_encode($sale->toArray()),
        ]);
    }
}
