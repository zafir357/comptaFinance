<?php

namespace App\Support\Tenancy;

use App\Models\Organization;

/**
 * HELPER: CurrentOrganization
 * 
 * Stocke l'organization active dans une variable statique.
 * Permet d'accéder facilement à l'org courante partout dans le code.
 * 
 * POURQUOI STATIQUE?
 * On veut que l'organization soit accessible partout sans passer par la session
 * à chaque fois. C'est plus rapide et plus propre.
 * 
 * COMMENT ÇA MARCHE?
 * 
 * 1. Le middleware SetCurrentOrganization s'exécute sur chaque requête
 * 2. Il charge l'org depuis la session et appelle CurrentOrganization::set($org)
 * 3. Pendant toute la requête, on peut faire:
 *    - CurrentOrganization::get() → récupère l'org
 *    - CurrentOrganization::id() → récupère juste l'ID
 * 4. À la fin de la requête, Laravel redémarre, la variable statique est réinitialisée
 * 
 * USAGE:
 * ```php
 * // Dans n'importe quel controller, action, service:
 * $orgId = CurrentOrganization::id();
 * 
 * // Filtrer les factures de l'org active:
 * Invoice::where('organization_id', CurrentOrganization::id())->get();
 * ```
 */
class CurrentOrganization
{
    /**
     * L'organization active (null si pas d'org sélectionnée)
     */
    protected static ?Organization $organization = null;
    
    /**
     * Définir l'organization courante
     * 
     * Appelé par le middleware SetCurrentOrganization
     */
    public static function set(?Organization $organization): void
    {
        static::$organization = $organization;
    }
    
    /**
     * Récupérer l'organization courante
     * 
     * @return Organization|null
     */
    public static function get(): ?Organization
    {
        return static::$organization;
    }
    
    /**
     * Récupérer l'ID de l'organization courante
     * 
     * Utile pour les requêtes:
     * Invoice::where('organization_id', CurrentOrganization::id())
     * 
     * @return int|null
     */
    public static function id(): ?int
    {
        return static::$organization?->id;
    }
    
    /**
     * Check si une organization est définie
     * 
     * @return bool
     */
    public static function check(): bool
    {
        return static::$organization !== null;
    }
    
    /**
     * Réinitialiser (utile pour les tests)
     */
    public static function reset(): void
    {
        static::$organization = null;
    }
}
