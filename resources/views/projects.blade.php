{{-- resources/views/projects.blade.php : Route::view('/projets', 'projects') --}}
@extends('layouts.app')

@section('title', 'Projets')
@section('heading', 'Projets')
@section('actions')
    <a href="#" class="btn">Nouveau projet</a>
@endsection

@section('content')
    @php
        // Valeurs d'exemple : passe $projects depuis ton contrôleur pour les remplacer.
        $projects = $projects ?? [
            ['name' => 'Implémentation du CRM', 'desc' => 'Mise en place et formation des équipes', 'progress' => 33, 'due' => '30 mars 2026', 'status' => 'cours', 'members' => ['David Miller', 'Michael Johnson']],
            ['name' => 'Refonte du site web', 'desc' => 'Nouvelle identité et nouveau design', 'progress' => 25, 'due' => '15 avril 2026', 'status' => 'cours', 'members' => ['Sophia Williams', 'Emily Carter']],
            ['name' => 'Campagne réseaux sociaux T1', 'desc' => 'Calendrier éditorial et visuels', 'progress' => 100, 'due' => '28 février 2026', 'status' => 'fini', 'members' => ['James Anderson']],
            ['name' => 'Automatisation RH interne', 'desc' => 'Onboarding et suivi des congés', 'progress' => 0, 'due' => '10 mai 2026', 'status' => 'todo', 'members' => ['Michael Johnson', 'Emily Carter']],
        ];
        $labels = ['todo' => 'À faire', 'cours' => 'En cours', 'revue' => 'En revue', 'fini' => 'Terminé', 'retard' => 'En retard'];
    @endphp

    <div class="chart">
        <div class="seg" role="group" aria-label="Filtrer les projets" id="filters">
            <button type="button" class="on" data-f="all">Tous</button>
            <button type="button" data-f="cours">En cours</button>
            <button type="button" data-f="todo">À faire</button>
            <button type="button" data-f="fini">Terminés</button>
        </div>

        <div class="cards" id="list">
            @forelse ($projects as $p)
                <article class="card" data-status="{{ $p['status'] }}">
                    <div class="between">
                        <h2>{{ $p['name'] }}</h2>
                        <span class="badge s-{{ $p['status'] }}">{{ $labels[$p['status']] }}</span>
                    </div>
                    <p class="sub" style="margin:6px 0 16px">{{ $p['desc'] }}</p>
                    <div class="between" style="margin-bottom:8px"><span class="sub">Avancement</span><span class="num">{{ $p['progress'] }} %</span></div>
                    <div class="track" role="progressbar" aria-valuenow="{{ $p['progress'] }}" aria-valuemin="0" aria-valuemax="100"><div class="fill" style="width: {{ $p['progress'] }}%"></div></div>
                    <div class="between" style="margin-top:16px">
                        <span class="sub">Échéance : {{ $p['due'] }}</span>
                        <span style="display:flex">
                            @foreach ($p['members'] as $name)
                                <span class="avatar" title="{{ $name }}" style="margin-left:-6px;border:2px solid #fff">{{ collect(explode(' ', $name))->map(fn ($w) => mb_substr($w, 0, 1))->join('') }}</span>
                            @endforeach
                        </span>
                    </div>
                </article>
            @empty
                <p class="sub">Aucun projet pour le moment.</p>
            @endforelse
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        var filters = document.getElementById('filters');
        filters.addEventListener('click', function (e) {
            var btn = e.target.closest('button');
            if (!btn) return;
            filters.querySelectorAll('button').forEach(function (b) { b.classList.toggle('on', b === btn); });
            document.querySelectorAll('#list .card').forEach(function (c) {
                c.hidden = btn.dataset.f !== 'all' && c.dataset.status !== btn.dataset.f;
            });
        });
    </script>
@endpush
