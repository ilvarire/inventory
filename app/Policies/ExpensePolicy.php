<?php

namespace App\Policies;

use App\Models\Expense;
use App\Models\User;

class ExpensePolicy
{
    /**
     * Determine if the user can view any expenses.
     */
    public function viewAny(User $user): bool
    {
        // Only Manager and Admin can view expenses
        return $user->isManager() || $user->isAdmin();
    }

    /**
     * Determine if the user can view the expense.
     */
    public function view(User $user, Expense $expense): bool
    {
        // Only Manager and Admin can view expenses
        return $user->isManager() || $user->isAdmin();
    }

    /**
     * Determine if the user can create expenses.
     */
    public function create(User $user): bool
    {
        // Only Manager and Admin can log expenses
        return $user->isManager() || $user->isAdmin();
    }

    /**
     * Determine if the user can update the expense.
     */
    public function update(User $user, Expense $expense): bool
    {
        // Expenses are immutable once confirmed
        // Check if expense has a 'confirmed_at' field or similar
        return false; // Enforce immutability
    }

    /**
     * Determine if the user can delete the expense.
     */
    public function delete(User $user, Expense $expense): bool
    {
        // Only Admin can delete expenses
        return $user->isAdmin();
    }

    /**
     * Determine if the user can confirm the expense.
     */
    public function confirm(User $user): bool
    {
        // Only Manager and Admin can confirm expenses
        return $user->isManager() || $user->isAdmin();
    }
}
