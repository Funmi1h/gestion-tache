<?php

namespace App\Http\Controllers;

use App\Models\Equipe;
use App\Models\MembreEquipe;
use App\Models\Tache;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TacheController extends Controller
{
    public function index(Request $request): View
    {
        // a retirer plus tard,c'est pour avoir un apercu de la page quand on est pas conecté
        if (! $request->user()) {
            return view('tasks', [
                'equipes' => collect(),
                'equipeId' => null,
                'membres' => collect(),
                'taches' => collect(),
                'apercu' => true,
            ]);
        }

        // on cherche les equipes dont l'utilisateur connecté est membre pour savoir quelles taches afficher
        $userId = $request->user()->id;
        $equipeIds = MembreEquipe::where('user_id', $userId)->pluck('equipe_id');
        $equipes = Equipe::whereIn('id', $equipeIds)->orderBy('nom')->get();
        $filters = $request->validate([
            'equipe_id' => ['nullable', 'integer'],
            'statut' => ['nullable', 'in:A_FAIRE,EN_COURS,TERMINEE'],
            'membre_equipe_id' => ['nullable', 'integer', 'exists:membre_equipe,id'],
            'en_retard' => ['nullable', 'boolean'],
        ]);

        $equipeId = $filters['equipe_id'] ?? $equipes->first()?->id;
        abort_if($equipeId && ! $equipeIds->contains((int) $equipeId), 403);
        $taches = collect();
        $membres = collect();

        if ($equipeId) {
            $membres = MembreEquipe::with('utilisateur')->where('equipe_id', $equipeId)->get();
            $query = Tache::with(['equipe', 'createur', 'responsable.utilisateur'])
                ->where('equipe_id', $equipeId);

            if (! empty($filters['statut'])) {
                $query->where('statut', $filters['statut']);
            }
            if (! empty($filters['membre_equipe_id'])) {
                $this->ensureAssigneeInTeam($filters['membre_equipe_id'], $equipeId);
                $query->where('membre_equipe_id', $filters['membre_equipe_id']);
            }
            if ($request->boolean('en_retard')) {
                $query->whereDate('date_echeance', '<', today())->where('statut', '!=', 'TERMINEE');
            }

            $taches = $query->latest()->get();
        }

        return view('tasks', compact('equipes', 'equipeId', 'membres', 'taches') + ['apercu' => false]);
    }

    public function create(Request $request): View
    {
        $equipeIds = MembreEquipe::where('user_id', $request->user()->id)->pluck('equipe_id');
        $equipes = Equipe::whereIn('id', $equipeIds)->orderBy('nom')->get();
        $membres = MembreEquipe::with(['equipe', 'utilisateur'])->whereIn('equipe_id', $equipeIds)->get();

        return view('tasks.form', compact('equipes', 'membres'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'equipe_id' => ['required', 'integer', 'exists:equipes,id'],
            'membre_equipe_id' => ['required', 'integer', 'exists:membre_equipe,id'],
            'titre' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'date_echeance' => ['nullable', 'date'],
        ]);
        $this->ensureUserInTeam($request, $data['equipe_id']);
        $this->ensureAssigneeInTeam($data['membre_equipe_id'], $data['equipe_id']);

        $tache = Tache::create($data + [
            'createur_id' => $request->user()->id,
            'statut' => 'A_FAIRE',
        ]);

        return redirect()->route('tasks.show', $tache)->with('status', 'Tâche créée.');
    }

    public function show(Request $request, Tache $tache): View
    {
        $membre = $this->ensureUserInTeam($request, $tache->equipe_id);
        $tache->load(['equipe', 'createur', 'responsable.utilisateur']);
        $canManage = (int) $tache->createur_id === (int) $request->user()->id || $membre->role === 'admin';
        $isResponsable = (int) $tache->responsable->user_id === (int) $request->user()->id;

        return view('tasks.show', compact('tache', 'canManage', 'isResponsable'));
    }

    public function edit(Request $request, Tache $tache): View
    {
        $this->ensureCanManage($request, $tache);
        $membres = MembreEquipe::with('utilisateur')->where('equipe_id', $tache->equipe_id)->get();

        return view('tasks.form', compact('tache', 'membres'));
    }

    public function update(Request $request, Tache $tache): RedirectResponse
    {
        $this->ensureCanManage($request, $tache);
        $data = $request->validate([
            'membre_equipe_id' => ['required', 'integer', 'exists:membre_equipe,id'],
            'titre' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'date_echeance' => ['nullable', 'date'],
        ]);
        $this->ensureAssigneeInTeam($data['membre_equipe_id'], $tache->equipe_id);
        $tache->update($data);

        return redirect()->route('tasks.show', $tache)->with('status', 'Tâche modifiée.');
    }

    public function destroy(Request $request, Tache $tache): RedirectResponse
    {
        $this->ensureCanManage($request, $tache);
        $tache->delete();

        return redirect()->route('tasks')->with('status', 'Tâche supprimée.');
    }

    public function updateStatut(Request $request, Tache $tache): RedirectResponse
    {
        abort_unless(
            MembreEquipe::whereKey($tache->membre_equipe_id)
                ->where('user_id', $request->user()->id)
                ->exists(),
            403
        );
        $data = $request->validate(['statut' => ['required', 'in:A_FAIRE,EN_COURS,TERMINEE']]);
        $tache->update($data);

        return back()->with('status', 'Statut mis à jour.');
    }

    private function ensureUserInTeam(Request $request, int $equipeId): MembreEquipe
    {
        return MembreEquipe::where('user_id', $request->user()->id)
            ->where('equipe_id', $equipeId)
            ->firstOrFail();
    }

    private function ensureCanManage(Request $request, Tache $tache): void
    {
        $membre = $this->ensureUserInTeam($request, $tache->equipe_id);
        abort_unless((int) $tache->createur_id === (int) $request->user()->id || $membre->role === 'admin', 403);
    }

    private function ensureAssigneeInTeam(int $membreEquipeId, int $equipeId): void
    {
        abort_unless(
            MembreEquipe::whereKey($membreEquipeId)->where('equipe_id', $equipeId)->exists(),
            422,
            'Le responsable doit appartenir à cette équipe.'
        );
    }
}
