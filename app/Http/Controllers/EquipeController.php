<?php

namespace App\Http\Controllers;
use Api\Models\Equipe;
use App\Mail\InvitationEquipe;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class EquipeController extends Controller
{
    //
    //fonction pour créer une équipe: on a besoin du nom de l'équipe, dÄ'une description et de l'id de la personne qui crée
    //cette fonction va permettre aussi d'envoyer les invitations aux mails entreer dans le formulaire d création d'équipe
    public function store(Request $request)
    {
        $request->validate([
            'nom'=> 'required|string|max:255',
            'description'=>'nullable|string',
            'emails'=>'nullable|array',
            'emails.*'=>'required|email'//le * c'est pour dire chaque elément du tableau
        ]);

        //on recupere le user connecté parce que on aura besoin de son id 
        $user = $request->user;

        // le tableau d'e-mails à inviter (s'il est vide, on prend un tableau vide par défaut)
        $emailsAInviter = $request->input('emails', []);
        // ca la c'est la regle métier qu'on avait definit Le créateur (1) + le nombre d'invités ne doit pas dépasser 6 au total
        if (count($emailsAInviter) + 1 > 6) {
            return response()->json([
                'message' => 'Une équipe ne peut pas dépasser 6 membres, invitations en attente incluses.'
            ], 422);
        }

        //utilisation d'une transaction pour que la création d'une équipe soit égal a la liaison automatique du user qi cré a l'équipe et la definition de son role
        //la creation d'une equipe soit égale aussi a l'envoi des mails d'invitation
        $equipe = DB::transaction (function() use ($request, $user){
        //on créer d'une nouvelle ligne dans equipes
        $equipe = Equipe::create([
            'nom' =>request->nom,
            'description' => $request->description,
            'createur_id'=> $user->id,
        ]);

        //on met le créateur de l'équipe dans la table pivot memebre_equipe et on lui donne le role d'admin
        $equipe->membres()->attach($user->id, ['role'=>'admin']);

        $invitationController = new InvitationController();

        //on fait une bocle sur le tableau des emails pour créer et envoyer les emails
        foreach ($emailsAInviter as $email) {
                //appelles de la fonction du controller invitation pour créer l'invitaion et les envoyer 

                $invitationController->creerInvitation($equipe->id, $email, $user->id);
        }




        return $equipe;
        
        });

        
        return response()->json([
            'message' => 'Équipe créée avec succès et vous en êtes l’ADMIN.',
            'equipe' => $equipe->load('membres', 'invitations') // on charge la liste des membres pour la réponse
        ], 201);

        
    }

    //consulter la liste de ses equipes
    public function index(Request $request){
        $equipes = $request->user()->equipes()->with('membres')->get();
    }

    //consulter une equipe précise et ses membres
    
    public function show($id, Request $request){
        $equipe = Equipe::with('membres')->findOrFail($id);

        //pr  mesure de sécurité on vérifie si la personne connectée ne  fait pas partie de l'équipe
        if(!$equipe->membres->contains($request->user()->id)){
            return response()->json([
                'message'=> 'Accès non autorisé à cette équipe.'
            ], 403);
        }

        return response()->json($equipe);
    }
}
