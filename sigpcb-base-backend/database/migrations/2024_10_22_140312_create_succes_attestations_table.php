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
        Schema::create('succes_attestations', function (Blueprint $table) {
            $table->id();
            $table->string('npi');
            $table->string('email');
            $table->unsignedBigInteger('examen_id')->nullable();
            $table->enum('status', ['pending', 'approved', 'declined', 'canceled'])->default('pending');
            $table->foreignId('categorie_permis_id')->nullable()->constrained();
            $table->foreignId('dossier_session_id')->nullable()->constrained();
            $table->foreignId('candidat_id')->constrained();
            $table->foreignId('dossier_candidat_id')->nullable()->constrained();
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
        Schema::dropIfExists('succes_attestations');
    }
};
