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
        Schema::create('eservice_parcour_suivis', function (Blueprint $table) {
            $table->id();
            $table->string('service');
            $table->string('candidat_id');
            $table->unsignedBigInteger('auto_ecole_id')->nullable();
            $table->unsignedBigInteger('agent_id')->nullable();
            $table->unsignedBigInteger('categorie_permis_id')->nullable();
            $table->string('npi');
            $table->string('slug')->nullable();
            $table->longText('message')->nullable();
            $table->json('bouton')->nullable();
            $table->json('eservice')->nullable();
            $table->string('action')->nullable();
            $table->text('url')->nullable();
            $table->date('date_action');
            $table->unsignedBigInteger('dossier_candidat_id')->nullable();
            $table->unsignedBigInteger('dossier_session_id')->nullable();
            $table->foreign('dossier_session_id')->references('id')->on('dossier_sessions')->onDelete('cascade');
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
        Schema::dropIfExists('eservice_parcour_suivis');
    }
};
