<?php

namespace App\Http\Controllers;

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

        //on créer le user
        $user = User::create([
            'name' => $request->nom,
            'first_name' => $request->prenom,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        //lorsque le user s'inscrit via un lien d'invitation
        //on vérifie d'abord si dans le formulaire d'inscription il y a un invitation_token qui a été envoyé
        if($request->filled('invitation_token')){
            $invitation = Invitation::where('token', $request->filled('invitation_token'))
            ->where('email', $request->email)
            ->where('date_expiration', '>', now())
            ->first();
        }

        if($invitation){
            //on lie le user a l'équipe auquel on a invité a rejoindre
            $user->equipes()->attach($invitation->equipe_id, ['role'=>'memebre']);
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
