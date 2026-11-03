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



    // pour ajouter un membre apres creation de l'equipe
    public function store(Request $request, $equipe = null)
    {
        $equipeId = $equipe ?? $request->input('equipe_id');
        $validated = $request->validate([
            'email' => 'required|email',
        ], [
            'email.required' => 'Veuillez saisir une adresse e-mail.',
            'email.email' => 'Veuillez saisir une adresse e-mail valide.',
        ]);

        $equipe = Equipe::findOrFail($equipeId);
        $user = $request->user();

        // on verifie si l'utilisateur est admin ou createur de l'equipe
        $membreConnecte = $equipe->membres()->where('user_id', $user->id)->first();
        $isAdmin = ($membreConnecte && strtolower($membreConnecte->pivot->role) === 'admin') || ((int) $equipe->createur_id === (int) $user->id);
        if (!$isAdmin) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Action non autorisée. Vous devez être l\'admin.'], 403);
            }
            abort(403, 'Action non autorisée. Vous devez être l\'admin de l\'équipe.');
        }

        // verifier si l'utilisateur est deja membre
        if ($equipe->membres()->where('email', $validated['email'])->exists()) {
            return back()->withErrors(['email' => 'Cet utilisateur fait déjà partie de l\'équipe.']);
        }

        // verifier si une invitation en attente existe deja
        $dejaInvite = $equipe->invitations()
            ->where('email', $validated['email'])
            ->where('statut', 'en_attente')
            ->where('date_expiration', '>', now())
            ->exists();
        if ($dejaInvite) {
            return back()->withErrors(['email' => 'Une invitation est déjà en cours pour cette adresse e-mail.']);
        }

        // verification de la limite des 6 places
        $totalOccupes = $equipe->membres()->count() + $equipe->invitations()->where('statut', 'en_attente')->count();
        if ($totalOccupes >= 6) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'L\'équipe a atteint sa limite de 6 membres.'], 422);
            }
            return back()->withErrors(['email' => 'L\'équipe a déjà atteint sa limite maximale de 6 membres.']);
        }

        // on cree l'invitation et envoie le mail
        $this->creerInvitation($equipe->id, $validated['email'], $user->id);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Invitation envoyée avec succès !']);
        }

        return back()->with('status', 'Invitation envoyée avec succès.');
    }

    // annuler une invitation (pour l'admin de l'équipe)
    public function annulerInvitation($id, Request $request)
    {
        $invitation = Invitation::findOrFail($id);
        $equipe = Equipe::findOrFail($invitation->equipe_id);

        // seul le createur ou l'admin peut annuler
        $membreConnecte = $equipe->membres()->where('user_id', $request->user()->id)->first();
        $isAdmin = ($membreConnecte && strtolower($membreConnecte->pivot->role) === 'admin') || ((int) $equipe->createur_id === (int) $request->user()->id);
        
        if (!$isAdmin) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Action non autorisée. Vous devez être l\'admin de l\'équipe.'], 403);
            }
            abort(403, 'Action non autorisée. Vous devez être l\'admin de l\'équipe.');
        }

        if ($invitation->statut !== 'en_attente') {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Cette invitation ne peut plus être annulée.'], 422);
            }
            return back()->withErrors(['error' => 'Cette invitation ne peut plus être annulée.']);
        }

        // changement de statut vers annulee
        $invitation->update(['statut' => 'annulee']);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'L\'invitation a été annulée avec succès.']);
        }

        return back()->with('status', 'L\'invitation a été annulée avec succès.');
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
