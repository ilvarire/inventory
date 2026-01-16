<?php

namespace App\Services;

use App\Models\{
    RawMaterial,
    ProcurementItem,
    Section,
    User,
    NotificationLog
};
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Send low stock alert
     */
    public function sendLowStockAlert(RawMaterial $material): void
    {
        $message = "Low stock alert: {$material->name} is below minimum quantity. Current stock: {$this->getStockBalance($material->id)}, Minimum: {$material->min_quantity}";

        // Log notification
        $this->logNotification('low_stock', $message);

        // Send email to managers and procurement
        $recipients = User::whereHas('role', function ($q) {
            $q->whereIn('name', ['Manager', 'Admin', 'Procurement']);
        })->where('is_active', true)->get();

        foreach ($recipients as $recipient) {
            try {
                // In production, send actual email
                // Mail::to($recipient->email)->send(new LowStockAlert($material));

                Log::info("Low stock alert sent to {$recipient->email} for {$material->name}");
            } catch (\Exception $e) {
                Log::error("Failed to send low stock alert: " . $e->getMessage());
            }
        }
    }

    /**
     * Send expiry alert
     */
    public function sendExpiryAlert(ProcurementItem $batch): void
    {
        $daysUntilExpiry = now()->diffInDays($batch->expiry_date);
        $expiryDateFormatted = $batch->expiry_date ? $batch->expiry_date->format('Y-m-d') : 'N/A';
        $message = "Expiry alert: {$batch->rawMaterial->name} (Batch #{$batch->id}) expires in {$daysUntilExpiry} days on {$expiryDateFormatted}";

        // Log notification
        $this->logNotification('expiry_alert', $message);

        // Send email to managers and store keepers
        $recipients = User::whereHas('role', function ($q) {
            $q->whereIn('name', ['Manager', 'Admin', 'Store Keeper']);
        })->where('is_active', true)->get();

        foreach ($recipients as $recipient) {
            try {
                Log::info("Expiry alert sent to {$recipient->email} for batch #{$batch->id}");
            } catch (\Exception $e) {
                Log::error("Failed to send expiry alert: " . $e->getMessage());
            }
        }
    }

    /**
     * Send high wastage alert
     */
    public function sendHighWastageAlert(Section $section, float $threshold): void
    {
        $message = "High wastage alert: {$section->name} has exceeded wastage threshold of {$threshold}";

        // Log notification
        $this->logNotification('high_wastage', $message);

        // Send email to managers
        $recipients = User::whereHas('role', function ($q) {
            $q->whereIn('name', ['Manager', 'Admin']);
        })->where('is_active', true)->get();

        foreach ($recipients as $recipient) {
            try {
                Log::info("High wastage alert sent to {$recipient->email} for {$section->name}");
            } catch (\Exception $e) {
                Log::error("Failed to send high wastage alert: " . $e->getMessage());
            }
        }
    }

    /**
     * Send pending approval alert
     */
    public function sendPendingApprovalAlert(User $manager, string $type, int $id): void
    {
        $message = "Pending approval: {$type} #{$id} requires your approval";

        // Log notification
        $this->logNotification('pending_approval', $message, $manager);

        try {
            Log::info("Pending approval alert sent to {$manager->email} for {$type} #{$id}");
        } catch (\Exception $e) {
            Log::error("Failed to send pending approval alert: " . $e->getMessage());
        }
    }

    /**
     * Log notification to database
     */
    public function logNotification(string $type, string $message, ?User $user = null): void
    {
        NotificationLog::create([
            'user_id' => $user?->id,
            'type' => $type,
            'message' => $message,
            'sent_at' => now(),
        ]);
    }

    /**
     * Get stock balance (helper method)
     */
    protected function getStockBalance(int $rawMaterialId): float
    {
        $inventoryService = app(InventoryService::class);
        return $inventoryService->getStockBalance($rawMaterialId);
    }
}
