<?php

namespace App\Policies;

use App\Models\InventoryMovement;
use App\Models\User;

class InventoryPolicy
{
    /**
     * Determine if the user can view inventory.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view inventory
        return true;
    }

    /**
     * Determine if the user can issue materials.
     */
    public function issue(User $user): bool
    {
        // Store Keeper, Manager, and Admin can issue materials
        return $user->isStoreKeeper() || $user->isManager() || $user->isAdmin();
    }

    /**
     * Determine if the user can approve movements.
     */
    public function approve(User $user): bool
    {
        // Only Manager and Admin can approve sensitive movements
        return $user->isManager() || $user->isAdmin();
    }

    /**
     * Determine if the user can adjust stock.
     */
    public function adjust(User $user): bool
    {
        // Only Manager and Admin can make stock adjustments
        return $user->isManager() || $user->isAdmin();
    }

    /**
     * Determine if the user can view section-specific inventory.
     */
    public function viewSection(User $user, int $sectionId): bool
    {
        // Chef can only view their section's inventory
        if ($user->isChef()) {
            return $user->canAccessSection($sectionId);
        }

        // Others can view all sections
        return true;
    }
}
