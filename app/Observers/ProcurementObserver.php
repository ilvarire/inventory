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
            'model_type' => Procurement::class,
            'model_id' => $procurement->id,
            'changes' => json_encode($procurement->toArray()),
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
            'model_type' => Procurement::class,
            'model_id' => $procurement->id,
            'changes' => json_encode($procurement->getChanges()),
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
            'model_type' => Procurement::class,
            'model_id' => $procurement->id,
            'changes' => json_encode($procurement->toArray()),
        ]);
    }
}
