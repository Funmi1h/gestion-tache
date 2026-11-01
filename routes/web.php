<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TacheController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('/dashboard', 'dashboard')->name('dashboard');
Route::view('/projets', 'projects')->name('projects');
Route::view('/activite', 'activity')->name('activity');
Route::view('/equipe', 'team')->name('team');

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');

    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/taches', [TacheController::class, 'index'])->name('tasks');

Route::middleware('auth')->group(function () {
    Route::get('/taches/creer', [TacheController::class, 'create'])->name('tasks.create');
    Route::post('/taches', [TacheController::class, 'store'])->name('tasks.store');
    Route::get('/taches/{tache}/modifier', [TacheController::class, 'edit'])->name('tasks.edit');
    Route::get('/taches/{tache}', [TacheController::class, 'show'])->name('tasks.show');
    Route::patch('/taches/{tache}', [TacheController::class, 'update'])->name('tasks.update');
    Route::delete('/taches/{tache}', [TacheController::class, 'destroy'])->name('tasks.destroy');
    Route::patch('/taches/{tache}/statut', [TacheController::class, 'updateStatut'])->name('tasks.status');
});
