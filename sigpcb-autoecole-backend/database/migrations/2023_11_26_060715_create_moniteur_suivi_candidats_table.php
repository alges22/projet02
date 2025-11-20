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
        Schema::create('moniteur_suivi_candidats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('suivi_candidat_id')->constrained('suivi_candidats');
            $table->unsignedDecimal('moniteur_id');
            $table->enum('user', ['promoteur', 'moniteur'])->default('moniteur');
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
        Schema::dropIfExists('moniteur_suivi_candidats');
    }
};
