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
        Schema::create('dispense_paiements', function (Blueprint $table) {
            $table->id();
            $table->dateTime('validated_at')->nullable();
            $table->dateTime('rejeted_at')->nullable();
            $table->string('validator_npi')->nullable();
            $table->string('created_by')->nullable();
            $table->integer('validator_id')->nullable();
            $table->enum('status', ['init', 'used', 'validated', 'rejected']);
            $table->dateTime('used_at')->nullable();
            $table->integer('examen_id')->nullable();
            $table->string('candidat_npi')->nullable();
            $table->integer('dossier_session_id')->nullable();
            $table->text('note')->nullable();
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
        Schema::dropIfExists('dispense_paiements');
    }
};
