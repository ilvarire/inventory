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
            'model_type' => WasteLog::class,
            'model_id' => $waste->id,
            'changes' => json_encode($waste->toArray()),
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
            'model_type' => WasteLog::class,
            'model_id' => $waste->id,
            'changes' => json_encode($waste->getChanges()),
        ]);
    }
}
