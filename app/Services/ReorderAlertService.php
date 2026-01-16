<?php

namespace App\Services;

use App\Models\RawMaterial;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReorderAlertMail;

class ReorderAlertService
{
    public function checkAndNotify(): void
    {
        $materials = RawMaterial::with('procurementItems')->get();

        foreach ($materials as $material) {
            $available = $material->procurementItems->sum(
                fn($batch) => $batch->quantity - $batch->received_quantity
            );

            if (
                $available <= $material->minimum_quantity &&
                $material->reorder_email_sent_at === null
            ) {
                Mail::to(config('inventory.procurement_email'))
                    ->send(new ReorderAlertMail($material, $available));

                $material->update([
                    'reorder_email_sent_at' => now()
                ]);
            }

            // Reset if stock restored
            if ($available > $material->minimum_quantity) {
                $material->update(['reorder_email_sent_at' => null]);
            }
        }
    }
}

//scheduler
// $schedule->call(fn () =>
//     app(ReorderAlertService::class)->checkAndNotify()
// )->hourly();

