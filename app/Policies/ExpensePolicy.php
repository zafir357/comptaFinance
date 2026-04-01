<?php

namespace App\Policies;

use App\Models\Expense;
use App\Models\User;

/**
 * ExpensePolicy
 * 
 * Purpose: Authorization logic for expenses
 * - Controls who can view/create/update/delete expenses
 * - Enforces multi-tenancy (users can only access their org's expenses)
 * - Role-based permissions (owner, accountant, member)
 */
class ExpensePolicy
{
    /**
     * Can the user view any expenses?
     * All authenticated users can view expenses in their organization
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Can the user view this specific expense?
     * User must belong to the same organization
     */
    public function view(User $user, Expense $expense): bool
    {
        return $user->belongsToOrganization($expense->organization_id);
    }

    /**
     * Can the user create expenses?
     * All users can create expenses in their organization
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Can the user update this expense?
     * Only the user who created it, or owner/accountant roles
     */
    public function update(User $user, Expense $expense): bool
    {
        // Must belong to same organization
        if (!$user->belongsToOrganization($expense->organization_id)) {
            return false;
        }

        // Owner and accountant can edit any expense
        $role = $user->roleInOrganization($expense->organization_id);
        if (in_array($role, ['owner', 'accountant'])) {
            return true;
        }

        // Regular members can only edit their own expenses
        return $expense->user_id === $user->id;
    }

    /**
     * Can the user delete this expense?
     * Only owners and accountants
     */
    public function delete(User $user, Expense $expense): bool
    {
        if (!$user->belongsToOrganization($expense->organization_id)) {
            return false;
        }

        $role = $user->roleInOrganization($expense->organization_id);
        return in_array($role, ['owner', 'accountant']);
    }
}
