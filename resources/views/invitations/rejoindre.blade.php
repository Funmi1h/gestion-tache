@extends('layouts.auth')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endpush

@section('title', 'Invitation d\'équipe · Taskflow')

@section('form')
<div class="auth-container">
    <div class="auth-header">
        <h1 class="auth-title">Invitation d'équipe</h1>
        <p class="auth-subtitle">
            Vous êtes invité(e) à rejoindre l'équipe <strong>{{ $invitation->equipe->nom }}</strong>.
        </p>
    </div>

    @if (session('error'))
        <div style="background:#fee2e2; color:#991b1b; padding:10px; border-radius:8px; margin-bottom:16px; font-size:13px;">
            {{ session('error') }}
        </div>
    @endif

    <div style="background:var(--bg-page, #f8fafc); border:1px solid var(--line, #e2e8f0); border-radius:8px; padding:12px 14px; margin-bottom:20px; font-size:13px;">
        <strong>Destinataire :</strong> {{ $invitation->email }}
    </div>

    @auth
        {{-- Cas 2 : Utilisateur connecté (Vérification sécurité CDC §5.1) --}}
        @if(auth()->user()->email === $invitation->email)
            <form method="POST" action="{{ route('invitations.valider', $invitation->token) }}">
                @csrf
                <p style="font-size:13px; color:var(--text-muted); margin-bottom:16px;">
                    Connecté avec le compte <strong>{{ auth()->user()->email }}</strong>. Cliquez pour accepter l'invitation.
                </p>
                <button type="submit" class="auth-btn-primary">
                    Accepter l'invitation et rejoindre l'équipe
                </button>
            </form>
        @else
            <div style="background:#fee2e2; color:#991b1b; padding:12px; border-radius:8px; font-size:13px; line-height:1.4;">
                ⚠️ Vous êtes actuellement connecté avec <strong>{{ auth()->user()->email }}</strong>, mais l'invitation s'adresse à <strong>{{ $invitation->email }}</strong>. Veuillez vous déconnecter pour accepter.
            </div>
        @endif
    @else
        {{-- Cas 1 : Utilisateur non connecté --}}
        <p style="font-size:13px; color:var(--text-muted); margin-bottom:16px; text-align:center;">
            Pour rejoindre cette équipe, vous devez posséder un compte avec l'adresse <strong>{{ $invitation->email }}</strong>.
        </p>
        <div style="display:flex; flex-direction:column; gap:10px;">
            <a href="{{ route('register') }}?invitation_token={{ $invitation->token }}&email={{ urlencode($invitation->email) }}" 
               class="auth-btn-primary" style="text-align:center; text-decoration:none;">
                Créer un compte et rejoindre
            </a>
            <a href="{{ route('login') }}" 
               style="text-align:center; font-size:13px; color:var(--text-muted); text-decoration:none; padding:8px;">
                J'ai déjà un compte, me connecter
            </a>
        </div>
    @endauth
</div>
@endsection