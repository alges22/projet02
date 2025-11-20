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
        Schema::create('compo_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidat_salle_id')->constrained('candidat_examen_salles');
            $table->enum('page', [
                'informations',
                'tuto',
                'start-compo',
                'questions',
                'thanks',
                'results',
                'logout'
            ])->nullable();
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
        Schema::dropIfExists('compo_pages');
    }
};
