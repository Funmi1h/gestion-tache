@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}?v={{ time() }}">
@endpush

@section('title', 'Tableau de bord · Taskflow')

@section('heading')
    @if($equipeCourante)
        Tableau de bord — {{ $equipeCourante->nom }}
    @else
        Tableau de bord
    @endif
@endsection

@section('actions')
    {{-- selecteur d'equipe toujours accessible --}}
    @if(isset($equipes) && $equipes->isNotEmpty())
        <form method="GET" action="{{ route('dashboard') }}" style="margin: 0;">
            <select name="equipe_id" onchange="this.form.submit()" class="auth-input" style="padding: 7px 14px; font-size: 13px; border-radius:8px; border:1px solid var(--line); background:#fff; cursor:pointer;">
                @foreach($equipes as $eq)
                    <option value="{{ $eq->id }}" {{ $equipeCourante && $eq->id === $equipeCourante->id ? 'selected' : '' }}>
                        Équipe : {{ $eq->nom }}
                    </option>
                @endforeach
            </select>
        </form>
    @endif
@endsection

@section('content')

@if(!isset($equipes) || $equipes->isEmpty())
    {{-- l'utilisateur n'a aucune equipe --}}
    <div style="text-align: center; max-width: 540px; margin: 40px auto; background: #fff; border-radius: 14px; padding: 40px 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="font-size: 40px; margin-bottom: 16px;">👋</div>
        <h2 style="font-size: 20px; font-weight: 600; color: var(--text-main); margin-bottom: 8px;">
            Bienvenue {{ auth()->user()->first_name ?? auth()->user()->name }} !
        </h2>
        <p style="font-size: 14px; color: var(--text-muted); line-height: 1.5; margin-bottom: 24px;">
            Vous ne faites partie d'aucune équipe pour le moment. Pour commencer à suivre des tâches et collaborer, créez votre propre équipe ou rejoignez-en une via une invitation reçue par e-mail.
        </p>
        
        <div style="display: flex; flex-direction: column; gap: 12px; align-items: center;">
            <a href="{{ route('equipes.create') }}" class="btn btn-primary" style="text-decoration: none;">
                + Créer ma première équipe
            </a>
            <span style="font-size: 12px; color: var(--text-muted);">
                (Vous pourrez y inviter jusqu'à 5 collaborateurs)
            </span>
        </div>
    </div>

@else
    {{-- progression globale de l'equipe sans bordure lourde --}}
    <div class="card" style="background:#fff; border:none; border-radius:12px; padding:18px 20px; margin-bottom:20px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
            <span style="font-weight:600; font-size:14px; color:var(--text-main);">Progression globale de l'équipe</span>
            <span style="font-weight:700; font-size:15px; color:var(--primary, #C83F74);">{{ $tauxAvancement ?? 0 }}% terminées</span>
        </div>
        <div class="progress-track" style="height:8px; background:#f1f5f9; border-radius:6px; overflow:hidden;">
            <div class="progress-fill" style="width: {{ $tauxAvancement ?? 0 }}%; height:100%; background:var(--primary, #C83F74);"></div>
        </div>
    </div>

    {{-- les 5 cartes kpi cliquables avec le meme style que le kanban --}}
    <div class="kpi-grid">
        <a href="{{ route('tasks', ['equipe_id' => $equipeCourante->id]) }}" class="kpi-card kpi-total">
            <div class="kpi-header">
                <span class="kpi-title">Total</span>
                <span class="kpi-count">{{ $stats['total'] ?? 0 }}</span>
            </div>
            <div class="kpi-subtext">{{ ($stats['total'] ?? 0) === 0 ? 'Aucune tâche' : ($stats['total'] . ' tâche(s)') }}</div>
        </a>

        <a href="{{ route('tasks', ['equipe_id' => $equipeCourante->id, 'statut' => 'A_FAIRE']) }}" class="kpi-card kpi-todo">
            <div class="kpi-header">
                <span class="kpi-title">À faire</span>
                <span class="kpi-count">{{ $stats['a_faire'] ?? 0 }}</span>
            </div>
            <div class="kpi-subtext">{{ ($stats['a_faire'] ?? 0) === 0 ? 'Aucune tâche' : ($stats['a_faire'] . ' tâche(s)') }}</div>
        </a>

        <a href="{{ route('tasks', ['equipe_id' => $equipeCourante->id, 'statut' => 'EN_COURS']) }}" class="kpi-card kpi-cours">
            <div class="kpi-header">
                <span class="kpi-title">En cours</span>
                <span class="kpi-count">{{ $stats['en_cours'] ?? 0 }}</span>
            </div>
            <div class="kpi-subtext">{{ ($stats['en_cours'] ?? 0) === 0 ? 'Aucune tâche' : ($stats['en_cours'] . ' tâche(s)') }}</div>
        </a>

        <a href="{{ route('tasks', ['equipe_id' => $equipeCourante->id, 'statut' => 'TERMINEE']) }}" class="kpi-card kpi-fini">
            <div class="kpi-header">
                <span class="kpi-title">Terminées</span>
                <span class="kpi-count">{{ $stats['terminees'] ?? 0 }}</span>
            </div>
            <div class="kpi-subtext">{{ ($stats['terminees'] ?? 0) === 0 ? 'Aucune tâche' : ($stats['terminees'] . ' tâche(s)') }}</div>
        </a>

        <a href="{{ route('tasks', ['equipe_id' => $equipeCourante->id, 'en_retard' => 1]) }}" class="kpi-card kpi-late">
            <div class="kpi-header">
                <span class="kpi-title">En retard</span>
                <span class="kpi-count">{{ $stats['en_retard'] ?? 0 }}</span>
            </div>
            <div class="kpi-subtext">{{ ($stats['en_retard'] ?? 0) === 0 ? 'Aucun retard' : ($stats['en_retard'] . ' en retard') }}</div>
        </a>
    </div>

    {{-- section mes taches sans bordure lourde --}}
    <div class="card" style="background:#fff; border:none; border-radius:12px; padding:20px; margin-bottom:20px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
            <h2 style="font-size:16px; font-weight:600; margin:0;">
                Mes tâches ({{ $mesTaches->count() }})
            </h2>
            @if($mesTaches->isNotEmpty())
                <a href="{{ route('tasks', ['equipe_id' => $equipeCourante->id]) }}" style="font-size:13px; color:var(--primary, #C83F74); text-decoration:none; font-weight:500;">
                    Voir dans le Kanban &rarr;
                </a>
            @endif
        </div>

        @if($mesTaches->isNotEmpty())
            <div style="display:flex; flex-direction:column; gap:10px;">
                @foreach($mesTaches as $tache)
                    @php
                        $isLate = $tache->date_echeance && \Carbon\Carbon::parse($tache->date_echeance)->isPast() && $tache->statut !== 'TERMINEE';
                    @endphp
                    <div style="display:flex; justify-content:space-between; align-items:center; padding:12px 14px; background:#f8fafc; border-radius:8px;">
                        <div>
                            <a href="{{ route('tasks.show', $tache->id) }}" style="font-weight:600; font-size:14px; color:var(--text-main); text-decoration:none;">
                                {{ $tache->titre }}
                            </a>
                            <div style="font-size:12px; color:var(--text-muted); margin-top:2px;">
                                Échéance : 
                                <span style="{{ $isLate ? 'color:#ef4444; font-weight:600;' : '' }}">
                                    {{ $tache->date_echeance ? \Carbon\Carbon::parse($tache->date_echeance)->format('d/m/Y') : 'Non définie' }}
                                    @if($isLate) (en retard) @endif
                                </span>
                            </div>
                        </div>
                        <div>
                            @if($tache->statut === 'TERMINEE')
                                <span style="background:#dcfce7; color:#166534; font-size:11px; font-weight:600; padding:4px 8px; border-radius:12px;">Terminée</span>
                            @elseif($tache->statut === 'EN_COURS')
                                <span style="background:#fef3c7; color:#92400e; font-size:11px; font-weight:600; padding:4px 8px; border-radius:12px;">En cours</span>
                            @else
                                <span style="background:#f1f5f9; color:#475569; font-size:11px; font-weight:600; padding:4px 8px; border-radius:12px;">À faire</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p style="color:var(--text-muted); font-size:14px; margin:0;">
                Vous n'avez aucune tâche assignée dans cette équipe pour le moment.
            </p>
        @endif
    </div>

    {{-- repartition des taches par membre sans bordure lourde --}}
    <div class="card" style="background:#fff; border:none; border-radius:12px; padding:20px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <h2 style="font-size:16px; font-weight:600; margin-bottom:16px;">
            Répartition des tâches par membre — {{ $equipeCourante->nom }}
        </h2>

        @if(!empty($membresStats) && count($membresStats) > 0 && ($stats['total'] ?? 0) > 0)
            <div style="display:flex; flex-direction:column; gap:16px;">
                @foreach($membresStats as $ms)
                    @php
                        $pourcent = round(($ms['tasks_count'] / $stats['total']) * 100);
                    @endphp
                    <div>
                        <div style="display:flex; justify-content:space-between; font-size:13px; font-weight:500;">
                            <span>{{ $ms['name'] }}</span>
                            <span style="color:var(--text-muted);">{{ $ms['tasks_count'] }} tâche(s) ({{ $pourcent }}%)</span>
                        </div>
                        <div class="progress-track">
                            <div class="progress-fill" style="width: {{ $pourcent }}%;"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p style="color:var(--text-muted); font-size:14px; margin:0;">
                Aucune tâche n'est encore enregistrée pour cette équipe.
            </p>
        @endif
    </div>
        <h2 style="font-size:16px; font-weight:600; margin-bottom:16px;">
            Répartition des tâches par membre — {{ $equipeCourante->nom }}
        </h2>

        @if(!empty($membresStats) && count($membresStats) > 0 && ($stats['total'] ?? 0) > 0)
            <div style="display:flex; flex-direction:column; gap:16px;">
                @foreach($membresStats as $ms)
                    @php
                        $pourcent = round(($ms['tasks_count'] / $stats['total']) * 100);
                    @endphp
                    <div>
                        <div style="display:flex; justify-content:space-between; font-size:13px; font-weight:500;">
                            <span>{{ $ms['name'] }}</span>
                            <span style="color:var(--text-muted);">{{ $ms['tasks_count'] }} tâche(s) ({{ $pourcent }}%)</span>
                        </div>
                        <div class="progress-track">
                            <div class="progress-fill" style="width: {{ $pourcent }}%;"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p style="color:var(--text-muted); font-size:14px; margin:0;">
                Aucune tâche n'est encore enregistrée pour cette équipe.
            </p>
        @endif
    </div>

@endif

@endsection
