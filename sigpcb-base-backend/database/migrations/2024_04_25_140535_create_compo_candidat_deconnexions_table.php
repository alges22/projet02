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
        Schema::create('compo_candidat_deconnexions', function (Blueprint $table) {
            $table->id();
            $table->string("npi");
            $table->foreignId('candidat_salle_id')->constrained("candidat_examen_salles");
            $table->text('motif');
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
        Schema::dropIfExists('compo_candidat_deconnexions');
    }
};
