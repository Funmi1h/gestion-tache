{{-- Layout principal de l'application : coque, navigation et en-tête communs --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Taskflow')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/taskflow.css') }}">
    @stack('styles')
</head>
<body>
<div class="shell @yield('shell_class')">
    <header class="top">
        <a href="{{ route('home') }}" class="brand">
            <span class="logo"></span>
            Taskflow
        </a>
        <nav class="tabs" aria-label="Navigation principale">
            @php
                $tabs = [
                    'Aperçu' => 'dashboard',
                    'Projets' => 'projects',
                    'Tâches' => 'tasks',
                    'Activité' => 'activity',
                    'Équipe' => 'team',
                ];
            @endphp
            @foreach ($tabs as $label => $name)
                <a href="{{ route($name) }}" @class(['on' => request()->routeIs($name)])>{{ $label }}</a>
            @endforeach
        </nav>
        <div class="aside">
            @auth
                <div class="search">Rechercher…</div>
                <a href="#" class="btn btn-dark btn-sm">Ajouter une tâche</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="link" style="cursor:pointer;border:0;background:none;font:inherit">Déconnexion</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="link">Connexion</a>
                <a href="{{ route('register') }}" class="btn btn-sm">S'inscrire</a>
            @endauth
        </div>
    </header>

    <main>
        @hasSection('heading')
            <div class="head">
                <h1>@yield('heading')</h1>
                @yield('actions')
            </div>
        @endif

        @yield('content')
    </main>
</div>

@stack('scripts')
</body>
</html>
