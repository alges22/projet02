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
            $table->foreignId('langue_id')->constrained();
            $table->foreignId('examen_id')->nullable()->constrained();
            $table->unsignedBigInteger('annexe_id');
            $table->foreignId('dossier_candidat_id')->constrained();
            $table->foreignId('dossier_session_id')->constrained();
            $table->text('chapitres_id'); // séparé par des virgules (,)
            $table->string('npi');
            $table->boolean('status')->default(false);
            $table->boolean('certification')->default(false);
            $table->enum('state', ['pending', 'validate', 'rejet', 'init'])->default('init');
            $table->foreign('auto_ecole_id')->references('id')->on('auto_ecoles')->onDelete('cascade');
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
        Schema::dropIfExists('suivi_candidats');
    }
};