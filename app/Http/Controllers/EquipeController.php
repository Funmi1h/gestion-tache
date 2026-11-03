<?php

namespace App\Http\Controllers;

use App\Models\Equipe; 
use App\Models\Invitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EquipeController extends Controller
{
    // pour lister les équipes de l'utilisateur connecté 
    public function index(Request $request)
    {
        $equipes = $request->user()->equipes()->with('membres')->get();

        if ($request->expectsJson()) {
            return response()->json($equipes);
        }

        return view('equipes.index', compact('equipes'));
    }

        //afficher le formulaire de création 
    public function create()
    {
        return view('equipes.create');
    }

    // enregistrer les equipes et envoyer les invitations 
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom'         => 'required|string|max:255',
            'description' => 'nullable|string',
            'emails'      => 'nullable|array',
            'emails.*'    => 'nullable|email',
        ], [
            'nom.required'   => 'Le nom de l\'équipe est obligatoire.',
            'nom.max'        => 'Le nom de l\'équipe ne peut pas dépasser 255 caractères.',
            'emails.*.email' => 'Veuillez renseigner une adresse e-mail valide pour chaque invité.',
        ]);

        $user = $request->user();

        // pour filtrer les emails non vides
        $emailsAInviter = array_filter($validated['emails'] ?? []);

        //le créateur  + invitations ne doit pas dépasser 6 au total
        if (count($emailsAInviter) + 1 > 6) {
            return back()
                ->withInput()
                ->withErrors(['emails' => 'Une équipe ne peut pas dépasser 6 membres, invitations incluses.']);
        }

        // on fait une transaction DB : Création équipe + Attachement ADMIN + Invitations
        $equipe = DB::transaction(function () use ($validated, $user, $emailsAInviter) {
            // Création de l'équipe
            $equipe = Equipe::create([
                'nom'         => $validated['nom'],
                'description' => $validated['description'] ?? null,
                'createur_id' => $user->id,
            ]);

            // créateur devient ADMIN automatiquement (
            $equipe->membres()->attach($user->id, ['role' => 'admin']);

            // Envoi des invitations
            $invitationController = new InvitationController();
            foreach ($emailsAInviter as $email) {
                $invitationController->creerInvitation($equipe->id, $email, $user->id);
            }

            return $equipe;
        });

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Équipe créée avec succès.',
                'equipe' => $equipe->load('membres', 'invitations')
            ], 201);
        }

        return redirect()
            ->route('equipes.show', $equipe->id)
            ->with('success', 'Équipe créée avec succès !');
    }

    // consulter une équipe précise et ses membres 
    public function show($id, Request $request)
    {
        $equipe = Equipe::with(['membres', 'invitations'])->findOrFail($id);

        // vérifier que la personne connectée fait bien partie de l'équipe
        if (!$equipe->membres->contains('id', $request->user()->id)) {
            abort(403, 'Accès non autorisé à cette équipe.');
        }

        if ($request->expectsJson()) {
            return response()->json($equipe);
        }

        return view('equipes.show', compact('equipe'));
    }
}