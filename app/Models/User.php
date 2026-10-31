<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Santum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name','first_name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasApiTokens, Notifiable;

    //les atributs qu'on peut voir 
    protected $fillable =[
        'name',
        'first_name',
        'email',
        'password'
    ];

    //les attributs qui doivent etre cachés
    protected $hidden = [
        'password',
        'remember_token',
    ];


    // Les types de conversion (Casting) des colonnes.
     
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed', // Laravel s'assure automatiquement que le mot de passe reste sécurisé
    ];

    // un utilisateur appartient a plusieurs équipes 
    // On lie l'utilisateur au modèle Equipe en passant explicitement par la table 'membre_equipe'
    public function equipes(): BelongsToMany
    {
        return $this->belongsToMany(Equipe::class, 'membre_equipe', 'user_id', 'equipe_id')
        ->withPivot('role')
        ->withTimestamps();
    }
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
}
