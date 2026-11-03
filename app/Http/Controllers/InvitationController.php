<?php

namespace App\Http\Controllers;

use App\Mail\InvitationEquipe;
use App\Models\Equipe;
use App\Models\Invitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
class InvitationController extends Controller
{
    //crrer une invitation et envoyer le lien 
    public function creerInvitation($equipeId, $email, $inviteurId)
    {
        // creation de la ligne dans la table invitations
        $invitation = Invitation::create([
            'equipe_id' => $equipeId,
            'email' => $email,
            'inviteur_id' => $inviteurId,
            'token' => Str::random(32), // génération du token unique de 32 caractères
            'statut' => 'en_attente',
            'date_expiration' => now()->addDays(7), 
        ]);

        // envoi de l'e-mail officiel via le Mailable
        Mail::to($email)->send(new InvitationEquipe($invitation));

        return $invitation;
    }



    //je ne sais pas encore si on va l'utiliser mais ca permet d'ajouter un membre apres création de l'équipe 
    public function store(Request $request)
    {
        $request->validate([
            'equipe_id' => 'required|exists:equipes,id',
            'email' => 'required|email',
        ]);

        $equipe = Equipe::findOrFail($request->equipe_id);
        $user = $request->user();

        // vérifier si l'utilisateur est bien l'admin de l'équipe
        $membreConnecte = $equipe->membres()->where('user_id', $user->id)->first();
        if (!$membreConnecte || $membreConnecte->pivot->role !== 'admin') {
            return response()->json(['message' => 'Action non autorisée. Vous devez être l\'admin.'], 403);
        }

        //  la limite de 6 membres
        if ($equipe->membres()->count() >= 6) {
            return response()->json(['message' => 'L\'équipe a atteint sa limite de 6 membres.'], 422);
        }

        //on crre l'inviation
        $this->creerInvitation($equipe->id, $request->email, $user->id);

        return response()->json(['message' => 'Invitation envoyée avec succès !']);
    }
    // annuler une invitation (Réservé à l'ADMIN de l'équipe)
 
    public function annulerInvitation($id, Request $request)
    {
        $invitation = Invitation::findOrFail($id);
        $equipe = \App\Models\Equipe::findOrFail($invitation->equipe_id);

        // Sécurité : Seul le créateur/ADMIN de l'équipe peut annuler l'invitation
        // On va chercher le rôle de l'utilisateur connecté dans la table pivot pour cette équipe
        $membreConnecte = $equipe->membres()->where('user_id', $request->user()->id)->first();
        
        if (!$membreConnecte || $membreConnecte->pivot->role !== 'admin') {
            return response()->json(['message' => 'Action non autorisée. Vous devez être l\'admin de l\'équipe.'], 403);
        }

        if ($invitation->statut !== 'en_attente') {
            return response()->json(['message' => 'Cette invitation ne peut plus être annulée.'], 422);
        }

        // Changement de statut vers annulée
        $invitation->update(['statut' => 'annulee']);

        return response()->json(['message' => 'L\'invitation a été annulée avec succès.']);
    }

// marquer comme "expiree" les invitations dépassées de facon automatique 
 
    public function nettoyerInvitationsExpirees()
    {
        $nombreModifie = Invitation::where('statut', 'en_attente')
            ->where('date_expiration', '<', now())
            ->update(['statut' => 'expiree']);

        return response()->json([
            'message' => 'Mise à jour effectuée.',
            'invitations_expirees_detectees' => $nombreModifie
        ]);
    }



       
     //Valide l'invitation et intègre l'utilisateur connecté dans l'équipe (CDC §5.1 & §3.2)
     
    public function accepterInvitation($token, Request $request)
    {
        // récupération de l'invitation valide
        $invitation = Invitation::with('equipe')
            ->where('token', $token)
            ->where('statut', 'en_attente')
            ->where('date_expiration', '>', now())
            ->firstOrFail();

        $user = $request->user();

        // Sécurité : L'utilisateur connecté doit posséder l'adresse e-mail invitée
        if ($user->email !== $invitation->email) {
            return redirect()->route('invitations.rejoindre', $token)
                ->with('error', 'Cette invitation est réservée à l\'adresse ' . $invitation->email . '.');
        }

        // vérifier que l'équipe n'a pas atteint la limite de 6 membres
        if ($invitation->equipe->membres()->count() >= 6) {
            return redirect()->route('dashboard')
                ->with('error', 'Désolé, cette équipe a déjà atteint la limite maximale de 6 membres.');
        }

        // ajouter l'utilisateur à l'équipe avec le rôle "membre"
        // syncWithoutDetaching évite les doublons si l'utilisateur en faisait déjà partie
        $invitation->equipe->membres()->syncWithoutDetaching([
            $user->id => ['role' => 'membre']
        ]);

        // marquer l'invitation comme acceptée
        $invitation->update(['statut' => 'acceptee']);

        // rediriger vers la page de l'équipe avec confirmation
        return redirect()->route('dashboard', $invitation->equipe_id)
            ->with('success', 'Félicitations ! Vous avez rejoint l\'équipe ' . $invitation->equipe->nom . ' avec succès.');
    }
}
