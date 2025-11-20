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
        Schema::create('demande_licence_files', function (Blueprint $table) {
            $table->id();
            $table->string('diplome_moniteur');
            $table->string('permis_moniteurs');
            $table->string('carte_grise');
            $table->string('assurance_visite');
            $table->string('photo_vehicules');
            $table->foreignId('demande_licence_id');
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
        Schema::dropIfExists('demande_licence_files');
    }
};
