@extends('layouts.app')

@section('title', 'Créer une équipe · Taskflow')
@section('heading', 'Créer une nouvelle équipe')

@section('content')
<div class="form-card">
    <p class="form-help" style="margin-bottom: 20px; font-size: 14px;">
        En créant cette équipe, vous en devenez l'administrateur. Vous pouvez dès à présent inviter jusqu'à 5 collaborateurs.
    </p>

    <form method="POST" action="{{ route('equipes.store') }}">
        @csrf

        <div class="form-group">
            <label for="nom" class="form-label">Nom de l'équipe *</label>
            <input id="nom" name="nom" type="text" value="{{ old('nom') }}" required autofocus
                   placeholder="Ex: Équipe Marketing, Pôle Dev..." class="form-input">
            @error('nom')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="description" class="form-label">Description (facultative)</label>
            <textarea id="description" name="description" rows="3"
                      placeholder="Objectif ou mission de l'équipe..." class="form-textarea">{{ old('description') }}</textarea>
            @error('description')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <div class="collab-header">
                <div>
                    <span class="form-label" style="margin-bottom:0;">Inviter des collaborateurs</span>
                    <span class="form-help" id="counter-text">1 sur 5 places utilisées</span>
                </div>
                <button type="button" id="btn-add" class="btn-add-email" title="Ajouter un autre e-mail">
                    + Ajouter un collaborateur
                </button>
            </div>

            <div id="emails-container">
                {{--premier champ de mail la par defaut --}}
                <div class="collab-row">
                    <input type="email" name="emails[]" placeholder="collaborateur1@exemple.com" class="form-input">
                    <button type="button" class="btn-remove-email" style="visibility: hidden;" aria-hidden="true">&times;</button>
                </div>
            </div>
            <p class="form-help">Les invitations par e-mail seront envoyées dès la validation.</p>
        </div>

        <div class="form-footer">
            <button type="submit" class="btn btn-dark" style="background:var(--primary); color:#fff; padding:10px 20px; border-radius:8px; border:0; cursor:pointer;">
                Créer l'équipe et envoyer les invitations
            </button>
            <a href="{{ route('equipes.index') }}" class="link" style="color:var(--text-muted); text-decoration:none;">
                Annuler
            </a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const container = document.getElementById('emails-container');
        const btnAdd = document.getElementById('btn-add');
        const counterText = document.getElementById('counter-text');
        const MAX_EMAILS = 5;

        function updateCounter() {
            const currentCount = container.querySelectorAll('.collab-row').length;
            counterText.textContent = `${currentCount} sur ${MAX_EMAILS} places utilisées`;
            
            // Si on a atteint la limite de 5, on désactive le bouton "+"
            if (currentCount >= MAX_EMAILS) {
                btnAdd.disabled = true;
                btnAdd.textContent = 'Limite atteinte (max 5)';
            } else {
                btnAdd.disabled = false;
                btnAdd.textContent = '+ Ajouter un collaborateur';
            }
        }

        // Clic sur le bouton "+"
        btnAdd.addEventListener('click', function () {
            const currentRows = container.querySelectorAll('.collab-row');
            if (currentRows.length >= MAX_EMAILS) return;

            const nextIndex = currentRows.length + 1;
            const newRow = document.createElement('div');
            newRow.className = 'collab-row';
            newRow.innerHTML = `
                <input type="email" name="emails[]" placeholder="collaborateur${nextIndex}@exemple.com" class="form-input">
                <button type="button" class="btn-remove-email" title="Supprimer">&times;</button>
            `;

            // Bouton de suppression de la ligne
            newRow.querySelector('.btn-remove-email').addEventListener('click', function () {
                newRow.remove();
                updateCounter();
            });

            container.appendChild(newRow);
            updateCounter();
        });
    });
</script>
@endpush