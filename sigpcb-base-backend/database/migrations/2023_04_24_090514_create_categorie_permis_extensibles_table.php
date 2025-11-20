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
        Schema::create('categorie_permis_extensibles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('categorie_permis_id');
            $table->unsignedBigInteger('categorie_permis_extensible_id');
            $table->timestamps();

            // Déclaration des clés étrangères
            $table->foreign('categorie_permis_id')->references('id')->on('categorie_permis')->onDelete('cascade');
            $table->foreign('categorie_permis_extensible_id', 'pm_ext_id')->references('id')->on('categorie_permis')->onDelete('cascade');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('categorie_permis_extensibles');
    }
};
