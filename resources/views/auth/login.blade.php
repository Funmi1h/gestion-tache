@extends('layouts.auth')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endpush

@section('title', 'Connexion · Taskflow')

@section('form')
<div class="auth-container">
    <div class="auth-header">
        <h1 class="auth-title">Connexion</h1>
        <p class="auth-subtitle">Accédez à votre espace d'équipe</p>
    </div>

    @if ($errors->any())
        <div style="background:#fee2e2; color:#991b1b; padding:10px; border-radius:8px; margin-bottom:16px; font-size:13px;">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('login.store') }}" class="auth-form">
        @csrf

        <div class="field">
            <label for="email">Adresse e-mail</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus class="auth-input">
        </div>

        <div class="field">
            <label for="password">Mot de passe</label>
            <input id="password" type="password" name="password" required class="auth-input">
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