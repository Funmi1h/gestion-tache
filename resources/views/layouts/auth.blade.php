{{-- ressources partagées pour les pages d'authentification --}}
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
<body class="authpage">
<div class="shell">
    <header class="top">
        <a class="brand" href="{{ route('home') }}"><span class="logo"></span>Taskflow</a>
        <div class="aside">
            @hasSection('aside')
                @yield('aside')
            @else
                <span class="txt">Déjà un compte ?</span>
                <a class="btn btn-sm" href="{{ route('login') }}">Se connecter</a>
            @endif
        </div>
    </header>

    <main class="form-side">
        <div class="box">
            @yield('form')
        </div>
    </main>
</div>

@stack('scripts')
</body>
</html>
