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

    }

    public function login(Request $request)
    {
        $request->validate([
            'email'=> 'request|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();
        
    }

    
}
