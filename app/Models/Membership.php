<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * MODEL: Membership (table PIVOT enrichie)
 * 
 * Lie un User à une Organization avec un rôle.
 * C'est une table PIVOT avec données supplémentaires (role).
 * 
 * RELATIONS SQL:
 * - MANY-TO-ONE avec User = belongsTo()
 * - MANY-TO-ONE avec Organization = belongsTo()
 * 
 * Pourquoi un model pour une table pivot?
 * Normalement Laravel gère les pivots automatiquement, mais ici on a
 * un champ supplémentaire (role) qu'on veut manipuler facilement.
 */
class Membership extends Model
{
    protected $guarded = [];
    
    protected $casts = [
        'role' => 'string',
    ];
    
    // MANY-TO-ONE: Plusieurs memberships appartiennent à un user
    // SQL: SELECT * FROM users WHERE id = ?
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    // MANY-TO-ONE: Plusieurs memberships appartiennent à une organization
    // SQL: SELECT * FROM organizations WHERE id = ?
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
