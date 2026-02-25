<?php

namespace App\Policies;

use App\Models\Recipe;
use App\Models\User;

class RecipePolicy
{
    /**
     * Determine if the user can view any recipes.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view recipes
        return true;
    }

    /**
     * Determine if the user can view the recipe.
     */
    public function view(User $user, Recipe $recipe): bool
    {
        // Chef can only view recipes from their section
        if ($user->isChef()) {
            return $user->canAccessSection($recipe->section_id);
        }

        // Manager and Admin can view all recipes
        return $user->isManager() || $user->isAdmin();
    }

    /**
     * Determine if the user can create recipes.
     */
    public function create(User $user): bool
    {
        // Only Manager and Admin can create recipes (Chef is view-only)
        return $user->isManager() || $user->isAdmin();
    }

    /**
     * Determine if the user can update the recipe.
     */
    public function update(User $user, Recipe $recipe): bool
    {
        // Chef is view-only, cannot update
        if ($user->isChef()) {
            return false;
        }

        // Manager and Admin can update any recipe
        return $user->isManager() || $user->isAdmin();
    }

    /**
     * Determine if the user can delete the recipe.
     */
    public function delete(User $user, Recipe $recipe): bool
    {
        // Only Manager and Admin can delete recipes
        return $user->isManager() || $user->isAdmin();
    }
}
