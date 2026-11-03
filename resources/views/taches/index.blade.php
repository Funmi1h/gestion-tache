@extends('layouts.app')

@section('title', 'Tâches')
@section('heading', 'Tâches')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/taches.css') }}?v={{ time() }}">
@endpush

@section('content')
    @if ($equipes->isEmpty())
        <div style="text-align: center; max-width: 520px; margin: 40px auto; background: #fff; border-radius: 14px; padding: 40px 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div style="font-size: 38px; margin-bottom: 14px;">📋</div>
            <h2 style="font-size: 19px; font-weight: 600; color: var(--text-main); margin-bottom: 8px;">
                Aucune équipe trouvée
            </h2>
            <p style="font-size: 14px; color: var(--text-muted); line-height: 1.5; margin-bottom: 24px;">
                Pour afficher et gérer un tableau de tâches, vous devez d'abord faire partie d'une équipe.
            </p>
            <a href="{{ route('equipes.create') }}" class="btn btn-primary" style="text-decoration: none;">
                + Créer une équipe
            </a>
        </div>
    @else
        @php
            $statusLabels = ['todo' => 'À faire', 'cours' => 'En cours', 'fini' => 'Terminée'];
            $rows = $taches->map(function ($tache) {
                // verification si la tache a depasse sa date
                $isLate = $tache->date_echeance?->lt(today()) && $tache->statut !== 'TERMINEE';
                $status = match($tache->statut) {
                    'TERMINEE' => 'fini',
                    'EN_COURS' => 'cours',
                    default    => 'todo',
                };

                return [
                    'tache' => $tache,
                    'title' => $tache->titre,
                    'team' => $tache->equipe->nom,
                    'who' => $tache->responsable->utilisateur->first_name.' '.$tache->responsable->utilisateur->name,
                    'due' => $tache->date_echeance?->format('j M') ?? '—',
                    'status' => $status,
                    'is_late' => $isLate,
                ];
            });

            // si filtre en retard actif
            if (request()->boolean('en_retard')) {
                $rows = $rows->where('is_late', true);
            }
        @endphp

        <div class="chart">
            {{-- barre d'outils : filtres a gauche et bouton nouvelle tache a droite --}}
            <div class="task-toolbar">
                <form method="GET" action="{{ route('tasks') }}" class="task-filters">
                    <select name="equipe_id" aria-label="Équipe" onchange="this.form.elements.membre_equipe_id.value=''; this.form.submit()">
                        @foreach ($equipes as $equipe)
                            <option value="{{ $equipe->id }}" @selected((int) $equipeId === $equipe->id)>Équipe : {{ $equipe->nom }}</option>
                        @endforeach
                    </select>

                    <select name="membre_equipe_id" aria-label="Responsable" onchange="this.form.submit()">
                        <option value="">Tous les responsables</option>
                        @foreach ($membres as $membre)
                            <option value="{{ $membre->id }}" @selected((string) request('membre_equipe_id') === (string) $membre->id)>
                                {{ $membre->utilisateur->first_name }} {{ $membre->utilisateur->name }}
                            </option>
                        @endforeach
                    </select>

                    {{-- bouton filtre rapide pour voir les retards --}}
                    @php
                        $isLateFilter = request()->boolean('en_retard');
                        $lateUrl = route('tasks', array_merge(request()->query(), ['en_retard' => $isLateFilter ? null : 1]));
                    @endphp
                    <a href="{{ $lateUrl }}" class="btn-filter-late {{ $isLateFilter ? 'active' : '' }}">
                        En retard uniquement
                    </a>
                </form>

                <button type="button" class="btn btn-new-task" onclick="openNewTaskModal()">
                    <span style="font-size:16px; line-height:1;">+</span> Nouvelle tâche
                </button>
            </div>

        @php
            // les 3 colonnes de notre kanban sans emojis
            $columns = [
                'todo'  => 'À faire',
                'cours' => 'En cours',
                'fini'  => 'Terminées',
            ];
        @endphp

        <div class="task-board-scroll" aria-label="Tableau des tâches">
            <div class="task-board" id="task-board">
                @foreach ($columns as $columnKey => $columnTitle)
                    @php
                        $columnRows = $rows->where('status', $columnKey);
                    @endphp
                    <section class="task-column task-column--{{ $columnKey }} is-selected" data-task-column>
                        <div class="task-column__heading">
                            <span class="task-column__title">{{ $columnTitle }}</span>
                            <span class="task-column__count">{{ $columnRows->count() }}</span>
                        </div>

                        <div class="task-column__list">
                            @forelse ($columnRows as $row)
                                @php
                                    $taskKey = $row['tache']?->id ?? \Illuminate\Support\Str::slug($row['title']);
                                @endphp
                                <article class="task-card task-card--{{ $row['status'] }} {{ $row['status'] === 'fini' ? 'is-done' : '' }}" data-task-card data-task-key="{{ $taskKey }}">
                                    <div class="task-card__topline">
                                        <div style="display:flex; align-items:center; gap:6px;">
                                            <span class="task-card__status">{{ $statusLabels[$row['status']] }}</span>
                                            @if($row['is_late'])
                                                <span style="background:#fee2e2; color:#b91c1c; font-size:10px; font-weight:700; padding:2px 6px; border-radius:4px;">En retard</span>
                                            @endif
                                        </div>
                                    </div>

                                    @if ($row['tache'])
                                        <a class="task-card__title" href="javascript:void(0)" onclick="openTaskModal({{ $row['tache']->id }})">{{ $row['title'] }}</a>
                                    @else
                                        <h3 class="task-card__title">{{ $row['title'] }}</h3>
                                    @endif

                                    <p class="task-card__team">{{ $row['team'] }}</p>

                                    <div class="task-card__footer">
                                        <span class="task-card__person">
                                            <span class="avatar">{{ collect(explode(' ', $row['who']))->map(fn ($part) => mb_substr($part, 0, 1))->join('') }}</span>
                                            <span>{{ $row['who'] }}</span>
                                        </span>
                                        <span class="task-card__date" style="{{ $row['is_late'] ? 'color:#b91c1c; font-weight:600;' : '' }}">{{ $row['due'] }}</span>
                                    </div>

                                    @if ($row['tache'])
                                        {{-- si l'utilisateur connecte est le responsable il peut changer le statut directement --}}
                                        @if ((int) $row['tache']->responsable->user_id === (int) auth()->id())
                                            <form method="POST" action="{{ route('tasks.status', $row['tache']) }}" style="margin: 8px 0 0;">
                                                @csrf
                                                @method('PATCH')
                                                <div style="display:flex; align-items:center; justify-content:space-between; gap:6px;">
                                                    <span style="font-size:11px; color:var(--text-muted);">Statut :</span>
                                                    <select name="statut" onchange="this.form.submit()" style="font-size:11px; padding:3px 8px; border-radius:6px; border:1px solid var(--line); background:#fff; cursor:pointer;">
                                                        <option value="A_FAIRE" @selected($row['tache']->statut === 'A_FAIRE')>À faire</option>
                                                        <option value="EN_COURS" @selected($row['tache']->statut === 'EN_COURS')>En cours</option>
                                                        <option value="TERMINEE" @selected($row['tache']->statut === 'TERMINEE')>Terminée</option>
                                                    </select>
                                                </div>
                                            </form>
                                        @else
                                            <div style="margin-top:6px; font-size:11px; color:var(--text-muted);">
                                                Responsable : {{ $row['who'] }}
                                            </div>
                                        @endif
                                    @endif
                                </article>
                            @empty
                                <p class="task-column__empty">Aucune tâche</p>
                            @endforelse
                        </div>
                    </section>
                @endforeach
            </div>
        </div>
    </div>

    {{-- modale flottante de creation de tache au centre --}}
    <div id="new-task-modal" class="task-modal" onclick="if(event.target === this) closeNewTaskModal()">
        <div class="task-modal__card new-task-modal__card">
            <div class="new-task-modal__header">
                <h2>Nouvelle tâche</h2>
                <button type="button" onclick="closeNewTaskModal()" class="btn-close-modal" aria-label="Fermer">&times;</button>
            </div>

            <form method="POST" action="{{ route('tasks.store') }}" novalidate class="new-task-modal__form">
                @csrf
                <input type="hidden" name="equipe_id" value="{{ $equipeId }}">

                <div class="field">
                    <label for="new-task-titre">Titre de la tâche *</label>
                    <input id="new-task-titre" name="titre" required maxlength="255" placeholder="Ex: Rédiger le rapport mensuel" class="input">
                </div>

                <div class="field">
                    <label for="new-task-desc">Description</label>
                    <textarea id="new-task-desc" name="description" rows="3" placeholder="Détails ou consignes pour la tâche..." class="input"></textarea>
                </div>

                <div class="form-grid-2">
                    <div class="field">
                        <label for="new-task-resp">Responsable *</label>
                        <select id="new-task-resp" name="membre_equipe_id" required class="input">
                            @foreach ($membres as $membre)
                                <option value="{{ $membre->id }}">{{ $membre->utilisateur->first_name }} {{ $membre->utilisateur->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field">
                        <label for="new-task-date">Date d'échéance</label>
                        <input id="new-task-date" name="date_echeance" type="date" class="input">
                    </div>
                </div>

                <div class="new-task-modal__footer">
                    <button type="button" class="btn btn-outline btn-sm" onclick="closeNewTaskModal()" style="padding: 8px 18px;">Annuler</button>
                    <button type="submit" class="btn btn-primary btn-sm" style="padding: 8px 20px;">Ajouter la tâche</button>
                </div>
            </form>
        </div>
    </div>

    {{-- modales flottantes pour chaque tache au clic --}}
    @foreach ($taches as $t)
        @php
            $isResp = (int) $t->responsable->user_id === (int) auth()->id();
        @endphp
        <div id="task-modal-{{ $t->id }}" class="task-modal" onclick="if(event.target === this) closeTaskModal({{ $t->id }})">
            <div class="task-modal__card">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:16px;">
                    <h2 style="font-size:18px; font-weight:600; margin:0; color:var(--text-main);">{{ $t->titre }}</h2>
                    <button type="button" onclick="closeTaskModal({{ $t->id }})" style="background:none; border:none; font-size:22px; cursor:pointer; color:var(--text-muted); line-height:1;">&times;</button>
                </div>

                <div style="font-size:13px; color:var(--text-muted); display:flex; flex-direction:column; gap:10px; margin-bottom:20px;">
                    <div><strong>Équipe :</strong> {{ $t->equipe->nom }}</div>
                    <div><strong>Responsable :</strong> {{ $t->responsable->utilisateur->first_name }} {{ $t->responsable->utilisateur->name }}</div>
                    <div>
                        <strong>Statut :</strong> 
                        <span style="font-weight:600; color:var(--text-main);">
                            {{ ['A_FAIRE' => 'À faire', 'EN_COURS' => 'En cours', 'TERMINEE' => 'Terminée'][$t->statut] }}
                        </span>
                    </div>
                    <div><strong>Échéance :</strong> {{ $t->date_echeance?->format('d/m/Y') ?? 'Non définie' }}</div>
                    <div>
                        <strong>Description :</strong>
                        <p style="margin:6px 0 0; color:var(--text-main); line-height:1.5;">
                            {{ $t->description ?: 'Aucune description fournie.' }}
                        </p>
                    </div>
                </div>

                {{-- changement de statut rapide dans la modale si responsable --}}
                @if($isResp)
                    <div style="padding:12px; background:#f8fafc; border-radius:8px; border:1px solid var(--line); margin-bottom:16px;">
                        <form method="POST" action="{{ route('tasks.status', $t) }}" style="display:flex; align-items:center; justify-content:space-between; gap:10px;">
                            @csrf
                            @method('PATCH')
                            <span style="font-size:12px; font-weight:600;">Changer le statut :</span>
                            <select name="statut" onchange="this.form.submit()" style="padding:6px 10px; font-size:12px; border-radius:6px; border:1px solid var(--line);">
                                <option value="A_FAIRE" @selected($t->statut === 'A_FAIRE')>À faire</option>
                                <option value="EN_COURS" @selected($t->statut === 'EN_COURS')>En cours</option>
                                <option value="TERMINEE" @selected($t->statut === 'TERMINEE')>Terminée</option>
                            </select>
                        </form>
                    </div>
                @endif

                <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid var(--line); padding-top:16px;">
                    <div style="display:flex; gap:8px;">
                        <a href="{{ route('tasks.edit', $t) }}" class="btn btn-sm" style="text-decoration:none;">Modifier</a>
                        <form method="POST" action="{{ route('tasks.destroy', $t) }}" onsubmit="return confirm('Supprimer cette tâche ?')" style="margin:0;">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm" type="submit" style="color:#ef4444; border-color:#fca5a5;">Supprimer</button>
                        </form>
                    </div>
                    <button type="button" class="btn btn-sm" onclick="closeTaskModal({{ $t->id }})">Fermer</button>
                </div>
            </div>
        </div>
    @endforeach
    @endif
@endsection

@push('scripts')
    <script>
        function openNewTaskModal() {
            const modal = document.getElementById('new-task-modal');
            if (modal) {
                modal.classList.add('is-open');
                const input = document.getElementById('new-task-titre');
                if (input) setTimeout(() => input.focus(), 60);
            }
        }

        function closeNewTaskModal() {
            const modal = document.getElementById('new-task-modal');
            if (modal) modal.classList.remove('is-open');
        }

        function openTaskModal(id) {
            const modal = document.getElementById('task-modal-' + id);
            if (modal) modal.classList.add('is-open');
        }

        function closeTaskModal(id) {
            const modal = document.getElementById('task-modal-' + id);
            if (modal) modal.classList.remove('is-open');
        }

        // fermeture par touche echap
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.task-modal.is-open').forEach(m => m.classList.remove('is-open'));
            }
        });
    </script>
@endpush
