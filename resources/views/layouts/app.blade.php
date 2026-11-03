<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Taskflow · Gestion de tâches')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    
    {{-- Feuille de style globale --}}
    <link rel="stylesheet" href="{{ asset('css/taskflow.css') }}">
    
    {{-- Emplacement pour charger le CSS spécifique à chaque page --}}
    @stack('styles')
</head>
<body>
<div class="app-shell">

    {{-- En-tête & Barre de navigation --}}
    <header class="top-nav">
        <a href="{{ route('home') }}" class="brand">
            <span class="logo"></span>
            Taskflow
        </a>


        <nav class="nav-tabs" aria-label="Navigation principale">
            @php
                $tabs = [
                    'Tableau de bord' => 'dashboard',
                    'Mes Équipes'     => 'equipes.index',
                    'Tâches'          => 'tasks',
                ];
            @endphp
            @foreach ($tabs as $label => $name)
                <a href="{{ Route::has($name) ? route($name) : '#' }}" 
                   @class(['nav-link', 'active' => request()->routeIs($name . '*')])>
                    {{ $label }}
                </a>
            @endforeach
        </nav>

        <div class="user-actions">
            @auth
                <span class="user-name">
                    {{ auth()->user()->first_name ?? auth()->user()->name }}
                </span>
                <form method="POST" action="{{ route('logout') }}" class="logout-form">

                    @csrf
                    <button type="submit" class="btn-logout">Déconnexion</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="btn-link">Connexion</a>
                <a href="{{ route('register') }}" class="btn btn-primary btn-sm">S'inscrire</a>
            @endauth
        </div>
    </header>

    {{-- Contenu principal --}}
    <main class="main-content">
        @hasSection('heading')
            <div class="page-heading">
                <h1>@yield('heading')</h1>
                <div class="heading-actions">@yield('actions')</div>
            </div>
        @endif

        {{-- Messages Flash globaux --}}
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-error">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-error">
                @if ($errors->count() === 1)
                    {{ $errors->first() }}
                @else
                    <ul style="margin: 0; padding-left: 20px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endif

        @yield('content')
    </main>

</div>

@stack('scripts')
</body>
</html>