<?php

namespace Database\Seeders;

use App\Models\Equipe;
use App\Models\MembreEquipe;
use App\Models\Tache;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    
    public function run(): void
    {
        // 1. Création des utilisateurs de test (mot de passe: password)
        $heloise = User::firstOrCreate(
            ['email' => 'ak.heloise2007@gmail.com'],
            [
                'name'       => 'Akohou',
                'first_name' => 'Héloïse',
                'password'   => Hash::make('password'),
            ]
        );

        $endedans = User::firstOrCreate(
            ['email' => 'ici.endedans@gmail.com'],
            [
                'name'       => 'Ici',
                'first_name' => 'Endedans',
                'password'   => Hash::make('password'),
            ]
        );

        $marc = User::firstOrCreate(
            ['email' => 'marc.dupont@taskflow.test'],
            [
                'name'       => 'Dupont',
                'first_name' => 'Marc',
                'password'   => Hash::make('password'),
            ]
        );

        $sarah = User::firstOrCreate(
            ['email' => 'sarah.martin@taskflow.test'],
            [
                'name'       => 'Martin',
                'first_name' => 'Sarah',
                'password'   => Hash::make('password'),
            ]
        );

        // 2. Équipe 1 : Equipe marketing (Admin: Héloïse)
        $equipeMarketing = Equipe::firstOrCreate(
            ['nom' => 'Equipe marketing'],
            [
                'description' => 'Campagnes réseaux sociaux, refonte du branding et acquisition.',
                'createur_id' => $heloise->id,
            ]
        );

        // Rattachement des membres à l'équipe marketing (max 6 membres CDC §3.2)
        $mHeloise = MembreEquipe::firstOrCreate(['user_id' => $heloise->id, 'equipe_id' => $equipeMarketing->id], ['role' => 'admin']);
        $mEndedans = MembreEquipe::firstOrCreate(['user_id' => $endedans->id, 'equipe_id' => $equipeMarketing->id], ['role' => 'membre']);
        $mMarc = MembreEquipe::firstOrCreate(['user_id' => $marc->id, 'equipe_id' => $equipeMarketing->id], ['role' => 'membre']);
        $mSarah = MembreEquipe::firstOrCreate(['user_id' => $sarah->id, 'equipe_id' => $equipeMarketing->id], ['role' => 'membre']);

        // 3. Équipe 2 : Refonte Application Web (Admin: Endedans)
        $equipeDev = Equipe::firstOrCreate(
            ['nom' => 'Refonte Application Web'],
            [
                'description' => 'Développement du nouveau tableau de bord et intégration API.',
                'createur_id' => $endedans->id,
            ]
        );

        $mDevEndedans = MembreEquipe::firstOrCreate(['user_id' => $endedans->id, 'equipe_id' => $equipeDev->id], ['role' => 'admin']);
        $mDevHeloise = MembreEquipe::firstOrCreate(['user_id' => $heloise->id, 'equipe_id' => $equipeDev->id], ['role' => 'membre']);
        $mDevMarc = MembreEquipe::firstOrCreate(['user_id' => $marc->id, 'equipe_id' => $equipeDev->id], ['role' => 'membre']);

        // 4. Tâches réalistes pour tester chaque statut et le filtre "En retard"
        
        // Tâche 1 : En cours (Assignée à Endedans)
        Tache::firstOrCreate(
            ['titre' => 'Rédiger les posts LinkedIn pour le lancement', 'equipe_id' => $equipeMarketing->id],
            [
                'description'      => 'Préparer 5 posts courts avec les visuels validés.',
                'createur_id'      => $heloise->id,
                'membre_equipe_id' => $mEndedans->id,
                'date_echeance'    => now()->addDays(4),
                'statut'           => 'EN_COURS',
            ]
        );

        // Tâche 2 : En retard (Date dépassée pour tester le filtre rouge)
        Tache::firstOrCreate(
            ['titre' => 'Validation des devis prestataires vidéo', 'equipe_id' => $equipeMarketing->id],
            [
                'description'      => 'Comparer les deux devis reçus et valider avec la compta.',
                'createur_id'      => $heloise->id,
                'membre_equipe_id' => $mSarah->id,
                'date_echeance'    => now()->subDays(3), // Échéance il y a 3 jours !
                'statut'           => 'A_FAIRE',
            ]
        );

        // Tâche 3 : À faire (Assignée à Marc)
        Tache::firstOrCreate(
            ['titre' => 'Mise à jour des bannières Twitter/X', 'equipe_id' => $equipeMarketing->id],
            [
                'description'      => 'Adapter les formats en 1500x500px.',
                'createur_id'      => $heloise->id,
                'membre_equipe_id' => $mMarc->id,
                'date_echeance'    => now()->addDays(7),
                'statut'           => 'A_FAIRE',
            ]
        );

        // Tâche 4 : Terminée (Assignée à Héloïse)
        Tache::firstOrCreate(
            ['titre' => 'Définition de la charte graphique et du logo', 'equipe_id' => $equipeMarketing->id],
            [
                'description'      => 'Choix des couleurs primaires et typographies Inter.',
                'createur_id'      => $heloise->id,
                'membre_equipe_id' => $mHeloise->id,
                'date_echeance'    => now()->subDays(1),
                'statut'           => 'TERMINEE',
            ]
        );

        // Tâche 5 dans l'équipe Dev : En cours (Assignée à Endedans)
        Tache::firstOrCreate(
            ['titre' => 'Optimisation des requêtes SQL du Dashboard', 'equipe_id' => $equipeDev->id],
            [
                'description'      => 'Ajouter les index sur membre_equipe_id et date_echeance.',
                'createur_id'      => $endedans->id,
                'membre_equipe_id' => $mDevEndedans->id,
                'date_echeance'    => now()->addDays(2),
                'statut'           => 'EN_COURS',
            ]
        );
    }
}