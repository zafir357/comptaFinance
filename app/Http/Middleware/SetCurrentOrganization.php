<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Support\Tenancy\CurrentOrganization;

/**
 * MIDDLEWARE: SetCurrentOrganization
 * 
 * S'exécute sur CHAQUE requête pour charger l'organization active.
 * 
 * FLUX D'EXÉCUTION:
 * 
 * 1. User fait une requête (ex: GET /invoices)
 * 2. Laravel passe par ce middleware AVANT le controller
 * 3. On récupère l'org_id depuis la session
 * 4. On charge l'organization depuis la DB
 * 5. On la stocke dans CurrentOrganization::set($org)
 * 6. La requête continue vers le controller
 * 7. Le controller peut utiliser CurrentOrganization::id()
 * 
 * LOGIQUE DE SÉLECTION:
 * 
 * - Si 'current_organization_id' existe en session → on l'utilise
 * - Sinon, on prend la PREMIÈRE organization de l'user
 * - Si l'user n'appartient à aucune org → on redirige vers onboarding
 * 
 * SÉCURITÉ IMPORTANTE:
 * On vérifie que l'user APPARTIENT bien à l'org qu'il essaie de charger!
 * Sinon quelqu'un pourrait mettre n'importe quel ID en session.
 */
class SetCurrentOrganization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Si pas authentifié, on skip (routes publiques)
        if (!$request->user()) {
            return $next($request);
        }
        
        $user = $request->user();
        
        // Récupérer l'org_id depuis la session
        $organizationId = session('current_organization_id');
        
        // Si pas d'org en session, prendre la première de l'user
        if (!$organizationId) {
            $firstMembership = $user->memberships()->first();
            
            if ($firstMembership) {
                $organizationId = $firstMembership->organization_id;
                session(['current_organization_id' => $organizationId]);
            } else {
                // User n'appartient à aucune org → rediriger vers onboarding
                // TODO: créer la page d'onboarding plus tard
                // Pour l'instant, on laisse passer
                return $next($request);
            }
        }
        
        // SÉCURITÉ: Vérifier que l'user appartient bien à cette org
        $organization = $user->organizations()->find($organizationId);
        
        if (!$organization) {
            // L'user essaie d'accéder à une org dont il ne fait pas partie!
            // On reset la session et on prend sa première org
            session()->forget('current_organization_id');
            
            $firstMembership = $user->memberships()->first();
            if ($firstMembership) {
                $organization = $firstMembership->organization;
                session(['current_organization_id' => $organization->id]);
            }
        }
        
        // Stocker l'org dans le helper statique
        if ($organization) {
            CurrentOrganization::set($organization);
        }
        
        return $next($request);
    }
}
