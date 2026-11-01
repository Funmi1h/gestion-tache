<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Equipe extends Model
{
    
    protected $table = 'equipes';

    protected $fillable = ['nom', 'description', 'createur_id'];

    //une equipe appartient a un seul createur 
    public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'createur_id');
    }

    //une equipe a plusieurs memebres
    public function membres(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'membre_equipe', 'equipe_id', 'user_id')
                    ->withPivot('role') // permet d'accéder à la colonne "role" de la table intermédiaire
                    ->withTimestamps();
    }

    //une equipe peut avoir emis plusieurs invitations 
    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class, 'equipe_id');
    }
}
