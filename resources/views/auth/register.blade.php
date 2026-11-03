@extends('layouts.auth')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endpush

@section('title', 'Inscription · Taskflow')

@section('form')
<div class="auth-container auth-container--wide">
    <div class="auth-header">
        <h1 class="auth-title">Créer un compte</h1>
        <p class="auth-subtitle">Rejoignez vos équipes sur Taskflow</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-error" style="margin-bottom:16px;">
            @if ($errors->count() === 1)
                {{ $errors->first() }}
            @else
                <ul style="margin: 0; padding-left: 18px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endif

    <form method="POST" action="{{ route('register.store') }}" class="auth-form" novalidate>
        @csrf
        {{-- on donne le token dans un input hidden --}}
        <input type="hidden" name="invitation_token" value="{{ old('invitation_token', request('invitation_token', $invitationToken ?? '')) }}">

        <div class="auth-name-grid">
            <div class="field">
                <label for="name">Nom *</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus class="auth-input">
            </div>
            <div class="field">
                <label for="first_name">Prénom *</label>
                <input id="first_name" name="first_name" type="text" value="{{ old('first_name') }}" required class="auth-input">
            </div>
        </div>

        <div class="field">
            <label for="email">Adresse e-mail *</label>
            <input id="email" name="email" type="email" value="{{ old('email', request('email')) }}" required class="auth-input">
        </div>

        {{-- 1. Mot de passe --}}
        <div class="field">
            <label for="password">Mot de passe * (min. 8 caractères)</label>
            <div class="password-wrapper">
                <input id="password" name="password" type="password" required class="auth-input">
                <button type="button" class="btn-toggle-password" onclick="togglePasswordVisibility('password', this)" aria-label="Afficher/Masquer le mot de passe">
                    {{-- Icône œil ouvert par défaut --}}
                    <svg class="eye-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                </button>
            </div>
        </div>

        {{-- 2. Confirmer le mot de passe --}}
        <div class="field">
            <label for="password_confirmation">Confirmer le mot de passe *</label>
            <div class="password-wrapper">
                <input id="password_confirmation" name="password_confirmation" type="password" required class="auth-input">
                <button type="button" class="btn-toggle-password" onclick="togglePasswordVisibility('password_confirmation', this)" aria-label="Afficher/Masquer la confirmation">
                    <svg class="eye-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                </button>
            </div>
        </div>

        <button type="submit" class="auth-btn-primary" style="margin-top:10px;">
            Créer mon compte
        </button>
    </form>

    <div class="auth-footer">
        Déjà un compte ? <a href="{{ route('login') }}">Se connecter</a>
    </div>
</div>



@push('scripts')
<script>
    function togglePasswordVisibility(inputId, button) {
        const input = document.getElementById(inputId);
        
        // Icône œil ouvert
        const eyeOpen = `
            <svg class="eye-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8z"></path>
                <circle cx="12" cy="12" r="3"></circle>
            </svg>
        `;
        
        // Icône œil barré
        const eyeClosed = `
            <svg class="eye-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                <line x1="1" y1="1" x2="23" y2="23"></line>
            </svg>
        `;

        if (input.type === 'password') {
            input.type = 'text';
            button.innerHTML = eyeClosed;
        } else {
            input.type = 'password';
            button.innerHTML = eyeOpen;
        }
    }
</script>
@endpush
@endsection

