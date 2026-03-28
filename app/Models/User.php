<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

/**
 * MODEL: User (utilisateur)
 * 
 * Extension du model User de base Laravel avec support multi-tenant.
 * 
 * RELATIONS SQL AJOUTÉES:
 * - MANY-TO-MANY avec Organization (via memberships) = belongsToMany()
 * - ONE-TO-MANY avec Membership = hasMany()
 * - ONE-TO-MANY avec Ticket = hasMany()
 */
class User extends Authenticatable // implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->map(fn (string $name) => Str::of($name)->substr(0, 1))
            ->implode('');
    }
    
    // ========================================
    // RELATIONS MULTI-TENANT
    // ========================================
    
    /**
     * MANY-TO-MANY: Un user peut appartenir à plusieurs organizations
     * 
     * SQL: SELECT organizations.* FROM organizations 
     *      INNER JOIN memberships ON organizations.id = memberships.organization_id 
     *      WHERE memberships.user_id = ?
     * 
     * Usage:
     * $user->organizations → Collection d'organizations
     * $user->organizations->first()->pivot->role → 'owner'
     */
    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class, 'memberships')
            ->withPivot('role')      // Accès au rôle via pivot
            ->withTimestamps();
    }
    
    /**
     * ONE-TO-MANY: Un user a plusieurs memberships
     * 
     * SQL: SELECT * FROM memberships WHERE user_id = ?
     */
    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }
    
    /**
     * ONE-TO-MANY: Un user a créé plusieurs tickets
     * 
     * SQL: SELECT * FROM tickets WHERE user_id = ?
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }
    
    /**
     * HELPER: Récupérer l'organization courante depuis la session
     * 
     * Usage: $user->currentOrganization()
     */
    public function currentOrganization(): ?Organization
    {
        $orgId = session('current_organization_id');
        if (!$orgId) {
            return null;
        }
        
        return $this->organizations()->find($orgId);
    }
    
    /**
     * HELPER: Check si l'user appartient à une organization
     * 
     * Usage: $user->belongsToOrganization($organizationId)
     */
    public function belongsToOrganization(int $organizationId): bool
    {
        return $this->organizations()->where('organizations.id', $organizationId)->exists();
    }
    
    /**
     * HELPER: Récupérer le rôle dans une organization
     * 
     * Usage: $user->roleInOrganization($organizationId) → 'owner'
     */
    public function roleInOrganization(int $organizationId): ?string
    {
        $membership = $this->memberships()
            ->where('organization_id', $organizationId)
            ->first();
            
        return $membership?->role;
    }
    
    /**
     * HELPER: Check si l'user est owner d'une organization
     */
    public function isOwnerOf(int $organizationId): bool
    {
        return $this->roleInOrganization($organizationId) === 'owner';
    }
    
    /**
     * HELPER: Check si l'user peut éditer dans une organization
     * (owner ou accountant)
     */
    public function canEditIn(int $organizationId): bool
    {
        $role = $this->roleInOrganization($organizationId);
        return in_array($role, ['owner', 'accountant']);
    }
}
