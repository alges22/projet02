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
        Schema::create('auto_ecole_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('service');
            $table->string('action'); //L'action spéficique de l'historique
            $table->string('title'); // Titre de la notification
            $table->string('npi');
            $table->longText('message')->nullable();
            $table->json('bouton')->nullable(); //Si un bouton est nécessaire
            $table->text("data")->nullable(); // Contiendra les informations concernant le model concerné
            $table->foreignId('promoteur_id')->constrained('promoteurs');
            $table->foreignId('auto_ecole_id')->nullable()->constrained();
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
        Schema::dropIfExists('auto_ecole_notifications');
    }
};