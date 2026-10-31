{{-- resources/views/activity.blade.php : Route::view('/activite', 'activity') --}}
@extends('layouts.app')

@section('title', 'Activité')
@section('heading', 'Activité')

@push('styles')
    <style>
        .feed{max-width:720px}
        .day{margin:24px 0 6px;color:var(--muted);font-size:12px;font-weight:500}
        .day:first-child{margin-top:0}
        .event{display:grid;grid-template-columns:28px 1fr auto;gap:14px;align-items:start;padding:12px 0;border-bottom:1px solid var(--line)}
        .event:last-child{border-bottom:0}
        .event b{font-weight:600}
        .tag{color:var(--blue);font-weight:500}
    </style>
@endpush

@section('content')
    @php
        // Valeurs d'exemple : passe $activity depuis ton contrôleur pour les remplacer.
        // Chaque jour contient ses évènements : [auteur, action, cible, heure].
        $activity = $activity ?? [
            'Aujourd’hui' => [
                ['Sophia Williams', 'a envoyé en revue', 'Prototype de l’application', '14:20'],
                ['David Miller', 'a terminé', 'Importer les contacts clients', '11:05'],
            ],
            'Hier' => [
                ['Michael Johnson', 'a commenté', 'Valider les maquettes', '17:42'],
                ['Emily Carter', 'a créé la tâche', 'Rédiger le guide d’onboarding', '10:15'],
            ],
            '25 février' => [
                ['James Anderson', 'a terminé le projet', 'Campagne réseaux sociaux T1', '16:30'],
            ],
        ];
    @endphp

    <div class="sec">
        <div class="feed">
            @forelse ($activity as $day => $events)
                <div class="day">{{ $day }}</div>
                @foreach ($events as [$who, $action, $target, $time])
                    <div class="event">
                        <span class="avatar">{{ collect(explode(' ', $who))->map(fn ($w) => mb_substr($w, 0, 1))->join('') }}</span>
                        <div><b>{{ $who }}</b> {{ $action }} <span class="tag">{{ $target }}</span></div>
                        <span class="sub num">{{ $time }}</span>
                    </div>
                @endforeach
            @empty
                <p class="sub">Aucune activité pour le moment.</p>
            @endforelse
        </div>
    </div>
@endsection
