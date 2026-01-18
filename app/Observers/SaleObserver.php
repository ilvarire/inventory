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
            'entity_type' => Sale::class,
            'entity_id' => $sale->id,
            'after' => json_encode($sale->toArray()),
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
            'entity_type' => Sale::class,
            'entity_id' => $sale->id,
            'before' => json_encode($sale->toArray()),
        ]);
    }
}
