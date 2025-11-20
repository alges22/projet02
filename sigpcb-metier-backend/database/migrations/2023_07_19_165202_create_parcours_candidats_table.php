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
        Schema::create('parcours_candidats', function (Blueprint $table) {
            $table->id();
            $table->string('npi');
            $table->unsignedBigInteger('candidat_id');
            $table->unsignedBigInteger('auto_ecole_id')->nullable();
            $table->unsignedBigInteger('categorie_permis_id');
            $table->unsignedBigInteger('examen_id')->nullable();
            $table->unsignedBigInteger('annexe_anatt_id');
            $table->string('examen_type');
            $table->string('candidat_ecrit_note')->nullable();
            $table->string('candidat_conduite_note')->nullable();
            $table->boolean('candidat_presence')->default(false);
            $table->boolean('is_close')->default(false);
            $table->unsignedBigInteger('dossier_candidat_id');
            $table->foreign('candidat_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('dossier_candidat_id')->references('id')->on('dossier_candidats')->onDelete('cascade');
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
        Schema::dropIfExists('parcours_candidats');
    }
};
