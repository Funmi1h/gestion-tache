@extends('layouts.app')

@section('title', 'Mes Équipes · Taskflow')
@section('heading', 'Mes Équipes')

@section('actions')
    <a href="{{ route('equipes.create') }}" class="btn btn-dark" style="background:var(--primary, #2563EB); color:#fff; padding:8px 16px; border-radius:8px; text-decoration:none; font-weight:500;">
        + Nouvelle équipe
    </a>
@endsection

@section('content')
<div class="cards" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(320px, 1fr)); gap:16px;">
    @forelse ($equipes as $equipe)
        @php
            // Rôle dans la table pivot membre_equipe
            $monRole = $equipe->pivot->role ?? 'MEMBRE';
            $isAdmin = ($monRole === 'ADMIN');
        @endphp
        <article class="card" style="background:#fff; border:1px solid var(--line); border-radius:12px; padding:20px; display:flex; flex-direction:column; justify-content:space-between;">
            <div>
                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:8px;">
                    <h2 style="font-size:18px; font-weight:600; margin:0;">
                        <a href="{{ route('equipes.show', $equipe->id) }}" style="text-decoration:none; color:inherit;">
                            {{ $equipe->nom }}
                        </a>
                    </h2>
                    {{-- Badge de rôle --}}
                    <span style="font-size:11px; font-weight:600; padding:3px 8px; border-radius:12px; {{ $isAdmin ? 'background:#EDE9FE; color:#5B21B6;' : 'background:#E0F2FE; color:#075985;' }}">
                        {{ $monRole }}
                    </span>
                </div>

                <p style="color:var(--text-muted, #64748B); font-size:13px; line-height:1.4; margin-bottom:16px;">
                    {{ $equipe->description ?? 'Aucune description disponible.' }}
                </p>
            </div>

            <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid var(--line); padding-top:12px; font-size:13px;">
                <span style="color:var(--text-muted, #64748B);">
                    👥 {{ $equipe->membres_count ?? $equipe->membres->count() }} / 6 membre(s)
                </span>
                <a href="{{ route('equipes.show', $equipe->id) }}" style="color:var(--primary, #2563EB); font-weight:500; text-decoration:none;">
                    Gérer &rarr;
                </a>
            </div>
        </article>
    @empty
        <div style="grid-column: 1 / -1; text-align:center; padding:48px 0; background:#fff; border:1px dashed var(--line); border-radius:12px;">
            <p style="color:var(--muted); margin-bottom:16px;">Vous n'appartenez à aucune équipe pour le moment.</p>
            <a href="{{ route('equipes.create') }}" class="btn" style="background:var(--primary, #2563EB); color:#fff; padding:8px 16px; border-radius:8px; text-decoration:none;">
                Créer ma première équipe
            </a>
        </div>
    @endforelse
</div>
@endsection