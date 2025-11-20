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
        Schema::create('jury_candidats', function (Blueprint $table) {
            $table->id();
            $table->string('npi');
            $table->unsignedBigInteger('jury_id');
            $table->foreignId('conduite_vague_id')->constrained();
            $table->foreignId('dossier_session_id')->constrained();
            $table->unsignedBigInteger('langue_id');
            $table->unsignedBigInteger('categorie_permis_id');
            $table->foreignId('examen_id')->constrained();
            $table->unsignedBigInteger('annexe_id')->nullable();
            $table->string('signature')->nullable();
            $table->boolean("closed")->default(false);
            $table->enum('resultat_conduite', ['success', 'failed'])->nullable();
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
        Schema::dropIfExists('jury_candidats');
    }
};
