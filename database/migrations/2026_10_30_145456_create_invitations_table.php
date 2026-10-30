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
        Schema::create('invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipe_id')->constrained('equipes')->onDelete('cascade');
            $table->string('email');//genre l'email de la personne que on veut inviter 
            $table->foreignId('inviteur_id')->constrained('users')->onDelete('cascade');
            $table->string('token')->unique();//c'est pour identifier l'invitation de facon unique et comme on ne veut pas utiliser l'id pour raison de sécurité on préfere genener un token
            $table->string('statut')->default('en_attente'); // en_attente, acceptee, refusee
            $table->timestamp('date_expiration');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invitations');
    }
};
