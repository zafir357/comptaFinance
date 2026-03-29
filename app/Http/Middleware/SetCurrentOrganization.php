<?php

namespace App\Http\Middleware;

use App\Models\Organization;
use App\Support\Tenancy\CurrentOrganization;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * MIDDLEWARE: SetCurrentOrganization
 *
 * Sets the current organization from request, either:
 * 1. From ?org={id} query parameter (org switching)
 * 2. From session (remember previous org)
 * 3. From user's first organization (default)
 *
 * Applied to 'web' middleware group so it runs for every request.
 */
class SetCurrentOrganization
{
    public function handle(Request $request, Closure $next): Response
    {
        // Must be authenticated
        if (!auth()->check()) {
            return $next($request);
        }

        $currentOrg = app(CurrentOrganization::class);

        // 1. Check if switching organization via query param
        if ($request->query('org')) {
            $org = Organization::findOrFail($request->query('org'));

            // Verify user has access to this organization
            if (!auth()->user()->organizations()->where('id', $org->id)->exists()) {
                abort(403, 'You do not have access to this organization.');
            }

            $currentOrg->set($org);
        }

        // 2. Check if organization is already set in session
        elseif ($currentOrg->isSet()) {
            // Organization already set, keep it
        }

        // 3. Fall back to user's first organization
        else {
            $org = auth()->user()->organizations()->first();

            if ($org) {
                $currentOrg->set($org);
            }
        }

        return $next($request);
    }
}
