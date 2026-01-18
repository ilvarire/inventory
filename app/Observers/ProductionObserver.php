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
            'entity_type' => ProductionLog::class,
            'entity_id' => $production->id,
            'after' => json_encode($production->toArray()),
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
            'entity_type' => ProductionLog::class,
            'entity_id' => $production->id,
            'before' => json_encode($production->getOriginal()),
            'after' => json_encode($production->getChanges()),
        ]);
    }
}
