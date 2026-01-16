<?php

namespace App\Observers;

use App\Models\ProductionLog;
use App\Models\AuditLog;

class ProductionObserver
{
    /**
     * Handle the ProductionLog "created" event.
     */
    public function created(ProductionLog $production): void
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'created',
            'model_type' => ProductionLog::class,
            'model_id' => $production->id,
            'changes' => json_encode($production->toArray()),
        ]);
    }

    /**
     * Handle the ProductionLog "updated" event.
     */
    public function updated(ProductionLog $production): void
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'updated',
            'model_type' => ProductionLog::class,
            'model_id' => $production->id,
            'changes' => json_encode($production->getChanges()),
        ]);
    }
}
