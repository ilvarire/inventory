<?php

namespace App\Policies;

use App\Models\Sale;
use App\Models\User;

class SalePolicy
{
    /**
     * Determine if the user can view any sales.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view sales
        return true;
    }

    /**
     * Determine if the user can view the sale.
     */
    public function view(User $user, Sale $sale): bool
    {
        // Sales user can only view sales from their section
        if ($user->isSales()) {
            return $user->canAccessSection($sale->section_id);
        }

        // Manager and Admin can view all
        return $user->isManager() || $user->isAdmin();
    }

    /**
     * Determine if the user can create sales.
     */
    public function create(User $user): bool
    {
        // Only Frontline Sales can record sales
        return $user->isSales();
    }

    /**
     * Determine if the user can update the sale.
     */
    public function update(User $user, Sale $sale): bool
    {
        // Sales are immutable once recorded
        return false;
    }

    /**
     * Determine if the user can delete the sale.
     */
    public function delete(User $user, Sale $sale): bool
    {
        // Only Admin can delete sales (soft delete)
        return $user->isAdmin();
    }
}
