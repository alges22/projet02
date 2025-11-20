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
        Schema::create('demande_licences', function (Blueprint $table) {
            $table->id();
            $table->string('reference'); //référence agrément
            $table->foreignId('auto_ecole_id');
            $table->foreignId('promoteur_id')->constrained('promoteurs');
            $table->string('npi', 11);
            $table->text('moniteurs'); //encode sous form json
            $table->text('vehicules'); //json
            $table->dateTime('date_validation')->nullable();
            $table->dateTime('date_rejet')->nullable();
            $table->enum('state', ['init', 'validate', 'rejected', 'pending'])->default('init');
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
        Schema::dropIfExists('demande_licences');
    }
};