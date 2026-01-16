<?php

namespace App\Jobs;

use App\Models\Section;
use App\Models\WasteLog;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckHighWastageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(NotificationService $notificationService): void
    {
        $sections = Section::all();
        $threshold = 1000; // Cost threshold for high wastage alert

        foreach ($sections as $section) {
            // Check wastage for the last 7 days
            $wasteCost = WasteLog::where('section_id', $section->id)
                ->where('created_at', '>=', now()->subDays(7))
                ->whereNotNull('approved_by')
                ->sum('cost_amount');

            if ($wasteCost > $threshold) {
                $notificationService->sendHighWastageAlert($section, $threshold);
            }
        }
    }
}
