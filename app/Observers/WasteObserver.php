<?php

namespace App\Observers;

use App\Models\WasteLog;
use App\Models\AuditLog;

class WasteObserver
{
    /**
     * Handle the WasteLog "created" event.
     */
    public function created(WasteLog $waste): void
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'created',
            'entity_type' => WasteLog::class,
            'entity_id' => $waste->id,
            'after' => json_encode($waste->toArray()),
        ]);
    }

    /**
     * Handle the WasteLog "updated" event.
     */
    public function updated(WasteLog $waste): void
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'updated',
            'entity_type' => WasteLog::class,
            'entity_id' => $waste->id,
            'before' => json_encode($waste->getOriginal()),
            'after' => json_encode($waste->getChanges()),
        ]);
    }
}
