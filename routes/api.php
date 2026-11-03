<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EquipeController;
use App\Http\Controllers\InvitationController;

//les routes d'authentification
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);



//la route temporaire pour tester l'envoi de mail
Route::get('/test-email', function(){
    Mail::raw('Felicitations! La configuration de Mailpit fonctionne parfaitement' , function($message){
        $message->to('ak.heloise2007@gmail.com')
        ->subject('Test d\'envoi d\'email -Gestion de taches ');
    });

    return response()->json(['message' => 'L\'e-mail de test a été envoyé ! Vérifie sur http://localhost:8025']);
        
});


//routes sécurisées
Route::middleware('auth:sanctum')->group(function () {
    
    // route de deconnexion
    Route::post('/logout', [AuthController::class, 'logout']);
    
    //route de profil de l'utilisateur 
    Route::get('/user', function (Request $request) {
        return response()->json([
            'user' => $request->user(),
            'equipes' => $request->user()->equipes()->with('membres')->get()
        ]);
    });

    // ici c'est pour gérer les équipes
    //on créer les équipes et on envoi l'invitation
    Route::post('/equipes', [EquipeController::class, 'store']);  
    //voir tous ces equipe si on en a plusierus
    Route::get('/equipes', [EquipeController::class, 'index']);        
    //voir les details et les membres d'une équipe
    Route::get('/equipes/{id}', [EquipeController::class, 'show']);    
    


    //ici on gere les invitations 

     // actions sur les invitations
    Route::post('/invitations/accepter', [InvitationController::class, 'accepterInvitation']);
    Route::post('/invitations/{id}/annuler', [InvitationController::class, 'annulerInvitation']);
    
    // Permet de forcer manuellement le nettoyage des invitations périmées
    Route::post('/invitations/nettoyer-expirees', [InvitationController::class, 'nettoyerInvitationsExpirees']);
});


