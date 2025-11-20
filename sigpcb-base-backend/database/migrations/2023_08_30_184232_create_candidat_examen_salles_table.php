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
        Schema::create('candidat_examen_salles', function (Blueprint $table) {
            $table->id();
            $table->string('npi');
            $table->foreignId('vague_id')->constrained();
            $table->foreignId('salle_compo_id')->constrained();
            $table->unsignedBigInteger('annexe_id');
            $table->foreignId('langue_id')->constrained();
            $table->foreignId('categorie_permis_id')->constrained();
            $table->foreignId('examen_id')->constrained();
            $table->unsignedInteger('num_table');
            $table->string("emargement")->nullable();
            $table->dateTime('emargement_at')->nullable();
            $table->dateTime('abscence_at')->nullable();
            $table->enum("presence", ['abscent', 'present'])->nullable();
            $table->boolean('closed')->default(false);

            $table->foreignId('dossier_session_id')->constrained();
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
        Schema::dropIfExists('candidat_examen_salles');
    }
};
