<?php

namespace App\Policies;

use App\Models\Procurement;
use App\Models\User;

class ProcurementPolicy
{
    /**
     * Determine if the user can view any procurements.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view procurements
        return true;
    }

    /**
     * Determine if the user can view the procurement.
     */
    public function view(User $user, Procurement $procurement): bool
    {
        // All authenticated users can view individual procurements
        return true;
    }

    /**
     * Determine if the user can create procurements.
     */
    public function create(User $user): bool
    {
        // Only Procurement role can create procurements
        return $user->isProcurement();
    }

    /**
     * Determine if the user can update the procurement.
     */
    public function update(User $user, Procurement $procurement): bool
    {
        // Only the creator or Admin can update (before approval)
        return $user->id === $procurement->procurement_user_id || $user->isAdmin();
    }

    /**
     * Determine if the user can delete the procurement.
     */
    public function delete(User $user, Procurement $procurement): bool
    {
        // Only Admin can delete procurements
        return $user->isAdmin();
    }

    /**
     * Determine if the user can approve/reject the procurement.
     */
    public function approve(User $user, Procurement $procurement): bool
    {
        // Only Store Keeper and Admin can approve/reject procurements
        return $user->isStoreKeeper() || $user->isAdmin();
    }
}
