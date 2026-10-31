@extends('layouts.app')

@section('title', 'Connexion — Taskflow')

@section('content')
    <div class="hero">
        <h1>Content de vous revoir.</h1>

        <p class="lead">Connectez-vous pour retrouver vos tâches.</p>

        @if (session('status'))
            <div class="alert alert-ok">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="field">
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}"
                       class="input" required autofocus autocomplete="email">
            </div>

            <div class="field">
                <label for="password">Mot de passe</label>
                <input id="password" type="password" name="password"
                       class="input" required autocomplete="current-password">
            </div>

            <div class="actions" style="margin-top:24px">
                <button type="submit" class="btn btn-dark">Se connecter</button>
                <a href="{{ route('home') }}" class="btn">Annuler</a>
            </div>
        </form>

        <p class="form-alt">
            Pas encore de compte ? <a href="{{ route('register') }}">Créer un compte</a>
        </p>
    </div>
@endsection
