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
        Schema::create('entreprise_parcour_suivis', function (Blueprint $table) {
            $table->id();
            $table->string('service');
            $table->unsignedBigInteger('entreprise_id');
            $table->unsignedBigInteger('recrutement_id');
            $table->unsignedBigInteger('agent_id')->nullable();
            $table->unsignedBigInteger('categorie_permis_id')->nullable();
            $table->string('slug')->nullable();
            $table->longText('message')->nullable();
            $table->json('bouton')->nullable();
            $table->json('eservice')->nullable();
            $table->string('action')->nullable();
            $table->text('url')->nullable();
            $table->date('date_action');
            $table->foreign('entreprise_id')->references('id')->on('entreprises')->onDelete('cascade');
            $table->foreign('recrutement_id')->references('id')->on('recrutements')->onDelete('cascade');
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
        Schema::dropIfExists('entreprise_parcour_suivis');
    }
};
