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

        $invitation = null;
        //on vérifie d'abord si dans le formulaire d'inscription il y a un invitation_token qui a été envoyé
        if ($request->filled('invitation_token')) {
            $invitation = Invitation::where('token', $validated['invitation_token'])
                ->where('statut', 'en_attente')
                ->where('date_expiration', '>', now())
                ->first();

            // lorsque le token est fourni par le formulaire et qu'il n'y a pas d'invitation associée
            if (!$invitation) {
                return back()
                    ->withInput($request->except('password', 'password_confirmation'))
                    ->withErrors(['invitation_token' => 'L\'invitation est invalide ou a expiré.']);
            }

            // l'e-mail saisi doit être celui qui a été invité
            if ($validated['email'] !== $invitation->email) {
                return back()
                    ->withInput($request->except('password', 'password_confirmation'))
                    ->withErrors(['email' => 'Cet e-mail ne correspond pas à l\'invitation reçue.']);
            }

            // Vérification de la limite des 6 membres avant d'autoriser l'inscription
            if ($invitation->equipe->membres()->count() >= 6) {
                return back()
                    ->withInput($request->except('password', 'password_confirmation'))
                    ->withErrors(['invitation_token' => 'L\'équipe a déjà atteint sa limite de 6 membres.']);
            }
        }

        $user = User::create([
            'name' => $validated['name'],
            'first_name' => $validated['first_name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        //genre quand l'inivtation est valide on fait a liaison en meme temps avec l'équipe
        if ($invitation) {
            //on lie le user a l'équipe auquel on a invité a rejoindre
            $user->equipes()->attach($invitation->equipe_id, ['role' => 'membre']);
            //on change le statut de l'utilisateur 
            $invitation->update(['statut' => 'acceptee']);
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
