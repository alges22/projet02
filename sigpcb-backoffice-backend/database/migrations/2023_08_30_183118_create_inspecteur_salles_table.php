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
        Schema::create('inspecteur_salles', function (Blueprint $table) {
            $table->id();
            $table->string("inspecteur_id");
            $table->foreignId("salle_compo_id"); //On peut avoir l'annexe avec ça
            $table->unsignedBigInteger("examen_id");
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
        Schema::dropIfExists('inspecteur_salles');
    }
};
