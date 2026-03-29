<?php

namespace App\Support\Tenancy;

use App\Models\Organization;

/**
 * SERVICE: CurrentOrganization
 *
 * Access the currently active organization from session.
 * Used throughout the app to scope queries automatically.
 *
 * Usage:
 *   $org = app(CurrentOrganization::class)->get();
 *   $orgId = app(CurrentOrganization::class)->id();
 */
class CurrentOrganization
{
    private const SESSION_KEY = 'current_organization_id';

    /**
     * Get the current organization model.
     * Returns null if no organization is set (e.g., on login page).
     */
    public function get(): ?Organization
    {
        $orgId = session(self::SESSION_KEY);

        if (!$orgId) {
            return null;
        }

        return Organization::find($orgId);
    }

    /**
     * Get just the organization ID.
     */
    public function id(): ?int
    {
        return session(self::SESSION_KEY);
    }

    /**
     * Set the current organization (called by middleware).
     */
    public function set(Organization $organization): void
    {
        session([self::SESSION_KEY => $organization->id]);
    }

    /**
     * Clear the current organization (called on logout).
     */
    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    /**
     * Check if organization is set.
     */
    public function isSet(): bool
    {
        return session()->has(self::SESSION_KEY);
    }
}
