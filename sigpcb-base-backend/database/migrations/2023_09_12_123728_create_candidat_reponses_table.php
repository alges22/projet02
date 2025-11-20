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
        Schema::create('candidat_reponses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidat_salle_id')->constrained('candidat_examen_salles');
            $table->unsignedBigInteger('dossier_candidat_id');
            $table->boolean('is_correct');
            $table->foreignId('question_id')->constrained();
            $table->string('answers'); // l'ID des réponses sous forme json
            $table->dateTime("answers_at"); //
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
        Schema::dropIfExists('candidat_reponses');
    }
};