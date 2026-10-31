@extends('layouts.app')

@section('title', 'Taskflow — Gérez vos tâches')

@section('content')
    <div class="hero">
        <p class="sub" style="margin-bottom:22px">Nouveau — organisation simple des tâches</p>

        <h1>Organisez votre travail,<br>sans effort.</h1>

        <p class="lead">
            Taskflow réunit vos tâches, vos projets et votre équipe
            en un seul endroit. Simple, rapide et clair.
        </p>

        <div class="actions">
            <a href="{{ url('/register') }}" class="btn btn-dark">Créer un compte</a>
            <a href="{{ url('/login') }}" class="btn">Se connecter</a>
        </div>

        <p class="legal">
            En continuant, vous acceptez nos
            <a href="{{ url('/conditions') }}">conditions d'utilisation</a> et notre
            <a href="{{ url('/confidentialite') }}">politique de confidentialité</a>.
        </p>
    </div>
@endsection
