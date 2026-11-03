<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('taches', function (Blueprint $table) 
        {
            $table->id();
    
            $table->foreignId('equipe_id')->constrained('equipes')->cascadeOnDelete();//lorsqu'on supprime une equipe,la tache asssocié est supprimée automatiquement
            $table->foreignId('createur_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('membre_equipe_id')->constrained('membre_equipe')->restrictOnDelete();//on doit d'abord affecter la tache a un autre membre avant de supprimer le membre a qui la tache appartien
            $table->string('titre');
            $table->text('description')->nullable();
            $table->date('date_echeance')->nullable();
            $table->enum('statut', ['A_FAIRE', 'EN_COURS', 'TERMINEE'])->default('A_FAIRE');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taches');
    }
};
