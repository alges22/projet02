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
        Schema::create('annexe_anatt_departements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('annexe_anatt_id')->constrained();

            # Département id n'est pas dans ce module
            $table->string('departement_id');
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
        Schema::dropIfExists('annexe_anatt_departements');
    }
};
