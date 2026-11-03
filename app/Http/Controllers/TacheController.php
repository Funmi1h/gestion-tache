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
        // on cherche les equipes dont l'utilisateur connecté est membre pour savoir quelles taches afficher
        $userId = $request->user()?->id;
        $equipeIds = $userId ? MembreEquipe::where('user_id', $userId)->pluck('equipe_id') : collect();
        $equipes = $equipeIds->isNotEmpty() ? Equipe::whereIn('id', $equipeIds)->orderBy('nom')->get() : collect();
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

        return view('taches.index', compact('equipes', 'equipeId', 'membres', 'taches'));
    }

    public function create(Request $request): View
    {
        $equipeIds = MembreEquipe::where('user_id', $request->user()->id)->pluck('equipe_id');
        $equipes = Equipe::whereIn('id', $equipeIds)->orderBy('nom')->get();
        $membres = MembreEquipe::with(['equipe', 'utilisateur'])->whereIn('equipe_id', $equipeIds)->get();

        return view('taches.form', compact('equipes', 'membres'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'equipe_id' => ['required', 'integer', 'exists:equipes,id'],
            'membre_equipe_id' => ['required', 'integer', 'exists:membre_equipe,id'],
            'titre' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'date_echeance' => ['nullable', 'date'],
        ], [
            'equipe_id.required' => 'Veuillez sélectionner une équipe.',
            'equipe_id.exists' => 'L\'équipe sélectionnée n\'existe pas.',
            'membre_equipe_id.required' => 'Veuillez désigner un responsable pour cette tâche.',
            'membre_equipe_id.exists' => 'Le responsable sélectionné n\'est pas valide.',
            'titre.required' => 'Le titre de la tâche est obligatoire.',
            'titre.max' => 'Le titre ne peut pas dépasser 255 caractères.',
            'date_echeance.date' => 'La date d\'échéance doit être une date valide.',
        ]);
        $this->ensureUserInTeam($request, $data['equipe_id']);
        $this->ensureAssigneeInTeam($data['membre_equipe_id'], $data['equipe_id']);

        $tache = Tache::create($data + [
            'createur_id' => $request->user()->id,
            'statut' => 'A_FAIRE',
        ]);

        // redirection directement vers le kanban de l'equipe
        return redirect()->route('tasks', ['equipe_id' => $tache->equipe_id])->with('status', 'Tâche créée avec succès.');
    }

    public function show(Request $request, Tache $tache): View
    {
        $membre = $this->ensureUserInTeam($request, $tache->equipe_id);
        $tache->load(['equipe', 'createur', 'responsable.utilisateur']);
        $canManage = (int) $tache->createur_id === (int) $request->user()->id || $membre->role === 'admin';
        $isResponsable = (int) $tache->responsable->user_id === (int) $request->user()->id;

        return view('taches.show', compact('tache', 'canManage', 'isResponsable'));
    }

    public function edit(Request $request, Tache $tache): View
    {
        $this->ensureCanManage($request, $tache);
        $membres = MembreEquipe::with('utilisateur')->where('equipe_id', $tache->equipe_id)->get();

        return view('taches.form', compact('tache', 'membres'));
    }

    public function update(Request $request, Tache $tache): RedirectResponse
    {
        $this->ensureCanManage($request, $tache);
        $data = $request->validate([
            'membre_equipe_id' => ['required', 'integer', 'exists:membre_equipe,id'],
            'titre' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'date_echeance' => ['nullable', 'date'],
        ], [
            'membre_equipe_id.required' => 'Veuillez désigner un responsable pour cette tâche.',
            'membre_equipe_id.exists' => 'Le responsable sélectionné n\'est pas valide.',
            'titre.required' => 'Le titre de la tâche est obligatoire.',
            'titre.max' => 'Le titre ne peut pas dépasser 255 caractères.',
            'date_echeance.date' => 'La date d\'échéance doit être une date valide.',
        ]);
        $this->ensureAssigneeInTeam($data['membre_equipe_id'], $tache->equipe_id);
        $tache->update($data);

        // retour direct au kanban avec message de succes (comme a la creation)
        return redirect()->route('tasks', ['equipe_id' => $tache->equipe_id])->with('status', 'Tâche modifiée avec succès.');
    }

    public function destroy(Request $request, Tache $tache): RedirectResponse
    {
        $this->ensureCanManage($request, $tache);
        $equipeId = $tache->equipe_id;
        $tache->delete();

        // retour direct au kanban
        return redirect()->route('tasks', ['equipe_id' => $equipeId])->with('status', 'Tâche supprimée avec succès.');
    }

    public function updateStatut(Request $request, Tache $tache): RedirectResponse
    {
        abort_unless(
            MembreEquipe::whereKey($tache->membre_equipe_id)
                ->where('user_id', $request->user()->id)
                ->exists(),
            403
        );
        $data = $request->validate([
            'statut' => ['required', 'in:A_FAIRE,EN_COURS,TERMINEE'],
        ], [
            'statut.required' => 'Veuillez sélectionner un statut.',
            'statut.in' => 'Le statut sélectionné n\'est pas valide.',
        ]);
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
