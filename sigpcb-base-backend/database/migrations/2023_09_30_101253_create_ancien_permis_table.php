<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ancien_permis', function (Blueprint $table) {
            $table->id();
            $table->string('num_matricule');
            $table->string('num_permis');
            $table->string('fichier_permis_prealable');
            $table->foreignId('categorie_permis_id')->constrained();
            $table->foreignId('candidat_id')->constrained();
            $table->foreignId('dossier_candidat_id')->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ancien_permis');
    }
};