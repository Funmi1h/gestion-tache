<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

//pour le lien d'invitation
//en fait la fonction prend un token (le token du lien d'invitation )
Route::get('/invitation/accepter{token}', function($token){
    $invitation = Invitation::with('equipe', 'inviteur')
    ->where('token', $token)
    ->where('statut', 'en_attente')
    ->where('date_expiration', now())
    ->firstOrFail();

    return 
    view('invitations.rejoindre', compact('invitation'))
})->name('invitation_rejoindre');

Route::view('/dashboard', 'dashboard.index')->name('dashboard');

//les routes pour les taches
Route::view('/taches', 'tasks')->name('tasks');

//les routes pour les équipes
Route::view('/equipe/create', 'equipes.create')->name('equipes.create');
Route::view('/equipe', 'equipes.index')->name('equipes.');

//en fait ce middleware redirige vers le dashboard quand la personne est déja connectée
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');

    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


