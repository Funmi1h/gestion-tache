<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MembreEquipe extends Model
{
    protected $table = 'membre_equipe';

    protected $fillable = [
        'user_id',
        'equipe_id',
        'role',
    ];

    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function equipe(): BelongsTo
    {
        return $this->belongsTo(Equipe::class, 'equipe_id');
    }

    public function taches(): HasMany
    {
        return $this->hasMany(Tache::class, 'membre_equipe_id');
    }
}
