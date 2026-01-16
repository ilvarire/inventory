<?php

namespace App\Policies;

use App\Models\ProductionLog;
use App\Models\User;

class ProductionPolicy
{
    /**
     * Determine if the user can view any production logs.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view production logs
        return true;
    }

    /**
     * Determine if the user can view the production log.
     */
    public function view(User $user, ProductionLog $production): bool
    {
        // Chef can only view production from their section
        if ($user->isChef()) {
            return $user->canAccessSection($production->section_id);
        }

        // Manager and Admin can view all
        return $user->isManager() || $user->isAdmin();
    }

    /**
     * Determine if the user can create production logs.
     */
    public function create(User $user): bool
    {
        // Only Chef can log production
        return $user->isChef();
    }

    /**
     * Determine if the user can approve production logs.
     */
    public function approve(User $user): bool
    {
        // Only Manager and Admin can approve production
        return $user->isManager() || $user->isAdmin();
    }

    /**
     * Determine if the user can update the production log.
     */
    public function update(User $user, ProductionLog $production): bool
    {
        // Production logs are immutable once created
        // Only allow updates if not yet approved and user is the creator
        return false; // Enforce immutability
    }

    /**
     * Determine if the user can delete the production log.
     */
    public function delete(User $user, ProductionLog $production): bool
    {
        // Production logs cannot be deleted (immutable)
        return false;
    }
}
