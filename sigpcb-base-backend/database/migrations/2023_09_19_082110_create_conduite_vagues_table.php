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
        Schema::create('conduite_vagues', function (Blueprint $table) {
            // en jour
            $table->id();
            $table->integer('numero')->nullable();
            $table->dateTime('date_compo')->nullable();
            $table->boolean('closed')->default(false);
            $table->bigInteger('annexe_anatt_id');
            $table->foreignId('examen_id')->constrained();

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
        Schema::dropIfExists('conduite_vagues');
    }
};