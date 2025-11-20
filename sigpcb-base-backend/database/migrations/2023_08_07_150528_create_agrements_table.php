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
        Schema::create('agrements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promoteur_id')->constrained('promoteurs');
            $table->dateTime('date_obtention');
            $table->string('code')->unique();

            $table->foreignId('demande_agrement_id')->constrained();
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
        Schema::dropIfExists('agrements');
    }
};