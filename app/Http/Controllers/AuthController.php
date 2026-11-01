<?php

namespace App\Http\Controllers;
use Api\Models\User;
use Api\Models\Invitation;
use Illuminate\Support\Facades\Hash;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    //la fonction d#inscription 
    public function register(Request $request)
    {
        //on vérfie les données  
        $request->validate([
            'name' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'invitation_token' => 'nullable|string', // optionnel dans le cas ou la personne s'inscrit depuis l'interface sans lien d#invitation
        ]);

        $invitation = null;
        //on vérifie d'abord si dans le formulaire d'inscription il y a un invitation_token qui a été envoyé
        if($request->filled('invitation_token')){
            $invitation = Invitation::where('token', $request->filled('invitation_token'))
            ->where('email', $request->email)
            ->where('date_expiration', '>', now())
            ->first();

            //lorsque le token est foruni par le formulaire et queil y a pas d'invitation associé 
            if (!$invitation) {
            return response()->json(['message' => 'L\'invitation est invalide ou a expiré.'], 422);
            }
            

            // ca c'est une regle métier e-mail saisi doit être celui qui a été invité
            if ($request->email !== $invitation->email) {
                return response()->json(['message' => 'Cet e-mail ne correspond pas à l\'invitation reçue.'], 403);
                }


                
            // Vérification de la limite des 6 membres avant d'autoriser l'inscription: on vérifie deja le nombre de mails envoyés mais imagine quelqu'un n'a pas de mail et contourne 
            if ($invitation->equipe->membres()->count() >= 6) {
                return response()->json(['message' => 'L\'équipe a déjà atteint sa limite de 6 membres.'], 422);
            }
            }
    

        //on créer le user
        $user = User::create([
            'name' => $request->nom,
            'first_name' => $request->prenom,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);


        //genre quand l'inivtation est valide on fait a liaison en meme temps avec l'équipe
        if($invitation){
            //on lie le user a l'équipe auquel on a invité a rejoindre
            $user->equipes()->attach($invitation->equipe_id, ['role'=>'membre']);
            //on change le statut de l'utilisateur 
            $invitation->update(['statut'=> 'acceptee']);
        }


        //on genere le token parce que vu que la personne vient de se connecter on ne va pas  lui demander de se reconnecter (vu que'on envoie meme pas de mail)
        $token = $user->createToken('auth_token')->plaintTextToken;
        
        return response()->json([
            'message'=>'Compte créé avec succes',
            'access_token'=>$token,
            'user'=> $user
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'=> 'request|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();
        if(!user || ! Hash::check($request->password,$user->password)){
            throw ValidationException::withMessages([
                'email'=> ['Les identifiants fournis sont incorrects.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plantTextToken;

        return response()->json([
            'access_token'=>$token,
            'token_type'=> 'Bearer',
            'user'=>$user
        ]);
    }


    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Déconnexion réussie'
        ]);
    }
    
}
