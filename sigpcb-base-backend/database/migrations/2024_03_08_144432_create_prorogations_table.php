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
        Schema::create('prorogations', function (Blueprint $table) {
            $table->id();
            $table->string('npi', 11);
            $table->string('email');
            $table->string('num_permis');
            $table->string('permis_file');
            $table->string('group_sanguin');
            $table->string('group_sanguin_file');
            $table->string('fiche_medical_file');
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
        Schema::dropIfExists('prorogations');
    }
};
