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
        Schema::create('auto_ecole_infos', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->enum('state', ['init', 'validate', 'rejected', 'pending'])->default('init');
            $table->string('email', 255);
            $table->string('phone', 25);
            $table->string('adresse', 255)->nullable();
            $table->unsignedBigInteger('commune_id');
            $table->text('moniteurs')->nullable();
            $table->unsignedBigInteger('departement_id');
            $table->year('annee_creation')->nullable();
            $table->string('num_ifu', 255);
            $table->foreignId("auto_ecole_id")->constrained();
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
        Schema::dropIfExists('auto_ecole_infos');
    }
};
