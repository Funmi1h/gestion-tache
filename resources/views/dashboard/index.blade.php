@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endpush

@section('title', 'Tableau de bord · Taskflow')
@section('heading', 'Tableau de bord — ' . ($equipeCourante->nom ?? 'Vue globale'))

@section('content')
@php
    $total     = $stats['total'] ?? 0;
    $aFaire    = $stats['a_faire'] ?? 0;
    $enCours   = $stats['en_cours'] ?? 0;
    $terminees = $stats['terminees'] ?? 0;
    $enRetard  = $stats['en_retard'] ?? 0;
    $membresStats = $membresStats ?? [];
@endphp

{{-- 1. Les 5 Cartes de KPI (CDC §7) --}}
<div class="kpi-grid">
    <div class="kpi-card">
        <div class="kpi-label">Total Tâches</div>
        <div class="kpi-value">{{ $total }}</div>
    </div>

    <div class="kpi-card">
        <div class="kpi-label">À faire</div>
        <div class="kpi-value">{{ $aFaire }}</div>
    </div>

    <div class="kpi-card doing">
        <div class="kpi-label">En cours</div>
        <div class="kpi-value">{{ $enCours }}</div>
    </div>

    <div class="kpi-card done">
        <div class="kpi-label">Terminées</div>
        <div class="kpi-value">{{ $terminees }}</div>
    </div>

    <div class="kpi-card late">
        <div class="kpi-label">En retard ⚠️</div>
        <div class="kpi-value">{{ $enRetard }}</div>
    </div>
</div>

{{-- 2. Répartition du travail par membre (CDC §7) --}}
<div class="card" style="background:#fff; border:1px solid var(--line); border-radius:12px; padding:20px;">
    <h2 style="font-size:16px; font-weight:600; margin-bottom:16px;">Répartition des tâches par membre</h2>
    
    @if(count($membresStats) > 0)
        <div style="display:flex; flex-direction:column; gap:16px;">
            @foreach($membresStats as $ms)
                @php
                    $pourcent = $total > 0 ? round(($ms['tasks_count'] / $total) * 100) : 0;
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
@endsection