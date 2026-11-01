<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tache extends Model
{
    protected $fillable = [
        'equipe_id',
        'createur_id',
        'membre_equipe_id',
        'titre',
        'description',
        'date_echeance',
        'statut',
    ];

    protected function casts(): array // cast sert a formater la date dans un format lisible
    {
        return [
            'date_echeance' => 'date',
        ];
    }

    public function equipe(): BelongsTo
    {
        return $this->belongsTo(Equipe::class, 'equipe_id');
    }

    public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'createur_id');
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(MembreEquipe::class, 'membre_equipe_id');
    }
}
