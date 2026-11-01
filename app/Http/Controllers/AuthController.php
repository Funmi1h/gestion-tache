<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Invitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showRegister(Request $request)
    {
        return view('auth.register', [
            'invitationToken' => $request->query('invitation_token'),
        ]);
    }

    // la fonction d'inscription
    public function register(Request $request)
    {
        // on vérifie les données
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'invitation_token' => 'nullable|string',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'first_name' => $validated['first_name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);


        if (!empty($validated['invitation_token'])) {
            $invitation = Invitation::where('token', $validated['invitation_token'])
                ->where('email', $validated['email'])
                ->where('statut', 'en_attente')
                ->where('date_expiration', '>', now())
                ->first();

            if ($invitation) {
                $user->equipes()->attach($invitation->equipe_id, ['role' => 'membre']);
                $invitation->update(['statut' => 'acceptee']);
            }
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()
            ->route('dashboard')
            ->with('status', 'Compte créé avec succès. Bienvenue sur Taskflow !');
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'Identifiants incorrects.']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
