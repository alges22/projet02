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
        Schema::create('chapitres_categories_permis', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('chapitre_id');
            $table->unsignedBigInteger('categorie_permis_id');
            $table->timestamps();

            $table->foreign('chapitre_id')->references('id')->on('chapitres');
            $table->foreign('categorie_permis_id')->references('id')->on('categorie_permis');
        });
    }

    public function down()
    {
        Schema::dropIfExists('chapitres_categories_permis');
    }
};