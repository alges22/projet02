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
        Schema::create('annexe_resultat_states', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('annexe_id');
            $table->foreignId('examen_id')->constrained();
            $table->boolean('ready')->default(false);
            $table->enum('type', ['code', 'conduite'])->default('code');
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
        Schema::dropIfExists('annexe_resultat_states');
    }
};