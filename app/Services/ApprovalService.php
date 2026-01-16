<?php

namespace App\Services;

use App\Models\ApprovalRequest;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Services\InventoryService;
use App\Services\WasteService;

class ApprovalService
{
    public function approve(ApprovalRequest $request, User $manager): void
    {
        DB::transaction(function () use ($request, $manager) {
            $request->update([
                'status' => 'approved',
                'approved_by' => $manager->id
            ]);

            match ($request->action_type) {
                'issue_material' =>
                app(InventoryService::class)->issueFromApproval($request),

                'waste_log' =>
                app(WasteService::class)->confirm($request),

                default => null
            };
        });
    }
}
