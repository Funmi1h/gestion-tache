@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/equipes.css') }}">
@endpush

@section('title', $equipe->nom . ' · Taskflow')
@section('heading', $equipe->nom)

@section('actions')
    <a href="{{ route('equipes.index') }}" style="text-decoration:none; color:var(--text-muted); font-size:13px;">
        &larr; Retour aux équipes
    </a>
@endsection

@section('content')
@php
    $membreActuel = $equipe->membres->firstWhere('id', auth()->id());
    $monRole = $membreActuel ? $membreActuel->pivot->role : 'MEMBRE';
    // l'admin ou le createur de l'equipe a les droits d'administration
    $isAdmin = (strtolower($monRole) === 'admin' || (int) $equipe->createur_id === (int) auth()->id());

    $invitationsEnAttente = $equipe->invitations ? $equipe->invitations->where('statut', 'en_attente') : collect();
    $totalOccupes = $equipe->membres->count() + $invitationsEnAttente->count();
    $peutInviter = $isAdmin && ($totalOccupes < 6);
@endphp

<div class="show-layout">
    {{-- Colonne Principale : Informations & Membres --}}
    <div>
        <div class="card" style="background:#fff; border:1px solid var(--line); border-radius:12px; padding:20px; margin-bottom:20px;">
            <p style="color:var(--text-muted); font-size:14px; margin:0 0 16px;">
                {{ $equipe->description ?? 'Aucune description disponible pour cette équipe.' }}
            </p>

            {{-- Jauge des 6 places (CDC §3.2) --}}
            <div class="gauge-box">
                <span>Capacité de l'équipe :</span>
                <span class="gauge-count {{ $totalOccupes >= 6 ? 'full' : '' }}">
                    {{ $totalOccupes }} / 6 places ({{ $equipe->membres->count() }} membres + {{ $invitationsEnAttente->count() }} en attente)
                </span>
            </div>
        </div>

        <h2 style="font-size:17px; font-weight:600; margin-bottom:12px;">Membres ({{ $equipe->membres->count() }})</h2>
        <div class="card" style="background:#fff; border:1px solid var(--line); border-radius:12px; overflow:hidden;">
            @foreach($equipe->membres as $membre)
                <div class="member-item">
                    <div style="display:flex; align-items:center; gap:12px;">
                        <span style="width:34px; height:34px; border-radius:50%; background:#e2e8f0; display:flex; align-items:center; justify-content:center; font-weight:600; font-size:12px;">
                            {{ strtoupper(substr($membre->first_name ?? $membre->name, 0, 1)) }}
                        </span>
                        <div>
                            <div style="font-weight:500; font-size:14px;">
                                {{ $membre->first_name }} {{ $membre->name }}
                                @if($membre->id === auth()->id()) <span style="color:var(--text-muted); font-size:12px;">(Vous)</span> @endif
                            </div>
                            <div style="color:var(--text-muted); font-size:12px;">{{ $membre->email }}</div>
                        </div>
                    </div>

                    <span class="badge-role {{ strtolower($membre->pivot->role) }}">
                        {{ $membre->pivot->role }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Colonne Latérale : Invitations (ADMIN uniquement) --}}
    <div>
        @if($isAdmin)
            <div class="card" style="background:#fff; border:1px solid var(--line); border-radius:12px; padding:20px; margin-bottom:20px;">
                <h3 style="font-size:15px; font-weight:600; margin-bottom:6px;">Inviter un membre</h3>
                <p style="font-size:12px; color:var(--text-muted); margin-bottom:14px;">
                    Envoie un email contenant un lien sécurisé d'invitation.
                </p>

                @if($peutInviter)
                    <form method="POST" action="{{ route('equipes.invitations.store', $equipe->id) }}" novalidate>
                        @csrf
                        <div style="margin-bottom:12px;">
                            <input type="email" name="email" required placeholder="collaborateur@email.com" class="form-input">
                            @error('email')
                                <span style="color:#b91c1c; font-size:12px; display:block; margin-top:4px;">{{ $message }}</span>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-primary" style="width:100%; padding:10px; font-weight:500;">
                            Envoyer l'invitation
                        </button>
                    </form>
                @else
                    <div style="background:#fee2e2; color:#991b1b; padding:10px 14px; border-radius:8px; font-size:12px; border:1px solid #fca5a5;">
                        Limite de 6 places atteinte. Libérez une place pour pouvoir inviter à nouveau.
                    </div>
                @endif
            </div>

            {{-- Invitations en attente --}}
            @if($invitationsEnAttente->count() > 0)
                <div class="card" style="background:#fff; border:1px solid var(--line); border-radius:12px; padding:16px;">
                    <h4 style="font-size:12px; font-weight:600; color:var(--text-muted); text-transform:uppercase; margin-bottom:10px;">
                        Invitations en attente ({{ $invitationsEnAttente->count() }})
                    </h4>
                    @foreach($invitationsEnAttente as $invitation)
                        <div style="display:flex; justify-content:space-between; align-items:center; padding:8px 0; border-bottom:1px solid var(--line); font-size:13px;">
                            <span style="overflow:hidden; text-overflow:ellipsis; max-width:180px;">{{ $invitation->email }}</span>
                            <form method="POST" action="{{ route('invitations.annuler', $invitation->id) }}" style="margin:0;">
                                @csrf
                                <button type="submit" onclick="return confirm('Annuler cette invitation ?')" style="background:none; border:0; color:#b91c1c; cursor:pointer; font-size:12px; font-weight:500;">
                                    Annuler
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif
        @else
            <div class="card" style="background:#f8fafc; border:1px dashed var(--line); border-radius:12px; padding:16px; text-align:center;">
                <p style="font-size:13px; color:var(--text-muted); margin:0;">
                    Seuls les administrateurs (ADMIN) peuvent envoyer des invitations.
                </p>
            </div>
        @endif
    </div>
</div>
@endsection