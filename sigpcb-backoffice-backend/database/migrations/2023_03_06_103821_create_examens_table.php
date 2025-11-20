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
        Schema::create('examens', function (Blueprint $table) {
            $table->id();
            $table->dateTime('debut_etude_dossier_at');
            $table->dateTime('fin_etude_dossier_at');
            $table->dateTime('debut_gestion_rejet_at');
            $table->dateTime('fin_gestion_rejet_at');
            $table->dateTime('date_code');
            $table->dateTime('date_conduite');
            $table->dateTime('date_convocation');
            $table->boolean('status')->default(false);
            $table->string('mois', 12);
            $table->string('annee');
            $table->string('numero', 10)->nullable();
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
        Schema::dropIfExists('examens');
    }
};
