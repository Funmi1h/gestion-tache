@extends('layouts.auth')

@section('title', 'Créer un compte · Taskflow')

@section('form')
    <h1>Crée ton compte</h1>
    <p class="lead">Quelques secondes pour commencer à organiser tes projets.</p>

        @if (session('status'))
            <div class="alert alert-ok">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('register.store') }}" class="box">
            @csrf

            <div class="field">
                <label for="name">Nom</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" placeholder="Thossou" autocomplete="family-name" @class(['invalid' => $errors->has('name')])>
                @error('name')<span class="err">{{ $message }}</span>@enderror
            </div>

            <div class="field">
                <label for="first_name">Prénom</label>
                <input id="first_name" name="first_name" type="text" value="{{ old('first_name') }}" placeholder="Primaël" autocomplete="given-name" @class(['invalid' => $errors->has('first_name')])>
                @error('first_name')<span class="err">{{ $message }}</span>@enderror
            </div>

            <div class="field">
                <label for="email">Adresse e-mail</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="toi@exemple.com" autocomplete="email" @class(['invalid' => $errors->has('email')])>
                @error('email')<span class="err">{{ $message }}</span>@enderror
            </div>

            <div class="field">
                <label for="password">Mot de passe</label>
                <div class="pwdwrap">
                    <input id="password" name="password" type="password" placeholder="8 caractères minimum" autocomplete="new-password" @class(['invalid' => $errors->has('password')])>
                    <button type="button" class="eye" data-eye="password" aria-label="Afficher le mot de passe">Afficher</button>
                </div>
                <div class="meter" aria-hidden="true"><i></i><i></i><i></i><i></i></div>
                <p class="hint" data-hint></p>
                @error('password')<span class="err">{{ $message }}</span>@enderror
            </div>

            <div class="field">
                <label for="password_confirmation">Confirmer le mot de passe</label>
                <div class="pwdwrap">
                    <input id="password_confirmation" name="password_confirmation" type="password" placeholder="Répète ton mot de passe" autocomplete="new-password">
                    <button type="button" class="eye" data-eye="password_confirmation" aria-label="Afficher la confirmation">Afficher</button>
                </div>
                <span class="err" data-mismatch hidden>Les mots de passe ne correspondent pas.</span>
            </div>

            <label class="check">
                <input type="checkbox" required>
                <span>J’accepte les <a href="{{ route('home') }}">conditions d’utilisation</a> et la <a href="{{ route('home') }}">politique de confidentialité</a>.</span>
            </label>

            <button type="submit" class="btn btn-dark btn-block">Créer mon compte</button>
        </form>

        <p class="foot">Tu as déjà un compte ? <a href="{{ route('login') }}">Se connecter</a></p>
@endsection
