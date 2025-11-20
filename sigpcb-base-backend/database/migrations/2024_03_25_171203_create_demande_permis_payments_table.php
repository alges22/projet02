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
        Schema::create('demande_permis_payments', function (Blueprint $table) {
            $table->id();
            $table->string('npi', 11);
            $table->decimal('montant', 32);
            $table->string('phone', 25);
            $table->string('operateur');
            $table->enum('status', ['pending', 'approved', 'declined', 'canceled'])->default('pending');
            $table->string('transactionId', 100)->unique();
            $table->string('payment_for');
            $table->date('date_payment');
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
        Schema::dropIfExists('demande_permis_payments');
    }
};
