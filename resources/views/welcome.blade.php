@extends('layouts.app')

@section('title', 'Bienvenue sur Taskflow · Gestion de tâches')

@section('content')
<div class="welcome-hero">
    {{-- logo agrandi descendu un peu vers le bas --}}
    <div class="welcome-logo-box">
        <span class="welcome-logo-ball"></span>
        <span class="welcome-logo-text">Taskflow</span>
    </div>

    {{-- textes a droite avec boutons en dessous --}}
    <div class="welcome-content">
        <h1 class="welcome-title">Centralisez et organisez le travail de votre équipe.</h1>
        
        <p class="welcome-subtitle">
            Créez vos équipes, invitez vos collaborateurs, répartissez les tâches et suivez la progression en temps réel.
        </p>

        <div class="welcome-actions">
            @auth
                <a href="{{ route('dashboard') }}" class="btn btn-primary">
                    Tableau de bord &rarr;
                </a>
                <a href="{{ route('equipes.index') }}" class="btn btn-outline">
                    Mes Équipes
                </a>
            @else
                <a href="{{ route('register') }}" class="btn btn-primary">
                    S'inscrire
                </a>
                <a href="{{ route('login') }}" class="btn btn-outline">
                    Connexion
                </a>
            @endauth
        </div>
    </div>
</div>
@endsection