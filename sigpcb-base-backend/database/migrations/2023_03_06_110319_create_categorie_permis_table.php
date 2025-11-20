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
        Schema::create('categorie_permis', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('description', 255)->nullable();
            $table->boolean('status')->default(false);
            $table->integer('age_min');
            $table->boolean('is_valid_age')->default(false);
            $table->integer('montant_militaire')->nullable();
            $table->integer('montant_etranger')->nullable();
            $table->integer('montant');
            $table->integer('note_min');
            $table->integer("montant_extension")->default(0);
            $table->unsignedBigInteger('permis_prealable')->nullable();
            $table->string('permis_prealable_dure')->nullable();
            $table->boolean('is_extension')->default(false);
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
        Schema::dropIfExists('categorie_permis');
    }
};