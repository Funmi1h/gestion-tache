{{-- resources/views/tasks.blade.php : Route::view('/taches', 'tasks') --}}
@extends('layouts.app')

@section('title', 'Tâches')
@section('heading', 'Tâches')

@push('styles')
    <style>
        .task{display:grid;grid-template-columns:22px 1fr 150px 110px 96px;gap:14px;align-items:center;padding:12px 0;border-bottom:1px solid var(--line)}
        .task:last-child{border-bottom:0}
        .task input{width:16px;height:16px;accent-color:var(--blue);cursor:pointer}
        .task.done .t{text-decoration:line-through;color:var(--muted)}
        .who{display:flex;align-items:center;gap:8px}
        @media(max-width:700px){.task{grid-template-columns:22px 1fr auto}.who,.when{display:none}}
    </style>
@endpush

@section('content')
    @php
        // Valeurs d'exemple : passe $tasks depuis ton contrôleur pour les remplacer.
        $tasks = $tasks ?? [
            ['title' => 'Prototype de l’application', 'project' => 'Refonte du site web', 'who' => 'Sophia Williams', 'due' => '2 mars', 'status' => 'revue'],
            ['title' => 'Importer les contacts clients', 'project' => 'Implémentation du CRM', 'who' => 'David Miller', 'due' => '4 mars', 'status' => 'cours'],
            ['title' => 'Rédiger le guide d’onboarding', 'project' => 'Automatisation RH interne', 'who' => 'Emily Carter', 'due' => '6 mars', 'status' => 'todo'],
            ['title' => 'Valider les maquettes', 'project' => 'Refonte du site web', 'who' => 'Michael Johnson', 'due' => '25 févr.', 'status' => 'retard'],
            ['title' => 'Publier le calendrier éditorial', 'project' => 'Campagne réseaux sociaux T1', 'who' => 'James Anderson', 'due' => '20 févr.', 'status' => 'fini'],
        ];
        $labels = ['todo' => 'À faire', 'cours' => 'En cours', 'revue' => 'En revue', 'fini' => 'Terminé', 'retard' => 'En retard'];
    @endphp

    <div class="chart">
        <div class="seg" role="group" aria-label="Filtrer les tâches" id="filters">
            <button type="button" class="on" data-f="all">Toutes</button>
            <button type="button" data-f="todo">À faire</button>
            <button type="button" data-f="cours">En cours</button>
            <button type="button" data-f="revue">En revue</button>
            <button type="button" data-f="retard">En retard</button>
            <button type="button" data-f="fini">Terminées</button>
        </div>

        <div id="list">
            @forelse ($tasks as $t)
                <div class="task {{ $t['status'] === 'fini' ? 'done' : '' }}" data-status="{{ $t['status'] }}">
                    <input type="checkbox" aria-label="Terminer : {{ $t['title'] }}" {{ $t['status'] === 'fini' ? 'checked' : '' }}>
                    <div>
                        <div class="t" style="font-weight:500">{{ $t['title'] }}</div>
                        <div class="sub">{{ $t['project'] }}</div>
                    </div>
                    <div class="who">
                        <span class="avatar">{{ collect(explode(' ', $t['who']))->map(fn ($w) => mb_substr($w, 0, 1))->join('') }}</span>
                        <span>{{ $t['who'] }}</span>
                    </div>
                    <span class="when num">{{ $t['due'] }}</span>
                    <span class="badge s-{{ $t['status'] }}">{{ $labels[$t['status']] }}</span>
                </div>
            @empty
                <p class="sub">Aucune tâche pour le moment.</p>
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
            document.querySelectorAll('#list .task').forEach(function (r) {
                r.hidden = btn.dataset.f !== 'all' && r.dataset.status !== btn.dataset.f;
            });
        });
        // Cocher une tâche la barre visuellement (côté navigateur seulement)
        document.querySelectorAll('.task input').forEach(function (cb) {
            cb.addEventListener('change', function () { cb.closest('.task').classList.toggle('done', cb.checked); });
        });
    </script>
@endpush
