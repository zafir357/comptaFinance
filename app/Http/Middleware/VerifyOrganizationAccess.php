<?php

namespace App\Http\Middleware;

use App\Support\Tenancy\CurrentOrganization;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * MIDDLEWARE: VerifyOrganizationAccess
 *
 * Verifies that the authenticated user has access to the current organization.
 * Used on routes that require organization context.
 *
 * Returns 403 if:
 * - User is not a member of the current organization
 * - No organization is set
 */
class VerifyOrganizationAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect('login');
        }

        $org = app(CurrentOrganization::class)->get();

        if (!$org || !auth()->user()->organizations()->where('id', $org->id)->exists()) {
            abort(403, 'You do not have permission to access this organization.');
        }

        return $next($request);
    }
}
