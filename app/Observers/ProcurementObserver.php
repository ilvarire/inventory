<?php

namespace App\Observers;

use App\Models\Procurement;
use App\Models\AuditLog;

class ProcurementObserver
{
    /**
     * Handle the Procurement "created" event.
     */
    public function created(Procurement $procurement): void
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'created',
            'entity_type' => Procurement::class,
            'entity_id' => $procurement->id,
            'after' => json_encode($procurement->toArray()),
        ]);
    }

    /**
     * Handle the Procurement "updated" event.
     */
    public function updated(Procurement $procurement): void
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'updated',
            'entity_type' => Procurement::class,
            'entity_id' => $procurement->id,
            'before' => json_encode($procurement->getOriginal()),
            'after' => json_encode($procurement->getChanges()),
        ]);
    }

    /**
     * Handle the Procurement "deleted" event.
     */
    public function deleted(Procurement $procurement): void
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'deleted',
            'entity_type' => Procurement::class,
            'entity_id' => $procurement->id,
            'before' => json_encode($procurement->toArray()),
        ]);
    }
}
