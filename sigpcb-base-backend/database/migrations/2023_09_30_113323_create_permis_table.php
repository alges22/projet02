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
        Schema::create('permis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('examen_id')->nullable()->constrained();
            $table->unsignedBigInteger('dossier_session_id');
            $table->string('npi', 255);
            $table->string('code_permis', 32);
            $table->unsignedBigInteger('categorie_permis_id');
            $table->unsignedBigInteger('jury_candidat_id')->nullable();
            $table->boolean('imported')->default(false);
            $table->boolean('status')->default(true);
            $table->dateTime('expired_at')->nullable();
            $table->foreign('categorie_permis_id')->references('id')->on('categorie_permis');
            $table->foreign('jury_candidat_id')->references('id')->on('jury_candidats')->nullable();
            $table->unsignedBigInteger('deliver_id');
            $table->unsignedBigInteger('signataire_id');
            $table->dateTime('delivered_at');
            $table->dateTime('signed_at');
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
        Schema::dropIfExists('permis');
    }
};