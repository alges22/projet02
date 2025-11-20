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
            $table->unsignedBigInteger('auto_ecole_id')->nullable();
            $table->foreignId('categorie_permis_id')->constrained();
            $table->foreignId('examen_id')->nullable()->constrained();
            $table->unsignedBigInteger('annexe_anatt_id');
            $table->string('examen_type');
            $table->string('candidat_ecrit_note')->nullable();
            $table->string('candidat_conduite_note')->nullable();
            $table->boolean('candidat_presence')->default(false);
            $table->boolean('is_close')->default(false);
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
        Schema::dropIfExists('parcours_candidats');
    }
};