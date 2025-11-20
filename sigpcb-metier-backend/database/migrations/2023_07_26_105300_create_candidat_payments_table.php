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
        Schema::create('candidat_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('candidat_id');
            $table->unsignedBigInteger('auto_ecole_id')->nullable();
            $table->string('agregateur');
            $table->text('description');
            $table->string('transaction_id');
            $table->string('reference');
            $table->string('mode');
            $table->string('operation');
            $table->string('transaction_key');
            $table->unsignedDecimal('montant', 8, 2);
            $table->string('phone_payment', 25);
            $table->string('ref_operateur')->nullable();
            $table->string('numero_recu')->nullable();
            $table->enum('moyen_payment', ['momo', 'portefeuille'])->default('momo');
            $table->enum('status', ['pending', 'approved', 'declined','canceled'])->default('pending');
            $table->string('num_transaction')->nullable();
            $table->dateTime('date_payment'); 
            $table->unsignedBigInteger('dossier_candidat_id');
            $table->unsignedBigInteger('dossier_session_id');
            $table->unsignedBigInteger('examen_id')->nullable();
            $table->timestamps();

            $table->foreign('candidat_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('dossier_candidat_id')->references('id')->on('dossier_candidats')->onDelete('cascade');
            // $table->foreign('auto_ecole_id')->references('id')->on('auto_ecoles')->onDelete('cascade');
            // $table->foreign('agregateur_id')->references('id')->on('agregateurs')->onDelete('cascade');
            // $table->foreign('examen_id')->references('id')->on('examens')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('candidat_payments');
    }
};
