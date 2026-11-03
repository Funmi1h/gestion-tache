<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // on récupere toutes les équipes de l'utilisateur
        $equipes = $user->equipes()->with(['membres', 'taches.responsable'])->get();

        // Cas où l'utilisateur n'a aucune équipe (nouveau compte)
        if ($equipes->isEmpty()) {
            return view('dashboard.index', [
                'equipes'        => $equipes,
                'equipeCourante' => null,
                'stats'          => null,
                'membresStats'   => [],
            ]);
        }

        // 2. Déterminer l'équipe active (par paramètre ?equipe_id= ou la première par défaut)
        $equipeId = $request->query('equipe_id', $equipes->first()->id);
        $equipeCourante = $equipes->firstWhere('id', $equipeId) ?? $equipes->first();

        // 3. Calculer les statistiques réelles de l'équipe (CDC §7)
        $taches = $equipeCourante->taches ?? collect();

        $stats = [
            'total'     => $taches->count(),
            'a_faire'   => $taches->where('statut', 'A_FAIRE')->count(),
            'en_cours'  => $taches->where('statut', 'EN_COURS')->count(),
            'terminees' => $taches->where('statut', 'TERMINEE')->count(),
            // En retard : date dépassée et non terminée
            'en_retard' => $taches->filter(function ($t) {
                return $t->date_echeance && \Carbon\Carbon::parse($t->date_echeance)->isPast() && $t->statut !== 'TERMINEE';
            })->count(),
        ];

        // 4. Répartition des tâches par membre (CDC §7)
        $membresStats = $equipeCourante->membres->map(function ($membre) use ($taches) {
            $count = $taches->where('membre_equipe_id', $membre->pivot->id ?? null)->count();
            return [
                'name'        => ($membre->first_name ?? '') . ' ' . $membre->name,
                'tasks_count' => $count,
            ];
        });

        // pourcentage de progression globale de l'équipe
        $tauxAvancement = ($stats['total'] > 0) ? round(($stats['terminees'] / $stats['total']) * 100) : 0;

        // on récupere les taches dont l'utilisateur connecté est responsable
        $monMembre = $equipeCourante->membres->firstWhere('id', $user->id);
        $monMembreId = $monMembre?->pivot?->id;
        $mesTaches = $monMembreId ? $taches->where('membre_equipe_id', $monMembreId) : collect();

        // envoi des données à la vue
        return view('dashboard.index', compact('equipes', 'equipeCourante', 'stats', 'membresStats', 'tauxAvancement', 'mesTaches'));
    }
}