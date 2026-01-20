<?php

namespace App\Policies;

use App\Models\MaterialRequest;
use App\Models\User;

class MaterialRequestPolicy
{
    /**
     * Determine if the user can view any material requests.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view material requests
        return true;
    }

    /**
     * Determine if the user can view the material request.
     */
    public function view(User $user, MaterialRequest $request): bool
    {
        // Chef can only view their own requests
        if ($user->isChef()) {
            return $user->id === $request->chef_id;
        }

        // Manager, Store Keeper, and Admin can view all
        return $user->isManager() || $user->isStoreKeeper() || $user->isAdmin();
    }

    /**
     * Determine if the user can create material requests.
     */
    public function create(User $user): bool
    {
        // Only Chef can create material requests
        return $user->isChef();
    }

    /**
     * Determine if the user can approve material requests.
     */
    public function approve(User $user): bool
    {
        // Only Manager and Admin can approve requests
        return $user->isManager() || $user->isAdmin();
    }

    /**
     * Determine if the user can reject material requests.
     */
    public function reject(User $user): bool
    {
        // Only Manager and Admin can reject requests
        return $user->isManager() || $user->isAdmin();
    }

    /**
     * Determine if the user can fulfill material requests.
     */
    public function fulfill(User $user): bool
    {
        // Only Manager and Admin can fulfill requests
        return $user->isManager() || $user->isAdmin();
    }
}
