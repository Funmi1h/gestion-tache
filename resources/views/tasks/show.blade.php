@extends('layouts.app')

@section('title', $tache->titre.' — Taskflow')
@section('heading', $tache->titre)

@section('actions')
    <a class="btn btn-sm" href="{{ route('tasks', ['equipe_id' => $tache->equipe_id]) }}">Retour aux tâches</a>
@endsection

@section('content')
    <div class="chart" style="max-width:680px">
        @if (session('status'))
            <p class="alert alert-ok">{{ session('status') }}</p>
        @endif
        <p><strong>Équipe :</strong> {{ $tache->equipe->nom }}</p>
        <p><strong>Description :</strong> {{ $tache->description ?: 'Aucune description.' }}</p>
        <p><strong>Responsable :</strong> {{ $tache->responsable->utilisateur->first_name }} {{ $tache->responsable->utilisateur->name }}</p>
        <p><strong>Statut :</strong> {{ ['A_FAIRE' => 'À faire', 'EN_COURS' => 'En cours', 'TERMINEE' => 'Terminée'][$tache->statut] }}</p>
        <p><strong>Échéance :</strong> {{ $tache->date_echeance?->format('d/m/Y') ?? 'Non définie' }}</p>

        @if ($isResponsable)
            <form method="POST" action="{{ route('tasks.status', $tache) }}" class="field">
                @csrf
                @method('PATCH')
                <label for="statut">Modifier le statut</label>
                <select id="statut" name="statut">
                    @foreach (['A_FAIRE' => 'À faire', 'EN_COURS' => 'En cours', 'TERMINEE' => 'Terminée'] as $value => $label)
                        <option value="{{ $value }}" @selected($tache->statut === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button class="btn btn-sm" type="submit">Enregistrer</button>
            </form>
        @endif

        @if ($canManage)
            <div class="actions">
                <a class="btn btn-dark btn-sm" href="{{ route('tasks.edit', $tache) }}">Modifier</a>
                <form method="POST" action="{{ route('tasks.destroy', $tache) }}" onsubmit="return confirm('Supprimer cette tâche ?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm" type="submit">Supprimer</button>
                </form>
            </div>
        @endif
    </div>
@endsection
