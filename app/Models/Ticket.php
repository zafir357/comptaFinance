<?php

namespace App\Models;

use App\Support\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * MODEL: Ticket (support client)
 *
 * Système de tickets pour gérer les demandes des utilisateurs.
 * Important pour le job: "Gestion des tickets techniques des clients"
 *
 * RELATIONS SQL:
 * - MANY-TO-ONE avec Organization = belongsTo() [from trait]
 * - MANY-TO-ONE avec User (créateur) = belongsTo()
 * - ONE-TO-MANY avec TicketMessage = hasMany()
 */
class Ticket extends Model
{
    use BelongsToOrganization;

    protected $guarded = [];

    protected $casts = [
        'tags' => 'array',  // JSON → array PHP automatiquement
    ];

    // MANY-TO-ONE: Plusieurs tickets appartiennent à un user (créateur)
    // SQL: SELECT * FROM users WHERE id = ?
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ONE-TO-MANY: Un ticket a plusieurs messages
    // SQL: SELECT * FROM ticket_messages WHERE ticket_id = ? ORDER BY created_at ASC
    public function messages(): HasMany
    {
        return $this->hasMany(TicketMessage::class)->oldest();
    }

    // SCOPE: Seulement les tickets ouverts
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    // HELPER: Check si ticket est ouvert
    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    // HELPER: Check si ticket est fermé
    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }
}

