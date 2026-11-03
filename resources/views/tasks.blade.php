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
        .new-task{padding-bottom:16px;border-bottom:1px solid var(--line)}
        .new-task summary{display:inline-flex;list-style:none}
        .new-task summary::-webkit-details-marker{display:none}
        .task-form{display:grid;grid-template-columns:1.2fr 1.5fr 1fr 1fr auto;gap:8px;align-items:end;margin-top:14px}
        .task-form label{display:grid;gap:4px;color:var(--muted);font-size:11px}
        .task-form input,.task-form select{min-width:0;width:100%;padding:8px;border:1px solid var(--line);border-radius:7px;background:#fff;color:var(--text);font:inherit;font-size:12px}
        .task-check{display:contents}
        .task-filters{display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin:14px 0}
        .task-filters select{padding:7px 9px;border:1px solid var(--line);border-radius:7px;background:#fff;color:var(--text);font:inherit;font-size:12px}
        @media(max-width:700px){.task-form{grid-template-columns:1fr 1fr}.task-form button{grid-column:1/-1}}
        @media(max-width:700px){.task{grid-template-columns:22px 1fr auto}.who,.when{display:none}}
    </style>
@endpush

@section('content')
    @if ($errors->any())
        <p class="alert">{{ $errors->first() }}</p>
    @endif
    @if (session('status'))
        <p class="alert alert-ok">{{ session('status') }}</p>
    @endif

    @php
        $statusLabels = ['todo' => 'À faire', 'cours' => 'En cours', 'fini' => 'Terminé', 'retard' => 'En retard'];
        $rows = $apercu
            ? collect([
                ['tache' => null, 'title' => 'Prototype de l’application', 'team' => 'Refonte du site web', 'who' => 'Sophia Williams', 'due' => '2 mars', 'status' => 'todo'],
                ['tache' => null, 'title' => 'Importer les contacts clients', 'team' => 'Implémentation du CRM', 'who' => 'David Miller', 'due' => '4 mars', 'status' => 'cours'],
                ['tache' => null, 'title' => 'Valider les maquettes', 'team' => 'Refonte du site web', 'who' => 'Michael Johnson', 'due' => '25 févr.', 'status' => 'retard'],
                ['tache' => null, 'title' => 'Publier le calendrier éditorial', 'team' => 'Campagne réseaux sociaux', 'who' => 'James Anderson', 'due' => '20 févr.', 'status' => 'fini'],
            ])
            : $taches->map(function ($tache) {
                $status = $tache->statut === 'TERMINEE'
                    ? 'fini'
                    : ($tache->date_echeance?->lt(today())
                        ? 'retard'
                        : ($tache->statut === 'EN_COURS' ? 'cours' : 'todo'));

                return [
                    'tache' => $tache,
                    'title' => $tache->titre,
                    'team' => $tache->equipe->nom,
                    'who' => $tache->responsable->utilisateur->first_name.' '.$tache->responsable->utilisateur->name,
                    'due' => $tache->date_echeance?->format('j M') ?? '—',
                    'status' => $status,
                ];
            });
    @endphp

    <div class="chart">
        @if ($apercu)
            <p class="sub">Aperçu de la page : les tâches affichées sont fictives.</p>
        @elseif ($equipes->isNotEmpty())
            <form method="GET" action="{{ route('tasks') }}" class="task-filters">
                <select name="equipe_id" aria-label="Équipe" onchange="this.form.elements.membre_equipe_id.value=''; this.form.submit()">
                    @foreach ($equipes as $equipe)
                        <option value="{{ $equipe->id }}" @selected((int) $equipeId === $equipe->id)>{{ $equipe->nom }}</option>
                    @endforeach
                </select>
                <select name="membre_equipe_id" aria-label="Responsable">
                    <option value="">Tous les responsables</option>
                    @foreach ($membres as $membre)
                        <option value="{{ $membre->id }}" @selected((string) request('membre_equipe_id') === (string) $membre->id)>
                            {{ $membre->utilisateur->first_name }} {{ $membre->utilisateur->name }}
                        </option>
                    @endforeach
                </select>
                <button class="btn btn-sm" type="submit">Filtrer</button>
            </form>

            <details class="new-task">
                <summary class="btn btn-dark btn-sm">+ Nouvelle tâche</summary>
                <form class="task-form" method="POST" action="{{ route('tasks.store') }}">
                    @csrf
                    <input type="hidden" name="equipe_id" value="{{ $equipeId }}">
                    <label>Titre<input name="titre" required maxlength="255"></label>
                    <label>Description<input name="description" placeholder="Description courte"></label>
                    <label>Responsable
                        <select name="membre_equipe_id" required>
                            @foreach ($membres as $membre)
                                <option value="{{ $membre->id }}">{{ $membre->utilisateur->first_name }} {{ $membre->utilisateur->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>Échéance<input name="date_echeance" type="date"></label>
                    <button class="btn btn-sm" type="submit">Ajouter</button>
                </form>
            </details>
        @else
            <p class="sub">Tu dois rejoindre une équipe pour voir ses tâches.</p>
        @endif

        <div class="seg" role="group" aria-label="Filtrer les tâches" id="filters">
            <button type="button" class="on" data-f="all">Toutes</button>
            <button type="button" data-f="todo">À faire</button>
            <button type="button" data-f="cours">En cours</button>
            <button type="button" data-f="retard">En retard</button>
            <button type="button" data-f="fini">Terminées</button>
        </div>

        <div id="list">
            @forelse ($rows as $row)
                <div class="task {{ $row['status'] === 'fini' ? 'done' : '' }}" data-status="{{ $row['status'] }}">
                    @if ($row['tache'] && (int) $row['tache']->responsable->user_id === (int) auth()->id())
                        <form class="task-check" method="POST" action="{{ route('tasks.status', $row['tache']) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="statut" value="{{ $row['tache']->statut }}">
                            <input type="checkbox" aria-label="Terminer : {{ $row['title'] }}" @checked($row['status'] === 'fini') onchange="this.form.statut.value=this.checked?'TERMINEE':'A_FAIRE';this.form.submit()">
                        </form>
                    @else
                        <input type="checkbox" aria-label="Terminer : {{ $row['title'] }}" @checked($row['status'] === 'fini') disabled>
                    @endif
                    <div>
                        @if ($row['tache'])
                            <a class="t" style="font-weight:500;color:inherit;text-decoration:none" href="{{ route('tasks.show', $row['tache']) }}">{{ $row['title'] }}</a>
                        @else
                            <div class="t" style="font-weight:500">{{ $row['title'] }}</div>
                        @endif
                        <div class="sub">{{ $row['team'] }}</div>
                    </div>
                    <div class="who">
                        <span class="avatar">{{ collect(explode(' ', $row['who']))->map(fn ($part) => mb_substr($part, 0, 1))->join('') }}</span>
                        <span>{{ $row['who'] }}</span>
                    </div>
                    <span class="when num">{{ $row['due'] }}</span>
                    <span class="badge s-{{ $row['status'] }}">{{ $statusLabels[$row['status']] }}</span>
                </div>
            @empty
                <p class="sub">Aucune tâche pour le moment.</p>
            @endforelse
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const filters = document.getElementById('filters');
        filters.addEventListener('click', function (event) {
            const button = event.target.closest('button');
            if (!button) return;
            filters.querySelectorAll('button').forEach(item => item.classList.toggle('on', item === button));
            document.querySelectorAll('#list .task').forEach(row => {
                row.hidden = button.dataset.f !== 'all' && row.dataset.status !== button.dataset.f;
            });
        });
    </script>
@endpush
