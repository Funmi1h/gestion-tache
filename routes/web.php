<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TacheController;
use App\Http\Controllers\EquipeController;
use App\Http\Controllers\InvitationController;
use App\Models\Invitation;
use Illuminate\Support\Facades\Route;

//les routes publiques d'abord 

Route::get('/', function () {
    return view('welcome');
})->name('home');

// le lien pour accepter une invitation a rejoindre une équipe
Route::get('/invitations/accepter/{token}', function ($token) {
    $invitation = Invitation::with('equipe', 'inviteur')
        ->where('token', $token)
        ->where('statut', 'en_attente')
        ->where('date_expiration', '>', now())
        ->firstOrFail();

    return view('invitations.rejoindre', compact('invitation'));
})->name('invitations.rejoindre');


//ces pages sont pour les visiteurs non connetés on les redirige vers ici quand ils veulent avoir acces a des pages ou il faudrait se connectér 
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');

    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});


//les pages auquel l'aaces est restreint a une authentification
Route::middleware('auth')->group(function () {

    Route::post('/invitations/accepter/{token}', [InvitationController::class, 'accepterInvitation'])->name('invitations.valider');
    
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    

    //crud pour les équipes 
    Route::get('/equipes', [EquipeController::class, 'index'])->name('equipes.index');
    Route::get('/equipes/creer', [EquipeController::class, 'create'])->name('equipes.create');
    Route::post('/equipes', [EquipeController::class, 'store'])->name('equipes.store');
    Route::get('/equipes/{equipe}', [EquipeController::class, 'show'])->name('equipes.show');
    
    //crud des taches 
    Route::get('/taches', [TacheController::class, 'index'])->name('tasks');
    Route::get('/taches/creer', [TacheController::class, 'create'])->name('tasks.create');
    Route::post('/taches', [TacheController::class, 'store'])->name('tasks.store');
    Route::get('/taches/{tache}/modifier', [TacheController::class, 'edit'])->name('tasks.edit');
    Route::get('/taches/{tache}', [TacheController::class, 'show'])->name('tasks.show');
    Route::patch('/taches/{tache}', [TacheController::class, 'update'])->name('tasks.update');
    Route::delete('/taches/{tache}', [TacheController::class, 'destroy'])->name('tasks.destroy');
    Route::patch('/taches/{tache}/statut', [TacheController::class, 'updateStatut'])->name('tasks.status');
});