{{-- resources/views/dashboard.blade.php : tableau de bord --}}
@extends('layouts.app')

@section('title', 'Tableau de bord · Taskflow')

@section('content')
    {{-- Valeurs par défaut : un contrôleur peut passer ces mêmes variables pour les remplacer --}}
    @php
        $stats = $stats ?? [
            ['label' => 'Tâches terminées', 'value' => 60, 'trend' => '↗ 15 %', 'up' => true, 'prev' => '52 la semaine dernière'],
            ['label' => 'Projets actifs', 'value' => 4, 'trend' => '↗ 2 %', 'up' => true, 'prev' => '3 la semaine dernière'],
            ['label' => 'En retard', 'value' => 3, 'trend' => '↘ 1', 'up' => false, 'prev' => '4 la semaine dernière'],
        ];
        $days = $days ?? [['Lundi', 24], ['Mardi', 9], ['Mercredi', 32], ['Jeudi', 12], ['Vendredi', 8]];
        $projects = $projects ?? [
            ['name' => 'Implémentation du CRM', 'progress' => 33],
            ['name' => 'Refonte du site web', 'progress' => 25],
            ['name' => 'Campagne réseaux sociaux T1', 'progress' => 100],
            ['name' => 'Automatisation RH interne', 'progress' => 0],
        ];
        $team = $team ?? [
            ['name' => 'David Miller', 'tasks' => 4],
            ['name' => 'Michael Johnson', 'tasks' => 5],
            ['name' => 'James Anderson', 'tasks' => 2],
            ['name' => 'Sophia Williams', 'tasks' => 2],
            ['name' => 'Emily Carter', 'tasks' => 2],
        ];
        $todos = $todos ?? [
            ['title' => 'Prototype de l’application', 'meta' => '2 mars 2026 · Sophia Williams', 'status' => 'En revue'],
        ];

        $totalDone = max(array_sum(array_column($days, 1)), 1);
        $maxDay = max(array_column($days, 1)) ?: 1;
        $selected = array_search($maxDay, array_column($days, 1));
        $maxTasks = max(array_column($team, 'tasks')) ?: 1;
        $initials = fn ($name) => collect(explode(' ', $name))->map(fn ($p) => mb_substr($p, 0, 1))->join('');
    @endphp

    <div class="head">
        <h1>Tableau de bord</h1>
        <button class="btn" type="button">Cette semaine ▾</button>
    </div>

    <div class="grid">
        <div class="left">
            <section class="stats">
                @foreach ($stats as $stat)
                    <div class="stat">
                        <div class="sub">{{ $stat['label'] }}</div>
                        <div class="stat-value">
                            <b>{{ $stat['value'] }}</b>
                            <span class="{{ $stat['up'] ? 'up' : 'down' }}">{{ $stat['trend'] }}</span>
                        </div>
                        <div class="sub">{{ $stat['prev'] }}</div>
                    </div>
                @endforeach
            </section>

            <section class="chart">
                <h2>Tâches terminées par jour</h2>
                <div class="sub">Clique sur une barre pour voir le détail</div>
                <div class="bars" id="bars">
                    @foreach ($days as $i => $day)
                        <button type="button" class="col {{ $i === $selected ? 'sel' : '' }}" aria-label="{{ $day[0] }} : {{ $day[1] }} tâches">
                            <div class="tip"><b>{{ $day[1] }}</b>{{ round($day[1] / $totalDone * 100) }} %<div class="sub">Tâches terminées</div></div>
                            <div class="bar" style="height: {{ max($day[1] / $maxDay * 100, 14) }}%"></div>
                        </button>
                    @endforeach
                </div>
                <div class="days">
                    @foreach ($days as $day)<span>{{ $day[0] }}</span>@endforeach
                </div>
            </section>

            <section class="bottom">
                <h2>Avancement</h2>
                <div class="seg" role="group" aria-label="Vue">
                    <button type="button" class="on" data-view="projects">Projets</button>
                    <button type="button" data-view="team">Équipe</button>
                </div>

                <div id="view-projects">
                    @foreach ($projects as $project)
                        <div class="row">
                            <span>{{ $project['name'] }}</span>
                            <div class="track" role="progressbar" aria-valuenow="{{ $project['progress'] }}" aria-valuemin="0" aria-valuemax="100"><div class="fill" style="width: {{ $project['progress'] }}%"></div></div>
                            <span class="num">{{ $project['progress'] }} %</span>
                        </div>
                    @endforeach
                </div>

                <div id="view-team" hidden>
                    @foreach ($team as $m)
                        <div class="row">
                            <span>{{ $m['name'] }}</span>
                            <div class="track"><div class="fill" style="width: {{ $m['tasks'] / $maxTasks * 100 }}%"></div></div>
                            <span class="num">{{ $m['tasks'] }} tâches</span>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>

        <aside>
            <div class="sec cta">
                <div>
                    <h2>Lance ton prochain projet</h2>
                    <div class="sub">Crée un projet et assigne tes premières tâches</div>
                </div>
                <a href="#" class="btn btn-dark">Nouveau projet</a>
            </div>

            <div class="sec">
                <h2>Charge de l’équipe</h2>
                <div style="margin-top:8px">
                    @foreach ($team as $m)
                        <div class="member">
                            <span class="avatar">{{ $initials($m['name']) }}</span>
                            <span>{{ $m['name'] }}</span>
                            <div class="track"><div class="fill" style="width: {{ $m['tasks'] / $maxTasks * 100 }}%"></div></div>
                            <span class="num">{{ $m['tasks'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="sec">
                <h2>À faire cette semaine</h2>
                @forelse ($todos as $todo)
                    <div class="todo">
                        <div>
                            <div style="font-weight:500">{{ $todo['title'] }}</div>
                            <div class="sub">{{ $todo['meta'] }}</div>
                        </div>
                        <span class="badge">{{ $todo['status'] }}</span>
                    </div>
                @empty
                    <p class="sub" style="margin-top:12px">Rien de prévu cette semaine.</p>
                @endforelse
            </div>
        </aside>
    </div>

    <script>
        var bars = document.getElementById('bars');
        if (bars) {
            bars.addEventListener('click', function (e) {
                var col = e.target.closest('.col');
                if (!col) return;
                bars.querySelectorAll('.col').forEach(function (c) { c.classList.toggle('sel', c === col); });
            });
        }

        document.querySelectorAll('.seg button').forEach(function (btn) {
            btn.addEventListener('click', function () {
                document.querySelectorAll('.seg button').forEach(function (b) { b.classList.toggle('on', b === btn); });
                var projects = document.getElementById('view-projects');
                var team = document.getElementById('view-team');
                if (projects) projects.hidden = btn.dataset.view !== 'projects';
                if (team) team.hidden = btn.dataset.view !== 'team';
            });
        });
    </script>
@endsection
