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
        Schema::create('demande_moniteurs', function (Blueprint $table) {
            $table->id();
            $table->string('npi',11);
            $table->string('email');
            $table->string('num_permis');
            $table->string('permis_file')->nullable();
            $table->string('diplome_file')->nullable();
            $table->string('categorie_permis_ids');
            $table->foreignId('moniteur_id')->constrained();
            $table->enum('state', ['init', 'validate', 'rejected', 'pending', 'payment'])->default('init');
            $table->date('date_validation')->nullable();
            $table->date('date_rejet')->nullable();
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
        Schema::dropIfExists('demande_moniteurs');
    }
};
