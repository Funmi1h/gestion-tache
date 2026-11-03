@extends('layouts.app')

@section('title', 'Bienvenue sur Taskflow · Gestion de tâches')

@section('content')
<div class="hero-landing">
    <span class="hero-badge">Collaboration simple & efficace</span>
    
    <h1 class="hero-title">Centralisez et organisez le travail de votre équipe.</h1>
    
    <p class="hero-subtitle">
        Créez vos équipes, invitez vos collaborateurs, répartissez les tâches et suivez la progression en temps réel.
    </p>

    <div class="hero-actions">
        @auth
            <a href="{{ route('dashboard') }}" class="btn btn-primary">
                Accéder à mon tableau de bord &rarr;
            </a>
            <a href="{{ route('equipes.index') }}" class="btn btn-outline">
                Mes Équipes
            </a>
        @else
            <a href="{{ route('register') }}" class="btn btn-primary">
                Créer un compte gratuitement
            </a>
            <a href="{{ route('login') }}" class="btn btn-outline">
                Se connecter
            </a>
        @endauth
    </div>
</div>
@endsection