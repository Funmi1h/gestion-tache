@extends('layouts.auth')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endpush

@section('title', 'Connexion · Taskflow')

@section('form')
<div class="auth-container auth-container--login">
    <div class="auth-header">
        <h1 class="auth-title">Connexion</h1>
        <p class="auth-subtitle">Accédez à votre espace d'équipe</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-error" style="margin-bottom:16px;">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('login.store') }}" class="auth-form" novalidate>
        @csrf

         {{-- on garde le token en mémoire pendant la connexion --}}
        @if(request('invitation_token'))
            <input type="hidden" name="invitation_token" value="{{ request('invitation_token') }}">
        @endif

        <div class="field">
            <label for="email">Adresse e-mail</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus class="auth-input">
        </div>

        <div class="field">
            <label for="password">Mot de passe</label>
            <div class="password-wrapper">
                <input id="password" type="password" name="password" required class="auth-input">
                <button type="button" class="btn-toggle-password" onclick="togglePasswordVisibility('password', this)" aria-label="Afficher le mot de passe" aria-pressed="false">
                    <svg class="eye-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                </button>
            </div>
        </div>

        <button type="submit" class="auth-btn-primary" style="margin-top:10px;">
            Se connecter
        </button>
    </form>

    <div class="auth-footer">
        Pas encore de compte ? <a href="{{ route('register') }}">S'inscrire</a>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function togglePasswordVisibility(inputId, button) {
        const input = document.getElementById(inputId);
        const isHidden = input.type === 'password';

        input.type = isHidden ? 'text' : 'password';
        button.setAttribute('aria-pressed', String(isHidden));
        button.setAttribute('aria-label', isHidden ? 'Masquer le mot de passe' : 'Afficher le mot de passe');
        button.innerHTML = isHidden
            ? `<svg class="eye-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>`
            : `<svg class="eye-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>`;
    }
</script>
@endpush