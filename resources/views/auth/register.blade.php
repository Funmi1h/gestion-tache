@extends('layouts.auth')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endpush

@section('title', 'Inscription · Taskflow')

@section('form')
<div class="auth-container" style="max-width: 480px;">
    <div class="auth-header">
        <h1 class="auth-title">Créer un compte</h1>
        <p class="auth-subtitle">Rejoignez vos équipes sur Taskflow</p>
    </div>

    @if ($errors->any())
        <div style="background:#fee2e2; color:#991b1b; padding:10px; border-radius:8px; margin-bottom:16px; font-size:13px;">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('register.store') }}" class="auth-form">
        @csrf
        {{-- Token d'invitation passé automatiquement si présent --}}
        <input type="hidden" name="invitation_token" value="{{ old('invitation_token', request('invitation_token', $invitationToken ?? '')) }}">

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px;">
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

        <div class="field">
            <label for="password">Mot de passe * (min. 8 caractères)</label>
            <input id="password" name="password" type="password" required class="auth-input">
        </div>

        <div class="field">
            <label for="password_confirmation">Confirmer le mot de passe *</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required class="auth-input">
        </div>

        <button type="submit" class="auth-btn-primary" style="margin-top:10px;">
            Créer mon compte
        </button>
    </form>

    <div class="auth-footer">
        Déjà un compte ? <a href="{{ route('login') }}">Se connecter</a>
    </div>
</div>
@endsection