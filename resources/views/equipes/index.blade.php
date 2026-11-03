@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/equipes.css') }}">
@endpush

@section('title', 'Mes Équipes · Taskflow')
@section('heading', 'Mes Équipes')

@section('actions')
    <a href="{{ route('equipes.create') }}" class="btn btn-dark" style="background:var(--primary, #C83F74); color:#fff; padding:8px 16px; border-radius:8px; text-decoration:none; font-weight:500;">
        + Nouvelle équipe
    </a>
@endsection

@section('content')
<div class="equipes-grid">
    @forelse ($equipes as $equipe)
        @php
            $monRole = $equipe->pivot->role ?? 'MEMBRE';
            $isAdmin = ($monRole === 'ADMIN');
        @endphp
        <article class="equipe-card">
            <div>
                <div class="equipe-card-header">
                    <h2 style="margin:0;">
                        <a href="{{ route('equipes.show', $equipe->id) }}" class="equipe-title">
                            {{ $equipe->nom }}
                        </a>
                    </h2>
                    <span class="badge-role {{ strtolower($monRole) }}">
                        {{ $monRole }}
                    </span>
                </div>

                <p class="equipe-desc">
                    {{ $equipe->description ?? 'Aucune description disponible pour cette équipe.' }}
                </p>
            </div>

            <div class="equipe-card-footer">
                <span style="color:var(--text-muted);">
                    👥 {{ $equipe->membres_count ?? $equipe->membres->count() }} / 6 membre(s)
                </span>
                <a href="{{ route('equipes.show', $equipe->id) }}" style="color:var(--primary, #C83F74); font-weight:500; text-decoration:none;">
                    Gérer &rarr;
                </a>
            </div>
        </article>
    @empty
        <div style="grid-column: 1 / -1; text-align:center; padding:48px 0; background:#fff; border:1px dashed var(--line); border-radius:12px;">
            <p style="color:var(--muted); margin-bottom:16px;">Vous n'appartenez à aucune équipe pour le moment.</p>
            <a href="{{ route('equipes.create') }}" class="btn" style="background:var(--primary, #C83F74); color:#fff; padding:8px 16px; border-radius:8px; text-decoration:none;">
                Créer ma première équipe
            </a>
        </div>
    @endforelse
</div>
@endsection