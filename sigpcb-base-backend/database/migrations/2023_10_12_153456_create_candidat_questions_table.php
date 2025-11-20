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
        Schema::create('candidat_questions', function (Blueprint $table) {
            $table->id();
            $table->text('questions');
            $table->foreignId('dossier_candidat_id')->constrained();
            $table->foreignId('candidat_salle_id')->constrained("candidat_examen_salles");
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
        Schema::dropIfExists('candidat_questions');
    }
};