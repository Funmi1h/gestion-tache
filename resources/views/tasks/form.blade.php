@extends('layouts.app')

@php($isEditing = isset($tache))
@section('title', $isEditing ? 'Modifier la tâche' : 'Nouvelle tâche')
@section('heading', $isEditing ? 'Modifier la tâche' : 'Nouvelle tâche')

@section('content')
    <div class="chart" style="max-width:680px">
        @if (! $isEditing && $equipes->isEmpty())
            <p class="alert">Tu dois d’abord rejoindre une équipe pour créer une tâche.</p>
        @endif
        @if ($errors->any())
            <div class="alert">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ $isEditing ? route('tasks.update', $tache) : route('tasks.store') }}">
            @csrf
            @if ($isEditing)
                @method('PATCH')
            @else
                <div class="field">
                    <label for="equipe_id">Équipe</label>
                    <select id="equipe_id" name="equipe_id" required>
                        @foreach ($equipes as $equipe)
                            <option value="{{ $equipe->id }}" @selected(old('equipe_id') == $equipe->id)>{{ $equipe->nom }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="field">
                <label for="titre">Titre</label>
                <input class="input" id="titre" name="titre" maxlength="255" value="{{ old('titre', $tache->titre ?? '') }}" required>
            </div>
            <div class="field">
                <label for="description">Description</label>
                <textarea class="input" id="description" name="description">{{ old('description', $tache->description ?? '') }}</textarea>
            </div>
            <div class="field">
                <label for="membre_equipe_id">Responsable</label>
                <select id="membre_equipe_id" name="membre_equipe_id" required>
                    @foreach ($membres as $membre)
                        <option value="{{ $membre->id }}" data-equipe="{{ $membre->equipe_id }}" @selected(old('membre_equipe_id', $tache->membre_equipe_id ?? '') == $membre->id)>
                            @unless ($isEditing){{ $membre->equipe->nom }} — @endunless{{ $membre->utilisateur->first_name }} {{ $membre->utilisateur->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="date_echeance">Date d’échéance</label>
                <input class="input" id="date_echeance" name="date_echeance" type="date" value="{{ old('date_echeance', isset($tache->date_echeance) ? $tache->date_echeance->format('Y-m-d') : '') }}">
            </div>
            <div class="actions">
                <button class="btn btn-dark" type="submit" @disabled(! $isEditing && $equipes->isEmpty())>{{ $isEditing ? 'Enregistrer' : 'Créer la tâche' }}</button>
                <a class="btn" href="{{ $isEditing ? route('tasks.show', $tache) : route('tasks') }}">Annuler</a>
            </div>
        </form>
    </div>
@endsection

@if (! $isEditing)
    @push('scripts')
        <script>
            const equipeSelect = document.getElementById('equipe_id');
            const membreSelect = document.getElementById('membre_equipe_id');
            const filterMembres = () => {
                [...membreSelect.options].forEach(option => {
                    option.hidden = option.dataset.equipe !== equipeSelect.value;
                });
                if (membreSelect.selectedOptions[0]?.hidden) membreSelect.selectedIndex = -1;
            };
            equipeSelect.addEventListener('change', filterMembres);
            filterMembres();
        </script>
    @endpush
@endif
