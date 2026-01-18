<?php

namespace App\Policies;

use App\Models\RawMaterial;
use App\Models\User;

class RawMaterialPolicy
{
    /**
     * Determine if the user can view any raw materials.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view raw materials
        return true;
    }

    /**
     * Determine if the user can view a specific raw material.
     */
    public function view(User $user, RawMaterial $rawMaterial): bool
    {
        // All authenticated users can view raw materials
        return true;
    }

    /**
     * Determine if the user can create raw materials.
     */
    public function create(User $user): bool
    {
        // Admin, Manager, and Store Keeper can create raw materials
        return $user->isAdmin() || $user->isManager() || $user->isStoreKeeper();
    }

    /**
     * Determine if the user can update raw materials.
     */
    public function update(User $user, RawMaterial $rawMaterial): bool
    {
        // Admin, Manager, and Store Keeper can update raw materials
        return $user->isAdmin() || $user->isManager() || $user->isStoreKeeper();
    }

    /**
     * Determine if the user can delete raw materials.
     */
    public function delete(User $user, RawMaterial $rawMaterial): bool
    {
        // Only Admin and Manager can delete raw materials
        return $user->isAdmin() || $user->isManager();
    }
}
