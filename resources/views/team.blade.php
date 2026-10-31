{{-- resources/views/team.blade.php : Route::view('/equipe', 'team') --}}
@extends('layouts.app')

@section('title', 'Équipe')
@section('heading', 'Équipe')
@section('actions')
    <a href="#" class="btn">Inviter un membre</a>
@endsection

@push('styles')
    <style>
        .card.member{display:flex;flex-direction:column;gap:16px}
        .card.member .avatar{width:44px;height:44px;font-size:14px}
        .mini-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;text-align:center}
        .mini-stats b{display:block;font-size:20px;font-weight:500;letter-spacing:-.02em}
    </style>
@endpush

@section('content')
    @php
        // Valeurs d'exemple : passe $team depuis ton contrôleur pour les remplacer.
        $team = $team ?? [
            ['name' => 'David Miller', 'role' => 'Chef de projet', 'open' => 4, 'done' => 12, 'late' => 0],
            ['name' => 'Michael Johnson', 'role' => 'Développeur', 'open' => 5, 'done' => 9, 'late' => 1],
            ['name' => 'James Anderson', 'role' => 'Marketing', 'open' => 2, 'done' => 15, 'late' => 0],
            ['name' => 'Sophia Williams', 'role' => 'Designer', 'open' => 2, 'done' => 11, 'late' => 0],
            ['name' => 'Emily Carter', 'role' => 'RH', 'open' => 2, 'done' => 7, 'late' => 2],
        ];
        $capacity = 6; // nombre de tâches ouvertes considéré comme une pleine charge
    @endphp

    <div class="cards">
        @forelse ($team as $m)
            @php $load = min($m['open'] / $capacity * 100, 100); @endphp
            <article class="card member">
                <div style="display:flex;align-items:center;gap:14px">
                    <span class="avatar">{{ collect(explode(' ', $m['name']))->map(fn ($w) => mb_substr($w, 0, 1))->join('') }}</span>
                    <div>
                        <h2>{{ $m['name'] }}</h2>
                        <div class="sub">{{ $m['role'] }}</div>
                    </div>
                </div>
                <div>
                    <div class="between" style="margin-bottom:8px"><span class="sub">Charge de travail</span><span class="num">{{ $m['open'] }} / {{ $capacity }}</span></div>
                    <div class="track"><div class="fill" style="width: {{ $load }}%;{{ $load >= 100 ? 'background:var(--bad)' : '' }}"></div></div>
                </div>
                <div class="mini-stats">
                    <div><b>{{ $m['open'] }}</b><span class="sub">En cours</span></div>
                    <div><b>{{ $m['done'] }}</b><span class="sub">Terminées</span></div>
                    <div><b style="{{ $m['late'] ? 'color:var(--bad)' : '' }}">{{ $m['late'] }}</b><span class="sub">En retard</span></div>
                </div>
            </article>
        @empty
            <p class="sub">Aucun membre pour le moment.</p>
        @endforelse
    </div>
@endsection
