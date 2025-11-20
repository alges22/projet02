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
        Schema::create('annexe_anatts', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('adresse_annexe', 255)->nullable();
            $table->string('phone', 25)->unique()->nullable();
            $table->string('conduite_lieu_adresse')->nullable();
            $table->unsignedBigInteger('commune_id')->nullable();
            $table->unsignedBigInteger('departement_id')->nullable();
            $table->boolean('status')->default(true);
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
        Schema::dropIfExists('annexe_anatts');
    }
};