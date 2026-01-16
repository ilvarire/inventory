<?php

namespace App\Jobs;

use App\Models\ProcurementItem;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckExpiringItemsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(NotificationService $notificationService): void
    {
        // Check for items expiring in the next 7 days
        $expiringBatches = ProcurementItem::whereNotNull('expiry_date')
            ->where('expiry_date', '<=', now()->addDays(7))
            ->where('expiry_date', '>=', now())
            ->whereRaw('quantity > received_quantity')
            ->with('rawMaterial')
            ->get();

        foreach ($expiringBatches as $batch) {
            $notificationService->sendExpiryAlert($batch);
        }
    }
}
