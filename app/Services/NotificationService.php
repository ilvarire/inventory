<?php

namespace App\Services;

use App\Models\{
    RawMaterial,
    ProcurementItem,
    Section,
    User,
    NotificationLog
};
use App\Mail\{
    LowStockAlert,
    ExpiryAlert,
    HighWastageAlert,
    PendingApprovalAlert,
    ApprovalStatusChanged
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
        $currentStock = $this->getStockBalance($material->id);
        $message = "Low stock alert: {$material->name} is below minimum quantity. Current stock: {$currentStock}, Minimum: {$material->min_quantity}";
        $actionUrl = "/inventory/{$material->id}";

        // Send to managers and procurement
        $recipients = User::whereHas('role', function ($q) {
            $q->whereIn('name', ['Manager', 'Admin', 'Procurement']);
        })->where('is_active', true)->get();

        foreach ($recipients as $recipient) {
            // Log for UI
            $this->logNotification($recipient, 'low_stock', $message, $actionUrl);

            try {
                if ($this->shouldSendEmail($recipient, 'low_stock')) {
                    Mail::to($recipient->email)->queue(
                        new LowStockAlert($material, $currentStock)
                    );
                    Log::info("Low stock alert sent to {$recipient->email} for {$material->name}");
                }
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
        $expiryDateFormatted = $batch->expiry_date?->format('Y-m-d') ?? 'N/A';
        $message = "Expiry alert: {$batch->rawMaterial->name} (Batch #{$batch->id}) expires in {$daysUntilExpiry} days on {$expiryDateFormatted}";
        $actionUrl = "/procurement/{$batch->procurement_id}";

        // Send to managers and store keepers
        $recipients = User::whereHas('role', function ($q) {
            $q->whereIn('name', ['Manager', 'Admin', 'Store Keeper']);
        })->where('is_active', true)->get();

        foreach ($recipients as $recipient) {
            // Log for UI
            $this->logNotification($recipient, 'expiry_alert', $message, $actionUrl);

            try {
                if ($this->shouldSendEmail($recipient, 'expiry_alert')) {
                    Mail::to($recipient->email)->queue(
                        new ExpiryAlert($batch)
                    );
                    Log::info("Expiry alert sent to {$recipient->email} for batch #{$batch->id}");
                }
            } catch (\Exception $e) {
                Log::error("Failed to send expiry alert: " . $e->getMessage());
            }
        }
    }

    /**
     * Send high wastage alert
     */
    public function sendHighWastageAlert(Section $section, float $threshold, float $actualWastage = 0, float $costImpact = 0): void
    {
        $message = "High wastage alert: {$section->name} has exceeded wastage threshold of {$threshold}%";
        $actionUrl = "/reports/waste"; // Or specific section dashboard

        // Send to managers
        $recipients = User::whereHas('role', function ($q) {
            $q->whereIn('name', ['Manager', 'Admin']);
        })->where('is_active', true)->get();

        foreach ($recipients as $recipient) {
            // Log for UI
            $this->logNotification($recipient, 'high_wastage', $message, $actionUrl);

            try {
                if ($this->shouldSendEmail($recipient, 'high_wastage')) {
                    Mail::to($recipient->email)->queue(
                        new HighWastageAlert($section, $threshold, $actualWastage, $costImpact)
                    );
                    Log::info("High wastage alert sent to {$recipient->email} for {$section->name}");
                }
            } catch (\Exception $e) {
                Log::error("Failed to send high wastage alert: " . $e->getMessage());
            }
        }
    }

    /**
     * Send pending approval alert
     */
    public function sendPendingApprovalAlert(User $manager, string $type, int $id, ?User $requester = null, array $details = []): void
    {
        $message = "Pending approval: {$type} #{$id} requires your approval";

        // Determine action URL based on type
        $actionUrl = match ($type) {
            'procurement' => "/procurement/{$id}",
            'material_request' => "/material-requests/{$id}",
            'production' => "/productions/{$id}",
            'waste' => "/waste/{$id}",
            default => null
        };

        // Log notification
        $this->logNotification($manager, 'pending_approval', $message, $actionUrl);

        try {
            if ($this->shouldSendEmail($manager, 'pending_approval')) {
                Mail::to($manager->email)->queue(
                    new PendingApprovalAlert($type, $id, $requester ?? auth()->user(), $details)
                );

                Log::info("Pending approval alert sent to {$manager->email} for {$type} #{$id}");
            }
        } catch (\Exception $e) {
            Log::error("Failed to send pending approval alert: " . $e->getMessage());
        }
    }

    /**
     * Send approval status changed notification
     */
    public function sendApprovalStatusChanged(User $requester, string $type, int $id, string $status, User $approver, ?string $notes = null): void
    {
        $message = "Approval status changed: {$type} #{$id} has been {$status}";

        // Determine action URL based on type
        $actionUrl = match ($type) {
            'procurement' => "/procurement/{$id}",
            'material_request' => "/material-requests/{$id}",
            'production' => "/productions/{$id}", // production.show
            'waste' => "/waste/{$id}",
            default => null
        };

        // Log notification
        $this->logNotification($requester, 'approval_status', $message, $actionUrl);

        try {
            if ($this->shouldSendEmail($requester, 'approval_status')) {
                Mail::to($requester->email)->queue(
                    new ApprovalStatusChanged($type, $id, $status, $approver, $notes)
                );

                Log::info("Approval status notification sent to {$requester->email} for {$type} #{$id}");
            }
        } catch (\Exception $e) {
            Log::error("Failed to send approval status notification: " . $e->getMessage());
        }
    }

    /**
     * Check if email should be sent to user
     */
    protected function shouldSendEmail(User $user, string $type): bool
    {
        // Check if notifications are globally enabled
        if (!config('notifications.enabled', true)) {
            return false;
        }

        // Check if user has email notifications enabled (if column exists)
        if (isset($user->email_notifications_enabled) && !$user->email_notifications_enabled) {
            return false;
        }

        // Check user-specific notification preferences (if column exists)
        if (isset($user->notification_preferences)) {
            $preferences = is_string($user->notification_preferences)
                ? json_decode($user->notification_preferences, true)
                : $user->notification_preferences;

            if (is_array($preferences) && isset($preferences[$type]) && !$preferences[$type]) {
                return false;
            }
        }

        return true;
    }

    /**
     * Mark notifications as read based on an action URL and user.
     * Useful for automatically clearing notifications when an item is settled.
     */
    public function markAsReadByActionUrl(string $actionUrl, ?User $user = null): void
    {
        $query = NotificationLog::where('action_url', $actionUrl)
            ->whereNull('read_at');

        if ($user) {
            $query->where('user_id', $user->id);
        }

        $query->update(['read_at' => now()]);
    }

    /**
     * Log notification to database
     */
    public function logNotification(User $user, string $type, string $message, ?string $actionUrl = null): void
    {
        NotificationLog::create([
            'user_id' => $user->id,
            'type' => $type,
            'message' => $message,
            'recipient_email' => $user->email,
            'action_url' => $actionUrl,
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
