<?php

namespace App\Policies;

use App\Models\WasteLog;
use App\Models\User;

class WastePolicy
{
    /**
     * Determine if the user can view any waste logs.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view waste logs
        return true;
    }

    /**
     * Determine if the user can view the waste log.
     */
    public function view(User $user, WasteLog $waste): bool
    {
        // All authenticated users can view waste logs
        return true;
    }

    /**
     * Determine if the user can create waste logs.
     */
    public function create(User $user): bool
    {
        // Any authenticated user can report waste
        return true;
    }

    /**
     * Determine if the user can approve waste logs.
     */
    public function approve(User $user): bool
    {
        // Only Manager and Admin can approve waste
        return $user->isManager() || $user->isAdmin();
    }

    /**
     * Determine if the user can update the waste log.
     */
    public function update(User $user, WasteLog $waste): bool
    {
        // Waste logs are immutable once approved
        return false;
    }

    /**
     * Determine if the user can delete the waste log.
     */
    public function delete(User $user, WasteLog $waste): bool
    {
        // Only Admin can delete waste logs
        return $user->isAdmin();
    }
}
