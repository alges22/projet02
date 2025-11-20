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
        Schema::create('dossier_candidats', function (Blueprint $table) {
            $table->id();

            $table->string('npi');
            $table->string('groupage_test');
            $table->string('group_sanguin', 255)->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->unsignedBigInteger('candidat_id');
            $table->string('is_militaire')->nullable();
            $table->foreign('candidat_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('categorie_permis_id');
            $table->enum('state', ['pending', 'success', 'failed', 'closed'])->default('pending');
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
        Schema::dropIfExists('dossier_candidats');
    }
};
