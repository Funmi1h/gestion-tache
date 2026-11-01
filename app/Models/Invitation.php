<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invitation extends Model
{
    protected $fillable = [
        'equipe_id', 
        'email', 
        'inviteur_id', 
        'token', 
        'statut', 
        'date_expiration'
    ];

    // On force Laravel à traiter ce champ comme un objet de date Carbon/PHP manipulable
    protected $casts = [
        'date_expiration' => 'datetime',
    ];

        // 'invitation est liée à UNE seule équipe "belongsTo" car la table 'invitations' contient 'equipe_id'
    public function equipe(): BelongsTo
    {
        return $this->belongsTo(Equipe::class, 'equipe_id');
    }

    // L'invitation a été créée par UN seul utilisateur (l'inviteur)
    //"belongsTo" car la table 'invitations' contient 'inviteur_id'
     
    public function inviteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inviteur_id');
    }
}
