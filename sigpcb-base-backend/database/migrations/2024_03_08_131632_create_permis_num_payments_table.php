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
        Schema::create('permis_num_payments', function (Blueprint $table) {
            $table->id();
            $table->string('npi');
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
            $table->enum('status', ['pending', 'approved', 'declined', 'canceled'])->default('pending');
            $table->string('num_transaction')->nullable();
            $table->dateTime('date_payment');

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
        Schema::dropIfExists('permis_num_payments');
    }
};