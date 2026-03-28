<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * MODEL: TicketMessage (message d'un ticket)
 * 
 * Chaque message dans un ticket (conversation type chat).
 * 
 * RELATIONS SQL:
 * - MANY-TO-ONE avec Ticket = belongsTo()
 * - MANY-TO-ONE avec User (auteur) = belongsTo()
 */
class TicketMessage extends Model
{
    protected $guarded = [];
    
    protected $casts = [
        'is_internal' => 'boolean',
    ];
    
    // MANY-TO-ONE: Plusieurs messages appartiennent à un ticket
    // SQL: SELECT * FROM tickets WHERE id = ?
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }
    
    // MANY-TO-ONE: Plusieurs messages appartiennent à un user (auteur)
    // SQL: SELECT * FROM users WHERE id = ?
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
