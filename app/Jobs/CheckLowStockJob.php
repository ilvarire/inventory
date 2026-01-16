<?php

namespace App\Jobs;

use App\Models\RawMaterial;
use App\Services\InventoryService;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckLowStockJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(
        InventoryService $inventoryService,
        NotificationService $notificationService
    ): void {
        $materials = RawMaterial::all();

        foreach ($materials as $material) {
            if ($inventoryService->checkLowStock($material->id)) {
                $notificationService->sendLowStockAlert($material);
            }
        }
    }
}
