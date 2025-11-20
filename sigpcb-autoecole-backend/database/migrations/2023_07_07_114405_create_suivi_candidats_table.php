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
        Schema::create('suivi_candidats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('auto_ecole_id');
            $table->unsignedBigInteger('categorie_permis_id');
            $table->unsignedBigInteger('langue_id');
            $table->unsignedBigInteger('examen_id')->nullable();
            $table->unsignedBigInteger('annexe_id');
            $table->unsignedBigInteger('dossier_candidat_id');
            $table->unsignedBigInteger('dossier_session_id');
            $table->text('chapitres_id'); // séparé par des virgules (,)
            $table->string('npi');
            $table->boolean('status')->default(false);
            $table->boolean('certification')->default(false);
            $table->enum('state', ['pending', 'validate', 'rejet', 'init'])->default('init');

            $table->timestamps();
            $table->foreign('auto_ecole_id')->references('id')->on('auto_ecoles')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('suivi_candidats');
    }
};
