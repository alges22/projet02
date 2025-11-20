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
        Schema::create('tranche_ages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('categorie_permis_id');
            $table->integer('age_min')->nullable();
            $table->integer('age_max')->nullable();
            $table->integer('validite');
            $table->boolean('status')->default(false);
            $table->foreign('categorie_permis_id')->references('id')->on('categorie_permis');

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
        Schema::dropIfExists('tranche_ages');
    }
};
