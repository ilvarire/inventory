<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WasteLog;

class WasteLogPolicy
{
    /**
     * Determine if the user can view any waste logs.
     */
    public function viewAny(User $user): bool
    {
        // Only Chef, Manager, and Admin can view waste logs
        return $user->isChef() || $user->isManager() || $user->isAdmin();
    }

    /**
     * Determine if the user can view the waste log.
     */
    public function view(User $user, WasteLog $wasteLog): bool
    {
        // Chef can only view waste logs from their section
        if ($user->isChef()) {
            return $user->section_id === $wasteLog->section_id;
        }

        // Manager and Admin can view all
        return $user->isManager() || $user->isAdmin();
    }

    /**
     * Determine if the user can create waste logs.
     */
    public function create(User $user): bool
    {
        // Only Chef can create waste logs
        return $user->isChef();
    }

    /**
     * Determine if the user can approve waste logs.
     */
    public function approve(User $user, WasteLog $wasteLog): bool
    {
        // Only Manager and Admin can approve waste logs
        return $user->isManager() || $user->isAdmin();
    }

    /**
     * Determine if the user can delete waste logs.
     */
    public function delete(User $user, WasteLog $wasteLog): bool
    {
        // Only Admin can delete waste logs
        return $user->isAdmin();
    }
}
