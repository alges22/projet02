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
            $table->text('carte_grise');
            $table->text('assurance_visite');
            $table->text('photo_vehicules');
            $table->foreignId('demande_licence_id')->constrained();

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